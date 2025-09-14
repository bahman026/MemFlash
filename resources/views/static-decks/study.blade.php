<x-layouts.static-deck :staticDeck="$staticDeck">
    <x-slot name="seo">
        <title>Study Session - {{ $staticDeck->name }} - MemFlash</title>
        <meta name="description" content="Study flashcards from {{ $staticDeck->name }} lesson using spaced repetition">
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
        <div class="max-w-6xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 py-4 sm:py-6 lg:py-8">
            <!-- Header -->
            <x-study.study-header 
                :title="$staticDeck->name"
                :subtitle="'Study Session'"
                :remaining-cards="$dueCards->count()"
                :completed-cards="0"
            />

            <!-- Progress Section -->
            <x-study.progress-bar 
                :total-cards="$cardsPerDay ?? $dueCards->count()"
                :completed-cards="0"
                :remaining-cards="$dueCards->count()"
                class="mb-4 sm:mb-6 lg:mb-8"
            />

            <!-- Main Study Area -->
            <div class="max-w-4xl mx-auto">
                <!-- Study Card -->
                <div>
                    <x-study.study-card />
                </div>
            </div>

            <!-- Study Tips - Bottom of Page -->
            <div class="mt-6 sm:mt-8">
                <x-study.study-tips />
            </div>
        </div>
    </div>

    <!-- Fullscreen Modal -->
    <x-study.fullscreen-modal />

    @push('scripts')
    <script>
        // Pass configuration data to JavaScript
        window.studyConfig = {
            cards: @json($dueCards->toArray() ?? []),
            totalCards: {{ $cardsPerDay ?? $dueCards->count() ?? 0 }}
        };
    </script>
    <script src="{{ asset('js/study-utils.js') }}"></script>
    <script src="{{ asset('js/study-session-unified.js') }}"></script>
    @endpush
</x-layouts.static-deck>