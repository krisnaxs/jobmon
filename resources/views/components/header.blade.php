@auth
    @php
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
        $division = $user->division;

        $currentTab = match (true) {
            request()->routeIs('dashboard') => 'dashboard',
            request()->routeIs('admin.wo.*', 'technician.wo.*', 'wo.*') => 'work-order',
            request()->routeIs('report.*') => 'report', // report admin
            request()->routeIs('technician.report.*') => 'my-report', //  report teknisi
            request()->routeIs('monitor') => 'monitor',
            request()->routeIs('team-settings.*') => 'team-settings',
            request()->routeIs('admin.users.*') => 'users',
            default => 'dashboard',
        };
        $tabs = [
            'dashboard' => [
                'label' => $isAdmin ? 'Dashboard' : 'Tugas Saya',
                'route' => route('dashboard'),
                'active' => 'bg-indigo-50 text-indigo-700',
                'icon' =>
                    '<path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />',
            ],
            'work-order' => [
                'label' => 'Work Order',
                'route' => $isAdmin ? route('admin.wo.index') : route('technician.wo.index'),
                'active' => 'bg-cyan-50 text-cyan-700',
                'icon' =>
                    '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />',
            ],
        ];
        if ($isAdmin) {
            $tabs['report'] = [
                'label' => 'Laporan',
                'route' => route('report.index'),
                'active' => 'bg-orange-50 text-orange-700',
                'icon' =>
                    '<path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />',
            ];
            $tabs['monitor'] = [
                'label' => 'Monitor',
                'route' => route('monitor'),
                'active' => 'bg-emerald-50 text-emerald-700',
                'icon' =>
                    '<path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />',
                'target' => '_blank',
            ];
            $tabs['team-settings'] = [
                'label' => 'Tim',
                'route' => route('team-settings.index'),
                'active' => 'bg-violet-50 text-violet-700',
                'icon' =>
                    '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />',
            ];
            $tabs['users'] = [
                'label' => 'Users',
                'route' => route('admin.users.index'),
                'active' => 'bg-pink-50 text-pink-700',
                'icon' =>
                    '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />',
            ];
        }
        if (!$isAdmin) {
            $tabs['my-report'] = [
                'label' => 'Laporan Saya',
                'route' => route('technician.report.index'),
                'active' => 'bg-orange-50 text-orange-700',
                'icon' =>
                    '<path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />',
            ];
        }
    @endphp

    <header x-data="{ userMenuOpen: false, mobileTabOpen: false, showLogoutConfirm: false }" class="fixed top-0 left-0 right-0 z-[100] bg-white shadow-md h-16">
        <div class="h-full px-4 flex items-center justify-between gap-4">

            {{-- LEFT: Logo + Tabs --}}
            <div class="flex items-center gap-4 min-w-0">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2 shrink-0">
                    <img src="{{ asset('favicon.png') }}?v=1" alt="Job Monitor"
                        class="w-9 h-9 rounded-lg object-contain bg-white border border-gray-200 p-0.5">
                    <span class="hidden lg:inline font-bold text-gray-800 text-lg">Job Monitor</span>
                </a>

                {{-- DESKTOP TABS --}}
                <nav class="hidden lg:flex items-center gap-1">
                    @foreach ($tabs as $key => $tab)
                        <a href="{{ $tab['route'] }}" @if (isset($tab['target'])) target="{{ $tab['target'] }}" @endif
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl
                                   text-sm font-semibold transition-all duration-200
                                   {{ $currentTab === $key ? $tab['active'] : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                {!! $tab['icon'] !!}
                            </svg>
                            {{ $tab['label'] }}
                        </a>
                    @endforeach
                </nav>
            </div>

            {{-- MOBILE: Tab Dropdown --}}
            <div class="lg:hidden relative">
                <button type="button" @click="mobileTabOpen = !mobileTabOpen"
                    class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-xl
                           bg-indigo-50 hover:bg-indigo-100 border border-indigo-200
                           text-indigo-700 font-semibold text-xs transition active:scale-95">
                    <span class="whitespace-nowrap">{{ $tabs[$currentTab]['label'] ?? 'Menu' }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5 transition-transform duration-200"
                        :class="mobileTabOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="mobileTabOpen" x-cloak @click.away="mobileTabOpen = false"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                    class="absolute left-0 top-full mt-2 w-52 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden z-[90]">
                    <div class="p-1.5">
                        @foreach ($tabs as $key => $tab)
                            <a href="{{ $tab['route'] }}" @click="mobileTabOpen = false"
                                @if (isset($tab['target'])) target="{{ $tab['target'] }}" @endif
                                class="flex items-center gap-2.5 px-2.5 py-2 rounded-xl text-sm font-semibold transition-all
                                       {{ $currentTab === $key ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50' }}">
                                <span
                                    class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0
                                             {{ $currentTab === $key ? 'bg-indigo-100' : 'bg-gray-100' }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        {!! $tab['icon'] !!}
                                    </svg>
                                </span>
                                <span class="flex-1">{{ $tab['label'] }}</span>
                                @if ($currentTab === $key)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-indigo-600"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- RIGHT: User Dropdown --}}
            <div class="flex items-center gap-3 shrink-0">
                <div class="relative">
                    <button type="button" @click="userMenuOpen = !userMenuOpen"
                        class="flex items-center gap-2 px-2 py-1.5 rounded-xl hover:bg-gray-100 transition">
                        <div class="text-right hidden lg:block">
                            <p class="text-sm font-semibold text-gray-800 leading-tight">{{ $user->name }}</p>
                            <p class="text-[11px] text-gray-500 leading-tight capitalize">
                                {{ $user->role }}
                                @if ($division)
                                    · {{ $division === 'cctv' ? 'CCTV' : 'Helpdesk' }}
                                @endif
                            </p>
                        </div>

                        {{-- AVATAR --}}
                        @if ($user->avatar_url)
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                                class="w-9 h-9 rounded-full object-cover border-2 border-indigo-100 shadow-sm">
                        @else
                            <div
                                class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600
                                        flex items-center justify-center text-white font-bold text-sm">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif

                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="hidden lg:block h-3 w-3 text-gray-400 transition-transform duration-200"
                            :class="userMenuOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="userMenuOpen" x-cloak @click.away="userMenuOpen = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                        class="absolute right-0 top-full mt-2 w-64 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden z-[80]">

                        {{-- User Info --}}
                        <div class="px-4 py-4 bg-gradient-to-br from-indigo-50 to-violet-50 border-b border-gray-100">
                            <div class="flex items-center gap-3">

                                @if ($user->avatar_url)
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                                        class="w-12 h-12 rounded-full object-cover border-2 border-white shadow-sm shrink-0">
                                @else
                                    <div
                                        class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600
                                                flex items-center justify-center text-white font-bold text-lg shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif

                                <div class="min-w-0 flex-1">
                                    <div class="font-semibold text-gray-800 truncate">{{ $user->name }}</div>
                                    <div class="text-xs text-gray-500 truncate">{{ $user->email }}</div>
                                    <div class="mt-1 flex gap-1">
                                        <span
                                            class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase
                                                     {{ $isAdmin ? 'bg-violet-100 text-violet-700 border border-violet-200' : 'bg-cyan-100 text-cyan-700 border border-cyan-200' }}">
                                            {{ $user->role }}
                                        </span>
                                        @if ($division)
                                            <span
                                                class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase
                                                         {{ $division === 'cctv' ? 'bg-purple-100 text-purple-700 border border-purple-200' : 'bg-teal-100 text-teal-700 border border-teal-200' }}">
                                                {{ $division === 'cctv' ? 'CCTV' : 'Helpdesk' }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- MENU ITEMS --}}
                        <div class="p-2 border-b border-gray-100 space-y-0.5">
                            {{-- Profile (semua user) --}}
                            <a href="{{ route('profile.index') }}"
                                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold
                                       text-gray-700 hover:bg-gray-50 transition">
                                <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <span>Profile Saya</span>
                            </a>
                        </div>

                        {{-- Logout --}}
                        <div class="p-2">
                            <button type="button" @click="userMenuOpen = false; showLogoutConfirm = true"
                                class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold
                                       text-red-600 hover:bg-red-50 transition">
                                <div class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-600" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                </div>
                                <span>Logout</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL LOGOUT --}}
        <div x-show="showLogoutConfirm" x-cloak
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[90] flex items-center justify-center"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" style="display: none;">
            <div class="bg-white rounded-2xl shadow-2xl p-6 w-96 relative" @click.away="showLogoutConfirm = false">
                <div class="flex justify-center mb-4">
                    <div class="w-14 h-14 rounded-full bg-red-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-red-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </div>
                </div>
                <h3 class="text-lg font-bold text-gray-900 text-center mb-1">Konfirmasi Logout</h3>
                <p class="text-sm text-gray-500 text-center mb-6">Yakin ingin keluar dari akun ini?</p>
                <div class="flex gap-2">
                    <button type="button" @click="showLogoutConfirm = false"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold transition">
                        Batal
                    </button>
                    <button type="button" @click="document.getElementById('headerLogoutForm').submit()"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold transition shadow-lg shadow-red-500/30">
                        Ya, Logout
                    </button>
                </div>
            </div>
        </div>

        {{-- HIDDEN LOGOUT FORM --}}
        <form id="headerLogoutForm" method="POST" action="{{ route('logout') }}" class="hidden">
            @csrf
        </form>
    </header>
@endauth
