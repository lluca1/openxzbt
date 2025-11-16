<header class="fixed top-0 left-0 w-full bg-black/95 border-b border-white/15 z-50">
    <div class="max-w-7xl mx-auto px-6 py-3 relative min-h-[68px]">

        @php
            $contextMap = [
                'login' => 'auth_login',
                'register' => 'auth_register',
                'home' => 'home',
                'profile' => 'profile_editor',
                'what.is.here' => 'what_is_here',
            ];

            $contextLabel = 'exposition_manager';
            foreach ($contextMap as $route => $label) {
                if (request()->routeIs($route)) {
                    $contextLabel = $label;
                    break;
                }
            }

            $stateClass = function (bool $isActive, string $active, string $inactive): string {
                return $isActive ? $active : $inactive;
            };

            $linkSets = [
                'authScreens' => [
                    [
                        'label' => '[*] HOME',
                        'url' => route('home'),
                        'class' => $stateClass(request()->routeIs('home'), 'border-[#f87171]/80 bg-[#3b0d0d] text-[#fecaca]', 'border-white/30 text-white/60 hover:text-white'),
                    ],
                    [
                        'label' => '[+] SIGN_UP',
                        'url' => route('register'),
                        'class' => $stateClass(request()->routeIs('register'), 'border-[#facc15]/90 bg-[#26220b] text-[#fef3c7]', 'border-white/30 text-white/60 hover:text-white'),
                    ],
                    [
                        'label' => '[?] WHAT_IS_HERE',
                        'url' => route('what.is.here'),
                        'class' => $stateClass(request()->routeIs('what.is.here'), 'border-[#22c55e]/70 bg-[#052713] text-[#bbf7d0]', 'border-white/30 text-white/60 hover:text-white'),
                    ],
                ],
                'default' => [
                    [
                        'label' => '[*] HOME',
                        'url' => route('home'),
                        'class' => $stateClass(request()->routeIs('home'), 'border-[#f87171]/80 bg-[#3b0d0d] text-[#fecaca]', 'border-white/30 text-white/60 hover:text-white'),
                    ],
                    [
                        'label' => '[+] CREATE_EXPOSITION',
                        'url' => auth()->check() ? route('dashboard') : route('login'),
                        'class' => $stateClass(request()->routeIs('dashboard'), 'border-[#facc15]/90 bg-[#26220b] text-[#fef3c7]', 'border-white/30 text-white/60 hover:text-white'),
                    ],
                    [
                        'label' => '[@] PROFILE',
                        'url' => route('profile'),
                        'class' => $stateClass(request()->routeIs('profile'), 'border-[#38bdf8]/70 bg-[#072635] text-[#bae6fd]', 'border-white/30 text-white/60 hover:text-white'),
                        'visible' => auth()->check(),
                    ],
                    [
                        'label' => '[?] WHAT_IS_HERE',
                        'url' => route('what.is.here'),
                        'class' => $stateClass(request()->routeIs('what.is.here'), 'border-[#22c55e]/70 bg-[#052713] text-[#bbf7d0]', 'border-white/30 text-white/60 hover:text-white'),
                    ],
                ],
            ];

            $activeLinks = request()->routeIs('login') || request()->routeIs('register')
                ? $linkSets['authScreens']
                : $linkSets['default'];

            $defaultAvatarFile = 'you-avatar-cap_on-cap_color_default-body_color_default.png';
            $avatarFile = auth()->check() && auth()->user()->avatar
                ? auth()->user()->avatar
                : $defaultAvatarFile;
        @endphp

        {{-- LEFT SIDE: BRAND + CONTEXT --}}
        <div class="flex items-center gap-3 absolute left-0 top-1/2 -translate-y-1/2 z-10">
            <span class="text-lg text-white tracking-tight font-semibold">openxzbt</span>
            <span class="text-[11px] text-white/40">:: {{ $contextLabel }}</span>
        </div>

        {{-- CENTER NAV: ABSOLUTE SO IT STAYS CENTERED --}}
        <nav class="hidden md:flex items-center justify-center gap-2 text-xs absolute inset-0">
            @foreach ($activeLinks as $link)
                @if(!isset($link['visible']) || $link['visible'])
                    <a href="{{ $link['url'] }}" class="px-3 py-1 border tracking-tight rounded-none transition {{ $link['class'] }}">
                        {{ $link['label'] }}
                    </a>
                @endif
            @endforeach
        </nav>

        {{-- RIGHT SIDE: AUTH CONTROLS + AVATAR --}}
        <div class="flex items-center gap-3 text-xs absolute right-0 top-1/2 -translate-y-1/2 z-10">
            @auth
                <div class="hidden md:flex flex-col text-right leading-tight">
                    <span class="text-white/70">{{ auth()->user()->name }}</span>
                    <span class="text-white/40">
                        {{ '@'.(auth()->user()->username ?? \Illuminate\Support\Str::slug(auth()->user()->name, '_')) }}
                    </span>
                </div>

                <form method="POST" action="{{ route('logout') }}" class="hidden md:block">
                    @csrf
                    <button type="submit"
                        class="px-4 py-4 text-xs text-white border border-white/30 bg-[#141414] hover:bg-[#1e1e1e] rounded-none">
                        LOGOUT
                    </button>
                </form>
            @else
                @if(!request()->routeIs('login') && !request()->routeIs('register'))
                    <a href="{{ route('login') }}"
                       class="px-4 py-4 text-xs text-white border border-white/30 bg-[#141414] hover:bg-[#1e1e1e] rounded-none">
                        LOGIN
                    </a>
                @endif
            @endauth

            <div class="h-12 w-12 bg-[#111] flex items-center justify-center overflow-hidden rounded-none">
                <img
                    src="{{ asset('assets/img/' . $avatarFile) }}"
                    alt="avatar"
                    class="w-full h-full object-contain opacity-90"
                    style="object-position: center 20%;"
                >
            </div>
        </div>

    </div>
</header>
