{{-- Read by resources/js/app.js: the study scripts the service worker keeps next
     to the study pages, versioned exactly as the pages load them. --}}
<meta name="memflash-offline-assets" content="{{ collect(['js/study-utils.js', 'js/study-session-unified.js'])->map(fn ($f) => asset($f) . '?v=' . filemtime(public_path($f)))->implode(' ') }}">
