@extends('layouts.dash')
@section('title', $title)
@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div><h1 class="text-2xl font-bold text-white">Compound Investment History</h1><p class="mt-1 text-sm text-gray-400">Your personal contributions and administrator-credited investment earnings are shown separately.</p></div>
        <a href="{{ route('compound-investment.create') }}" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-primary-dark">Start Compounding</a>
    </div>
    <x-danger-alert /><x-success-alert />

    @if(($activeRecurring ?? collect())->isNotEmpty())
        <section class="space-y-3">
            <div>
                <h2 class="text-lg font-semibold text-white">Recurring Top-ups</h2>
                <p class="mt-1 text-sm text-gray-400">You can top up now. The due date is a reminder, not a payment lock.</p>
            </div>
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach($activeRecurring as $investment)
                    @php
                        $cardPendingContribution = $investment->contributions->first(function ($contribution) {
                            return $contribution->status === 'pending'
                                && ($contribution->deposit_id || $contribution->balance_debited);
                        });
                        $cardDue = $investment->next_recurring_due;
                    @endphp
                    <div class="rounded-xl border border-primary/40 bg-dark-200 p-5 shadow-lg">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-primary">Investment #{{ $investment->id }}</p>
                                <p class="mt-2 text-2xl font-bold text-white">{{ $currency }}{{ number_format($investment->recurring_amount, 2) }}</p>
                                <p class="mt-1 text-xs text-gray-400">{{ ucfirst($investment->recurring_frequency) }} recurring top-up</p>
                            </div>
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $cardPendingContribution ? 'bg-amber-500/15 text-amber-300' : 'bg-green-500/15 text-green-300' }}">
                                {{ $cardPendingContribution ? 'Pending' : 'Ready' }}
                            </span>
                        </div>

                        <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <dt class="text-xs text-gray-400">Next due date</dt>
                                <dd class="mt-1 font-semibold text-white">{{ $cardDue ? $cardDue->format('j F Y') : 'Not scheduled' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-400">Frequency</dt>
                                <dd class="mt-1 font-semibold text-white">{{ ucfirst($investment->recurring_frequency) }}</dd>
                            </div>
                            <div class="col-span-2">
                                <dt class="text-xs text-gray-400">My approved contributions</dt>
                                <dd class="mt-1 font-semibold text-white">{{ $currency }}{{ number_format($investment->total_user_contributions, 2) }}</dd>
                            </div>
                        </dl>

                        @if($cardPendingContribution)
                            <button type="button" disabled
                                class="mt-4 block w-full cursor-not-allowed rounded-lg bg-primary/50 px-4 py-3 text-center font-bold text-white opacity-70">
                                Top Up Now
                            </button>
                            <p class="mt-2 text-center text-xs text-amber-200">Your top-up is being reviewed. The button unlocks after admin approval or rejection.</p>
                        @else
                            <a href="{{ route('compound-investment.recurring-topup', $investment) }}"
                                class="mt-4 block rounded-lg bg-primary px-4 py-3 text-center font-bold text-white transition hover:bg-primary-dark">
                                Top Up Now
                            </a>
                            <p class="mt-2 text-center text-xs text-gray-400">Available now — the due date does not block payment.</p>
                        @endif

                        <a href="{{ route('compound-investment.show', $investment) }}" class="mt-2 block text-center text-xs text-primary hover:underline">
                            View investment details
                        </a>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <div class="overflow-hidden rounded-xl border border-dark-100 bg-dark-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-dark-100 text-sm">
                <thead class="bg-dark-100 text-left text-xs uppercase tracking-wide text-gray-400">
                    <tr>
                        <th class="px-4 py-3">Investment</th><th class="px-4 py-3">Requested</th>
                        <th class="px-4 py-3">Payment</th><th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Terms</th><th class="px-4 py-3">My Contributions</th>
                        <th class="px-4 py-3">Investment Earnings</th><th class="px-4 py-3">Next Due</th><th class="px-4 py-3">Completion</th><th class="px-4 py-3">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-100 text-gray-200">
                    @forelse($investments as $investment)
                        @php
                            $paymentLabel = $investment->deposit_status === 'approved' ? 'Approved' : ($investment->deposit_status === 'rejected' ? 'Rejected' : ($investment->payment_proof ? 'Awaiting review' : 'Pending payment'));
                            $rowCanTopup = $investment->status === 'active'
                                && $investment->deposit_status === 'approved'
                                && (float) $investment->recurring_amount > 0;
                            $rowPendingContribution = $investment->contributions->first(function ($contribution) {
                                return $contribution->status === 'pending'
                                    && ($contribution->deposit_id || $contribution->balance_debited);
                            });
                        @endphp
                        <tr class="hover:bg-dark-100/70">
                            <td class="whitespace-nowrap px-4 py-4 font-semibold text-white">#{{ $investment->id }}</td>
                            <td class="whitespace-nowrap px-4 py-4">{{ $currency }}{{ number_format($investment->amount, 2) }}<span class="mt-1 block text-xs text-gray-500">{{ $investment->created_at->format('M j, Y') }}</span></td>
                            <td class="whitespace-nowrap px-4 py-4">{{ $paymentLabel }}<span class="mt-1 block text-xs text-gray-500">{{ $investment->payment_method ?: '—' }}</span></td>
                            <td class="whitespace-nowrap px-4 py-4"><span class="rounded-full bg-primary/10 px-2.5 py-1 text-xs font-semibold">{{ $investment->display_status }}</span></td>
                            <td class="whitespace-nowrap px-4 py-4">{{ $investment->duration_months }} mo · {{ number_format($investment->monthly_return, 1) }}%<span class="mt-1 block text-xs text-gray-500">Performance {{ number_format($investment->performance_rate, 2) }}%</span><span class="mt-1 block text-xs text-gray-500">{{ $currency }}{{ number_format($investment->recurring_amount,2) }} / {{ ucfirst($investment->recurring_frequency) }}</span></td>
                            <td class="whitespace-nowrap px-4 py-4">{{ $currency }}{{ number_format($investment->total_user_contributions, 2) }}<span class="mt-1 block text-xs text-gray-500">{{ $investment->contributions->count() }} recurring payment record(s)</span></td>
                            <td class="whitespace-nowrap px-4 py-4 text-green-300">{{ $currency }}{{ number_format($investment->total_earnings, 2) }}</td>
                            <td class="whitespace-nowrap px-4 py-4">
                                @if($rowCanTopup)
                                    <span>{{ $investment->next_recurring_due ? $investment->next_recurring_due->format('M j, Y') : 'Not scheduled' }}</span>
                                    @if($rowPendingContribution)
                                        <span class="mt-1 block text-xs font-semibold text-amber-300">Pending admin approval</span>
                                    @else
                                        <a href="{{ route('compound-investment.recurring-topup', $investment) }}"
                                            class="mt-2 inline-flex rounded-lg bg-primary px-3 py-1.5 text-xs font-bold text-white hover:bg-primary-dark">
                                            Top Up Now
                                        </a>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-4">{{ $investment->completed_at ? $investment->completed_at->format('M j, Y') : ($investment->expected_completion_at ? $investment->expected_completion_at->format('M j, Y') : '—') }}</td>
                            <td class="whitespace-nowrap px-4 py-4">
                                <a href="{{ route('compound-investment.show', $investment) }}" class="font-semibold text-primary hover:underline">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="px-4 py-12 text-center text-gray-400">You don’t have any compound investment requests yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($investments->hasPages())<div class="border-t border-dark-100 p-4">{{ $investments->links() }}</div>@endif
    </div>
</div>
@endsection
