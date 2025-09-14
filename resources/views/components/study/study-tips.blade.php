@props([
    'class' => 'bg-gradient-to-br from-blue-50 to-purple-50 rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-blue-200'
])

<div class="{{ $class }}">
    <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4 flex items-center">
        <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
        </svg>
        Study Tips
    </h3>
    <ul class="text-xs sm:text-sm text-gray-600 space-y-1 sm:space-y-2">
        <li class="flex items-start">
            <span class="text-blue-500 mr-2 text-xs">•</span>
            <span class="text-xs sm:text-sm">Take your time to think before rating</span>
        </li>
        <li class="flex items-start">
            <span class="text-blue-500 mr-2 text-xs">•</span>
            <span class="text-xs sm:text-sm">Be honest with your self-assessment</span>
        </li>
        <li class="flex items-start">
            <span class="text-blue-500 mr-2 text-xs">•</span>
            <span class="text-xs sm:text-sm">Review difficult cards more frequently</span>
        </li>
    </ul>
</div>
