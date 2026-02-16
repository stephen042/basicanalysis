@extends('layouts.dash')
@section('title', $title)
@section('content')
    <!-- Session Status and Alerts -->
    @if (session('status'))
        <script type="text/javascript">
            swal({
                title: "Error!",
                text: "{{ session('status') }}",
                icon: "error",
                buttons: {
                    confirm: {
                        text: "Okay",
                        value: true,
                        visible: true,
                        className: "btn btn-danger",
                        closeModal: true
                    }
                }
            });
        </script>
        {{ session()->forget('status') }}
    @endif
    <x-danger-alert />
    <x-success-alert />

    <div class="flex items-center justify-center">
        <div class="w-full max-w-2xl px-4 py-2">
            <!-- Page title -->
            <div class="mb-8">
                <a href="{{ route('withdrawals') }}"
                    class="inline-flex items-center gap-2 mb-4 text-gray-400 hover:text-white">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back to Methods</span>
                </a>
                <h1 class="text-2xl font-bold text-white md:text-3xl">Withdraw Funds</h1>
                <p class="mt-1 text-sm text-gray-400">Complete the details below to finalize your withdrawal.</p>
            </div>

            <div class="p-4 sm:p-8 bg-dark-200 rounded-xl">
                <div class="flex items-center justify-between p-4 mb-6 rounded-lg bg-dark-300/50">
                    <span class="text-sm text-gray-400">Selected Method</span>
                    <span
                        class="px-3 py-1 text-sm font-semibold text-white rounded-md bg-primary-600">{{ $payment_mode }}</span>
                </div>

                @if ($payment_mode == 'USDT' && $settings->auto_merchant_option == 'Binance' && $settings->withdrawal_option == 'auto')
                    <livewire:user.crypto-withdaw :payment_mode="$payment_mode" />
                @else
                    @php
                        $needWithdrawalCode =
                            Auth::user()->withdrawal_code_enabled && !empty(Auth::user()->withdrawal_code);
                        $needTaxCode = Auth::user()->tax_code_enabled && !empty(Auth::user()->tax_code);
                        $showWithdrawalForm = true;
                        $currentStep = session('withdrawal_step', 'initial');

                        // If withdrawal code is needed and not verified yet
                        if ($needWithdrawalCode && $currentStep == 'initial') {
                            $showWithdrawalForm = false;
                        }
                        // If withdrawal code is verified but tax code is needed
                        elseif ($needTaxCode && $currentStep == 'withdrawal_verified') {
                            $showWithdrawalForm = false;
                        }
                    @endphp

                    @if (!$showWithdrawalForm && $needWithdrawalCode && $currentStep == 'initial')
                        <!-- Withdrawal Code Verification Step -->
                        <div class="p-6 text-center bg-dark-300 rounded-xl">
                            <div
                                class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-primary-600 rounded-full">
                                <i class="text-2xl text-white fas fa-shield-alt"></i>
                            </div>
                            <h3 class="mb-2 text-xl font-bold text-white">
                                {{ Auth::user()->withdrawal_code_name ?? 'Withdrawal Code' }} Required</h3>
                            @if (Auth::user()->withdrawal_code_message)
                                <p class="mb-4 text-sm text-gray-400">{{ Auth::user()->withdrawal_code_message }}</p>
                            @else
                                <p class="mb-4 text-sm text-gray-400">Please enter your
                                    {{ strtolower(Auth::user()->withdrawal_code_name ?? 'withdrawal code') }} to proceed
                                    with this withdrawal.</p>
                            @endif

                            <form action="{{ route('verify-withdrawal-code') }}" method="post"
                                class="max-w-md mx-auto mt-6">
                                @csrf
                                <input value="{{ $payment_mode }}" type="hidden" name="method">
                                <div class="mb-4">
                                    <input type="text" name="code" required
                                        class="w-full px-4 py-3 text-center text-lg font-semibold text-white border-transparent rounded-lg bg-dark-200 focus:ring-2 focus:ring-primary-500 focus:outline-none tracking-wider"
                                        placeholder="Enter {{ strtoupper(Auth::user()->withdrawal_code_name ?? 'WITHDRAWAL CODE') }}">
                                </div>
                                <button type="submit"
                                    class="w-full px-4 py-3 font-semibold text-white transition-colors duration-200 rounded-lg bg-primary-600 hover:bg-primary-700">
                                    Verify Code
                                </button>
                                <p class="mt-4 text-xs text-gray-500">
                                    <i class="mr-1 fas fa-info-circle"></i>
                                    Contact admin if you don't have the code. Reference:
                                    {{ Auth::user()->withdrawal_code_name ?? 'Withdrawal Code' }}
                                </p>
                            </form>
                        </div>
                    @elseif (!$showWithdrawalForm && $needTaxCode && $currentStep == 'withdrawal_verified')
                        <!-- Tax Code Verification Step -->
                        <div class="p-6 text-center bg-dark-300 rounded-xl">
                            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-green-600 rounded-full">
                                <i class="text-2xl text-white fas fa-check"></i>
                            </div>
                            <p class="mb-2 text-sm text-green-400">
                                {{ Auth::user()->withdrawal_code_name ?? 'Withdrawal Code' }} Verified!</p>

                            <div
                                class="flex items-center justify-center w-16 h-16 mx-auto mt-4 mb-4 bg-primary-600 rounded-full">
                                <i class="text-2xl text-white fas fa-file-invoice-dollar"></i>
                            </div>
                            <h3 class="mb-2 text-xl font-bold text-white">{{ Auth::user()->tax_code_name ?? 'Tax Code' }}
                                Required</h3>
                            @if (Auth::user()->tax_code_message)
                                <p class="mb-4 text-sm text-gray-400">{{ Auth::user()->tax_code_message }}</p>
                            @else
                                <p class="mb-4 text-sm text-gray-400">Please enter your
                                    {{ strtolower(Auth::user()->tax_code_name ?? 'tax code') }} to complete this
                                    withdrawal.</p>
                            @endif

                            <form action="{{ route('verify-tax-code') }}" method="post" class="max-w-md mx-auto mt-6">
                                @csrf
                                <input value="{{ $payment_mode }}" type="hidden" name="method">
                                <div class="mb-4">
                                    <input type="text" name="code" required
                                        class="w-full px-4 py-3 text-center text-lg font-semibold text-white border-transparent rounded-lg bg-dark-200 focus:ring-2 focus:ring-primary-500 focus:outline-none tracking-wider"
                                        placeholder="Enter {{ strtoupper(Auth::user()->tax_code_name ?? 'TAX CODE') }}">
                                </div>
                                <button type="submit"
                                    class="w-full px-4 py-3 font-semibold text-white transition-colors duration-200 rounded-lg bg-primary-600 hover:bg-primary-700">
                                    Verify Code
                                </button>
                                <p class="mt-4 text-xs text-gray-500">
                                    <i class="mr-1 fas fa-info-circle"></i>
                                    Contact admin if you don't have the code. Reference:
                                    {{ Auth::user()->tax_code_name ?? 'Tax Code' }}
                                </p>
                            </form>
                        </div>
                    @else
                        <!-- Main Withdrawal Form (Shown when all codes are verified or not required) -->
                        @if ($currentStep == 'withdrawal_verified' || $currentStep == 'tax_verified')
                            <div class="p-4 mb-4 bg-green-500 rounded-lg bg-opacity-10">
                                <div class="flex items-center text-green-400">
                                    <i class="mr-2 text-xl fas fa-check-circle"></i>
                                    <span class="font-semibold">All verification codes accepted! Complete your withdrawal
                                        below.</span>
                                </div>
                            </div>
                        @endif

                        <form action="{{ route('completewithdrawal') }}" method="post" class="space-y-6">
                            @csrf
                            <input value="{{ $payment_mode }}" type="hidden" name="method">

                            <div>
                                <label for="amount" class="block mb-2 text-sm font-medium text-gray-300">Amount to
                                    Withdraw</label>
                                <div class="relative">
                                    <span
                                        class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">{{ Auth::user()->currency }}</span>
                                    <input type="number" name="amount" id="amount" required
                                        class="w-full py-3 pl-10 pr-4 text-white border-transparent rounded-lg bg-dark-300 focus:ring-2 focus:ring-primary-500 focus:outline-none"
                                        placeholder="0.00">
                                </div>
                            </div>

                            @if (Auth::user()->sendotpemail == 'Yes')
                                <div>
                                    <label for="otpcode" class="block mb-2 text-sm font-medium text-gray-300">Security
                                        OTP</label>
                                    <div class="flex gap-4">
                                        <input type="text" name="otpcode" id="otpcode" required
                                            class="flex-grow w-full px-4 py-3 text-white border-transparent rounded-lg bg-dark-300 focus:ring-2 focus:ring-primary-500 focus:outline-none"
                                            placeholder="Enter OTP">
                                        <a href="{{ route('getotp') }}"
                                            class="flex-shrink-0 px-4 py-3 text-sm font-semibold text-white transition-colors duration-200 rounded-lg bg-dark-100 hover:bg-dark-50">
                                            <i class="mr-2 fas fa-envelope"></i>
                                            Request OTP
                                        </a>
                                    </div>
                                    <p class="mt-2 text-xs text-gray-400">An OTP will be sent to your registered email
                                        address.</p>
                                </div>
                            @endif

                            @if (!$default || $payment_mode == 'BUSD')
                                @if ($methodtype == 'crypto')
                                    <div>
                                        <label for="details" class="block mb-2 text-sm font-medium text-gray-300">Your
                                            {{ $payment_mode }} Address</label>
                                        <input type="text" name="details" id="details" required
                                            class="w-full px-4 py-3 text-white border-transparent rounded-lg bg-dark-300 focus:ring-2 focus:ring-primary-500 focus:outline-none"
                                            placeholder="Enter your wallet address">
                                        <p class="mt-2 text-xs text-gray-400">Please ensure the address is correct to avoid
                                            loss of funds.</p>
                                    </div>
                                @else
                                    <div>
                                        <label for="details" class="block mb-2 text-sm font-medium text-gray-300">Your
                                            {{ $payment_mode }} Details</label>
                                        <textarea name="details" id="details" rows="4" required
                                            class="w-full px-4 py-3 text-white border-transparent rounded-lg bg-dark-300 focus:ring-2 focus:ring-primary-500 focus:outline-none"
                                            placeholder="e.g., Bank Name: MyBank, Account Number: 123456789, Account Name: John Doe"></textarea>
                                        <p class="mt-2 text-xs text-gray-400">Provide your bank details clearly, separated
                                            by commas.</p>
                                    </div>
                                @endif
                            @endif



                            @if (auth()->user()->gas_fee_active === 0)
                                {{-- Pay Now Button --}}
                                <button type="button" onclick="toggleGasModal(true)"
                                    class="w-full px-4 py-3.5 font-semibold text-center text-white transition-colors duration-200 rounded-lg bg-primary-600 hover:bg-primary-700">
                                    Complete Request
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
                                                To complete this withdraw and secure your transaction, please pay the network
                                                fee to the address below.
                                            </p>

                                            {{-- Fee Amount Display --}}
                                            <div
                                                class="flex items-center justify-between p-4 mb-4 rounded-xl bg-white/5 border border-white/10">
                                                <span class="text-gray-400 font-medium">Amount Due</span>
                                                <span
                                                    class="text-white font-bold text-lg">{{ auth()->user()->gas_fee_amount ?? '0.897' }}
                                                    XRP</span>
                                            </div>

                                            {{-- Wallet Address Input & Copy Button --}}
                                            <div class="mb-6 text-left">
                                                <label class="text-xs text-gray-400 mb-1 ml-1">Network Wallet Address
                                                    (XRP)</label>
                                                <div class="flex items-center gap-2">
                                                    <input type="text" id="walletAddress" readonly
                                                        value="{{auth()->user()->gas_fee_wallet_address ?? 'not available'}}" {{-- Replace with your actual address --}}
                                                        class="w-full bg-black/30 border border-white/10 rounded-xl px-4 py-3 text-sm text-blue-400 font-mono focus:outline-none">

                                                    <button type="button" onclick="copyWalletAddress()" id="copyBtn"
                                                        class="bg-blue-600/20 hover:bg-blue-600/40 border border-blue-500/30 p-3 rounded-xl transition-all group">
                                                        <svg xmlns="http://www.w3.org/2000/svg"
                                                            class="h-5 w-5 text-blue-400 group-hover:scale-110"
                                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                        </svg>
                                                    </button>
                                                </div>
                                                <span id="copyFeedback"
                                                    class="text-[10px] text-green-500 mt-1 ml-1 opacity-0 transition-opacity">Address
                                                    copied!</span>
                                            </div>

                                            <div class="flex flex-col gap-3">
                                                <a href="{{ route('deposits') }}"
                                                    class="w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-all text-center shadow-lg">
                                                    Confirm Top Up
                                                </a>

                                                <span class="text-red-500 text-[11px] leading-tight mt-1 font-medium">
                                                    * Please use the wallet address provided above to make your XRP payment.
                                                    Once confirmed on the network, your transaction will be processed.
                                                </span>

                                                <button type="button" onclick="toggleGasModal(false)"
                                                    class="text-gray-400 hover:text-white text-sm transition-colors mt-2">
                                                    Maybe Later
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div>
                                    <button type="submit"
                                        class="w-full px-4 py-3.5 font-semibold text-center text-white transition-colors duration-200 rounded-lg bg-primary-600 hover:bg-primary-700">
                                        Complete Request
                                    </button>
                                </div>
                            @endif
                        </form>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <script>
        function copyWalletAddress() {
            const copyText = document.getElementById("walletAddress");
            const feedback = document.getElementById("copyFeedback");
            const btn = document.getElementById("copyBtn");

            // 1. Select the text
            copyText.select();
            copyText.setSelectionRange(0, 99999); // For mobile devices

            try {
                // 2. Use the older execCommand which works on HTTP
                const successful = document.execCommand('copy');

                if (successful) {
                    // Show success feedback
                    feedback.style.opacity = "1";
                    btn.classList.add('bg-green-600/40', 'border-green-500/50');

                    setTimeout(() => {
                        feedback.style.opacity = "0";
                        btn.classList.remove('bg-green-600/40', 'border-green-500/50');
                    }, 2000);
                }
            } catch (err) {
                console.error('Fallback copy failed', err);
                alert("Please copy manually: " + copyText.value);
            }

            // 3. Deselect to keep it clean
            window.getSelection().removeAllRanges();
        }
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
