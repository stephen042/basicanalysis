<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Settings;
use App\Models\User_plans;
use App\Models\Wdmethod;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Mail\NewNotification;
use Illuminate\Support\Facades\Mail;
use App\Mail\WithdrawalStatus;
use App\Services\NotificationService;
use App\Traits\Coinpayment;

class WithdrawalController extends Controller
{
    use Coinpayment;

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Receive the selected withdrawal method and advance to the details step.
     */
    public function withdrawamount(Request $request)
    {
        $data = $request->validate([
            'method_id' => 'required|integer',
            'method_type' => 'required|in:crypto,currency',
            'asset' => 'required|string|max:64',
        ]);

        $method = Wdmethod::whereKey($data['method_id'])
            ->whereRaw('LOWER(TRIM(status)) = ?', ['enabled'])
            ->whereIn('type', ['withdrawal', 'both'])
            ->first();
        $actualType = $method ? strtolower(trim((string) $method->methodtype)) : null;
        $settings = Settings::find(1);
        $actualAsset = $actualType === 'crypto'
            ? $method->name
            : ($method ? ($method->currency ?: optional($settings)->s_currency ?: 'USD') : null);

        if (!$method
            || $actualType !== $data['method_type']
            || !hash_equals((string) $actualAsset, (string) $data['asset'])) {
            return redirect()->route('withdrawals')
                ->with('message', 'The selected withdrawal method is not available.');
        }

        session()->put('withdraw_method_id', $method->id);
        session()->put('withdrawal_type', $actualType);
        session()->put('withdrawal_asset', $actualAsset);
        session()->put('withdrawal_step', 'initial');

        return redirect()->route('withdrawfunds');
    }

    /**
     * Render the withdrawal details form for the method selected in the session.
     */
    public function withdrawfunds()
    {
        $method = Wdmethod::find(session('withdraw_method_id'));

        if (!$method) {
            return redirect()->route('withdrawals')
                ->with('message', 'Please select a withdrawal method.');
        }

        if (strtolower(trim((string) $method->status)) !== 'enabled'
            || !in_array($method->type, ['withdrawal', 'both'], true)) {
            return redirect()->route('withdrawals')
                ->with('message', 'The selected withdrawal method is no longer available.');
        }

        $methodtype = strtolower(trim((string) $method->methodtype));
        if (!in_array($methodtype, ['crypto', 'currency'], true)) {
            return redirect()->route('withdrawals')
                ->with('message', 'The configured withdrawal asset type is invalid.');
        }
        $settings = Settings::find(1);
        $asset = $methodtype === 'crypto'
            ? $method->name
            : ($method->currency ?: optional($settings)->s_currency ?: 'USD');
        if (session('withdrawal_type') !== $methodtype || (string) session('withdrawal_asset') !== (string) $asset) {
            session()->forget(['withdraw_method_id', 'withdrawal_type', 'withdrawal_asset']);
            return redirect()->route('withdrawals')
                ->with('message', 'Your withdrawal selection expired. Please select the asset and payment method again.');
        }

        return view('user.withdraw', [
            'title' => 'Complete Withdrawal Request',
            'method' => $method,
            'methodtype' => $methodtype,
            'asset' => $asset,
            'default' => $method->defaultpay === 'yes',
        ]);
    }

    public function getotp()
    {
        $code = $this->RandomStringGenerator(5);

        $user = Auth::user();
        User::where('id', $user->id)->update([
            'withdrawotp' => $code,
        ]);

        $message = "You have initiated a withdrawal request, use the OTP: $code to complete your request.";
        $subject = "OTP Request";
        Mail::bcc($user->email)->send(new NewNotification($message, $subject, $user->name));

        return redirect()->back()
            ->with('success', 'Action Successful! OTP has been sent to your email');
    }

