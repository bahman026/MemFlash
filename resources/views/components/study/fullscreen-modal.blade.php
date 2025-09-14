@props([
    'class' => 'fixed inset-0 bg-black bg-opacity-90 z-50 hidden flex items-center justify-center p-4'
])

<!-- Fullscreen Question Modal -->
<div id="fullscreen-modal" class="{{ $class }}">
    <div class="relative w-full h-full max-w-6xl max-h-full bg-white rounded-2xl shadow-2xl overflow-hidden">
        <!-- Fullscreen Header -->
        <div class="flex items-center justify-between p-4 sm:p-6 border-b border-gray-200 bg-gray-50">
            <div class="flex items-center space-x-3">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Question</h3>
                    <p class="text-sm text-gray-500">Fullscreen View</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button id="fullscreen-speak-btn" class="inline-flex items-center px-3 py-2 rounded-lg bg-blue-100 hover:bg-blue-200 text-blue-800 text-sm font-medium transition-colors duration-200" title="Play question audio">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path>
                    </svg>
                    Play Audio
                </button>
                <button id="exit-fullscreen-btn" class="inline-flex items-center px-3 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 hover:text-gray-900 text-sm font-medium transition-colors duration-200" title="Exit fullscreen">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Exit
                </button>
            </div>
        </div>
        
        <!-- Fullscreen Content -->
        <div class="flex-1 flex items-center justify-center p-8 sm:p-12 lg:p-16">
            <div class="text-center max-w-4xl w-full">
                <!-- Question Section -->
                <div id="fullscreen-question-section" class="mb-8">
                    <div class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl xl:text-7xl font-bold text-gray-900 leading-tight" id="fullscreen-question-text">-</div>
                </div>
                
                <!-- Show Answer Button in Fullscreen (Always visible when answer is hidden) -->
                <div id="fullscreen-show-answer-section" class="text-center mb-8">
                    <button id="fullscreen-show-answer-btn" class="group relative bg-blue-600 hover:bg-blue-700 text-white px-6 py-4 sm:px-8 sm:py-5 lg:px-10 lg:py-6 rounded-2xl sm:rounded-3xl text-base sm:text-lg font-bold transition-all duration-500 shadow-2xl hover:shadow-3xl transform hover:-translate-y-2 hover:scale-105 inline-flex items-center justify-center w-full sm:w-auto min-w-[200px] overflow-hidden">
                        <!-- Animated background effect -->
                        <div class="absolute inset-0 bg-gradient-to-r from-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        
                        <!-- Shimmer effect -->
                        <div class="absolute inset-0 -translate-x-full group-hover:translate-x-full transition-transform duration-1000 bg-gradient-to-r from-transparent via-white/30 to-transparent"></div>

                        <!-- Button content -->
                        <div class="relative z-10 flex items-center justify-center">
                            <div class="mr-3 sm:mr-4">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </div>
                            <span class="text-white font-bold tracking-wide group-hover:tracking-wider transition-all duration-300">Show Answer</span>

                            <!-- Arrow icon -->
                            <div class="ml-3 sm:ml-4 group-hover:translate-x-1 transition-transform duration-300">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Ripple effect on click -->
                        <div class="absolute inset-0 rounded-2xl sm:rounded-3xl overflow-hidden">
                            <div class="absolute inset-0 bg-white/20 scale-0 group-active:scale-100 transition-transform duration-150 rounded-2xl sm:rounded-3xl"></div>
                        </div>
                    </button>
                    
                    <!-- Helper text -->
                    <p class="mt-3 text-sm text-gray-500 font-medium">
                        Think about your answer, then click to reveal
                    </p>
                </div>
                
                <!-- Answer Section (Hidden by default) -->
                <div id="fullscreen-answer-section" class="hidden">
                    <div class="mb-8">
                        <div class="inline-flex items-center px-3 py-2 rounded-full bg-green-100 text-green-800 text-sm font-medium mb-4">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Answer
                        </div>
                        <div class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl xl:text-6xl font-bold text-gray-900 leading-tight mb-8" id="fullscreen-answer-text">-</div>
                    </div>
                    
                    <!-- Rating Buttons in Fullscreen -->
                    <div class="text-center">
                        <div class="mb-4">
                            <p class="text-sm text-gray-600 font-medium mb-4">How well did you know this?</p>
                        </div>
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 lg:gap-4 max-w-4xl mx-auto">
                            <button id="fullscreen-again-btn" class="bg-red-500 hover:bg-red-600 text-white px-2 py-2 sm:px-3 sm:py-2 lg:px-4 lg:py-3 rounded-lg sm:rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 text-center">
                                <div class="text-xs sm:text-sm lg:text-base font-bold mb-1">Again</div>
                                <div class="text-xs opacity-90">1 day</div>
                            </button>
                            <button id="fullscreen-hard-btn" class="bg-orange-500 hover:bg-orange-600 text-white px-2 py-2 sm:px-3 sm:py-2 lg:px-4 lg:py-3 rounded-lg sm:rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 text-center">
                                <div class="text-xs sm:text-sm lg:text-base font-bold mb-1">Hard</div>
                                <div class="text-xs opacity-90">2 days</div>
                            </button>
                            <button id="fullscreen-good-btn" class="bg-green-500 hover:bg-green-600 text-white px-2 py-2 sm:px-3 sm:py-2 lg:px-4 lg:py-3 rounded-lg sm:rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 text-center">
                                <div class="text-xs sm:text-sm lg:text-base font-bold mb-1">Good</div>
                                <div class="text-xs opacity-90">7 days</div>
                            </button>
                            <button id="fullscreen-easy-btn" class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-2 sm:px-3 sm:py-2 lg:px-4 lg:py-3 rounded-lg sm:rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 text-center">
                                <div class="text-xs sm:text-sm lg:text-base font-bold mb-1">Easy</div>
                                <div class="text-xs opacity-90">10 days</div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
