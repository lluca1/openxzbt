<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';
    public string $email = '';

    // color selections
    public string $capColor = 'default';
    public string $bodyColor = 'default';

    // available color options
    public array $availableColors = ['default', 'green', 'red'];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();

        $this->name  = $user->name;
        $this->email = $user->email;

        // Try to read colors from stored avatar filename if present
        if ($user->avatar && preg_match('/cap_color_([a-z]+)-body_color_([a-z]+)/', $user->avatar, $matches)) {
            $this->capColor  = $matches[1] ?? 'default';
            $this->bodyColor = $matches[2] ?? 'default';
        } else {
            $this->capColor  = 'default';
            $this->bodyColor = 'default';
        }
    }

    /**
     * Build the avatar filename from current colors.
     */
    private function buildAvatarFilename(): string
    {
        return "you-avatar-cap_on-cap_color_{$this->capColor}-body_color_{$this->bodyColor}.png";
    }

    /**
     * Update the profile information.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'capColor'  => ['required', Rule::in($this->availableColors)],
            'bodyColor' => ['required', Rule::in($this->availableColors)],
        ]);

        $avatarFilename = $this->buildAvatarFilename();

        $user->fill([
            'name'   => $validated['name'],
            'email'  => $validated['email'],
            'avatar' => $avatarFilename,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));
            return;
        }

        $user->sendEmailVerificationNotification();
        Session::flash('status', 'verification-link-sent');
    }
}; ?>

@php
    $previewFile = "you-avatar-cap_on-cap_color_{$capColor}-body_color_{$bodyColor}.png";
@endphp

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-100">
            :: profile_core
        </h2>

        <p class="mt-1 text-xs text-zinc-400">
            update your display identity, avatar, and email address.
        </p>
    </header>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-8">

        {{-- AVATAR CONFIGURATION --}}
        <div class="space-y-4">
            <h3 class="text-xs font-semibold tracking-[0.2em] text-yellow-300 uppercase">
                avatar_config
            </h3>

            {{-- preview --}}
            <div class="flex items-center gap-4">
                <div class="w-20 h-20 border border-[#facc15]/60 bg-black/70 flex items-center justify-center">
                    <img
                        src="{{ asset('assets/img/' . $previewFile) }}"
                        alt="Avatar preview"
                        class="w-full h-full object-contain"
                    >
                </div>
                <div class="text-[11px] text-zinc-400">
                    <div class="text-zinc-200">
                        cap_color: <span class="text-yellow-300">{{ $capColor }}</span>
                        &nbsp;//&nbsp;
                        body_color: <span class="text-yellow-300">{{ $bodyColor }}</span>
                    </div>
                </div>
            </div>

            {{-- selectors (cap & body) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-[11px]">

                {{-- cap color --}}
                <div>
                    <div class="mb-2 text-zinc-300 uppercase tracking-[0.15em] text-[10px]">
                        cap_color
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($availableColors as $color)
                            <button
                                type="button"
                                wire:click="$set('capColor', '{{ $color }}')"
                                class="px-3 py-1 border
                                       {{ $capColor === $color
                                            ? 'border-[#facc15] bg-[#facc15]/15 text-yellow-200'
                                            : 'border-white/20 bg-black/60 text-zinc-300 hover:border-[#facc15]/60 hover:text-yellow-200' }}
                                       text-[10px] uppercase tracking-[0.18em]"
                            >
                                {{ $color }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- body color (ACTIVE IS NOW YELLOW TOO) --}}
                <div>
                    <div class="mb-2 text-zinc-300 uppercase tracking-[0.15em] text-[10px]">
                        body_color
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($availableColors as $color)
                            <button
                                type="button"
                                wire:click="$set('bodyColor', '{{ $color }}')"
                                class="px-3 py-1 border
                                       {{ $bodyColor === $color
                                            ? 'border-[#facc15] bg-[#facc15]/15 text-yellow-200'
                                            : 'border-white/20 bg-black/60 text-zinc-300 hover:border-[#facc15]/60 hover:text-yellow-200' }}
                                       text-[10px] uppercase tracking-[0.18em]"
                            >
                                {{ $color }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- NAME --}}
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input
                wire:model="name"
                id="name"
                name="name"
                type="text"
                class="mt-1 block w-full"
                required
                autocomplete="name"
            />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        {{-- EMAIL --}}
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input
                wire:model="email"
                id="email"
                name="email"
                type="email"
                class="mt-1 block w-full"
                required
                autocomplete="username"
            />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-200">
                        Your email address is unverified.

                        <button
                            wire:click.prevent="sendVerification"
                            class="underline text-xs text-zinc-400 hover:text-zinc-100"
                        >
                            Resend verification email.
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-xs text-green-400">
                            A new verification link has been sent.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        {{-- ACTIONS --}}
        <div class="flex items-center gap-4">
            <button
                type="submit"
                class="px-4 py-1 border border-[#facc15]/60 bg-black/80 text-[10px] uppercase tracking-[0.22em] text-yellow-300
                       hover:bg-[#facc15]/15 transition-colors"
            >
                save_profile
            </button>

            <x-action-message class="me-3 text-xs text-emerald-300" on="profile-updated">
                Saved.
            </x-action-message>
        </div>
    </form>
</section>
