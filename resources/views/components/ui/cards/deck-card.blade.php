@props([
    'deck' => null,
    'type' => 'user', // 'user' or 'static'
    'showActions' => true,
    'class' => 'bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow duration-200'
])

<div class="{{ $class }}">
    <div class="flex items-start justify-between">
        <div class="flex-1 min-w-0">
            <h3 class="text-lg font-semibold text-gray-900 truncate">
                {{ $deck->name ?? 'Untitled Deck' }}
            </h3>
            
            @if($deck->description ?? null)
                <p class="mt-1 text-sm text-gray-600 line-clamp-2">
                    {{ $deck->description }}
                </p>
            @endif
            
            <div class="mt-2 flex items-center space-x-4 text-sm text-gray-500">
                <span class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    {{ $deck->cards_count ?? 0 }} cards
                </span>
                
                @if($type === 'static' && $deck->level ?? null)
                    <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                        {{ ucfirst($deck->level) }}
                    </span>
                @endif
            </div>
        </div>
        
        @if($showActions)
            <div class="flex items-center space-x-2 ml-4">
                @if($type === 'user')
                    <a href="{{ route('decks.edit', $deck) }}" 
                       class="text-gray-400 hover:text-gray-600 p-1 rounded transition-colors"
                       title="Edit Deck">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </a>
                @endif
                
                <a href="{{ $type === 'static' ? route('static-decks.study', $deck) : route('decks.study', $deck) }}" 
                   class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors">
                    Study
                </a>
            </div>
        @endif
    </div>
</div>
