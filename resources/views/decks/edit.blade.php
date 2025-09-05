<x-layouts.dashboard>
    <x-slot name="seo">
        <title>Edit Deck - Memora</title>
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
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6 mb-4 sm:mb-6">
                    <div>
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
                    <div>
                        <label for="is_public" class="block text-sm font-semibold text-gray-700 mb-2">Visibility</label>
                        <select id="is_public" 
                                name="is_public" 
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors @error('is_public') border-red-300 @enderror">
                            <option value="0" {{ old('is_public', $deck->is_public) == 0 ? 'selected' : '' }}>Private</option>
                            <option value="1" {{ old('is_public', $deck->is_public) == 1 ? 'selected' : '' }}>Public</option>
                        </select>
                        @error('is_public')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Deck Stats -->
                <div class="mb-4 sm:mb-6 p-4 bg-gray-50 rounded-lg">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Deck Information</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
                        <div>
                            <p class="text-2xl font-bold text-primary-600">{{ $deck->cards()->count() }}</p>
                            <p class="text-xs text-gray-500">Total Cards</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-green-600">{{ $deck->new_cards_per_day }}</p>
                            <p class="text-xs text-gray-500">Cards/Day</p>
                        </div>
                        <div>
                            @php
                                $daysOld = $deck->created_at->diffInDays(now());
                                $hoursOld = $deck->created_at->diffInHours(now());
                                $minutesOld = $deck->created_at->diffInMinutes(now());
                                
                                if ($daysOld >= 1) {
                                    $age = $daysOld . ' day' . ($daysOld > 1 ? 's' : '');
                                } elseif ($hoursOld >= 1) {
                                    $age = $hoursOld . ' hour' . ($hoursOld > 1 ? 's' : '');
                                } else {
                                    $age = $minutesOld . ' minute' . ($minutesOld > 1 ? 's' : '');
                                }
                            @endphp
                            <p class="text-2xl font-bold text-blue-600">{{ $age }}</p>
                            <p class="text-xs text-gray-500">Age</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-purple-600">{{ $deck->is_public ? 'Public' : 'Private' }}</p>
                            <p class="text-xs text-gray-500">Visibility</p>
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
    </div>
</x-layouts.dashboard>
