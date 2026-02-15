<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\PredictionTrade;
use App\Models\User;
use App\Models\TradeSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProcessTradingPayoutsJob implements ShouldQueue
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
            $this->processPendingPayouts();
            $this->processDelayedPayouts();
            $this->validateUserBalances();
            $this->generatePayoutReport();
            
            Log::info('Trading payouts processed successfully');
            
        } catch (\Exception $e) {
            Log::error('Error processing trading payouts: ' . $e->getMessage(), [
                'exception' => $e
            ]);
        }
    }

    /**
     * Process pending payouts from recent winning trades
     */
    private function processPendingPayouts(): void
    {
        // Get trades that won but payout hasn't been credited yet
        $pendingPayouts = PredictionTrade::where('status', 'won')
            ->where('actual_payout', '>', 0)
            ->whereColumn('actual_payout', '!=', 'credited_amount')
            ->orWhere('credited_amount', null)
            ->get();

        $totalProcessed = 0;
        $totalAmount = 0;

        foreach ($pendingPayouts as $trade) {
            try {
                $user = User::find($trade->user_id);
                if (!$user) {
                    Log::warning('User not found for payout', [
                        'trade_id' => $trade->id,
                        'user_id' => $trade->user_id
                    ]);
                    continue;
                }

                $payoutAmount = (float) $trade->actual_payout;
                $alreadyCredited = (float) ($trade->credited_amount ?? 0);
                $amountToCredit = $payoutAmount - $alreadyCredited;

                if ($amountToCredit <= 0) {
                    continue; // Already fully credited
                }

                // Credit the user's trading balance
                DB::transaction(function() use ($user, $trade, $amountToCredit, $payoutAmount) {
                    $user->increment('trading_balance', $amountToCredit);
                    
                    $trade->update([
                        'credited_amount' => $payoutAmount,
                        'credited_at' => now()
                    ]);
                });

                $totalProcessed++;
                $totalAmount += $amountToCredit;

                Log::info('Payout credited successfully', [
                    'trade_id' => $trade->id,
                    'user_id' => $user->id,
                    'amount' => $amountToCredit,
                    'new_balance' => $user->fresh()->trading_balance
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to process individual payout', [
                    'trade_id' => $trade->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        if ($totalProcessed > 0) {
            Log::info('Payouts processed', [
                'total_trades' => $totalProcessed,
                'total_amount' => $totalAmount
            ]);
        }
    }

    /**
     * Process delayed payouts (for manual review trades)
     */
    private function processDelayedPayouts(): void
    {
        // Get trades marked for delayed payout (admin review)
        $delayedPayouts = PredictionTrade::where('status', 'won')
            ->where('payout_status', 'delayed')
            ->where('created_at', '>=', Carbon::now()->subHours(24)) // Only last 24 hours
            ->get();

        foreach ($delayedPayouts as $trade) {
            try {
                // Check if delay period has passed (default 1 hour)
                $delayHours = TradeSetting::getSetting('payout_delay_hours', 1);
                $delayUntil = $trade->updated_at->addHours($delayHours);

                if (now()->gte($delayUntil)) {
                    // Auto-approve if no admin intervention
                    $this->approvePayout($trade);
                }

            } catch (\Exception $e) {
                Log::error('Failed to process delayed payout', [
                    'trade_id' => $trade->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Approve a delayed payout
     */
    private function approvePayout($trade): void
    {
        try {
            $user = User::find($trade->user_id);
            if (!$user) return;

            $payoutAmount = (float) $trade->actual_payout;

            DB::transaction(function() use ($user, $trade, $payoutAmount) {
                $user->increment('trading_balance', $payoutAmount);
                
                $trade->update([
                    'payout_status' => 'completed',
                    'credited_amount' => $payoutAmount,
                    'credited_at' => now()
                ]);
            });

            Log::info('Delayed payout approved and credited', [
                'trade_id' => $trade->id,
                'user_id' => $user->id,
                'amount' => $payoutAmount
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to approve delayed payout', [
                'trade_id' => $trade->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Validate user balances against trade records
     */
    private function validateUserBalances(): void
    {
        try {
            // Check for users with potentially incorrect balances
            $users = User::whereHas('predictionTrades', function($query) {
                $query->where('created_at', '>=', Carbon::now()->subDays(7));
            })->get();

            foreach ($users as $user) {
                $this->validateSingleUserBalance($user);
            }

        } catch (\Exception $e) {
            Log::error('Error validating user balances', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Validate a single user's balance
     */
    private function validateSingleUserBalance($user): void
    {
        try {
            // Calculate expected balance from trades
            $completedTrades = $user->predictionTrades()
                ->whereIn('status', ['won', 'lost'])
                ->get();

            $totalWinnings = $completedTrades->where('status', 'won')
                ->sum('actual_payout');
            
            $totalLosses = $completedTrades->where('status', 'lost')
                ->sum('trade_amount');

            // Note: This is a simplified validation
            // In reality, you'd need to account for deposits, withdrawals, etc.
            $expectedNetFromTrades = $totalWinnings - $totalLosses;

            // Check for major discrepancies (> $10)
            $balanceDiscrepancy = abs($expectedNetFromTrades - $user->total_profit_loss);
            
            if ($balanceDiscrepancy > 10) {
                Log::warning('User balance discrepancy detected', [
                    'user_id' => $user->id,
                    'expected_net' => $expectedNetFromTrades,
                    'recorded_profit_loss' => $user->total_profit_loss,
                    'discrepancy' => $balanceDiscrepancy
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to validate user balance', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generate payout summary report
     */
    private function generatePayoutReport(): void
    {
        try {
            $startTime = Carbon::now()->subDay();
            
            $stats = PredictionTrade::where('credited_at', '>=', $startTime)
                ->select([
                    DB::raw('COUNT(*) as total_payouts'),
                    DB::raw('SUM(credited_amount) as total_amount'),
                    DB::raw('AVG(credited_amount) as avg_payout'),
                    DB::raw('MAX(credited_amount) as max_payout'),
                    DB::raw('COUNT(DISTINCT user_id) as unique_users')
                ])
                ->first();

            // Store daily payout report
            TradeSetting::updateOrCreate(
                ['setting_key' => 'daily_payout_report_' . now()->format('Y-m-d')],
                [
                    'setting_value' => json_encode([
                        'date' => now()->format('Y-m-d'),
                        'total_payouts' => $stats->total_payouts ?? 0,
                        'total_amount' => $stats->total_amount ?? 0,
                        'avg_payout' => round($stats->avg_payout ?? 0, 2),
                        'max_payout' => $stats->max_payout ?? 0,
                        'unique_users' => $stats->unique_users ?? 0,
                        'generated_at' => now()->toISOString()
                    ]),
                    'description' => 'Daily payout summary report'
                ]
            );

        } catch (\Exception $e) {
            Log::error('Failed to generate payout report', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
