<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Settings;
use App\Models\SettingsCont;
use App\Models\Deposit;
use App\Models\Wdmethod;
use App\Models\Tp_Transaction;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Mail\DepositStatus;
use App\Models\CompoundInvestmentContribution;
use Illuminate\Support\Facades\DB;

class DepositController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    public function getmethod($id)
    {
        $methodname = Wdmethod::whereKey($id)
            ->whereIn('type', ['deposit', 'both'])
            ->whereRaw('LOWER(TRIM(methodtype)) = ?', ['crypto'])
            ->whereRaw('LOWER(TRIM(status)) = ?', ['enabled'])
            ->firstOrFail();
        return response()->json($methodname->name);
    }

    //Return payment page
    public function newdeposit(Request $request)
    {
        // A standard account deposit must not inherit a pending recurring
        // contribution context from a previous browser session.
        $request->session()->forget('compound_contribution_id');
        $settings = Settings::where('id', '1')->first();
        $minimum = optional(SettingsCont::find(1))->minamt;
        $minimum = is_numeric($minimum) ? (float) $minimum : 1;
        $data = $request->validate([
            'amount' => 'required|numeric|min:' . $minimum,
            'payment_method_id' => 'required|integer|exists:wdmethods,id',
        ]);
        $methodname = Wdmethod::whereKey($data['payment_method_id'])
            ->whereIn('type', ['deposit', 'both'])
            ->whereRaw('LOWER(TRIM(methodtype)) = ?', ['crypto'])
            ->whereRaw('LOWER(TRIM(status)) = ?', ['enabled'])
            ->firstOrFail();
        if ($settings === null) {
            abort(503, 'Deposit settings are currently unavailable.');
        }
        $client_secret = null;

        if ($methodname->name == "Credit Card" and $settings->credit_card_provider == "Stripe") {
            $secretkey = $settings->s_s_k;
            $zero = '00';
            $amt = $data['amount'] . $zero;

            \Stripe\Stripe::setApiKey($secretkey);
            $paymentIntent  = \Stripe\PaymentIntent::create([
                'amount' => $amt,
                'currency' => strtolower($settings->s_currency),
                'payment_method_types' => ['card'],
                'description' => 'Funding My Investment Account',
                'shipping' => [
                    'name' => Auth::user()->name,
                    'address' => [
                        'line1' => 'No Address',
                        'postal_code' => '000000',
                        'city' => 'No City',
                        'state' => 'CA',
                        'country' => 'US',
                    ],
                ],
                'metadata' => ['integration_check' => 'accept_a_payment'],
            ]);

            //return $client_secret;
            $client_secret = $paymentIntent->client_secret;
        }


        //store payment info in session
        $request->session()->put('amount', $data['amount']);
        $request->session()->put('payment_mode', $methodname->name);
        $request->session()->put('payment_method_id', $methodname->id);
        $request->session()->put('payment_network', $methodname->network);
        $request->session()->put('intent', $client_secret);

        return redirect()->route('payment');
    }

    //payment route
    public function payment(Request $request)
    {
        $methodId = $request->session()->get('payment_method_id');
        $contributionId = $request->session()->get('compound_contribution_id');
        $contribution = null;
        if ($contributionId) {
            $contribution = CompoundInvestmentContribution::with('investment')
                ->whereKey($contributionId)
                ->whereHas('investment', function ($query) {
                    $query->where('user_id', Auth::id())
                        ->where('status', 'active')
                        ->where('deposit_status', 'approved');
                })
                ->where('status', 'pending')
                ->whereNull('deposit_id')
                ->first();
            if (!$contribution) {
                $request->session()->forget(['compound_contribution_id', 'payment_method_id', 'payment_network', 'payment_mode', 'amount', 'intent']);
                return redirect()->route('compound-investment.history')
                    ->with('error', 'This recurring contribution is no longer awaiting payment.');
            }
        }
        $methodname = $methodId
            ? Wdmethod::whereKey($methodId)
                ->whereIn('type', ['deposit', 'both'])
                ->whereRaw('LOWER(TRIM(methodtype)) = ?', ['crypto'])
                ->whereRaw('LOWER(TRIM(status)) = ?', ['enabled'])
                ->first()
            : Wdmethod::where('name', $request->session()->get('payment_mode'))
                ->whereIn('type', ['deposit', 'both'])
                ->whereRaw('LOWER(TRIM(methodtype)) = ?', ['crypto'])
                ->whereRaw('LOWER(TRIM(status)) = ?', ['enabled'])
                ->first();
        if (!$methodname) {
            return redirect()->route('deposits')
                ->with('error', 'The selected deposit method is no longer enabled. Please choose another method.');
        }
        if ($methodId && (string) $request->session()->get('payment_network') !== (string) $methodname->network) {
            $request->session()->forget(['payment_method_id', 'payment_network', 'payment_mode', 'amount', 'intent']);
            return redirect()->route('deposits')
                ->with('error', 'The configured payment network changed. Choose the method again and verify its current details.');
        }
        $paymentAddress = strtolower(trim((string) $methodname->methodtype)) === 'currency'
            ? null
            : ($methodname->wallet_address ?: env($methodname->name));
        $paymentBarcode = $methodname->barcode
            && Storage::disk('public')->exists($methodname->barcode)
                ? Storage::disk('public')->url($methodname->barcode)
                : null;

        return view('user.payment')
            ->with(array(
                'amount' => $contribution ? $contribution->amount : $request->session()->get('amount'),
                'payment_mode' => $methodname,
                'paymentAddress' => $paymentAddress,
                'paymentBarcode' => $paymentBarcode,
                'intent' => $request->session()->get('intent'),
                'isCompoundRecurring' => (bool) $contribution,
                'compoundContribution' => $contribution,
                'title' => 'Make Payment',
            ));
    }

    public function savestripepayment(Request $request)
    {
        abort(410, 'Card and fiat deposits are disabled. Use an enabled cryptocurrency deposit method.');
        $user = User::where('id', Auth::user()->id)->first();

        //get settings
        $settings = Settings::where('id', '=', '1')->first();
        $earnings = $settings->referral_commission * $request->amount / 100;


        //save and confirm the deposit
        $dp = new Deposit();
        $dp->amount = $request->amount;
        $dp->payment_mode = "Stripe";
        $dp->status = 'Processed';
        $dp->proof = "Credit Card";
        $dp->plan = 0;
        $dp->user = $user->id;
        $this->setDepositPaymentMethodMetadata($dp, $request->session()->get('payment_method_id'));
        $dp->save();

        // Send deposit notification for Stripe payment
        $this->notificationService->sendDepositNotification(
            $user->id,
            $request->amount,
            'Stripe',
            'Processed'
        );

        if ($settings->deposit_bonus != NULL and $settings->deposit_bonus > 0) {
            $bonus = $request->amount * $settings->deposit_bonus / 100;
            //create history
            Tp_Transaction::create([
                'user' => $user->id,
                'plan' => "Deposit Bonus for $settings->currency $request->amount deposited",
                'amount' => $bonus,
                'type' => "Bonus",
            ]);
        } else {
            $bonus = 0;
        }

        //add funds to user's account
        User::where('id', $user->id)
            ->update([
                'account_bal' => $user->account_bal + $request->amount + $bonus,
                'bonus' => $user->bonus + $bonus,
                'cstatus' => 'Customer',
            ]);

        if (!empty($user->ref_by)) {

            $agent = User::where('id', $user->ref_by)->first();
            User::where('id', $user->ref_by)
                ->update([
                    'account_bal' => $agent->account_bal + $earnings,
                    'ref_bonus' => $agent->ref_bonus + $earnings,
                ]);

            //credit commission to ancestors
            $users = User::all();
            $this->getAncestors($users, $request->amount, $user->id);

            Tp_Transaction::create([
                'user' => $user->ref_by,
                'plan' => "Credit",
                'amount' => $earnings,
                'type' => "Ref_bonus",
            ]);
        }

        try {
            //Send confirmation email to user regarding his deposit and it's successful.
            Mail::to($user->email)->send(new DepositStatus($dp, $user, 'Successful Deposit', false));
        } catch (\Exception $e) {
            Log::error('Failed to send Stripe deposit confirmation email to user', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'deposit_id' => $dp->id,
                'user_email' => $user->email,
                'amount' => $request->amount
            ]);
        }

        try {
            // Send notification for successful automatic deposit
            $this->notificationService->sendDepositNotification(
                $user->id,
                $request->amount,
                'Credit Card (Stripe)',
                'approved'
            );
        } catch (\Exception $e) {
            Log::error('Failed to send Stripe deposit notification', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'deposit_id' => $dp->id,
                'amount' => $request->amount
            ]);
        }

        // delete the session variables
        $request->session()->forget('payment_mode');
        $request->session()->forget('payment_method_id');
        $request->session()->forget('payment_network');
        $request->session()->forget('amount');
        $request->session()->forget('intent');

        return response()->json(['success' => 'Payment Completed, redirecting']);
    }

    //Save deposit requests
    public function savedeposit(Request $request)
    {
        $this->validate($request, [
            'proof' => 'required|file|mimes:jpg,jpeg,png,webp,pdf,doc|max:10000',
        ]);

        $settings = Settings::where('id', '=', '1')->first();
        $sessionMethodId = $request->session()->get('payment_method_id');
        $requestedMethodId = $request->input('payment_method_id', $sessionMethodId);
        if ($sessionMethodId && (int) $requestedMethodId !== (int) $sessionMethodId) {
            return redirect()->back()->with('error', 'The selected deposit method changed. Please restart the deposit.');
        }

        $method = null;
        if ($requestedMethodId) {
            $method = Wdmethod::whereKey($requestedMethodId)
                ->whereIn('type', ['deposit', 'both'])
                ->whereRaw('LOWER(TRIM(methodtype)) = ?', ['crypto'])
                ->whereRaw('LOWER(TRIM(status)) = ?', ['enabled'])
                ->first();
        } else {
            // Preserve a valid in-flight session created before payment IDs
            // were added, while still resolving against current admin config.
            $legacyMethodName = $request->session()->get('payment_mode', $request->input('paymethd_method'));
            $method = Wdmethod::where('name', $legacyMethodName)
                ->whereIn('type', ['deposit', 'both'])
                ->whereRaw('LOWER(TRIM(methodtype)) = ?', ['crypto'])
                ->whereRaw('LOWER(TRIM(status)) = ?', ['enabled'])
                ->first();
        }
        if (!$method) {
            return redirect()->back()->with('error', 'The selected deposit method is no longer enabled. Please choose another method.');
        }
        if ($sessionMethodId
            && (string) $request->session()->get('payment_network') !== (string) $method->network) {
            return redirect()->back()->with('error', 'The configured payment network changed. Restart the deposit and verify the current network.');
        }

        $sessionAmount = $request->session()->get('amount');
        $amount = is_numeric($sessionAmount) ? (float) $sessionAmount : (float) $request->input('amount');
        $contributionId = $request->session()->get('compound_contribution_id');
        $contribution = $contributionId
            ? CompoundInvestmentContribution::with('investment')
                ->whereKey($contributionId)
                ->whereHas('investment', function ($query) {
                    $query->where('user_id', Auth::id())
                        ->where('status', 'active')
                        ->where('deposit_status', 'approved');
                })
                ->where('status', 'pending')
                ->whereNull('deposit_id')
                ->first()
            : null;
        if ($contributionId && (!$contribution
            || (int) $requestedMethodId !== (int) $sessionMethodId
            || abs(round($amount * 100) - round((float) $contribution->amount * 100)) > 0)) {
            return redirect()->route('compound-investment.history')
                ->with('error', 'The recurring contribution payment details changed. Please restart the contribution.');
        }
        if (!is_finite($amount) || $amount <= 0) {
            return redirect()->back()->with('error', 'The deposit amount is invalid. Please start the deposit again.');
        }
        $minimum = optional(SettingsCont::find(1))->minamt;
        $minimum = is_numeric($minimum) ? (float) $minimum : 1;
        if (!$contribution && $amount < $minimum) {
            return redirect()->back()->with('error', 'The deposit amount is below the configured minimum.');
        }
        if ($request->filled('amount')
            && abs(round((float) $request->input('amount') * 100) - round($amount * 100)) > 0) {
            return redirect()->back()->with('error', 'The deposit amount changed. Please start the deposit again.');
        }

        $path = $request->file('proof')->store('uploads', 'public');
        if ($contribution) {
            try {
                $dp = DB::transaction(function () use ($contribution, $method, $amount, $path) {
                    $lockedContribution = CompoundInvestmentContribution::whereKey($contribution->id)
                        ->lockForUpdate()
                        ->firstOrFail();
                    $investment = \App\Models\CompoundInvestment::whereKey($lockedContribution->compound_investment_id)
                        ->lockForUpdate()
                        ->firstOrFail();
                    if ($lockedContribution->status !== 'pending'
                        || $lockedContribution->deposit_id
                        || $investment->status !== 'active'
                        || $investment->deposit_status !== 'approved'
                        || abs(round($amount * 100) - round((float) $lockedContribution->amount * 100)) > 0) {
                        throw new \DomainException('This recurring payment has already been submitted or is no longer eligible.');
                    }

                    $dp = new Deposit();
                    $dp->amount = $lockedContribution->amount;
                    $dp->payment_mode = $method->name;
                    $dp->payment_method_id = $method->id;
                    $dp->payment_network = $method->network;
                    $dp->status = 'Pending';
                    $dp->proof = $path;
                    $dp->user = Auth::id();
                    $dp->save();

                    $lockedContribution->deposit_id = $dp->id;
                    $lockedContribution->payment_method = $method->name;
                    $lockedContribution->save();

                    return $dp;
                });
            } catch (\Throwable $e) {
                Storage::disk('public')->delete($path);
                if ($e instanceof \DomainException) {
                    return redirect()->route('compound-investment.show', $contribution->investment)
                        ->with('error', $e->getMessage());
                }
                throw $e;
            }
        } else {
            $dp = new Deposit();
            $dp->amount = $amount;
            $dp->payment_mode = $method->name;
            $dp->payment_method_id = $method->id;
            $dp->payment_network = $sessionMethodId
                ? $request->session()->get('payment_network')
                : $method->network;
            $dp->status = 'Pending';
            $dp->proof = $path;
            $dp->user = Auth::user()->id;
            $dp->save();
        }

        // Send deposit notification for manual deposit
        $this->notificationService->sendDepositNotification(
            Auth::user()->id,
            $amount,
            $method->name,
            'Pending'
        );

        //get user
        $user = User::where('id', Auth::user()->id)->first();

        try {
            //Send Email to admin regarding this deposit
            if ($contribution) {
                $body = "New USER RECURRING TOP-UP submitted and pending review.\n"
                    . "User: {$user->name} ({$user->email})\n"
                    . "Investment reference: #{$contribution->compound_investment_id}\n"
                    . "Amount: $" . number_format((float) $contribution->amount, 2) . "\n"
                    . "Payment method: {$method->name}" . ($method->network ? " ({$method->network})" : '') . "\n"
                    . "Date: " . now()->format('M j, Y H:i') . "\n"
                    . "Payment status: Pending admin approval";
                Mail::to($settings->contact_email)->send(new \App\Mail\NewNotification(
                    $body,
                    'Recurring compound contribution awaiting approval',
                    'Admin',
                    url('/admin/dashboard/mdeposits')
                ));
            } else {
                Mail::to($settings->contact_email)->send(new DepositStatus($dp, $user, 'Successful Deposit', true));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send deposit notification email to admin', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'deposit_id' => $dp->id,
                'admin_email' => $settings->contact_email
            ]);
        }

        try {
            //Send confirmation email to user regarding his deposit and it's successful.to get a response back from admin
            Mail::to($user->email)->send(new DepositStatus($dp, $user, 'Successful Deposit', false));
        } catch (\Exception $e) {
            Log::error('Failed to send deposit confirmation email to user', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'deposit_id' => $dp->id,
                'user_email' => $user->email
            ]);
        }

        try {
            // Send notification for pending manual deposit
            $this->notificationService->sendDepositNotification(
                $user->id,
                $request['amount'],
                $request['paymethd_method'],
                'pending'
            );
        } catch (\Exception $e) {
            Log::error('Failed to send deposit notification', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'deposit_id' => $dp->id,
                'amount' => $request['amount'],
                'payment_method' => $request['paymethd_method']
            ]);
        }

        // Kill the session variables
        $request->session()->forget('payment_mode');
        $request->session()->forget('payment_method_id');
        $request->session()->forget('payment_network');
        $request->session()->forget('amount');
        $request->session()->forget('compound_contribution_id');

        return redirect()->route('deposits')
            ->with('success', 'Account Fund Sucessful! Please wait for system to validate this transaction.');
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
}
