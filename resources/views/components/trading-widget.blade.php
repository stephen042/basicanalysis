<!-- Real-time Trading Widget -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6"
     x-data="tradingWidget()"
     x-init="init()">

    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Live Market Data</h3>
        <div class="flex items-center space-x-2">
            <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
            <span class="text-xs text-gray-500 dark:text-gray-400">Live</span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <template x-for="pair in tradingPairs" :key="pair.symbol">
            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors cursor-pointer">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-semibold text-gray-900 dark:text-white text-sm" x-text="pair.symbol"></h4>
                    <div :class="pair.change >= 0 ? 'text-green-600' : 'text-red-600'" class="text-xs">
                        <i :class="pair.change >= 0 ? 'fas fa-arrow-up' : 'fas fa-arrow-down'"></i>
                    </div>
                </div>
                <div class="space-y-1">
                    <p class="text-lg font-bold text-gray-900 dark:text-white" x-text="pair.price"></p>
                    <p :class="pair.change >= 0 ? 'text-green-600' : 'text-red-600'"
                       class="text-xs font-medium"
                       x-text="(pair.change >= 0 ? '+' : '') + pair.change.toFixed(2) + '%'"></p>
                </div>
            </div>
        </template>
    </div>

    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
        <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
            <span>Last updated: <span x-text="lastUpdate"></span></span>
            <button @click="refreshData()"
                    class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                <i class="fas fa-sync-alt" :class="{ 'animate-spin': isLoading }"></i>
                Refresh
            </button>
        </div>
    </div>
</div>

<script>
function tradingWidget() {
    return {
        tradingPairs: [
            { symbol: 'EUR/USD', price: '1.0850', change: 0.12 },
            { symbol: 'GBP/USD', price: '1.2640', change: -0.08 },
            { symbol: 'USD/JPY', price: '148.25', change: 0.25 },
            { symbol: 'BTC/USD', price: '$42,150', change: 2.15 }
        ],
        lastUpdate: '',
        isLoading: false,

        init() {
            this.updateTimestamp();
            this.startRealTimeUpdates();
        },

        updateTimestamp() {
            this.lastUpdate = new Date().toLocaleTimeString();
        },

        refreshData() {
            this.isLoading = true;

            // Simulate API call
            setTimeout(() => {
                this.tradingPairs.forEach(pair => {
                    const change = (Math.random() - 0.5) * 2;
                    pair.change = change;

                    // Update price based on change
                    const currentPrice = parseFloat(pair.price.replace(/[^0-9.-]+/g, ''));
                    const newPrice = currentPrice * (1 + change / 100);

                    if (pair.symbol.includes('USD') && !pair.symbol.includes('BTC')) {
                        pair.price = newPrice.toFixed(4);
                    } else if (pair.symbol.includes('BTC')) {
                        pair.price = '$' + Math.round(newPrice).toLocaleString();
                    } else {
                        pair.price = newPrice.toFixed(2);
                    }
                });

                this.updateTimestamp();
                this.isLoading = false;
            }, 1000);
        },

        startRealTimeUpdates() {
            setInterval(() => {
                this.refreshData();
            }, 30000); // Update every 30 seconds
        }
    }
}
</script>
