@extends('components.layouts.dashboard')

@section('seo')
    <title>Study Session - {{ $deck->name }} - Memora</title>
    <meta name="description" content="Study flashcards from {{ $deck->name }} deck using spaced repetition">
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
    <div class="max-w-6xl mx-auto px-3 sm:px-4 lg:px-6 xl:px-8 py-4 sm:py-6 lg:py-8">
        <!-- Header -->
        <div class="mb-4 sm:mb-6 lg:mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 sm:gap-6">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 sm:gap-3 mb-2">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg sm:rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 truncate">{{ $deck->name }}</h1>
                            <p class="text-sm sm:text-base text-gray-600">Study Session</p>
                        </div>
                    </div>
                </div>
                
                <!-- Mobile: Exit button at top right -->
                <div class="flex justify-between items-center mb-4 sm:hidden">
                    <div></div>
                    <a href="{{ route('dashboard') }}" class="bg-white/90 hover:bg-white text-gray-700 hover:text-gray-900 px-3 py-2 rounded-lg font-semibold transition-all duration-200 shadow-lg hover:shadow-xl border border-white/20 backdrop-blur-sm text-sm flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Exit
                    </a>
                </div>
                
                <!-- Desktop: Original layout -->
                <div class="hidden sm:flex sm:flex-row items-center justify-between gap-4">
                    <!-- Stats Cards -->
                    <div class="flex gap-3 sm:gap-4">
                        <div class="bg-white/80 backdrop-blur-sm rounded-xl sm:rounded-2xl p-3 sm:p-4 shadow-lg border border-white/20">
                            <div class="text-center">
                                <div class="text-xl sm:text-2xl font-bold text-blue-600" id="cards-remaining">-</div>
                                <div class="text-xs sm:text-sm text-gray-600">Remaining</div>
                            </div>
                        </div>
                        
                        <div class="bg-white/80 backdrop-blur-sm rounded-xl sm:rounded-2xl p-3 sm:p-4 shadow-lg border border-white/20">
                            <div class="text-center">
                                <div class="text-xl sm:text-2xl font-bold text-green-600" id="completed-cards">0</div>
                                <div class="text-xs sm:text-sm text-gray-600">Completed</div>
                            </div>
                        </div>
                    </div>
                    
                    <a href="{{ route('dashboard') }}" class="bg-white/90 hover:bg-white text-gray-700 hover:text-gray-900 px-4 py-2 lg:px-6 lg:py-3 rounded-xl lg:rounded-2xl font-semibold transition-all duration-200 shadow-lg hover:shadow-xl border border-white/20 backdrop-blur-sm text-sm flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Exit
                    </a>
                </div>
                
                <!-- Mobile: Stats cards below -->
                <div class="flex gap-3 sm:hidden justify-center">
                    <div class="bg-white/80 backdrop-blur-sm rounded-xl p-3 shadow-lg border border-white/20">
                        <div class="text-center">
                            <div class="text-xl font-bold text-blue-600" id="cards-remaining-mobile">-</div>
                            <div class="text-xs text-gray-600">Remaining</div>
                        </div>
                    </div>
                    
                    <div class="bg-white/80 backdrop-blur-sm rounded-xl p-3 shadow-lg border border-white/20">
                        <div class="text-center">
                            <div class="text-xl font-bold text-green-600" id="completed-cards-mobile">0</div>
                            <div class="text-xs text-gray-600">Completed</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Section -->
        <div class="mb-4 sm:mb-6 lg:mb-8">
            <div class="bg-white/80 backdrop-blur-sm rounded-xl sm:rounded-2xl p-4 sm:p-6 shadow-lg border border-white/20">
                <div class="flex items-center justify-between mb-3 sm:mb-4">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900">Progress</h3>
                    <span class="text-xs sm:text-sm text-gray-600" id="session-stats">Loading...</span>
                </div>
                <div class="relative">
                    <div class="bg-gray-200 rounded-full h-3 sm:h-4 overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-500 to-purple-500 h-3 sm:h-4 rounded-full transition-all duration-500 ease-out" id="progress-bar" style="width: 0%"></div>
                    </div>
                    <div class="flex justify-between text-xs sm:text-sm text-gray-600 mt-2">
                        <span id="progress-text">0 / 0</span>
                        <span id="progress-percentage">0%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Study Area -->
        <div class="grid grid-cols-1 xl:grid-cols-4 gap-3 sm:gap-4 lg:gap-6 xl:gap-8">
            <!-- Study Card -->
            <div class="xl:col-span-3 order-1">
                <div class="bg-white/90 backdrop-blur-sm rounded-xl sm:rounded-2xl lg:rounded-3xl shadow-2xl border border-white/20 overflow-hidden" id="study-card">
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
                            <div class="mb-6 sm:mb-8 lg:mb-12">
                                <div class="flex items-center justify-between mb-3 sm:mb-4">
                                    <div class="inline-flex items-center px-2 py-1 sm:px-3 sm:py-2 rounded-full bg-blue-100 text-blue-800 text-xs sm:text-sm font-medium">
                                        <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Question
                                    </div>
                                    <button id="speak-btn" class="inline-flex items-center px-2 py-1 sm:px-3 sm:py-2 rounded-full bg-blue-100 hover:bg-blue-200 text-blue-800 text-xs sm:text-sm font-medium transition-colors duration-200" title="Play question audio">
                                        <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path>
                                        </svg>
                                        <span class="hidden sm:inline">Play</span>
                                    </button>
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
                                <button id="show-answer-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 sm:px-4 sm:py-2 lg:px-6 lg:py-3 rounded-lg sm:rounded-xl text-sm sm:text-base font-semibold transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1 inline-flex items-center border-2 border-blue-600 w-full sm:w-auto">
                                    <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <span class="text-white font-bold">Show Answer</span>
                                </button>
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
            </div>

            <!-- Session Info Sidebar -->
            <div class="xl:col-span-1 order-2 xl:order-2">
                <div class="space-y-4 sm:space-y-6">
                    <!-- Session Info -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-xl sm:rounded-2xl p-4 sm:p-6 shadow-lg border border-white/20">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4 flex items-center">
                            <svg class="w-4 h-4 sm:w-5 sm:h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Session Info
                        </h3>
                        <div class="space-y-3 sm:space-y-4">
                            <div>
                                <div class="text-xs sm:text-sm text-gray-500">Deck</div>
                                <div class="font-semibold text-gray-900 text-sm sm:text-base truncate">{{ $deck->name }}</div>
                            </div>
                            <div>
                                <div class="text-xs sm:text-sm text-gray-500">Cards Loaded</div>
                                <div class="font-semibold text-gray-900 text-sm sm:text-base" id="cards-loaded">-</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Study Tips - Bottom of Page -->
        <div class="mt-6 sm:mt-8">
            <div class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-blue-200">
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
        </div>
    </div>
