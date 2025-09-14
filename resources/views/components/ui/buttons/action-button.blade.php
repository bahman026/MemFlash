@props([
    'href' => '#',
    'title' => '',
    'class' => 'text-gray-500 hover:text-gray-700 p-2 rounded-md hover:bg-gray-100 transition-colors',
    'icon' => null
])

<a href="{{ $href }}" class="{{ $class }}" title="{{ $title }}">
    @if($icon)
        {!! $icon !!}
    @else
        {{ $slot }}
    @endif
</a>
