@auth
    @php
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
    @endphp

    <div x-data class="fixed inset-0 z-[45] pointer-events-none">

        {{-- OVERLAY (mobile) --}}
        <div x-show="$store.sidebar.open" @click="$store.sidebar.close()" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 pointer-events-auto lg:hidden" style="display: none;">
        </div>

        {{-- SIDEBAR --}}
        <aside
            :class="{
                'translate-x-0': $store.sidebar.open,
                '-translate-x-full lg:translate-x-0': !$store.sidebar.open,
                'lg:w-64': !$store.sidebar.collapsed,
                'lg:w-16': $store.sidebar.collapsed
            }"
            class="fixed top-16 left-0 bottom-0 w-64 z-40
               bg-white border-r border-gray-200 shadow-xl
               flex flex-col
               transform transition-all duration-300 ease-out
               pointer-events-auto">

            <nav class="flex-1 p-2 space-y-0.5 overflow-y-auto">

                {{-- SECTION: DASHBOARD --}}
                <p :class="$store.sidebar.collapsed ? 'lg:hidden' : ''"
                    class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-2.5 pt-2 pb-0.5 whitespace-nowrap">
                    Dashboard
                </p>

                <a href="{{ route('dashboard') }}" :class="$store.sidebar.collapsed ? 'lg:justify-center lg:px-0' : ''"
                    class="group relative flex items-center gap-2 px-2.5 py-2 rounded-lg text-[12px] font-semibold transition-all
                      {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span
                        class="w-6 h-6 rounded-md flex items-center justify-center shrink-0
                             {{ request()->routeIs('dashboard') ? 'bg-indigo-100' : 'bg-gray-100' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                    </span>
                    <span :class="$store.sidebar.collapsed ? 'lg:hidden' : ''" class="whitespace-nowrap">
                        {{ $isAdmin ? 'Dashboard' : 'Tugas Saya' }}
                    </span>
                </a>

                {{-- SECTION: WORK ORDER --}}
                <p :class="$store.sidebar.collapsed ? 'lg:hidden' : ''"
                    class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-2.5 pt-3 pb-0.5 whitespace-nowrap">
                    Work Order
                </p>

                <a href="{{ $isAdmin ? route('admin.wo.index') : route('technician.wo.index') }}"
                    :class="$store.sidebar.collapsed ? 'lg:justify-center lg:px-0' : ''"
                    class="group relative flex items-center gap-2 px-2.5 py-2 rounded-lg text-[12px] font-semibold transition-all
                      {{ request()->routeIs('admin.wo.index', 'technician.wo.index') ? 'bg-cyan-50 text-cyan-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span
                        class="w-6 h-6 rounded-md flex items-center justify-center shrink-0
                             {{ request()->routeIs('admin.wo.index', 'technician.wo.index') ? 'bg-cyan-100' : 'bg-gray-100' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </span>
                    <span :class="$store.sidebar.collapsed ? 'lg:hidden' : ''" class="whitespace-nowrap">
                        Work Order
                    </span>
                </a>

                <a href="{{ $isAdmin ? route('admin.wo.create') : route('technician.wo.create') }}"
                    :class="$store.sidebar.collapsed ? 'lg:justify-center lg:px-0' : ''"
                    class="group relative flex items-center gap-2 px-2.5 py-2 rounded-lg text-[12px] font-semibold transition-all
                      {{ request()->routeIs('admin.wo.create', 'technician.wo.create') ? 'bg-violet-50 text-violet-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <span
                        class="w-6 h-6 rounded-md flex items-center justify-center shrink-0
                             {{ request()->routeIs('admin.wo.create', 'technician.wo.create') ? 'bg-violet-100' : 'bg-gray-100' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                    </span>
                    <span :class="$store.sidebar.collapsed ? 'lg:hidden' : ''" class="whitespace-nowrap">
                        {{ $isAdmin ? 'Buat WO' : 'Tugas Mandiri' }}
                    </span>
                </a>

                {{-- SECTION: PUBLIK --}}
                <p :class="$store.sidebar.collapsed ? 'lg:hidden' : ''"
                    class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-2.5 pt-3 pb-0.5 whitespace-nowrap">
                    Publik
                </p>

                <a href="{{ route('monitor') }}" target="_blank"
                    :class="$store.sidebar.collapsed ? 'lg:justify-center lg:px-0' : ''"
                    class="group relative flex items-center gap-2 px-2.5 py-2 rounded-lg text-[12px] font-semibold transition-all
                      text-gray-600 hover:bg-gray-50 hover:text-gray-900">
                    <span class="w-6 h-6 rounded-md flex items-center justify-center shrink-0 bg-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </span>
                    <span :class="$store.sidebar.collapsed ? 'lg:hidden' : ''" class="whitespace-nowrap">Monitor</span>
                </a>

            </nav>

            {{-- FOOTER SIDEBAR: User info --}}
            <div class="border-t border-gray-200 p-3" :class="$store.sidebar.collapsed ? 'lg:hidden' : ''">
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600
                            flex items-center justify-center text-white font-bold text-xs shrink-0">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-xs font-semibold text-gray-800 truncate">{{ $user->name }}</div>
                        <div class="text-[10px] text-gray-500 capitalize">{{ $user->role }}</div>
                    </div>
                </div>
            </div>

        </aside>
    </div>
@endauth
