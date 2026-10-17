@extends('layouts.app')

@section('title', 'Pengaturan Tim')

@section('content')

    <div class="max-w-5xl mx-auto space-y-6" x-data="{
        showAddTeam: false,
        addTeamDivision: null,
        showRenameModal: false,
        renameTeamId: null,
        renameTeamName: '',
        showDeleteModal: false,
        deleteTeamId: null,
        deleteTeamName: ''
    }">

        {{-- HEADER --}}
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Pengaturan Tim</h1>
            <p class="text-sm text-gray-500 mt-1">
                Kelola tim & rotasi otomatis per divisi
            </p>
        </div>

        {{-- FLASH --}}
        @if (session('success'))
            <div
                class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm flex items-start gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm">
                @foreach ($errors->all() as $err)
                    <div>{{ $err }}</div>
                @endforeach
            </div>
        @endif

        {{-- CARD PER DIVISI --}}
        @foreach (['cctv', 'helpdesk'] as $div)
            @php
                $setting = $settings[$div];
                $isCctv = $div === 'cctv';
                $teamList = $teams[$div] ?? collect();
                $techList = $technicians[$div] ?? collect();
                $members = $currentMembers[$div] ?? collect();
                $perTeamPreview = $teamList->count() > 0 ? ceil($techList->count() / $teamList->count()) : 0;
            @endphp

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                {{-- Header Card --}}
                <div
                    class="px-6 py-4 border-b border-gray-100 flex items-center justify-between flex-wrap gap-3
                            {{ $isCctv ? 'bg-purple-50' : 'bg-teal-50' }}">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-xl flex items-center justify-center
                                    {{ $isCctv ? 'bg-purple-500' : 'bg-teal-500' }}">
                            <span class="text-white text-lg">{{ $isCctv ? '📹' : '🎧' }}</span>
                        </div>
                        <div>
                            <h2 class="font-bold text-gray-800">Divisi {{ strtoupper($div) }}</h2>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $techList->count() }} teknisi aktif · {{ $teamList->count() }} tim
                            </p>
                        </div>
                    </div>

                    @if ($setting->shuffle_enabled)
                        <span
                            class="px-3 py-1 rounded-lg bg-emerald-100 text-emerald-700
                                     text-xs font-bold uppercase tracking-wide border border-emerald-200">
                            ✓ Aktif
                        </span>
                    @else
                        <span
                            class="px-3 py-1 rounded-lg bg-gray-100 text-gray-500
                                     text-xs font-bold uppercase tracking-wide border border-gray-200">
                            ○ Nonaktif
                        </span>
                    @endif
                </div>

                {{-- ============ SECTION: DAFTAR TIM ============ --}}
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                    <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                        <div class="font-semibold text-gray-700 text-sm flex items-center gap-2">
                            📋 Daftar Tim
                            <span class="text-xs font-normal text-gray-400">
                                ({{ $teamList->count() }})
                            </span>
                        </div>
                        <button type="button" @click="showAddTeam = true; addTeamDivision = '{{ $div }}'"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg
                                   bg-{{ $isCctv ? 'purple' : 'teal' }}-500 hover:bg-{{ $isCctv ? 'purple' : 'teal' }}-600
                                   text-white text-xs font-semibold transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            Tambah Tim
                        </button>
                    </div>

                    @if ($teamList->count() > 0)
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                            @foreach ($teamList as $team)
                                <div
                                    class="flex items-center gap-2 bg-white rounded-xl border border-gray-200 p-2.5
                                            hover:border-{{ $isCctv ? 'purple' : 'teal' }}-300 transition group">
                                    <div
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold shrink-0
                                                {{ $isCctv ? 'bg-purple-500' : 'bg-teal-500' }}">
                                        {{ substr($team->name, -1) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-semibold text-gray-800 text-sm truncate">
                                            {{ $team->name }}
                                        </div>
                                        <div class="text-[10px] text-gray-400">
                                            Order: {{ $team->order }}
                                            · {{ $team->membersForPeriod(now()->month, now()->year)->count() }} org
                                        </div>
                                    </div>
                                    <div class="flex gap-0.5 opacity-0 group-hover:opacity-100 transition">
                                        <button type="button"
                                            @click="showRenameModal = true; renameTeamId = {{ $team->id }}; renameTeamName = '{{ addslashes($team->name) }}'"
                                            class="w-7 h-7 rounded-lg text-indigo-600 hover:bg-indigo-50
                                                   flex items-center justify-center transition"
                                            title="Rename">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button type="button"
                                            @click="showDeleteModal = true; deleteTeamId = {{ $team->id }}; deleteTeamName = '{{ addslashes($team->name) }}'"
                                            class="w-7 h-7 rounded-lg text-red-600 hover:bg-red-50
                                                   flex items-center justify-center transition"
                                            title="Hapus">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        {{-- EMPTY STATE --}}
                        <div class="text-center py-6 rounded-xl bg-white border border-dashed border-gray-300">
                            <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                            <p class="text-sm text-gray-600 font-semibold mb-1">Belum ada tim</p>
                            <p class="text-xs text-gray-400 mb-3">
                                Buat minimal 2 tim untuk mengaktifkan rotasi
                            </p>
                            <div class="flex gap-2 justify-center flex-wrap">
                                <form method="POST" action="{{ route('team-settings.auto-generate') }}">
                                    @csrf
                                    <input type="hidden" name="division" value="{{ $div }}">
                                    <button type="submit"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl
                                               bg-gradient-to-br from-{{ $isCctv ? 'purple' : 'teal' }}-500
                                               to-{{ $isCctv ? 'purple' : 'teal' }}-600
                                               hover:from-{{ $isCctv ? 'purple' : 'teal' }}-600
                                               hover:to-{{ $isCctv ? 'purple' : 'teal' }}-700
                                               text-white text-xs font-semibold shadow-md transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                                        </svg>
                                        Generate 2 Tim Default
                                    </button>
                                </form>
                                <button type="button" @click="showAddTeam = true; addTeamDivision = '{{ $div }}'"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl
                                           bg-white border border-gray-300 hover:bg-gray-50
                                           text-gray-700 text-xs font-semibold transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Tambah Manual
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- ============ SECTION: SETTING ============ --}}
                <form method="POST" action="{{ route('team-settings.update', $div) }}" class="p-6 space-y-4">
                    @csrf @method('PUT')

                    <div class="flex items-start gap-4">
                        <label class="flex items-center cursor-pointer">
                            <input type="hidden" name="shuffle_enabled" value="0">
                            <input type="checkbox" name="shuffle_enabled" value="1"
                                {{ $setting->shuffle_enabled ? 'checked' : '' }} class="sr-only peer">
                            <div
                                class="relative w-12 h-6 bg-gray-300 peer-checked:bg-emerald-500
                                        rounded-full transition-colors
                                        after:content-[''] after:absolute after:top-0.5 after:left-0.5
                                        after:bg-white after:rounded-full after:h-5 after:w-5
                                        after:transition-transform peer-checked:after:translate-x-6">
                            </div>
                        </label>

                        <div class="flex-1">
                            <div class="font-semibold text-gray-800 text-sm">Aktifkan Rotasi Tim</div>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Kalau aktif, tim akan di-rotate otomatis setiap awal bulan.
                            </p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            Jumlah Anggota per Tim
                        </label>
                        <input type="number" name="members_per_team" min="1" max="20"
                            value="{{ $setting->members_per_team }}" placeholder="Kosongkan untuk auto"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <p class="text-[11px] text-gray-400 mt-1">
                            Auto: <b>{{ $techList->count() }}</b> user ÷ <b>{{ max($teamList->count(), 1) }}</b> tim
                            = <b>{{ $perTeamPreview }}</b> per tim
                        </p>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white
                                   {{ $isCctv ? 'bg-purple-600 hover:bg-purple-700' : 'bg-teal-600 hover:bg-teal-700' }}
                                   shadow-lg transition">
                            Simpan Pengaturan
                        </button>
                    </div>
                </form>

                {{-- ============ SECTION: TIM BULAN INI ============ --}}
                @if ($setting->shuffle_enabled)
                    <div class="border-t border-gray-100 px-6 py-4 bg-gray-50">
                        <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                            <div class="font-semibold text-gray-700 text-sm">
                                📅 Tim Bulan Ini — {{ now()->translatedFormat('F Y') }}
                            </div>
                            <form method="POST" action="{{ route('team-settings.shuffle', $div) }}"
                                onsubmit="return confirm('Rotasi ulang tim {{ strtoupper($div) }} bulan ini?')">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg
                                           bg-amber-500 hover:bg-amber-600 text-white
                                           text-xs font-semibold transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Rotate Ulang
                                </button>
                            </form>
                        </div>

                        @if ($members->count())
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach ($members->groupBy('team_id') as $teamId => $membersList)
                                    @php $team = $membersList->first()->team; @endphp
                                    <div class="bg-white rounded-xl border border-gray-200 p-3">
                                        <div class="font-semibold text-gray-800 text-sm mb-2 flex items-center gap-2">
                                            <span
                                                class="w-6 h-6 rounded-lg flex items-center justify-center text-white text-xs font-bold
                                                        {{ $isCctv ? 'bg-purple-500' : 'bg-teal-500' }}">
                                                {{ substr($team->name, -1) }}
                                            </span>
                                            {{ $team->name }}
                                            <span class="text-xs text-gray-400 font-normal">
                                                ({{ $membersList->count() }} org)
                                            </span>
                                        </div>
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach ($membersList as $m)
                                                <div
                                                    class="flex items-center gap-1.5 px-2 py-1 rounded-lg
                                                            bg-gray-50 border border-gray-200">
                                                    <div
                                                        class="w-5 h-5 rounded-full flex items-center justify-center
                                                                text-white text-[10px] font-bold
                                                                {{ $isCctv ? 'bg-purple-400' : 'bg-teal-400' }}">
                                                        {{ strtoupper(substr($m->user->name, 0, 1)) }}
                                                    </div>
                                                    <span class="text-xs text-gray-700 font-medium">
                                                        {{ $m->user->name }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div
                                class="text-center py-6 text-sm text-gray-500 bg-white rounded-xl border border-dashed border-gray-200">
                                Belum ada tim untuk bulan ini.<br>
                                <span class="text-xs">Klik <b>Rotate Ulang</b> untuk generate.</span>
                            </div>
                        @endif
                    </div>
                @endif

            </div>
        @endforeach

        {{-- ==================== MODAL: TAMBAH TIM ==================== --}}
        <div x-show="showAddTeam" x-cloak class="fixed inset-0 z-[200] flex items-center justify-center p-4"
            style="background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(4px);"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6" @click.away="showAddTeam = false">
                <div class="flex items-start gap-4 mb-4">
                    <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Tambah Tim Baru</h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Buat tim baru untuk divisi
                            <b x-text="addTeamDivision === 'cctv' ? 'CCTV' : 'Helpdesk'"></b>
                        </p>
                    </div>
                </div>

                <form method="POST" action="{{ route('team-settings.teams.store') }}">
                    @csrf
                    <input type="hidden" name="division" :value="addTeamDivision">

                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            Nama Tim <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" required maxlength="50" placeholder="Contoh: Tim C"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div class="flex gap-2 justify-end">
                        <button type="button" @click="showAddTeam = false"
                            class="px-4 py-2 rounded-xl text-sm font-semibold text-gray-600 border border-gray-200 hover:bg-gray-50 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2 rounded-xl text-sm font-semibold text-white
                                   bg-indigo-600 hover:bg-indigo-700 shadow-lg transition">
                            Simpan Tim
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ==================== MODAL: RENAME TIM ==================== --}}
        <div x-show="showRenameModal" x-cloak class="fixed inset-0 z-[200] flex items-center justify-center p-4"
            style="background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(4px);"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6" @click.away="showRenameModal = false">
                <div class="flex items-start gap-4 mb-4">
                    <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Rename Tim</h3>
                        <p class="text-sm text-gray-500 mt-1">Ubah nama tim</p>
                    </div>
                </div>

                <form method="POST" :action="`/team-settings/teams/${renameTeamId}`">
                    @csrf @method('PUT')

                    <div class="mb-4">
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            Nama Tim Baru <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" required maxlength="50" x-model="renameTeamName"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div class="flex gap-2 justify-end">
                        <button type="button" @click="showRenameModal = false"
                            class="px-4 py-2 rounded-xl text-sm font-semibold text-gray-600 border border-gray-200 hover:bg-gray-50 transition">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2 rounded-xl text-sm font-semibold text-white
                                   bg-indigo-600 hover:bg-indigo-700 shadow-lg transition">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ==================== MODAL: HAPUS TIM ==================== --}}
        <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-[200] flex items-center justify-center p-4"
            style="background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(4px);"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6" @click.away="showDeleteModal = false">
                <div class="flex items-start gap-4 mb-4">
                    <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.74-2.98L13.74 4.02a2 2 0 00-3.48 0L3.33 16.02A2 2 0 005.07 19z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Hapus Tim?</h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Tim <b x-text="deleteTeamName"></b> akan dihapus permanen.
                            Semua history anggotanya juga akan hilang.
                        </p>
                    </div>
                </div>

                <form method="POST" :action="`/team-settings/teams/${deleteTeamId}`">
                    @csrf @method('DELETE')

                    <div class="flex gap-2 justify-end">
                        <button type="button" @click="showDeleteModal = false"
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

    </div>

@endsection
