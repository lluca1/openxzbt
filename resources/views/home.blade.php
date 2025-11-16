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
                <section class="space-y-4">

                    <h3 class="text-lg font-semibold tracking-tight text-white">
                        your expositions
                    </h3>

                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($yourExpositions as $expo)
                            <x-exposition.card
                                :exposition="$expo"
                                :index="$loop->iteration"
                                :description-limit="140"
                            >
                                <div class="flex gap-2">
                                    <a href="{{ route('expositions.show', $expo) }}"
                                       class="flex-1 border border-zinc-600 hover:border-zinc-300 px-3 py-1 text-center rounded-none">
                                        :: MANAGE EXHIBITS
                                    </a>
                                    <form method="POST" action="{{ route('expositions.destroy', $expo) }}" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="px-3 py-1 border border-[#f97373]/80 bg-[#5b1010] text-[#ffecec] rounded-none hover:bg-[#7f1717]"
                                        >
                                            :: DELETE
                                        </button>
                                    </form>
                                </div>
                            </x-exposition.card>
                        @endforeach
                    </div>

                </section>
            @endif
        @endauth


        {{-- PUBLIC EXPOSITIONS SECTION --}}
        <section class="space-y-4">

            <h3 class="text-lg font-semibold tracking-tight text-white">
                featured public expositions
            </h3>

            <p class="text-xs text-white/40">
                curated selection of latest public builds.
            </p>

            @if(isset($expositions) && $expositions->isNotEmpty())
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($expositions as $expo)
                        <x-exposition.card
                            :exposition="$expo"
                            :index="$loop->iteration"
                            :description-limit="140"
                        >
                            @guest
                                <a href="{{ route('login') }}"
                                   class="border border-white/30 text-white px-3 py-1 rounded-none">
                                    login_to_view →
                                </a>
                            @else
                                <a href="{{ route('expositions.show', $expo) }}"
                                   class="border border-[#f97373]/70 bg-[#5b1010] text-[#ffecec] px-3 py-1 rounded-none hover:bg-[#7f1717]/80 text-left">
                                    view_details →
                                </a>
                            @endguest
                        </x-exposition.card>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-white/40">
                    no public expositions yet.
                </p>
            @endif

        </section>

    </div>

</x-app-layout>
