@props([
    'remainingCards' => 0,
    'completedCards' => 0,
    'showMobile' => true,
    'class' => ''
])

<!-- Desktop Stats Cards -->
<div class="hidden sm:flex gap-3 sm:gap-4 {{ $class }}">
    <div class="bg-white/80 backdrop-blur-sm rounded-xl sm:rounded-2xl p-3 sm:p-4 shadow-lg border border-white/20">
        <div class="text-center">
            <div class="text-xl sm:text-2xl font-bold text-blue-600" id="cards-remaining">{{ $remainingCards }}</div>
            <div class="text-xs sm:text-sm text-gray-600">Remaining</div>
        </div>
    </div>

    <div class="bg-white/80 backdrop-blur-sm rounded-xl sm:rounded-2xl p-3 sm:p-4 shadow-lg border border-white/20">
        <div class="text-center">
            <div class="text-xl sm:text-2xl font-bold text-green-600" id="completed-cards">{{ $completedCards }}</div>
            <div class="text-xs sm:text-sm text-gray-600">Completed</div>
        </div>
    </div>
</div>

@if($showMobile)
    <!-- Mobile Stats Cards -->
    <div class="flex gap-3 sm:hidden justify-center {{ $class }}">
        <div class="bg-white/80 backdrop-blur-sm rounded-xl p-3 shadow-lg border border-white/20">
            <div class="text-center">
                <div class="text-xl font-bold text-blue-600" id="cards-remaining-mobile">{{ $remainingCards }}</div>
                <div class="text-xs text-gray-600">Remaining</div>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur-sm rounded-xl p-3 shadow-lg border border-white/20">
            <div class="text-center">
                <div class="text-xl font-bold text-green-600" id="completed-cards-mobile">{{ $completedCards }}</div>
                <div class="text-xs text-gray-600">Completed</div>
            </div>
        </div>
    </div>
@endif
