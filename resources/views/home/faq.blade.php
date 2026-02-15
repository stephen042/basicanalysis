@extends('layouts.base')

@section('title', $settings->site_name . ' - Frequently Asked Questions')

@section('content')

    <!-- FAQ Intro Section -->
    <section class="relative py-20 lg:py-32 overflow-hidden">

        <!-- ==================== Breadcrumb Start Here ==================== -->
        <section class="breadcrumb">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="breadcrumb__wrapper">
                            <h2 class="breadcrumb__title">FAQ</h2>
                            <ul class="breadcrumb__list">
                                <li class="breadcrumb__item"><a href="{{ route('home') }}" class="breadcrumb__link"> <i
                                            class="las la-home"></i> Home</a> </li>
                                <li class="breadcrumb__item"><i class="fa-solid fa-minus"></i></li>
                                <li class="breadcrumb__item"> <span class="breadcrumb__item-text">FAQ
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- ==================== Breadcrumb End Here ==================== -->

        <div class="container mx-auto px-4 max-w-7xl">
            <div class="flex justify-center">
                <div class="w-full lg:w-2/3 xl:w-1/2 text-center">
                    <span class="inline-block text-cyan-400 font-medium text-lg mb-4">Help Center</span>
                    <h2 class="text-3xl lg:text-4xl xl:text-5xl font-bold mb-6">Everything You Need to Know About Automated
                        Bot Trading</h2>
                    <p class="text-lg text-gray-400 max-w-3xl mx-auto">
                        Find answers to common questions about our AI-powered trading platform,
                        bot configuration, security, and more.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="relative py-20 lg:py-32">
        <div class="container mx-auto px-4 max-w-6xl">
            <div class="flex justify-center">
                <div class="w-full lg:w-5/6 xl:w-full">
                    <!-- Getting Started FAQ -->
                    <div class="mb-16 lg:mb-20">
                        <div class="mb-10 lg:mb-12">
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-12 h-12 bg-gray-800 rounded-full flex items-center justify-center">
                                    <i class="ti ti-rocket text-xl text-cyan-400"></i>
                                </div>
                                <h3 class="text-2xl lg:text-3xl font-bold">Getting Started</h3>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <!-- FAQ Item 1 -->
                            <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                                <button
                                    class="accordion-btn w-full flex justify-between items-center text-left font-semibold text-lg">
                                    <span>What is automated bot trading?</span>
                                    <i
                                        class="ti ti-chevron-down accordion-icon transition-transform duration-300 text-cyan-400"></i>
                                </button>
                                <div class="accordion-content mt-0">
                                    <div class="pt-4 border-t border-gray-700">
                                        <p class="text-gray-300">
                                            Automated bot trading uses AI-powered algorithms to execute trades
                                            automatically on your behalf, 24/7. Our bots analyze market trends,
                                            identify trading opportunities, and execute trades based on sophisticated
                                            strategies without requiring human intervention.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ Item 2 -->
                            <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                                <button
                                    class="accordion-btn w-full flex justify-between items-center text-left font-semibold text-lg">
                                    <span>How do I get started with {{ $settings->site_name }}?</span>
                                    <i
                                        class="ti ti-chevron-down accordion-icon transition-transform duration-300 text-cyan-400"></i>
                                </button>
                                <div class="accordion-content mt-0">
                                    <div class="pt-4 border-t border-gray-700">
                                        <p class="text-gray-300">
                                            Getting started is simple: 1) Create your account by registering on our
                                            platform,
                                            2) Complete identity verification (KYC), 3) Deposit funds into your account,
                                            4) Choose from our selection of AI trading bots, 5) Configure your bot settings
                                            and risk parameters, 6) Activate your bot and start trading automatically.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ Item 3 -->
                            <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                                <button
                                    class="accordion-btn w-full flex justify-between items-center text-left font-semibold text-lg">
                                    <span>What is the minimum deposit required?</span>
                                    <i
                                        class="ti ti-chevron-down accordion-icon transition-transform duration-300 text-cyan-400"></i>
                                </button>
                                <div class="accordion-content mt-0">
                                    <div class="pt-4 border-t border-gray-700">
                                        <p class="text-gray-300">
                                            The minimum deposit varies by bot type and strategy. Generally, you can start
                                            with as little as $100 for basic bots, but we recommend a minimum of $500-$1000
                                            for optimal performance and better risk management. Higher-tier bots with
                                            advanced
                                            strategies may require larger minimum deposits.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ Item 4 -->
                            <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                                <button
                                    class="accordion-btn w-full flex justify-between items-center text-left font-semibold text-lg">
                                    <span>Do I need trading experience to use your bots?</span>
                                    <i
                                        class="ti ti-chevron-down accordion-icon transition-transform duration-300 text-cyan-400"></i>
                                </button>
                                <div class="accordion-content mt-0">
                                    <div class="pt-4 border-t border-gray-700">
                                        <p class="text-gray-300">
                                            No prior trading experience is required! Our AI-powered bots are designed for
                                            both beginners and experienced traders. The bots handle all technical analysis
                                            and trade execution automatically. However, we do provide educational resources
                                            to help you understand how the bots work and make informed decisions.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bot Trading FAQ -->
                    <div class="mb-16 lg:mb-20">
                        <div class="mb-10 lg:mb-12">
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-12 h-12 bg-gray-800 rounded-full flex items-center justify-center">
                                    <i class="ti ti-robot text-xl text-cyan-400"></i>
                                </div>
                                <h3 class="text-2xl lg:text-3xl font-bold">Bot Trading & Strategies</h3>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <!-- FAQ Item 1 -->
                            <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                                <button
                                    class="accordion-btn w-full flex justify-between items-center text-left font-semibold text-lg">
                                    <span>What types of trading bots are available?</span>
                                    <i
                                        class="ti ti-chevron-down accordion-icon transition-transform duration-300 text-cyan-400"></i>
                                </button>
                                <div class="accordion-content mt-0">
                                    <div class="pt-4 border-t border-gray-700">
                                        <p class="text-gray-300">
                                            We offer multiple bot types: Scalping Bots (quick trades for small profits),
                                            Trend-Following Bots (follow market momentum), Arbitrage Bots (exploit price
                                            differences), Grid Trading Bots (buy low, sell high in ranges), and AI Strategy
                                            Bots (advanced machine learning algorithms). Each bot uses different strategies
                                            optimized for various market conditions.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ Item 2 -->
                            <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                                <button
                                    class="accordion-btn w-full flex justify-between items-center text-left font-semibold text-lg">
                                    <span>How do AI bots analyze the market?</span>
                                    <i
                                        class="ti ti-chevron-down accordion-icon transition-transform duration-300 text-cyan-400"></i>
                                </button>
                                <div class="accordion-content mt-0">
                                    <div class="pt-4 border-t border-gray-700">
                                        <p class="text-gray-300">
                                            Our AI bots use machine learning algorithms to analyze historical data,
                                            real-time
                                            market conditions, technical indicators, price patterns, volume data, and market
                                            sentiment. They continuously learn from market behavior and adapt their
                                            strategies
                                            to optimize performance and minimize risk.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ Item 3 -->
                            <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                                <button
                                    class="accordion-btn w-full flex justify-between items-center text-left font-semibold text-lg">
                                    <span>Can I customize my trading bot settings?</span>
                                    <i
                                        class="ti ti-chevron-down accordion-icon transition-transform duration-300 text-cyan-400"></i>
                                </button>
                                <div class="accordion-content mt-0">
                                    <div class="pt-4 border-t border-gray-700">
                                        <p class="text-gray-300">
                                            Yes! You have full control over bot configuration. You can adjust risk levels,
                                            set stop-loss and take-profit parameters, choose trading pairs, define
                                            investment
                                            amounts, and customize trading frequency. Advanced users can even fine-tune
                                            technical indicator parameters and strategy behavior.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ Item 4 -->
                            <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                                <button
                                    class="accordion-btn w-full flex justify-between items-center text-left font-semibold text-lg">
                                    <span>Which markets and assets can bots trade?</span>
                                    <i
                                        class="ti ti-chevron-down accordion-icon transition-transform duration-300 text-cyan-400"></i>
                                </button>
                                <div class="accordion-content mt-0">
                                    <div class="pt-4 border-t border-gray-700">
                                        <p class="text-gray-300">
                                            Our bots can trade across multiple markets including Cryptocurrencies (Bitcoin,
                                            Ethereum, and 100+ altcoins), Forex (major and minor currency pairs), Stocks
                                            (US and international markets), and Commodities (gold, silver, oil). You can
                                            run multiple bots simultaneously across different markets.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security & Safety FAQ -->
                    <div class="mb-16 lg:mb-20">
                        <div class="mb-10 lg:mb-12">
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-12 h-12 bg-gray-800 rounded-full flex items-center justify-center">
                                    <i class="ti ti-shield-lock text-xl text-cyan-400"></i>
                                </div>
                                <h3 class="text-2xl lg:text-3xl font-bold">Security & Safety</h3>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <!-- FAQ Item 1 -->
                            <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                                <button
                                    class="accordion-btn w-full flex justify-between items-center text-left font-semibold text-lg">
                                    <span>Is my money safe with {{ $settings->site_name }}?</span>
                                    <i
                                        class="ti ti-chevron-down accordion-icon transition-transform duration-300 text-cyan-400"></i>
                                </button>
                                <div class="accordion-content mt-0">
                                    <div class="pt-4 border-t border-gray-700">
                                        <p class="text-gray-300">
                                            Yes! We use bank-level security measures including 256-bit SSL encryption,
                                            multi-factor authentication (2FA), cold storage for the majority of funds,
                                            regular security audits, and compliance with financial regulations.
                                            Additionally,
                                            all bots include automated risk management features and stop-loss protection.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ Item 2 -->
                            <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                                <button
                                    class="accordion-btn w-full flex justify-between items-center text-left font-semibold text-lg">
                                    <span>What are the risks of automated bot trading?</span>
                                    <i
                                        class="ti ti-chevron-down accordion-icon transition-transform duration-300 text-cyan-400"></i>
                                </button>
                                <div class="accordion-content mt-0">
                                    <div class="pt-4 border-t border-gray-700">
                                        <p class="text-gray-300">
                                            Like all trading, bot trading carries risks including market volatility,
                                            potential
                                            losses, and technical failures. However, our bots include built-in risk
                                            management
                                            features such as stop-loss orders, position sizing, and diversification. We
                                            strongly
                                            recommend only investing funds you can afford to lose and starting with smaller
                                            amounts.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ Item 3 -->
                            <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                                <button
                                    class="accordion-btn w-full flex justify-between items-center text-left font-semibold text-lg">
                                    <span>How does risk management work?</span>
                                    <i
                                        class="ti ti-chevron-down accordion-icon transition-transform duration-300 text-cyan-400"></i>
                                </button>
                                <div class="accordion-content mt-0">
                                    <div class="pt-4 border-t border-gray-700">
                                        <p class="text-gray-300">
                                            Our bots employ multiple risk management strategies: automatic stop-loss orders
                                            to limit potential losses, position sizing based on account balance, portfolio
                                            diversification across multiple assets, daily/weekly loss limits, and real-time
                                            monitoring with alerts. You can customize these parameters based on your risk
                                            tolerance.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ Item 4 -->
                            <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                                <button
                                    class="accordion-btn w-full flex justify-between items-center text-left font-semibold text-lg">
                                    <span>Can I stop or pause my bot anytime?</span>
                                    <i
                                        class="ti ti-chevron-down accordion-icon transition-transform duration-300 text-cyan-400"></i>
                                </button>
                                <div class="accordion-content mt-0">
                                    <div class="pt-4 border-t border-gray-700">
                                        <p class="text-gray-300">
                                            Absolutely! You have complete control and can pause or stop your bots at any
                                            time
                                            through your dashboard. When paused, the bot will complete any open trades
                                            according
                                            to your settings but won't initiate new trades. You can also modify settings or
                                            switch to different bots whenever you want.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account & Payments FAQ -->
                    <div class="mb-16 lg:mb-20">
                        <div class="mb-10 lg:mb-12">
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-12 h-12 bg-gray-800 rounded-full flex items-center justify-center">
                                    <i class="ti ti-wallet text-xl text-cyan-400"></i>
                                </div>
                                <h3 class="text-2xl lg:text-3xl font-bold">Account & Payments</h3>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <!-- FAQ Item 1 -->
                            <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                                <button
                                    class="accordion-btn w-full flex justify-between items-center text-left font-semibold text-lg">
                                    <span>What payment methods do you accept?</span>
                                    <i
                                        class="ti ti-chevron-down accordion-icon transition-transform duration-300 text-cyan-400"></i>
                                </button>
                                <div class="accordion-content mt-0">
                                    <div class="pt-4 border-t border-gray-700">
                                        <p class="text-gray-300">
                                            We accept multiple payment methods including bank transfers (wire/ACH),
                                            credit/debit
                                            cards (Visa, Mastercard), cryptocurrencies (Bitcoin, Ethereum, USDT, and
                                            others),
                                            e-wallets (PayPal, Skrill, Neteller), and other regional payment options.
                                            Processing
                                            times vary by method.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ Item 2 -->
                            <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                                <button
                                    class="accordion-btn w-full flex justify-between items-center text-left font-semibold text-lg">
                                    <span>How do withdrawals work?</span>
                                    <i
                                        class="ti ti-chevron-down accordion-icon transition-transform duration-300 text-cyan-400"></i>
                                </button>
                                <div class="accordion-content mt-0">
                                    <div class="pt-4 border-t border-gray-700">
                                        <p class="text-gray-300">
                                            You can withdraw funds at any time through your dashboard. Simply submit a
                                            withdrawal
                                            request, and we'll process it within 24-48 hours. Crypto withdrawals are
                                            typically
                                            faster (1-6 hours), while bank transfers may take 3-5 business days. There's no
                                            minimum holding period for your funds.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ Item 3 -->
                            <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                                <button
                                    class="accordion-btn w-full flex justify-between items-center text-left font-semibold text-lg">
                                    <span>Are there any fees?</span>
                                    <i
                                        class="ti ti-chevron-down accordion-icon transition-transform duration-300 text-cyan-400"></i>
                                </button>
                                <div class="accordion-content mt-0">
                                    <div class="pt-4 border-t border-gray-700">
                                        <p class="text-gray-300">
                                            We charge a small performance fee (typically 10-20% of profits) only when your
                                            bots
                                            make money. There are no monthly subscription fees or hidden charges. Deposit
                                            and
                                            withdrawal fees vary by payment method and are clearly displayed before you
                                            confirm
                                            any transaction.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ Item 4 -->
                            <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                                <button
                                    class="accordion-btn w-full flex justify-between items-center text-left font-semibold text-lg">
                                    <span>Is KYC verification required?</span>
                                    <i
                                        class="ti ti-chevron-down accordion-icon transition-transform duration-300 text-cyan-400"></i>
                                </button>
                                <div class="accordion-content mt-0">
                                    <div class="pt-4 border-t border-gray-700">
                                        <p class="text-gray-300">
                                            Yes, to comply with financial regulations and ensure security, we require
                                            identity
                                            verification (KYC). You'll need to provide a government-issued ID and proof of
                                            address.
                                            The verification process is quick and secure, typically completed within 24
                                            hours.
                                            This helps protect your account and prevent fraud.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Support FAQ -->
                    <div class="mb-16 lg:mb-20">
                        <div class="mb-10 lg:mb-12">
                            <div class="flex items-center gap-4 mb-6">
                                <div class="w-12 h-12 bg-gray-800 rounded-full flex items-center justify-center">
                                    <i class="ti ti-headset text-xl text-cyan-400"></i>
                                </div>
                                <h3 class="text-2xl lg:text-3xl font-bold">Support & Assistance</h3>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <!-- FAQ Item 1 -->
                            <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                                <button
                                    class="accordion-btn w-full flex justify-between items-center text-left font-semibold text-lg">
                                    <span>How can I contact customer support?</span>
                                    <i
                                        class="ti ti-chevron-down accordion-icon transition-transform duration-300 text-cyan-400"></i>
                                </button>
                                <div class="accordion-content mt-0">
                                    <div class="pt-4 border-t border-gray-700">
                                        <p class="text-gray-300">
                                            Our support team is available 24/7 through multiple channels: Live Chat (instant
                                            responses on our website), Email Support ({{ $settings->contact_email }}), Phone
                                            Support (call us anytime), and Help Center (comprehensive guides and tutorials).
                                            We aim to respond to all inquiries within 24 hours.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ Item 2 -->
                            <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                                <button
                                    class="accordion-btn w-full flex justify-between items-center text-left font-semibold text-lg">
                                    <span>Do you provide training or tutorials?</span>
                                    <i
                                        class="ti ti-chevron-down accordion-icon transition-transform duration-300 text-cyan-400"></i>
                                </button>
                                <div class="accordion-content mt-0">
                                    <div class="pt-4 border-t border-gray-700">
                                        <p class="text-gray-300">
                                            Yes! We offer comprehensive educational resources including video tutorials,
                                            step-by-step
                                            guides, webinars, a knowledge base with articles, bot configuration
                                            walkthroughs,
                                            and trading strategy explanations. Our goal is to help you understand and
                                            maximize
                                            the potential of automated trading.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ Item 3 -->
                            <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                                <button
                                    class="accordion-btn w-full flex justify-between items-center text-left font-semibold text-lg">
                                    <span>What if I encounter technical issues?</span>
                                    <i
                                        class="ti ti-chevron-down accordion-icon transition-transform duration-300 text-cyan-400"></i>
                                </button>
                                <div class="accordion-content mt-0">
                                    <div class="pt-4 border-t border-gray-700">
                                        <p class="text-gray-300">
                                            Contact our technical support team immediately through live chat or email. We
                                            have
                                            dedicated technical specialists available 24/7 to resolve any platform issues,
                                            bot
                                            errors, or connectivity problems. We also monitor system performance
                                            continuously
                                            and proactively address potential issues.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ Item 4 -->
                            <div class="bg-gray-800 rounded-xl p-6 shadow-lg">
                                <button
                                    class="accordion-btn w-full flex justify-between items-center text-left font-semibold text-lg">
                                    <span>Can I get help with bot configuration?</span>
                                    <i
                                        class="ti ti-chevron-down accordion-icon transition-transform duration-300 text-cyan-400"></i>
                                </button>
                                <div class="accordion-content mt-0">
                                    <div class="pt-4 border-t border-gray-700">
                                        <p class="text-gray-300">
                                            Absolutely! Our support team can assist you with bot selection, configuration,
                                            parameter optimization, and strategy recommendations. We also offer premium
                                            account
                                            management services where dedicated specialists can help configure and monitor
                                            your
                                            bots for optimal performance.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Still Have Questions CTA Section -->
    <section class="bg-gradient-to-r from-cyan-500 to-blue-600 py-20 lg:py-32 relative overflow-hidden">
    
        <div class="container mx-auto px-4 max-w-6xl">
            <div class="flex justify-center">
                <div class="w-full lg:w-2/3 xl:w-1/2 text-center">
                    <h2 class="text-3xl lg:text-4xl xl:text-5xl font-bold mb-6">Still Have Questions?</h2>
                    <p class="text-lg text-white/90 max-w-3xl mx-auto mb-10">
                        Can't find the answer you're looking for? Our support team is ready to help you
                        with any questions about automated bot trading.
                    </p>
                    <div class="flex flex-wrap justify-center gap-5 items-center">
                        <a href="{{ route('contact') }}"
                            class="bg-gray-800 hover:bg-gray-700 text-white font-medium py-3 px-6 rounded-xl flex items-center gap-2 transition-colors">
                            Contact Support <i class="ti ti-message-circle"></i>
                        </a>
                        <a href="{{ route('register') }}"
                            class="text-white font-medium flex items-center gap-2 hover:underline">
                            Get Started <i class="ti ti-arrow-narrow-right text-xl"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- JavaScript for Accordion Functionality -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const accordionButtons = document.querySelectorAll('.accordion-btn');

            accordionButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Toggle active class on button
                    this.classList.toggle('active');

                    // Toggle active class on content
                    const content = this.parentElement.nextElementSibling;
                    content.classList.toggle('active');

                    // Close other open accordions (optional)
                    accordionButtons.forEach(otherButton => {
                        if (otherButton !== button) {
                            otherButton.classList.remove('active');
                            const otherContent = otherButton.parentElement
                                .nextElementSibling;
                            otherContent.classList.remove('active');
                        }
                    });
                });
            });
        });
    </script>

@endsection
