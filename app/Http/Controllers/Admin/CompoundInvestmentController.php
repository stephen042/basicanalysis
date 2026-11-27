<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompoundInvestment;
use App\Models\CompoundInvestmentTopup;
use App\Models\Settings;
use App\Models\Tp_Transaction;
use App\Models\User;
use App\Models\Wdmethod;
use App\Models\CompoundInvestmentContribution;
use App\Services\NotificationService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class CompoundInvestmentController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $query = CompoundInvestment::with(['user', 'topups'])->latest();
        if ($this->tableExists('compound_investment_contributions')) {
            $query->with('contributions');
        }

        return view('admin.compound-investments.index', [
            'title' => 'Compound Investments',
            'investments' => $query->paginate(25),
            'currency' => '$',
        ]);
    }

    public function show($id)
    {
        $query = CompoundInvestment::with(['user', 'topups.admin', 'paymentMethod']);
        if ($this->tableExists('compound_investment_contributions')) {
            $query->with(['contributions.deposit', 'contributions.reviewer']);
        }
        $investment = $query->findOrFail($id);
        if (!$this->tableExists('compound_investment_contributions')) {
            $investment->setRelation('contributions', collect());
        }
        $method = $investment->paymentMethod;
        $isUsdtPayment = $this->isUsdtMethod($method);
        if (!$isUsdtPayment || !in_array($method->type, ['deposit', 'both'], true)) {
            $method = null;
            $isUsdtPayment = false;
        }
        $isBalancePayment = $investment->payment_source === 'subscription_balance'
            || $investment->payment_method === 'Subscription Balance';
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
            'isUsdtPayment' => $isUsdtPayment,
            'isBalancePayment' => $isBalancePayment,
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
                $isBalancePayment = $locked->payment_source === 'subscription_balance'
                    || $locked->payment_method === 'Subscription Balance';
                if ($data['decision'] === 'approve' && !$isBalancePayment && !$locked->payment_proof) {
                    throw new \DomainException('A payment receipt is required before approval.');
                }
                if ($data['decision'] === 'approve' && !$isBalancePayment) {
                    $paymentMethod = $locked->payment_method_id
                        ? Wdmethod::find($locked->payment_method_id)
                        : null;
                    if (!$this->isUsdtMethod($paymentMethod)
                        || !in_array($paymentMethod->type, ['deposit', 'both'], true)
                        || strtolower(trim((string) $paymentMethod->status)) !== 'enabled') {
                        throw new \DomainException('Only payments made using an enabled USDT deposit method can be approved.');
                    }
                }

                if ($data['decision'] === 'approve') {
                    $start = now();
                    $locked->deposit_status = 'approved';
                    $locked->status = 'active';
                    $locked->approved_at = $start;
                    $locked->start_date = $start;
                    $locked->expected_completion_at = $start->copy()->addMonths((int) $locked->duration_months);
                    $locked->next_recurring_due_at = (float) $locked->recurring_amount > 0
                        ? CompoundInvestment::nextRecurringDate($start, $locked->recurring_frequency)
                        : null;
                    $locked->rejected_at = null;
                    $locked->rejection_reason = null;
                } else {
                    // Balance-funded applications reserve the initial amount
                    // when submitted, so rejection restores it exactly once.
                    if ($isBalancePayment) {
                        $user = User::whereKey($locked->user_id)->lockForUpdate()->firstOrFail();
                        $user->account_bal = round((float) $user->account_bal + (float) $locked->amount, 2);
                        $user->save();
                        Tp_Transaction::create([
                            'user' => $user->id,
                            'plan' => 'Compound investment #' . $locked->id . ' rejected refund',
                            'amount' => (float) $locked->amount,
                            'type' => 'Compound Investment',
                        ]);
                    }
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
            || !$this->columnExists('compound_investment_topups', 'idempotency_key')
            || !$this->columnExists('compound_investment_topups', 'currency')) {
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
                    'currency' => 'USD',
                    'note' => $data['note'] ?: 'Administrator-credited investment profit',
                ]);
                // The administrator's entry is investment earnings. It is
                // intentionally not written to deposits or user contributions.
                $user->roi = round((float) $user->roi + $amount, 2);
                $user->save();
                Tp_Transaction::create([
                    'user' => $user->id,
                    'plan' => 'Compound investment profit #' . $locked->id,
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
        $currency = $this->investmentCurrency();
        $totalEarnings = (float) $investment->topups()->sum('amount');
        try {
            Mail::raw(
                "Investment earnings credited by an administrator\n"
                . "Investment reference: #{$investment->id}\n"
                . "Initial investment: {$currency}" . number_format($investment->amount, 2) . "\n"
                . "User recurring contribution schedule: {$currency}" . number_format($investment->recurring_amount, 2) . " / " . ucfirst($investment->recurring_frequency) . "\n"
                . "Profit credited: {$currency}" . number_format($amount, 2) . "\n"
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
        $this->notificationService->create(
            $user->id,
            \App\Models\Notification::TYPE_PROFIT_RETURN,
            'Compound investment earnings credited',
            'An administrator credited $' . number_format($amount, 2) . ' in investment earnings to compound investment #' . $investment->id . '.',
            ['investment_id' => $investment->id, 'amount' => $amount, 'admin_id' => $adminId],
            \App\Models\Notification::PRIORITY_HIGH,
            route('compound-investment.show', $investment),
            'fas fa-chart-line'
        );

        return redirect()->route('admin.compound-investments.show', $investment)
            ->with('success', 'Investment profit credited to Total Earning and recorded.');
    }

    public function approveContribution(Request $request, CompoundInvestmentContribution $contribution)
    {
        $adminId = Auth::guard('admin')->id();

        try {
            $review = DB::transaction(function () use ($contribution, $adminId) {
                $lockedContribution = CompoundInvestmentContribution::whereKey($contribution->id)
                    ->lockForUpdate()
                    ->firstOrFail();
                if ($lockedContribution->status !== 'pending'
                    || (!$lockedContribution->deposit_id && !$lockedContribution->balance_debited)) {
                    throw new \DomainException('Only a submitted pending contribution can be approved.');
                }

                $investment = CompoundInvestment::whereKey($lockedContribution->compound_investment_id)
                    ->lockForUpdate()
                    ->firstOrFail();
                if (!in_array($investment->status, ['active', 'completed'], true)
                    || $investment->deposit_status !== 'approved') {
                    throw new \DomainException('This investment cannot accept contribution review actions.');
                }

                $deposit = null;
                if ($lockedContribution->deposit_id) {
                    $deposit = \App\Models\Deposit::whereKey($lockedContribution->deposit_id)
                        ->lockForUpdate()
                        ->firstOrFail();
                    if ((int) $deposit->user !== (int) $investment->user_id
                        || strtolower((string) $deposit->status) !== 'pending'
                        || abs(round((float) $deposit->amount * 100) - round((float) $lockedContribution->amount * 100)) > 0) {
                        throw new \DomainException('The contribution deposit has already been processed or its amount does not match.');
                    }
                    $method = $deposit->paymentMethod;
                    if (!$method
                        || strtolower(trim((string) $method->methodtype)) !== 'crypto'
                        || !in_array(strtolower(trim((string) $method->type)), ['deposit', 'both'], true)) {
                        throw new \DomainException('Recurring contributions must use a configured cryptocurrency deposit method.');
                    }
                    $deposit->status = 'Processed';
                    $deposit->save();
                }

                $approvedAt = now();
                $lockedContribution->status = 'approved';
                $lockedContribution->approved_at = $approvedAt;
                $lockedContribution->reviewed_by = $adminId;
                $lockedContribution->save();

                if ($investment->status === 'active') {
                    // Base the next period on the contribution's scheduled
                    // period so an early payment still advances the schedule.
                    $investment->next_recurring_due_at = $investment->nextRecurringDueAfterContribution(
                        $lockedContribution,
                        $approvedAt
                    );
                    $investment->save();
                }

                return [
                    'investment_id' => $investment->id,
                    'user_id' => $investment->user_id,
                    'amount' => (float) $lockedContribution->amount,
                    'payment_method' => $lockedContribution->payment_method ?: ($deposit ? $deposit->payment_mode : 'Subscription Balance'),
                    'deposit' => $deposit,
                ];
            });
        } catch (\DomainException $e) {
            return back()->with('message', $e->getMessage());
        }

        $user = User::findOrFail($review['user_id']);
        $message = 'Your recurring contribution of $' . number_format($review['amount'], 2)
            . ' for compound investment #' . $review['investment_id'] . ' has been approved.';
        $this->notificationService->create(
            $user->id,
            \App\Models\Notification::TYPE_DEPOSIT,
            'Recurring contribution approved',
            $message,
            ['investment_id' => $review['investment_id'], 'contribution_id' => $contribution->id],
            \App\Models\Notification::PRIORITY_NORMAL,
            route('compound-investment.show', $review['investment_id']),
            'fas fa-circle-check'
        );
        try {
            if ($review['deposit']) {
                Mail::to($user->email)->send(new \App\Mail\DepositStatus(
                    $review['deposit'],
                    $user,
                    'Recurring Contribution Approved',
                    false
                ));
            } elseif ($user->email) {
                Mail::raw($message, function ($mail) use ($user) {
                    $mail->to($user->email)->subject('Compound recurring contribution approved');
                });
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->route('admin.compound-investments.show', $review['investment_id'])
            ->with('success', 'Recurring contribution approved. No wallet or earnings credit was made.');
    }

    public function rejectContribution(Request $request, CompoundInvestmentContribution $contribution)
    {
        $data = $request->validate(['admin_note' => 'nullable|string|max:1000']);
        $adminId = Auth::guard('admin')->id();

        try {
            $review = DB::transaction(function () use ($contribution, $data, $adminId) {
                $lockedContribution = CompoundInvestmentContribution::whereKey($contribution->id)
                    ->lockForUpdate()
                    ->firstOrFail();
                if ($lockedContribution->status !== 'pending'
                    || (!$lockedContribution->deposit_id && !$lockedContribution->balance_debited)) {
                    throw new \DomainException('Only a submitted pending contribution can be rejected.');
                }

                $investment = CompoundInvestment::whereKey($lockedContribution->compound_investment_id)
                    ->lockForUpdate()
                    ->firstOrFail();
                if (!in_array($investment->status, ['active', 'completed'], true)) {
                    throw new \DomainException('This investment cannot accept contribution review actions.');
                }

                $deposit = null;
                if ($lockedContribution->deposit_id) {
                    $deposit = \App\Models\Deposit::whereKey($lockedContribution->deposit_id)
                        ->lockForUpdate()
                        ->firstOrFail();
                    if ((int) $deposit->user !== (int) $investment->user_id
                        || strtolower((string) $deposit->status) !== 'pending') {
                        throw new \DomainException('The contribution payment has already been processed.');
                    }
                    $deposit->status = 'Rejected';
                    $deposit->save();
                }

                $balanceRefunded = false;
                if ($lockedContribution->balance_debited) {
                    $user = User::whereKey($investment->user_id)->lockForUpdate()->firstOrFail();
                    $amount = round((float) $lockedContribution->amount, 2);
                    $user->account_bal = round((float) $user->account_bal + $amount, 2);
                    $user->save();
                    Tp_Transaction::create([
                        'user' => $user->id,
                        'plan' => 'Compound investment recurring contribution #' . $investment->id . ' rejected refund',
                        'amount' => $amount,
                        'type' => 'Compound Investment',
                    ]);
                    $balanceRefunded = true;
                }

                $lockedContribution->status = 'rejected';
                $lockedContribution->admin_note = $data['admin_note'] ?: null;
                $lockedContribution->reviewed_by = $adminId;
                $lockedContribution->rejected_at = now();
                $lockedContribution->save();

                return [
                    'investment_id' => $investment->id,
                    'user_id' => $investment->user_id,
                    'amount' => (float) $lockedContribution->amount,
                    'balance_refunded' => $balanceRefunded,
                ];
            });
        } catch (\DomainException $e) {
            return back()->with('message', $e->getMessage());
        }

        $investment = CompoundInvestment::with('user')->findOrFail($review['investment_id']);
        $message = 'Your recurring contribution of $' . number_format($review['amount'], 2)
            . ' for compound investment #' . $investment->id . ' was not approved.'
            . ($data['admin_note'] ? ' Admin note: ' . $data['admin_note'] : '');
        if ($review['balance_refunded']) {
            $message .= ' The Subscription Balance payment was returned to your available balance.';
        }
        $this->notificationService->create(
            $investment->user_id,
            \App\Models\Notification::TYPE_DEPOSIT,
            'Recurring contribution rejected',
            $message,
            ['investment_id' => $investment->id, 'contribution_id' => $contribution->id],
            \App\Models\Notification::PRIORITY_HIGH,
            route('compound-investment.show', $investment),
            'fas fa-circle-xmark'
        );
        try {
            Mail::raw($message, function ($mail) use ($investment) {
                $mail->to($investment->user->email)->subject('Compound recurring contribution update');
            });
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()->route('admin.compound-investments.show', $investment)
            ->with('success', $review['balance_refunded']
                ? 'Recurring contribution rejected. The Subscription Balance was restored.'
                : 'Recurring contribution rejected. No wallet balance or earnings were changed.');
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
        $currency = $this->investmentCurrency();
        $totalEarnings = (float) $investment->topups->sum('amount');
        try {
            Mail::raw(
                "Your compound investment is complete\n"
                . "Investment reference: #{$investment->id}\n"
                . "Initial investment: {$currency}" . number_format($investment->amount, 2) . "\n"
                . "Duration: {$investment->duration_months} months\n"
                . "Monthly return: " . number_format($investment->monthly_return, 2) . "%\n"
                . "Performance rate: " . number_format($investment->performance_rate, 2) . "%\n"
                . "Administrator-credited investment earnings: {$currency}" . number_format($totalEarnings, 2) . "\n"
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
        foreach (['start_date', 'expected_completion_at', 'rejected_at', 'rejection_reason', 'completed_by', 'payment_source', 'currency', 'next_recurring_due_at'] as $column) {
            if (!$this->columnExists('compound_investments', $column)) {
                return false;
            }
        }

        return true;
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
