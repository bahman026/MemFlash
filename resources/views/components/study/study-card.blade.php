@props([
    'class' => 'bg-white/90 backdrop-blur-sm rounded-xl sm:rounded-2xl lg:rounded-3xl shadow-2xl border border-white/20 overflow-hidden'
])

<div class="{{ $class }}" id="study-card">
    <div class="text-center p-3 sm:p-4 lg:p-6 xl:p-8 2xl:p-12" style="min-height: 350px; sm:min-height: 400px; lg:min-height: 500px;">
        <!-- Loading State -->
        <div id="loading-state" class="py-12 sm:py-16 lg:py-20">
            <div class="relative">
                <div class="animate-spin rounded-full h-12 w-12 sm:h-16 sm:w-16 border-4 border-blue-200 border-t-blue-600 mx-auto"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-6 h-6 sm:w-8 sm:h-8 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full animate-pulse"></div>
                </div>
            </div>
            <p class="mt-4 sm:mt-6 text-gray-600 text-base sm:text-lg">Loading your study session...</p>
        </div>

        <!-- Card Content -->
        <div id="card-content" class="hidden">
            <!-- Front of Card -->
            <div class="mb-6 sm:mb-8 lg:mb-12" id="question-section">
                <div class="flex items-center justify-between mb-3 sm:mb-4">
                    <div class="inline-flex items-center px-2 py-1 sm:px-3 sm:py-2 rounded-full bg-blue-100 text-blue-800 text-xs sm:text-sm font-medium">
                        <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Question
                    </div>
                    <div class="flex items-center space-x-2">
                        <button id="fullscreen-btn" class="inline-flex items-center px-2 py-1 sm:px-3 sm:py-2 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-700 hover:text-gray-900 text-xs sm:text-sm font-medium transition-all duration-200 group" title="View question in fullscreen">
                            <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 group-hover:scale-110 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                            </svg>
                            <span class="hidden sm:inline">Fullscreen</span>
                        </button>
                        <button id="speak-btn" class="inline-flex items-center px-2 py-1 sm:px-3 sm:py-2 rounded-full bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs sm:text-sm font-medium transition-colors duration-200" title="Play question audio">
                            <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path>
                            </svg>
                            <span class="hidden sm:inline">Play</span>
                        </button>
                    </div>
                </div>
                <div class="text-xl sm:text-2xl md:text-3xl lg:text-4xl xl:text-5xl font-bold text-gray-900 leading-tight px-1 sm:px-2" id="card-front">-</div>
            </div>

            <!-- Back of Card -->
            <div id="card-back" class="hidden mb-6 sm:mb-8 lg:mb-12">
                <div class="inline-flex items-center px-2 py-1 sm:px-3 sm:py-2 rounded-full bg-green-100 text-green-800 text-xs sm:text-sm font-medium mb-3 sm:mb-4 lg:mb-6">
                    <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Answer
                </div>
                <div class="text-lg sm:text-xl md:text-2xl lg:text-3xl xl:text-4xl text-gray-700 leading-relaxed px-1 sm:px-2 font-bold" id="card-back-text">-</div>
            </div>

            <!-- Show Answer Button -->
            <div id="show-answer-section" class="text-center">
                <button id="show-answer-btn" class="group relative bg-blue-600 hover:bg-blue-700 text-white px-6 py-4 sm:px-8 sm:py-5 lg:px-10 lg:py-6 rounded-2xl sm:rounded-3xl text-base sm:text-lg font-bold transition-all duration-500 shadow-2xl hover:shadow-3xl transform hover:-translate-y-2 hover:scale-105 inline-flex items-center justify-center w-full sm:w-auto min-w-[200px] overflow-hidden">
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

            <!-- Rating Buttons -->
            <div id="rating-buttons" class="hidden">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 lg:gap-4 max-w-4xl mx-auto">
                    <button id="again-btn" class="bg-red-500 hover:bg-red-600 text-white px-2 py-2 sm:px-3 sm:py-2 lg:px-4 lg:py-3 rounded-lg sm:rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 text-center">
                        <div class="text-xs sm:text-sm lg:text-base font-bold mb-1">Again</div>
                        <div class="text-xs opacity-90">1 day</div>
                    </button>
                    <button id="hard-btn" class="bg-orange-500 hover:bg-orange-600 text-white px-2 py-2 sm:px-3 sm:py-2 lg:px-4 lg:py-3 rounded-lg sm:rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 text-center">
                        <div class="text-xs sm:text-sm lg:text-base font-bold mb-1">Hard</div>
                        <div class="text-xs opacity-90">2 days</div>
                    </button>
                    <button id="good-btn" class="bg-green-500 hover:bg-green-600 text-white px-2 py-2 sm:px-3 sm:py-2 lg:px-4 lg:py-3 rounded-lg sm:rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 text-center">
                        <div class="text-xs sm:text-sm lg:text-base font-bold mb-1">Good</div>
                        <div class="text-xs opacity-90">7 days</div>
                    </button>
                    <button id="easy-btn" class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-2 sm:px-3 sm:py-2 lg:px-4 lg:py-3 rounded-lg sm:rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 text-center">
                        <div class="text-xs sm:text-sm lg:text-base font-bold mb-1">Easy</div>
                        <div class="text-xs opacity-90">10 days</div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Session Complete -->
        <div id="session-complete" class="hidden py-12 sm:py-16 lg:py-20">
            <div class="text-6xl sm:text-8xl mb-4 sm:mb-6">🎉</div>
            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 mb-3 sm:mb-4">Amazing Work!</h2>
            <p class="text-lg sm:text-xl text-gray-600 mb-6 sm:mb-8">You've completed all the cards for today. Keep up the great work!</p>
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center">
                <a href="{{ route('dashboard') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 sm:px-8 sm:py-4 rounded-xl sm:rounded-2xl text-base sm:text-lg font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 inline-flex items-center justify-center border-2 border-blue-600">
                    <span class="text-white font-bold">Back to Dashboard</span>
                </a>
                <button id="restart-session" class="bg-white hover:bg-gray-50 text-gray-700 hover:text-gray-900 px-6 py-3 sm:px-8 sm:py-4 rounded-xl sm:rounded-2xl text-base sm:text-lg font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 border border-gray-200">
                    Study Again
                </button>
            </div>
        </div>

        <!-- Error State -->
        <div id="error-state" class="hidden py-12 sm:py-16 lg:py-20">
            <div class="text-6xl sm:text-8xl mb-4 sm:mb-6">❌</div>
            <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 mb-3 sm:mb-4">Oops!</h2>
            <p class="text-lg sm:text-xl text-gray-600 mb-6 sm:mb-8" id="error-message">Something went wrong. Please try again.</p>
            <button id="retry-btn" class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-6 py-3 sm:px-8 sm:py-4 rounded-xl sm:rounded-2xl text-base sm:text-lg font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                Try Again
            </button>
        </div>
    </div>
</div>
