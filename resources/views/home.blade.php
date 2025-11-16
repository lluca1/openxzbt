{{-- resources/views/home.blade.php --}}
<x-app-layout>

    {{-- PAGE HEADER --}}
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex flex-col">
                <h2 class="text-xl font-semibold tracking-tight text-white">
                    openxzbt :: home
                </h2>
                <p class="text-xs text-white/40 mt-1">
                    browse public expositions or continue working on your own.
                </p>
            </div>

            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ route('expositions.index') }}"
                       class="px-4 py-1 border border-[#facc15]/60 bg-[#26220b] text-[#fef3c7] text-xs tracking-tight">
                        [+] CREATE_EXPOSITION
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="px-4 py-1 border border-white/30 bg-[#141414] text-white text-xs hover:bg-[#1e1e1e]">
                        LOGIN TO CREATE
                    </a>
                @endauth

                {{-- WHAT IS HERE SHORTCUT --}}
                <a href="{{ route('what.is.here') }}"
                   class="px-4 py-1 border border-[#22c55e]/70 bg-[#052713] text-[#bbf7d0] text-xs tracking-tight">
                    [?] WHAT_IS_HERE
                </a>
            </div>
        </div>
    </x-slot>


    {{-- MAIN PAGE WRAPPER --}}
    <div class="max-w-7xl mx-auto px-6 py-10 space-y-16">

        {{-- HERO / TOP PANEL --}}
        <section class="border border-white/10 bg-[#0a0a0a] p-6 md:p-8">
            <h1 class="text-3xl md:text-4xl font-semibold tracking-tight text-white mb-2">
                welcome to <span class="text-red-400">openxzbt</span>
            </h1>

            @guest
                <p class="text-sm text-white/60 max-w-xl">
                    you are exploring as <span class="text-red-400 font-semibold">guest</span>.
                    thumbnails & short previews only. log in to create & manage expositions.
                </p>
            @else
                <p class="text-sm text-white/60 max-w-xl">
                    logged in as <span class="text-red-400">{{ auth()->user()->name }}</span>.
                    create, edit and preview your 3d museum spaces.
                </p>
            @endguest

            @php
                $tips = [
                    'check your scale.',
                    'normals are wrong. they always are.',
                    'lighting fixes more than modeling does.',
                    'don’t trust viewport shading.',
                    'your scene has too many lights.',
                    'reduce your textures. you won’t notice.',
                    'test it in low light before calling it done.',
                    'glass is never as simple as you think.',
                    'your camera is probably in the wrong place.',
                    'bake it. then bake it again.',
                    'overdetailing is not detail.',
                    'your shadows are too soft.',
                    'stop smoothing everything.',
                    'check the silhouette, not the surface.',
                    'remove half your polygons. it will look the same.',
                    'reflections lie.',
                    'check the backfaces. someone will see them.',
                    'texture seams always find an audience.',
                    'realism starts with roughness.',
                    'your color grading is doing the heavy lifting.',
                ];

                $tip = $tips[array_rand($tips)];
            @endphp

            <div class="mt-4 inline-flex items-center gap-2 px-3 py-1 border border-white/20 bg-[#111] text-[10px] text-white/50">
                tip: {{ $tip }}
            </div>
        </section>
        {{-- SCAN FOR EXPOS@ (LIVEWIRE SEARCH) --}}
        <livewire:expo-scan />

        {{-- YOUR EXHIBITIONS (LOGGED IN ONLY) --}}
        @auth
            @php
                $yourExpositions = isset($expositions)
                    ? $expositions->where('user_id', auth()->id())
                    : collect();
            @endphp

            @if($yourExpositions->isNotEmpty())
                <details class="border border-white/15 bg-[#090909] group" aria-label="Toggle your expositions">
                    <summary class="flex items-center justify-between px-4 py-3 cursor-pointer list-none">
                        <span class="text-lg font-semibold tracking-tight text-white">
                            your expositions
                        </span>
                        <span class="text-xs uppercase tracking-wide text-white/50">
                            <span class="group-open:hidden">click to expand</span>
                            <span class="hidden group-open:inline">click to collapse</span>
                        </span>
                    </summary>

                    <div class="px-4 pb-4 space-y-4">
                        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($yourExpositions as $expo)
                                <x-exposition.card
                                    :exposition="$expo"
                                    :index="$loop->iteration"
                                    :description-limit="140"
                                    action-variant="manage"
                                    delete-mode="form"
                                />
                            @endforeach
                        </div>
                    </div>
                </details>
            @endif
        @endauth


        {{-- PUBLIC EXPOSITIONS SECTION --}}
        <details class="border border-white/15 bg-[#090909] group" aria-label="Toggle featured public expositions">
            <summary class="flex items-center justify-between px-4 py-3 cursor-pointer list-none">
                <span class="text-lg font-semibold tracking-tight text-white">
                    featured public expositions
                </span>
                <span class="text-xs uppercase tracking-wide text-white/50">
                    <span class="group-open:hidden">click to expand</span>
                    <span class="hidden group-open:inline">click to collapse</span>
                </span>
            </summary>

            <div class="px-4 pb-4 space-y-4">
                @if(isset($expositions) && $expositions->isNotEmpty())
                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($expositions as $expo)
                            <x-exposition.card
                                :exposition="$expo"
                                :index="$loop->iteration"
                                :description-limit="140"
                            />
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-white/40">
                        no public expositions yet.
                    </p>
                @endif
            </div>
        </details>

    </div>

</x-app-layout>
