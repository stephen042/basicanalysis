<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="XTrady – A modern and professional template for Forex and stock trading businesses.">
    <meta name="keywords" content="Forex, Stock Broker, Trading Investments">
    <!-- Title -->
    <title>{{ $settings->site_name }} | @yield('title')</title>
    <!-- Favicon -->
    {{-- <link rel="shortcut icon" href="assets/images/logo/favicon.ico"> --}}
    <link rel="icon"
        href="{{ asset('storage/' . $settings->favicon) ?? asset('assets/images/logo/favicon.ico') }}"
        type="image/png" />
    <!-- Bootstrap -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('assets/css/all.min.css') }}">
    <!-- Flat Icon -->
    <link rel="stylesheet" href="{{ asset('assets/fonts/flaticon_xtrade.css') }}">
    <!-- Fancy Box -->
    <link rel="stylesheet" href="{{ asset('assets/css/fancybox.css') }}">
    <!-- Swiper Slider -->
    <link rel="stylesheet" href="{{ asset('assets/css/swiper-bundle.min.css') }}">
    <!-- Odometer -->
    <link rel="stylesheet" href="{{ asset('assets/css/odometer.css') }}">
    <!-- Main css -->
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>


<body>
    <!--==========================   Preloader Start  ==========================-->
    <div id="preloader">
        <div id="text">
            <p class="active">B</p>
            <p>B</p>
            <p>A</p>
            <p>S</p>
            <p>I</p>
            <p>C</p>
        </div>
    </div>
    <!--==========================  Preloader End  ==========================-->


    <div class="wrapper">

        <!--==========================   Header Start  ==========================-->
        <header>
            <nav class="navbar navbar-expand-lg navbar-main">
                <div class="container">
                    <a class="navbar-brand" href="/">
                        <img src="{{ asset('storage/' . $settings->logo) }}" alt="{{ $settings->site_name }}"
                            class="logo-img" width="50" height="50">
                    </a>
                    <div class="right-nav">
                        <a href="{{ route('login') }}" class="btn btn-outline--base d-none d-sm-block">
                            Log In <i class="flaticon-arrow-upper-right"></i>
                        </a>
                        <a href="{{ route('contact') }}" class="btn btn--base-two d-none d-sm-block">
                            Contact Us <i class="flaticon-arrow-upper-right"></i>
                        </a>
                        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar"
                            aria-label="Toggle navigation">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                class="bi bi-list" viewBox="0 0 16 16">
                                <path fill-rule="evenodd"
                                    d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5" />
                            </svg>
                        </button>
                    </div>
                    <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar"
                        aria-labelledby="offcanvasNavbarLabel">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                                aria-label="Close"></button>
                        </div>
                        <div class="d-flex d-lg-none gap-4 pt-3 justify-content-center">
                            <a href="{{ route('login') }}" class="btn btn-outline--base d-sm-none">
                                Log In <i class="flaticon-arrow-upper-right"></i>
                            </a>
                            <a href="{{ route('contact') }}" class="btn btn--base-two d-sm-none">
                                Contact Us <i class="flaticon-arrow-upper-right"></i>
                            </a>
                        </div>
                        <div class="offcanvas-body align-items-center">
                            <ul class="navbar-nav justify-content-center flex-grow-1">
                                <li class="nav-item">
                                    <a class="nav-link" href="/">Home</a>
                                </li>
                                <li class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        Company
                                    </a>
                                    <ul class="dropdown-menu fade-down">
                                        <li><a class="dropdown-item" href="{{ route('about') }}">About Us</a></li>
                                        <li><a class="dropdown-item" href="{{ route('faq') }}">FAQ</a></li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>
        </header>
        <!--==========================  Header End  ==========================-->


        <!-- Main content area -->
        @yield('content')


        <!-- ==================== Footer Start Here ==================== -->
        <footer class="footer-area">
            <div class="py-120">
                <div class="container position-relative">
                    <div class="footer-shape d-none d-lg-block">
                        <img src="assets/svg/twostar.svg" alt="star">
                        <img src="assets/svg/chart.svg" alt="chart">
                    </div>
                    <div class="row justify-content-center gy-5">
                        <div class="col-xl-4 col-lg-6">
                            <div class="footer-item footer-logo-con pe-xl-5">
                                <div class="footer-item__logo">
                                    <a href="/"> <img src="{{ asset('storage/' . $settings->logo) }}"
                                            alt="{{ $settings->site_name }}" class="logo-img"></a>
                                </div>
                                <p class="footer-item__desc">
                                    {{ $settings->site_name }}, we believe is the great traders aren't born they’re
                                    built through the
                                    right
                                    tools, education, and support. Our mission is simple.
                                </p>
                            </div>
                        </div>
                        <div class="col-xl-8">
                            <div class="footer-widget__content ">
                                <div class="footer-item">
                                    <h5 class="footer-item__title">Company</h5>
                                    <ul class="footer-menu">
                                        <li class="footer-menu__item"> <a href="{{ route('about') }}"
                                                class="footer-menu__link">
                                                About Us
                                            </a> </li>
                                    </ul>
                                </div>
                                <div class="footer-item">
                                    <h5 class="footer-item__title">Contact Us</h5>
                                    <ul class="footer-contact-menu">
                                        <li class="footer-contact-menu__item">
                                            <div class="footer-contact-menu__item-icon">
                                                <i class="fa-solid fa-location-dot"></i>
                                            </div>
                                            <div class="footer-contact-menu__item-content">
                                                <p>New Street 243 West Victoria Vip Road 3527 Canada</p>
                                            </div>
                                        </li>
                                        <li class="footer-contact-menu__item">
                                            <a href="https://photoclerks.com/cdn-cgi/l/email-protection#e28b8c848dcc9a969083869ba2858f838b8ecc818d8f"
                                                class="d-flex">
                                                <div class="footer-contact-menu__item-icon">
                                                    <i class="fa-solid fa-envelope"></i>
                                                </div>
                                                <div class="footer-contact-menu__item-content">
                                                    <p><span class="__cf_email__"
                                                            data-cfemail="b4dddad2db9accc0c6d5d0cdf4d3d9d5ddd89ad7dbd9">{{ $settings->contact_email }}</span>
                                                    </p>
                                                </div>
                                            </a>
                                        </li>
                                        <li class="footer-contact-menu__item">
                                            <a href="tel:+8243944562" class="d-flex">
                                                <div class="footer-contact-menu__item-icon">
                                                    <i class="fa-solid fa-phone-volume"></i>
                                                </div>
                                                <div class="footer-contact-menu__item-content">
                                                    <p>(+1) 824 394 4562</p>
                                                </div>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Footer Top End-->

            <!-- bottom Footer -->
            <div class="bottom-footer py-4">
                <div class="container">
                    <div class="row gy-3">
                        <div class="col-md-5 order-1 order-md-0">
                            <div class="bottom-footer-text text-white order-1 order-md-0 text-center text-md-start">
                                <a href="/">{{ $settings->site_name }}</a>
                                &copy; {{ date('Y') }}. All Rights Reserved.
                            </div>
                        </div>
                        <div class="col-md-7">
                            <nav
                                class="d-flex justify-content-md-end gap-4 row-gap-2 justify-content-center flex-wrap">
                                <a href="{{ route('privacy') }}" class="fs-16 text-white">Privacy Policy</a>
                                <a href="{{ route('terms') }}" class="fs-16 text-white">Terms & Conditions</a>
                                <a href="{{ route('contact') }}" class="fs-16 text-white">Contact Us</a>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <!-- ==================== Footer End Here ==================== -->
    </div>
    <div class="progress-wrap">
        <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
            <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
        </svg>
    </div>

    <!-- Tidio Integration -->
    @include('livechat')
    @include('layouts.lang')

    <!-- Jquery js -->
    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
    <!-- gsap Js -->
    <script src="{{ asset('assets/js/gsap.min.js') }}"></script>
    <script src="{{ asset('assets/js/ScrollTrigger.js') }}"></script>
    <!-- Bootstrap Bundle Js -->
    <script src="{{ asset('assets/js/boostrap.bundle.min.js') }}"></script>
    <!-- Scroll Reveal Js -->
    <script src="{{ asset('assets/js/scrollreveal.min.js') }}"></script>
    <!-- Swiper Bundle Js -->
    <script src="{{ asset('assets/js/swiper-bundle.min.js') }}"></script>
    <!-- Fancy Box js -->
    <script src="{{ asset('assets/js/fancybox.umd.js') }}"></script>
    <!-- Odometer js -->
    <script src="{{ asset('assets/js/odometer.min.js') }}"></script>

    <!-- main js -->
    <script src="{{ asset('assets/js/main.js') }}"></script>

</body>

</html>
