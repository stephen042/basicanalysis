@extends('layouts.dash')
@section('title', $title)
@section('content')
    <div x-data="withdrawalSelector(@json($cryptoAssets), @json($fiatCurrencies), @json($defaultCurrency))" x-cloak>
        <!-- Page title -->
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <h1 class="text-2xl font-bold text-white md:text-3xl">Request Withdrawal</h1>
                <p class="mt-1 text-sm text-gray-400">Choose what you want to withdraw and how you want to receive it.</p>
            </div>
        </div>

        <x-danger-alert />
        <x-success-alert />

        @if ($settings->enable_with == 'false')
            <div class="py-24 text-center">
                <div class="flex items-center justify-center w-20 h-20 mx-auto mb-6 text-yellow-400 bg-dark-100 rounded-full">
                    <i class="text-4xl fas fa-exclamation-circle"></i>
                </div>
                <h3 class="text-xl font-semibold text-white">Withdrawals Are Temporarily Unavailable</h3>
                <p class="mt-2 text-gray-400">Withdrawals are temporarily disabled by the administrator. Please check back later.</p>
            </div>
        @else
            <!-- Step 1: Choose Cryptocurrency or Currency -->
            <div class="mt-8">
                <h2 class="mb-4 text-sm font-semibold tracking-wide text-gray-400 uppercase">Step 1 · What do you want to withdraw?</h2>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <button type="button" @click="selectType('crypto')"
                        class="relative flex items-center gap-4 p-6 text-left transition-all duration-300 rounded-xl bg-dark-200 hover:bg-dark-100 hover:shadow-lg"
                        :class="type === 'crypto' ? 'ring-2 ring-primary-500' : ''">
                        <div class="flex items-center justify-center w-12 h-12 text-xl rounded-full bg-dark-300 text-primary-400">
                            <i class="fab fa-bitcoin"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-white">Cryptocurrency</h3>
                            <p class="text-sm text-gray-400">Withdraw Bitcoin, Ethereum, USDT and other supported crypto assets.</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-500"></i>
                    </button>

                    <button type="button" @click="selectType('currency')"
                        class="relative flex items-center gap-4 p-6 text-left transition-all duration-300 rounded-xl bg-dark-200 hover:bg-dark-100 hover:shadow-lg"
                        :class="type === 'currency' ? 'ring-2 ring-primary-500' : ''">
                        <div class="flex items-center justify-center w-12 h-12 text-xl rounded-full bg-dark-300 text-primary-400">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-white">Currency</h3>
                            <p class="text-sm text-gray-400">Withdraw regular currency ({{ $defaultCurrency['code'] }}{{ isset($fiatCurrencies) && count($fiatCurrencies) > 1 ? ' and more' : '' }}) via bank transfer.</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-500"></i>
                    </button>
                </div>
            </div>

            <!-- Step 2: Choose the specific asset -->
            <div class="mt-10" x-show="type !== null">
                <template x-if="type === 'crypto'">
                    <div>
                        <h2 class="mb-4 text-sm font-semibold tracking-wide text-gray-400 uppercase">Step 2 · Choose a cryptocurrency</h2>
                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                            <template x-for="(methods, assetName) in cryptoAssets" :key="assetName">
                                <button type="button" @click="selectCrypto(assetName)"
                                    class="p-5 text-center transition-all duration-300 rounded-xl bg-dark-200 hover:bg-dark-100"
                                    :class="selectedCrypto === assetName ? 'ring-2 ring-primary-500' : ''">
                                    <div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 rounded-full bg-dark-300 text-primary-400">
                                        <span class="text-lg font-bold" x-text="ticker(assetName)"></span>
                                    </div>
                                    <h3 class="font-semibold text-white" x-text="assetName"></h3>
                                    <p class="text-xs text-gray-400" x-text="methods.length + (methods.length === 1 ? ' network' : ' networks')"></p>
                                </button>
                            </template>
                        </div>
                        <p class="mt-6 text-center text-gray-500" x-show="Object.keys(cryptoAssets).length === 0">
                            No cryptocurrency withdrawal methods are currently available.
                        </p>
                    </div>
                </template>

                <template x-if="type === 'currency'">
                    <div>
                        <h2 class="mb-4 text-sm font-semibold tracking-wide text-gray-400 uppercase">Step 2 · Choose a currency</h2>
                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                            <template x-for="currency in fiatCurrencies" :key="currency.code">
                                <button type="button" @click="selectCurrency(currency.code)"
                                    class="p-5 text-center transition-all duration-300 rounded-xl bg-dark-200 hover:bg-dark-100"
                                    :class="selectedCurrency === currency.code ? 'ring-2 ring-primary-500' : ''">
                                    <div class="flex items-center justify-center w-12 h-12 mx-auto mb-3 rounded-full bg-dark-300 text-primary-400">
                                        <span class="text-lg font-bold" x-text="currency.symbol"></span>
                                    </div>
                                    <h3 class="font-semibold text-white" x-text="currency.code"></h3>
                                    <p class="text-xs text-gray-400" x-text="currency.methods.length + (currency.methods.length === 1 ? ' method' : ' methods')"></p>
                                </button>
                            </template>
                        </div>
                        <p class="mt-6 text-center text-gray-500" x-show="fiatCurrencies.length === 0">
                            No currency withdrawal methods are currently available.
                        </p>
                    </div>
                </template>
            </div>

            <!-- Step 3: Choose the withdrawal method -->
            <div class="mt-10" x-show="selectedMethods().length > 0">
                <h2 class="mb-4 text-sm font-semibold tracking-wide text-gray-400 uppercase">Step 3 · Choose a withdrawal method</h2>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <template x-for="method in selectedMethods()" :key="method.id">
                        <div class="flex flex-col p-6 transition-all duration-300 bg-dark-200 rounded-xl hover:bg-dark-100 hover:shadow-lg">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold text-white" x-text="method.name"></h3>
                                    <div class="flex flex-wrap items-center gap-2 mt-1">
                                        <span x-show="method.network"
                                            class="px-2 py-0.5 text-xs font-semibold text-primary-400 bg-primary-500/10 rounded-md"
                                            x-text="method.network"></span>
                                        <span x-show="method.duration" class="text-xs text-gray-400" x-text="method.duration"></span>
                                    </div>
                                </div>
                                <div class="flex items-center justify-center w-12 h-12 text-xl rounded-full bg-dark-300 text-primary-400">
                                    <i class="fas fa-wallet"></i>
                                </div>
                            </div>

                            <div class="mt-6 space-y-3 text-sm">
                                <div class="p-3 rounded-lg bg-dark-300/50">
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Minimum</span>
                                        <span class="font-medium text-white" x-text="currencySymbol() + formatNumber(method.minimum)"></span>
                                    </div>
                                </div>
                                <div class="p-3 rounded-lg bg-dark-300/50">
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Maximum</span>
                                        <span class="font-medium text-white" x-text="currencySymbol() + formatNumber(method.maximum)"></span>
                                    </div>
                                </div>
                                <div class="p-3 rounded-lg bg-dark-300/50">
                                    <div class="flex justify-between">
                                        <span class="text-gray-400">Charge</span>
                                        <span class="font-medium text-white" x-text="formatCharge(method)"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6">
                                <form action="{{ route('withdrawamount') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="method_id" :value="method.id">
                                    <button type="submit"
                                        class="w-full px-4 py-2.5 font-semibold text-center text-white transition-colors duration-200 rounded-lg bg-primary-600 hover:bg-primary-700">
                                        Continue
                                    </button>
                                </form>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        @endif
    </div>

    <script>
        function withdrawalSelector(cryptoAssets, fiatCurrencies, defaultCurrency) {
            return {
                type: null,
                selectedCrypto: null,
                selectedCurrency: null,
                cryptoAssets: cryptoAssets || {},
                fiatCurrencies: fiatCurrencies || [],
                defaultCurrency: defaultCurrency || { code: 'USD', symbol: '$' },

                selectType(t) {
                    this.type = t;
                    this.selectedCrypto = null;
                    this.selectedCurrency = null;
                },
                selectCrypto(assetName) {
                    this.selectedCrypto = assetName;
                },
                selectCurrency(code) {
                    this.selectedCurrency = code;
                },
                selectedMethods() {
                    if (this.type === 'crypto' && this.selectedCrypto) {
                        return this.cryptoAssets[this.selectedCrypto] || [];
                    }
                    if (this.type === 'currency' && this.selectedCurrency) {
                        const currency = this.fiatCurrencies.find(c => c.code === this.selectedCurrency);
                        return currency ? currency.methods : [];
                    }
                    return [];
                },
                currencySymbol() {
                    if (this.type === 'currency') {
                        const currency = this.fiatCurrencies.find(c => c.code === this.selectedCurrency);
                        return currency ? currency.symbol : this.defaultCurrency.symbol;
                    }
                    return this.defaultCurrency.symbol;
                },
                ticker(name) {
                    const map = { Bitcoin: 'BTC', Ethereum: 'ETH', Litecoin: 'LTC', USDT: 'USDT', Solana: 'SOL', BUSD: 'BUSD' };
                    return map[name] || (name ? name.slice(0, 3).toUpperCase() : '');
                },
                formatNumber(value) {
                    if (value === null || value === undefined || value === '') return '0';
                    const n = Number(value);
                    return Number.isFinite(n) ? n.toLocaleString(undefined, { maximumFractionDigits: 2 }) : '0';
                },
                formatCharge(method) {
                    const type = String(method.charges_type || '').toLowerCase();
                    if (type === 'percentage') {
                        return (method.charges_amount || 0) + '%';
                    }
                    return this.currencySymbol() + this.formatNumber(method.charges_amount);
                },
            };
        }
    </script>
@endsection
