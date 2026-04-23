<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use App\Models\Signal;
use Illuminate\Http\Request;

class SubscriptionSettings extends Controller
{
    // Return view
    public function index(Request $request)
    {
        return view('admin.Settings.SubscriptionSettings.show', [
            'title' => 'Subscription settings',
            'settings' => Settings::where('id', '=', '1')->first(),
        ]);
    }

    //Update Subscription Fees
    public function updatesubfee(Request $request)
    {

        Settings::where('id', $request['id'])
            ->update([
                'monthlyfee' => $request['monthlyfee'],
                'quarterlyfee' => $request['quaterlyfee'],
                'yearlyfee' => $request['yearlyfee'],
                'subscription_service' => $request['subscription_service'],
            ]);
        return response()->json(['status' => 200, 'success' => 'Subscription Settings Saved successfully']);
    }

    // Modify your existing method to also fetch the signals
    public function signalSettings()
    {
        return view('admin.Settings.signalSettings', [
            'title' => 'Signal settings',
            'settings' => Settings::where('id', '1')->first(),
            'signals' => Signal::latest()->get(), // Fetch all signals
        ]);
    }

    // Add Store Method
    public function storeSignal(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'duration' => 'required|integer|min:1',
            'amount' => 'required|numeric|min:0',
        ]);

        Signal::create($request->all());

        return back()->with('success', 'Signal Plan created successfully!');
    }

    // Add Delete Method
    public function deleteSignal($id)
    {
        Signal::findOrFail($id)->delete();
        return back()->with('success', 'Signal Plan deleted!');
    }
}
