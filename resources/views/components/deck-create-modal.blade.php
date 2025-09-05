<!-- Deck Creation Modal -->
<div id="deck-create-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-4 sm:top-10 mx-auto p-4 sm:p-6 w-full max-w-md sm:max-w-lg">
        <div class="relative bg-white rounded-xl shadow-xl">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-4 sm:p-6 border-b border-gray-200">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900">Create New Deck</h3>
                <button onclick="closeDeckModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Body -->
            <form id="deck-create-form" method="POST" action="{{ route('decks.store') }}" enctype="multipart/form-data" class="p-4 sm:p-6">
                @csrf
                
                <!-- Deck Name -->
                <div class="mb-4 sm:mb-6">
                    <label for="deck_name" class="block text-sm font-semibold text-gray-700 mb-2">Deck Name</label>
                    <input type="text" 
                           id="deck_name" 
                           name="name" 
                           required
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors"
                           placeholder="Enter deck name">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- File Upload -->
                <div class="mb-4 sm:mb-6">
                    <label for="deck_file" class="block text-sm font-semibold text-gray-700 mb-2">Upload File</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-400 transition-colors">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="deck_file" class="relative cursor-pointer bg-white rounded-md font-medium text-primary-600 hover:text-primary-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary-500">
                                    <span>Upload a file</span>
                                    <input id="deck_file" name="file" type="file" class="sr-only" accept=".csv,.xlsx,.xls" required>
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">CSV or Excel files only</p>
                        </div>
                    </div>
                    @error('file')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- File Preview -->
                <div id="file-preview" class="hidden mb-4 sm:mb-6">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span id="file-name" class="text-sm font-medium text-gray-900"></span>
                        </div>
                        <p id="file-info" class="text-xs text-gray-500 mt-1"></p>
                    </div>
                </div>

                <!-- Deck Settings -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4 sm:mb-6">
                    <div>
                        <label for="new_cards_per_day" class="block text-sm font-semibold text-gray-700 mb-2">Cards per day</label>
                        <input type="number" 
                               id="new_cards_per_day" 
                               name="new_cards_per_day" 
                               value="10"
                               min="1"
                               max="100"
                               class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                        @error('new_cards_per_day')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="is_public" class="block text-sm font-semibold text-gray-700 mb-2">Visibility</label>
                        <select id="is_public" 
                                name="is_public" 
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors">
                            <option value="0">Private</option>
                            <option value="1">Public</option>
                        </select>
                        @error('is_public')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- File Format Info -->
                <div class="mb-4 sm:mb-6 p-3 bg-blue-50 rounded-lg">
                    <h4 class="text-sm font-semibold text-blue-900 mb-2">Supported File Formats:</h4>
                    <div class="text-xs text-blue-800 space-y-2">
                        <div>
                            <p class="font-medium mb-1">Option 1: Simple Two-Column Format</p>
                            <p>English, Persian</p>
                            <p>critical thinking, تفکر انتقادی</p>
                        </div>
                        <div>
                            <p class="font-medium mb-1">Option 2: Google Sheets Format</p>
                            <p>English, Persian, accomplishment, دستاورد</p>
                            <p>English, Persian, critical thinking, تفکر انتقادی</p>
                        </div>
                        <div class="mt-2 pt-2 border-t border-blue-200">
                            <p>• Supported formats: CSV, Excel (.xlsx, .xls)</p>
                            <p>• Headers are automatically detected and skipped</p>
                            <p>• Maximum file size: 10MB</p>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                    <button type="button" 
                            onclick="closeDeckModal()"
                            class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="flex-1 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-sm font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                        Create Deck
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openDeckModal() {
    document.getElementById('deck-create-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeDeckModal() {
    document.getElementById('deck-create-modal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    // Reset form
    document.getElementById('deck-create-form').reset();
    document.getElementById('file-preview').classList.add('hidden');
}

// File upload preview
document.getElementById('deck_file').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('file-preview');
    const fileName = document.getElementById('file-name');
    const fileInfo = document.getElementById('file-info');
    
    if (file) {
        const fileSize = (file.size / 1024).toFixed(1);
        const fileType = file.type;
        
        fileName.textContent = file.name;
        fileInfo.textContent = `${fileSize} KB • ${fileType}`;
        preview.classList.remove('hidden');
    } else {
        preview.classList.add('hidden');
    }
});

// Close modal when clicking outside
document.getElementById('deck-create-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeckModal();
    }
});

// Prevent form submission if no file is selected
document.getElementById('deck-create-form').addEventListener('submit', function(e) {
    const fileInput = document.getElementById('deck_file');
    if (!fileInput.files[0]) {
        e.preventDefault();
        alert('Please select a file to upload.');
    }
});
</script>
