<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Settings;
use App\Models\Wdmethod;
use App\Models\Withdrawal;
use App\Mail\NewNotification;
use App\Services\NotificationService;
use App\Traits\PingServer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ManageWithdrawalController extends Controller
{
    use PingServer;

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    //process withdrawals
    public function pwithdrawal(Request $request)
    {
        $withdrawal = Withdrawal::find($request->id);

        if (!$withdrawal) {
            return redirect()->route('mwithdrawals')->with('message', 'Withdrawal request not found.');
        }

        if ($withdrawal->status === 'Processed') {
            return redirect()->route('mwithdrawals')->with('message', 'This withdrawal has already been processed.');
        }

        $user = User::find($withdrawal->user);

        if (!$user) {
            return redirect()->route('mwithdrawals')->with('message', 'The user for this withdrawal no longer exists.');
        }

        if ($request->action == "Paid") {
            DB::transaction(function () use ($withdrawal) {
                $locked = Withdrawal::whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();
                if ($locked->status !== 'Pending') {
                    return;
                }
                $locked->status = 'Processed';
                $locked->save();
            });

            $settings = Settings::where('id', '=', '1')->first();
            $message = "This is to inform you that your withdrawal request of $user->currency$withdrawal->amount have approved and funds have been sent to your selected account";

            try {
                $this->notificationService->sendWithdrawalProcessedNotification(
                    $user->id,
                    $withdrawal->amount,
                    $withdrawal->payment_mode,
                    'processed'
                );
            } catch (\Exception $e) {
                Log::error('Failed to send withdrawal processed notification', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                    'withdrawal_id' => $withdrawal->id,
                    'amount' => $withdrawal->amount
                ]);
            }

            try {
                Mail::to($user->email)->send(new NewNotification($message, 'Successful Withdrawal', $user->name));
            } catch (\Exception $e) {
                Log::error('Failed to send withdrawal approval email to user', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                    'withdrawal_id' => $withdrawal->id,
                    'user_email' => $user->email,
                    'amount' => $withdrawal->amount
                ]);
            }
        } else {
            // Reject: refund the reserved amount exactly once, using a lock to
            // prevent race conditions or double refunds.
            DB::transaction(function () use ($withdrawal, $user, $request) {
                $locked = Withdrawal::whereKey($withdrawal->id)->lockForUpdate()->firstOrFail();
                $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

                if ($locked->status !== 'Pending') {
                    return; // Already resolved — do not refund again.
                }

                $lockedUser->account_bal = round((float) $lockedUser->account_bal + (float) $locked->to_deduct, 2);
                $lockedUser->save();

                $locked->status = 'Rejected';
                $locked->save();
            });

            try {
                $this->notificationService->sendWithdrawalRejectedNotification(
                    $user->id,
                    $withdrawal->amount,
                    $withdrawal->payment_mode,
                    $request->reason ?? null
                );
            } catch (\Exception $e) {
                Log::error('Failed to send withdrawal rejected notification', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                    'withdrawal_id' => $withdrawal->id,
                    'amount' => $withdrawal->amount,
                    'reason' => $request->reason ?? 'No reason provided'
                ]);
            }

            if ($request->emailsend == "true") {
                try {
                    Mail::to($user->email)->send(new NewNotification($request->reason, $request->subject, $user->name));
                } catch (\Exception $e) {
                    Log::error('Failed to send withdrawal rejection email to user', [
                        'error' => $e->getMessage(),
                        'user_id' => $user->id,
                        'withdrawal_id' => $withdrawal->id,
                        'user_email' => $user->email,
                        'reason' => $request->reason ?? 'No reason provided'
                    ]);
                }
            }
        }

        return redirect()->route('mwithdrawals')->with('success', 'Action Successful!');
    }


    public function processwithdraw($id){
         $with = Withdrawal::where('id',$id)->first();
         $method = Wdmethod::where('name', $with->payment_mode)->first();
         $user = User::where('id', $with->user)->first();
        return view('admin.Withdrawals.pwithrdawal',[
            'withdrawal' => $with,
            'method' => $method,
            'user' => $user,
            'title'=>'Process withdrawal Request',
        ]);
    }
}
