<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Settings;
use App\Models\Wdmethod;
use Auth;

class SettingsController extends Controller
{
    /**
     * Update general settings
     */
    public function updatesettings(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'currency' => 'required|string|max:10',
            'website_keywords' => 'nullable|string',
            'website_description' => 'nullable|string',
        ]);

        try {
            $settings = Settings::first();
            if (!$settings) {
                $settings = new Settings();
            }

            $settings->site_name = $request->site_name;
            $settings->currency = $request->currency;
            $settings->website_keywords = $request->website_keywords;
            $settings->website_description = $request->website_description;
            
            if ($request->hasFile('logo')) {
                $logo = $request->file('logo');
                $logoName = time() . '_logo.' . $logo->getClientOriginalExtension();
                $logo->move(public_path('uploads'), $logoName);
                $settings->logo = $logoName;
            }

            if ($request->hasFile('favicon')) {
                $favicon = $request->file('favicon');
                $faviconName = time() . '_favicon.' . $favicon->getClientOriginalExtension();
                $favicon->move(public_path('uploads'), $faviconName);
                $settings->favicon = $faviconName;
            }

            $settings->save();

            return back()->with('success', 'Settings updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }

    /**
     * Update asset settings
     */
    public function updateasset(Request $request)
    {
        $request->validate([
            'asset_name' => 'required|string|max:255',
            'asset_symbol' => 'required|string|max:10',
            'asset_price' => 'required|numeric|min:0',
            'asset_status' => 'required|in:active,inactive',
        ]);

        try {
            // This would typically update a crypto assets table
            // For now, we'll store in settings or handle accordingly
            $settings = Settings::first();
            if (!$settings) {
                $settings = new Settings();
            }

            // Store asset information (you might want a separate assets table)
            $assets = json_decode($settings->crypto_assets ?? '[]', true);
            $assets[] = [
                'name' => $request->asset_name,
                'symbol' => $request->asset_symbol,
                'price' => $request->asset_price,
                'status' => $request->asset_status,
                'updated_at' => now()->toDateTimeString(),
            ];

            $settings->crypto_assets = json_encode($assets);
            $settings->save();

            return back()->with('success', 'Asset updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update asset: ' . $e->getMessage());
        }
    }

    /**
     * Update market settings
     */
    public function updatemarket(Request $request)
    {
        $request->validate([
            'market_status' => 'required|in:open,closed',
            'trading_hours_start' => 'nullable|date_format:H:i',
            'trading_hours_end' => 'nullable|date_format:H:i',
            'weekend_trading' => 'required|boolean',
        ]);

        try {
            $settings = Settings::first();
            if (!$settings) {
                $settings = new Settings();
            }

            $settings->market_status = $request->market_status;
            $settings->trading_hours_start = $request->trading_hours_start;
            $settings->trading_hours_end = $request->trading_hours_end;
            $settings->weekend_trading = $request->weekend_trading;
            $settings->save();

            return back()->with('success', 'Market settings updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update market settings: ' . $e->getMessage());
        }
    }

    /**
     * Update fee settings
     */
    public function updatefee(Request $request)
    {
        $request->validate([
            'deposit_fee' => 'required|numeric|min:0|max:100',
            'withdrawal_fee' => 'required|numeric|min:0|max:100',
            'trading_fee' => 'required|numeric|min:0|max:100',
            'transfer_fee' => 'required|numeric|min:0|max:100',
        ]);

        try {
            $settings = Settings::first();
            if (!$settings) {
                $settings = new Settings();
            }

            $settings->deposit_fee = $request->deposit_fee;
            $settings->withdrawal_fee = $request->withdrawal_fee;
            $settings->trading_fee = $request->trading_fee;
            $settings->transfer_fee = $request->transfer_fee;
            $settings->save();

            return back()->with('success', 'Fee settings updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update fee settings: ' . $e->getMessage());
        }
    }

    /**
     * Delete withdrawal method
     */
    public function deletewdmethod($id)
    {
        try {
            $method = Wdmethod::findOrFail($id);
            $method->delete();

            return back()->with('success', 'Withdrawal method deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete withdrawal method: ' . $e->getMessage());
        }
    }
}
