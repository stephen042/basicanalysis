@extends('layouts.dash')
@section('title', $title)
@section('content')
    <div class="space-y-6" x-data="depositFlow()">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-primary">Account funding</p>
            <h1 class="mt-2 text-2xl font-bold text-white md:text-3xl">Fund Your Account</h1>
            <p class="mt-1 text-sm text-gray-400">Choose a cryptocurrency, then select its configured network and payment details.</p>
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

                    <section class="rounded-2xl border border-dark-100 bg-dark-200 p-5 sm:p-6">
                        <div class="mb-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-primary">Step 1</p>
                            <h2 class="mt-1 text-lg font-semibold text-white">Select a cryptocurrency</h2>
                            <p class="mt-1 text-sm text-gray-400">Only cryptocurrencies enabled by the administrator are available for deposits.</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                            <template x-for="asset in cryptoAssets" :key="asset.name">
                                <button type="button" @click="selectAsset(asset.name)"
                                    :aria-pressed="selectedAsset === asset.name"
                                    class="rounded-xl border p-4 text-left transition"
                                    :class="selectedAsset === asset.name ? 'border-primary bg-primary/10' : 'border-dark-100 bg-dark-100 hover:border-primary/50'">
                                     <span class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-dark-300 font-bold text-primary">
                                         <img x-show="asset.image && !asset.imageFailed" :src="asset.image" :alt="asset.name + ' logo'" x-on:error="asset.imageFailed = true" class="h-full w-full object-contain">
                                         <span x-show="!asset.image || asset.imageFailed" x-text="asset.ticker || '◆'" class="text-xs"></span>
                                     </span>
                                     <span class="mt-3 block font-semibold text-white" x-text="asset.name"></span>
                                     <span class="mt-1 block text-xs font-semibold text-primary" x-text="asset.ticker"></span>
                                    <span class="mt-1 block text-xs text-gray-400" x-text="asset.methods.length + (asset.methods.length === 1 ? ' network' : ' networks')"></span>
                                </button>
                            </template>
                        </div>
                        <p class="rounded-lg bg-dark-100 p-4 text-sm text-gray-400" x-show="cryptoAssets.length === 0">No enabled cryptocurrency deposit methods are configured.</p>
                    </section>

                    <section x-show="selectedAsset" x-cloak class="rounded-2xl border border-dark-100 bg-dark-200 p-5 sm:p-6">
                        <div class="mb-4">
                            <p class="text-xs font-semibold uppercase tracking-wider text-primary">Step 2</p>
                            <h2 class="mt-1 text-lg font-semibold text-white">Select the <span x-text="selectedAsset"></span> network</h2>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <template x-for="method in selectedAssetMethods" :key="method.id">
                                <button type="button"
                                    @click="selectMethod(method.id)"
                                    :aria-pressed="selectedMethodId === String(method.id)"
                                    class="flex min-h-20 items-center gap-3 rounded-xl border p-4 text-left transition"
                                    :class="selectedMethodId === String(method.id) ? 'border-primary bg-primary/10' : 'border-dark-100 bg-dark-100 hover:border-primary/50'">
                                     <span class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-dark-300 text-primary">
                                         <img x-show="method.image && !method.imageFailed" :src="method.image" :alt="selectedAsset + ' logo'" x-on:error="method.imageFailed = true" class="h-full w-full object-contain">
                                         <i x-show="!method.image || method.imageFailed" class="fas fa-coins" aria-hidden="true"></i>
                                     </span>
                                     <span class="min-w-0 flex-1">
                                         <span class="block font-semibold text-white" x-text="selectedAsset + ' (' + method.symbol + ')'"></span>
                                         <span class="mt-1 block text-xs text-gray-400" x-text="method.network || 'Default network'"></span>
                                    </span>
                                    <i x-show="selectedMethodId === String(method.id)" class="fas fa-check-circle text-primary" aria-hidden="true"></i>
                                </button>
                            </template>
                        </div>
                    </section>

                    <section x-show="selectedMethod" x-cloak class="rounded-2xl border border-dark-100 bg-dark-200 p-5 sm:p-6">
                        <p class="text-xs font-semibold uppercase tracking-wider text-primary">Step 3</p>
                        <h2 class="mt-1 text-lg font-semibold text-white">Payment details</h2>
                        <template x-if="selectedMethod">
                            <div class="mt-4 space-y-5">
                                <div class="flex items-center gap-3">
                                     <span class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-dark-300 text-primary">
                                         <img x-show="selectedMethod.image && !selectedMethod.imageFailed" :src="selectedMethod.image" :alt="selectedMethod.name + ' logo'" x-on:error="selectedMethod.imageFailed = true" class="h-full w-full object-contain">
                                         <i x-show="!selectedMethod.image || selectedMethod.imageFailed" class="fas fa-coins" aria-hidden="true"></i>
                                     </span>
                                     <div>
                                         <p class="font-semibold text-white" x-text="selectedMethod.name"></p>
                                         <p class="text-xs font-semibold text-primary" x-text="selectedMethod.symbol"></p>
                                        <p class="text-xs text-gray-400" x-text="selectedMethod.network ? 'Network: ' + selectedMethod.network : 'Cryptocurrency payment'"></p>
                                    </div>
                                </div>

                                <template x-if="true">
                                    <div class="space-y-4">
                                        <div x-show="selectedQr" class="flex justify-center rounded-xl bg-white p-4 sm:justify-start">
                                            <img :src="selectedQr" :alt="'QR code for ' + selectedMethod.name" class="h-48 w-48 object-contain">
                                        </div>
                                        <div>
                                            <div class="flex items-center justify-between gap-3">
                                                <p class="text-xs font-medium uppercase tracking-wide text-gray-500">Wallet address</p>
                                                <button type="button"
                                                    x-show="selectedMethod.address"
                                                    @click="copyAddress()"
                                                    class="inline-flex shrink-0 items-center gap-1.5 rounded-md border border-dark-100 bg-dark-100 px-2.5 py-1 text-xs font-semibold text-gray-300 transition hover:border-primary/50 hover:text-primary"
                                                    :class="copied ? 'text-primary border-primary/50' : ''">
                                                    <span x-show="!copied"><i class="fas fa-copy"></i> Copy</span>
                                                    <span x-show="copied" x-cloak><i class="fas fa-check"></i> Copied!</span>
                                                </button>
                                            </div>
                                            <code class="mt-2 block break-all rounded-lg border border-dark-100 bg-dark-300 p-3 text-sm text-primary" x-text="selectedMethod.address || 'No wallet address is configured. Contact support before sending funds.'"></code>
                                        </div>
                                    </div>
                                </template>

                                <p x-show="selectedMethod.instructions" class="whitespace-pre-line rounded-lg bg-dark-100 p-4 text-sm leading-6 text-gray-300" x-text="selectedMethod.instructions"></p>

                                <div class="border-t border-dark-100 pt-5">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-primary">Step 4</p>
                                    <label for="amount" class="mt-2 block text-sm font-medium text-gray-300">Deposit amount (USD)</label>
                                    <input type="number" name="amount" id="amount" x-model="amount"
                                        min="{{ $moresettings->minamt }}" step="0.01" required
                                        class="mt-2 w-full rounded-lg border border-dark-100 bg-dark-300 px-4 py-3 text-white focus:border-primary focus:outline-none"
                                        placeholder="0.00">
                                    <p class="mt-1 text-xs text-gray-500">Minimum deposit: ${{ $moresettings->minamt }}</p>
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
        $defaultDepositAsset = $oldDepositMethod ? $oldDepositMethod->name : '';
        $defaultDepositMethodId = $oldDepositMethod ? (string) $oldDepositMethod->id : '';
    @endphp
    <script>
        const depositMethodDetails = @json($depositMethodDetails);

        function depositFlow() {
            return {
                cryptoAssets: @json($cryptoAssets),
                methodDetails: depositMethodDetails,
                selectedAsset: @json($defaultDepositAsset),
                selectedMethodId: @json($defaultDepositMethodId),
                amount: @json(old('amount', $moresettings->minamt)),
                copied: false,
                get selectedAssetMethods() {
                    const asset = this.cryptoAssets.find(item => item.name === this.selectedAsset);
                    return asset ? asset.methods : [];
                },
                get selectedMethod() {
                    return this.selectedMethodId ? this.methodDetails[String(this.selectedMethodId)] || null : null;
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
                selectAsset(asset) {
                    this.selectedAsset = asset;
                    this.selectedMethodId = '';
                },
                selectMethod(id) {
                    this.selectedMethodId = String(id);
                    this.copied = false;
                    const method = this.selectedMethod;
                    if (method) method.imageFailed = false;
                },
                copyAddress() {
                    const address = this.selectedMethod && this.selectedMethod.address;
                    if (!address) return;

                    const done = () => {
                        this.copied = true;
                        setTimeout(() => { this.copied = false; }, 2000);
                    };

                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(address).then(done).catch(() => this.legacyCopy(address, done));
                    } else {
                        this.legacyCopy(address, done);
                    }
                },
                legacyCopy(text, done) {
                    const ta = document.createElement('textarea');
                    ta.value = text;
                    ta.setAttribute('readonly', '');
                    ta.style.position = 'fixed';
                    ta.style.opacity = '0';
                    document.body.appendChild(ta);
                    ta.focus();
                    ta.select();
                    try {
                        document.execCommand('copy');
                        done();
                    } catch (e) {
                        // Copy not supported; leave the address visible to select manually.
                    }
                    document.body.removeChild(ta);
                },
            };
        }
    </script>
@endsection
