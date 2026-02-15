@extends('layouts.dash')
@section('title', $title)
@section('content')
    <!-- Page title & actions -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white md:text-3xl">My Bots Investment</h1>
            <p class="mt-1 text-sm text-gray-400">Track and manage your trading bot investments</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('trading-bots.index') }}"
               class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-300 bg-dark-200 border border-dark-100 rounded-lg hover:bg-dark-100 transition-colors">
                <i class="fas fa-robot mr-2"></i>
                Browse Bots
            </a>
        </div>
    </div>

    <x-danger-alert />
    <x-success-alert />

    <!-- Investment Overview Stats -->
    <div class="grid grid-cols-1 gap-6 mb-8 sm:grid-cols-2 lg:grid-cols-4">
        <div class="p-6 bg-dark-200 rounded-xl border border-dark-100">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">Total Investment</p>
                    <p class="mt-2 text-2xl font-bold text-white">{{ Auth::user()->currency }}{{ number_format($stats['total_investment'], 2) }}</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 text-primary-400 bg-primary-500/10 rounded-full">
                    <i class="text-xl fas fa-wallet"></i>
                </div>
            </div>
        </div>

        <div class="p-6 bg-dark-200 rounded-xl border border-dark-100">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">Current Value</p>
                    <p class="mt-2 text-2xl font-bold text-white">{{ Auth::user()->currency }}{{ number_format($stats['total_current_value'], 2) }}</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 text-primary-400 bg-primary-500/10 rounded-full">
                    <i class="text-xl fas fa-chart-line"></i>
                </div>
            </div>
        </div>

        <div class="p-6 bg-dark-200 rounded-xl border border-dark-100">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">Net P&L</p>
                    <p class="mt-2 text-2xl font-bold {{ $stats['net_profit'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                        {{ $stats['net_profit'] >= 0 ? '+' : '' }}{{ Auth::user()->currency }}{{ number_format($stats['net_profit'], 2) }}
                    </p>
                    <p class="text-xs {{ $stats['profit_percentage'] >= 0 ? 'text-green-400' : 'text-red-400' }} mt-1">
                        {{ $stats['profit_percentage'] >= 0 ? '+' : '' }}{{ number_format($stats['profit_percentage'], 2) }}%
                    </p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 {{ $stats['net_profit'] >= 0 ? 'text-green-400 bg-green-500/10' : 'text-red-400 bg-red-500/10' }} rounded-full">
                    <i class="text-xl fas {{ $stats['net_profit'] >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i>
                </div>
            </div>
        </div>

        <div class="p-6 bg-dark-200 rounded-xl border border-dark-100">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">Active Bots</p>
                    <p class="mt-2 text-2xl font-bold text-white">{{ $stats['active_bots'] }}</p>
                    <p class="text-xs text-gray-400 mt-1">of {{ $stats['total_bots'] }} total</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 text-primary-400 bg-primary-500/10 rounded-full">
                    <i class="text-xl fas fa-robot"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Chart & Quick Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Performance Chart -->
        <div class="lg:col-span-2 p-6 bg-dark-200 rounded-xl border border-dark-100">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-white">Performance Overview</h3>
                <div class="flex space-x-2 text-xs">
                    <span class="px-2 py-1 bg-green-500/10 text-green-400 rounded">● Profit</span>
                    <span class="px-2 py-1 bg-red-500/10 text-red-400 rounded">● Loss</span>
                </div>
            </div>
            <div class="h-64">
                <canvas id="performanceChart"></canvas>
            </div>
        </div>

        <!-- Quick Statistics -->
        <div class="p-6 bg-dark-200 rounded-xl border border-dark-100">
            <h3 class="text-lg font-semibold text-white mb-6">Quick Stats</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-400">Total Trades</span>
                    <span class="text-sm font-medium text-white">{{ number_format($stats['total_trades']) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-400">Active Bots</span>
                    <span class="text-sm font-medium text-white">{{ $stats['active_bots'] }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-400">Completed Bots</span>
                    <span class="text-sm font-medium text-white">{{ $stats['completed_bots'] }}</span>
                </div>
                <div class="border-t border-dark-100 pt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-sm text-gray-400">Success Rate</span>
                        <span class="text-sm font-medium text-white">
                            {{ $stats['total_trades'] > 0 ? number_format(($stats['net_profit'] > 0 ? 1 : 0) * 100, 1) : 0 }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- My Trading Bots -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-white">My Trading Bots</h2>
            <span class="text-sm text-gray-400">{{ $userTradingBots->count() }} bot{{ $userTradingBots->count() !== 1 ? 's' : '' }}</span>
        </div>

        @if($userTradingBots->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($userTradingBots as $userBot)
                    @php
                        $botProfit = $userBot->tradingLogs->where('type', 'profit')->sum('amount');
                        $botLoss = $userBot->tradingLogs->where('type', 'loss')->sum('amount');
                        $netProfit = $botProfit - $botLoss;
                        $profitPercentage = $userBot->amount > 0 ? (($netProfit / $userBot->amount) * 100) : 0;
                        $totalTrades = $userBot->tradingLogs->count();
                    @endphp
                    <div class="p-6 bg-dark-200 rounded-xl border border-dark-100 hover:border-primary-500/30 transition-all">
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-white">{{ $userBot->tradingBot->name }}</h3>
                                <p class="text-sm text-gray-400 mt-1">{{ $userBot->tradingBot->description }}</p>
                            </div>
                            <span class="px-2 py-1 text-xs font-medium rounded-full
                                {{ $userBot->status === 'active' ? 'bg-green-500/10 text-green-400' :
                                   ($userBot->status === 'completed' ? 'bg-blue-500/10 text-blue-400' : 'bg-gray-500/10 text-gray-400') }}">
                                {{ ucfirst($userBot->status) }}
                            </span>
                        </div>

                        <div class="space-y-3 mb-4">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-400">Investment</span>
                                <span class="text-sm font-medium text-white">{{ Auth::user()->currency }}{{ number_format($userBot->amount, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-400">Current Value</span>
                                <span class="text-sm font-medium text-white">{{ Auth::user()->currency }}{{ number_format($userBot->amount + $netProfit, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-400">P&L</span>
                                <span class="text-sm font-medium {{ $netProfit >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                    {{ $netProfit >= 0 ? '+' : '' }}{{ Auth::user()->currency }}{{ number_format($netProfit, 2) }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-400">Return</span>
                                <span class="text-sm font-medium {{ $profitPercentage >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                    {{ $profitPercentage >= 0 ? '+' : '' }}{{ number_format($profitPercentage, 2) }}%
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-400">Trades</span>
                                <span class="text-sm font-medium text-white">{{ number_format($totalTrades) }}</span>
                            </div>
                        </div>

                        <div class="flex space-x-3">
                            <a href="{{ route('trading-bots.details', $userBot->id) }}"
                               class="flex-1 px-3 py-2 text-sm font-medium text-center text-primary-400 bg-primary-500/10 hover:bg-primary-500/20 border border-primary-500/20 rounded-lg transition-colors">
                                View Details
                            </a>
                            @if($userBot->status === 'active')
                                <form action="{{ route('trading-bots.cancel', $userBot->id) }}" method="POST" class="flex-1">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            onclick="return confirm('Are you sure you want to cancel this bot?')"
                                            class="w-full px-3 py-2 text-sm font-medium text-center text-red-400 bg-red-500/10 hover:bg-red-500/20 border border-red-500/20 rounded-lg transition-colors">
                                        Cancel
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-12 text-center bg-dark-200 rounded-xl border border-dark-100">
                <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 text-gray-400 bg-gray-500/10 rounded-full">
                    <i class="text-2xl fas fa-robot"></i>
                </div>
                <h3 class="text-lg font-medium text-white mb-2">No Trading Bots Yet</h3>
                <p class="text-sm text-gray-400 mb-6">Start your automated trading journey by subscribing to a trading bot.</p>
                <a href="{{ route('trading-bots.index') }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-primary-500 hover:bg-primary-600 rounded-lg transition-colors">
                    <i class="fas fa-robot mr-2"></i>
                    Browse Trading Bots
                </a>
            </div>
        @endif
    </div>

    <!-- Top Performing Bots -->
    @if($topPerformingBots->count() > 0)
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-white mb-6">Top Performing Bots</h2>
        <div class="p-6 bg-dark-200 rounded-xl border border-dark-100">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-dark-100">
                            <th class="text-left py-3 text-sm font-medium text-gray-400">Bot Name</th>
                            <th class="text-right py-3 text-sm font-medium text-gray-400">Investment</th>
                            <th class="text-right py-3 text-sm font-medium text-gray-400">P&L</th>
                            <th class="text-right py-3 text-sm font-medium text-gray-400">Return %</th>
                            <th class="text-right py-3 text-sm font-medium text-gray-400">Trades</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topPerformingBots as $bot)
                        <tr class="border-b border-dark-100/50 last:border-0">
                            <td class="py-4">
                                <div class="flex items-center">
                                    <span class="text-sm font-medium text-white">{{ $bot->tradingBot->name }}</span>
                                    <span class="ml-2 px-2 py-1 text-xs rounded-full
                                        {{ $bot->status === 'active' ? 'bg-green-500/10 text-green-400' : 'bg-gray-500/10 text-gray-400' }}">
                                        {{ ucfirst($bot->status) }}
                                    </span>
                                </div>
                            </td>
                            <td class="text-right py-4 text-sm text-white">{{ Auth::user()->currency }}{{ number_format($bot->amount, 2) }}</td>
                            <td class="text-right py-4 text-sm {{ $bot->net_profit >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                {{ $bot->net_profit >= 0 ? '+' : '' }}{{ Auth::user()->currency }}{{ number_format($bot->net_profit, 2) }}
                            </td>
                            <td class="text-right py-4 text-sm {{ $bot->profit_percentage >= 0 ? 'text-green-400' : 'text-red-400' }}">
                                {{ $bot->profit_percentage >= 0 ? '+' : '' }}{{ number_format($bot->profit_percentage, 2) }}%
                            </td>
                            <td class="text-right py-4 text-sm text-white">{{ number_format($bot->total_trades) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Trading Activity -->
    @if($recentActivities->count() > 0)
    <div>
        <h2 class="text-xl font-semibold text-white mb-6">Recent Trading Activity</h2>
        <div class="p-6 bg-dark-200 rounded-xl border border-dark-100">
            <div class="space-y-4">
                @foreach($recentActivities->take(5) as $activity)
                <div class="flex items-center justify-between py-3 border-b border-dark-100/50 last:border-0">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center justify-center w-10 h-10 rounded-full {{ $activity->type === 'profit' ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400' }}">
                            <i class="text-sm fas {{ $activity->type === 'profit' ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-white">{{ $activity->userTradingBot->tradingBot->name }}</p>
                            <p class="text-xs text-gray-400">{{ $activity->tradingAsset->name ?? 'Asset' }} • {{ $activity->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium {{ $activity->type === 'profit' ? 'text-green-400' : 'text-red-400' }}">
                            {{ $activity->type === 'profit' ? '+' : '-' }}{{ Auth::user()->currency }}{{ number_format($activity->amount, 2) }}
                        </p>
                        <p class="text-xs text-gray-400 capitalize">{{ $activity->type }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @if($recentActivities->count() > 5)
            <div class="mt-4 pt-4 border-t border-dark-100">
                <a href="{{ route('trading-bots.history') }}"
                   class="text-sm text-primary-400 hover:text-primary-300">
                    View all activity →
                </a>
            </div>
            @endif
        </div>
    </div>
    @endif

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Performance Chart
const ctx = document.getElementById('performanceChart').getContext('2d');
const monthlyData = @json($monthlyData);

const labels = monthlyData.map(item => {
    const date = new Date(item.year, item.month - 1);
    return date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' });
});

const profitData = monthlyData.map(item => item.monthly_profit);
const lossData = monthlyData.map(item => -item.monthly_loss);

new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Profit',
            data: profitData,
            borderColor: '#10b981',
            backgroundColor: 'rgba(16, 185, 129, 0.1)',
            tension: 0.4,
            fill: true
        }, {
            label: 'Loss',
            data: lossData,
            borderColor: '#ef4444',
            backgroundColor: 'rgba(239, 68, 68, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            x: {
                grid: {
                    color: 'rgba(255, 255, 255, 0.1)'
                },
                ticks: {
                    color: '#9CA3AF'
                }
            },
            y: {
                grid: {
                    color: 'rgba(255, 255, 255, 0.1)'
                },
                ticks: {
                    color: '#9CA3AF',
                    callback: function(value) {
                        return '{{ Auth::user()->currency }}' + value.toLocaleString();
                    }
                }
            }
        },
        elements: {
            point: {
                radius: 4,
                hoverRadius: 6
            }
        }
    }
});
</script>
@endsection
