<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    //Updating Profile Route
    public function updateprofile(Request $request)
    {
        User::where('id', Auth::user()->id)
            ->update([
                'name' => $request->name,
                'dob' => $request->dob,
                'phone' => $request->phone,
                'address' => $request->address,
            ]);

        try {
            // Send profile update notification
            $this->notificationService->sendProfileUpdateNotification(
                Auth::user()->id,
                'profile information',
                [
                    'updated_fields' => ['name', 'date_of_birth', 'phone', 'address']
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to send profile update notification', [
                'error' => $e->getMessage(),
                'user_id' => Auth::user()->id,
                'update_type' => 'profile_information'
            ]);
        }

        return response()->json(['status' => 200, 'success' => 'Profile Information Updated Sucessfully!']);
    }

    //update account and contact info
    public function updateacct(Request $request)
    {
        User::where('id', Auth::user()->id)
            ->update([
                'bank_name' => $request['bank_name'],
                'account_name' => $request['account_name'],
                'account_number' => $request['account_no'],
                'swift_code' => $request['swiftcode'],
                'btc_address' => $request['btc_address'],
                'eth_address' => $request['eth_address'],
                'ltc_address' => $request['ltc_address'],
                'usdt_address' => $request['usdt_address'],
            ]);

        try {
            // Send account settings update notification
            $this->notificationService->sendAccountSettingsNotification(
                Auth::user()->id,
                'withdrawal information',
                [
                    'updated_fields' => ['bank_details', 'crypto_addresses']
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to send account settings update notification', [
                'error' => $e->getMessage(),
                'user_id' => Auth::user()->id,
                'update_type' => 'withdrawal_information'
            ]);
        }

        return response()->json(['status' => 200, 'success' => 'Withdrawal Info updated Sucessfully']);
    }

    //Update Password
    public function updatepass(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required',
        ]);

        $user = User::find(Auth::user()->id);

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('message', 'Current password does not match!');
        }
        $user->password = Hash::make($request->password);
        $user->save();

        try {
            // Send password change notification
            $this->notificationService->sendPasswordChangeNotification(Auth::user()->id);
        } catch (\Exception $e) {
            Log::error('Failed to send password change notification', [
                'error' => $e->getMessage(),
                'user_id' => Auth::user()->id,
                'update_type' => 'password_change'
            ]);
        }

        return back()->with('success', 'Password updated successfully');
    }

    // Update email preference logic
    public function updateemail(Request $request)
    {
        $user = User::find(Auth::user()->id);

        $user->sendotpemail = $request->otpsend;
        $user->sendroiemail = $request->roiemail;
        $user->sendinvplanemail = $request->invplanemail;
        $user->save();

        try {
            // Send email preferences update notification
            $this->notificationService->sendAccountSettingsNotification(
                Auth::user()->id,
                'email preferences',
                [
                    'otp_email' => $request->otpsend,
                    'roi_email' => $request->roiemail,
                    'investment_plan_email' => $request->invplanemail
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to send email preferences update notification', [
                'error' => $e->getMessage(),
                'user_id' => Auth::user()->id,
                'update_type' => 'email_preferences'
            ]);
        }

        return response()->json(['status' => 200, 'success' => 'Email Preference updated']);
    }
}
