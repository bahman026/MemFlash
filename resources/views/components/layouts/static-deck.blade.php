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
