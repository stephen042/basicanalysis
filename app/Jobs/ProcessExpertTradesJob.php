<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\ExpertTrader;
use App\Models\ExpertTrade;
use App\Models\CopySubscription;
use App\Models\CopyTrade;
use App\Models\TradingAsset;
use App\Models\ExpertPerformanceHistory;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProcessExpertTradesJob implements ShouldQueue
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
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::info('ProcessExpertTradesJob started at ' . now());
        
        // Get all active expert traders
        $activeExperts = ExpertTrader::active()
            ->with(['copySubscriptions' => function($query) {
                $query->active()->with('user');
            }])
            ->get();

        \Log::info('Found ' . $activeExperts->count() . ' active expert traders');

        foreach ($activeExperts as $expert) {
            $this->processExpertTrader($expert);
        }

        // Update performance history
        $this->updatePerformanceHistory();
        
        \Log::info('ProcessExpertTradesJob completed at ' . now());
    }

    /**
     * Process individual expert trader - generate trading activity
     */
    private function processExpertTrader(ExpertTrader $expert)
    {
        try {
            // Check if expert should generate trading activity (random intervals between 8 to 15 minutes)
            $lastActivity = $expert->expertTrades()->latest()->first();
            
            if ($lastActivity) {
                $minutesSinceLastActivity = $lastActivity->created_at->diffInMinutes(Carbon::now());
                $minWaitTime = 8; // Minimum 8 minutes between trades
                $maxWaitTime = 15; // Maximum 15 minutes between trades
                $randomWaitTime = rand($minWaitTime, $maxWaitTime);
                
                if ($minutesSinceLastActivity < $randomWaitTime) {
                    return; // Not time for next trade yet
                }
            }

            \Log::info("Generating trading activity for expert {$expert->id} ({$expert->name})");

            // Expert trade type is just for display, actual user outcomes are determined individually
            $expertWinRate = $expert->win_rate ?? 75.0;
            $isExpertProfit = rand(1, 100) <= $expertWinRate;
            $type = $isExpertProfit ? 'profit' : 'loss';
            
            // Calculate trade amount based on expert's portfolio value
            $baseAmount = $expert->portfolio_value * 0.02; // 2% of portfolio per trade
            $variation = rand(50, 200) / 100; // 50% to 200% variation
            $tradeAmount = $baseAmount * $variation;
            
            // Ensure reasonable limits
            $tradeAmount = max($tradeAmount, 50.00); // Minimum $50
            $tradeAmount = min($tradeAmount, $expert->portfolio_value * 0.1); // Maximum 10% of portfolio
            $tradeAmount = round($tradeAmount, 2);

            // Get a random trading asset
            $tradingAsset = TradingAsset::where('is_active', true)->inRandomOrder()->first();
            
            // Use actual asset price from trading_assets table
            $assetPrice = $tradingAsset ? $tradingAsset->current_price : 100;
            $entryPrice = $assetPrice; // Use actual current price as entry price
            
            // Determine trade direction
            $tradeDirection = rand(1, 2) === 1 ? 'long' : 'short';
            
            // Calculate P&L based on actual asset price movements
            // Simulate realistic price movement (1% to 5% for profit, 0.5% to 3% for loss)
            $pnlPercentage = rand(100, 500) / 100; // 1% to 5% for profit or loss
            if ($type === 'profit') {
                $priceChange = ($pnlPercentage / 100);
                $exitPrice = $tradeDirection === 'long' 
                    ? $assetPrice * (1 + $priceChange)  // Long: price goes up
                    : $assetPrice * (1 - $priceChange); // Short: price goes down
                $pnl = $tradeAmount * $priceChange;
            } else {
                $lossPercentage = rand(50, 300) / 100; // 0.5% to 3% loss
                $priceChange = ($lossPercentage / 100);
                $exitPrice = $tradeDirection === 'long' 
                    ? $assetPrice * (1 - $priceChange)  // Long: price goes down (loss)
                    : $assetPrice * (1 + $priceChange); // Short: price goes up (loss)
                $pnl = -($tradeAmount * $priceChange);
            }
            
            $quantity = $tradingAsset ? $tradeAmount / $entryPrice : 0;

            // Create expert trade
            $expertTrade = ExpertTrade::create([
                'expert_trader_id' => $expert->id,
                'trading_asset_id' => $tradingAsset ? $tradingAsset->id : null,
                'amount' => $tradeAmount,
                'type' => $type,
                'asset_price' => $assetPrice,
                'quantity' => round($quantity, 8),
                'entry_price' => round($entryPrice, 8),
                'exit_price' => round($exitPrice, 8),
                'pnl' => round($pnl, 2),
                'trade_direction' => $tradeDirection,
                'status' => 'closed',
                'opened_at' => Carbon::now()->subMinutes(rand(5, 60)), // Random duration
                'closed_at' => Carbon::now()
            ]);

            // Update expert's statistics
            $this->updateExpertStatistics($expert);

            // Process copy trades for this expert trade
            $this->processCopyTrades($expert, $expertTrade);
            
            \Log::info("Created {$type} trade for expert {$expert->name}: ${$tradeAmount} (P&L: ${$pnl})");
            
        } catch (\Exception $e) {
            \Log::error('Error processing expert trader ' . $expert->id . ': ' . $e->getMessage());
        }
    }

    /**
     * Process copy trades for an expert trade
     */
    private function processCopyTrades(ExpertTrader $expert, ExpertTrade $expertTrade)
    {
        $activeSubscriptions = $expert->copySubscriptions()
            ->active()
            ->with('user')
            ->get();

        foreach ($activeSubscriptions as $subscription) {
            try {
                $user = $subscription->user;
                
                // Calculate copy trade amount
                $copyAmount = $subscription->calculateCopyAmount($expertTrade->amount);
                
                // Ensure user has sufficient balance (for demonstration, we'll assume they do)
                if ($copyAmount < 1) {
                    continue; // Skip if copy amount is too small
                }

                // Determine profit/loss for THIS USER based on their copy_trading_win_rate set by admin
                $userWinRate = $user->copy_trading_win_rate ?? 70.0; // Default 70% if not set
                $isUserProfit = rand(1, 100) <= $userWinRate;
                $userType = $isUserProfit ? 'profit' : 'loss';
                
                // Use admin-set profit/loss percentages (defaults: 5% profit, 3% loss)
                if ($isUserProfit) {
                    // Use admin's profit percentage (default 5%)
                    $pnlPercentage = $user->copy_trading_profit_percentage ?? 5.00;
                    $copyPnl = $copyAmount * ($pnlPercentage / 100);
                } else {
                    // Use admin's loss percentage (default 3%)
                    $pnlPercentage = $user->copy_trading_loss_percentage ?? 3.00;
                    $copyPnl = -($copyAmount * ($pnlPercentage / 100));
                }
                
                $copyRatio = $copyAmount / $expertTrade->amount;

                // Get asset details for notification
                $assetSymbol = $expertTrade->tradingAsset ? $expertTrade->tradingAsset->symbol : 'Unknown';
                $expertName = $expert->name;

                // Create copy trade with user-specific profit/loss
                $copyTrade = CopyTrade::create([
                    'copy_subscription_id' => $subscription->id,
                    'expert_trade_id' => $expertTrade->id,
                    'user_id' => $subscription->user_id,
                    'trading_asset_id' => $expertTrade->trading_asset_id,
                    'amount' => round($copyAmount, 2),
                    'type' => $userType, // User's specific profit/loss outcome
                    'asset_price' => $expertTrade->asset_price,
                    'quantity' => round($expertTrade->quantity * $copyRatio, 8),
                    'entry_price' => $expertTrade->entry_price,
                    'exit_price' => $expertTrade->exit_price,
                    'pnl' => round($copyPnl, 2),
                    'trade_direction' => $expertTrade->trade_direction,
                    'status' => $expertTrade->status,
                    'copy_ratio' => round($copyRatio, 4),
                    'opened_at' => $expertTrade->opened_at,
                    'closed_at' => $expertTrade->closed_at
                ]);

                // Update user balance with P&L (for completed trades)
                if ($expertTrade->status === 'closed') {
                    $user->account_bal += $copyPnl;
                    $user->roi += $copyPnl;
                    $user->save();
                    
                    // Send notification that trade was executed and closed with result
                    $profitPercentage = ($copyPnl / $copyAmount) * 100;
                    $this->notificationService->sendCopyTradeClosedNotification(
                        $user->id,
                        $expertName,
                        $assetSymbol,
                        $expertTrade->trade_direction,
                        $copyPnl,
                        $userType === 'profit',
                        $expertTrade->exit_price,
                        abs($profitPercentage)
                    );
                }
                
                \Log::info("Created copy trade for user {$subscription->user_id}: ${$copyAmount} ({$userType}) P&L: ${$copyPnl} (Win Rate: {$userWinRate}%)");
                
            } catch (\Exception $e) {
                \Log::error('Error creating copy trade for subscription ' . $subscription->id . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * Update expert trader statistics
     */
    private function updateExpertStatistics(ExpertTrader $expert)
    {
        $recentTrades = $expert->expertTrades()->recent(30)->get();
        
        if ($recentTrades->isNotEmpty()) {
            $totalTrades = $recentTrades->count();
            $profitTrades = $recentTrades->where('type', 'profit')->count();
            $winRate = ($profitTrades / $totalTrades) * 100;
            
            $totalProfit = $recentTrades->where('type', 'profit')->sum('pnl');
            $totalLoss = abs($recentTrades->where('type', 'loss')->sum('pnl'));
            $netPnl = $totalProfit - $totalLoss;
            
            // Update expert's current statistics
            $expert->update([
                'total_trades' => $expert->expertTrades()->count(),
                'win_rate' => round($winRate, 2),
                'total_pnl' => $expert->expertTrades()->sum(DB::raw('CASE WHEN type = "profit" THEN pnl ELSE -ABS(pnl) END')),
                'last_active_at' => Carbon::now()
            ]);
        }
    }

    /**
     * Update daily performance history for all experts
     */
    private function updatePerformanceHistory()
    {
        $today = Carbon::today();
        
        ExpertTrader::active()->chunk(50, function($experts) use ($today) {
            foreach ($experts as $expert) {
                try {
                    // Check if today's record already exists
                    $existingRecord = ExpertPerformanceHistory::where('expert_trader_id', $expert->id)
                        ->where('date', $today)
                        ->first();

                    if ($existingRecord) {
                        continue; // Skip if already processed today
                    }

                    // Calculate today's statistics
                    $todayTrades = $expert->expertTrades()
                        ->whereDate('created_at', $today)
                        ->get();
                    
                    $dailyPnl = $todayTrades->sum(function($trade) {
                        return $trade->type === 'profit' ? $trade->pnl : -abs($trade->pnl);
                    });
                    
                    $winningTrades = $todayTrades->where('type', 'profit')->count();
                    
                    // Create performance history record
                    ExpertPerformanceHistory::create([
                        'expert_trader_id' => $expert->id,
                        'date' => $today,
                        'portfolio_value' => $expert->current_portfolio_value,
                        'daily_pnl' => round($dailyPnl, 2),
                        'total_trades' => $todayTrades->count(),
                        'winning_trades' => $winningTrades,
                        'roi_percentage' => $expert->current_roi,
                        'drawdown_percentage' => 0, // Calculate if needed
                        'volume_traded' => $todayTrades->sum('amount'),
                        'followers_count' => $expert->getActiveSubscribersCount()
                    ]);
                    
                } catch (\Exception $e) {
                    \Log::error('Error updating performance history for expert ' . $expert->id . ': ' . $e->getMessage());
                }
            }
        });
    }
}