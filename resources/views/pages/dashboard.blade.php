<x-layouts.dashboard>
    <x-slot name="seo">
        <title>Dashboard - MemFlash</title>
        <meta name="description" content="Your personal flashcard dashboard">
    </x-slot>

    <div class="space-y-4 sm:space-y-6 lg:space-y-8">
        <!-- Success/Error Messages -->
        <x-ui.feedback.session-messages />

        <!-- Welcome Section -->
        <x-layouts.sections.welcome-section :user="auth()->user()" />

        <!-- Stats Cards -->
        <x-ui.feedback.dashboard-stats 
            :decks="$decks" 
            :total-cards="$totalCards" 
            :total-static-cards="$totalStaticCards" 
        />

        <!-- Deck Limits Information -->
        <x-ui.feedback.static-deck-limits :user="auth()->user()" />

        <!-- Static Decks Section -->
        <x-layouts.sections.static-decks-section 
            :static-decks="$staticDecks" 
            :user="auth()->user()" 
        />

        <!-- Personal Decks Section -->
        <x-layouts.sections.personal-decks-section 
            :decks="$decks" 
            :user="auth()->user()" 
        />
    </div>

    <!-- Deck Creation Modal -->
    <x-ui.modals.deck-create-modal />
</x-layouts.dashboard>