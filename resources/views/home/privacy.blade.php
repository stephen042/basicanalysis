@extends('layouts.base')

@section('title', $settings->site_name . ' - Privacy Policy')

@section('content')


    <!-- ==================== Breadcrumb Start Here ==================== -->
    <section class="breadcrumb">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="breadcrumb__wrapper">
                        <h2 class="breadcrumb__title"> Privacy & Policy</h2>
                        <ul class="breadcrumb__list">
                            <li class="breadcrumb__item"><a href="{{ route('home') }}" class="breadcrumb__link"> <i
                                        class="las la-home"></i> Home</a> </li>
                            <li class="breadcrumb__item"><i class="fa-solid fa-minus"></i></li>
                            <li class="breadcrumb__item"> <span class="breadcrumb__item-text"> Privacy & Policy
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ==================== Breadcrumb End Here ==================== -->
    <!--==========================  Privacy Section Start  ==========================-->
    <section class="privacy-section bg--black-two py-120">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-10">
                    <div class="privacy-content">
                        <h2>{{ $settings->site_name }} Trading Privacy Policy</h2>
                        <div class="last-updated pb-1">Last Updated: <strong class="text-white">{{date('Y')}}</strong></div>
                        <div class="section">
                            <p>At {{ $settings->site_name }} Trading, we are committed to protecting your privacy and safeguarding
                                your personal
                                and financial information. This Privacy Policy explains how we collect, use,
                                disclose, and
                                protect your information when you use our trading platform and services.</p>
                        </div>
                        <br>
                        <h4 class="mb-2">1. Information We Collect</h4>
                        <div class="section">
                            <p>We may collect the following types of information:</p>
                            <ul>
                                <li><strong>Personal Identification Information:</strong> Name, date of birth,
                                    address,
                                    phone number, email, government-issued ID</li>
                                <li><strong>Financial Information:</strong> Bank account details, credit card
                                    information,
                                    trading history, tax identification numbers</li>
                                <li><strong>Technical Data:</strong> IP address, device information, browser
                                    type, operating
                                    system</li>
                                <li><strong>Usage Data:</strong> Trading activity, account preferences, session
                                    duration,
                                    pages visited</li>
                                <li><strong>KYC Documentation:</strong> Proof of identity, proof of residence,
                                    source of
                                    funds documentation</li>
                            </ul>
                        </div>

                        <br>
                        <h4 class="mb-2">2. How We Use Your Information</h4>
                        <div class="section">
                            <p>We use your information for the following purposes:</p>
                            <ul>
                                <li>To provide and maintain our trading services</li>
                                <li>To verify your identity and comply with legal obligations (KYC/AML)</li>
                                <li>To process transactions and manage your account</li>
                                <li>To improve our platform and develop new features</li>
                                <li>To communicate with you about your account and our services</li>
                                <li>To detect and prevent fraud, money laundering, and other illegal activities
                                </li>
                                <li>To personalize your trading experience</li>
                                <li>To comply with regulatory requirements</li>
                            </ul>
                        </div>

                        <br>
                        <h4 class="mb-2">3. Data Sharing and Disclosure</h4>
                        <div class="section">
                            <p>We may share your information with:</p>
                            <ul>
                                <li>Regulatory authorities and law enforcement agencies as required by law</li>
                                <li>Payment processors and financial institutions</li>
                                <li>Third-party service providers (e.g., cloud hosting, customer support)</li>
                                <li>Affiliates and subsidiaries within our corporate group</li>
                                <li>In connection with business transfers (mergers, acquisitions)</li>
                            </ul>
                            <p class="pt-3">We do not sell your personal information to third parties for
                                marketing purposes.</p>
                        </div>

                        <br>
                        <h4 class="mb-2">4. Data Security</h4>
                        <div class="section">
                            <p>We implement robust security measures to protect your information:</p>
                            <ul>
                                <li>Encryption of sensitive data in transit and at rest</li>
                                <li>Secure socket layer (SSL) technology</li>
                                <li>Two-factor authentication</li>
                                <li>Regular security audits</li>
                                <li>Restricted access to personal information</li>
                            </ul>
                        </div>

                        <br>
                        <h4 class="mb-2">5. Your Rights</h4>
                        <div class="section">
                            <p>Depending on your jurisdiction, you may have the following rights:</p>
                            <ul>
                                <li>Right to access your personal information</li>
                                <li>Right to request correction of inaccurate data</li>
                                <li>Right to request deletion of your data</li>
                                <li>Right to restrict or object to processing</li>
                                <li>Right to data portability</li>
                                <li>Right to withdraw consent</li>
                            </ul>
                            <p class="pt-3">To exercise these rights, please contact our Data Protection Officer
                                at [DPO email].</p>
                        </div>

                        <br>
                        <h4 class="mb-2">6. Cookies and Tracking Technologies</h4>
                        <div class="section">
                            <p>We use cookies and similar technologies to:</p>
                            <ul>
                                <li>Authenticate users and maintain sessions</li>
                                <li>Remember preferences</li>
                                <li>Analyze platform usage</li>
                                <li>Improve security</li>
                            </ul>
                            <p class="pt-3">You can manage cookie preferences through your browser settings.</p>
                        </div>

                        <br>
                        <h4 class="mb-2">7. International Data Transfers</h4>
                        <div class="section">
                            <p>As a global trading platform, your data may be transferred to and processed in
                                countries
                                outside your jurisdiction. We ensure adequate protection through:</p>
                            <ul>
                                <li>Standard contractual clauses</li>
                                <li>Adequacy decisions</li>
                                <li>Other lawful transfer mechanisms</li>
                            </ul>
                        </div>

                        <br>
                        <h4 class="mb-2">8. Changes to This Policy</h4>
                        <div class="section">
                            <p>We may update this Privacy Policy periodically. We will notify you of significant
                                changes
                                through our platform or via email.</p>
                        </div>

                        <br>
                        <h4 class="mb-2">9. Contact Us</h4>
                        <div class="section">
                            <p>If you have any questions about this Privacy Policy or our data practices, please
                                contact us
                                at:</p>
                            <p>
                                {{ $settings->site_name }} Trading<br>
                                Email: <a href="https://photoclerks.com/cdn-cgi/l/email-protection" class="__cf_email__"
                                    data-cfemail="d7beb9b1b8f9afa3a5b6b3ae97b0bab6bebbf9b4b8ba">{{ $settings->site_email }}</a><br>
                                Phone: (+1) 824 394 4562
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--==========================  Privacy Section End  ==========================-->
    <!--==========================  Call-to-action Section Start  ==========================-->
    <section class="call-to-action-section bg--black-two">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="call-action-box">
                        <div class="call-shape">
                            <img src="assets/svg/twostar.svg" alt="star">
                        </div>
                        <div class="row row-gap-5">
                            <div class="col-lg-6 align-self-center order-1 order-lg-0">
                                <div class="call-text text-center text-lg-start">
                                    <span class="text--base pb-3 right-reveal">Start Your Forex Journey
                                        Today</span>
                                    <h2 class="mb-4 right-reveal">Learn, Trade, And Grow With Trusted Tools And
                                        Signals.</h2>
                                    <a href="{{ route('register') }}" class="btn btn--base-two right-reveal">
                                        Register Now <i class="flaticon-arrow-upper-right"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="col-lg-6 align-self-center">
                                <div class="call-img text-lg-end text-center scaleUp">
                                    <img src="assets/images/call/call-img.png" alt="call" class="img-fluid m-auto">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--==========================  Call-to-action Section End  ==========================-->



@endsection
