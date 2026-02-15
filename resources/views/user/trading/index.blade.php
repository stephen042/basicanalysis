@extends('layouts.dash')

@section('styles')
<style>
    .trading-interface {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        color: white;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 15px 35px rgba(102, 126, 234, 0.3);
    }
    
    .price-chart {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .prediction-buttons {
        display: flex;
        gap: 15px;
        margin-top: 20px;
    }
    
    .prediction-btn {
        flex: 1;
        padding: 15px;
        border: none;
        border-radius: 12px;
        font-size: 1.2rem;
        font-weight: bold;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .prediction-btn.up {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
    }
    
    .prediction-btn.down {
        background: linear-gradient(135deg, #dc3545, #fd7e14);
        color: white;
    }
    
    .prediction-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    }
    
    .trade-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    
    .trade-card:hover {
        transform: translateY(-2px);
    }
    
    .asset-selector {
        background: rgba(255,255,255,0.2);
        border: 2px solid rgba(255,255,255,0.3);
        border-radius: 10px;
        color: white;
        padding: 10px 15px;
        margin-bottom: 20px;
    }
    
    .asset-selector option {
        color: #333;
    }
    
    .price-display {
        font-size: 3rem;
        font-weight: bold;
        text-align: center;
        margin: 20px 0;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }
    
    .price-change {
        text-align: center;
        font-size: 1.2rem;
        margin-bottom: 20px;
    }
    
    .price-up { color: #28a745; }
    .price-down { color: #dc3545; }
    
    .trading-form {
        background: rgba(255,255,255,0.1);
        border-radius: 15px;
        padding: 25px;
        margin-top: 20px;
    }
    
    .form-control-white {
        background: rgba(255,255,255,0.2);
        border: 2px solid rgba(255,255,255,0.3);
        border-radius: 8px;
        color: white;
        padding: 12px 15px;
    }
    
    .form-control-white::placeholder {
        color: rgba(255,255,255,0.7);
    }
    
    .form-control-white:focus {
        background: rgba(255,255,255,0.3);
        border-color: rgba(255,255,255,0.5);
        color: white;
        box-shadow: none;
    }
    
    .balance-display {
        background: rgba(255,255,255,0.2);
        border-radius: 10px;
        padding: 15px;
        text-align: center;
        margin-bottom: 20px;
    }
    
    .balance-amount {
        font-size: 1.5rem;
        font-weight: bold;
    }
    
    .active-trades-panel {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .trade-item {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
        border-left: 4px solid #007bff;
    }
    
    .trade-item.winning {
        border-left-color: #28a745;
        background: #d4edda;
    }
    
    .trade-item.losing {
        border-left-color: #dc3545;
        background: #f8d7da;
    }
    
    .countdown-timer {
        font-size: 1.5rem;
        font-weight: bold;
        text-align: center;
        margin: 15px 0;
        padding: 10px;
        background: rgba(255,255,255,0.2);
        border-radius: 8px;
    }
    
    .demo-mode-banner {
        background: linear-gradient(135deg, #ffc107, #fd7e14);
        color: white;
        padding: 10px;
        text-align: center;
        border-radius: 10px;
        margin-bottom: 20px;
        font-weight: bold;
    }
    
    .quick-amounts {
        display: flex;
        gap: 10px;
        margin: 15px 0;
        flex-wrap: wrap;
    }
    
    .quick-amount {
        background: rgba(255,255,255,0.2);
        border: 2px solid rgba(255,255,255,0.3);
        color: white;
        padding: 8px 15px;
        border-radius: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }
    
    .quick-amount:hover {
        background: rgba(255,255,255,0.3);
        border-color: rgba(255,255,255,0.5);
    }
</style>
@endsection

@section('content')
@if(session('isDemo', false))
<div class="demo-mode-banner">
    <i class="fa fa-info-circle"></i> DEMO MODE - You're trading with virtual money
</div>
@endif

<div class="row">
    <!-- Trading Interface -->
    <div class="col-lg-8 col-md-12">
        <div class="trading-interface">
            <div class="row align-items-center mb-4">
                <div class="col-md-6">
                    <h3><i class="fa fa-chart-line"></i> Trading Panel</h3>
                </div>
                <div class="col-md-6 text-right">
                    <div class="balance-display">
                        <div class="balance-label">Available Balance</div>
                        <div class="balance-amount" id="userBalance">
                            ${{ number_format(auth()->user()->account_bal ?? 0, 2) }}
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Asset Selection -->
            <div class="row">
                <div class="col-md-6">
                    <label for="assetSelect">Select Asset</label>
                    <select class="form-control asset-selector" id="assetSelect">
                        @foreach($assets as $asset)
                            <option value="{{ $asset->id }}" data-price="{{ $asset->current_price }}">
                                {{ $asset->name }} ({{ $asset->symbol }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="tradeMode">Trade Mode</label>
                    <select class="form-control asset-selector" id="tradeMode">
                        <option value="fixed_time">Fixed Time (90 seconds)</option>
                        <option value="cancel_anytime">Cancel Anytime</option>
                    </select>
                </div>
            </div>
            
            <!-- Price Display -->
            <div class="price-chart">
                <div class="text-center">
                    <h5 id="assetName">BTC/USD</h5>
                    <div class="price-display" id="currentPrice">$0.00</div>
                    <div class="price-change" id="priceChange">
                        <span id="changeAmount">+$0.00</span>
                        <span id="changePercent">(+0.00%)</span>
                    </div>
                    <div class="countdown-timer" id="countdownTimer" style="display: none;">
                        Time Left: <span id="timeLeft">90</span>s
                    </div>
                </div>
                
                <!-- Simple Chart Canvas -->
                <canvas id="priceChart" width="600" height="200"></canvas>
            </div>
            
            <!-- Trading Form -->
            <div class="trading-form">
                <form id="tradingForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <label>Investment Amount</label>
                            <input type="number" class="form-control form-control-white" id="investmentAmount" 
                                   placeholder="Enter amount" min="1" step="0.01" required>
                            
                            <div class="quick-amounts">
                                <div class="quick-amount" onclick="setAmount(10)">$10</div>
                                <div class="quick-amount" onclick="setAmount(25)">$25</div>
                                <div class="quick-amount" onclick="setAmount(50)">$50</div>
                                <div class="quick-amount" onclick="setAmount(100)">$100</div>
                                <div class="quick-amount" onclick="setAmount(250)">$250</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label>Potential Profit</label>
                            <div class="form-control form-control-white" id="potentialProfit">$0.00</div>
                            <small style="color: rgba(255,255,255,0.8);">
                                Payout: <span id="payoutRate">85%</span>
                            </small>
                        </div>
                    </div>
                    
                    <div class="prediction-buttons">
                        <button type="button" class="prediction-btn up" onclick="placeTrade('up')">
                            <i class="fa fa-arrow-up"></i> HIGHER
                        </button>
                        <button type="button" class="prediction-btn down" onclick="placeTrade('down')">
                            <i class="fa fa-arrow-down"></i> LOWER
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Active Trades Sidebar -->
    <div class="col-lg-4 col-md-12">
        <div class="active-trades-panel">
            <h5><i class="fa fa-list"></i> Active Trades</h5>
            <div id="activeTradesList">
                <div class="text-center text-muted" id="noTrades">
                    <i class="fa fa-chart-line fa-3x mb-3"></i>
                    <p>No active trades</p>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="trade-card mt-4">
            <h6>Today's Stats</h6>
            <div class="row text-center">
                <div class="col-4">
                    <div class="h4 text-success" id="todayWins">0</div>
                    <small>Wins</small>
                </div>
                <div class="col-4">
                    <div class="h4 text-danger" id="todayLosses">0</div>
                    <small>Losses</small>
                </div>
                <div class="col-4">
                    <div class="h4 text-info" id="todayProfit">$0</div>
                    <small>Profit</small>
                </div>
            </div>
        </div>
        
        <!-- Demo Mode Toggle -->
        <div class="trade-card mt-3">
            <h6>Trading Mode</h6>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="demoModeToggle" 
                       {{ session('isDemo', false) ? 'checked' : '' }}>
                <label class="form-check-label" for="demoModeToggle">
                    Demo Mode
                </label>
            </div>
            <small class="text-muted">
                Practice with virtual money
            </small>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let priceUpdateInterval;
let chart;
let priceHistory = [];
let activeTradeCountdown;

$(document).ready(function() {
    initializeChart();
    loadActiveTrades();
    startPriceUpdates();
    loadTodayStats();
    
    // Update potential profit when amount changes
    $('#investmentAmount').on('input', updatePotentialProfit);
    
    // Asset selection change
    $('#assetSelect').change(function() {
        updateAssetInfo();
        clearPriceHistory();
    });
    
    // Demo mode toggle
    $('#demoModeToggle').change(function() {
        toggleDemoMode($(this).is(':checked'));
    });
    
    updateAssetInfo();
});

function initializeChart() {
    const ctx = document.getElementById('priceChart').getContext('2d');
    chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Price',
                data: [],
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: false
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

function startPriceUpdates() {
    updatePrices();
    priceUpdateInterval = setInterval(updatePrices, 2000);
}

function updatePrices() {
    const assetId = $('#assetSelect').val();
    
    $.get('/api/trading/prices', function(prices) {
        if (prices[assetId]) {
            const newPrice = parseFloat(prices[assetId]);
            const oldPrice = parseFloat($('#currentPrice').text().replace('$', '').replace(',', ''));
            
            updatePriceDisplay(newPrice, oldPrice);
            updateChart(newPrice);
        }
    });
}

function updatePriceDisplay(newPrice, oldPrice) {
    $('#currentPrice').text('$' + newPrice.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 4
    }));
    
    const change = newPrice - oldPrice;
    const changePercent = oldPrice > 0 ? (change / oldPrice) * 100 : 0;
    
    $('#changeAmount').text((change >= 0 ? '+' : '') + '$' + Math.abs(change).toFixed(4));
    $('#changePercent').text('(' + (changePercent >= 0 ? '+' : '') + changePercent.toFixed(2) + '%)');
    
    const priceChangeEl = $('#priceChange');
    priceChangeEl.removeClass('price-up price-down');
    priceChangeEl.addClass(change >= 0 ? 'price-up' : 'price-down');
}

function updateChart(price) {
    const now = new Date();
    const timeLabel = now.getHours() + ':' + String(now.getMinutes()).padStart(2, '0') + ':' + String(now.getSeconds()).padStart(2, '0');
    
    priceHistory.push(price);
    
    // Keep only last 50 points
    if (priceHistory.length > 50) {
        priceHistory.shift();
        chart.data.labels.shift();
    }
    
    chart.data.labels.push(timeLabel);
    chart.data.datasets[0].data = [...priceHistory];
    chart.update('none');
}

function updateAssetInfo() {
    const selected = $('#assetSelect option:selected');
    const assetName = selected.text();
    const initialPrice = parseFloat(selected.data('price'));
    
    $('#assetName').text(assetName);
    $('#currentPrice').text('$' + initialPrice.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 4
    }));
    
    // Reset price change display
    $('#changeAmount').text('+$0.00');
    $('#changePercent').text('(+0.00%)');
    $('#priceChange').removeClass('price-up price-down');
}

function clearPriceHistory() {
    priceHistory = [];
    chart.data.labels = [];
    chart.data.datasets[0].data = [];
    chart.update();
}

function setAmount(amount) {
    $('#investmentAmount').val(amount);
    updatePotentialProfit();
}

function updatePotentialProfit() {
    const amount = parseFloat($('#investmentAmount').val()) || 0;
    const payoutRate = 0.85; // 85% payout rate
    const profit = amount * payoutRate;
    
    $('#potentialProfit').text('$' + profit.toFixed(2));
}

function placeTrade(direction) {
    const amount = parseFloat($('#investmentAmount').val());
    const assetId = $('#assetSelect').val();
    const tradeMode = $('#tradeMode').val();
    
    if (!amount || amount <= 0) {
        toastr.error('Please enter a valid investment amount');
        return;
    }
    
    const currentBalance = parseFloat($('#userBalance').text().replace('$', '').replace(',', ''));
    if (amount > currentBalance) {
        toastr.error('Insufficient balance');
        return;
    }
    
    $.post('/api/trading/place-trade', {
        asset_id: assetId,
        amount: amount,
        prediction_type: direction,
        trade_mode: tradeMode,
        _token: $('meta[name="csrf-token"]').attr('content')
    }).done(function(response) {
        toastr.success('Trade placed successfully!');
        $('#investmentAmount').val('');
        updatePotentialProfit();
        loadActiveTrades();
        updateBalance();
        
        if (tradeMode === 'fixed_time') {
            startTradeCountdown(response.trade_id, 90);
        }
    }).fail(function(xhr) {
        const error = xhr.responseJSON?.message || 'Failed to place trade';
        toastr.error(error);
    });
}

function startTradeCountdown(tradeId, seconds) {
    let timeLeft = seconds;
    $('#countdownTimer').show();
    
    activeTradeCountdown = setInterval(function() {
        $('#timeLeft').text(timeLeft);
        timeLeft--;
        
        if (timeLeft < 0) {
            clearInterval(activeTradeCountdown);
            $('#countdownTimer').hide();
            loadActiveTrades();
            toastr.info('Trade completed!');
        }
    }, 1000);
}

function loadActiveTrades() {
    $.get('/api/trading/user-trades', function(trades) {
        let tradesHtml = '';
        
        if (trades.length === 0) {
            $('#noTrades').show();
        } else {
            $('#noTrades').hide();
            
            trades.forEach(function(trade) {
                const statusClass = trade.current_pnl >= 0 ? 'winning' : 'losing';
                const directionIcon = trade.prediction_type === 'up' ? 'fa-arrow-up text-success' : 'fa-arrow-down text-danger';
                const pnlClass = trade.current_pnl >= 0 ? 'text-success' : 'text-danger';
                
                tradesHtml += `
                    <div class="trade-item ${statusClass}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">
                                    <i class="fa ${directionIcon}"></i> ${trade.asset.name}
                                </h6>
                                <small>$${parseFloat(trade.amount).toFixed(2)} - ${trade.prediction_type.toUpperCase()}</small>
                            </div>
                            <div class="text-right">
                                <div class="${pnlClass}">$${parseFloat(trade.current_pnl || 0).toFixed(2)}</div>
                                ${trade.trade_mode === 'cancel_anytime' && trade.status === 'active' ? 
                                    `<button class="btn btn-sm btn-warning mt-1" onclick="cancelTrade(${trade.id})">Cancel</button>` : 
                                    ''}
                            </div>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                Entry: $${parseFloat(trade.entry_price).toFixed(4)} | 
                                Current: $${parseFloat(trade.current_price || trade.entry_price).toFixed(4)}
                            </small>
                        </div>
                    </div>
                `;
            });
        }
        
        $('#activeTradesList').html(tradesHtml);
    });
}

function cancelTrade(tradeId) {
    if (confirm('Are you sure you want to cancel this trade?')) {
        $.post(`/api/trading/cancel-trade/${tradeId}`, {
            _token: $('meta[name="csrf-token"]').attr('content')
        }).done(function(response) {
            toastr.success('Trade cancelled successfully');
            loadActiveTrades();
            updateBalance();
        }).fail(function() {
            toastr.error('Failed to cancel trade');
        });
    }
}

function updateBalance() {
    $.get('/api/trading/balance', function(data) {
        $('#userBalance').text('$' + parseFloat(data.balance).toLocaleString());
    });
}

function loadTodayStats() {
    $.get('/api/trading/today-stats', function(stats) {
        $('#todayWins').text(stats.wins);
        $('#todayLosses').text(stats.losses);
        $('#todayProfit').text('$' + parseFloat(stats.profit).toFixed(2));
    });
}

function toggleDemoMode(isDemo) {
    $.post('/api/trading/toggle-demo', {
        demo_mode: isDemo,
        _token: $('meta[name="csrf-token"]').attr('content')
    }).done(function(response) {
        if (isDemo) {
            toastr.info('Demo mode activated - You are now trading with virtual money');
            $('.demo-mode-banner').show();
        } else {
            toastr.info('Live trading mode activated');
            $('.demo-mode-banner').hide();
        }
        updateBalance();
    }).fail(function() {
        toastr.error('Failed to toggle demo mode');
        $('#demoModeToggle').prop('checked', !isDemo);
    });
}

// Auto-refresh active trades every 5 seconds
setInterval(function() {
    loadActiveTrades();
    loadTodayStats();
}, 5000);

// Cleanup on page unload
$(window).on('beforeunload', function() {
    if (priceUpdateInterval) {
        clearInterval(priceUpdateInterval);
    }
    if (activeTradeCountdown) {
        clearInterval(activeTradeCountdown);
    }
});
</script>
@endsection
@extends('layouts.dash')

@section('styles')
<style>
    .trading-interface {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        color: white;
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: 0 15px 35px rgba(102, 126, 234, 0.3);
    }
    
    .price-chart {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .prediction-buttons {
        display: flex;
        gap: 15px;
        margin-top: 20px;
    }
    
    .prediction-btn {
        flex: 1;
        padding: 15px;
        border: none;
        border-radius: 12px;
        font-size: 1.2rem;
        font-weight: bold;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .prediction-btn.up {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
    }
    
    .prediction-btn.down {
        background: linear-gradient(135deg, #dc3545, #fd7e14);
        color: white;
    }
    
    .prediction-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.2);
    }
    
    .trade-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    
    .trade-card:hover {
        transform: translateY(-2px);
    }
    
    .asset-selector {
        background: rgba(255,255,255,0.2);
        border: 2px solid rgba(255,255,255,0.3);
        border-radius: 10px;
        color: white;
        padding: 10px 15px;
        margin-bottom: 20px;
    }
    
    .asset-selector option {
        color: #333;
    }
    
    .price-display {
        font-size: 3rem;
        font-weight: bold;
        text-align: center;
        margin: 20px 0;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    }
    
    .price-change {
        text-align: center;
        font-size: 1.2rem;
        margin-bottom: 20px;
    }
    
    .price-up { color: #28a745; }
    .price-down { color: #dc3545; }
    
    .trading-form {
        background: rgba(255,255,255,0.1);
        border-radius: 15px;
        padding: 25px;
        margin-top: 20px;
    }
    
    .form-control-white {
        background: rgba(255,255,255,0.2);
        border: 2px solid rgba(255,255,255,0.3);
        border-radius: 8px;
        color: white;
        padding: 12px 15px;
    }
    
    .form-control-white::placeholder {
        color: rgba(255,255,255,0.7);
    }
    
    .form-control-white:focus {
        background: rgba(255,255,255,0.3);
        border-color: rgba(255,255,255,0.5);
        color: white;
        box-shadow: none;
    }
    
    .balance-display {
        background: rgba(255,255,255,0.2);
        border-radius: 10px;
        padding: 15px;
        text-align: center;
        margin-bottom: 20px;
    }
    
    .balance-amount {
        font-size: 1.5rem;
        font-weight: bold;
    }
    
    .active-trades-panel {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .trade-item {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
        border-left: 4px solid #007bff;
    }
    
    .trade-item.winning {
        border-left-color: #28a745;
        background: #d4edda;
    }
    
    .trade-item.losing {
        border-left-color: #dc3545;
        background: #f8d7da;
    }
    
    .countdown-timer {
        font-size: 1.5rem;
        font-weight: bold;
        text-align: center;
        margin: 15px 0;
        padding: 10px;
        background: rgba(255,255,255,0.2);
        border-radius: 8px;
    }
    
    .demo-mode-banner {
        background: linear-gradient(135deg, #ffc107, #fd7e14);
        color: white;
        padding: 10px;
        text-align: center;
        border-radius: 10px;
        margin-bottom: 20px;
        font-weight: bold;
    }
    
    .quick-amounts {
        display: flex;
        gap: 10px;
        margin: 15px 0;
        flex-wrap: wrap;
    }
    
    .quick-amount {
        background: rgba(255,255,255,0.2);
        border: 2px solid rgba(255,255,255,0.3);
        color: white;
        padding: 8px 15px;
        border-radius: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }
    
    .quick-amount:hover {
        background: rgba(255,255,255,0.3);
        border-color: rgba(255,255,255,0.5);
    }
</style>
@endsection

@section('content')
@if(session('isDemo', false))
<div class="demo-mode-banner">
    <i class="fa fa-info-circle"></i> DEMO MODE - You're trading with virtual money
</div>
@endif

<div class="row">
    <!-- Trading Interface -->
    <div class="col-lg-8 col-md-12">
        <div class="trading-interface">
            <div class="row align-items-center mb-4">
                <div class="col-md-6">
                    <h3><i class="fa fa-chart-line"></i> Trading Panel</h3>
                </div>
                <div class="col-md-6 text-right">
                    <div class="balance-display">
                        <div class="balance-label">Available Balance</div>
                        <div class="balance-amount" id="userBalance">
                            ${{ number_format(auth()->user()->account_bal ?? 0, 2) }}
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Asset Selection -->
            <div class="row">
                <div class="col-md-6">
                    <label for="assetSelect">Select Asset</label>
                    <select class="form-control asset-selector" id="assetSelect">
                        @foreach($assets as $asset)
                            <option value="{{ $asset->id }}" data-price="{{ $asset->current_price }}">
                                {{ $asset->name }} ({{ $asset->symbol }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="tradeMode">Trade Mode</label>
                    <select class="form-control asset-selector" id="tradeMode">
                        <option value="fixed_time">Fixed Time (90 seconds)</option>
                        <option value="cancel_anytime">Cancel Anytime</option>
                    </select>
                </div>
            </div>
            
            <!-- Price Display -->
            <div class="price-chart">
                <div class="text-center">
                    <h5 id="assetName">BTC/USD</h5>
                    <div class="price-display" id="currentPrice">$0.00</div>
                    <div class="price-change" id="priceChange">
                        <span id="changeAmount">+$0.00</span>
                        <span id="changePercent">(+0.00%)</span>
                    </div>
                    <div class="countdown-timer" id="countdownTimer" style="display: none;">
                        Time Left: <span id="timeLeft">90</span>s
                    </div>
                </div>
                
                <!-- Simple Chart Canvas -->
                <canvas id="priceChart" width="600" height="200"></canvas>
            </div>
            
            <!-- Trading Form -->
            <div class="trading-form">
                <form id="tradingForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <label>Investment Amount</label>
                            <input type="number" class="form-control form-control-white" id="investmentAmount" 
                                   placeholder="Enter amount" min="1" step="0.01" required>
                            
                            <div class="quick-amounts">
                                <div class="quick-amount" onclick="setAmount(10)">$10</div>
                                <div class="quick-amount" onclick="setAmount(25)">$25</div>
                                <div class="quick-amount" onclick="setAmount(50)">$50</div>
                                <div class="quick-amount" onclick="setAmount(100)">$100</div>
                                <div class="quick-amount" onclick="setAmount(250)">$250</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label>Potential Profit</label>
                            <div class="form-control form-control-white" id="potentialProfit">$0.00</div>
                            <small style="color: rgba(255,255,255,0.8);">
                                Payout: <span id="payoutRate">85%</span>
                            </small>
                        </div>
                    </div>
                    
                    <div class="prediction-buttons">
                        <button type="button" class="prediction-btn up" onclick="placeTrade('up')">
                            <i class="fa fa-arrow-up"></i> HIGHER
                        </button>
                        <button type="button" class="prediction-btn down" onclick="placeTrade('down')">
                            <i class="fa fa-arrow-down"></i> LOWER
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Active Trades Sidebar -->
    <div class="col-lg-4 col-md-12">
        <div class="active-trades-panel">
            <h5><i class="fa fa-list"></i> Active Trades</h5>
            <div id="activeTradesList">
                <div class="text-center text-muted" id="noTrades">
                    <i class="fa fa-chart-line fa-3x mb-3"></i>
                    <p>No active trades</p>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="trade-card mt-4">
            <h6>Today's Stats</h6>
            <div class="row text-center">
                <div class="col-4">
                    <div class="h4 text-success" id="todayWins">0</div>
                    <small>Wins</small>
                </div>
                <div class="col-4">
                    <div class="h4 text-danger" id="todayLosses">0</div>
                    <small>Losses</small>
                </div>
                <div class="col-4">
                    <div class="h4 text-info" id="todayProfit">$0</div>
                    <small>Profit</small>
                </div>
            </div>
        </div>
        
        <!-- Demo Mode Toggle -->
        <div class="trade-card mt-3">
            <h6>Trading Mode</h6>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="demoModeToggle" 
                       {{ session('isDemo', false) ? 'checked' : '' }}>
                <label class="form-check-label" for="demoModeToggle">
                    Demo Mode
                </label>
            </div>
            <small class="text-muted">
                Practice with virtual money
            </small>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let priceUpdateInterval;
let chart;
let priceHistory = [];
let activeTradeCountdown;

$(document).ready(function() {
    initializeChart();
    loadActiveTrades();
    startPriceUpdates();
    loadTodayStats();
    
    // Update potential profit when amount changes
    $('#investmentAmount').on('input', updatePotentialProfit);
    
    // Asset selection change
    $('#assetSelect').change(function() {
        updateAssetInfo();
        clearPriceHistory();
    });
    
    // Demo mode toggle
    $('#demoModeToggle').change(function() {
        toggleDemoMode($(this).is(':checked'));
    });
    
    updateAssetInfo();
});

function initializeChart() {
    const ctx = document.getElementById('priceChart').getContext('2d');
    chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Price',
                data: [],
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: false
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

function startPriceUpdates() {
    updatePrices();
    priceUpdateInterval = setInterval(updatePrices, 2000);
}

function updatePrices() {
    const assetId = $('#assetSelect').val();
    
    $.get('/api/trading/prices', function(prices) {
        if (prices[assetId]) {
            const newPrice = parseFloat(prices[assetId]);
            const oldPrice = parseFloat($('#currentPrice').text().replace('$', '').replace(',', ''));
            
            updatePriceDisplay(newPrice, oldPrice);
            updateChart(newPrice);
        }
    });
}

function updatePriceDisplay(newPrice, oldPrice) {
    $('#currentPrice').text('$' + newPrice.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 4
    }));
    
    const change = newPrice - oldPrice;
    const changePercent = oldPrice > 0 ? (change / oldPrice) * 100 : 0;
    
    $('#changeAmount').text((change >= 0 ? '+' : '') + '$' + Math.abs(change).toFixed(4));
    $('#changePercent').text('(' + (changePercent >= 0 ? '+' : '') + changePercent.toFixed(2) + '%)');
    
    const priceChangeEl = $('#priceChange');
    priceChangeEl.removeClass('price-up price-down');
    priceChangeEl.addClass(change >= 0 ? 'price-up' : 'price-down');
}

function updateChart(price) {
    const now = new Date();
    const timeLabel = now.getHours() + ':' + String(now.getMinutes()).padStart(2, '0') + ':' + String(now.getSeconds()).padStart(2, '0');
    
    priceHistory.push(price);
    
    // Keep only last 50 points
    if (priceHistory.length > 50) {
        priceHistory.shift();
        chart.data.labels.shift();
    }
    
    chart.data.labels.push(timeLabel);
    chart.data.datasets[0].data = [...priceHistory];
    chart.update('none');
}

function updateAssetInfo() {
    const selected = $('#assetSelect option:selected');
    const assetName = selected.text();
    const initialPrice = parseFloat(selected.data('price'));
    
    $('#assetName').text(assetName);
    $('#currentPrice').text('$' + initialPrice.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 4
    }));
    
    // Reset price change display
    $('#changeAmount').text('+$0.00');
    $('#changePercent').text('(+0.00%)');
    $('#priceChange').removeClass('price-up price-down');
}

function clearPriceHistory() {
    priceHistory = [];
    chart.data.labels = [];
    chart.data.datasets[0].data = [];
    chart.update();
}

function setAmount(amount) {
    $('#investmentAmount').val(amount);
    updatePotentialProfit();
}

function updatePotentialProfit() {
    const amount = parseFloat($('#investmentAmount').val()) || 0;
    const payoutRate = 0.85; // 85% payout rate
    const profit = amount * payoutRate;
    
    $('#potentialProfit').text('$' + profit.toFixed(2));
}

function placeTrade(direction) {
    const amount = parseFloat($('#investmentAmount').val());
    const assetId = $('#assetSelect').val();
    const tradeMode = $('#tradeMode').val();
    
    if (!amount || amount <= 0) {
        toastr.error('Please enter a valid investment amount');
        return;
    }
    
    const currentBalance = parseFloat($('#userBalance').text().replace('$', '').replace(',', ''));
    if (amount > currentBalance) {
        toastr.error('Insufficient balance');
        return;
    }
    
    $.post('/api/trading/place-trade', {
        asset_id: assetId,
        amount: amount,
        prediction_type: direction,
        trade_mode: tradeMode,
        _token: $('meta[name="csrf-token"]').attr('content')
    }).done(function(response) {
        toastr.success('Trade placed successfully!');
        $('#investmentAmount').val('');
        updatePotentialProfit();
        loadActiveTrades();
        updateBalance();
        
        if (tradeMode === 'fixed_time') {
            startTradeCountdown(response.trade_id, 90);
        }
    }).fail(function(xhr) {
        const error = xhr.responseJSON?.message || 'Failed to place trade';
        toastr.error(error);
    });
}

function startTradeCountdown(tradeId, seconds) {
    let timeLeft = seconds;
    $('#countdownTimer').show();
    
    activeTradeCountdown = setInterval(function() {
        $('#timeLeft').text(timeLeft);
        timeLeft--;
        
        if (timeLeft < 0) {
            clearInterval(activeTradeCountdown);
            $('#countdownTimer').hide();
            loadActiveTrades();
            toastr.info('Trade completed!');
        }
    }, 1000);
}

function loadActiveTrades() {
    $.get('/api/trading/user-trades', function(trades) {
        let tradesHtml = '';
        
        if (trades.length === 0) {
            $('#noTrades').show();
        } else {
            $('#noTrades').hide();
            
            trades.forEach(function(trade) {
                const statusClass = trade.current_pnl >= 0 ? 'winning' : 'losing';
                const directionIcon = trade.prediction_type === 'up' ? 'fa-arrow-up text-success' : 'fa-arrow-down text-danger';
                const pnlClass = trade.current_pnl >= 0 ? 'text-success' : 'text-danger';
                
                tradesHtml += `
                    <div class="trade-item ${statusClass}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">
                                    <i class="fa ${directionIcon}"></i> ${trade.asset.name}
                                </h6>
                                <small>$${parseFloat(trade.amount).toFixed(2)} - ${trade.prediction_type.toUpperCase()}</small>
                            </div>
                            <div class="text-right">
                                <div class="${pnlClass}">$${parseFloat(trade.current_pnl || 0).toFixed(2)}</div>
                                ${trade.trade_mode === 'cancel_anytime' && trade.status === 'active' ? 
                                    `<button class="btn btn-sm btn-warning mt-1" onclick="cancelTrade(${trade.id})">Cancel</button>` : 
                                    ''}
                            </div>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                Entry: $${parseFloat(trade.entry_price).toFixed(4)} | 
                                Current: $${parseFloat(trade.current_price || trade.entry_price).toFixed(4)}
                            </small>
                        </div>
                    </div>
                `;
            });
        }
        
        $('#activeTradesList').html(tradesHtml);
    });
}

function cancelTrade(tradeId) {
    if (confirm('Are you sure you want to cancel this trade?')) {
        $.post(`/api/trading/cancel-trade/${tradeId}`, {
            _token: $('meta[name="csrf-token"]').attr('content')
        }).done(function(response) {
            toastr.success('Trade cancelled successfully');
            loadActiveTrades();
            updateBalance();
        }).fail(function() {
            toastr.error('Failed to cancel trade');
        });
    }
}

function updateBalance() {
    $.get('/api/trading/balance', function(data) {
        $('#userBalance').text('$' + parseFloat(data.balance).toLocaleString());
    });
}

function loadTodayStats() {
    $.get('/api/trading/today-stats', function(stats) {
        $('#todayWins').text(stats.wins);
        $('#todayLosses').text(stats.losses);
        $('#todayProfit').text('$' + parseFloat(stats.profit).toFixed(2));
    });
}

function toggleDemoMode(isDemo) {
    $.post('/api/trading/toggle-demo', {
        demo_mode: isDemo,
        _token: $('meta[name="csrf-token"]').attr('content')
    }).done(function(response) {
        if (isDemo) {
            toastr.info('Demo mode activated - You are now trading with virtual money');
            $('.demo-mode-banner').show();
        } else {
            toastr.info('Live trading mode activated');
            $('.demo-mode-banner').hide();
        }
        updateBalance();
    }).fail(function() {
        toastr.error('Failed to toggle demo mode');
        $('#demoModeToggle').prop('checked', !isDemo);
    });
}

// Auto-refresh active trades every 5 seconds
setInterval(function() {
    loadActiveTrades();
    loadTodayStats();
}, 5000);

// Cleanup on page unload
$(window).on('beforeunload', function() {
    if (priceUpdateInterval) {
        clearInterval(priceUpdateInterval);
    }
    if (activeTradeCountdown) {
        clearInterval(activeTradeCountdown);
    }
});
</script>
@endsection
