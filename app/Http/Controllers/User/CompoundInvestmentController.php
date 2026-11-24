<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\CompoundInvestment;
use App\Models\Settings;
use App\Models\Wdmethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CompoundInvestmentController extends Controller
{
    public function create()
    {
        $methods = Wdmethod::whereIn('type', ['deposit', 'both'])
            ->where('status', 'enabled')
            ->orderBy('name')
            ->get();

        return view('user.compound-investment', [
            'title' => 'Start Compounding',
            'methods' => $methods,
            'performanceRate' => (float) (Auth::user()->trading_profit_rate ?: 0),
            'currency' => optional(Settings::find(1))->currency ?: '$',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:1|max:1000000000',
            'duration_months' => 'required|integer|in:1,3,6,12',
            'recurring_amount' => 'nullable|numeric|min:0|max:1000000000',
            'recurring_frequency' => 'required|in:daily,weekly',
            'payment_method_id' => 'required|exists:wdmethods,id',
            'submission_key' => 'required|string|max:64',
        ]);

        $method = Wdmethod::whereKey($data['payment_method_id'])
            ->whereIn('type', ['deposit', 'both'])
            ->where('status', 'enabled')
            ->firstOrFail();

        $cacheKey = 'compound-investment-submit:' . Auth::id() . ':' . sha1($data['submission_key']);
        if (!Cache::add($cacheKey, 1, now()->addMinutes(15))) {
            return back()->with('message', 'This investment request was already submitted.')->withInput();
        }

        try {
            $investment = CompoundInvestment::create([
                'user_id' => Auth::id(),
                'payment_method_id' => $method->id,
                'payment_method' => $method->name,
                'amount' => $data['amount'],
                'duration_months' => $data['duration_months'],
                'monthly_return' => 27.8,
                // Source this from the existing account setting; never trust
                // a client-supplied performance rate.
                'performance_rate' => (float) (Auth::user()->trading_profit_rate ?: 0),
                'recurring_amount' => $data['recurring_amount'] ?: 0,
                'recurring_frequency' => $data['recurring_frequency'],
                'deposit_status' => 'pending',
                'status' => 'pending',
            ]);
        } catch (\Throwable $e) {
            Cache::forget($cacheKey);
            throw $e;
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

        $methods = Wdmethod::whereIn('type', ['deposit', 'both'])
            ->where('status', 'enabled')
            ->orderBy('name')
            ->get();
        if ($investment->payment_proof && $investment->payment_method_id) {
            $savedMethod = Wdmethod::find($investment->payment_method_id);
            if ($savedMethod && !$methods->contains('id', (int) $savedMethod->id)) {
                $methods->push($savedMethod);
            }
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
            'currency' => optional(Settings::find(1))->currency ?: '$',
        ]);
    }

    public function uploadProof(Request $request, $id)
    {
        $data = $request->validate([
            'payment_proof' => 'required|image|max:5120',
            'payment_method_id' => 'required|exists:wdmethods,id',
        ]);

        $method = Wdmethod::whereKey($data['payment_method_id'])
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
            'currency' => optional(Settings::find(1))->currency ?: '$',
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
            'currency' => optional(Settings::find(1))->currency ?: '$',
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
}
