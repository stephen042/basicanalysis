@extends('layouts.dash')
@section('title', 'Trading History')

@section('content')
<div x-data="tradingHistoryApp()" x-init="init()" class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white md:text-3xl">Trading History</h1>
            <p class="mt-1 text-sm text-gray-400">View your past trades and performance statistics</p>
        </div>
        
        <!-- Filter Controls -->
        <div class="flex items-center gap-3">
            <select x-model="filterPeriod" @change="loadHistory()" 
                    class="px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                <option value="all">All Time</option>
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month">This Month</option>
            </select>
            
            <select x-model="filterStatus" @change="loadHistory()" 
                    class="px-4 py-2 bg-dark-200 border border-dark-100 rounded-lg text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                <option value="all">All Status</option>
                <option value="won">Won</option>
                <option value="lost">Lost</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
    </div>

    <!-- Statistics Overview -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
        <div class="p-6 bg-dark-200 rounded-xl border border-dark-100">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase">Total Trades</p>
                    <p class="mt-2 text-3xl font-bold text-white" x-text="stats.totalTrades">0</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 text-primary-400 bg-primary-500/10 rounded-full">
                    <i class="fas fa-chart-line text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-xs text-gray-400">Since registration</span>
            </div>
        </div>

        <div class="p-6 bg-dark-200 rounded-xl border border-dark-100">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase">Win Rate</p>
                    <p class="mt-2 text-3xl font-bold text-white">
                        <span x-text="stats.winRate">0</span><span class="text-lg">%</span>
                    </p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 text-green-400 bg-green-500/10 rounded-full">
                    <i class="fas fa-trophy text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-xs" :class="stats.winRate >= 50 ? 'text-green-400' : 'text-red-400'">
                    <span x-text="stats.wins">0</span> wins, <span x-text="stats.losses">0</span> losses
                </span>
            </div>
        </div>

        <div class="p-6 bg-dark-200 rounded-xl border border-dark-100">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase">Total Profit/Loss</p>
                    <p class="mt-2 text-3xl font-bold" :class="stats.totalPnL >= 0 ? 'text-green-400' : 'text-red-400'">
                        <span x-text="formatCurrency(stats.totalPnL)">$0.00</span>
                    </p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 text-green-400 bg-green-500/10 rounded-full" 
                     :class="stats.totalPnL >= 0 ? 'text-green-400 bg-green-500/10' : 'text-red-400 bg-red-500/10'">
                    <i class="fas" :class="stats.totalPnL >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down'" class="text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-xs text-gray-400">All time P&L</span>
            </div>
        </div>

        <div class="p-6 bg-dark-200 rounded-xl border border-dark-100">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase">Best Streak</p>
                    <p class="mt-2 text-3xl font-bold text-white" x-text="stats.bestStreak">0</p>
                </div>
                <div class="flex items-center justify-center w-12 h-12 text-yellow-400 bg-yellow-500/10 rounded-full">
                    <i class="fas fa-fire text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-xs text-gray-400">Consecutive wins</span>
            </div>
        </div>
    </div>

    <!-- Trading History Table -->
    <div class="p-6 bg-dark-200 rounded-xl border border-dark-100">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-white">Recent Trades</h3>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-400">
                    Showing <span x-text="trades.length">0</span> trades
                </span>
            </div>
        </div>

        <!-- Mobile Cards View -->
        <div class="block lg:hidden space-y-4">
            <template x-for="trade in trades" :key="trade.id">
                <div class="p-4 bg-dark-100 rounded-lg border border-dark-100">
                    <!-- Trade Header -->
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-medium text-white" x-text="trade.asset"></span>
                            <span class="px-2 py-1 text-xs font-medium rounded-full" 
                                  :class="getTradeStatusClass(trade.status)">
                                <i class="fas" :class="getTradeStatusIcon(trade.status)"></i>
                                <span x-text="trade.status.toUpperCase()"></span>
                            </span>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium text-white" x-text="formatCurrency(trade.amount)"></div>
                            <div class="text-xs text-gray-400" x-text="formatDate(trade.created_at)"></div>
                        </div>
                    </div>
                    
                    <!-- Trade Details -->
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-gray-400">Prediction:</span>
                            <span class="ml-2 font-medium" :class="trade.prediction === 'up' ? 'text-green-400' : 'text-red-400'">
                                <i class="fas" :class="trade.prediction === 'up' ? 'fa-arrow-up' : 'fa-arrow-down'"></i>
                                <span x-text="trade.prediction.toUpperCase()"></span>
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-400">Profit/Loss:</span>
                            <span class="ml-2 font-medium" :class="trade.profit >= 0 ? 'text-green-400' : 'text-red-400'" 
                                  x-text="formatCurrency(trade.profit)"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Desktop Table View -->
        <div class="hidden lg:block overflow-x-auto">
            <table class="min-w-full divide-y divide-dark-100">
                <thead class="bg-dark-100">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-400 uppercase">Asset</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-400 uppercase">Prediction</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-400 uppercase">Amount</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-400 uppercase">Entry Price</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-400 uppercase">Exit Price</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-400 uppercase">Profit/Loss</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-400 uppercase">Status</th>
                        <th scope="col" class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-400 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-dark-200 divide-y divide-dark-100">
                    <template x-for="trade in trades" :key="trade.id">
                        <tr class="hover:bg-dark-100 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 w-8 h-8">
                                        <div class="w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center">
                                            <span class="text-xs font-bold text-white" x-text="trade.asset.substring(0, 2)"></span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-white" x-text="trade.asset"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <i class="fas mr-2" :class="trade.prediction === 'up' ? 'fa-arrow-up text-green-400' : 'fa-arrow-down text-red-400'"></i>
                                    <span class="text-sm font-medium" :class="trade.prediction === 'up' ? 'text-green-400' : 'text-red-400'" 
                                          x-text="trade.prediction.toUpperCase()"></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-white whitespace-nowrap" x-text="formatCurrency(trade.amount)"></td>
                            <td class="px-6 py-4 text-sm text-white whitespace-nowrap" x-text="formatCurrency(trade.entry_price)"></td>
                            <td class="px-6 py-4 text-sm text-white whitespace-nowrap" x-text="trade.exit_price ? formatCurrency(trade.exit_price) : '-'"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium" :class="trade.profit >= 0 ? 'text-green-400' : 'text-red-400'" 
                                      x-text="formatCurrency(trade.profit)"></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full" :class="getTradeStatusClass(trade.status)" 
                                      x-text="trade.status.toUpperCase()"></span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-400 whitespace-nowrap" x-text="formatDate(trade.created_at)"></td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Empty State -->
        <div x-show="trades.length === 0" class="text-center py-12">
            <i class="fas fa-chart-line text-6xl text-gray-600 mb-4"></i>
            <h3 class="text-lg font-medium text-white mb-2">No trades yet</h3>
            <p class="text-gray-400 mb-6">Start trading to see your history here</p>
            <a href="{{ route('user.trading.index') }}" 
               class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                <i class="fas fa-plus"></i>
                <span>Start Trading</span>
            </a>
        </div>

        <!-- Pagination -->
        <div x-show="trades.length > 0" class="flex items-center justify-between mt-6">
            <div class="text-sm text-gray-400">
                Showing <span x-text="pagination.from">1</span> to <span x-text="pagination.to">10</span> of <span x-text="pagination.total">0</span> results
            </div>
            <div class="flex items-center gap-2">
                <button @click="loadPage(pagination.current_page - 1)" 
                        :disabled="pagination.current_page <= 1"
                        class="px-3 py-2 text-sm text-gray-400 bg-dark-100 rounded-lg hover:bg-dark-300 hover:text-white disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    Previous
                </button>
                <span class="px-3 py-2 text-sm text-white bg-primary-600 rounded-lg" x-text="pagination.current_page"></span>
                <button @click="loadPage(pagination.current_page + 1)" 
                        :disabled="pagination.current_page >= pagination.last_page"
                        class="px-3 py-2 text-sm text-gray-400 bg-dark-100 rounded-lg hover:bg-dark-300 hover:text-white disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    Next
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
@parent
<script>
function tradingHistoryApp() {
    return {
        // Data properties
        trades: [],
        stats: {
            totalTrades: 0,
            wins: 0,
            losses: 0,
            winRate: 0,
            totalPnL: 0,
            bestStreak: 0
        },
        pagination: {
            current_page: 1,
            last_page: 1,
            from: 0,
            to: 0,
            total: 0
        },
        filterPeriod: 'all',
        filterStatus: 'all',
        loading: false,

        // Initialize the app
        init() {
            this.loadHistory();
            this.loadStats();
        },

        // Load trading history
        async loadHistory(page = 1) {
            this.loading = true;
            try {
                const response = await fetch(`{{ route("user.trading.history") }}?page=${page}&period=${this.filterPeriod}&status=${this.filterStatus}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.trades = result.trades.data || [];
                    this.pagination = {
                        current_page: result.trades.current_page,
                        last_page: result.trades.last_page,
                        from: result.trades.from,
                        to: result.trades.to,
                        total: result.trades.total
                    };
                }
            } catch (error) {
                console.error('Failed to load trading history:', error);
            }
            this.loading = false;
        },

        // Load statistics
        async loadStats() {
            try {
                const response = await fetch('{{ route("user.trading.stats") }}', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.stats = result.stats;
                }
            } catch (error) {
                console.error('Failed to load stats:', error);
            }
        },

        // Load specific page
        loadPage(page) {
            if (page >= 1 && page <= this.pagination.last_page) {
                this.loadHistory(page);
            }
        },

        // Get trade status CSS class
        getTradeStatusClass(status) {
            switch (status) {
                case 'won':
                    return 'bg-green-500/10 text-green-400';
                case 'lost':
                    return 'bg-red-500/10 text-red-400';
                case 'cancelled':
                    return 'bg-gray-500/10 text-gray-400';
                default:
                    return 'bg-blue-500/10 text-blue-400';
            }
        },

        // Get trade status icon
        getTradeStatusIcon(status) {
            switch (status) {
                case 'won':
                    return 'fa-check-circle';
                case 'lost':
                    return 'fa-times-circle';
                case 'cancelled':
                    return 'fa-ban';
                default:
                    return 'fa-clock';
            }
        },

        // Utility functions
        formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(amount);
        },

        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }
}
</script>
        padding: 15px;
    }
    
    .stat-value {
        font-size: 2rem;
        font-weight: bold;
        display: block;
    }
    
    .stat-label {
        font-size: 0.9rem;
        opacity: 0.8;
    }
    
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .trade-outcome {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .outcome-win { background: #28a745; color: white; }
    .outcome-loss { background: #dc3545; color: white; }
    .outcome-cancelled { background: #6c757d; color: white; }
    .outcome-active { background: #007bff; color: white; }
    
    .trade-direction {
        padding: 3px 8px;
        border-radius: 15px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .direction-up { background: #28a745; color: white; }
    .direction-down { background: #dc3545; color: white; }
    
    .pagination-wrapper {
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }
    
    .trade-details-modal .modal-content {
        border-radius: 15px;
        border: none;
        box-shadow: 0 15px 35px rgba(0,0,0,0.2);
    }
    
    .trade-details-modal .modal-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px 15px 0 0;
    }
    
    .performance-chart {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
</style>
@endsection

@section('content')
<!-- Statistics Overview -->
<div class="stats-overview">
    <div class="row">
        <div class="col-md-3">
            <div class="stat-item">
                <span class="stat-value">{{ $stats['total_trades'] }}</span>
                <span class="stat-label">Total Trades</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-item">
                <span class="stat-value">{{ $stats['win_trades'] }}</span>
                <span class="stat-label">Winning Trades</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-item">
                <span class="stat-value">{{ number_format($stats['win_rate'], 1) }}%</span>
                <span class="stat-label">Win Rate</span>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-item">
                <span class="stat-value {{ $stats['total_pnl'] >= 0 ? '' : 'text-warning' }}">
                    ${{ number_format($stats['total_pnl'], 2) }}
                </span>
                <span class="stat-label">Total P&L</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Filters -->
    <div class="col-lg-3 col-md-12">
        <div class="filter-card">
            <h6><i class="fa fa-filter"></i> Filter Trades</h6>
            <form id="filterForm">
                <div class="form-group">
                    <label>Status</label>
                    <select class="form-control" name="status" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Outcome</label>
                    <select class="form-control" name="outcome" id="outcomeFilter">
                        <option value="">All Outcomes</option>
                        <option value="win">Win</option>
                        <option value="loss">Loss</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Asset</label>
                    <select class="form-control" name="asset" id="assetFilter">
                        <option value="">All Assets</option>
                        @foreach($assets as $asset)
                            <option value="{{ $asset->id }}">{{ $asset->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Date Range</label>
                    <select class="form-control" name="date_range" id="dateRangeFilter">
                        <option value="">All Time</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                        <option value="3months">Last 3 Months</option>
                    </select>
                </div>
                
                <button type="button" class="btn btn-primary btn-block" onclick="applyFilters()">
                    <i class="fa fa-search"></i> Apply Filters
                </button>
                
                <button type="button" class="btn btn-secondary btn-block mt-2" onclick="resetFilters()">
                    <i class="fa fa-refresh"></i> Reset
                </button>
            </form>
        </div>
        
        <!-- Performance Chart -->
        <div class="performance-chart">
            <h6>Performance Trend</h6>
            <canvas id="performanceChart" width="250" height="200"></canvas>
        </div>
    </div>
    
    <!-- Trade History -->
    <div class="col-lg-9 col-md-12">
        <div class="history-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5><i class="fa fa-history"></i> Trade History</h5>
                <div>
                    <button class="btn btn-outline-primary btn-sm" onclick="exportHistory()">
                        <i class="fa fa-download"></i> Export
                    </button>
                    <button class="btn btn-outline-secondary btn-sm ml-2" onclick="refreshHistory()">
                        <i class="fa fa-refresh"></i> Refresh
                    </button>
                </div>
            </div>
            
            <div id="tradesContainer">
                @forelse($trades as $trade)
                <div class="trade-history-item {{ $trade->getOutcomeClass() }}" 
                     onclick="showTradeDetails({{ $trade->id }})">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <div class="trade-direction direction-{{ $trade->prediction_type }}">
                                <i class="fa fa-arrow-{{ $trade->prediction_type == 'up' ? 'up' : 'down' }}"></i>
                                {{ strtoupper($trade->prediction_type) }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <strong>{{ $trade->asset->name }}</strong><br>
                            <small class="text-muted">{{ $trade->asset->symbol }}</small>
                        </div>
                        <div class="col-md-2">
                            <strong>${{ number_format($trade->amount, 2) }}</strong><br>
                            <small class="text-muted">Investment</small>
                        </div>
                        <div class="col-md-2">
                            <strong class="{{ $trade->pnl >= 0 ? 'text-success' : 'text-danger' }}">
                                ${{ number_format($trade->pnl, 2) }}
                            </strong><br>
                            <small class="text-muted">P&L</small>
                        </div>
                        <div class="col-md-2">
                            <span class="trade-outcome outcome-{{ $trade->getOutcome() }}">
                                {{ ucfirst($trade->getOutcome()) }}
                            </span><br>
                            <small class="text-muted">{{ $trade->created_at->format('M j, H:i') }}</small>
                        </div>
                        <div class="col-md-1 text-right">
                            <i class="fa fa-chevron-right text-muted"></i>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5">
                    <i class="fa fa-chart-line fa-3x text-muted mb-3"></i>
                    <h6 class="text-muted">No trades found</h6>
                    <p class="text-muted">Start trading to see your history here</p>
                    <a href="{{ route('user.trading') }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i> Start Trading
                    </a>
                </div>
                @endforelse
            </div>
            
            <!-- Pagination -->
            @if($trades->hasPages())
            <div class="pagination-wrapper">
                {{ $trades->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Trade Details Modal -->
<div class="modal fade trade-details-modal" id="tradeDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-chart-line"></i> Trade Details
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" style="color: white;">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="tradeDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let performanceChart;

$(document).ready(function() {
    initializePerformanceChart();
    
    // Filter change handlers
    $('#statusFilter, #outcomeFilter, #assetFilter, #dateRangeFilter').change(function() {
        applyFilters();
    });
});

function initializePerformanceChart() {
    const ctx = document.getElementById('performanceChart').getContext('2d');
    
    // Get performance data
    $.get('/api/trading/performance-data', function(data) {
        performanceChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Cumulative P&L',
                    data: data.values,
                    borderColor: data.values[data.values.length - 1] >= 0 ? '#28a745' : '#dc3545',
                    backgroundColor: data.values[data.values.length - 1] >= 0 ? 'rgba(40, 167, 69, 0.1)' : 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    });
}

function applyFilters() {
    const formData = $('#filterForm').serialize();
    
    $.get(`/user/trading/history?${formData}`, function(response) {
        // Update the trades container with filtered results
        $('#tradesContainer').html(response.trades_html);
        
        // Update URL without page reload
        const url = new URL(window.location);
        const params = new URLSearchParams(formData);
        params.forEach((value, key) => {
            if (value) {
                url.searchParams.set(key, value);
            } else {
                url.searchParams.delete(key);
            }
        });
        window.history.pushState({}, '', url);
        
        toastr.info('Filters applied');
    }).fail(function() {
        toastr.error('Failed to apply filters');
    });
}

function resetFilters() {
    $('#filterForm')[0].reset();
    applyFilters();
}

function showTradeDetails(tradeId) {
    $.get(`/api/trading/trade-details/${tradeId}`, function(trade) {
        const outcomeClass = trade.pnl >= 0 ? 'text-success' : 'text-danger';
        const directionIcon = trade.prediction_type === 'up' ? 'fa-arrow-up text-success' : 'fa-arrow-down text-danger';
        
        const modalContent = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Trade Information</h6>
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Trade ID:</strong></td>
                            <td>#${trade.id}</td>
                        </tr>
                        <tr>
                            <td><strong>Asset:</strong></td>
                            <td>${trade.asset.name} (${trade.asset.symbol})</td>
                        </tr>
                        <tr>
                            <td><strong>Direction:</strong></td>
                            <td>
                                <i class="fa ${directionIcon}"></i>
                                ${trade.prediction_type.toUpperCase()}
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Investment:</strong></td>
                            <td>$${parseFloat(trade.amount).toFixed(2)}</td>
                        </tr>
                        <tr>
                            <td><strong>Trade Mode:</strong></td>
                            <td>${trade.trade_mode === 'fixed_time' ? 'Fixed Time' : 'Cancel Anytime'}</td>
                        </tr>
                        <tr>
                            <td><strong>Created:</strong></td>
                            <td>${new Date(trade.created_at).toLocaleString()}</td>
                        </tr>
                        ${trade.closed_at ? `
                        <tr>
                            <td><strong>Closed:</strong></td>
                            <td>${new Date(trade.closed_at).toLocaleString()}</td>
                        </tr>
                        ` : ''}
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Price Information</h6>
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Entry Price:</strong></td>
                            <td>$${parseFloat(trade.entry_price).toFixed(4)}</td>
                        </tr>
                        ${trade.close_price ? `
                        <tr>
                            <td><strong>Close Price:</strong></td>
                            <td>$${parseFloat(trade.close_price).toFixed(4)}</td>
                        </tr>
                        ` : ''}
                        <tr>
                            <td><strong>Price Change:</strong></td>
                            <td class="${trade.close_price > trade.entry_price ? 'text-success' : 'text-danger'}">
                                ${trade.close_price ? '$' + (trade.close_price - trade.entry_price).toFixed(4) : 'N/A'}
                            </td>
                        </tr>
                        <tr>
                            <td><strong>P&L:</strong></td>
                            <td class="${outcomeClass}">
                                <strong>$${parseFloat(trade.pnl).toFixed(2)}</strong>
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="trade-outcome outcome-${trade.status}">
                                    ${trade.status.charAt(0).toUpperCase() + trade.status.slice(1)}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            ${trade.manipulated ? `
            <div class="alert alert-warning mt-3">
                <i class="fa fa-exclamation-triangle"></i>
                <strong>Note:</strong> This trade was affected by price manipulation.
            </div>
            ` : ''}
            
            ${trade.notes ? `
            <div class="mt-3">
                <h6>Notes</h6>
                <p class="text-muted">${trade.notes}</p>
            </div>
            ` : ''}
        `;
        
        $('#tradeDetailsContent').html(modalContent);
        $('#tradeDetailsModal').modal('show');
    }).fail(function() {
        toastr.error('Failed to load trade details');
    });
}

function exportHistory() {
    const formData = $('#filterForm').serialize();
    const exportUrl = `/user/trading/export?${formData}`;
    
    // Create a temporary link to trigger download
    const link = document.createElement('a');
    link.href = exportUrl;
    link.download = 'trading_history.csv';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    toastr.success('Export started');
}

function refreshHistory() {
    window.location.reload();
}

// Handle pagination clicks
$(document).on('click', '.pagination a', function(e) {
    e.preventDefault();
    const url = $(this).attr('href');
    
    $.get(url, function(response) {
        $('#tradesContainer').html(response.trades_html);
        
        // Update URL
        window.history.pushState({}, '', url);
    });
});
</script>
@endsection
