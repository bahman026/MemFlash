<x-layouts.dashboard>
    <x-slot name="seo">
        <title>Add Card - MemFlash</title>
        <meta name="description" content="Add a new card to your deck">
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-6 sm:mb-8">
            <div class="flex items-center mb-4">
                <a href="{{ route('decks.show', $deck) }}" class="text-gray-500 hover:text-gray-700 mr-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Add New Card</h1>
            </div>
            <p class="text-gray-600">Add a new vocabulary card to "{{ $deck->name }}".</p>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
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

        <!-- Add Card Form -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-100">
            <form method="POST" action="{{ route('cards.store', $deck) }}" class="p-4 sm:p-6">
                @csrf

                <!-- Front Text -->
                <div class="mb-4 sm:mb-6">
                    <label for="front" class="block text-sm font-semibold text-gray-700 mb-2">Front (Question/Term)</label>
                    <textarea id="front"
                              name="front"
                              rows="3"
                              required
                              class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors @error('front') border-red-300 @enderror"
                              placeholder="Enter the front text (e.g., English word, question, or term)">{{ old('front') }}</textarea>
                    @error('front')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Back Text -->
                <div class="mb-4 sm:mb-6">
                    <label for="back" class="block text-sm font-semibold text-gray-700 mb-2">Back (Answer/Definition)</label>
                    <textarea id="back"
                              name="back"
                              rows="3"
                              required
                              class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors @error('back') border-red-300 @enderror"
                              placeholder="Enter the back text (e.g., translation, definition, or answer)">{{ old('back') }}</textarea>
                    @error('back')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Deck Info -->
                <div class="mb-4 sm:mb-6 p-4 bg-gray-50 rounded-lg">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Deck Information</h3>
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div>
                            <p class="text-2xl font-bold text-primary-600">{{ $deck->cards()->count() }}</p>
                            <p class="text-xs text-gray-500">Current Cards</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-green-600">{{ $deck->getRemainingCardSlots() }}</p>
                            <p class="text-xs text-gray-500">Remaining Slots</p>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                    <a href="{{ route('decks.show', $deck) }}"
                       class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500 text-center">
                        Cancel
                    </a>
                    <button type="submit"
                            class="flex-1 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                        Add Card
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.dashboard>
