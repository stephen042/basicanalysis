@extends('layouts.app')

@section('styles')
<style>
    .trading-card {
        background: linear-gradient(145deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        color: white;
        border: none;
        box-shadow: 0 8px 32px rgba(102, 126, 234, 0.25);
    }
    
    .stats-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
    }
    
    .manipulation-controls {
        background: linear-gradient(135deg, #ff6b6b, #ee5a24);
        border-radius: 12px;
        color: white;
        padding: 20px;
    }
    
    .price-display {
        font-size: 2.5rem;
        font-weight: bold;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }
    
    .trade-status {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .trade-active { background: #28a745; color: white; }
    .trade-closed { background: #6c757d; color: white; }
    .trade-cancelled { background: #dc3545; color: white; }
</style>
@endsection

@section('content')
<div class="page-header">
    <div class="row">
        <div class="col-md-6 col-sm-12">
            <div class="title">
                <h4>Trading Control Center</h4>
            </div>
            <nav aria-label="breadcrumb" role="navigation">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Trading Dashboard</li>
                </ol>
            </nav>
        </div>
        <div class="col-md-6 col-sm-12 text-right">
            <button class="btn btn-danger" id="emergencyStop">
                <i class="fa fa-stop-circle"></i> Emergency Stop
            </button>
        </div>
    </div>
</div>

<!-- Real-time Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-30">
        <div class="stats-card p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1" id="activeTrades">{{ $stats['active_trades'] ?? 0 }}</h3>
                    <p class="text-muted mb-0">Active Trades</p>
                </div>
                <div class="text-primary">
                    <i class="fa fa-chart-line fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-30">
        <div class="stats-card p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1" id="totalVolume">${{ number_format($stats['total_volume'] ?? 0, 2) }}</h3>
                    <p class="text-muted mb-0">Total Volume</p>
                </div>
                <div class="text-success">
                    <i class="fa fa-dollar-sign fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-30">
        <div class="stats-card p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1" id="winRate">{{ number_format($stats['win_rate'] ?? 0, 1) }}%</h3>
                    <p class="text-muted mb-0">Win Rate</p>
                </div>
                <div class="text-warning">
                    <i class="fa fa-trophy fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12 mb-30">
        <div class="stats-card p-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="mb-1" id="onlineUsers">{{ $stats['online_users'] ?? 0 }}</h3>
                    <p class="text-muted mb-0">Online Users</p>
                </div>
                <div class="text-info">
                    <i class="fa fa-users fa-2x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Price Manipulation Panel -->
<div class="row mb-4">
    <div class="col-lg-6 col-md-12 mb-30">
        <div class="manipulation-controls">
            <h5 class="mb-3"><i class="fa fa-sliders-h"></i> Price Manipulation</h5>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Asset</label>
                        <select class="form-control" id="manipulateAsset">
                            @foreach($assets as $asset)
                                <option value="{{ $asset->id }}">{{ $asset->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Current Price</label>
                        <div class="price-display" id="currentPrice">$0.00</div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <button class="btn btn-light btn-block" onclick="manipulatePrice('up')">
                        <i class="fa fa-arrow-up"></i> Push Up
                    </button>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-light btn-block" onclick="manipulatePrice('down')">
                        <i class="fa fa-arrow-down"></i> Push Down
                    </button>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-warning btn-block" onclick="manipulatePrice('volatile')">
                        <i class="fa fa-bolt"></i> Make Volatile
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 col-md-12 mb-30">
        <div class="card">
            <div class="card-header">
                <h5>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <button class="btn btn-success btn-block" onclick="forceWinAll()">
                            <i class="fa fa-check-circle"></i> Force Win All
                        </button>
                    </div>
                    <div class="col-md-6 mb-3">
                        <button class="btn btn-danger btn-block" onclick="forceLoseAll()">
                            <i class="fa fa-times-circle"></i> Force Lose All
                        </button>
                    </div>
                    <div class="col-md-6 mb-3">
                        <button class="btn btn-info btn-block" onclick="freezePrices()">
                            <i class="fa fa-pause"></i> Freeze Prices
                        </button>
                    </div>
                    <div class="col-md-6 mb-3">
                        <button class="btn btn-primary btn-block" onclick="resetManipulation()">
                            <i class="fa fa-undo"></i> Reset All
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Active Trades Table -->
<div class="row">
    <div class="col-12 mb-30">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Active Trades</h5>
                <button class="btn btn-sm btn-primary" onclick="refreshTrades()">
                    <i class="fa fa-refresh"></i> Refresh
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped" id="tradesTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User</th>
                                <th>Asset</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Entry Price</th>
                                <th>Current Price</th>
                                <th>P&L</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tradesTableBody">
                            @foreach($activeTrades as $trade)
                            <tr id="trade-{{ $trade->id }}">
                                <td>{{ $trade->id }}</td>
                                <td>{{ $trade->user->name }}</td>
                                <td>{{ $trade->asset->name }}</td>
                                <td>
                                    <span class="badge badge-{{ $trade->prediction_type == 'up' ? 'success' : 'danger' }}">
                                        {{ strtoupper($trade->prediction_type) }}
                                    </span>
                                </td>
                                <td>${{ number_format($trade->amount, 2) }}</td>
                                <td>${{ number_format($trade->entry_price, 4) }}</td>
                                <td class="current-price" data-asset="{{ $trade->asset->id }}">
                                    ${{ number_format($trade->current_price ?? $trade->entry_price, 4) }}
                                </td>
                                <td class="pnl {{ ($trade->current_pnl ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                    ${{ number_format($trade->current_pnl ?? 0, 2) }}
                                </td>
                                <td>
                                    <span class="trade-status trade-{{ $trade->status }}">
                                        {{ ucfirst($trade->status) }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="overrideTrade({{ $trade->id }}, 'win')">
                                        Force Win
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="overrideTrade({{ $trade->id }}, 'lose')">
                                        Force Lose
                                    </button>
                                    <button class="btn btn-sm btn-secondary" onclick="closeTrade({{ $trade->id }})">
                                        Close
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let priceUpdateInterval;

$(document).ready(function() {
    startPriceUpdates();
    startStatsUpdates();
});

function startPriceUpdates() {
    priceUpdateInterval = setInterval(function() {
        updatePrices();
        updateTrades();
    }, 2000);
}

function updatePrices() {
    $.get('/api/trading/prices', function(data) {
        // Update current price display
        const assetId = $('#manipulateAsset').val();
        if (data[assetId]) {
            $('#currentPrice').text('$' + parseFloat(data[assetId]).toFixed(4));
        }
        
        // Update all current prices in trades table
        $('.current-price').each(function() {
            const assetId = $(this).data('asset');
            if (data[assetId]) {
                $(this).text('$' + parseFloat(data[assetId]).toFixed(4));
            }
        });
    });
}

function updateTrades() {
    $.get('/api/trading/active-trades', function(trades) {
        let tbody = '';
        trades.forEach(function(trade) {
            const pnlClass = trade.current_pnl >= 0 ? 'text-success' : 'text-danger';
            const typeClass = trade.prediction_type == 'up' ? 'success' : 'danger';
            
            tbody += `
                <tr id="trade-${trade.id}">
                    <td>${trade.id}</td>
                    <td>${trade.user.name}</td>
                    <td>${trade.asset.name}</td>
                    <td><span class="badge badge-${typeClass}">${trade.prediction_type.toUpperCase()}</span></td>
                    <td>$${parseFloat(trade.amount).toFixed(2)}</td>
                    <td>$${parseFloat(trade.entry_price).toFixed(4)}</td>
                    <td class="current-price" data-asset="${trade.asset.id}">$${parseFloat(trade.current_price || trade.entry_price).toFixed(4)}</td>
                    <td class="pnl ${pnlClass}">$${parseFloat(trade.current_pnl || 0).toFixed(2)}</td>
                    <td><span class="trade-status trade-${trade.status}">${trade.status.charAt(0).toUpperCase() + trade.status.slice(1)}</span></td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="overrideTrade(${trade.id}, 'win')">Force Win</button>
                        <button class="btn btn-sm btn-danger" onclick="overrideTrade(${trade.id}, 'lose')">Force Lose</button>
                        <button class="btn btn-sm btn-secondary" onclick="closeTrade(${trade.id})">Close</button>
                    </td>
                </tr>
            `;
        });
        $('#tradesTableBody').html(tbody);
    });
}

function startStatsUpdates() {
    setInterval(function() {
        $.get('/api/trading/stats', function(stats) {
            $('#activeTrades').text(stats.active_trades);
            $('#totalVolume').text('$' + parseFloat(stats.total_volume).toLocaleString());
            $('#winRate').text(parseFloat(stats.win_rate).toFixed(1) + '%');
            $('#onlineUsers').text(stats.online_users);
        });
    }, 5000);
}

function manipulatePrice(direction) {
    const assetId = $('#manipulateAsset').val();
    
    $.post('/api/trading/manipulate-price', {
        asset_id: assetId,
        direction: direction,
        _token: $('meta[name="csrf-token"]').attr('content')
    }).done(function(response) {
        toastr.success('Price manipulation applied successfully');
    }).fail(function() {
        toastr.error('Failed to manipulate price');
    });
}

function overrideTrade(tradeId, outcome) {
    $.post(`/api/trading/override-trade/${tradeId}`, {
        outcome: outcome,
        _token: $('meta[name="csrf-token"]').attr('content')
    }).done(function(response) {
        toastr.success(`Trade ${outcome} override applied`);
        updateTrades();
    }).fail(function() {
        toastr.error('Failed to override trade');
    });
}

function closeTrade(tradeId) {
    if (confirm('Are you sure you want to close this trade?')) {
        $.post(`/api/trading/close-trade/${tradeId}`, {
            _token: $('meta[name="csrf-token"]').attr('content')
        }).done(function(response) {
            toastr.success('Trade closed successfully');
            updateTrades();
        }).fail(function() {
            toastr.error('Failed to close trade');
        });
    }
}

function forceWinAll() {
    if (confirm('Force WIN all active trades?')) {
        $.post('/api/trading/force-all', {
            outcome: 'win',
            _token: $('meta[name="csrf-token"]').attr('content')
        }).done(function(response) {
            toastr.success('All trades set to WIN');
            updateTrades();
        });
    }
}

function forceLoseAll() {
    if (confirm('Force LOSE all active trades?')) {
        $.post('/api/trading/force-all', {
            outcome: 'lose',
            _token: $('meta[name="csrf-token"]').attr('content')
        }).done(function(response) {
            toastr.success('All trades set to LOSE');
            updateTrades();
        });
    }
}

function freezePrices() {
    $.post('/api/trading/freeze-prices', {
        _token: $('meta[name="csrf-token"]').attr('content')
    }).done(function(response) {
        toastr.info('All prices frozen');
    });
}

function resetManipulation() {
    $.post('/api/trading/reset-manipulation', {
        _token: $('meta[name="csrf-token"]').attr('content')
    }).done(function(response) {
        toastr.info('All manipulations reset');
    });
}

$('#emergencyStop').click(function() {
    if (confirm('EMERGENCY STOP - This will close all active trades immediately!')) {
        $.post('/api/trading/emergency-stop', {
            _token: $('meta[name="csrf-token"]').attr('content')
        }).done(function(response) {
            toastr.error('EMERGENCY STOP ACTIVATED');
            updateTrades();
            clearInterval(priceUpdateInterval);
        });
    }
});

function refreshTrades() {
    updateTrades();
    toastr.info('Trades refreshed');
}

// Change asset selection
$('#manipulateAsset').change(function() {
    updatePrices();
});
</script>
@endsection
