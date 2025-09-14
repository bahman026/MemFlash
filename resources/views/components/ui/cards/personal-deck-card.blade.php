@props([
    'deck' => null,
    'class' => 'bg-white rounded-2xl border border-gray-200 hover:border-gray-300 hover:shadow-lg transition-all duration-300 group overflow-hidden'
])

@php
    $dueCards = $deck->cards()
        ->where(function ($query) {
            $query->whereNull('revised_at')
                ->orWhere('revised_at', '<=', now());
        })
        ->count();
    $totalCards = $deck->cards()->count();
    $studiedCards = $deck->cards()->whereNotNull('last_reviewed')->count();
    $progressPercentage = $totalCards > 0 ? ($studiedCards / $totalCards) * 100 : 0;
@endphp

<div class="{{ $class }}">
    <!-- Header with deck name and status -->
    <div class="p-6 pb-4">
        <div class="flex items-start justify-between mb-4">
            <div class="flex-1 min-w-0">
                <h3 class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition-colors truncate leading-tight mb-1">
                    {{ $deck->name }}
                </h3>
                <div class="flex items-center space-x-3 text-sm text-gray-500">
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        {{ $deck->cards_count }} {{ Str::plural('card', $deck->cards_count) }}
                    </span>
                    <span class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ $deck->new_cards_per_day }}/day
                    </span>
                </div>
            </div>
        </div>

        <!-- Progress section -->
        <div class="space-y-3">
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600">Progress</span>
                <span class="font-medium text-gray-900">{{ round($progressPercentage) }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                <div class="bg-gradient-to-r from-blue-500 to-green-500 h-2 rounded-full transition-all duration-500" style="width: {{ $progressPercentage }}%"></div>
            </div>
            <div class="flex items-center justify-between text-xs text-gray-500">
                <span>{{ $studiedCards }}/{{ $totalCards }} studied</span>
                <span class="flex items-center font-medium {{ $dueCards > 0 ? 'text-orange-600' : 'text-green-600' }}">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ $dueCards }} due today
                </span>
            </div>
        </div>
    </div>

    <!-- Action buttons -->
    <div class="px-6 pb-6">
        <div class="flex flex-col space-y-3">
            <!-- Primary Study Button -->
            <a href="{{ route('study.start', $deck) }}" class="w-full {{ $dueCards > 0 ? 'bg-blue-600 hover:bg-blue-700 shadow-lg hover:shadow-xl' : 'bg-gray-500 hover:bg-gray-600' }} text-white px-4 py-3 rounded-xl text-sm font-bold transition-all duration-200 text-center flex items-center justify-center group">
                @if($dueCards > 0)
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Study Now ({{ $dueCards }})
                @else
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    All Caught Up!
                @endif
            </a>

            <!-- Secondary Actions -->
            <div class="grid grid-cols-2 gap-2 mb-2">
                <a href="{{ route('decks.edit', $deck) }}" class="px-3 py-2 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 text-center flex items-center justify-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit
                </a>
                
                <a href="{{ route('decks.export', $deck) }}" class="px-3 py-2 border border-green-300 rounded-lg text-xs font-medium text-green-700 hover:bg-green-50 hover:border-green-400 transition-all duration-200 text-center flex items-center justify-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export
                </a>
            </div>

            <!-- Danger Actions -->
            <div class="grid grid-cols-2 gap-2">
                <form method="POST" action="{{ route('decks.reset', $deck) }}" onsubmit="return confirm('Are you sure you want to reset the learning progress for this deck? All cards will start from the beginning. This action cannot be undone.')">
                    @csrf
                    <button type="submit" class="w-full px-3 py-2 border border-orange-300 rounded-lg text-xs font-medium text-orange-700 hover:bg-orange-50 hover:border-orange-400 transition-all duration-200 flex items-center justify-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Reset
                    </button>
                </form>
                
                <form method="POST" action="{{ route('decks.destroy', $deck) }}" onsubmit="return confirm('Are you sure you want to delete this deck? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full px-3 py-2 border border-red-300 rounded-lg text-xs font-medium text-red-700 hover:bg-red-50 hover:border-red-400 transition-all duration-200 flex items-center justify-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
