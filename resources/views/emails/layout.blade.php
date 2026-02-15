<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $settings->site_name ?? config('app.name') }}</title>
    <style>
        /* Reset styles */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; }
        a[x-apple-data-detectors] { color: inherit !important; text-decoration: none !important; font-size: inherit !important; font-family: inherit !important; font-weight: inherit !important; line-height: inherit !important; }
        
        /* Primary colors from green.css */
        :root {
            --primary: #0e4152;
            --secondary: #ebf8fc;
            --success: #49b336;
            --info: #00b8d9;
            --warning: #ffab00;
            --danger: #ff5630;
            --dark: #1f2d3d;
            --light: #f7fafc;
        }

        body {
            background-color: #f7fafc;
            font-family: 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            font-size: 16px;
            line-height: 1.6;
            color: #333333;
            margin: 0;
            padding: 0;
        }

        .email-wrapper {
            width: 100%;
            background-color: #f7fafc;
            padding: 20px 0;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Header */
        .email-header {
            background: linear-gradient(135deg, #0e4152 0%, #49b336 100%);
            padding: 30px 40px;
            text-align: center;
        }

        .email-logo img {
            max-height: 60px;
            width: auto;
        }

        .email-logo-text {
            color: #ffffff;
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            text-decoration: none;
        }

        /* Body */
        .email-body {
            padding: 40px;
            background-color: #ffffff;
        }

        .email-body h1 {
            color: #0e4152;
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 20px 0;
            line-height: 1.3;
        }

        .email-body h2 {
            color: #0e4152;
            font-size: 20px;
            font-weight: 600;
            margin: 30px 0 15px 0;
            line-height: 1.3;
        }

        .email-body h3 {
            color: #0e4152;
            font-size: 18px;
            font-weight: 600;
            margin: 25px 0 12px 0;
        }

        .email-body p {
            color: #555555;
            font-size: 16px;
            line-height: 1.6;
            margin: 0 0 15px 0;
        }

        .email-body strong {
            color: #333333;
            font-weight: 600;
        }

        .email-body a {
            color: #49b336;
            text-decoration: none;
        }

        .email-body a:hover {
            text-decoration: underline;
        }

        /* Button */
        .button {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, #49b336 0%, #0e4152 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
            text-align: center;
            transition: all 0.3s ease;
        }

        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(73, 179, 54, 0.4);
        }

        .button-secondary {
            background: #ebf8fc;
            color: #0e4152 !important;
            border: 2px solid #0e4152;
        }

        /* Info Box */
        .info-box {
            background-color: #ebf8fc;
            border-left: 4px solid #49b336;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }

        .info-box p {
            margin: 0;
            color: #0e4152;
        }

        .warning-box {
            background-color: #fff8e1;
            border-left: 4px solid #ffab00;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }

        .warning-box p {
            margin: 0;
            color: #856404;
        }

        .success-box {
            background-color: #f1f9f0;
            border-left: 4px solid #49b336;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }

        .success-box p {
            margin: 0;
            color: #2d5a2c;
        }

        /* Details Table */
        .details-table {
            width: 100%;
            margin: 25px 0;
            border-collapse: collapse;
        }

        .details-table tr {
            border-bottom: 1px solid #e2e8f0;
        }

        .details-table td {
            padding: 12px 0;
            font-size: 15px;
        }

        .details-table td:first-child {
            color: #666666;
            font-weight: 500;
            width: 40%;
        }

        .details-table td:last-child {
            color: #333333;
            font-weight: 600;
            text-align: right;
        }

        /* Features List */
        .features-list {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }

        .features-list li {
            padding: 10px 0 10px 30px;
            position: relative;
            color: #555555;
            line-height: 1.6;
        }

        .features-list li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #49b336;
            font-weight: bold;
            font-size: 18px;
        }

        /* Footer */
        .email-footer {
            background-color: #0e4152;
            padding: 30px 40px;
            text-align: center;
        }

        .email-footer p {
            color: #ebf8fc;
            font-size: 14px;
            margin: 8px 0;
            line-height: 1.5;
        }

        .email-footer a {
            color: #49b336;
            text-decoration: none;
        }

        .email-footer a:hover {
            text-decoration: underline;
        }

        .social-links {
            margin: 20px 0 15px 0;
        }

        .social-links a {
            display: inline-block;
            margin: 0 8px;
            color: #ebf8fc;
            font-size: 18px;
            text-decoration: none;
        }

        .divider {
            height: 1px;
            background-color: #e2e8f0;
            margin: 30px 0;
        }

        .text-center {
            text-align: center;
        }

        .text-muted {
            color: #888888;
            font-size: 14px;
        }

        .spacer {
            height: 20px;
        }

        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-container {
                width: 100% !important;
                margin: 0 !important;
                border-radius: 0 !important;
            }

            .email-header,
            .email-body,
            .email-footer {
                padding: 25px 20px !important;
            }

            .email-body h1 {
                font-size: 22px !important;
            }

            .email-body h2 {
                font-size: 18px !important;
            }

            .button {
                display: block !important;
                width: 100% !important;
                box-sizing: border-box;
            }

            .details-table td:first-child,
            .details-table td:last-child {
                display: block;
                width: 100% !important;
                text-align: left !important;
            }

            .details-table td:last-child {
                padding-top: 5px;
                font-weight: 700;
            }
        }
    </style>
</head>
<body>
    <table class="email-wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
        <tr>
            <td align="center">
                <table class="email-container" width="600" cellpadding="0" cellspacing="0" role="presentation">
                    <!-- Header -->
                    <tr>
                        <td class="email-header">
                            <div class="email-logo">
                                @if(isset($settings->logo) && $settings->logo)
                                    <img src="{{ asset('storage/app/public/'. $settings->logo) }}" alt="{{ $settings->site_name ?? config('app.name') }}" style="max-height: 60px; width: auto;">
                                @else
                                    <h1 class="email-logo-text">{{ $settings->site_name ?? config('app.name') }}</h1>
                                @endif
                            </div>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td class="email-body">
                            @yield('content')
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="email-footer">
                            <p style="font-weight: 600; font-size: 16px; margin-bottom: 15px;">{{ $settings->site_name ?? config('app.name') }}</p>
                            
                            @if(isset($settings->address_o) && $settings->address_o)
                                <p>{{ $settings->address_o }}</p>
                            @endif
                            
                            <p>
                                <a href="mailto:{{ $settings->contact_email ?? config('mail.from.address') }}">{{ $settings->contact_email ?? config('mail.from.address') }}</a>
                            </p>

                            @if(isset($settings->site_address) && $settings->site_address)
                                <p style="margin-top: 20px;">
                                    <a href="{{ $settings->site_address }}" style="color: #49b336; font-weight: 600;">Visit Our Website</a>
                                </p>
                            @endif

                            <div class="divider" style="background-color: rgba(235, 248, 252, 0.2); margin: 20px 0;"></div>

                            <p style="font-size: 12px; color: #b0c9d1; margin-top: 15px;">
                                &copy; {{ date('Y') }} {{ $settings->site_name ?? config('app.name') }}. All rights reserved.
                            </p>

                            <p style="font-size: 12px; color: #b0c9d1;">
                                This email was sent to you as a registered member of {{ $settings->site_name ?? config('app.name') }}.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
