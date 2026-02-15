<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExpertTrader;
use App\Models\CopySubscription;
use App\Models\CopyTrade;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CopyTradingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get active expert traders
        $expertTraders = ExpertTrader::active()
            ->with(['expertTrades' => function($query) {
                $query->recent(30)->orderBy('created_at', 'desc');
            }])
            ->orderByDesc('roi_percentage')
            ->orderByDesc('win_rate')
            ->limit(20)
            ->get();

        // Get user's active subscriptions
        $activeSubscriptions = CopySubscription::forUser($user->id)
            ->active()
            ->with(['expertTrader', 'copyTrades.tradingAsset'])
            ->get();

        // Calculate statistics
        $activeExperts = ExpertTrader::active()->recentlyActive()->count();
        $totalCopiedAmount = $activeSubscriptions->sum('amount');
        $totalPnl = $activeSubscriptions->sum('total_pnl');
        
        // Calculate overall win rate
        $allCopyTrades = CopyTrade::forUser($user->id)->get();
        $overallWinRate = $allCopyTrades->count() > 0 
            ? ($allCopyTrades->where('pnl', '>', 0)->count() / $allCopyTrades->count()) * 100 
            : 0;

        return view('user.copy.index', compact(
            'expertTraders',
            'activeSubscriptions', 
            'activeExperts',
            'totalCopiedAmount',
            'totalPnl',
            'overallWinRate'
        ));
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'expert_trader_id' => 'required|exists:expert_traders,id',
            'amount' => 'required|numeric|min:1',
            'copy_percentage' => 'required|numeric|min:10|max:100',
            'duration_days' => 'required|integer|in:1,2,3,4,5,6,7,14,30,60,90'
        ]);

        $expertTrader = ExpertTrader::findOrFail($request->expert_trader_id);
        $user = Auth::user();

        // Validate amount limits
        if ($request->amount < $expertTrader->min_copy_amount || $request->amount > $expertTrader->max_copy_amount) {
            return back()->with('error', 'Amount must be between $' . number_format($expertTrader->min_copy_amount) . ' and $' . number_format($expertTrader->max_copy_amount));
        }

        // Check if user has sufficient balance
        if ($user->account_bal < $request->amount) {
            return back()->with('error', 'Insufficient balance. Required: $' . number_format($request->amount));
        }

        // Check if user already has an active subscription to this expert
        $existingSubscription = CopySubscription::where('user_id', $user->id)
            ->where('expert_trader_id', $expertTrader->id)
            ->where('status', 'active')
            ->first();

        if ($existingSubscription) {
            return back()->with('error', 'You already have an active subscription to this expert trader.');
        }

        DB::transaction(function () use ($request, $user, $expertTrader) {
            // Deduct from user balance
            $user->account_bal -= $request->amount;
            $user->save();

            // Create copy subscription
            CopySubscription::create([
                'user_id' => $user->id,
                'expert_trader_id' => $expertTrader->id,
                'amount' => $request->amount,
                'copy_percentage' => $request->copy_percentage,
                'status' => 'active',
                'started_at' => Carbon::now(),
                'expires_at' => Carbon::now()->addDays($request->duration_days),
                'auto_renew' => false,
                'max_risk_per_trade' => 10, // Default 10%
                'stop_loss_percentage' => 20 // Default 20%
            ]);

            // Update expert's follower count
            $expertTrader->increment('total_followers');
        });

        return back()->with('success', 'Successfully started copying ' . $expertTrader->name . '! Your copy trading session is now active.');
    }

    public function details($id)
    {
        $subscription = CopySubscription::where('id', $id)
            ->where('user_id', Auth::id())
            ->with(['expertTrader'])
            ->firstOrFail();

        // Get paginated copy trades
        $copyTrades = $subscription->copyTrades()
            ->with('tradingAsset')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Calculate additional metrics
        $recentTrades = $subscription->copyTrades()->orderBy('created_at', 'desc')->take(10)->get();
        $totalProfit = $subscription->copyTrades()->where('pnl', '>', 0)->sum('pnl');
        $totalLoss = abs($subscription->copyTrades()->where('pnl', '<', 0)->sum('pnl'));

        return view('user.copy.details', compact(
            'subscription',
            'copyTrades',
            'recentTrades',
            'totalProfit',
            'totalLoss'
        ));
    }

    public function expert($id)
    {
        $expertTrader = ExpertTrader::with([
            'expertTrades' => function($query) {
                $query->orderBy('created_at', 'desc')->limit(50);
            }
        ])->findOrFail($id);

        // Get performance history for charts
        $performanceHistory = $expertTrader->performanceHistory()
            ->recent(30)
            ->orderBy('date')
            ->get();

        // Calculate statistics
        $recentTrades = $expertTrader->expertTrades()->recent(30);
        $profitTrades = (clone $recentTrades)->where('type', 'profit');
        $lossTrades = (clone $recentTrades)->where('type', 'loss');
        
        $monthlyStats = [
            'total_trades' => $recentTrades->count(),
            'winning_trades' => $profitTrades->count(),
            'total_pnl' => $profitTrades->sum('amount') - $lossTrades->sum('amount'),
            'avg_trade_size' => $recentTrades->avg('amount'),
            'best_trade' => $profitTrades->max('amount') ?? 0,
            'worst_trade' => $lossTrades->max('amount') ?? 0
        ];

        // Check if user already follows this expert
        $userSubscription = CopySubscription::where('user_id', Auth::id())
            ->where('expert_trader_id', $id)
            ->where('status', 'active')
            ->first();

        return view('user.copy.expert', compact(
            'expertTrader',
            'performanceHistory',
            'monthlyStats',
            'userSubscription'
        ));
    }

    public function history()
    {
        $user = Auth::user();
        
        $subscriptions = CopySubscription::forUser($user->id)
            ->with(['expertTrader', 'copyTrades'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Calculate overall statistics
        $stats = [
            'total_subscriptions' => CopySubscription::forUser($user->id)->count(),
            'active_subscriptions' => CopySubscription::forUser($user->id)->active()->count(),
            'total_invested' => CopySubscription::forUser($user->id)->sum('amount'),
            'total_pnl' => CopyTrade::forUser($user->id)->sum('pnl'),
            'total_trades' => CopyTrade::forUser($user->id)->count(),
            'winning_trades' => CopyTrade::forUser($user->id)->where('pnl', '>', 0)->count()
        ];

        $stats['win_rate'] = $stats['total_trades'] > 0 
            ? ($stats['winning_trades'] / $stats['total_trades']) * 100 
            : 0;

        return view('user.copy.history', compact('subscriptions', 'stats'));
    }

    public function pause($id)
    {
        $subscription = CopySubscription::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->firstOrFail();

        $subscription->update(['status' => 'paused']);

        return back()->with('success', 'Copy trading paused. You can resume it anytime.');
    }

    public function resume($id)
    {
        $subscription = CopySubscription::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('status', 'paused')
            ->firstOrFail();

        // Check if subscription hasn't expired
        if ($subscription->expires_at <= Carbon::now()) {
            return back()->with('error', 'Cannot resume expired subscription. Please create a new one.');
        }

        $subscription->update(['status' => 'active']);

        return back()->with('success', 'Copy trading resumed successfully.');
    }

    public function cancel($id)
    {
        $subscription = CopySubscription::where('id', $id)
            ->where('user_id', Auth::id())
            ->whereIn('status', ['active', 'paused'])
            ->firstOrFail();

        DB::transaction(function () use ($subscription) {
            $user = Auth::user();
            
            // Calculate refund (50% of remaining time)
            $totalDays = $subscription->started_at->diffInDays($subscription->expires_at);
            $remainingDays = Carbon::now()->diffInDays($subscription->expires_at);
            
            if ($remainingDays > 0 && $totalDays > 0) {
                $refundPercentage = ($remainingDays / $totalDays) * 0.5; // 50% of remaining time
                $refundAmount = $subscription->amount * $refundPercentage;
                
                $user->account_bal += $refundAmount;
                $user->save();
            }

            // Cancel subscription
            $subscription->update(['status' => 'cancelled']);

            // Decrement expert's follower count
            $subscription->expertTrader->decrement('total_followers');
        });

        return back()->with('success', 'Copy trading cancelled. Refund has been processed to your account.');
    }

    public function analytics()
    {
        $user = Auth::user();
        
        // Get all copy trades for analytics
        $copyTrades = CopyTrade::forUser($user->id)
            ->with(['tradingAsset', 'copySubscription.expertTrader'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Performance by expert
        $expertPerformance = $copyTrades
            ->groupBy(function($trade) {
                return $trade->copySubscription->expertTrader->name;
            })
            ->map(function($trades) {
                return [
                    'total_trades' => $trades->count(),
                    'total_pnl' => $trades->sum('pnl'),
                    'win_rate' => $trades->count() > 0 ? ($trades->where('pnl', '>', 0)->count() / $trades->count()) * 100 : 0,
                    'avg_pnl' => $trades->avg('pnl')
                ];
            });

        // Performance by asset
        $assetPerformance = $copyTrades
            ->groupBy(function($trade) {
                return $trade->tradingAsset->symbol ?? 'Unknown';
            })
            ->map(function($trades) {
                return [
                    'total_trades' => $trades->count(),
                    'total_pnl' => $trades->sum('pnl'),
                    'win_rate' => $trades->count() > 0 ? ($trades->where('pnl', '>', 0)->count() / $trades->count()) * 100 : 0
                ];
            });

        // Monthly performance
        $monthlyPerformance = $copyTrades
            ->groupBy(function($trade) {
                return $trade->created_at->format('Y-m');
            })
            ->map(function($trades) {
                return [
                    'total_trades' => $trades->count(),
                    'total_pnl' => $trades->sum('pnl'),
                    'profit_trades' => $trades->where('pnl', '>', 0)->count(),
                    'loss_trades' => $trades->where('pnl', '<', 0)->count()
                ];
            })
            ->sortKeys();

        return view('user.copy.analytics', compact(
            'expertPerformance',
            'assetPerformance',
            'monthlyPerformance',
            'copyTrades'
        ));
    }

    private function getPerformanceChartData($subscription)
    {
        $chartData = [];
        $cumulativePnl = 0;
        
        // Get trades grouped by date
        $tradesByDate = $subscription->copyTrades
            ->groupBy(function($trade) {
                return $trade->created_at->format('Y-m-d');
            });

        // Generate chart data for last 30 days
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dayTrades = $tradesByDate->get($date, collect());
            $dayPnl = $dayTrades->sum('pnl');
            $cumulativePnl += $dayPnl;
            
            $chartData[] = [
                'date' => $date,
                'daily_pnl' => round($dayPnl, 2),
                'cumulative_pnl' => round($cumulativePnl, 2),
                'trades_count' => $dayTrades->count()
            ];
        }
        
        return $chartData;
    }
}