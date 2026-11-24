@extends('layouts.dash')
@section('title', $title)
@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div><h1 class="text-2xl font-bold text-white">Compound Investment History</h1><p class="mt-1 text-sm text-gray-400">Track all requests, payment reviews, recurring earnings, and completions.</p></div>
        <a href="{{ route('compound-investment.create') }}" class="rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white hover:bg-primary-dark">Start Compounding</a>
    </div>
    <x-danger-alert /><x-success-alert />
    <div class="overflow-hidden rounded-xl border border-dark-100 bg-dark-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-dark-100 text-sm">
                <thead class="bg-dark-100 text-left text-xs uppercase tracking-wide text-gray-400">
                    <tr>
                        <th class="px-4 py-3">Investment</th><th class="px-4 py-3">Requested</th>
                        <th class="px-4 py-3">Payment</th><th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Terms</th><th class="px-4 py-3">Top-ups</th>
                        <th class="px-4 py-3">Earnings</th><th class="px-4 py-3">Completion</th><th class="px-4 py-3">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-100 text-gray-200">
                    @forelse($investments as $investment)
                        @php
                            $paymentLabel = $investment->deposit_status === 'approved' ? 'Approved' : ($investment->deposit_status === 'rejected' ? 'Rejected' : ($investment->payment_proof ? 'Awaiting review' : 'Pending payment'));
                            $investmentCurrency = in_array(strtoupper(trim((string) $investment->payment_method)), ['USDT', 'SUBSCRIPTION BALANCE'], true) ? 'USDT ' : $currency;
                        @endphp
                        <tr class="hover:bg-dark-100/70">
                            <td class="whitespace-nowrap px-4 py-4 font-semibold text-white">#{{ $investment->id }}</td>
                            <td class="whitespace-nowrap px-4 py-4">{{ $investmentCurrency }}{{ number_format($investment->amount, 2) }}<span class="mt-1 block text-xs text-gray-500">{{ $investment->created_at->format('M j, Y') }}</span></td>
                            <td class="whitespace-nowrap px-4 py-4">{{ $paymentLabel }}<span class="mt-1 block text-xs text-gray-500">{{ $investment->payment_method ?: '—' }}</span></td>
                            <td class="whitespace-nowrap px-4 py-4"><span class="rounded-full bg-primary/10 px-2.5 py-1 text-xs font-semibold">{{ $investment->display_status }}</span></td>
                            <td class="whitespace-nowrap px-4 py-4">{{ $investment->duration_months }} mo · {{ number_format($investment->monthly_return, 1) }}%<span class="mt-1 block text-xs text-gray-500">{{ $investmentCurrency }}{{ number_format($investment->recurring_amount,2) }} / {{ $investment->recurring_frequency }}</span></td>
                            <td class="whitespace-nowrap px-4 py-4">{{ $investment->topups->count() }}</td>
                            <td class="whitespace-nowrap px-4 py-4 text-green-300">{{ $investmentCurrency }}{{ number_format($investment->total_earnings, 2) }}</td>
                            <td class="whitespace-nowrap px-4 py-4">{{ $investment->completed_at ? $investment->completed_at->format('M j, Y') : ($investment->expected_completion_at ? $investment->expected_completion_at->format('M j, Y') : '—') }}</td>
                            <td class="whitespace-nowrap px-4 py-4">
                                <a href="{{ route('compound-investment.show', $investment) }}" class="font-semibold text-primary hover:underline">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-4 py-12 text-center text-gray-400">You don’t have any compound investment requests yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($investments->hasPages())<div class="border-t border-dark-100 p-4">{{ $investments->links() }}</div>@endif
    </div>
</div>
@endsection
