@extends('layouts.dash')

@section('title', $title)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">{{ $title }}</h1>
                <p class="text-gray-400 mt-1">Automated trading solutions for consistent returns</p>
            </div>
            <div class="flex items-center gap-2 text-sm">
                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                <span class="text-green-400">Live Trading Active</span>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/20 rounded-lg p-4">
            <div class="flex items-center gap-3">
                <i class="fas fa-check-circle text-green-400"></i>
                <span class="text-green-400">{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-500/10 border border-red-500/20 rounded-lg p-4">
            <div class="flex items-center gap-3">
                <i class="fas fa-exclamation-triangle text-red-400"></i>
                <span class="text-red-400">{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <!-- Available Trading Bots -->
    <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <h2 class="text-xl font-semibold text-white flex items-center gap-2">
                <i class="fas fa-robot text-primary"></i>
                Available Trading Bots
            </h2>

            <!-- Search Input -->
            <div class="relative w-full md:w-80">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input
                    type="text"
                    id="botSearch"
                    placeholder="Search trading bots..."
                    class="w-full pl-10 pr-4 py-2 bg-dark-300 border border-dark-100 rounded-lg text-white placeholder-gray-400 focus:border-primary focus:outline-none"
                    onkeyup="filterBots()"
                >
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="botsGrid">
            @forelse($tradingBots as $bot)
                <div class="bot-card bg-dark-300 rounded-lg p-6 border border-dark-100 hover:border-primary/50 transition-all duration-300" data-bot-name="{{ strtolower($bot->name) }}" data-bot-description="{{ strtolower($bot->description) }}">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-white">{{ $bot->name }}</h3>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-500/10 text-green-400 mt-2">
                                <div class="w-2 h-2 bg-green-400 rounded-full mr-1"></div>
                                Active
                            </span>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-primary">{{ $bot->profit_rate }}%</div>
                            <div class="text-xs text-gray-400">Expected Return</div>
                            <div class="text-sm font-medium text-green-400 mt-1">
                                {{ number_format(($bot->profit_rate / ($bot->duration / 24)), 2) }}% daily
                            </div>
                        </div>
                    </div>

                    <p class="text-gray-400 text-sm mb-4">{{ $bot->description }}</p>

                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-400">Min Investment:</span>
                            <span class="text-white font-medium">{{ Auth::user()->currency }}{{ number_format($bot->min_amount) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-400">Max Investment:</span>
                            <span class="text-white font-medium">{{ Auth::user()->currency }}{{ number_format($bot->max_amount) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-400">Duration:</span>
                            <span class="text-white font-medium">{{ $bot->duration }} hours ({{ number_format($bot->duration / 24, 1) }} days)</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-400">Daily Return Rate:</span>
                            <span class="text-green-400 font-medium">{{ number_format(($bot->profit_rate / ($bot->duration / 24)), 2) }}%</span>
                        </div>
                    </div>

                    <!-- Trading Assets -->
                    @if($bot->tradingAssets->count() > 0)
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-400 mb-3">Trading Assets</h4>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach($bot->tradingAssets->take(4) as $asset)
                                    <div class="bg-dark-400 rounded-lg p-2 border border-dark-100">
                                        <div class="flex items-center gap-2">
                                            <img src="{{ $asset->icon_url }}" alt="{{ $asset->symbol }}" class="w-5 h-5 rounded-full" onerror="this.src='{{ asset('dash/default-crypto.png') }}'">
                                            <div class="flex-1 min-w-0">
                                                <div class="text-xs font-medium text-white truncate">{{ $asset->symbol }}</div>
                                                <div class="text-xs text-gray-400">{{ $asset->pivot->allocation_percentage }}%</div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if($bot->tradingAssets->count() > 4)
                                <div class="text-center mt-2">
                                    <span class="text-xs text-gray-400">+{{ $bot->tradingAssets->count() - 4 }} more assets</span>
                                </div>
                            @endif
                        </div>
                    @endif

                    <form action="{{ route('trading-bots.subscribe') }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="trading_bot_id" value="{{ $bot->id }}">

                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Investment Amount</label>
                            <input type="number"
                                   name="amount"
                                   min="{{ $bot->min_amount }}"
                                   max="{{ $bot->max_amount }}"
                                   step="0.01"
                                   placeholder="Enter amount"
                                   class="w-full px-4 py-3 bg-dark-400 border border-dark-100 rounded-lg text-white placeholder-gray-500 focus:border-primary focus:outline-none"
                                   required>
                        </div>

                        <button type="submit" class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                            <i class="fas fa-play mr-2"></i>
                            Start Trading
                        </button>
                    </form>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <i class="fas fa-robot text-6xl text-gray-600 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-400 mb-2">No Trading Bots Available</h3>
                    <p class="text-gray-500">Check back later for new trading opportunities.</p>
                </div>
            @endforelse

            <!-- No Search Results Message -->
            <div id="noResults" class="col-span-full text-center py-12 hidden">
                <i class="fas fa-search text-6xl text-gray-600 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-400 mb-2">No Bots Found</h3>
                <p class="text-gray-500">Try adjusting your search terms or browse all available bots.</p>
            </div>
        </div>
    </div>

    <!-- Active Subscriptions -->
    @if($userTradingBots->where('status', 'active')->count() > 0)
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <h2 class="text-xl font-semibold text-white mb-6 flex items-center gap-2">
                <i class="fas fa-chart-line text-green-400"></i>
                Active Trading Sessions
            </h2>

            <div class="space-y-4">
                @foreach($userTradingBots->where('status', 'active') as $userBot)
                    <div class="bg-dark-300 rounded-lg p-4 border border-dark-100">
                        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-lg font-semibold text-white">{{ $userBot->tradingBot->name }}</h3>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-500/10 text-green-400">
                                        <div class="w-2 h-2 bg-green-400 rounded-full mr-1 animate-pulse"></div>
                                        Trading
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-400">Investment:</span>
                                        <div class="text-white font-medium">{{ Auth::user()->currency }}{{ number_format($userBot->amount) }}</div>
                                    </div>
                                    <div>
                                        <span class="text-gray-400">Total Expected:</span>
                                        <div class="text-green-400 font-medium">{{ Auth::user()->currency }}{{ number_format($userBot->amount * $userBot->tradingBot->profit_rate / 100) }}</div>
                                    </div>
                                    <div>
                                        <span class="text-gray-400">Daily Expected:</span>
                                        <div class="text-green-400 font-medium">{{ Auth::user()->currency }}{{ number_format($userBot->amount * ($userBot->tradingBot->profit_rate / ($userBot->tradingBot->duration / 24)) / 100) }}</div>
                                    </div>
                                    <div>
                                        <span class="text-gray-400">Started:</span>
                                        <div class="text-white">{{ $userBot->created_at->format('M d, Y H:i') }}</div>
                                    </div>
                                    <div>
                                        <span class="text-gray-400">Expires:</span>
                                        <div class="text-white">{{ \Carbon\Carbon::parse($userBot->expires_at)->format('M d, Y H:i') }}</div>
                                    </div>
                                </div>

                                @if($userBot->tradingLogs->count() > 0)
                                    <div class="mt-3 pt-3 border-t border-dark-100">
                                        <div class="text-sm text-gray-400 mb-2">Recent Activity:</div>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($userBot->tradingLogs->take(5) as $log)
                                                <span class="inline-flex items-center px-2 py-1 rounded text-xs {{ $log->type === 'profit' ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400' }}">
                                                    <i class="fas {{ $log->type === 'profit' ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-1"></i>
                                                    {{ Auth::user()->currency }}{{ number_format($log->amount, 2) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div class="flex gap-2">
                                <form action="{{ route('trading-bots.cancel', $userBot->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure? You will only receive 50% refund.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-4 py-2 bg-red-500/10 text-red-400 border border-red-500/20 rounded-lg hover:bg-red-500/20 transition-colors">
                                        <i class="fas fa-stop mr-1"></i>
                                        Cancel
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Active Sessions</p>
                    <p class="text-2xl font-bold text-white">{{ $userTradingBots->where('status', 'active')->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-play text-primary text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total Invested</p>
                    <p class="text-2xl font-bold text-white">{{ Auth::user()->currency }}{{ number_format($userTradingBots->where('status', 'active')->sum('amount')) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-500/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-green-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Expected Returns</p>
                    <p class="text-2xl font-bold text-green-400">
                        {{ Auth::user()->currency }}{{ number_format($userTradingBots->where('status', 'active')->sum(function($bot) {
                            return $bot->amount * $bot->tradingBot->profit_rate / 100;
                        })) }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-500/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-green-400 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row gap-4">
        <a href="{{ route('trading-bots.history') }}" class="flex-1 bg-dark-200 hover:bg-dark-100 text-white font-semibold py-3 px-6 rounded-lg border border-dark-100 transition-colors duration-200 text-center">
            <i class="fas fa-history mr-2"></i>
            View Trading History
        </a>
    </div>
</div>

<script>
function filterBots() {
    const searchInput = document.getElementById('botSearch');
    const searchTerm = searchInput.value.toLowerCase().trim();
    const botCards = document.querySelectorAll('.bot-card');
    const noResults = document.getElementById('noResults');
    let visibleCount = 0;

    botCards.forEach(card => {
        const botName = card.getAttribute('data-bot-name');
        const botDescription = card.getAttribute('data-bot-description');

        if (searchTerm === '' ||
            botName.includes(searchTerm) ||
            botDescription.includes(searchTerm)) {
            card.style.display = 'block';
            card.classList.remove('hidden');
            visibleCount++;
        } else {
            card.style.display = 'none';
            card.classList.add('hidden');
        }
    });

    // Show/hide no results message
    if (visibleCount === 0 && searchTerm !== '') {
        noResults.classList.remove('hidden');
    } else {
        noResults.classList.add('hidden');
    }
}

// Clear search on Escape key
document.getElementById('botSearch').addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        this.value = '';
        filterBots();
        this.blur();
    }
});

// Real-time search as user types
document.getElementById('botSearch').addEventListener('input', filterBots);
</script>
@endsection
