<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" href="{{ asset('assets/images/favicon-300x300.png') }}" sizes="32x32">
    <link rel="icon" href="{{ asset('assets/images/favicon-150x150.png') }}" sizes="192x192">

    @if(config('app.gtm_enabled'))
        <!-- Google Tag Manager -->
        <script>
            (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
                j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
                'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
            })(window,document,'script','dataLayer','GTM-N7S8ZBZG');
        </script>
        <!-- End Google Tag Manager -->
    @endif

    {{-- seo related tags --}}
    {{ $seo }}

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body x-data="{ isMenuOpen: false }" :class="{'overflow-hidden': isMenuOpen}">
    @if(config('app.gtm_enabled'))
        <!-- Google Tag Manager (noscript) -->
        <noscript>
            <iframe src="https://www.googletagmanager.com/ns.html?id=GTM-N7S8ZBZG" height="0" width="0" style="display:none;visibility:hidden"></iframe>
        </noscript>
        <!-- End Google Tag Manager (noscript) -->
    @endif

    <main :class="{'blur duration-500': isMenuOpen, 'mt-mobile-header-height lg:mt-header-height': true}">{{ $slot }}
    </main>

</body>

</html>
