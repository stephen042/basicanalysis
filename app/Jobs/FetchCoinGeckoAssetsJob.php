<?php

namespace App\Jobs;

use App\Models\TradingAsset;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FetchCoinGeckoAssetsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $coinIds;
    protected $updateExisting;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($coinIds = null, $updateExisting = true)
    {
        $this->coinIds = $coinIds;
        $this->updateExisting = $updateExisting;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            Log::info('Starting CoinGecko assets fetch job');

            // Get top cryptocurrencies by market cap if no specific coins provided
            $coinIds = $this->coinIds ?? $this->getTopCoinIds();

            // Fetch market data from CoinGecko
            $marketData = $this->fetchMarketData($coinIds);
            
            if (!$marketData) {
                Log::error('Failed to fetch market data from CoinGecko');
                return;
            }

            // Process each coin
            foreach ($marketData as $coinData) {
                $this->processCoinData($coinData);
            }

            Log::info('CoinGecko assets fetch job completed successfully', [
                'coins_processed' => count($marketData)
            ]);

        } catch (\Exception $e) {
            Log::error('Error in CoinGecko assets fetch job', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Get top cryptocurrency IDs from CoinGecko
     */
    private function getTopCoinIds($limit = 50)
    {
        try {
            $response = Http::timeout(30)->get('https://api.coingecko.com/api/v3/coins/markets', [
                'vs_currency' => 'usd',
                'order' => 'market_cap_desc',
                'per_page' => $limit,
                'page' => 1,
                'sparkline' => false,
                'price_change_percentage' => '24h'
            ]);

            if ($response->successful()) {
                return collect($response->json())->pluck('id')->toArray();
            }

            Log::warning('Failed to fetch top coins list from CoinGecko', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            // Fallback to popular coins
            return [
                'bitcoin', 'ethereum', 'tether', 'binancecoin', 'solana',
                'usd-coin', 'cardano', 'dogecoin', 'avalanche-2', 'chainlink',
                'polygon', 'litecoin', 'near', 'uniswap', 'internet-computer'
            ];

        } catch (\Exception $e) {
            Log::error('Error fetching top coin IDs', ['error' => $e->getMessage()]);
            
            // Return popular coins as fallback
            return [
                'bitcoin', 'ethereum', 'tether', 'binancecoin', 'solana',
                'usd-coin', 'cardano', 'dogecoin', 'avalanche-2', 'chainlink'
            ];
        }
    }

    /**
     * Fetch market data for specific coins
     */
    private function fetchMarketData(array $coinIds)
    {
        try {
            $coinIdsString = implode(',', $coinIds);
            
            $response = Http::timeout(30)->get('https://api.coingecko.com/api/v3/coins/markets', [
                'ids' => $coinIdsString,
                'vs_currency' => 'usd',
                'order' => 'market_cap_desc',
                'per_page' => 250,
                'page' => 1,
                'sparkline' => false,
                'price_change_percentage' => '24h'
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning('Failed to fetch market data from CoinGecko', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('Error fetching market data from CoinGecko', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Process individual coin data and save to database
     */
    private function processCoinData(array $coinData)
    {
        try {
            // Check if asset already exists
            $asset = TradingAsset::where('coingecko_id', $coinData['id'])
                ->orWhere('symbol', strtoupper($coinData['symbol']))
                ->first();

            $assetData = [
                'name' => $coinData['name'],
                'symbol' => strtoupper($coinData['symbol']),
                'type' => 'crypto',
                'coingecko_id' => $coinData['id'],
                'icon_url' => $coinData['image'] ?? null,
                'current_price' => $coinData['current_price'] ?? 0,
                'change_24h' => $coinData['price_change_percentage_24h'] ?? 0,
                'market_cap' => $coinData['market_cap'] ?? null,
                'market_cap_rank' => $coinData['market_cap_rank'] ?? null,
                'total_volume' => $coinData['total_volume'] ?? null,
                'high_24h' => $coinData['high_24h'] ?? null,
                'low_24h' => $coinData['low_24h'] ?? null,
                'is_active' => true,
                'last_updated' => Carbon::now(),
            ];

            if ($asset && $this->updateExisting) {
                // Update existing asset
                $asset->update($assetData);
                Log::info('Updated existing trading asset', [
                    'symbol' => $assetData['symbol'],
                    'name' => $assetData['name'],
                    'price' => $assetData['current_price']
                ]);
            } elseif (!$asset) {
                // Create new asset
                TradingAsset::create($assetData);
                Log::info('Created new trading asset', [
                    'symbol' => $assetData['symbol'],
                    'name' => $assetData['name'],
                    'price' => $assetData['current_price']
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error processing coin data', [
                'coin_id' => $coinData['id'] ?? 'unknown',
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle job failure
     */
    public function failed(\Throwable $exception)
    {
        Log::error('CoinGecko assets fetch job failed', [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
