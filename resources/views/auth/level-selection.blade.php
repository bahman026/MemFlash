<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" href="{{ asset('assets/images/favicon-300x300.png') }}" sizes="32x32">
    <link rel="icon" href="{{ asset('assets/images/favicon-150x150.png') }}" sizes="192x192">

    <title>Choose Your Level - MemFlash</title>
    <meta name="description" content="Select your learning level to get personalized content">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('js/tailwind.min.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
</head>

<body class="min-h-screen bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">
    <!-- Background Pattern -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-blue-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-purple-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-pulse"></div>
        <div class="absolute top-40 left-40 w-80 h-80 bg-indigo-200 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-pulse"></div>
    </div>

    <!-- Main Content -->
    <div class="relative min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl w-full space-y-8">
            <!-- Header -->
            <div class="text-center">
                <div class="mx-auto h-16 w-16 bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg mb-6">
                    <span class="text-3xl">🎯</span>
                </div>
                <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-2">
                    @if($isChangingLevel ?? false)
                        Change Your Learning Level
                    @else
                        Choose Your Learning Level
                    @endif
                </h1>
                <p class="text-lg text-gray-600 mb-8">
                    @if($isChangingLevel ?? false)
                        Update your level to get personalized content and recommendations
                    @else
                        Select your current level to get personalized content and recommendations
                    @endif
                </p>
            </div>

            <!-- Level Selection Form -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-2xl p-8 sm:p-10 border border-white/20">
                @if(session('error'))
                    <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('level.store') }}" class="space-y-6">
                    @csrf
                    
                    <!-- Level Options -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($levels as $level)
                            <label class="relative cursor-pointer group">
                                <input type="radio" 
                                       name="level" 
                                       value="{{ $level->value }}" 
                                       class="sr-only peer"
                                       {{ $level === ($currentLevel ?? \App\Enums\UserLevelEnum::STARTER) ? 'checked' : '' }}>
                                
                                <div class="p-6 rounded-xl border-2 border-gray-200 bg-white hover:border-blue-300 hover:shadow-lg transition-all duration-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 peer-checked:shadow-lg">
                                    <div class="text-center">
                                        <!-- Level Icon -->
                                        <div class="mx-auto w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center mb-4 group-hover:scale-110 transition-transform duration-200"
                                             style="background: linear-gradient(to right, #3b82f6, #8b5cf6) !important;">
                                            <span class="text-white font-bold text-lg" style="color: white !important;">{{ $level->getNumericValue() }}</span>
                                        </div>
                                        
                                        <!-- Level Name -->
                                        <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                            {{ $level->getDisplayName() }}
                                        </h3>
                                        
                                        <!-- Level Description -->
                                        <p class="text-sm text-gray-600">
                                            @switch($level)
                                                @case(\App\Enums\UserLevelEnum::STARTER)
                                                    Beginner level - Perfect for starting your English learning journey
                                                    @break
                                                @case(\App\Enums\UserLevelEnum::ELEMENTARY)
                                                    Elementary level - Basic vocabulary and simple phrases
                                                    @break
                                                @case(\App\Enums\UserLevelEnum::PRE_INTERMEDIATE)
                                                    Pre-intermediate level - Common expressions and everyday conversations
                                                    @break
                                                @case(\App\Enums\UserLevelEnum::INTERMEDIATE)
                                                    Intermediate level - Grammar and complex sentences
                                                    @break
                                                @case(\App\Enums\UserLevelEnum::UPPER_INTERMEDIATE)
                                                    Upper-intermediate level - Advanced vocabulary and professional terms
                                                    @break
                                                @case(\App\Enums\UserLevelEnum::ADVANCED)
                                                    Advanced level - Expert content and specialized topics
                                                    @break
                                            @endswitch
                                        </p>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-6">
                        <button type="submit" 
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-4 px-6 rounded-xl transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl"
                                style="background-color: #2563eb !important; color: white !important;">
                                <span class="flex items-center justify-center" style="color: white !important;">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: white !important;">
                                        @if($isChangingLevel ?? false)
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                        @endif
                                    </svg>
                                    <span style="color: white !important;">
                                        @if($isChangingLevel ?? false)
                                            Update Level
                                        @else
                                            Start Learning
                                        @endif
                                    </span>
                                </span>
                        </button>
                    </div>
                </form>

                <!-- Help Text -->
                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-500">
                        Don't worry! You can change your level anytime in your profile settings.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Elements -->
    <div class="fixed top-10 left-10 w-20 h-20 bg-blue-100 rounded-full opacity-20 animate-bounce"></div>
    <div class="fixed bottom-10 right-10 w-16 h-16 bg-purple-100 rounded-full opacity-20 animate-bounce" style="animation-delay: 1s;"></div>
    <div class="fixed top-1/2 right-20 w-12 h-12 bg-indigo-100 rounded-full opacity-20 animate-bounce" style="animation-delay: 2s;"></div>
</body>
</html>
