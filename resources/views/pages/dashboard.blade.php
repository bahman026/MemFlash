<x-layouts.dashboard>
    <x-slot name="seo">
        <title>Dashboard - MemFlash</title>
        <meta name="description" content="Your personal flashcard dashboard">
    </x-slot>

    <div class="space-y-4 sm:space-y-6 lg:space-y-8">
        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Welcome Section -->
        <div class="mb-4 sm:mb-6 lg:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 mb-2 leading-tight">
                        Welcome back, {{ auth()->user()->name }}! 👋
                    </h1>
                    <p class="text-gray-600 text-sm sm:text-base leading-relaxed">Ready to continue your learning journey?</p>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 lg:gap-6 mb-4 sm:mb-6 lg:mb-8">
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

        <!-- Deck Limits Information -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-4 sm:p-6 mb-6 sm:mb-8">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4 flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Deck Limits</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Personal Decks:</span>
                            <span class="font-medium text-gray-900">
                                {{ auth()->user()->getDeckCount() }}/200
                                @if(auth()->user()->hasReachedDeckLimit())
                                    <span class="text-red-600 ml-1">(Limit reached)</span>
                                @else
                                    <span class="text-green-600 ml-1">({{ auth()->user()->getRemainingDeckSlots() }} remaining)</span>
                                @endif
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Cards per Deck:</span>
                            <span class="font-medium text-gray-900">Up to 2,000 cards per deck</span>
                        </div>
                    </div>
                    @if(auth()->user()->hasReachedDeckLimit())
                        <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-sm text-red-800">
                                <strong>Deck limit reached!</strong> You have created the maximum of 200 personal decks. 
                                Please delete some decks before creating new ones.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Static Decks Section -->
        @if($staticDecks->isNotEmpty())
            <div class="bg-white shadow-sm rounded-xl border border-gray-100 mb-6 sm:mb-8">
                <div class="px-3 py-4 sm:px-4 sm:py-5 lg:px-6 lg:py-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6 space-y-3 sm:space-y-0">
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-gray-900">American English File {{ auth()->user()->level->getDisplayName() }}</h2>
                            <p class="text-sm text-gray-600 mt-1">Curriculum decks for your level</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $staticDecks->count() }} lessons
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                        @foreach($staticDecks as $staticDeck)
                            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl border border-blue-200 hover:border-blue-300 hover:shadow-lg transition-all duration-300 group overflow-hidden">
                                <!-- Header with deck name and lesson number -->
                                <div class="p-6 pb-4">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center space-x-2 mb-2">
                                                <span class="inline-flex items-center justify-center w-8 h-8 bg-blue-600 text-white text-sm font-bold rounded-full">
                                                    {{ $staticDeck->lesson_number }}
                                                </span>
                                                <h3 class="text-lg font-bold text-gray-900 group-hover:text-blue-600 transition-colors truncate leading-tight">
                                                    Lesson {{ $staticDeck->lesson_number }}
                                                </h3>
                                            </div>
                                            
                                            @php
                                                $userProgress = $staticDeck->userProgress->first();
                                                $cardsStudied = $userProgress ? $userProgress->cards_studied : 0;
                                                $totalCards = $staticDeck->cards_count;
                                                $progressPercentage = $totalCards > 0 ? ($cardsStudied / $totalCards) * 100 : 0;
                                                $isCompleted = $userProgress ? $userProgress->isCompleted() : false;
                                            @endphp
                                            
                                            <!-- Progress Section -->
                                            <div class="space-y-3">
                                                <div class="flex items-center justify-between text-sm">
                                                    <span class="text-gray-600">Progress</span>
                                                    <span class="font-medium text-gray-900">{{ round($progressPercentage) }}%</span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-2">
                                                    <div class="bg-gradient-to-r from-blue-500 to-green-500 h-2 rounded-full transition-all duration-500" style="width: {{ $progressPercentage }}%"></div>
                                                </div>
                                                <div class="flex items-center justify-between text-xs text-gray-500">
                                                    <span>{{ $cardsStudied }}/{{ $totalCards }} studied</span>
                                                    @if($isCompleted)
                                                        <span class="flex items-center text-green-600 font-medium">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                            Completed!
                                                        </span>
                                                    @else
                                                        <span class="flex items-center text-blue-600 font-medium">
                                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                            {{ $staticDeck->userSettings->first()->cards_per_day ?? 10 }}/day
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action buttons -->
                                <div class="px-6 pb-6">
                                    <div class="flex flex-col space-y-3">
                                        <!-- Primary Study Button -->
                                        <a href="{{ route('static-decks.study', $staticDeck) }}" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-xl text-sm font-bold transition-all duration-200 text-center flex items-center justify-center group shadow-lg hover:shadow-xl">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                            </svg>
                                            Start Learning
                                        </a>

                                        <!-- Cards per day setting -->
                                        <div class="mb-3">
                                            <form method="POST" action="{{ route('static-decks.cards-per-day', $staticDeck) }}" class="flex items-center space-x-2">
                                                @csrf
                                                <label class="text-xs text-gray-600 font-medium">Cards per day:</label>
                                                <input type="number" name="cards_per_day" value="{{ $staticDeck->userSettings->first()->cards_per_day ?? 10 }}" 
                                                       min="1" max="100" class="w-16 px-2 py-1 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                <button type="submit" class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                                                    Update
                                                </button>
                                            </form>
                                        </div>

                                        <!-- Secondary Actions -->
                                        <div class="grid grid-cols-2 gap-2">
                                            <a href="{{ route('static-decks.preview', $staticDeck) }}" class="px-3 py-2 border border-blue-300 rounded-lg text-xs font-medium text-blue-700 hover:bg-blue-50 hover:border-blue-400 transition-all duration-200 text-center flex items-center justify-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                Preview
                                            </a>
                                            
                                            <form method="POST" action="{{ route('static-decks.reset', $staticDeck) }}" onsubmit="return confirm('Are you sure you want to reset the learning progress for this lesson? All cards will start from the beginning. This action cannot be undone.')">
                                                @csrf
                                                <button type="submit" class="w-full px-3 py-2 border border-orange-300 rounded-lg text-xs font-medium text-orange-700 hover:bg-orange-50 hover:border-orange-400 transition-all duration-200 flex items-center justify-center">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                    </svg>
                                                    Reset
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Personal Decks Section -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-100">
            <div class="px-3 py-4 sm:px-4 sm:py-5 lg:px-6 lg:py-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6 space-y-3 sm:space-y-0">
                    <h2 class="text-lg sm:text-xl font-bold text-gray-900">Your Personal Decks</h2>
                    @if(auth()->user()->hasReachedDeckLimit())
                        <button disabled class="bg-gray-400 text-white px-4 py-2.5 rounded-lg text-sm font-semibold w-full sm:w-auto cursor-not-allowed opacity-75">
                            + Create New Deck (Limit Reached)
                        </button>
                    @else
                        <button onclick="openDeckModal()" class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200 w-full sm:w-auto shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                            + Create New Deck
                        </button>
                    @endif
                </div>

                @if($decks->isEmpty())
                    <div class="text-center py-8 sm:py-12">
                        <div class="mx-auto h-20 w-20 sm:h-24 sm:w-24 text-gray-400 mb-4">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-2">No decks yet</h3>
                        <p class="text-gray-500 mb-6 text-sm sm:text-base">Get started by creating your first flashcard deck!</p>
                        <div class="flex flex-col sm:flex-row gap-3 justify-center">
                            @if(auth()->user()->hasReachedDeckLimit())
                                <button disabled class="bg-gray-400 text-white px-6 py-3 rounded-lg text-sm font-semibold cursor-not-allowed opacity-75">
                                    Create Your First Deck (Limit Reached)
                                </button>
                            @else
                                <button onclick="openDeckModal()" class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-lg text-sm font-semibold transition-all duration-200 shadow-sm hover:shadow-md focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    Create Your First Deck
                                </button>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                        @foreach($decks as $deck)
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
                            
                            <div class="bg-white rounded-2xl border border-gray-200 hover:border-gray-300 hover:shadow-lg transition-all duration-300 group overflow-hidden">
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
                                        <div class="flex items-center space-x-2 ml-3 flex-shrink-0">
                                            @if($deck->is_public)
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Public
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                                    </svg>
                                                    Private
                                                </span>
                                            @endif
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
                                        <div class="grid grid-cols-3 gap-2">
                                            <a href="{{ route('decks.edit', $deck) }}" class="px-3 py-2 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 transition-all duration-200 text-center flex items-center justify-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                Edit
                                            </a>
                                            
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
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>

    <!-- Deck Creation Modal -->
    <x-deck-create-modal />
</x-layouts.dashboard>
