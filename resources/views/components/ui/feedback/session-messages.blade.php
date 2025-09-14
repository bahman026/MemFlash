@props([
    'class' => 'space-y-4'
])

<div class="{{ $class }}">
    @if(session('success'))
        <x-ui.feedback.alert type="success" dismissible>
            {{ session('success') }}
        </x-ui.feedback.alert>
    @endif

    @if(session('error'))
        <x-ui.feedback.alert type="error" dismissible>
            {{ session('error') }}
        </x-ui.feedback.alert>
    @endif
</div>
