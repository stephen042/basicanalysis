<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExpertTrader;
use App\Models\ExpertTrade;
use App\Models\CopySubscription;
use App\Models\CopyTrade;
use App\Models\ExpertPerformanceHistory;
use App\Models\TradingAsset;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CopyTradingController extends Controller
{
    public function index()
    {
        $title = "Copy Trading Management";
        
        // Get statistics
        $stats = [
            'total_experts' => ExpertTrader::count(),
            'active_experts' => ExpertTrader::active()->count(),
            'total_subscriptions' => CopySubscription::count(),
            'active_subscriptions' => CopySubscription::active()->count(),
            'total_copy_trades' => CopyTrade::count(),
            'total_volume' => CopySubscription::sum('amount'),
            'total_pnl' => CopyTrade::sum('pnl'),
            'today_trades' => CopyTrade::whereDate('created_at', today())->count()
        ];

        // Recent activity
        $recentTrades = CopyTrade::with(['user', 'copySubscription.expertTrader', 'tradingAsset'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Top performing experts
        $topExperts = ExpertTrader::active()
            ->orderByDesc('roi_percentage')
            ->orderByDesc('win_rate')
            ->limit(5)
            ->get();

        return view('admin.copy-trading.index', compact('title', 'stats', 'recentTrades', 'topExperts'));
    }

    public function experts()
    {
        $title = "Expert Traders Management";
        
        $experts = ExpertTrader::with(['copySubscriptions', 'expertTrades'])
            ->withCount(['copySubscriptions as active_subscribers' => function($query) {
                $query->where('status', 'active');
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.copy-trading.experts', compact('title', 'experts'));
    }

    public function createExpert()
    {
        $title = "Create Expert Trader";
        return view('admin.copy-trading.create-expert', compact('title'));
    }

    public function storeExpert(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'specialization' => 'required|string',
            'experience_years' => 'required|integer|min:1',
            'roi_percentage' => 'required|numeric',
            'win_rate' => 'required|numeric|min:0|max:100',
            'portfolio_value' => 'required|numeric|min:1000',
            'total_followers' => 'required|integer|min:0',
            'total_pnl' => 'required|numeric',
            'min_copy_amount' => 'required|numeric|min:100',
            'max_copy_amount' => 'required|numeric|min:1000',
            'description' => 'nullable|string',
            'trading_strategy' => 'nullable|string',
            'avatar' => 'nullable|url|max:500'
        ]);

        ExpertTrader::create([
            'name' => $request->name,
            'avatar' => $request->avatar,
            'total_followers' => $request->total_followers,
            'roi_percentage' => $request->roi_percentage,
            'total_trades' => 0,
            'win_rate' => $request->win_rate,
            'total_pnl' => $request->total_pnl,
            'portfolio_value' => $request->portfolio_value,
            'experience_years' => $request->experience_years,
            'specialization' => $request->specialization,
            'status' => 'active',

            'min_copy_amount' => $request->min_copy_amount,
            'max_copy_amount' => $request->max_copy_amount,
            'description' => $request->description,
            'trading_strategy' => $request->trading_strategy,
            'last_active_at' => Carbon::now()
        ]);

        return redirect()->route('admin.copy-trading.experts')
            ->with('success', 'Expert trader created successfully!');
    }

    public function editExpert($id)
    {
        $title = "Edit Expert Trader";
        $expert = ExpertTrader::findOrFail($id);
        return view('admin.copy-trading.edit-expert', compact('title', 'expert'));
    }

    public function updateExpert(Request $request, $id)
    {
        $expert = ExpertTrader::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'specialization' => 'required|string',
            'experience_years' => 'required|integer|min:1',
            'roi_percentage' => 'required|numeric',
            'win_rate' => 'required|numeric|min:0|max:100',
            'portfolio_value' => 'required|numeric|min:1000',
            'total_followers' => 'required|integer|min:0',
            'total_pnl' => 'required|numeric',
            'min_copy_amount' => 'required|numeric|min:100',
            'max_copy_amount' => 'required|numeric|min:1000',
            'status' => 'required|in:active,inactive,suspended',
            'description' => 'nullable|string',
            'trading_strategy' => 'nullable|string',
            'avatar' => 'nullable|url|max:500'
        ]);

        $expert->update($request->all());

        return redirect()->route('admin.copy-trading.experts')
            ->with('success', 'Expert trader updated successfully!');
    }

    public function deleteExpert($id)
    {
        $expert = ExpertTrader::findOrFail($id);
        
        // Check if expert has active subscriptions
        $activeSubscriptions = $expert->copySubscriptions()->active()->count();
        if ($activeSubscriptions > 0) {
            return back()->with('error', 'Cannot delete expert with active subscriptions.');
        }

        $expert->delete();
        return back()->with('success', 'Expert trader deleted successfully!');
    }

    public function subscriptions()
    {
        $title = "Copy Subscriptions Management";
        
        $subscriptions = CopySubscription::with(['user', 'expertTrader', 'copyTrades'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.copy-trading.subscriptions', compact('title', 'subscriptions'));
    }

    public function tradingLogs()
    {
        $title = "Copy Trading Logs";
        
        $logs = CopyTrade::with(['user', 'copySubscription.expertTrader', 'tradingAsset'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('admin.copy-trading.logs', compact('title', 'logs'));
    }

    public function analytics()
    {
        $title = "Copy Trading Analytics";
        
        // Performance metrics
        $metrics = [
            'total_volume' => CopySubscription::sum('amount'),
            'total_pnl' => CopyTrade::sum('pnl'),
            'avg_roi' => CopySubscription::avg(DB::raw('(SELECT SUM(pnl) FROM copy_trades WHERE copy_subscription_id = copy_subscriptions.id) / amount * 100')),
            'success_rate' => CopyTrade::where('pnl', '>', 0)->count() / max(CopyTrade::count(), 1) * 100
        ];

        // Monthly performance
        $monthlyData = CopyTrade::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as trades_count'),
                DB::raw('SUM(pnl) as total_pnl'),
                DB::raw('SUM(amount) as total_volume')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Top performing experts
        $topExperts = ExpertTrader::select('expert_traders.*')
            ->selectRaw('(SELECT SUM(pnl) FROM copy_trades ct JOIN copy_subscriptions cs ON ct.copy_subscription_id = cs.id WHERE cs.expert_trader_id = expert_traders.id) as total_pnl')
            ->selectRaw('(SELECT COUNT(*) FROM copy_subscriptions WHERE expert_trader_id = expert_traders.id AND status = "active") as active_subscribers')
            ->orderByDesc('total_pnl')
            ->limit(10)
            ->get();

        return view('admin.copy-trading.analytics', compact('title', 'metrics', 'monthlyData', 'topExperts'));
    }

    public function simulateTrade(Request $request)
    {
        $request->validate([
            'expert_trader_id' => 'required|exists:expert_traders,id',
            'amount' => 'required|numeric|min:1',
            'type' => 'required|in:profit,loss',
            'asset_id' => 'nullable|exists:trading_assets,id'
        ]);

        $expert = ExpertTrader::findOrFail($request->expert_trader_id);
        $tradingAsset = $request->asset_id ? TradingAsset::find($request->asset_id) : null;

        // Create manual expert trade
        $expertTrade = ExpertTrade::create([
            'expert_trader_id' => $expert->id,
            'trading_asset_id' => $tradingAsset?->id,
            'amount' => $request->amount,
            'type' => $request->type,
            'asset_price' => $tradingAsset?->current_price ?? 100,
            'quantity' => $tradingAsset ? $request->amount / $tradingAsset->current_price : 0,
            'entry_price' => $tradingAsset?->current_price ?? 100,
            'exit_price' => $tradingAsset?->current_price ?? 100,
            'pnl' => $request->type === 'profit' ? $request->amount * 0.05 : -($request->amount * 0.03),
            'trade_direction' => 'long',
            'status' => 'closed',
            'opened_at' => Carbon::now()->subMinutes(30),
            'closed_at' => Carbon::now()
        ]);

        // Process copy trades
        $this->processCopyTradesForManualTrade($expert, $expertTrade);

        return back()->with('success', 'Manual trade simulated successfully!');
    }

    private function processCopyTradesForManualTrade($expert, $expertTrade)
    {
        $activeSubscriptions = $expert->copySubscriptions()->active()->with('user')->get();

        foreach ($activeSubscriptions as $subscription) {
            $copyAmount = ($expertTrade->amount * $subscription->copy_percentage) / 100;
            $copyPnl = ($expertTrade->pnl / $expertTrade->amount) * $copyAmount;

            CopyTrade::create([
                'copy_subscription_id' => $subscription->id,
                'expert_trade_id' => $expertTrade->id,
                'user_id' => $subscription->user_id,
                'trading_asset_id' => $expertTrade->trading_asset_id,
                'amount' => $copyAmount,
                'type' => $expertTrade->type,
                'asset_price' => $expertTrade->asset_price,
                'quantity' => $expertTrade->quantity * ($copyAmount / $expertTrade->amount),
                'entry_price' => $expertTrade->entry_price,
                'exit_price' => $expertTrade->exit_price,
                'pnl' => $copyPnl,
                'trade_direction' => $expertTrade->trade_direction,
                'status' => $expertTrade->status,
                'copy_ratio' => $copyAmount / $expertTrade->amount,
                'opened_at' => $expertTrade->opened_at,
                'closed_at' => $expertTrade->closed_at
            ]);

            // Update user balance
            $user = $subscription->user;
            $user->account_bal += $copyPnl;
            $user->save();
        }
    }
}