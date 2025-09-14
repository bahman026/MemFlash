@props([
    'title' => 'Master Vocabulary & Learn Faster with Smart Flashcards',
    'subtitle' => 'MemFlash is the ultimate vocabulary builder and memory app that uses scientifically-proven spaced repetition to help you learn faster. Perfect for language learning, study sessions, and quiz card reviews.',
    'showDemoCard' => true,
    'showCtaButtons' => true
])

<div class="max-w-7xl mx-auto py-12 sm:py-16 lg:py-20 px-3 sm:px-4 lg:px-6 xl:px-8">
    <div class="text-center">
        <!-- Hero Content -->
        <div class="max-w-4xl mx-auto">
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 mb-6">
                {!! str_replace('Smart Flashcards', '<span class="text-primary-500">Smart Flashcards</span>', $title) !!}
            </h1>
            
            <p class="text-lg sm:text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                {{ $subtitle }}
            </p>

            @if($showCtaButtons)
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
            @endif

            @if($showDemoCard)
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
                                <x-sections.demo-card />
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
