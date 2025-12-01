<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
};
?>

<div class="w-full sm:max-w-md mx-auto mt-20
            border border-white/20 bg-[#0a0a0a] px-8 py-10
            shadow-[0_0_15px_rgba(255,255,255,0.05)]
            text-[13px] tracking-tight">

    <!-- Header -->
    <h2 class="text-white text-lg font-semibold mb-6 border-b border-white/10 pb-2">
        :: create_new_account
    </h2>

    <form wire:submit="register" class="space-y-6">

        <!-- NAME -->
        <div>
            <label for="name" class="uppercase text-[11px] text-white/60">
                display_name
            </label>

            <input
                wire:model="name"
                id="name"
                type="text"
                name="name"
                autocomplete="name"
                required
                autofocus
                class="block w-full mt-1 px-3 py-2 bg-black text-white
                       border border-white/20 focus:border-red-500
                       focus:ring-red-500 outline-none tracking-tight"
            />

            <x-input-error :messages="$errors->get('name')" class="mt-2 text-red-500" />
        </div>

        <!-- EMAIL -->
        <div>
            <label for="email" class="uppercase text-[11px] text-white/60">
                email_address
            </label>

            <input
                wire:model="email"
                id="email"
                type="email"
                name="email"
                autocomplete="username"
                required
                class="block w-full mt-1 px-3 py-2 bg-black text-white
                       border border-white/20 focus:border-red-500
                       focus:ring-red-500 outline-none tracking-tight"
            />

            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-500" />
        </div>

        <!-- PASSWORD -->
        <div>
            <label for="password" class="uppercase text-[11px] text-white/60">
                password_key
            </label>

            <input
                wire:model="password"
                id="password"
                type="password"
                name="password"
                autocomplete="new-password"
                required
                class="block w-full mt-1 px-3 py-2 bg-black text-white
                       border border-white/20 focus:border-red-500
                       focus:ring-red-500 outline-none tracking-tight"
            />

            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-500" />
        </div>

        <!-- CONFIRM PASSWORD -->
        <div>
            <label for="password_confirmation" class="uppercase text-[11px] text-white/60">
                confirm_password
            </label>

            <input
                wire:model="password_confirmation"
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                autocomplete="new-password"
                required
                class="block w-full mt-1 px-3 py-2 bg-black text-white
                       border border-white/20 focus:border-red-500
                       focus:ring-red-500 outline-none tracking-tight"
            />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-500" />
        </div>

        <!-- ACTION ROW -->
        <div class="flex items-center justify-between pt-4 border-t border-white/10">

            <a href="{{ route('login') }}"
               wire:navigate
               class="text-[11px] text-white/40 hover:text-white transition">
                already_registered?
            </a>

            <button
                class="px-6 py-2 bg-[#052713] text-[#bbf7d0] border border-[#22c55e]/70
                       hover:bg-[#06411c] hover:border-[#22c55e] transition
                       uppercase text-[11px] tracking-wider">
                register_user
            </button>
        </div>

    </form>
</div>
