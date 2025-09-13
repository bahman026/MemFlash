@props(['staticDeck' => null])

<x-layouts.app 
    :show-level="true" 
    :show-user="true"
    :header-title="$staticDeck ? $staticDeck->name : 'Static Deck'"
    :header-subtitle="$staticDeck ? $staticDeck->cards_count . ' cards' : null"
    :header-variant="'default'"
>
    <x-slot name="seo">
        {{ $seo ?? '' }}
    </x-slot>

    <x-slot name="headerActions">
        @if($staticDeck)
            <div class="flex items-center space-x-2">
                <!-- Static Deck Actions -->
                <a href="{{ route('static-decks.preview', $staticDeck) }}" class="text-gray-500 hover:text-gray-700 p-2 rounded-md hover:bg-gray-100 transition-colors" title="Preview Cards">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </a>
                
                <form method="POST" action="{{ route('static-decks.reset', $staticDeck) }}" onsubmit="return confirm('Are you sure you want to reset the learning progress for this lesson? All cards will start from the beginning. This action cannot be undone.')" class="inline">
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
