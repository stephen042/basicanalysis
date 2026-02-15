@extends('layouts.dash')

@section('title', $title)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">{{ $title }}</h1>
                <p class="text-gray-400 mt-1">Complete history of your trading bot activities</p>
            </div>
            <a href="{{ route('trading-bots.index') }}" class="bg-primary hover:bg-primary-dark text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Trading Bots
            </a>
        </div>
    </div>

    <!-- Trading History -->
    <div class="bg-dark-200 rounded-xl border border-dark-100 overflow-hidden">
        <div class="p-6 border-b border-dark-100">
            <h2 class="text-xl font-semibold text-white flex items-center gap-2">
                <i class="fas fa-chart-bar text-primary"></i>
                Trading Sessions History
            </h2>
        </div>

        @if($userTradingBots->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-dark-300">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Bot</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Investment</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Duration</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Profit/Loss</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Started</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dark-100">
                        @foreach($userTradingBots as $userBot)
                            <tr class="hover:bg-dark-300/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas fa-robot text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-white">{{ $userBot->tradingBot->name }}</div>
                                            <div class="text-sm text-gray-400">{{ $userBot->tradingBot->profit_rate }}% expected</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-white">{{ Auth::user()->currency }}{{ number_format($userBot->amount) }}</div>
                                    <div class="text-sm text-gray-400">Principal</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-white">{{ $userBot->tradingBot->duration }} hours</div>
                                    @if($userBot->status === 'active')
                                        <div class="text-xs text-gray-400">
                                            Expires: {{ \Carbon\Carbon::parse($userBot->expires_at)->format('M d, H:i') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($userBot->status === 'active')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-500/10 text-green-400">
                                            <div class="w-2 h-2 bg-green-400 rounded-full mr-1 animate-pulse"></div>
                                            Active
                                        </span>
                                    @elseif($userBot->status === 'completed')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-500/10 text-blue-400">
                                            <i class="fas fa-check mr-1"></i>
                                            Completed
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-red-500/10 text-red-400">
                                            <i class="fas fa-times mr-1"></i>
                                            Cancelled
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $totalProfit = $userBot->tradingLogs->where('type', 'profit')->sum('amount');
                                        $totalLoss = $userBot->tradingLogs->where('type', 'loss')->sum('amount');
                                        $netProfit = $totalProfit - $totalLoss;
                                    @endphp

                                    @if($netProfit > 0)
                                        <div class="text-sm font-medium text-green-400">+{{ Auth::user()->currency }}{{ number_format($netProfit, 2) }}</div>
                                        <div class="text-xs text-gray-400">Profit</div>
                                    @elseif($netProfit < 0)
                                        <div class="text-sm font-medium text-red-400">-{{ Auth::user()->currency }}{{ number_format(abs($netProfit), 2) }}</div>
                                        <div class="text-xs text-gray-400">Loss</div>
                                    @else
                                        <div class="text-sm text-gray-400">{{ Auth::user()->currency }}0.00</div>
                                        <div class="text-xs text-gray-400">Neutral</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-white">{{ $userBot->created_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-400">{{ $userBot->created_at->format('H:i') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('trading-bots.details', $userBot->id) }}" class="text-primary hover:text-primary-dark text-sm">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Details
                                        </a>

                                        @if($userBot->tradingLogs->count() > 0)
                                            <button onclick="toggleLogs({{ $userBot->id }})" class="text-blue-400 hover:text-blue-300 text-sm">
                                                <i class="fas fa-eye mr-1"></i>
                                                Logs
                                            </button>
                                        @endif

                                        @if($userBot->status === 'active')
                                            <form action="{{ route('trading-bots.cancel', $userBot->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure? You will only receive 50% refund.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-400 hover:text-red-300 text-sm">
                                                    <i class="fas fa-stop mr-1"></i>
                                                    Cancel
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <!-- Trading Logs Row (Hidden by default) -->
                            @if($userBot->tradingLogs->count() > 0)
                                <tr id="logs-{{ $userBot->id }}" class="hidden bg-dark-400/50">
                                    <td colspan="7" class="px-6 py-4">
                                        <div class="bg-dark-300 rounded-lg p-4">
                                            <h4 class="text-sm font-medium text-white mb-3">Trading Activity Logs</h4>
                                            <div class="space-y-2 max-h-40 overflow-y-auto">
                                                @foreach($userBot->tradingLogs->sortByDesc('created_at') as $log)
                                                    <div class="flex items-center justify-between py-2 px-3 bg-dark-200 rounded">
                                                        <div class="flex items-center gap-3">
                                                            <i class="fas {{ $log->type === 'profit' ? 'fa-arrow-up text-green-400' : 'fa-arrow-down text-red-400' }}"></i>
                                                            <span class="text-sm text-white">
                                                                {{ ucfirst($log->type) }}: {{ Auth::user()->currency }}{{ number_format($log->amount, 2) }}
                                                            </span>
                                                        </div>
                                                        <span class="text-xs text-gray-400">{{ $log->created_at->format('M d, H:i') }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($userTradingBots->hasPages())
                <div class="px-6 py-4 border-t border-dark-100">
                    {{ $userTradingBots->links() }}
                </div>
            @endif
        @else
            <div class="p-12 text-center">
                <i class="fas fa-chart-bar text-6xl text-gray-600 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-400 mb-2">No Trading History</h3>
                <p class="text-gray-500 mb-6">You haven't started any trading sessions yet.</p>
                <a href="{{ route('trading-bots.index') }}" class="bg-primary hover:bg-primary-dark text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                    <i class="fas fa-robot mr-2"></i>
                    Start Trading Now
                </a>
            </div>
        @endif
    </div>
</div>

<script>
function toggleLogs(userBotId) {
    const logsRow = document.getElementById('logs-' + userBotId);
    logsRow.classList.toggle('hidden');
}
</script>
@endsection
