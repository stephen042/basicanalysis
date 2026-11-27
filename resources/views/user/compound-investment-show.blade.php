@extends('layouts.dash')
@section('title', $title)
@section('content')
@php
    $isBalancePayment = $investment->payment_source === 'subscription_balance'
        || $investment->payment_method === 'Subscription Balance';
    $paymentLabel = $investment->deposit_status === 'approved'
        ? 'Approved'
        : ($investment->deposit_status === 'rejected'
            ? 'Rejected'
            : ($isBalancePayment ? 'Balance reserved · Awaiting admin approval' : ($investment->payment_proof ? 'Awaiting Admin Approval' : 'Pending Payment')));
    $progress = (float) $investment->progress_percentage;
    $pendingRecurringPayment = $investment->contributions->first(function ($contribution) {
        return $contribution->status === 'pending'
            && ($contribution->deposit_id || $contribution->balance_debited);
    });
    $recurringDue = $investment->next_recurring_due;
    $recurringAvailable = $investment->status === 'active'
        && $investment->deposit_status === 'approved'
        && (float) $investment->recurring_amount > 0;
    $lastApprovedContribution = $investment->contributions
        ->where('status', 'approved')
        ->sortByDesc(function ($contribution) {
            return $contribution->approved_at ?: $contribution->created_at;
        })
        ->first();
