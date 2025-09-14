@props([
    'title' => 'Why Choose MemFlash for Your Learning?',
    'subtitle' => 'Our vocabulary builder and study app combines cutting-edge spaced repetition science with an intuitive interface to maximize your learning efficiency and memory retention.',
    'features' => [
        [
            'icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
            'title' => 'Smart Spaced Repetition',
            'description' => 'Advanced spaced repetition algorithm that adapts to your learning pace and optimizes review timing for maximum memory retention.'
        ],
        [
            'icon' => 'M13 10V3L4 14h7v7l9-11h-7z',
            'title' => 'Learn Faster',
            'description' => 'Study more efficiently with our optimized learning system that reduces study time while improving vocabulary retention and memory.'
        ],
        [
            'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
            'title' => 'Progress Tracking',
            'description' => 'Monitor your learning progress with detailed analytics and insights to stay motivated and on track.'
        ],
        [
            'icon' => 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4',
            'title' => 'Vocabulary Builder',
            'description' => 'Create custom flashcard decks, import vocabulary lists, and personalize your language learning experience with our quiz card system.'
        ],
        [
            'icon' => 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z',
            'title' => 'Study App',
            'description' => 'Study anywhere, anytime with our mobile-ready study app that works perfectly on all devices for vocabulary practice and quiz reviews.'
        ],
        [
            'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
            'title' => 'Review App',
            'description' => 'Share flashcard decks with friends, join study groups, and review vocabulary together with our collaborative learning features.'
        ]
    ]
])

<!-- Features Section -->
<section id="features" class="py-16 sm:py-20">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-16">
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                {{ $title }}
            </h2>
            <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                {{ $subtitle }}
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($features as $feature)
                <div class="bg-white rounded-xl p-6 shadow-lg border border-gray-200">
                    <div class="w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feature['icon'] }}"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ $feature['title'] }}</h3>
                    <p class="text-gray-600">{{ $feature['description'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
