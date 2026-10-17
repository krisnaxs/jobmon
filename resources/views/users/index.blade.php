@extends('layouts.app')

@section('title', 'Kelola User')

@section('content')

    @php
        $user = auth()->user();
        $isAdmin = $user->isAdmin();

        $roleBadge = [
            'admin' => ['bg-violet-50', 'text-violet-700', 'border-violet-200'],
            'technician' => ['bg-cyan-50', 'text-cyan-700', 'border-cyan-200'],
        ];
        $divBadge = [
            'cctv' => ['bg-purple-50', 'text-purple-700', 'border-purple-200'],
            'helpdesk' => ['bg-teal-50', 'text-teal-700', 'border-teal-200'],
        ];
    @endphp

    <div class="max-w-7xl mx-auto space-y-5">

        {{-- ============ HEADER ============ --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Kelola User</h1>
                <p class="text-sm text-gray-500 mt-1">
                    Manajemen akun <b>admin</b> & <b>teknisi</b> sistem
                </p>
            </div>

            <a href="{{ route('admin.users.create') }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg
                       bg-indigo-600 hover:bg-indigo-700
                       text-white text-sm font-semibold shadow-sm transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah User
            </a>
        </div>

        {{-- ============ FLASH ============ --}}
        @if (session('success'))
            <div
                class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg px-4 py-3 text-sm flex items-start gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600 shrink-0" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
                @foreach ($errors->all() as $err)
                    <div>{{ $err }}</div>
                @endforeach
            </div>
        @endif

        {{-- ============ STATS CARDS ============ --}}
        <div id="statsRow" class="grid grid-cols-2 md:grid-cols-4 gap-3">
            @for ($i = 0; $i < 4; $i++)
                <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 h-20 animate-pulse"></div>
            @endfor
        </div>

        {{-- ============ FILTER ============ --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 space-y-3">

            {{-- Filter Role --}}
            <div class="flex justify-between items-center flex-wrap gap-3">
                <div class="flex gap-1 text-xs">
                    <button onclick="setRoleFilter('all')" data-rolefilter="all"
                        class="rolefilter-btn px-3 py-1.5 rounded-lg bg-indigo-600 text-white font-semibold">
                        Semua
                    </button>
                    <button onclick="setRoleFilter('admin')" data-rolefilter="admin"
                        class="rolefilter-btn px-3 py-1.5 rounded-lg text-violet-700 hover:bg-violet-50 font-semibold">
                        👤 Admin
                    </button>
                    <button onclick="setRoleFilter('technician')" data-rolefilter="technician"
                        class="rolefilter-btn px-3 py-1.5 rounded-lg text-cyan-700 hover:bg-cyan-50 font-semibold">
                        🔧 Teknisi
                    </button>
                </div>
                <div class="text-xs text-gray-400" id="counter">Memuat...</div>
            </div>

            {{-- Filter Divisi --}}
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

            {{-- Filter Status --}}
            <div class="flex gap-1 text-xs pt-3 border-t border-gray-100 flex-wrap">
                <span class="text-gray-400 font-semibold mr-1 py-1.5">Filter Status:</span>
                <button onclick="setStatusFilter('all')" data-statusfilter="all"
                    class="statusfilter-btn px-3 py-1.5 rounded-lg bg-gray-800 text-white font-semibold">
                    Semua
                </button>
                <button onclick="setStatusFilter('active')" data-statusfilter="active"
                    class="statusfilter-btn px-3 py-1.5 rounded-lg text-emerald-700 hover:bg-emerald-50 font-semibold">
                    ✓ Aktif
                </button>
                <button onclick="setStatusFilter('inactive')" data-statusfilter="inactive"
                    class="statusfilter-btn px-3 py-1.5 rounded-lg text-red-700 hover:bg-red-50 font-semibold">
                    ✕ Nonaktif
                </button>
            </div>

            {{-- Filter Tim --}}
            <div class="flex gap-1 text-xs pt-3 border-t border-gray-100 flex-wrap">
                <span class="text-gray-400 font-semibold mr-1 py-1.5">Filter Tim:</span>
                <button onclick="setTeamFilter('all')" data-teamfilter="all"
                    class="teamfilter-btn px-3 py-1.5 rounded-lg bg-gray-800 text-white font-semibold">
                    Semua
                </button>
                <button onclick="setTeamFilter('Tim A')" data-teamfilter="Tim A"
                    class="teamfilter-btn px-3 py-1.5 rounded-lg text-indigo-700 hover:bg-indigo-50 font-semibold">
                    👥 Tim A
                </button>
                <button onclick="setTeamFilter('Tim B')" data-teamfilter="Tim B"
                    class="teamfilter-btn px-3 py-1.5 rounded-lg text-indigo-700 hover:bg-indigo-50 font-semibold">
                    👥 Tim B
                </button>
                <button onclick="setTeamFilter('none')" data-teamfilter="none"
                    class="teamfilter-btn px-3 py-1.5 rounded-lg text-gray-500 hover:bg-gray-100 font-semibold">
                    Tanpa Tim
                </button>
            </div>

        </div>

        {{-- ============ TABEL USER ============ --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs uppercase text-gray-500 border-b border-gray-200">
                        <tr>
                            <th class="px-3 py-2.5 text-left font-semibold w-24">Avatar</th>
                            <th class="px-3 py-2.5 text-left font-semibold">User</th>
                            <th class="px-3 py-2.5 text-left font-semibold w-56">Kontak</th>
                            <th class="px-3 py-2.5 text-left font-semibold w-28">Role</th>
                            <th class="px-3 py-2.5 text-left font-semibold w-28">Divisi</th>
                            <th class="px-3 py-2.5 text-left font-semibold w-28">Tim</th>
                            <th class="px-3 py-2.5 text-left font-semibold w-28">Status</th>
                            <th class="px-3 py-2.5 text-center font-semibold w-28">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody" class="divide-y divide-gray-100">
                        <tr>
                            <td colspan="8" class="px-3 py-10 text-center text-gray-400 text-sm">
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

    {{-- ==================== MODAL DELETE ==================== --}}
    <div id="modalDelete" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4"
        style="background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(4px);">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-start gap-4 mb-4">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.74-2.98L13.74 4.02a2 2 0 00-3.48 0L3.33 16.02A2 2 0 005.07 19z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Hapus User?</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        User <b id="deleteUserName" class="text-gray-800"></b> akan dihapus permanen.
                        Tindakan ini tidak bisa dibatalkan.
                    </p>
                </div>
            </div>
            <form id="deleteForm" method="POST">
                @csrf @method('DELETE')
                <div class="flex gap-2 justify-end">
                    <button type="button" onclick="closeDeleteModal()"
                        class="px-4 py-2 rounded-xl text-sm font-semibold text-gray-600 border border-gray-200 hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 rounded-xl text-sm font-semibold text-white
                               bg-red-600 hover:bg-red-700 shadow-lg shadow-red-500/30 transition">
                        Ya, Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ==================== LIGHTBOX AVATAR ==================== --}}
    <div id="avatarLightbox" class="hidden fixed inset-0 z-[60] items-center justify-center p-4"
        style="background-color: rgba(0,0,0,0.85); backdrop-filter: blur(4px);">

        {{-- Tombol Close --}}
        <button type="button" onclick="closeAvatarLightbox()"
            class="absolute top-4 right-4 text-white/80 hover:text-white bg-white/10 hover:bg-white/20
                   rounded-full p-2.5 transition z-10">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>

        {{-- Navigasi prev/next --}}
        <button type="button" id="lightboxPrev" onclick="event.stopPropagation(); lightboxNav(-1)"
            class="hidden absolute left-4 top-1/2 -translate-y-1/2 text-white/80 hover:text-white bg-white/10 hover:bg-white/20
                   rounded-full p-3 transition z-10">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <button type="button" id="lightboxNext" onclick="event.stopPropagation(); lightboxNav(1)"
            class="hidden absolute right-4 top-1/2 -translate-y-1/2 text-white/80 hover:text-white bg-white/10 hover:bg-white/20
                   rounded-full p-3 transition z-10">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
        </button>

        {{-- Gambar Besar --}}
        <div class="relative max-w-2xl w-full flex flex-col items-center" onclick="event.stopPropagation()">
            <img id="lightboxImage" src="" alt=""
                class="max-w-full max-h-[75vh] rounded-2xl shadow-2xl object-contain border-4 border-white/10">

            {{-- Info User --}}
            <div class="mt-4 text-center">
                <div id="lightboxName" class="text-white font-semibold text-lg">—</div>
                <div id="lightboxEmail" class="text-white/60 text-sm">—</div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        const CURRENT_USER_ID = {{ auth()->id() }};

        let currentRoleFilter = 'all';
        let currentDivisionFilter = 'all';
        let currentStatusFilter = 'all';
        let currentTeamFilter = 'all';
        let allUsers = [];
        let lightboxList = [];
        let lightboxIndex = 0;
        const roleStyle = {
            'admin': {
                bg: 'bg-violet-50',
                text: 'text-violet-700',
                border: 'border-violet-200',
                label: 'Admin'
            },
            'technician': {
                bg: 'bg-cyan-50',
                text: 'text-cyan-700',
                border: 'border-cyan-200',
                label: 'Teknisi'
            },
        };

        const divStyle = {
            'cctv': {
                bg: 'bg-purple-50',
                text: 'text-purple-700',
                border: 'border-purple-200',
                dot: 'bg-purple-500',
                label: 'CCTV'
            },
            'helpdesk': {
                bg: 'bg-teal-50',
                text: 'text-teal-700',
                border: 'border-teal-200',
                dot: 'bg-teal-500',
                label: 'Helpdesk'
            },
        };

        function roleBadge(role) {
            const s = roleStyle[role] || {
                bg: 'bg-gray-50',
                text: 'text-gray-600',
                border: 'border-gray-200',
                label: role || '-'
            };
            return `<span class="inline-block text-[10px] uppercase tracking-wide font-bold
                             px-2 py-0.5 rounded ${s.bg} ${s.text} border ${s.border}">
                        ${s.label}
                    </span>`;
        }

        function divisionBadge(division) {
            if (!division) return '<span class="text-xs text-gray-400 italic">—</span>';
            const s = divStyle[division] || {
                bg: 'bg-gray-50',
                text: 'text-gray-600',
                border: 'border-gray-200',
                dot: 'bg-gray-400',
                label: division
            };
            return `<span class="inline-flex items-center gap-1 text-[10px] uppercase tracking-wide font-bold
                             px-1.5 py-0.5 rounded ${s.bg} ${s.text} border ${s.border}">
                        <span class="w-1 h-1 rounded-full ${s.dot}"></span>
                        ${s.label}
                    </span>`;
        }

        function teamBadge(team, division) {
            if (!team) return '<span class="text-xs text-gray-300 italic">—</span>';

            const isCctv = division === 'cctv';
            const cls = isCctv ?
                'bg-purple-50 text-purple-700 border-purple-200' :
                'bg-teal-50 text-teal-700 border-teal-200';

            return `<span class="inline-flex items-center gap-1 text-[10px] uppercase tracking-wide font-bold
                             px-2 py-0.5 rounded border ${cls}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        ${team.name}
                    </span>`;
        }

        function esc(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }
        async function loadUsers() {
            try {
                allUsers = await api.get("{{ route('admin.users.data') }}");
                renderStats();
                renderTable();
            } catch (err) {
                console.error('User error:', err);
                document.getElementById('userTableBody').innerHTML = `
                    <tr><td colspan="8" class="px-3 py-10 text-center text-red-500 text-sm">
                        Gagal memuat data: ${err.message}
                    </td></tr>`;
            }
        }

        function renderStats() {
            const list = allUsers;
            const total = list.length;
            const admin = list.filter(u => u.role === 'admin').length;
            const tech = list.filter(u => u.role === 'technician').length;
            const inactive = list.filter(u => !u.is_active).length;

            document.getElementById('statsRow').innerHTML = `
                <div class="bg-white rounded-lg shadow-sm border-l-4 border-gray-400 p-4">
                    <div class="text-xs text-gray-500 uppercase font-semibold">Total</div>
                    <div class="text-2xl font-bold text-gray-800">${total}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm border-l-4 border-violet-500 p-4">
                    <div class="text-xs text-gray-500 uppercase font-semibold">Admin</div>
                    <div class="text-2xl font-bold text-violet-600">${admin}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm border-l-4 border-cyan-500 p-4">
                    <div class="text-xs text-gray-500 uppercase font-semibold">Teknisi</div>
                    <div class="text-2xl font-bold text-cyan-600">${tech}</div>
                </div>
                <div class="bg-white rounded-lg shadow-sm border-l-4 border-red-500 p-4">
                    <div class="text-xs text-gray-500 uppercase font-semibold">Nonaktif</div>
                    <div class="text-2xl font-bold text-red-600">${inactive}</div>
                </div>
            `;
        }

        function setRoleFilter(role) {
            currentRoleFilter = role;
            document.querySelectorAll('.rolefilter-btn').forEach(btn => {
                if (btn.dataset.rolefilter === role) {
                    btn.className =
                        'rolefilter-btn px-3 py-1.5 rounded-lg bg-indigo-600 text-white font-semibold';
                } else {
                    const map = {
                        'all': 'text-gray-600 hover:bg-gray-100',
                        'admin': 'text-violet-700 hover:bg-violet-50',
                        'technician': 'text-cyan-700 hover:bg-cyan-50',
                    };
                    btn.className =
                        `rolefilter-btn px-3 py-1.5 rounded-lg ${map[btn.dataset.rolefilter]} font-semibold`;
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
                    const map = {
                        'all': 'text-gray-600 hover:bg-gray-100',
                        'cctv': 'text-purple-700 hover:bg-purple-50',
                        'helpdesk': 'text-teal-700 hover:bg-teal-50',
                    };
                    btn.className =
                        `divfilter-btn px-3 py-1.5 rounded-lg ${map[btn.dataset.divfilter]} font-semibold`;
                }
            });
            renderTable();
        }

        function setStatusFilter(status) {
            currentStatusFilter = status;
            document.querySelectorAll('.statusfilter-btn').forEach(btn => {
                const isActive = btn.dataset.statusfilter === status;
                if (isActive) {
                    btn.className = 'statusfilter-btn px-3 py-1.5 rounded-lg bg-gray-800 text-white font-semibold';
                } else {
                    const map = {
                        'all': 'text-gray-600 hover:bg-gray-100',
                        'active': 'text-emerald-700 hover:bg-emerald-50',
                        'inactive': 'text-red-700 hover:bg-red-50',
                    };
                    btn.className =
                        `statusfilter-btn px-3 py-1.5 rounded-lg ${map[btn.dataset.statusfilter]} font-semibold`;
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
                    const map = {
                        'all': 'text-gray-600 hover:bg-gray-100',
                        'Tim A': 'text-indigo-700 hover:bg-indigo-50',
                        'Tim B': 'text-indigo-700 hover:bg-indigo-50',
                        'none': 'text-gray-500 hover:bg-gray-100',
                    };
                    btn.className =
                        `teamfilter-btn px-3 py-1.5 rounded-lg ${map[btn.dataset.teamfilter] || 'text-gray-600 hover:bg-gray-100'} font-semibold`;
                }
            });
            renderTable();
        }

        function renderTable() {
            const tbody = document.getElementById('userTableBody');
            const counter = document.getElementById('counter');
            const footerCounter = document.getElementById('footerCounter');

            let list = allUsers;

            if (currentRoleFilter !== 'all') {
                list = list.filter(u => u.role === currentRoleFilter);
            }

            if (currentDivisionFilter !== 'all') {
                list = list.filter(u => u.division === currentDivisionFilter);
            }

            if (currentStatusFilter !== 'all') {
                const isActive = currentStatusFilter === 'active';
                list = list.filter(u => u.is_active === isActive);
            }
            if (currentTeamFilter !== 'all') {
                if (currentTeamFilter === 'none') {
                    list = list.filter(u => !u.team);
                } else {
                    list = list.filter(u => u.team && u.team.name === currentTeamFilter);
                }
            }
            const roleOrder = {
                'admin': 0,
                'technician': 1
            };
            list = [...list].sort((a, b) => {
                const orderDiff = (roleOrder[a.role] ?? 9) - (roleOrder[b.role] ?? 9);
                if (orderDiff !== 0) return orderDiff;
                return (a.name || '').localeCompare(b.name || '');
            });
            lightboxList = list.filter(u => u.avatar_url);

            counter.textContent = `Menampilkan ${list.length} dari ${allUsers.length} user`;
            footerCounter.textContent = `${list.length} baris ditampilkan`;

            if (!list.length) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="px-3 py-16 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5.13a4 4 0 11-8 0 4 4 0 018 0zm6 0a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <p class="text-sm text-gray-400">Tidak ada user.</p>
                        </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = list.map(u => {
                const isSelf = u.id === CURRENT_USER_ID;
                const statusBadge = u.is_active ?
                    `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase
                            bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1 h-1 rounded-full bg-emerald-500"></span>
                        Aktif
                    </span>` :
                    `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold uppercase
                            bg-red-50 text-red-700 border border-red-200">
                        <span class="w-1 h-1 rounded-full bg-red-500"></span>
                        Nonaktif
                    </span>`;
                let actionButtons = `
                    <a href="/admin/users/${u.id}/edit"
                        class="inline-flex items-center justify-center w-7 h-7 rounded-md
                               text-indigo-600 hover:bg-indigo-50 transition" title="Edit">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </a>
                `;

                if (!isSelf) {
                    if (u.is_active) {
                        actionButtons += `
                            <button onclick="toggleActive(${u.id}, false)"
                                class="inline-flex items-center justify-center w-7 h-7 rounded-md
                                       text-amber-600 hover:bg-amber-50 transition" title="Nonaktifkan">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                            </button>
                        `;
                    } else {
                        actionButtons += `
                            <button onclick="toggleActive(${u.id}, true)"
                                class="inline-flex items-center justify-center w-7 h-7 rounded-md
                                       text-emerald-600 hover:bg-emerald-50 transition" title="Aktifkan">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </button>
                        `;
                    }

                    actionButtons += `
                        <button onclick="openDeleteModal(${u.id}, '${esc(u.name).replace(/'/g, "\\'")}')"
                            class="inline-flex items-center justify-center w-7 h-7 rounded-md
                                   text-red-600 hover:bg-red-50 transition" title="Hapus">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    `;
                } else {
                    actionButtons += `<span class="text-[10px] text-gray-300 italic">Anda</span>`;
                }
                const avatarHtml = u.avatar_url ?
                    `<button type="button" onclick="openAvatarLightbox(${u.id})"
                                class="group relative block rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-400 transition cursor-zoom-in"
                                title="Lihat foto">
                            <img src="${u.avatar_url}" alt="${esc(u.name)}"
                                class="w-9 h-9 rounded-full object-cover border-2 border-white shadow-sm
                                       group-hover:ring-2 group-hover:ring-indigo-300 transition">
                            <span class="absolute inset-0 rounded-full bg-black/40 opacity-0 group-hover:opacity-100
                                         flex items-center justify-center text-white transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" />
                                </svg>
                            </span>
                        </button>` :
                    `<div class="w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600
                                flex items-center justify-center text-white font-bold text-xs">
                            ${esc(u.name).charAt(0).toUpperCase()}
                        </div>`;

                return `
                    <tr class="hover:bg-indigo-50/40 transition">
                        <td class="px-3 py-2.5">${avatarHtml}</td>

                        <td class="px-3 py-2.5">
                            <div class="font-semibold text-gray-800 text-sm leading-snug">
                                ${esc(u.name)}
                                ${isSelf ? '<span class="text-[10px] text-indigo-600 font-bold ml-1">(Anda)</span>' : ''}
                            </div>
                            <div class="text-xs text-gray-500">@${esc(u.username)}</div>
                        </td>

                        <td class="px-3 py-2.5 text-xs text-gray-600">
                            ${esc(u.email)}
                        </td>

                        <td class="px-3 py-2.5">
                            ${roleBadge(u.role)}
                        </td>

                        <td class="px-3 py-2.5">
                            ${divisionBadge(u.division)}
                        </td>

                        <td class="px-3 py-2.5">
                            ${teamBadge(u.team, u.division)}
                        </td>

                        <td class="px-3 py-2.5">
                            ${statusBadge}
                        </td>

                        <td class="px-3 py-2.5 text-center">
                            <div class="flex items-center justify-center gap-1">
                                ${actionButtons}
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }
        const avatarLightbox = document.getElementById('avatarLightbox');
        const lightboxImage = document.getElementById('lightboxImage');
        const lightboxName = document.getElementById('lightboxName');
        const lightboxEmail = document.getElementById('lightboxEmail');
        const lightboxPrev = document.getElementById('lightboxPrev');
        const lightboxNext = document.getElementById('lightboxNext');

        function openAvatarLightbox(userId) {
            const idx = lightboxList.findIndex(u => u.id === userId);
            if (idx === -1) return;

            lightboxIndex = idx;
            renderLightbox();

            avatarLightbox.classList.remove('hidden');
            avatarLightbox.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function renderLightbox() {
            const u = lightboxList[lightboxIndex];
            if (!u) return;

            lightboxImage.src = u.avatar_url;
            lightboxImage.alt = u.name || '';
            lightboxName.textContent = u.name || '—';
            lightboxEmail.textContent = u.email || '';

            const multi = lightboxList.length > 1;
            lightboxPrev.classList.toggle('hidden', !multi);
            lightboxNext.classList.toggle('hidden', !multi);
        }

        function lightboxNav(dir) {
            if (lightboxList.length <= 1) return;
            lightboxIndex = (lightboxIndex + dir + lightboxList.length) % lightboxList.length;
            renderLightbox();
        }

        function closeAvatarLightbox() {
            avatarLightbox.classList.add('hidden');
            avatarLightbox.classList.remove('flex');
            document.body.style.overflow = '';
        }

        avatarLightbox?.addEventListener('click', function(e) {
            if (e.target === avatarLightbox) closeAvatarLightbox();
        });

        document.addEventListener('keydown', function(e) {
            if (avatarLightbox.classList.contains('hidden')) return;
            if (e.key === 'Escape') closeAvatarLightbox();
            if (e.key === 'ArrowLeft') lightboxNav(-1);
            if (e.key === 'ArrowRight') lightboxNav(1);
        });
        async function toggleActive(id, activate) {
            const ok = await showConfirm({
                title: activate ? 'Aktifkan User?' : 'Nonaktifkan User?',
                subtitle: 'Konfirmasi diperlukan',
                message: activate ?
                    'User akan dapat login kembali ke sistem.' :
                    'User tidak akan bisa login sampai diaktifkan kembali.',
                okText: activate ? 'Ya, Aktifkan' : 'Ya, Nonaktifkan',
                cancelText: 'Batal',
                type: activate ? 'success' : 'warning',
            });

            if (!ok) return;

            try {
                await api.patch(`/admin/users/${id}/toggle-active`);
                showToast(activate ? 'User diaktifkan' : 'User dinonaktifkan', 'success');
                await loadUsers();
            } catch (err) {
                showToast('Gagal: ' + err.message, 'error');
            }
        }

        function openDeleteModal(userId, userName) {
            document.getElementById('deleteUserName').textContent = userName;
            document.getElementById('deleteForm').action = `/admin/users/${userId}`;
            document.getElementById('modalDelete').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeDeleteModal() {
            document.getElementById('modalDelete').classList.add('hidden');
            document.body.style.overflow = '';
        }

        document.getElementById('modalDelete')?.addEventListener('click', function(e) {
            if (e.target === this) closeDeleteModal();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeDeleteModal();
        });
        loadUsers();
    </script>
@endpush
