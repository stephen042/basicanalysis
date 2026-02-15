<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TradingBot;
use App\Models\UserTradingBot;
use App\Models\TradingLog;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class TradingBotController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    public function index()
    {
        $title = "Trading Bots";
        $tradingBots = TradingBot::where('status', 'active')
            ->with('tradingAssets')
            ->get();
        $userTradingBots = UserTradingBot::where('user_id', Auth::id())
            ->with(['tradingBot.tradingAssets', 'tradingLogs.tradingAsset'])
            ->latest()
            ->get();

        return view('user.trading-bots.index', compact('title', 'tradingBots', 'userTradingBots'));
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'trading_bot_id' => 'required|exists:trading_bots,id',
            'amount' => 'required|numeric|min:1',
        ]);

        $tradingBot = TradingBot::findOrFail($request->trading_bot_id);
        $user = Auth::user();

        // Check if amount is within bot limits
        if ($request->amount < $tradingBot->min_amount || $request->amount > $tradingBot->max_amount) {
            return back()->with('error', 'Amount must be between $' . number_format($tradingBot->min_amount) . ' and $' . number_format($tradingBot->max_amount));
        }

        // Check if user has sufficient balance
        if ($user->account_bal < $request->amount) {
            return back()->with('error', 'Insufficient balance. Please fund your account.');
        }

        // Deduct from user balance
        $user->account_bal -= $request->amount;
        $user->save();

        // Create user trading bot subscription
        $userTradingBot = UserTradingBot::create([
            'user_id' => $user->id,
            'trading_bot_id' => $tradingBot->id,
            'amount' => $request->amount,
            'status' => 'active',
            'expires_at' => Carbon::now()->addHours($tradingBot->duration),
        ]);

        // Send notification for bot start
        $duration = $tradingBot->duration . ' hour' . ($tradingBot->duration > 1 ? 's' : '');
        $this->notificationService->sendBotStartedNotification(
            $user->id,
            $tradingBot->name,
            $request->amount,
            $duration
        );

        return back()->with('success', 'Successfully subscribed to ' . $tradingBot->name . ' trading bot!');
    }

    public function cancel($id)
    {
        $userTradingBot = UserTradingBot::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('status', 'active')
            ->firstOrFail();

        $user = Auth::user();

        // Return 50% of investment if cancelled early
        $refundAmount = $userTradingBot->amount * 0.5;
        $user->account_bal += $refundAmount;
        $user->save();

        $userTradingBot->update(['status' => 'cancelled']);

        return back()->with('success', 'Trading bot cancelled. 50% refund of $' . number_format($refundAmount) . ' has been added to your account.');
    }

    public function history()
    {
        $title = "Trading History";
        $userTradingBots = UserTradingBot::where('user_id', Auth::id())
            ->with(['tradingBot', 'tradingLogs'])
            ->latest()
            ->paginate(20);

        return view('user.trading-bots.history', compact('title', 'userTradingBots'));
    }

    public function details($id, Request $request)
    {
        $userBot = UserTradingBot::where('id', $id)
            ->where('user_id', Auth::id())
            ->with(['tradingBot', 'user'])
            ->firstOrFail();

        // Get paginated trading logs
        $tradingLogs = TradingLog::where('user_trading_bot_id', $userBot->id)
            ->with('tradingAsset')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Calculate statistics from all logs (not just paginated ones)
        $allLogs = TradingLog::where('user_trading_bot_id', $userBot->id)->get();
        $profitTrades = $allLogs->where('type', 'profit');
        $lossTrades = $allLogs->where('type', 'loss');
        $totalTrades = $profitTrades->count() + $lossTrades->count();
        $winRate = $totalTrades > 0 ? ($profitTrades->count() / $totalTrades) * 100 : 0;
        $avgProfit = $profitTrades->count() > 0 ? $profitTrades->avg('amount') : 0;
        $avgLoss = $lossTrades->count() > 0 ? $lossTrades->avg('amount') : 0;

        $totalProfit = $profitTrades->sum('amount');
        $totalLoss = $lossTrades->sum('amount');
        $netProfit = $totalProfit - $totalLoss;

        return view('user.trading-bots.details', compact(
            'userBot',
            'tradingLogs',
            'netProfit',
            'totalProfit',
            'totalLoss',
            'totalTrades',
            'profitTrades',
            'lossTrades',
            'winRate',
            'avgProfit',
            'avgLoss'
        ));
    }

    public function myBotsInvestment()
    {
        $title = "My Bots Investment";
        $user = Auth::user();

        // Get user's trading bot investments with detailed statistics
        $userTradingBots = UserTradingBot::where('user_id', $user->id)
            ->with(['tradingBot.tradingAssets', 'tradingLogs.tradingAsset'])
            ->latest()
            ->get();

        // Calculate overall statistics
        $totalInvestment = $userTradingBots->sum('amount');
        $totalCurrentValue = 0;
        $totalProfit = 0;
        $totalLoss = 0;
        $activeBots = 0;
        $completedBots = 0;

        foreach ($userTradingBots as $userBot) {
            $botProfit = $userBot->tradingLogs->where('type', 'profit')->sum('amount');
            $botLoss = $userBot->tradingLogs->where('type', 'loss')->sum('amount');

            $totalProfit += $botProfit;
            $totalLoss += $botLoss;
            $totalCurrentValue += $userBot->amount + ($botProfit - $botLoss);

            if ($userBot->status === 'active') {
                $activeBots++;
            } elseif ($userBot->status === 'completed') {
                $completedBots++;
            }
        }

        $netProfit = $totalProfit - $totalLoss;
        $profitPercentage = $totalInvestment > 0 ? (($netProfit / $totalInvestment) * 100) : 0;

        // Recent trading activities (last 10 logs)
        $recentActivities = TradingLog::whereHas('userTradingBot', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['userTradingBot.tradingBot', 'tradingAsset'])
            ->latest()
            ->limit(10)
            ->get();

        // Monthly performance data for chart
        $monthlyData = TradingLog::whereHas('userTradingBot', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->selectRaw('MONTH(created_at) as month, YEAR(created_at) as year')
            ->selectRaw('SUM(CASE WHEN type = "profit" THEN amount ELSE 0 END) as monthly_profit')
            ->selectRaw('SUM(CASE WHEN type = "loss" THEN amount ELSE 0 END) as monthly_loss')
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        // Top performing bots
        $topPerformingBots = $userTradingBots->map(function($userBot) {
            $profit = $userBot->tradingLogs->where('type', 'profit')->sum('amount');
            $loss = $userBot->tradingLogs->where('type', 'loss')->sum('amount');
            $netProfit = $profit - $loss;
            $profitPercentage = $userBot->amount > 0 ? (($netProfit / $userBot->amount) * 100) : 0;

            $userBot->net_profit = $netProfit;
            $userBot->profit_percentage = $profitPercentage;
            $userBot->total_trades = $userBot->tradingLogs->count();

            return $userBot;
        })->sortByDesc('profit_percentage')->take(5);

        $stats = [
            'total_investment' => $totalInvestment,
            'total_current_value' => $totalCurrentValue,
            'net_profit' => $netProfit,
            'profit_percentage' => $profitPercentage,
            'active_bots' => $activeBots,
            'completed_bots' => $completedBots,
            'total_bots' => $userTradingBots->count(),
            'total_trades' => TradingLog::whereHas('userTradingBot', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })->count(),
        ];

        return view('user.my-bots-investment', compact(
            'title',
            'userTradingBots',
            'stats',
            'recentActivities',
            'monthlyData',
            'topPerformingBots'
        ));
    }
}
