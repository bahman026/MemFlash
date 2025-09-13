@props(['user' => null])

<div class="relative" x-data="{ open: false }">
    <!-- Profile Button -->
    <button @click="open = !open" 
            @click.away="open = false"
            class="flex items-center space-x-2 text-gray-700 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded-lg p-1.5 sm:p-2 hover:bg-gray-100 transition-colors duration-200">
        <!-- User Avatar -->
        <div class="flex-shrink-0">
            @if($user && $user->avatar)
                <img class="h-8 w-8 sm:h-9 sm:w-9 rounded-full ring-2 ring-gray-200" 
                     src="{{ $user->avatar }}" 
                     alt="{{ $user->name }}">
            @else
                <div class="h-8 w-8 sm:h-9 sm:w-9 rounded-full bg-primary-500 flex items-center justify-center ring-2 ring-gray-200">
                    <span class="text-white text-sm font-medium">
                        {{ $user ? substr($user->name ?? 'U', 0, 1) : 'U' }}
                    </span>
                </div>
            @endif
        </div>
        
        <!-- User Name (Desktop only) -->
        <div class="hidden lg:block min-w-0">
            <p class="text-sm font-medium text-gray-900 truncate max-w-32">{{ $user->name ?? 'User' }}</p>
            <p class="text-xs text-gray-500 truncate max-w-32">{{ $user->email ?? '' }}</p>
        </div>
        
        <!-- Dropdown Arrow (Hidden on mobile for cleaner look) -->
        <svg class="hidden sm:block h-4 w-4 text-gray-400 transition-transform duration-200" 
             :class="{ 'rotate-180': open }" 
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <!-- Dropdown Menu -->
    <div x-show="open" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 sm:right-0 mt-2 w-72 sm:w-80 bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-50 max-w-[calc(100vw-2rem)]"
         style="display: none;">
        
        <!-- User Info Header -->
        <div class="px-4 py-3 border-b border-gray-100">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    @if($user && $user->avatar)
                        <img class="h-10 w-10 rounded-full ring-2 ring-gray-200" 
                             src="{{ $user->avatar }}" 
                             alt="{{ $user->name }}">
                    @else
                        <div class="h-10 w-10 rounded-full bg-primary-500 flex items-center justify-center ring-2 ring-gray-200">
                            <span class="text-white text-sm font-medium">
                                {{ $user ? substr($user->name ?? 'U', 0, 1) : 'U' }}
                            </span>
                        </div>
                    @endif
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ $user->name ?? 'User' }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ $user->email ?? '' }}</p>
                </div>
            </div>
        </div>

        <!-- Level Section -->
        @if($user && $user->level)
            <div class="px-4 py-3 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Current Level</p>
                        <p class="text-sm font-semibold text-gray-900 mt-1">{{ $user->level->getDisplayName() }}</p>
                    </div>
                    <a href="{{ route('level.selection') }}" 
                       class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Change
                    </a>
                </div>
            </div>
        @endif

        <!-- Menu Items -->
        <div class="py-2">
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" 
               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors">
                <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v6H8V5z"></path>
                </svg>
                Dashboard
            </a>
        </div>

        <!-- Logout Section -->
        <div class="border-t border-gray-100 pt-2">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" 
                        class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Sign Out
                </button>
            </form>
        </div>
    </div>
</div>
