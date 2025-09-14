@props([
    'actions' => null,
    'spacing' => 'space-x-2'
])

@if($actions)
    <div class="flex items-center {{ $spacing }}">
        {{ $actions }}
    </div>
@endif
