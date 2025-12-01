<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<section>
    <header>
        <p class="mt-1 text-xs text-zinc-400">
            rotate your access key with something long and unguessable.
        </p>
    </header>

    <form wire:submit="updatePassword" class="mt-6 space-y-6">

        <div>
            <x-input-label
                for="update_password_current_password"
                :value="__('current_password')"
                class="uppercase tracking-[0.18em] text-[10px] text-zinc-300"
            />
            <x-text-input
                wire:model="current_password"
                id="update_password_current_password"
                name="current_password"
                type="password"
                class="mt-1 block w-full"
                autocomplete="current-password"
            />
            <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label
                for="update_password_password"
                :value="__('new_password')"
                class="uppercase tracking-[0.18em] text-[10px] text-zinc-300"
            />
            <x-text-input
                wire:model="password"
                id="update_password_password"
                name="password"
                type="password"
                class="mt-1 block w-full"
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label
                for="update_password_password_confirmation"
                :value="__('confirm_password')"
                class="uppercase tracking-[0.18em] text-[10px] text-zinc-300"
            />
            <x-text-input
                wire:model="password_confirmation"
                id="update_password_password_confirmation"
                name="password_confirmation"
                type="password"
                class="mt-1 block w-full"
                autocomplete="new-password"
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <button
                type="submit"
                class="px-4 py-1 border border-sky-400/60 bg-black/80 text-[10px]
                       uppercase tracking-[0.22em] text-sky-300
                       hover:bg-sky-400/15 transition-colors"
            >
                save_password
            </button>

            <x-action-message class="me-3 text-xs text-sky-300" on="password-updated">
                {{ __('Saved.') }}
            </x-action-message>
        </div>
    </form>
</section>
