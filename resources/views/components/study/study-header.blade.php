@props([
    'title' => 'Study Session',
    'subtitle' => 'Study Session',
    'remainingCards' => 0,
    'completedCards' => 0,
    'class' => 'mb-4 sm:mb-6 lg:mb-8'
])

<div class="{{ $class }}">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 sm:gap-6">
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 sm:gap-3 mb-2">
                <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg sm:rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 truncate">{{ $title }}</h1>
                    <p class="text-sm sm:text-base text-gray-600">{{ $subtitle }}</p>
                </div>
            </div>
        </div>

        <!-- Mobile: Exit button at top right -->
        <div class="flex justify-between items-center mb-4 sm:hidden">
            <div></div>
            <a href="{{ route('dashboard') }}" class="bg-white/90 hover:bg-white text-gray-700 hover:text-gray-900 px-3 py-2 rounded-lg font-semibold transition-all duration-200 shadow-lg hover:shadow-xl border border-white/20 backdrop-blur-sm text-sm flex items-center justify-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Exit
            </a>
        </div>

        <!-- Desktop: Original layout -->
        <div class="hidden sm:flex sm:flex-row items-center justify-between gap-4">
            <!-- Stats Cards -->
            <x-study.stats-cards 
                :remaining-cards="$remainingCards" 
                :completed-cards="$completedCards" 
                :show-mobile="false"
            />

            <a href="{{ route('dashboard') }}" class="bg-white/90 hover:bg-white text-gray-700 hover:text-gray-900 px-4 py-2 lg:px-6 lg:py-3 rounded-xl lg:rounded-2xl font-semibold transition-all duration-200 shadow-lg hover:shadow-xl border border-white/20 backdrop-blur-sm text-sm flex items-center justify-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Exit
            </a>
        </div>

        <!-- Mobile: Stats cards below -->
        <x-study.stats-cards 
            :remaining-cards="$remainingCards" 
            :completed-cards="$completedCards" 
            :show-mobile="true"
            class="sm:hidden"
        />
    </div>
</div>
