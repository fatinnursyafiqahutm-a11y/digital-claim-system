<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Digital Claim System') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 p-4">
            <div class="mb-8 fade-in-down">
                <a href="/" class="block">
                    <div class="text-center">
                        <h1 class="text-4xl font-bold text-red-800 mb-2 tracking-tight drop-shadow-lg">DIGITAL CLAIM</h1>
                        <p class="text-red-800 drop-shadow-md text-sm font-medium">Management System</p>
                    </div>
                </a>
            </div>

            <div class="w-full sm:max-w-md glass-card p-8 fade-in">
                {{ $slot }}
            </div>

            <div class="mt-8 text-center fade-in">
                <p class="text-black/70 text-sm">
                    © 2025 Digital Claim System. All rights reserved.
                </p>
            </div>
        </div>
    </body>
</html>
