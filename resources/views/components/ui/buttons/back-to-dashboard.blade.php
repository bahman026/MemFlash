@props(['class' => 'text-gray-500 hover:text-gray-700 p-2 rounded-md hover:bg-gray-100 transition-colors'])

<a href="{{ route('dashboard') }}" class="{{ $class }}" title="Back to Dashboard">
    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
    </svg>
</a>
