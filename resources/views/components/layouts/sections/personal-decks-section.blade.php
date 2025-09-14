@props([
    'decks' => null,
    'user' => null,
    'class' => 'bg-white shadow-sm rounded-xl border border-gray-100'
])

<div class="{{ $class }}">
    <div class="px-3 py-4 sm:px-4 sm:py-5 lg:px-6 lg:py-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6 space-y-3 sm:space-y-0">
            <h2 class="text-lg sm:text-xl font-bold text-gray-900">Your Personal Decks</h2>
            @if($user->hasReachedDeckLimit())
                <button disabled class="bg-gray-400 text-white px-4 py-2.5 rounded-lg text-sm font-semibold w-full sm:w-auto cursor-not-allowed opacity-75">
                    + Create New Deck (Limit Reached)
                </button>
            @else
                <button onclick="openDeckModal()" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 w-full sm:w-auto shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                    + Create New Deck
                </button>
            @endif
        </div>

        @if($decks->isEmpty())
            <div class="text-center py-8 sm:py-12">
                <div class="mx-auto h-20 w-20 sm:h-24 sm:w-24 text-gray-400 mb-4">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-2">No decks yet</h3>
                <p class="text-gray-500 mb-6 text-sm sm:text-base">Get started by creating your first flashcard deck!</p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    @if($user->hasReachedDeckLimit())
                        <button disabled class="bg-gray-400 text-white px-6 py-3 rounded-lg text-sm font-semibold cursor-not-allowed opacity-75">
                            Create Your First Deck (Limit Reached)
                        </button>
                    @else
                        <button onclick="openDeckModal()" class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-lg text-sm font-semibold transition-all duration-200 shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                            Create Your First Deck
                        </button>
                    @endif
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                @foreach($decks as $deck)
                    <x-ui.cards.personal-deck-card :deck="$deck" />
                @endforeach
            </div>
        @endif
    </div>
</div>
