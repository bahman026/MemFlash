<x-layouts.app 
    :show-level="false" 
    :show-user="false"
    :header-variant="'minimal'"
    :header-title="$headerTitle ?? 'MemFlash'"
    :header-subtitle="$headerSubtitle ?? null"
>
    <x-slot name="seo">
        {{ $seo ?? '' }}
    </x-slot>

    {{ $slot ?? '' }}
    @yield('content')
</x-layouts.app>
