<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PredictionTrade;
use App\Models\TradingAsset;
use App\Models\UserTradeControl;
use App\Models\TradeSetting;
use App\Services\TradingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TradingController extends Controller
{
    protected $tradingService;

    public function __construct(TradingService $tradingService)
    {
        $this->tradingService = $tradingService;
    }

    /**
     * Main trading interface
     */
    public function index()
    {
        $title = 'Trading Platform';
        $user = Auth::user();
        
        // Get available assets
        $assets = TradingAsset::where('is_active', true)->get();
        
        // Get user's active trades
        $activeTrades = PredictionTrade::with('tradingAsset')
            ->where('user_id', $user->id)
            ->active()
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get user's trade controls
        $tradeControls = UserTradeControl::getForUser($user->id);
        
        // Check if trading is enabled
        $tradingEnabled = TradeSetting::isTradingAllowed() && $user->trading_enabled;
        
        // Get trading settings
        $settings = [
            'min_trade_amount' => TradeSetting::getMinTradeAmount(),
            'max_trade_amount' => TradeSetting::getMaxTradeAmount(),
            'default_payout' => TradeSetting::getDefaultPayout(),
        ];

        return view('user.trading.index', compact(
            'title', 'assets', 'activeTrades', 'tradeControls', 
            'tradingEnabled', 'settings'
        ));
    }

    /**
     * Get real-time asset price (AJAX)
     */
    public function getAssetPrice(TradingAsset $asset)
    {
        return response()->json([
            'success' => true,
            'price' => $asset->current_price,
            'change_24h' => $asset->change_24h,
            'last_updated' => $asset->updated_at->toISOString()
        ]);
    }

    /**
     * Get all available assets
     */
    public function getAssets()
    {
        $assets = TradingAsset::where('is_active', true)
            ->select('id', 'name', 'symbol', 'current_price', 'change_24h', 'icon_url')
            ->get();

        return response()->json([
            'success' => true,
            'assets' => $assets
        ]);
    }

    /**
     * Place a new trade
     */
    public function placeTrade(Request $request)
    {
        $request->validate([
            'asset_id' => 'required|exists:trading_assets,id',
            'prediction' => 'required|in:UP,DOWN',
            'trade_amount' => 'required|numeric|min:0.01',
            'trade_type' => 'required|in:fixed_time,flexible',
            'duration_minutes' => 'required_if:trade_type,fixed_time|nullable|integer|in:1,5,15,30,60'
        ]);

        $user = Auth::user();
        
        // Check if user can trade
        $controls = UserTradeControl::getForUser($user->id);
        if (!$controls->canUserTrade()) {
            return response()->json([
                'success' => false,
                'message' => $controls->getTradeValidationMessage()
            ], 422);
        }

        // Validate trade amount
        if (!$controls->validateTradeAmount($request->trade_amount)) {
            return response()->json([
                'success' => false,
                'message' => "Trade amount must be between {$controls->min_trade_amount} and {$controls->max_trade_amount}"
            ], 422);
        }

        // Check if user can open new trade
        if (!$controls->canOpenNewTrade()) {
            return response()->json([
                'success' => false,
                'message' => $controls->getTradeValidationMessage()
            ], 422);
        }

        // Check balance
        if ($user->trading_balance < $request->trade_amount) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient trading balance'
            ], 422);
        }

        try {
            $trade = $this->tradingService->placeTrade(
                $user->id,
                $request->asset_id,
                $request->prediction,
                $request->trade_amount,
                $request->trade_type,
                $request->duration_minutes
            );

            return response()->json([
                'success' => true,
                'message' => 'Trade placed successfully!',
                'trade' => $trade->load('tradingAsset')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to place trade: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Close an active trade manually
     */
    public function closeTrade(PredictionTrade $trade)
    {
        $user = Auth::user();
        
        // Verify ownership
        if ($trade->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if (!$trade->canClose()) {
            return response()->json([
                'success' => false,
                'message' => 'Trade cannot be closed at this time'
            ], 422);
        }

        try {
            $result = $this->tradingService->closeTradeManually($trade->id, $user->id);

            return response()->json([
                'success' => true,
                'message' => 'Trade closed successfully!',
                'trade' => $trade->fresh()->load('tradingAsset'),
                'result' => $result
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to close trade: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel an active trade (returns investment)
     */
    public function cancelTrade(PredictionTrade $trade)
    {
        $user = Auth::user();
        
        // Verify ownership
        if ($trade->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        if (!$trade->canClose()) {
            return response()->json([
                'success' => false,
                'message' => 'Trade cannot be cancelled at this time'
            ], 422);
        }

        try {
            $trade->cancelTrade();

            // Return the investment to user's balance
            $user->increment('trading_balance', $trade->trade_amount);

            return response()->json([
                'success' => true,
                'message' => 'Trade cancelled and investment returned!',
                'trade' => $trade->fresh(),
                'returned_amount' => $trade->trade_amount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel trade: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's active trades
     */
    public function activeTrades()
    {
        $user = Auth::user();
        
        $trades = PredictionTrade::with('tradingAsset')
            ->where('user_id', $user->id)
            ->active()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($trade) {
                return [
                    'id' => $trade->id,
                    'asset' => $trade->tradingAsset->only(['name', 'symbol', 'icon_url']),
                    'prediction' => $trade->prediction,
                    'trade_amount' => $trade->trade_amount,
                    'entry_price' => $trade->entry_price,
                    'current_price' => $trade->current_price,
                    'potential_payout' => $trade->potential_payout,
                    'current_profit' => $trade->calculateCurrentProfit(),
                    'trade_type' => $trade->trade_type,
                    'start_time' => $trade->start_time,
                    'end_time' => $trade->end_time,
                    'can_close' => $trade->canClose(),
                    'is_winning' => $trade->calculateCurrentProfit() > 0
                ];
            });

        return response()->json([
            'success' => true,
            'trades' => $trades
        ]);
    }

    /**
     * Get specific trade details
     */
    public function getTrade(PredictionTrade $trade)
    {
        $user = Auth::user();
        
        // Verify ownership
        if ($trade->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'trade' => $trade->load('tradingAsset')
        ]);
    }

    /**
     * Get user's trading balance
     */
    public function balance()
    {
        $user = Auth::user();
        
        return response()->json([
            'success' => true,
            'trading_balance' => $user->trading_balance,
            'demo_balance' => $user->demo_balance,
            'total_trades' => $user->total_trades,
            'winning_trades' => $user->winning_trades,
            'losing_trades' => $user->losing_trades,
            'win_rate' => $user->win_rate,
            'total_profit_loss' => $user->total_profit_loss
        ]);
    }

    /**
     * Get trading history
     */
    public function tradeHistory(Request $request)
    {
        $user = Auth::user();
        
        $query = PredictionTrade::with('tradingAsset')
            ->where('user_id', $user->id)
            ->whereIn('status', ['won', 'lost', 'cancelled']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('asset_id')) {
            $query->where('trading_asset_id', $request->asset_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $trades = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'trades' => $trades
        ]);
    }

    /**
     * Deposit to trading balance
     */
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1|max:10000'
        ]);

        $user = Auth::user();
        
        // Here you would integrate with payment gateway
        // For now, we'll just add to balance (demo purposes)
        
        $user->increment('trading_balance', $request->amount);

        return response()->json([
            'success' => true,
            'message' => 'Deposit successful!',
            'new_balance' => $user->fresh()->trading_balance
        ]);
    }

    /**
     * Withdraw from trading balance
     */
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        $user = Auth::user();
        
        if ($user->trading_balance < $request->amount) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance'
            ], 422);
        }

        // Here you would integrate with payment gateway
        // For now, we'll just subtract from balance (demo purposes)
        
        $user->decrement('trading_balance', $request->amount);

        return response()->json([
            'success' => true,
            'message' => 'Withdrawal successful!',
            'new_balance' => $user->fresh()->trading_balance
        ]);
    }

    /**
     * Toggle demo mode
     */
    public function toggleDemoMode()
    {
        $user = Auth::user();
        $user->is_demo_mode = !$user->is_demo_mode;
        $user->save();

        return response()->json([
            'success' => true,
            'demo_mode' => $user->is_demo_mode,
            'message' => $user->is_demo_mode ? 'Demo mode activated' : 'Live trading activated'
        ]);
    }

    /**
     * Get demo mode status
     */
    public function getDemoStatus()
    {
        $user = Auth::user();
        
        return response()->json([
            'success' => true,
            'is_demo_mode' => $user->is_demo_mode ?? false
        ]);
    }

    /**
     * Trading history view
     */
    public function history()
    {
        $title = 'Trading History';
        $user = Auth::user();
        
        $trades = PredictionTrade::with('tradingAsset')
            ->where('user_id', $user->id)
            ->whereIn('status', ['won', 'lost', 'cancelled'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $assets = TradingAsset::where('is_active', true)->get();

        return view('user.trading.history', compact('title', 'trades', 'assets'));
    }

    /**
     * Balance management view
     */
    public function balanceView()
    {
        $title = 'Trading Balance';
        $user = Auth::user();
        
        // Recent transactions (you might want to create a separate transactions table)
        $recentTrades = PredictionTrade::where('user_id', $user->id)
            ->whereIn('status', ['won', 'lost'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('user.trading.balance', compact('title', 'recentTrades'));
    }

    /**
     * Trading settings view
     */
    public function settings()
    {
        $title = 'Trading Settings';
        $user = Auth::user();
        
        $tradeControls = UserTradeControl::getForUser($user->id);

        return view('user.trading.settings', compact('title', 'tradeControls'));
    }

    /**
     * Get active trades for AJAX calls
     */
    public function getActiveTrades()
    {
        $user = Auth::user();
        
        $activeTrades = PredictionTrade::with('tradingAsset')
            ->where('user_id', $user->id)
            ->active()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($trade) {
                return [
                    'id' => $trade->id,
                    'asset' => $trade->tradingAsset->name ?? 'Unknown',
                    'prediction' => $trade->prediction,
                    'amount' => $trade->trade_amount,
                    'expiresAt' => $trade->expire_time,
                    'created_at' => $trade->created_at->toISOString(),
                ];
            });

        return response()->json([
            'success' => true,
            'trades' => $activeTrades
        ]);
    }

    /**
     * Get today's trading statistics
     */
    public function getTodayStats()
    {
        $user = Auth::user();
        $today = Carbon::today();
        
        $todayTrades = PredictionTrade::where('user_id', $user->id)
            ->whereDate('created_at', $today)
            ->get();

        $wins = $todayTrades->where('status', 'won')->count();
        $losses = $todayTrades->where('status', 'lost')->count();
        $totalTrades = $wins + $losses;
        $winRate = $totalTrades > 0 ? round(($wins / $totalTrades) * 100, 1) : 0;
        
        $profit = $todayTrades->where('status', 'won')->sum('profit_amount') - 
                 $todayTrades->where('status', 'lost')->sum('trade_amount');

        return response()->json([
            'success' => true,
            'stats' => [
                'wins' => $wins,
                'losses' => $losses,
                'winRate' => $winRate,
                'profit' => $profit,
                'totalTrades' => $totalTrades
            ]
        ]);
    }
}
