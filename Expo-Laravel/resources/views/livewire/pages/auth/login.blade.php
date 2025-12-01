<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function login(): void
    {
        $this->validate();
        $this->form->authenticate();
        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
};
?>

<div class="w-full sm:max-w-md mx-auto mt-20
            border border-white/20 bg-[#0a0a0a] px-8 py-10
            shadow-[0_0_15px_rgba(255,255,255,0.05)]
            text-[13px] tracking-tight">

    <!-- Header -->
    <h2 class="text-white text-lg font-semibold mb-6 border-b border-white/10 pb-2">
        :: authentication_required
    </h2>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form wire:submit="login" class="space-y-6">

        <!-- EMAIL -->
        <div>
            <label for="email" class="uppercase text-[11px] text-white/60">
                email_address
            </label>

            <input
                wire:model="form.email"
                id="email"
                type="email"
                autocomplete="username"
                class="block w-full mt-1 px-3 py-2 bg-black text-white
                       border border-white/20 focus:border-red-500
                       focus:ring-red-500 outline-none tracking-tight"
                required
            />

            <x-input-error :messages="$errors->get('form.email')" class="mt-2 text-red-500" />
        </div>

        <!-- PASSWORD -->
        <div>
            <label for="password" class="uppercase text-[11px] text-white/60">
                password_key
            </label>

            <input
                wire:model="form.password"
                id="password"
                type="password"
                autocomplete="current-password"
                class="block w-full mt-1 px-3 py-2 bg-black text-white
                       border border-white/20 focus:border-red-500
                       focus:ring-red-500 outline-none tracking-tight"
                required
            />

            <x-input-error :messages="$errors->get('form.password')" class="mt-2 text-red-500" />
        </div>

        <!-- REMEMBER -->
        <label class="flex items-center gap-2 text-[11px] text-white/60">
            <input
                wire:model="form.remember"
                id="remember"
                type="checkbox"
                class="bg-black border-white/30 text-red-500 focus:ring-red-500"
            >
            remember_session
        </label>

        <!-- ACTIONS -->
        <div class="flex items-center justify-between pt-4 border-t border-white/10">

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}"
                   wire:navigate
                   class="text-white/40 hover:text-white transition text-[11px]">
                    forgot_password?
                </a>
            @endif

            <button
                class="px-6 py-2 bg-[#3b0d0d] text-[#fca5a5] border border-[#f87171]/60
                       hover:bg-[#5c0d0d] hover:border-red-500 transition
                       uppercase text-[11px] tracking-wider">
                log_in
            </button>
        </div>

    </form>

</div>
