<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * MarketPriceService
 *
 * Fetches live cryptocurrency market prices for the swap feature.
 *
 * CoinGecko is the primary provider (broad fiat support, no key required on
 * the free tier). Binance's public ticker API is used as an automatic fallback
 * when CoinGecko is unavailable, rate-limited or returns an invalid payload.
 *
 * All network calls are batched and cached so a page load or a conversion
 * never fires one request per coin (the previous implementation exhausted the
 * free-tier rate limit and intermittently returned empty results).
 *
 * A market-price API only provides exchange-rate information. It is NOT a
 * payment processor and does not prove that any transfer has occurred.
 */
class MarketPriceService
{
    /**
     * TTL (seconds) for a cached USD price map. Short enough to stay fresh,
     * long enough to absorb a burst of UI requests without new HTTP calls.
     */
    public const CACHE_TTL = 60;

    /**
     * Application coin symbol => CoinGecko id.
     */
    public const COINGECKO_IDS = [
        'btc' => 'bitcoin',
        'eth' => 'ethereum',
        'ltc' => 'litecoin',
        'link' => 'chainlink',
        'bnb' => 'binancecoin',
        'ada' => 'cardano',
        'aave' => 'aave',
        'usdt' => 'tether',
        'bch' => 'bitcoin-cash',
        'xrp' => 'ripple',
        'xlm' => 'stellar',
        'sol' => 'solana',
    ];

    /**
     * Application coin symbol => Binance symbol (USDT quote).
     */
    public const BINANCE_SYMBOLS = [
        'btc' => 'BTCUSDT',
        'eth' => 'ETHUSDT',
        'ltc' => 'LTCUSDT',
        'link' => 'LINKUSDT',
        'bnb' => 'BNBUSDT',
        'ada' => 'ADAUSDT',
        'aave' => 'AAVEUSDT',
        'usdt' => 'USDTUSDT',
        'bch' => 'BCHUSDT',
        'xrp' => 'XRPUSDT',
        'xlm' => 'XLMUSDT',
        'sol' => 'SOLUSDT',
    ];

    /**
     * Human friendly labels for display.
     */
    public const LABELS = [
        'usd' => 'US Dollar',
        'btc' => 'Bitcoin',
        'eth' => 'Ethereum',
        'ltc' => 'Litecoin',
        'link' => 'Chainlink',
        'bnb' => 'Binance Coin',
        'ada' => 'Cardano',
        'aave' => 'Aave',
        'usdt' => 'Tether',
        'bch' => 'Bitcoin Cash',
        'xrp' => 'XRP',
        'xlm' => 'Stellar',
        'sol' => 'Solana',
    ];

    /**
     * Ticker symbols for display.
     */
    public const TICKERS = [
        'usd' => 'USD',
        'btc' => 'BTC',
        'eth' => 'ETH',
        'ltc' => 'LTC',
        'link' => 'LINK',
        'bnb' => 'BNB',
        'ada' => 'ADA',
        'aave' => 'AAVE',
        'usdt' => 'USDT',
        'bch' => 'BCH',
        'xrp' => 'XRP',
        'xlm' => 'XLM',
        'sol' => 'SOL',
    ];

    /**
     * Return the list of supported coin symbols.
     */
    public static function supportedSymbols(): array
    {
        return array_keys(self::COINGECKO_IDS);
    }

    public static function isSupported(string $symbol): bool
    {
        return array_key_exists(strtolower($symbol), self::COINGECKO_IDS);
    }

    public static function label(string $symbol): string
    {
        $symbol = strtolower($symbol);

        return self::LABELS[$symbol] ?? strtoupper($symbol);
    }

    public static function ticker(string $symbol): string
    {
        $symbol = strtolower($symbol);

        return self::TICKERS[$symbol] ?? strtoupper($symbol);
    }

    /**
     * Get the current USD price for a single symbol.
     *
     * @param string $symbol e.g. "btc"
     * @return float|null null when the price is unavailable
     */
    public function getUsdPrice(string $symbol): ?float
    {
        $symbol = strtolower($symbol);

        if ($symbol === 'usd') {
            return 1.0;
        }

        if (!self::isSupported($symbol)) {
            return null;
        }

        $prices = $this->getUsdPrices([$symbol]);

        return $prices[$symbol] ?? null;
    }

