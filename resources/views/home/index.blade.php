@extends('layouts.base')

@section('title', $settings->site_name . ' - Automated Trading Bots for Stocks and Cryptos')

@section('content')


    <!--==========================  Banner Section Start  ==========================-->
    <section class="banner-one-section bg--black">
        <div class="container position-relative">
            <div class="shape-icon d-none d-lg-block">
                <img src="assets/svg/twostar.svg" alt="twostar">
                <img src="assets/svg/star-4.svg" alt="star">
                <img src="assets/svg/star-4.svg" alt="star">
            </div>
            <div class="row row-gap-5 justify-content-between">
                <div class="col-lg-6 col-xl-5 align-self-center order-1 order-lg-0">
                    <div class="banner_content">
                        <div class="banner_content--sub text--base fs-20 fw-medium right-reveal">
                            <img src="assets/svg/arrow-right.svg" alt="arrow">
                            Invest Smart, Trade Smarter
                        </div>
                        <h1 class="right-reveal">
                            Professional Forex and Stock
                            <span class="text--base">Trading Investments</span>
                            <i class="flaticon-compass text--base-two"></i>
                        </h1>
                        <p class="fs-18 fw-medium right-reveal">Whether you're just starting or you're a
                            seasoned trader, our platform offers comprehensive
                            secure.</p>
                        <div class="d-flex flex-wrap gap-4">
                            <a href="{{ route('login') }}" class="btn btn--base-two right-reveal">
                                Start Trading <i class="flaticon-arrow-upper-right"></i>
                            </a>
                            <a href="{{ route('register') }}" class="btn btn-outline--base right-reveal">
                                Create Account <i class="flaticon-arrow-upper-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 align-self-center">
                    <div class="banner-img scaleUp">
                        <img src="assets/images/banner/banner-img.png" alt="banner">
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--==========================  Banner Section End  ==========================-->
    <!--==========================  About Section Start  ==========================-->
    <section class="about-section bg--black-two py-120">
        <div class="container position-relative">
            <div class="about-shape d-none d-lg-block">
                <img src="assets/svg/twostar.svg" alt="star">
                <img src="assets/svg/star-1.svg" alt="star">
            </div>
            <div class="row row-gap-5">
                <div class="col-xl-6 align-self-center">
                    <div class="about-img pe-xl-4 scaleUp">
                        <img src="assets/images/about/about1.png" alt="about image" class="img-fluid ">
                    </div>
                </div>
                <div class="col-xl-6 align-self-center">
                    <div class="section-content">
                        <h6 class="right-reveal">About Us</h6>
                        <h2 class="right-reveal">Building Better Traders One Trade at a Time</h2>
                        <p class="right-reveal">{{ $settings->site_name }}, we believe great traders aren't born they’re
                            built through the right tools, education, and support. Our mission is simple: to
                            empower every client with the resources they need to grow, succeed, and master the
                            financial markets.
                            We combine cutting-edge trading technology.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-4 my-4 my-lg-5">
                        <div class="info-box right-reveal">
                            <div class="fs-40 text--base fw-bold">
                                <span class="odometer" data-odometer-final="10">5</span>+
                                Years
                            </div>
                            <p>Consulting Experience</p>
                        </div>
                        <div class="info-box right-reveal">
                            <div class="fs-40 text--base fw-bold">
                                <span class="odometer" data-odometer-final="36">10</span>k+
                            </div>
                            <p>Satisfied Customers</p>
                        </div>
                    </div>
                    <a href="{{ route('about') }}" class="btn btn--base-two right-reveal">
                        Explore More <i class="flaticon-arrow-upper-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>
    <!--==========================  About Section End  ==========================-->
    <!--==========================  Services Section Start  ==========================-->
    <section class="services-section bg--black py-120">
        <div class="container position-relative">
            <div class="services-shape d-none d-lg-block">
                <img src="assets/svg/balance-1.svg" alt="balance">
                <img src="assets/svg/balance-2.svg" alt="balance">
            </div>
            <div class="row justify-content-center">
                <div class="col-xl-6 col-lg-8">
                    <div class="section-content text-center">
                        <h6 class="top-reveal">Services</h6>
                        <h2 class="top-reveal">Comprehensive Services <br class="d-none d-xl-block"> for
                            Every Trader</h2>
                        <p class="top-reveal">{{ $settings->site_name }}, we believe great traders aren't born they’re
                            built through the right tools, education, and support. Our mission is simple: to
                            empower every client with the resources they need.
                        </p>
                    </div>
                </div>
            </div>
            <div class="row row-gap-4 mt-60 justify-content-center">
                <div class="col-12 position-relative bottom-reveal">
                    <div class="services-slider swiper">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <div class="services-box">
                                    <div class="icon">
                                        <i class="flaticon-balance-sheet"></i>
                                    </div>
                                    <h3><a href="#">Equity Trading</a></h3>
                                    <p>{{ $settings->site_name }} is more than a trading platform it's a gateway to the
                                        mastering the financial markets Built for traders.</p>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="services-box">
                                    <div class="icon">
                                        <i class="flaticon-chart"></i>
                                    </div>
                                    <h3><a href="#">Market Analysis</a></h3>
                                    <p>{{ $settings->site_name }} is more than a trading platform it's a gateway to the
                                        mastering the financial markets Built for traders.</p>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="services-box">
                                    <div class="icon">
                                        <i class="flaticon-forex"></i>
                                    </div>
                                    <h3><a href="#">Forex Trading</a></h3>
                                    <p>{{ $settings->site_name }} is more than a trading platform it's a gateway to the
                                        mastering the financial markets Built for traders.</p>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="services-box">
                                    <div class="icon">
                                        <i class="flaticon-chart-1"></i>
                                    </div>
                                    <h3><a href="#">Stock Market</a></h3>
                                    <p>{{ $settings->site_name }} is more than a trading platform it's a gateway to the
                                        mastering the financial markets Built for traders.</p>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="services-box">
                                    <div class="icon">
                                        <i class="flaticon-graph"></i>
                                    </div>
                                    <h3><a href="#">Technical Analysis</a></h3>
                                    <p>{{ $settings->site_name }} is more than a trading platform it's a gateway to the
                                        mastering the financial markets Built for traders.</p>
                                </div>
                            </div>
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                    <div class="slider-btn">
                        <div class="btn-slider-prev services-prev">
                            <i class="fa-solid fa-chevron-left"></i>
                        </div>
                        <div class="btn-slider-next services-next">
                            <i class="fa-solid fa-chevron-right"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--==========================  Services Section End  ==========================-->
    <!--==========================  Trade Section Start  ==========================-->
    <section class="trade-section bg--black-two py-120">
        <div class="container position-relative">
            <div class="trade-shape d-none d-lg-block">
                <img src="assets/svg/twostar.svg" alt="start">
                <img src="assets/svg/calculator.svg" alt="start">
            </div>
            <div class="row row-gap-5">
                <div class="col-xl-6 align-self-center">
                    <div class="trade-img me-xl-4 scaleUp">
                        <img src="assets/images/trade/trade.png" alt="trade" class="img-fluid">
                    </div>
                </div>
                <div class="col-xl-6 align-self-center">
                    <div class="section-content">
                        <h6 class="right-reveal">Trade on Our</h6>
                        <h2 class="right-reveal">Powerful All-in-One Trading Platform Built for Performance</h2>
                        <p class="right-reveal">{{ $settings->site_name }}, we believe great traders aren't born they’re
                            built through the right tools, education, and support. Our mission is simple: to
                            empower every client with the resources they need to grow, succeed, and master the
                            financial markets.
                            We combine cutting-edge trading technology.</p>
                    </div>
                    <ul class="trade-list py-4 mt-2 mb-3 ">
                        <li class="right-reveal">Forex Market Access</li>
                        <li class="right-reveal">Commodities & Indices</li>
                        <li class="right-reveal">Real-Time Market Data</li>
                        <li class="right-reveal">Stock & Equity Trading</li>
                        <li class="right-reveal">Cryptocurrency Pairs</li>
                        <li class="right-reveal">Advanced Charting Tools</li>
                    </ul>
                    <a href="{{ route('register') }}" class="btn btn--base-two right-reveal">
                        Sing Up Now <i class="flaticon-arrow-upper-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>
    <!--==========================  Trade Section End  ==========================-->
    <!--==========================  Number Section Start  ==========================-->
    <div class="number-section py-120">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="number-count">
                        <div class="number-box left-reveal">
                            <div class="fs-60 fw-bold ">
                                2.<span class="odometer" data-odometer-final="7">2</span>K
                            </div>
                            <p>Money Invested</p>
                        </div>
                        <div class="number-box left-reveal">
                            <div class="fs-60 fw-bold ">
                                <span class="odometer" data-odometer-final="860">660</span>+
                            </div>
                            <p>Expert Traders</p>
                        </div>
                        <div class="number-box left-reveal">
                            <div class="fs-60 fw-bold ">
                                <span class="odometer" data-odometer-final="14">5</span>K
                            </div>
                            <p>Money Invested</p>
                        </div>
                        <div class="number-box left-reveal">
                            <div class="fs-60 fw-bold ">
                                <span class="odometer" data-odometer-final="130">100</span>+
                            </div>
                            <p>Awards</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--==========================  Number Section End  ==========================-->
    <!--==========================  Trade Section Start  ==========================-->
    <section class="trade-smartet-section bg--black py-120">
        <div class="container position-relative">
            <div class="trade-smartet-shape d-none d-lg-block">
                <img src="assets/svg/twostar.svg" alt="star">
                <img src="assets/svg/balance-3.svg" alt="balance">
            </div>
            <div class="row justify-content-center">
                <div class="col-xl-6 col-lg-8">
                    <div class="section-content text-center">
                        <h6 class="top-reveal">Trade Smarter</h6>
                        <h2 class="top-reveal">Trade Smarter with Institutional Grade Speed & Spread</h2>
                        <p class="top-reveal">Trade Mastery, we believe great traders aren't born they’re
                            built through the
                            right tools,
                            education, and support. Our mission is simple: to empower every client with the
                            resources they
                            need to grow, succeed, and master.</p>
                    </div>
                </div>
            </div>
            <div class="row mt-60">
                <div class="col-lg-12">
                    <div class="bg--black-two trabe-info bottom-reveal">
                        <div class="table-nav d-flex flex-wrap gap-4 align-items-center justify-content-between">
                            <ul class="nav nav-pills order-1 order-xl-0" id="pills-tab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#pills-all"
                                        role="tab">all</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pills-forex"
                                        role="tab">forex</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pills-metals"
                                        role="tab">metals</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pills-indices"
                                        role="tab">indices</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pills-commodities"
                                        role="tab">commodities</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pills-stocks"
                                        role="tab">stocks</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="pill" data-bs-target="#pills-cryptos"
                                        role="tab">cryptos</button>
                                </li>
                            </ul>
                        </div>
                        <div class="tab-content mt-4" id="pills-tabContent">
                            <div class="tab-pane fade show active" id="pills-all" role="tabpanel" tabindex="0">
                                <div class="table-responsive">

                                    <div class="trade-table-content">
                                        <div class="trade-content d-grid gap-3">
                                            <ul class="bg--base">
                                                <li>Name</li>
                                                <li>Sell</li>
                                                <li>Buy</li>
                                                <li>Spread</li>
                                                <li></li>
                                            </ul>
                                            <div class="accordion-trave d-grid row-gap-3">
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>EUR/USD</li>
                                                            <li>1.12962</li>
                                                            <li>1.12986</li>
                                                            <li>2.4</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>BGP/USD</li>
                                                            <li>1.32753</li>
                                                            <li>1.32793</li>
                                                            <li>4.0</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>AUD/USD</li>
                                                            <li>0.64371</li>
                                                            <li>0.64403</li>
                                                            <li>3.2</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>CAD/USD</li>
                                                            <li>104.943</li>
                                                            <li>104.983</li>
                                                            <li>4.0</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>USD/CHF</li>
                                                            <li>0.82641</li>
                                                            <li>0.82697</li>
                                                            <li>5.6</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="pills-forex" role="tabpanel" tabindex="0">
                                <div class="table-responsive">

                                    <div class="trade-table-content">
                                        <div class="trade-content d-grid gap-3">
                                            <ul class="bg--base">
                                                <li>Name</li>
                                                <li>Sell</li>
                                                <li>Buy</li>
                                                <li>Spread</li>
                                                <li></li>
                                            </ul>
                                            <div class="accordion-trave d-grid row-gap-3">
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>EUR/USD</li>
                                                            <li>1.12962</li>
                                                            <li>1.12986</li>
                                                            <li>2.4</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>BGP/USD</li>
                                                            <li>1.32753</li>
                                                            <li>1.32793</li>
                                                            <li>4.0</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>AUD/USD</li>
                                                            <li>0.64371</li>
                                                            <li>0.64403</li>
                                                            <li>3.2</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>CAD/USD</li>
                                                            <li>104.943</li>
                                                            <li>104.983</li>
                                                            <li>4.0</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>USD/CHF</li>
                                                            <li>0.82641</li>
                                                            <li>0.82697</li>
                                                            <li>5.6</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="pills-metals" role="tabpanel" tabindex="0">
                                <div class="table-responsive">

                                    <div class="trade-table-content">
                                        <div class="trade-content d-grid gap-3">
                                            <ul class="bg--base">
                                                <li>Name</li>
                                                <li>Sell</li>
                                                <li>Buy</li>
                                                <li>Spread</li>
                                                <li></li>
                                            </ul>
                                            <div class="accordion-trave d-grid row-gap-3">
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>EUR/USD</li>
                                                            <li>1.12962</li>
                                                            <li>1.12986</li>
                                                            <li>2.4</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>BGP/USD</li>
                                                            <li>1.32753</li>
                                                            <li>1.32793</li>
                                                            <li>4.0</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>AUD/USD</li>
                                                            <li>0.64371</li>
                                                            <li>0.64403</li>
                                                            <li>3.2</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>CAD/USD</li>
                                                            <li>104.943</li>
                                                            <li>104.983</li>
                                                            <li>4.0</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>USD/CHF</li>
                                                            <li>0.82641</li>
                                                            <li>0.82697</li>
                                                            <li>5.6</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="pills-indices" role="tabpanel" tabindex="0">
                                <div class="table-responsive">

                                    <div class="trade-table-content">
                                        <div class="trade-content d-grid gap-3">
                                            <ul class="bg--base">
                                                <li>Name</li>
                                                <li>Sell</li>
                                                <li>Buy</li>
                                                <li>Spread</li>
                                                <li></li>
                                            </ul>
                                            <div class="accordion-trave d-grid row-gap-3">
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>EUR/USD</li>
                                                            <li>1.12962</li>
                                                            <li>1.12986</li>
                                                            <li>2.4</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>BGP/USD</li>
                                                            <li>1.32753</li>
                                                            <li>1.32793</li>
                                                            <li>4.0</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>AUD/USD</li>
                                                            <li>0.64371</li>
                                                            <li>0.64403</li>
                                                            <li>3.2</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>CAD/USD</li>
                                                            <li>104.943</li>
                                                            <li>104.983</li>
                                                            <li>4.0</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>USD/CHF</li>
                                                            <li>0.82641</li>
                                                            <li>0.82697</li>
                                                            <li>5.6</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="pills-commodities" role="tabpanel" tabindex="0">
                                <div class="table-responsive">

                                    <div class="trade-table-content">
                                        <div class="trade-content d-grid gap-3">
                                            <ul class="bg--base">
                                                <li>Name</li>
                                                <li>Sell</li>
                                                <li>Buy</li>
                                                <li>Spread</li>
                                                <li></li>
                                            </ul>
                                            <div class="accordion-trave d-grid row-gap-3">
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>EUR/USD</li>
                                                            <li>1.12962</li>
                                                            <li>1.12986</li>
                                                            <li>2.4</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>BGP/USD</li>
                                                            <li>1.32753</li>
                                                            <li>1.32793</li>
                                                            <li>4.0</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>AUD/USD</li>
                                                            <li>0.64371</li>
                                                            <li>0.64403</li>
                                                            <li>3.2</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>CAD/USD</li>
                                                            <li>104.943</li>
                                                            <li>104.983</li>
                                                            <li>4.0</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>USD/CHF</li>
                                                            <li>0.82641</li>
                                                            <li>0.82697</li>
                                                            <li>5.6</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="pills-stocks" role="tabpanel" tabindex="0">
                                <div class="table-responsive">

                                    <div class="trade-table-content">
                                        <div class="trade-content d-grid gap-3">
                                            <ul class="bg--base">
                                                <li>Name</li>
                                                <li>Sell</li>
                                                <li>Buy</li>
                                                <li>Spread</li>
                                                <li></li>
                                            </ul>
                                            <div class="accordion-trave d-grid row-gap-3">
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>EUR/USD</li>
                                                            <li>1.12962</li>
                                                            <li>1.12986</li>
                                                            <li>2.4</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>BGP/USD</li>
                                                            <li>1.32753</li>
                                                            <li>1.32793</li>
                                                            <li>4.0</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>AUD/USD</li>
                                                            <li>0.64371</li>
                                                            <li>0.64403</li>
                                                            <li>3.2</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>CAD/USD</li>
                                                            <li>104.943</li>
                                                            <li>104.983</li>
                                                            <li>4.0</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>USD/CHF</li>
                                                            <li>0.82641</li>
                                                            <li>0.82697</li>
                                                            <li>5.6</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="pills-cryptos" role="tabpanel" tabindex="0">
                                <div class="table-responsive">

                                    <div class="trade-table-content">
                                        <div class="trade-content d-grid gap-3">
                                            <ul class="bg--base">
                                                <li>Name</li>
                                                <li>Sell</li>
                                                <li>Buy</li>
                                                <li>Spread</li>
                                                <li></li>
                                            </ul>
                                            <div class="accordion-trave d-grid row-gap-3">
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>EUR/USD</li>
                                                            <li>1.12962</li>
                                                            <li>1.12986</li>
                                                            <li>2.4</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>BGP/USD</li>
                                                            <li>1.32753</li>
                                                            <li>1.32793</li>
                                                            <li>4.0</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>AUD/USD</li>
                                                            <li>0.64371</li>
                                                            <li>0.64403</li>
                                                            <li>3.2</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>CAD/USD</li>
                                                            <li>104.943</li>
                                                            <li>104.983</li>
                                                            <li>4.0</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="at-item">
                                                    <div class="at-title">
                                                        <ul>
                                                            <li>USD/CHF</li>
                                                            <li>0.82641</li>
                                                            <li>0.82697</li>
                                                            <li>5.6</li>
                                                            <li></li>
                                                        </ul>
                                                    </div>
                                                    <div class="at-tab">
                                                        <div class="tradingChart tradingview-widget-container">
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--==========================  Trade Section End  ==========================-->

    <!--==========================  People Section Start  ==========================-->
    <section class="people-trust-section bg--black py-120">
        <div class="container position-relative">
            <div class="people-shape d-none d-lg-block">
                <img src="{{asset('assets/svg/twostar.svg')}}" alt="star">
                <img src="{{asset('assets/svg/balance-4.svg')}}" alt="star">
            </div>
            <div class="row justify-content-center">
                <div class="col-xxl-6 col-xl-7 col-lg-8">
                    <div class="section-content text-center">
                        <h6 class="top-reveal">People Trust Us</h6>
                        <h2 class="top-reveal">Millions of Users Worldwide</h2>
                        <p class="top-reveal">{{ $settings->site_name }}, we believe great traders aren't born they’re built
                            through the right tools,
                            education, and support. Our mission is simple: to empower.</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 mt-5">
                    <ul class="nav nav-pills gap-4 justify-content-center " id="people-tab" role="tablist">
                        <li class="nav-item bottom-reveal" role="presentation">
                            <button class="nav-link " data-bs-toggle="pill" data-bs-target="#people-one" role="tab">
                                <i class="flaticon-cam-recorder"></i> Test Your Knowledge
                            </button>
                        </li>
                        <li class="nav-item bottom-reveal" role="presentation">
                            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#people-two"
                                role="tab">
                                <i class="flaticon-live-1"></i> Live Commentary
                            </button>
                        </li>
                        <li class="nav-item bottom-reveal" role="presentation">
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#people-three"
                                role="tab">
                                <i class="flaticon-cam-recorder"></i> Tutorial Videos
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="col-12 mt-60">
                    <div class="tab-content bottom-reveal" id="people-tabContent">
                        <div class="tab-pane fade " id="people-one" role="tabpanel" tabindex="0">
                            <a class="people-video video-container" href=""
                                data-fancybox>
                                <img src="{{asset('assets/images/video/video2.jpg')}}" alt="video">
                                <div class="play-button">
                                    <span class="play-icon"><i class="fa fa-solid fa-play"></i></span>
                                </div>
                            </a>
                            <div class="row mt-5 row-gap-5 justify-content-between">
                                <div class="col-xl-5 col-lg-6">
                                    <div class="section-content">
                                        <h2>What you will Learn</h2>
                                        <p>
                                            {{ $settings->site_name }}, we believe is the great traders aren't born they’re
                                            built through the right tools, education, and support. Our mission
                                            is simple to empower every client with the resources and they need
                                            to the grow succeed, and master the financial markets. We combine
                                            cutting-edge trading technology.
                                        </p>
                                    </div>
                                </div>
                                <div class="col-xl-5 col-lg-6">
                                    <ul class="people-list">
                                        <li>Forex Market Access</li>
                                        <li>Stock & Equity Trading</li>
                                        <li>Commodities & Indices</li>
                                        <li>Cryptocurrency Pairs</li>
                                        <li>Forex Market Access</li>
                                        <li>Stock & Equity Trading</li>
                                        <li>Commodities & Indices</li>
                                        <li>Cryptocurrency Pairs</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade show active" id="people-two" role="tabpanel" tabindex="0">
                            <a class="people-video video-container" href=""
                                data-fancybox>
                                <img src="{{asset('assets/images/video/video1.jpg')}}" alt="video">
                                <div class="play-button">
                                    <span class="play-icon"><i class="fa fa-solid fa-play"></i></span>
                                </div>
                            </a>
                            <div class="row mt-5 row-gap-5 justify-content-between">
                                <div class="col-xl-5 col-lg-6">
                                    <div class="section-content">
                                        <h2>What you will Learn</h2>
                                        <p>
                                            {{ $settings->site_name }}, we believe is the great traders aren't born they’re
                                            built through the right tools, education, and support. Our mission
                                            is simple to empower every client with the resources and they need
                                            to the grow succeed, and master the financial markets. We combine
                                            cutting-edge trading technology.
                                        </p>
                                    </div>
                                </div>
                                <div class="col-xl-5 col-lg-6">
                                    <ul class="people-list">
                                        <li>Forex Market Access</li>
                                        <li>Stock & Equity Trading</li>
                                        <li>Commodities & Indices</li>
                                        <li>Cryptocurrency Pairs</li>
                                    </ul>
                                    <div class="row mt-4 row-gap-4">
                                        <div class="col-6">
                                            <div class="people-box">
                                                <h2 class="fw-bold text--base mb-2">6.<span class="odometer"
                                                        data-odometer-final="9">2</span>M</h2>
                                                <p>Monthly Voulme</p>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="people-box">
                                                <h2 class="fw-bold text--base mb-2">8.<span class="odometer"
                                                        data-odometer-final="5">0</span>M</h2>
                                                <p>Yearly Voulme</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="people-three" role="tabpanel" tabindex="0">
                            <a class="people-video video-container" href=""
                                data-fancybox>
                                <img src="{{asset('assets/images/video/video3.jpg')}}" alt="video">
                                <div class="play-button">
                                    <span class="play-icon"><i class="fa fa-solid fa-play"></i></span>
                                </div>
                            </a>
                            <div class="row mt-5 row-gap-5 justify-content-between">
                                <div class="col-xl-5 col-lg-6">
                                    <div class="section-content">
                                        <h2>What you will Learn</h2>
                                        <p>
                                            {{ $settings->site_name }}, we believe is the great traders aren't born they’re
                                            built through the right tools, education, and support. Our mission
                                            is simple to empower every client with the resources and they need
                                            to the grow succeed, and master the financial markets. We combine
                                            cutting-edge trading technology.
                                        </p>
                                    </div>
                                </div>
                                <div class="col-xl-5 col-lg-6">
                                    <ul class="people-list">
                                        <li>Forex Market Access</li>
                                        <li>Stock & Equity Trading</li>
                                        <li>Commodities & Indices</li>
                                        <li>Cryptocurrency Pairs</li>
                                    </ul>
                                    <div class="row mt-4 row-gap-4">
                                        <div class="col-6">
                                            <div class="people-box">
                                                <h2 class="fw-bold text--base mb-2">6.<span class="odometer"
                                                        data-odometer-final="9">2</span>M</h2>
                                                <p>Monthly Voulme</p>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="people-box">
                                                <h2 class="fw-bold text--base mb-2">8.<span class="odometer"
                                                        data-odometer-final="5">0</span>M</h2>
                                                <p>Yearly Voulme</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--==========================  People Section End  ==========================-->
    <!--==========================  Testimonial Section Start  ==========================-->
    <section class="testimonial-section bg--black-two py-120">
        <div class="container position-relative">
            <div class="testimonial-shape d-none d-lg-block">
                <img src="assets/svg/twostar.svg" alt="star">
                <img src="assets/svg/maneyPlant.svg" alt="money Plant">
            </div>
            <div class="row row-gap-2">
                <div class="col-lg-6 align-self-end">
                    <div class="section-content">
                        <h6 class="right-reveal">Testimonial</h6>
                        <h2 class="mb-2 right-reveal">Our Clients Says</h2>
                    </div>
                </div>
                <div class="col-lg-6 align-self-end">
                    <div class="section-content">
                        <p class="right-reveal">{{ $settings->site_name }}, we believe great traders aren't born they’re
                            built through the right tools,
                            education, and support. Our mission is simple: to empower every client with the
                            resources they
                            need to grow, succeed, and master.</p>
                    </div>
                </div>
            </div>
            <div class="row mt-60">
                <div class="col-12 position-relative bottom-reveal">
                    <div class="testimonial-slide swiper">
                        <div class="swiper-wrapper">
                            <div class="swiper-slide">
                                <div class="testimonial-item">
                                    <div class="start-client">
                                        <img src="assets/svg/star-5.svg" alt="star">
                                    </div>
                                    <div class="client-body">
                                        <img src="assets/svg/quite.svg" alt="quite">
                                        <p class="client-text">
                                            {{ $settings->site_name }}, we believes great traders aren't born they’re built
                                            through the rights there tools, education, and supports. Our mission
                                            is simple to empower every client.
                                        </p>
                                        <div class="client-info d-flex gap-3 align-self-center">
                                            <img src="assets/images/client/client1.png" alt="client">
                                            <div class="text">
                                                <h4 class="mb-2">Robert Jenkins</h4>
                                                <p>Urban Designer</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="testimonial-item">
                                    <div class="start-client">
                                        <img src="assets/svg/star-5.svg" alt="star">
                                    </div>
                                    <div class="client-body">
                                        <img src="assets/svg/quite.svg" alt="quite">
                                        <p class="client-text">
                                            {{ $settings->site_name }}, we believes great traders aren't born they’re built
                                            through the rights there tools, education, and supports. Our mission
                                            is simple to empower every client.
                                        </p>
                                        <div class="client-info d-flex gap-3 align-self-center">
                                            <img src="assets/images/client/client2.png" alt="client">
                                            <div class="text">
                                                <h4 class="mb-2">Johanna Dach</h4>
                                                <p>Urban Artist</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="testimonial-item">
                                    <div class="start-client">
                                        <img src="assets/svg/star-5.svg" alt="star">
                                    </div>
                                    <div class="client-body">
                                        <img src="assets/svg/quite.svg" alt="quite">
                                        <p class="client-text">
                                            {{ $settings->site_name }}, we believes great traders aren't born they’re built
                                            through the rights there tools, education, and supports. Our mission
                                            is simple to empower every client.
                                        </p>
                                        <div class="client-info d-flex gap-3 align-self-center">
                                            <img src="assets/images/client/client3.png" alt="client">
                                            <div class="text">
                                                <h4 class="mb-2">Harvey Witting</h4>
                                                <p>Urban Designer</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="testimonial-item">
                                    <div class="start-client">
                                        <img src="assets/svg/star-5.svg" alt="star">
                                    </div>
                                    <div class="client-body">
                                        <img src="assets/svg/quite.svg" alt="quite">
                                        <p class="client-text">
                                            {{ $settings->site_name }}, we believes great traders aren't born they’re built
                                            through the rights there tools, education, and supports. Our mission
                                            is simple to empower every client.
                                        </p>
                                        <div class="client-info d-flex gap-3 align-self-center">
                                            <img src="assets/images/client/client1.png" alt="client">
                                            <div class="text">
                                                <h4 class="mb-2">Robert Jenkins</h4>
                                                <p>Urban Designer</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="swiper-slide">
                                <div class="testimonial-item">
                                    <div class="start-client">
                                        <img src="assets/svg/star-5.svg" alt="star">
                                    </div>
                                    <div class="client-body">
                                        <img src="assets/svg/quite.svg" alt="quite">
                                        <p class="client-text">
                                            Trade Mastery, we believes great traders aren't born they’re built
                                            through the rights there tools, education, and supports. Our mission
                                            is simple to empower every client.
                                        </p>
                                        <div class="client-info d-flex gap-3 align-self-center">
                                            <img src="assets/images/client/client2.png" alt="client">
                                            <div class="text">
                                                <h4 class="mb-2">Johanna Dach</h4>
                                                <p>Urban Artist</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                    <div class="slider-btn">
                        <div class="btn-slider-prev testi-prev">
                            <i class="fa-solid fa-chevron-left"></i>
                        </div>
                        <div class="btn-slider-next testi-next">
                            <i class="fa-solid fa-chevron-right"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--==========================  Testimonial Section End  ==========================-->
    <!--==========================  Faq Section Start  ==========================-->
    <section class="faq-section bg--black py-120">
        <div class="container position-relative">
            <div class="faq-shape d-none d-lg-block">
                <img src="assets/svg/bank.svg" alt="bank">
                <img src="assets/svg/balance-5.svg" alt="balance">
            </div>
            <div class="row justify-content-center">
                <div class="col-xl-7 col-lg-8">
                    <div class="section-content text-center">
                        <h6 class="top-reveal">Faq</h6>
                        <h2 class="top-reveal">Frequently Asked Questions</h2>
                        <p class="top-reveal">Trade Mastery, we believe great traders aren't born they’re built
                            through the right tools, education, and support. Our mission is simple: to empower.
                        </p>
                    </div>
                </div>
            </div>
            <div class="row mt-60 row-gap-5">
                <div class="col-lg-6 align-self-center">
                    <img src="assets/images/faq/faq.png" alt="faq" class="img-fluid scaleUp">
                </div>
                <div class="col-lg-6 align-self-center">
                    <div class="accordion custom--accordion" id="faqExample">
                        <div class="accordion-item bottom-reveal">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faqOne" aria-expanded="false" aria-controls="faqOne">
                                    What is the minimum deposit to start trading?
                                </button>
                            </h2>
                            <div id="faqOne" class="accordion-collapse collapse" data-bs-parent="#faqExample">
                                <div class="accordion-body">
                                    <p>
                                        Trade Mastery, we believe is the great traders aren't born they’re built
                                        through the right tools, education, and support. Our mission is simple
                                        to empower every client with the resources.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item bottom-reveal">
                            <h2 class="accordion-header">
                                <button class="accordion-button " type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faqTwo" aria-expanded="true" aria-controls="faqTwo">
                                    Do you offer a demo account for practice trading?
                                </button>
                            </h2>
                            <div id="faqTwo" class="accordion-collapse collapse show" data-bs-parent="#faqExample">
                                <div class="accordion-body">
                                    <p>
                                        Trade Mastery, we believe is the great traders aren't born they’re built
                                        through the right tools, education, and support. Our mission is simple
                                        to empower every client with the resources.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item bottom-reveal">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faqThree" aria-expanded="false" aria-controls="faqThree">
                                    What platforms can I use to trade with your broker?
                                </button>
                            </h2>
                            <div id="faqThree" class="accordion-collapse collapse" data-bs-parent="#faqExample">
                                <div class="accordion-body">
                                    <p>
                                        Trade Mastery, we believe is the great traders aren't born they’re built
                                        through the right tools, education, and support. Our mission is simple
                                        to empower every client with the resources.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item bottom-reveal">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faqFour" aria-expanded="false" aria-controls="faqFour">
                                    Are my funds secure with your company?
                                </button>
                            </h2>
                            <div id="faqFour" class="accordion-collapse collapse" data-bs-parent="#faqExample">
                                <div class="accordion-body">
                                    <p>
                                        Trade Mastery, we believe is the great traders aren't born they’re built
                                        through the right tools, education, and support. Our mission is simple
                                        to empower every client with the resources.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item bottom-reveal">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#faqFive" aria-expanded="false" aria-controls="faqFive">
                                    What trading instruments do you offer?
                                </button>
                            </h2>
                            <div id="faqFive" class="accordion-collapse collapse" data-bs-parent="#faqExample">
                                <div class="accordion-body">
                                    <p>
                                        Trade Mastery, we believe is the great traders aren't born they’re built
                                        through the right tools, education, and support. Our mission is simple
                                        to empower every client with the resources.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--==========================  Faq Section End  ==========================-->
    <!--==========================  Blog Section Start  ==========================-->
    <section class="blog-section bg--black-two py-120">
        <div class="container position-relative">
            <div class="blog-shape d-none d-lg-block">
                <img src="assets/svg/balance-6.svg" alt="balance">
                <img src="assets/svg/maneyPlant.svg" alt="balance">
            </div>
            <div class="row row-gap-4">
                <div class="col-lg-6 align-self-center">
                    <div class="section-content">
                        <h6 class="right-reveal">Blogs</h6>
                        <h2 class="mb-0 right-reveal">News & Analysis</h2>
                    </div>
                </div>
            </div>
            <div class="row mt-60 row-gap-4">
                <div class="col-lg-6 right-reveal">
                    <div class="blog-item">
                        <img src="assets/images/blog/blog1.jpg" alt="blog" class="img-fluid w-100">
                        <ul class="blog-list">
                            <li>Ai Trends</li>
                        </ul>
                        <h4>
                            <a href="#">
                                The Difference Between Fixed and Variable Spreads Explained
                            </a>
                        </h4>
                    </div>
                </div>
                <div class="col-lg-6 left-reveal">
                    <div class="blog-item">
                        <img src="assets/images/blog/blog2.jpg" alt="blog" class="img-fluid w-100">
                        <ul class="blog-list">
                            <li>Ai Trends</li>
                        </ul>
                        <h4>
                            <a href="#">
                                Why Risk Management Is the Most Important Trading Skill
                            </a>
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--==========================  Blog Section End  ==========================-->
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
