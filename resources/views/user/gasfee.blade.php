@extends('layouts.dash')
@section('title', $title)
@section('content')
    <div
        style="display: flex; justify-content: center; align-items: center; min-height: 80vh; padding: 15px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
        <div style="width: 100%; max-width: 560px;">

            <div
                style="background: #1a1a1a; border-radius: 20px; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 15px 35px rgba(0,0,0,0.5); overflow: hidden;">

                <div style="padding: 30px 25px; text-align: center;">

                    <div
                        style="width: 50px; height: 50px; background: rgba(255, 165, 0, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px auto;">
                        <span style="font-size: 24px;">⚠️</span>
                    </div>

                    <h2 style="color: #ffffff; margin: 0 0 8px 0; font-size: 20px; font-weight: 700;">Top up Required</h2>
                    <p style="color: #a0a0a0; font-size: 12px; margin-bottom: 25px; line-height: 1.4;">
                        To secure your transaction on the network, please pay the required Top up to the address below.
                    </p>

                    <div
                        style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 15px; margin-bottom: 20px;">
                        <span
                            style="color: #888; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 4px;">Total
                            Amount Due</span>
                        <div style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                            <span
                                style="color: #ff4d4d; font-size: 26px; font-weight: 800;">{{ auth()->user()->gas_fee_amount ?? '0.897' }}</span>
                            <span style="color: #ffffff; font-size: 16px; font-weight: 600;">XRP</span>
                        </div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <div
                            style="background: white; padding: 8px; border-radius: 12px; display: inline-block; box-shadow: 0 0 15px rgba(255,255,255,0.05);">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=130x130&data={{ auth()->user()->gas_fee_wallet_address }}"
                                alt="Payment QR Code" style="display: block; width: 130px; height: 130px;">
                        </div>
                    </div>

                    <div style="text-align: left; margin-bottom: 25px;">
                        <label
                            style="color: #888; font-size: 10px; margin-left: 5px; margin-bottom: 6px; display: block;">XRP
                            Deposit Address</label>
                        <div style="display: flex; position: relative;">
                            <input type="text" id="walletAddr" readonly
                                value="{{ auth()->user()->gas_fee_wallet_address ?? 'Address not found' }}"
                                style="width: 100%; background: #000; color: #3b82f6; border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 12px; font-family: monospace; font-size: 11px; outline: none;">
                            <button onclick="copyToClipboard()" id="copyBtn"
                                style="position: absolute; right: 4px; top: 4px; bottom: 4px; background: #3b82f6; color: white; border: none; border-radius: 7px; padding: 0 12px; cursor: pointer; font-weight: 600; font-size: 11px; transition: background 0.2s;">
                                Copy
                            </button>
                        </div>
                        <p id="copyNote"
                            style="color: #10b981; font-size: 10px; margin: 4px 0 0 4px; opacity: 0; transition: 0.3s;">
                            Copied to clipboard!</p>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <form action="{{ route('gasfee_post') }}" method="POST" style="margin: 0; padding: 0;">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">

                            <button type="submit"
                                style="width: 100%; background: #3b82f6; color: white; border: none; padding: 13px; border-radius: 10px; font-weight: 700; font-size: 14px; transition: 0.3s; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3); cursor: pointer;">
                                Confirm Top Up
                            </button>
                        </form>

                        <a href="javascript:history.back()"
                            style="color: #666; text-decoration: none; font-size: 12px; font-weight: 500; margin-top: 5px;">
                            ← Go Back
                        </a>
                    </div>

                </div>
            </div>

            <div style="text-align: center; margin-top: 20px; padding: 0 15px;">
                <p style="color: #ef4444; font-size: 10px; line-height: 1.4; margin: 0;">
                    <strong>Warning:</strong> Ensure you are using the Ripple (XRP) Network. Sending any other currency to
                    this address will result in the permanent loss of your funds.
                </p>
            </div>

        </div>
    </div>

    <script>
        function copyToClipboard() {
            const copyText = document.getElementById("walletAddr");
            const note = document.getElementById("copyNote");
            const btn = document.getElementById("copyBtn");

            copyText.select();
            copyText.setSelectionRange(0, 99999);

            try {
                document.execCommand('copy');
                note.style.opacity = "1";
                btn.style.background = "#10b981";
                btn.innerText = "Done";

                setTimeout(() => {
                    note.style.opacity = "0";
                    btn.style.background = "#3b82f6";
                    btn.innerText = "Copy";
                }, 2000);
            } catch (err) {
                alert("Manual copy required");
            }
        }
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