    /**
     * Get the USD price for many symbols using a single batched, cached fetch.
     *
     * @param string[] $symbols
     * @return array<string,float>
     */
    public function getUsdPrices(array $symbols): array
    {
        $symbols = array_values(array_unique(array_map('strtolower', $symbols)));
        $symbols = array_values(array_filter($symbols, function ($symbol) {
            return $symbol !== 'usd' && self::isSupported($symbol);
        }));

        if (empty($symbols)) {
            return [];
        }

        $result = [];
        $missing = [];

        foreach ($symbols as $symbol) {
            $cached = Cache::get($this->cacheKey($symbol));
            if (is_numeric($cached)) {
                $result[$symbol] = (float) $cached;
            } else {
                $missing[] = $symbol;
            }
        }

        if (empty($missing)) {
            return $result;
        }

        $fetched = $this->fetchCoinGeckoUsd($missing);

        // Fall back to Binance only for symbols CoinGecko did not return.
        $stillMissing = array_values(array_diff($missing, array_keys($fetched)));
        if (!empty($stillMissing)) {
            $binance = $this->fetchBinanceUsd($stillMissing);
            $fetched = array_merge($fetched, $binance);
        }

        foreach ($fetched as $symbol => $price) {
            if (!is_numeric($price) || $price <= 0) {
                continue;
            }
            $result[$symbol] = (float) $price;
            Cache::put($this->cacheKey($symbol), (float) $price, now()->addSeconds(self::CACHE_TTL));
        }

        return $result;
    }

    /**
     * Get the exchange rate between two symbols (base / quote) using USD as
     * the common denominator.
     */
    public function getRate(string $base, string $quote): ?float
    {
        $base = strtolower($base);
        $quote = strtolower($quote);

        if ($base === $quote) {
            return 1.0;
        }

        $baseUsd = $base === 'usd' ? 1.0 : $this->getUsdPrice($base);
        $quoteUsd = $quote === 'usd' ? 1.0 : $this->getUsdPrice($quote);

        if ($baseUsd === null || $quoteUsd === null || (float) $quoteUsd <= 0) {
            return null;
        }

        return (float) $baseUsd / (float) $quoteUsd;
    }

    /**
     * Fetch USD prices from CoinGecko for a set of symbols in one request.
     */
    private function fetchCoinGeckoUsd(array $symbols): array
    {
        $ids = array_values(array_map(function ($symbol) {
            return self::COINGECKO_IDS[$symbol];
        }, $symbols));

        $headers = [
            'Accept' => 'application/json',
            'User-Agent' => (string) config('app.name', 'Laravel') . ' crypto price calculator',
        ];

        $apiKey = (string) config('services.coingecko.api_key', '');
        if ($apiKey !== '') {
            $headers['x-cg-demo-api-key'] = $apiKey;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders($headers)
                ->get('https://api.coingecko.com/api/v3/simple/price', [
                    'ids' => implode(',', $ids),
                    'vs_currencies' => 'usd',
                ]);

            if ($response->successful()) {
                $body = $response->json();
                $map = [];
                foreach ($symbols as $symbol) {
                    $id = self::COINGECKO_IDS[$symbol];
                    if (is_array($body) && isset($body[$id]['usd']) && is_numeric($body[$id]['usd'])) {
                        $map[$symbol] = (float) $body[$id]['usd'];
                    }
                }

                if (!empty($map)) {
                    return $map;
                }
            }

            Log::warning('MarketPriceService: CoinGecko request failed or returned an invalid payload.', [
                'status' => $response->status(),
                'body' => substr((string) $response->body(), 0, 300),
            ]);
        } catch (\Throwable $e) {
            Log::warning('MarketPriceService: CoinGecko request threw an exception.', [
                'error' => $e->getMessage(),
            ]);
        }

        return [];
    }

    /**
     * Fetch USDT prices from Binance (≈ USD) for a set of symbols in one request.
     */
    private function fetchBinanceUsd(array $symbols): array
    {
        $binanceSymbols = array_values(array_filter(array_map(function ($symbol) {
            return self::BINANCE_SYMBOLS[$symbol] ?? null;
        }, $symbols)));

        if (empty($binanceSymbols)) {
            return [];
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders(['Accept' => 'application/json'])
                ->get('https://api.binance.com/api/v3/ticker/price', [
                    'symbols' => '[' . implode(',', array_map(function ($s) {
                        return '"' . $s . '"';
                    }, $binanceSymbols)) . ']',
                ]);

            if ($response->successful()) {
                $body = $response->json();
                $map = [];
                if (is_array($body)) {
                    foreach ($body as $ticker) {
                        $binanceSymbol = $ticker['symbol'] ?? null;
                        if ($binanceSymbol === null || !isset($ticker['price']) || !is_numeric($ticker['price'])) {
                            continue;
                        }
                        $symbol = array_search($binanceSymbol, self::BINANCE_SYMBOLS, true);
                        if ($symbol !== false) {
                            $map[$symbol] = (float) $ticker['price'];
                        }
                    }
                }

                if (!empty($map)) {
                    return $map;
                }
            }

            Log::warning('MarketPriceService: Binance request failed or returned an invalid payload.', [
                'status' => $response->status(),
                'body' => substr((string) $response->body(), 0, 300),
            ]);
        } catch (\Throwable $e) {
            Log::warning('MarketPriceService: Binance request threw an exception.', [
                'error' => $e->getMessage(),
            ]);
        }

        return [];
    }

    private function cacheKey(string $symbol): string
    {
        return 'market_price_usd_' . strtolower($symbol);
    }
}
