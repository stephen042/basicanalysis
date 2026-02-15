@extends('layouts.dash')

@section('title', 'Copy Trading Analytics')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Copy Trading Analytics</h1>
                <p class="text-gray-400 mt-1">Detailed performance analysis of your copy trading activities</p>
            </div>
            <a href="{{ route('copy-trading.index') }}" class="px-4 py-2 bg-dark-300 hover:bg-dark-100 text-white border border-dark-100 rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Copy Trading
            </a>
        </div>
    </div>

    <!-- Key Performance Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total Portfolio Value</p>
                    <p class="text-2xl font-bold text-white">${{ number_format($totalPortfolioValue ?? 0) }}</p>
                    <p class="text-sm {{ ($portfolioChange ?? 0) >= 0 ? 'text-green-400' : 'text-red-400' }}">
                        {{ ($portfolioChange ?? 0) >= 0 ? '+' : '' }}{{ number_format($portfolioChange ?? 0, 2) }}% this month
                    </p>
                </div>
                <div class="w-12 h-12 bg-blue-500/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-wallet text-blue-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total Profit/Loss</p>
                    <p class="text-2xl font-bold {{ ($totalPnl ?? 0) >= 0 ? 'text-green-400' : 'text-red-400' }}">
                        {{ ($totalPnl ?? 0) >= 0 ? '+' : '' }}${{ number_format($totalPnl ?? 0, 2) }}
                    </p>
                    <p class="text-sm text-gray-400">Across all positions</p>
                </div>
                <div class="w-12 h-12 bg-{{ ($totalPnl ?? 0) >= 0 ? 'green' : 'red' }}-500/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-{{ ($totalPnl ?? 0) >= 0 ? 'green' : 'red' }}-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Win Rate</p>
                    <p class="text-2xl font-bold text-primary">{{ number_format($overallWinRate ?? 0, 1) }}%</p>
                    <p class="text-sm text-gray-400">{{ $totalTrades ?? 0 }} total trades</p>
                </div>
                <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-percentage text-primary text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Active Experts</p>
                    <p class="text-2xl font-bold text-white">{{ $activeExpertsCount ?? 0 }}</p>
                    <p class="text-sm text-gray-400">Currently copying</p>
                </div>
                <div class="w-12 h-12 bg-purple-500/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-purple-400 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Portfolio Performance Chart -->
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-white">Portfolio Performance</h3>
                <select class="bg-dark-300 border border-dark-100 rounded-lg px-3 py-2 text-white text-sm">
                    <option value="7">Last 7 days</option>
                    <option value="30" selected>Last 30 days</option>
                    <option value="90">Last 90 days</option>
                </select>
            </div>
            <div class="h-64">
                <canvas id="portfolioChart"></canvas>
            </div>
        </div>

        <!-- Profit/Loss Distribution -->
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <h3 class="text-lg font-semibold text-white mb-6">Profit/Loss Distribution</h3>
            <div class="h-64">
                <canvas id="pnlChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Expert Performance Analysis -->
    <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
        <h3 class="text-lg font-semibold text-white mb-6">Expert Performance Breakdown</h3>
        
        @if(isset($expertPerformance) && count($expertPerformance) > 0)
            <div class="space-y-4">
                @foreach($expertPerformance as $expert)
                    <div class="bg-dark-300 rounded-lg p-4 border border-dark-100">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $expert['avatar'] ?? asset('dash/default-avatar.png') }}" 
                                     alt="{{ $expert['name'] }}" 
                                     class="w-10 h-10 rounded-full">
                                <div>
                                    <h4 class="text-white font-semibold">{{ $expert['name'] }}</h4>
                                    <p class="text-sm text-gray-400">{{ $expert['specialization'] ?? 'Multi-Asset' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-bold {{ $expert['total_pnl'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                    {{ $expert['total_pnl'] >= 0 ? '+' : '' }}${{ number_format($expert['total_pnl'], 2) }}
                                </div>
                                <div class="text-sm text-gray-400">Total P&L</div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
                            <div>
                                <span class="text-gray-400">Amount Copied:</span>
                                <div class="text-white font-medium">${{ number_format($expert['amount_copied']) }}</div>
                            </div>
                            <div>
                                <span class="text-gray-400">ROI:</span>
                                <div class="font-medium {{ $expert['roi'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                    {{ $expert['roi'] >= 0 ? '+' : '' }}{{ number_format($expert['roi'], 2) }}%
                                </div>
                            </div>
                            <div>
                                <span class="text-gray-400">Win Rate:</span>
                                <div class="text-white">{{ number_format($expert['win_rate'], 1) }}%</div>
                            </div>
                            <div>
                                <span class="text-gray-400">Total Trades:</span>
                                <div class="text-white">{{ $expert['total_trades'] }}</div>
                            </div>
                            <div>
                                <span class="text-gray-400">Days Copying:</span>
                                <div class="text-white">{{ $expert['days_copying'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <i class="fas fa-chart-bar text-4xl text-gray-600 mb-4"></i>
                <h4 class="text-lg font-semibold text-gray-400 mb-2">No Expert Performance Data</h4>
                <p class="text-gray-500">Start copying experts to see performance analytics here.</p>
            </div>
        @endif
    </div>

    <!-- Trading Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Copy Trades -->
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-semibold text-white">Recent Copy Trades</h3>
                <a href="{{ route('copy-trading.history') }}" class="text-primary hover:text-primary-dark text-sm">
                    View All <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            
            @if(isset($recentTrades) && count($recentTrades) > 0)
                <div class="space-y-3">
                    @foreach($recentTrades as $trade)
                        <div class="flex items-center justify-between p-3 bg-dark-300 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 {{ $trade['pnl'] >= 0 ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400' }} rounded-lg flex items-center justify-center">
                                    <i class="fas {{ $trade['pnl'] >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} text-xs"></i>
                                </div>
                                <div>
                                    <div class="text-white font-medium">{{ $trade['expert_name'] }}</div>
                                    <div class="text-xs text-gray-400">{{ $trade['asset'] ?? 'Multi-Asset' }} • {{ $trade['created_at'] }}</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-medium {{ $trade['pnl'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                    {{ $trade['pnl'] >= 0 ? '+' : '' }}${{ number_format($trade['pnl'], 2) }}
                                </div>
                                <div class="text-xs text-gray-400">${{ number_format($trade['amount'], 0) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fas fa-history text-4xl text-gray-600 mb-3"></i>
                    <p class="text-gray-400">No recent trades to display</p>
                </div>
            @endif
        </div>

        <!-- Monthly Statistics -->
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <h3 class="text-lg font-semibold text-white mb-6">Monthly Statistics</h3>
            
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-gray-400">Total Trades This Month</span>
                    <span class="text-white font-semibold">{{ $monthlyStats['total_trades'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-400">Winning Trades</span>
                    <span class="text-green-400 font-semibold">{{ $monthlyStats['winning_trades'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-400">Losing Trades</span>
                    <span class="text-red-400 font-semibold">{{ $monthlyStats['losing_trades'] ?? 0 }}</span>
                </div>
                <div class="flex justify-between items-center pt-3 border-t border-dark-100">
                    <span class="text-gray-400">Average Trade Size</span>
                    <span class="text-white font-semibold">${{ number_format($monthlyStats['avg_trade_size'] ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-400">Best Trade</span>
                    <span class="text-green-400 font-semibold">+${{ number_format($monthlyStats['best_trade'] ?? 0, 2) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-400">Worst Trade</span>
                    <span class="text-red-400 font-semibold">-${{ number_format($monthlyStats['worst_trade'] ?? 0, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Risk Analysis -->
    <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
        <h3 class="text-lg font-semibold text-white mb-6">Risk Analysis</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-dark-300 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-white font-medium">Portfolio Risk Level</h4>
                    <span class="px-2 py-1 {{ ($riskLevel ?? 'medium') === 'low' ? 'bg-green-500/10 text-green-400' : (($riskLevel ?? 'medium') === 'high' ? 'bg-red-500/10 text-red-400' : 'bg-yellow-500/10 text-yellow-400') }} rounded text-xs">
                        {{ ucfirst($riskLevel ?? 'Medium') }}
                    </span>
                </div>
                <div class="w-full bg-dark-400 rounded-full h-2">
                    <div class="h-2 rounded-full {{ ($riskLevel ?? 'medium') === 'low' ? 'bg-green-500' : (($riskLevel ?? 'medium') === 'high' ? 'bg-red-500' : 'bg-yellow-500') }}" 
                         style="width: {{ ($riskLevel ?? 'medium') === 'low' ? '30' : (($riskLevel ?? 'medium') === 'high' ? '80' : '60') }}%"></div>
                </div>
            </div>
            
            <div class="bg-dark-300 rounded-lg p-4">
                <h4 class="text-white font-medium mb-3">Max Drawdown</h4>
                <div class="text-2xl font-bold text-red-400">{{ number_format($maxDrawdown ?? 0, 2) }}%</div>
                <p class="text-xs text-gray-400 mt-1">Largest portfolio decline</p>
            </div>
            
            <div class="bg-dark-300 rounded-lg p-4">
                <h4 class="text-white font-medium mb-3">Sharpe Ratio</h4>
                <div class="text-2xl font-bold text-{{ ($sharpeRatio ?? 0) > 1 ? 'green' : 'yellow' }}-400">{{ number_format($sharpeRatio ?? 0, 2) }}</div>
                <p class="text-xs text-gray-400 mt-1">Risk-adjusted returns</p>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Portfolio Performance Chart
const portfolioCtx = document.getElementById('portfolioChart').getContext('2d');
new Chart(portfolioCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($portfolioChartLabels ?? ['Week 1', 'Week 2', 'Week 3', 'Week 4']) !!},
        datasets: [{
            label: 'Portfolio Value',
            data: {!! json_encode($portfolioChartData ?? [10000, 10500, 10200, 11000]) !!},
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                labels: {
                    color: '#9ca3af'
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    color: '#6b7280'
                },
                grid: {
                    color: 'rgba(75, 85, 99, 0.3)'
                }
            },
            y: {
                ticks: {
                    color: '#6b7280',
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                },
                grid: {
                    color: 'rgba(75, 85, 99, 0.3)'
                }
            }
        }
    }
});

// P&L Distribution Chart
const pnlCtx = document.getElementById('pnlChart').getContext('2d');
new Chart(pnlCtx, {
    type: 'doughnut',
    data: {
        labels: ['Profitable Trades', 'Break-even', 'Losing Trades'],
        datasets: [{
            data: {!! json_encode($pnlDistribution ?? [65, 10, 25]) !!},
            backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    color: '#9ca3af',
                    padding: 20
                }
            }
        }
    }
});
</script>
@endsection