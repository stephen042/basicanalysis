<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Settings;
use App\Models\Plans;
use App\Models\Agent;
use App\Models\User_plans;
use App\Models\Deposit;
use App\Models\Withdrawal;
use App\Models\Tp_Transaction;
use App\Models\Activity;
use App\Models\TradingBot;
use App\Models\UserTradingBot;
use App\Models\TradingLog;
use App\Models\CopySubscription;
use App\Models\CopyTrade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Mail\NewNotification;
use App\Models\Kyc;
use App\Services\NotificationService;
use App\Traits\PingServer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ManageUsersController extends Controller
{
    use PingServer;

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    // See user wallet balances
    public function loginactivity($id)
    {

        $user = User::where('id', $id)->first();

        return view('admin.Users.loginactivity', [
            'activities' => Activity::where('user', $id)->orderByDesc('id')->get(),
            'title' => "$user->name login activities",
            'user' => $user,
        ]);
    }

    public function showUsers($id)
    {
        $user = User::where('id', $id)->first();
        $ref = User::whereNull('ref_by')->where('id', '!=', $id)->get();

        return view('admin.Users.referral', [
            'title' => "Add users to $user->name referral list",
            'user' => $user,
            'ref' => $ref,
        ]);
    }

    public function fetchUsers()
    {
        $users = User::orderByDesc('id')->get();
        return response()->json([
            'message' => 'Success',
            'data' => $users,
            'code' => 200
        ]);
    }


    public function addReferral(Request $request)
    {
        $user = User::where('id', $request->user_id)->first();
        $ref = User::where('id', $request->ref_id)->first();

        $ref->ref_by = $user->id;
        $ref->save();
        return redirect()->back()
            ->with('success', "$ref->name is now referred by $user->name successfully");
    }

    public function clearactivity($id)
    {
        $activities = Activity::where('user', $id)->get();

        if (count($activities) > 0) {
            foreach ($activities as $act) {
                Activity::where('id', $act->id)->delete();
            }
            return redirect()->back()
                ->with('success', 'Activity Cleared Successfully!');
        }
        return redirect()->back()
            ->with('message', 'No Activity to clear!');
    }

    public function markplanas($status, $id)
    {
        User_plans::where('id', $id)->update([
            'active' => $status,
        ]);
        return redirect()->back()
            ->with('success', "Plan Active state changed to $status");
    }

    public function viewuser($id)
    {
        $user = User::where('id', $id)->first();
        return view('admin.Users.userdetails', [
            'user' => $user,
            'pl' => Plans::orderByDesc('id')->get(),
            'bots' => TradingBot::where('status', 'active')->orderByDesc('id')->get(),
            'title' => "Manage $user->name",
        ]);
    }
    //block user
    public function ublock($id)
    {
        User::where('id', $id)->update([
            'status' => 'blocked',
        ]);
        return redirect()->back()->with('success', 'Action Sucessful!');
    }

    //unblock user
    public function unblock($id)
    {
        User::where('id', $id)->update([
            'status' => 'active',
        ]);
        return redirect()->back()->with('success', 'Action Sucessful!');
    }

    //Turn on/off user trade
    public function usertrademode($id, $action)
    {
        if ($action == "on") {
            $action = "on";
        } elseif ($action == "off") {
            $action = "off";
        } else {
            return redirect() - back()->with('message', "Unknown action!");
        }

        User::where('id', $id)->update([
            'trade_mode' => $action,
        ]);
        return redirect()->back()->with('success', "User trade mode has been turned $action.");
    }

    //Manually Verify users email
    public function emailverify($id)
    {
        User::where('id', $id)->update([
            'email_verified_at' => \Carbon\Carbon::now(),
        ]);
        return redirect()->back()->with('success', 'User Email have been verified');
    }

    //Reset Password
    public function resetpswd($id)
    {
        User::where('id', $id)
            ->update([
                'password' => Hash::make('user01236'),
            ]);
        return redirect()->back()->with('success', 'Password has been reset to default');
    }

    //Clear user Account
    public function clearacct(Request $request, $id)
    {
        $settings = Settings::where('id', 1)->first();

        $deposits = Deposit::where('user', $id)->get();
        if (!empty($deposits)) {
            foreach ($deposits as $deposit) {
                Deposit::where('id', $deposit->id)->delete();
            }
        }

        $withdrawals = Withdrawal::where('user', $id)->get();
        if (!empty($withdrawals)) {
            foreach ($withdrawals as $withdrawals) {
                Withdrawal::where('id', $withdrawals->id)->delete();
            }
        }

        User::where('id', $id)->update([
            'account_bal' => '0',
            'roi' => '0',
            'bonus' => '0',
            'ref_bonus' => '0',
        ]);
        return redirect()->back()->with('success', "Account cleared to $settings->currency 0.00");
    }

    //Access users account
    public function switchuser($id)
    {
        $user = User::where('id', $id)->first();
        Auth::loginUsingId($user->id, true);
        return redirect()->route('dashboard')->with('success', "You are logged in as $user->name !");
    }

    //Manually Add Trading History to Users Route
    public function addHistory(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'plan' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'type' => 'required|in:Bonus,ROI',
        ]);

        // Create trading history record
        Tp_Transaction::create([
            'user' => $request->user_id,
            'plan' => $request->plan,
            'amount' => $request->amount,
            'type' => $request->type,
        ]);

        $user = User::where('id', $request->user_id)->first();
        $user_bal = $user->account_bal;

        // Add amount to account balance
        if ($request->amount > 0) {
            User::where('id', $request->user_id)
                ->update([
                    'account_bal' => $user_bal + $request->amount,
                ]);
        }

        // If type is ROI, also update ROI field
        if ($request->type == "ROI") {
            $user_roi = $user->roi;
            User::where('id', $request->user_id)
                ->update([
                    'roi' => $user_roi + $request->amount,
                ]);
        }

        return redirect()->back()
            ->with('success', 'Bot trading history added successfully!');
    }


    //Delete user
    public function delsystemuser($id)
    {
        //delete the user's withdrawals and deposits
        $deposits = Deposit::where('user', $id)->get();
        if (!empty($deposits)) {
            foreach ($deposits as $deposit) {
                Deposit::where('id', $deposit->id)->delete();
            }
        }
        $withdrawals = Withdrawal::where('user', $id)->get();
        if (!empty($withdrawals)) {
            foreach ($withdrawals as $withdrawals) {
                Withdrawal::where('id', $withdrawals->id)->delete();
            }
        }
        //delete the user plans
        $userp = User_plans::where('user', $id)->get();
        if (!empty($userp)) {
            foreach ($userp as $p) {
                //delete plans that their owner does not exist
                User_plans::where('id', $p->id)->delete();
            }
        }
        //delete the user from agent model if exists
        $agent = Agent::where('agent', $id)->first();
        if (!empty($agent)) {
            Agent::where('id', $agent->id)->delete();
        }

        // delete user from verification list
        if (DB::table('kycs')->where('user_id', $id)->exists()) {
            Kyc::where('user_id', $id)->delete();
        }

        // Delete user's trading bot records
        $userTradingBots = UserTradingBot::where('user_id', $id)->get();
        if (!empty($userTradingBots)) {
            foreach ($userTradingBots as $userBot) {
                // Delete trading logs for this bot
                TradingLog::where('user_trading_bot_id', $userBot->id)->delete();
                // Delete the user trading bot
                UserTradingBot::where('id', $userBot->id)->delete();
            }
        }

        // Delete user's copy trading records
        $copySubscriptions = CopySubscription::where('user_id', $id)->get();
        if (!empty($copySubscriptions)) {
            foreach ($copySubscriptions as $subscription) {
                // Delete copy trades for this subscription
                CopyTrade::where('copy_subscription_id', $subscription->id)->delete();
                // Delete the copy subscription
                CopySubscription::where('id', $subscription->id)->delete();
            }
        }

        User::where('id', $id)->delete();
        return redirect()->route('manageusers')
            ->with('success', 'User Account deleted successfully!');
    }

    //update users info
    public function edituser(Request $request)
    {
        $request->validate([
            'trading_profit_rate' => 'required|numeric|min:0|max:100',
            'copy_trading_win_rate' => 'required|numeric|min:0|max:100',
            'copy_trading_profit_percentage' => 'required|numeric|min:0|max:100',
            'copy_trading_loss_percentage' => 'required|numeric|min:0|max:100',
        ]);

        User::where('id', $request['user_id'])
            ->update([
                'name' => $request['name'],
                'email' => $request['email'],
                'country' => $request['country'],
                'username' => $request['username'],
                'phone' => $request['phone'],
                'ref_link' => $request['ref_link'],
                'trading_profit_rate' => $request['trading_profit_rate'],
                'copy_trading_win_rate' => $request['copy_trading_win_rate'],
                'copy_trading_profit_percentage' => $request['copy_trading_profit_percentage'],
                'copy_trading_loss_percentage' => $request['copy_trading_loss_percentage'],
            ]);
        return redirect()->back()->with('success', 'User details updated Successfully!');
    }

    //Update Signal Strength
    public function updateSignalStrength(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'signal_strength_enabled' => 'required|boolean',
            'signal_strength_value' => 'required|integer|min:0|max:100',
        ]);

        User::where('id', $request->user_id)->update([
            'signal_strength_enabled' => $request->signal_strength_enabled,
            'signal_strength_value' => $request->signal_strength_value,
        ]);

        return redirect()->back()->with('success', 'Signal strength updated successfully!');
    }

    //Update User Notification
    public function updateUserNotification(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'notification_enabled' => 'required|boolean',
            'notification_message' => 'nullable|string|max:1000',
        ]);

        User::where('id', $request->user_id)->update([
            'notification_enabled' => $request->notification_enabled,
            'notification_message' => $request->notification_message,
        ]);

        return redirect()->back()->with('success', 'User notification updated successfully!');
    }

    //Update Withdrawal Codes
    public function updateWithdrawalCodes(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'withdrawal_code_enabled' => 'required|boolean',
            'withdrawal_code' => 'nullable|string|max:255',
            'withdrawal_code_name' => 'nullable|string|max:255',
            'withdrawal_code_message' => 'nullable|string|max:1000',
            'tax_code_enabled' => 'required|boolean',
            'tax_code' => 'nullable|string|max:255',
            'tax_code_name' => 'nullable|string|max:255',
            'tax_code_message' => 'nullable|string|max:1000',
        ]);

        User::where('id', $request->user_id)->update([
            'withdrawal_code_enabled' => $request->withdrawal_code_enabled,
            'withdrawal_code' => $request->withdrawal_code,
            'withdrawal_code_name' => $request->withdrawal_code_name ?? 'Withdrawal Code',
            'withdrawal_code_message' => $request->withdrawal_code_message,
            'tax_code_enabled' => $request->tax_code_enabled,
            'tax_code' => $request->tax_code,
            'tax_code_name' => $request->tax_code_name ?? 'Tax Code',
            'tax_code_message' => $request->tax_code_message,
        ]);

        return redirect()->back()->with('success', 'Withdrawal codes updated successfully!');
    }

    //Send mail to one user
    public function sendmailtooneuser(Request $request)
    {

        $mailduser = User::where('id', $request->user_id)->first();

        try {
            Mail::to($mailduser->email)->send(new NewNotification($request->message, $request->subject, $mailduser->name));
        } catch (\Exception $e) {
            Log::error('Failed to send admin email to user', [
                'error' => $e->getMessage(),
                'user_id' => $mailduser->id,
                'user_email' => $mailduser->email,
                'subject' => $request->subject,
                'admin_id' => Auth::id()
            ]);
        }

        try {
            // Send admin message notification
            $this->notificationService->sendAdminMessageNotification(
                $mailduser->id,
                $request->subject,
                $request->message,
                Auth::user()->name ?? 'Admin'
            );
        } catch (\Exception $e) {
            Log::error('Failed to send admin message notification to user', [
                'error' => $e->getMessage(),
                'user_id' => $mailduser->id,
                'subject' => $request->subject,
                'admin_id' => Auth::id()
            ]);
        }

        return redirect()->back()->with('success', 'Your message was sent successfully!');
    }

    // Send Mail to all users
    public function sendmailtoall(Request $request)
    {

        if ($request->category == "All") {
            $users = User::all();
        } elseif ($request->category == "No active plans") {
            $users = User::whereDoesntHave('plans', function (Builder $query) {
                $query->where('active', '!=', 'yes');
            })->get();
        } elseif ($request->category == "No deposit") {
            $users = User::doesntHave('dp')->get();
        } elseif ($request->category == "Select Users") {
            $users = DB::table('users')
                ->whereIn('id', array_column($request->users, null))
                ->get();
        }
        if (count($users) > 0) {
            try {
                Mail::to($users)->send(new NewNotification($request->message, $request->subject, $request->title, null, null, $request->greet));
            } catch (\Exception $e) {
                Log::error('Failed to send bulk admin email to users', [
                    'error' => $e->getMessage(),
                    'user_count' => count($users),
                    'category' => $request->category,
                    'subject' => $request->subject,
                    'admin_id' => Auth::id()
                ]);
            }

            // Send notifications to all users in the batch
            foreach ($users as $user) {
                try {
                    $this->notificationService->sendAdminMessageNotification(
                        $user->id,
                        $request->subject,
                        $request->message,
                        Auth::user()->name ?? 'Admin'
                    );
                } catch (\Exception $e) {
                    Log::error('Failed to send bulk admin message notification to user', [
                        'error' => $e->getMessage(),
                        'user_id' => $user->id,
                        'subject' => $request->subject,
                        'admin_id' => Auth::id()
                    ]);
                    // Continue with other users even if one fails
                    continue;
                }
            }

            return redirect()->back()->with('success', 'Your message was sent successfully!');
        } else {
            return redirect()->back()->with("success", "No user under selected category to send mail to");
        }
    }

    // Delete User investment Plan
    public function deleteplan($id)
    {
        User_plans::where('id', $id)->delete();
        return redirect()->back()->with('success', 'User Plan deleted successfully!');
    }


       //action
     public function action(Request $request){

       $user = User::where('id', $request->user_id)->first();
       User::where('id', $request['user_id'])
            ->update([
            'amount'=> $request['amount'],
            'action'=> $request->type,
            ]);

     return redirect()->back()->with('success', 'Action added Successful!');
}



 //action
     public function signalaction(Request $request){

       $user = User::where('id', $request->user_id)->first();
       User::where('id', $request['user_id'])
            ->update([
            'signalamount'=> $request['signalamount'],
            'signalname'=> $request['signalname'],
            'signalstatus'=> $request->signalstatus,
            ]);

     return redirect()->back()->with('success', 'signal action added Successful!');
}

    public function saveuser(Request $request)
    {

        $request->validate([
            'name' => 'required|max:255',
            'username' => 'required|unique:users,username',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        $thisid = DB::table('users')->insertGetId([
            'name' => $request['name'],
            'email' => $request['email'],
            'ref_by' => NULL,
            'username' => $request['username'],
            'password' => Hash::make($request->password),
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ]);

        //assign referal link to user
        $settings = Settings::where('id', '=', '1')->first();
        $user = User::where('id', $thisid)->first();

        User::where('id', $thisid)
            ->update([
                'ref_link' => $settings->site_address . '/ref/' . $user->username,
            ]);
        return redirect()->back()->with('success', 'User created Sucessfully!');
    }

    // View user's trading bots
    public function userTradingBots($id)
    {
        $user = User::findOrFail($id);
        
        $userTradingBots = UserTradingBot::where('user_id', $id)
            ->with(['tradingBot', 'tradingLogs'])
            ->orderByDesc('created_at')
            ->paginate(10);

        $activeBotsCount = UserTradingBot::where('user_id', $id)
            ->where('status', 'active')
            ->count();

        $completedBotsCount = UserTradingBot::where('user_id', $id)
            ->where('status', 'completed')
            ->count();

        $totalInvested = UserTradingBot::where('user_id', $id)
            ->sum('amount');

        $totalReturns = TradingLog::whereHas('userTradingBot', function($query) use ($id) {
                $query->where('user_id', $id);
            })
            ->where('type', 'profit')
            ->sum('amount');

        return view('admin.Users.user-trading-bots', [
            'user' => $user,
            'userTradingBots' => $userTradingBots,
            'activeBotsCount' => $activeBotsCount,
            'completedBotsCount' => $completedBotsCount,
            'totalInvested' => $totalInvested,
            'totalReturns' => $totalReturns,
            'title' => "{$user->name}'s Trading Bots",
        ]);
    }
}
