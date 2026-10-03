<x-layouts.dashboard>
    <x-slot name="seo">
        <title>{{ $query !== '' ? $query . ' · ' : '' }}Look up · MemFlash</title>
        <meta name="description" content="Look a word up and save it to a word list">
    </x-slot>

    {{-- Opened by URL, often from another app: /lookup?q=%%SS, optionally
         with Google Translate's &sl=en&tl=fa (or fa/en, en/en) to fix the direction. --}}
    <div class="max-w-2xl mx-auto space-y-3">
        <x-ui.word-lookup
            :lists="$lists"
            :selected-list-id="$selectedListId"
            :query="$query"
            :direction="$direction"
            sync-url
            autofocus
        />

        <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-800">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Dashboard
        </a>
    </div>
</x-layouts.dashboard>
