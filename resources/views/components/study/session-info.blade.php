@props([
    'deckName' => '',
    'cardsLoaded' => 0,
    'class' => 'bg-white/80 backdrop-blur-sm rounded-xl sm:rounded-2xl p-4 sm:p-6 shadow-lg border border-white/20'
])

<div class="{{ $class }}">
    <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4 flex items-center">
        <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        Session Info
    </h3>
    <div class="space-y-3 sm:space-y-4">
        <div>
            <div class="text-xs sm:text-sm text-gray-500">Deck</div>
            <div class="font-semibold text-gray-900 text-sm sm:text-base truncate">{{ $deckName }}</div>
        </div>
        <div>
            <div class="text-xs sm:text-sm text-gray-500">Cards Loaded</div>
            <div class="font-semibold text-gray-900 text-sm sm:text-base" id="cards-loaded">{{ $cardsLoaded }}</div>
        </div>
    </div>
</div>
