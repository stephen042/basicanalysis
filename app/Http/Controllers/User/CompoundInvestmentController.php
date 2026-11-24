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

class CompoundInvestmentController extends Controller
{
    public function create()
    {
        $methods = $this->enabledUsdtMethods();

        return view('user.compound-investment', [
            'title' => 'Start Compounding',
            'methods' => $methods,
            'performanceRate' => (float) (Auth::user()->trading_profit_rate ?: 0),
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

        if (!SchemaInspector::hasColumn('compound_investments', 'submission_key')) {
            return back()
                ->with('error', 'Compound investment database upgrade is pending. No payment was taken; please retry after the upgrade is applied.')
                ->withInput();
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
                    'submission_key' => $idempotencyKey,
                    'amount' => $amount,
                    'duration_months' => $data['duration_months'],
                    'monthly_return' => 27.8,
                    // Source this from the existing account setting; never trust
                    // a client-supplied performance rate.
                    'performance_rate' => (float) ($user->trading_profit_rate ?: 0),
                    'recurring_amount' => round((float) ($data['recurring_amount'] ?? 0), 2),
                    'recurring_frequency' => $data['recurring_frequency'],
                    'deposit_status' => $paymentSource === 'subscription_balance' ? 'approved' : 'pending',
                    'status' => $paymentSource === 'subscription_balance' ? 'active' : 'pending',
                    'approved_at' => $paymentSource === 'subscription_balance' ? $now : null,
                    'start_date' => $paymentSource === 'subscription_balance' ? $now : null,
                    'expected_completion_at' => $paymentSource === 'subscription_balance'
                        ? $now->copy()->addMonths((int) $data['duration_months'])
                        : null,
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
                ->with('success', 'Investment paid from Subscription Balance and activated.');
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

        return view('user.compound-investment-show', [
            'title' => 'Compound Investment',
            'investment' => $investment,
            'currency' => $this->investmentCurrency(),
        ]);
    }

    public function history()
    {
        return view('user.compound-investment-history', [
            'title' => 'Compound Investment History',
            'investments' => CompoundInvestment::where('user_id', Auth::id())
                ->with('topups')
                ->latest()
                ->paginate(15),
            'currency' => '$',
        ]);
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
            ->whereIn('type', ['deposit', 'both'])
            ->where('status', 'enabled')
            ->orderBy('name')
            ->get();
    }

    private function isUsdtMethod($method): bool
    {
        return $method && strtoupper(trim((string) $method->name)) === 'USDT';
    }

    private function investmentCurrency(): string
    {
        return '$';
    }
}
