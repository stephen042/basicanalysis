@extends('layouts.dash')
@section('title', $title)
@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">Start Compounding</h1>
            <p class="mt-1 text-sm text-gray-400">Build a scenario, submit your investment request in USDT, then complete payment for admin review.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('compound-investment.history') }}" class="rounded-lg border border-dark-100 px-4 py-2 text-sm text-gray-200">Investment history</a>
            <a href="{{ route('compoundinterest') }}" class="rounded-lg border border-dark-100 px-4 py-2 text-sm text-gray-200">Back to calculator</a>
        </div>
    </div>
    <x-danger-alert /><x-success-alert />
    @if($errors->any())<div class="rounded-lg bg-red-500/10 p-3 text-sm text-red-300">{{ $errors->first() }}</div>@endif

    <section class="rounded-xl border border-dark-100 bg-dark-200 p-5 md:p-7">
        <div class="mb-5">
            <h2 class="text-xl font-semibold text-white">Your scenario</h2>
            <p class="text-sm text-gray-400">The monthly return and performance rate are read-only. Projection is illustrative only.</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <label class="rounded-xl bg-dark-100 p-4 text-xs uppercase tracking-wider text-gray-400">Starting amount (USDT)
                <input id="ci-start" type="number" min="1" step="0.01" value="{{ old('amount', 1000) }}" class="mt-2 w-full bg-transparent text-2xl font-bold text-white">
            </label>
            <label class="rounded-xl bg-dark-100 p-4 text-xs uppercase tracking-wider text-gray-400">Monthly return
                <input readonly value="27.8%" class="mt-2 w-full cursor-not-allowed bg-transparent text-2xl font-bold text-primary">
            </label>
            <label class="rounded-xl bg-dark-100 p-4 text-xs uppercase tracking-wider text-gray-400">Performance rate
                <input readonly value="{{ number_format($performanceRate, 2) }}%" class="mt-2 w-full cursor-not-allowed bg-transparent text-2xl font-bold text-primary">
            </label>
            <label class="rounded-xl bg-dark-100 p-4 text-xs uppercase tracking-wider text-gray-400">Duration
                <select id="ci-duration" class="mt-2 w-full bg-transparent text-lg font-bold text-white">
                    @foreach([1,3,6,12] as $months)
                        <option value="{{ $months }}" {{ old('duration_months', 3) == $months ? 'selected' : '' }}>{{ $months }} {{ $months === 1 ? 'month' : 'months' }}</option>
                    @endforeach
                </select>
            </label>
            <label class="rounded-xl bg-dark-100 p-4 text-xs uppercase tracking-wider text-gray-400">Recurring top-up (USDT)
                <input id="ci-recurring" type="number" min="0" step="0.01" value="{{ old('recurring_amount', 0) }}" class="mt-2 w-full bg-transparent text-2xl font-bold text-white">
            </label>
            <label class="rounded-xl bg-dark-100 p-4 text-xs uppercase tracking-wider text-gray-400">Frequency
                <select id="ci-frequency" class="mt-2 w-full bg-transparent text-lg font-bold text-white">
                    <option value="daily" {{ old('recurring_frequency') === 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="weekly" {{ old('recurring_frequency', 'weekly') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                </select>
            </label>
            <div class="rounded-xl bg-primary/10 p-4 sm:col-span-2">
                <p class="text-xs uppercase tracking-wider text-gray-400">Illustrative projected value</p>
                <p id="ci-projection" class="mt-2 text-3xl font-black text-primary">{{ $currency }}0.00</p>
                <p class="mt-1 text-xs text-gray-500">Assumes a constant 27.8% monthly return and periodic deposits. This is not a guarantee.</p>
            </div>
        </div>
    </section>

    @php
        $oldPaymentSource = old('payment_source');
        $defaultPaymentSource = in_array($oldPaymentSource, ['subscription_balance', 'direct_deposit'], true)
            ? $oldPaymentSource
            : ($methods->isNotEmpty() ? 'direct_deposit' : 'subscription_balance');
        $oldMethodId = old('payment_method_id');
        $defaultUsdtMethodId = $oldMethodId && $methods->contains('id', (int) $oldMethodId)
            ? (string) $oldMethodId
            : (string) optional($methods->first())->id;
    @endphp
    <section class="rounded-xl border border-dark-100 bg-dark-200 p-5 md:p-7">
        <h2 class="text-xl font-semibold text-white">Payment method</h2>
        <p class="mt-1 text-sm text-gray-400">Pay from your dashboard Subscription Balance, or continue with the existing direct USDT deposit and admin approval process.</p>
        <form method="POST" action="{{ route('compound-investment.store') }}"
            class="mt-5 space-y-5"
            x-data='{
                paymentSource: @json($defaultPaymentSource),
                directDepositMethodId: @json((string) $defaultUsdtMethodId),
                subscriptionBalance: @json((float) $subscriptionBalance),
                amount: @json((float) old("amount", 1000)),
                get hasEnoughSubscriptionBalance() { return this.subscriptionBalance >= this.amount; }
            }'>
            @csrf
            <input type="hidden" name="submission_key" value="{{ \Illuminate\Support\Str::random(48) }}">
            <input type="hidden" name="payment_source" :value="paymentSource">

            <div class="grid gap-3 sm:grid-cols-2">
                <button type="button" @click="paymentSource = 'subscription_balance'"
                    :aria-pressed="paymentSource === 'subscription_balance'"
                    class="rounded-xl border p-4 text-left transition"
                    :class="paymentSource === 'subscription_balance' ? 'border-primary bg-primary/10' : 'border-dark-100 bg-dark-100 hover:border-primary/50'">
                    <span class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary"><i class="fas fa-wallet"></i></span>
                        <span><span class="block font-semibold text-white">Subscription Balance</span><span class="mt-1 block text-xs text-gray-400">Available: {{ $subscriptionBalanceCurrency }}{{ number_format($subscriptionBalance, 2) }}</span></span>
                    </span>
                </button>
                <button type="button" @click="paymentSource = 'direct_deposit'"
                    :aria-pressed="paymentSource === 'direct_deposit'"
                    class="rounded-xl border p-4 text-left transition"
                    :class="paymentSource === 'direct_deposit' ? 'border-primary bg-primary/10' : 'border-dark-100 bg-dark-100 hover:border-primary/50'">
                    <span class="flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary"><i class="fas fa-building-columns"></i></span>
                        <span><span class="block font-semibold text-white">Direct Deposit</span><span class="mt-1 block text-xs text-gray-400">Pay by USDT and submit a receipt for approval</span></span>
                    </span>
                </button>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <label class="text-sm text-gray-300">Initial investment amount (USDT)
                    <input name="amount" id="ci-deposit-amount" type="number" min="1" step="0.01" required
                        value="{{ old('amount', 1000) }}" x-model.number="amount"
                        class="mt-2 w-full rounded-lg border border-dark-100 bg-dark-300 px-3 py-3 text-white">
                </label>
                <label class="text-sm text-gray-300">Investment duration
                    <select name="duration_months" id="ci-deposit-duration" class="mt-2 w-full rounded-lg border border-dark-100 bg-dark-300 px-3 py-3 text-white">
                        @foreach([1,3,6,12] as $months)
                            <option value="{{ $months }}" {{ old('duration_months', 3) == $months ? 'selected' : '' }}>{{ $months }} {{ $months === 1 ? 'month' : 'months' }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-sm text-gray-300">Recurring top-up (USDT)
                    <input name="recurring_amount" id="ci-deposit-recurring" type="number" min="0" step="0.01" value="{{ old('recurring_amount', 0) }}" class="mt-2 w-full rounded-lg border border-dark-100 bg-dark-300 px-3 py-3 text-white">
                </label>
                <label class="text-sm text-gray-300">Top-up frequency
                    <select name="recurring_frequency" id="ci-deposit-frequency" class="mt-2 w-full rounded-lg border border-dark-100 bg-dark-300 px-3 py-3 text-white">
                        <option value="daily" {{ old('recurring_frequency') === 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="weekly" {{ old('recurring_frequency', 'weekly') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                    </select>
                </label>
            </div>

            <div x-show="paymentSource === 'subscription_balance'" x-cloak>
                <div class="rounded-lg border border-dark-100 bg-dark-100 p-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="text-sm text-gray-400">Subscription Balance</span>
                        <span class="font-semibold text-white">{{ $subscriptionBalanceCurrency }}{{ number_format($subscriptionBalance, 2) }}</span>
                    </div>
                    <p x-show="!hasEnoughSubscriptionBalance" class="mt-3 rounded-lg bg-red-500/10 p-3 text-sm text-red-300">
                        Insufficient Subscription Balance. Fund your account or choose Direct Deposit.
                    </p>
                    <p x-show="hasEnoughSubscriptionBalance" class="mt-3 text-xs text-gray-500">
                        The full investment amount is deducted atomically after server-side balance validation.
                    </p>
                </div>
            </div>

            <fieldset x-show="paymentSource === 'direct_deposit'" x-cloak>
                <legend class="mb-2 text-sm font-semibold text-gray-200">Choose an enabled USDT payment configuration</legend>
                <input type="hidden" name="payment_method_id" :value="directDepositMethodId" :disabled="paymentSource !== 'direct_deposit'">
                @if($methods->isEmpty())
                    <p class="rounded-lg bg-yellow-500/10 p-4 text-sm text-yellow-300">No enabled USDT direct-deposit method is configured. Choose Subscription Balance or contact support.</p>
                @else
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($methods as $method)
                            <button type="button" @click="directDepositMethodId = '{{ $method->id }}'"
                                :aria-pressed="directDepositMethodId === '{{ $method->id }}'"
                                class="flex min-h-20 items-center gap-3 rounded-xl border p-4 text-left transition"
                                :class="directDepositMethodId === '{{ $method->id }}' ? 'border-primary bg-primary/10' : 'border-dark-100 bg-dark-100 hover:border-primary/50'">
                                @if($method->img_url)
                                    <img src="{{ $method->img_url }}" alt="" class="h-10 w-10 rounded-full object-contain">
                                @else
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-dark-300 text-xl font-bold text-primary">₮</span>
                                @endif
                                <span class="min-w-0">
                                    <span class="block font-semibold text-white">{{ $method->name }}</span>
                                    <span class="mt-1 block text-xs text-gray-400">{{ ucfirst($method->methodtype ?: 'crypto') }}{{ $method->network ? ' · ' . $method->network : '' }}</span>
                                </span>
                                <i class="fas fa-check-circle ml-auto text-primary" aria-hidden="true"></i>
                            </button>
                        @endforeach
                    </div>
                @endif
            </fieldset>

            @error('payment_method_id')<p class="text-sm text-red-400">{{ $message }}</p>@enderror
            <div class="pt-2">
                <button type="submit"
                    :disabled="paymentSource === 'subscription_balance' ? !hasEnoughSubscriptionBalance : !directDepositMethodId"
                    class="rounded-lg bg-primary px-6 py-3 font-bold text-white transition hover:bg-primary-dark disabled:cursor-not-allowed disabled:opacity-50">
                    <span x-text="paymentSource === 'subscription_balance' ? 'Pay from Subscription Balance' : 'Continue to Direct Deposit'"></span>
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
@section('scripts')
@parent
<script>
(function () {
    var currency = @json($currency);
    var start = document.getElementById('ci-start');
    var duration = document.getElementById('ci-duration');
    var recurring = document.getElementById('ci-recurring');
    var frequency = document.getElementById('ci-frequency');
    var depositAmount = document.getElementById('ci-deposit-amount');
    var depositDuration = document.getElementById('ci-deposit-duration');
    var depositRecurring = document.getElementById('ci-deposit-recurring');
    var depositFrequency = document.getElementById('ci-deposit-frequency');

    function calculate() {
        var months = Number(duration.value) || 1;
        var factor = frequency.value === 'daily' ? 30 : 4.345;
        var periods = Math.ceil(months * factor);
        var balance = Number(start.value) || 0;
        var contribution = Number(recurring.value) || 0;
        var monthlyRate = 0.278;
        for (var i = 0; i < periods; i++) {
            balance *= Math.pow(1 + monthlyRate, 1 / factor);
            balance += contribution;
        }
        document.getElementById('ci-projection').textContent = currency + balance.toLocaleString(undefined, {
            minimumFractionDigits: 2, maximumFractionDigits: 2
        });
        depositAmount.value = start.value;
        depositAmount.dispatchEvent(new Event('input', { bubbles: true }));
        depositDuration.value = duration.value;
        depositRecurring.value = recurring.value;
        depositFrequency.value = frequency.value;
    }

    [start, duration, recurring, frequency].forEach(function (field) {
        field.addEventListener('input', calculate);
        field.addEventListener('change', calculate);
    });
    calculate();
})();
</script>
@endsection
