<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TradingAsset;

class TradingAssetController extends Controller
{
    public function index(Request $request)
    {
        $title = "Manage Trading Assets";
        $perPage = $request->get('per_page', 15); // Default to 15
        $assets = TradingAsset::withCount('tradingBots')->latest()->paginate($perPage);
        return view('admin.trading-assets.index', compact('title', 'assets'));
    }

    public function create()
    {
        $title = "Create Trading Asset";
        return view('admin.trading-assets.create', compact('title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:20|unique:trading_assets,symbol',
            'type' => 'required|in:crypto,stock,forex,commodity',
            'current_price' => 'nullable|numeric|min:0',
            'change_24h' => 'nullable|numeric',
            'status' => 'required|in:active,inactive',
        ]);

        TradingAsset::create($request->all());

        return redirect()->route('admin.trading-assets.index')->with('success', 'Trading asset created successfully!');
    }

    public function show($id)
    {
        $title = "View Trading Asset";
        $asset = TradingAsset::with('tradingBots')->findOrFail($id);
        return view('admin.trading-assets.show', compact('title', 'asset'));
    }

    public function edit($id)
    {
        $title = "Edit Trading Asset";
        $asset = TradingAsset::findOrFail($id);
        return view('admin.trading-assets.edit', compact('title', 'asset'));
    }

    public function update(Request $request, $id)
    {
        $tradingAsset = TradingAsset::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:20|unique:trading_assets,symbol,' . $id,
            'type' => 'required|in:crypto,stock,forex,commodity',
            'current_price' => 'nullable|numeric|min:0',
            'change_24h' => 'nullable|numeric',
            'status' => 'required|in:active,inactive',
        ]);

        $tradingAsset->update($request->all());

        return redirect()->route('admin.trading-assets.index')->with('success', 'Trading asset updated successfully!');
    }

    public function destroy($id)
    {
        $tradingAsset = TradingAsset::findOrFail($id);
        
        // Check if asset is used by any trading bots
        if ($tradingAsset->tradingBots()->count() > 0) {
            return back()->with('error', 'Cannot delete asset that is assigned to trading bots.');
        }
        
        $tradingAsset->delete();

        return redirect()->route('admin.trading-assets.index')->with('success', 'Trading asset deleted successfully!');
    }
}
