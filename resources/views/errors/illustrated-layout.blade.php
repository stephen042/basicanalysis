<!DOCTYPE html>
<html lang="en" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title') - {{ $settings->site_name }}</title>

        <link rel="icon" href="{{ asset('storage/app/public/' . $settings->favicon) }}" type="image/png" />

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
        
        <!-- Font Awesome 6 -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
        
        <!-- Tailwind CSS -->
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                        colors: {
                            primary: {
                                DEFAULT: 'rgb(154, 217, 83)',
                                dark: 'rgb(154, 217, 83)',
                                light: 'rgb(154, 217, 83)',
                            },
                            danger: {
                                DEFAULT: '#ef4444',
                                dark: '#dc2626',
                                light: '#f87171',
                            },
                            warning: {
                                DEFAULT: '#f59e0b',
                                dark: '#d97706',
                                light: '#fbbf24',
                            },
                            dark: {
                                100: '#0D0E10',
                                200: '#1e1e1e',
                                300: '#121212',
                                400: '#121212',
                                500: '#0D0E10',
                            }
                        }
                    }
                }
            }
        </script>

        <!-- Styles -->
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
            
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'Inter', sans-serif;
                -webkit-font-smoothing: antialiased;
                -moz-osx-font-smoothing: grayscale;
                background: #121212;
                color: #e5e7eb;
            }

            /* Animated gradient background */
            .animated-bg {
                background: linear-gradient(135deg, #121212 0%, #1e1e1e 50%, #0D0E10 100%);
                background-size: 400% 400%;
                animation: gradientShift 15s ease infinite;
            }

            @keyframes gradientShift {
                0% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
                100% { background-position: 0% 50%; }
            }

            /* Floating animation for icons */
            @keyframes float {
                0%, 100% { transform: translateY(0px) rotate(0deg); }
                25% { transform: translateY(-20px) rotate(5deg); }
                50% { transform: translateY(-10px) rotate(-5deg); }
                75% { transform: translateY(-15px) rotate(3deg); }
            }

            .float-animation {
                animation: float 6s ease-in-out infinite;
            }

            /* Pulse animation */
            @keyframes pulse {
                0%, 100% { opacity: 1; transform: scale(1); }
                50% { opacity: 0.8; transform: scale(1.05); }
            }

            .pulse-animation {
                animation: pulse 2s ease-in-out infinite;
            }

            /* Glitch effect for error code */
            @keyframes glitch {
                0% { text-shadow: 2px 2px #22c55e, -2px -2px #ef4444; }
                25% { text-shadow: -2px 2px #22c55e, 2px -2px #ef4444; }
                50% { text-shadow: 2px -2px #22c55e, -2px 2px #ef4444; }
                75% { text-shadow: -2px -2px #22c55e, 2px 2px #ef4444; }
                100% { text-shadow: 2px 2px #22c55e, -2px -2px #ef4444; }
            }

            .glitch-effect:hover {
                animation: glitch 0.3s ease-in-out;
            }

            /* Button hover effects */
            .btn-hover {
                position: relative;
                overflow: hidden;
                transition: all 0.3s ease;
            }

            .btn-hover::before {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                width: 0;
                height: 0;
                border-radius: 50%;
                background: rgba(34, 197, 94, 0.2);
                transform: translate(-50%, -50%);
                transition: width 0.6s, height 0.6s;
            }

            .btn-hover:hover::before {
                width: 300px;
                height: 300px;
            }

            .btn-hover:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 30px rgba(34, 197, 94, 0.3);
            }

            /* Particles background */
            .particles {
                position: absolute;
                width: 100%;
                height: 100%;
                overflow: hidden;
                pointer-events: none;
            }

            .particle {
                position: absolute;
                width: 2px;
                height: 2px;
                background: #22c55e;
                border-radius: 50%;
                opacity: 0.5;
                animation: particleFloat 10s infinite ease-in-out;
            }

            @keyframes particleFloat {
                0%, 100% { transform: translateY(0) translateX(0); opacity: 0; }
                10% { opacity: 0.5; }
                90% { opacity: 0.5; }
                100% { transform: translateY(-100vh) translateX(50px); opacity: 0; }
            }

            /* Custom scrollbar */
            ::-webkit-scrollbar {
                width: 8px;
            }

            ::-webkit-scrollbar-track {
                background: #0D0E10;
            }

            ::-webkit-scrollbar-thumb {
                background: #22c55e;
                border-radius: 4px;
            }

            ::-webkit-scrollbar-thumb:hover {
                background: #16a34a;
            }

            /* Responsive text */
            @media (max-width: 640px) {
                .error-code {
                    font-size: 4rem !important;
                }
            }

            /* Loading shimmer effect */
            @keyframes shimmer {
                0% { background-position: -1000px 0; }
                100% { background-position: 1000px 0; }
            }

            .shimmer {
                background: linear-gradient(90deg, transparent, rgba(34, 197, 94, 0.1), transparent);
                background-size: 1000px 100%;
                animation: shimmer 2s infinite;
            }
        </style>
    </head>
    <body class="antialiased animated-bg min-h-screen overflow-x-hidden">
        <!-- Particles Background -->
        <div class="particles">
            <div class="particle" style="left: 10%; animation-delay: 0s;"></div>
            <div class="particle" style="left: 20%; animation-delay: 2s;"></div>
            <div class="particle" style="left: 30%; animation-delay: 4s;"></div>
            <div class="particle" style="left: 40%; animation-delay: 1s;"></div>
            <div class="particle" style="left: 50%; animation-delay: 3s;"></div>
            <div class="particle" style="left: 60%; animation-delay: 5s;"></div>
            <div class="particle" style="left: 70%; animation-delay: 2.5s;"></div>
            <div class="particle" style="left: 80%; animation-delay: 4.5s;"></div>
            <div class="particle" style="left: 90%; animation-delay: 1.5s;"></div>
        </div>

        <!-- Main Container -->
        <div class="relative min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8 py-12">
            <!-- Logo/Header -->
            <div class="absolute top-6 left-6 z-10">
                <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                    <img src="{{ asset('storage/app/public/' . $settings->favicon) }}" alt="Logo" class="w-10 h-10 rounded-lg shadow-lg group-hover:scale-110 transition-transform duration-300">
                    <span class="text-xl font-bold text-white hidden sm:block">{{ $settings->site_name }}</span>
                </a>
            </div>

            <!-- Error Content Card -->
            <div class="max-w-4xl w-full">
                <div class="bg-dark-200 rounded-2xl shadow-2xl border border-dark-100 overflow-hidden backdrop-blur-xl bg-opacity-80">
                    <div class="p-8 sm:p-12 lg:p-16">
                        <div class="flex flex-col lg:flex-row items-center gap-8 lg:gap-12">
                            <!-- Left Side - Error Code & Icon -->
                            <div class="flex-shrink-0 text-center lg:text-left">
                                <!-- Animated Icon -->
                                <div class="mb-6 flex justify-center lg:justify-start">
                                    <div class="relative">
                                        <div class="absolute inset-0 bg-primary rounded-full blur-2xl opacity-30 pulse-animation"></div>
                                        <div class="relative bg-dark-300 rounded-full p-8 border-4 border-primary shadow-xl float-animation">
                                            @yield('icon', '<i class="fas fa-exclamation-triangle text-6xl text-primary"></i>')
                                        </div>
                                    </div>
                                </div>

                                <!-- Error Code -->
                                <div class="error-code text-8xl sm:text-9xl font-black text-white mb-4 glitch-effect leading-none">
                                    @yield('code', '404')
                                </div>

                                <!-- Status Badge -->
                                <div class="inline-flex items-center gap-2 px-4 py-2 bg-danger bg-opacity-20 border border-danger rounded-full">
                                    <span class="w-2 h-2 bg-danger rounded-full pulse-animation"></span>
                                    <span class="text-danger text-sm font-semibold uppercase tracking-wide">@yield('status', 'Error')</span>
                                </div>
                            </div>

                            <!-- Right Side - Message & Actions -->
                            <div class="flex-1 text-center lg:text-left">
                                <!-- Title -->
                                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white mb-4 leading-tight">
                                    @yield('title', 'Oops! Something went wrong')
                                </h1>

                                <!-- Accent Line -->
                                <div class="h-1 w-24 bg-gradient-to-r from-primary to-transparent rounded-full mb-6 mx-auto lg:mx-0 shimmer"></div>

                                <!-- Message -->
                                <p class="text-lg text-gray-400 mb-8 leading-relaxed max-w-lg mx-auto lg:mx-0">
                                    @yield('message', 'The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.')
                                </p>

                                <!-- Error Details (Optional) -->
                                @hasSection('details')
                                <div class="bg-dark-300 rounded-lg p-4 mb-8 border border-dark-100">
                                    <div class="flex items-start gap-3">
                                        <i class="fas fa-info-circle text-primary mt-1"></i>
                                        <div class="text-sm text-gray-400 text-left">
                                            @yield('details')
                                        </div>
                                    </div>
                                </div>
                                @endif

                                <!-- Action Buttons -->
                                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                                    <!-- Primary Button -->
                                    <a href="{{ app('router')->has('home') ? route('home') : url('/') }}" class="btn-hover relative inline-flex items-center justify-center gap-2 px-8 py-4 bg-primary text-white font-semibold rounded-xl shadow-lg transition-all duration-300 hover:bg-primary-dark group">
                                        <i class="fas fa-home group-hover:scale-110 transition-transform"></i>
                                        <span>Go to Homepage</span>
                                    </a>

                                    <!-- Secondary Button -->
                                    <button onclick="window.history.back()" class="btn-hover relative inline-flex items-center justify-center gap-2 px-8 py-4 bg-dark-300 text-gray-300 font-semibold rounded-xl border border-dark-100 shadow-lg transition-all duration-300 hover:bg-dark-100 hover:text-white group">
                                        <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i>
                                        <span>Go Back</span>
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Bottom Stats Bar (Optional) -->
                    <div class="bg-dark-300 border-t border-dark-100 px-8 py-6">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div class="flex items-center gap-2 text-sm text-gray-500">
                                <i class="fas fa-shield-alt text-primary"></i>
                                <span>Secure Trading Platform</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-500">
                                <i class="fas fa-clock text-primary"></i>
                                <span>24/7 Support Available</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-500">
                                <i class="fas fa-users text-primary"></i>
                                <span>Trusted by Thousands</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Text -->
                <div class="text-center mt-8">
                    <p class="text-sm text-gray-500">
                        Need help? Contact us at 
                        <a href="mailto:{{ $settings->contact_email }}" class="text-primary hover:text-primary-light transition-colors">
                            {{ $settings->contact_email }}
                        </a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Additional Scripts -->
        <script>
            // Add more particles dynamically for better effect
            const particlesContainer = document.querySelector('.particles');
            for (let i = 0; i < 20; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 10 + 's';
                particle.style.animationDuration = (10 + Math.random() * 10) + 's';
                particlesContainer.appendChild(particle);
            }
        </script>
    </body>
</html>
