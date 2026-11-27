<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CompoundInvestment;
use App\Models\Tp_Transaction;
use App\Models\Wdmethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use App\Support\SchemaInspector;
use App\Models\CompoundInvestmentContribution;
use Illuminate\Support\Str;
use App\Models\Settings;
use Illuminate\Support\Facades\Mail;

class CompoundInvestmentController extends Controller
{
    public function create()
    {
        $methods = $this->enabledUsdtMethods();

        return view('user.compound-investment', [
            'title' => 'Start Compounding',
            'methods' => $methods,
            'performanceRate' => (float) (Auth::user()->trading_profit_rate ?: 0),
            'monthlyReturn' => (float) config('compound.monthly_return', 27.8),
            'currency' => '$',
            'subscriptionBalance' => (float) Auth::user()->account_bal,
            'subscriptionBalanceCurrency' => Auth::user()->currency ?: '$',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:1|max:1000000000',
            'duration_months' => 'required|integer|in:1,3,6,12',
            'recurring_amount' => 'nullable|numeric|min:0|max:1000000000',
            'recurring_frequency' => 'required|in:daily,weekly',
            'payment_source' => 'required|in:subscription_balance,direct_deposit',
            'payment_method_id' => 'nullable|required_if:payment_source,direct_deposit|integer|exists:wdmethods,id',
            'submission_key' => 'required|string|max:64',
        ]);

        $requiredInvestmentColumns = ['submission_key', 'payment_source', 'currency', 'next_recurring_due_at'];
        foreach ($requiredInvestmentColumns as $requiredColumn) {
            if (!SchemaInspector::hasColumn('compound_investments', $requiredColumn)) {
                return back()
                    ->with('error', 'Compound investment database upgrade is pending. No payment was taken; please retry after the upgrade is applied.')
                    ->withInput();
            }
        }

        $paymentSource = $data['payment_source'];
        $paymentMethodId = $paymentSource === 'direct_deposit'
            ? (int) $data['payment_method_id']
            : null;
        $amountCents = (int) round((float) $data['amount'] * 100);
        $amount = $amountCents / 100;

        $idempotencyKey = hash('sha256', Auth::id() . ':' . $data['submission_key']);
        $cacheKey = 'compound-investment-submit:' . $idempotencyKey;
        if (!Cache::add($cacheKey, 1, now()->addMinutes(15))) {
            return back()->with('message', 'This investment request was already submitted.')->withInput();
        }

        try {
            $investment = DB::transaction(function () use ($data, $paymentSource, $paymentMethodId, $idempotencyKey, $amountCents, $amount) {
                $user = \App\Models\User::whereKey(Auth::id())->lockForUpdate()->firstOrFail();
                $method = null;

                if ($paymentSource === 'direct_deposit') {
                    $method = Wdmethod::whereKey($paymentMethodId)
                        ->whereRaw('LOWER(TRIM(name)) = ?', ['usdt'])
                        ->whereRaw('LOWER(TRIM(methodtype)) = ?', ['crypto'])
                        ->whereIn('type', ['deposit', 'both'])
                        ->where('status', 'enabled')
                        ->lockForUpdate()
                        ->firstOrFail();
                } else {
                    // The dashboard's "Subscription Balance" card reads
                    // users.account_bal. Do not use total_balance (which adds
                    // ROI), trading_balance, demo_balance, or ROI itself.
                    $availableCents = (int) round((float) $user->account_bal * 100);
                    if ($availableCents < $amountCents) {
                        throw new \DomainException(
                            'Insufficient Subscription Balance. Fund your account or choose Direct Deposit.'
                        );
                    }
                }

                $now = now();
                $investment = CompoundInvestment::create([
                    'user_id' => $user->id,
                    'payment_method_id' => $method ? $method->id : null,
                    'payment_method' => $method ? $method->name : 'Subscription Balance',
                    'payment_source' => $paymentSource,
                    'submission_key' => $idempotencyKey,
                    'amount' => $amount,
                    'currency' => 'USD',
                    'duration_months' => $data['duration_months'],
                    'monthly_return' => (float) config('compound.monthly_return', 27.8),
                    // Source this from the existing account setting; never trust
                    // a client-supplied performance rate.
                    'performance_rate' => (float) ($user->trading_profit_rate ?: 0),
                    'recurring_amount' => round((float) ($data['recurring_amount'] ?? 0), 2),
                    'recurring_frequency' => $data['recurring_frequency'],
                    // All applications remain pending until an administrator
                    // explicitly approves them, including balance-funded ones.
                    'deposit_status' => 'pending',
                    'status' => 'pending',
                ]);

                if ($paymentSource === 'subscription_balance') {
                    $user->account_bal = ($availableCents - $amountCents) / 100;
                    $user->save();

                    Tp_Transaction::create([
                        'user' => $user->id,
                        'plan' => 'Compound investment #' . $investment->id . ' paid from Subscription Balance',
                        'amount' => -$amount,
                        'type' => 'Compound Investment',
                    ]);
                }

                return $investment;
            });
        } catch (\DomainException $e) {
            Cache::forget($cacheKey);
            return back()->with('error', $e->getMessage())->withInput();
        } catch (QueryException $e) {
            Cache::forget($cacheKey);
            $driverCode = isset($e->errorInfo[1]) ? (int) $e->errorInfo[1] : 0;
            if ((string) $e->getCode() === '23000' || $driverCode === 1062) {
                return back()->with('message', 'This investment request was already submitted.')->withInput();
            }
            throw $e;
        } catch (\Throwable $e) {
            Cache::forget($cacheKey);
            throw $e;
        }

        if ($paymentSource === 'subscription_balance') {
            return redirect()->route('compound-investment.show', $investment)
                ->with('success', 'Your balance has been reserved. The investment will activate after admin approval.');
        }

        return redirect()->route('compound-investment.payment', $investment)
            ->with('success', 'Your request is saved. Complete the payment and upload its receipt for admin approval.');
    }

