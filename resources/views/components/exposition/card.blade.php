@props([
    'exposition',
    'index' => null,
    'descriptionLimit' => null,
])

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $coverUrl = $exposition->cover_image_path ? Storage::url($exposition->cover_image_path) : null;
    $ordinal = str_pad((string) ($index ?? 0), 2, '0', STR_PAD_LEFT);
    $description = $exposition->description ?: 'no description yet — add a short note.';

    if ($descriptionLimit) {
        $description = Str::limit($description, $descriptionLimit);
    }

    $curatorHandle = '@' . ($exposition->user?->name ? Str::slug($exposition->user->name, '_') : 'anonymous');
    $likesCount = $exposition->likes_count ?? ($exposition->relationLoaded('likes') ? $exposition->likes->count() : 0);
@endphp

<article {{ $attributes->merge(['class' => 'border border-zinc-700 hover:border-zinc-300 transition bg-[#050608] rounded-none p-4 flex flex-col gap-3']) }}>
    <div class="w-full bg-zinc-900 border border-dashed border-zinc-700 rounded-none flex items-center justify-center text-[10px] text-zinc-500 overflow-hidden"
         style="aspect-ratio: 4 / 3;">
        @if ($coverUrl)
            <img
                src="{{ $coverUrl }}"
                alt="{{ $exposition->title }} cover"
                class="w-full h-full object-cover"
            >
        @else
            preview_placeholder
        @endif
    </div>

    <span class="text-zinc-200">
        {{ '[' . $ordinal . ']' }}
        {{ Str::upper($exposition->title) }}
    </span>

    <p class="text-[11px] text-zinc-400 line-clamp-3">
        {{ $description }}
    </p>

    <div class="flex flex-col gap-1 text-[10px] text-zinc-500">
        <span>curator:
            <span class="text-zinc-300">{{ $curatorHandle }}</span>
        </span>
        <span>exhibits: {{ $exposition->exhibits_count }}</span>
        <span>status: {{ $exposition->is_public ? 'public' : 'private' }}</span>
        <span class="flex items-center gap-1">
            <span>likes:</span>
            <span class="text-zinc-300 flex items-center gap-1">
                {{ $likesCount }}
                <span class="text-[#facc15] text-xs"><3</span>
            </span>
        </span>
    </div>

    <div class="flex flex-col gap-2 mt-2">
        @if ($slot->isNotEmpty())
            {{ $slot }}
        @else
            <a href="{{ route('expositions.show', $exposition) }}"
               class="border border-zinc-600 hover:border-zinc-300 px-3 py-1 text-left rounded-none">
                :: MANAGE EXHIBITS
            </a>
            <button type="button"
                    wire:click="delete({{ $exposition->id }})"
                    class="border border-[#f97373]/80 text-[#ffecec] px-3 py-1 rounded-none hover:bg-[#5b1010]/50">
                :: DELETE EXPOSITION
            </button>
        @endif
    </div>
</article>
