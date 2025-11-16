<section class="max-w-6xl mx-auto px-6 pb-20 space-y-10">
    @php
        // split into "yours" vs "others" by user_id
        $yourExpositions = isset($expositions)
            ? $expositions->where('user_id', auth()->id())
            : collect();

        $otherExpositions = isset($expositions)
            ? $expositions->where('user_id', '!=', auth()->id())
            : collect();
    @endphp

    <form wire:submit.prevent="save">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 text-xs">
            <div class="lg:col-span-2 space-y-6">
                <div class="border border-zinc-700 bg-[#050608] rounded-none p-4 space-y-4">
                    <div class="space-y-2">
                        <label class="block text-[11px] text-zinc-400" for="title">exposition title</label>
                        <input
                            id="title"
                            type="text"
                            placeholder="e.g. neon vault, echo forest"
                            wire:model.live.debounce.300ms="title"
                            class="w-full bg-[#050608] border border-zinc-700 focus:border-zinc-300 outline-none px-3 py-2 rounded-none text-[12px] text-zinc-100 placeholder:text-zinc-500"
                        >
                        @error('title')
                            <p class="text-[10px] text-[#f97373]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[11px] text-zinc-400" for="thumbnail">thumbnail image</label>
                        <input
                            id="thumbnail"
                            type="file"
                            accept="image/*"
                            wire:model="thumbnail"
                            class="input-file-fancy"
                        >
                        <p class="text-[10px] text-zinc-500">4:3 aspect ratio. max 4 MB.</p>
                        @error('thumbnail')
                            <p class="text-[10px] text-[#f97373]">{{ $message }}</p>
                        @enderror
                        @if ($thumbnail)
                            <div class="mt-2 border border-zinc-700 rounded-none p-2">
                                <p class="text-[10px] text-zinc-500 mb-2">live preview</p>
                                <div class="w-full bg-zinc-900 border border-dashed border-zinc-700 rounded-none flex items-center justify-center overflow-hidden" style="aspect-ratio: 4 / 3;">
                                    <img
                                        src="{{ $thumbnail->temporaryUrl() }}"
                                        alt="thumbnail preview"
                                        class="w-full h-full object-cover"
                                    >
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[11px] text-zinc-400" for="description">short description</label>
                        <textarea
                            id="description"
                            rows="4"
                            placeholder="what kind of 3d space is this? mood, materials, pacing..."
                            wire:model.live.debounce.300ms="description"
                            class="w-full bg-[#050608] border border-zinc-700 focus:border-zinc-300 outline-none px-3 py-2 rounded-none text-[12px] text-zinc-100 placeholder:text-zinc-500 resize-none"
                        ></textarea>
                        @error('description')
                            <p class="text-[10px] text-[#f97373]">{{ $message }}</p>
                        @enderror
                    </div>

                    @if ($preset_theme === -1)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block text-[11px] text-zinc-400" for="floor_texture">floor texture</label>
                                <input
                                    id="floor_texture"
                                    type="file"
                                    accept="image/png,image/jpeg,image/jpg,image/webp,image/avif,image/bmp"
                                    wire:model="floor_texture"
                                    class="input-file-fancy"
                                >
                                <p class="text-[10px] text-zinc-500">stored under /storage/textures/[expo id]/floor-*. recommended 2k square.</p>
                                @error('floor_texture')
                                    <p class="text-[10px] text-[#f97373]">{{ $message }}</p>
                                @enderror
                                <p class="text-[10px] text-zinc-500">status: <span class="text-zinc-300">{{ $floor_texture ? 'ready to upload' : 'none yet' }}</span></p>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-[11px] text-zinc-400" for="ceiling_texture">ceiling texture</label>
                                <input
                                    id="ceiling_texture"
                                    type="file"
                                    accept="image/png,image/jpeg,image/jpg,image/webp,image/avif,image/bmp"
                                    wire:model="ceiling_texture"
                                    class="input-file-fancy"
                                >
                                <p class="text-[10px] text-zinc-500">stored under /storage/textures/[expo id]/ceiling-*.</p>
                                @error('ceiling_texture')
                                    <p class="text-[10px] text-[#f97373]">{{ $message }}</p>
                                @enderror
                                <p class="text-[10px] text-zinc-500">status: <span class="text-zinc-300">{{ $ceiling_texture ? 'ready to upload' : 'none yet' }}</span></p>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-[11px] text-zinc-400" for="wall_texture">wall texture</label>
                                <input
                                    id="wall_texture"
                                    type="file"
                                    accept="image/png,image/jpeg,image/jpg,image/webp,image/avif,image/bmp"
                                    wire:model="wall_texture"
                                    class="input-file-fancy"
                                >
                                <p class="text-[10px] text-zinc-500">stored under /storage/textures/[expo id]/wall-*.</p>
                                @error('wall_texture')
                                    <p class="text-[10px] text-[#f97373]">{{ $message }}</p>
                                @enderror
                                <p class="text-[10px] text-zinc-500">status: <span class="text-zinc-300">{{ $wall_texture ? 'ready to upload' : 'none yet' }}</span></p>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-[11px] text-zinc-400" for="ambient_track">ambient audio loop</label>
                                <input
                                    id="ambient_track"
                                    type="file"
                                    accept="audio/*,.mp3,.wav,.ogg,.flac,.m4a"
                                    wire:model="ambient_track"
                                    class="input-file-fancy"
                                >
                                <p class="text-[10px] text-zinc-500">saved to /storage/audio/[expo id]/ambient-*.</p>
                                @error('ambient_track')
                                    <p class="text-[10px] text-[#f97373]">{{ $message }}</p>
                                @enderror
                                <p class="text-[10px] text-zinc-500">status: <span class="text-zinc-300">{{ $ambient_track ? 'ready to upload' : 'none yet' }}</span></p>
                            </div>
                        </div>
                    @else
                        <div class="border border-dashed border-zinc-700 rounded-none p-3 text-[11px] text-zinc-500">
                            environment uploads unlock when you select the custom preset theme.
                        </div>
                    @endif
                </div>

                <div class="border border-zinc-700 bg-[#050608] rounded-none p-4 space-y-4">
                    <div class="space-y-2">
                        <label class="block text-[11px] text-zinc-400">visibility</label>
                        <div class="flex flex-wrap gap-2 text-[11px]">
                            <button type="button"
                                    wire:click="$set('is_public', true)"
                                    class="px-3 py-1 border rounded-none {{ $is_public ? 'border-[#22c55e]/60 bg-[#052713] text-[#bbf7d0]' : 'border-white/20 text-white/50 hover:text-white' }}">
                                public (featured)
                            </button>
                            <button type="button"
                                    wire:click="$set('is_public', false)"
                                    class="px-3 py-1 border rounded-none {{ ! $is_public ? 'border-[#f97373]/80 bg-[#5b1010] text-[#ffecec]' : 'border-white/20 text-white/50 hover:text-white' }}">
                                private (only you)
                            </button>
                        </div>
                        <p class="text-[10px] text-zinc-500">
                            private expositions stay hidden from recommendations until you toggle them public.
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[11px] text-zinc-400">preset theme</label>
                        <div class="flex flex-wrap gap-2 text-[11px]">
                            <button type="button" wire:click="$set('preset_theme', -1)" class="px-3 py-1 border rounded-none {{ $preset_theme === -1 ? 'border-zinc-400 bg-zinc-800/50 text-zinc-200' : 'border-white/20 text-white/50 hover:text-white' }}">
                                custom
                            </button>
                            <button type="button"
                                    wire:click="$set('preset_theme', 0)"
                                    class="px-3 py-1 border rounded-none {{ $preset_theme === 0 ? 'border-zinc-300 bg-zinc-800/50 text-zinc-200' : 'border-white/20 text-white/50 hover:text-white' }}">
                                classic
                            </button>
                            <button type="button"
                                    wire:click="$set('preset_theme', 1)"
                                    class="px-3 py-1 border rounded-none {{ $preset_theme === 1 ? 'border-zinc-300 bg-zinc-800/50 text-zinc-200' : 'border-white/20 text-white/50 hover:text-white' }}">
                                medieval
                            </button>
                            <button type="button"
                                    wire:click="$set('preset_theme', 2)"
                                    class="px-3 py-1 border rounded-none {{ $preset_theme === 2 ? 'border-zinc-300 bg-zinc-800/50 text-zinc-200' : 'border-white/20 text-white/50 hover:text-white' }}">
                                scifi
                            </button>
                        </div>
                        @error('preset_theme')
                            <p class="text-[10px] text-[#f97373]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-2">
                        <label class="block text-[11px] text-zinc-400">exposition actions</label>
                        <div class="flex flex-col md:flex-row gap-2">
                            <button type="submit"
                                    class="px-4 py-2 border border-[#f97373]/80 bg-[#5b1010] text-[#ffecec] rounded-none hover:bg-[#7f1717]">
                                :: SAVE EXPOSITION
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <aside class="space-y-4">
                <div class="border border-zinc-700 bg-[#050608] rounded-none p-4 space-y-3">
                    <h2 class="text-[12px] font-semibold tracking-tight text-zinc-100">exposition summary</h2>
                    <p class="text-[11px] text-zinc-400">
                        snapshot of what you are about to save. private entries stay visible only to you.
                    </p>
                    <div class="mt-2 border border-zinc-700 rounded-none p-3 text-[10px] text-zinc-400 space-y-1">
                        <p>title: <span class="text-zinc-300">{{ $title !== '' ? $title : 'pending…' }}</span></p>
                        <p>status: <span class="text-zinc-300">{{ $is_public ? 'public' : 'private' }}</span></p>
                        <p>description: <span class="text-zinc-300">{{ $description !== '' ? \Illuminate\Support\Str::limit($description, 60) : 'add a short description' }}</span></p>
                        @php($themeLabels = [-1=>'custom',0=>'classic',1=>'medieval',2=>'scifi'])
                        <p>preset theme: <span class="text-zinc-300">{{ $themeLabels[$preset_theme] ?? 'default' }} ({{ $preset_theme }})</span></p>
                        <p>thumbnail: <span class="text-zinc-300">{{ $thumbnail ? 'ready to upload' : 'none yet' }}</span></p>
                        @if ($preset_theme === -1)
                            <p>floor texture: <span class="text-zinc-300">{{ $floor_texture ? 'ready to upload' : 'none yet' }}</span></p>
                            <p>ceiling texture: <span class="text-zinc-300">{{ $ceiling_texture ? 'ready to upload' : 'none yet' }}</span></p>
                            <p>wall texture: <span class="text-zinc-300">{{ $wall_texture ? 'ready to upload' : 'none yet' }}</span></p>
                            <p>ambient audio: <span class="text-zinc-300">{{ $ambient_track ? 'ready to upload' : 'none yet' }}</span></p>
                        @else
                            <p>environment media: <span class="text-zinc-300">locked to preset theme</span></p>
                        @endif
                    </div>
                </div>

                <div class="border border-zinc-700 bg-[#050608] rounded-none p-4 space-y-3">
                    <h2 class="text-[12px] font-semibold tracking-tight text-zinc-100">notes</h2>
                    <p class="text-[11px] text-zinc-400">
                        the 3d client points here using your exposition id. deleting an exposition removes its 3d references.
                    </p>
                </div>
            </aside>
        </div>
    </form>

    {{-- YOUR ACTIVE EXPOSITIONS --}}
    @if ($yourExpositions->isNotEmpty())
        <div class="mt-10">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold tracking-tight text-zinc-100">your active expositions</h2>
                <span class="text-[11px] text-zinc-500">{{ $yourExpositions->count() }} total</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 text-xs">
                @foreach ($yourExpositions as $exposition)
                    <x-exposition.card
                        :exposition="$exposition"
                        :index="$loop->iteration"
                        wire:key="your-exposition-{{ $exposition->id }}"
                    />
                @endforeach
            </div>
        </div>
    @endif

    {{-- OTHER USERS' EXPOSITIONS (VIEW-ONLY) --}}
    @if ($otherExpositions->isNotEmpty())
        <div class="mt-10">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold tracking-tight text-zinc-100">other expositions</h2>
                <span class="text-[11px] text-zinc-500">{{ $otherExpositions->count() }} total</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 text-xs">
                @foreach ($otherExpositions as $exposition)
                    <x-exposition.card
                        :exposition="$exposition"
                        :index="$loop->iteration"
                        wire:key="other-exposition-{{ $exposition->id }}"
                    >
                        <a href="{{ route('expositions.show', $exposition) }}"
                           class="border border-[#f97373]/70 bg-[#5b1010] text-[#ffecec] px-3 py-1 rounded-none hover:bg-[#7f1717]/80 text-left">
                            view_details →
                        </a>
                    </x-exposition.card>
                @endforeach
            </div>
        </div>
    @endif

    @if ($yourExpositions->isEmpty() && $otherExpositions->isEmpty())
        <div class="border border-dashed border-zinc-700 rounded-none p-6 text-center text-[12px] text-zinc-400 mt-8">
            no expositions yet. start by creating one above.
        </div>
    @endif
</section>
