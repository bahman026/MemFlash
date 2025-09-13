<x-layouts.app 
    :show-level="auth()->check()" 
    :show-user="auth()->check()"
    :header-variant="'default'"
    :header-title="'MemFlash'"
    :header-subtitle="null"
>
    <x-slot name="seo">
        {{-- SEO Meta Tags --}}
        <title>MemFlash - Smart Flashcard Learning Platform | Spaced Repetition & Vocabulary Builder</title>
        <meta name="description" content="MemFlash is the ultimate flashcard learning platform for vocabulary building, language learning, and memory enhancement. Use spaced repetition to learn faster with our study app and quiz cards.">
        <meta name="keywords" content="MemFlash, flashcards, spaced repetition, vocabulary, memory app, learn faster, study app, vocabulary builder, language learning, review app, quiz cards, memory enhancement, learning platform">
        <meta name="author" content="MemFlash">
        
        {{-- Open Graph Meta Tags --}}
        <meta property="og:title" content="MemFlash - Smart Flashcard Learning Platform | Spaced Repetition & Vocabulary Builder">
        <meta property="og:description" content="MemFlash is the ultimate flashcard learning platform for vocabulary building, language learning, and memory enhancement. Use spaced repetition to learn faster with our study app and quiz cards.">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:image" content="{{ asset('assets/images/og-image.png') }}">
        <meta property="og:site_name" content="MemFlash">
        
        {{-- Twitter Card Meta Tags --}}
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="MemFlash - Smart Flashcard Learning Platform | Spaced Repetition & Vocabulary Builder">
        <meta name="twitter:description" content="MemFlash is the ultimate flashcard learning platform for vocabulary building, language learning, and memory enhancement. Use spaced repetition to learn faster with our study app and quiz cards.">
        <meta name="twitter:image" content="{{ asset('assets/images/og-image.png') }}">
        
        {{-- Additional SEO Meta Tags --}}
        <meta name="robots" content="index, follow">
        <meta name="googlebot" content="index, follow">
        <link rel="alternate" hreflang="en" href="{{ url()->current() }}">
    </x-slot>

    <x-slot name="headerActions">
        @auth
            <!-- Dashboard Button -->
            <a href="{{ route('dashboard') }}" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                Dashboard
            </a>
        @endauth
    </x-slot>

    <!-- Hero Section -->
    <div class="max-w-7xl mx-auto py-12 sm:py-16 lg:py-20 px-3 sm:px-4 lg:px-6 xl:px-8">
        <div class="text-center">
            <!-- Hero Content -->
            <div class="max-w-4xl mx-auto">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 mb-6">
                    Master Vocabulary & Learn Faster with
                    <span class="text-primary-500">Smart Flashcards</span>
                </h1>
                
                <p class="text-lg sm:text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                    MemFlash is the ultimate vocabulary builder and memory app that uses scientifically-proven spaced repetition to help you learn faster. Perfect for language learning, study sessions, and quiz card reviews.
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
                        Why Choose MemFlash for Your Learning?
                    </h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                        Our vocabulary builder and study app combines cutting-edge spaced repetition science with an intuitive interface to maximize your learning efficiency and memory retention.
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
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Smart Spaced Repetition</h3>
                        <p class="text-gray-600">Advanced spaced repetition algorithm that adapts to your learning pace and optimizes review timing for maximum memory retention.</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-200">
                        <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Learn Faster</h3>
                        <p class="text-gray-600">Study more efficiently with our optimized learning system that reduces study time while improving vocabulary retention and memory.</p>
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
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Vocabulary Builder</h3>
                        <p class="text-gray-600">Create custom flashcard decks, import vocabulary lists, and personalize your language learning experience with our quiz card system.</p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-200">
                        <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Study App</h3>
                        <p class="text-gray-600">Study anywhere, anytime with our mobile-ready study app that works perfectly on all devices for vocabulary practice and quiz reviews.</p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-200">
                        <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Review App</h3>
                        <p class="text-gray-600">Share flashcard decks with friends, join study groups, and review vocabulary together with our collaborative learning features.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-16 sm:py-20">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                    Ready to Learn Faster with MemFlash?
                </h2>
                <p class="text-lg text-gray-600 mb-8">
                    Join thousands of learners who are already using MemFlash to build vocabulary, master language learning, and retain information better with our spaced repetition system.
                </p>
                
                @auth
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center bg-primary-500 hover:bg-primary-600 text-white px-8 py-4 rounded-lg text-lg font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                        Go to Dashboard
                        <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </a>
                @endauth
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto py-8 px-3 sm:px-4 lg:px-6 xl:px-8">
            <div class="text-center">
                <p class="text-gray-500 mb-4">
                    © {{ date('Y') }} MemFlash. All rights reserved.
                </p>
                <p class="text-sm text-gray-400 mb-4">
                    Built with ❤️ for learners everywhere
                </p>
                <div class="flex justify-center">
                    <a href="https://github.com/bahman026/MemFlash" target="_blank" rel="noopener noreferrer" class="inline-flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                        <span class="text-sm">Contribute on GitHub</span>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    @push('scripts')
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
    @endpush
</x-layouts.app>
