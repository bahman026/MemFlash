@props([
    'decks' => null,
    'totalCards' => 0,
    'totalStaticCards' => 0,
    'class' => 'grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 lg:gap-6 mb-4 sm:mb-6 lg:mb-8'
])

<div class="{{ $class }}">
    <div class="bg-white overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200 rounded-xl border border-gray-100">
        <div class="p-4 sm:p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-sm">
                        <span class="text-white text-lg sm:text-xl">📚</span>
                    </div>
                </div>
                <div class="ml-3 sm:ml-4 w-0 flex-1 min-w-0">
                    <dl>
                        <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate">Personal Decks</dt>
                        <dd class="text-xl sm:text-2xl font-bold text-gray-900 mt-1">{{ $decks->count() }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-200 rounded-xl border border-gray-100">
        <div class="p-4 sm:p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-sm">
                        <span class="text-white text-lg sm:text-xl">🎯</span>
                    </div>
                </div>
                <div class="ml-3 sm:ml-4 w-0 flex-1 min-w-0">
                    <dl>
                        <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate">Total Cards</dt>
                        <dd class="text-xl sm:text-2xl font-bold text-gray-900 mt-1">{{ $totalCards + $totalStaticCards }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
