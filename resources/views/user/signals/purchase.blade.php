@extends('layouts.dash')
@section('title', $title)
@section('content')
    <a href="javascript:history.back()"
        style="color: #e3e3e3; text-decoration: none; font-size: 12px; font-weight: 500; margin-top: 5px; margin-bottom: 15px; display: inline-block;">
        ← Go Back
    </a>
    <div
        style="display: flex; justify-content: center; align-items: center; min-height: 80vh; padding: 15px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
        <div style="width: 100%; max-width: 560px;">
            <div
                style="background: #1a1a1a; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 15px 35px rgba(0,0,0,0.5); overflow: hidden;">
                <div style="padding: 30px 25px; text-align: center;">

                    {{-- Plan Details Block --}}
                    <div
                        style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 18px; margin-bottom: 20px;">
                        <div
                            style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 10px; margin-bottom: 12px;">
                            <div style="text-align: left;">
                                <span
                                    style="color: #888; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; display: block;">Signal
                                    Name</span>
                                <span style="color: #ffffff; font-size: 13px; font-weight: 700;">{{ $signal->name }}</span>
                            </div>
                            <div style="text-align: right;">
                                <span
                                    style="color: #888; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; display: block;">Duration</span>
                                <span style="color: #3b82f6; font-size: 13px; font-weight: 700;">{{ $signal->duration }}
                                    Days</span>
                            </div>
                        </div>
                        <span
                            style="color: #888; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 4px;">Total
                            Amount</span>
                        <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <span
                                style="color: #4d88ff; font-size: 28px; font-weight: 800; line-height: 1;">{{ $settings->currency }}{{ number_format($signal->amount, 2) }}</span>
                        </div>
                    </div>

                    {{-- Payment Method Selection --}}
                    <div style="text-align: left; margin-bottom: 20px;">
                        <label
                            style="color: #888; font-size: 10px; margin-left: 5px; margin-bottom: 6px; display: block;">Select
                            Payment Method</label>
                        <select id="methodSelector" onchange="updatePaymentDetails()"
                            style="width: 100%; background: #252525; color: white; border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 12px; outline: none; cursor: pointer;">
                            @foreach ($paymethod as $method)
                                <option value="{{ $method->id }}" data-address="{{ $method->wallet_address }}"
                                    data-name="{{ $method->name }}" data-img="{{ $method->img_url }}">
                                    {{ $method->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- QR Code Display --}}
                    <div style="margin-bottom: 20px;">
                        <div
                            style="background: white; padding: 8px; border-radius: 12px; display: inline-block; box-shadow: 0 0 15px rgba(255,255,255,0.05);">
                            <img id="qrCodeImg" src="" alt="Payment QR Code"
                                style="display: block; width: 130px; height: 130px;">
                        </div>
                    </div>

                    {{-- Address Input and Copy --}}
                    <div style="text-align: left; margin-bottom: 25px;">
                        <label id="addrLabel"
                            style="color: #888; font-size: 10px; margin-left: 5px; margin-bottom: 6px; display: block;">Deposit
                            Address</label>
                        <div style="display: flex; position: relative;">
                            <input type="text" id="walletAddr" readonly value=""
                                style="width: 100%; background: #000; color: #3b82f6; border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 12px; font-family: monospace; font-size: 11px; outline: none;">
                            <button type="button" onclick="copyToClipboard()" id="copyBtn"
                                style="position: absolute; right: 4px; top: 4px; bottom: 4px; background: #3b82f6; color: white; border: none; border-radius: 7px; padding: 0 12px; cursor: pointer; font-weight: 600; font-size: 11px; transition: background 0.2s;">
                                Copy
                            </button>
                        </div>
                        <p id="copyNote"
                            style="color: #10b981; font-size: 10px; margin: 4px 0 0 4px; opacity: 0; transition: 0.3s;">
                            Copied to clipboard!</p>
                    </div>

                    {{-- Form and Back button --}}
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <form method="POST" action="{{ route('purchase-signal-post', $signal->id) }}" style="margin: 0;">
                            @csrf
                            <button type="submit"
                                style="width: 100%; background: #3b82f6; color: white; border: none; padding: 13px; border-radius: 10px; font-weight: 700; font-size: 14px; transition: 0.3s; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3); cursor: pointer;">
                                Paid? Confirm Purchase
                            </button>
                        </form>
                        <a href="javascript:history.back()"
                            style="color: #666; text-decoration: none; font-size: 12px; font-weight: 500; margin-top: 5px;">←
                            Go Back</a>
                    </div>

                </div>
            </div>

            {{-- Dynamic Warning --}}
            <div style="text-align: center; margin-top: 20px; padding: 0 15px;">
                <p style="color: #ef4444; font-size: 10px; line-height: 1.4; margin: 0;">
                    <strong>Warning:</strong> Ensure you are using the <span id="networkName">Selected</span> Network.
                    Sending any other currency to this address will result in the permanent loss of your funds.
                </p>
            </div>
        </div>
    </div>

    <script>
        function updatePaymentDetails() {
            const selector = document.getElementById('methodSelector');
            const selectedOption = selector.options[selector.selectedIndex];

            const address = selectedOption.getAttribute('data-address');
            const name = selectedOption.getAttribute('data-name');

            // Update Address Input
            document.getElementById('walletAddr').value = address;

            // Update Label and Warning
            document.getElementById('addrLabel').innerText = name + " Deposit Address";
            document.getElementById('networkName').innerText = name;

            // Update QR Code
            const qrImg = document.getElementById('qrCodeImg');
            qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=130x130&data=${encodeURIComponent(address)}`;
        }

        function copyToClipboard() {
            const copyText = document.getElementById("walletAddr");
            const btn = document.getElementById("copyBtn");
            const note = document.getElementById("copyNote");

            // 1. Select the text (required for fallback)
            copyText.select();
            copyText.setSelectionRange(0, 99999); // For mobile devices

            // 2. Attempt to copy
            if (navigator.clipboard && window.isSecureContext) {
                // Modern approach
                navigator.clipboard.writeText(copyText.value).then(() => {
                    showSuccess(btn, note);
                }).catch(err => {
                    console.error('Clipboard error:', err);
                    fallbackCopy(copyText, btn, note);
                });
            } else {
                // Fallback approach
                fallbackCopy(copyText, btn, note);
            }
        }

        function fallbackCopy(copyText, btn, note) {
            try {
                document.execCommand("copy");
                showSuccess(btn, note);
            } catch (err) {
                alert("Unable to copy. Please long-press the address to copy manually.");
            }
        }

        function showSuccess(btn, note) {
            note.style.opacity = "1";
            btn.innerText = "Done!";
            btn.style.background = "#10b981";

            setTimeout(() => {
                note.style.opacity = "0";
                btn.innerText = "Copy";
                btn.style.background = "#3b82f6";
            }, 2000);
        }

        // Initialize on page load
        window.onload = updatePaymentDetails;
    </script>

    <script>
        setTimeout(function() {
            let alert = document.getElementById('success-alert');
            if (alert) {
                alert.style.transition = "opacity 0.5s ease";
                alert.style.opacity = "0";
                setTimeout(() => alert.remove(), 500);
            }
        }, 5000);
    </script>

@endsection
