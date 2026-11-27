@extends('layouts.dash')
@section('title', $title)
@section('content')
<div class="space-y-8">
    <div>
        <h1 class="text-2xl font-bold text-white md:text-3xl">Request Withdrawal</h1>
        <p class="mt-1 text-sm text-gray-400">Choose an asset type, asset and configured payment network.</p>
    </div>
    <x-danger-alert /><x-success-alert />

    @if ($settings->enable_with == 'false')
        <div class="rounded-xl bg-dark-200 p-8 text-center">
            <h2 class="text-xl font-semibold text-white">Withdrawals Are Temporarily Unavailable</h2>
            <p class="mt-2 text-gray-400">Withdrawals are temporarily disabled by the administrator.</p>
        </div>
    @else
        <section class="space-y-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-primary">Step 1</p>
                <h2 class="mt-1 text-lg font-semibold text-white">Choose withdrawal type</h2>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <a href="{{ route('withdrawals', ['type' => 'crypto']) }}" @class(['rounded-xl border p-5 transition', 'border-primary bg-primary/10' => $type === 'crypto', 'border-dark-100 bg-dark-200 hover:border-primary/50' => $type !== 'crypto'])>
                    <span class="flex items-center gap-3">
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-dark-300 text-xl text-primary"><i class="fab fa-bitcoin"></i></span>
                        <span><span class="block font-semibold text-white">Cryptocurrency</span><span class="mt-1 block text-sm text-gray-400">Crypto assets and configured networks</span></span>
                    </span>
                </a>
                <a href="{{ route('withdrawals', ['type' => 'currency']) }}" @class(['rounded-xl border p-5 transition', 'border-primary bg-primary/10' => $type === 'currency', 'border-dark-100 bg-dark-200 hover:border-primary/50' => $type !== 'currency'])>
                    <span class="flex items-center gap-3">
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-dark-300 text-xl text-primary"><i class="fas fa-money-bill-wave"></i></span>
                        <span><span class="block font-semibold text-white">Currency</span><span class="mt-1 block text-sm text-gray-400">Fiat currencies and configured bank methods</span></span>
                    </span>
                </a>
            </div>
        </section>

        @if($type)
            <section class="space-y-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-primary">Step 2</p>
                    <h2 class="mt-1 text-lg font-semibold text-white">Choose {{ $type === 'crypto' ? 'a cryptocurrency' : 'a currency' }}</h2>
                </div>
                @if(empty($availableAssets))
                    <p class="rounded-lg bg-dark-200 p-5 text-sm text-gray-400">No enabled {{ $type === 'crypto' ? 'cryptocurrency' : 'currency' }} withdrawal options are currently configured.</p>
                @else
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                        @foreach($availableAssets as $available)
                            <a href="{{ route('withdrawals', ['type' => $type, 'asset' => $available['value']]) }}"
                                @class(['rounded-xl border p-4 transition', 'border-primary bg-primary/10' => $asset === $available['value'], 'border-dark-100 bg-dark-200 hover:border-primary/50' => $asset !== $available['value']])>
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-dark-300 font-bold text-primary">{{ $available['symbol'] }}</span>
                                <span class="mt-3 block font-semibold text-white">{{ $available['label'] }}</span>
                                <span class="mt-1 block text-xs text-gray-400">{{ count($available['methods']) }} configured {{ count($available['methods']) === 1 ? 'method' : 'methods' }}</span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif

        @if($asset)
            <section class="space-y-4">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-primary">Step 3</p>
                        <h2 class="mt-1 text-lg font-semibold text-white">Select payment method for {{ $asset }}</h2>
                    </div>
                    <a href="{{ route('withdrawals', ['type' => $type]) }}" class="text-sm font-semibold text-primary hover:underline">Back to assets</a>
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    @foreach($selectedMethods as $method)
                        <article class="rounded-xl border border-dark-100 bg-dark-200 p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="font-semibold text-white">{{ $method['name'] }}</h3>
                                    @if($method['network'])<p class="mt-1 text-sm text-primary">{{ $method['network'] }}</p>@endif
                                    @if($method['duration'])<p class="mt-1 text-xs text-gray-400">{{ $method['duration'] }}</p>@endif
                                </div>
                                <i class="fas fa-wallet text-xl text-primary"></i>
                            </div>
                            <dl class="mt-5 space-y-2 text-sm">
                                <div class="flex justify-between gap-3"><dt class="text-gray-400">Minimum</dt><dd class="text-white">{{ $type === 'currency' ? $defaultCurrency['symbol'] : '' }}{{ number_format((float) $method['minimum'], 2) }}</dd></div>
                                <div class="flex justify-between gap-3"><dt class="text-gray-400">Maximum</dt><dd class="text-white">{{ $type === 'currency' ? $defaultCurrency['symbol'] : '' }}{{ number_format((float) $method['maximum'], 2) }}</dd></div>
                                <div class="flex justify-between gap-3"><dt class="text-gray-400">Charge</dt><dd class="text-white">{{ strtolower((string) $method['charges_type']) === 'percentage' ? $method['charges_amount'] . '%' : (($type === 'currency' ? $defaultCurrency['symbol'] : '') . number_format((float) $method['charges_amount'], 2)) }}</dd></div>
                            </dl>
                            <form action="{{ route('withdrawamount') }}" method="POST" class="mt-5">
                                @csrf
                                <input type="hidden" name="method_id" value="{{ $method['id'] }}">
                                <input type="hidden" name="method_type" value="{{ $type }}">
                                <input type="hidden" name="asset" value="{{ $asset }}">
                                <button type="submit" class="w-full rounded-lg bg-primary px-4 py-3 font-semibold text-white hover:bg-primary-dark">Continue</button>
                            </form>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    @endif
</div>
@endsection
