@extends('layouts.dash')
@section('title', $title)
@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-primary">Investment #{{ $investment->id }}</p>
            <h1 class="mt-1 text-2xl font-bold text-white">Make Recurring Top-up</h1>
            <p class="mt-1 text-sm text-gray-400">This is your personal contribution and is separate from administrator-credited earnings.</p>
        </div>
        <a href="{{ route('compound-investment.show', $investment) }}" class="rounded-lg border border-dark-100 px-4 py-2 text-sm text-gray-200">Back to investment</a>
    </div>
    <x-danger-alert /><x-success-alert />
    @if($errors->any())<div class="rounded-lg bg-red-500/10 p-3 text-sm text-red-300">{{ $errors->first() }}</div>@endif

    <section class="grid gap-4 rounded-xl border border-dark-100 bg-dark-200 p-5 sm:grid-cols-2">
        <div><p class="text-xs uppercase text-gray-500">Contribution amount</p><p class="mt-1 text-2xl font-bold text-white">{{ $currency }}{{ number_format($investment->recurring_amount, 2) }}</p></div>
        <div><p class="text-xs uppercase text-gray-500">Frequency</p><p class="mt-1 text-lg font-semibold text-white">{{ ucfirst($investment->recurring_frequency) }}</p></div>
        <div><p class="text-xs uppercase text-gray-500">Due date</p><p class="mt-1 text-white">{{ $dueAt->format('M j, Y H:i') }}</p></div>
        <div><p class="text-xs uppercase text-gray-500">Payment status</p><p class="mt-1 text-white">Pending admin approval after payment</p></div>
    </section>

    <section class="rounded-xl border border-dark-100 bg-dark-200 p-5 md:p-7">
        <h2 class="text-lg font-semibold text-white">Choose how to pay</h2>
        <p class="mt-1 text-sm text-gray-400">The amount is fixed to your saved recurring schedule. Your payment is a user-funded contribution, not investment profit.</p>

        <div class="mt-5 rounded-xl border border-dark-100 bg-dark-100 p-4 sm:p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="font-semibold text-white">Subscription Balance</h3>
                    <p class="mt-1 text-sm text-gray-400">Available: {{ $subscriptionBalanceCurrency }}{{ number_format($subscriptionBalance, 2) }}</p>
                    <p class="mt-1 text-xs text-gray-500">Funds are deducted only after you click the button below. Admin approval is still required.</p>
                </div>
                <form method="POST" action="{{ route('compound-investment.recurring-topup.start', $investment) }}">
                    @csrf
                    <input type="hidden" name="payment_source" value="subscription_balance">
                    <button type="submit" {{ $subscriptionBalance + 0.00001 < (float) $investment->recurring_amount ? 'disabled' : '' }}
                        class="rounded-lg bg-primary px-5 py-3 font-bold text-white hover:bg-primary-dark disabled:cursor-not-allowed disabled:opacity-50">
                        Pay from Subscription Balance
                    </button>
                </form>
            </div>
            @if($subscriptionBalance + 0.00001 < (float) $investment->recurring_amount)
                <p class="mt-3 text-sm text-amber-300">Your Subscription Balance is insufficient for this payment.</p>
            @endif
        </div>

        <div class="mt-5 rounded-xl border border-dark-100 bg-dark-100 p-4 sm:p-5">
            <h3 class="font-semibold text-white">Deposit</h3>
            <p class="mt-1 text-sm text-gray-400">Pay using an enabled cryptocurrency deposit method and upload payment proof in the existing deposit workflow.</p>
            @if($methods->isEmpty())
                <p class="mt-4 rounded-lg bg-yellow-500/10 p-4 text-sm text-yellow-300">No enabled cryptocurrency deposit networks are configured.</p>
            @else
                <form method="POST" action="{{ route('compound-investment.recurring-topup.start', $investment) }}" class="mt-4 space-y-4">
                    @csrf
                    <input type="hidden" name="payment_source" value="direct_deposit">
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach($methods as $method)
                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-dark-100 bg-dark-200 p-4 hover:border-primary/60">
                                <input type="radio" name="payment_method_id" value="{{ $method->id }}" required class="accent-emerald-500">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-dark-300 text-primary">
                                    @if(!empty($method->img_url) && stripos(trim($method->img_url), 'https://') === 0)
                                        <img src="{{ $method->img_url }}" alt="{{ $method->name }} logo" class="h-full w-full object-contain" onerror="this.hidden=true;this.nextElementSibling.hidden=false">
                                        <i hidden class="fas fa-coins" aria-hidden="true"></i>
                                    @else
                                        <i class="fas fa-coins" aria-hidden="true"></i>
                                    @endif
                                </span>
                                <span class="min-w-0">
                                    <span class="block font-semibold text-white">{{ $method->name }}</span>
                                    <span class="mt-1 block text-xs text-gray-400">{{ $method->network ?: 'Default network' }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <button type="submit" class="rounded-lg border border-primary px-6 py-3 font-bold text-primary hover:bg-primary/10">Continue to Deposit</button>
                </form>
            @endif
        </div>
    </section>
</div>
@endsection
