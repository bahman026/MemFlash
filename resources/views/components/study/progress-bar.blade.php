@props([
    'totalCards' => 0,
    'completedCards' => 0,
    'remainingCards' => 0,
    'class' => 'bg-white/80 backdrop-blur-sm rounded-xl sm:rounded-2xl p-4 sm:p-6 shadow-lg border border-white/20'
])

<div class="{{ $class }}">
    <div class="flex items-center justify-between mb-3 sm:mb-4">
        <h3 class="text-base sm:text-lg font-semibold text-gray-900">Progress</h3>
        <span class="text-xs sm:text-sm text-gray-600" id="session-stats">{{ $completedCards }} completed</span>
    </div>
    <div class="relative">
        <div class="bg-gray-200 rounded-full h-3 sm:h-4 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-3 sm:h-4 rounded-full transition-all duration-500 ease-out" 
                 id="progress-bar" 
                 style="width: {{ $totalCards > 0 ? ($completedCards / $totalCards) * 100 : 0 }}%"></div>
        </div>
        <div class="flex justify-between text-xs sm:text-sm text-gray-600 mt-2">
            <span id="progress-text">{{ $completedCards }} / {{ $totalCards }}</span>
            <span id="progress-percentage">{{ $totalCards > 0 ? round(($completedCards / $totalCards) * 100) : 0 }}%</span>
        </div>
    </div>
</div>
