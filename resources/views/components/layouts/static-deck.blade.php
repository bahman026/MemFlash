@props(['staticDeck' => null])

<x-layouts.app 
    :show-level="true" 
    :show-user="true"
    :header-title="$staticDeck?->name ?? 'Static Deck'"
    :header-subtitle="$staticDeck ? $staticDeck->cards_count . ' cards' : null"
    :header-variant="'default'"
>
    <x-slot name="seo">
        {{ $seo ?? '' }}
    </x-slot>

    <x-slot name="headerActions">
        @if($staticDeck)
            <x-layouts.partials.header-actions>
                <x-ui.buttons.back-to-dashboard />
            </x-layouts.partials.header-actions>
        @endif
    </x-slot>

    {{ $slot ?? '' }}
    @yield('content')
</x-layouts.app>
