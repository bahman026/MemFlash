@props(['deck' => null])

<x-layouts.app 
    :show-level="true" 
    :show-user="true"
    :header-title="$deck?->name ?? 'Deck'"
    :header-subtitle="$deck ? $deck->cards_count . ' cards' : null"
    :header-variant="'default'"
>
    <x-slot name="seo">
        {{ $seo ?? '' }}
    </x-slot>

    <x-slot name="headerActions">
        @if($deck)
            <x-layouts.partials.header-actions>
                <x-ui.buttons.edit-deck :deck="$deck" />
                <x-ui.buttons.back-to-dashboard />
            </x-layouts.partials.header-actions>
        @endif
    </x-slot>

    {{ $slot ?? '' }}
    @yield('content')
</x-layouts.app>
