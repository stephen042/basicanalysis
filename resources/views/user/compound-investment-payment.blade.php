@extends('layouts.dash')
@section('title', $title)
@section('content')
@php
    $payment = isset($paymentMethods[$selectedMethodId]) ? $paymentMethods[$selectedMethodId] : [];
    $paymentStatus = $investment->deposit_status === 'approved'
        ? 'Approved'
        : ($investment->deposit_status === 'rejected'
            ? 'Rejected'
            : ($investment->payment_proof ? 'Awaiting Admin Approval' : 'Pending Payment'));
    $initialQr = !empty($payment['barcode'])
        ? $payment['barcode']
        : (!empty($payment['address']) ? 'https://api.qrserver.com/v1/create-qr-code/?size=320x320&data=' . urlencode($payment['address']) : null);
@endphp
<div class="mx-auto max-w-5xl space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-primary">Investment #{{ $investment->id }}</p>
            <h1 class="mt-1 text-2xl font-bold text-white">Complete your Direct Deposit</h1>
            <p class="mt-1 text-sm text-gray-400">Your investment is denominated in USD. Send the equivalent amount in USDT using the selected network below.</p>
        </div>
        <a href="{{ route('compound-investment.show', $investment) }}" class="rounded-lg border border-dark-100 px-4 py-2 text-sm text-gray-200">Investment details</a>
    </div>
    <x-danger-alert /><x-success-alert />
    @if($errors->any())<div class="rounded-lg bg-red-500/10 p-3 text-sm text-red-300">{{ $errors->first() }}</div>@endif

    <div class="grid gap-6 lg:grid-cols-5">
        <section class="space-y-6 lg:col-span-3">
            <div class="rounded-2xl border border-dark-100 bg-dark-200 p-5 md:p-7">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-gray-400">Investment amount (USD)</p>
                        <p class="mt-1 text-3xl font-black text-white">{{ $currency }}{{ number_format($investment->amount, 2) }}</p>
                        <p class="mt-2 text-sm text-gray-400">Payment method: <span class="font-semibold text-white">{{ $investment->payment_method ?: 'USDT' }}</span></p>
                    </div>
                    <span class="rounded-full bg-yellow-500/10 px-3 py-1 text-xs font-semibold text-yellow-300">{{ $paymentStatus }}</span>
                </div>
                <div class="mt-5 grid gap-3 border-t border-dark-100 pt-5 sm:grid-cols-2">
                    <div><p class="text-xs uppercase tracking-wide text-gray-500">Duration</p><p class="mt-1 font-semibold text-white">{{ $investment->duration_months }} months</p></div>
                    <div><p class="text-xs uppercase tracking-wide text-gray-500">Monthly return</p><p class="mt-1 font-semibold text-white">{{ number_format($investment->monthly_return, 2) }}%</p></div>
                    <div><p class="text-xs uppercase tracking-wide text-gray-500">Performance rate</p><p class="mt-1 font-semibold text-white">{{ number_format($investment->performance_rate, 2) }}%</p></div>
                    <div><p class="text-xs uppercase tracking-wide text-gray-500">Recurring top-up</p><p class="mt-1 font-semibold text-white">{{ $currency }}{{ number_format($investment->recurring_amount, 2) }} / {{ ucfirst($investment->recurring_frequency) }}</p></div>
                </div>
            </div>

            <div class="rounded-2xl border border-dark-100 bg-dark-200 p-5 md:p-7">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-white">1. Select a payment method</h2>
                    <p class="mt-1 text-sm text-gray-400">USDT is available as a Direct Deposit payment option; it does not change your USD investment amount.</p>
                </div>
                @if($legacyUnsupportedProof)
                    <div class="rounded-lg bg-red-500/10 p-4 text-sm text-red-300">
                        This existing receipt was submitted using a method that is not accepted for compound investments. Only an enabled USDT deposit method can be approved. Contact support for assistance.
                    </div>
                @elseif(empty($paymentMethods))
                    <div class="rounded-lg bg-red-500/10 p-4 text-sm text-red-300">No enabled USDT payment method is configured. Please contact support.</div>
                @else
                    @if($investment->payment_proof)
                        <div class="rounded-lg bg-blue-500/10 p-4 text-sm text-blue-200">
                            Payment method locked as <b>{{ $investment->payment_method }}</b> because your receipt has been submitted. It cannot be changed while admin review is in progress.
                        </div>
                    @endif
                    <div id="compound-payment-methods" data-selected="{{ $selectedMethodId }}" class="grid gap-3 sm:grid-cols-2">
                        @foreach($paymentMethods as $methodId => $method)
                            <label class="relative {{ $investment->payment_proof ? 'cursor-not-allowed opacity-70' : 'cursor-pointer' }}">
                                <input class="peer sr-only" type="radio" name="payment-method-choice" value="{{ $methodId }}"
                                    {{ (string) $selectedMethodId === (string) $methodId ? 'checked' : '' }}
                                    {{ $investment->payment_proof ? 'disabled' : '' }}>
                                <span class="flex min-h-20 items-center gap-3 rounded-xl border border-dark-100 bg-dark-100 p-4 transition peer-checked:border-primary peer-checked:bg-primary/10">
                                    @if(!empty($method['image']))
                                        <img src="{{ $method['image'] }}" alt="" class="h-10 w-10 rounded-full object-contain">
                                    @else
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-dark-300 text-sm font-bold text-primary">{{ $method['symbol'] }}</span>
                                    @endif
                                    <span class="min-w-0">
                                        <span class="block font-semibold text-white">{{ $method['name'] }}</span>
                                        <span class="mt-1 block text-xs text-gray-400">{{ $method['type'] }}{{ $method['network'] ? ' · ' . $method['network'] : '' }}</span>
                                    </span>
                                    <span class="ml-auto text-primary"><i class="fas fa-check-circle"></i></span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="rounded-2xl border border-dark-100 bg-dark-200 p-5 md:p-7">
                <h2 class="text-lg font-semibold text-white">2. Payment details</h2>
                <div id="compound-payment-details" class="mt-4" data-selected="{{ $selectedMethodId }}">
                    <h3 id="payment-method-title" class="font-semibold text-white">{{ $payment['name'] ?? $investment->payment_method }}</h3>
                    <div id="payment-admin-instructions" class="mt-3 rounded-lg bg-dark-100 p-4 text-sm leading-6 text-gray-200 whitespace-pre-line">{{ $payment['instructions'] ?? 'Follow the payment details for the selected method.' }}</div>
                    <div id="payment-network" class="mt-3 text-sm text-gray-300">{{ !empty($payment['network']) ? 'Network: ' . $payment['network'] : '' }}</div>
                    <div id="payment-bank-details" class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div data-payment-field="bank" class="{{ empty($payment['bank']) ? 'hidden' : '' }}"><span class="text-xs text-gray-500">Bank</span><p id="payment-bank" class="mt-1 text-sm text-white">{{ $payment['bank'] ?? '' }}</p></div>
                        <div data-payment-field="account_name" class="{{ empty($payment['account_name']) ? 'hidden' : '' }}"><span class="text-xs text-gray-500">Account name</span><p id="payment-account-name" class="mt-1 text-sm text-white">{{ $payment['account_name'] ?? '' }}</p></div>
                        <div data-payment-field="account_number" class="{{ empty($payment['account_number']) ? 'hidden' : '' }}"><span class="text-xs text-gray-500">Account number</span><p id="payment-account-number" class="mt-1 break-all text-sm text-white">{{ $payment['account_number'] ?? '' }}</p></div>
                        <div data-payment-field="swift" class="{{ empty($payment['swift']) ? 'hidden' : '' }}"><span class="text-xs text-gray-500">SWIFT / other code</span><p id="payment-swift" class="mt-1 text-sm text-white">{{ $payment['swift'] ?? '' }}</p></div>
                    </div>
                    <div id="payment-details-box" class="mt-5 rounded-xl border border-dark-100 bg-dark-100 p-5">
                        <div id="payment-qr-wrap" class="{{ $initialQr ? '' : 'hidden' }} mb-4 flex justify-center sm:justify-start">
                            <div class="shrink-0 rounded-xl bg-white p-3 shadow-lg">
                                <img id="payment-qr" src="{{ $initialQr }}" width="256" height="256" alt="QR code for selected payment method" class="h-56 w-56 max-w-full object-contain sm:h-64 sm:w-64">
                            </div>
                        </div>
                        <p id="payment-details-label" class="font-semibold text-white">{{ !empty($payment['address']) ? 'Wallet address' : 'Payment account details' }}</p>
                        <code id="payment-wallet-address" class="mt-2 block break-all whitespace-pre-line rounded-lg border border-dark-100 bg-dark-300 p-3 text-sm leading-6 text-primary">{{ $payment['address'] ?? ($payment['account_number'] ?? '') }}</code>
                        <button type="button" id="copy-compound-address" class="mt-3 inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-bold text-white hover:bg-primary-dark">
                            <i class="fas fa-copy"></i><span id="copy-address-label">{{ !empty($payment['address']) ? 'Copy wallet address' : 'Copy payment details' }}</span>
                        </button>
                        <p id="copy-compound-feedback" class="mt-2 min-h-5 text-xs text-green-400" aria-live="polite"></p>
                    </div>
                </div>
            </div>
        </section>

        <aside class="space-y-6 lg:col-span-2">
            <div class="rounded-2xl border border-dark-100 bg-dark-200 p-5">
                <h2 class="text-lg font-semibold text-white">3. Confirm your payment</h2>
                @if($investment->payment_proof)
                    <div class="mt-3 rounded-lg bg-blue-500/10 p-4 text-sm text-blue-200">
                        Receipt submitted using <b>{{ $investment->payment_method }}</b>. Your request is awaiting admin review and remains inactive until approved.
                    </div>
                    <a target="_blank" rel="noopener" href="{{ \Illuminate\Support\Facades\Storage::url($investment->payment_proof) }}" class="mt-3 inline-block text-sm text-primary underline">View submitted receipt</a>
                @elseif(!empty($paymentMethods))
                    <p class="mt-2 text-sm leading-6 text-gray-400">After sending the exact amount using the selected method, upload your payment receipt. The selected method will be saved with your request for admin review.</p>
                    <form method="POST" action="{{ route('compound-investment.payment-proof', $investment) }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                        @csrf
                        <input type="hidden" name="payment_method_id" id="selected-payment-method" value="{{ $selectedMethodId }}">
                        <input name="payment_proof" type="file" accept="image/*" required class="block w-full text-sm text-gray-300 file:mr-3 file:rounded-md file:border-0 file:bg-primary file:px-4 file:py-2 file:font-semibold file:text-white">
                        <button class="w-full rounded-lg bg-primary px-4 py-3 font-bold text-white hover:bg-primary-dark">Submit receipt for approval</button>
                    </form>
                @endif
            </div>
            <div class="rounded-2xl border border-yellow-500/20 bg-yellow-500/5 p-5 text-sm leading-6 text-gray-300">
                <h3 class="font-semibold text-yellow-300">Important</h3>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    <li>Check the selected currency/network before sending funds.</li>
                    <li>Your request remains pending until an admin verifies payment.</li>
                    <li>Only admin-approved recurring top-ups are credited as earnings.</li>
                </ul>
            </div>
        </aside>
    </div>
