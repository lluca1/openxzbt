@props([
    'exposition',
    'index' => null,
    'descriptionLimit' => null,
    'actionVariant' => 'public',
    'deleteMode' => 'wire',
    'wireDeleteAction' => 'delete',
    'showOwnerMeta' => true,
    'showActions' => true,
])

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
    use App\Models\User;

    // Cover image
    $coverUrl = $exposition->cover_image_path ? Storage::url($exposition->cover_image_path) : null;

    // Ordinal index label
    $ordinal = str_pad((string) ($index ?? 0), 2, '0', STR_PAD_LEFT);

    // Description handling
    $description = $exposition->description ?: 'no description yet — add a short note.';
    if ($descriptionLimit) {
        $description = Str::limit($description, $descriptionLimit);
    }

    // Curator handle + likes count
    $curatorHandle = '@' . ($exposition->user?->name ? Str::slug($exposition->user->name, '_') : 'anonymous');
    $likesCount = $exposition->likes_count ?? ($exposition->relationLoaded('likes') ? $exposition->likes->count() : 0);

    // Resolve owner (DB relation fallback)
    $owner = $exposition->user;
    if (! $owner && auth()->check() && $exposition->user_id === auth()->id()) {
        $owner = auth()->user();
    }
    if (! $owner && ! empty($exposition->user_id)) {
        $owner = User::find($exposition->user_id);
    }
    $ownerName = $owner?->name ?? 'unknown_user';

    // ---------------- AVATAR RESOLUTION ----------------
    $defaultAvatarFile = 'you-avatar-cap_on-cap_color_default-body_color_default.png';
    $avatarUrl = asset('assets/img/' . $defaultAvatarFile);

    $avatarUser = $owner;

    if ($actionVariant === 'manage' && auth()->check()) {
        $avatarUser = auth()->user();
        $ownerName  = $avatarUser->name ?? $ownerName;
    }

    if ($avatarUser) {
        $candidate = null;

        if (! empty($avatarUser->avatar_url)) {
            $candidate = $avatarUser->avatar_url;
        } elseif (! empty($avatarUser->avatar)) {
            $candidate = $avatarUser->avatar;
        } elseif (! empty($avatarUser->profile_photo_url)) {
            $candidate = $avatarUser->profile_photo_url;
        }

        if (! empty($candidate)) {
            if (Str::startsWith($candidate, ['http://', 'https://'])) {
                $avatarUrl = $candidate;
            } elseif (Str::startsWith($candidate, ['/'])) {
                $avatarUrl = asset(ltrim($candidate, '/'));
            } elseif (Str::startsWith($candidate, ['storage/', 'assets/', 'images/'])) {
                $avatarUrl = asset($candidate);
            } else {
                $avatarUrl = asset('assets/img/' . ltrim($candidate, '/'));
            }
        }
    }

    // Action variant / delete mode normalization
    $actionVariant = in_array($actionVariant, ['manage', 'public'], true) ? $actionVariant : 'public';
    $deleteMode = $deleteMode === 'form' ? 'form' : 'wire';

    // Manage URL
    $manageUrl = route('expositions.show', $exposition);
@endphp

<article {{ $attributes->merge(['class' => 'border border-zinc-700 hover:border-zinc-300 transition bg-[#050608] rounded-none p-4 flex flex-col gap-3']) }}>

    {{-- COVER / THUMBNAIL --}}
    <div
        class="w-full bg-zinc-900 border border-dashed border-zinc-700 rounded-none flex items-center justify-center text-[10px] text-zinc-500 overflow-hidden"
        style="aspect-ratio: 4 / 3;"
    >
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

    {{-- TITLE --}}
    <span class="text-zinc-200">
        {{ '[' . $ordinal . ']' }}
        {{ Str::upper($exposition->title) }}
    </span>

    {{-- DESCRIPTION --}}
    <p class="text-[11px] text-zinc-400 line-clamp-3">
        {{ $description }}
    </p>

    {{-- META --}}
    <div class="flex flex-col gap-1 text-[10px] text-zinc-500">
        <span>
            curator:
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

    {{-- OWNER + ACTIONS --}}
    @if ($showActions)
        <div class="mt-3 flex items-center justify-between gap-3">
            @if ($showOwnerMeta)
                <div class="flex items-center gap-2">
                    <div class="h-8 w-8 bg-[#111] border border-white/15 overflow-hidden rounded-none">
                        <img
                            src="{{ $avatarUrl }}"
                            alt="owner avatar"
                            class="w-full h-full object-contain"
                        >
                    </div>
                    <span class="text-[11px] text-white/60">
                        {{ $ownerName }}
                    </span>
                </div>
            @endif

            <div class="flex items-center gap-2 text-[11px]">
                @if ($slot->isNotEmpty())
                    {{ $slot }}
                @else
                    @if ($actionVariant === 'manage')
                        <a href="{{ $manageUrl }}"
                           class="border border-zinc-600 hover:border-zinc-300 px-3 py-1 rounded-none text-left">
                            :: MANAGE EXHIBITS
                        </a>

                        @if ($deleteMode === 'wire')
                            <button
                                type="button"
                                wire:click="{{ $wireDeleteAction }}({{ $exposition->id }})"
                                class="px-3 py-1 border border-[#f97373]/80 bg-[#5b1010] text-[#ffecec] rounded-none hover:bg-[#7f1717]"
                            >
                                :: DELETE
                            </button>
                        @else
                            <form method="POST" action="{{ route('expositions.destroy', $exposition) }}">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="px-3 py-1 border border-[#f97373]/80 bg-[#5b1010] text-[#ffecec] rounded-none hover:bg-[#7f1717]"
                                >
                                    :: DELETE
                                </button>
                            </form>
                        @endif
                    @else
                        @guest
                            <a href="{{ route('login') }}"
                               class="border border-white/30 text-white px-3 py-1 rounded-none">
                                login_to_view →
                            </a>
                        @else
                            <a href="{{ $manageUrl }}"
                               class="border border-[#f97373]/70 bg-[#5b1010] text-[#ffecec] px-3 py-1 rounded-none hover:bg-[#7f1717]/80 text-left">
                                view_details →
                            </a>
                        @endguest
                    @endif
                @endif
            </div>
        </div>
    @endif
</article>
