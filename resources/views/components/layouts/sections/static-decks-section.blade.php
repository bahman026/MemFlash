@props([
    'staticDecks' => null,
    'user' => null,
    'class' => 'bg-white shadow-sm rounded-xl border border-gray-100 mb-6 sm:mb-8'
])

@if($staticDecks->isNotEmpty())
    <div class="{{ $class }}">
        <div class="px-3 py-4 sm:px-4 sm:py-5 lg:px-6 lg:py-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6 space-y-3 sm:space-y-0">
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900">American English File {{ $user->level->getDisplayName() }}</h2>
                    <p class="text-sm text-gray-600 mt-1">Curriculum decks for your level</p>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ $staticDecks->count() }} lessons
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                @foreach($staticDecks as $staticDeck)
                    <x-ui.cards.static-deck-card :static-deck="$staticDeck" />
                @endforeach
            </div>
        </div>
    </div>
@endif
