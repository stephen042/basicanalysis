<!-- Portfolio Summary Component -->
<div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden"
     x-data="portfolioSummary()"
     x-init="init()">

    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-purple-700 p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-xl font-bold">Portfolio Performance</h3>
                <p class="text-blue-100 text-sm">Track your investment growth</p>
            </div>
            <div class="text-right">
                <p class="text-2xl font-bold" x-text="totalValue"></p>
                <p class="text-blue-100 text-sm">Total Value</p>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="text-center p-4 bg-green-50 dark:bg-green-900 rounded-lg">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-800 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-chart-line text-green-600 dark:text-green-400"></i>
                </div>
                <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Total Profit</h4>
                <p class="text-xl font-bold text-green-600 dark:text-green-400" x-text="totalProfit"></p>
                <p class="text-xs text-green-600 dark:text-green-400" x-text="profitPercentage"></p>
            </div>

            <div class="text-center p-4 bg-blue-50 dark:bg-blue-900 rounded-lg">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-800 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-wallet text-blue-600 dark:text-blue-400"></i>
                </div>
                <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Active Investments</h4>
                <p class="text-xl font-bold text-blue-600 dark:text-blue-400" x-text="activeInvestments"></p>
                <p class="text-xs text-blue-600 dark:text-blue-400">Running plans</p>
            </div>

            <div class="text-center p-4 bg-purple-50 dark:bg-purple-900 rounded-lg">
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-800 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-clock text-purple-600 dark:text-purple-400"></i>
                </div>
                <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1">Avg. ROI</h4>
                <p class="text-xl font-bold text-purple-600 dark:text-purple-400" x-text="averageROI"></p>
                <p class="text-xs text-purple-600 dark:text-purple-400">Monthly return</p>
            </div>
        </div>

        <!-- Performance Chart -->
        <div class="mb-6">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Performance Chart</h4>
            <div class="h-64 bg-gray-50 dark:bg-gray-700 rounded-lg flex items-center justify-center">
                <div id="performance-chart" class="w-full h-full"></div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div>
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Activity</h4>
                <button @click="refreshTransactions()"
                        class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm">
                    <i class="fas fa-sync-alt mr-1" :class="{ 'animate-spin': isLoading }"></i>
                    Refresh
                </button>
            </div>

            <div class="space-y-3">
                <template x-for="transaction in recentTransactions" :key="transaction.id">
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                        <div class="flex items-center space-x-3">
                            <div :class="transaction.type === 'profit' ? 'bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400' :
                                        transaction.type === 'deposit' ? 'bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400' :
                                        'bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400'"
                                 class="w-8 h-8 rounded-full flex items-center justify-center">
                                <i :class="transaction.type === 'profit' ? 'fas fa-arrow-up' :
                                          transaction.type === 'deposit' ? 'fas fa-plus' : 'fas fa-arrow-down'"
                                   class="text-xs"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="transaction.description"></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400" x-text="transaction.time"></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p :class="transaction.type === 'withdrawal' ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'"
                               class="text-sm font-semibold"
                               x-text="(transaction.type === 'withdrawal' ? '-' : '+') + transaction.amount"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
function portfolioSummary() {
    return {
        totalValue: '{{ Auth::user()->currency }}{{ number_format(Auth::user()->account_bal, 2, ".", ",") }}',
        totalProfit: '{{ Auth::user()->currency }}{{ number_format(Auth::user()->roi ?? 0, 2, ".", ",") }}',
        profitPercentage: '+12.5%',
        activeInvestments: '{{ $user_plans->where("active", "yes")->count() ?? 0 }}',
        averageROI: '8.5%',
        isLoading: false,
        recentTransactions: [
            {
                id: 1,
                type: 'profit',
                description: 'AI Trading Profit',
                amount: '$125.50',
                time: '2 hours ago'
            },
            {
                id: 2,
                type: 'deposit',
                description: 'Account Deposit',
                amount: '$500.00',
                time: '1 day ago'
            },
            {
                id: 3,
                type: 'profit',
                description: 'Investment Return',
                amount: '$89.25',
                time: '2 days ago'
            }
        ],

        init() {
            this.initChart();
        },

        initChart() {
            // Initialize performance chart with Chart.js
            const ctx = document.getElementById('performance-chart');
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                        datasets: [{
                            label: 'Portfolio Value',
                            data: [1000, 1150, 1080, 1250, 1400, 1520],
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
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
                                    display: false
                                }
                            },
                            y: {
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.1)'
                                }
                            }
                        }
                    }
                });
            }
        },

        refreshTransactions() {
            this.isLoading = true;

            // Simulate API call
            setTimeout(() => {
                // Update transactions with new data
                this.recentTransactions.unshift({
                    id: Date.now(),
                    type: 'profit',
                    description: 'New Trading Profit',
                    amount: '$' + (Math.random() * 100 + 50).toFixed(2),
                    time: 'Just now'
                });

                // Keep only last 5 transactions
                if (this.recentTransactions.length > 5) {
                    this.recentTransactions = this.recentTransactions.slice(0, 5);
                }

                this.isLoading = false;
            }, 1000);
        }
    }
}
</script>
