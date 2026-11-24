<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompoundInvestment;
use App\Models\CompoundInvestmentTopup;
use App\Models\Settings;
use App\Models\Tp_Transaction;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class CompoundInvestmentController extends Controller
{
    public function index()
    {
        return view('admin.compound-investments.index', [
            'title' => 'Compound Investments',
            'investments' => CompoundInvestment::with(['user', 'topups'])
                ->latest()
                ->paginate(25),
            'currency' => optional(Settings::find(1))->currency ?: '$',
        ]);
    }

    public function show($id)
    {
        $investment = CompoundInvestment::with(['user', 'topups.admin', 'paymentMethod'])
            ->findOrFail($id);
        $method = $investment->paymentMethod;
        $address = $method ? ($method->wallet_address ?: env($method->name)) : null;
        $barcode = null;
        if ($method && $method->barcode && Storage::disk('public')->exists($method->barcode)) {
            $barcode = Storage::disk('public')->url($method->barcode);
        }

        return view('admin.compound-investments.show', [
            'title' => 'Compound Investment Request',
            'investment' => $investment,
            'payment' => [
                'address' => $address,
                'network' => $method ? $method->network : null,
                'barcode' => $barcode,
                'bank' => $method ? $method->bankname : null,
                'account_name' => $method ? $method->account_name : null,
                'account_number' => $method ? $method->account_number : null,
                'swift' => $method ? $method->swift_code : null,
                'instructions' => $method ? $method->duration : null,
            ],
            'currency' => optional(Settings::find(1))->currency ?: '$',
        ]);
    }

    public function decide(Request $request, CompoundInvestment $investment)
    {
        if (!$this->hasDecisionColumns()) {
            return back()->with('message', 'Compound investment database fields are missing. Apply SQL/compound_investment_workflow_upgrade.sql first.');
        }

        $data = $request->validate([
            'decision' => 'required|in:approve,reject',
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        try {
            DB::transaction(function () use ($investment, $data) {
                $locked = CompoundInvestment::whereKey($investment->id)
                    ->lockForUpdate()
                    ->firstOrFail();
                if ($locked->status !== 'pending' || $locked->deposit_status !== 'pending') {
                    throw new \DomainException('This request has already been reviewed.');
                }
                if ($data['decision'] === 'approve' && !$locked->payment_proof) {
                    throw new \DomainException('A payment receipt is required before approval.');
                }

                if ($data['decision'] === 'approve') {
                    $start = now();
                    $locked->deposit_status = 'approved';
                    $locked->status = 'active';
                    $locked->approved_at = $start;
                    $locked->start_date = $start;
                    $locked->expected_completion_at = $start->copy()->addMonths((int) $locked->duration_months);
                    $locked->rejected_at = null;
                    $locked->rejection_reason = null;
                } else {
                    $locked->deposit_status = 'rejected';
                    $locked->status = 'rejected';
                    $locked->rejected_at = now();
                    $locked->rejection_reason = $data['rejection_reason'] ?: null;
                }
                $locked->save();
            });
        } catch (\DomainException $e) {
            return back()->with('message', $e->getMessage());
        }

        return redirect()->route('admin.compound-investments.show', $investment)
            ->with('success', $data['decision'] === 'approve'
                ? 'Payment approved and investment activated.'
                : 'Investment request rejected.');
    }

    public function topup(Request $request, CompoundInvestment $investment)
    {
        if (!$this->tableExists('compound_investment_topups')
            || !$this->columnExists('compound_investment_topups', 'admin_id')
            || !$this->columnExists('compound_investment_topups', 'idempotency_key')) {
            return back()->with('message', 'Compound top-up audit fields are missing. Apply SQL/compound_investment_workflow_upgrade.sql first.');
        }

        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:1000000000',
            'note' => 'nullable|string|max:255',
            'idempotency_key' => 'required|string|max:64',
        ]);
        $amount = round((float) $data['amount'], 2);
        $adminId = Auth::guard('admin')->id();

        try {
            $userId = DB::transaction(function () use ($investment, $data, $amount, $adminId) {
                $locked = CompoundInvestment::whereKey($investment->id)
                    ->lockForUpdate()
                    ->firstOrFail();
                if ($locked->status !== 'active' || $locked->deposit_status !== 'approved') {
                    throw new \DomainException('Only an approved active investment can receive a top-up.');
                }

                $user = User::whereKey($locked->user_id)->lockForUpdate()->firstOrFail();
                CompoundInvestmentTopup::create([
                    'compound_investment_id' => $locked->id,
                    'admin_id' => $adminId,
                    'idempotency_key' => $data['idempotency_key'],
                    'amount' => $amount,
                    'note' => $data['note'] ?: 'Compound investment top-up',
                ]);
                $user->roi = round((float) $user->roi + $amount, 2);
                $user->save();
                Tp_Transaction::create([
                    'user' => $user->id,
                    'plan' => 'Compound investment top-up #' . $locked->id,
                    'amount' => $amount,
                    'type' => 'ROI',
                ]);

                return $user->id;
            });
        } catch (\DomainException $e) {
            return back()->with('message', $e->getMessage());
        } catch (QueryException $e) {
            // The unique idempotency key is the final guard against duplicate
            // credits if a browser or proxy replays the same submission.
            $driverCode = isset($e->errorInfo[1]) ? (int) $e->errorInfo[1] : 0;
            if ((string) $e->getCode() === '23000' || $driverCode === 1062) {
                return back()->with('message', 'This top-up was already processed. No additional credit was made.');
            }
            throw $e;
        }

        $investment->load('topups');
        $user = User::findOrFail($userId);
        $currency = optional(Settings::find(1))->currency ?: '$';
        $totalEarnings = (float) $investment->topups()->sum('amount');
        try {
            Mail::raw(
                "Compound investment top-up received\n"
                . "Investment reference: #{$investment->id}\n"
                . "Initial investment: {$currency}" . number_format($investment->amount, 2) . "\n"
                . "Top-up frequency: " . ucfirst($investment->recurring_frequency) . "\n"
                . "Top-up amount: {$currency}" . number_format($amount, 2) . "\n"
                . "Total earnings credited from this investment: {$currency}" . number_format($totalEarnings, 2) . "\n"
                . "Investment duration: {$investment->duration_months} months\n"
                . "Monthly return: " . number_format($investment->monthly_return, 2) . "%\n"
                . "Performance rate: " . number_format($investment->performance_rate, 2) . "%\n"
                . "Payment date: " . now()->format('M j, Y H:i') . "\n"
                . "Investment status: " . ucfirst($investment->status),
                function ($message) use ($user) {
                    $message->to($user->email)->subject('Compound investment earnings credited');
                }
            );
        } catch (\Exception $e) {
            report($e);
        }

        return redirect()->route('admin.compound-investments.show', $investment)
            ->with('success', 'Top-up credited to earnings and recorded.');
    }

    public function complete(Request $request, CompoundInvestment $investment)
    {
        if (!$this->tableExists('compound_investments') || !$this->columnExists('compound_investments', 'completed_by')) {
            return back()->with('message', 'Compound completion audit fields are missing. Apply SQL/compound_investment_workflow_upgrade.sql first.');
        }

        $request->validate([
            'manual_override' => 'nullable|boolean',
        ]);
        $adminId = Auth::guard('admin')->id();

        try {
            DB::transaction(function () use ($request, $investment, $adminId) {
                $locked = CompoundInvestment::whereKey($investment->id)
                    ->lockForUpdate()
                    ->firstOrFail();
                if ($locked->status !== 'active') {
                    throw new \DomainException('Only active investments can be completed.');
                }
                $termEnded = !$locked->expected_completion_at || now()->greaterThanOrEqualTo($locked->expected_completion_at);
                if (!$termEnded && !$request->boolean('manual_override')) {
                    throw new \DomainException('The planned term has not ended. Confirm the authorized early-completion action to proceed.');
                }
                $locked->status = 'completed';
                $locked->completed_at = now();
                $locked->completed_by = $adminId;
                $locked->save();
            });
        } catch (\DomainException $e) {
            return back()->with('message', $e->getMessage());
        }

        $investment->refresh()->load(['user', 'topups']);
        $user = $investment->user;
        $currency = optional(Settings::find(1))->currency ?: '$';
        $totalEarnings = (float) $investment->topups->sum('amount');
        try {
            Mail::raw(
                "Your compound investment is complete\n"
                . "Investment reference: #{$investment->id}\n"
                . "Initial investment: {$currency}" . number_format($investment->amount, 2) . "\n"
                . "Duration: {$investment->duration_months} months\n"
                . "Monthly return: " . number_format($investment->monthly_return, 2) . "%\n"
                . "Performance rate: " . number_format($investment->performance_rate, 2) . "%\n"
                . "Recurring top-ups credited: {$currency}" . number_format($totalEarnings, 2) . "\n"
                . "Investment start date: " . ($investment->start_date ? $investment->start_date->format('M j, Y H:i') : 'N/A') . "\n"
                . "Completion date: " . $investment->completed_at->format('M j, Y H:i') . "\n"
                . "Final status: Completed\n"
                . "No additional balance adjustment was made at completion.",
                function ($message) use ($user) {
                    $message->to($user->email)->subject('Compound investment completed');
                }
            );
        } catch (\Exception $e) {
            report($e);
        }

        return redirect()->route('admin.compound-investments.show', $investment)
            ->with('success', 'Investment marked completed. No extra funds were credited.');
    }

    private function hasDecisionColumns()
    {
        if (!$this->tableExists('compound_investments')) {
            return false;
        }
        foreach (['start_date', 'expected_completion_at', 'rejected_at', 'rejection_reason', 'completed_by'] as $column) {
            if (!$this->columnExists('compound_investments', $column)) {
                return false;
            }
        }

        return true;
    }

    private function tableExists($table)
    {
        try {
            if (DB::connection()->getDriverName() === 'mysql') {
                foreach (DB::select('SHOW TABLES') as $row) {
                    if (in_array($table, array_values((array) $row), true)) {
                        return true;
                    }
                }
                return false;
            }

            return \Illuminate\Support\Facades\Schema::hasTable($table);
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function columnExists($table, $column)
    {
        try {
            if (DB::connection()->getDriverName() === 'mysql') {
                // Table names passed here are hard-coded by the controller;
                // SHOW COLUMNS avoids requiring SELECT on information_schema.
                foreach (DB::select('SHOW COLUMNS FROM `' . $table . '`') as $row) {
                    if (isset($row->Field) && $row->Field === $column) {
                        return true;
                    }
                }
                return false;
            }

            return \Illuminate\Support\Facades\Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
