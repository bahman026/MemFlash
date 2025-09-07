<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" href="{{ asset('assets/images/favicon-300x300.png') }}" sizes="32x32">
    <link rel="icon" href="{{ asset('assets/images/favicon-150x150.png') }}" sizes="192x192">

    {{-- SEO Meta Tags --}}
    <title>MemFlash - Smart Flashcard Learning Platform</title>
    <meta name="description" content="MemFlash is a powerful flashcard learning platform that helps you memorize information effectively using spaced repetition and smart study techniques.">
    <meta name="keywords" content="flashcards, learning, memorization, spaced repetition, study, education, memory, quiz">
    <meta name="author" content="MemFlash">
    
    {{-- Open Graph Meta Tags --}}
    <meta property="og:title" content="MemFlash - Smart Flashcard Learning Platform">
    <meta property="og:description" content="MemFlash is a powerful flashcard learning platform that helps you memorize information effectively using spaced repetition and smart study techniques.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('assets/images/og-image.png') }}">
    <meta property="og:site_name" content="MemFlash">
    
    {{-- Twitter Card Meta Tags --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="MemFlash - Smart Flashcard Learning Platform">
    <meta name="twitter:description" content="MemFlash is a powerful flashcard learning platform that helps you memorize information effectively using spaced repetition and smart study techniques.">
    <meta name="twitter:image" content="{{ asset('assets/images/og-image.png') }}">
    
    {{-- Additional SEO Meta Tags --}}
    <meta name="robots" content="index, follow">
    <meta name="googlebot" content="index, follow">
    <link rel="alternate" hreflang="en" href="{{ url()->current() }}">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* Mobile-specific improvements */
        @media (max-width: 640px) {
            button, a {
                min-height: 44px;
                min-width: 44px;
            }
            .overflow-auto {
                -webkit-overflow-scrolling: touch;
            }
            input, textarea, select {
                font-size: 16px;
            }
        }

        /* Smooth transitions for all interactive elements */
        button, a, .hover\:shadow-md:hover {
            transition: all 0.2s ease-in-out;
        }

        /* Better focus states for accessibility */
        button:focus, a:focus {
            outline: 2px solid #0ea5e9;
            outline-offset: 2px;
        }

    </style>
</head>

<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-14 sm:h-16">
                <div class="flex items-center min-w-0 flex-1">
                    <div class="flex-shrink-0">
                        <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900">📚 MemFlash</h1>
                    </div>
                </div>

                <div class="flex items-center space-x-1 sm:space-x-4">
                    @auth
                        <!-- User Avatar and Name - Mobile -->
                        <div class="flex items-center space-x-2 sm:hidden">
                            <div class="flex-shrink-0">
                                @if(auth()->user()->avatar)
                                <img class="h-8 w-8 rounded-full ring-2 ring-gray-200" src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}">
                            @else
                                <div class="h-8 w-8 rounded-full bg-primary-500 flex items-center justify-center ring-2 ring-gray-200">
                                        <span class="text-white text-xs font-medium">
                                            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- User Avatar and Name - Desktop -->
                        <div class="hidden sm:flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                @if(auth()->user()->avatar)
                                <img class="h-9 w-9 lg:h-10 lg:w-10 rounded-full ring-2 ring-gray-200" src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}">
                            @else
                                <div class="h-9 w-9 lg:h-10 lg:w-10 rounded-full bg-primary-500 flex items-center justify-center ring-2 ring-gray-200">
                                    <span class="text-white text-sm font-medium">
                                        {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        <div class="hidden lg:block min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate max-w-32">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate max-w-32">{{ auth()->user()->email }}</p>
                        </div>
                        </div>

                        <!-- Dashboard Button -->
                        <a href="{{ route('dashboard') }}" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                            Dashboard
                        </a>

                        <!-- Logout Button -->
                        <form method="POST" action="{{ route('logout') }}" class="hidden sm:inline">
                            @csrf
                            <button type="submit" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                                Logout
                            </button>
                        </form>
                    @else
                        <!-- Login Button -->
                        <a href="{{ route('login.page') }}" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                            Login
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <main class="max-w-7xl mx-auto py-12 sm:py-16 lg:py-20 px-3 sm:px-4 lg:px-6 xl:px-8">
        <div class="text-center">
            <!-- Hero Content -->
            <div class="max-w-4xl mx-auto">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 mb-6">
                    Master Any Subject with
                    <span class="text-primary-500">Smart Flashcards</span>
                </h1>
                
                <p class="text-lg sm:text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                    MemFlash uses scientifically-proven spaced repetition to help you memorize information faster and retain it longer. Create, study, and master any subject with our intelligent flashcard system.
                </p>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-12">
                    @auth
                        <a href="{{ route('dashboard') }}" class="w-full sm:w-auto bg-primary-500 hover:bg-primary-600 text-white px-8 py-3 rounded-lg text-lg font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                            Go to Dashboard
                        </a>
                    @else
                        <a href="{{ route('login.page') }}" class="w-full sm:w-auto bg-primary-500 hover:bg-primary-600 text-white px-8 py-3 rounded-lg text-lg font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                            Get Started Free
                        </a>
                    @endauth
                    
                    <a href="#features" class="w-full sm:w-auto border border-gray-300 text-gray-700 hover:bg-gray-50 px-8 py-3 rounded-lg text-lg font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                        Learn More
                    </a>
                </div>

                <!-- Hero Image/Illustration -->
                <div class="relative max-w-4xl mx-auto mb-16">
                    <div class="bg-gradient-to-br from-primary-50 to-primary-100 rounded-2xl p-8 sm:p-12">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-center">
                            <div class="text-left">
                                <h3 class="text-2xl font-bold text-gray-900 mb-4">
                                    Spaced Repetition Algorithm
                                </h3>
                                <p class="text-gray-600 mb-6">
                                    Our intelligent system schedules reviews at optimal intervals to maximize retention and minimize study time.
                                </p>
                                <ul class="space-y-2 text-sm text-gray-600">
                                    <li class="flex items-center">
                                        <svg class="w-4 h-4 text-primary-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        Scientifically proven method
                                    </li>
                                    <li class="flex items-center">
                                        <svg class="w-4 h-4 text-primary-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        Adaptive learning schedule
                                    </li>
                                    <li class="flex items-center">
                                        <svg class="w-4 h-4 text-primary-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        Progress tracking
                                    </li>
                                </ul>
                            </div>
                            <div class="relative">
                                <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-500">Card 1 of 25</span>
                                            <span class="text-sm text-primary-500 font-medium">Easy</span>
                                        </div>
                                        <div class="h-32 bg-gray-50 rounded-lg flex items-center justify-center">
                                            <span class="text-gray-500">What is the capital of France?</span>
                                        </div>
                                        <div class="flex space-x-2">
                                            <button class="flex-1 bg-red-100 text-red-600 py-2 px-3 rounded text-sm font-medium">
                                                Hard
                                            </button>
                                            <button class="flex-1 bg-yellow-100 text-yellow-600 py-2 px-3 rounded text-sm font-medium">
                                                Good
                                            </button>
                                            <button class="flex-1 bg-green-100 text-green-600 py-2 px-3 rounded text-sm font-medium">
                                                Easy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <section id="features" class="py-16 sm:py-20">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-16">
                    <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                        Why Choose MemFlash?
                    </h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                        Our platform combines cutting-edge learning science with an intuitive interface to maximize your study efficiency.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-200">
                        <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Smart Algorithm</h3>
                        <p class="text-gray-600">Advanced spaced repetition algorithm that adapts to your learning pace and optimizes review timing.</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-200">
                        <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Fast Learning</h3>
                        <p class="text-gray-600">Study more efficiently with our optimized learning system that reduces study time while improving retention.</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-200">
                        <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Progress Tracking</h3>
                        <p class="text-gray-600">Monitor your learning progress with detailed analytics and insights to stay motivated and on track.</p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-200">
                        <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Customizable</h3>
                        <p class="text-gray-600">Create custom decks, import from various formats, and personalize your learning experience.</p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-200">
                        <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Mobile Ready</h3>
                        <p class="text-gray-600">Study anywhere, anytime with our responsive design that works perfectly on all devices.</p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-200">
                        <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Collaborative</h3>
                        <p class="text-gray-600">Share decks with friends, join study groups, and learn together with our collaborative features.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-16 sm:py-20">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                    Ready to Start Learning?
                </h2>
                <p class="text-lg text-gray-600 mb-8">
                    Join thousands of learners who are already using MemFlash to master new subjects and retain information better.
                </p>
                
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center bg-primary-500 hover:bg-primary-600 text-white px-8 py-4 rounded-lg text-lg font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                        Go to Dashboard
                        <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>
                @else
                    <a href="{{ route('login.page') }}" class="inline-flex items-center bg-primary-500 hover:bg-primary-600 text-white px-8 py-4 rounded-lg text-lg font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                        Get Started Free
                        <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>
                @endauth
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto py-8 px-3 sm:px-4 lg:px-6 xl:px-8">
            <div class="text-center">
                <p class="text-gray-500">
                    © {{ date('Y') }} MemFlash. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
