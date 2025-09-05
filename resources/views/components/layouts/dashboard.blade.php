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

<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-14 sm:h-16">
                <div class="flex items-center min-w-0 flex-1">
                    <div class="flex-shrink-0">
                        <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900">📚 Memora</h1>
                    </div>
                </div>
                
                <div class="flex items-center space-x-1 sm:space-x-4">
                    <!-- User Avatar and Name - Mobile -->
                    <div class="flex items-center space-x-2 sm:hidden">
                        <div class="flex-shrink-0">
                            @if(auth()->user()->avatar)
                                <img class="h-8 w-8 rounded-full ring-2 ring-gray-200" src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}">
                            @else
                                <div class="h-8 w-8 rounded-full bg-primary-500 flex items-center justify-center ring-2 ring-gray-200">
                                    <span class="text-white text-xs font-medium">
                                        {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- User Avatar and Name - Desktop -->
                    <div class="hidden sm:flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            @if(auth()->user()->avatar)
                                <img class="h-9 w-9 lg:h-10 lg:w-10 rounded-full ring-2 ring-gray-200" src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}">
                            @else
                                <div class="h-9 w-9 lg:h-10 lg:w-10 rounded-full bg-primary-500 flex items-center justify-center ring-2 ring-gray-200">
                                    <span class="text-white text-sm font-medium">
                                        {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        <div class="hidden lg:block min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate max-w-32">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate max-w-32">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                    
                    <!-- Mobile Menu Button -->
                    <div class="sm:hidden">
                        <button type="button" class="text-gray-500 hover:text-gray-700 p-2 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500" onclick="toggleMobileMenu()">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Desktop Logout Button -->
                    <form method="POST" action="{{ route('logout') }}" class="hidden sm:inline">
                        @csrf
                        <button type="submit" class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Mobile Menu -->
            <div id="mobile-menu" class="hidden sm:hidden border-t border-gray-200 py-3">
                <div class="space-y-3">
                    <div class="px-2 py-2 bg-gray-50 rounded-lg">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left text-gray-500 hover:text-gray-700 hover:bg-gray-50 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-3 sm:py-4 lg:py-6 px-3 sm:px-4 lg:px-6 xl:px-8">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            const isHidden = menu.classList.contains('hidden');
            
            if (isHidden) {
                menu.classList.remove('hidden');
                // Add smooth animation
                menu.style.opacity = '0';
                menu.style.transform = 'translateY(-10px)';
                requestAnimationFrame(() => {
                    menu.style.transition = 'opacity 0.2s ease-out, transform 0.2s ease-out';
                    menu.style.opacity = '1';
                    menu.style.transform = 'translateY(0)';
                });
            } else {
                menu.style.transition = 'opacity 0.2s ease-out, transform 0.2s ease-out';
                menu.style.opacity = '0';
                menu.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    menu.classList.add('hidden');
                    menu.style.transition = '';
                    menu.style.opacity = '';
                    menu.style.transform = '';
                }, 200);
            }
        }
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('mobile-menu');
            const button = event.target.closest('button[onclick="toggleMobileMenu()"]');
            
            if (!menu.contains(event.target) && !button && !menu.classList.contains('hidden')) {
                menu.style.transition = 'opacity 0.2s ease-out, transform 0.2s ease-out';
                menu.style.opacity = '0';
                menu.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    menu.classList.add('hidden');
                    menu.style.transition = '';
                    menu.style.opacity = '';
                    menu.style.transform = '';
                }, 200);
            }
        });

        // Add touch support for mobile interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Add touch feedback for buttons
            const buttons = document.querySelectorAll('button');
            buttons.forEach(button => {
                button.addEventListener('touchstart', function() {
                    this.style.transform = 'scale(0.98)';
                });
                
                button.addEventListener('touchend', function() {
                    this.style.transform = 'scale(1)';
                });
            });

            // Add swipe gesture support for mobile menu
            let startY = 0;
            let currentY = 0;
            let isDragging = false;

            document.addEventListener('touchstart', function(e) {
                startY = e.touches[0].clientY;
                isDragging = true;
            });

            document.addEventListener('touchmove', function(e) {
                if (!isDragging) return;
                currentY = e.touches[0].clientY;
                const diff = startY - currentY;
                
                // If swiping up and menu is open, close it
                if (diff < -50) {
                    const menu = document.getElementById('mobile-menu');
                    if (!menu.classList.contains('hidden')) {
                        toggleMobileMenu();
                    }
                }
            });

            document.addEventListener('touchend', function() {
                isDragging = false;
            });
        });
    </script>
</body>
</html>
