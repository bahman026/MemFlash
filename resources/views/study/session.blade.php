<x-layouts.deck :deck="$deck">
    <x-slot name="seo">
        <title>Study Session - {{ $deck->name }} - MemFlash</title>
        <meta name="description" content="Study flashcards from {{ $deck->name }} deck using spaced repetition">
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
        <div class="max-w-6xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 py-4 sm:py-6 lg:py-8">
            <!-- Header -->
            <x-study.study-header 
                :title="$deck->name"
                :subtitle="'Study Session'"
                :remaining-cards="0"
                :completed-cards="0"
            />

            <!-- Progress Section -->
            <x-study.progress-bar 
                :total-cards="0"
                :completed-cards="0"
                :remaining-cards="0"
                class="mb-4 sm:mb-6 lg:mb-8"
            />

            <!-- Main Study Area -->
            <div class="grid grid-cols-1 xl:grid-cols-4 gap-3 sm:gap-4 lg:gap-6 xl:gap-8">
                <!-- Study Card -->
                <div class="xl:col-span-3 order-1">
                    <x-study.study-card />
                </div>

                <!-- Session Info Sidebar -->
                <div class="xl:col-span-1 order-2 xl:order-2">
                    <div class="space-y-4 sm:space-y-6">
                        <x-study.session-info 
                            :deck-name="$deck->name"
                            :cards-loaded="0"
                        />
                    </div>
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
            deckId: {{ $deck->id }}
        };
    </script>
    <script src="{{ asset('js/study-utils.js') }}"></script>
    <script src="{{ asset('js/study-session-unified.js') }}"></script>
    @endpush
</x-layouts.deck>