<x-layouts.dashboard>
    <x-slot name="seo">
        <title>Create Deck - MemFlash</title>
        <meta name="description" content="Create a new flashcard deck">
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-6 sm:mb-8">
            <div class="flex items-center mb-4">
                <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Create Deck</h1>
            </div>
            <p class="text-gray-600">Start an empty deck and add cards yourself.</p>
        </div>

        <!-- Errors -->
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <ul class="text-sm text-red-800 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Create Form -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-100">
            <form method="POST" action="{{ route('decks.store') }}" class="p-4 sm:p-6">
                @csrf
                <input type="hidden" name="import_mode" value="empty">

                <!-- Deck Name -->
                <div class="mb-4 sm:mb-6">
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Deck Name</label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ old('name') }}"
                           required
                           maxlength="255"
                           placeholder="e.g. Unit 3 vocabulary"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors @error('name') border-red-300 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Cards per day -->
                <div class="mb-4 sm:mb-6">
                    <label for="new_cards_per_day" class="block text-sm font-semibold text-gray-700 mb-2">Cards per day</label>
                    <input type="number"
                           id="new_cards_per_day"
                           name="new_cards_per_day"
                           value="{{ old('new_cards_per_day', 10) }}"
                           min="1"
                           max="100"
                           required
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors @error('new_cards_per_day') border-red-300 @enderror">
                    <p class="mt-1 text-xs text-gray-500">How many new cards to introduce in each study session.</p>
                    @error('new_cards_per_day')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remaining slots -->
                <div class="mb-4 sm:mb-6 p-4 bg-gray-50 rounded-lg">
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div>
                            <p class="text-2xl font-bold text-primary-600">{{ auth()->user()->getDeckCount() }}</p>
                            <p class="text-xs text-gray-500">Your Decks</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-green-600">{{ auth()->user()->getRemainingDeckSlots() }}</p>
                            <p class="text-xs text-gray-500">Slots Remaining</p>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                    <a href="{{ route('dashboard') }}"
                       class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500 text-center">
                        Cancel
                    </a>
                    <button type="submit"
                            class="flex-1 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                        Create Deck
                    </button>
                </div>
            </form>
        </div>

        <!-- Import hint: the dashboard modal owns the CSV/XLSX import flow -->
        <div class="mt-6 bg-white shadow-sm rounded-xl border border-gray-100 p-4 sm:p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-2">Importing from a file?</h3>
            <p class="text-sm text-gray-600">
                Use <span class="font-medium">Create Deck</span> on the
                <a href="{{ route('dashboard') }}" class="text-primary-600 hover:text-primary-700 font-medium">dashboard</a>
                to build a deck from a CSV or Excel file, or to add cards to a deck you already have.
            </p>
        </div>
    </div>
</x-layouts.dashboard>
