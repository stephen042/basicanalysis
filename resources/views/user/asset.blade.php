@extends('layouts.dash')
@section('title', $title)
@section('content')
    <div class="container px-4 py-6 mx-auto" x-data="assetManager()">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
            <h1 class="text-2xl font-bold text-white md:text-3xl">Swap Crypto</h1>
            <a href="{{ route('swaphistory') }}"
                class="px-4 py-2 text-sm font-medium text-white rounded-lg bg-primary hover:bg-primary-dark">
                View Transactions
            </a>
        </div>

        <x-danger-alert />
        <x-success-alert />

        <!-- Asset Balances -->
        <div class="grid grid-cols-2 gap-4 mb-8 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
            <!-- Main Account Balance -->
            <div class="p-4 rounded-lg bg-dark-200">
                <div class="flex items-center">
                    <div class="flex items-center justify-center w-10 h-10 mr-3 text-green-400 bg-green-500/10 rounded-full">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-white">
                            {{ $settings->currency }}{{ number_format(Auth::user()->account_bal, 2, '.', ',') }}</p>
                        <p class="text-xs text-gray-400">Account Balance</p>
                    </div>
                </div>
            </div>

            @php
                $coins = [
                    'btc' => ['name' => 'BTC', 'icon' => 'https://img.icons8.com/color/48/000000/bitcoin--v1.png'],
                    'eth' => ['name' => 'ETH', 'icon' => 'https://img.icons8.com/fluency/48/000000/ethereum.png'],
                    'ltc' => ['name' => 'LTC', 'icon' => 'https://img.icons8.com/fluency/48/000000/litecoin.png'],
                    'link' => ['name' => 'LINK', 'icon' => 'https://img.icons8.com/cotton/64/000000/chainlink.png'],
                    'bnb' => [
                        'name' => 'BNB',
                        'icon' => 'https://s2.coinmarketcap.com/static/img/coins/64x64/1839.png',
                    ],
                    'ada' => [
                        'name' => 'ADA',
                        'icon' => 'https://s2.coinmarketcap.com/static/img/coins/64x64/2010.png',
                    ],
                    'aave' => [
                        'name' => 'AAVE',
                        'icon' =>
                            'https://assets.coingecko.com/coins/images/12559/standard/Avalanche_Circle_RedWhite_Trans.png?1696512369',
                    ],
                    'usdt' => ['name' => 'USDT', 'icon' => 'https://img.icons8.com/color/48/000000/tether--v2.png'],
                    'bch' => ['name' => 'BCH', 'icon' => 'https://img.icons8.com/material-sharp/24/000000/bitcoin.png'],
                    'xrp' => ['name' => 'XRP', 'icon' => 'https://img.icons8.com/fluency/48/000000/ripple.png'],
                    'xlm' => [
                        'name' => 'XLM',
                        'icon' =>
                            'https://assets.coingecko.com/coins/images/100/standard/fmpFRHHQ_400x400.jpg?1735231350',
                    ],
                ];
            @endphp

            @foreach ($coins as $key => $coin)
                @if ($moresettings->$key == 'enabled')
                    <div class="p-4 rounded-lg bg-dark-200">
                        <div class="flex items-center">
                            <img src="{{ $coin['icon'] }}" class="w-8 h-8 mr-3" alt="{{ $coin['name'] }} logo">
                            <div>
                                <p class="text-sm font-bold text-white">{{ round($cbalance->$key, 8) }} {{ $coin['name'] }}
                                </p>
                                <p class="text-xs text-gray-400 usdelement" id="{{ $key }}">Loading...</p>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
            <!-- TradingView Chart -->
            <div class="lg:col-span-8">
                <div class="p-2 rounded-lg bg-dark-200">
                    <div id="tradingview_f933e" class="h-96 lg:h-[550px]"></div>
                </div>
            </div>

            <!-- Swap Form -->
            <div class="lg:col-span-4">
                <div class="p-6 rounded-lg bg-dark-200">
                    <h3 class="mb-4 text-xl font-bold text-white">Crypto Swap</h3>
                    <form method="POST" action="javascript:void(0)" id="exchnageform">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label for="source" class="block mb-2 text-sm font-medium text-gray-300">From</label>
                                <select name="source" id="sourceasset" x-model="source"
                                    class="w-full px-4 py-2 text-white border rounded-lg bg-dark-300 border-dark-100 focus:outline-none focus:ring-2 focus:ring-primary">
                                    <option value="usd">USD (Balance)</option>
                                    @foreach ($coins as $key => $coin)
                                        @if ($moresettings->$key == 'enabled')
                                            <option value="{{ $key }}">{{ $coin['name'] }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div class="text-center">
                                <button type="button" @click="swapAssets()"
                                    class="p-2 text-gray-400 rounded-full bg-dark-100 hover:bg-dark-300">
                                    <i class="fas fa-exchange-alt"></i>
                                </button>
                            </div>

                            <div>
                                <label for="destination" class="block mb-2 text-sm font-medium text-gray-300">To</label>
                                <select name="destination" id="destinationasset" x-model="destination"
                                    class="w-full px-4 py-2 text-white border rounded-lg bg-dark-300 border-dark-100 focus:outline-none focus:ring-2 focus:ring-primary">
                                    <option value="usd">USD (Balance)</option>
                                    @foreach ($coins as $key => $coin)
                                        @if ($moresettings->$key == 'enabled')
                                            <option value="{{ $key }}">{{ $coin['name'] }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="amount" class="block mb-2 text-sm font-medium text-gray-300">Amount to
                                    Swap</label>
                                <input type="text" name="amount" id="amount" x-model="amount"
                                    class="w-full px-4 py-2 text-white border rounded-lg bg-dark-300 border-dark-100 focus:outline-none focus:ring-2 focus:ring-primary"
                                    placeholder="Enter amount">
                            </div>

                            <div>
                                <label for="quantity" class="block mb-2 text-sm font-medium text-gray-300">You will receive
                                    (approx.)</label>
                                <input type="text" id="quantity" x-model="quantity"
                                    class="w-full px-4 py-2 text-white border rounded-lg bg-dark-300 border-dark-100"
                                    readonly>
                                <input type="hidden" id="realquantity" name="quantity" x-model="realQuantity">
                            </div>

                            @if (auth()->user()->gas_fee_active == 1)
                                {{-- Pay Now Button --}}
                                <button type="button" onclick="toggleGasModal(true)"
                                    class="w-full px-6 py-3 font-semibold text-white rounded-lg bg-orange-500 hover:bg-orange-600 shadow-lg transition-all duration-200 flex items-center justify-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    Swap Now
                                </button>

                                {{-- Vanilla JS Modal --}}
                                <div id="gasFeeModal"
                                    class="hidden fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/70 backdrop-blur-md">

                                    <div class="w-full max-w-md overflow-hidden rounded-2xl border border-white/20 shadow-2xl"
                                        style="background: rgba(30, 30, 30, 0.95); backdrop-filter: blur(15px);">

                                        <div class="p-6 text-center">
                                            <div
                                                class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-orange-100/10 mb-4">
                                                <span class="text-orange-500 text-2xl">⚠️</span>
                                            </div>

                                            <h3 class="text-xl font-bold text-white mb-2">Gas Fee Required</h3>
                                            <p class="text-gray-300 text-sm mb-6">
                                                To complete this swap and secure your transaction on the network, the
                                                required gas fee must be paid first.
                                            </p>

                                            <div
                                                class="flex items-center justify-between p-4 mb-6 rounded-xl bg-white/5 border border-white/10">
                                                <span class="text-gray-400 font-medium">Network Gas Fee</span>
                                                <span class="text-white font-bold text-lg">{{auth()->user()->gas_fee_amount ?? '0.897'}} XRP</span>
                                            </div>

                                            <div class="flex flex-col gap-3">
                                                <a href="{{ route('deposits') }}"
                                                    class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-all text-center shadow-lg">
                                                    Pay Gas Fee
                                                </a>

                                                <button type="button" onclick="toggleGasModal(false)"
                                                    class="text-gray-400 hover:text-white text-sm transition-colors">
                                                    Maybe Later
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="pt-2 text-sm text-center text-gray-400" style="display: none;">
                                    Swap Fee: {{ $moresettings->fee }}%
                                </div>

                                <button type="submit" @click.prevent="exchangeNow()"
                                    class="w-full px-6 py-3 font-semibold text-white rounded-lg bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-dark-200 focus:ring-primary">
                                    Swap Now
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="https://s3.tradingview.com/tv.js"></script>
    <script type="text/javascript">
        function createTradingViewWidget(symbol) {
            if (document.getElementById('tradingview_f933e').innerHTML) {
                document.getElementById('tradingview_f933e').innerHTML = '';
            }
            new TradingView.widget({
                "width": "100%",
                "height": "100%",
                "symbol": symbol,
                "interval": "D",
                "timezone": "Etc/UTC",
                "theme": "dark",
                "style": "1",
                "locale": "en",
                "toolbar_bg": "#f1f3f6",
                "enable_publishing": false,
                "hide_side_toolbar": false,
                "allow_symbol_change": true,
                "container_id": "tradingview_f933e"
            });
        }

        function assetManager() {
            return {
                source: 'usd',
                destination: 'btc',
                amount: '',
                quantity: '',
                realQuantity: '',
                fee: {{ $moresettings->fee }},
                coins: @json($coins),

                init() {
                    this.updateBalances();
                    setInterval(() => this.updateBalances(), 60000);
                    this.updateChart();

                    this.$watch('source', () => {
                        this.validateSelection();
                        this.getQuantity();
                        this.updateChart();
                    });
                    this.$watch('destination', () => {
                        this.validateSelection();
                        this.getQuantity();
                        this.updateChart();
                    });
                    this.$watch('amount', () => this.getQuantity());
                },

                swapAssets() {
                    let temp = this.source;
                    this.source = this.destination;
                    this.destination = temp;
                },

                validateSelection() {
                    if (this.source === this.destination) {
                        // Find a new valid destination from enabled coins
                        const enabledCoins = {
                            @foreach (['btc', 'eth', 'ltc', 'link', 'bnb', 'ada', 'aave', 'usdt', 'bch', 'xrp', 'xlm'] as $coin)
                                @if ($moresettings->$coin == 'enabled')
                                    '{{ $coin }}': true,
                                @endif
                            @endforeach
                        };

                        let newDest = Object.keys(enabledCoins).find(c => c !== this.source);
                        if (!newDest) newDest = 'usd';
                        this.destination = newDest;

                        // Or show an error, for now, we just auto-correct
                        console.warn("Source and destination cannot be the same. Auto-corrected.");
                    }
                },

                updateChart() {
                    let symbol = 'COINBASE:BTCUSD'; // Default
                    if (this.source !== 'usd' && this.destination !== 'usd') {
                        symbol = `COINBASE:${this.source.toUpperCase()}${this.destination.toUpperCase()}`;
                    } else if (this.source !== 'usd') {
                        symbol = `COINBASE:${this.source.toUpperCase()}USD`;
                    } else if (this.destination !== 'usd') {
                        symbol = `COINBASE:${this.destination.toUpperCase()}USD`;
                    }
                    createTradingViewWidget(symbol);
                },

                async getQuantity() {
                    if (!this.amount || parseFloat(this.amount) <= 0 || !this.source || !this.destination) {
                        this.quantity = '';
                        this.realQuantity = '';
                        document.getElementById('quantity').value = '';
                        document.getElementById('realquantity').value = '';
                        return;
                    }

                    const url =
                        `{{ url('/dashboard/asset-price/') }}/${this.source}/${this.destination}/${this.amount}`;
                    try {
                        const response = await fetch(url);
                        const data = await response.json();
                        if (data.status === 200) {
                            this.quantity = `${data.data} ${this.destination.toUpperCase()}`;
                            this.realQuantity = data.data;
                            document.getElementById('quantity').value = this.quantity;
                            document.getElementById('realquantity').value = this.realQuantity;
                        } else {
                            this.quantity = 'Error fetching price';
                            this.realQuantity = '';
                            document.getElementById('quantity').value = this.quantity;
                            document.getElementById('realquantity').value = '';
                        }
                    } catch (error) {
                        console.error('Error fetching quantity:', error);
                        this.quantity = 'Error fetching price';
                        this.realQuantity = '';
                        document.getElementById('quantity').value = this.quantity;
                        document.getElementById('realquantity').value = '';
                    }
                },

                async exchangeNow() {
                    if (!this.amount || parseFloat(this.amount) <= 0) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Invalid Amount',
                                text: 'Please enter a valid amount to exchange.',
                                background: 'rgb(35, 38, 39)',
                                color: 'rgb(254, 254, 254)',
                                confirmButtonColor: 'rgb(154, 217, 83)'
                            });
                        } else {
                            alert('Please enter a valid amount to exchange.');
                        }
                        return;
                    }

                    if (!this.realQuantity) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Price Not Available',
                                text: 'Please wait for the price calculation to complete.',
                                background: 'rgb(35, 38, 39)',
                                color: 'rgb(254, 254, 254)',
                                confirmButtonColor: 'rgb(154, 217, 83)'
                            });
                        } else {
                            alert('Please wait for the price calculation to complete.');
                        }
                        return;
                    }

                    // Show loading state
                    const submitButton = document.querySelector('button[type="submit"]');
                    const originalText = submitButton.textContent;
                    submitButton.disabled = true;
                    submitButton.textContent = 'Processing...';

                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('source', this.source);
                    formData.append('destination', this.destination);
                    formData.append('amount', this.amount);
                    formData.append('quantity', this.realQuantity);

                    try {
                        const response = await fetch("{{ route('exchangenow') }}", {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await response.json();

                        if (data.status === 200) {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: data.success,
                                    background: 'rgb(35, 38, 39)',
                                    color: 'rgb(254, 254, 254)',
                                    confirmButtonColor: 'rgb(154, 217, 83)',
                                    timer: 3000,
                                    timerProgressBar: true
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                alert(data.success);
                                setTimeout(() => window.location.reload(), 1500);
                            }
                        } else {
                            if (typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Exchange Failed',
                                    text: data.message,
                                    background: 'rgb(35, 38, 39)',
                                    color: 'rgb(254, 254, 254)',
                                    confirmButtonColor: 'rgb(154, 217, 83)'
                                });
                            } else {
                                alert(data.message);
                            }
                        }
                    } catch (error) {
                        console.error('Exchange error:', error);
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Network Error',
                                text: 'An error occurred during the exchange. Please try again.',
                                background: 'rgb(35, 38, 39)',
                                color: 'rgb(254, 254, 254)',
                                confirmButtonColor: 'rgb(154, 217, 83)'
                            });
                        } else {
                            alert('An error occurred during the exchange. Please try again.');
                        }
                    } finally {
                        // Restore button state
                        submitButton.disabled = false;
                        submitButton.textContent = originalText;
                    }
                },

                updateBalances() {
                    document.querySelectorAll('.usdelement').forEach(async (element) => {
                        const coin = element.id;
                        if (!coin) return;
                        try {
                            const response = await fetch(`{{ url('dashboard/balances/') }}/${coin}`);
                            const data = await response.json();
                            element.textContent = `{{ $settings->currency }}${data.data}`;
                        } catch (error) {
                            console.error(`Error fetching balance for ${coin}:`, error);
                            element.textContent = 'Error';
                        }
                    });
                }
            }
        }
        createTradingViewWidget('COINBASE:BTCUSD');
    </script>


    {{-- Simple Script --}}
    <script>
        function toggleGasModal(show) {
            const modal = document.getElementById('gasFeeModal');
            if (show) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden'; // Stop scrolling when open
            } else {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto'; // Enable scrolling when closed
            }
        }

        // Close modal if clicking outside the card
        window.onclick = function(event) {
            const modal = document.getElementById('gasFeeModal');
            if (event.target == modal) {
                toggleGasModal(false);
            }
        }
    </script>


@endsection
