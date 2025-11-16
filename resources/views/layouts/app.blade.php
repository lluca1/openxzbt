<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'openxzbt') }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('logo.svg') }}">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="bg-[#050608] text-zinc-100 antialiased min-h-screen">
        <div class="min-h-screen flex flex-col">
            <x-navbar />

            <main class="flex-1 pt-28 pb-12">
                {{ $slot }}
            </main>

            {{-- FOOTER --}}
            <footer class="border-t border-zinc-800 py-6 text-[11px] text-white/60">
                <div class="max-w-6xl mx-auto px-6 flex flex-col items-center gap-4 text-center">

                    <p class="max-w-2xl text-white/50 leading-relaxed">
                        openxzbt is a minimal web console for creating and managing 3D art expositions.
                        artwork metadata and layout live here — the actual museum is generated and explored
                        inside the unity viewer.
                    </p>

                    <div class="flex flex-wrap justify-center gap-3">
                        <span class="px-3 py-1 border border-[#38bdf8]/70 bg-[#072635] rounded-none">
                            classic laravel
                        </span>

                        <span class="px-3 py-1 border border-[#facc15]/70 bg-[#26220b] rounded-none">
                            status: experimental
                        </span>

                        <span class="px-3 py-1 border border-[#f97373]/70 bg-[#5b1010] rounded-none">
                            build: {{ now()->format('Y-m-d') }}
                        </span>
                        
                        <span class="px-3 py-1 border border-[#22c55e]/70 bg-[#052713] rounded-none">
                            game in what_is_here
                        </span>
                    </div>
                </div>
            </footer>

        </div>

        @stack('modals')
        @stack('scripts')
        @livewireScripts
    </body>
</html>
