@extends('layouts.dash')
@section('title', $title)
@section('content')
    <!-- Page title & actions -->
    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="text-2xl font-bold text-white md:text-3xl">Portfolio Overview</h1>
            <p class="mt-1 text-sm text-gray-400">Monitor your trading performance in real-time</p>
        </div>
        <div>
            @if ($settings->wallet_status == 'on')
                <a href="{{ route('connect-wallet') }}"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 sm:py-3 bg-gradient-to-r from-primary-600 to-primary-500 text-white rounded-lg shadow hover:from-indigo-700 transition animate-pulse text-sm sm:text-base">
                    <i class="fas fa-link w-4 h-4 sm:w-5 sm:h-5"></i> Connect Wallet
                </a>
            @endif
        </div>
    </div>

    <x-danger-alert />
    <x-success-alert />
    <x-pay-alert />
    <!--
            @if (!empty($settings->welcome_message) && Auth::user()->created_at->diffInDays() <= 3)
    <div class="p-4 mb-4 text-sm border rounded-lg bg-dark-200 text-gray-300 border-dark-100" role="alert">
                    {{ $settings->welcome_message }}
                </div>
    @endif
            @if ($settings->enable_annoc == 'on' && !empty($settings->newupdate))
    <div class="p-4 mb-4 text-sm border rounded-lg bg-dark-200 text-gray-300 border-dark-100" role="alert">
                    {{ $settings->newupdate }}
                </div>
    @endif -->

    <!-- Signal Strength Progress Bar -->
    @if (Auth::user()->signal_strength_enabled)
        <div class="p-5 mb-6 bg-dark-200 rounded-xl">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-lg font-semibold text-white">Trading Signal Strength</h3>
                <span class="px-3 py-1 text-sm font-medium text-white bg-primary-600 rounded-full">
                    {{ Auth::user()->signal_strength_value }}%
                </span>
            </div>
            <div class="relative w-full h-4 bg-dark-100 rounded-full overflow-hidden">
                <div class="h-full transition-all duration-300 rounded-full {{ Auth::user()->signal_strength_value >= 70 ? 'bg-gradient-to-r from-green-500 to-green-400' : (Auth::user()->signal_strength_value >= 40 ? 'bg-gradient-to-r from-yellow-500 to-yellow-400' : 'bg-gradient-to-r from-red-500 to-red-400') }}"
                    style="width: {{ Auth::user()->signal_strength_value }}%;">
                </div>
            </div>
            <div class="flex justify-between mt-2 text-xs text-gray-400">
                <span>Weak</span>
                <span>Moderate</span>
                <span>Strong</span>
            </div>
            <p class="mt-3 text-sm text-gray-400">
                @if (Auth::user()->signal_strength_value >= 70)
                    <span class="text-green-400">✓ Strong Signal:</span> Optimal trading conditions detected
                @elseif (Auth::user()->signal_strength_value >= 40)
                    <span class="text-yellow-400">⚠ Moderate Signal:</span> Trading conditions are fair
                @else
                    <span class="text-red-400">✗ Weak Signal:</span> Trading conditions need improvement
                @endif
            </p>
        </div>
    @endif

    <!-- Custom Admin Notification -->
    @if (Auth::user()->notification_enabled && !empty(Auth::user()->notification_message))
        <div class="relative p-5 mb-6 overflow-hidden bg-gradient-to-r from-primary-600 to-primary-800 rounded-xl">
            <div
                class="absolute top-0 right-0 w-40 h-40 transform translate-x-16 -translate-y-16 bg-white opacity-5 rounded-full">
            </div>
            <div
                class="absolute bottom-0 left-0 w-32 h-32 transform -translate-x-10 translate-y-10 bg-white opacity-5 rounded-full">
            </div>
            <div class="relative flex items-start">
                <div
                    class="flex items-center justify-center flex-shrink-0 w-12 h-12 mr-4 bg-white rounded-full bg-opacity-20">
                    <i class="text-2xl text-white fas fa-bell"></i>
                </div>
                <div class="flex-1">
                    <h3 class="mb-2 text-lg font-bold text-white">Important Notification</h3>
                    <p class="text-white text-opacity-95">{{ Auth::user()->notification_message }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6" id="stats-grid">
        <!-- Total Balance -->
        <div class="stats-card group relative overflow-hidden rounded-2xl bg-gradient-to-br from-gray-900 to-gray-800 border border-gray-800 hover:border-emerald-500/50 transition-all duration-300"
            style="animation-delay: 0.2s;">
            <div
                class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500">
            </div>
            <div
                class="absolute -inset-1 bg-gradient-to-r from-emerald-500/20 to-teal-500/20 rounded-2xl blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 -z-10">
            </div>

            <div class="relative p-6 space-y-4">
                <div class="flex items-start justify-between">
                    <div class="space-y-1">
                        <p class="text-gray-400 uppercase tracking-wider text-sm">Total Balance</p>
                        <div class="flex items-center gap-1 text-emerald-400">
                            <!--<span class="text-xs">↑ 8.2%</span>-->
                        </div>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-0 bg-emerald-500/20 blur-xl rounded-full"></div>
                        <div
                            class="relative p-3 bg-gradient-to-br from-emerald-500/20 to-teal-500/20 rounded-xl border border-emerald-500/30 backdrop-blur-sm">
                            <!-- ArrowDownToLine Icon -->
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 11l5 5m0 0l5-5m-5 5V4m-7 16h14" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="space-y-1">
                    <div class="text-white text-2xl font-medium transition-all duration-300 group-hover:text-emerald-50"
                        data-value="500000">{{ Auth::user()->currency }}{{ number_format($total_balance, 2, '.', ',') }}
                    </div>
                </div>
                <div
                    class="h-1 w-full bg-gradient-to-r from-gray-800 via-emerald-500/50 to-gray-800 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                </div>
            </div>
        </div>
        @if ($mod['investment'])
            <!-- Total Profit -->
            <div class="stats-card group relative overflow-hidden rounded-2xl bg-gradient-to-br from-gray-900 to-gray-800 border border-gray-800 hover:border-emerald-500/50 transition-all duration-300"
                style="animation-delay: 0.1s;">
                <div
                    class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                </div>
                <div
                    class="absolute -inset-1 bg-gradient-to-r from-emerald-500/20 to-teal-500/20 rounded-2xl blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 -z-10">
                </div>

                <div class="relative p-6 space-y-4">
                    <div class="flex items-start justify-between">
                        <div class="space-y-1">
                            <p class="text-gray-400 uppercase tracking-wider text-sm">Total Profit</p>
                            <div class="flex items-center gap-1 text-emerald-400">
                                <!--<span class="text-xs">↑ 0%</span>-->
                            </div>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-0 bg-emerald-500/20 blur-xl rounded-full"></div>
                            <div
                                class="relative p-3 bg-gradient-to-br from-emerald-500/20 to-teal-500/20 rounded-xl border border-emerald-500/30 backdrop-blur-sm">
                                <!-- TrendingUp Icon -->
                                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <div class="text-white text-2xl font-medium transition-all duration-300 group-hover:text-emerald-50"
                            data-value="0">
                            {{ Auth::user()->currency }}{{ number_format(Auth::user()->roi, 2, '.', ',') }}</div>
                    </div>
                    <div
                        class="h-1 w-full bg-gradient-to-r from-gray-800 via-emerald-500/50 to-gray-800 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    </div>
                </div>
            </div>
        @endif
        <!-- Sub Balance -->
        <div class="stats-card group relative overflow-hidden rounded-2xl bg-gradient-to-br from-gray-900 to-gray-800 border border-gray-800 hover:border-emerald-500/50 transition-all duration-300"
            style="animation-delay: 0s;">
            <div
                class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500">
            </div>
            <div
                class="absolute -inset-1 bg-gradient-to-r from-emerald-500/20 to-teal-500/20 rounded-2xl blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 -z-10">
            </div>

            <div class="relative p-6 space-y-4">
                <div class="flex items-start justify-between">
                    <div class="space-y-1">
                        <p class="text-gray-400 uppercase tracking-wider text-sm">Subscription Balance</p>
                        <div class="flex items-center gap-1 text-emerald-400">
                            <!--<span class="text-xs">↑ 12.5%</span>-->
                        </div>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-0 bg-emerald-500/20 blur-xl rounded-full"></div>
                        <div
                            class="relative p-3 bg-gradient-to-br from-emerald-500/20 to-teal-500/20 rounded-xl border border-emerald-500/30 backdrop-blur-sm">
                            <!-- Wallet Icon -->
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="space-y-1">
                    <div class="text-white text-2xl font-medium transition-all duration-300 group-hover:text-emerald-50"
                        data-value="495700">
                        {{ Auth::user()->currency }}{{ number_format(Auth::user()->account_bal, 2, '.', ',') }}</div>
                </div>
                <div
                    class="h-1 w-full bg-gradient-to-r from-gray-800 via-emerald-500/50 to-gray-800 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                </div>
            </div>
        </div>
        @if ($mod['investment'] || $mod['cryptoswap'])
            <!-- Total Withdrawals -->
            <div class="stats-card group relative overflow-hidden rounded-2xl bg-gradient-to-br from-gray-900 to-gray-800 border border-gray-800 hover:border-emerald-500/50 transition-all duration-300"
                style="animation-delay: 0.3s;">
                <div
                    class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                </div>
                <div
                    class="absolute -inset-1 bg-gradient-to-r from-emerald-500/20 to-teal-500/20 rounded-2xl blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 -z-10">
                </div>

                <div class="relative p-6 space-y-4">
                    <div class="flex items-start justify-between">
                        <div class="space-y-1">
                            <p class="text-gray-400 uppercase tracking-wider text-sm">Total Withdrawals</p>
                            <div class="flex items-center gap-1 text-red-400">
                                <!--<span class="text-xs">↓ 0%</span>-->
                            </div>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-0 bg-emerald-500/20 blur-xl rounded-full"></div>
                            <div
                                class="relative p-3 bg-gradient-to-br from-emerald-500/20 to-teal-500/20 rounded-xl border border-emerald-500/30 backdrop-blur-sm">
                                <!-- ArrowUpFromLine Icon -->
                                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 13l5-5m0 0l5 5m-5-5v12m-7-4h14" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-1">
                        <div class="text-white text-2xl font-medium transition-all duration-300 group-hover:text-emerald-50"
                            data-value="0">
                            {{ Auth::user()->currency }}{{ number_format($total_withdrawal, 2, '.', ',') }}</div>
                    </div>
                    <div
                        class="h-1 w-full bg-gradient-to-r from-gray-800 via-emerald-500/50 to-gray-800 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    </div>
                </div>
            </div>
        @endif
    </div>




    <!-- Main content grid -->
    <div class="grid grid-cols-1 gap-6 mt-6 lg:grid-cols-12">
        <!-- Left column -->
        <div class="space-y-6 lg:col-span-8">
            <!-- Performance Chart -->
            <div class="p-4 bg-dark-200 sm:p-6 rounded-xl">
                <h3 class="mb-4 text-lg font-semibold text-white">Portfolio Performance</h3>
                <div id="performance-chart" class="h-80"></div>
            </div>

            <!-- Active Investments -->
            @if ($mod['investment'])
                <div class="p-4 bg-dark-200 sm:p-6 rounded-xl">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-white">Active Trading Bots</h3>
                        <a href="{{ route('trading-bots.history') }}"
                            class="text-sm font-medium text-primary-500 hover:text-primary-400">View All</a>
                    </div>
                    <div class="space-y-4">
                        @forelse ($plans as $userBot)
                            <div class="flex items-center justify-between p-4 bg-dark-100 rounded-lg">
                                <div class="flex items-center">
                                    <div
                                        class="flex items-center justify-center w-10 h-10 text-primary-400 bg-primary-500/10 rounded-full">
                                        <i class="fas fa-robot"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="font-semibold text-white">{{ $userBot->tradingBot->name }}</p>
                                        <p class="text-sm text-gray-400">Amount:
                                            {{ Auth::user()->currency }}{{ number_format($userBot->amount) }}</p>
                                    </div>
                                </div>
                                <div class="hidden text-right sm:block">
                                    <p class="text-sm font-medium text-white">
                                        {{ \Carbon\Carbon::parse($userBot->end_date)->toFormattedDateString() }}</p>
                                    <p class="text-xs text-gray-400">End Date</p>
                                </div>
                                <div class="text-right">
                                    @if ($userBot->status == 'active')
                                        <span
                                            class="px-2 py-1 text-xs font-medium text-green-400 bg-green-500/10 rounded-full">Active</span>
                                    @else
                                        <span
                                            class="px-2 py-1 text-xs font-medium text-red-400 bg-red-500/10 rounded-full">{{ ucfirst($userBot->status) }}</span>
                                    @endif
                                </div>
                                <a href="{{ route('trading-bots.details', $userBot->id) }}"
                                    class="p-2 text-gray-400 rounded-md hover:bg-dark-300">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </div>
                        @empty
                            <div class="py-8 text-center">
                                <p class="mb-4 text-gray-400">You have no active trading bots.</p>
                                <a href="{{ route('trading-bots.index') }}"
                                    class="px-5 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700">Start
                                    Trading Bot</a>
                            </div>
                        @endforelse
                    </div>
                </div>
            @endif

            <!-- Active Copy Trading -->
            @if (isset($activeCopySubscriptions) && $activeCopySubscriptions->count() > 0)
                <div class="p-4 bg-dark-200 sm:p-6 rounded-xl">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-white">Active Copy Trading</h3>
                        <a href="{{ route('copy-trading.history') }}"
                            class="text-sm font-medium text-primary-500 hover:text-primary-400">View All</a>
                    </div>
                    <div class="space-y-4">
                        @foreach ($activeCopySubscriptions as $subscription)
                            <div class="flex items-center justify-between p-4 bg-dark-100 rounded-lg">
                                <div class="flex items-center">
                                    <div class="relative">
                                        <img src="{{ $subscription->expertTrader->avatar ?? asset('dash/default-avatar.png') }}"
                                            alt="{{ $subscription->expertTrader->name }}"
                                            class="object-cover w-10 h-10 border-2 border-primary-500 rounded-full">
                                        @if ($subscription->expertTrader->isOnline())
                                            <span
                                                class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-dark-100 rounded-full"></span>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <p class="font-semibold text-white">{{ $subscription->expertTrader->name }}</p>
                                        <p class="text-sm text-gray-400">Amount:
                                            {{ Auth::user()->currency }}{{ number_format($subscription->amount) }}</p>
                                    </div>
                                </div>
                                <div class="hidden text-right sm:block">
                                    <p class="text-sm font-medium text-white">
                                        {{ \Carbon\Carbon::parse($subscription->end_date)->toFormattedDateString() }}</p>
                                    <p class="text-xs text-gray-400">End Date</p>
                                </div>
                                <div class="text-right">
                                    <span
                                        class="px-2 py-1 text-xs font-medium text-green-400 bg-green-500/10 rounded-full">
                                        +{{ number_format($subscription->expertTrader->roi_percentage, 1) }}% ROI
                                    </span>
                                </div>
                                <a href="{{ route('copy-trading.details', $subscription->id) }}"
                                    class="p-2 text-gray-400 rounded-md hover:bg-dark-300">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Right column -->
        <div class="space-y-6 lg:col-span-4">
            <!-- Top Performing Experts -->
            @if (isset($topExperts) && $topExperts->count() > 0)
                <div class="p-4 bg-dark-200 sm:p-6 rounded-xl">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-white">Top Copy Trading Experts</h3>
                        <a href="{{ route('copy-trading.index') }}"
                            class="text-sm font-medium text-primary-500 hover:text-primary-400">View All</a>
                    </div>
                    <div class="space-y-3">
                        @foreach ($topExperts as $expert)
                            <a href="{{ route('copy-trading.index') }}"
                                class="block p-3 transition-colors bg-dark-100 rounded-lg hover:bg-dark-300">
                                <div class="flex items-center">
                                    <div class="relative">
                                        <img src="{{ $expert->avatar ?? asset('dash/default-avatar.png') }}"
                                            alt="{{ $expert->name }}"
                                            class="object-cover w-12 h-12 border-2 border-primary-500 rounded-full">
                                        @if ($expert->isOnline())
                                            <span
                                                class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-dark-100 rounded-full"></span>
                                        @endif
                                    </div>
                                    <div class="flex-1 ml-3">
                                        <div class="flex items-center justify-between">
                                            <p class="font-semibold text-white">{{ $expert->name }}</p>
                                            <span
                                                class="px-2 py-1 text-xs font-medium text-green-400 bg-green-500/10 rounded-full">
                                                +{{ number_format($expert->roi_percentage, 1) }}%
                                            </span>
                                        </div>
                                        <div class="flex items-center mt-1 space-x-3 text-xs text-gray-400">
                                            <span>
                                                <i class="mr-1 fas fa-chart-line"></i>
                                                {{ number_format($expert->win_rate, 0) }}% Win Rate
                                            </span>
                                            <span>
                                                <i class="mr-1 fas fa-users"></i>
                                                {{ number_format($expert->total_followers) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="p-4 bg-dark-200 sm:p-6 rounded-xl">
                <h3 class="mb-4 text-lg font-semibold text-white">Quick Actions</h3>
                <div class="grid grid-cols-2 gap-4">
                    <a href="{{ route('deposits') }}"
                        class="flex flex-col items-center justify-center p-4 text-center bg-dark-100 rounded-lg hover:bg-dark-300 transition-colors">
                        <div
                            class="flex items-center justify-center w-12 h-12 mb-2 text-primary-400 bg-primary-500/10 rounded-full">
                            <i class="fas fa-download"></i>
                        </div>
                        <p class="text-sm font-medium text-white">Deposit</p>
                    </a>
                    <a href="{{ route('withdrawals') }}"
                        class="flex flex-col items-center justify-center p-4 text-center bg-dark-100 rounded-lg hover:bg-dark-300 transition-colors">
                        <div
                            class="flex items-center justify-center w-12 h-12 mb-2 text-primary-400 bg-primary-500/10 rounded-full">
                            <i class="fas fa-upload"></i>
                        </div>
                        <p class="text-sm font-medium text-white">Withdraw</p>
                    </a>
                    <a href="{{ route('connect-wallet') }}"
                        class="flex flex-col items-center justify-center p-4 text-center bg-dark-100 rounded-lg hover:bg-dark-300 transition-colors">
                        <div
                            class="flex items-center justify-center w-12 h-12 mb-2 text-primary-400 bg-primary-500/10 rounded-full">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <p class="text-sm font-medium text-white">Connect Wallet</p>
                    </a>
                    <a href="{{ route('trading-bots.index') }}"
                        class="flex flex-col items-center justify-center p-4 text-center bg-dark-100 rounded-lg hover:bg-dark-300 transition-colors">
                        <div
                            class="flex items-center justify-center w-12 h-12 mb-2 text-primary-400 bg-primary-500/10 rounded-full">
                            <i class="fas fa-robot"></i>
                        </div>
                        <p class="text-sm font-medium text-white">Trading Bots</p>
                    </a>
                </div>
            </div>

            <!-- Market Overview -->
            <div class="p-4 bg-dark-200 sm:p-6 rounded-xl">
                <h3 class="mb-4 text-lg font-semibold text-white">Market Overview</h3>
                <!-- TradingView Widget BEGIN -->
                <div class="tradingview-widget-container" style="height:350px;">
                    <div class="tradingview-widget-container__widget"></div>
                    <script type="text/javascript" src="https://s3.tradingview.com/external-embedding/embed-widget-market-overview.js"
                        async>
                        {
                            "colorTheme": "dark",
                            "dateRange": "12M",
                            "showChart": true,
                            "locale": "en",
                            "largeChartUrl": "",
                            "isTransparent": true,
                            "showSymbolLogo": true,
                            "showFloatingTooltip": false,
                            "width": "100%",
                            "height": "100%",
                            "tabs": [{
                                "title": "Crypto",
                                "symbols": [{
                                        "s": "BINANCE:BTCUSDT"
                                    },
                                    {
                                        "s": "BINANCE:ETHUSDT"
                                    },
                                    {
                                        "s": "BINANCE:LTCUSDT"
                                    },
                                    {
                                        "s": "BINANCE:DOGEUSDT"
                                    }
                                ],
                                "originalTitle": "Crypto"
                            }]
                        }
                    </script>
                </div>
                <!-- TradingView Widget END -->
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="mt-6">
        <div class="p-4 bg-dark-200 sm:p-6 rounded-xl">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-white">Recent Transactions</h3>
                <a href="{{ route('accounthistory') }}"
                    class="text-sm font-medium text-primary-500 hover:text-primary-400">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-dark-100">
                    <thead class="bg-dark-100">
                        <tr>
                            <th scope="col"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-400 uppercase">Type
                            </th>
                            <th scope="col"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-400 uppercase">
                                Amount</th>
                            <th scope="col"
                                class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-400 uppercase">Date
                            </th>
                            <!-- <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-400 uppercase">Status</th> -->
                        </tr>
                    </thead>
                    <tbody class="bg-dark-200 divide-y divide-dark-100">
                        @forelse ($t_history as $history)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="flex items-center justify-center w-8 h-8 mr-3 rounded-full {{ $history->type == 'Deposit' ? 'bg-primary-500/10 text-primary-400' : 'bg-red-500/10 text-red-400' }}">
                                            <i
                                                class="fas {{ $history->type == 'Deposit' ? 'fa-arrow-down' : 'fa-arrow-up' }}"></i>
                                        </div>
                                        <span class="font-medium text-white">{{ $history->type }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-white whitespace-nowrap">
                                    {{ Auth::user()->currency }}{{ number_format($history->amount) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-400 whitespace-nowrap">
                                    {{ $history->created_at->toFormattedDateString() }}</td>
                                <!-- <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($history->status == 'processed')
    <span class="px-2 py-1 text-xs font-medium text-green-400 bg-green-500/10 rounded-full">Processed</span>
@else
    <span class="px-2 py-1 text-xs font-medium text-yellow-400 bg-yellow-500/10 rounded-full">Pending</span>
    @endif
                                        </td> -->
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-12 text-center text-gray-400">No transactions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var options = {
                series: [{
                    name: 'Portfolio Value',
                    data: {!! json_encode($portfolioData['values']) !!}
                }],
                chart: {
                    type: 'area',
                    height: '100%',
                    parentHeightOffset: 0,
                    toolbar: {
                        show: false
                    },
                    zoom: {
                        enabled: false
                    },
                    background: 'transparent'
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                colors: ['#818cf8'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'dark',
                        type: "vertical",
                        shadeIntensity: 0.5,
                        gradientToColors: ['#3730a3'],
                        inverseColors: true,
                        opacityFrom: 0.5,
                        opacityTo: 0.1,
                        stops: [0, 100]
                    }
                },
                grid: {
                    borderColor: '#1E2028',
                    strokeDashArray: 4,
                    yaxis: {
                        lines: {
                            show: true
                        }
                    },
                    xaxis: {
                        lines: {
                            show: false
                        }
                    }
                },
                xaxis: {
                    categories: {!! json_encode($portfolioData['months']) !!},
                    labels: {
                        style: {
                            colors: '#9ca3af'
                        }
                    },
                    axisBorder: {
                        show: false
                    },
                    axisTicks: {
                        show: false
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#9ca3af'
                        },
                        formatter: (value) => {
                            return `{{ Auth::user()->currency }}${value.toLocaleString()}`
                        }
                    }
                },
                tooltip: {
                    theme: 'dark',
                    x: {
                        show: false
                    },
                    y: {
                        formatter: (value) => {
                            return `{{ Auth::user()->currency }}${value.toLocaleString()}`
                        },
                        title: {
                            formatter: (seriesName) => seriesName,
                        },
                    },
                    marker: {
                        show: false
                    }
                }
            };

            var chart = new ApexCharts(document.querySelector("#performance-chart"), options);
            chart.render();
        });
    </script>
@endsection
