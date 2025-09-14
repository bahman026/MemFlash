@props([
    'showLevel' => true,
    'showUser' => true,
    'title' => 'MemFlash',
    'subtitle' => null,
    'actions' => null,
    'variant' => 'default' // 'default', 'minimal', 'full'
])

<nav class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-14 sm:h-16">
            <!-- Logo and Title Section -->
            <div class="flex items-center min-w-0 flex-1">
                <div class="flex-shrink-0">
                    @if($variant === 'minimal')
                        <h1 class="text-lg sm:text-xl font-bold text-gray-900">{{ $title }}</h1>
                    @else
                        <a href="{{ route('welcome') }}" class="flex items-center space-x-2 group">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center shadow-sm group-hover:shadow-md transition-shadow duration-200">
                                <img src="{{ asset('flash-cards.svg') }}" alt="MemFlash Logo" class="w-full h-full object-contain" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                <div class="w-full h-full bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl flex items-center justify-center" style="display: none;">
                                    <span class="text-white text-lg sm:text-xl">📚</span>
                                </div>
                            </div>
                            <div class="hidden sm:block">
                                <h1 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-900 group-hover:text-blue-600 transition-colors">MemFlash</h1>
                                @if($subtitle)
                                    <p class="text-xs text-gray-500">{{ $subtitle }}</p>
                                @endif
                            </div>
                        </a>
                    @endif
                </div>
            </div>

            @if($showUser && auth()->check())
                <!-- User Section -->
                <div class="flex items-center space-x-1 sm:space-x-4">
                    <!-- Custom Actions -->
                    @if($actions)
                        <div class="flex items-center space-x-2">
                            {{ $actions }}
                        </div>
                    @endif

                    <!-- Profile Dropdown -->
                    <x-ui.navigation.profile-dropdown :user="auth()->user()" />
                </div>
            @elseif(!auth()->check())
                <!-- Guest User Actions -->
                <div class="flex items-center space-x-2">
                    <a href="{{ route('login.page') }}" class="text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                        Login
                    </a>
                    @if($actions)
                        {{ $actions }}
                    @endif
                </div>
            @endif
        </div>

    </div>
</nav>

