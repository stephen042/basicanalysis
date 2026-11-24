<?php

namespace App\Traits;

use App\Services\MarketPriceService;

trait Apitrait
{
    /**
     * Get the current USD exchange rate for a cryptocurrency.
     *
     * This is a thin, backward-compatible wrapper around MarketPriceService,
     * which batches requests and caches results to avoid rate-limit failures.
     *
     * @param string $coin The symbol of the cryptocurrency (e.g., 'btc', 'eth').
     * @param string $currency The quote currency (the swap feature uses 'usd').
     * @return float|null The exchange rate, or null if the coin is unsupported or the price is unavailable.
     */
    public function get_rate($coin, $currency = 'usd')
    {
        $coin = strtolower((string) $coin);
        $currency = strtolower((string) $currency);

        if ($coin === 'usd') {
            return 1.0;
        }

        // The swap feature anchors conversions in USD. USDT is treated as a
        // 1:1 stand-in for USD when a USD price is requested.
        if ($currency === 'usd' || $currency === 'usdt') {
            return app(MarketPriceService::class)->getUsdPrice($coin);
        }

        return null;
    }
}
