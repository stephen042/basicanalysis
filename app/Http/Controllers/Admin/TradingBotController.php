<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TradingBot;
use App\Models\UserTradingBot;
use App\Models\TradingLog;
use App\Models\TradingAsset;

class TradingBotController extends Controller
{
    public function index()
    {
        $title = "Manage Trading Bots";
        $tradingBots = TradingBot::latest()->get();
        return view('admin.trading-bots.index', compact('title', 'tradingBots'));
    }

    public function create()
    {
        $title = "Create Trading Bot";
        $tradingAssets = TradingAsset::where('is_active', true)->get();
        return view('admin.trading-bots.create', compact('title', 'tradingAssets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'min_amount' => 'required|numeric|min:1',
            'max_amount' => 'required|numeric|gt:min_amount',
            'duration' => 'required|integer|min:1',
            'profit_rate' => 'required|numeric|min:0.01|max:100',
            'status' => 'required|in:active,inactive',
            'assets' => 'required|array|min:1',
            'assets.*' => 'exists:trading_assets,id',
            'allocations' => 'required|array',
        ]);

        // Validate that each selected asset has an allocation
        foreach ($request->assets as $assetId) {
            if (!isset($request->allocations[$assetId]) || empty($request->allocations[$assetId])) {
                return back()->withErrors(['allocations' => "Please set allocation percentage for all selected assets."])->withInput();
            }
            
            $allocation = floatval($request->allocations[$assetId]);
            if ($allocation < 1 || $allocation > 100) {
                return back()->withErrors(['allocations' => "Allocation percentages must be between 1% and 100%."])->withInput();
            }
        }

        // Validate total allocation
        $totalAllocation = 0;
        foreach ($request->assets as $assetId) {
            $totalAllocation += floatval($request->allocations[$assetId]);
        }

        if ($totalAllocation > 100) {
            return back()->withErrors(['allocations' => "Total allocation cannot exceed 100%. Current total: {$totalAllocation}%"])->withInput();
        }

        $bot = TradingBot::create($request->only([
            'name', 'description', 'min_amount', 'max_amount', 
            'duration', 'profit_rate', 'status'
        ]));

        // Attach assets with allocations
        if ($request->has('assets')) {
            foreach ($request->assets as $assetId) {
                $allocation = $request->allocations[$assetId];
                $bot->tradingAssets()->attach($assetId, ['allocation_percentage' => $allocation]);
            }
        }

        return redirect()->route('admin.trading-bots.index')
            ->with('success', 'Trading bot created successfully!');
    }

    public function edit($id)
    {
        $title = "Edit Trading Bot";
        $tradingBot = TradingBot::with('tradingAssets')->findOrFail($id);
        $tradingAssets = TradingAsset::where('is_active', true)->get();
        return view('admin.trading-bots.edit', compact('title', 'tradingBot', 'tradingAssets'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'min_amount' => 'required|numeric|min:1',
            'max_amount' => 'required|numeric|gt:min_amount',
            'duration' => 'required|integer|min:1',
            'profit_rate' => 'required|numeric|min:0.01|max:100',
            'status' => 'required|in:active,inactive',
        ]);

        $tradingBot = TradingBot::findOrFail($id);
        $tradingBot->update($request->all());

        return redirect()->route('admin.trading-bots.index')->with('success', 'Trading bot updated successfully!');
    }

    public function destroy($id)
    {
        $tradingBot = TradingBot::findOrFail($id);
        $tradingBot->delete();

        return redirect()->route('admin.trading-bots.index')->with('success', 'Trading bot deleted successfully!');
    }

    public function subscribers()
    {
        $title = "Trading Bot Subscribers";
        $subscribers = UserTradingBot::with(['user', 'tradingBot'])->latest()->get();
        return view('admin.trading-bots.subscribers', compact('title', 'subscribers'));
    }

    public function logs()
    {
        $title = "Trading Logs";
        $logs = TradingLog::with(['userTradingBot.user', 'userTradingBot.tradingBot'])->latest()->paginate(50);
        return view('admin.trading-bots.logs', compact('title', 'logs'));
    }
}
