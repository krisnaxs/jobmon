@extends('layouts.app')

@section('title', 'Laporan Work Order')

@section('content')

    @php
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
    @endphp

    <div class="max-w-7xl mx-auto space-y-5">

        {{-- ==================== HEADER ==================== --}}
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Laporan Work Order</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Rekap & analisa pekerjaan · @if ($isAdmin)
                        Semua Divisi
                    @else
                        Divisi Anda
                    @endif
                </p>
            </div>

            {{-- Export Dropdown --}}
            <div class="relative inline-block" id="exportWrapper">
                <button onclick="toggleExportMenu(event)" id="exportBtn"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl
                           bg-gradient-to-br from-emerald-500 to-emerald-600
                           hover:from-emerald-600 hover:to-emerald-700
                           text-white text-sm font-semibold
                           shadow-lg shadow-emerald-500/30 transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Export Excel</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div id="exportMenu"
                    class="hidden absolute right-0 top-full mt-2 bg-white border border-gray-200
                           rounded-xl shadow-2xl p-3 w-72 z-[120]">
                    <div class="space-y-3">
                        <div>
                            <div class="text-[10px] uppercase tracking-wider text-gray-500 font-bold mb-2">
                                Pilih Format Export
                            </div>
                            <div class="space-y-1.5">
                                <button onclick="exportExcel('wo')"
                                    class="w-full text-left px-3 py-2 rounded-lg hover:bg-indigo-50 transition flex items-start gap-2">
                                    <div
                                        class="w-7 h-7 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-indigo-600"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-gray-800">Per Work Order</div>
                                        <div class="text-[10px] text-gray-500">1 baris = 1 WO (default)</div>
                                    </div>
                                </button>

                                <button onclick="exportExcel('technician')"
                                    class="w-full text-left px-3 py-2 rounded-lg hover:bg-violet-50 transition flex items-start gap-2">
                                    <div
                                        class="w-7 h-7 rounded-lg bg-violet-100 flex items-center justify-center shrink-0 mt-0.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-violet-600"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-gray-800">Per Teknisi</div>
                                        <div class="text-[10px] text-gray-500">1 baris = 1 teknisi × WO</div>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-gray-100">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" id="includePhotos"
                                    class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <div>
                                    <div class="text-xs font-semibold text-gray-700">Sertakan foto bukti</div>
                                    <div class="text-[10px] text-gray-500">
                                        Gambar tampil langsung di Excel · max 300 WO
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ==================== FILTER ==================== --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 space-y-3">

            {{-- Row 1: Periode --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wide mr-1">Periode:</span>

                <button onclick="setRangePreset('all')" data-range="all"
                    class="range-btn px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-600 text-white transition">
                    Semua
                </button>
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

                <div class="relative" id="customRangeWrapper">
                    <button onclick="toggleCustomPicker(event)"
                        class="range-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:bg-gray-100 border border-gray-200 transition flex items-center gap-1">
                        📅 <span id="customRangeLabel">Custom</span>
                    </button>

                    <div id="customPickerDropdown"
                        class="hidden absolute left-0 top-full mt-2 bg-white border border-gray-200
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

            {{-- Row 2: Filter lanjutan --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 pt-3 border-t border-gray-100">

                <div>
                    <label class="block text-[10px] uppercase tracking-wider text-gray-500 font-bold mb-1">Divisi</label>
                    <select id="filterDivision" onchange="refresh()"
                        class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs text-gray-800
                               focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white">
                        <option value="all">Semua</option>
                        <option value="cctv">CCTV</option>
                        <option value="helpdesk">Helpdesk</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] uppercase tracking-wider text-gray-500 font-bold mb-1">Mode</label>
                    <select id="filterMode" onchange="refresh()"
                        class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs text-gray-800
                               focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white">
                        <option value="all">Semua</option>
                        <option value="collective">Global (Tim)</option>
                        <option value="individual">Individual</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] uppercase tracking-wider text-gray-500 font-bold mb-1">Status</label>
                    <select id="filterStatus" onchange="refresh()"
                        class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs text-gray-800
                               focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white">
                        <option value="all">Semua</option>
                        <option value="pending">Pending</option>
                        <option value="in_progress">Proses</option>
                        <option value="completed">Selesai</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] uppercase tracking-wider text-gray-500 font-bold mb-1">Teknisi</label>
                    <select id="filterTechnician" onchange="refresh()"
                        class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs text-gray-800
                               focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white">
                        <option value="all">Semua</option>
                        @foreach ($technicians as $t)
                            <option value="{{ $t->id }}">
                                {{ $t->name }} ({{ strtoupper($t->division) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[10px] uppercase tracking-wider text-gray-500 font-bold mb-1">Tim</label>
                    <div class="flex gap-1">
                        <select id="filterTeam" onchange="refresh()"
                            class="flex-1 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs text-gray-800
                                   focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white">
                            <option value="all">Semua</option>
                            <option value="Tim A">Tim A</option>
                            <option value="Tim B">Tim B</option>
                            <option value="none">Tanpa Tim</option>
                        </select>
                        <select id="filterTeamMode" onchange="refresh()" title="Mode filter tim"
                            class="w-20 border border-gray-200 rounded-lg px-1 py-1.5 text-[10px] text-gray-600
                                   focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white">
                            <option value="snapshot">Snap</option>
                            <option value="live">Live</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-[10px] uppercase tracking-wider text-gray-500 font-bold mb-1">
                        Partisipasi
                    </label>
                    <select id="filterNoParticipation" onchange="refresh()"
                        class="w-full border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs text-gray-800
                               focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-white">
                        <option value="all">Semua</option>
                        <option value="yes">Ada Skip</option>
                        <option value="no">Tanpa Skip</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button onclick="resetFilters()"
                    class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Reset Filter
                </button>
            </div>
        </div>

        {{-- ==================== SUMMARY CARDS ==================== --}}
        <div id="summaryRow" class="grid grid-cols-2 md:grid-cols-6 gap-3">
            @for ($i = 0; $i < 6; $i++)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 h-20 animate-pulse"></div>
            @endfor
        </div>

        {{-- ==================== BREAKDOWN PER DIVISI ==================== --}}
        <div id="divisionRow" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 h-32 animate-pulse"></div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 h-32 animate-pulse"></div>
        </div>

        {{-- ==================== BREAKDOWN PER TEKNISI ==================== --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Rekap Per Teknisi
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Berdasarkan <b>status per-teknisi</b> · Progress dihitung dari partisipasi (exclude skip)
                    </p>
                </div>
                <div class="text-xs text-gray-500">
                    Total: <span id="techCount" class="font-bold text-indigo-600">0</span> teknisi
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="py-3 px-4 font-semibold text-left">Teknisi</th>
                            <th class="py-3 px-3 font-semibold text-center">Divisi</th>
                            <th class="py-3 px-3 font-semibold text-center">Total</th>
                            <th class="py-3 px-3 font-semibold text-center">✓ Selesai</th>
                            <th class="py-3 px-3 font-semibold text-center">⚙ Proses</th>
                            <th class="py-3 px-3 font-semibold text-center">⏸ Pending</th>
                            <th class="py-3 px-3 font-semibold text-center text-rose-600">⊘ Skip</th>
                            <th class="py-3 px-4 font-semibold w-1/4">Progress</th>
                        </tr>
                    </thead>
                    <tbody id="techTable" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="8" class="text-center py-6 text-gray-400 text-sm">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ==================== BREAKDOWN PER TIM ==================== --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Breakdown Per Tim
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Snapshot (tim saat WO dibuat) & Live (tim bulan ini) · Exclude skip
                    </p>
                </div>

                <div class="flex gap-1 text-xs">
                    <button onclick="setTeamBreakdownMode('snapshot')" data-tbm="snapshot"
                        class="tbm-btn px-3 py-1.5 rounded-lg bg-indigo-600 text-white font-semibold">
                        Snapshot
                    </button>
                    <button onclick="setTeamBreakdownMode('live')" data-tbm="live"
                        class="tbm-btn px-3 py-1.5 rounded-lg text-gray-600 hover:bg-gray-100 font-semibold">
                        Live
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="py-3 px-4 font-semibold text-left">Tim</th>
                            <th class="py-3 px-3 font-semibold text-center">Total</th>
                            <th class="py-3 px-3 font-semibold text-center">✓ Selesai</th>
                            <th class="py-3 px-3 font-semibold text-center">⚙ Proses</th>
                            <th class="py-3 px-3 font-semibold text-center">⏸ Pending</th>
                            <th class="py-3 px-3 font-semibold text-center text-rose-600">⊘ Skip</th>
                            <th class="py-3 px-4 font-semibold w-1/4">Progress</th>
                        </tr>
                    </thead>
                    <tbody id="teamTable" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="7" class="text-center py-6 text-gray-400 text-sm">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ==================== DETAIL TABLE ==================== --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h2 class="font-semibold text-gray-800">Detail Work Order</h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Menampilkan <span id="detailCount" class="font-bold text-indigo-600">0</span> WO
                        · Maksimal 1000 baris
                    </p>
                </div>

                <input type="text" id="searchDetail" placeholder="Cari kode / judul..." oninput="renderDetail()"
                    class="border border-gray-200 rounded-lg px-3 py-1.5 text-xs
                           focus:outline-none focus:ring-1 focus:ring-indigo-500 w-56">
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500 border-b border-gray-200">
                        <tr>
                            <th class="py-3 px-3 font-semibold text-left w-24">Kode</th>
                            <th class="py-3 px-3 font-semibold text-left">Judul</th>
                            <th class="py-3 px-3 font-semibold text-center w-20">Divisi</th>
                            <th class="py-3 px-3 font-semibold text-center w-20">Mode</th>
                            <th class="py-3 px-3 font-semibold text-center w-24">Status</th>
                            <th class="py-3 px-3 font-semibold text-left w-48">Teknisi</th>
                            <th class="py-3 px-3 font-semibold text-center w-24">Progress</th>
                            <th class="py-3 px-3 font-semibold text-left w-40">Lokasi</th>
                            <th class="py-3 px-3 font-semibold text-center w-32">Dibuat</th>
                        </tr>
                    </thead>
                    <tbody id="detailTable" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="9" class="text-center py-6 text-gray-400 text-sm">Loading...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
    <script>
        const API_DATA = "{{ route('report.data') }}";
        const API_EXPORT = "{{ route('report.export') }}";

        let currentRange = 'all';
        let customFrom = null;
        let customTo = null;
        let teamBreakdownMode = 'snapshot';
        let lastData = null;

        function esc(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function divisionBadge(division) {
            const map = {
                'cctv': {
                    label: 'CCTV',
                    cls: 'bg-purple-50 text-purple-700 border-purple-200'
                },
                'helpdesk': {
                    label: 'Helpdesk',
                    cls: 'bg-teal-50 text-teal-700 border-teal-200'
                },
            };
            const s = map[division] || {
                label: division || '-',
                cls: 'bg-gray-50 text-gray-600 border-gray-200'
            };
            return `<span class="inline-flex items-center text-[10px] uppercase tracking-wide font-bold
                             px-1.5 py-0.5 rounded border ${s.cls}">${s.label}</span>`;
        }

        function modeBadge(mode) {
            if (mode === 'collective') {
                return `<span class="inline-flex items-center gap-0.5 text-[10px] uppercase tracking-wide font-bold
                                 px-1.5 py-0.5 rounded border bg-blue-50 text-blue-700 border-blue-200"
                             title="Global — 1 selesai semua selesai">
                            👥 Tim
                        </span>`;
            }
            return `<span class="inline-flex items-center gap-0.5 text-[10px] uppercase tracking-wide font-bold
                             px-1.5 py-0.5 rounded border bg-violet-50 text-violet-700 border-violet-200"
                         title="Individual — per teknisi">
                        👤 Individu
                    </span>`;
        }

        function statusBadge(status) {
            const map = {
                'pending': {
                    label: 'Pending',
                    cls: 'bg-gray-100 text-gray-600'
                },
                'in_progress': {
                    label: 'Proses',
                    cls: 'bg-amber-50 text-amber-700'
                },
                'completed': {
                    label: 'Selesai',
                    cls: 'bg-emerald-50 text-emerald-700'
                },
                'cancelled': {
                    label: 'Batal',
                    cls: 'bg-red-50 text-red-700'
                },
            };
            const s = map[status] || {
                label: status,
                cls: 'bg-gray-100 text-gray-600'
            };
            return `<span class="inline-block text-xs px-2 py-0.5 rounded-md font-semibold ${s.cls}">${s.label}</span>`;
        }

        function formatDate(iso) {
            if (!iso) return '-';
            const d = new Date(iso);
            return d.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short',
                year: 'numeric'
            });
        }

        function buildQuery() {
            const params = new URLSearchParams();

            params.append('range', currentRange);
            if (currentRange === 'custom' && customFrom && customTo) {
                params.append('from', customFrom);
                params.append('to', customTo);
            }

            const division = document.getElementById('filterDivision').value;
            if (division !== 'all') params.append('division', division);

            const mode = document.getElementById('filterMode').value;
            if (mode !== 'all') params.append('completion_mode', mode);

            const status = document.getElementById('filterStatus').value;
            if (status !== 'all') params.append('status', status);

            const techId = document.getElementById('filterTechnician').value;
            if (techId !== 'all') params.append('technician_id', techId);

            const team = document.getElementById('filterTeam').value;
            if (team !== 'all') params.append('team', team);

            const teamMode = document.getElementById('filterTeamMode').value;
            params.append('team_mode', teamMode);

            const noPart = document.getElementById('filterNoParticipation').value;
            if (noPart !== 'all') params.append('no_participation', noPart);

            return params.toString();
        }

        function setRangePreset(range) {
            currentRange = range;
            customFrom = null;
            customTo = null;

            document.querySelectorAll('.range-btn').forEach(btn => {
                const isActive = btn.dataset.range === range;
                if (isActive) {
                    btn.className =
                        'range-btn px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-600 text-white transition';
                } else {
                    btn.className =
                        'range-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:bg-gray-100 border border-gray-200 transition';
                }
            });

            const label = document.getElementById('customRangeLabel');
            if (label) label.textContent = 'Custom';

            closeCustomPicker();
            refresh();
        }

        function toggleCustomPicker(e) {
            e.stopPropagation();
            document.getElementById('customPickerDropdown').classList.toggle('hidden');
        }

        function closeCustomPicker() {
            document.getElementById('customPickerDropdown').classList.add('hidden');
            document.getElementById('customError').classList.add('hidden');
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

        document.addEventListener('click', e => {
            const wrapper = document.getElementById('customRangeWrapper');
            if (wrapper && !wrapper.contains(e.target)) closeCustomPicker();
        });

        function setTeamBreakdownMode(mode) {
            teamBreakdownMode = mode;
            document.querySelectorAll('.tbm-btn').forEach(btn => {
                if (btn.dataset.tbm === mode) {
                    btn.className =
                        'tbm-btn px-3 py-1.5 rounded-lg bg-indigo-600 text-white font-semibold';
                } else {
                    btn.className =
                        'tbm-btn px-3 py-1.5 rounded-lg text-gray-600 hover:bg-gray-100 font-semibold';
                }
            });
            if (lastData) renderTeamBreakdown(lastData);
        }

        function resetFilters() {
            document.getElementById('filterDivision').value = 'all';
            document.getElementById('filterMode').value = 'all';
            document.getElementById('filterStatus').value = 'all';
            document.getElementById('filterTechnician').value = 'all';
            document.getElementById('filterTeam').value = 'all';
            document.getElementById('filterTeamMode').value = 'snapshot';
            document.getElementById('filterNoParticipation').value = 'all';

            setRangePreset('all');
        }

        function renderSummary(s) {
            const totalSkip = (lastData?.byTechnician || []).reduce((sum, t) => sum + (t.skipped || 0), 0);

            const items = [{
                    label: 'Total WO',
                    value: s.total,
                    color: 'text-gray-800',
                    bar: 'border-indigo-500'
                },
                {
                    label: 'Selesai',
                    value: s.completed,
                    color: 'text-emerald-600',
                    bar: 'border-emerald-500'
                },
                {
                    label: 'Proses',
                    value: s.in_progress,
                    color: 'text-amber-600',
                    bar: 'border-amber-500'
                },
                {
                    label: 'Pending',
                    value: s.pending,
                    color: 'text-gray-500',
                    bar: 'border-gray-400'
                },
                {
                    label: 'Batal',
                    value: s.cancelled,
                    color: 'text-red-600',
                    bar: 'border-red-500'
                },
                {
                    label: '⊘ Skip Teknisi',
                    value: totalSkip,
                    color: 'text-rose-600',
                    bar: 'border-rose-500'
                },
            ];

            document.getElementById('summaryRow').innerHTML = items.map(i => `
                <div class="bg-white rounded-2xl shadow-sm border-l-4 ${i.bar} border-y border-r border-gray-100 p-4">
                    <div class="text-[10px] text-gray-500 uppercase tracking-wide font-bold">${i.label}</div>
                    <div class="text-2xl font-bold ${i.color} mt-1">${i.value}</div>
                </div>
            `).join('');
        }

        function renderDivision(byDivision) {
            const cctv = byDivision.cctv || {
                total: 0,
                completed: 0,
                in_progress: 0,
                pending: 0
            };
            const hd = byDivision.helpdesk || {
                total: 0,
                completed: 0,
                in_progress: 0,
                pending: 0
            };

            document.getElementById('divisionRow').innerHTML = `
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
                            <div class="text-purple-700 font-bold text-lg">${cctv.in_progress}</div>
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
                            <div class="text-teal-700 font-bold text-lg">${hd.in_progress}</div>
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

        function renderTechnicians(list) {
            const tbody = document.getElementById('techTable');
            document.getElementById('techCount').textContent = list.length;

            if (!list.length) {
                tbody.innerHTML =
                    '<tr><td colspan="8" class="text-center py-6 text-gray-400 text-sm">Belum ada data teknisi.</td></tr>';
                return;
            }

            const sorted = [...list].sort((a, b) => b.completed - a.completed || a.name.localeCompare(b.name));

            tbody.innerHTML = sorted.map(t => {
                const participating = t.participating ?? t.total;
                const pct = participating > 0 ? Math.round((t.completed / participating) * 100) : 0;
                const skipped = t.skipped ?? 0;

                const isCctv = t.division === 'cctv';
                const barColor = isCctv ? 'from-purple-500 to-purple-400' : 'from-teal-500 to-teal-400';
                const textColor = isCctv ? 'text-purple-700' : 'text-teal-700';
                const avatarGradient = isCctv ? 'from-purple-500 to-purple-600' : 'from-teal-500 to-teal-600';

                const skipBadge = skipped > 0 ?
                    `<span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-rose-50 text-rose-700 font-bold text-sm"
                           title="${skipped} WO tidak diikuti">
                        ${skipped}
                    </span>` :
                    `<span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-50 text-gray-300 font-bold text-sm">
                        —
                    </span>`;

                return `
                    <tr class="hover:bg-indigo-50/30 transition">
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br ${avatarGradient}
                                            flex items-center justify-center text-white font-bold text-xs shrink-0">
                                    ${t.name.charAt(0).toUpperCase()}
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-800 text-sm">${esc(t.name)}</div>
                                    <div class="text-[10px] text-gray-400">@${esc(t.username)}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3 px-3 text-center">${divisionBadge(t.division)}</td>
                        <td class="py-3 px-3 text-center text-gray-700 font-semibold">${t.total}</td>
                        <td class="py-3 px-3 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 font-bold text-sm">
                                ${t.completed}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 text-amber-700 font-bold text-sm">
                                ${t.in_progress}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-600 font-bold text-sm">
                                ${t.pending}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-center">
                            ${skipBadge}
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                                    <div class="bg-gradient-to-r ${barColor} h-2 rounded-full transition-all"
                                         style="width:${pct}%"></div>
                                </div>
                                <span class="text-xs ${textColor} font-semibold tabular-nums w-12 text-right">
                                    ${pct}%
                                </span>
                            </div>
                            <div class="text-[10px] text-gray-400 mt-0.5">
                                ${t.completed} dari ${participating} partisipasi${skipped > 0 ? ` · <span class="text-rose-400">${skipped} skip</span>` : ''}
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function renderTeamBreakdown(data) {
            const tbody = document.getElementById('teamTable');
            const source = teamBreakdownMode === 'snapshot' ? data.byTeamSnapshot : data.byTeamLive;

            const entries = Object.entries(source || {});

            if (!entries.length) {
                tbody.innerHTML =
                    '<tr><td colspan="7" class="text-center py-6 text-gray-400 text-sm">Belum ada data tim.</td></tr>';
                return;
            }

            tbody.innerHTML = entries.map(([teamName, t]) => {
                const pctCompleted = t.total > 0 ? Math.round((t.completed / t.total) * 100) : 0;
                const skipped = t.skipped ?? 0;

                const isNoTeam = teamName === 'Tanpa Tim';
                const teamCls = isNoTeam ?
                    'bg-gray-100 text-gray-600 border-gray-200' :
                    'bg-indigo-50 text-indigo-700 border-indigo-200';

                const skipBadge = skipped > 0 ?
                    `<span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-rose-50 text-rose-700 font-bold text-sm"
                           title="${skipped} WO tidak diikuti">
                        ${skipped}
                    </span>` :
                    `<span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-50 text-gray-300 font-bold text-sm">
                        —
                    </span>`;

                return `
                    <tr class="hover:bg-indigo-50/30 transition">
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center gap-1 text-xs font-bold uppercase tracking-wide
                                         px-2 py-1 rounded-lg border ${teamCls}">
                                ${teamName}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-center text-gray-700 font-semibold">${t.total}</td>
                        <td class="py-3 px-3 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 font-bold text-sm">
                                ${t.completed}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 text-amber-700 font-bold text-sm">
                                ${t.in_progress}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-gray-600 font-bold text-sm">
                                ${t.pending}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-center">
                            ${skipBadge}
                        </td>
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                                    <div class="bg-gradient-to-r from-indigo-500 to-indigo-400 h-2 rounded-full transition-all"
                                         style="width:${pctCompleted}%"></div>
                                </div>
                                <span class="text-xs text-indigo-700 font-semibold tabular-nums w-12 text-right">
                                    ${pctCompleted}%
                                </span>
                            </div>
                            <div class="text-[10px] text-gray-400 mt-0.5">
                                ${t.completed} / ${t.total} selesai${skipped > 0 ? ` · <span class="text-rose-400">${skipped} skip</span>` : ''}
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function renderDetail() {
            const tbody = document.getElementById('detailTable');
            if (!lastData) return;

            const q = (document.getElementById('searchDetail').value || '').toLowerCase().trim();

            let list = lastData.detail || [];
            if (q) {
                list = list.filter(wo =>
                    (wo.code || '').toLowerCase().includes(q) ||
                    (wo.title || '').toLowerCase().includes(q)
                );
            }

            document.getElementById('detailCount').textContent = list.length;

            if (!list.length) {
                tbody.innerHTML =
                    '<tr><td colspan="9" class="text-center py-6 text-gray-400 text-sm">Belum ada work order.</td></tr>';
                return;
            }

            tbody.innerHTML = list.map(wo => {
                const isIndividual = wo.completion_mode === 'individual';

                const totalTechs = wo.team_progress?.total || 0;
                const completedTechs = wo.team_progress?.completed || 0;
                const skippedTechs = wo.team_progress?.skipped || 0;

                const progressText = totalTechs > 0 ? `${completedTechs}/${totalTechs}` : '—';
                const progressPct = totalTechs > 0 ? Math.round((completedTechs / totalTechs) * 100) : 0;

                const participating = (wo.technicians || []).filter(t => t.pivot_no_participation !== true);
                const skipped = (wo.technicians || []).filter(t => t.pivot_no_participation === true);

                const techHtml = participating.slice(0, 3).map(t => {
                    const isDone = t.pivot_status === 'completed';
                    return `
                        <span class="inline-flex items-center gap-1 text-[11px]
                                     ${isDone ? 'text-emerald-700' : 'text-gray-600'}">
                            ${esc(t.name.split(' ')[0])}
                            ${isDone ? '✓' : ''}
                        </span>
                    `;
                }).join(', ');

                const moreTech = participating.length > 3 ?
                    ` <span class="text-[10px] text-gray-400">+${participating.length - 3}</span>` :
                    '';

                const skipBadge = skipped.length > 0 ?
                    `<span class="ml-1 inline-flex items-center text-[10px] font-bold
                                 px-1.5 py-0.5 rounded bg-rose-50 text-rose-600 border border-rose-200"
                           title="Skip: ${skipped.map(t => t.name).join(', ')}">
                        ⊘${skipped.length}
                    </span>` :
                    '';

                const loc = [wo.building, wo.location].filter(Boolean).join(' — ') || '—';

                return `
                    <tr class="hover:bg-indigo-50/30 transition cursor-pointer"
                        onclick="window.location.href='/work-orders/${wo.id}'">
                        <td class="py-3 px-3">
                            <span class="font-mono text-xs text-gray-500">${esc(wo.code)}</span>
                        </td>
                        <td class="py-3 px-3">
                            <div class="font-semibold text-gray-800 text-sm line-clamp-1">${esc(wo.title)}</div>
                        </td>
                        <td class="py-3 px-3 text-center">${divisionBadge(wo.division)}</td>
                        <td class="py-3 px-3 text-center">${modeBadge(wo.completion_mode)}</td>
                        <td class="py-3 px-3 text-center">${statusBadge(wo.status)}</td>
                        <td class="py-3 px-3">
                            <div class="text-xs">${techHtml}${moreTech}${skipBadge}</div>
                        </td>
                        <td class="py-3 px-3 text-center">
                            <div class="text-xs font-semibold ${progressPct === 100 ? 'text-emerald-600' : 'text-gray-500'}">
                                ${progressText}
                            </div>
                            ${isIndividual && totalTechs > 1 ? `
                                                                                <div class="w-full bg-gray-100 rounded-full h-1 mt-1 overflow-hidden">
                                                                                    <div class="bg-violet-500 h-1 rounded-full" style="width:${progressPct}%"></div>
                                                                                </div>
                                                                            ` : ''}
                        </td>
                        <td class="py-3 px-3 text-xs text-gray-600 line-clamp-1">${esc(loc)}</td>
                        <td class="py-3 px-3 text-center text-xs text-gray-500">${formatDate(wo.created_at)}</td>
                    </tr>
                `;
            }).join('');
        }

        function renderAll(data) {
            renderSummary(data.summary);
            renderDivision(data.byDivision);
            renderTechnicians(data.byTechnician || []);
            renderTeamBreakdown(data);
            renderDetail();
        }
        async function refresh() {
            try {
                const res = await fetch(`${API_DATA}?${buildQuery()}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!res.ok) throw new Error('HTTP ' + res.status);

                const data = await res.json();
                lastData = data;
                renderAll(data);
            } catch (err) {
                console.error('Report fetch error:', err);
                showToast('Gagal memuat report: ' + err.message, 'error');
            }
        }

        function toggleExportMenu(e) {
            e.stopPropagation();
            document.getElementById('exportMenu').classList.toggle('hidden');
        }

        document.addEventListener('click', e => {
            const wrapper = document.getElementById('exportWrapper');
            if (wrapper && !wrapper.contains(e.target)) {
                document.getElementById('exportMenu').classList.add('hidden');
            }
        });

        async function exportExcel(mode = 'wo') {
            const includePhotos = document.getElementById('includePhotos').checked;
            document.getElementById('exportMenu').classList.add('hidden');
            const params = new URLSearchParams(buildQuery());
            params.append('export_mode', mode);
            if (includePhotos) params.append('include_photos', '1');

            const url = `${API_EXPORT}?${params.toString()}`;
            const a = document.createElement('a');
            a.href = url;
            a.style.display = 'none';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);

            showToast('File Excel sedang diunduh...', 'success');
        }
        refresh();
    </script>
@endpush
