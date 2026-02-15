<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpertTrader;
use App\Models\ExpertTrade;
use App\Models\ExpertPerformanceHistory;
use App\Models\CopySubscription;
use App\Models\CopyTrade;
use App\Models\TradingAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ExpertTraderController extends Controller
{
    /**
     * Display a listing of expert traders
     */
    public function index(Request $request)
    {
        $query = ExpertTrader::with(['performanceHistory', 'subscriptions']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('specialization', 'like', "%{$search}%")
                  ->orWhere('strategy_type', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by risk level
        if ($request->filled('risk_level')) {
            $query->where('risk_level', $request->risk_level);
        }

        // Filter by verification status
        if ($request->filled('verified')) {
            $query->where('is_verified', $request->verified === 'true');
        }

        $experts = $query->paginate(15);

        // Get summary statistics
        $stats = [
            'total_experts' => ExpertTrader::count(),
            'active_experts' => ExpertTrader::where('status', 'active')->count(),
            'verified_experts' => ExpertTrader::where('is_verified', true)->count(),
            'total_subscribers' => CopySubscription::where('status', 'active')->count(),
            'total_copy_trades' => CopyTrade::count(),
            'total_profit_generated' => CopyTrade::sum('profit_loss')
        ];

        return view('admin.copy-trading.experts.index', compact('experts', 'stats'));
    }

    /**
     * Show the form for creating a new expert trader
     */
    public function create()
    {
        $tradingAssets = TradingAsset::where('status', 'active')->get();

        return view('admin.copy-trading.experts.create', compact('tradingAssets'));
    }

    /**
     * Store a newly created expert trader
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'specialization' => 'nullable|string|max:255',
            'years_experience' => 'required|integer|min:0|max:50',
            'monthly_return' => 'required|numeric|min:-100|max:1000',
            'win_rate' => 'required|numeric|min:0|max:100',
            'is_verified' => 'boolean',
            'performance_badge' => 'nullable|string|max:100',
            'strategy_type' => 'nullable|string|max:100',
            'experience_level' => 'required|in:Beginner,Intermediate,Advanced,Expert',
            'status_text' => 'nullable|string|max:100',
            'current_portfolio_value' => 'required|numeric|min:0',
            'initial_portfolio_value' => 'required|numeric|min:0',
            'risk_level' => 'required|in:Low,Medium,High',
            'minimum_copy_amount' => 'required|numeric|min:1',
            'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            $imagePath = $request->file('profile_image')->store('expert-traders', 'public');
            $data['profile_image'] = $imagePath;
        }

        // Set default status class based on status
        $data['status_class'] = $data['status'] === 'active' ? 'status-active' : 'status-inactive';

        // Set default status text if not provided
        if (empty($data['status_text'])) {
            $data['status_text'] = $data['status'] === 'active' ? 'Active Trading' : 'Inactive';
        }

        $expert = ExpertTrader::create($data);

        // Create initial performance history record
        ExpertPerformanceHistory::create([
            'expert_trader_id' => $expert->id,
            'date' => now()->format('Y-m-d'),
            'daily_return' => 0,
            'portfolio_value' => $expert->current_portfolio_value,
            'total_trades' => 0,
            'winning_trades' => 0,
            'losing_trades' => 0,
            'total_profit' => 0,
            'total_loss' => 0,
            'biggest_win' => 0,
            'biggest_loss' => 0
        ]);

        return redirect()->route('admin.copy-trading.experts.index')
            ->with('success', 'Expert trader created successfully!');
    }

    /**
     * Display the specified expert trader
     */
    public function show($id)
    {
        $expert = ExpertTrader::with([
            'trades' => function($query) {
                $query->orderBy('created_at', 'desc')->limit(20);
            },
            'performanceHistory' => function($query) {
                $query->orderBy('date', 'desc')->limit(30);
            },
            'activeSubscriptions.user'
        ])->findOrFail($id);

        // Get performance metrics
        $metrics = $this->getExpertMetrics($expert);

        // Get recent activity
        $recentTrades = $expert->trades()->orderBy('created_at', 'desc')->limit(10)->get();
        $recentSubscriptions = $expert->subscriptions()->with('user')
            ->orderBy('created_at', 'desc')->limit(10)->get();

        return view('admin.copy-trading.experts.show', compact('expert', 'metrics', 'recentTrades', 'recentSubscriptions'));
    }

    /**
     * Show the form for editing the specified expert trader
     */
    public function edit($id)
    {
        $expert = ExpertTrader::findOrFail($id);
        $tradingAssets = TradingAsset::where('status', 'active')->get();

        return view('admin.copy-trading.experts.edit', compact('expert', 'tradingAssets'));
    }

    /**
     * Update the specified expert trader
     */
    public function update(Request $request, $id)
    {
        $expert = ExpertTrader::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'specialization' => 'nullable|string|max:255',
            'years_experience' => 'required|integer|min:0|max:50',
            'monthly_return' => 'required|numeric|min:-100|max:1000',
            'win_rate' => 'required|numeric|min:0|max:100',
            'is_verified' => 'boolean',
            'performance_badge' => 'nullable|string|max:100',
            'strategy_type' => 'nullable|string|max:100',
            'experience_level' => 'required|in:Beginner,Intermediate,Advanced,Expert',
            'status_text' => 'nullable|string|max:100',
            'current_portfolio_value' => 'required|numeric|min:0',
            'initial_portfolio_value' => 'required|numeric|min:0',
            'additional_gains' => 'nullable|numeric',
            'risk_level' => 'required|in:Low,Medium,High',
            'minimum_copy_amount' => 'required|numeric|min:1',
            'status' => 'required|in:active,inactive'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete old image
            if ($expert->profile_image) {
                Storage::disk('public')->delete($expert->profile_image);
            }
            $imagePath = $request->file('profile_image')->store('expert-traders', 'public');
            $data['profile_image'] = $imagePath;
        }

        // Update status class based on status
        $data['status_class'] = $data['status'] === 'active' ? 'status-active' : 'status-inactive';

        $expert->update($data);

        return redirect()->route('admin.copy-trading.experts.show', $expert->id)
            ->with('success', 'Expert trader updated successfully!');
    }

    /**
     * Remove the specified expert trader
     */
    public function destroy($id)
    {
        $expert = ExpertTrader::findOrFail($id);

        // Check if expert has active subscriptions
        $activeSubscriptions = $expert->activeSubscriptions()->count();
        if ($activeSubscriptions > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete expert trader with active subscriptions. Please deactivate all subscriptions first.');
        }

        // Delete profile image
        if ($expert->profile_image) {
            Storage::disk('public')->delete($expert->profile_image);
        }

        $expert->delete();

        return redirect()->route('admin.copy-trading.experts.index')
            ->with('success', 'Expert trader deleted successfully!');
    }

    /**
     * Generate a manual trade for the expert
     */
    public function generateTrade(Request $request, $id)
    {
        $expert = ExpertTrader::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'asset_symbol' => 'required|string',
            'trade_type' => 'required|in:buy,sell',
            'entry_price' => 'required|numeric|min:0',
            'exit_price' => 'required|numeric|min:0',
            'quantity' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $trade = ExpertTrade::create([
            'expert_trader_id' => $expert->id,
            'asset_symbol' => $request->asset_symbol,
            'trade_type' => $request->trade_type,
            'entry_price' => $request->entry_price,
            'exit_price' => $request->exit_price,
            'quantity' => $request->quantity,
            'notes' => $request->notes,
            'status' => 'completed',
            'opened_at' => now()->subMinutes(rand(15, 120)),
            'closed_at' => now(),
            'duration_minutes' => rand(15, 120)
        ]);

        // Calculate profit/loss
        $trade->calculateProfitLoss();
        $trade->save();

        // Create copy trades for subscribers
        $trade->createCopyTrades();

        // Update expert performance
        $dailyReturn = ($trade->profit_loss / $expert->current_portfolio_value) * 100;
        $expert->current_portfolio_value += $trade->profit_loss;
        $expert->last_trade_at = now();
        $expert->updatePerformance($dailyReturn);

        return response()->json([
            'success' => true,
            'message' => 'Trade generated successfully!',
            'trade' => $trade
        ]);
    }

    /**
     * Get expert performance analytics
     */
    public function analytics($id)
    {
        $expert = ExpertTrader::findOrFail($id);

        // Get performance data for charts
        $performanceData = $expert->performanceHistory()
            ->where('date', '>=', now()->subDays(30))
            ->orderBy('date')
            ->get();

        $tradingData = $expert->trades()
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as trades_count, SUM(profit_loss) as daily_profit')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $subscriptionData = $expert->subscriptions()
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as new_subscriptions')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('admin.copy-trading.experts.analytics', compact(
            'expert',
            'performanceData',
            'tradingData',
            'subscriptionData'
        ));
    }

    /**
     * Bulk actions for expert traders
     */
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:activate,deactivate,verify,unverify,delete',
            'expert_ids' => 'required|array|min:1',
            'expert_ids.*' => 'exists:expert_traders,id'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $experts = ExpertTrader::whereIn('id', $request->expert_ids);
        $count = 0;

        switch ($request->action) {
            case 'activate':
                $count = $experts->update(['status' => 'active', 'status_class' => 'status-active']);
                break;
            case 'deactivate':
                $count = $experts->update(['status' => 'inactive', 'status_class' => 'status-inactive']);
                break;
            case 'verify':
                $count = $experts->update(['is_verified' => true]);
                break;
            case 'unverify':
                $count = $experts->update(['is_verified' => false]);
                break;
            case 'delete':
                // Check for active subscriptions
                $expertsWithSubscriptions = ExpertTrader::whereIn('id', $request->expert_ids)
                    ->whereHas('activeSubscriptions')
                    ->count();

                if ($expertsWithSubscriptions > 0) {
                    return redirect()->back()
                        ->with('error', 'Cannot delete experts with active subscriptions.');
                }

                $count = $experts->delete();
                break;
        }

        $actionText = [
            'activate' => 'activated',
            'deactivate' => 'deactivated',
            'verify' => 'verified',
            'unverify' => 'unverified',
            'delete' => 'deleted'
        ][$request->action];

        return redirect()->back()
            ->with('success', "{$count} expert traders {$actionText} successfully!");
    }

    /**
     * Get expert performance metrics
     */
    private function getExpertMetrics($expert)
    {
        $last30Days = now()->subDays(30);

        return [
            'total_trades' => $expert->trades()->count(),
            'profitable_trades' => $expert->trades()->where('profit_loss', '>', 0)->count(),
            'monthly_trades' => $expert->trades()->where('created_at', '>=', $last30Days)->count(),
            'monthly_profit' => $expert->trades()->where('created_at', '>=', $last30Days)->sum('profit_loss'),
            'total_subscribers' => $expert->activeSubscriptions()->count(),
            'total_copy_trades' => CopyTrade::whereHas('expertTrade', function($query) use ($expert) {
                $query->where('expert_trader_id', $expert->id);
            })->count(),
            'copy_profit_generated' => CopyTrade::whereHas('expertTrade', function($query) use ($expert) {
                $query->where('expert_trader_id', $expert->id);
            })->sum('profit_loss'),
            'average_trade_profit' => $expert->trades()->avg('profit_loss') ?? 0,
            'best_trade' => $expert->trades()->max('profit_loss') ?? 0,
            'worst_trade' => $expert->trades()->min('profit_loss') ?? 0,
        ];
    }
}
