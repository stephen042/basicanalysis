@extends('layouts.base')

@section('title', $settings->site_name . ' - AI Trading Bots')

@inject('content', 'App\Http\Controllers\FrontController')
@section('content')

    <!-- Trading Bots Intro Section -->
    <section class="relative py-20 lg:py-32 overflow-hidden">
        <!-- Background Animation Elements -->
        <div class="absolute inset-0 -z-10">
            <div class="absolute top-20 left-10 animate-float hidden xl:block">
                <img src="{{ asset('public/assets/images/star.png') }}" alt="star" class="w-16 h-16 opacity-30">
            </div>
            <div class="absolute bottom-20 right-10 animate-pulse-slow hidden xxl:block">
                <img src="{{ asset('public/assets/images/sun.png') }}" alt="sun" class="w-24 h-24 opacity-20">
            </div>
        </div>
        
        <div class="container mx-auto px-4 max-w-7xl">
            <div class="flex justify-center">
                <div class="w-full lg:w-2/3 xl:w-1/2 text-center mb-12 lg:mb-16">
                    <span class="inline-block text-green-400 font-medium text-lg mb-4">Automated Trading Solutions</span>
                    <h2 class="text-3xl lg:text-4xl xl:text-5xl font-bold mb-6">Choose Your AI-Powered Trading Bot</h2>
                    <p class="text-lg text-gray-400 max-w-3xl mx-auto">
                        Select from our range of AI-powered trading bots designed for different investment 
                        strategies and risk profiles. Each bot operates 24/7, executing trades automatically 
                        to maximize your returns.
                    </p>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-16 lg:mb-24">
                <div class="bg-gray-800 rounded-xl p-6 lg:p-8 text-center">
                    <div class="w-16 h-16 bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ti ti-robot text-2xl text-green-400"></i>
                    </div>
                    <div class="flex justify-center items-baseline">
                        <span class="text-4xl font-bold text-white">12</span>
                        <span class="text-4xl font-bold text-white">+</span>
                    </div>
                    <h6 class="mt-3 text-gray-300">Active Bots</h6>
                </div>
                <div class="bg-gray-800 rounded-xl p-6 lg:p-8 text-center">
                    <div class="w-16 h-16 bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ti ti-users text-2xl text-green-400"></i>
                    </div>
                    <div class="flex justify-center items-baseline">
                        <span class="text-4xl font-bold text-white">50</span>
                        <span class="text-4xl font-bold text-white">K+</span>
                    </div>
                    <h6 class="mt-3 text-gray-300">Active Users</h6>
                </div>
                <div class="bg-gray-800 rounded-xl p-6 lg:p-8 text-center">
                    <div class="w-16 h-16 bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ti ti-trending-up text-2xl text-green-400"></i>
                    </div>
                    <div class="flex justify-center items-baseline">
                        <span class="text-4xl font-bold text-white">85</span>
                        <span class="text-4xl font-bold text-white">%</span>
                    </div>
                    <h6 class="mt-3 text-gray-300">Success Rate</h6>
                </div>
                <div class="bg-gray-800 rounded-xl p-6 lg:p-8 text-center">
                    <div class="w-16 h-16 bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ti ti-clock-24 text-2xl text-green-400"></i>
                    </div>
                    <h5 class="text-4xl font-bold text-white">24/7</h5>
                    <h6 class="mt-3 text-gray-300">Trading Active</h6>
                </div>
            </div>
        </div>
    </section>

    <!-- Trading Bots Grid Section -->
    <section class="relative py-20 lg:py-32 overflow-hidden">
        <div class="text-center mb-12" data-aos="fade-up">
                        <div
                            class="inline-flex items-center px-3 py-1 rounded-full bg-primary-900/30 text-primary-400 text-sm font-medium mb-4">
                            Bots PLANS
                        </div>
                        <h2 class="text-3xl md:text-4xl font-bold mb-4">Premium Bots Options</h2>
                        <p class="text-gray-400 max-w-2xl mx-auto">
                            {{  $settings->site_name }} offers a range of strategic Bots opportunities that maximize
                            returns
                        </p>
                    </div>
        <div class="container mx-auto px-4 max-w-7xl">
            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-8">
                @forelse($tradingBots as $bot)
                    <div class="bot-card bg-gray-800 rounded-2xl overflow-hidden h-full flex flex-col">
                        <!-- Header with Status Badge -->
                        <div class="p-6 lg:p-8 border-b border-gray-700">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-xl font-bold mb-2">{{ $bot->name }}</h3>
                                    <span class="inline-flex items-center bg-green-900/20 text-green-400 px-3 py-1.5 rounded-full text-sm">
                                        <i class="ti ti-circle-filled text-xs mr-1"></i>
                                        Active & Trading
                                    </span>
                                </div>
                                <div class="w-12 h-12 bg-gray-700 rounded-full flex items-center justify-center">
                                    <i class="ti ti-robot text-xl text-green-400"></i>
                                </div>
                            </div>
                            
                            <!-- Profit Display -->
                            <div class="bg-gray-700 rounded-xl py-6 my-6 text-center">
                                <div class="text-gray-400 text-sm mb-2">Total Expected Return</div>
                                <div class="text-4xl font-bold text-white">{{ $bot->profit_rate }}%</div>
                                <div class="text-green-400 text-lg mt-2">
                                    {{ number_format(($bot->profit_rate / ($bot->duration / 24)), 2) }}% Daily
                                </div>
                            </div>

                            <p class="text-gray-400">{{ $bot->description }}</p>
                        </div>

                        <!-- Bot Details -->
                        <div class="p-6 lg:p-8 flex-grow">
                            <div class="space-y-4">
                                <div class="flex justify-between items-center py-3 border-b border-gray-700">
                                    <span class="text-gray-400 flex items-center">
                                        <i class="ti ti-coin mr-2 text-green-400"></i>
                                        Minimum Investment
                                    </span>
                                    <span class="font-semibold">${{ number_format($bot->min_amount) }}</span>
                                </div>
                                <div class="flex justify-between items-center py-3 border-b border-gray-700">
                                    <span class="text-gray-400 flex items-center">
                                        <i class="ti ti-coins mr-2 text-green-400"></i>
                                        Maximum Investment
                                    </span>
                                    <span class="font-semibold">${{ number_format($bot->max_amount) }}</span>
                                </div>
                                <div class="flex justify-between items-center py-3 border-b border-gray-700">
                                    <span class="text-gray-400 flex items-center">
                                        <i class="ti ti-clock mr-2 text-green-400"></i>
                                        Trading Duration
                                    </span>
                                    <span class="font-semibold">{{ number_format($bot->duration / 24, 1) }} Days</span>
                                </div>
                                <div class="flex justify-between items-center py-3 border-b border-gray-700">
                                    <span class="text-gray-400 flex items-center">
                                        <i class="ti ti-calendar mr-2 text-green-400"></i>
                                        Total Hours
                                    </span>
                                    <span class="font-semibold">{{ $bot->duration }} Hours</span>
                                </div>
                                <div class="flex justify-between items-center py-3">
                                    <span class="text-gray-400 flex items-center">
                                        <i class="ti ti-trending-up mr-2 text-green-400"></i>
                                        Daily Return Rate
                                    </span>
                                    <span class="font-semibold text-green-400">{{ number_format(($bot->profit_rate / ($bot->duration / 24)), 2) }}%</span>
                                </div>
                            </div>

                            <!-- Trading Assets -->
                            @if($bot->tradingAssets && $bot->tradingAssets->count() > 0)
                                <div class="mt-8 pt-8 border-t border-gray-700">
                                    <h6 class="mb-4 flex items-center font-medium">
                                        <i class="ti ti-currency-bitcoin mr-2 text-green-400"></i>
                                        Trading Assets
                                    </h6>
                                    <div class="grid grid-cols-2 gap-3">
                                        @foreach($bot->tradingAssets->take(6) as $asset)
                                            <div class="bg-gray-700 p-3 rounded-xl flex items-center gap-3">
                                                <img src="{{ $asset->icon_url }}" alt="{{ $asset->symbol }}" class="w-6 h-6 rounded-full" onerror="this.src='{{ asset('dash/default-crypto.png') }}'">
                                                <div class="flex-1">
                                                    <div class="text-sm font-semibold">{{ $asset->symbol }}</div>
                                                    <div class="text-xs text-gray-400">{{ $asset->pivot->allocation_percentage }}%</div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @if($bot->tradingAssets->count() > 6)
                                        <div class="text-center mt-4">
                                            <small class="text-gray-500">+{{ $bot->tradingAssets->count() - 6 }} more assets</small>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <!-- Key Features -->
                            <div class="mt-8 pt-8 border-t border-gray-700">
                                <h6 class="mb-4 flex items-center font-medium">
                                    <i class="ti ti-check mr-2 text-green-400"></i>
                                    Key Features
                                </h6>
                                <ul class="space-y-2">
                                    <li class="flex items-center">
                                        <i class="ti ti-circle-check text-green-400 mr-2"></i>
                                        24/7 Automated Trading
                                    </li>
                                    <li class="flex items-center">
                                        <i class="ti ti-circle-check text-green-400 mr-2"></i>
                                        AI-Powered Strategy
                                    </li>
                                    <li class="flex items-center">
                                        <i class="ti ti-circle-check text-green-400 mr-2"></i>
                                        Built-in Risk Management
                                    </li>
                                    <li class="flex items-center">
                                        <i class="ti ti-circle-check text-green-400 mr-2"></i>
                                        Real-time Performance Tracking
                                    </li>
                                    <li class="flex items-center">
                                        <i class="ti ti-circle-check text-green-400 mr-2"></i>
                                        Multi-Asset Diversification
                                    </li>
                                    <li class="flex items-center">
                                        <i class="ti ti-circle-check text-green-400 mr-2"></i>
                                        Automated Profit Withdrawal
                                    </li>
                                </ul>
                            </div>

                            <!-- CTA Button -->
                            @auth
                                <a href="{{ route('dashboard') }}" class="w-full mt-8 bg-cyan-500 hover:bg-cyan-600 text-white font-medium py-3 px-4 rounded-xl flex items-center justify-center gap-2 transition-colors">
                                    <i class="ti ti-play"></i>
                                    Start Trading Now
                                </a>
                            @else
                                <a href="{{ route('register') }}" class="w-full mt-8 bg-cyan-500 hover:bg-cyan-600 text-white font-medium py-3 px-4 rounded-xl flex items-center justify-center gap-2 transition-colors">
                                    <i class="ti ti-user-plus"></i>
                                    Register to Start Trading
                                </a>
                            @endauth
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-16">
                        <i class="ti ti-robot text-6xl text-gray-500 mb-6 block"></i>
                        <h4 class="text-2xl font-bold mb-4">No Trading Bots Available</h4>
                        <p class="text-gray-400 max-w-md mx-auto">Check back soon for new AI-powered trading opportunities.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="bg-gray-800 py-20 lg:py-32 relative overflow-hidden">
        <!-- Background Animation Elements -->
        <div class="absolute inset-0 -z-10">
            <div class="absolute top-20 right-10 animate-pulse-slow hidden xl:block">
                <img src="{{ asset('public/assets/images/vector.png') }}" alt="vector" class="w-24 h-24 opacity-20">
            </div>
            <div class="absolute bottom-20 left-10 animate-float hidden xxxl:block">
                <img src="{{ asset('public/assets/images/star3.png') }}" alt="star" class="w-16 h-16 opacity-30">
            </div>
        </div>
        
        <div class="container mx-auto px-4 max-w-7xl">
            <div class="flex justify-center">
                <div class="w-full lg:w-2/3 xl:w-1/2 text-center mb-16 lg:mb-20">
                    <span class="inline-block text-green-400 font-medium text-lg mb-4">How It Works</span>
                    <h2 class="text-3xl lg:text-4xl xl:text-5xl font-bold mb-6">Start Trading in 4 Simple Steps</h2>
                    <p class="text-lg text-gray-400 max-w-3xl mx-auto">
                        Get started with automated bot trading in minutes. Our streamlined process 
                        makes it easy for anyone to begin earning passive income.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-20 h-20 bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-white">1</span>
                    </div>
                    <div class="w-16 h-16 bg-gray-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ti ti-user-plus text-xl text-green-400"></i>
                    </div>
                    <h5 class="text-xl font-semibold mb-3">Create Account</h5>
                    <p class="text-gray-400">
                        Sign up for free and complete quick identity verification to secure your account.
                    </p>
                </div>
                <div class="text-center">
                    <div class="w-20 h-20 bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-white">2</span>
                    </div>
                    <div class="w-16 h-16 bg-gray-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ti ti-wallet text-xl text-green-400"></i>
                    </div>
                    <h5 class="text-xl font-semibold mb-3">Deposit Funds</h5>
                    <p class="text-gray-400">
                        Add funds to your account using various payment methods including crypto and cards.
                    </p>
                </div>
                <div class="text-center">
                    <div class="w-20 h-20 bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-white">3</span>
                    </div>
                    <div class="w-16 h-16 bg-gray-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ti ti-robot text-xl text-green-400"></i>
                    </div>
                    <h5 class="text-xl font-semibold mb-3">Choose Bot</h5>
                    <p class="text-gray-400">
                        Select an AI trading bot that matches your investment goals and risk tolerance.
                    </p>
                </div>
                <div class="text-center">
                    <div class="w-20 h-20 bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-2xl font-bold text-white">4</span>
                    </div>
                    <div class="w-16 h-16 bg-gray-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ti ti-chart-line text-xl text-green-400"></i>
                    </div>
                    <h5 class="text-xl font-semibold mb-3">Start Earning</h5>
                    <p class="text-gray-400">
                        Watch your bot trade 24/7 and track your profits in real-time from the dashboard.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Our Bots Section -->
    <section class="py-20 lg:py-32">
        <div class="container mx-auto px-4 max-w-7xl">
            <div class="flex justify-center">
                <div class="w-full lg:w-2/3 xl:w-1/2 text-center mb-16 lg:mb-20">
                    <span class="inline-block text-green-400 font-medium text-lg mb-4">Advantages</span>
                    <h2 class="text-3xl lg:text-4xl xl:text-5xl font-bold mb-6">Why Choose Our AI Trading Bots?</h2>
                    <p class="text-lg text-gray-400 max-w-3xl mx-auto">
                        Our AI-powered bots combine advanced algorithms, real-time market analysis, 
                        and proven trading strategies to deliver consistent results.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-gray-800 rounded-2xl p-6 lg:p-8 h-full">
                    <div class="w-16 h-16 bg-gray-700 rounded-full flex items-center justify-center mb-5">
                        <i class="ti ti-brain text-xl text-green-400"></i>
                    </div>
                    <h5 class="text-xl font-semibold mb-3">Advanced AI Technology</h5>
                    <p class="text-gray-400">
                        Our bots use machine learning algorithms that continuously adapt to market 
                        conditions, improving performance over time.
                    </p>
                </div>
                <div class="bg-gray-800 rounded-2xl p-6 lg:p-8 h-full">
                    <div class="w-16 h-16 bg-gray-700 rounded-full flex items-center justify-center mb-5">
                        <i class="ti ti-shield-check text-xl text-green-400"></i>
                    </div>
                    <h5 class="text-xl font-semibold mb-3">Built-in Risk Management</h5>
                    <p class="text-gray-400">
                        Automated stop-loss, position sizing, and diversification protect your 
                        capital while maximizing returns.
                    </p>
                </div>
                <div class="bg-gray-800 rounded-2xl p-6 lg:p-8 h-full">
                    <div class="w-16 h-16 bg-gray-700 rounded-full flex items-center justify-center mb-5">
                        <i class="ti ti-clock-24 text-xl text-green-400"></i>
                    </div>
                    <h5 class="text-xl font-semibold mb-3">24/7 Trading</h5>
                    <p class="text-gray-400">
                        Never miss an opportunity. Our bots monitor markets and execute trades 
                        around the clock, even while you sleep.
                    </p>
                </div>
                <div class="bg-gray-800 rounded-2xl p-6 lg:p-8 h-full">
                    <div class="w-16 h-16 bg-gray-700 rounded-full flex items-center justify-center mb-5">
                        <i class="ti ti-trending-up text-xl text-green-400"></i>
                    </div>
                    <h5 class="text-xl font-semibold mb-3">Proven Performance</h5>
                    <p class="text-gray-400">
                        Track record of consistent returns across various market conditions with 
                        transparent performance metrics.
                    </p>
                </div>
                <div class="bg-gray-800 rounded-2xl p-6 lg:p-8 h-full">
                    <div class="w-16 h-16 bg-gray-700 rounded-full flex items-center justify-center mb-5">
                        <i class="ti ti-adjustments text-xl text-green-400"></i>
                    </div>
                    <h5 class="text-xl font-semibold mb-3">Fully Customizable</h5>
                    <p class="text-gray-400">
                        Adjust risk levels, trading pairs, and strategy parameters to match your 
                        personal investment preferences.
                    </p>
                </div>
                <div class="bg-gray-800 rounded-2xl p-6 lg:p-8 h-full">
                    <div class="w-16 h-16 bg-gray-700 rounded-full flex items-center justify-center mb-5">
                        <i class="ti ti-users-group text-xl text-green-400"></i>
                    </div>
                    <h5 class="text-xl font-semibold mb-3">Expert Support</h5>
                    <p class="text-gray-400">
                        Access 24/7 customer support and dedicated account managers to help you 
                        optimize your trading strategy.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-gradient-to-r from-cyan-500 to-blue-600 py-20 lg:py-32 relative overflow-hidden">
        <!-- Background Animation Elements -->
        <div class="absolute inset-0 -z-10">
            <div class="absolute top-20 left-10 animate-float">
                <img src="{{ asset('public/assets/images/star.png') }}" alt="star" class="w-16 h-16 opacity-30">
            </div>
            <div class="absolute bottom-20 right-10 animate-pulse-slow hidden xxl:block">
                <img src="{{ asset('public/assets/images/sun.png') }}" alt="sun" class="w-24 h-24 opacity-20">
            </div>
        </div>
        
        <div class="container mx-auto px-4 max-w-7xl">
            <div class="flex justify-center">
                <div class="w-full lg:w-2/3 xl:w-1/2 text-center">
                    <h2 class="text-3xl lg:text-4xl xl:text-5xl font-bold mb-6">Ready to Start Automated Trading?</h2>
                    <p class="text-lg text-white/90 max-w-3xl mx-auto mb-10">
                        Join thousands of traders who trust our AI-powered bots to generate consistent 
                        returns. Start your journey to financial freedom today.
                    </p>
                    <div class="flex flex-wrap justify-center gap-5 items-center">
                        @auth
                            <a href="{{ route('dashboard') }}" class="bg-gray-800 hover:bg-gray-700 text-white font-medium py-3 px-6 rounded-xl flex items-center gap-2 transition-colors">
                                <i class="ti ti-dashboard"></i>
                                Go to Dashboard
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="bg-gray-800 hover:bg-gray-700 text-white font-medium py-3 px-6 rounded-xl flex items-center gap-2 transition-colors">
                                <i class="ti ti-rocket"></i>
                                Get Started Now
                            </a>
                        @endauth
                        <a href="{{ route('contact') }}" class="text-white font-medium flex items-center gap-2 hover:underline">
                            Learn More <i class="ti ti-arrow-narrow-right text-xl"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

 @endsection