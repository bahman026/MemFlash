@props(['type', 'id'])
{{-- Read by resources/js/app.js: opening this deck online downloads it and keeps
     its pages, so it opens and studies with no connection afterwards.
     $type is the sync type: "card" (personal deck) or "static_card" (lesson). --}}
<meta name="memflash-offline-deck" content="{{ $type }}:{{ $id }}">
<x-pwa.offline-assets />