@endphp
<div class="space-y-6">
    <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-primary">Compound Investment #{{ $investment->id }}</p>
                <h1 class="mt-1 text-2xl font-bold text-white">Investment details</h1>
                <p class="mt-1 text-sm text-gray-400">Requested {{ $investment->created_at->format('M j, Y H:i') }}</p>
            </div>
            <div class="flex gap-2">
                @if($investment->status === 'pending' && $investment->payment_source !== 'subscription_balance')
                    <a href="{{ route('compound-investment.payment', $investment) }}" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Payment details</a>
                @endif
                <a href="{{ route('compound-investment.history') }}" class="rounded-lg border border-dark-100 px-4 py-2 text-sm text-gray-200">History</a>
            </div>
        </div>

        @if($recurringAvailable)
            <aside class="rounded-xl border border-primary/40 bg-dark-200 p-4 shadow-lg sm:p-5">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-primary">Next Recurring Top-up</h2>
                <dl class="mt-3 grid grid-cols-2 gap-3">
                    <div>
                        <dt class="text-xs text-gray-400">Recurring amount</dt>
                        <dd class="mt-1 font-semibold text-white">{{ $currency }}{{ number_format($investment->recurring_amount, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400">Frequency</dt>
                        <dd class="mt-1 font-semibold text-white">{{ ucfirst($investment->recurring_frequency) }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400">Next due date</dt>
                        <dd class="mt-1 font-semibold text-white">{{ $recurringDue ? $recurringDue->format('j F Y') : 'Not scheduled' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-400">Payment status</dt>
                        <dd class="mt-1 font-semibold {{ $pendingRecurringPayment ? 'text-amber-300' : 'text-green-300' }}">
                            {{ $pendingRecurringPayment ? 'Pending admin approval' : 'Ready' }}
                        </dd>
                    </div>
                    @if($lastApprovedContribution)
                        <div class="col-span-2">
                            <dt class="text-xs text-gray-400">Last approved contribution</dt>
                            <dd class="mt-1 font-semibold text-white">
                                {{ $currency }}{{ number_format($lastApprovedContribution->amount, 2) }}
                                · {{ ($lastApprovedContribution->approved_at ?: $lastApprovedContribution->created_at)->format('j F Y') }}
                            </dd>
                        </div>
                    @endif
                </dl>
                @if($pendingRecurringPayment)
                    <button type="button" disabled
                        class="mt-4 block w-full cursor-not-allowed rounded-lg bg-primary/50 px-4 py-3 text-center font-bold text-white opacity-70">
                        Top Up Now
                    </button>
                    <p class="mt-2 text-center text-xs text-amber-200">Your submitted top-up is being reviewed. The button unlocks after it is approved or rejected.</p>
                @else
                    <a href="{{ route('compound-investment.recurring-topup', $investment) }}"
                        class="mt-4 block rounded-lg bg-primary px-4 py-3 text-center font-bold text-white transition hover:bg-primary-dark">
                        Top Up Now
                    </a>
                    <p class="mt-2 text-center text-xs text-gray-400">Available now — the due date is a reminder, not a payment lock.</p>
                @endif
            </aside>
        @endif
    </div>
    <x-danger-alert /><x-success-alert />

    <section class="rounded-xl border border-dark-100 bg-dark-200 p-5 md:p-7">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm text-gray-400">Initial investment</p>
                <p class="mt-1 text-3xl font-black text-white">{{ $currency }}{{ number_format($investment->amount, 2) }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <span class="rounded-full bg-primary/10 px-3 py-1.5 text-xs font-bold text-primary">{{ $investment->display_status }}</span>
                <span class="rounded-full bg-blue-500/10 px-3 py-1.5 text-xs font-bold text-blue-300">Payment: {{ $paymentLabel }}</span>
            </div>
        </div>
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div class="rounded-lg bg-dark-100 p-4"><p class="text-xs uppercase text-gray-500">Payment method</p><p class="mt-1 text-lg font-semibold text-white">{{ $investment->payment_method ?: '—' }}</p></div>
            <div class="rounded-lg bg-dark-100 p-4"><p class="text-xs uppercase text-gray-500">Monthly return</p><p class="mt-1 text-lg font-semibold text-white">{{ number_format($investment->monthly_return, 2) }}%</p></div>
            <div class="rounded-lg bg-dark-100 p-4"><p class="text-xs uppercase text-gray-500">Performance rate</p><p class="mt-1 text-lg font-semibold text-white">{{ number_format($investment->performance_rate, 2) }}%</p></div>
            <div class="rounded-lg bg-dark-100 p-4"><p class="text-xs uppercase text-gray-500">Duration</p><p class="mt-1 text-lg font-semibold text-white">{{ $investment->duration_months }} months</p></div>
            <div class="rounded-lg bg-dark-100 p-4"><p class="text-xs uppercase text-gray-500">Recurring top-up</p><p class="mt-1 text-lg font-semibold text-white">{{ $currency }}{{ number_format($investment->recurring_amount, 2) }} / {{ ucfirst($investment->recurring_frequency) }}</p></div>
        </div>
    </section>

    <section class="rounded-xl border border-dark-100 bg-dark-200 p-5 md:p-7">
        <div class="flex items-center justify-between gap-3">
            <div><h2 class="text-lg font-semibold text-white">Investment progress</h2><p class="mt-1 text-sm text-gray-400">Progress begins after admin approves your initial payment.</p></div>
            <span class="text-lg font-bold text-primary">{{ number_format($progress, 1) }}%</span>
        </div>
        <div class="mt-4 h-3 overflow-hidden rounded-full bg-dark-100"><div class="h-full rounded-full bg-primary transition-all" style="width: {{ $progress }}%"></div></div>
        <div class="mt-4 grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-5">
            <div><span class="text-gray-500">Start date</span><p class="mt-1 text-white">{{ $investment->start_date ? $investment->start_date->format('M j, Y H:i') : 'Starts after approval' }}</p></div>
            <div><span class="text-gray-500">Expected completion</span><p class="mt-1 text-white">{{ $investment->expected_completion_at ? $investment->expected_completion_at->format('M j, Y H:i') : 'Set after approval' }}</p></div>
            <div><span class="text-gray-500">Remaining</span><p class="mt-1 text-white">{{ $investment->status === 'active' ? $investment->remaining_days . ' days' : '—' }}</p></div>
            <div><span class="text-gray-500">Completed</span><p class="mt-1 text-white">{{ $investment->completed_at ? $investment->completed_at->format('M j, Y H:i') : '—' }}</p></div>
            <div><span class="text-gray-500">Next recurring contribution</span><p class="mt-1 text-white">{{ $investment->status === 'active' && $investment->next_recurring_due ? $investment->next_recurring_due->format('M j, Y H:i') : '—' }}</p></div>
        </div>
        @if($investment->status === 'rejected' && $investment->rejection_reason)
            <p class="mt-4 rounded-lg bg-red-500/10 p-3 text-sm text-red-300">Admin note: {{ $investment->rejection_reason }}</p>
        @endif
    </section>

    <section class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-dark-100 bg-dark-200 p-5"><p class="text-xs uppercase tracking-wide text-gray-500">Total user contributions</p><p class="mt-2 text-2xl font-bold text-white">{{ $currency }}{{ number_format($investment->total_user_contributions, 2) }}</p><p class="mt-1 text-xs text-gray-500">Approved recurring payments: {{ $currency }}{{ number_format($investment->total_recurring_contributions, 2) }}</p></div>
        <div class="rounded-xl border border-dark-100 bg-dark-200 p-5"><p class="text-xs uppercase tracking-wide text-gray-500">Total investment earnings</p><p class="mt-2 text-2xl font-bold text-green-300">{{ $currency }}{{ number_format($investment->total_earnings, 2) }}</p><p class="mt-1 text-xs text-gray-500">Administrator-credited profit; not a user deposit.</p></div>
        <div class="rounded-xl border border-dark-100 bg-dark-200 p-5"><p class="text-xs uppercase tracking-wide text-gray-500">Recurring schedule</p><p class="mt-2 text-lg font-bold text-white">{{ $currency }}{{ number_format($investment->recurring_amount, 2) }} / {{ ucfirst($investment->recurring_frequency) }}</p><p class="mt-1 text-xs text-gray-500">Next due: {{ $investment->status === 'active' && $recurringDue ? $recurringDue->format('M j, Y H:i') : '—' }}</p></div>
    </section>

    <section class="overflow-hidden rounded-xl border border-dark-100 bg-dark-200">
        <div class="border-b border-dark-100 p-5"><h2 class="text-lg font-semibold text-white">My Contributions</h2><p class="mt-1 text-sm text-gray-400">Initial investment and recurring amounts you personally paid. Pending amounts are not counted as approved contributions.</p></div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-dark-100 text-xs uppercase text-gray-400"><tr><th class="px-4 py-3">Contribution</th><th class="px-4 py-3">Amount</th><th class="px-4 py-3">Date / due date</th><th class="px-4 py-3">Payment status</th><th class="px-4 py-3">Reference</th></tr></thead>
                <tbody class="divide-y divide-dark-100 text-gray-200">
                    <tr>
                        <td class="px-4 py-3 font-semibold text-white">Initial starting amount</td>
                        <td class="whitespace-nowrap px-4 py-3">{{ $currency }}{{ number_format($investment->amount, 2) }}</td>
                        <td class="whitespace-nowrap px-4 py-3">{{ $investment->approved_at ? $investment->approved_at->format('M j, Y H:i') : $investment->created_at->format('M j, Y H:i') }}</td>
                        <td class="px-4 py-3">{{ ucfirst($investment->deposit_status) }}</td>
                        <td class="px-4 py-3">Investment #{{ $investment->id }}</td>
                    </tr>
                    @foreach($investment->contributions as $contribution)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-white">Recurring {{ ucfirst($investment->recurring_frequency) }} contribution</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $currency }}{{ number_format($contribution->amount, 2) }}</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $contribution->deposit ? $contribution->deposit->created_at->format('M j, Y H:i') : ($contribution->balance_debited ? $contribution->created_at->format('M j, Y H:i') : 'Due ' . $contribution->scheduled_for->format('M j, Y H:i')) }}</td>
                            <td class="px-4 py-3">{{ ucfirst($contribution->status) }}{{ $contribution->deposit ? ' · ' . ucfirst($contribution->deposit->status) : '' }}{{ $contribution->admin_note ? ' · ' . $contribution->admin_note : '' }}</td>
                            <td class="px-4 py-3">{{ $contribution->payment_method ?: ($contribution->deposit ? $contribution->deposit->payment_mode : 'Payment not submitted') }}{{ $contribution->deposit_id ? ' · Deposit #' . $contribution->deposit_id : '' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <section class="overflow-hidden rounded-xl border border-dark-100 bg-dark-200">
        <div class="border-b border-dark-100 p-5"><h2 class="text-lg font-semibold text-white">Investment Earnings</h2><p class="mt-1 text-sm text-gray-400">Profit manually credited by an administrator. Earnings are separate from contributions and are included in Total Earning.</p></div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-dark-100 text-xs uppercase text-gray-400"><tr><th class="px-4 py-3">Profit</th><th class="px-4 py-3">Date</th><th class="px-4 py-3">Admin</th><th class="px-4 py-3">Note</th></tr></thead>
                <tbody class="divide-y divide-dark-100 text-gray-200">
                    @forelse($investment->topups as $earning)
                        <tr><td class="whitespace-nowrap px-4 py-3 font-semibold text-green-300">{{ $currency }}{{ number_format($earning->amount, 2) }}</td><td class="whitespace-nowrap px-4 py-3">{{ $earning->created_at->format('M j, Y H:i') }}</td><td class="px-4 py-3">{{ $earning->admin ? $earning->admin->firstName : 'Admin' }}</td><td class="px-4 py-3">{{ $earning->note ?: '—' }}</td></tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-10 text-center text-gray-400">No investment earnings have been credited.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
