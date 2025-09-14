@props([
    'staticDeck' => null,
    'class' => 'bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl border border-blue-200 hover:border-blue-300 hover:shadow-lg transition-all duration-300 group overflow-hidden'
])

@php
    $userProgress = $staticDeck->userProgress->first();
    $cardsStudied = $userProgress ? $userProgress->cards_studied : 0;
    $totalCards = $staticDeck->cards_count;
    $progressPercentage = $totalCards > 0 ? ($cardsStudied / $totalCards) * 100 : 0;
    $isCompleted = $userProgress ? $userProgress->isCompleted() : false;
@endphp

<div class="{{ $class }}">
    <!-- Header with deck name and lesson number -->
    <div class="p-6 pb-4">
        <div class="flex items-start justify-between mb-4">
            <div class="flex-1 min-w-0">
                <div class="flex items-center space-x-2 mb-2">
                    <span class="inline-flex items-center justify-center w-8 h-8 bg-blue-600 text-white text-sm font-bold rounded-full">
                        {{ $staticDeck->lesson_number }}
                    </span>
                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition-colors truncate leading-tight">
                        Lesson {{ $staticDeck->lesson_number }}
                    </h3>
                </div>
                
                <!-- Progress Section -->
                <div class="space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">Progress</span>
                        <span class="font-medium text-gray-900">{{ round($progressPercentage) }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-gradient-to-r from-blue-500 to-green-500 h-2 rounded-full transition-all duration-500" style="width: {{ $progressPercentage }}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-500">
                        <span>{{ $cardsStudied }}/{{ $totalCards }} studied</span>
                        @if($isCompleted)
                            <span class="flex items-center text-green-600 font-medium">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Completed!
                            </span>
                        @else
                            <span class="flex items-center text-blue-600 font-medium">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ $staticDeck->userSettings->first()->cards_per_day ?? 10 }}/day
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action buttons -->
    <div class="px-6 pb-6">
        <div class="flex flex-col space-y-3">
            <!-- Primary Study Button -->
            <a href="{{ route('static-decks.study', $staticDeck) }}" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-xl text-sm font-bold transition-all duration-200 text-center flex items-center justify-center group shadow-lg hover:shadow-xl">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                Start Learning
            </a>

            <!-- Cards per day setting -->
            <div class="mb-3">
                <form method="POST" action="{{ route('static-decks.cards-per-day', $staticDeck) }}" class="flex items-center space-x-2">
                    @csrf
                    <label class="text-xs text-gray-600 font-medium">Cards per day:</label>
                    <input type="number" name="cards_per_day" value="{{ $staticDeck->userSettings->first()->cards_per_day ?? 10 }}" 
                           min="1" max="100" class="w-16 px-2 py-1 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <button type="submit" class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                        Update
                    </button>
                </form>
            </div>

            <!-- Secondary Actions -->
            <div class="grid grid-cols-2 gap-2">
                <a href="{{ route('static-decks.preview', $staticDeck) }}" class="px-3 py-2 border border-blue-300 rounded-lg text-xs font-medium text-blue-700 hover:bg-blue-50 hover:border-blue-400 transition-all duration-200 text-center flex items-center justify-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    Preview
                </a>
                
                <form method="POST" action="{{ route('static-decks.reset', $staticDeck) }}" onsubmit="return confirm('Are you sure you want to reset the learning progress for this lesson? All cards will start from the beginning. This action cannot be undone.')">
                    @csrf
                    <button type="submit" class="w-full px-3 py-2 border border-orange-300 rounded-lg text-xs font-medium text-orange-700 hover:bg-orange-50 hover:border-orange-400 transition-all duration-200 flex items-center justify-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Reset
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