    public function payment($id)
    {
        $investment = CompoundInvestment::where('user_id', Auth::id())
            ->with('topups')
            ->findOrFail($id);

        if ($investment->status !== 'pending') {
            return redirect()->route('compound-investment.show', $investment);
        }

        $methods = $this->enabledUsdtMethods();
        $savedMethod = null;
        $acceptedSavedMethod = false;
        if ($investment->payment_proof && $investment->payment_method_id) {
            $savedMethod = Wdmethod::find($investment->payment_method_id);
            $acceptedSavedMethod = $this->isUsdtMethod($savedMethod)
                && in_array($savedMethod->type, ['deposit', 'both'], true);
            if ($acceptedSavedMethod
                && !$methods->contains('id', (int) $savedMethod->id)) {
                $methods->push($savedMethod);
            }
        }
        $legacyUnsupportedProof = $investment->payment_proof && !$acceptedSavedMethod;
        if ($legacyUnsupportedProof) {
            $methods = collect();
        }
        $selectedMethodId = (string) ($investment->payment_proof
            ? $investment->payment_method_id
            : request()->query('method', $investment->payment_method_id));
        if (!$methods->contains('id', (int) $selectedMethodId)) {
            $selectedMethodId = (string) optional($methods->first())->id;
        }
        $paymentMethods = [];
        foreach ($methods as $method) {
            $paymentMethods[(string) $method->id] = $this->paymentInstructions($method);
        }

        return view('user.compound-investment-payment', [
            'title' => 'Investment Payment',
            'investment' => $investment,
            'paymentMethods' => $paymentMethods,
            'selectedMethodId' => $selectedMethodId,
            'currency' => '$',
            'legacyUnsupportedProof' => $legacyUnsupportedProof,
        ]);
    }

