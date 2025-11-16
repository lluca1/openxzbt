<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex flex-col">
                <h2 class="text-xl font-semibold tracking-tight text-white">[?] what is here</h2>
                <p class="text-xs text-white/40 mt-1">
                    the practical backroom for openxzbt — builds, notices, and a concise tutorial.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto px-6 py-10 space-y-10">
        {{-- INTRO BLOCK --}}
        <div class="border border-zinc-700 bg-[#050608] rounded-none px-6 py-4">
            <p class="text-[11px] text-zinc-400 leading-relaxed">
                this section keeps everything you need to get the client running: the
                <span class="text-zinc-200">latest world-loader build</span> and a
                <span class="text-zinc-200">brief run-through</span> before you start curating.
                nothing theatrical here—just the files and the context to use them.
            </p>
        </div>

        <div class="grid md:grid-cols-2 gap-8">
            {{-- DOWNLOAD CARD --}}
            <div class="border border-zinc-700 bg-[#050608] rounded-none p-6 flex flex-col justify-between">
                <div class="space-y-3">
                    <h3 class="text-sm font-semibold text-white tracking-tight">
                        download the world-loader
                    </h3>

                    <p class="text-xs text-zinc-400 leading-relaxed">
                        grab the <span class="text-zinc-200">openxzbt desktop build</span>.
                        unzip, run the executable, and connect to your exposition space without extra setup.
                    </p>

                    <div class="mt-2 text-[11px] text-zinc-500 font-mono">
                                status: <span class="text-emerald-400">probably safe to run*</span>
                    </div>
                </div>

                <div class="mt-6">
                    <a
                        href="{{ asset('storage/what_is_here/openxzbt_client.zip') }}"
                        download
                        class="inline-flex items-center gap-2 px-4 py-2 text-xs font-medium uppercase tracking-wide
                               border border-[#facc15]/60 bg-[#facc15]/10 text-[#facc15]
                               hover:bg-[#facc15]/20 transition-colors"
                    >
                        <span class="h-2 w-2 rounded-full bg-[#facc15]"></span>
                        download world-loader (.zip)
                    </a>

                    <p class="mt-3 text-[11px] text-zinc-500">
                        * signed for internal testing; expect the usual unsigned-app prompts.
                    </p>
                </div>
            </div>

            {{-- VIDEO CARD --}}
            <div class="border border-zinc-700 bg-[#050608] rounded-none p-6 flex flex-col">
                <div class="space-y-3 mb-4">
                    <h3 class="text-sm font-semibold text-white tracking-tight">
                        watch someone else push the buttons
                    </h3>

                    <p class="text-xs text-zinc-400 leading-relaxed">
                        a concise walkthrough of <span class="text-zinc-200">setting up openxzbt</span>:
                        creating expositions, placing works, and navigating the scene without guesswork.
                        useful when you want confirmation before going live.
                    </p>

                    <div class="text-[11px] text-zinc-500 font-mono">
                        format: <span class="text-zinc-300">video</span> ·
                        duration: <span class="text-zinc-300">short enough</span>
                    </div>
                </div>

                <div class="aspect-video w-full bg-black/60">
                    <video controls class="w-full h-full">
                        <source src="{{ asset('storage/what_is_here/openxzbt_tutorial.mp4') }}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>

                <p class="mt-3 text-[11px] text-zinc-500">
                    tip: go fullscreen for the clearest view of the interface.
                </p>
            </div>
        </div>

        {{-- FOOTNOTE --}}
        <div class="text-[10px] text-zinc-500 text-center font-mono pt-2">
            if anything misbehaves, log it as an experimental constraint and let the team know.
        </div>
    </div>
</x-app-layout>
