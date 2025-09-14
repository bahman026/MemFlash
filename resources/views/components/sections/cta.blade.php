@props([
    'title' => 'Ready to Learn Faster with MemFlash?',
    'subtitle' => 'Join thousands of learners who are already using MemFlash to build vocabulary, master language learning, and retain information better with our spaced repetition system.',
    'buttonText' => null,
    'buttonUrl' => null
])

<!-- CTA Section -->
<section class="py-16 sm:py-20">
    <div class="max-w-4xl mx-auto text-center">
        <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
            {{ $title }}
        </h2>
        <p class="text-lg text-gray-600 mb-8">
            {{ $subtitle }}
        </p>
        
        @if($buttonText && $buttonUrl)
            <a href="{{ $buttonUrl }}" class="inline-flex items-center bg-primary-500 hover:bg-primary-600 text-white px-8 py-4 rounded-lg text-lg font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                {{ $buttonText }}
                <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                </svg>
            </a>
        @else
            @auth
                <a href="{{ route('dashboard') }}" class="inline-flex items-center bg-primary-500 hover:bg-primary-600 text-white px-8 py-4 rounded-lg text-lg font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                    Go to Dashboard
                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </a>
            @endauth
        @endif
    </div>
</section>
