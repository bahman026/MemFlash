@auth
    <script>
        window.location.href = "{{ route('dashboard') }}";
    </script>
@else
    <x-layouts.app>
        <x-slot name="seo">
            <title>MemFlash - Learn with Flashcards</title>
            <meta name="description" content="Master any subject with MemFlash's intelligent flashcard system. Create, study, and track your learning progress.">
            <meta property="og:locale" content="en_US">
            <meta property="og:type" content="website">
            <meta property="og:url" content="{{ url()->current() }}">
            <meta property="og:title" content="MemFlash - Learn with Flashcards">
            <meta property="og:description" content="Master any subject with MemFlash's intelligent flashcard system. Create, study, and track your learning progress.">
        </x-slot>

        <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <div class="mb-6 sm:mb-8">
                        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                            📚 Welcome to MemFlash
                        </h1>
                        <p class="text-lg sm:text-xl text-gray-600 mb-6 sm:mb-8">
                            Master any subject with our intelligent flashcard system
                        </p>
                    </div>

                    <div class="bg-white rounded-2xl shadow-xl p-6 sm:p-8 mb-6 sm:mb-8">
                        <h2 class="text-xl sm:text-2xl font-semibold text-gray-900 mb-4 sm:mb-6">Get Started</h2>
                        <p class="text-gray-600 mb-4 sm:mb-6 text-sm sm:text-base">
                            Create personalized flashcards, track your progress, and learn more effectively.
                        </p>
                        <a href="{{ route('login.page') }}"
                           class="inline-flex items-center justify-center w-full sm:w-auto px-6 sm:px-8 py-3 sm:py-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                            <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                            Continue with Google
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 text-left">
                        <div class="bg-white rounded-lg p-4 sm:p-6 shadow-lg">
                            <div class="text-2xl sm:text-3xl mb-3 sm:mb-4">🎯</div>
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-2">Smart Learning</h3>
                            <p class="text-sm sm:text-base text-gray-600">Adaptive algorithms that focus on what you need to learn most.</p>
                        </div>
                        <div class="bg-white rounded-lg p-4 sm:p-6 shadow-lg">
                            <div class="text-2xl sm:text-3xl mb-3 sm:mb-4">📊</div>
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-2">Track Progress</h3>
                            <p class="text-sm sm:text-base text-gray-600">Monitor your learning journey with detailed analytics and insights.</p>
                        </div>
                        <div class="bg-white rounded-lg p-4 sm:p-6 shadow-lg sm:col-span-2 lg:col-span-1">
                            <div class="text-2xl sm:text-3xl mb-3 sm:mb-4">⚡</div>
                            <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-2">Study Anywhere</h3>
                            <p class="text-sm sm:text-base text-gray-600">Access your flashcards on any device, anytime, anywhere.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-layouts.app>
@endauth
