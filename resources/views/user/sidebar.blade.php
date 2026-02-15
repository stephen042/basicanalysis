<div class="flex flex-col h-full">
    <!-- Logo -->
    <div class="flex items-center justify-center h-16 px-4 border-b border-dark-100">
        <a href="{{ route('dashboard') }}">
            <img src="{{ asset('storage/app/public/' . $settings->logo) }}" class="h-8 w-auto" alt="logo">
        </a>
    </div>

    <!-- User Profile -->
    <div class="p-4 text-center border-b border-dark-100">
        <a href="#" class="relative inline-block mx-auto">
            <i class="fas fa-user-circle text-5xl text-primary-500"></i>
            <span class="absolute bottom-0 right-0 block h-3 w-3 rounded-full bg-green-500 border-2 border-dark-200"></span>
        </a>
        <h5 class="mt-2 text-base font-semibold text-white">{{ Auth::user()->name }}</h5>
        <div class="mt-2">
            <span class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-full bg-dark-100 text-gray-300">
                <i class="far fa-coins mr-2 text-yellow-400"></i>
                {{ Auth::user()->currency }}{{ number_format(Auth::user()->account_bal, 2, '.', ',') }}
            </span>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        <!-- Dashboard Overview -->
        <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')" icon="fas fa-chart-pie">
            Portfolio Overview
        </x-nav-link>

        <!-- Trading & Investment Section -->
        <div class="pt-4 pb-2">
            <h3 class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Trading & Investment</h3>
        </div>

        <x-nav-link href="{{ route('trading-bots.index') }}" :active="request()->routeIs('trading-bots.*')" icon="fas fa-robot">
            AI Trading Bots
        </x-nav-link>

        <x-nav-link href="{{ route('my-bots-investment') }}" :active="request()->routeIs('my-bots-investment')" icon="fas fa-coins">
            My Investments
        </x-nav-link>


        <x-nav-link href="{{ route('copy-trading.index') }}" :active="request()->routeIs('copy-trading.*')" icon="fas fa-copy">
            Copy Trading
        </x-nav-link>

       
            <x-nav-link href="{{ route('tradinghistory') }}" :active="request()->routeIs('tradinghistory')" icon="fas fa-chart-line">
                Trading History
            </x-nav-link>
      

        <!-- Wallet & Transactions Section -->
        <div class="pt-4 pb-2">
            <h3 class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Wallet & Transactions</h3>
        </div>

        <x-nav-link href="{{ route('deposits') }}" :active="request()->routeIs('deposits') || request()->routeIs('payment') || request()->routeIs('pay.crypto')" icon="fas fa-plus-circle">
            Fund Account
        </x-nav-link>

        @if ($mod['investment'] || $mod['cryptoswap'])
            <x-nav-link href="{{ route('withdrawals') }}" :active="request()->routeIs('withdrawals') || request()->routeIs('withdrawfunds')" icon="fas fa-minus-circle">
                Withdraw Funds
            </x-nav-link>
        @endif

        <x-nav-link href="{{ route('accounthistory') }}" :active="request()->routeIs('accounthistory')" icon="fas fa-receipt">
            Transaction History
        </x-nav-link>

        @if ($moresettings->use_transfer)
            <x-nav-link href="{{ route('transferview') }}" :active="request()->routeIs('transferview')" icon="fas fa-exchange-alt">
                Transfer Funds
            </x-nav-link>
        @endif

        @if ($mod['cryptoswap'])
            <x-nav-link href="{{ route('assetbalance') }}" :active="request()->routeIs('assetbalance') || request()->routeIs('swaphistory')" icon="fab fa-bitcoin">
                Swap Cryptos
            </x-nav-link>
        @endif

        <!-- Account & Settings Section -->
        <div class="pt-4 pb-2">
            <h3 class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Account & Settings</h3>
        </div>

        <x-nav-link href="{{ route('profile') }}" :active="request()->routeIs('profile')" icon="fas fa-user-circle">
            Account Settings
        </x-nav-link>

        @if ($settings->enable_kyc == 'yes')
            @if (Auth::user()->account_verify != 'Verified')
                <x-nav-link href="{{ route('account.verify') }}" :active="request()->routeIs('account.verify') || request()->routeIs('kycform')" icon="fas fa-shield-alt">
                    Identity Verification
                </x-nav-link>
            @endif
        @endif

        <x-nav-link href="{{ route('referuser') }}" :active="request()->routeIs('referuser')" icon="fas fa-users">
            Referral Program
        </x-nav-link>

        {{-- @if ($settings->subscription_service == 'on')
            <x-nav-link href="{{ route('subtrade') }}" :active="request()->routeIs('subtrade')" icon="fas fa-crown">
                Premium Services
            </x-nav-link>
        @endif --}}
    </nav>

    <!-- Help Center -->
    <div class="p-2 mt-auto">
        <div class="p-4 text-center bg-dark-100 rounded-lg">
            <div class="flex items-center justify-center w-10 h-10 mx-auto mb-2 text-blue-400 bg-blue-500/10 rounded-full">
                <i class="fas fa-headset"></i>
            </div>
            <h5 class="text-sm font-semibold text-white">Need Help?</h5>
            <a href="{{ route('support') }}" class="inline-block w-full px-4 py-2 mt-3 text-xs font-medium text-center text-white bg-primary-600 rounded-lg hover:bg-primary-700">
                Contact Us
            </a>
        </div>
    </div>
</div>
