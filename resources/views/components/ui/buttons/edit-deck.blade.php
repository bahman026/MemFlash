@props(['deck', 'class' => 'text-gray-500 hover:text-gray-700 p-2 rounded-md hover:bg-gray-100 transition-colors'])

<a href="{{ route('decks.edit', $deck) }}" class="{{ $class }}" title="Edit Deck">
    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
    </svg>
</a>
