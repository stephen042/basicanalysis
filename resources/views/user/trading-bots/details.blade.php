@extends('layouts.dash')

@section('title', 'Trading Bot Details - ' . $userBot->tradingBot->name)

@section('content')
<div class="space-y-6">
    <!-- Header with Bot Info -->
    <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-primary/10 rounded-xl flex items-center justify-center">
                    <i class="fas fa-robot text-primary text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">{{ $userBot->tradingBot->name }}</h1>
                    <p class="text-gray-400 mt-1">{{ $userBot->tradingBot->description }}</p>
                    <div class="flex items-center gap-4 mt-2">
                        @if($userBot->status === 'active')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-green-500/10 text-green-400">
                                <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                                Active Trading
                            </span>
                        @elseif($userBot->status === 'completed')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-500/10 text-blue-400">
                                <i class="fas fa-check mr-2"></i>
                                Completed
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-red-500/10 text-red-400">
                                <i class="fas fa-times mr-2"></i>
                                Cancelled
                            </span>
                        @endif
                        <span class="text-sm text-gray-400">
                            Started: {{ $userBot->created_at->format('M d, Y H:i') }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('trading-bots.history') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                    <i class="fas fa-history mr-2"></i>
                    History
                </a>
                <a href="{{ route('trading-bots.index') }}" class="bg-primary hover:bg-primary-dark text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Bots
                </a>
            </div>
        </div>
    </div>

    <!-- Performance Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Investment Amount -->
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Investment Amount</p>
                    <p class="text-2xl font-bold text-white">{{ Auth::user()->currency }}{{ number_format($userBot->amount, 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-500/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-blue-400 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Current Profit/Loss -->
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Current P&L</p>
                    @if($netProfit > 0)
                        <p class="text-2xl font-bold text-green-400">+{{ Auth::user()->currency }}{{ number_format($netProfit, 2) }}</p>
                    @elseif($netProfit < 0)
                        <p class="text-2xl font-bold text-red-400">-{{ Auth::user()->currency }}{{ number_format(abs($netProfit), 2) }}</p>
                    @else
                        <p class="text-2xl font-bold text-gray-400">{{ Auth::user()->currency }}0.00</p>
                    @endif
                </div>
                <div class="w-12 h-12 bg-{{ $netProfit >= 0 ? 'green' : 'red' }}-500/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-{{ $netProfit >= 0 ? 'arrow-up' : 'arrow-down' }} text-{{ $netProfit >= 0 ? 'green' : 'red' }}-400 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- ROI Percentage -->
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Current ROI</p>
                    @php
                        $roiPercentage = $userBot->amount > 0 ? ($netProfit / $userBot->amount) * 100 : 0;
                    @endphp
                    <p class="text-2xl font-bold text-{{ $roiPercentage >= 0 ? 'green' : 'red' }}-400">
                        {{ $roiPercentage >= 0 ? '+' : '' }}{{ number_format($roiPercentage, 2) }}%
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-500/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-percentage text-purple-400 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Expected Profit -->
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Expected Profit</p>
                    @php
                        $expectedProfit = ($userBot->tradingBot->profit_rate / 100) * $userBot->amount;
                    @endphp
                    <p class="text-2xl font-bold text-primary">+{{ Auth::user()->currency }}{{ number_format($expectedProfit, 2) }}</p>
                    <p class="text-xs text-gray-400">{{ $userBot->tradingBot->profit_rate }}% target</p>
                </div>
                <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-primary text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress and Timeline -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Progress Bar -->
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <i class="fas fa-chart-line text-primary"></i>
                Trading Progress
            </h3>

            @php
                $startTime = \Carbon\Carbon::parse($userBot->created_at);
                $endTime = \Carbon\Carbon::parse($userBot->expires_at);
                $currentTime = \Carbon\Carbon::now();

                if ($userBot->status === 'completed') {
                    $progressPercentage = 100;
                    $timeRemaining = 'Completed';
                } elseif ($userBot->status === 'cancelled') {
                    $progressPercentage = $startTime->diffInHours($currentTime) / $startTime->diffInHours($endTime) * 100;
                    $timeRemaining = 'Cancelled';
                } else {
                    $totalHours = $startTime->diffInHours($endTime);
                    $elapsedHours = $startTime->diffInHours($currentTime);
                    $progressPercentage = min(($elapsedHours / $totalHours) * 100, 100);

                    if ($currentTime > $endTime) {
                        $timeRemaining = 'Expired';
                        $progressPercentage = 100;
                    } else {
                        $diff = $endTime->diff($currentTime);
                        if ($diff->d > 0) {
                            $timeRemaining = $diff->d . ' days, ' . $diff->h . ' hours remaining';
                        } elseif ($diff->h > 0) {
                            $timeRemaining = $diff->h . ' hours, ' . $diff->i . ' minutes remaining';
                        } else {
                            $timeRemaining = $diff->i . ' minutes remaining';
                        }
                    }
                }
            @endphp

            <div class="space-y-4">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-400">Duration: {{ $userBot->tradingBot->duration }} hours</span>
                    <span class="text-white">{{ number_format($progressPercentage, 1) }}% Complete</span>
                </div>

                <div class="w-full bg-dark-300 rounded-full h-3">
                    <div class="bg-gradient-to-r from-primary to-blue-500 h-3 rounded-full transition-all duration-300"
                         style="width: {{ $progressPercentage }}%"></div>
                </div>

                <div class="flex justify-between text-sm">
                    <span class="text-gray-400">Started: {{ $startTime->format('M d, H:i') }}</span>
                    <span class="text-white">{{ $timeRemaining }}</span>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <i class="fas fa-chart-bar text-primary"></i>
                Trading Statistics
            </h3>

            <div class="space-y-4">
                <div class="flex justify-between">
                    <span class="text-gray-400">Total Trades</span>
                    <span class="text-white font-semibold">{{ $totalTrades }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Winning Trades</span>
                    <span class="text-green-400 font-semibold">{{ $profitTrades->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Losing Trades</span>
                    <span class="text-red-400 font-semibold">{{ $lossTrades->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Win Rate</span>
                    <span class="text-primary font-semibold">{{ number_format($winRate, 1) }}%</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Avg Profit</span>
                    <span class="text-green-400 font-semibold">
                        {{ Auth::user()->currency }}{{ number_format($avgProfit, 2) }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Avg Loss</span>
                    <span class="text-red-400 font-semibold">
                        {{ Auth::user()->currency }}{{ number_format($avgLoss, 2) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Trading Activity -->
    <div class="bg-dark-200 rounded-xl border border-dark-100 overflow-hidden">
        <div class="p-6 border-b border-dark-100">
            <h2 class="text-xl font-semibold text-white flex items-center gap-2">
                <i class="fas fa-list text-primary"></i>
                Detailed Trading Activity
            </h2>
            <p class="text-gray-400 text-sm mt-1">Complete log of all trading operations with asset details</p>
        </div>

        @if($tradingLogs->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-dark-300">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Time</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Asset</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Asset Price</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">ROI Impact</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dark-100">
                        @foreach($tradingLogs as $log)
                            <tr class="hover:bg-dark-300/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-sm text-white">{{ $log->created_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-400">{{ $log->created_at->format('H:i:s') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($log->tradingAsset)
                                        <div class="flex items-center gap-2">
                                            @if($log->tradingAsset->icon_url)
                                                <img src="{{ $log->tradingAsset->icon_url }}" alt="{{ $log->tradingAsset->name }}" class="w-6 h-6 rounded-full">
                                            @else
                                                <div class="w-6 h-6 bg-primary/10 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-coins text-primary text-xs"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="text-sm font-medium text-white">{{ $log->tradingAsset->symbol }}</div>
                                                <div class="text-xs text-gray-400">{{ $log->tradingAsset->name }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-sm text-gray-400">N/A</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($log->type === 'profit')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-500/10 text-green-400">
                                            <i class="fas fa-arrow-up mr-1"></i>
                                            Profit
                                        </span>
                                    @elseif($log->type === 'loss')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-red-500/10 text-red-400">
                                            <i class="fas fa-arrow-down mr-1"></i>
                                            Loss
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-500/10 text-blue-400">
                                            <i class="fas fa-exchange-alt mr-1"></i>
                                            {{ ucfirst($log->type) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-{{ $log->type === 'profit' ? 'green' : ($log->type === 'loss' ? 'red' : 'white') }}-400">
                                        {{ $log->type === 'profit' ? '+' : ($log->type === 'loss' ? '-' : '') }}{{ Auth::user()->currency }}{{ number_format($log->amount, 2) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($log->asset_price)
                                        <div class="text-sm text-white">{{ Auth::user()->currency }}{{ number_format($log->asset_price, 4) }}</div>
                                    @else
                                        <div class="text-sm text-gray-400">-</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($log->quantity)
                                        <div class="text-sm text-white">{{ number_format($log->quantity, 6) }}</div>
                                        @if($log->tradingAsset)
                                            <div class="text-xs text-gray-400">{{ $log->tradingAsset->symbol }}</div>
                                        @endif
                                    @else
                                        <div class="text-sm text-gray-400">-</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $impactPercentage = $userBot->amount > 0 ? (($log->type === 'profit' ? $log->amount : -$log->amount) / $userBot->amount) * 100 : 0;
                                    @endphp
                                    <div class="text-sm font-medium text-{{ $log->type === 'profit' ? 'green' : ($log->type === 'loss' ? 'red' : 'white') }}-400">
                                        {{ $log->type === 'profit' ? '+' : ($log->type === 'loss' ? '-' : '') }}{{ number_format(abs($impactPercentage), 2) }}%
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-dark-100">
                {{ $tradingLogs->links('pagination.custom') }}
            </div>
        @else
            <div class="p-12 text-center">
                <i class="fas fa-chart-line text-6xl text-gray-600 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-400 mb-2">No Trading Activity Yet</h3>
                <p class="text-gray-500">Trading activities will appear here as the bot executes trades.</p>
                @if($userBot->status === 'active')
                    <p class="text-sm text-gray-400 mt-2">Next trading session in less than 3 hours.</p>
                @endif
            </div>
        @endif
    </div>

    <!-- Action Buttons -->
    @if($userBot->status === 'active')
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-white">Bot Actions</h3>
                    <p class="text-gray-400 text-sm">Manage your active trading bot</p>
                </div>
                <div class="flex gap-3">
                    <button onclick="refreshData()" class="bg-primary hover:bg-primary-dark text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                        <i class="fas fa-sync-alt mr-2"></i>
                        Refresh Data
                    </button>
                    <form action="{{ route('trading-bots.cancel', $userBot->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure? You will only receive 50% refund.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            <i class="fas fa-stop mr-2"></i>
                            Cancel Bot
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
function refreshData() {
    location.reload();
}

// Auto-refresh every 30 seconds for active bots
@if($userBot->status === 'active')
    setInterval(function() {
        location.reload();
    }, 30000);
@endif
</script>
@endsection
