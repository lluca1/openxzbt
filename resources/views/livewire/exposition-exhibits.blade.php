<section class="max-w-6xl mx-auto px-6 pb-20 space-y-10">
    {{-- HEADER / SUMMARY --}}
    <div class="border border-zinc-800 bg-[#050608] rounded-none p-6 flex flex-col gap-3">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <p class="text-[11px] text-zinc-500">
                    {{ $exposition->is_public ? 'public exposition' : 'private' }}
                    ·
                    {{ $exposition->exhibits->count() }} exhibits
                </p>
                <h1 class="text-3xl font-semibold tracking-tight">{{ $exposition->title }}</h1>
                <p class="text-[12px] text-zinc-400 mt-1">
                    {{ $exposition->description ?: 'no description — add context to set the mood.' }}
                </p>
            </div>

            <a href="{{ route('expositions.index') }}"
               class="self-start md:self-auto px-3 py-1 border border-[#38bdf8]/80 bg-[#072635] text-[#bae6fd] text-xs tracking-tight rounded-none">
                &larr; back to all expositions
            </a>
        </div>
    </div>

    {{-- ENGAGEMENT: LIKES + COMMENTS --}}
    <div class="border border-zinc-800 bg-[#050608] rounded-none p-5 space-y-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex items-center gap-3 text-[11px] text-zinc-400">
                <button
                    type="button"
                    wire:click="toggleLike"
                    @disabled(! $canInteract)
                    class="flex items-center gap-2 px-3 py-1.5 border rounded-none text-xs tracking-tight transition {{ $likedByUser ? 'border-[#38bdf8]/70 bg-[#072635] text-[#bae6fd]' : 'border-zinc-700 text-zinc-200 hover:border-zinc-500' }}"
                >
                    <span class="text-base">
                        {{ $likedByUser ? '♥' : '♡' }}
                    </span>
                    <span>{{ $likedByUser ? 'liked' : 'like this exposition' }}</span>
                </button>

                <span class="text-zinc-500">
                    {{ $likesCount }} {{ \Illuminate\Support\Str::plural('like', $likesCount) }}
                </span>
            </div>

            <span class="text-[10px] text-zinc-500">{{ count($comments) }} {{ \Illuminate\Support\Str::plural('comment', count($comments)) }}</span>
        </div>

        <div class="space-y-3">
            @if ($canInteract)
                <form wire:submit.prevent="postComment" class="space-y-2">
                    <label for="commentBody" class="text-[11px] text-zinc-400 uppercase tracking-wide">
                        share your thoughts
                    </label>
                    <textarea
                        id="commentBody"
                        wire:model.defer="commentBody"
                        class="w-full bg-zinc-900/40 border border-zinc-700 focus:border-zinc-400 outline-none text-[12px] text-zinc-100 rounded-none px-3 py-2"
                        rows="3"
                        placeholder="leave a note for this exposition"
                    ></textarea>
                    @error('commentBody')
                        <p class="text-[10px] text-[#f97373]">{{ $message }}</p>
                    @enderror

                    <div class="flex items-center justify-end">
                        <button type="submit" class="px-3 py-1 border border-zinc-600 text-zinc-200 text-[11px] tracking-tight rounded-none hover:bg-zinc-800">
                            :: POST COMMENT
                        </button>
                    </div>
                </form>
            @else
                <div class="bg-zinc-900/30 border border-dashed border-zinc-700 px-3 py-2 text-[11px] text-zinc-400 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <span>sign in to like or comment on this exposition.</span>
                    <a href="{{ route('login') }}" class="text-[#38bdf8] underline text-[11px]">go to login</a>
                </div>
            @endif
        </div>

        <div class="space-y-3 border-t border-zinc-800 pt-4">
            @forelse ($comments as $comment)
                <article class="border border-zinc-700 rounded-none p-3 bg-zinc-900/20" wire:key="comment-{{ $comment['id'] }}">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[11px] text-zinc-300 font-semibold">{{ $comment['user_name'] }}</p>
                            <p class="text-[10px] text-zinc-500">
                                {{ $comment['user_handle'] }}
                                ·
                                {{ $comment['timestamp'] }}
                            </p>
                        </div>

                        @if ($comment['can_delete'])
                            <button
                                type="button"
                                wire:click="deleteComment({{ $comment['id'] }})"
                                class="text-[10px] text-[#f97373] hover:text-[#fca5a5]"
                            >
                                remove
                            </button>
                        @endif
                    </div>

                    <p class="text-[12px] text-zinc-200 mt-2 whitespace-pre-line">{{ $comment['body'] }}</p>
                </article>
            @empty
                <div class="border border-dashed border-zinc-700 px-4 py-6 text-center text-[12px] text-zinc-400 rounded-none">
                    no comments yet. be the first to share a reaction.
                </div>
            @endforelse
        </div>
    </div>

    {{-- NOTE: added items-start here --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 text-xs items-start">
        {{-- LEFT: SETTINGS / UPLOAD / THUMBNAIL --}}
        <div class="border border-zinc-700 bg-[#050608] rounded-none p-4 space-y-6">
            @if ($isOwner)
                @php($themeLabels = [-1=>'default',0=>'classic',1=>'medieval',2=>'scifi'])

                {{-- THUMBNAIL CONTROLS --}}
                <section class="space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                        <div>
                            <h2 class="text-[12px] font-semibold tracking-tight text-zinc-100">exposition thumbnail</h2>
                            <p class="text-[10px] text-zinc-500">4:3 cover · max 4&nbsp;MB</p>
                        </div>
                        <button
                            type="button"
                            wire:click="$toggle('showThumbnailEditor')"
                            class="px-3 py-1 border border-zinc-700 hover:border-zinc-500 rounded-none text-[11px] text-zinc-200"
                        >
                            {{ $showThumbnailEditor ? 'close editor' : 'update' }}
                        </button>
                    </div>

                    @if ($showThumbnailEditor)
                        <div class="space-y-3 border border-zinc-700 rounded-none p-3 bg-zinc-900/20">
                            <label class="block text-[11px] text-zinc-400" for="thumbnail">upload image</label>
                            <input
                                id="thumbnail"
                                type="file"
                                accept="image/*"
                                wire:model="thumbnail"
                                class="input-file-fancy"
                            >
                            @error('thumbnail')
                                <p class="text-[10px] text-[#f97373]">{{ $message }}</p>
                            @enderror

                            <div class="border border-zinc-700 rounded-none p-2">
                                <p class="text-[10px] text-zinc-500 mb-2">preview</p>
                                <div class="w-full bg-zinc-900 border border-dashed border-zinc-700 rounded-none flex items-center justify-center overflow-hidden"
                                     style="aspect-ratio: 4 / 3;">
                                    @if ($thumbnail)
                                        <img src="{{ $thumbnail->temporaryUrl() }}" alt="thumbnail preview" class="w-full h-full object-cover">
                                    @elseif ($exposition->cover_image_path)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($exposition->cover_image_path) }}" alt="{{ $exposition->title }} cover" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-[10px] text-zinc-500">no thumbnail yet</span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2">
                                <button
                                    type="button"
                                    wire:click="saveThumbnail"
                                    class="px-3 py-1 border border-[#f97373]/80 bg-[#5b1010] text-[#ffecec] rounded-none hover:bg-[#7f1717] text-[11px]"
                                >
                                    :: SAVE THUMBNAIL
                                </button>

                                @if ($exposition->cover_image_path)
                                    <button
                                        type="button"
                                        wire:click="clearThumbnail"
                                        class="px-3 py-1 border border-zinc-600 text-zinc-200 rounded-none hover:bg-zinc-800/60 text-[11px]"
                                    >
                                        :: CLEAR THUMBNAIL
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif
                </section>

                {{-- THEME PRESET --}}
                <section class="space-y-2">
                    <div class="flex items-center justify-between">
                        <h2 class="text-[12px] font-semibold tracking-tight text-zinc-100">theme preset</h2>
                        <span class="text-[10px] text-zinc-500">current: <span class="text-zinc-300">{{ $themeLabels[$exposition->preset_theme] ?? 'default' }}</span></span>
                    </div>
                    <div class="flex flex-wrap gap-2 text-[11px]">
                        <button type="button"
                                wire:click="setPresetTheme(-1)"
                                class="px-3 py-1 border rounded-none {{ ($exposition->preset_theme ?? -1) === -1 ? 'border-zinc-400 bg-zinc-800/50 text-zinc-200' : 'border-white/20 text-white/50 hover:text-white' }}">
                            custom
                        </button>
                        <button type="button"
                                wire:click="setPresetTheme(0)"
                                class="px-3 py-1 border rounded-none {{ $exposition->preset_theme === 0 ? 'border-zinc-300 bg-zinc-800/50 text-zinc-200' : 'border-white/20 text-white/50 hover:text-white' }}">
                            classic
                        </button>
                        <button type="button"
                                wire:click="setPresetTheme(1)"
                                class="px-3 py-1 border rounded-none {{ $exposition->preset_theme === 1 ? 'border-zinc-300 bg-zinc-800/50 text-zinc-200' : 'border-white/20 text-white/50 hover:text-white' }}">
                            medieval
                        </button>
                        <button type="button"
                                wire:click="setPresetTheme(2)"
                                class="px-3 py-1 border rounded-none {{ $exposition->preset_theme === 2 ? 'border-zinc-300 bg-zinc-800/50 text-zinc-200' : 'border-white/20 text-white/50 hover:text-white' }}">
                            scifi
                        </button>
                    </div>
                </section>

                {{-- ENVIRONMENT MEDIA --}}
                <section class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-[12px] font-semibold tracking-tight text-zinc-100">environment media</h2>
                        <span class="text-[10px] text-zinc-500">
                            {{ ($exposition->preset_theme ?? -1) === -1 ? 'custom mode' : 'preset locked' }}
                        </span>
                    </div>

                    @if (($exposition->preset_theme ?? -1) === -1)
                        <p class="text-[11px] text-zinc-500">Drop seamless textures or a short loop to shape the space.</p>

                        <form wire:submit.prevent="saveEnvironmentAssets" class="space-y-4">
                            <div class="grid grid-cols-1 gap-4">
                                <div class="space-y-2">
                                    <label class="block text-[11px] text-zinc-400" for="floorTextureUpload">floor texture</label>
                                    <input
                                        id="floorTextureUpload"
                                        type="file"
                                        accept="image/png,image/jpeg,image/jpg,image/webp,image/avif,image/bmp"
                                        wire:model="floorTextureUpload"
                                        class="input-file-fancy"
                                    >
                                    <p class="text-[10px] text-zinc-500">
                                        @if ($exposition->floor_texture)
                                            <a href="{{ \Illuminate\Support\Facades\Storage::url($exposition->floor_texture) }}" class="text-[#38bdf8] underline" target="_blank">
                                                {{ basename($exposition->floor_texture) }}
                                            </a>
                                            <button type="button" wire:click="clearEnvironmentAsset('floor')" class="ml-2 text-[#f97373] hover:text-[#fca5a5]">remove</button>
                                        @else
                                            <span class="text-zinc-300">none uploaded</span>
                                        @endif
                                    </p>
                                    @error('floorTextureUpload')
                                        <p class="text-[10px] text-[#f97373]">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="space-y-2">
                                    <label class="block text-[11px] text-zinc-400" for="ceilingTextureUpload">ceiling texture</label>
                                    <input
                                        id="ceilingTextureUpload"
                                        type="file"
                                        accept="image/png,image/jpeg,image/jpg,image/webp,image/avif,image/bmp"
                                        wire:model="ceilingTextureUpload"
                                        class="input-file-fancy"
                                    >
                                    <p class="text-[10px] text-zinc-500">
                                        @if ($exposition->ceiling_texture)
                                            <a href="{{ \Illuminate\Support\Facades\Storage::url($exposition->ceiling_texture) }}" class="text-[#38bdf8] underline" target="_blank">
                                                {{ basename($exposition->ceiling_texture) }}
                                            </a>
                                            <button type="button" wire:click="clearEnvironmentAsset('ceiling')" class="ml-2 text-[#f97373] hover:text-[#fca5a5]">remove</button>
                                        @else
                                            <span class="text-zinc-300">none uploaded</span>
                                        @endif
                                    </p>
                                    @error('ceilingTextureUpload')
                                        <p class="text-[10px] text-[#f97373]">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="space-y-2">
                                    <label class="block text-[11px] text-zinc-400" for="wallTextureUpload">wall texture</label>
                                    <input
                                        id="wallTextureUpload"
                                        type="file"
                                        accept="image/png,image/jpeg,image/jpg,image/webp,image/avif,image/bmp"
                                        wire:model="wallTextureUpload"
                                        class="input-file-fancy"
                                    >
                                    <p class="text-[10px] text-zinc-500">
                                        @if ($exposition->wall_texture)
                                            <a href="{{ \Illuminate\Support\Facades\Storage::url($exposition->wall_texture) }}" class="text-[#38bdf8] underline" target="_blank">
                                                {{ basename($exposition->wall_texture) }}
                                            </a>
                                            <button type="button" wire:click="clearEnvironmentAsset('wall')" class="ml-2 text-[#f97373] hover:text-[#fca5a5]">remove</button>
                                        @else
                                            <span class="text-zinc-300">none uploaded</span>
                                        @endif
                                    </p>
                                    @error('wallTextureUpload')
                                        <p class="text-[10px] text-[#f97373]">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="space-y-2">
                                    <label class="block text-[11px] text-zinc-400" for="ambientTrackUpload">ambient loop</label>
                                    <input
                                        id="ambientTrackUpload"
                                        type="file"
                                        accept="audio/*,.mp3,.wav,.ogg,.flac,.m4a"
                                        wire:model="ambientTrackUpload"
                                        class="input-file-fancy"
                                    >
                                    <p class="text-[10px] text-zinc-500">
                                        @if ($exposition->ambient_track)
                                            <a href="{{ \Illuminate\Support\Facades\Storage::url($exposition->ambient_track) }}" class="text-[#38bdf8] underline" target="_blank">
                                                {{ basename($exposition->ambient_track) }}
                                            </a>
                                            <button type="button" wire:click="clearEnvironmentAsset('ambient')" class="ml-2 text-[#f97373] hover:text-[#fca5a5]">remove</button>
                                        @else
                                            <span class="text-zinc-300">none uploaded</span>
                                        @endif
                                    </p>
                                    <p class="text-[10px] text-zinc-500">mp3, wav, ogg, flac, m4a · up to ~30&nbsp;MB</p>
                                    @error('ambientTrackUpload')
                                        <p class="text-[10px] text-[#f97373]">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <button
                                    type="submit"
                                    class="px-3 py-1 border border-[#38bdf8]/80 bg-[#072635] text-[#bae6fd] text-[11px] rounded-none hover:bg-[#0a3a50]"
                                >
                                    :: SAVE MEDIA
                                </button>
                                <p class="text-[10px] text-zinc-500">leave blanks to keep files</p>
                            </div>
                        </form>
                    @else
                        <div class="border border-dashed border-zinc-700 rounded-none p-3 text-[11px] text-zinc-500">
                            presets supply their own textures. switch to custom to override them.
                        </div>
                    @endif
                </section>

                {{-- EXHIBIT UPLOAD FORM --}}
                <section>
                    <h2 class="sr-only">upload new exhibit</h2>
                    <x-expositions.upload-form />
                </section>
            @else
                <section class="space-y-2">
                    <h2 class="text-[12px] font-semibold tracking-tight text-zinc-100">read-only mode</h2>
                    <p class="text-[11px] text-zinc-500">
                        only
                        <span class="text-zinc-300">
                            {{ '@'.($exposition->user?->name ? \Illuminate\Support\Str::slug($exposition->user->name, '_') : 'its_curator') }}
                        </span>
                        can upload or edit assets in this space.
                    </p>
                </section>
            @endif
        </div>

        {{-- RIGHT: EXHIBITS LIST --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-[12px] font-semibold tracking-tight text-zinc-100">exhibits in this exposition</h2>
                <span class="text-[10px] text-zinc-500">{{ count($exhibits) }} total</span>
            </div>

            <div class="space-y-4">
                @forelse ($exhibits as $exhibit)
                    <article class="border border-zinc-700 bg-[#050608] rounded-none p-4 space-y-2" wire:key="exhibit-{{ $exhibit->id }}">
                        <div class="flex items-center justify-between text-[10px] text-zinc-500">
                            <span>uploaded {{ $exhibit->created_at->diffForHumans() }}</span>
                        </div>

                        <h3 class="text-[14px] text-zinc-100 font-semibold tracking-tight">
                            {{ $exhibit->title }}
                        </h3>

                        <p class="text-[11px] text-zinc-400">
                            {{ $exhibit->description ?: 'no description — add one when needed.' }}
                        </p>

                        <p class="text-[10px] text-zinc-500">
                            stored under:
                            <span class="text-zinc-300">{{ $exhibit->media_path }}</span>
                        </p>

                        <div class="space-y-3 pt-1">
                            <div class="flex items-center justify-between text-[10px] text-zinc-500">
                                <span>size multiplier</span>
                                <span class="text-zinc-300 font-mono">
                                    <span
                                        class="js-slider-output"
                                        data-slider-output="exhibit-{{ $exhibit->id }}"
                                    >
                                        {{ number_format((float) ($exhibitSizes[$exhibit->id] ?? $exhibit->size ?? 1), 1) }}
                                    </span>&times;
                                </span>
                            </div>

                            @if ($isOwner)
                                <div class="space-y-2">
                                    <input
                                        id="exhibit-size-{{ $exhibit->id }}"
                                        type="range"
                                        min="1"
                                        max="10"
                                        step="0.1"
                                        wire:model.defer="exhibitSizes.{{ $exhibit->id }}"
                                        class="slider-square w-full accent-[#facc15] js-size-slider"
                                        style="--slider-thumb-color:#facc15"
                                        data-slider-target="exhibit-{{ $exhibit->id }}"
                                    >

                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-[10px] text-zinc-500">
                                        <span>drag to preview, then save to push live.</span>
                                        <button
                                            type="button"
                                            wire:click="saveExhibitSize({{ $exhibit->id }})"
                                            wire:loading.attr="disabled"
                                            wire:target="saveExhibitSize({{ $exhibit->id }})"
                                            class="px-3 py-1 border border-[#facc15]/60 text-[#fef9c3] rounded-none bg-[#3b3001]/60 hover:bg-[#4a4003]"
                                        >
                                            :: SAVE SIZE
                                        </button>
                                    </div>
                                </div>
                            @else
                                <p class="text-[10px] text-zinc-500">set by the curator.</p>
                            @endif
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pt-2">
                            <span class="text-[10px] text-zinc-500">position: {{ $exhibit->position ?? 0 }}</span>

                            <div class="flex items-center gap-2">
                                <a href="{{ route('exhibits.download', [$exposition, $exhibit]) }}"
                                   class="px-3 py-1 border border-[#38bdf8]/80 bg-[#072635] text-[#bae6fd] text-[11px] rounded-none hover:bg-[#0a3a50]"
                                   title="Download this exhibit as .zip">
                                    :: DOWNLOAD
                                </a>

                                @if ($isOwner)
                                    <button
                                        type="button"
                                        wire:click="delete({{ $exhibit->id }})"
                                        class="px-3 py-1 border border-[#f97373]/80 text-[#ffecec] text-[11px] rounded-none hover:bg-[#5b1010]/40"
                                    >
                                        :: DELETE EXHIBIT
                                    </button>
                                @endif
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="border border-dashed border-zinc-700 p-6 text-center text-[12px] text-zinc-400 rounded-none">
                        no exhibits yet. upload your first asset on the left.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>

@push('scripts')
    @once
        <script>
            document.addEventListener('livewire:init', () => {
                const updateOutput = (slider) => {
                    const targetKey = slider.dataset.sliderTarget;
                    const output = document.querySelector(`[data-slider-output="${targetKey}"]`);

                    if (!output) {
                        return;
                    }

                    const value = Number.parseFloat(slider.value || '1').toFixed(1);
                    output.textContent = value;
                };

                const bindSizeSliders = () => {
                    document.querySelectorAll('[data-slider-target]').forEach((slider) => {
                        if (slider.dataset.sliderBound === 'true') {
                            return;
                        }

                        slider.dataset.sliderBound = 'true';
                        slider.addEventListener('input', () => updateOutput(slider));
                        updateOutput(slider);
                    });
                };

                bindSizeSliders();

                if (window.Livewire && typeof window.Livewire.hook === 'function') {
                    window.Livewire.hook('message.processed', bindSizeSliders);
                }
            });
        </script>
    @endonce
@endpush
