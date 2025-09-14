<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" href="{{ asset('flash-cards.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('flash-cards.svg') }}">

    {{-- seo related tags --}}
    {{ $seo ?? '' }}
    @yield('seo')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="{{ asset('js/alpine.min.js') }}"></script>
    <style>
        /* Mobile-specific improvements */
        @media (max-width: 640px) {
            /* Improve touch targets */
            button, a {
                min-height: 44px;
                min-width: 44px;
            }

            /* Better scrolling on mobile */
            .overflow-auto {
                -webkit-overflow-scrolling: touch;
            }

            /* Prevent zoom on input focus */
            input, textarea, select {
                font-size: 16px;
            }
        }

        /* Smooth transitions for all interactive elements */
        button, a, .hover\:shadow-md:hover {
            transition: all 0.2s ease-in-out;
        }

        /* Better focus states for accessibility */
        button:focus, a:focus {
            outline: 2px solid #0ea5e9;
            outline-offset: 2px;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Header -->
    <x-layouts.sections.header 
        :show-level="$showLevel ?? true" 
        :show-user="$showUser ?? true"
        :title="$headerTitle ?? 'MemFlash'"
        :subtitle="$headerSubtitle ?? null"
        :variant="$headerVariant ?? 'default'"
    >
        <x-slot name="actions">
            {{ $headerActions ?? '' }}
        </x-slot>
    </x-layouts.sections.header>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl mx-auto py-3 sm:py-4 lg:py-6 px-3 sm:px-4 lg:px-6 xl:px-8">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Footer -->
    @if(!isset($hideFooter) || !$hideFooter)
        <x-layouts.sections.simple-footer />
    @endif

    <!-- Additional Scripts -->
    @stack('scripts')
</body>
</html>