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
            <header class="fixed top-0 left-0 w-full bg-black/95 border-b border-white/15 z-50">
                <div class="max-w-7xl mx-auto px-6 py-3 flex items-center justify-between relative">

                    {{-- LEFT: BRAND + CONTEXT --}}
                    <div class="flex items-center gap-3 flex-none">
                        <span class="text-lg text-white tracking-tight font-semibold">openxzbt</span>

                        <span class="text-[11px] text-white/40">
                            :: 
                            @if(request()->routeIs('login'))
                                auth_login
                            @elseif(request()->routeIs('register'))
                                auth_register
                            @else
                                auth
                            @endif
                        </span>
                    </div>

                    @php
                        // Button style helpers
                        $nav = function ($route) {
                            return request()->routeIs($route)
                                ? 'border-[#facc15]/90 bg-[#26220b] text-[#fef3c7]'
                                : 'border-white/30 text-white/60 hover:text-white';
                        };

                        $navRed = function ($route) {
                            return request()->routeIs($route)
                                ? 'border-[#f87171]/80 bg-[#3b0d0d] text-[#fecaca]'
                                : 'border-white/30 text-white/60 hover:text-white';
                        };

                        // Default avatar used when user has no custom avatar or is guest
                        $defaultAvatarFile = 'you-avatar-cap_on-cap_color_default-body_color_default.png';

                        // Avatar resolution logic
                        $avatarFile = $defaultAvatarFile;
                        if (auth()->check() && auth()->user()->avatar) {
                            $avatarFile = auth()->user()->avatar;
                        }
                    @endphp

                    {{-- CENTER: HOME + SIGN UP + WHAT IS HERE --}}
                    <nav class="hidden md:flex items-center gap-2 text-xs absolute left-1/2 -translate-x-1/2 top-1/2 -translate-y-1/2">

                        {{-- HOME --}}
                        <a href="{{ route('home') }}"
                           class="px-3 py-1 border tracking-tight rounded-none transition {{ $navRed('home') }}">
                            [*] HOME
                        </a>

                        {{-- SIGN UP (YELLOW ACTIVE) --}}
                        <a href="{{ route('register') }}"
                           class="px-3 py-1 border tracking-tight rounded-none transition
                                  {{ request()->routeIs('register')
                                        ? 'border-[#facc15]/90 bg-[#26220b] text-[#fef3c7]'
                                        : 'border-white/30 text-white/60 hover:text-white' }}">
                            [+] SIGN_UP
                        </a>

                        {{-- WHAT IS HERE --}}
                        <a href="{{ route('what.is.here') }}"
                           class="px-3 py-1 border tracking-tight rounded-none transition
                                  {{ request()->routeIs('what.is.here')
                                        ? 'border-[#22c55e]/70 bg-[#052713] text-[#bbf7d0]'
                                        : 'border-white/30 text-white/60 hover:text-white' }}">
                            [?] WHAT_IS_HERE
                        </a>

                    </nav>

                    {{-- RIGHT: AVATAR --}}
                    <div class="flex items-center gap-3 text-xs flex-none">
                        <div class="h-12 w-12 bg-[#111] flex items-center justify-center overflow-hidden rounded-none">
                            <img
                                src="{{ asset('assets/img/' . $avatarFile) }}"
                                alt="avatar"
                                class="w-full h-full object-contain opacity-90"
                                style="object-position: center 20%;"
                            >
                        </div>
                    </div>

                </div>
            </header>

            {{-- MAIN: tall enough so footer is below fold --}}
            <main class="flex-1 pt-28 pb-12 min-h-screen">
                {{ $slot }}
            </main>

            {{-- FOOTER --}}
            <footer class="border-t border-zinc-800 py-6 text-[11px] text-white/60">
                <div class="max-w-6xl mx-auto px-6 flex flex-col items-center gap-4 text-center">

                    {{-- SHORT DESCRIPTION --}}
                    <p class="max-w-2xl text-white/50 leading-relaxed">
                        openxzbt is a minimal web console for creating and managing 3D art expositions.
                        artwork metadata and layout live here — the actual museum is generated and explored
                        inside the unity viewer.
                    </p>

                    {{-- COLORED STATUS TAGS --}}
                    <div class="flex flex-wrap justify-center gap-3">
                        <span class="px-3 py-1 border border-[#38bdf8]/70 bg-[#072635] rounded-none">
                            endpoint: /expositions
                        </span>

                        <span class="px-3 py-1 border border-[#22c55e]/70 bg-[#052713] rounded-none">
                            build: {{ now()->format('Y-m-d') }}
                        </span>

                        <span class="px-3 py-1 border border-[#facc15]/70 bg-[#26220b] rounded-none">
                            status: experimental
                        </span>

                        <span class="px-3 py-1 border border-[#f97373]/70 bg-[#5b1010] rounded-none">
                            unity_client_required
                        </span>
                    </div>

                    <p class="text-white/30 text-[10px]">
                        openxzbt alpha — hackathon prototype
                    </p>
                </div>
            </footer>

        </div>

        @stack('modals')
        @stack('scripts')
        @livewireScripts
    </body>
</html>
