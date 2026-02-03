<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <!-- Apply a persistent background image while keeping the form centered -->
    <body class="font-sans text-gray-900 antialiased" style="background-image: url('{{ asset('images/bg.png') }}'); background-size: cover; background-position: center; background-repeat: no-repeat;">
        <div class="min-h-screen flex flex-col items-center px-3 pt-3 pb-4 gap-4">
            <div class="w-full max-w-7xl flex flex-col items-center text-white text-center drop-shadow-lg gap-1">
                <div class="flex items-center justify-center gap-4 mb-2">
                    <img src="{{ asset('images/ph-logo.png') }}" alt="Philippines" class="h-16 w-auto object-contain drop-shadow-md sm:h-11 lg:h-16">
                    <img src="{{ asset('images/Bfar logo.png') }}" alt="BFAR" class="h-16 w-auto object-contain drop-shadow-md sm:h-11 lg:h-16">
                    <img src="{{ asset('images/gad-logo.jpg') }}" alt="GAD" class="h-16 w-auto object-contain drop-shadow-md sm:h-11 lg:h-16">
                </div>
                <p class="text-base sm:text-lg font-semibold tracking-[0.35em] text-emerald-100 [text-shadow:0_4px_12px_rgba(0,0,0,0.7)]">DEPARTMENT OF AGRICULTURE</p>
                <h1 class="text-2xl sm:text-3xl md:text-4xl font-bold tracking-[0.15em] [text-shadow:0_4px_12px_rgba(0,0,0,0.55)]">BUREAU OF FISHERIES AND AQUATIC RESOURCES</h1>
                <p class="text-base sm:text-lg text-emerald-100/90 [text-shadow:0_4px_12px_rgba(0,0,0,0.7)]">REGION XII</p>
            </div>

            <div class="w-full max-w-5xl justify-center">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
