@extends('layouts.dash')

@section('title', 'Copy Trading Details - ' . $subscription->expertTrader->name)

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div class="flex items-center gap-4">
                <div class="relative">
                    <img src="{{ $subscription->expertTrader->avatar ?? asset('dash/default-avatar.png') }}" 
                         alt="{{ $subscription->expertTrader->name }}" 
                         class="w-16 h-16 rounded-full">
                    @if($subscription->expertTrader->isOnline())
                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-dark-200"></div>
                    @endif
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">{{ $subscription->expertTrader->name }}</h1>
                    <p class="text-gray-400 mt-1">{{ $subscription->expertTrader->specialization ?? 'Multi-Asset Trading' }}</p>
                    <div class="flex items-center gap-4 mt-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm {{ $subscription->getStatusColorClass() }} bg-{{ $subscription->status === 'active' ? 'green' : ($subscription->status === 'paused' ? 'yellow' : 'gray') }}-500/10">
                            @if($subscription->status === 'active')
                                <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                                Active Copying
                            @elseif($subscription->status === 'paused')
                                <i class="fas fa-pause mr-2"></i>
                                Paused
                            @else
                                <i class="fas fa-stop mr-2"></i>
                                {{ ucfirst($subscription->status) }}
                            @endif
                        </span>
                        <span class="text-sm text-gray-400">
                            Started: {{ $subscription->started_at->format('M d, Y') }}
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('copy-trading.history') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                    <i class="fas fa-history mr-2"></i>
                    History
                </a>
                <a href="{{ route('copy-trading.index') }}" class="bg-primary hover:bg-primary-dark text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Copy Trading
                </a>
            </div>
        </div>
    </div>

    <!-- Performance Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Copy Amount -->
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Copy Amount</p>
                    <p class="text-2xl font-bold text-white">${{ number_format($subscription->amount, 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-500/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-blue-400 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Current P&L -->
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Current P&L</p>
                    <p class="text-2xl font-bold {{ $subscription->total_pnl >= 0 ? 'text-green-400' : 'text-red-400' }}">
                        {{ $subscription->total_pnl >= 0 ? '+' : '' }}${{ number_format($subscription->total_pnl, 2) }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-{{ $subscription->total_pnl >= 0 ? 'green' : 'red' }}-500/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-{{ $subscription->total_pnl >= 0 ? 'arrow-up' : 'arrow-down' }} text-{{ $subscription->total_pnl >= 0 ? 'green' : 'red' }}-400 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- ROI Percentage -->
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">ROI</p>
                    <p class="text-2xl font-bold {{ $subscription->current_roi >= 0 ? 'text-green-400' : 'text-red-400' }}">
                        {{ $subscription->current_roi >= 0 ? '+' : '' }}{{ number_format($subscription->current_roi, 2) }}%
                    </p>
                </div>
                <div class="w-12 h-12 bg-purple-500/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-percentage text-purple-400 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Days Remaining -->
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Days Remaining</p>
                    <p class="text-2xl font-bold text-primary">{{ $subscription->getDaysRemaining() }}</p>
                    <p class="text-xs text-gray-400">Expires {{ $subscription->expires_at->format('M d, Y') }}</p>
                </div>
                <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar text-primary text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress and Statistics -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Progress Bar -->
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <i class="fas fa-chart-line text-primary"></i>
                Copy Trading Progress
            </h3>
            
            <div class="space-y-4">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-400">Duration: {{ $subscription->started_at->diffInDays($subscription->expires_at) }} days</span>
                    <span class="text-white">{{ number_format($subscription->getProgressPercentage(), 1) }}% Complete</span>
                </div>
                
                <div class="w-full bg-dark-300 rounded-full h-3">
                    <div class="bg-gradient-to-r from-primary to-blue-500 h-3 rounded-full transition-all duration-300" 
                         style="width: {{ $subscription->getProgressPercentage() }}%"></div>
                </div>
                
                <div class="flex justify-between text-sm">
                    <span class="text-gray-400">Started: {{ $subscription->started_at->format('M d, H:i') }}</span>
                    <span class="text-white">{{ $subscription->getDaysRemaining() }} days left</span>
                </div>
            </div>
        </div>

        <!-- Copy Trading Statistics -->
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <i class="fas fa-chart-bar text-primary"></i>
                Trading Statistics
            </h3>
            
            <div class="space-y-4">
                <div class="flex justify-between">
                    <span class="text-gray-400">Total Copy Trades</span>
                    <span class="text-white font-semibold">{{ $subscription->total_trades }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Winning Trades</span>
                    <span class="text-green-400 font-semibold">{{ $subscription->winning_trades }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Losing Trades</span>
                    <span class="text-red-400 font-semibold">{{ $subscription->total_trades - $subscription->winning_trades }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Win Rate</span>
                    <span class="text-primary font-semibold">{{ number_format($subscription->win_rate, 1) }}%</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Copy Percentage</span>
                    <span class="text-white font-semibold">{{ $subscription->copy_percentage }}%</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-400">Active Trades</span>
                    <span class="text-blue-400 font-semibold">{{ $subscription->active_trades }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Copy Trades -->
    <div class="bg-dark-200 rounded-xl border border-dark-100 overflow-hidden">
        <div class="p-6 border-b border-dark-100">
            <h2 class="text-xl font-semibold text-white flex items-center gap-2">
                <i class="fas fa-list text-primary"></i>
                Copy Trade History
            </h2>
            <p class="text-gray-400 text-sm mt-1">All trades copied from {{ $subscription->expertTrader->name }}</p>
        </div>

        @if($subscription->copyTrades->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-dark-300">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Asset</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Direction</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Entry Price</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Exit Price</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">P&L</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dark-100">
                        @foreach($copyTrades as $trade)
                            <tr class="hover:bg-dark-300/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="text-sm text-white">{{ $trade->created_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-400">{{ $trade->created_at->format('H:i:s') }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($trade->tradingAsset)
                                        <div class="flex items-center gap-2">
                                            @if($trade->tradingAsset->icon_url)
                                                <img src="{{ $trade->tradingAsset->icon_url }}" alt="{{ $trade->tradingAsset->name }}" class="w-6 h-6 rounded-full">
                                            @else
                                                <div class="w-6 h-6 bg-primary/10 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-coins text-primary text-xs"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="text-sm font-medium text-white">{{ $trade->tradingAsset->symbol }}</div>
                                                <div class="text-xs text-gray-400">{{ $trade->tradingAsset->name }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-sm text-gray-400">N/A</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <i class="fas {{ $trade->getTradeDirectionIcon() }} mr-2"></i>
                                    <span class="text-sm text-white capitalize">{{ $trade->trade_direction }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-white">${{ number_format($trade->amount, 2) }}</div>
                                    @if($trade->quantity)
                                        <div class="text-xs text-gray-400">{{ number_format($trade->quantity, 6) }} {{ $trade->tradingAsset?->symbol }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($trade->entry_price)
                                        <div class="text-sm text-white">${{ number_format($trade->entry_price, 4) }}</div>
                                    @else
                                        <div class="text-sm text-gray-400">-</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($trade->exit_price)
                                        <div class="text-sm text-white">${{ number_format($trade->exit_price, 4) }}</div>
                                    @else
                                        <div class="text-sm text-gray-400">-</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium {{ $trade->getPnlColorClass() }}">
                                        {{ $trade->pnl >= 0 ? '+' : '' }}${{ number_format($trade->pnl, 2) }}
                                    </div>
                                    @if($trade->entry_price)
                                        <div class="text-xs text-gray-400">{{ number_format($trade->getPnlPercentage(), 2) }}%</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs {{ $trade->getStatusColorClass() }}">
                                        {{ ucfirst($trade->status) }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($copyTrades->hasPages())
                <div class="p-6 border-t border-dark-100">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="text-sm text-gray-400">
                            Showing {{ $copyTrades->firstItem() ?? 0 }} to {{ $copyTrades->lastItem() ?? 0 }} of {{ $copyTrades->total() }} trades
                        </div>
                        <div class="flex gap-2">
                            {{ $copyTrades->links() }}
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="p-12 text-center">
                <i class="fas fa-chart-line text-6xl text-gray-600 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-400 mb-2">No Copy Trades Yet</h3>
                <p class="text-gray-500">Copy trades will appear here as the expert trader executes trades.</p>
            </div>
        @endif
    </div>

    <!-- Action Buttons -->
    @if($subscription->status === 'active')
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-white">Copy Trading Actions</h3>
                    <p class="text-gray-400 text-sm">Manage your copy trading subscription</p>
                </div>
                <div class="flex gap-3">
                    <form action="{{ route('copy-trading.pause', $subscription->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            <i class="fas fa-pause mr-2"></i>
                            Pause Copying
                        </button>
                    </form>
                    <form action="{{ route('copy-trading.cancel', $subscription->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure? This will stop all copy trading and process any applicable refund.')">
                        @csrf
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            <i class="fas fa-stop mr-2"></i>
                            Cancel Subscription
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @elseif($subscription->status === 'paused')
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-white">Copy Trading Paused</h3>
                    <p class="text-gray-400 text-sm">Your copy trading is currently paused</p>
                </div>
                <div class="flex gap-3">
                    <form action="{{ route('copy-trading.resume', $subscription->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            <i class="fas fa-play mr-2"></i>
                            Resume Copying
                        </button>
                    </form>
                    <form action="{{ route('copy-trading.cancel', $subscription->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure? This will cancel your copy trading subscription.')">
                        @csrf
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                            <i class="fas fa-times mr-2"></i>
                            Cancel
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection