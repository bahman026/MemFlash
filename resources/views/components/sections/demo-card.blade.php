@props([
    'cardNumber' => '1',
    'totalCards' => '25',
    'question' => 'What is the capital of France?',
    'difficulty' => 'Easy'
])

<div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-500">Card {{ $cardNumber }} of {{ $totalCards }}</span>
            <div class="flex items-center space-x-2">
                <button class="p-1 text-gray-400 hover:text-red-500 transition-colors" title="Like this card">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </button>
                <span class="text-sm text-primary-500 font-medium">{{ $difficulty }}</span>
            </div>
        </div>
        <div class="h-32 bg-gray-50 rounded-lg flex items-center justify-center relative">
            <span class="text-gray-500">{{ $question }}</span>
            <button class="absolute top-2 right-2 p-2 text-gray-400 hover:text-primary-500 transition-colors" title="Read aloud">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path>
                </svg>
            </button>
        </div>
        <div class="flex space-x-2">
            <button class="flex-1 bg-red-100 text-red-600 py-2 px-3 rounded text-sm font-medium">
                Again
            </button>
            <button class="flex-1 bg-orange-100 text-orange-600 py-2 px-3 rounded text-sm font-medium">
                Hard
            </button>
            <button class="flex-1 bg-yellow-100 text-yellow-600 py-2 px-3 rounded text-sm font-medium">
                Good
            </button>
            <button class="flex-1 bg-green-100 text-green-600 py-2 px-3 rounded text-sm font-medium">
                Easy
            </button>
        </div>
    </div>
</div>
