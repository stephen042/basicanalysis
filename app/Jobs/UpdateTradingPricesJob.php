<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\PredictionTrade;
use App\Models\TradingAsset;
use App\Services\PriceManipulationService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateTradingPricesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Get all active trades
            $activeTrades = PredictionTrade::where('status', 'active')->get();
            
            if ($activeTrades->isEmpty()) {
                return; // No active trades to update
            }

            // Get unique asset IDs from active trades
            $assetIds = $activeTrades->pluck('trading_asset_id')->unique();
            
            // Get trading assets with their symbols
            $tradingAssets = TradingAsset::whereIn('id', $assetIds)->get();
            
            if ($tradingAssets->isEmpty()) {
                return;
            }

            // Prepare symbols for API call
            $symbols = $tradingAssets->pluck('symbol')->map(function($symbol) {
                return strtolower($symbol);
            })->implode(',');

            // Fetch latest prices from CoinGecko
            $response = Http::timeout(10)->get('https://api.coingecko.com/api/v3/simple/price', [
                'ids' => $symbols,
                'vs_currencies' => 'usd',
                'include_24hr_change' => 'true'
            ]);

            if (!$response->successful()) {
                Log::warning('Failed to fetch prices from CoinGecko', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return;
            }

            $priceData = $response->json();
            
            // Initialize price manipulation service
            $priceManipulationService = new PriceManipulationService();

            // Update prices for each asset
            foreach ($tradingAssets as $asset) {
                $symbol = strtolower($asset->symbol);
                
                if (!isset($priceData[$symbol]['usd'])) {
                    continue;
                }

                $realPrice = $priceData[$symbol]['usd'];
                
                // Apply price manipulation
                $manipulatedPrice = $priceManipulationService->getManipulatedPrice($realPrice, $asset->id);
                
                // Update the asset's current price
                $asset->update([
                    'price_usd' => $manipulatedPrice,
                    'updated_at' => now()
                ]);

                // Update all active trades for this asset
                $assetTrades = $activeTrades->where('trading_asset_id', $asset->id);
                
                foreach ($assetTrades as $trade) {
                    $trade->update([
                        'current_price' => $manipulatedPrice,
                        'updated_at' => now()
                    ]);

                    // Check if trade should be auto-resolved (for flexible trades)
                    $this->checkAutoResolution($trade, $manipulatedPrice);
                }
            }

            // Note: WebSocket broadcasting can be implemented later
            // broadcast(new \App\Events\PricesUpdated($priceData));

            Log::info('Trading prices updated successfully', [
                'assets_updated' => $tradingAssets->count(),
                'active_trades' => $activeTrades->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating trading prices: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    /**
     * Check if a trade should be auto-resolved for profit taking
     */
    private function checkAutoResolution($trade, $currentPrice)
    {
        // Only for flexible trades
        if ($trade->trade_type !== 'flexible') {
            return;
        }

        $profitPercentage = 0;
        
        if ($trade->prediction === 'UP') {
            $profitPercentage = (($currentPrice - $trade->entry_price) / $trade->entry_price) * 100;
        } else {
            $profitPercentage = (($trade->entry_price - $currentPrice) / $trade->entry_price) * 100;
        }

        // Auto-close if profit reaches 80% (configurable)
        $autoCloseThreshold = 80;
        
        if ($profitPercentage >= $autoCloseThreshold) {
            // Will be implemented when ResolveExpiredTradesJob is created
            // dispatch(new ResolveTradeJob($trade->id, 'auto_profit_taking'));
        }
    }
}
