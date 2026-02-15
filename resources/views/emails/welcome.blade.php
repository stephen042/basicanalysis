@extends('emails.layout')

@section('content')
<h1>Welcome to {{ $settings->site_name }}, {{ $user->name }}! 🎉</h1>

<p>Thank you for joining our cutting-edge automated trading platform. We're excited to have you as part of our growing community of smart investors.</p>

<div class="success-box">
    <p><strong>Your account has been successfully created!</strong> You can now access all our premium trading features.</p>
</div>

<h2>Get Started in 3 Easy Steps</h2>

<table class="details-table">
    <tr>
        <td><strong>1. Fund Your Account</strong></td>
        <td></td>
    </tr>
    <tr>
        <td colspan="2" style="padding-top: 5px; color: #666;">
            Securely deposit funds using your preferred payment method. We support multiple currencies and payment options.
        </td>
    </tr>
    <tr>
        <td><strong>2. Select a Trading Bot</strong></td>
        <td></td>
    </tr>
    <tr>
        <td colspan="2" style="padding-top: 5px; color: #666;">
            Choose from our range of AI-powered bots designed with sophisticated algorithms to maximize returns.
        </td>
    </tr>
    <tr>
        <td><strong>3. Monitor & Grow</strong></td>
        <td></td>
    </tr>
    <tr>
        <td colspan="2" style="padding-top: 5px; color: #666;">
            Sit back and watch your portfolio grow. Our systems work 24/7 to optimize your investment strategy.
        </td>
    </tr>
</table>

<div class="text-center">
    <a href="{{ $settings->site_address }}/dashboard" class="button">Access Your Dashboard</a>
</div>

<h2>Why Choose Us?</h2>

<ul class="features-list">
    <li><strong>Automated Intelligence</strong> - Advanced AI algorithms analyze markets in real-time</li>
    <li><strong>Secure Platform</strong> - Bank-level security protecting your investments</li>
    <li><strong>24/7 Trading</strong> - Never miss an opportunity with round-the-clock automation</li>
    <li><strong>Transparent Returns</strong> - Track your performance with detailed analytics</li>
    <li><strong>Expert Support</strong> - Our team is here to assist you every step of the way</li>
</ul>

<div class="info-box">
    <p><strong>Need Help Getting Started?</strong></p>
    <p style="margin-top: 10px;">Our support team is ready to guide you through the platform and answer any questions you may have.</p>
    <div style="margin-top: 15px;">
        <a href="{{ $settings->site_address }}/user/support" class="button button-secondary">Contact Support</a>
    </div>
</div>

<div class="spacer"></div>

<p>We're committed to helping you achieve your financial goals through intelligent automation and professional service.</p>

<p style="margin-top: 30px;">Best regards,<br><strong>The {{ $settings->site_name }} Team</strong></p>

<div class="divider"></div>

<p class="text-muted" style="font-size: 13px;">This email was sent to <strong>{{ $user->email }}</strong> as part of your registration. If you did not create this account, please contact us immediately.</p>
@endsection
