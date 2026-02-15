<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $settings->theme }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $settings->site_name }} | @yield('title')</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('storage/app/public/' . $settings->favicon) }}" type="image/png" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Custom Styles & Tailwind Config -->
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        /* Main theme color variables */
        :root {
            --p1: 25, 150, 119;
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
            background-color: rgb(var(--nb4)) !important;
            color: rgb(var(--nw2)) !important;
        }

        .bg-pattern {
            background-image: url("{{ asset('dash/images/pattern.png') }}");
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }

        /* Custom styles for Google Translate Widget */
        #google_translate_element {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            z-index: 100;
        }

        .goog-te-gadget-simple {
            background-color: rgba(0, 0, 0, 0.2) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            border-radius: 0.5rem !important;
            padding: 0.5rem 0.75rem !important;
            cursor: pointer;
        }

        .goog-te-gadget-simple span {
            color: #e5e7eb !important;
        }

        .goog-te-gadget-icon {
            display: none !important;
        }

        body>.skiptranslate {
            display: none;
        }
    </style>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: {
                            DEFAULT: 'rgb(20, 20, 20)', // --nb4 main dark background
                            '100': 'rgb(52, 56, 57)', // --nb1 border color
                            '200': 'rgb(35, 38, 39)', // --nb2 card background
                            '300': 'rgb(31, 31, 31)', // --nb3 secondary dark
                        },
                        primary: {
                            DEFAULT: 'rgb(25, 150, 119)', // --p1 primary green
                            'dark': 'rgb(245, 192, 109)', // --s1 secondary yellow
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="antialiased bg-dark text-gray-300">
    <div class="login-section min-h-dvh w-full flex items-center justify-center bg-cover bg-center bg-no-repeat bg-fixed px-4 py-12 relative"
        style="background-image: url('https://images.unsplash.com/photo-1559526324-593bc073d938?q=80&w=1170&auto=format&fit=crop&');">

        <div class="fixed inset-0 bg-black/60 backdrop-blur-sm pointer-events-none"></div>

        <div class="relative z-10 w-full max-w-lg my-auto rounded-2xl shadow-2xl border border-white/10"
            style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);">

            <div class="px-8 py-10 sm:px-12">
                <div class="flex justify-end mb-4">
                    <div id="google_translate_element" class="opacity-80 hover:opacity-100 transition-opacity"></div>
                </div>

                <div class="text-center">
                    <a href="/" class="inline-block transform hover:scale-105 transition-transform">
                        <img class="w-auto h-16 mx-auto mb-6 drop-shadow-lg"
                            src="{{ asset('storage/app/public/' . $settings->logo) }}" alt="{{ $settings->site_name }}">
                    </a>
                </div>

                <div class="mt-4 text-white">
                    @yield('content')
                </div>
            </div>

            <div class="h-1 w-full bg-gradient-to-r from-transparent via-blue-500 to-transparent opacity-50"></div>
        </div>
    </div>

    <!-- Google Translate Script -->
    <script type="text/javascript">
        function googleTranslateElementInit() {
            new google.translate.TranslateElement({
                pageLanguage: 'en',
                layout: google.translate.TranslateElement.InlineLayout.SIMPLE
            }, 'google_translate_element');
        }
    </script>
    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit">
    </script>
    @include('livechat')
    @include('layouts.lang')
    @livewireScripts
    <script src="https://cdn.jsdelivr.net/gh/livewire/turbolinks@v0.1.4/dist/livewire-turbolinks.js"
        data-turbolinks-eval="false" data-turbo-eval="false"></script>
</body>

</html>
