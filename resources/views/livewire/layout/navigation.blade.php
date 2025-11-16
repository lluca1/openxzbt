<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
};
?>

<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
    <!-- Primary Navigation -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">

            <!-- Left side -->
            <div class="flex items-center">

                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}" wire:navigate>
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Nav Links -->
                <div class="hidden sm:flex sm:space-x-8 ms-10">

                    <!-- HOME -->
                    <x-nav-link 
                        :href="route('home')" 
                        :active="request()->routeIs('home')"
                        wire:navigate
                    >
                        <span class="{{ request()->routeIs('home') ? 'text-red-600 dark:text-red-400' : '' }}">
                            Home
                        </span>
                    </x-nav-link>

                    <!-- DASHBOARD -->
                    @auth
                    <x-nav-link 
                        :href="route('dashboard')" 
                        :active="request()->routeIs('dashboard')"
                        wire:navigate
                    >
                        Dashboard
                    </x-nav-link>
                    @endauth

                </div>

            </div>

            <!-- Right side -->
            <div class="hidden sm:flex sm:items-center">

                @auth
                <!-- Profile Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                            @if(request()->routeIs('profile'))
                                <button
                                    style="background-color: #072635 !important; color: #bae6fd !important; border-color: rgba(56,189,248,0.44) !important;"
                                    class="inline-flex items-center px-3 py-2 border rounded-md text-sm font-medium transition focus:outline-none border-[#38bdf8]/70 bg-[#072635] text-[#bae6fd]">
                                    <span>{{ auth()->user()->name }}</span>

                                    <svg class="ms-2 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd"
                                              d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 
                                              111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 
                                              010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            @else
                                <button
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md 
                                           text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 hover:text-gray-800 dark:hover:text-gray-100 
                                           focus:outline-none transition">
                                    <span>{{ auth()->user()->name }}</span>

                                    <svg class="ms-2 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd"
                                              d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 
                                              111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 
                                              010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </button>
                            @endif
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile')" wire:navigate
                            class="{{ request()->routeIs('profile') ? 'border-[#38bdf8]/70 bg-[#072635] text-[#bae6fd]' : '' }}"
                            style="{{ request()->routeIs('profile') ? 'background-color: #072635; color: #bae6fd; border-color: rgba(56,189,248,0.44);' : '' }}">
                            Profile
                        </x-dropdown-link>

                        <button wire:click="logout" class="w-full text-start">
                            <x-dropdown-link>
                                Log out
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
                @endauth

                @guest
                <a href="{{ route('login') }}"
                   class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium 
                          bg-red-600 text-white hover:bg-red-500 transition">
                    Log in
                </a>
                @endguest

            </div>

            <!-- Hamburger -->
            <div class="sm:hidden flex items-center">
                <button @click="open = !open"
                        class="p-2 rounded-md text-gray-500 dark:text-gray-400 hover:text-gray-700 
                               dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-900 transition">

                    <svg class="h-6 w-6" stroke="currentColor" fill="none">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }"
                              class="inline-flex"
                              stroke-width="2"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M4 6h16M4 12h16M4 18h16"/>

                        <path :class="{ 'hidden': !open, 'inline-flex': open }"
                              class="hidden"
                              stroke-width="2"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                              d="M6 18L18 6M6 6l12 12"/>
                    </svg>

                </button>
            </div>

        </div>
    </div>

    <!-- Mobile Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">

        <!-- MOBILE LINKS -->
        <div class="pt-2 pb-3 space-y-1">

            <!-- MOBILE HOME -->
            <x-responsive-nav-link 
                :href="route('home')" 
                :active="request()->routeIs('home')" 
                wire:navigate
            >
                <span class="{{ request()->routeIs('home') ? 'text-red-600 dark:text-red-400' : '' }}">
                    Home
                </span>
            </x-responsive-nav-link>

            @auth
            <!-- MOBILE DASHBOARD -->
            <x-responsive-nav-link 
                :href="route('dashboard')" 
                :active="request()->routeIs('dashboard')" 
                wire:navigate
            >
                Dashboard
            </x-responsive-nav-link>
            @endauth

        </div>

        <!-- MOBILE PROFILE/LOGIN -->
        @auth
        <div class="pt-4 pb-3 border-t border-gray-200 dark:border-gray-700">

            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">
                    {{ auth()->user()->name }}
                </div>
                <div class="font-medium text-sm text-gray-500">
                    {{ auth()->user()->email }}
                </div>
            </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile')" :active="request()->routeIs('profile')" wire:navigate>
                        Profile
                    </x-responsive-nav-link>

                    <button wire:click="logout" class="w-full text-start">
                        <x-responsive-nav-link>
                            Log out
                        </x-responsive-nav-link>
                    </button>
                </div>

        </div>
        @endauth

        @guest
        <div class="pt-4 pb-3 border-t border-gray-200 dark:border-gray-700">
            <x-responsive-nav-link :href="route('login')">
                Log in
            </x-responsive-nav-link>
        </div>
        @endguest

    </div>
</nav>
