@extends('layouts.dash')
@section('title', $title)
@section('content')

    <!-- Page title -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white md:text-3xl">{{ $title }}</h1>
        <p class="mt-1 text-sm text-gray-400">Complete your deposit of <span class="font-bold text-white">{{ Auth::user()->currency }}{{ number_format($amount) }}</span>.</p>
    </div>

    <x-danger-alert />
    <x-success-alert />
    <x-error-alert/>

    <div class="max-w-4xl mx-auto">
        <div class="bg-dark-200 rounded-xl" x-data="{ copied: false }">
            <div class="p-6 border-b md:p-8 border-dark-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-400">You are paying</p>
                        <p class="text-3xl font-bold text-white">{{ Auth::user()->currency }}{{ number_format($amount, 2) }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-400">Payment Method</p>
                        <div class="flex items-center mt-1">
                             @if (!empty($payment_mode->img_url))
                                <img src="{{ $payment_mode->img_url }}" alt="{{ $payment_mode->name }}" class="w-6 h-6 mr-2 rounded-full">
                            @else
                                <span class="mr-2 text-primary-400"><i class="fas fa-money-check-alt"></i></span>
                            @endif
                            <p class="text-lg font-semibold text-white">{{ $payment_mode->name }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-6 md:p-8">
                {{-- Main Payment Logic --}}
                @if ($title == 'Complete Payment')
                    {{-- Automatic Crypto Payment QR Code Display --}}
                    <div class="text-center">
                        <h3 class="text-lg font-semibold text-white">Complete Your Payment</h3>
                        <p class="mt-2 text-gray-400">Send exactly <span class="font-bold text-white">{{ $amount }} {{ $coin }}</span> to the address below or scan the QR code.</p>

                        <div class="flex justify-center my-6">
                            <img width="220" height="220" alt="Payment QR code" src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ env($payment_mode->name) }}" class="border-4 rounded-lg border-primary-500">
                        </div>

                        <div class="w-full max-w-md mx-auto">
                            <label class="text-sm text-gray-400">Payment Address</label>
                            <div class="relative mt-1">
                                <input type="text" class="w-full px-4 py-3 pr-12 text-center text-white bg-dark-100 rounded-md" value="{{ $p_address }}" readonly>
                                <button @click="navigator.clipboard.writeText('{{ $p_address }}'); copied = true; setTimeout(() => copied = false, 2000)" class="absolute top-0 bottom-0 right-0 px-4 text-gray-400 hover:text-white">
                                    <span x-show="!copied"><i class="fas fa-copy"></i></span>
                                    <span x-show="copied" class="text-primary-400"><i class="fas fa-check"></i></span>
                                </button>
                            </div>
                        </div>

                        <p class="mt-6 text-xs text-gray-500">You can leave this page after payment. The system will automatically track the transaction and update your account.</p>
                    </div>

                @else
                    {{-- Other Payment Methods --}}
                    @php
                        $isCrypto = in_array($payment_mode->name, ['Bitcoin', 'Litecoin', 'Ethereum', 'USDT', 'BUSD']);
                    @endphp

                    @if ($settings->deposit_option != 'manual' && $isCrypto)
                        {{-- Auto Crypto Gateways --}}
                        @if ($payment_mode->name == 'USDT' && $settings->auto_merchant_option == 'Binance')
                            <livewire:user.crypto-payment />
                        @else
                            @php
                                if ($payment_mode->name == 'Bitcoin') { $coin = 'BTC'; }
                                elseif ($payment_mode->name == 'Litecoin') { $coin = 'LTC'; }
                                elseif ($payment_mode->name == 'Ethereum') { $coin = 'ETH'; }
                                elseif ($payment_mode->name == 'BUSD') { $coin = 'BUSD'; }
                                else { $coin = 'USDT.TRC20'; }
                            @endphp
                            <div class="text-center">
                                <p class="mb-4 text-gray-300">Click the button below to proceed to the secure payment gateway.</p>
                                <a href="{{ url('dashboard/cpay') }}/{{ $amount }}/{{ $coin }}/{{ Auth::user()->id }}/new" class="w-full px-6 py-3 font-medium text-white transition-colors bg-primary-600 rounded-md sm:w-auto hover:bg-primary-700">
                                    Pay with CoinPayments
                                </a>
                            </div>
                        @endif

                    @elseif ($payment_mode->methodtype != 'currency' || ($isCrypto && $settings->deposit_option == 'manual'))
                        {{-- Manual Crypto Payment --}}
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg font-semibold text-white">Manual Crypto Deposit</h3>
                                <p class="mt-1 text-gray-400">Please send the exact amount to the wallet address below.</p>
                            </div>

                            @if (!empty($payment_mode->barcode) && $payment_mode->barcode != null)
                                <div class="flex justify-center">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data={{ env($payment_mode->name) }}" alt="QR Code" class="w-48 h-48 border-4 rounded-lg border-primary-500">
                                </div>
                            @endif

                            <div>
                                <label class="text-sm text-gray-400">{{ $payment_mode->name }} Address</label>
                                <div class="relative mt-1">
                                    <input type="text" class="w-full px-4 py-3 pr-12 text-white bg-dark-100 rounded-md" value="{{ env($payment_mode->name) }}" readonly>
                                    <button @click="navigator.clipboard.writeText('{{ env($payment_mode->name) }}'); copied = true; setTimeout(() => copied = false, 2000)" class="absolute top-0 bottom-0 right-0 px-4 text-gray-400 hover:text-white">
                                        <span x-show="!copied"><i class="fas fa-copy"></i></span>
                                        <span x-show="copied" class="text-primary-400"><i class="fas fa-check"></i></span>
                                    </button>
                                </div>
                                @if($payment_mode->network)
                                <p class="mt-1 text-xs text-gray-500">Network: <span class="font-semibold">{{ $payment_mode->network }}</span></p>
                                @endif
                            </div>

                            {{-- Proof Upload Form --}}
                            <form method="post" action="{{ route('savedeposit') }}" enctype="multipart/form-data" class="pt-6 border-t border-dark-100">
                                @csrf
                                <h3 class="font-semibold text-white">Upload Proof of Payment</h3>
                                <p class="mt-1 mb-4 text-sm text-gray-400">After making the payment, upload a screenshot of the transaction.</p>
                                <input type="file" name="proof" class="w-full text-sm text-gray-300 border rounded-md file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-600 file:text-white hover:file:bg-primary-700 border-dark-100" required>
                                <input type="hidden" name="amount" value="{{ $amount }}">
                                <input type="hidden" name="paymethd_method" value="{{ $payment_mode->name }}">
                                <button type="submit" class="w-full px-6 py-3 mt-4 font-medium text-white transition-colors bg-primary-600 rounded-md hover:bg-primary-700">
                                    Submit Proof
                                </button>
                            </form>
                        </div>

                    @elseif ($payment_mode->methodtype == 'currency')
                        {{-- Fiat Gateways & Bank Transfer --}}
                        @if ($payment_mode->name == 'Credit Card' && $settings->credit_card_provider == 'Stripe')
                            {{-- Stripe Form --}}
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-white">Pay with Credit Card</h3>
                                <form id="payment-form">
                                    @csrf
                                    <div class="p-4 rounded-md bg-dark-100">
                                        <div id="card-element">
                                            <!-- A Stripe Element will be inserted here. -->
                                        </div>
                                    </div>
                                    <div id="card-errors" role="alert" class="mt-2 text-sm text-red-400"></div>
                                    <button id="stripesubmit" class="w-full px-6 py-3 mt-4 font-medium text-white transition-colors rounded-md bg-primary-600 hover:bg-primary-700 disabled:opacity-50" disabled>
                                        <div class="hidden spinner" id="spinner"></div>
                                        <span id="button-text">Pay {{ Auth::user()->currency }}{{ number_format($amount, 2) }}</span>
                                    </button>
                                </form>
                                <form id="selectform" method="POST" class="hidden">
                                    @csrf
                                    <input type="hidden" name="amount" value="{{ $amount }}">
                                </form>
                            </div>

                        @elseif ($payment_mode->name == 'Credit Card' && $settings->credit_card_provider == 'Paystack')
                            <div class="text-center">
                                <h3 class="mb-4 text-lg font-semibold text-white">Pay with Paystack</h3>
                                <form method="POST" action="{{ route('pay.paystack') }}" accept-charset="UTF-8">
                                    @csrf
                                    <input type="hidden" name="email" value="{{ Auth::user()->email }}">
                                    <input type="hidden" name="amount" value="{{ $amount * 100 }}">
                                    <input type="hidden" name="currency" value="{{ $settings->s_currency }}">
                                    <input type="hidden" name="reference" value="{{ Paystack::genTranxRef() }}">
                                    <button type="submit" class="inline-flex items-center px-6 py-3 font-medium text-white transition-colors rounded-md bg-primary-600 hover:bg-primary-700">
                                        <i class="mr-2 fas fa-credit-card"></i> Pay with Card
                                    </button>
                                </form>
                            </div>

                        @elseif ($payment_mode->name == 'Credit Card' && $settings->credit_card_provider == 'Flutterwave')
                             <div class="text-center">
                                <h3 class="mb-4 text-lg font-semibold text-white">Pay with Flutterwave</h3>
                                <form method="POST" action="{{ route('paybyflutterwave') }}">
                                    @csrf
                                    <input type="hidden" name="name" value="{{ Auth::user()->name }}" />
                                    <input name="email" type="hidden" value="{{ Auth::user()->email }}" />
                                    <input name="phone" type="hidden" value="{{ Auth::user()->phone }}" />
                                    <input name="amount" type="hidden" value="{{ $amount }}" />
                                    <button type="submit" class="inline-flex items-center px-6 py-3 font-medium text-white transition-colors rounded-md bg-primary-600 hover:bg-primary-700">
                                        <i class="mr-2 fas fa-credit-card"></i> Pay with Card
                                    </button>
                                </form>
                            </div>

                        @elseif ($payment_mode->name == 'Paypal')
                            <div class="text-center">
                                <h3 class="mb-4 text-lg font-semibold text-white">Pay with PayPal</h3>
                                @include('includes.paypal')
                            </div>

                        @elseif ($payment_mode->name == 'Bank Transfer')
                            {{-- Bank Transfer Details --}}
                            <div class="space-y-6">
                                <div>
                                    <h3 class="text-lg font-semibold text-white">Bank Transfer Details</h3>
                                    <p class="mt-1 text-gray-400">Use the details below to complete your payment.</p>
                                </div>
                                <div class="space-y-4">
                                    @if($payment_mode->bankname)
                                    <div class="flex items-center justify-between p-3 rounded-md bg-dark-100">
                                        <span class="text-sm text-gray-400">Bank Name</span>
                                        <span class="font-semibold text-white">{{ $payment_mode->bankname }}</span>
                                    </div>
                                    @endif
                                    @if($payment_mode->account_name)
                                    <div class="flex items-center justify-between p-3 rounded-md bg-dark-100">
                                        <span class="text-sm text-gray-400">Account Name</span>
                                        <span class="font-semibold text-white">{{ $payment_mode->account_name }}</span>
                                    </div>
                                    @endif
                                    @if($payment_mode->account_number)
                                    <div class="flex items-center justify-between p-3 rounded-md bg-dark-100">
                                        <span class="text-sm text-gray-400">Account Number</span>
                                        <span class="font-semibold text-white">{{ $payment_mode->account_number }}</span>
                                    </div>
                                    @endif
                                     @if($payment_mode->swift_code)
                                    <div class="flex items-center justify-between p-3 rounded-md bg-dark-100">
                                        <span class="text-sm text-gray-400">Swift Code</span>
                                        <span class="font-semibold text-white">{{ $payment_mode->swift_code }}</span>
                                    </div>
                                    @endif
                                </div>
                                {{-- Proof Upload Form --}}
                                <form method="post" action="{{ route('savedeposit') }}" enctype="multipart/form-data" class="pt-6 border-t border-dark-100">
                                    @csrf
                                    <h3 class="font-semibold text-white">Upload Proof of Payment</h3>
                                    <p class="mt-1 mb-4 text-sm text-gray-400">After making the payment, upload a screenshot of the transaction.</p>
                                    <input type="file" name="proof" class="w-full text-sm text-gray-300 border rounded-md file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary-600 file:text-white hover:file:bg-primary-700 border-dark-100" required>
                                    <input type="hidden" name="amount" value="{{ $amount }}">
                                    <input type="hidden" name="paymethd_method" value="{{ $payment_mode->name }}">
                                    <button type="submit" class="w-full px-6 py-3 mt-4 font-medium text-white transition-colors bg-primary-600 rounded-md hover:bg-primary-700">
                                        Submit Proof
                                    </button>
                                </form>
                            </div>
                        @endif
                    @endif
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
@if ($payment_mode->name == 'Credit Card' && $settings->credit_card_provider == 'Stripe' && $title != 'Complete Payment')
<script src="https://js.stripe.com/v3/"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const stripe = Stripe("{{ $settings->s_p_k }}");
        const elements = stripe.elements({
            fonts: [{
                cssSrc: 'https://fonts.googleapis.com/css?family=Inter:400,500,600,700',
            }, ]
        });

        const style = {
            base: {
                color: '#ffffff',
                fontFamily: '"Inter", sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '16px',
                '::placeholder': {
                    color: '#a0aec0'
                }
            },
            invalid: {
                color: '#ef4444',
                iconColor: '#ef4444'
            }
        };

        const card = elements.create('card', { style: style });
        card.mount('#card-element');

        const payBtn = document.getElementById('stripesubmit');
        const cardErrors = document.getElementById('card-errors');

        card.on('change', function(event) {
            if (event.error) {
                cardErrors.textContent = event.error.message;
                payBtn.disabled = true;
            } else {
                cardErrors.textContent = '';
                payBtn.disabled = false;
            }
        });

        const form = document.getElementById('payment-form');
        form.addEventListener('submit', function(ev) {
            ev.preventDefault();
            payBtn.disabled = true;

            document.getElementById('spinner').classList.remove('hidden');
            document.getElementById('button-text').classList.add('opacity-0');

            stripe.confirmCardPayment("{{ $intent }}", {
                payment_method: {
                    card: card,
                    billing_details: { name: "{{ Auth::user()->name }}" }
                }
            }).then(function(result) {
                if (result.error) {
                    cardErrors.textContent = result.error.message;
                    payBtn.disabled = false;
                    document.getElementById('spinner').classList.add('hidden');
                    document.getElementById('button-text').classList.remove('opacity-0');
                } else {
                    if (result.paymentIntent.status === 'succeeded') {
                        // Use fetch for modern JS
                        const formData = new FormData(document.getElementById('selectform'));
                        fetch("{{ url('/dashboard/submit-stripe-payment') }}", {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Payment Successful!',
                                text: data.success,
                                background: '#1e293b',
                                color: '#ffffff',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                            }).then(() => {
                                window.location.href = "{{ route('accounthistory') }}";
                            });
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'There was an issue confirming your payment.',
                                background: '#1e293b',
                                color: '#ffffff',
                            });
                            payBtn.disabled = false;
                            document.getElementById('spinner').classList.add('hidden');
                            document.getElementById('button-text').classList.remove('opacity-0');
                        });
                    }
                }
            });
        });
    });
</script>
@endif
@endpush
