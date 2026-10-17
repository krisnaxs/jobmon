<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitor Pekerjaan — Job Monitor</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v=1">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}?v=1">

    {{-- ============ PWA HEAD ============ --}}
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4f46e5">
    {{-- ================================== --}}

    @vite(['resources/css/app.css'])

    <style>
        @keyframes pulse-dot {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.4;
            }
        }

        .pulse-dot {
            animation: pulse-dot 1.5s ease-in-out infinite;
        }

        @keyframes pending-pulse {

            0%,
            100% {
                border-color: rgba(239, 68, 68, 0.5);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
            }

            50% {
                border-color: rgba(239, 68, 68, 1);
                box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.25), 0 0 12px 0 rgba(239, 68, 68, 0.4);
            }
        }

        .pending-card {
            animation: pending-pulse 1.8s ease-in-out infinite;
        }

        @keyframes pending-dot-pulse {

            0%,
            100% {
                opacity: 0.5;
                transform: scale(1);
                background-color: rgba(239, 68, 68, 0.6);
            }

            50% {
                opacity: 1;
                transform: scale(1.4);
                background-color: rgba(239, 68, 68, 1);
                box-shadow: 0 0 6px 2px rgba(239, 68, 68, 0.6);
            }
        }

        .pending-dot {
            animation: pending-dot-pulse 1.2s ease-in-out infinite;
        }

        .thin-scroll::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .thin-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .thin-scroll::-webkit-scrollbar-thumb {
            background: rgba(100, 116, 139, 0.3);
            border-radius: 3px;
        }

        .thin-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 0.5);
        }

        .dropdown-enter {
            animation: dropdownIn 0.15s ease-out;
        }

        @keyframes dropdownIn {
            from {
                opacity: 0;
                transform: translateY(-4px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body
    class="bg-slate-950 min-h-screen text-slate-100 antialiased flex flex-col overflow-x-hidden md:h-screen md:overflow-hidden">

    {{-- ==================== HEADER ==================== --}}
    <header
        class="shrink-0 px-4 py-3 md:px-6 flex flex-wrap justify-between items-center border-b border-slate-800/60 bg-slate-900/40 backdrop-blur-sm gap-3">
        <div class="flex items-center gap-3">
            <img src="{{ asset('favicon.png') }}?v=1" alt="Job Monitor" class="w-10 h-10 rounded-xl object-contain">

            <div>
                <h1 class="text-lg font-bold text-white tracking-tight leading-tight">
                    Monitor Pekerjaan
                </h1>
                <p class="text-xs text-slate-500 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 pulse-dot"></span>
                    <span id="statusIndicator">Menampilkan pekerjaan aktif</span>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-4 flex-wrap">
            {{-- FILTER DIVISI --}}
            <div class="flex gap-1 overflow-x-auto thin-scroll pb-1 md:pb-0 w-full md:w-auto">
                <button onclick="setDivisionFilter('all')" data-divfilter="all"
                    class="divfilter-btn px-3 py-1.5 rounded-lg text-[11px] font-semibold bg-slate-600 text-white transition whitespace-nowrap">
                    Semua
                </button>
                <button onclick="setDivisionFilter('cctv')" data-divfilter="cctv"
                    class="divfilter-btn px-3 py-1.5 rounded-lg text-[11px] font-semibold text-purple-300 hover:bg-purple-500/20 border border-purple-500/40 transition whitespace-nowrap">
                    📹 CCTV
                </button>
                <button onclick="setDivisionFilter('helpdesk')" data-divfilter="helpdesk"
                    class="divfilter-btn px-3 py-1.5 rounded-lg text-[11px] font-semibold text-teal-300 hover:bg-teal-500/20 border border-teal-500/40 transition whitespace-nowrap">
                    🎧 Helpdesk
                </button>
            </div>

            {{-- FILTER RANGE WAKTU --}}
            <div class="flex gap-1 items-center overflow-x-auto thin-scroll pb-1 md:pb-0 w-full md:w-auto">
                <button onclick="setRangePreset('today')" data-range="today"
                    class="range-btn px-3 py-1.5 rounded-lg text-[11px] font-semibold bg-indigo-500 text-white transition whitespace-nowrap">
                    Hari Ini
                </button>
                <button onclick="setRangePreset('week')" data-range="week"
                    class="range-btn px-3 py-1.5 rounded-lg text-[11px] font-semibold text-slate-300 hover:bg-slate-700/60 border border-slate-600 transition whitespace-nowrap">
                    7 Hari
                </button>
                <button onclick="setRangePreset('month')" data-range="month"
                    class="range-btn px-3 py-1.5 rounded-lg text-[11px] font-semibold text-slate-300 hover:bg-slate-700/60 border border-slate-600 transition whitespace-nowrap">
                    30 Hari
                </button>
                <button onclick="setRangePreset('all')" data-range="all"
                    class="range-btn px-3 py-1.5 rounded-lg text-[11px] font-semibold text-slate-300 hover:bg-slate-700/60 border border-slate-600 transition whitespace-nowrap">
                    Semua
                </button>

                <div class="relative" id="customRangeWrapper">
                    <button onclick="toggleCustomPicker(event)"
                        class="range-btn px-3 py-1.5 rounded-lg text-[11px] font-semibold text-slate-300 hover:bg-slate-700/60 border border-slate-600 transition flex items-center gap-1 whitespace-nowrap">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span id="customRangeLabel">Custom</span>
                    </button>

                    <div id="customPickerDropdown"
                        class="hidden absolute right-0 top-full mt-2 bg-slate-900 border border-slate-700
                               rounded-xl shadow-2xl p-3 w-72 z-[120] dropdown-enter">
                        <div class="space-y-2">
                            <div>
                                <label class="block text-[10px] uppercase tracking-wider text-slate-400 font-bold mb-1">
                                    Dari Tanggal
                                </label>
                                <input type="date" id="customFrom"
                                    class="w-full bg-slate-800 border border-slate-700 rounded-lg
                                           px-2.5 py-1.5 text-xs text-slate-100
                                           focus:outline-none focus:ring-1 focus:ring-indigo-500
                                           focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-[10px] uppercase tracking-wider text-slate-400 font-bold mb-1">
                                    Sampai Tanggal
                                </label>
                                <input type="date" id="customTo"
                                    class="w-full bg-slate-800 border border-slate-700 rounded-lg
                                           px-2.5 py-1.5 text-xs text-slate-100
                                           focus:outline-none focus:ring-1 focus:ring-indigo-500
                                           focus:border-indigo-500">
                            </div>

                            <div id="customError" class="hidden text-[10px] text-red-400"></div>

                            <div class="flex gap-2 pt-1">
                                <button onclick="closeCustomPicker()"
                                    class="flex-1 px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700
                                           text-slate-300 text-[11px] font-semibold transition">
                                    Batal
                                </button>
                                <button onclick="applyCustomRange()"
                                    class="flex-1 px-3 py-1.5 rounded-lg bg-indigo-500 hover:bg-indigo-600
                                           text-white text-[11px] font-semibold transition">
                                    Terapkan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-right hidden md:block">
                <div class="text-xl font-light text-white tabular-nums tracking-wide leading-tight" id="clock">
                    --:--:--</div>
                <div class="text-[10px] text-slate-500" id="date">Memuat...</div>
            </div>

            @auth
                <a href="{{ route('dashboard') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg
              bg-gradient-to-br from-emerald-500 to-teal-600
              hover:from-emerald-600 hover:to-teal-700
              text-white text-[11px] font-semibold
              shadow-lg shadow-emerald-500/30
              transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Dashboard
                </a>
            @else
                <a href="{{ route('login') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg
              bg-gradient-to-br from-indigo-500 to-violet-600
              hover:from-indigo-600 hover:to-violet-700
              text-white text-[11px] font-semibold
              shadow-lg shadow-indigo-500/30
              transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                    Login
                </a>
            @endauth
        </div>
    </header>

    {{-- AREA SCROLL UNTUK KONTEN (Stats & Rekap) --}}
    <div class="flex-1 overflow-y-auto md:overflow-hidden flex flex-col">

        {{-- ==================== STATS BAR ==================== --}}
        <section class="shrink-0 px-4 md:px-6 py-3 grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-2"
            id="statsBar">
            @for ($i = 0; $i < 6; $i++)
                <div class="h-14 rounded-xl bg-slate-900/40 border border-slate-800/60 animate-pulse"></div>
            @endfor
        </section>

        {{-- ==================== REKAP PER TEKNISI ==================== --}}
        <section class="shrink-0 px-4 md:px-6 pb-3">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">

                <div id="techCardCctv" class="bg-slate-900/40 border border-slate-800/60 rounded-2xl overflow-hidden">
                    <div
                        class="flex justify-between items-center px-4 py-2 border-b border-slate-800/60 bg-purple-500/5">
                        <h2
                            class="text-[11px] uppercase tracking-widest text-purple-300 font-bold flex items-center gap-2">
                            <span>📹</span>
                            Rekap Teknisi CCTV
                        </h2>
                        <div class="text-[10px] text-slate-500 font-mono" id="techCountCctv">—</div>
                    </div>

                    <div class="max-h-56 overflow-y-auto thin-scroll">
                        <table class="w-full text-xs">
                            <thead class="bg-slate-950/40 text-slate-500 sticky top-0 z-10">
                                <tr>
                                    <th
                                        class="px-2.5 py-1.5 text-left font-semibold uppercase tracking-wider text-[9px] w-6">
                                        #</th>
                                    <th
                                        class="px-2.5 py-1.5 text-left font-semibold uppercase tracking-wider text-[9px]">
                                        Nama</th>
                                    <th
                                        class="px-2.5 py-1.5 text-center font-semibold uppercase tracking-wider text-[9px] w-10">
                                        Total</th>
                                    <th
                                        class="px-2.5 py-1.5 text-center font-semibold uppercase tracking-wider text-[9px] w-12">
                                        ✓</th>
                                    <th
                                        class="px-2.5 py-1.5 text-center font-semibold uppercase tracking-wider text-[9px] w-12">
                                        ⚙</th>
                                    <th
                                        class="px-2.5 py-1.5 text-center font-semibold uppercase tracking-wider text-[9px] w-10 text-rose-400">
                                        ⊘</th>
                                    <th
                                        class="px-2.5 py-1.5 text-left font-semibold uppercase tracking-wider text-[9px] w-32">
                                        Progress</th>
                                </tr>
                            </thead>
                            <tbody id="techTableCctv" class="divide-y divide-slate-800/60">
                                <tr>
                                    <td colspan="7" class="px-2.5 py-3 text-center text-slate-600 text-[10px]">
                                        Memuat...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="techCardHd" class="bg-slate-900/40 border border-slate-800/60 rounded-2xl overflow-hidden">
                    <div
                        class="flex justify-between items-center px-4 py-2 border-b border-slate-800/60 bg-teal-500/5">
                        <h2
                            class="text-[11px] uppercase tracking-widest text-teal-300 font-bold flex items-center gap-2">
                            <span>🎧</span>
                            Rekap Teknisi Helpdesk
                        </h2>
                        <div class="text-[10px] text-slate-500 font-mono" id="techCountHd">—</div>
                    </div>

                    <div class="max-h-56 overflow-y-auto thin-scroll">
                        <table class="w-full text-xs">
                            <thead class="bg-slate-950/40 text-slate-500 sticky top-0 z-10">
                                <tr>
                                    <th
                                        class="px-2.5 py-1.5 text-left font-semibold uppercase tracking-wider text-[9px] w-6">
                                        #</th>
                                    <th
                                        class="px-2.5 py-1.5 text-left font-semibold uppercase tracking-wider text-[9px]">
                                        Nama</th>
                                    <th
                                        class="px-2.5 py-1.5 text-center font-semibold uppercase tracking-wider text-[9px] w-10">
                                        Total</th>
                                    <th
                                        class="px-2.5 py-1.5 text-center font-semibold uppercase tracking-wider text-[9px] w-12">
                                        ✓</th>
                                    <th
                                        class="px-2.5 py-1.5 text-center font-semibold uppercase tracking-wider text-[9px] w-12">
                                        ⚙</th>
                                    <th
                                        class="px-2.5 py-1.5 text-center font-semibold uppercase tracking-wider text-[9px] w-10 text-rose-400">
                                        ⊘</th>
                                    <th
                                        class="px-2.5 py-1.5 text-left font-semibold uppercase tracking-wider text-[9px] w-32">
                                        Progress</th>
                                </tr>
                            </thead>
                            <tbody id="techTableHd" class="divide-y divide-slate-800/60">
                                <tr>
                                    <td colspan="7" class="px-2.5 py-3 text-center text-slate-600 text-[10px]">
                                        Memuat...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        {{-- ==================== MAIN: GRID 6 KOLOM ==================== --}}
        <main class="flex-1 md:min-h-0 px-4 md:px-6 pb-3 md:overflow-hidden">

            {{-- BUNGKUS DENGAN OVERFLOW-X-AUTO UNTUK MOBILE --}}
            <div class="w-full md:h-full overflow-x-auto thin-scroll pb-2 md:pb-0">
                <div id="mainGrid" class="h-full grid gap-3 min-w-[1200px]"
                    style="grid-template-columns: repeat(6, minmax(0, 1fr));">

                    <section id="cctv-group" class="contents">
                        <div
                            class="flex flex-col min-h-[400px] md:min-h-0 bg-slate-900/30 border border-slate-800/60 rounded-2xl overflow-hidden">
                            <div
                                class="shrink-0 px-3 py-2 border-b border-slate-800/60 flex justify-between items-center bg-purple-500/5">
                                <h2
                                    class="text-[10px] uppercase tracking-widest text-slate-400 font-bold flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400 pending-dot"></span>
                                    Menunggu
                                </h2>
                                <div class="text-[10px] text-slate-500 font-mono" id="countCctvPending">—</div>
                            </div>
                            <div id="gridCctvPending"
                                class="flex-1 min-h-0 overflow-y-auto thin-scroll p-2 space-y-1.5">
                                <div class="text-center py-6 text-slate-600 text-xs">Memuat...</div>
                            </div>
                        </div>

                        <div
                            class="flex flex-col min-h-[400px] md:min-h-0 bg-slate-900/30 border border-slate-800/60 rounded-2xl overflow-hidden">
                            <div
                                class="shrink-0 px-3 py-2 border-b border-slate-800/60 flex justify-between items-center bg-purple-500/5">
                                <h2
                                    class="text-[10px] uppercase tracking-widest text-amber-400 font-bold flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 pulse-dot"></span>
                                    Dikerjakan
                                </h2>
                                <div class="text-[10px] text-slate-500 font-mono" id="countCctvProgress">—</div>
                            </div>
                            <div id="gridCctvProgress"
                                class="flex-1 min-h-0 overflow-y-auto thin-scroll p-2 space-y-1.5">
                                <div class="text-center py-6 text-slate-600 text-xs">Memuat...</div>
                            </div>
                        </div>

                        <div
                            class="flex flex-col min-h-[400px] md:min-h-0 bg-slate-900/30 border border-slate-800/60 rounded-2xl overflow-hidden">
                            <div
                                class="shrink-0 px-3 py-2 border-b border-slate-800/60 flex justify-between items-center bg-purple-500/5">
                                <h2
                                    class="text-[10px] uppercase tracking-widest text-emerald-400 font-bold flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    Selesai
                                </h2>
                                <div class="text-[10px] text-slate-500 font-mono" id="countCctvCompleted">—</div>
                            </div>
                            <div id="gridCctvCompleted"
                                class="flex-1 min-h-0 overflow-y-auto thin-scroll p-2 space-y-1.5">
                                <div class="text-center py-6 text-slate-600 text-xs">Memuat...</div>
                            </div>
                        </div>
                    </section>

                    <section id="helpdesk-group" class="contents">
                        <div
                            class="flex flex-col min-h-[400px] md:min-h-0 bg-slate-900/30 border border-slate-800/60 rounded-2xl overflow-hidden">
                            <div
                                class="shrink-0 px-3 py-2 border-b border-slate-800/60 flex justify-between items-center bg-teal-500/5">
                                <h2
                                    class="text-[10px] uppercase tracking-widest text-slate-400 font-bold flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400 pending-dot"></span>
                                    Menunggu
                                </h2>
                                <div class="text-[10px] text-slate-500 font-mono" id="countHdPending">—</div>
                            </div>
                            <div id="gridHdPending"
                                class="flex-1 min-h-0 overflow-y-auto thin-scroll p-2 space-y-1.5">
                                <div class="text-center py-6 text-slate-600 text-xs">Memuat...</div>
                            </div>
                        </div>

                        <div
                            class="flex flex-col min-h-[400px] md:min-h-0 bg-slate-900/30 border border-slate-800/60 rounded-2xl overflow-hidden">
                            <div
                                class="shrink-0 px-3 py-2 border-b border-slate-800/60 flex justify-between items-center bg-teal-500/5">
                                <h2
                                    class="text-[10px] uppercase tracking-widest text-amber-400 font-bold flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 pulse-dot"></span>
                                    Dikerjakan
                                </h2>
                                <div class="text-[10px] text-slate-500 font-mono" id="countHdProgress">—</div>
                            </div>
                            <div id="gridHdProgress"
                                class="flex-1 min-h-0 overflow-y-auto thin-scroll p-2 space-y-1.5">
                                <div class="text-center py-6 text-slate-600 text-xs">Memuat...</div>
                            </div>
                        </div>

                        <div
                            class="flex flex-col min-h-[400px] md:min-h-0 bg-slate-900/30 border border-slate-800/60 rounded-2xl overflow-hidden">
                            <div
                                class="shrink-0 px-3 py-2 border-b border-slate-800/60 flex justify-between items-center bg-teal-500/5">
                                <h2
                                    class="text-[10px] uppercase tracking-widest text-emerald-400 font-bold flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                    Selesai
                                </h2>
                                <div class="text-[10px] text-slate-500 font-mono" id="countHdCompleted">—</div>
                            </div>
                            <div id="gridHdCompleted"
                                class="flex-1 min-h-0 overflow-y-auto thin-scroll p-2 space-y-1.5">
                                <div class="text-center py-6 text-slate-600 text-xs">Memuat...</div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </main>
    </div>

    <footer
        class="shrink-0 px-4 md:px-6 py-2 border-t border-slate-800/60 bg-slate-900/60 backdrop-blur-sm
                   flex justify-between text-[11px] text-slate-500">
        <div class="flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 pulse-dot"></span>
            Auto-refresh
            <span class="text-slate-600 mx-1">·</span>
            <span class="text-slate-400">Range: <span id="rangeDisplay">Hari Ini</span></span>
        </div>
        <div id="lastUpdate">—</div>
    </footer>

    <script>
        const POLL_INTERVAL = 3000;
        const API_URL = "{{ route('monitor.data') }}";
        const NOTIF_ENABLED = true;

        let lastJobIds = [];
        let isFirstLoad = true;
        let currentDivisionFilter = 'all';
        let lastData = null;

        let currentRange = 'today';
        let customFrom = null;
        let customTo = null;

        function formatRelativeTime(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            const now = new Date();
            const diffMs = now - date;
            const diffMin = Math.floor(diffMs / 60000);
            const diffHour = Math.floor(diffMs / 3600000);
            const diffDay = Math.floor(diffMs / 86400000);

            if (diffMin < 1) return 'baru saja';
            if (diffMin < 60) return `${diffMin} mnt lalu`;
            if (diffHour < 24) return `${diffHour} jam lalu`;
            if (diffDay < 7) return `${diffDay} hari lalu`;

            return date.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short'
            });
        }

        function formatFullDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleString('id-ID', {
                day: '2-digit',
                month: 'long',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function divisionBadgeDark(division) {
            const map = {
                'cctv': {
                    label: 'CCTV',
                    cls: 'bg-purple-500/20 text-purple-300 border-purple-500/40'
                },
                'helpdesk': {
                    label: 'Helpdesk',
                    cls: 'bg-teal-500/20 text-teal-300 border-teal-500/40'
                },
            };
            const s = map[division] || {
                label: division || '-',
                cls: 'bg-slate-700/40 text-slate-400 border-slate-600'
            };
            return `<span class="text-[9px] uppercase tracking-wider font-bold px-1.5 py-0.5 rounded border ${s.cls}">${s.label}</span>`;
        }

        function setDivisionFilter(division) {
            currentDivisionFilter = division;

            document.querySelectorAll('.divfilter-btn').forEach(btn => {
                const isActive = btn.dataset.divfilter === division;

                if (isActive) {
                    if (division === 'cctv') {
                        btn.className =
                            'divfilter-btn px-3 py-1.5 rounded-lg text-[11px] font-semibold bg-purple-500 text-white transition shadow-lg shadow-purple-500/30 whitespace-nowrap';
                    } else if (division === 'helpdesk') {
                        btn.className =
                            'divfilter-btn px-3 py-1.5 rounded-lg text-[11px] font-semibold bg-teal-500 text-white transition shadow-lg shadow-teal-500/30 whitespace-nowrap';
                    } else {
                        btn.className =
                            'divfilter-btn px-3 py-1.5 rounded-lg text-[11px] font-semibold bg-slate-600 text-white transition whitespace-nowrap';
                    }
                } else {
                    if (btn.dataset.divfilter === 'cctv') {
                        btn.className =
                            'divfilter-btn px-3 py-1.5 rounded-lg text-[11px] font-semibold text-purple-300 hover:bg-purple-500/20 border border-purple-500/40 transition whitespace-nowrap';
                    } else if (btn.dataset.divfilter === 'helpdesk') {
                        btn.className =
                            'divfilter-btn px-3 py-1.5 rounded-lg text-[11px] font-semibold text-teal-300 hover:bg-teal-500/20 border border-teal-500/40 transition whitespace-nowrap';
                    } else {
                        btn.className =
                            'divfilter-btn px-3 py-1.5 rounded-lg text-[11px] font-semibold text-slate-300 hover:bg-slate-700/60 border border-slate-600 transition whitespace-nowrap';
                    }
                }
            });

            const cctvGroup = document.getElementById('cctv-group');
            const hdGroup = document.getElementById('helpdesk-group');
            const mainGrid = document.getElementById('mainGrid');

            if (division === 'cctv') {
                cctvGroup.style.display = 'contents';
                hdGroup.style.display = 'none';
                mainGrid.style.gridTemplateColumns = 'repeat(3, minmax(0, 1fr))';
                mainGrid.style.minWidth = '600px'; // Agar di HP tetap proporsional
            } else if (division === 'helpdesk') {
                cctvGroup.style.display = 'none';
                hdGroup.style.display = 'contents';
                mainGrid.style.gridTemplateColumns = 'repeat(3, minmax(0, 1fr))';
                mainGrid.style.minWidth = '600px';
            } else {
                cctvGroup.style.display = 'contents';
                hdGroup.style.display = 'contents';
                mainGrid.style.gridTemplateColumns = 'repeat(6, minmax(0, 1fr))';
                mainGrid.style.minWidth = '1200px';
            }

            if (lastData) {
                renderAll(lastData);
            }
        }

        function setRangePreset(range) {
            currentRange = range;
            customFrom = null;
            customTo = null;

            document.querySelectorAll('.range-btn').forEach(btn => {
                if (btn.dataset.range === range) {
                    btn.className =
                        'range-btn px-3 py-1.5 rounded-lg text-[11px] font-semibold bg-indigo-500 text-white transition whitespace-nowrap';
                } else {
                    btn.className =
                        'range-btn px-3 py-1.5 rounded-lg text-[11px] font-semibold text-slate-300 hover:bg-slate-700/60 border border-slate-600 transition whitespace-nowrap';
                }
            });

            const customLabel = document.getElementById('customRangeLabel');
            if (customLabel) customLabel.textContent = 'Custom';

            closeCustomPicker();
            fetchData();
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
                    'range-btn px-3 py-1.5 rounded-lg text-[11px] font-semibold text-slate-300 hover:bg-slate-700/60 border border-slate-600 transition whitespace-nowrap';
            });

            const customBtn = document.querySelector('#customRangeWrapper .range-btn');
            if (customBtn) {
                customBtn.className =
                    'range-btn px-3 py-1.5 rounded-lg text-[11px] font-semibold bg-indigo-500 text-white transition flex items-center gap-1 whitespace-nowrap';
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
            fetchData();
        }

        document.addEventListener('click', function(e) {
            const wrapper = document.getElementById('customRangeWrapper');
            if (wrapper && !wrapper.contains(e.target)) {
                closeCustomPicker();
            }
        });

        function updateClock() {
            const now = new Date();
            const time = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            const date = now.toLocaleDateString('id-ID', {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
            const clockEl = document.getElementById('clock');
            const dateEl = document.getElementById('date');
            if (clockEl) clockEl.textContent = time;
            if (dateEl) dateEl.textContent = date;
        }
        setInterval(updateClock, 1000);
        updateClock();

        function playNotif() {
            if (!NOTIF_ENABLED) return;
            try {
                const audioCtx = new(window.AudioContext || window.webkitAudioContext)();
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.type = 'sine';
                osc.frequency.value = 800;
                gain.gain.value = 0.15;
                osc.start();
                osc.stop(audioCtx.currentTime + 0.15);

                setTimeout(() => {
                    const osc2 = audioCtx.createOscillator();
                    const gain2 = audioCtx.createGain();
                    osc2.connect(gain2);
                    gain2.connect(audioCtx.destination);
                    osc2.type = 'sine';
                    osc2.frequency.value = 1000;
                    gain2.gain.value = 0.15;
                    osc2.start();
                    osc2.stop(audioCtx.currentTime + 0.15);
                }, 200);
            } catch (err) {
                console.warn('Audio error:', err);
            }
        }

        function renderStats(stats, statsByDivision) {
            const items = [{
                    label: 'Total',
                    value: stats.total,
                    color: 'text-white',
                    iconBg: 'bg-indigo-500/20',
                    iconColor: 'text-indigo-400',
                    icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />',
                },
                {
                    label: 'Selesai',
                    value: stats.completed,
                    color: 'text-emerald-400',
                    iconBg: 'bg-emerald-500/20',
                    iconColor: 'text-emerald-400',
                    icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />',
                },
                {
                    label: 'Proses',
                    value: stats.progress,
                    color: 'text-amber-400',
                    iconBg: 'bg-amber-500/20',
                    iconColor: 'text-amber-400',
                    icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />',
                },
                {
                    label: 'Pending',
                    value: stats.pending,
                    color: 'text-slate-300',
                    iconBg: 'bg-slate-700/40',
                    iconColor: 'text-slate-400',
                    icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
                },
            ];

            if (statsByDivision) {
                items.push({
                    label: 'CCTV',
                    value: statsByDivision.cctv.total,
                    color: 'text-purple-400',
                    iconBg: 'bg-purple-500/20',
                    iconColor: 'text-purple-400',
                    icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />'
                });
                items.push({
                    label: 'Helpdesk',
                    value: statsByDivision.helpdesk.total,
                    color: 'text-teal-400',
                    iconBg: 'bg-teal-500/20',
                    iconColor: 'text-teal-400',
                    icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />'
                });
            }

            document.getElementById('statsBar').innerHTML = items.map(s => `
                <div class="bg-slate-900/60 border border-slate-800/60 rounded-xl px-3 py-2
                            flex items-center justify-between
                            hover:border-indigo-500/40 transition-colors">
                    <div class="min-w-0">
                        <div class="text-[9px] uppercase tracking-widest text-slate-500 font-bold">${s.label}</div>
                        <div class="text-xl font-light tabular-nums leading-tight ${s.color}">${s.value}</div>
                    </div>
                    <div class="w-7 h-7 rounded-lg ${s.iconBg} flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 ${s.iconColor}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            ${s.icon}
                        </svg>
                    </div>
                </div>
            `).join('');
        }

        function renderTechnicians(techniciansByDivision) {
            const statsByDiv = (lastData && lastData.statsByDivision) || {
                cctv: {
                    total: 0
                },
                helpdesk: {
                    total: 0
                }
            };

            const cctvList = [...(techniciansByDivision.cctv || [])].sort((a, b) => a.name.localeCompare(b.name));
            const hdList = [...(techniciansByDivision.helpdesk || [])].sort((a, b) => a.name.localeCompare(b.name));

            renderTechTable('techTableCctv', 'techCountCctv', cctvList, 'cctv', statsByDiv.cctv.total);
            renderTechTable('techTableHd', 'techCountHd', hdList, 'helpdesk', statsByDiv.helpdesk.total);
        }

        function renderTechTable(tbodyId, countId, list, division, divisionTotal) {
            const tbody = document.getElementById(tbodyId);
            const countEl = document.getElementById(countId);

            if (!tbody) return;

            countEl.textContent = `${list.length} teknisi`;

            if (!list.length) {
                tbody.innerHTML = `
            <tr>
                <td colspan="7" class="px-2.5 py-3 text-center text-slate-600 text-[10px]">
                    Belum ada teknisi
                </td>
            </tr>
        `;
                return;
            }

            const isCctv = division === 'cctv';
            const barColor = isCctv ? 'from-purple-500 to-purple-400' : 'from-teal-500 to-teal-400';
            const avatarGradient = isCctv ? 'from-purple-500 to-purple-600' : 'from-teal-500 to-teal-600';
            const textColor = isCctv ? 'text-purple-300' : 'text-teal-300';
            const teamBadgeCls = isCctv ?
                'bg-purple-500/20 text-purple-300 border-purple-500/40' :
                'bg-teal-500/20 text-teal-300 border-teal-500/40';

            tbody.innerHTML = list.map((t, i) => {
                const participating = t.participating_wo ?? t.total_wo;
                const pct = participating > 0 ?
                    Math.round((t.completed_wo / participating) * 100) :
                    0;
                const noPart = t.no_participation_wo ?? 0;
                const teamBadge = t.team_name ?
                    `<span class="inline-flex items-center gap-0.5 text-[8px] px-1 py-0.5 rounded
                             font-bold uppercase tracking-wider border mt-0.5
                             ${teamBadgeCls}">
                   <svg xmlns="http://www.w3.org/2000/svg" class="h-2 w-2" fill="none" viewBox="0 0 24 24"
                       stroke="currentColor" stroke-width="2.5">
                       <path stroke-linecap="round" stroke-linejoin="round"
                           d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                   </svg>
                   ${t.team_name}
               </span>` :
                    '';
                const skipBadge = noPart > 0 ?
                    `<span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1 rounded
                                 bg-rose-500/20 text-rose-300 font-bold text-[9px] border border-rose-500/40"
                           title="${noPart} WO tidak diikuti">
                        ${noPart}
                    </span>` :
                    `<span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1 rounded
                                 text-slate-600 font-bold text-[9px]">
                        —
                    </span>`;

                return `
            <tr class="hover:bg-slate-800/30 transition">
                <td class="px-2.5 py-1 text-slate-500 font-mono text-[10px] align-top pt-1.5">${i + 1}</td>
                <td class="px-2.5 py-1 align-top">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <div class="w-5 h-5 rounded-full bg-gradient-to-br ${avatarGradient}
                                    flex items-center justify-center text-white font-bold text-[8px] shrink-0">
                            ${t.name.charAt(0).toUpperCase()}
                        </div>
                        <span class="text-slate-200 font-medium truncate text-[10px]">${t.name}</span>
                        ${teamBadge}
                    </div>
                </td>
                <td class="px-2.5 py-1 text-center text-slate-300 font-semibold text-[10px] align-top pt-1.5">${t.total_wo}</td>
                <td class="px-2.5 py-1 text-center align-top pt-1">
                    <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1 rounded
                                 bg-emerald-500/20 text-emerald-300 font-bold text-[9px] border border-emerald-500/40">
                        ${t.completed_wo}
                    </span>
                </td>
                <td class="px-2.5 py-1 text-center align-top pt-1">
                    <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1 rounded
                                 bg-amber-500/20 text-amber-300 font-bold text-[9px] border border-amber-500/40">
                        ${t.progress_wo}
                    </span>
                </td>
                <td class="px-2.5 py-1 text-center align-top pt-1">
                    ${skipBadge}
                </td>
                <td class="px-2.5 py-1 align-top pt-1.5">
                    <div class="flex items-center gap-1.5">
                        <div class="flex-1 bg-slate-800 rounded-full h-1 overflow-hidden">
                            <div class="bg-gradient-to-r ${barColor} h-1 rounded-full transition-all"
                                 style="width:${pct}%"></div>
                        </div>
                        <span class="text-[9px] ${textColor} font-bold tabular-nums w-8 text-right">
                            ${pct}%
                        </span>
                    </div>
                    <div class="text-[8px] text-slate-500 mt-0.5">
                        ${t.completed_wo} / ${participating} partisipasi${noPart > 0 ? ` · <span class="text-rose-400">${noPart} skip</span>` : ''}
                    </div>
                </td>
            </tr>
        `;
            }).join('');
        }

        function renderJobTechnicians(j) {
            const list = j.technicians || [];

            if (!list.length) {
                return '<span class="text-slate-600 italic shrink-0">Belum</span>';
            }

            const gradient = (div) =>
                div === 'cctv' ? 'from-purple-500 to-purple-600' :
                div === 'helpdesk' ? 'from-teal-500 to-teal-600' :
                'from-indigo-500 to-violet-600';

            const isCctv = j.division === 'cctv';
            const teamBadgeCls = isCctv ?
                'bg-purple-500/30 text-purple-200 border-purple-500/50' :
                'bg-teal-500/30 text-teal-200 border-teal-500/50';
            if (list.length === 1) {
                const t = list[0];
                const isDone = t.pivot_status === 'completed';
                const isSkipped = t.pivot_no_participation === true;
                if (isSkipped) {
                    return `
                        <div class="flex items-center gap-1 shrink-0 min-w-0">
                            <span class="text-[9px] text-rose-400 font-bold shrink-0" title="Tidak partisipasi">
                                ⊘ SKIP
                            </span>
                        </div>
                    `;
                }

                const teamBadge = t.team_name ? `
                    <span class="text-[8px] px-1 py-0.5 rounded font-bold uppercase tracking-wider border shrink-0
                                 ${teamBadgeCls}" title="${t.team_name}">
                        ${t.team_name}
                    </span>
                ` : '';

                return `
                    <div class="flex items-center gap-1 shrink-0 min-w-0">
                        <div class="w-3.5 h-3.5 rounded-full bg-gradient-to-br ${gradient(t.division)}
                                    flex items-center justify-center text-white font-bold text-[7px] shrink-0
                                    ${isDone ? 'ring-1 ring-emerald-400' : ''}">
                            ${t.name.charAt(0).toUpperCase()}
                        </div>
                        <span class="text-slate-400 font-medium truncate max-w-[50px]
                                     ${isDone ? 'text-emerald-300' : ''}">
                            ${t.name.split(' ')[0]}${isDone ? ' ✓' : ''}
                        </span>
                        ${teamBadge}
                    </div>
                `;
            }
            const participating = list.filter(t => t.pivot_no_participation !== true);
            const skipped = list.filter(t => t.pivot_no_participation === true);

            const teamNames = [...new Set(participating.map(t => t.team_name).filter(Boolean))];
            const completedCount = participating.filter(t => t.pivot_status === 'completed').length;
            const skippedCount = skipped.length;
            const skipMiniBadge = skippedCount > 0 ?
                `<span class="text-[8px] text-rose-400 font-bold shrink-0" title="${skippedCount} teknisi tidak partisipasi">
                    ⊘${skippedCount}
                </span>` :
                '';
            if (teamNames.length === 1) {
                return `
                    <div class="flex items-center gap-1 shrink-0" title="${participating.map(t => t.name).join(', ')}${skippedCount > 0 ? ' | Skip: ' + skipped.map(t => t.name).join(', ') : ''}">
                        <span class="text-slate-400 font-medium">
                            👥 ${participating.length} org${completedCount > 0 ? ` <span class="text-emerald-400">(${completedCount}✓)</span>` : ''}
                        </span>
                        ${skipMiniBadge}
                        <span class="text-[8px] px-1 py-0.5 rounded font-bold uppercase tracking-wider border shrink-0
                                     ${teamBadgeCls}" title="${teamNames[0]}">
                            ${teamNames[0]}
                        </span>
                    </div>
                `;
            }
            if (teamNames.length > 1) {
                return `
                    <div class="flex items-center gap-1 shrink-0" title="${participating.map(t => t.name).join(', ')}${skippedCount > 0 ? ' | Skip: ' + skipped.map(t => t.name).join(', ') : ''}">
                        <span class="text-slate-400 font-medium">
                            👥 ${participating.length} org${completedCount > 0 ? ` <span class="text-emerald-400">(${completedCount}✓)</span>` : ''}
                        </span>
                        ${skipMiniBadge}
                        <span class="text-[8px] px-1 py-0.5 rounded font-bold uppercase tracking-wider border shrink-0
                                     ${teamBadgeCls}" title="${teamNames.join(' + ')}">
                            ${teamNames.length} tim
                        </span>
                    </div>
                `;
            }
            return `
                <div class="flex items-center gap-1 shrink-0" title="${participating.map(t => t.name).join(', ')}${skippedCount > 0 ? ' | Skip: ' + skipped.map(t => t.name).join(', ') : ''}">
                    <span class="text-slate-400 font-medium">
                        👥 ${participating.length} teknisi${completedCount > 0 ? ` <span class="text-emerald-400">(${completedCount}✓)</span>` : ''}
                    </span>
                    ${skipMiniBadge}
                </div>
            `;
        }

        function renderJobCard(j) {
            const priorityStyle = {
                'urgent': {
                    dot: 'bg-red-500',
                    text: 'text-red-400',
                    label: 'URGENT',
                    border: 'border-l-red-500'
                },
                'high': {
                    dot: 'bg-amber-500',
                    text: 'text-amber-400',
                    label: 'HIGH',
                    border: 'border-l-amber-500'
                },
                'normal': {
                    dot: 'bg-indigo-400',
                    text: 'text-indigo-300',
                    label: 'NORMAL',
                    border: 'border-l-indigo-500'
                },
                'low': {
                    dot: 'bg-slate-500',
                    text: 'text-slate-400',
                    label: 'LOW',
                    border: 'border-l-slate-600'
                },
            } [j.priority] || {
                dot: 'bg-slate-500',
                text: 'text-slate-400',
                label: 'NORMAL',
                border: 'border-l-slate-600'
            };

            const borderLeft = j.status === 'completed' ? 'border-l-emerald-500' : priorityStyle.border;
            const cardBg = j.status === 'completed' ? 'bg-emerald-950/20' : 'bg-slate-900/60';
            const pulseClass = j.status === 'pending' ? 'pending-card' : '';
            const priorityDotClass = j.status === 'pending' ?
                `${priorityStyle.dot} pending-dot` :
                priorityStyle.dot;
            let modeBadge = '';
            const techCount = (j.technicians || []).length;
            const teamProgress = j.team_progress || {};
            const skippedCount = teamProgress.skipped ?? 0;

            if (techCount > 1) {
                if (j.completion_mode === 'collective') {
                    modeBadge = `
                <span class="inline-flex items-center gap-0.5 text-[8px] px-1.5 py-0.5 rounded font-bold uppercase tracking-wider border
                             bg-blue-500/20 text-blue-300 border-blue-500/40"
                      title="Mode Global — 1 teknisi selesai, semua selesai">
                    👥 TIM
                </span>
            `;
                } else {
                    const prog = j.team_progress;
                    const progText = prog ? ` ${prog.completed}/${prog.total}` : '';
                    modeBadge = `
                <span class="inline-flex items-center gap-0.5 text-[8px] px-1.5 py-0.5 rounded font-bold uppercase tracking-wider border
                             bg-violet-500/20 text-violet-300 border-violet-500/40"
                      title="Mode Individual — setiap teknisi selesai sendiri">
                    👤 INDIVIDU${progText}
                </span>
            `;
                }
            }
            const skipBadge = skippedCount > 0 ?
                `<span class="inline-flex items-center gap-0.5 text-[8px] px-1.5 py-0.5 rounded font-bold uppercase tracking-wider border
                             bg-rose-500/20 text-rose-300 border-rose-500/40"
                      title="${skippedCount} teknisi tidak berpartisipasi">
                    ⊘ ${skippedCount} SKIP
                </span>` :
                '';
            let progressBar = '';
            if (j.completion_mode === 'individual' && j.team_progress && j.team_progress.total > 1) {
                const pct = Math.round((j.team_progress.completed / j.team_progress.total) * 100);
                progressBar = `
            <div class="mt-1.5">
                <div class="flex items-center justify-between text-[8px] text-slate-500 mb-0.5">
                    <span>Progress tim</span>
                    <span class="font-bold text-violet-400">${j.team_progress.completed}/${j.team_progress.total}${skippedCount > 0 ? ` <span class="text-rose-400">(${skippedCount} skip)</span>` : ''}</span>
                </div>
                <div class="w-full bg-slate-800 rounded-full h-1 overflow-hidden">
                    <div class="bg-gradient-to-r from-violet-500 to-violet-400 h-1 rounded-full transition-all"
                         style="width:${pct}%"></div>
                </div>
            </div>
        `;
            }

            return `
        <div class="${cardBg} border border-slate-800/60 ${borderLeft} border-l-2 rounded-md px-2.5 py-2
                    hover:border-slate-700 hover:bg-slate-900/80 transition-all duration-200
                    ${pulseClass}">

            <div class="flex items-center justify-between gap-2 mb-1">
                <div class="flex items-center gap-1.5 min-w-0">
                    <span class="text-[9px] font-mono text-slate-500 shrink-0">${j.code}</span>
                    ${divisionBadgeDark(j.division)}
                </div>
                <span class="flex items-center gap-1 text-[9px] font-bold ${priorityStyle.text} shrink-0">
                    <span class="w-1 h-1 rounded-full ${priorityDotClass}"></span>
                    ${priorityStyle.label}
                </span>
            </div>

            <h3 class="text-[11px] font-semibold text-white leading-tight mb-1 line-clamp-1">
                ${j.title}
            </h3>

            ${(modeBadge || skipBadge) ? `<div class="mb-1 flex items-center gap-1 flex-wrap">${modeBadge}${skipBadge}</div>` : ''}

            <div class="flex items-center justify-between gap-2 text-[9px] text-slate-500">
                <div class="flex items-center gap-1.5 min-w-0">
                    ${(j.building || j.location) ? `
                                                <span class="inline-flex items-center gap-0.5 truncate max-w-[80px]"
                                                      title="${j.building ? j.building + ' — ' : ''}${j.location || ''}">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-2 w-2 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    </svg>
                                                    ${j.building ? j.building + ' — ' : ''}${j.location || ''}
                                                </span>
                                            ` : ''}
                    <span class="inline-flex items-center gap-0.5 shrink-0" title="${formatFullDate(j.created_at)}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-2 w-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        ${formatRelativeTime(j.created_at)}
                    </span>
                </div>

                ${renderJobTechnicians(j)}
            </div>

            ${progressBar}

            ${j.completed_at ? `
                                        <div class="text-[9px] text-emerald-500 flex items-center gap-0.5 mt-0.5"
                                             title="${formatFullDate(j.completed_at)}">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-2 w-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            ${formatRelativeTime(j.completed_at)}
                                        </div>
                                    ` : ''}
        </div>
    `;
        }

        function renderGrid(gridId, countId, jobs, emptyText) {
            const grid = document.getElementById(gridId);
            const countEl = document.getElementById(countId);

            if (!grid || !countEl) return;

            countEl.textContent = `${jobs.length}`;

            if (!jobs.length) {
                grid.innerHTML = `
                    <div class="text-center py-4 rounded-md bg-slate-900/30 border border-slate-800/40">
                        <p class="text-slate-600 text-[10px]">${emptyText}</p>
                    </div>
                `;
                return;
            }

            grid.innerHTML = jobs.map(renderJobCard).join('');
        }

        function renderAll(data) {
            renderStats(data.stats, data.statsByDivision);

            renderTechnicians(data.techniciansByDivision || {
                cctv: [],
                helpdesk: []
            });

            const byStatus = data.jobsByStatus || {
                in_progress: [],
                pending: [],
                completed: []
            };

            const sortJobs = (arr) => [...arr].sort((a, b) => {
                const aAssigned = (a.technicians && a.technicians.length) ? 1 : 0;
                const bAssigned = (b.technicians && b.technicians.length) ? 1 : 0;
                if (aAssigned !== bAssigned) return aAssigned - bAssigned;
                return new Date(b.created_at) - new Date(a.created_at);
            });

            const cctvPending = sortJobs(byStatus.pending.filter(j => j.division === 'cctv'));
            const cctvProgress = sortJobs(byStatus.in_progress.filter(j => j.division === 'cctv'));
            const cctvCompleted = sortJobs(byStatus.completed.filter(j => j.division === 'cctv'));

            const hdPending = sortJobs(byStatus.pending.filter(j => j.division === 'helpdesk'));
            const hdProgress = sortJobs(byStatus.in_progress.filter(j => j.division === 'helpdesk'));
            const hdCompleted = sortJobs(byStatus.completed.filter(j => j.division === 'helpdesk'));

            renderGrid('gridCctvPending', 'countCctvPending', cctvPending, 'Tidak ada pending CCTV');
            renderGrid('gridCctvProgress', 'countCctvProgress', cctvProgress, 'Tidak ada proses CCTV');
            renderGrid('gridCctvCompleted', 'countCctvCompleted', cctvCompleted, 'Belum ada selesai');

            renderGrid('gridHdPending', 'countHdPending', hdPending, 'Tidak ada pending Helpdesk');
            renderGrid('gridHdProgress', 'countHdProgress', hdProgress, 'Tidak ada proses Helpdesk');
            renderGrid('gridHdCompleted', 'countHdCompleted', hdCompleted, 'Belum ada selesai');

            const rangeDisplay = document.getElementById('rangeDisplay');
            if (rangeDisplay) {
                rangeDisplay.textContent = data.rangeLabel || 'Hari Ini';
            }
        }
        async function fetchData() {
            try {
                let url = `${API_URL}?range=${currentRange}`;

                if (currentRange === 'custom' && customFrom && customTo) {
                    url += `&from=${customFrom}&to=${customTo}`;
                }

                const res = await fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!res.ok) throw new Error('HTTP ' + res.status);

                const data = await res.json();
                lastData = data;

                const allJobs = [
                    ...(data.jobsByStatus?.in_progress || []),
                    ...(data.jobsByStatus?.pending || []),
                    ...(data.jobsByStatus?.completed || []),
                ];
                const currentIds = allJobs.map(j => j.id);

                if (!isFirstLoad) {
                    const newJobs = currentIds.filter(id => !lastJobIds.includes(id));
                    if (newJobs.length > 0) {
                        playNotif();
                        console.log('🔔 Job baru:', newJobs.length);
                    }
                }
                lastJobIds = currentIds;
                isFirstLoad = false;

                renderAll(data);

                const indicator = document.getElementById('statusIndicator');
                const dot = indicator.parentElement.querySelector('span');
                indicator.textContent = 'Terhubung';
                dot.className = 'w-1.5 h-1.5 rounded-full bg-emerald-500 pulse-dot';

                document.getElementById('lastUpdate').textContent =
                    'Update: ' + new Date().toLocaleTimeString('id-ID');

            } catch (err) {
                console.error('Fetch error:', err);
                const indicator = document.getElementById('statusIndicator');
                const dot = indicator.parentElement.querySelector('span');
                indicator.textContent = 'Koneksi terputus';
                dot.className = 'w-1.5 h-1.5 rounded-full bg-red-500';
            }
        }

        fetchData();
        setInterval(fetchData, POLL_INTERVAL);
    </script>

    {{-- ============ PWA SERVICE WORKER ============ --}}
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(err => {
                    console.log('SW registration failed: ', err);
                });
            });
        }
    </script>
    {{-- ============================================ --}}

</body>

</html>
