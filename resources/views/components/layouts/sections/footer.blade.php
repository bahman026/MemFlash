@props([
    'variant' => 'default' // 'default', 'minimal', 'full'
])

<footer class="bg-white border-t border-gray-200 mt-auto">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
        @if($variant === 'minimal')
            <!-- Minimal Footer -->
            <div class="py-4">
                <div class="text-center text-sm text-gray-500">
                    <p>&copy; {{ date('Y') }} MemFlash. All rights reserved.</p>
                </div>
            </div>
        @elseif($variant === 'full')
            <!-- Full Footer -->
            <div class="py-8 sm:py-12">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <!-- Brand Section -->
                    <div class="col-span-1 md:col-span-2">
                        <div class="flex items-center space-x-2 mb-4">
                            <div class="w-8 h-8 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl flex items-center justify-center">
                                <span class="text-white text-lg">📚</span>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900">MemFlash</h3>
                        </div>
                        <p class="text-gray-600 text-sm leading-relaxed max-w-md">
                            Master vocabulary with spaced repetition. Create your own decks or study from our curated American English File curriculum.
                        </p>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 mb-4">Quick Links</h4>
                        <ul class="space-y-2">
                            <li>
                                <a href="{{ route('welcome') }}" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">
                                    Home
                                </a>
                            </li>
                            @auth
                                <li>
                                    <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">
                                        Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('decks.create') }}" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">
                                        Create Deck
                                    </a>
                                </li>
                            @endauth
                        </ul>
                    </div>

                    <!-- Support -->
                    <div>
                        <h4 class="text-sm font-semibold text-gray-900 mb-4">Support</h4>
                        <ul class="space-y-2">
                            <li>
                                <a href="#" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">
                                    Help Center
                                </a>
                            </li>
                            <li>
                                <a href="#" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">
                                    Contact Us
                                </a>
                            </li>
                            <li>
                                <a href="#" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">
                                    Privacy Policy
                                </a>
                            </li>
                            <li>
                                <a href="#" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">
                                    Terms of Service
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Bottom Section -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <div class="flex flex-col sm:flex-row justify-between items-center space-y-2 sm:space-y-0">
                        <p class="text-sm text-gray-500">
                            &copy; {{ date('Y') }} MemFlash. All rights reserved.
                        </p>
                        <div class="flex items-center space-x-4 text-sm text-gray-500">
                            <span>Made with ❤️ for language learners</span>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Default Footer -->
            <div class="py-6">
                <div class="flex flex-col sm:flex-row justify-between items-center space-y-4 sm:space-y-0">
                    <!-- Brand -->
                    <div class="flex items-center space-x-2">
                        <div class="w-6 h-6 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                            <span class="text-white text-sm">📚</span>
                        </div>
                        <span class="text-sm font-semibold text-gray-900">MemFlash</span>
                    </div>

                    <!-- Links -->
                    <div class="flex items-center space-x-6">
                        <a href="{{ route('welcome') }}" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">
                            Home
                        </a>
                        @auth
                            <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">
                                Dashboard
                            </a>
                        @endauth
                        <a href="#" class="text-sm text-gray-600 hover:text-blue-600 transition-colors">
                            Help
                        </a>
                    </div>

                    <!-- Copyright -->
                    <p class="text-sm text-gray-500">
                        &copy; {{ date('Y') }} MemFlash
                    </p>
                </div>
            </div>
        @endif
    </div>
</footer>
