<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\CopySubscription;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessExpiredCopySubscriptionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $notificationService;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->notificationService = app(NotificationService::class);
    }

    /**
     * Execute the job - Process expired copy trading subscriptions
     *
     * @return void
     */
    public function handle()
    {
        Log::info('ProcessExpiredCopySubscriptionsJob started at ' . now());
        
        // Get all expired active subscriptions
        $expiredSubscriptions = CopySubscription::where('status', 'active')
            ->where('expires_at', '<=', Carbon::now())
            ->with(['user', 'expertTrader'])
            ->get();

        Log::info('Found ' . $expiredSubscriptions->count() . ' expired copy subscriptions');

        foreach ($expiredSubscriptions as $subscription) {
            $this->completeSubscription($subscription);
        }
        
        Log::info('ProcessExpiredCopySubscriptionsJob completed at ' . now());
    }

    /**
     * Complete an expired subscription and return investment to user
     */
    private function completeSubscription(CopySubscription $subscription)
    {
        try {
            DB::transaction(function () use ($subscription) {
                $user = $subscription->user;
                
                // Calculate total P&L from all copy trades during subscription
                // Note: P&L was already added to user balance during trades via ProcessExpertTradesJob
                $totalPnl = $subscription->copyTrades->sum('pnl');
                
                // Return original investment amount only
                // (Profit/loss was already added to account_bal during each trade)
                $returnAmount = $subscription->amount;
                
                // Update user balance
                $user->account_bal += $returnAmount;
                $user->save();
                
                // Mark subscription as completed
                $subscription->update([
                    'status' => 'completed',
                    'completed_at' => Carbon::now()
                ]);
                
                // Decrement expert's follower count
                $subscription->expertTrader->decrement('total_followers');
                
                // Send completion notification
                $this->sendCompletionNotification($user, $subscription, $finalPnl, $returnAmount);
                
                Log::info("Completed copy subscription {$subscription->id} for user {$user->id}: Investment Returned: \${$subscription->amount}, Total P&L During Subscription: \${$pnl}");
            });
            
        } catch (\Exception $e) {
            Log::error('Error completing expired subscription ' . $subscription->id . ': ' . $e->getMessage());
        }
    }

    /**
     * Send completion notification to user
     */
    private function sendCompletionNotification($user, $subscription, $pnl, $totalReturned)
    {
        try {
            $expertName = $subscription->expertTrader->name;
            $duration = $subscription->started_at->diffInDays($subscription->expires_at);
            $isProfit = $pnl >= 0;
            
            $title = '🎉 Copy Trading Completed';
            
            if ($isProfit) {
                $message = "Your {$duration}-day copy trading subscription with {$expertName} has completed successfully! ";
                $message .= "Investment Returned: {$user->currency}" . number_format($subscription->amount, 2) . " | ";
                $message .= "Total Profit Earned: {$user->currency}" . number_format($pnl, 2) . " (already in your account)";
            } else {
                $message = "Your {$duration}-day copy trading subscription with {$expertName} has completed. ";
                $message .= "Investment Returned: {$user->currency}" . number_format($subscription->amount, 2) . " | ";
                $message .= "Total Loss: {$user->currency}" . number_format(abs($pnl), 2) . " (already deducted)";
            }
            
            $data = [
                'subscription_id' => $subscription->id,
                'expert_name' => $expertName,
                'investment_amount' => $subscription->amount,
                'total_pnl' => $pnl,
                'total_returned' => $totalReturned,
                'duration_days' => $duration,
                'total_trades' => $subscription->total_trades,
                'winning_trades' => $subscription->winning_trades,
                'win_rate' => $subscription->win_rate,
                'completion_time' => Carbon::now()->toISOString(),
            ];
            
            $priority = $isProfit ? 'high' : 'normal';
            $icon = $isProfit ? 'fas fa-trophy text-success' : 'fas fa-handshake text-primary';
            
            $this->notificationService->create(
                $user->id,
                'copy_subscription_completed',
                $title,
                $message,
                $data,
                $priority,
                route('copy-trading.history'),
                $icon
            );
            
        } catch (\Exception $e) {
            Log::error('Error sending completion notification for subscription ' . $subscription->id . ': ' . $e->getMessage());
        }
    }
}
