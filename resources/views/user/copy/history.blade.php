@extends('layouts.dash')

@section('title', 'Copy Trading History')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Copy Trading History</h1>
                <p class="text-gray-400 mt-1">Complete history of your copy trading activities</p>
            </div>
            <a href="{{ route('copy-trading.index') }}" class="bg-primary hover:bg-primary-dark text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Copy Trading
            </a>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total Subscriptions</p>
                    <p class="text-2xl font-bold text-white">{{ $stats['total_subscriptions'] }}</p>
                </div>
                <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-primary text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total Invested</p>
                    <p class="text-2xl font-bold text-white">${{ number_format($stats['total_invested'], 2) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-500/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-blue-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Total P&L</p>
                    <p class="text-2xl font-bold {{ $stats['total_pnl'] >= 0 ? 'text-green-400' : 'text-red-400' }}">
                        {{ $stats['total_pnl'] >= 0 ? '+' : '' }}${{ number_format($stats['total_pnl'], 2) }}
                    </p>
                </div>
                <div class="w-12 h-12 bg-{{ $stats['total_pnl'] >= 0 ? 'green' : 'red' }}-500/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-chart-line text-{{ $stats['total_pnl'] >= 0 ? 'green' : 'red' }}-400 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-400 text-sm">Overall Win Rate</p>
                    <p class="text-2xl font-bold text-primary">{{ number_format($stats['win_rate'], 1) }}%</p>
                    <p class="text-xs text-gray-400">{{ $stats['winning_trades'] }}/{{ $stats['total_trades'] }} trades</p>
                </div>
                <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                    <i class="fas fa-percentage text-primary text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Copy Trading History -->
    <div class="bg-dark-200 rounded-xl border border-dark-100 overflow-hidden">
        <div class="p-6 border-b border-dark-100">
            <h2 class="text-xl font-semibold text-white flex items-center gap-2">
                <i class="fas fa-history text-primary"></i>
                Copy Trading Sessions
            </h2>
        </div>

        @if($subscriptions->count() > 0)
            <div class="space-y-1">
                @foreach($subscriptions as $subscription)
                    <div class="p-6 border-b border-dark-100 last:border-b-0 hover:bg-dark-300/30 transition-colors">
                        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                            <div class="flex items-center gap-4 flex-1">
                                <div class="relative">
                                    <img src="{{ $subscription->expertTrader->avatar ?? asset('dash/default-avatar.png') }}" 
                                         alt="{{ $subscription->expertTrader->name }}" 
                                         class="w-12 h-12 rounded-full">
                                    @if($subscription->expertTrader->isOnline())
                                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-dark-200"></div>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <h3 class="text-lg font-semibold text-white">{{ $subscription->expertTrader->name }}</h3>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs {{ $subscription->getStatusColorClass() }} bg-{{ $subscription->status === 'active' ? 'green' : ($subscription->status === 'paused' ? 'yellow' : ($subscription->status === 'completed' ? 'blue' : 'gray')) }}-500/10">
                                            @if($subscription->status === 'active')
                                                <div class="w-2 h-2 bg-green-400 rounded-full mr-1 animate-pulse"></div>
                                                Active
                                            @elseif($subscription->status === 'paused')
                                                <i class="fas fa-pause mr-1"></i>
                                                Paused
                                            @elseif($subscription->status === 'completed')
                                                <i class="fas fa-check mr-1"></i>
                                                Completed
                                            @else
                                                <i class="fas fa-times mr-1"></i>
                                                {{ ucfirst($subscription->status) }}
                                            @endif
                                        </span>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
                                        <div>
                                            <span class="text-gray-400">Amount:</span>
                                            <div class="text-white font-medium">${{ number_format($subscription->amount) }}</div>
                                        </div>
                                        <div>
                                            <span class="text-gray-400">Total P&L:</span>
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
                                            <span class="text-gray-400">Copy Trades:</span>
                                            <div class="text-white">{{ $subscription->total_trades }}</div>
                                        </div>
                                        <div>
                                            <span class="text-gray-400">Duration:</span>
                                            <div class="text-white">{{ $subscription->started_at->diffInDays($subscription->expires_at) }} days</div>
                                        </div>
                                    </div>

                                    <div class="mt-3 text-sm text-gray-400">
                                        <span>Started: {{ $subscription->started_at->format('M d, Y') }}</span>
                                        <span class="mx-2">•</span>
                                        <span>
                                            @if($subscription->status === 'active')
                                                Expires: {{ $subscription->expires_at->format('M d, Y') }} ({{ $subscription->getDaysRemaining() }} days left)
                                            @elseif($subscription->status === 'completed')
                                                Completed: {{ $subscription->expires_at->format('M d, Y') }}
                                            @else
                                                {{ ucfirst($subscription->status) }}: {{ $subscription->updated_at->format('M d, Y') }}
                                            @endif
                                        </span>
                                    </div>

                                    @if($subscription->copyTrades->count() > 0)
                                        <div class="mt-3 pt-3 border-t border-dark-100">
                                            <div class="text-sm text-gray-400 mb-2">Recent Copy Trades:</div>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach($subscription->copyTrades->take(8) as $trade)
                                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs {{ $trade->isProfit() ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400' }}">
                                                        <i class="fas {{ $trade->isProfit() ? 'fa-arrow-up' : 'fa-arrow-down' }} mr-1"></i>
                                                        {{ $trade->isProfit() ? '+' : '' }}${{ number_format($trade->pnl, 2) }}
                                                    </span>
                                                @endforeach
                                                @if($subscription->copyTrades->count() > 8)
                                                    <span class="text-xs text-gray-400">+{{ $subscription->copyTrades->count() - 8 }} more</span>
                                                @endif
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
                                
                                @if($subscription->status === 'active')
                                    <form action="{{ route('copy-trading.pause', $subscription->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-4 py-2 bg-yellow-500/10 text-yellow-400 border border-yellow-500/20 rounded-lg hover:bg-yellow-500/20 transition-colors">
                                            <i class="fas fa-pause mr-1"></i>
                                            Pause
                                        </button>
                                    </form>
                                @elseif($subscription->status === 'paused')
                                    <form action="{{ route('copy-trading.resume', $subscription->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-4 py-2 bg-green-500/10 text-green-400 border border-green-500/20 rounded-lg hover:bg-green-500/20 transition-colors">
                                            <i class="fas fa-play mr-1"></i>
                                            Resume
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            @if($subscriptions->hasPages())
                <div class="px-6 py-4 border-t border-dark-100">
                    {{ $subscriptions->links() }}
                </div>
            @endif
        @else
            <div class="p-12 text-center">
                <i class="fas fa-history text-6xl text-gray-600 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-400 mb-2">No Copy Trading History</h3>
                <p class="text-gray-500 mb-6">You haven't started any copy trading sessions yet.</p>
                <a href="{{ route('copy-trading.index') }}" class="bg-primary hover:bg-primary-dark text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-200">
                    <i class="fas fa-copy mr-2"></i>
                    Start Copy Trading
                </a>
            </div>
        @endif
    </div>

    <!-- Performance Summary -->
    @if($subscriptions->count() > 0)
        <div class="bg-dark-200 rounded-xl p-6 border border-dark-100">
            <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <i class="fas fa-chart-pie text-primary"></i>
                Performance Summary
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-dark-300 rounded-lg p-4">
                    <div class="text-sm text-gray-400 mb-1">Active Subscriptions</div>
                    <div class="text-2xl font-bold text-green-400">{{ $stats['active_subscriptions'] }}</div>
                    <div class="text-xs text-gray-400">Currently copying</div>
                </div>
                
                <div class="bg-dark-300 rounded-lg p-4">
                    <div class="text-sm text-gray-400 mb-1">Average ROI</div>
                    @php
                        $avgRoi = $subscriptions->where('total_pnl', '!=', 0)->avg('current_roi') ?? 0;
                    @endphp
                    <div class="text-2xl font-bold {{ $avgRoi >= 0 ? 'text-green-400' : 'text-red-400' }}">
                        {{ $avgRoi >= 0 ? '+' : '' }}{{ number_format($avgRoi, 2) }}%
                    </div>
                    <div class="text-xs text-gray-400">Across all subscriptions</div>
                </div>
                
                <div class="bg-dark-300 rounded-lg p-4">
                    <div class="text-sm text-gray-400 mb-1">Best Performance</div>
                    @php
                        $bestPerformance = $subscriptions->max('current_roi') ?? 0;
                    @endphp
                    <div class="text-2xl font-bold text-primary">+{{ number_format($bestPerformance, 2) }}%</div>
                    <div class="text-xs text-gray-400">Top performing subscription</div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection