<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Settings;
use App\Models\Deposit;
use App\Models\CompoundInvestment;
use App\Models\CompoundInvestmentContribution;
use App\Support\SchemaInspector;
use App\Models\Tp_Transaction;
use App\Mail\DepositStatus;
use App\Services\NotificationService;
use App\Traits\PingServer;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ManageDepositController extends Controller
{
    use PingServer;

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    //Delete deposit
    public function deldeposit($id)
    {
        $deposit = Deposit::where('id', $id)->first();
        Storage::disk('public')->delete($deposit->proof);
        Deposit::where('id', $id)->delete();
        return redirect()->back()->with('success', 'Deposit history has been deleted!');
    }

    //process deposits
    public function pdeposit($id)
    {
        //confirm the users plan
        $deposit = Deposit::where('id', $id)->first();
        abort_unless($deposit, 404);
        $user = User::where('id', $deposit->user)->first();
        abort_unless($user, 404);

        // Recurring investment contributions use the existing deposit approval
        // screen, but are never credited to the user's wallet or deposit total.
        $contribution = SchemaInspector::hasTable('compound_investment_contributions')
            ? CompoundInvestmentContribution::where('deposit_id', $deposit->id)->first()
            : null;
        if ($contribution) {
            try {
                DB::transaction(function () use ($deposit, $contribution) {
                    $lockedDeposit = Deposit::whereKey($deposit->id)->lockForUpdate()->firstOrFail();
                    $lockedContribution = CompoundInvestmentContribution::whereKey($contribution->id)
                        ->lockForUpdate()
                        ->firstOrFail();
                    $investment = CompoundInvestment::whereKey($lockedContribution->compound_investment_id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if (strtolower((string) $lockedDeposit->status) !== 'pending'
                        || $lockedContribution->status !== 'pending'
                        || (int) $lockedDeposit->user !== (int) $investment->user_id
                        || $investment->status !== 'active'
                        || $investment->deposit_status !== 'approved') {
                        throw new \DomainException('This contribution is no longer eligible for approval.');
                    }
                    if (abs(round((float) $lockedDeposit->amount * 100) - round((float) $lockedContribution->amount * 100)) > 0) {
                        throw new \DomainException('The deposit amount does not match the recurring contribution amount.');
                    }
                    $method = $lockedDeposit->paymentMethod;
                    if (!$method
                        || strtolower(trim((string) $method->methodtype)) !== 'crypto'
                        || !in_array(strtolower(trim((string) $method->type)), ['deposit', 'both'], true)) {
                        throw new \DomainException('Recurring contributions must be paid using a configured cryptocurrency method.');
                    }

                    $approvedAt = now();
                    $lockedDeposit->status = 'Processed';
                    $lockedDeposit->save();
                    $lockedContribution->status = 'approved';
                    $lockedContribution->approved_at = $approvedAt;
                    $lockedContribution->reviewed_by = Auth::guard('admin')->id();
                    $lockedContribution->save();

                    // Advance from the contribution's scheduled period, not the
                    // approval timestamp, so early payments move the schedule
                    // forward correctly.
                    $investment->next_recurring_due_at = $investment->nextRecurringDueAfterContribution(
                        $lockedContribution,
                        $approvedAt
                    );
                    $investment->save();
                });
            } catch (\DomainException $e) {
                return redirect()->back()->with('message', $e->getMessage());
            }

            $this->notificationService->create(
                $user->id,
                \App\Models\Notification::TYPE_DEPOSIT,
                'Recurring contribution approved',
                '$' . number_format((float) $contribution->amount, 2)
                    . ' recurring contribution for compound investment #' . $contribution->compound_investment_id
                    . ' has been verified and approved.',
                ['investment_id' => $contribution->compound_investment_id, 'contribution_id' => $contribution->id],
                \App\Models\Notification::PRIORITY_NORMAL,
                route('compound-investment.show', $contribution->compound_investment_id),
                'fas fa-circle-check'
            );
            try {
                Mail::to($user->email)->send(new DepositStatus($deposit, $user, 'Recurring Contribution Approved', false));
            } catch (\Throwable $e) {
                Log::error('Failed to send recurring contribution approval email', [
                    'error' => $e->getMessage(),
                    'deposit_id' => $deposit->id,
                    'contribution_id' => $contribution->id,
                ]);
            }

            return redirect()->back()->with('success', 'Recurring contribution approved. The wallet balance and deposit total were not changed.');
        }

        //get settings
        $settings = Settings::where('id', '=', '1')->first();

        $response = $this->callServer('earnings', '/process-deposit', [
            'referral_commission' => $settings->referral_commission,
            'amount' => $deposit->amount,
            'account_bal' => $user->account_bal,
            'depositBonus' => $settings->deposit_bonus,
        ]);

    if($deposit->user==$user->id){
            //add funds to user's account
            User::where('id',$user->id)
            ->update([
                'account_bal' => $user->account_bal + $deposit->amount,
                'cstatus' => 'Customer',
            ]);

            //get settings
            $settings=Settings::where('id', '=', '1')->first();
            $earnings=$settings->referral_commission*$deposit->amount/100;

            if(!empty($user->ref_by)){

                //get agent
                $agent=User::where('id',$user->ref_by)->first();
                User::where('id',$user->ref_by)
                ->update([
                    'account_bal' => $agent->account_bal + $earnings,
                    'ref_bonus' => $agent->ref_bonus + $earnings,
                ]);

                //create history
                Tp_Transaction::create([
                    'user' => $user->ref_by,
                    'plan' => "Credit",
                    'amount'=>$earnings,
                    'type'=>"Ref_bonus",
                ]);

                //credit commission to ancestors
                $deposit_amount = $deposit->amount;
                $array=User::all();
                $parent=$user->id;
                $this->getAncestors($array, $deposit_amount, $parent);
            }

 //update deposits
        Deposit::where('id',$id)
            ->update([
            'status' => 'Processed',
        ]);


         $user = User::where('id', $deposit->user)->first();

            try {
                //Send confirmation email to user regarding his deposit and it's successful.
                Mail::to($user->email)->send(new DepositStatus($deposit, $user,'Your Deposit have been Confirmed', false));
            } catch (\Exception $e) {
                Log::error('Failed to send deposit approval email to user', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                    'deposit_id' => $deposit->id,
                    'user_email' => $user->email,
                    'amount' => $deposit->amount
                ]);
            }

            try {
                // Send notification for approved deposit
                $this->notificationService->sendDepositNotification(
                    $user->id,
                    $deposit->amount,
                    $deposit->payment_mode,
                    'approved'
                );
            } catch (\Exception $e) {
                Log::error('Failed to send deposit approval notification', [
                    'error' => $e->getMessage(),
                    'user_id' => $user->id,
                    'deposit_id' => $deposit->id,
                    'amount' => $deposit->amount
                ]);
            }

        }


        return redirect()->back()->with('success', 'Action Sucessful!');
    }


    public function viewdepositimage($id)
    {
        $deposit = Deposit::where('id', $id)->first();

        return view('admin.Deposits.depositimg', [
            'deposit' => $deposit,
            'title' => 'View Deposit Screenshot',
            'settings' => Settings::where('id', '=', '1')->first(),
        ]);
    }


    //Get uplines
    function getAncestors($array, $deposit_amount, $parent = 0, $level = 0)
    {
        $referedMembers = '';
        $parent = User::where('id', $parent)->first();

        foreach ($array as $entry) {
            if ($entry->id == $parent->ref_by) {
                //get settings
                $settings = Settings::where('id', '=', '1')->first();

                if ($level == 1) {
                    $earnings = $settings->referral_commission1 * $deposit_amount / 100;
                    //add earnings to ancestor balance
                    User::where('id', $entry->id)
                        ->update([
                            'account_bal' => $entry->account_bal + $earnings,
                            'ref_bonus' => $entry->ref_bonus + $earnings,
                        ]);

                    //create history
                    Tp_Transaction::create([
                        'user' => $entry->id,
                        'plan' => "Credit",
                        'amount' => $earnings,
                        'type' => "Ref_bonus",
                    ]);
                } elseif ($level == 2) {
                    $earnings = $settings->referral_commission2 * $deposit_amount / 100;
                    //add earnings to ancestor balance
                    User::where('id', $entry->id)
                        ->update([
                            'account_bal' => $entry->account_bal + $earnings,
                            'ref_bonus' => $entry->ref_bonus + $earnings,
                        ]);

                    //create history
                    Tp_Transaction::create([
                        'user' => $entry->id,
                        'plan' => "Credit",
                        'amount' => $earnings,
                        'type' => "Ref_bonus",
                    ]);
                } elseif ($level == 3) {
                    $earnings = $settings->referral_commission3 * $deposit_amount / 100;
                    //add earnings to ancestor balance
                    User::where('id', $entry->id)
                        ->update([
                            'account_bal' => $entry->account_bal + $earnings,
                            'ref_bonus' => $entry->ref_bonus + $earnings,
                        ]);

                    //create history
                    Tp_Transaction::create([
                        'user' => $entry->id,
                        'plan' => "Credit",
                        'amount' => $earnings,
                        'type' => "Ref_bonus",
                    ]);
                } elseif ($level == 4) {
                    $earnings = $settings->referral_commission4 * $deposit_amount / 100;
                    //add earnings to ancestor balance
                    User::where('id', $entry->id)
                        ->update([
                            'account_bal' => $entry->account_bal + $earnings,
                            'ref_bonus' => $entry->ref_bonus + $earnings,
                        ]);

                    //create history
                    Tp_Transaction::create([
                        'user' => $entry->id,
                        'plan' => "Credit",
                        'amount' => $earnings,
                        'type' => "Ref_bonus",
                    ]);
                } elseif ($level == 5) {
                    $earnings = $settings->referral_commission5 * $deposit_amount / 100;
                    //add earnings to ancestor balance
                    User::where('id', $entry->id)
                        ->update([
                            'account_bal' => $entry->account_bal + $earnings,
                            'ref_bonus' => $entry->ref_bonus + $earnings,
                        ]);

                    //create history
                    Tp_Transaction::create([
                        'user' => $entry->id,
                        'plan' => "Credit",
                        'amount' => $earnings,
                        'type' => "Ref_bonus",
                    ]);
                }

                if ($level == 6) {
                    break;
                }

                //$referedMembers .= '- ' . $entry->name . '- Level: '. $level. '- Commission: '.$earnings.'<br/>';
                $referedMembers .= $this->getAncestors($array, $deposit_amount, $entry->id, $level + 1);
            }
        }
        return $referedMembers;
    }

    // for front end content management
    function RandomStringGenerator($n)
    {
        $generated_string = "";
        $domain = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
        $len = strlen($domain);
        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, $len - 1);
            $generated_string = $generated_string . $domain[$index];
        }
        // Return the random generated string
        return $generated_string;
    }
}
