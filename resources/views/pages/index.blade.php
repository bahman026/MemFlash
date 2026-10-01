<x-layouts.app
    :show-level="auth()->check()"
    :show-user="auth()->check()"
    :header-variant="'default'"
    :header-title="'MemFlash'"
    :header-subtitle="null"
    :hide-footer="true"
>
    <x-slot name="seo">
        {{-- SEO Meta Tags --}}
        <title>MemFlash – Vocabulary Flashcards</title>
        <meta name="description" content="MemFlash is the ultimate flashcard learning platform for vocabulary building, language learning, and memory enhancement. Use spaced repetition to learn faster with our study app and quiz cards.">
        <meta name="keywords" content="MemFlash, flashcards, spaced repetition, vocabulary, memory app, learn faster, study app, vocabulary builder, language learning, review app, quiz cards, memory enhancement, learning platform">
        <meta name="author" content="MemFlash">

        {{-- Open Graph Meta Tags --}}
        <meta property="og:title" content="MemFlash – Vocabulary Flashcards">
        <meta property="og:description" content="MemFlash is the ultimate flashcard learning platform for vocabulary building, language learning, and memory enhancement. Use spaced repetition to learn faster with our study app and quiz cards.">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:image" content="{{ asset('icons/icon-512.png') }}">
        <meta property="og:image:width" content="512">
        <meta property="og:image:height" content="512">
        <meta property="og:site_name" content="MemFlash">

        {{-- Twitter Card Meta Tags --}}
        <meta name="twitter:card" content="summary">
        <meta name="twitter:title" content="MemFlash – Vocabulary Flashcards">
        <meta name="twitter:description" content="MemFlash is the ultimate flashcard learning platform for vocabulary building, language learning, and memory enhancement. Use spaced repetition to learn faster with our study app and quiz cards.">
        <meta name="twitter:image" content="{{ asset('icons/icon-512.png') }}">

        {{-- Additional SEO Meta Tags --}}
        <meta name="robots" content="index, follow">
        <meta name="googlebot" content="index, follow">
        <link rel="alternate" hreflang="en" href="{{ url()->current() }}">
    </x-slot>

    <x-slot name="headerActions">
        @auth
            <!-- Dashboard Button -->
            <a href="{{ route('dashboard') }}" class="bg-primary-500 hover:bg-primary-600 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                Dashboard
            </a>
        @endauth
    </x-slot>

    <!-- Hero Section -->
    <x-sections.hero
        :title="'Master Vocabulary & Learn Faster with Smart Flashcards'"
    />

    <!-- Features Section -->
    <x-sections.features />

    <!-- CTA Section -->
    <x-sections.cta />

    <!-- Footer -->
    <x-layouts.sections.simple-footer />

    @push('scripts')
        <script>
            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        </script>
    @endpush

    <x-pwa.install-prompt />
</x-layouts.app>