    public function verifyWithdrawalCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'method' => 'required|string',
        ]);

        $user = Auth::user();

        if ($user->withdrawal_code_enabled && $user->withdrawal_code == $request->code) {
            session()->put('paymentmethod', $request->method);

            if ($user->tax_code_enabled && !empty($user->tax_code)) {
                session()->put('withdrawal_step', 'withdrawal_verified');
            } else {
                session()->put('withdrawal_step', 'tax_verified');
            }

            return redirect()->route('withdrawfunds')->with('success', $user->withdrawal_code_name . ' verified successfully!');
        }

        $codeName = $user->withdrawal_code_name ?? 'Withdrawal Code';
        return redirect()->back()->with('message', "You have entered an incorrect {$codeName} . Please contact support for the correct code for this transaction.");
    }

    public function verifyTaxCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'method' => 'required|string',
        ]);

        $user = Auth::user();

        if ($user->tax_code_enabled && $user->tax_code == $request->code) {
            session()->put('paymentmethod', $request->method);
            session()->put('withdrawal_step', 'tax_verified');

            return redirect()->route('withdrawfunds')->with('success', $user->tax_code_name . ' verified successfully!');
        }

        $codeName = $user->tax_code_name ?? 'Tax Code';
        return redirect()->back()->with('message', "You have entered an incorrect {$codeName} . Please contact support for the correct code for this transaction.");
    }

    /**
     * Process a withdrawal request.
     *
     * Every financial rule is enforced on the server: the selected method must
     * exist, be enabled and support withdrawals, belong to the chosen
     * asset/currency, honour min/max limits and fees, and never exceed the
     * user's available balance. The balance is reserved inside a locked
     * database transaction and duplicate submissions are rejected.
     */
    public function completewithdrawal(Request $request)
    {
        $user = Auth::user();
        $settings = Settings::where('id', '1')->first();

        if ($user->sendotpemail == "Yes" && $request->otpcode != $user->withdrawotp) {
            return redirect()->back()->with('message', 'OTP is incorrect, please recheck the code');
        }

        if ($settings->enable_kyc == "yes" && $user->account_verify != "Verified") {
            return redirect()->back()->with('message', 'Your account must be verified before you can make a withdrawal.');
        }

        $validated = $request->validate([
            'method_id' => 'required|integer',
            'method_type' => 'required|in:crypto,currency',
            'asset' => 'required|string|max:64',
            'amount' => 'required|numeric|gt:0|max:1000000000',
            'submission_key' => 'required|string|max:64',
            'wallet_address' => 'nullable|string|max:512',
            'bank_name' => 'nullable|string|max:255',
            'account_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'swift_code' => 'nullable|string|max:255',
        ]);

        $method = Wdmethod::find($validated['method_id']);
        if (!$method
            || strtolower(trim((string) $method->status)) !== 'enabled'
            || !in_array($method->type, ['withdrawal', 'both'], true)) {
            return redirect()->back()->with('message', 'The selected withdrawal method is not available.');
        }

        $methodtype = strtolower(trim((string) $method->methodtype));
        if (!in_array($methodtype, ['crypto', 'currency'], true)) {
            return redirect()->route('withdrawals')->with('message', 'The configured withdrawal asset type is invalid.');
        }
        $isCrypto = $methodtype === 'crypto';
        $actualType = $isCrypto ? 'crypto' : 'currency';
        $actualAsset = $isCrypto ? $method->name : ($method->currency ?: (optional($settings)->s_currency ?: 'USD'));
        if ($actualType !== $validated['method_type']
            || !hash_equals((string) $actualAsset, (string) $validated['asset'])
            || (int) session('withdraw_method_id') !== (int) $method->id
            || session('withdrawal_type') !== $actualType
            || (string) session('withdrawal_asset') !== (string) $actualAsset) {
            return redirect()->route('withdrawals')->with('message', 'The selected withdrawal asset changed. Please choose the method again.');
        }
        $amount = round((float) $validated['amount'], 8);

        // Calculate charges on the backend.
        if (strtolower((string) $method->charges_type) === 'percentage') {
            $charges = $amount * ((float) $method->charges_amount) / 100;
        } else {
            $charges = (float) $method->charges_amount;
        }
        $charges = round($charges, 2);
        $toWithdraw = round($amount + $charges, 2);

        // Enforce minimum and maximum withdrawal limits.
        if ($method->minimum !== null && $method->minimum !== '' && $amount < (float) $method->minimum) {
            return redirect()->back()->with('message', "Sorry, the minimum amount you can withdraw is {$settings->currency}{$method->minimum}.");
        }
        if ($method->maximum !== null && $method->maximum !== '' && (float) $method->maximum > 0 && $amount > (float) $method->maximum) {
            return redirect()->back()->with('message', "Sorry, the maximum amount you can withdraw is {$settings->currency}{$method->maximum}.");
        }

        // Validate and collect destination details based on the method type.
        if ($isCrypto) {
            $walletAddress = trim((string) ($validated['wallet_address'] ?? ''));
            if ($walletAddress === '') {
                return redirect()->back()->with('message', 'Please enter your destination wallet address.');
            }
            $details = [
                'wallet_address' => $walletAddress,
                'network' => $method->network ?: null,
            ];
        } else {
            $bankName = trim((string) ($validated['bank_name'] ?? ''));
            $accountName = trim((string) ($validated['account_name'] ?? ''));
            $accountNumber = trim((string) ($validated['account_number'] ?? ''));
            if ($bankName === '' || $accountName === '' || $accountNumber === '') {
                return redirect()->back()->with('message', 'Please provide your bank name, account name and account number.');
            }
            $details = [
                'bank_name' => $bankName,
                'account_name' => $accountName,
                'account_number' => $accountNumber,
                'swift_code' => trim((string) ($validated['swift_code'] ?? '')),
            ];
        }

        $paydetails = json_encode($details);

        // Auto payout via CoinPayments for supported crypto when enabled.
        if ($isCrypto
            && $settings->withdrawal_option == "auto"
            && in_array($method->name, ['Bitcoin', 'Litecoin', 'Ethereum', 'USDT'], true)) {
            return $this->cpwithdraw($amount, $this->coinSymbolFor($method), $details['wallet_address'], $user->id, $toWithdraw);
        }

        try {
            DB::transaction(function () use ($user, $method, $amount, $charges, $toWithdraw, $paydetails, $validated, $isCrypto, $settings) {
                $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

                if ((float) $lockedUser->account_bal < $toWithdraw) {
                    throw new \DomainException('Sorry, your account balance is insufficient for this request.');
                }

                if (Withdrawal::where('submission_key', $validated['submission_key'])->exists()) {
                    throw new \DomainException('This withdrawal request has already been submitted.');
                }

                // Reserve funds immediately; the admin approval workflow refunds
                // on rejection.
                $lockedUser->account_bal = round((float) $lockedUser->account_bal - $toWithdraw, 2);
                $lockedUser->withdrawotp = null;
                $lockedUser->save();

                Withdrawal::create([
                    'user' => $lockedUser->id,
                    'amount' => $amount,
                    'to_deduct' => $toWithdraw,
                    'charges' => $charges,
                    'payment_mode' => $method->name,
                    'method_id' => $method->id,
                    'asset' => $isCrypto ? $method->name : null,
                    'currency' => $isCrypto ? null : ($method->currency ?: $settings->s_currency),
                    'network' => $isCrypto ? $method->network : null,
                    'paydetails' => $paydetails,
                    'status' => 'Pending',
                    'submission_key' => $validated['submission_key'],
                ]);
            });
        } catch (\DomainException $e) {
            return redirect()->back()->with('message', $e->getMessage());
        }

        // Clear the guided-withdrawal session.
        session()->forget(['withdrawal_step', 'withdraw_method_id', 'withdrawal_type', 'withdrawal_asset']);

        try {
            $this->notificationService->sendWithdrawalRequestNotification(
                $user->id,
                $amount,
                $method->name,
                'pending'
            );
        } catch (\Exception $e) {
            Log::error('Failed to send withdrawal request notification', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'amount' => $amount,
            ]);
        }

        try {
            Mail::to($settings->contact_email)->send(new WithdrawalStatus(Withdrawal::where('submission_key', $validated['submission_key'])->first(), $user, 'Withdrawal Request', true));
        } catch (\Exception $e) {
            Log::error('Failed to send withdrawal notification email to admin', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'admin_email' => $settings->contact_email,
                'amount' => $amount,
            ]);
        }

        try {
            Mail::to($user->email)->send(new WithdrawalStatus(Withdrawal::where('submission_key', $validated['submission_key'])->first(), $user, 'Successful Withdrawal Request'));
        } catch (\Exception $e) {
            Log::error('Failed to send withdrawal confirmation email to user', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'user_email' => $user->email,
                'amount' => $amount,
            ]);
        }

        return redirect()->route('withdrawals')
            ->with('success', 'Action Successful! Please wait while we process your request.');
    }

    /**
     * Map a payment-method name to the CoinPayments currency symbol.
     */
    private function coinSymbolFor(Wdmethod $method): string
    {
        $map = [
            'Bitcoin' => 'BTC',
            'Ethereum' => 'ETH',
            'Litecoin' => 'LTC',
            'USDT' => 'USDT.TRC20',
            'Solana' => 'SOL',
            'BUSD' => 'BUSD',
        ];

        return $map[$method->name] ?? strtoupper($method->name);
    }

    function RandomStringGenerator($n)
    {
        $generated_string = "";
        $domain = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
        $len = strlen($domain);
        for ($i = 0; $i < $n; $i++) {
            $index = rand(0, $len - 1);
            $generated_string = $generated_string . $domain[$index];
        }
        return $generated_string;
    }
}
