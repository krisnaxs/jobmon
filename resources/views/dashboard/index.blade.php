@extends('layouts.app')

@section('title', auth()->user()->isAdmin() ? 'Dashboard' : 'Tugas Saya')

@section('content')

    @php
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
        $division = $user->division;
    @endphp

    <div class="max-w-7xl mx-auto space-y-6">

        {{-- ==================== HEADER ==================== --}}
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                    @if ($isAdmin)
                        Dashboard
                    @else
                        Tugas Saya
                        @if ($division === 'cctv')
                            <span
                                class="text-xs px-2.5 py-1 rounded-lg bg-purple-100 text-purple-700 font-bold uppercase tracking-wide">
                                Divisi CCTV
                            </span>
                        @elseif ($division === 'helpdesk')
                            <span
                                class="text-xs px-2.5 py-1 rounded-lg bg-teal-100 text-teal-700 font-bold uppercase tracking-wide">
                                Divisi Helpdesk
                            </span>
                        @endif
                    @endif
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    @if ($isAdmin)
                        Ringkasan seluruh work order dari semua divisi
                    @else
                        Daftar tugas divisi
                        <b>{{ $division === 'cctv' ? 'CCTV' : 'Helpdesk' }}</b>
                        untuk Anda
                    @endif
                </p>
            </div>

            @if ($isAdmin)
                <a href="{{ route('admin.wo.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl
                      bg-gradient-to-br from-indigo-500 to-violet-600
                      hover:from-indigo-600 hover:to-violet-700
                      text-white text-sm font-semibold
                      shadow-lg shadow-indigo-500/30 transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Buat Work Order
                </a>
            @else
                <a href="{{ route('technician.wo.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl
                      bg-gradient-to-br from-indigo-500 to-violet-600
                      hover:from-indigo-600 hover:to-violet-700
                      text-white text-sm font-semibold
                      shadow-lg shadow-indigo-500/30 transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Tugas Mandiri
                </a>
            @endif
        </div>

        {{-- ==================== TIM SAYA (TEKNISI) ==================== --}}
        @if (!$isAdmin && $shuffleEnabled && $myTeamMembership && $myTeamMembership->team)
            @php
                $myTeam = $myTeamMembership->team;
                $isCctv = $division === 'cctv';

                $cardBg = $isCctv
                    ? 'from-purple-50 to-white border-purple-200'
                    : 'from-teal-50 to-white border-teal-200';
                $iconBg = $isCctv ? 'from-purple-500 to-purple-600' : 'from-teal-500 to-teal-600';
                $labelColor = $isCctv ? 'text-purple-600' : 'text-teal-600';
                $meBg = $isCctv
                    ? 'bg-purple-100 border-purple-300 ring-2 ring-purple-200'
                    : 'bg-teal-100 border-teal-300 ring-2 ring-teal-200';
                $meAvatar = $isCctv ? 'from-purple-500 to-purple-600' : 'from-teal-500 to-teal-600';
                $infoBg = $isCctv
                    ? 'bg-purple-50 border-purple-200 text-purple-700'
                    : 'bg-teal-50 border-teal-200 text-teal-700';
            @endphp

            <div class="bg-gradient-to-br {{ $cardBg }} rounded-2xl shadow-sm border p-5">
                <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 rounded-xl bg-gradient-to-br {{ $iconBg }}
                                    flex items-center justify-center text-white shadow-lg
                                    {{ $isCctv ? 'shadow-purple-500/30' : 'shadow-teal-500/30' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs {{ $labelColor }} uppercase font-bold tracking-wide">
                                Tim Anda Bulan Ini
                            </div>
                            <div class="text-xl font-bold text-gray-800 flex items-center gap-2 flex-wrap">
                                {{ $myTeam->name }}
                                <span class="text-xs font-normal text-gray-500">
                                    · {{ now()->translatedFormat('F Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ $myTeamMembers->count() }} anggota
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    @foreach ($myTeamMembers as $member)
                        @php
                            $isMe = $member->user_id === auth()->id();
                            $memberUser = $member->user;
                            if (!$memberUser) {
                                continue;
                            }
                        @endphp
                        <div
                            class="flex items-center gap-2 px-3 py-2 rounded-lg border
                                    {{ $isMe ? $meBg : 'bg-white border-gray-200' }}">
                            @if ($memberUser->avatar_url)
                                <img src="{{ $memberUser->avatar_url }}" alt="{{ $memberUser->name }}"
                                    class="w-8 h-8 rounded-full object-cover border-2 border-white shadow-sm shrink-0">
                            @else
                                <div
                                    class="w-8 h-8 rounded-full bg-gradient-to-br
                                            {{ $isMe ? $meAvatar : 'from-gray-400 to-gray-500' }}
                                            flex items-center justify-center text-white font-bold text-sm shrink-0">
                                    {{ strtoupper(substr($memberUser->name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <div class="text-sm font-semibold text-gray-800">
                                    {{ $memberUser->name }}
                                    @if ($isMe)
                                        <span class="text-[10px] {{ $labelColor }} font-bold">(Anda)</span>
                                    @endif
                                </div>
                                <div class="text-[10px] text-gray-400">{{ $memberUser->username }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 {{ $infoBg }} border rounded-xl p-2.5 flex items-start gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0 mt-0.5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-[11px]">
                        <b>Info:</b> Tim di-rotate otomatis setiap awal bulan.
                    </div>
                </div>
            </div>
        @endif

        {{-- ==================== FILTER BAR ==================== --}}
        <div
            class="bg-white rounded-2xl shadow-sm border border-gray-100 px-4 py-3
                    flex flex-wrap items-center justify-between gap-3">

            @if ($isAdmin)
                <div class="flex gap-1 overflow-x-auto pb-1 md:pb-0 w-full md:w-auto custom-scrollbar">
                    <button onclick="setDivisionFilter('all')" data-divfilter="all"
                        class="divfilter-btn px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-600 text-white transition whitespace-nowrap">
                        Semua
                    </button>
                    <button onclick="setDivisionFilter('cctv')" data-divfilter="cctv"
                        class="divfilter-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-purple-700 hover:bg-purple-50 border border-purple-200 transition whitespace-nowrap">
                        📹 CCTV
                    </button>
                    <button onclick="setDivisionFilter('helpdesk')" data-divfilter="helpdesk"
                        class="divfilter-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-teal-700 hover:bg-teal-50 border border-teal-200 transition whitespace-nowrap">
                        🎧 Helpdesk
                    </button>
                </div>
            @else
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <span class="font-semibold">Divisi:</span>
                    <span
                        class="px-2.5 py-1 rounded-lg font-bold uppercase tracking-wide
            {{ $division === 'cctv' ? 'bg-purple-100 text-purple-700' : 'bg-teal-100 text-teal-700' }}">
                        {{ $division === 'cctv' ? '📹 CCTV' : '🎧 Helpdesk' }}
                    </span>
                </div>
            @endif

            <div class="flex gap-1 items-center flex-wrap">
                <button onclick="setRangePreset('today')" data-range="today"
                    class="range-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:bg-gray-100 border border-gray-200 transition">
                    Hari Ini
                </button>
                <button onclick="setRangePreset('week')" data-range="week"
                    class="range-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:bg-gray-100 border border-gray-200 transition">
                    7 Hari
                </button>
                <button onclick="setRangePreset('month')" data-range="month"
                    class="range-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:bg-gray-100 border border-gray-200 transition">
                    30 Hari
                </button>
                <button onclick="setRangePreset('all')" data-range="all"
                    class="range-btn px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-600 text-white transition">
                    Semua
                </button>

                <div class="relative" id="customRangeWrapper">
                    <button onclick="toggleCustomPicker(event)"
                        class="range-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:bg-gray-100 border border-gray-200 transition flex items-center gap-1">
                        📅 <span id="customRangeLabel">Custom</span>
                    </button>

                    <div id="customPickerDropdown"
                        class="hidden absolute right-0 top-full mt-2 bg-white border border-gray-200
                               rounded-xl shadow-xl p-3 w-72 z-[120]">
                        <div class="space-y-2">
                            <div>
                                <label class="block text-[10px] uppercase tracking-wider text-gray-500 font-bold mb-1">
                                    Dari Tanggal
                                </label>
                                <input type="date" id="customFrom"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs
                                           focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-[10px] uppercase tracking-wider text-gray-500 font-bold mb-1">
                                    Sampai Tanggal
                                </label>
                                <input type="date" id="customTo"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs
                                           focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                            </div>
                            <div id="customError" class="hidden text-[10px] text-red-500"></div>
                            <div class="flex gap-2 pt-1">
                                <button onclick="closeCustomPicker()"
                                    class="flex-1 px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200
                                           text-gray-600 text-[11px] font-semibold transition">
                                    Batal
                                </button>
                                <button onclick="applyCustomRange()"
                                    class="flex-1 px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700
                                           text-white text-[11px] font-semibold transition">
                                    Terapkan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== STATS PER DIVISI (ADMIN) ==================== --}}
        @if ($isAdmin)
            <div id="statsByDivision" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 h-32 animate-pulse"></div>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 h-32 animate-pulse"></div>
            </div>
        @endif

        {{-- ==================== GRAFIK WO BULANAN (ADMIN ONLY) ==================== --}}
        @if ($isAdmin)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                    <div>
                        <h2 class="font-bold text-gray-800 text-base flex items-center gap-2 flex-wrap">
                            Grafik Work Order Bulanan
                            <span
                                class="text-[10px] px-2 py-0.5 rounded-lg font-bold uppercase tracking-wide
                        bg-indigo-100 text-indigo-700">
                                Semua Divisi
                            </span>
                        </h2>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Statistik semua WO per bulan
                        </p>
                    </div>
                    <div class="flex gap-1 text-xs">
                        <button onclick="setMonthlyRange(6)" data-rangebtn="6"
                            class="range-month-btn px-3 py-1.5 rounded-lg text-gray-600 hover:bg-gray-100 font-semibold border border-gray-200 transition">
                            6 Bulan
                        </button>
                        <button onclick="setMonthlyRange(12)" data-rangebtn="12"
                            class="range-month-btn px-3 py-1.5 rounded-lg bg-indigo-600 text-white font-semibold transition">
                            12 Bulan
                        </button>
                    </div>
                </div>

                <div class="relative h-72">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>
        @endif

        {{-- ==================== STATS CARDS GLOBAL ==================== --}}
        <div id="statsCard" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
            @for ($i = 0; $i < 6; $i++)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 h-24 animate-pulse"></div>
            @endfor
        </div>

        {{-- ==================== REKAP PER TEKNISI (ADMIN) ==================== --}}
        @if ($isAdmin)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center flex-wrap gap-3">
                    <div>
                        <h2 class="font-semibold text-gray-800">Rekap Per Teknisi</h2>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Kontribusi tiap teknisi terhadap total pekerjaan divisinya
                            · <span id="rekapRangeLabel" class="text-indigo-600 font-semibold">Semua Waktu</span>
                            · <span id="rekapDivLabel" class="text-indigo-600 font-semibold">Semua Divisi</span>
                        </p>
                    </div>

                    <div class="flex gap-1 text-xs" id="rekapFilter">
                        <button onclick="setRekapFilter('all')" data-rf="all"
                            class="rekap-btn px-3 py-1.5 rounded-lg bg-indigo-600 text-white font-semibold">Semua</button>
                        <button onclick="setRekapFilter('cctv')" data-rf="cctv"
                            class="rekap-btn px-3 py-1.5 rounded-lg text-gray-600 hover:bg-gray-100 font-semibold">
                            📹 CCTV
                        </button>
                        <button onclick="setRekapFilter('helpdesk')" data-rf="helpdesk"
                            class="rekap-btn px-3 py-1.5 rounded-lg text-gray-600 hover:bg-gray-100 font-semibold">
                            🎧 Helpdesk
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="py-3 px-5 font-semibold text-left">Teknisi</th>
                                <th class="py-3 px-3 font-semibold text-center">Divisi</th>
                                <th class="py-3 px-3 font-semibold text-center">Total</th>
                                <th class="py-3 px-3 font-semibold text-center">Selesai</th>
                                <th class="py-3 px-3 font-semibold text-center">Proses</th>
                                <th class="py-3 px-3 font-semibold text-center">Pending</th>
                                <th class="py-3 px-3 font-semibold text-center text-rose-600">⊘ Skip</th>
                                <th class="py-3 px-5 font-semibold w-1/4">Kontribusi Divisi</th>
                            </tr>
                        </thead>
                        <tbody id="rekapBody" class="divide-y divide-gray-100">
                            <tr>
                                <td colspan="8" class="text-center py-6 text-gray-400 text-sm">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- ==================== LIST WORK ORDER ==================== --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center flex-wrap gap-3">
                <div>
                    <h2 class="font-semibold text-gray-800">
                        @if ($isAdmin)
                            Semua Work Order
                        @else
                            Daftar Tugas
                        @endif
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Auto-refresh
                    </p>
                </div>

                <div class="space-y-2">
                    <div class="flex gap-1 text-xs flex-wrap">
                        <button onclick="setFilter('all')" data-filter="all"
                            class="filter-btn px-3 py-1.5 rounded-lg bg-indigo-600 text-white font-semibold">Semua</button>
                        <button onclick="setFilter('pending')" data-filter="pending"
                            class="filter-btn px-3 py-1.5 rounded-lg text-gray-600 hover:bg-gray-100 font-semibold">Pending</button>
                        <button onclick="setFilter('in_progress')" data-filter="in_progress"
                            class="filter-btn px-3 py-1.5 rounded-lg text-gray-600 hover:bg-gray-100 font-semibold">Proses</button>
                        <button onclick="setFilter('completed')" data-filter="completed"
                            class="filter-btn px-3 py-1.5 rounded-lg text-gray-600 hover:bg-gray-100 font-semibold">Selesai</button>

                        {{-- Filter Skip (khusus teknisi) --}}
                        @if (!$isAdmin)
                            <button onclick="setFilter('skipped')" data-filter="skipped"
                                class="filter-btn px-3 py-1.5 rounded-lg text-rose-600 hover:bg-rose-50 font-semibold">
                                ⊘ Skip
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- BUNGKUS DENGAN OVERFLOW-X-AUTO UNTUK MOBILE --}}
            <div class="w-full overflow-x-auto pb-2 custom-scrollbar">
                {{-- 6 KOLOM GRID KAKU (Hanya aktif di Desktop) --}}
                <div id="woMainGrid"
                    class="p-3 grid gap-3 min-w-[1200px] {{ $isAdmin ? 'grid-cols-6' : 'grid-cols-3' }}">

                    @if ($isAdmin || $division === 'cctv')
                        <div id="cctv-group" class="contents">
                            <div class="flex flex-col bg-gray-50/60 border border-gray-100 rounded-xl overflow-hidden">
                                <div
                                    class="shrink-0 px-3 py-2 border-b border-gray-100 flex justify-between items-center bg-purple-50">
                                    <h3
                                        class="text-[10px] uppercase tracking-wider text-purple-700 font-bold flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        CCTV · Pending
                                    </h3>
                                    <span class="text-[10px] text-gray-500 font-mono" id="countCctvPending">—</span>
                                </div>
                                <div id="gridCctvPending"
                                    class="flex-1 max-h-96 overflow-y-auto p-2 space-y-1.5 custom-scrollbar">
                                    <div class="text-center py-4 text-gray-400 text-[10px]">Memuat...</div>
                                </div>
                            </div>

                            <div class="flex flex-col bg-gray-50/60 border border-gray-100 rounded-xl overflow-hidden">
                                <div
                                    class="shrink-0 px-3 py-2 border-b border-gray-100 flex justify-between items-center bg-purple-50">
                                    <h3
                                        class="text-[10px] uppercase tracking-wider text-purple-700 font-bold flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        CCTV · Proses
                                    </h3>
                                    <span class="text-[10px] text-gray-500 font-mono" id="countCctvProgress">—</span>
                                </div>
                                <div id="gridCctvProgress"
                                    class="flex-1 max-h-96 overflow-y-auto p-2 space-y-1.5 custom-scrollbar">
                                    <div class="text-center py-4 text-gray-400 text-[10px]">Memuat...</div>
                                </div>
                            </div>

                            <div class="flex flex-col bg-gray-50/60 border border-gray-100 rounded-xl overflow-hidden">
                                <div
                                    class="shrink-0 px-3 py-2 border-b border-gray-100 flex justify-between items-center bg-purple-50">
                                    <h3
                                        class="text-[10px] uppercase tracking-wider text-purple-700 font-bold flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        CCTV · Selesai
                                    </h3>
                                    <span class="text-[10px] text-gray-500 font-mono" id="countCctvCompleted">—</span>
                                </div>
                                <div id="gridCctvCompleted"
                                    class="flex-1 max-h-96 overflow-y-auto p-2 space-y-1.5 custom-scrollbar">
                                    <div class="text-center py-4 text-gray-400 text-[10px]">Memuat...</div>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($isAdmin || $division === 'helpdesk')
                        <div id="helpdesk-group" class="contents">
                            <div class="flex flex-col bg-gray-50/60 border border-gray-100 rounded-xl overflow-hidden">
                                <div
                                    class="shrink-0 px-3 py-2 border-b border-gray-100 flex justify-between items-center bg-teal-50">
                                    <h3
                                        class="text-[10px] uppercase tracking-wider text-teal-700 font-bold flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Helpdesk · Pending
                                    </h3>
                                    <span class="text-[10px] text-gray-500 font-mono" id="countHdPending">—</span>
                                </div>
                                <div id="gridHdPending"
                                    class="flex-1 max-h-96 overflow-y-auto p-2 space-y-1.5 custom-scrollbar">
                                    <div class="text-center py-4 text-gray-400 text-[10px]">Memuat...</div>
                                </div>
                            </div>

                            <div class="flex flex-col bg-gray-50/60 border border-gray-100 rounded-xl overflow-hidden">
                                <div
                                    class="shrink-0 px-3 py-2 border-b border-gray-100 flex justify-between items-center bg-teal-50">
                                    <h3
                                        class="text-[10px] uppercase tracking-wider text-teal-700 font-bold flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        Helpdesk · Proses
                                    </h3>
                                    <span class="text-[10px] text-gray-500 font-mono" id="countHdProgress">—</span>
                                </div>
                                <div id="gridHdProgress"
                                    class="flex-1 max-h-96 overflow-y-auto p-2 space-y-1.5 custom-scrollbar">
                                    <div class="text-center py-4 text-gray-400 text-[10px]">Memuat...</div>
                                </div>
                            </div>

                            <div class="flex flex-col bg-gray-50/60 border border-gray-100 rounded-xl overflow-hidden">
                                <div
                                    class="shrink-0 px-3 py-2 border-b border-gray-100 flex justify-between items-center bg-teal-50">
                                    <h3
                                        class="text-[10px] uppercase tracking-wider text-teal-700 font-bold flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Helpdesk · Selesai
                                    </h3>
                                    <span class="text-[10px] text-gray-500 font-mono" id="countHdCompleted">—</span>
                                </div>
                                <div id="gridHdCompleted"
                                    class="flex-1 max-h-96 overflow-y-auto p-2 space-y-1.5 custom-scrollbar">
                                    <div class="text-center py-4 text-gray-400 text-[10px]">Memuat...</div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

@endsection

@push('styles')
    <style>
        /* Custom Scrollbar untuk area Work Order di mobile */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
@endpush

@push('scripts')
    <script>
        const IS_ADMIN = {{ $isAdmin ? 'true' : 'false' }};
        const MY_DIVISION = @json($division);

        let currentFilter = 'all';
        let currentRekapFilter = 'all';
        let currentDivisionFilter = 'all';
        let currentRange = 'all';
        let customFrom = null;
        let customTo = null;

        let allWorkOrders = [];
        let allTechnicians = [];

        let monthlyChart = null;
        let currentMonths = 12;
        const divisionStyle = {
            'cctv': {
                label: 'CCTV',
                bg: 'bg-purple-50',
                text: 'text-purple-700',
                border: 'border-purple-200',
                dot: 'bg-purple-500'
            },
            'helpdesk': {
                label: 'Helpdesk',
                bg: 'bg-teal-50',
                text: 'text-teal-700',
                border: 'border-teal-200',
                dot: 'bg-teal-500'
            },
        };

        function divisionBadge(division) {
            const s = divisionStyle[division] || {
                label: division || '-',
                bg: 'bg-gray-50',
                text: 'text-gray-600',
                border: 'border-gray-200',
                dot: 'bg-gray-400'
            };
            return `<span class="inline-flex items-center gap-1 text-[9px] uppercase tracking-wide font-bold
                             px-1.5 py-0.5 rounded ${s.bg} ${s.text} border ${s.border}">
                        <span class="w-1 h-1 rounded-full ${s.dot}"></span>
                        ${s.label}
                    </span>`;
        }

        function buildWoQuery() {
            let qs = `?range=${currentRange}`;
            if (currentRange === 'custom' && customFrom && customTo) {
                qs += `&from=${customFrom}&to=${customTo}`;
            }
            return qs;
        }

        function buildStatsQuery() {
            let qs = `?range=${currentRange}`;
            if (currentRange === 'custom' && customFrom && customTo) {
                qs += `&from=${customFrom}&to=${customTo}`;
            }
            if (IS_ADMIN && currentRekapFilter !== 'all') {
                qs += `&division=${currentRekapFilter}`;
            }
            return qs;
        }

        function styleDivFilterButtons(active) {
            document.querySelectorAll('.divfilter-btn').forEach(btn => {
                const isActive = btn.dataset.divfilter === active;
                if (isActive) {
                    if (active === 'cctv') {
                        btn.className =
                            'divfilter-btn px-3 py-1.5 rounded-lg text-xs font-semibold bg-purple-600 text-white transition';
                    } else if (active === 'helpdesk') {
                        btn.className =
                            'divfilter-btn px-3 py-1.5 rounded-lg text-xs font-semibold bg-teal-600 text-white transition';
                    } else {
                        btn.className =
                            'divfilter-btn px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-600 text-white transition';
                    }
                } else {
                    if (btn.dataset.divfilter === 'cctv') {
                        btn.className =
                            'divfilter-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-purple-700 hover:bg-purple-50 border border-purple-200 transition';
                    } else if (btn.dataset.divfilter === 'helpdesk') {
                        btn.className =
                            'divfilter-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-teal-700 hover:bg-teal-50 border border-teal-200 transition';
                    } else {
                        btn.className =
                            'divfilter-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:bg-gray-100 border border-gray-200 transition';
                    }
                }
            });
        }

        function styleRekapButtons(active) {
            document.querySelectorAll('.rekap-btn').forEach(btn => {
                const isActive = btn.dataset.rf === active;
                if (isActive) {
                    if (active === 'cctv') {
                        btn.className =
                            'rekap-btn px-3 py-1.5 rounded-lg bg-purple-600 text-white font-semibold';
                    } else if (active === 'helpdesk') {
                        btn.className =
                            'rekap-btn px-3 py-1.5 rounded-lg bg-teal-600 text-white font-semibold';
                    } else {
                        btn.className =
                            'rekap-btn px-3 py-1.5 rounded-lg bg-indigo-600 text-white font-semibold';
                    }
                } else {
                    btn.className =
                        'rekap-btn px-3 py-1.5 rounded-lg text-gray-600 hover:bg-gray-100 font-semibold';
                }
            });
        }

        function setDivisionFilter(division) {
            currentDivisionFilter = division;
            currentRekapFilter = division; //  SINKRON

            styleDivFilterButtons(division);
            styleRekapButtons(division);

            applyDivisionVisibility();
            renderWorkOrders();
            renderRekap();
            loadStats(); //  re-fetch stats dengan filter divisi
        }

        function applyDivisionVisibility() {
            const cctvGroup = document.getElementById('cctv-group');
            const hdGroup = document.getElementById('helpdesk-group');
            const mainGrid = document.getElementById('woMainGrid');

            if (!cctvGroup || !hdGroup || !mainGrid) return;

            if (currentDivisionFilter === 'cctv') {
                cctvGroup.style.display = 'contents';
                hdGroup.style.display = 'none';
                mainGrid.style.gridTemplateColumns = 'repeat(3, minmax(0, 1fr))';
                mainGrid.style.minWidth = '600px'; // Menjaga lebar di mobile
            } else if (currentDivisionFilter === 'helpdesk') {
                cctvGroup.style.display = 'none';
                hdGroup.style.display = 'contents';
                mainGrid.style.gridTemplateColumns = 'repeat(3, minmax(0, 1fr))';
                mainGrid.style.minWidth = '600px'; // Menjaga lebar di mobile
            } else {
                cctvGroup.style.display = 'contents';
                hdGroup.style.display = 'contents';
                mainGrid.style.gridTemplateColumns = 'repeat(6, minmax(0, 1fr))';
                mainGrid.style.minWidth = '1200px'; // Menjaga lebar di mobile
            }
        }

        function setRangePreset(range) {
            currentRange = range;
            customFrom = null;
            customTo = null;

            document.querySelectorAll('.range-btn').forEach(btn => {
                if (btn.dataset.range === range) {
                    btn.className =
                        'range-btn px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-600 text-white transition';
                } else {
                    btn.className =
                        'range-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:bg-gray-100 border border-gray-200 transition';
                }
            });

            const customLabel = document.getElementById('customRangeLabel');
            if (customLabel) customLabel.textContent = 'Custom';

            closeCustomPicker();
            refresh();
        }

        function toggleCustomPicker(event) {
            event.stopPropagation();
            const dropdown = document.getElementById('customPickerDropdown');
            dropdown.classList.toggle('hidden');
        }

        function closeCustomPicker() {
            const dropdown = document.getElementById('customPickerDropdown');
            if (dropdown) dropdown.classList.add('hidden');

            const err = document.getElementById('customError');
            if (err) err.classList.add('hidden');
        }

        function applyCustomRange() {
            const from = document.getElementById('customFrom').value;
            const to = document.getElementById('customTo').value;
            const errEl = document.getElementById('customError');

            if (!from || !to) {
                errEl.textContent = 'Isi kedua tanggal';
                errEl.classList.remove('hidden');
                return;
            }

            if (new Date(from) > new Date(to)) {
                errEl.textContent = 'Tanggal "Dari" harus ≤ "Sampai"';
                errEl.classList.remove('hidden');
                return;
            }

            customFrom = from;
            customTo = to;
            currentRange = 'custom';

            document.querySelectorAll('.range-btn').forEach(btn => {
                btn.className =
                    'range-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:bg-gray-100 border border-gray-200 transition';
            });

            const customBtn = document.querySelector('#customRangeWrapper .range-btn');
            if (customBtn) {
                customBtn.className =
                    'range-btn px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-600 text-white transition flex items-center gap-1';
            }

            const label = document.getElementById('customRangeLabel');
            if (label) {
                const df = new Date(from).toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'short'
                });
                const dt = new Date(to).toLocaleDateString('id-ID', {
                    day: '2-digit',
                    month: 'short'
                });
                label.textContent = `${df} — ${dt}`;
            }

            closeCustomPicker();
            refresh();
        }

        document.addEventListener('click', function(e) {
            const wrapper = document.getElementById('customRangeWrapper');
            if (wrapper && !wrapper.contains(e.target)) {
                closeCustomPicker();
            }
        });
        async function loadStats() {
            try {
                const d = await api.get("{{ route('dashboard.stats') }}" + buildStatsQuery());

                if (IS_ADMIN) {
                    document.getElementById('statsCard').innerHTML = `
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
                            <div class="flex items-center justify-between">
                                <div class="text-[10px] text-gray-500 uppercase tracking-wide font-semibold">Total</div>
                                <div class="w-7 h-7 rounded-lg bg-indigo-50 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                            </div>
                            <div class="text-2xl font-bold text-gray-800 mt-2">${d.total}</div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
                            <div class="flex items-center justify-between">
                                <div class="text-[10px] text-gray-500 uppercase tracking-wide font-semibold">Selesai</div>
                                <div class="w-7 h-7 rounded-lg bg-emerald-50 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="text-2xl font-bold text-emerald-600 mt-2">${d.completed}</div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
                            <div class="flex items-center justify-between">
                                <div class="text-[10px] text-gray-500 uppercase tracking-wide font-semibold">Proses</div>
                                <div class="w-7 h-7 rounded-lg bg-amber-50 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="text-2xl font-bold text-amber-600 mt-2">${d.progress}</div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
                            <div class="flex items-center justify-between">
                                <div class="text-[10px] text-gray-500 uppercase tracking-wide font-semibold">Pending</div>
                                <div class="w-7 h-7 rounded-lg bg-gray-100 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="text-2xl font-bold text-gray-500 mt-2">${d.pending}</div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
                            <div class="flex items-center justify-between">
                                <div class="text-[10px] text-gray-500 uppercase tracking-wide font-semibold">CCTV</div>
                                <div class="w-7 h-7 rounded-lg bg-purple-50 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="text-2xl font-bold text-purple-600 mt-2">${d.perDivision.cctv.total}</div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
                            <div class="flex items-center justify-between">
                                <div class="text-[10px] text-gray-500 uppercase tracking-wide font-semibold">Helpdesk</div>
                                <div class="w-7 h-7 rounded-lg bg-teal-50 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="text-2xl font-bold text-teal-600 mt-2">${d.perDivision.helpdesk.total}</div>
                        </div>
                    `;

                    if (d.perDivision) {
                        const cctv = d.perDivision.cctv;
                        const hd = d.perDivision.helpdesk;

                        document.getElementById('statsByDivision').innerHTML = `
                            <div class="bg-gradient-to-br from-purple-50 to-purple-100/50 rounded-2xl border border-purple-200 p-5">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-9 h-9 rounded-xl bg-purple-500 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="font-bold text-purple-900">Divisi CCTV</div>
                                            <div class="text-xs text-purple-600">Total ${cctv.total} pekerjaan</div>
                                        </div>
                                    </div>
                                    <div class="text-3xl font-bold text-purple-700">${cctv.total}</div>
                                </div>
                                <div class="grid grid-cols-3 gap-2 text-center text-xs">
                                    <div class="bg-white/60 rounded-lg py-2">
                                        <div class="text-purple-700 font-bold text-lg">${cctv.completed}</div>
                                        <div class="text-purple-500">Selesai</div>
                                    </div>
                                    <div class="bg-white/60 rounded-lg py-2">
                                        <div class="text-purple-700 font-bold text-lg">${cctv.progress}</div>
                                        <div class="text-purple-500">Proses</div>
                                    </div>
                                    <div class="bg-white/60 rounded-lg py-2">
                                        <div class="text-purple-700 font-bold text-lg">${cctv.pending}</div>
                                        <div class="text-purple-500">Pending</div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gradient-to-br from-teal-50 to-teal-100/50 rounded-2xl border border-teal-200 p-5">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-9 h-9 rounded-xl bg-teal-500 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <div class="font-bold text-teal-900">Divisi Helpdesk</div>
                                            <div class="text-xs text-teal-600">Total ${hd.total} pekerjaan</div>
                                        </div>
                                    </div>
                                    <div class="text-3xl font-bold text-teal-700">${hd.total}</div>
                                </div>
                                <div class="grid grid-cols-3 gap-2 text-center text-xs">
                                    <div class="bg-white/60 rounded-lg py-2">
                                        <div class="text-teal-700 font-bold text-lg">${hd.completed}</div>
                                        <div class="text-teal-500">Selesai</div>
                                    </div>
                                    <div class="bg-white/60 rounded-lg py-2">
                                        <div class="text-teal-700 font-bold text-lg">${hd.progress}</div>
                                        <div class="text-teal-500">Proses</div>
                                    </div>
                                    <div class="bg-white/60 rounded-lg py-2">
                                        <div class="text-teal-700 font-bold text-lg">${hd.pending}</div>
                                        <div class="text-teal-500">Pending</div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }

                    if (d.perTechnician) {
                        allTechnicians = d.perTechnician;
                        renderRekap();
                    }

                    const rekapRangeLabel = document.getElementById('rekapRangeLabel');
                    if (rekapRangeLabel && d.rangeLabel) {
                        rekapRangeLabel.textContent = d.rangeLabel;
                    }
                } else {
                    const hasSkip = d.no_participation !== undefined && d.no_participation > 0;

                    document.getElementById('statsCard').innerHTML = `
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
                            <div class="flex items-center justify-between">
                                <div class="text-[10px] text-gray-500 uppercase tracking-wide font-semibold">Total</div>
                                <div class="w-7 h-7 rounded-lg bg-indigo-50 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                            </div>
                            <div class="text-2xl font-bold text-gray-800 mt-2">${d.total}</div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
                            <div class="flex items-center justify-between">
                                <div class="text-[10px] text-gray-500 uppercase tracking-wide font-semibold">Selesai</div>
                                <div class="w-7 h-7 rounded-lg bg-emerald-50 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="text-2xl font-bold text-emerald-600 mt-2">${d.completed}</div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
                            <div class="flex items-center justify-between">
                                <div class="text-[10px] text-gray-500 uppercase tracking-wide font-semibold">Proses</div>
                                <div class="w-7 h-7 rounded-lg bg-amber-50 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="text-2xl font-bold text-amber-600 mt-2">${d.progress}</div>
                        </div>

                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 hover:shadow-md transition">
                            <div class="flex items-center justify-between">
                                <div class="text-[10px] text-gray-500 uppercase tracking-wide font-semibold">Pending</div>
                                <div class="w-7 h-7 rounded-lg bg-gray-100 flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="text-2xl font-bold text-gray-500 mt-2">${d.pending}</div>
                        </div>

                        ${hasSkip ? `
                                                                <div class="bg-white rounded-xl shadow-sm border border-rose-100 p-4 hover:shadow-md transition">
                                                                    <div class="flex items-center justify-between">
                                                                        <div class="text-[10px] text-rose-500 uppercase tracking-wide font-semibold">Skip</div>
                                                                        <div class="w-7 h-7 rounded-lg bg-rose-50 flex items-center justify-center">
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                                            </svg>
                                                                        </div>
                                                                    </div>
                                                                    <div class="text-2xl font-bold text-rose-600 mt-2">${d.no_participation}</div>
                                                                </div>
                                                            ` : ''}
                    `;
                }
            } catch (err) {
                console.error('Stats error:', err);
            }
        }

        function setRekapFilter(filter) {
            currentRekapFilter = filter;
            currentDivisionFilter = filter; //  SINKRON

            styleRekapButtons(filter);
            styleDivFilterButtons(filter);

            applyDivisionVisibility();
            renderRekap();
            renderWorkOrders();
            loadStats(); //  re-fetch stats dengan filter divisi
        }

        function renderRekap() {
            const rekapEl = document.getElementById('rekapBody');
            if (!rekapEl) return;
            const divLabel = document.getElementById('rekapDivLabel');
            if (divLabel) {
                const divText = currentRekapFilter === 'all' ?
                    'Semua Divisi' :
                    (currentRekapFilter === 'cctv' ? '📹 CCTV' : '🎧 Helpdesk');
                divLabel.textContent = divText;

                if (currentRekapFilter === 'cctv') {
                    divLabel.className = 'text-purple-600 font-semibold';
                } else if (currentRekapFilter === 'helpdesk') {
                    divLabel.className = 'text-teal-600 font-semibold';
                } else {
                    divLabel.className = 'text-indigo-600 font-semibold';
                }
            }
            let list = allTechnicians;
            if (currentRekapFilter !== 'all') {
                list = list.filter(t => t.division === currentRekapFilter);
            }

            if (!list.length) {
                rekapEl.innerHTML =
                    '<tr><td colspan="8" class="text-center py-6 text-gray-400 text-sm">Belum ada teknisi.</td></tr>';
                return;
            }

            rekapEl.innerHTML = list.map(t => {
                const participating = t.participating_wo ?? t.total_wo;
                const pct = t.progress_pct ?? 0;
                const noPart = t.no_participation_wo ?? 0;

                const barColor = t.division === 'cctv' ?
                    'from-purple-500 to-purple-400' :
                    t.division === 'helpdesk' ?
                    'from-teal-500 to-teal-400' :
                    'from-emerald-500 to-emerald-400';

                const textColor = t.division === 'cctv' ?
                    'text-purple-700' :
                    t.division === 'helpdesk' ?
                    'text-teal-700' :
                    'text-gray-600';

                const isCctv = t.division === 'cctv';
                const teamBadgeCls = isCctv ?
                    'bg-purple-50 text-purple-700 border-purple-200' :
                    'bg-teal-50 text-teal-700 border-teal-200';

                const teamBadge = t.team_name ?
                    `<span class="inline-flex items-center gap-0.5 text-[9px] font-bold uppercase tracking-wide
                             px-1.5 py-0.5 rounded border ${teamBadgeCls} shrink-0">
                   <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24"
                       stroke="currentColor" stroke-width="2.5">
                       <path stroke-linecap="round" stroke-linejoin="round"
                           d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                   </svg>
                   ${t.team_name}
               </span>` :
                    '';

                const skipBadge = noPart > 0 ?
                    `<span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-rose-50 text-rose-700 font-bold text-sm"
                           title="${noPart} WO tidak diikuti">
                        ${noPart}
                    </span>` :
                    `<span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-50 text-gray-300 font-bold text-sm">
                        —
                    </span>`;

                return `
                    <tr class="hover:bg-indigo-50/30 transition">
                        <td class="py-3 px-5">
                            <div class="flex items-center gap-3 flex-wrap">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600
                                            flex items-center justify-center text-white font-bold text-xs shrink-0">
                                    ${t.name.charAt(0).toUpperCase()}
                                </div>
                                <span class="text-gray-800 font-medium">${t.name}</span>
                                ${teamBadge}
                            </div>
                        </td>
                        <td class="py-3 px-3 text-center">${divisionBadge(t.division)}</td>
                        <td class="py-3 px-3 text-center text-gray-700 font-semibold">${t.total_wo}</td>
                        <td class="py-3 px-3 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 font-bold text-sm">
                                ${t.completed_wo}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 text-amber-700 font-bold text-sm">
                                ${t.progress_wo}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-600 font-bold text-sm">
                                ${t.pending_wo}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-center">
                            ${skipBadge}
                        </td>
                        <td class="py-3 px-5">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                                    <div class="bg-gradient-to-r ${barColor} h-2 rounded-full transition-all"
                                         style="width:${pct}%"></div>
                                </div>
                                <span class="text-xs ${textColor} font-semibold tabular-nums w-14 text-right">
                                    ${pct}%
                                </span>
                            </div>
                            <div class="text-[10px] text-gray-400 mt-0.5">
                                ${t.completed_wo} dari ${participating} partisipasi
                                ${noPart > 0 ? `<span class="text-rose-400">· ${noPart} skip</span>` : ''}
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }
        async function loadWorkOrders() {
            try {
                const url = IS_ADMIN ?
                    "{{ route('admin.wo.data') }}" :
                    "{{ route('technician.wo.data') }}";

                allWorkOrders = await api.get(url + buildWoQuery());
                renderWorkOrders();
            } catch (err) {
                console.error('WO error:', err);
            }
        }

        function setFilter(status) {
            currentFilter = status;
            document.querySelectorAll('.filter-btn').forEach(btn => {
                const isSkipBtn = btn.dataset.filter === 'skipped';
                const isActive = btn.dataset.filter === status;

                if (isActive) {
                    if (isSkipBtn) {
                        btn.className =
                            'filter-btn px-3 py-1.5 rounded-lg bg-rose-600 text-white font-semibold';
                    } else {
                        btn.className =
                            'filter-btn px-3 py-1.5 rounded-lg bg-indigo-600 text-white font-semibold';
                    }
                } else {
                    if (isSkipBtn) {
                        btn.className =
                            'filter-btn px-3 py-1.5 rounded-lg text-rose-600 hover:bg-rose-50 font-semibold';
                    } else {
                        btn.className =
                            'filter-btn px-3 py-1.5 rounded-lg text-gray-600 hover:bg-gray-100 font-semibold';
                    }
                }
            });
            renderWorkOrders();
        }

        function renderWoTechnicians(w) {
            const list = w.technicians || [];
            if (!list.length) {
                return '<span class="text-gray-400 italic text-[9px]">Belum</span>';
            }

            const participating = list.filter(t => t.pivot_no_participation !== true);
            const skipped = list.filter(t => t.pivot_no_participation === true);

            const isCctv = w.division === 'cctv';
            const badgeCls = isCctv ?
                'bg-purple-100 text-purple-700 border-purple-200' :
                'bg-teal-100 text-teal-700 border-teal-200';

            if (participating.length === 0) {
                return `<span class="text-[9px] text-rose-500 font-semibold"
                              title="${skipped.map(t => t.name).join(', ')}">
                            ⊘ ${skipped.length} teknisi skip
                        </span>`;
            }

            if (participating.length === 1) {
                const t = participating[0];
                const teamBadge = t.team_name ?
                    `<span class="inline-flex items-center gap-0.5 text-[8px] font-bold uppercase tracking-wide
                             px-1 py-0.5 rounded border ${badgeCls} shrink-0">
                   ${t.team_name}
               </span>` :
                    '';

                const skipMini = skipped.length > 0 ?
                    `<span class="text-[8px] text-rose-500 font-bold shrink-0"
                               title="${skipped.map(t => t.name).join(', ')}">⊘${skipped.length}</span>` :
                    '';

                return `<div class="flex items-center gap-1 min-w-0">
                    <span class="text-gray-500 font-medium truncate max-w-[60px]">👤 ${t.name.split(' ')[0]}</span>
                    ${teamBadge}
                    ${skipMini}
                </div>`;
            }

            const teamNames = [...new Set(participating.map(t => t.team_name).filter(Boolean))];
            const skipMini = skipped.length > 0 ?
                `<span class="text-[8px] text-rose-500 font-bold shrink-0"
                           title="${skipped.map(t => t.name).join(', ')}">⊘${skipped.length}</span>` :
                '';

            if (teamNames.length === 1) {
                return `<div class="flex items-center gap-1 min-w-0">
                    <span class="text-gray-500 font-medium truncate max-w-[50px]"
                          title="${participating.map(t => t.name).join(', ')}">👥 ${participating.length} org</span>
                    <span class="inline-flex items-center gap-0.5 text-[8px] font-bold uppercase tracking-wide
                                 px-1 py-0.5 rounded border ${badgeCls} shrink-0">
                        ${teamNames[0]}
                    </span>
                    ${skipMini}
                </div>`;
            }

            if (teamNames.length > 1) {
                return `<div class="flex items-center gap-1 min-w-0">
                    <span class="text-gray-500 font-medium truncate max-w-[50px]"
                          title="${participating.map(t => t.name).join(', ')}">👥 ${participating.length} org</span>
                    <span class="inline-flex items-center gap-0.5 text-[8px] font-bold uppercase tracking-wide
                                 px-1 py-0.5 rounded border ${badgeCls} shrink-0"
                          title="${teamNames.join(' + ')}">
                        ${teamNames.length} tim
                    </span>
                    ${skipMini}
                </div>`;
            }

            return `<div class="flex items-center gap-1 min-w-0">
                <span class="text-gray-500 font-medium truncate max-w-[70px]"
                      title="${participating.map(t => t.name).join(', ')}">👥 ${participating.length} teknisi</span>
                ${skipMini}
            </div>`;
        }

        function renderWoCard(w) {
            const priorityStyle = {
                'urgent': {
                    dot: 'bg-red-500',
                    text: 'text-red-700',
                    label: 'Urgent'
                },
                'high': {
                    dot: 'bg-amber-500',
                    text: 'text-amber-700',
                    label: 'High'
                },
                'normal': {
                    dot: 'bg-gray-400',
                    text: 'text-gray-600',
                    label: 'Normal'
                },
                'low': {
                    dot: 'bg-gray-300',
                    text: 'text-gray-500',
                    label: 'Low'
                },
            } [w.priority] || {
                dot: 'bg-gray-400',
                text: 'text-gray-600',
                label: 'Normal'
            };

            const sourceLabel = w.source === 'technician' ?
                '<span class="text-[9px] uppercase tracking-wide text-purple-500 font-semibold">Mandiri</span>' :
                '<span class="text-[9px] uppercase tracking-wide text-cyan-500 font-semibold">Admin</span>';

            let myStatusBadge = '';
            let isMyCompleted = false;
            let isMySkipped = false;

            if (!IS_ADMIN) {
                isMySkipped = w.my_no_participation === true;
                const myStatus = w.my_status || w.status;
                isMyCompleted = myStatus === 'completed' && !isMySkipped;

                if (isMySkipped) {
                    myStatusBadge = `
                        <span class="inline-flex items-center gap-0.5 text-[8px] font-bold uppercase tracking-wide
                                     px-1.5 py-0.5 rounded border bg-rose-100 text-rose-700 border-rose-200 shrink-0">
                            ⊘ Skip
                        </span>
                    `;
                } else {
                    const badges = {
                        'pending': {
                            cls: 'bg-gray-100 text-gray-600 border-gray-200',
                            icon: '⏸',
                            label: 'Pending'
                        },
                        'in_progress': {
                            cls: 'bg-amber-100 text-amber-700 border-amber-200',
                            icon: '▶',
                            label: 'Proses'
                        },
                        'completed': {
                            cls: 'bg-emerald-100 text-emerald-700 border-emerald-200',
                            icon: '✓',
                            label: 'Selesai'
                        },
                    };
                    const b = badges[myStatus] || badges.pending;
                    myStatusBadge = `
                        <span class="inline-flex items-center gap-0.5 text-[8px] font-bold uppercase tracking-wide
                                     px-1.5 py-0.5 rounded border ${b.cls} shrink-0">
                            ${b.icon} ${b.label}
                        </span>
                    `;
                }
            }

            let modeBadge = '';
            const techCount = (w.technicians || []).length;
            if (techCount > 1) {
                if (w.completion_mode === 'collective') {
                    modeBadge =
                        `<span class="text-[8px] text-blue-600 font-bold" title="Mode Global — 1 selesai semua selesai">👥 Tim</span>`;
                } else {
                    const prog = w.team_progress;
                    const progText = prog ? ` ${prog.completed}/${prog.total}` : '';
                    modeBadge =
                        `<span class="text-[8px] text-violet-600 font-bold" title="Mode Individual">👤 Individu${progText}</span>`;
                }
            }

            const skippedCount = w.team_progress?.skipped ?? 0;
            const skipBadge = skippedCount > 0 ?
                `<span class="text-[8px] text-rose-500 font-bold ml-1"
                       title="${skippedCount} teknisi tidak partisipasi">⊘${skippedCount}</span>` : '';
            const createdDate = w.created_at ? new Date(w.created_at) : null;
            const deadlineDate = w.deadline_at ? new Date(w.deadline_at) : null;

            function fmtDate(date, withTime = true) {
                if (!date) return '';
                const d = String(date.getDate()).padStart(2, '0');
                const m = String(date.getMonth() + 1).padStart(2, '0');
                const y = date.getFullYear();
                if (!withTime) return `${d}/${m}/${y}`;
                const hh = String(date.getHours()).padStart(2, '0');
                const mm = String(date.getMinutes()).padStart(2, '0');
                return `${d}/${m}/${y} ${hh}:${mm}`;
            }

            function timeAgo(date) {
                if (!date) return '';
                const diff = Math.floor((Date.now() - date.getTime()) / 1000);
                if (diff < 60) return 'baru saja';
                if (diff < 3600) return `${Math.floor(diff / 60)} menit lalu`;
                if (diff < 86400) return `${Math.floor(diff / 3600)} jam lalu`;
                if (diff < 604800) return `${Math.floor(diff / 86400)} hari lalu`;
                return fmtDate(date, false);
            }

            const createdAt = createdDate ? timeAgo(createdDate) : '';
            const createdFull = createdDate ? fmtDate(createdDate) : '';
            const deadlineStr = deadlineDate ? fmtDate(deadlineDate, false) : '';
            const now = new Date();
            const isOverdue = deadlineDate && deadlineDate < now && !isMyCompleted && w.status !== 'completed';
            const isNearDeadline = deadlineDate && !isOverdue &&
                (deadlineDate - now) < 24 * 60 * 60 * 1000 &&
                !isMyCompleted && w.status !== 'completed';

            return `
                <a href="{{ url('/work-orders') }}/${w.id}"
                   class="block border rounded-lg p-2.5
                          hover:shadow-sm transition cursor-pointer group
                          ${isMySkipped
                              ? 'bg-rose-50/40 border-rose-200 hover:border-rose-300'
                              : 'bg-white border-gray-100 hover:border-indigo-300'}
                          ${isMyCompleted ? 'opacity-75' : ''}">

                    <div class="flex items-center justify-between gap-2 mb-1">
                        <span class="text-[10px] font-mono text-gray-400">${w.code}</span>
                        <div class="flex items-center gap-1">
                            ${myStatusBadge}
                            <span class="flex items-center gap-1 text-[9px] font-bold ${priorityStyle.text}">
                                <span class="w-1.5 h-1.5 rounded-full ${priorityStyle.dot}"></span>
                                ${priorityStyle.label}
                            </span>
                        </div>
                    </div>

                    <h4 class="text-[11px] font-semibold text-gray-800 leading-tight line-clamp-2 group-hover:text-indigo-600 transition mb-1">
                        ${w.title}
                    </h4>

                    {{--  BARU: Tanggal dibuat --}}
                    ${createdAt ? `
                                        <div class="flex items-center gap-1 text-[9px] text-gray-400 mb-1.5"
                                             title="Dibuat: ${createdFull}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5 shrink-0" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <span>${createdAt}</span>
                                        </div>
                                    ` : ''}

                    <div class="flex items-center justify-between gap-2 text-[9px] text-gray-400 flex-wrap">
                        <div class="flex items-center gap-1">
                            ${sourceLabel}
                            ${w.category ? `<span>· ${w.category}</span>` : ''}
                        </div>
                        ${renderWoTechnicians(w)}
                    </div>

                    ${modeBadge ? `<div class="mt-1 flex items-center gap-1">${modeBadge}${skipBadge}</div>` : ''}

                    {{--  BARU: Deadline dengan warning --}}
                    ${deadlineStr ? `
                                        <div class="mt-1 flex items-center gap-1 text-[9px] font-semibold
                                                    ${isOverdue
                                                        ? 'text-red-600'
                                                        : isNearDeadline
                                                            ? 'text-amber-600'
                                                            : 'text-gray-400'}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5 shrink-0" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                            <span>
                                                ${isOverdue ? '⚠️ Overdue: ' : isNearDeadline ? '⏰ Deadline: ' : '📅 '}
                                                ${deadlineStr}
                                            </span>
                                        </div>
                                    ` : ''}

                    ${(w.building || w.location) ? `
                                        <div class="text-[9px] text-gray-400 mt-1 truncate">
                                            📍 ${w.building ? w.building + ' — ' : ''}${w.location || ''}
                                        </div>
                                    ` : ''}
                </a>
            `;
        }

        function renderWoGrid(gridId, countId, jobs, emptyText) {
            const grid = document.getElementById(gridId);
            const countEl = document.getElementById(countId);

            if (!grid || !countEl) return;

            countEl.textContent = `${jobs.length}`;

            if (!jobs.length) {
                grid.innerHTML = `
                    <div class="text-center py-4 rounded-md bg-white/40 border border-dashed border-gray-200">
                        <p class="text-gray-400 text-[10px]">${emptyText}</p>
                    </div>
                `;
                return;
            }

            grid.innerHTML = jobs.map(renderWoCard).join('');
        }

        function renderWorkOrders() {
            let list = allWorkOrders;

            if (currentDivisionFilter !== 'all') {
                list = list.filter(w => w.division === currentDivisionFilter);
            }

            const effectiveStatus = (w) => IS_ADMIN ? w.status : (w.my_status || w.status);

            if (currentFilter !== 'all') {
                if (!IS_ADMIN && currentFilter === 'skipped') {
                    list = list.filter(w => w.my_no_participation === true);
                } else if (IS_ADMIN) {
                    list = list.filter(w => w.status === currentFilter);
                } else {
                    list = list.filter(w =>
                        (w.my_status || w.status) === currentFilter &&
                        w.my_no_participation !== true
                    );
                }
            }

            const sortJobs = (arr) => [...arr].sort((a, b) => {
                const aAssigned = (a.technicians && a.technicians.length) ? 1 : 0;
                const bAssigned = (b.technicians && b.technicians.length) ? 1 : 0;
                if (aAssigned !== bAssigned) return aAssigned - bAssigned;
                return new Date(b.created_at) - new Date(a.created_at);
            });

            const cctvPending = sortJobs(list.filter(w => w.division === 'cctv' && effectiveStatus(w) ===
                'pending'));
            const cctvProgress = sortJobs(list.filter(w => w.division === 'cctv' && effectiveStatus(w) ===
                'in_progress'));
            const cctvCompleted = sortJobs(list.filter(w => w.division === 'cctv' && effectiveStatus(w) ===
                'completed'));

            const hdPending = sortJobs(list.filter(w => w.division === 'helpdesk' && effectiveStatus(w) ===
                'pending'));
            const hdProgress = sortJobs(list.filter(w => w.division === 'helpdesk' && effectiveStatus(w) ===
                'in_progress'));
            const hdCompleted = sortJobs(list.filter(w => w.division === 'helpdesk' && effectiveStatus(w) ===
                'completed'));

            renderWoGrid('gridCctvPending', 'countCctvPending', cctvPending, 'Kosong');
            renderWoGrid('gridCctvProgress', 'countCctvProgress', cctvProgress, 'Kosong');
            renderWoGrid('gridCctvCompleted', 'countCctvCompleted', cctvCompleted, 'Kosong');

            renderWoGrid('gridHdPending', 'countHdPending', hdPending, 'Kosong');
            renderWoGrid('gridHdProgress', 'countHdProgress', hdProgress, 'Kosong');
            renderWoGrid('gridHdCompleted', 'countHdCompleted', hdCompleted, 'Kosong');
        }
        async function loadMonthlyChart(months = 12) {
            try {
                const data = await api.get(`{{ route('dashboard.monthly-stats') }}?months=${months}`);
                const canvas = document.getElementById('monthlyChart');
                if (!canvas) return;

                const ctx = canvas.getContext('2d');

                if (monthlyChart) monthlyChart.destroy();

                const allDatasets = [{
                        label: '📹 CCTV · Total',
                        data: data.series.cctv.total,
                        backgroundColor: 'rgba(147, 51, 234, 0.85)',
                        borderColor: 'rgb(147, 51, 234)',
                        borderWidth: 0,
                        borderRadius: 6,
                        barPercentage: 0.85,
                        categoryPercentage: 0.65,
                    },
                    {
                        label: '📹 CCTV · Selesai',
                        data: data.series.cctv.completed,
                        backgroundColor: 'rgba(147, 51, 234, 0.35)',
                        borderColor: 'rgb(147, 51, 234)',
                        borderWidth: 0,
                        borderRadius: 6,
                        barPercentage: 0.85,
                        categoryPercentage: 0.65,
                    },
                    {
                        label: '🎧 Helpdesk · Total',
                        data: data.series.helpdesk.total,
                        backgroundColor: 'rgba(20, 184, 166, 0.85)',
                        borderColor: 'rgb(20, 184, 166)',
                        borderWidth: 0,
                        borderRadius: 6,
                        barPercentage: 0.85,
                        categoryPercentage: 0.65,
                    },
                    {
                        label: '🎧 Helpdesk · Selesai',
                        data: data.series.helpdesk.completed,
                        backgroundColor: 'rgba(20, 184, 166, 0.35)',
                        borderColor: 'rgb(20, 184, 166)',
                        borderWidth: 0,
                        borderRadius: 6,
                        barPercentage: 0.85,
                        categoryPercentage: 0.65,
                    },
                ];

                const visibleDatasets = IS_ADMIN ?
                    allDatasets :
                    allDatasets.filter(ds => ds.data.some(v => v > 0));

                monthlyChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: visibleDatasets,
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    boxWidth: 8,
                                    padding: 14,
                                    font: {
                                        size: 11,
                                        weight: '600'
                                    }
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.92)',
                                padding: 12,
                                cornerRadius: 8,
                                titleFont: {
                                    size: 12,
                                    weight: '700'
                                },
                                bodyFont: {
                                    size: 12
                                },
                                callbacks: {
                                    afterBody: function(items) {
                                        if (!IS_ADMIN) return '';
                                        const i = items[0].dataIndex;
                                        const cctv = data.series.cctv.total[i];
                                        const hd = data.series.helpdesk.total[i];
                                        return `\nTotal gabungan: ${cctv + hd}`;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    precision: 0,
                                    color: '#9ca3af',
                                    font: {
                                        size: 11
                                    }
                                },
                                grid: {
                                    color: 'rgba(0,0,0,0.05)'
                                }
                            },
                            x: {
                                ticks: {
                                    color: '#6b7280',
                                    font: {
                                        size: 11
                                    }
                                },
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            } catch (err) {
                console.error('Monthly chart error:', err);
                showToast('Gagal memuat grafik: ' + err.message, 'error');
            }
        }

        function setMonthlyRange(months) {
            currentMonths = months;
            document.querySelectorAll('.range-month-btn').forEach(btn => {
                const isActive = parseInt(btn.dataset.rangebtn) === months;
                btn.className = isActive ?
                    'range-month-btn px-3 py-1.5 rounded-lg bg-indigo-600 text-white font-semibold transition' :
                    'range-month-btn px-3 py-1.5 rounded-lg text-gray-600 hover:bg-gray-100 font-semibold border border-gray-200 transition';
            });
            loadMonthlyChart(months);
        }

        async function refresh() {
            await Promise.all([loadStats(), loadWorkOrders()]);
            applyDivisionVisibility();
            styleDivFilterButtons(currentDivisionFilter);
            styleRekapButtons(currentRekapFilter);
        }
        if (!IS_ADMIN && MY_DIVISION) {
            currentDivisionFilter = MY_DIVISION;
            currentRekapFilter = MY_DIVISION;
        }

        refresh();
        loadMonthlyChart(12);
        setInterval(refresh, 5000);
    </script>
@endpush
