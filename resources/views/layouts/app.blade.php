<!DOCTYPE html>
<html lang="id" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#195905">
    <meta name="description" content="Perkoci Eatery — Artisanal Coffee &amp; Kitchen. Pesan langsung dari meja kamu.">

    <title>@yield('title', 'Perkoci Eatery')</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
<body class="h-full min-h-screen bg-cream font-sans text-cocoa antialiased">
    @yield('body')

    {{-- Wadah notifikasi (dipakai window.perkociToast / data-toast-message) --}}
    <div data-toast-stack
        class="pointer-events-none fixed inset-x-0 bottom-0 z-[60] flex flex-col items-center gap-2 p-4 pb-[calc(1rem+env(safe-area-inset-bottom))] sm:items-end sm:p-6">
    </div>

    @stack('scripts')
</body>
</html>