    public function uploadProof(Request $request, $id)
    {
        $data = $request->validate([
            'payment_proof' => 'required|image|max:5120',
            'payment_method_id' => 'required|exists:wdmethods,id',
        ]);

        $method = Wdmethod::whereKey($data['payment_method_id'])
            ->whereRaw('LOWER(TRIM(name)) = ?', ['usdt'])
            ->whereRaw('LOWER(TRIM(methodtype)) = ?', ['crypto'])
            ->whereIn('type', ['deposit', 'both'])
            ->where('status', 'enabled')
            ->firstOrFail();
        $investment = CompoundInvestment::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->findOrFail($id);
        if ($investment->payment_proof) {
            return back()->with('message', 'A receipt has already been submitted. Your request is awaiting admin review.');
        }

        $path = $request->file('payment_proof')->store('compound-investment-proofs', 'public');
        try {
            DB::transaction(function () use ($investment, $path, $method) {
                $locked = CompoundInvestment::where('user_id', Auth::id())
                    ->whereKey($investment->id)
                    ->lockForUpdate()
                    ->firstOrFail();
                if ($locked->status !== 'pending' || $locked->deposit_status !== 'pending') {
                    throw new \DomainException('This payment request is no longer awaiting review.');
                }
                if ($locked->payment_proof) {
                    throw new \DomainException('A receipt has already been submitted for this investment.');
                }
                $locked->payment_method_id = $method->id;
                $locked->payment_method = $method->name;
                $locked->payment_proof = $path;
                $locked->save();
            });
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($path);
            if ($e instanceof \DomainException) {
                return back()->with('message', $e->getMessage());
            }
            throw $e;
        }

        return redirect()->route('compound-investment.payment', $investment)
            ->with('success', 'Payment receipt submitted. Your investment will activate after admin approval.');
    }

    public function show($id)
    {
        $investment = CompoundInvestment::where('user_id', Auth::id())
            ->with(['topups.admin', 'paymentMethod'])
            ->findOrFail($id);
        if (SchemaInspector::hasTable('compound_investment_contributions')) {
            $investment->load(['contributions.deposit', 'contributions.reviewer']);
        } else {
            $investment->setRelation('contributions', collect());
        }

        return view('user.compound-investment-show', [
            'title' => 'Compound Investment',
            'investment' => $investment,
            'currency' => $this->investmentCurrency(),
        ]);
    }

    public function history()
    {
        $query = CompoundInvestment::where('user_id', Auth::id())->with(['topups']);
        if (SchemaInspector::hasTable('compound_investment_contributions')) {
            $query->with('contributions');
        }
        $investments = $query->latest()->paginate(15);
        if (!SchemaInspector::hasTable('compound_investment_contributions')) {
            $investments->getCollection()->each(function ($investment) {
                $investment->setRelation('contributions', collect());
            });
        }

        // Keep every active recurring plan visible as a prominent top-up card,
        // independent of the paginated history table below it.
        $activeRecurringQuery = CompoundInvestment::where('user_id', Auth::id())
            ->where('status', 'active')
            ->where('deposit_status', 'approved')
            ->where('recurring_amount', '>', 0)
            ->orderBy('next_recurring_due_at');
        if (SchemaInspector::hasTable('compound_investment_contributions')) {
            $activeRecurringQuery->with('contributions');
        }
        $activeRecurring = $activeRecurringQuery->get();
        if (!SchemaInspector::hasTable('compound_investment_contributions')) {
            $activeRecurring->each(function ($investment) {
                $investment->setRelation('contributions', collect());
            });
        }

        return view('user.compound-investment-history', [
            'title' => 'Compound Investment History',
            'investments' => $investments,
            'activeRecurring' => $activeRecurring,
            'currency' => '$',
        ]);
    }

