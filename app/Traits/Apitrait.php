<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait Apitrait
{
    /**
     * Get the exchange rate of a cryptocurrency in a given currency using the CoinGecko API.
     * Results are cached for 5 minutes to improve performance and reduce API calls.
     *
     * @param string $coin The symbol of the cryptocurrency (e.g., 'btc', 'eth').
     * @param string $currency The symbol of the currency to get the price in (e.g., 'usd').
     * @return float|null The exchange rate, or null if the API call fails or the coin is not found.
     */
    public function get_rate($coin, $currency)
    {
        $coin = strtolower($coin);
        $currency = strtolower($currency);
        $cacheKey = "price_{$coin}_to_{$currency}";

        // Cache the result for 5 minutes.
        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($coin, $currency) {
            // Map our application's coin symbols to CoinGecko's API identifiers.
            $coinIdMap = [
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
            ];

            // If the coin is not in our map, we cannot fetch its price.
            if (!array_key_exists($coin, $coinIdMap)) {
                Log::error("CoinGecko API: Coin ID not found for symbol '{$coin}'");
                return null;
            }

            $coinId = $coinIdMap[$coin];
            $apiUrl = "https://api.coingecko.com/api/v3/simple/price";

            try {
                $response = Http::get($apiUrl, [
                    'ids' => $coinId,
                    'vs_currencies' => $currency,
                ]);

                if ($response->successful() && $response->json() && isset($response->json()[$coinId][$currency])) {
                    return (float) $response->json()[$coinId][$currency];
                } else {
                    Log::error("CoinGecko API request failed for {$coinId} to {$currency}. Status: " . $response->status(), $response->json() ?? []);
                    return null; // Return null on failure
                }
            } catch (\Exception $e) {
                Log::error("Exception during CoinGecko API call: " . $e->getMessage());
                return null; // Return null on exception
            }
        });
    }
}
