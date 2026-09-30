@props(['title', 'updated'])

{{--
    Shared frame for the privacy policy and terms. Styled here rather than with
    Tailwind utilities: public/build is only compiled when it is missing, so new
    utility classes would not reach a deployed server until assets were rebuilt.
--}}
<x-layouts.app
    :show-level="auth()->check()"
    :show-user="auth()->check()"
    :header-variant="'default'"
    :header-title="'MemFlash'"
    :header-subtitle="null"
>
    <x-slot name="seo">
        <title>{{ $title }} - MemFlash</title>
        <meta name="description" content="{{ $title }} for MemFlash, the English–Persian vocabulary flashcard app.">
    </x-slot>

    <article class="legal">
        <h1>{{ $title }}</h1>
        <p class="legal__updated">Last updated {{ $updated }}</p>

        {{ $slot }}
    </article>

    <style>
        .legal { max-width: 46rem; margin: 0 auto; padding: 16px 4px 48px; color: #374151; line-height: 1.7; }
        .legal h1 { font-size: 1.875rem; line-height: 1.2; font-weight: 700; color: #111827; margin: 0 0 4px; }
        .legal h2 { font-size: 1.2rem; font-weight: 600; color: #111827; margin: 32px 0 8px; }
        .legal p, .legal ul { margin: 0 0 12px; }
        .legal ul { padding-left: 1.25rem; list-style: disc; }
        .legal li { margin-bottom: 6px; }
        .legal a { color: #2563eb; text-decoration: underline; }
        .legal strong { color: #111827; font-weight: 600; }
        .legal__updated { font-size: 0.875rem; color: #6b7280; margin-bottom: 24px !important; }
    </style>
</x-layouts.app>
