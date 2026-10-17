@extends('layouts.app')

@section('title', 'Laporan Saya')

@section('content')

    @php
        $user = auth()->user();
        $division = $user->division;
        $divisionLabel = $division === 'cctv' ? 'CCTV' : 'Helpdesk';
        $divisionClass = $division === 'cctv' ? 'bg-purple-100 text-purple-700' : 'bg-teal-100 text-teal-700';
    @endphp

    <div class="max-w-7xl mx-auto space-y-6">

        {{-- ==================== HEADER ==================== --}}
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                    Laporan Saya
                    <span class="text-xs px-2.5 py-1 rounded-lg font-bold uppercase tracking-wide {{ $divisionClass }}">
                        {{ $divisionLabel }}
                    </span>
                </h1>
                <p class="text-sm text-gray-500 mt-1">
                    Rekap semua work order yang ditugaskan ke Anda ({{ $user->name }})
                </p>
            </div>

            <div class="flex gap-2 flex-wrap">
                {{-- Export WO (1 baris = 1 WO) --}}
                <button onclick="exportReport('wo')"
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
                    Export WO
                </button>

                {{-- Export Per Teknisi (1 baris = 1 teknisi × WO) --}}
                <button onclick="exportReport('technician')"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl
                           bg-gradient-to-br from-violet-500 to-violet-600
                           hover:from-violet-600 hover:to-violet-700
                           text-white text-sm font-semibold
                           shadow-lg shadow-violet-500/30 transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export Detail
                </button>
            </div>
        </div>

        {{-- ==================== FILTER BAR ==================== --}}
        <div
            class="bg-white rounded-2xl shadow-sm border border-gray-100 px-4 py-3
                    flex flex-wrap items-center justify-between gap-3">

            <div class="flex items-center gap-2 text-xs text-gray-500">
                <span class="font-semibold">Periode & Status:</span>
            </div>

            <div class="flex gap-1 items-center flex-wrap">

                {{-- Range --}}
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

                {{-- Custom Range --}}
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
                                           focus:outline-none focus:ring-1 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-[10px] uppercase tracking-wider text-gray-500 font-bold mb-1">
                                    Sampai Tanggal
                                </label>
                                <input type="date" id="customTo"
                                    class="w-full bg-gray-50 border border-gray-200 rounded-lg px-2.5 py-1.5 text-xs
                                           focus:outline-none focus:ring-1 focus:ring-indigo-500">
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

                <div class="w-px h-6 bg-gray-200 mx-1"></div>

                {{-- Status --}}
                <button onclick="setStatusFilter('all')" data-status="all"
                    class="status-btn px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-600 text-white transition">
                    Semua Status
                </button>
                <button onclick="setStatusFilter('pending')" data-status="pending"
                    class="status-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:bg-gray-100 border border-gray-200 transition">
                    Pending
                </button>
                <button onclick="setStatusFilter('in_progress')" data-status="in_progress"
                    class="status-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:bg-gray-100 border border-gray-200 transition">
                    Proses
                </button>
                <button onclick="setStatusFilter('completed')" data-status="completed"
                    class="status-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:bg-gray-100 border border-gray-200 transition">
                    Selesai
                </button>
            </div>
        </div>

        {{-- ==================== SUMMARY CARDS ==================== --}}
        <div id="summaryCards" class="grid grid-cols-2 md:grid-cols-5 gap-3">
            @for ($i = 0; $i < 5; $i++)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 h-24 animate-pulse"></div>
            @endfor
        </div>

        {{-- ==================== STATS PER TIM ==================== --}}
        <div id="teamStats" class="hidden"></div>

        {{-- ==================== TABEL DETAIL ==================== --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center flex-wrap gap-3">
                <div>
                    <h2 class="font-semibold text-gray-800">Detail Work Order</h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        <span id="detailCount">0</span> WO ditemukan
                        · <span id="rangeLabel" class="text-indigo-600 font-semibold">Semua Waktu</span>
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Kode</th>
                            <th class="px-4 py-3 text-left font-semibold">Judul</th>
                            <th class="px-4 py-3 text-left font-semibold">Kategori</th>
                            <th class="px-4 py-3 text-left font-semibold">Status</th>
                            <th class="px-4 py-3 text-left font-semibold">Prioritas</th>
                            <th class="px-4 py-3 text-left font-semibold">Lokasi</th>
                            <th class="px-4 py-3 text-left font-semibold">Dibuat</th>
                            <th class="px-4 py-3 text-left font-semibold">Mulai</th>
                            <th class="px-4 py-3 text-left font-semibold">Selesai</th>
                        </tr>
                    </thead>
                    <tbody id="detailBody" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="9" class="text-center py-8 text-gray-400 text-sm">
                                Loading...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
    <script>
        const MY_DIVISION = @json($division);

        let currentRange = 'all';
        let currentStatus = 'all';
        let customFrom = null;
        let customTo = null;
        let reportData = null;

        function buildQuery() {
            let qs = `?range=${currentRange}`;
            if (currentRange === 'custom' && customFrom && customTo) {
                qs += `&from=${customFrom}&to=${customTo}`;
            }
            if (currentStatus !== 'all') {
                qs += `&status=${currentStatus}`;
            }
            return qs;
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
            loadReport();
        }

        function toggleCustomPicker(e) {
            e.stopPropagation();
            document.getElementById('customPickerDropdown').classList.toggle('hidden');
        }

        function closeCustomPicker() {
            const d = document.getElementById('customPickerDropdown');
            if (d) d.classList.add('hidden');
        }

        document.addEventListener('click', (e) => {
            const wrapper = document.getElementById('customRangeWrapper');
            if (wrapper && !wrapper.contains(e.target)) closeCustomPicker();
        });

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
            loadReport();
        }

        function setStatusFilter(status) {
            currentStatus = status;
            document.querySelectorAll('.status-btn').forEach(btn => {
                const isActive = btn.dataset.status === status;
                btn.className = isActive ?
                    'status-btn px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-600 text-white transition' :
                    'status-btn px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 hover:bg-gray-100 border border-gray-200 transition';
            });
            loadReport();
        }
        async function loadReport() {
            try {
                const res = await api.get("{{ route('technician.report.data') }}" + buildQuery());
                reportData = res;

                renderSummary(res.summary);
                renderTeamStats(res.byTeamSnapshot || res.byTeamLive);
                renderDetail(res.detail);

                const detailCount = document.getElementById('detailCount');
                if (detailCount) detailCount.textContent = res.detail.length;

            } catch (err) {
                console.error('Report error:', err);
                if (typeof showToast === 'function') {
                    showToast('Gagal memuat laporan: ' + err.message, 'error');
                }
            }
        }

        function renderSummary(s) {
            const el = document.getElementById('summaryCards');
            if (!el) return;

            el.innerHTML = `
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <div class="text-[10px] text-gray-500 uppercase tracking-wide font-semibold">Total</div>
                    <div class="text-2xl font-bold text-gray-800 mt-2">${s.total}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <div class="text-[10px] text-emerald-500 uppercase tracking-wide font-semibold">Selesai</div>
                    <div class="text-2xl font-bold text-emerald-600 mt-2">${s.completed}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <div class="text-[10px] text-amber-500 uppercase tracking-wide font-semibold">Proses</div>
                    <div class="text-2xl font-bold text-amber-600 mt-2">${s.in_progress}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <div class="text-[10px] text-gray-500 uppercase tracking-wide font-semibold">Pending</div>
                    <div class="text-2xl font-bold text-gray-600 mt-2">${s.pending}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                    <div class="text-[10px] text-rose-500 uppercase tracking-wide font-semibold">Batal</div>
                    <div class="text-2xl font-bold text-rose-600 mt-2">${s.cancelled ?? 0}</div>
                </div>
            `;
        }

        function renderTeamStats(teams) {
            const el = document.getElementById('teamStats');
            if (!el) return;

            const entries = Object.entries(teams || {});
            if (!entries.length) {
                el.classList.add('hidden');
                return;
            }

            el.classList.remove('hidden');
            el.innerHTML = `
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                    <h2 class="font-semibold text-gray-800 mb-3">Rekap Per Tim</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        ${entries.map(([team, s]) => `
                                                                        <div class="bg-gray-50 border border-gray-100 rounded-xl p-3">
                                                                            <div class="text-xs font-bold text-gray-700 truncate mb-2">${team}</div>
                                                                            <div class="grid grid-cols-2 gap-1 text-[11px]">
                                                                                <div>Total: <b>${s.total}</b></div>
                                                                                <div class="text-emerald-600">Selesai: <b>${s.completed}</b></div>
                                                                                <div class="text-amber-600">Proses: <b>${s.in_progress}</b></div>
                                                                                <div class="text-gray-600">Pending: <b>${s.pending}</b></div>
                                                                            </div>
                                                                        </div>
                                                                    `).join('')}
                    </div>
                </div>
            `;
        }

        function renderDetail(items) {
            const body = document.getElementById('detailBody');
            if (!body) return;

            if (!items.length) {
                body.innerHTML = `
                    <tr>
                        <td colspan="9" class="text-center py-8 text-gray-400 text-sm">
                            Tidak ada data untuk filter ini.
                        </td>
                    </tr>
                `;
                return;
            }

            const statusStyle = {
                'pending': 'bg-gray-100 text-gray-600',
                'in_progress': 'bg-amber-100 text-amber-700',
                'completed': 'bg-emerald-100 text-emerald-700',
                'cancelled': 'bg-red-100 text-red-700',
            };

            const priorityStyle = {
                'urgent': 'bg-red-100 text-red-700',
                'high': 'bg-amber-100 text-amber-700',
                'normal': 'bg-gray-100 text-gray-600',
                'low': 'bg-gray-50 text-gray-500',
            };

            const statusLabel = {
                'pending': 'Pending',
                'in_progress': 'Proses',
                'completed': 'Selesai',
                'cancelled': 'Batal',
            };

            const priorityLabel = {
                'urgent': 'Urgent',
                'high': 'High',
                'normal': 'Normal',
                'low': 'Low',
            };

            body.innerHTML = items.map(wo => {
                const statusCls = statusStyle[wo.status] || statusStyle.pending;
                const prioCls = priorityStyle[wo.priority] || priorityStyle.normal;
                const loc = [wo.building, wo.location].filter(Boolean).join(' — ') || '-';

                const startedFmt = wo.started_at ?
                    new Date(wo.started_at).toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    }) :
                    '—';

                const completedFmt = wo.completed_at ?
                    new Date(wo.completed_at).toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    }) :
                    '—';

                const createdFmt = wo.created_at ?
                    new Date(wo.created_at).toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric'
                    }) :
                    '—';
                return `
                    <tr class="hover:bg-indigo-50/30 transition cursor-pointer"
                        onclick="window.location.href='/work-orders/${wo.id}'">
                        <td class="px-4 py-3">
                            <span class="font-mono text-xs text-indigo-600">${wo.code}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-800 font-medium max-w-xs truncate" title="${wo.title}">
                            ${wo.title}
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs">${wo.category || '-'}</td>
                        <td class="px-4 py-3">
                            <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded ${statusCls}">
                                ${statusLabel[wo.status] || wo.status}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded ${prioCls}">
                                ${priorityLabel[wo.priority] || wo.priority}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-xs max-w-xs truncate" title="${loc}">${loc}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">${createdFmt}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">${startedFmt}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">${completedFmt}</td>
                    </tr>
                `;
            }).join('');
        }

        function exportReport(mode) {
            let url = "{{ route('technician.report.export') }}?range=" + currentRange +
                "&export_mode=" + mode;

            if (currentRange === 'custom' && customFrom && customTo) {
                url += `&from=${customFrom}&to=${customTo}`;
            }
            if (currentStatus !== 'all') {
                url += `&status=${currentStatus}`;
            }

            if (typeof showToast === 'function') {
                showToast('Menyiapkan file export...', 'info');
            }

            window.location.href = url;
        }
        loadReport();
    </script>
@endpush
