@props([
    'user' => null,
    'class' => 'mb-4 sm:mb-6 lg:mb-8'
])

<div class="{{ $class }}">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 mb-2 leading-tight">
                Welcome back, {{ $user->name }}! 👋
            </h1>
            <p class="text-gray-600 text-sm sm:text-base leading-relaxed">Ready to continue your learning journey?</p>
        </div>
    </div>
</div>
