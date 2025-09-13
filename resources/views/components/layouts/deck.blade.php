@props(['deck' => null])

<x-layouts.app 
    :show-level="true" 
    :show-user="true"
    :header-title="$deck ? $deck->name : 'Deck'"
    :header-subtitle="$deck ? $deck->cards_count . ' cards' : null"
    :header-variant="'default'"
>
    <x-slot name="seo">
        {{ $seo ?? '' }}
    </x-slot>

    <x-slot name="headerActions">
        @if($deck)
            <div class="flex items-center space-x-2">
                <!-- Deck Actions -->
                <a href="{{ route('decks.edit', $deck) }}" class="text-gray-500 hover:text-gray-700 p-2 rounded-md hover:bg-gray-100 transition-colors" title="Edit Deck">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </a>
                
                <form method="POST" action="{{ route('decks.reset', $deck) }}" onsubmit="return confirm('Are you sure you want to reset the learning progress for this deck? All cards will start from the beginning. This action cannot be undone.')" class="inline">
                    @csrf
                    <button type="submit" class="text-orange-500 hover:text-orange-700 p-2 rounded-md hover:bg-orange-50 transition-colors" title="Reset Progress">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                    </button>
                </form>

                <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700 p-2 rounded-md hover:bg-gray-100 transition-colors" title="Back to Dashboard">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
            </div>
        @endif
    </x-slot>

    {{ $slot ?? '' }}
    @yield('content')
</x-layouts.app>
