{{--
    Makes MemFlash installable as a home-screen app.

    The manifest covers Android and desktop browsers. iOS still reads the Apple
    tags instead: Safari takes the home-screen icon from apple-touch-icon (and
    ignores SVG there, so it has to be a PNG), and apple-mobile-web-app-capable is
    what opens the saved app full screen rather than as a Safari tab.
--}}
{{-- Favicons as PNG and ICO. iOS cannot use an SVG favicon, and favicon.ico used
     to be the SVG logo renamed, so bookmarks and the share sheet showed no icon. --}}
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
<link rel="icon" href="{{ asset('icons/favicon-32.png') }}" type="image/png" sizes="32x32">
<link rel="manifest" href="{{ asset('manifest.json') }}">
<meta name="theme-color" content="#2563eb">
<meta name="application-name" content="MemFlash">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icons/apple-touch-icon.png') }}">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="MemFlash">
