<x-layouts.dashboard>
    <x-slot name="seo">
        <title>Edit Deck - MemFlash</title>
        <meta name="description" content="Edit your flashcard deck">
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
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Edit Deck</h1>
            </div>
            <p class="text-gray-600">Update your deck settings and information.</p>
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

        <!-- Edit Form -->
        <div class="bg-white shadow-sm rounded-xl border border-gray-100">
            <form method="POST" action="{{ route('decks.update', $deck) }}" class="p-4 sm:p-6">
                @csrf
                @method('PUT')

                <!-- Deck Name -->
                <div class="mb-4 sm:mb-6">
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Deck Name</label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="{{ old('name', $deck->name) }}"
                           required
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors @error('name') border-red-300 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Deck Settings -->
                <div class="mb-4 sm:mb-6">
                    <label for="new_cards_per_day" class="block text-sm font-semibold text-gray-700 mb-2">Cards per day</label>
                    <input type="number"
                           id="new_cards_per_day"
                           name="new_cards_per_day"
                           value="{{ old('new_cards_per_day', $deck->new_cards_per_day) }}"
                           min="1"
                           max="100"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors @error('new_cards_per_day') border-red-300 @enderror">
                    @error('new_cards_per_day')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Deck Stats -->
                <div class="mb-4 sm:mb-6 p-4 bg-gray-50 rounded-lg">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Deck Information</h3>
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-2xl font-bold text-primary-600">{{ $deck->cards()->count() }}</p>
                            <p class="text-xs text-gray-500">Total Cards</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-green-600">{{ $deck->new_cards_per_day }}</p>
                            <p class="text-xs text-gray-500">Cards/Day</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-blue-600">{{ $deck->created_at->diffForHumans() }}</p>
                            <p class="text-xs text-gray-500">Age</p>
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
                        Update Deck
                    </button>
                </div>
            </form>
        </div>

        <!-- Card Management Actions -->
        <div class="mt-6 bg-white shadow-sm rounded-xl border border-gray-100">
            <div class="p-4 sm:p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Card Management</h3>
                <div class="flex flex-col sm:flex-row gap-3 mb-6">
                    <a href="{{ route('cards.create', $deck) }}" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 text-center flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add New Card
                    </a>
                    <a href="{{ route('decks.show', $deck) }}" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 text-center flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Manage All Cards
                    </a>
                </div>

                <!-- Quick Card List -->
                @if($deck->cards->count() > 0)
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 mb-3">Recent Cards ({{ $deck->cards->count() }} total)</h4>
                        <div class="bg-gray-50 border border-gray-200 rounded-lg max-h-64 overflow-y-auto">
                            @foreach($deck->cards->take(5) as $card)
                                <div class="px-4 py-3 border-b border-gray-100 last:border-b-0 hover:bg-gray-100">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1 min-w-0">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                                <div>
                                                    <p class="text-xs font-medium text-gray-500">Front</p>
                                                    <p class="text-sm text-gray-900 truncate">{{ $card->front }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-medium text-gray-500">Back</p>
                                                    <p class="text-sm text-gray-900 truncate">{{ $card->back }}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="ml-4 flex items-center space-x-2">
                                            <a href="{{ route('cards.edit', $card) }}" class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('cards.destroy', $card) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this card?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            @if($deck->cards->count() > 5)
                                <div class="px-4 py-3 text-center">
                                    <a href="{{ route('decks.show', $deck) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        View all {{ $deck->cards->count() }} cards →
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No cards yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Get started by adding your first card.</p>
                        <div class="mt-4">
                            <a href="{{ route('cards.create', $deck) }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add First Card
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.dashboard>
