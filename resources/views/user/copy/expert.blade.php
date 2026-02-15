@extends('layouts.dash')

@section('title', 'Expert Profile - ' . $expertTrader->name)

@section('content')
<div class="space-y-6">
    <!-- Back Button -->
    <div>
        <a href="{{ route('copy-trading.index') }}" class="inline-flex items-center text-gray-400 hover:text-white transition-colors">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Copy Trading
        </a>
    </div>

    <!-- Expert Header -->
    <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
        <div class="flex flex-col md:flex-row gap-6">
            <div class="flex items-start gap-4 flex-1">
                <div class="relative">
                    <img src="{{ $expertTrader->avatar ?? asset('dash/default-avatar.png') }}" 
                         alt="{{ $expertTrader->name }}" 
                         class="w-24 h-24 rounded-full border-4 border-primary-500">
                    @if($expertTrader->isOnline())
                        <div class="absolute bottom-2 right-2 w-6 h-6 bg-green-500 rounded-full border-4 border-dark-200"></div>
                    @endif
                </div>
                <div class="flex-1">
                    <h1 class="text-3xl font-bold text-white mb-2">{{ $expertTrader->name }}</h1>
                    <p class="text-gray-400 mb-3">{{ $expertTrader->specialization ?? 'Multi-Asset Trading' }}</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1 bg-primary-500/10 text-primary-400 rounded-full text-sm">
                            <i class="fas fa-chart-line mr-1"></i>
                            {{ $expertTrader->experience_years }} Years Experience
                        </span>
                        <!-- <span class="px-3 py-1 {{ $expertTrader->getRiskColorClass() }}/10 text-{{ $expertTrader->getRiskColorClass() }} rounded-full text-sm">
                            <i class="fas fa-shield-alt mr-1"></i>
                            {{ $expertTrader->getRiskLevelText() }} Risk
                        </span> -->
                        <span class="px-3 py-1 bg-green-500/10 text-green-400 rounded-full text-sm">
                            <i class="fas fa-users mr-1"></i>
                            {{ number_format($expertTrader->getActiveSubscribersCount()) }} Followers
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Action Button -->
            <div class="flex items-center">
                @if($userSubscription)
                    <a href="{{ route('copy-trading.details', $userSubscription->id) }}" class="px-6 py-3 bg-yellow-500/10 border border-yellow-500/20 hover:bg-yellow-500/20 text-yellow-400 font-semibold rounded-lg transition-colors">
                        <i class="fas fa-check-circle mr-2"></i>
                        Already Copying
                    </a>
                @else
                    <button onclick="showCopyModal({{ $expertTrader->id }})" class="px-6 py-3 bg-primary hover:bg-primary-dark text-white font-semibold rounded-lg transition-colors">
                        <i class="fas fa-copy mr-2"></i>
                        Start Copying
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Key Performance Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex items-center justify-between mb-2">
                <span class="text-gray-400 text-sm">30-Day ROI</span>
                <div class="w-10 h-10 bg-green-500/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-percentage text-green-400"></i>
                </div>
            </div>
            <p class="text-3xl font-bold text-{{ $expertTrader->current_roi >= 0 ? 'green' : 'red' }}-400">
                {{ $expertTrader->current_roi >= 0 ? '+' : '' }}{{ number_format($expertTrader->current_roi, 2) }}%
            </p>
        </div>

        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex items-center justify-between mb-2">
                <span class="text-gray-400 text-sm">Win Rate</span>
                <div class="w-10 h-10 bg-primary-500/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-bullseye text-primary-400"></i>
                </div>
            </div>
            <p class="text-3xl font-bold text-white">{{ number_format($expertTrader->current_win_rate, 1) }}%</p>
        </div>

        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex items-center justify-between mb-2">
                <span class="text-gray-400 text-sm">7-Day P&L</span>
                <div class="w-10 h-10 bg-{{ $expertTrader->current_7d_pnl >= 0 ? 'green' : 'red' }}-500/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-{{ $expertTrader->current_7d_pnl >= 0 ? 'green' : 'red' }}-400"></i>
                </div>
            </div>
            <p class="text-3xl font-bold text-{{ $expertTrader->current_7d_pnl >= 0 ? 'green' : 'red' }}-400">
                {{ $expertTrader->current_7d_pnl >= 0 ? '+' : '' }}${{ number_format($expertTrader->current_7d_pnl, 2) }}
            </p>
        </div>

        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex items-center justify-between mb-2">
                <span class="text-gray-400 text-sm">Portfolio Value</span>
                <div class="w-10 h-10 bg-blue-500/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-wallet text-blue-400"></i>
                </div>
            </div>
            <p class="text-3xl font-bold text-white">${{ number_format($expertTrader->current_portfolio_value, 0) }}</p>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Trading Strategy -->
            <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
                <h2 class="text-xl font-semibold text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-lightbulb text-primary-400"></i>
                    Trading Strategy
                </h2>
                <div class="prose prose-invert max-w-none">
                    <p class="text-gray-300 leading-relaxed">
                        {{ $expertTrader->trading_strategy ?? 'This expert employs a diversified trading strategy focusing on both technical and fundamental analysis. The approach combines momentum trading with risk management protocols to maximize returns while minimizing exposure.' }}
                    </p>
                </div>
            </div>

            <!-- About -->
            @if($expertTrader->description)
            <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
                <h2 class="text-xl font-semibold text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-user text-primary-400"></i>
                    About
                </h2>
                <p class="text-gray-300 leading-relaxed">{{ $expertTrader->description }}</p>
            </div>
            @endif

            <!-- Monthly Statistics -->
            <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
                <h2 class="text-xl font-semibold text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-chart-bar text-primary-400"></i>
                    30-Day Statistics
                </h2>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <div class="bg-dark-300 rounded-lg p-4">
                        <p class="text-gray-400 text-sm mb-1">Total Trades</p>
                        <p class="text-2xl font-bold text-white">{{ $monthlyStats['total_trades'] ?? 0 }}</p>
                    </div>
                    <div class="bg-dark-300 rounded-lg p-4">
                        <p class="text-gray-400 text-sm mb-1">Winning Trades</p>
                        <p class="text-2xl font-bold text-green-400">{{ $monthlyStats['winning_trades'] ?? 0 }}</p>
                    </div>
                    <div class="bg-dark-300 rounded-lg p-4">
                        <p class="text-gray-400 text-sm mb-1">Total P&L</p>
                        <p class="text-2xl font-bold text-{{ ($monthlyStats['total_pnl'] ?? 0) >= 0 ? 'green' : 'red' }}-400">
                            {{ ($monthlyStats['total_pnl'] ?? 0) >= 0 ? '+' : '' }}${{ number_format($monthlyStats['total_pnl'] ?? 0, 2) }}
                        </p>
                    </div>
                    <div class="bg-dark-300 rounded-lg p-4">
                        <p class="text-gray-400 text-sm mb-1">Avg Trade Size</p>
                        <p class="text-2xl font-bold text-white">${{ number_format($monthlyStats['avg_trade_size'] ?? 0, 2) }}</p>
                    </div>
                    <div class="bg-dark-300 rounded-lg p-4">
                        <p class="text-gray-400 text-sm mb-1">Best Trade</p>
                        <p class="text-2xl font-bold text-green-400">+${{ number_format($monthlyStats['best_trade'] ?? 0, 2) }}</p>
                    </div>
                    <div class="bg-dark-300 rounded-lg p-4">
                        <p class="text-gray-400 text-sm mb-1">Worst Trade</p>
                        <p class="text-2xl font-bold text-red-400">
                            @if(($monthlyStats['worst_trade'] ?? 0) > 0)
                                -${{ number_format($monthlyStats['worst_trade'], 2) }}
                            @else
                                $0.00
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <!-- Trading Assets -->
            <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
                <h2 class="text-xl font-semibold text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-coins text-primary-400"></i>
                    Trading Assets
                </h2>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    @php
                        // Get unique assets from expert trades
                        $tradingAssets = $expertTrader->expertTrades
                            ->pluck('tradingAsset')
                            ->unique('id')
                            ->filter()
                            ->values();
                    @endphp
                    
                    @forelse($tradingAssets as $asset)
                        <div class="bg-dark-300 rounded-lg p-3 flex items-center gap-3">
                            @if($asset->icon_url)
                                <img src="{{ $asset->icon_url }}" alt="{{ $asset->symbol }}" class="w-8 h-8 rounded-full">
                            @elseif($asset->logo_url)
                                <img src="{{ $asset->logo_url }}" alt="{{ $asset->symbol }}" class="w-8 h-8 rounded-full">
                            @else
                                <div class="w-8 h-8 bg-primary-500/10 rounded-full flex items-center justify-center">
                                    <i class="fas fa-coins text-primary-400 text-sm"></i>
                                </div>
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-white truncate">{{ $asset->symbol }}</p>
                                <p class="text-xs text-gray-400 truncate">{{ $asset->name }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-8">
                            <i class="fas fa-coins text-4xl text-gray-600 mb-2"></i>
                            <p class="text-gray-400">No trading assets available yet</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Trades -->
            <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
                <h2 class="text-xl font-semibold text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-history text-primary-400"></i>
                    Recent Trades
                </h2>
                <div class="space-y-3">
                    @forelse($expertTrader->expertTrades->take(10) as $trade)
                        <div class="bg-dark-300 rounded-lg p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3 flex-1">
                                @if($trade->tradingAsset)
                                    @if($trade->tradingAsset->icon_url)
                                        <img src="{{ $trade->tradingAsset->icon_url }}" alt="{{ $trade->tradingAsset->symbol }}" class="w-10 h-10 rounded-full">
                                    @elseif($trade->tradingAsset->logo_url)
                                        <img src="{{ $trade->tradingAsset->logo_url }}" alt="{{ $trade->tradingAsset->symbol }}" class="w-10 h-10 rounded-full">
                                    @else
                                        <div class="w-10 h-10 bg-primary-500/10 rounded-full flex items-center justify-center">
                                            <i class="fas fa-coins text-primary-400"></i>
                                        </div>
                                    @endif
                                @else
                                    <div class="w-10 h-10 bg-gray-500/10 rounded-full flex items-center justify-center">
                                        <i class="fas fa-chart-line text-gray-400"></i>
                                    </div>
                                @endif
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <p class="font-semibold text-white">
                                            {{ $trade->tradingAsset ? $trade->tradingAsset->symbol : 'N/A' }}
                                        </p>
                                        <span class="px-2 py-0.5 rounded text-xs {{ $trade->type == 'profit' ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400' }}">
                                            {{ ucfirst($trade->type) }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-400">
                                        {{ \Carbon\Carbon::parse($trade->created_at)->format('M d, Y h:i A') }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold {{ $trade->type == 'profit' ? 'text-green-400' : 'text-red-400' }}">
                                    {{ $trade->type == 'profit' ? '+' : '-' }}${{ number_format($trade->amount, 2) }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <i class="fas fa-chart-line text-4xl text-gray-600 mb-2"></i>
                            <p class="text-gray-400">No recent trades available</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
            <!-- Copy Settings -->
            <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
                <h2 class="text-xl font-semibold text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-cog text-primary-400"></i>
                    Copy Settings
                </h2>
                <div class="space-y-4">
                    <div class="flex justify-between items-center py-2 border-b border-dark-100">
                        <span class="text-gray-400">Min Copy Amount</span>
                        <span class="text-white font-semibold">${{ number_format($expertTrader->min_copy_amount) }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-dark-100">
                        <span class="text-gray-400">Max Copy Amount</span>
                        <span class="text-white font-semibold">${{ number_format($expertTrader->max_copy_amount) }}</span>
                    </div>

                </div>
            </div>

            <!-- Risk Management -->
            <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
                <h2 class="text-xl font-semibold text-white mb-4 flex items-center gap-2">
                    <i class="fas fa-shield-alt text-primary-400"></i>
                    Risk Management
                </h2>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-sm mb-2">
                            <span class="text-gray-400">Risk Score</span>
                            <span class="text-white font-semibold">{{ number_format($expertTrader->risk_score, 1) }}/10</span>
                        </div>
                        <div class="w-full bg-dark-300 rounded-full h-2">
                            <div class="bg-{{ $expertTrader->getRiskColorClass() }}-500 h-2 rounded-full" style="width: {{ $expertTrader->risk_score * 10 }}%"></div>
                        </div>
                    </div>
                    <p class="text-sm text-gray-400 mt-3">
                        <i class="fas fa-info-circle mr-1"></i>
                        {{ $expertTrader->getRiskLevelText() }} risk profile - 
                        @if($expertTrader->risk_score <= 3)
                            Conservative approach with focus on capital preservation
                        @elseif($expertTrader->risk_score <= 6)
                            Balanced strategy with moderate risk exposure
                        @else
                            Aggressive trading with higher return potential
                        @endif
                    </p>
                </div>
            </div>

            <!-- Action Card -->
            @if(!$userSubscription)
            <div class="bg-gradient-to-br from-primary/20 to-blue-500/20 rounded-xl p-6 border border-primary/30">
                <h3 class="text-xl font-bold text-white mb-2">Ready to Copy?</h3>
                <p class="text-gray-300 text-sm mb-4">Start copying {{ $expertTrader->name }}'s trades automatically and benefit from their expertise.</p>
                <button onclick="showCopyModal({{ $expertTrader->id }})" class="w-full px-4 py-3 bg-primary hover:bg-primary-dark text-white font-semibold rounded-lg transition-colors">
                    <i class="fas fa-copy mr-2"></i>
                    Start Copying Now
                </button>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Copy Trading Modal -->
<div id="copyModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-dark-200 rounded-xl p-6 border border-dark-100 max-w-md w-full mx-4">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-xl font-semibold text-white">Start Copy Trading</h3>
            <button onclick="closeCopyModal()" class="text-gray-400 hover:text-white">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <form id="copyForm" method="POST" action="{{ route('copy-trading.subscribe') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="expert_trader_id" value="{{ $expertTrader->id }}">
            
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">Copy Amount</label>
                <input type="number" 
                       name="amount" 
                       min="{{ $expertTrader->min_copy_amount }}" 
                       max="{{ $expertTrader->max_copy_amount }}"
                       step="0.01"
                       placeholder="Enter amount to copy"
                       class="w-full px-4 py-3 bg-dark-400 border border-dark-100 rounded-lg text-white placeholder-gray-500 focus:border-primary focus:outline-none"
                       required>
                <p class="text-xs text-gray-400 mt-1">Min: ${{ number_format($expertTrader->min_copy_amount) }} | Max: ${{ number_format($expertTrader->max_copy_amount) }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">Copy Percentage</label>
                <input type="range" 
                       id="copyPercentage"
                       name="copy_percentage" 
                       min="10" 
                       max="100" 
                       value="100"
                       class="w-full">
                <div class="flex justify-between text-xs text-gray-400 mt-1">
                    <span>10%</span>
                    <span id="currentPercentage">100%</span>
                    <span>100%</span>
                </div>
                <p class="text-xs text-gray-400 mt-1">Percentage of expert's trades to copy</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">Duration (days)</label>
                <select name="duration_days" class="w-full px-4 py-3 bg-dark-400 border border-dark-100 rounded-lg text-white">
                    <option value="1">1 day</option>
                    <option value="2">2 days</option>
                    <option value="3">3 days</option>
                    <option value="4">4 days</option>
                    <option value="5">5 days</option>
                    <option value="6">6 days</option>
                    <option value="7">7 days</option>
                    <option value="14">14 days</option>
                    <option value="30" selected>30 days</option>
                    <option value="60">60 days</option>
                    <option value="90">90 days</option>
                </select>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeCopyModal()" class="flex-1 px-4 py-3 bg-gray-600 hover:bg-gray-700 text-white font-semibold rounded-lg transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-3 bg-primary hover:bg-primary-dark text-white font-semibold rounded-lg transition-colors">
                    Start Copying
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showCopyModal(expertId) {
    document.getElementById('copyModal').classList.remove('hidden');
    document.getElementById('copyModal').classList.add('flex');
}

function closeCopyModal() {
    document.getElementById('copyModal').classList.add('hidden');
    document.getElementById('copyModal').classList.remove('flex');
}

// Update percentage display
document.getElementById('copyPercentage').addEventListener('input', function() {
    document.getElementById('currentPercentage').textContent = this.value + '%';
});

// Close modal on outside click
document.getElementById('copyModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCopyModal();
    }
});
</script>
@endsection