    public function recurringTopup($id)
    {
        if (!SchemaInspector::hasTable('compound_investment_contributions')) {
            return redirect()->route('compound-investment.history')
                ->with('error', 'Recurring contribution upgrades are pending. Please try again after maintenance.');
        }

        $investment = CompoundInvestment::where('user_id', Auth::id())
            ->with(['contributions.deposit'])
            ->findOrFail($id);

        $dueAt = $investment->next_recurring_due_at ?: $investment->next_recurring_due;
        $pendingPayment = $investment->contributions
            ->first(function ($contribution) {
                return $contribution->status === 'pending'
                    && ($contribution->deposit_id || $contribution->balance_debited);
            });
        if ($pendingPayment) {
            return redirect()->route('compound-investment.show', $investment)
                ->with('info', 'Your recurring contribution payment is already submitted and awaiting admin review.');
        }
        if ($investment->status !== 'active'
            || $investment->deposit_status !== 'approved'
            || (float) $investment->recurring_amount <= 0) {
            return redirect()->route('compound-investment.show', $investment)
                ->with('error', 'This investment is not eligible for a recurring contribution.');
        }

        return view('user.compound-investment-recurring', [
            'title' => 'Make Recurring Top-up',
            'investment' => $investment,
            'methods' => $this->enabledCryptoMethods(),
            'currency' => '$',
            'dueAt' => $dueAt ?: now(),
            'subscriptionBalance' => (float) Auth::user()->account_bal,
            'subscriptionBalanceCurrency' => Auth::user()->currency ?: '$',
        ]);
    }

    public function startRecurringTopup(Request $request, $id)
    {
        $data = $request->validate([
            'payment_source' => 'nullable|in:direct_deposit,subscription_balance',
            'payment_method_id' => 'nullable|required_if:payment_source,direct_deposit|integer|exists:wdmethods,id',
        ]);
        $paymentSource = $data['payment_source']
            ?? (!empty($data['payment_method_id']) ? 'direct_deposit' : null);
        if (!$paymentSource) {
            return back()->withErrors(['payment_source' => 'Choose Deposit or Subscription Balance.']);
        }

        $requiredContributionColumns = ['payment_method', 'balance_debited'];
        foreach ($requiredContributionColumns as $column) {
            if (!SchemaInspector::hasColumn('compound_investment_contributions', $column)) {
                return redirect()->route('compound-investment.show', $id)
                    ->with('error', 'Recurring payment database upgrade is pending. No payment was taken.');
            }
        }

        $method = null;
        if ($paymentSource === 'direct_deposit') {
            $method = Wdmethod::whereKey($data['payment_method_id'])
                ->whereRaw('LOWER(TRIM(methodtype)) = ?', ['crypto'])
                ->whereIn('type', ['deposit', 'both'])
                ->whereRaw('LOWER(TRIM(status)) = ?', ['enabled'])
                ->firstOrFail();
        }

        try {
            $contribution = DB::transaction(function () use ($id, $method, $paymentSource) {
                $investment = CompoundInvestment::where('user_id', Auth::id())
                    ->whereKey($id)
                    ->lockForUpdate()
                    ->firstOrFail();
                $dueAt = $investment->next_recurring_due_at ?: $investment->next_recurring_due;
                // The schedule is a reminder, not a payment lock. Early
                // submissions are assigned to the upcoming scheduled period.
                $scheduledFor = $dueAt ?: now();

                if ($investment->status !== 'active'
                    || $investment->deposit_status !== 'approved'
                    || (float) $investment->recurring_amount <= 0) {
                    throw new \DomainException('This investment is not currently eligible for a recurring contribution.');
                }

                $pending = CompoundInvestmentContribution::where('compound_investment_id', $investment->id)
                    ->where('scheduled_for', $scheduledFor)
                    ->where('status', 'pending')
                    ->latest('id')
                    ->first();

                if ($pending && $pending->deposit_id) {
                    throw new \DomainException('A recurring contribution for this due period has already been submitted and is awaiting admin review.');
                }

                if ($pending && $pending->balance_debited) {
                    throw new \DomainException('A Subscription Balance contribution for this due period is already awaiting admin review.');
                }

                $contribution = $pending ?: CompoundInvestmentContribution::create([
                    'compound_investment_id' => $investment->id,
                    'idempotency_key' => Str::random(64),
                    'amount' => $investment->recurring_amount,
                    'currency' => 'USD',
                    'scheduled_for' => $scheduledFor,
                    'status' => 'pending',
                ]);

                if ($paymentSource === 'subscription_balance') {
                    $user = \App\Models\User::whereKey(Auth::id())->lockForUpdate()->firstOrFail();
                    $amountCents = (int) round((float) $contribution->amount * 100);
                    $availableCents = (int) round((float) $user->account_bal * 100);
                    if ($availableCents < $amountCents) {
                        throw new \DomainException(
                            'Insufficient Subscription Balance. Fund your account or choose Deposit.'
                        );
                    }

                    $user->account_bal = ($availableCents - $amountCents) / 100;
                    $user->save();
                    Tp_Transaction::create([
                        'user' => $user->id,
                        'plan' => 'Compound investment recurring contribution #' . $investment->id,
                        'amount' => -($amountCents / 100),
                        'type' => 'Compound Investment',
                    ]);
                    $contribution->payment_method = 'Subscription Balance';
                    $contribution->balance_debited = true;
                    $contribution->save();
                } else {
                    $contribution->payment_method = 'Deposit';
                    $contribution->balance_debited = false;
                    $contribution->save();
                }

                return $contribution;
            });
        } catch (\DomainException $e) {
            return redirect()->route('compound-investment.show', $id)->with('error', $e->getMessage());
        }

        if ($paymentSource === 'subscription_balance') {
            $settings = Settings::find(1);
            if ($settings && filter_var($settings->contact_email, FILTER_VALIDATE_EMAIL)) {
                try {
                    Mail::raw(
                        "New compound investment recurring top-up awaiting review\n"
                        . "User: " . Auth::user()->name . " (" . Auth::user()->email . ")\n"
                        . "Investment reference: #{$id}\n"
                        . "Amount: $" . number_format((float) $contribution->amount, 2) . "\n"
                        . "Payment method: Subscription Balance\n"
                        . "Submitted: " . now()->format('M j, Y H:i'),
                        function ($mail) use ($settings) {
                            $mail->to($settings->contact_email)
                                ->subject('Recurring compound contribution awaiting approval');
                        }
                    );
                } catch (\Throwable $e) {
                    report($e);
                }
            }

            return redirect()->route('compound-investment.show', $id)
                ->with('success', 'Your Subscription Balance payment was submitted and is awaiting admin approval.');
        }

        $request->session()->put('compound_contribution_id', $contribution->id);
        $request->session()->put('amount', (float) $contribution->amount);
        $request->session()->put('payment_mode', $method->name);
        $request->session()->put('payment_method_id', $method->id);
        $request->session()->put('payment_network', $method->network);
        $request->session()->put('intent', null);

        return redirect()->route('payment')->with('info', 'Submit the payment proof to send this contribution for admin review.');
    }