</div>
@endsection
@section('scripts')
@parent
<script>
(function () {
    var container = document.getElementById('compound-payment-methods');
    var selectedMethodField = document.getElementById('selected-payment-method');
    if (!container) return;
    var methods = @json($paymentMethods);
    var radios = container.querySelectorAll('input[name="payment-method-choice"]');

    function updatePaymentDetails(methodId) {
        var method = methods[String(methodId)] || {};
        if (selectedMethodField) selectedMethodField.value = methodId;
        document.getElementById('payment-method-title').textContent = method.name || 'Payment method';
        document.getElementById('payment-admin-instructions').textContent = method.instructions || 'Follow the configured payment details for this method.';
        document.getElementById('payment-network').textContent = method.network ? 'Network: ' + method.network : '';

        var fields = {
            bank: ['payment-bank', method.bank],
            account_name: ['payment-account-name', method.account_name],
            account_number: ['payment-account-number', method.account_number],
            swift: ['payment-swift', method.swift]
        };
        Object.keys(fields).forEach(function (key) {
            var item = fields[key];
            document.getElementById(item[0]).textContent = item[1] || '';
            var wrapper = container.parentElement.parentElement.querySelector('[data-payment-field="' + key + '"]');
            if (wrapper) wrapper.classList.toggle('hidden', !item[1]);
        });

        var qrWrap = document.getElementById('payment-qr-wrap');
        var qr = document.getElementById('payment-qr');
        var address = method.address || '';
        var bankDetails = [
            method.bank ? 'Bank: ' + method.bank : '',
            method.account_name ? 'Account name: ' + method.account_name : '',
            method.account_number ? 'Account number: ' + method.account_number : '',
            method.swift ? 'SWIFT / code: ' + method.swift : ''
        ].filter(Boolean).join('\n');
        var copyableDetails = address || bankDetails;
        var addressQr = address
            ? 'https://api.qrserver.com/v1/create-qr-code/?size=320x320&data=' + encodeURIComponent(address)
            : '';
        var qrSource = method.barcode || addressQr;
        qrWrap.classList.toggle('hidden', !qrSource);
        qr.onerror = function () {
            if (method.barcode && addressQr && qr.src !== addressQr) {
                qr.src = addressQr;
            } else {
                qrWrap.classList.add('hidden');
            }
        };
        if (qrSource) qr.src = qrSource;
        document.getElementById('payment-details-label').textContent = address ? 'Wallet address' : 'Payment account details';
        document.getElementById('payment-wallet-address').textContent = copyableDetails || 'No wallet address or account details configured for this method.';
        document.getElementById('copy-address-label').textContent = address ? 'Copy wallet address' : 'Copy payment details';
    }

    radios.forEach(function (radio) {
        radio.addEventListener('change', function () {
            if (radio.checked) updatePaymentDetails(radio.value);
        });
    });

    var copyButton = document.getElementById('copy-compound-address');
    if (copyButton) {
        copyButton.addEventListener('click', function () {
            var method = methods[String(selectedMethodField ? selectedMethodField.value : container.getAttribute('data-selected'))] || {};
            var bankDetails = [
                method.bank ? 'Bank: ' + method.bank : '',
                method.account_name ? 'Account name: ' + method.account_name : '',
                method.account_number ? 'Account number: ' + method.account_number : '',
                method.swift ? 'SWIFT / code: ' + method.swift : ''
            ].filter(Boolean).join('\n');
            var text = method.address || bankDetails;
            var feedback = document.getElementById('copy-compound-feedback');
            function copied() { feedback.textContent = 'Payment details copied.'; }
            if (!text) { feedback.textContent = 'No copyable address or account number is configured.'; return; }
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(copied).catch(function () { feedback.textContent = 'Copy failed. Select the details and copy them manually.'; });
            } else {
                var field = document.createElement('textarea'); field.value = text; field.style.position = 'fixed'; field.style.opacity = '0';
                document.body.appendChild(field); field.select();
                try { document.execCommand('copy'); copied(); } catch (error) { feedback.textContent = 'Copy failed. Select the details and copy them manually.'; }
                document.body.removeChild(field);
            }
        });
    }

    var checked = container.querySelector('input[name="payment-method-choice"]:checked');
    if (checked) updatePaymentDetails(checked.value);
})();
</script>
@endsection
