@extends('layouts.dash')

@section('title', 'Copy Trading - Expert Traders')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Copy Trading</h1>
                <p class="text-gray-400 mt-1">Follow and copy successful traders automatically</p>
            </div>
            <div class="flex items-center gap-2 text-sm">
                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                <span class="text-green-400">{{ $activeExperts }} Expert Traders Online</span>
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

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Active Copies</p>
                    <p class="text-2xl font-bold text-white">{{ $activeSubscriptions->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-copy text-primary text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total Copied</p>
                    <p class="text-2xl font-bold text-white">${{ number_format($totalCopiedAmount) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-500/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-green-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total P&L</p>
                    <p class="text-2xl font-bold text-{{ $totalPnl >= 0 ? 'green' : 'red' }}-400">
                        {{ $totalPnl >= 0 ? '+' : '' }}${{ number_format($totalPnl, 2) }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-{{ $totalPnl >= 0 ? 'green' : 'red' }}-500/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-{{ $totalPnl >= 0 ? 'green' : 'red' }}-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Win Rate</p>
                    <p class="text-2xl font-bold text-primary">{{ number_format($overallWinRate, 1) }}%</p>
                </div>
                <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-percentage text-primary text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row gap-4">
        <a href="{{ route('copy-trading.history') }}" class="bg-dark-200 hover:bg-dark-100 text-white font-semibold py-3 px-6 rounded-lg border border-dark-100 transition-colors duration-200 text-center">
            <i class="fas fa-history mr-2"></i>
            View Copy History
        </a>
    </div>

    <!-- Active Subscriptions -->
    @if($activeSubscriptions->count() > 0)
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <h2 class="text-xl font-semibold text-white mb-6 flex items-center gap-2">
                <i class="fas fa-chart-line text-green-400"></i>
                My Active Copy Trading
            </h2>

            <div class="space-y-4">
                @foreach($activeSubscriptions as $subscription)
                    <div class="bg-dark-300 rounded-lg p-4 border border-dark-100">
                        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                            <div class="flex items-center gap-4 flex-1">
                                <div class="relative">
                                    <img src="{{ $subscription->expertTrader->avatar ?? asset('dash/default-avatar.png') }}" 
                                         alt="{{ $subscription->expertTrader->name }}" 
                                         class="w-12 h-12 rounded-full">
                                    @if($subscription->expertTrader->isOnline())
                                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-dark-300"></div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h3 class="text-lg font-semibold text-white">{{ $subscription->expertTrader->name }}</h3>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-500/10 text-green-400">
                                            <div class="w-2 h-2 bg-green-400 rounded-full mr-1 animate-pulse"></div>
                                            Copying
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                        <div>
                                            <span class="text-gray-400">Amount:</span>
                                            <div class="text-white font-medium">${{ number_format($subscription->amount) }}</div>
                                        </div>
                                        <div>
                                            <span class="text-gray-400">Current P&L:</span>
                                            <div class="font-medium {{ $subscription->total_pnl >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                                {{ $subscription->total_pnl >= 0 ? '+' : '' }}${{ number_format($subscription->total_pnl, 2) }}
                                            </div>
                                        </div>
                                        <div>
                                            <span class="text-gray-400">ROI:</span>
                                            <div class="font-medium {{ $subscription->current_roi >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                                {{ $subscription->current_roi >= 0 ? '+' : '' }}{{ number_format($subscription->current_roi, 2) }}%
                                            </div>
                                        </div>
                                        <div>
                                            <span class="text-gray-400">Expires:</span>
                                            <div class="text-white">{{ $subscription->getDaysRemaining() }} days</div>
                                        </div>
                                    </div>
                                    
                                    <!-- Recent Copy Trades -->
                                    @if($subscription->copyTrades->count() > 0)
                                        <div class="mt-3 pt-3 border-t border-dark-100">
                                            <div class="text-sm text-gray-400 mb-2">Recent Copy Trades:</div>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($subscription->copyTrades->take(5) as $trade)
                                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs {{ $trade->isProfit() ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400' }}">
                                                        <i class="fas {{ $trade->isProfit() ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-1"></i>
                                                        {{ $trade->isProfit() ? '+' : '' }}${{ number_format($trade->pnl, 2) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="flex gap-2">
                                <a href="{{ route('copy-trading.details', $subscription->id) }}" class="px-4 py-2 bg-primary/10 text-primary border border-primary/20 rounded-lg hover:bg-primary/20 transition-colors">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Details
                                </a>
                                <form action="{{ route('copy-trading.pause', $subscription->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 bg-yellow-500/10 text-yellow-400 border border-yellow-500/20 rounded-lg hover:bg-yellow-500/20 transition-colors">
                                        <i class="fas fa-pause mr-1"></i>
                                        Pause
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Expert Traders -->
    <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <h2 class="text-xl font-semibold text-white flex items-center gap-2">
                <i class="fas fa-users text-primary"></i>
                Expert Traders
            </h2>
            
            <!-- Search Box -->
            <div class="relative w-full md:w-64">
                <input type="text" 
                       id="expertSearch" 
                       placeholder="Search experts by name..." 
                       class="w-full px-4 py-2 pl-10 bg-dark-300 border border-dark-100 rounded-lg text-white placeholder-gray-500 focus:border-primary focus:outline-none text-sm">
                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
            @forelse($expertTraders as $expert)
                <div class="bg-dark-300 rounded-lg p-6 border border-dark-100 hover:border-primary/50 transition-all duration-300">
                    <!-- Expert Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="relative">

                            
                                <img src="{{ $expert->avatar ?? asset('dash/default-avatar.png') }}" 
                                     alt="{{ $expert->name }}" 
                                     class="w-12 h-12 rounded-full">
                                @if($expert->isOnline())
                                    <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-dark-300"></div>
                                @endif
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-white">{{ $expert->name }}</h3>
                                <p class="text-sm text-gray-400">{{ $expert->specialization ?? 'Multi-Asset' }}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-xs text-gray-400">{{ $expert->experience_years }}y exp</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-{{ $expert->current_roi >= 0 ? 'green' : 'red' }}-400">
                                {{ $expert->current_roi >= 0 ? '+' : '' }}{{ number_format($expert->current_roi, 1) }}%
                            </div>
                            <div class="text-xs text-gray-400">30D ROI</div>
                        </div>
                    </div>

                    <!-- Performance Metrics -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="bg-dark-400 rounded-lg p-3">
                            <div class="text-sm text-gray-400">7D P&L</div>
                            <div class="text-lg font-semibold text-{{ $expert->current_7d_pnl >= 0 ? 'green' : 'red' }}-400">
                                {{ $expert->current_7d_pnl >= 0 ? '+' : '' }}${{ number_format($expert->current_7d_pnl, 0) }}
                            </div>
                        </div>
                        <div class="bg-dark-400 rounded-lg p-3">
                            <div class="text-sm text-gray-400">Win Rate</div>
                            <div class="text-lg font-semibold text-white">{{ number_format($expert->current_win_rate, 1) }}%</div>
                        </div>
                        <div class="bg-dark-400 rounded-lg p-3">
                            <div class="text-sm text-gray-400">Followers</div>
                            <div class="text-lg font-semibold text-white">{{ number_format($expert->getActiveSubscribersCount()) }}</div>
                        </div>
                        <div class="bg-dark-400 rounded-lg p-3">
                            <div class="text-sm text-gray-400">Portfolio</div>
                            <div class="text-lg font-semibold text-white">${{ number_format($expert->current_portfolio_value, 0) }}</div>
                        </div>
                    </div>

                    <!-- Performance Chart -->
                    <div class="bg-dark-400 rounded-lg p-3 mb-4">
                        <div class="text-sm text-gray-400 mb-2">30-Day Performance</div>
                        <div class="h-16">
                            <canvas id="chart-{{ $expert->id }}" class="w-full" height="60"></canvas>
                        </div>
                    </div>

                    <!-- Copy Trading Info -->
                    <div class="space-y-3 mb-4">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-400">Min Copy Amount:</span>
                            <span class="text-white font-medium">${{ number_format($expert->min_copy_amount) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-400">Max Copy Amount:</span>
                            <span class="text-white font-medium">${{ number_format($expert->max_copy_amount) }}</span>
                        </div>

                    </div>

                    <!-- Action Buttons -->
                    <div class="space-y-2">
                        @php
                            $userSubscription = $activeSubscriptions->firstWhere('expert_trader_id', $expert->id);
                        @endphp
                        
                        @if($userSubscription)
                            <a href="{{ route('copy-trading.details', $userSubscription->id) }}" class="w-full bg-yellow-500/10 border border-yellow-500/20 hover:bg-yellow-500/20 text-yellow-400 font-semibold py-3 px-4 rounded-lg transition-colors duration-200 text-center block">
                                <i class="fas fa-check-circle mr-2"></i>
                                Already Copying
                            </a>
                            <form action="{{ route('copy-trading.pause', $userSubscription->id) }}" method="POST" class="w-full">
                                @csrf
                                <button type="submit" class="w-full bg-red-500/10 border border-red-500/20 hover:bg-red-500/20 text-red-400 font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-stop-circle mr-2"></i>
                                    Stop Copying
                                </button>
                            </form>
                        @else
                            <button onclick="showCopyModal({{ $expert->id }})" class="w-full bg-primary hover:bg-primary-dark text-white font-semibold py-3 px-4 rounded-lg transition-colors duration-200">
                                <i class="fas fa-copy mr-2"></i>
                                Start Copying
                            </button>
                        @endif
                        
                        <a href="{{ route('copy-trading.expert', $expert->id) }}" class="w-full bg-dark-400 hover:bg-dark-300 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200 text-center block">
                            <i class="fas fa-chart-area mr-2"></i>
                            View Profile
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <i class="fas fa-users text-6xl text-gray-600 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-400 mb-2">No Expert Traders Available</h3>
                    <p class="text-gray-500">Check back later for new trading experts.</p>
                </div>
            @endforelse
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

        <form id="copyForm" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" id="expertTraderId" name="expert_trader_id">
            
            <div>
                <label class="block text-sm font-medium text-gray-400 mb-2">Copy Amount</label>
                <input type="number" 
                       id="copyAmount"
                       name="amount" 
                       min="100" 
                       step="0.01"
                       placeholder="Enter amount to copy"
                       class="w-full px-4 py-3 bg-dark-400 border border-dark-100 rounded-lg text-white placeholder-gray-500 focus:border-primary focus:outline-none"
                       required>
                <p class="text-xs text-gray-400 mt-1">Minimum: $<span id="minAmount">100</span> | Maximum: $<span id="maxAmount">10,000</span></p>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
function showCopyModal(expertId) {
    document.getElementById('expertTraderId').value = expertId;
    document.getElementById('copyForm').action = '{{ route("copy-trading.subscribe") }}';
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

// Expert search functionality
document.getElementById('expertSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const expertCards = document.querySelectorAll('.bg-dark-300.rounded-lg.p-6');
    
    expertCards.forEach(card => {
        const expertName = card.querySelector('h3').textContent.toLowerCase();
        if (expertName.includes(searchTerm)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
});

// Initialize performance charts
document.addEventListener('DOMContentLoaded', function() {
    @foreach($expertTraders as $expert)
        (function() {
            const ctx = document.getElementById('chart-{{ $expert->id }}');
            if (!ctx) return;
            
            // Get last 30 days of trades for this expert
            const trades = @json($expert->expertTrades->where('created_at', '>=', now()->subDays(30))->sortBy('created_at')->values());
            
            // Calculate cumulative P&L for chart
            let cumulativePnL = 0;
            const chartData = [];
            const labels = [];
            
            if (trades.length === 0) {
                // No trades, show flat line at 0
                for (let i = 0; i < 30; i++) {
                    chartData.push(0);
                    labels.push('');
                }
            } else {
                trades.forEach(trade => {
                    if (trade.type === 'profit') {
                        cumulativePnL += parseFloat(trade.amount);
                    } else {
                        cumulativePnL -= parseFloat(trade.amount);
                    }
                    chartData.push(cumulativePnL);
                    labels.push('');
                });
            }
            
            const finalPnL = chartData.length > 0 ? chartData[chartData.length - 1] : 0;
            const lineColor = finalPnL >= 0 ? 'rgb(34, 197, 94)' : 'rgb(239, 68, 68)';
            const gradientColor = finalPnL >= 0 ? 'rgba(34, 197, 94, 0.2)' : 'rgba(239, 68, 68, 0.2)';
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        data: chartData,
                        borderColor: lineColor,
                        backgroundColor: gradientColor,
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 0,
                        pointHoverRadius: 4,
                        pointHoverBackgroundColor: lineColor,
                        pointHoverBorderColor: '#fff',
                        pointHoverBorderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            enabled: true,
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            padding: 8,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return '$' + context.parsed.y.toFixed(2);
                                },
                                title: function() {
                                    return '30D P&L';
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            display: false,
                            grid: { display: false }
                        },
                        y: {
                            display: false,
                            grid: { display: false }
                        }
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        })();
    @endforeach
});
</script>
@endsection