@extends('layouts.dash')
@section('title', $title)
@section('content')
@php
    $paymentLabel = $investment->deposit_status === 'approved'
        ? 'Approved'
        : ($investment->deposit_status === 'rejected'
            ? 'Rejected'
            : ($investment->payment_proof ? 'Awaiting Admin Approval' : 'Pending Payment'));
    $progress = (float) $investment->progress_percentage;
@endphp
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-primary">Compound Investment #{{ $investment->id }}</p>
            <h1 class="mt-1 text-2xl font-bold text-white">Investment details</h1>
            <p class="mt-1 text-sm text-gray-400">Requested {{ $investment->created_at->format('M j, Y H:i') }}</p>
        </div>
        <div class="flex gap-2">
            @if($investment->status === 'pending')
                <a href="{{ route('compound-investment.payment', $investment) }}" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white">Payment details</a>
            @endif
            <a href="{{ route('compound-investment.history') }}" class="rounded-lg border border-dark-100 px-4 py-2 text-sm text-gray-200">History</a>
        </div>
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
        <div class="mt-4 grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-4">
            <div><span class="text-gray-500">Start date</span><p class="mt-1 text-white">{{ $investment->start_date ? $investment->start_date->format('M j, Y H:i') : 'Starts after approval' }}</p></div>
            <div><span class="text-gray-500">Expected completion</span><p class="mt-1 text-white">{{ $investment->expected_completion_at ? $investment->expected_completion_at->format('M j, Y H:i') : 'Set after approval' }}</p></div>
            <div><span class="text-gray-500">Remaining</span><p class="mt-1 text-white">{{ $investment->status === 'active' ? $investment->remaining_days . ' days' : '—' }}</p></div>
            <div><span class="text-gray-500">Completed</span><p class="mt-1 text-white">{{ $investment->completed_at ? $investment->completed_at->format('M j, Y H:i') : '—' }}</p></div>
        </div>
        @if($investment->status === 'rejected' && $investment->rejection_reason)
            <p class="mt-4 rounded-lg bg-red-500/10 p-3 text-sm text-red-300">Admin note: {{ $investment->rejection_reason }}</p>
        @endif
    </section>

    <section class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-xl border border-dark-100 bg-dark-200 p-5"><p class="text-xs uppercase tracking-wide text-gray-500">Total top-ups credited</p><p class="mt-2 text-2xl font-bold text-white">{{ $currency }}{{ number_format($investment->total_topups, 2) }}</p></div>
        <div class="rounded-xl border border-dark-100 bg-dark-200 p-5"><p class="text-xs uppercase tracking-wide text-gray-500">Total compound earnings received</p><p class="mt-2 text-2xl font-bold text-green-300">{{ $currency }}{{ number_format($investment->total_earnings, 2) }}</p><p class="mt-1 text-xs text-gray-500">The initial principal is not credited again at completion.</p></div>
    </section>

    <section class="overflow-hidden rounded-xl border border-dark-100 bg-dark-200">
        <div class="border-b border-dark-100 p-5"><h2 class="text-lg font-semibold text-white">Top-up and earnings history</h2></div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-dark-100 text-xs uppercase text-gray-400"><tr><th class="px-4 py-3">Amount received</th><th class="px-4 py-3">Date</th><th class="px-4 py-3">Frequency</th><th class="px-4 py-3">Admin</th><th class="px-4 py-3">Note</th></tr></thead>
                <tbody class="divide-y divide-dark-100 text-gray-200">
                    @forelse($investment->topups as $topup)
                        <tr><td class="whitespace-nowrap px-4 py-3 font-semibold text-green-300">{{ $currency }}{{ number_format($topup->amount, 2) }}</td><td class="whitespace-nowrap px-4 py-3">{{ $topup->created_at->format('M j, Y H:i') }}</td><td class="px-4 py-3">{{ ucfirst($investment->recurring_frequency) }}</td><td class="px-4 py-3">{{ $topup->admin ? $topup->admin->firstName : 'Admin' }}</td><td class="px-4 py-3">{{ $topup->note ?: '—' }}</td></tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-gray-400">No top-ups have been credited yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
