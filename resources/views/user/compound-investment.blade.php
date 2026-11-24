@extends('layouts.dash')
@section('title', $title)
@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-white">Start Compounding</h1>
            <p class="mt-1 text-sm text-gray-400">Build a scenario, submit your investment request, then complete payment for admin review.</p>
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
            <label class="rounded-xl bg-dark-100 p-4 text-xs uppercase tracking-wider text-gray-400">Starting amount
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
            <label class="rounded-xl bg-dark-100 p-4 text-xs uppercase tracking-wider text-gray-400">Recurring top-up
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

    <section class="rounded-xl border border-dark-100 bg-dark-200 p-5 md:p-7">
        <h2 class="text-xl font-semibold text-white">Initial deposit request</h2>
        <p class="mt-1 text-sm text-gray-400">Choose an existing enabled deposit method. You’ll receive its configured payment details on the next page. Investment activation requires admin payment approval.</p>
        @if($methods->isEmpty())
            <div class="mt-4 rounded-lg bg-yellow-500/10 p-4 text-sm text-yellow-300">No enabled deposit payment methods are configured.</div>
        @else
            <form method="POST" action="{{ route('compound-investment.store') }}" class="mt-5 grid gap-4 sm:grid-cols-2">
                @csrf
                <input type="hidden" name="submission_key" value="{{ \Illuminate\Support\Str::random(48) }}">
                <label class="text-sm text-gray-300">Initial investment amount ({{ $currency }})
                    <input name="amount" id="ci-deposit-amount" type="number" min="1" step="0.01" required value="{{ old('amount', 1000) }}" class="mt-2 w-full rounded-lg border border-dark-100 bg-dark-300 px-3 py-3 text-white">
                </label>
                <label class="text-sm text-gray-300">Investment duration
                    <select name="duration_months" id="ci-deposit-duration" class="mt-2 w-full rounded-lg border border-dark-100 bg-dark-300 px-3 py-3 text-white">
                        @foreach([1,3,6,12] as $months)
                            <option value="{{ $months }}" {{ old('duration_months', 3) == $months ? 'selected' : '' }}>{{ $months }} {{ $months === 1 ? 'month' : 'months' }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="text-sm text-gray-300">Daily / weekly recurring top-up ({{ $currency }})
                    <input name="recurring_amount" id="ci-deposit-recurring" type="number" min="0" step="0.01" value="{{ old('recurring_amount', 0) }}" class="mt-2 w-full rounded-lg border border-dark-100 bg-dark-300 px-3 py-3 text-white">
                </label>
                <label class="text-sm text-gray-300">Top-up frequency
                    <select name="recurring_frequency" id="ci-deposit-frequency" class="mt-2 w-full rounded-lg border border-dark-100 bg-dark-300 px-3 py-3 text-white">
                        <option value="daily" {{ old('recurring_frequency') === 'daily' ? 'selected' : '' }}>Daily</option>
                        <option value="weekly" {{ old('recurring_frequency', 'weekly') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                    </select>
                </label>
                <fieldset class="sm:col-span-2">
                    <legend class="mb-2 text-sm font-semibold text-gray-200">Choose a payment method / currency</legend>
                    <p class="mb-3 text-xs text-gray-400">Select the coin or payment method you will use, such as Bitcoin, Ethereum, or USDT.</p>
                    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @php
                            $methodSymbols = ['Bitcoin' => '₿', 'Ethereum' => '◆', 'Litecoin' => 'Ł', 'USDT' => '₮', 'BUSD' => 'B', 'Solana' => '◎'];
                            $selectedMethod = old('payment_method_id', $methods->first() ? $methods->first()->id : null);
                        @endphp
                        @foreach($methods as $method)
                            <label class="group relative cursor-pointer">
                                <input class="peer sr-only" type="radio" name="payment_method_id" value="{{ $method->id }}" required
                                    {{ (string) $selectedMethod === (string) $method->id ? 'checked' : '' }}>
                                <span class="flex min-h-20 items-center gap-3 rounded-xl border border-dark-100 bg-dark-100 p-4 transition group-hover:border-primary peer-checked:border-primary peer-checked:bg-primary/10">
                                    @if($method->img_url)
                                        <img src="{{ $method->img_url }}" alt="" class="h-10 w-10 rounded-full object-contain">
                                    @else
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-dark-300 text-xl font-bold text-primary">{{ $methodSymbols[$method->name] ?? strtoupper(substr($method->name, 0, 1)) }}</span>
                                    @endif
                                    <span class="min-w-0">
                                        <span class="block font-semibold text-white">{{ $method->name }}</span>
                                        <span class="mt-1 block text-xs text-gray-400">
                                            {{ ucfirst($method->methodtype ?: 'payment') }}{{ $method->network ? ' · ' . $method->network : '' }}
                                        </span>
                                    </span>
                                    <span class="ml-auto hidden text-primary peer-checked:block"><i class="fas fa-check-circle"></i></span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('payment_method_id')<p class="mt-2 text-sm text-red-400">{{ $message }}</p>@enderror
                </fieldset>
                <div class="sm:col-span-2">
                    <button class="rounded-lg bg-primary px-6 py-3 font-bold text-white hover:bg-primary-dark">Continue to payment</button>
                </div>
            </form>
        @endif
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