    private function paymentInstructions($method)
    {
        if (!$method) {
            return [];
        }

        $address = $method->wallet_address ?: env($method->name);
        $barcode = null;
        if ($method->barcode && Storage::disk('public')->exists($method->barcode)) {
            $barcode = Storage::disk('public')->url($method->barcode);
        }

        return [
            'name' => $method->name,
            'type' => $method->methodtype ?: 'payment',
            'image' => $method->img_url,
            'symbol' => strtoupper(substr((string) $method->name, 0, 1)),
            'address' => $address,
            'network' => $method->network,
            'barcode' => $barcode,
            'bank' => $method->bankname,
            'account_name' => $method->account_name,
            'account_number' => $method->account_number,
            'swift' => $method->swift_code,
            'instructions' => $method->duration,
        ];
    }

    private function enabledUsdtMethods()
    {
        return Wdmethod::whereRaw('LOWER(TRIM(name)) = ?', ['usdt'])
            ->whereRaw('LOWER(TRIM(methodtype)) = ?', ['crypto'])
            ->whereIn('type', ['deposit', 'both'])
            ->where('status', 'enabled')
            ->orderBy('name')
            ->get();
    }

    private function enabledCryptoMethods()
    {
        return Wdmethod::whereRaw('LOWER(TRIM(methodtype)) = ?', ['crypto'])
            ->whereIn('type', ['deposit', 'both'])
            ->where('status', 'enabled')
            ->orderBy('name')
            ->orderBy('network')
            ->get();
    }

    private function isUsdtMethod($method): bool
    {
        return $method
            && strtoupper(trim((string) $method->name)) === 'USDT'
            && strtolower(trim((string) $method->methodtype)) === 'crypto';
    }

    private function investmentCurrency(): string
    {
        return '$';
    }
}
