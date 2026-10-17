@extends('layouts.app')

@section('title', 'Work Order')

@section('content')

    @php
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
        $myDivision = $user->division;
    @endphp

    <div class="max-w-7xl mx-auto space-y-5">

        {{-- ============ HEADER ============ --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Work Order</h1>
                <p class="text-sm text-gray-500 mt-1">
                    @if ($isAdmin)
                        Semua work order dari seluruh divisi
                    @else
                        Tugas divisi
                        <b>{{ $myDivision === 'cctv' ? 'CCTV' : 'Helpdesk' }}</b>
                        untuk Anda
                    @endif
                </p>
            </div>

            <div class="flex gap-2 flex-wrap">
                @if ($isAdmin)
                    {{-- Download Template --}}
                    <a href="{{ route('admin.wo.template') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg
                       bg-white border border-gray-200
                       text-gray-700 hover:bg-gray-50 hover:border-gray-300
                       text-sm font-semibold shadow-sm transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Template
                    </a>

                    {{-- Import Excel --}}
                    <a href="{{ route('admin.wo.import-form') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg
                       bg-emerald-600 hover:bg-emerald-700
                       text-white text-sm font-semibold shadow-sm transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        Import
                    </a>

                    {{-- Buat Work Order --}}
                    <a href="{{ route('admin.wo.create') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg
                       bg-indigo-600 hover:bg-indigo-700
                       text-white text-sm font-semibold shadow-sm transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Buat Work Order
                    </a>
                @else
                    <a href="{{ route('technician.wo.create') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg
                       bg-indigo-600 hover:bg-indigo-700
                       text-white text-sm font-semibold shadow-sm transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Tugas Mandiri
                    </a>
                @endif
            </div>
        </div>

        {{-- ============ STATS CARDS ============ --}}
        <div id="statsRow" class="grid grid-cols-2 md:grid-cols-4 {{ !$isAdmin ? 'lg:grid-cols-5' : '' }} gap-3">
            @for ($i = 0; $i < 4; $i++)
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 h-20 animate-pulse"></div>
            @endfor
        </div>

        {{-- ============ FILTER ============ --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 space-y-3">
            <div class="flex justify-between items-center flex-wrap gap-3">
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
                <div class="text-xs text-gray-400" id="counter">Memuat...</div>
            </div>

            {{-- Filter Divisi (khusus admin) --}}
            @if ($isAdmin)
                <div class="flex gap-1 text-xs pt-3 border-t border-gray-100 flex-wrap">
                    <span class="text-gray-400 font-semibold mr-1 py-1.5">Filter Divisi:</span>
                    <button onclick="setDivisionFilter('all')" data-divfilter="all"
                        class="divfilter-btn px-3 py-1.5 rounded-lg bg-gray-800 text-white font-semibold">
                        Semua
                    </button>
                    <button onclick="setDivisionFilter('cctv')" data-divfilter="cctv"
                        class="divfilter-btn px-3 py-1.5 rounded-lg text-purple-700 hover:bg-purple-50 font-semibold">
                        📹 CCTV
                    </button>
                    <button onclick="setDivisionFilter('helpdesk')" data-divfilter="helpdesk"
                        class="divfilter-btn px-3 py-1.5 rounded-lg text-teal-700 hover:bg-teal-50 font-semibold">
                        🎧 Helpdesk
                    </button>
                </div>
            @endif

            {{-- Filter Tim (khusus admin) --}}
            @if ($isAdmin)
                <div class="flex gap-1 text-xs pt-3 border-t border-gray-100 flex-wrap">
                    <span class="text-gray-400 font-semibold mr-1 py-1.5">Filter Tim:</span>
                    <button onclick="setTeamFilter('all')" data-teamfilter="all"
                        class="teamfilter-btn px-3 py-1.5 rounded-lg bg-gray-800 text-white font-semibold">
                        Semua
                    </button>
                    <button onclick="setTeamFilter('A')" data-teamfilter="A"
                        class="teamfilter-btn px-3 py-1.5 rounded-lg text-indigo-700 hover:bg-indigo-50 font-semibold">
                        👥 Tim A
                    </button>
                    <button onclick="setTeamFilter('B')" data-teamfilter="B"
                        class="teamfilter-btn px-3 py-1.5 rounded-lg text-indigo-700 hover:bg-indigo-50 font-semibold">
                        👥 Tim B
                    </button>
                    <button onclick="setTeamFilter('none')" data-teamfilter="none"
                        class="teamfilter-btn px-3 py-1.5 rounded-lg text-gray-500 hover:bg-gray-100 font-semibold">
                        Tanpa Tim
                    </button>
                </div>
            @endif
        </div>

        {{-- ============ TABEL WORK ORDER ============ --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500 border-b border-gray-200">
                        <tr>
                            <th class="px-3 py-2.5 text-left font-semibold w-24">Kode</th>
                            <th class="px-3 py-2.5 text-left font-semibold">Judul</th>
                            <th class="px-3 py-2.5 text-left font-semibold w-24">Divisi</th>
                            <th class="px-3 py-2.5 text-left font-semibold w-24">Prioritas</th>
                            <th class="px-3 py-2.5 text-left font-semibold w-24">Mode</th>
                            <th class="px-3 py-2.5 text-left font-semibold w-32">Status</th>
                            <th class="px-3 py-2.5 text-left font-semibold w-36">Teknisi</th>
                            <th class="px-3 py-2.5 text-left font-semibold w-28">Tim</th>
                            <th class="px-3 py-2.5 text-left font-semibold w-40">Lokasi</th>
                            <th class="px-3 py-2.5 text-left font-semibold w-28">Dibuat</th>
                            <th class="px-3 py-2.5 text-left font-semibold w-28">Deadline</th>
                            <th class="px-3 py-2.5 text-center font-semibold w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="woTableBody" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="12" class="px-3 py-10 text-center text-gray-400 text-sm">
                                Loading...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                class="px-4 py-3 border-t border-gray-100 bg-gray-50 flex justify-between items-center text-xs text-gray-500">
                <div id="footerCounter">—</div>
                <div class="text-gray-400">Auto-refresh</div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
    <script>
        const IS_ADMIN = {{ $isAdmin ? 'true' : 'false' }};
        let currentFilter = 'all';
        let currentDivisionFilter = 'all';
        let currentTeamFilter = 'all';
        let allWorkOrders = [];
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
            return `<span class="inline-flex items-center gap-1 text-[10px] uppercase tracking-wide font-bold
                             px-1.5 py-0.5 rounded ${s.bg} ${s.text} border ${s.border}">
                        <span class="w-1 h-1 rounded-full ${s.dot}"></span>
                        ${s.label}
                    </span>`;
        }

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
        };

        const statusStyle = {
            'pending': {
                bg: 'bg-gray-100',
                text: 'text-gray-600',
                label: 'Pending'
            },
            'in_progress': {
                bg: 'bg-amber-50',
                text: 'text-amber-700',
                label: 'Proses'
            },
            'completed': {
                bg: 'bg-emerald-50',
                text: 'text-emerald-700',
                label: 'Selesai'
            },
            'cancelled': {
                bg: 'bg-red-50',
                text: 'text-red-700',
                label: 'Batal'
            },
        };

        function modeBadge(mode) {
            if (mode === 'collective') {
                return `<span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wide
                                 px-1.5 py-0.5 rounded border bg-blue-50 text-blue-700 border-blue-200"
                             title="1 teknisi selesai → semua selesai">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Tim
                        </span>`;
            }
            return `<span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wide
                             px-1.5 py-0.5 rounded border bg-violet-50 text-violet-700 border-violet-200"
                         title="Setiap teknisi selesai sendiri">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        Individu
                    </span>`;
        }

        function relativeTime(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            const diffMs = new Date() - date;
            const min = Math.floor(diffMs / 60000);
            const hour = Math.floor(diffMs / 3600000);
            const day = Math.floor(diffMs / 86400000);
            if (min < 1) return 'baru saja';
            if (min < 60) return `${min} mnt lalu`;
            if (hour < 24) return `${hour} jam lalu`;
            if (day < 7) return `${day} hari lalu`;
            return date.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'short'
            });
        }

        function esc(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function renderTechnicians(w) {
            const list = w.technicians || [];

            if (!list.length) {
                return '<span class="text-gray-400 italic">Belum di-assign</span>';
            }
            const participating = list.filter(t => t.pivot_no_participation !== true);
            const skipped = list.filter(t => t.pivot_no_participation === true);
            const skippedCount = skipped.length;
            const skipMiniBadge = skippedCount > 0 ?
                `<span class="inline-flex items-center gap-0.5 text-[9px] px-1 py-0.5 rounded font-bold
                     bg-rose-50 text-rose-600 border border-rose-200"
               title="${skippedCount} teknisi tidak partisipasi: ${skipped.map(t => t.name).join(', ')}">
            ⊘${skippedCount}
        </span>` :
                '';
            if (participating.length === 1 && skippedCount === 0) {
                const t = participating[0];
                return `
            <div class="flex items-center gap-1.5">
                <div class="w-5 h-5 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600
                            flex items-center justify-center text-white font-bold text-[9px] shrink-0">
                    ${t.name.charAt(0).toUpperCase()}
                </div>
                <span class="text-gray-700 font-medium truncate">${esc(t.name)}</span>
            </div>
        `;
            }
            if (participating.length === 0 && skippedCount > 0) {
                return `
            <span class="inline-flex items-center gap-1 text-xs text-rose-500 italic"
                  title="${skipped.map(t => t.name).join(', ')}">
                ⊘ ${skippedCount} teknisi skip
            </span>
        `;
            }
            return `
        <div class="flex items-center gap-1.5" title="${participating.map(t => t.name).join(', ')}${skippedCount > 0 ? ' | Skip: ' + skipped.map(t => t.name).join(', ') : ''}">
            <div class="flex -space-x-1.5 shrink-0">
                ${participating.slice(0, 3).map(t => `
                                                                        <div class="w-5 h-5 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600
                                                                                    flex items-center justify-center text-white font-bold text-[9px]
                                                                                    ring-2 ring-white">
                                                                            ${t.name.charAt(0).toUpperCase()}
                                                                        </div>
                                                                    `).join('')}
            </div>
            <span class="text-gray-700 font-medium">${participating.length} teknisi</span>
            ${skipMiniBadge}
        </div>
    `;
        }

        function renderTeams(w) {
            const list = w.technicians || [];
            if (!list.length) return '<span class="text-gray-300 italic text-xs">—</span>';
            const participating = list.filter(t => t.pivot_no_participation !== true);
            if (!participating.length) {
                return '<span class="text-gray-300 italic text-xs">—</span>';
            }

            const teamNames = [...new Set(participating.map(t => t.team_name).filter(Boolean))];

            if (!teamNames.length) {
                return '<span class="text-gray-300 italic text-xs">—</span>';
            }

            const isCctv = w.division === 'cctv';
            const cls = isCctv ?
                'bg-purple-50 text-purple-700 border-purple-200' :
                'bg-teal-50 text-teal-700 border-teal-200';

            if (teamNames.length === 1) {
                return `
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wide
                                 px-1.5 py-0.5 rounded border ${cls}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        ${teamNames[0]}
                    </span>
                `;
            }

            return `
                <div class="flex flex-wrap gap-1">
                    ${teamNames.map(name => `
                                                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wide
                                                                                 px-1.5 py-0.5 rounded border ${cls}">
                                                                        ${name}
                                                                    </span>
                                                                `).join('')}
                </div>
            `;
        }

        function woHasTeam(w, teamLetter) {
            const list = w.technicians || [];
            return list.some(t => t.team_name && t.team_name === `Tim ${teamLetter}`);
        }

        function woHasNoTeam(w) {
            const list = w.technicians || [];
            if (!list.length) return true;
            return !list.some(t => t.team_name);
        }

        function renderLocation(w) {
            if (!w.building && !w.location) {
                return '<span class="text-gray-400 italic">-</span>';
            }
            const parts = [];
            if (w.building) parts.push(`<span class="font-medium text-gray-700">${esc(w.building)}</span>`);
            if (w.location) parts.push(`<span class="text-gray-500">${esc(w.location)}</span>`);
            return parts.join('<span class="text-gray-300 mx-1">—</span>');
        }

        function renderStatusCell(w) {
            if (IS_ADMIN) {
                const s = statusStyle[w.status] || statusStyle.pending;
                const skipBadge = (w.team_progress && w.team_progress.skipped > 0) ?
                    `<span class="inline-flex items-center gap-0.5 text-[9px] px-1 py-0.5 rounded font-bold
                         bg-rose-50 text-rose-600 border border-rose-200 ml-1"
                   title="${w.team_progress.skipped} teknisi tidak partisipasi">
                ⊘ ${w.team_progress.skipped}
            </span>` :
                    '';

                return `
            <div class="flex items-center gap-1 flex-wrap">
                <span class="inline-block text-xs px-2 py-0.5 rounded-md font-semibold ${s.bg} ${s.text}">
                    ${s.label}
                </span>
                ${skipBadge}
            </div>
        `;
            }
            if (w.my_no_participation === true) {
                return `
            <div>
                <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-md font-semibold
                             bg-rose-50 text-rose-700 border border-rose-200">
                    ⊘ Skip
                </span>
                <div class="text-[10px] text-gray-400 mt-0.5">
                    Anda tidak berpartisipasi
                </div>
            </div>
        `;
            }
            const myStatus = w.my_status || w.status;
            const s = statusStyle[myStatus] || statusStyle.pending;
            let progressHtml = '';
            if (w.completion_mode === 'individual' && w.team_progress && w.team_progress.total > 1) {
                const skippedTxt = w.team_progress.skipped > 0 ?
                    ` · <span class="text-rose-400">${w.team_progress.skipped} skip</span>` :
                    '';

                progressHtml = `
            <div class="text-[10px] text-gray-400 mt-0.5">
                ${w.team_progress.completed}/${w.team_progress.total} selesai${skippedTxt}
            </div>
        `;
            }

            return `
        <div>
            <span class="inline-block text-xs px-2 py-0.5 rounded-md font-semibold ${s.bg} ${s.text}">
                ${s.label}
            </span>
            ${progressHtml}
        </div>
    `;
        }

        function renderActionButton(w) {
            if (IS_ADMIN) {
                return `<span class="text-xs text-gray-400 italic">—</span>`;
            }
            if (w.my_no_participation === true) {
                return `
            <span class="inline-flex items-center gap-1 text-xs text-rose-500 italic">
                ⊘ Skip
            </span>
        `;
            }

            const myStatus = w.my_status || w.status;

            if (myStatus === 'pending') {
                return `
            <button onclick="event.stopPropagation(); startWO(${w.id})"
                class="inline-flex items-center gap-1 text-xs font-semibold text-white
                       bg-indigo-600 hover:bg-indigo-700
                       px-2.5 py-1 rounded-md shadow-sm transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Mulai
            </button>`;
            }

            if (myStatus === 'in_progress') {
                return `
            <button onclick="event.stopPropagation(); goFinish(${w.id})"
                class="inline-flex items-center gap-1 text-xs font-semibold text-white
                       bg-emerald-600 hover:bg-emerald-700
                       px-2.5 py-1 rounded-md shadow-sm transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Selesai
            </button>`;
            }
            return `
        <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            Selesai
        </span>`;
        }

        async function loadWorkOrders() {
            try {
                const url = IS_ADMIN ?
                    "{{ route('admin.wo.data') }}" :
                    "{{ route('technician.wo.data') }}";

                allWorkOrders = await api.get(url);
                renderStats();
                renderTable();
            } catch (err) {
                console.error('WO error:', err);
                document.getElementById('woTableBody').innerHTML = `
                    <tr><td colspan="12" class="px-3 py-10 text-center text-red-500 text-sm">
                        Gagal memuat data: ${err.message}
                    </td></tr>`;
            }
        }

        function renderStats() {
            const list = allWorkOrders;
            const total = list.length;

            let pending, progress, completed;

            if (IS_ADMIN) {
                pending = list.filter(w => w.status === 'pending').length;
                progress = list.filter(w => w.status === 'in_progress').length;
                completed = list.filter(w => w.status === 'completed').length;
            } else {
                pending = list.filter(w => (w.my_status || w.status) === 'pending' && w.my_no_participation !==
                    true).length;
                progress = list.filter(w => (w.my_status || w.status) === 'in_progress' && w
                    .my_no_participation !== true).length;
                completed = list.filter(w => (w.my_status || w.status) === 'completed' && w
                    .my_no_participation !== true).length;
            }
            const skipCount = !IS_ADMIN ? list.filter(w => w.my_no_participation === true).length : 0;

            document.getElementById('statsRow').innerHTML = `
        <div class="bg-white rounded-lg shadow-sm border-l-4 border-gray-400 p-4">
            <div class="text-xs text-gray-500 uppercase font-semibold">Total</div>
            <div class="text-2xl font-bold text-gray-800">${total}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border-l-4 border-gray-500 p-4">
            <div class="text-xs text-gray-500 uppercase font-semibold">Pending</div>
            <div class="text-2xl font-bold text-gray-600">${pending}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border-l-4 border-amber-500 p-4">
            <div class="text-xs text-gray-500 uppercase font-semibold">Proses</div>
            <div class="text-2xl font-bold text-amber-600">${progress}</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border-l-4 border-emerald-500 p-4">
            <div class="text-xs text-gray-500 uppercase font-semibold">Selesai</div>
            <div class="text-2xl font-bold text-emerald-600">${completed}</div>
        </div>
        ${!IS_ADMIN && skipCount > 0 ? `
                                                                            <div class="bg-white rounded-lg shadow-sm border-l-4 border-rose-500 p-4">
                                                                                <div class="text-xs text-rose-500 uppercase font-semibold">⊘ Skip</div>
                                                                                <div class="text-2xl font-bold text-rose-600">${skipCount}</div>
                                                                            </div>
                                                                        ` : ''}
    `;
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

            renderTable();
        }

        function setDivisionFilter(div) {
            currentDivisionFilter = div;
            document.querySelectorAll('.divfilter-btn').forEach(btn => {
                const isActive = btn.dataset.divfilter === div;
                if (isActive) {
                    btn.className = 'divfilter-btn px-3 py-1.5 rounded-lg bg-gray-800 text-white font-semibold';
                } else {
                    if (btn.dataset.divfilter === 'cctv') {
                        btn.className =
                            'divfilter-btn px-3 py-1.5 rounded-lg text-purple-700 hover:bg-purple-50 font-semibold';
                    } else if (btn.dataset.divfilter === 'helpdesk') {
                        btn.className =
                            'divfilter-btn px-3 py-1.5 rounded-lg text-teal-700 hover:bg-teal-50 font-semibold';
                    } else {
                        btn.className =
                            'divfilter-btn px-3 py-1.5 rounded-lg text-gray-600 hover:bg-gray-100 font-semibold';
                    }
                }
            });
            renderTable();
        }

        function setTeamFilter(filter) {
            currentTeamFilter = filter;
            document.querySelectorAll('.teamfilter-btn').forEach(btn => {
                const isActive = btn.dataset.teamfilter === filter;
                if (isActive) {
                    btn.className = 'teamfilter-btn px-3 py-1.5 rounded-lg bg-indigo-600 text-white font-semibold';
                } else {
                    if (btn.dataset.teamfilter === 'none') {
                        btn.className =
                            'teamfilter-btn px-3 py-1.5 rounded-lg text-gray-500 hover:bg-gray-100 font-semibold';
                    } else {
                        btn.className =
                            'teamfilter-btn px-3 py-1.5 rounded-lg text-indigo-700 hover:bg-indigo-50 font-semibold';
                    }
                }
            });
            renderTable();
        }

        function renderTable() {
            const tbody = document.getElementById('woTableBody');
            const counter = document.getElementById('counter');
            const footerCounter = document.getElementById('footerCounter');

            let list = allWorkOrders;
            if (currentFilter !== 'all') {
                if (IS_ADMIN) {
                    list = list.filter(w => w.status === currentFilter);
                } else if (currentFilter === 'skipped') {
                    list = list.filter(w => w.my_no_participation === true);
                } else {
                    list = list.filter(w =>
                        (w.my_status || w.status) === currentFilter &&
                        w.my_no_participation !== true
                    );
                }
            }
            if (IS_ADMIN && currentDivisionFilter !== 'all') {
                list = list.filter(w => w.division === currentDivisionFilter);
            }
            if (IS_ADMIN && currentTeamFilter !== 'all') {
                if (currentTeamFilter === 'none') {
                    list = list.filter(w => woHasNoTeam(w));
                } else {
                    list = list.filter(w => woHasTeam(w, currentTeamFilter));
                }
            }
            const statusOrder = {
                pending: 0,
                in_progress: 1,
                completed: 2,
                skipped: 2.5,
                cancelled: 3
            };
            list = [...list].sort((a, b) => {
                let statusA, statusB;

                if (IS_ADMIN) {
                    statusA = a.status;
                    statusB = b.status;
                } else {
                    statusA = a.my_no_participation === true ? 'skipped' : (a.my_status || a.status);
                    statusB = b.my_no_participation === true ? 'skipped' : (b.my_status || b.status);
                }

                const orderDiff = (statusOrder[statusA] ?? 9) - (statusOrder[statusB] ?? 9);
                if (orderDiff !== 0) return orderDiff;
                return new Date(b.created_at) - new Date(a.created_at);
            });

            counter.textContent = `Menampilkan ${list.length} dari ${allWorkOrders.length} WO`;
            footerCounter.textContent = `${list.length} baris ditampilkan`;

            if (!list.length) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="12" class="px-3 py-16 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p class="text-sm text-gray-400">Tidak ada data.</p>
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = list.map(w => {
                const p = priorityStyle[w.priority] || priorityStyle.normal;
                const isSkipRow = !IS_ADMIN && w.my_no_participation === true;
                const rowClass = isSkipRow ?
                    'bg-rose-50/30 hover:bg-rose-50/50 transition cursor-pointer' :
                    'hover:bg-indigo-50/40 transition cursor-pointer';

                return `
                    <tr class="${rowClass}"
                        onclick="window.location.href='/work-orders/${w.id}'">

                        {{-- Kode --}}
                        <td class="px-3 py-2.5">
                            <span class="font-mono text-xs text-gray-500">${w.code}</span>
                        </td>

                        {{-- Judul --}}
                        <td class="px-3 py-2.5">
                            <div class="font-semibold text-gray-800 text-sm leading-snug line-clamp-1
                                        ${isSkipRow ? 'opacity-60' : ''}">
                                ${esc(w.title)}
                            </div>
                            ${w.description ? `
                                                                                <div class="text-xs text-gray-500 line-clamp-1 mt-0.5">
                                                                                    ${esc(w.description)}
                                                                                </div>
                                                                            ` : ''}
                        </td>

                        {{-- Divisi --}}
                        <td class="px-3 py-2.5">${divisionBadge(w.division)}</td>

                        {{-- Prioritas --}}
                        <td class="px-3 py-2.5">
                            <span class="inline-flex items-center gap-1.5 text-xs ${p.text} font-medium">
                                <span class="w-1.5 h-1.5 rounded-full ${p.dot}"></span>
                                ${p.label}
                            </span>
                        </td>

                        {{-- Mode --}}
                        <td class="px-3 py-2.5">${modeBadge(w.completion_mode)}</td>

                        {{-- Status (berdasar peran) --}}
                        <td class="px-3 py-2.5">${renderStatusCell(w)}</td>

                        {{-- Teknisi --}}
                        <td class="px-3 py-2.5 text-xs">${renderTechnicians(w)}</td>

                        {{-- Tim --}}
                        <td class="px-3 py-2.5">${renderTeams(w)}</td>

                        {{-- Lokasi --}}
                        <td class="px-3 py-2.5 text-xs">${renderLocation(w)}</td>

                        {{-- Dibuat --}}
                        <td class="px-3 py-2.5 text-xs">
                            ${w.created_at ? `
                                                                                <span class="text-gray-700">
                                                                                    ${new Date(w.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}
                                                                                </span>
                                                                            ` : `<span class="text-gray-400 italic">-</span>`}
                        </td>

                        {{-- Deadline --}}
                        <td class="px-3 py-2.5 text-xs">
                            ${w.deadline_at ? `
                                                                                <span class="text-gray-700">
                                                                                    ${new Date(w.deadline_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}
                                                                                </span>
                                                                            ` : `<span class="text-gray-400 italic">-</span>`}
                        </td>

                        {{-- Aksi --}}
                        <td class="px-3 py-2.5 text-center" onclick="event.stopPropagation();">
                            ${renderActionButton(w)}
                        </td>
                    </tr>
                `;
            }).join('');
        }

        async function startWO(id) {
            const ok = await showConfirm({
                title: 'Mulai Pekerjaan?',
                subtitle: 'Konfirmasi diperlukan',
                message: 'Tugas akan berubah status menjadi <strong>Proses</strong> untuk Anda.',
                okText: 'Ya, Mulai',
                cancelText: 'Batal',
                type: 'info',
            });

            if (!ok) return;

            try {
                await api.post(`/work-orders/${id}/start`);
                showToast('Tugas dimulai', 'success');
                await loadWorkOrders();
            } catch (err) {
                showToast('Gagal: ' + err.message, 'error');
            }
        }

        function goFinish(id) {
            window.location.href = `/work-orders/${id}`;
        }
        loadWorkOrders();
        setInterval(loadWorkOrders, 5000);
    </script>
@endpush
