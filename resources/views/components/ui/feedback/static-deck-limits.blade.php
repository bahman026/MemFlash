@props([
    'user' => null,
    'class' => 'bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4 sm:p-6 mb-6 sm:mb-8'
])

<div class="{{ $class }}">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
        <div class="ml-4 flex-1">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Deck Limits</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">Personal Decks:</span>
                    <span class="font-medium text-gray-900">
                        {{ $user->getDeckCount() }}/200
                        @if($user->hasReachedDeckLimit())
                            <span class="text-red-600 ml-1">(Limit reached)</span>
                        @else
                            <span class="text-green-600 ml-1">({{ $user->getRemainingDeckSlots() }} remaining)</span>
                        @endif
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-gray-600">Cards per Deck:</span>
                    <span class="font-medium text-gray-900">Up to 2,000 cards per deck</span>
                </div>
            </div>
            @if($user->hasReachedDeckLimit())
                <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                    <p class="text-sm text-red-800">
                        <strong>Deck limit reached!</strong> You have created the maximum of 200 personal decks. 
                        Please delete some decks before creating new ones.
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
