@extends('layouts.dash')
@section('title', 'Binary Trading')

@section('content')
<!-- Trading app data and functions -->
<div x-data="tradingApp()" x-init="init()" class="space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white md:text-3xl">Binary Trading</h1>
            <p class="mt-1 text-sm text-gray-400">Trade on price movements with fixed returns</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Demo Mode Banner -->
            @if(session('isDemo', false))
            <div class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-orange-400 bg-orange-500/10 rounded-lg border border-orange-500/20">
                <i class="fas fa-flask"></i>
                <span>Demo Mode</span>
            </div>
            @endif
            
            <!-- Balance Display -->
            <div class="px-4 py-2 bg-dark-200 rounded-lg border border-dark-100">
                <div class="flex items-center gap-2">
                    <i class="fas fa-wallet text-primary-400"></i>
                    <span class="text-sm text-gray-400">Balance:</span>
                    <span class="font-semibold text-white" x-text="formatCurrency(balance)">
                        ${{ number_format(auth()->user()->account_bal ?? 0, 2) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Trading Grid -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        <!-- Trading Panel - Left Side -->
        <div class="lg:col-span-8 space-y-6">
            <!-- Asset Selection & Price Display -->
            <div class="p-6 bg-dark-200 rounded-xl border border-dark-100">
                <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                    <!-- Asset Selector -->
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-400 mb-2">Select Asset</label>
                        <div class="relative">
                            <select x-model="selectedAsset" @change="assetChanged()" 
                                    class="w-full px-4 py-3 bg-dark-100 border border-dark-100 rounded-lg text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500 appearance-none">
                                @foreach($assets as $asset)
                                    <option value="{{ $asset->id }}" data-price="{{ $asset->current_price }}" data-symbol="{{ $asset->symbol }}">
                                        {{ $asset->name }} ({{ $asset->symbol }})
                                    </option>
                                @endforeach
                            </select>
                            <i class="fas fa-chevron-down absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- Current Price Display -->
                    <div class="text-center sm:text-right">
                        <div class="text-sm text-gray-400 mb-1">Current Price</div>
                        <div class="flex items-center justify-center sm:justify-end gap-2">
                            <span class="text-3xl font-bold text-white" x-text="formatCurrency(currentPrice)">$0.00</span>
                            <div class="flex items-center gap-1" x-show="priceChange !== 0">
                                <i class="fas" :class="priceChange > 0 ? 'fa-arrow-up text-green-400' : 'fa-arrow-down text-red-400'"></i>
                                <span class="text-sm font-medium" :class="priceChange > 0 ? 'text-green-400' : 'text-red-400'" 
                                      x-text="(priceChange > 0 ? '+' : '') + priceChange.toFixed(2) + '%'"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Price Chart -->
            <div class="p-6 bg-dark-200 rounded-xl border border-dark-100">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-white">Price Chart</h3>
                    <div class="flex items-center gap-2">
                        <button class="px-3 py-1 text-xs font-medium text-gray-400 hover:text-white transition-colors">1M</button>
                        <button class="px-3 py-1 text-xs font-medium text-gray-400 hover:text-white transition-colors">5M</button>
                        <button class="px-3 py-1 text-xs font-medium bg-primary-500 text-white rounded">15M</button>
                        <button class="px-3 py-1 text-xs font-medium text-gray-400 hover:text-white transition-colors">1H</button>
                    </div>
                </div>
                <div class="h-80 bg-dark-100 rounded-lg flex items-center justify-center">
                    <canvas id="priceChart" class="w-full h-full"></canvas>
                </div>
            </div>

            <!-- Trading Form -->
            <div class="p-6 bg-dark-200 rounded-xl border border-dark-100">
                <h3 class="text-lg font-semibold text-white mb-6">Place Trade</h3>
                
                <form @submit.prevent="placeTrade()">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Trade Amount -->
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Trade Amount</label>
                            <div class="relative">
                                <input type="number" x-model="tradeAmount" step="0.01" min="1" 
                                       class="w-full px-4 py-3 bg-dark-100 border border-dark-100 rounded-lg text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                       placeholder="Enter amount">
                                <span class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400">USD</span>
                            </div>
                            
                            <!-- Quick Amount Buttons -->
                            <div class="flex gap-2 mt-3">
                                <button type="button" @click="setQuickAmount(25)" 
                                        class="px-3 py-1 text-xs font-medium bg-dark-100 text-gray-400 rounded hover:bg-dark-300 hover:text-white transition-colors">$25</button>
                                <button type="button" @click="setQuickAmount(50)" 
                                        class="px-3 py-1 text-xs font-medium bg-dark-100 text-gray-400 rounded hover:bg-dark-300 hover:text-white transition-colors">$50</button>
                                <button type="button" @click="setQuickAmount(100)" 
                                        class="px-3 py-1 text-xs font-medium bg-dark-100 text-gray-400 rounded hover:bg-dark-300 hover:text-white transition-colors">$100</button>
                                <button type="button" @click="setQuickAmount(250)" 
                                        class="px-3 py-1 text-xs font-medium bg-dark-100 text-gray-400 rounded hover:bg-dark-300 hover:text-white transition-colors">$250</button>
                            </div>
                        </div>

                        <!-- Potential Profit -->
                        <div>
                            <label class="block text-sm font-medium text-gray-400 mb-2">Potential Profit</label>
                            <div class="p-3 bg-dark-100 rounded-lg border border-dark-100">
                                <div class="text-lg font-semibold text-white" x-text="formatCurrency(potentialProfit)">$0.00</div>
                                <div class="text-xs text-gray-400 mt-1">
                                    Payout Rate: <span class="text-primary-400 font-medium" x-text="payoutRate + '%'">85%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Expiry Time -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-gray-400 mb-2">Expiry Time</label>
                        <select x-model="expiryTime" 
                                class="w-full md:w-auto px-4 py-3 bg-dark-100 border border-dark-100 rounded-lg text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                            <option value="60">1 Minute</option>
                            <option value="300">5 Minutes</option>
                            <option value="900">15 Minutes</option>
                            <option value="1800">30 Minutes</option>
                            <option value="3600">1 Hour</option>
                        </select>
                    </div>

                    <!-- Prediction Buttons -->
                    <div class="grid grid-cols-2 gap-4 mt-6">
                        <button type="button" @click="placeTrade('up')" 
                                class="flex items-center justify-center gap-3 px-6 py-4 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-all duration-200 hover:scale-105 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:ring-offset-dark-200">
                            <i class="fas fa-arrow-up text-xl"></i>
                            <span>HIGHER</span>
                        </button>
                        <button type="button" @click="placeTrade('down')" 
                                class="flex items-center justify-center gap-3 px-6 py-4 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-all duration-200 hover:scale-105 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-dark-200">
                            <i class="fas fa-arrow-down text-xl"></i>
                            <span>LOWER</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar - Right Side -->
        <div class="lg:col-span-4 space-y-6">
            <!-- Active Trades -->
            <div class="p-6 bg-dark-200 rounded-xl border border-dark-100">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-white">Active Trades</h3>
                    <span class="px-2 py-1 text-xs font-medium bg-primary-500/10 text-primary-400 rounded-full" x-text="activeTrades.length + ' Active'"></span>
                </div>
                
                <div class="space-y-3 max-h-96 overflow-y-auto custom-scrollbar" x-show="activeTrades.length > 0">
                    <template x-for="trade in activeTrades" :key="trade.id">
                        <div class="p-4 bg-dark-100 rounded-lg border border-dark-100">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-white" x-text="trade.asset"></span>
                                <span class="px-2 py-1 text-xs font-medium rounded-full" 
                                      :class="trade.prediction === 'up' ? 'bg-green-500/10 text-green-400' : 'bg-red-500/10 text-red-400'">
                                    <i class="fas" :class="trade.prediction === 'up' ? 'fa-arrow-up' : 'fa-arrow-down'"></i>
                                    <span x-text="trade.prediction.toUpperCase()"></span>
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-400">Amount:</span>
                                <span class="text-white font-medium" x-text="formatCurrency(trade.amount)"></span>
                            </div>
                            <div class="flex items-center justify-between text-sm mt-1">
                                <span class="text-gray-400">Expires:</span>
                                <span class="text-white font-medium" x-text="formatTime(trade.expiresAt)"></span>
                            </div>
                            <!-- Countdown Timer -->
                            <div class="mt-3 p-2 bg-dark-200 rounded text-center">
                                <span class="text-sm font-mono text-primary-400" x-text="getCountdown(trade.expiresAt)"></span>
                            </div>
                        </div>
                    </template>
                </div>
                
                <div x-show="activeTrades.length === 0" class="text-center py-8">
                    <i class="fas fa-chart-line text-4xl text-gray-600 mb-3"></i>
                    <p class="text-gray-400">No active trades</p>
                </div>
            </div>

            <!-- Trading Stats -->
            <div class="p-6 bg-dark-200 rounded-xl border border-dark-100">
                <h3 class="text-lg font-semibold text-white mb-4">Today's Performance</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-400" x-text="todayStats.wins">0</div>
                        <div class="text-xs text-gray-400">Wins</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-red-400" x-text="todayStats.losses">0</div>
                        <div class="text-xs text-gray-400">Losses</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-primary-400" x-text="todayStats.winRate + '%'">0%</div>
                        <div class="text-xs text-gray-400">Win Rate</div>
                    </div>
                </div>
                
                <!-- Profit/Loss -->
                <div class="mt-4 pt-4 border-t border-dark-100">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-400">Today's P&L:</span>
                        <span class="font-semibold" :class="todayStats.profit >= 0 ? 'text-green-400' : 'text-red-400'" 
                              x-text="(todayStats.profit >= 0 ? '+' : '') + formatCurrency(todayStats.profit)">$0.00</span>
                    </div>
                </div>
            </div>

            <!-- Market Trends -->
            <div class="p-6 bg-dark-200 rounded-xl border border-dark-100">
                <h3 class="text-lg font-semibold text-white mb-4">Market Trends</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 bg-dark-100 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center">
                                <i class="fab fa-bitcoin text-white text-sm"></i>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-white">BTC/USD</div>
                                <div class="text-xs text-gray-400">Bitcoin</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium text-white">$43,250</div>
                            <div class="text-xs text-green-400">+2.4%</div>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-dark-100 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                <i class="fab fa-ethereum text-white text-sm"></i>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-white">ETH/USD</div>
                                <div class="text-xs text-gray-400">Ethereum</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium text-white">$2,650</div>
                            <div class="text-xs text-red-400">-1.2%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
@parent
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function tradingApp() {
    return {
        // Data properties
        selectedAsset: '1',
        currentPrice: 0,
        priceChange: 0,
        tradeAmount: 25,
        potentialProfit: 0,
        payoutRate: 85,
        expiryTime: 300,
        balance: {{ auth()->user()->account_bal ?? 0 }},
        activeTrades: [],
        todayStats: {
            wins: 0,
            losses: 0,
            winRate: 0,
            profit: 0
        },
        priceChart: null,

        // Initialize the app
        init() {
            this.initChart();
            this.loadActiveTrades();
            this.loadTodayStats();
            this.startPriceUpdates();
            this.calculatePotentialProfit();
        },

        // Chart initialization
        initChart() {
            const ctx = document.getElementById('priceChart').getContext('2d');
            this.priceChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: this.generateTimeLabels(),
                    datasets: [{
                        label: 'Price',
                        data: this.generatePriceData(),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            grid: { color: '#1E2028' },
                            ticks: { color: '#9ca3af' }
                        },
                        y: {
                            grid: { color: '#1E2028' },
                            ticks: { color: '#9ca3af' }
                        }
                    }
                }
            });
        },

        // Generate sample data
        generateTimeLabels() {
            const labels = [];
            for (let i = 15; i >= 0; i--) {
                const time = new Date(Date.now() - i * 60000);
                labels.push(time.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}));
            }
            return labels;
        },

        generatePriceData() {
            const basePrice = 43250;
            const data = [];
            for (let i = 0; i < 16; i++) {
                data.push(basePrice + (Math.random() - 0.5) * 1000);
            }
            return data;
        },

        // Asset change handler
        assetChanged() {
            const asset = document.querySelector(`option[value="${this.selectedAsset}"]`);
            this.currentPrice = parseFloat(asset.dataset.price || 0);
            this.calculatePotentialProfit();
        },

        // Quick amount setter
        setQuickAmount(amount) {
            this.tradeAmount = amount;
            this.calculatePotentialProfit();
        },

        // Calculate potential profit
        calculatePotentialProfit() {
            this.potentialProfit = (this.tradeAmount * this.payoutRate) / 100;
        },

        // Place trade
        async placeTrade(direction) {
            if (!this.tradeAmount || this.tradeAmount < 1) {
                this.showAlert('Please enter a valid trade amount', 'error');
                return;
            }

            if (this.tradeAmount > this.balance) {
                this.showAlert('Insufficient balance', 'error');
                return;
            }

            try {
                const response = await fetch('{{ route("user.trading.place") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        asset_id: this.selectedAsset,
                        prediction: direction,
                        amount: this.tradeAmount,
                        expiry_minutes: this.expiryTime / 60
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    this.showAlert('Trade placed successfully!', 'success');
                    this.balance = result.new_balance;
                    this.loadActiveTrades();
                } else {
                    this.showAlert(result.message || 'Failed to place trade', 'error');
                }
            } catch (error) {
                this.showAlert('An error occurred while placing the trade', 'error');
            }
        },

        // Load active trades
        async loadActiveTrades() {
            try {
                const response = await fetch('{{ route("user.trading.active") }}');
                const result = await response.json();
                this.activeTrades = result.trades || [];
            } catch (error) {
                console.error('Failed to load active trades:', error);
            }
        },

        // Load today's stats
        async loadTodayStats() {
            try {
                const response = await fetch('{{ route("user.trading.stats") }}');
                const result = await response.json();
                this.todayStats = result.stats || this.todayStats;
            } catch (error) {
                console.error('Failed to load stats:', error);
            }
        },

        // Start price updates
        startPriceUpdates() {
            setInterval(() => {
                this.updatePrices();
            }, 30000); // Update every 30 seconds
        },

        // Update prices
        updatePrices() {
            // Simulate price changes
            const change = (Math.random() - 0.5) * 100;
            this.currentPrice += change;
            this.priceChange = (change / this.currentPrice) * 100;
            
            // Update chart
            if (this.priceChart) {
                const data = this.priceChart.data.datasets[0].data;
                data.shift();
                data.push(this.currentPrice);
                this.priceChart.update('none');
            }
        },

        // Utility functions
        formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(amount);
        },

        formatTime(timestamp) {
            return new Date(timestamp).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        },

        getCountdown(expiresAt) {
            const now = new Date().getTime();
            const expires = new Date(expiresAt).getTime();
            const diff = expires - now;
            
            if (diff <= 0) return 'EXPIRED';
            
            const minutes = Math.floor(diff / 60000);
            const seconds = Math.floor((diff % 60000) / 1000);
            return `${minutes}:${seconds.toString().padStart(2, '0')}`;
        },

        showAlert(message, type = 'info') {
            // You can implement your preferred alert system here
            alert(message);
        }
    }
}
</script>
<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #1E2028;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #374151;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #4B5563;
}
</style>
