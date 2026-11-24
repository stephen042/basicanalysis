<!DOCTYPE html>
<html lang="en" class="">

<head>
    <meta charset="utf-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $settings->site_name }} | @yield('title')</title>

    @section('styles')
        <link rel="icon" href="{{ asset('storage/' . $settings->favicon) }}" type="image/png" />

        <!-- Tailwind CSS -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        colors: {
                            primary: {
                                DEFAULT: 'rgb(21, 149, 125)', // #15957d main
                                dark: 'rgb(245, 192, 109)', // --s1 from main theme
                                '50': 'rgba(21, 149, 125, 0.05)',
                                '100': 'rgba(21, 149, 125, 0.1)',
                                '200': 'rgba(21, 149, 125, 0.2)',
                                '300': 'rgba(21, 149, 125, 0.3)',
                                '400': 'rgba(21, 149, 125, 0.4)',
                                '500': 'rgb(21, 149, 125)',
                                '600': 'rgba(21, 149, 125, 0.6)',
                                '700': 'rgba(21, 149, 125, 0.7)',
                                '800': 'rgba(21, 149, 125, 0.8)',
                                '900': 'rgba(21, 149, 125, 0.9)',
                                '950': 'rgb(17, 120, 100)'
                            },
                            dark: {
                                100: '#0D0E10', // --nb1: Border / divider color
                                200: '#1e1e1e', // --nb2: Card background
                                300: '#121212', // --nb3: Sidebar / section background
                                400: '#121212', // --nb4: Main background
                                500: '#0D0E10', // --nb5: Deep black background
                            }
                        },
                        fontFamily: {
                            sans: ['Inter', 'sans-serif'],
                        },
                    }
                }
            }
        </script>


        <!-- Mobile bottom navigation and safe-area layout -->
        <style>
            @@media screen and (max-width: 1023px) {
                html,
                body {
                    overflow-x: hidden;
                }

                body {
                    position: relative;
                    height: 100%;
                    overscroll-behavior-y: contain;
                    -webkit-overflow-scrolling: touch;
                }

                input,
                select,
                textarea {
                    font-size: 16px !important;
                }

                main {
                    padding-bottom: calc(76px + env(safe-area-inset-bottom, 0px)) !important;
                }

                aside {
                    padding-bottom: calc(76px + env(safe-area-inset-bottom, 0px)) !important;
                }

                aside .flex.flex-col.h-full {
                    height: calc(100vh - 76px - env(safe-area-inset-bottom, 0px)) !important;
                }

                .mobile-nav-container {
                    padding-bottom: env(safe-area-inset-bottom, 0px);
                    padding-left: env(safe-area-inset-left, 0px);
                    padding-right: env(safe-area-inset-right, 0px);
                    background: linear-gradient(180deg, rgba(35, 38, 39, 0.99), #121212);
                    border-top: 1px solid rgba(245, 192, 109, 0.35);
                    box-shadow: 0 -8px 24px rgba(0, 0, 0, 0.38);
                    -webkit-transform: translateZ(0);
                    transform: translateZ(0);
                }

                .mobile-nav-inner {
                    display: flex;
                    min-height: 68px;
                    align-items: center;
                    justify-content: space-around;
                    padding: 5px 8px 4px;
                }

                .mobile-nav-item {
                    display: flex;
                    min-height: 56px;
                    flex: 1 1 0;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    gap: 4px;
                    color: rgb(var(--nw2));
                    text-decoration: none;
                    transition: color 150ms ease, background-color 150ms ease, border-color 150ms ease;
                    -webkit-tap-highlight-color: transparent;
                }

                .mobile-nav-item[aria-current="page"] {
                    color: rgb(var(--p1));
                    font-weight: 600;
                    border-radius: 12px;
                    background: linear-gradient(135deg, rgba(154, 217, 83, 0.16), rgba(245, 192, 109, 0.12));
                    box-shadow: inset 0 0 0 1px rgba(154, 217, 83, 0.24);
                }

                .mobile-nav-item:focus-visible {
                    outline: 2px solid rgb(var(--s1));
                    outline-offset: -2px;
                    border-radius: 10px;
                }
            }
        </style>

        <!-- Font Awesome 6 -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

        <!-- Alpine.js -->
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

        <!-- SweetAlert2 -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.min.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.1/dist/sweetalert2.all.min.js"></script>

        <!-- Custom Styles -->
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

            [x-cloak] {
                display: none !important;
            }

            /* Main theme color variables */
            :root {
                --p1: 154, 217, 83;
                /* Primary green */
                --s1: 245, 192, 109;
                /* Secondary yellow */
                --a2: 0, 0, 0;
                /* Black */
                --nb1: 52, 56, 57;
                /* Border color */
                --nb2: 35, 38, 39;
                /* Card background */
                --nb3: 31, 31, 31;
                /* Secondary dark */
                --nb4: 20, 20, 20;
                /* Main dark background */
                --nw1: 254, 254, 254;
                /* White text */
                --nw2: 182, 182, 182;
                /* Light gray text */
            }

            body {
                background-color: rgb(var(--nb4));
                color: rgb(var(--nw2));
            }
        </style>


        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/dt-1.10.21/datatables.min.css" />
    @show
    @livewireStyles
</head>

<body class="bg-dark-400 text-gray-300 antialiased">
    <div x-data="{ sidebarOpen: window.innerWidth >= 1024 }" class="flex h-screen bg-dark-300" x-cloak>
        <!-- Four-item mobile navigation; desktop sidebar remains unchanged. -->
        <nav class="mobile-nav-container fixed inset-x-0 bottom-0 z-50 lg:hidden"
            aria-label="Mobile navigation" style="touch-action: manipulation;">
            <div class="mobile-nav-inner">
                @php
                    $mobileMenuItems = [
                        ['route' => 'deposits', 'icon' => 'fa-solid fa-wallet', 'label' => 'Fund', 'activeRoutes' => ['deposits', 'payment', 'pay.crypto']],
                        ['route' => 'copy-trading.index', 'icon' => 'fa-solid fa-copy', 'label' => 'Copy Trading', 'activeRoutes' => ['copy-trading.*']],
                        ['route' => 'tradinghistory', 'icon' => 'fa-solid fa-clock-rotate-left', 'label' => 'History', 'activeRoutes' => ['tradinghistory', 'accounthistory', 'trading.history', 'copy-trading.history']],
                        ['route' => 'compound-investment.create', 'icon' => 'fa-solid fa-chart-pie', 'label' => 'Compounding', 'activeRoutes' => ['compound-investment.*', 'compoundinterest']],
                    ];
                @endphp
                @foreach ($mobileMenuItems as $item)
                    @php
                        $isActive = false;
                        foreach ($item['activeRoutes'] as $activeRoute) {
                            if (request()->routeIs($activeRoute)) {
                                $isActive = true;
                                break;
                            }
                        }
                    @endphp
                    <a href="{{ route($item['route']) }}"
                        class="mobile-nav-item"
                        @if($isActive) aria-current="page" @endif>
                        <i class="{{ $item['icon'] }} text-lg" aria-hidden="true"></i>
                        <span class="text-[11px] leading-tight text-center">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </nav>
        <!-- Sidebar -->
        <aside x-show="sidebarOpen" @click.away="sidebarOpen = window.innerWidth >= 1024 ? true : false"
            x-transition:enter="transition-all duration-300" x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0" x-transition:leave="transition-all duration-300"
            x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
            class="fixed inset-y-0 left-0 z-50 w-64 bg-dark-200 border-r border-dark-100 shadow-lg lg:static lg:translate-x-0"
            x-cloak>
            @include('user.sidebar')
        </aside>

        <!-- Main content -->
        <div class="flex flex-col flex-1 w-full overflow-y-auto">
            <!-- Top menu -->
            <header class="sticky top-0 z-30 bg-dark-200/80 backdrop-blur-lg border-b border-dark-100">
                @include('user.topmenu')
            </header>

            <!-- Page content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @yield('content')
            </main>

            <!-- Modern Alert Modals -->
            @if (Session::has('success'))
                <div x-data="{ show: true }" x-show="show" x-transition:enter="transition-all duration-300 ease-out"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition-all duration-200 ease-in"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-4" x-init="setTimeout(() => show = false, 5000)"
                    class="fixed top-4 right-4 z-50 max-w-sm w-full" x-cloak>
                    <div class="bg-green-50 border border-green-200 rounded-xl shadow-lg backdrop-blur-sm p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-check text-green-600 text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-sm font-semibold text-green-800">Success!</h3>
                                <p class="text-sm text-green-700 mt-1 leading-relaxed">{{ Session::get('success') }}</p>
                            </div>
                            <button @click="show = false"
                                class="ml-4 flex-shrink-0 w-8 h-8 bg-green-100 hover:bg-green-200 rounded-full flex items-center justify-center text-green-600 hover:text-green-800 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if (Session::has('message'))
                <div x-data="{ show: true }" x-show="show" x-transition:enter="transition-all duration-300 ease-out"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition-all duration-200 ease-in"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-4" x-init="setTimeout(() => show = false, 6000)"
                    class="fixed top-4 right-4 z-50 max-w-sm w-full" x-cloak>
                    <div class="bg-red-50 border border-red-200 rounded-xl shadow-lg backdrop-blur-sm p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-exclamation-triangle text-red-600 text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-sm font-semibold text-red-800">Attention Required</h3>
                                <p class="text-sm text-red-700 mt-1 leading-relaxed">{{ Session::get('message') }}</p>
                            </div>
                            <button @click="show = false"
                                class="ml-4 flex-shrink-0 w-8 h-8 bg-red-100 hover:bg-red-200 rounded-full flex items-center justify-center text-red-600 hover:text-red-800 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if (Session::has('error'))
                <div x-data="{ show: true }" x-show="show" x-transition:enter="transition-all duration-300 ease-out"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition-all duration-200 ease-in"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-4" x-init="setTimeout(() => show = false, 6000)"
                    class="fixed top-4 right-4 z-50 max-w-sm w-full" x-cloak>
                    <div class="bg-red-50 border border-red-200 rounded-xl shadow-lg backdrop-blur-sm p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-times-circle text-red-600 text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-sm font-semibold text-red-800">Error Occurred</h3>
                                <p class="text-sm text-red-700 mt-1 leading-relaxed">{{ Session::get('error') }}</p>
                            </div>
                            <button @click="show = false"
                                class="ml-4 flex-shrink-0 w-8 h-8 bg-red-100 hover:bg-red-200 rounded-full flex items-center justify-center text-red-600 hover:text-red-800 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if (Session::has('info'))
                <div x-data="{ show: true }" x-show="show" x-transition:enter="transition-all duration-300 ease-out"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition-all duration-200 ease-in"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-4" x-init="setTimeout(() => show = false, 5000)"
                    class="fixed top-4 right-4 z-50 max-w-sm w-full" x-cloak>
                    <div class="bg-blue-50 border border-blue-200 rounded-xl shadow-lg backdrop-blur-sm p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-info-circle text-blue-600 text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-sm font-semibold text-blue-800">Information</h3>
                                <p class="text-sm text-blue-700 mt-1 leading-relaxed">{{ Session::get('info') }}</p>
                            </div>
                            <button @click="show = false"
                                class="ml-4 flex-shrink-0 w-8 h-8 bg-blue-100 hover:bg-blue-200 rounded-full flex items-center justify-center text-blue-600 hover:text-blue-800 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if (Session::has('warning'))
                <div x-data="{ show: true }" x-show="show" x-transition:enter="transition-all duration-300 ease-out"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition-all duration-200 ease-in"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-4" x-init="setTimeout(() => show = false, 6000)"
                    class="fixed top-4 right-4 z-50 max-w-sm w-full" x-cloak>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-xl shadow-lg backdrop-blur-sm p-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-exclamation-triangle text-yellow-600 text-lg"></i>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <h3 class="text-sm font-semibold text-yellow-800">Warning</h3>
                                <p class="text-sm text-yellow-700 mt-1 leading-relaxed">{{ Session::get('warning') }}
                                </p>
                            </div>
                            <button @click="show = false"
                                class="ml-4 flex-shrink-0 w-8 h-8 bg-yellow-100 hover:bg-yellow-200 rounded-full flex items-center justify-center text-yellow-600 hover:text-yellow-800 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                                <i class="fas fa-times text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Footer -->
            <footer class="px-6 py-4 mt-auto border-t border-dark-100">
                <div class="flex flex-col md:flex-row justify-between items-center text-sm">

                    <p class="text-gray-400">&copy; {{ date('Y') }} {{ $settings->site_name }}. All Rights
                        Reserved.</p>
                    <div class="mt-2 md:mt-0">
                        <a href="{{ route('terms') }}" class="text-gray-400 hover:text-white mx-2">Terms of
                            Service</a>
                        {{-- <a href="{{ route('privacy') }}" class="text-gray-400 hover:text-white mx-2">Privacy Policy</a> --}}
                        <a href="{{ route('support') }}" class="text-gray-400 hover:text-white mx-2">Contact Us</a>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    @section('scripts')
        <!-- Core JS -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <script type="text/javascript" src="https://cdn.datatables.net/v/bs4/dt-1.10.21/datatables.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

        <!-- Google Translate -->
        <script type="text/javascript" src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit">
        </script>
        <script type="text/javascript">
            function googleTranslateElementInit() {
                new google.translate.TranslateElement({
                    pageLanguage: 'en',
                    layout: google.translate.TranslateElement.InlineLayout.SIMPLE
                }, 'google_translate_element');
            }
        </script>
    @show

    @if ($settings->whatsapp)
        <script type="text/javascript">
            (function() {
                var options = {
                    whatsapp: "{{ $settings->whatsapp }}",
                    call_to_action: "Message us",
                    position: "left",
                };
                var proto = document.location.protocol,
                    host = "getbutton.io",
                    url = proto + "//static." + host;
                var s = document.createElement('script');
                s.type = 'text/javascript';
                s.async = true;
                s.src = url + '/widget-send-button/js/init.js';
                s.onload = function() {
                    WhWidgetSendButton.init(host, proto, options);
                };
                var x = document.getElementsByTagName('script')[0];
                x.parentNode.insertBefore(s, x);
            })();
        </script>
    @endif



    <!-- Mobile Google Translate Initialization -->
    <script type="text/javascript">
        function googleTranslateElementInitMobile() {
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
                multilanguagePage: true
            }, 'google_translate_element_mobile');

            // Hide the icon when translate widget loads
            setTimeout(() => {
                const translateWidget = document.querySelector(
                    '#google_translate_element_mobile .goog-te-gadget-simple');
                const translateIcon = document.getElementById('translate-icon');
                if (translateWidget && translateIcon) {
                    translateIcon.style.display = 'none';
                }
            }, 1000);
        }

        // Initialize mobile translate when page loads
        if (window.innerWidth < 1024) {
            document.addEventListener('DOMContentLoaded', googleTranslateElementInitMobile);
        }

        // Live chat toggle function
        function toggleLiveChat() {
            // Add your live chat toggle logic here
            // This could open a chat widget, modal, or redirect to support
            console.log('Live chat toggled');
            // Example: window.open('your-chat-url', '_blank');
        }
    </script>

    @include('layouts.livechat')
    @livewireScripts
</body>

</html>
