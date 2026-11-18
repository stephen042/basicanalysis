<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExpertTrader;
use App\Models\ExpertTrade;
use App\Models\CopySubscription;
use App\Models\CopyTrade;
use App\Models\ExpertPerformanceHistory;
use App\Models\Tp_Transaction;
use App\Models\TradingAsset;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
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
            ->get();

        // trading assets for simulation
        $tradingAssets = TradingAsset::select('id', 'symbol')->get();

        return view('admin.copy-trading.index', compact('title', 'stats', 'recentTrades', 'topExperts', 'tradingAssets'));
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

    /**
     * Manually stimulate a trade for an expert and distribute the entered
     * profit/loss to every active subscriber of that expert.
     *
     * IMPORTANT: the amount typed by the administrator is the FINAL amount.
     * No ROI percentage, profit share, copy-percentage scaling or any other
     * background calculation is applied on top of it - each affected user
     * receives exactly the entered amount.
     */
    public function simulateTrade(Request $request)
    {
        $request->validate([
            'expert_trader_id' => 'required|exists:expert_traders,id',
            'amount' => 'required|numeric|min:0.01|max:1000000000',
            'type' => 'required|in:profit,loss',
            'asset_id' => 'nullable|exists:trading_assets,id',
            'simulation_token' => 'nullable|string|max:64',
        ]);

        $amount = round((float) $request->input('amount'), 2);
        $type = $request->input('type');
        $signedAmount = $type === 'profit' ? $amount : -$amount;

        // Idempotency: claim this submission before touching the database so a
        // double click / re-post can never credit the same entry twice.
        $token = $request->input('simulation_token');
        $claimed = true;
        if ($token) {
            $claimed = Cache::add('copy-trading:simulated:' . sha1($token), 1, now()->addMinutes(30));

            if (!$claimed) {
                return back()->with('message', 'This entry was already processed. No additional credit was made.');
            }
        }

        try {
            $credited = 0;

            DB::transaction(function () use ($request, $amount, $type, $signedAmount, &$credited) {
                $expert = ExpertTrader::findOrFail($request->input('expert_trader_id'));
                $tradingAsset = $request->filled('asset_id') ? TradingAsset::find($request->input('asset_id')) : null;

                // The manual trade record mirrors the entered amount exactly:
                // pnl is the amount itself (profit) or its negation (loss).
                $expertTrade = ExpertTrade::create([
                    'expert_trader_id' => $expert->id,
                    'trading_asset_id' => $tradingAsset?->id,
                    'amount' => $amount,
                    'type' => $type,
                    'asset_price' => $tradingAsset?->current_price ?? 100,
                    'quantity' => $tradingAsset ? $amount / $tradingAsset->current_price : 0,
                    'entry_price' => $tradingAsset?->current_price ?? 100,
                    'exit_price' => $tradingAsset?->current_price ?? 100,
                    'pnl' => $signedAmount,
                    'trade_direction' => 'long',
                    'status' => 'closed',
                    'opened_at' => Carbon::now()->subMinutes(30),
                    'closed_at' => Carbon::now(),
                ]);

                $credited = $this->processCopyTradesForManualTrade($expert, $expertTrade, $signedAmount);

                if ($credited === 0 && $token) {
                    // Nothing was credited - release the idempotency claim so
                    // the administrator can retry the same entry.
                    Cache::forget('copy-trading:simulated:' . sha1($token));
                }
            });
        } catch (\Throwable $e) {
            if ($token) {
                Cache::forget('copy-trading:simulated:' . sha1($token));
            }

            throw $e;
        }

        $displayAmount = number_format($amount, 2, '.', ',');

        if ($credited === 0) {
            return back()->with(
                'message',
                "No active subscribers found for this expert, so no credit was applied. The {$displayAmount} {$type} entry was recorded but not distributed."
            );
        }

        $word = $type === 'profit' ? 'profit' : 'loss';

        return back()->with(
            'success',
            "Manual trade simulated! Exactly {$displayAmount} was applied as {$word} to every active subscriber's account."
        );
    }

    /**
     * Credit the EXACT entered amount to every active subscriber.
     *
     * Runs inside the caller's transaction, locks the subscription rows and
     * skips any subscription that already has a record for this trade, so the
     * same trade can never be distributed twice.
     *
     * @return int number of subscriptions processed
     */
    private function processCopyTradesForManualTrade(ExpertTrader $expert, ExpertTrade $expertTrade, float $signedAmount): int
    {
        $activeSubscriptions = CopySubscription::where('expert_trader_id', $expert->id)
            ->active()
            ->with('user')
            ->lockForUpdate()
            ->get();

        $processed = 0;

        foreach ($activeSubscriptions as $subscription) {
            // Never credit the same trade to the same subscription twice.
            $alreadyProcessed = CopyTrade::where('expert_trade_id', $expertTrade->id)
                ->where('copy_subscription_id', $subscription->id)
                ->exists();

            if ($alreadyProcessed) {
                continue;
            }

            $user = $subscription->user;

            if (!$user) {
                continue;
            }

            // Position sizing metadata only - it never changes the credited
            // amount, which is always exactly what the administrator entered.
            $copyAmount = $subscription->calculateCopyAmount($expertTrade->amount);
            $copyRatio = $expertTrade->amount > 0 ? $copyAmount / $expertTrade->amount : 1;

            CopyTrade::create([
                'copy_subscription_id' => $subscription->id,
                'expert_trade_id' => $expertTrade->id,
                'user_id' => $subscription->user_id,
                'trading_asset_id' => $expertTrade->trading_asset_id,
                'amount' => round($copyAmount, 2),
                'type' => $expertTrade->type,
                'asset_price' => $expertTrade->asset_price,
                'quantity' => round($expertTrade->quantity * $copyRatio, 8),
                'entry_price' => $expertTrade->entry_price,
                'exit_price' => $expertTrade->exit_price,
                'pnl' => $signedAmount,
                'trade_direction' => $expertTrade->trade_direction,
                'status' => $expertTrade->status,
                'copy_ratio' => round($copyRatio, 4),
                'opened_at' => $expertTrade->opened_at,
                'closed_at' => $expertTrade->closed_at
            ]);

            // Credit the exact amount to the user's available balance/wallet.
            $user->account_bal = round($user->account_bal + $signedAmount, 2);
            $user->save();

            // Transaction record + trading history entry for the same amount.
            Tp_Transaction::create([
                'user' => $user->id,
                'plan' => $signedAmount >= 0 ? 'Copy Trading Profit' : 'Copy Trading Loss',
                'amount' => $signedAmount,
                'type' => 'ROI',
            ]);

            $processed++;
        }

        return $processed;
    }
}