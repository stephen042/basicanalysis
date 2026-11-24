@extends('layouts.dash')
@section('title', $title)
@section('content')
    <div class="space-y-6" x-data="depositFlow()">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-primary">Account funding</p>
            <h1 class="mt-2 text-2xl font-bold text-white md:text-3xl">Fund Your Account</h1>
            <p class="mt-1 text-sm text-gray-400">Choose a deposit category first, then select an admin-configured payment method.</p>
        </div>

        <x-danger-alert />
        <x-success-alert />
        @if($errors->any())
            <div class="rounded-lg border border-red-500/20 bg-red-500/10 p-4 text-sm text-red-300">{{ $errors->first() }}</div>
        @endif

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            <div class="space-y-6 lg:col-span-8">
                <form action="{{ route('newdeposit') }}" method="post" id="submitpaymentform" class="space-y-6">
                    @csrf
                    <input type="hidden" name="payment_method_id" :value="selectedMethodId">
                    <input type="hidden" name="deposit_category" :value="selectedCategory">

                    <section class="rounded-2xl border border-dark-100 bg-dark-200 p-5 sm:p-6">
                        <div class="mb-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-primary">Step 1</p>
                            <h2 class="mt-1 text-lg font-semibold text-white">Choose deposit type</h2>
                            <p class="mt-1 text-sm text-gray-400">Select fiat/currency or cryptocurrency to see the available configured methods.</p>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <button type="button"
                                @click="chooseCategory('currency')"
                                :aria-pressed="selectedCategory === 'currency'"
                                class="flex min-h-24 items-center gap-4 rounded-xl border p-4 text-left transition"
                                :class="selectedCategory === 'currency' ? 'border-primary bg-primary/10' : 'border-dark-100 bg-dark-100 hover:border-primary/50'">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                    <i class="fas fa-money-bill-transfer text-xl"></i>
                                </span>
                                <span>
                                    <span class="block font-semibold text-white">Currency / Fiat</span>
                                    <span class="mt-1 block text-xs text-gray-400">Bank transfer and supported fiat methods</span>
                                </span>
                            </button>
                            <button type="button"
                                @click="chooseCategory('crypto')"
                                :aria-pressed="selectedCategory === 'crypto'"
                                class="flex min-h-24 items-center gap-4 rounded-xl border p-4 text-left transition"
                                :class="selectedCategory === 'crypto' ? 'border-primary bg-primary/10' : 'border-dark-100 bg-dark-100 hover:border-primary/50'">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                    <i class="fab fa-bitcoin text-xl"></i>
                                </span>
                                <span>
                                    <span class="block font-semibold text-white">Cryptocurrency / Crypto</span>
                                    <span class="mt-1 block text-xs text-gray-400">Admin-configured coins, wallets and networks</span>
                                </span>
                            </button>
                        </div>
                    </section>

                    <section x-show="selectedCategory" x-cloak class="rounded-2xl border border-dark-100 bg-dark-200 p-5 sm:p-6">
                        <div class="mb-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-primary">Step 2</p>
                            <h2 class="mt-1 text-lg font-semibold text-white">Select a payment method</h2>
                        </div>

                        <div x-show="selectedCategory === 'currency'" class="grid gap-3 sm:grid-cols-2">
                            @forelse($fiatMethods as $method)
                                <button type="button"
                                    @click="selectMethod('{{ $method->id }}')"
                                    :aria-pressed="selectedMethodId === '{{ $method->id }}'"
                                    class="flex min-h-20 items-center gap-3 rounded-xl border p-4 text-left transition"
                                    :class="selectedMethodId === '{{ $method->id }}' ? 'border-primary bg-primary/10' : 'border-dark-100 bg-dark-100 hover:border-primary/50'">
                                    @if($method->img_url)
                                        <img src="{{ $method->img_url }}" alt="" class="h-10 w-10 shrink-0 rounded-full object-contain">
                                    @else
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-dark-300 text-primary"><i class="fas fa-building-columns"></i></span>
                                    @endif
                                    <span class="min-w-0 flex-1">
                                        <span class="block font-semibold text-white">{{ $method->name }}</span>
                                        <span class="mt-1 block text-xs text-gray-400">{{ $method->network ?: ucfirst($method->methodtype) }}</span>
                                    </span>
                                    <i x-show="selectedMethodId === '{{ $method->id }}'" class="fas fa-check-circle text-primary" aria-hidden="true"></i>
                                </button>
                            @empty
                                <p class="rounded-lg bg-dark-100 p-4 text-sm text-gray-400 sm:col-span-2">No enabled fiat deposit methods are configured.</p>
                            @endforelse
                        </div>

                        <div x-show="selectedCategory === 'crypto'" x-cloak class="grid gap-3 sm:grid-cols-2">
                            @forelse($cryptoMethods as $method)
                                <button type="button"
                                    @click="selectMethod('{{ $method->id }}')"
                                    :aria-pressed="selectedMethodId === '{{ $method->id }}'"
                                    class="flex min-h-20 items-center gap-3 rounded-xl border p-4 text-left transition"
                                    :class="selectedMethodId === '{{ $method->id }}' ? 'border-primary bg-primary/10' : 'border-dark-100 bg-dark-100 hover:border-primary/50'">
                                    @if($method->img_url)
                                        <img src="{{ $method->img_url }}" alt="" class="h-10 w-10 shrink-0 rounded-full object-contain">
                                    @else
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-dark-300 text-primary"><i class="fab fa-bitcoin"></i></span>
                                    @endif
                                    <span class="min-w-0 flex-1">
                                        <span class="block font-semibold text-white">{{ $method->name }}</span>
                                        <span class="mt-1 block text-xs text-gray-400">{{ $method->network ?: 'Network shown in payment details' }}</span>
                                    </span>
                                    <i x-show="selectedMethodId === '{{ $method->id }}'" class="fas fa-check-circle text-primary" aria-hidden="true"></i>
                                </button>
                            @empty
                                <p class="rounded-lg bg-dark-100 p-4 text-sm text-gray-400 sm:col-span-2">No enabled cryptocurrency deposit methods are configured.</p>
                            @endforelse
                        </div>
                    </section>

                    <section x-show="selectedMethod" x-cloak class="rounded-2xl border border-dark-100 bg-dark-200 p-5 sm:p-6">
                        <p class="text-xs font-semibold uppercase tracking-wider text-primary">Step 3</p>
                        <h2 class="mt-1 text-lg font-semibold text-white">Payment details</h2>
                        <template x-if="selectedMethod">
                            <div class="mt-4 space-y-5">
                                <div class="flex items-center gap-3">
                                    <template x-if="selectedMethod.image">
                                        <img :src="selectedMethod.image" :alt="selectedMethod.name" class="h-10 w-10 rounded-full object-contain">
                                    </template>
                                    <div>
                                        <p class="font-semibold text-white" x-text="selectedMethod.name"></p>
                                        <p class="text-xs text-gray-400" x-text="selectedMethod.network ? 'Network: ' + selectedMethod.network : (selectedMethod.methodtype === 'currency' ? 'Fiat / currency payment' : 'Cryptocurrency payment')"></p>
                                    </div>
                                </div>

                                <template x-if="selectedMethod.methodtype !== 'currency'">
                                    <div class="space-y-4">
                                        <div x-show="selectedQr" class="flex justify-center rounded-xl bg-white p-4 sm:justify-start">
                                            <img :src="selectedQr" :alt="'QR code for ' + selectedMethod.name" class="h-48 w-48 object-contain">
                                        </div>
                                        <div>
                                            <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Wallet address</p>
                                            <code class="mt-2 block break-all rounded-lg border border-dark-100 bg-dark-300 p-3 text-sm text-primary" x-text="selectedMethod.address || 'No wallet address is configured. Contact support before sending funds.'"></code>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="selectedMethod.methodtype === 'currency'">
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div x-show="selectedMethod.bank" class="rounded-lg bg-dark-100 p-3"><p class="text-xs text-gray-500">Bank</p><p class="mt-1 break-all text-sm font-medium text-white" x-text="selectedMethod.bank"></p></div>
                                        <div x-show="selectedMethod.account_name" class="rounded-lg bg-dark-100 p-3"><p class="text-xs text-gray-500">Account name</p><p class="mt-1 break-all text-sm font-medium text-white" x-text="selectedMethod.account_name"></p></div>
                                        <div x-show="selectedMethod.account_number" class="rounded-lg bg-dark-100 p-3"><p class="text-xs text-gray-500">Account number</p><p class="mt-1 break-all text-sm font-medium text-white" x-text="selectedMethod.account_number"></p></div>
                                        <div x-show="selectedMethod.swift" class="rounded-lg bg-dark-100 p-3"><p class="text-xs text-gray-500">SWIFT / code</p><p class="mt-1 break-all text-sm font-medium text-white" x-text="selectedMethod.swift"></p></div>
                                        <p x-show="!selectedMethod.bank && !selectedMethod.account_name && !selectedMethod.account_number && !selectedMethod.swift" class="text-sm text-gray-400 sm:col-span-2">Follow the configured instructions for this payment method.</p>
                                    </div>
                                </template>

                                <p x-show="selectedMethod.instructions" class="whitespace-pre-line rounded-lg bg-dark-100 p-4 text-sm leading-6 text-gray-300" x-text="selectedMethod.instructions"></p>

                                <div class="border-t border-dark-100 pt-5">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-primary">Step 4</p>
                                    <label for="amount" class="mt-2 block text-sm font-medium text-gray-300">Deposit amount ({{ Auth::user()->currency }})</label>
                                    <input type="number" name="amount" id="amount" x-model="amount"
                                        min="{{ $moresettings->minamt }}" step="0.01" required
                                        class="mt-2 w-full rounded-lg border border-dark-100 bg-dark-300 px-4 py-3 text-white focus:border-primary focus:outline-none"
                                        placeholder="0.00">
                                    <p class="mt-1 text-xs text-gray-500">Minimum deposit: {{ Auth::user()->currency }}{{ $moresettings->minamt }}</p>
                                    <button type="submit" :disabled="!selectedMethodId || !amount"
                                        class="mt-5 w-full rounded-lg bg-primary px-6 py-3 font-semibold text-white transition hover:bg-primary-dark disabled:cursor-not-allowed disabled:opacity-50">
                                        Continue to payment
                                    </button>
                                </div>
                            </div>
                        </template>
                    </section>
                </form>
            </div>

            <aside class="space-y-6 lg:col-span-4">
                <section class="rounded-2xl border border-dark-100 bg-dark-200 p-5 sm:p-6">
                    <h2 class="text-lg font-semibold text-white">Deposit summary</h2>
                    <div class="mt-4 flex items-center justify-between border-b border-dark-100 pb-4">
                        <p class="text-sm text-gray-400">Total deposited</p>
                        <p class="text-xl font-bold text-white">{{ Auth::user()->currency }}{{ number_format($deposited, 2, '.', ',') }}</p>
                    </div>
                    <a href="{{ route('accounthistory') }}" class="mt-4 flex items-center justify-between text-sm font-medium text-primary hover:text-primary-light">
                        <span>View deposit history</span><i class="fas fa-arrow-right"></i>
                    </a>
                </section>
                <p class="rounded-xl border border-dark-100 bg-dark-200 p-4 text-xs leading-5 text-gray-400">
                    Payment methods, wallet addresses, networks, and instructions are loaded from the administrator’s enabled deposit configuration. Always verify the displayed network before sending crypto.
                </p>
            </aside>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    @php
        $oldDepositMethod = $dmethods->firstWhere('id', (int) old('payment_method_id', 0));
        $defaultDepositCategory = $oldDepositMethod
            ? (strtolower(trim((string) $oldDepositMethod->methodtype)) === 'currency' ? 'currency' : 'crypto')
            : '';
        $defaultDepositMethodId = $oldDepositMethod ? (string) $oldDepositMethod->id : '';
    @endphp
    <script>
        const depositMethodDetails = @json($depositMethodDetails);

        function depositFlow() {
            return {
                selectedCategory: @json($defaultDepositCategory),
                selectedMethodId: @json($defaultDepositMethodId),
                amount: @json(old('amount', $moresettings->minamt)),
                get selectedMethod() {
                    return this.selectedMethodId ? depositMethodDetails[String(this.selectedMethodId)] || null : null;
                },
                get selectedQr() {
                    if (!this.selectedMethod) return '';
                    if (this.selectedMethod.barcode) return this.selectedMethod.barcode;
                    if (this.selectedMethod.address) {
                        return 'https://api.qrserver.com/v1/create-qr-code/?size=320x320&data='
                            + encodeURIComponent(this.selectedMethod.address);
                    }
                    return '';
                },
                chooseCategory(category) {
                    this.selectedCategory = category;
                    this.selectedMethodId = '';
                },
                selectMethod(id) {
                    this.selectedMethodId = String(id);
                },
            };
        }
    </script>
@endsection