</div>

<script>
class StudySession {
    constructor() {
        this.cards = [];
        this.currentCardIndex = 0;
        this.deckInfo = null;
        this.isAnswerShown = false;
        this.pendingUpdates = [];
        
        this.init();
    }

    async init() {
        try {
            console.log('Initializing study session...');
            await this.loadCards();
            console.log('Cards loaded, setting up event listeners...');
            this.setupEventListeners();
            console.log('Event listeners set up, showing current card...');
            this.showCurrentCard();
            console.log('Study session initialized successfully');
        } catch (error) {
            console.error('Failed to initialize study session:', error);
            this.showError('Failed to load study session. Please try again.');
        }
    }

    async loadCards() {
        try {
            console.log('Fetching cards for deck {{ $deck->id }}...');
            const response = await fetch(`/api/study/{{ $deck->id }}/cards`);
            
            console.log('Response status:', response.status);
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('API Error:', errorText);
                throw new Error(`Failed to load cards: ${response.status} - ${errorText}`);
            }
            
            const data = await response.json();
            console.log('API Response:', data);
            
            this.cards = data.cards;
            this.deckInfo = data.deck;
            
            console.log('Loaded cards:', this.cards.length);
            
            if (this.cards.length === 0) {
                console.log('No cards available for this deck');
            }
        } catch (error) {
            console.error('Error in loadCards:', error);
            throw error;
        }
    }

    setupEventListeners() {
        document.getElementById('show-answer-btn').addEventListener('click', () => this.showAnswer());
        document.getElementById('speak-btn').addEventListener('click', () => this.playAudio());
        document.getElementById('again-btn').addEventListener('click', () => this.rateCard(0));
        document.getElementById('hard-btn').addEventListener('click', () => this.rateCard(1));
        document.getElementById('good-btn').addEventListener('click', () => this.rateCard(2));
        document.getElementById('easy-btn').addEventListener('click', () => this.rateCard(3));
        document.getElementById('restart-session').addEventListener('click', () => this.restartSession());
        document.getElementById('retry-btn').addEventListener('click', () => this.init());
    }

    showCurrentCard() {
        console.log('showCurrentCard called, cards length:', this.cards.length);
        
        // Hide loading state and show card content
        document.getElementById('loading-state').classList.add('hidden');
        document.getElementById('card-content').classList.remove('hidden');
        
        if (this.cards.length === 0) {
            console.log('No cards available, showing session complete');
            this.showSessionComplete();
            return;
        }

        const card = this.cards[this.currentCardIndex];
        console.log('Showing card:', card);
        
        document.getElementById('card-front').textContent = card.front;
        document.getElementById('card-back-text').textContent = card.back;
        
        // Hide answer and rating buttons
        document.getElementById('card-back').classList.add('hidden');
        document.getElementById('rating-buttons').classList.add('hidden');
        document.getElementById('show-answer-section').classList.remove('hidden');
        
        // Debug: Check if button is visible
        const showAnswerBtn = document.getElementById('show-answer-btn');
        const showAnswerSection = document.getElementById('show-answer-section');
        console.log('Show Answer Button:', showAnswerBtn);
        console.log('Show Answer Section:', showAnswerSection);
        console.log('Button text content:', showAnswerBtn ? showAnswerBtn.textContent : 'Button not found');
        console.log('Section classes:', showAnswerSection ? showAnswerSection.className : 'Section not found');
        
        this.isAnswerShown = false;
        this.updateProgress();
        console.log('Card displayed successfully');
    }

    showAnswer() {
        document.getElementById('card-back').classList.remove('hidden');
        document.getElementById('rating-buttons').classList.remove('hidden');
        document.getElementById('show-answer-section').classList.add('hidden');
        this.isAnswerShown = true;
    }

    async rateCard(quality) {
        if (this.cards.length === 0) return;

        const card = this.cards[this.currentCardIndex];
        
        // Add to pending updates
        this.pendingUpdates.push({
            card_id: card.id,
            quality: quality
        });

        // Handle card based on quality according to the algorithm
        if (quality === 3) {
            // Easy - remove from array completely
            this.cards.splice(this.currentCardIndex, 1);
        } else {
            // Again, Hard, Good - move to end of array for review
            const movedCard = this.cards.splice(this.currentCardIndex, 1)[0];
            this.cards.push(movedCard);
        }

        // Check if session is complete
        if (this.cards.length === 0) {
            await this.sendUpdates();
            this.showSessionComplete();
            return;
        }

        // Show next card
        this.showCurrentCard();
    }

    async sendUpdates() {
        if (this.pendingUpdates.length === 0) return;

        try {
            const response = await fetch('/api/study/batch-update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    updates: this.pendingUpdates
                })
            });

            if (!response.ok) {
                throw new Error('Failed to update cards');
            }

            console.log('Cards updated successfully');
            this.pendingUpdates = [];
        } catch (error) {
            console.error('Failed to send updates:', error);
        }
    }

    updateProgress() {
        const totalCards = this.deckInfo.cards_loaded;
        const remainingCards = this.cards.length;
        const completedCards = totalCards - remainingCards;
        
        const progressPercentage = totalCards > 0 ? (completedCards / totalCards) * 100 : 0;
        
        document.getElementById('progress-bar').style.width = `${progressPercentage}%`;
        document.getElementById('progress-text').textContent = `${completedCards} / ${totalCards}`;
        document.getElementById('progress-percentage').textContent = `${Math.round(progressPercentage)}%`;
        document.getElementById('cards-remaining').textContent = remainingCards;
        document.getElementById('completed-cards').textContent = completedCards;
        document.getElementById('cards-loaded').textContent = totalCards;
        document.getElementById('session-stats').textContent = `${completedCards} completed`;
        
        // Update mobile stats cards
        document.getElementById('cards-remaining-mobile').textContent = remainingCards;
        document.getElementById('completed-cards-mobile').textContent = completedCards;
    }

    showSessionComplete() {
        document.getElementById('loading-state').classList.add('hidden');
        document.getElementById('card-content').classList.add('hidden');
        document.getElementById('session-complete').classList.remove('hidden');
        
        // Send any pending updates
        this.sendUpdates();
    }

    showError(message) {
        document.getElementById('loading-state').classList.add('hidden');
        document.getElementById('card-content').classList.add('hidden');
        document.getElementById('error-state').classList.remove('hidden');
        document.getElementById('error-message').textContent = message;
    }

    async restartSession() {
        document.getElementById('session-complete').classList.add('hidden');
        document.getElementById('loading-state').classList.remove('hidden');
        
        try {
            await this.loadCards();
            this.currentCardIndex = 0;
            this.pendingUpdates = [];
            this.showCurrentCard();
        } catch (error) {
            console.error('Failed to restart session:', error);
            this.showError('Failed to restart session. Please try again.');
        }
    }

    playAudio() {
        const frontText = document.getElementById('card-front').textContent;
        this.speakText(frontText);
    }

    speakText(text) {
        if ('speechSynthesis' in window) {
            // Stop any current speech
            speechSynthesis.cancel();
            
            const utterance = new SpeechSynthesisUtterance(text);
            
            // Configure speech settings
            utterance.lang = 'en-US'; // Change to 'fa-IR' for Persian if needed
            utterance.rate = 0.8; // Slightly slower for better comprehension
            utterance.pitch = 1.0;
            utterance.volume = 0.8;
            
            // Visual feedback - show speaking state
            const speakBtn = document.getElementById('speak-btn');
            const originalContent = speakBtn.innerHTML;
            
            speakBtn.innerHTML = `
                <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"></path>
                </svg>
                <span class="hidden sm:inline">Playing...</span>
            `;
            speakBtn.classList.add('bg-blue-200');
            speakBtn.disabled = true;
            
            // Restore button when speech ends
            utterance.onend = () => {
                speakBtn.innerHTML = originalContent;
                speakBtn.classList.remove('bg-blue-200');
                speakBtn.disabled = false;
            };
            
            // Speak the text
            speechSynthesis.speak(utterance);
            
            console.log('Speaking text:', text);
        } else {
            console.log('Text-to-speech not supported in this browser');
        }
    }
}

// Initialize study session when page loads
document.addEventListener('DOMContentLoaded', () => {
    new StudySession();
});
</script>
@endsection