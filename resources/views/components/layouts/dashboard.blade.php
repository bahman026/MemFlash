<x-layouts.app>
    <x-slot name="seo">
        {{ $seo ?? '' }}
    </x-slot>

    {{ $slot ?? '' }}
    @yield('content')
</x-layouts.app>
