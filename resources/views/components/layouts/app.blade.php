<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" href="{{ asset('assets/images/favicon-300x300.png') }}" sizes="32x32">
    <link rel="icon" href="{{ asset('assets/images/favicon-150x150.png') }}" sizes="192x192">

    {{-- seo related tags --}}
    {{ $seo ?? '' }}
    @yield('seo')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
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
    <x-header 
        :show-level="$showLevel ?? true" 
        :show-user="$showUser ?? true"
        :title="$headerTitle ?? 'MemFlash'"
        :subtitle="$headerSubtitle ?? null"
        :variant="$headerVariant ?? 'default'"
    >
        <x-slot name="actions">
            {{ $headerActions ?? '' }}
        </x-slot>
    </x-header>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl mx-auto py-3 sm:py-4 lg:py-6 px-3 sm:px-4 lg:px-6 xl:px-8">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <!-- Footer -->
    @if(!isset($hideFooter) || !$hideFooter)
        <x-simple-footer />
    @endif

    <!-- Additional Scripts -->
    @stack('scripts')
</body>
</html>