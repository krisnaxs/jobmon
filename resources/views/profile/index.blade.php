@extends('layouts.app')

@section('title', 'Profile Saya')

@section('content')

    @php
        $user = auth()->user();
        $divisionLabel = $user->division === 'cctv' ? 'CCTV' : ($user->division === 'helpdesk' ? 'Helpdesk' : null);
        $roleBadge =
            [
                'admin' => 'bg-violet-100 text-violet-700 border-violet-200',
                'technician' => 'bg-cyan-100 text-cyan-700 border-cyan-200',
            ][$user->role] ?? 'bg-gray-100 text-gray-700 border-gray-200';
        $divBadge =
            [
                'cctv' => 'bg-purple-100 text-purple-700 border-purple-200',
                'helpdesk' => 'bg-teal-100 text-teal-700 border-teal-200',
            ][$user->division] ?? null;
    @endphp

    <div class="max-w-3xl mx-auto space-y-6">

        {{-- HEADER --}}
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Profile Saya</h1>
            <p class="text-sm text-gray-500 mt-1">Kelola informasi pribadi & password akun Anda</p>
        </div>

        {{-- FLASH --}}
        @if (session('success'))
            <div
                class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm flex items-start gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600 shrink-0" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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

        {{-- ==================== CARD PROFILE ==================== --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

            {{-- Header Card dengan Avatar --}}
            <div class="bg-gradient-to-br from-indigo-50 to-violet-50 px-6 py-6 border-b border-gray-100">
                <div class="flex flex-wrap items-center gap-4">

                    {{-- AVATAR (KLIK UNTUK LIGHTBOX) --}}
                    <div class="relative group shrink-0">
                        @if ($user->avatar_url)
                            <button type="button" onclick="openAvatarLightbox()"
                                class="relative block rounded-full focus:outline-none focus:ring-4 focus:ring-indigo-300 transition cursor-zoom-in">
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                                    class="w-20 h-20 rounded-full object-cover border-4 border-white shadow-lg">

                                <div
                                    class="absolute inset-0 rounded-full bg-black/40 opacity-0 group-hover:opacity-100
                                           flex items-center justify-center text-white transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7" />
                                    </svg>
                                </div>
                            </button>
                        @else
                            <div
                                class="w-20 h-20 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600
                                        flex items-center justify-center text-white font-bold text-3xl
                                        border-4 border-white shadow-lg">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    {{-- INFO --}}
                    <div class="min-w-0 flex-1">
                        <div class="text-lg font-bold text-gray-800 truncate">{{ $user->name }}</div>
                        <div class="text-sm text-gray-500 truncate">{{ $user->email }}</div>
                        <div class="mt-2 flex gap-1.5 flex-wrap">
                            <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase border {{ $roleBadge }}">
                                {{ $user->role }}
                            </span>
                            @if ($divisionLabel)
                                <span
                                    class="px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase border {{ $divBadge }}">
                                    {{ $divisionLabel }}
                                </span>
                            @endif
                        </div>

                        <div class="mt-3 flex gap-2 flex-wrap">
                            <button type="button" onclick="document.getElementById('avatarInput').click()"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg
                                       bg-white border border-indigo-200 text-indigo-700
                                       hover:bg-indigo-50 text-xs font-semibold transition shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                </svg>
                                {{ $user->avatar_url ? 'Ganti Foto' : 'Upload Foto' }}
                            </button>

                            @if ($user->avatar_url)
                                <button type="button" onclick="confirmRemoveAvatar()"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg
                                           bg-white border border-red-200 text-red-700
                                           hover:bg-red-50 text-xs font-semibold transition shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Hapus Foto
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Form Profile --}}
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data"
                class="p-6 space-y-5">
                @csrf @method('PUT')

                <input type="file" name="avatar" id="avatarInput" accept="image/jpeg,image/jpg,image/png,image/webp"
                    class="hidden">

                <div class="flex items-center gap-2 mb-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <h2 class="font-semibold text-gray-800 text-sm">Informasi Pribadi</h2>
                </div>

                <div id="avatarPreviewWrapper" class="hidden bg-indigo-50/50 border border-indigo-100 rounded-xl p-3">
                    <div class="flex items-center gap-3">
                        <img id="avatarPreview" src="" alt="Preview"
                            class="w-12 h-12 rounded-full object-cover border-2 border-white shadow">
                        <div class="flex-1 min-w-0">
                            <div class="text-xs font-semibold text-indigo-900">Foto baru dipilih</div>
                            <div class="text-[11px] text-indigo-600 truncate" id="avatarFileName">—</div>
                        </div>
                        <button type="button" onclick="cancelAvatar()"
                            class="text-red-600 hover:bg-red-50 rounded-lg p-1.5 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" required maxlength="255"
                        value="{{ old('name', $user->name) }}"
                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                    @error('name')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            Username <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="username" required maxlength="100"
                            value="{{ old('username', $user->username) }}"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                        @error('username')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" required maxlength="255"
                            value="{{ old('email', $user->email) }}"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                        @error('email')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white
                               bg-gradient-to-br from-indigo-500 to-violet-600
                               hover:from-indigo-600 hover:to-violet-700
                               shadow-lg shadow-indigo-500/30 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Simpan Profile
                    </button>
                </div>
            </form>
        </div>

        {{-- ==================== CARD TIM SAYA ==================== --}}
        @if ($shuffleEnabled && $myTeamMembership && $myTeamMembership->team)
            @php
                $myTeam = $myTeamMembership->team;
                $isCctv = $user->division === 'cctv';
            @endphp

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

                {{-- Header --}}
                <div
                    class="px-6 py-4 border-b border-gray-100 flex items-center gap-3
                            {{ $isCctv ? 'bg-purple-50/50' : 'bg-teal-50/50' }}">
                    <div
                        class="w-9 h-9 rounded-xl {{ $isCctv ? 'bg-purple-500' : 'bg-teal-500' }}
                                flex items-center justify-center shrink-0">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-semibold text-gray-800">Tim Saya</h2>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Info tim Anda untuk bulan {{ now()->translatedFormat('F Y') }}
                        </p>
                    </div>
                </div>

                {{-- Content --}}
                <div class="p-6 space-y-4">

                    {{-- Team Name Card --}}
                    <div
                        class="flex items-center gap-4 p-4 rounded-xl
                                {{ $isCctv ? 'bg-purple-50 border border-purple-200' : 'bg-teal-50 border border-teal-200' }}">
                        <div
                            class="w-14 h-14 rounded-xl {{ $isCctv ? 'bg-purple-500' : 'bg-teal-500' }}
                                    flex items-center justify-center text-white text-xl font-bold shrink-0 shadow-lg
                                    {{ $isCctv ? 'shadow-purple-500/30' : 'shadow-teal-500/30' }}">
                            {{ substr($myTeam->name, -1) }}
                        </div>
                        <div>
                            <div
                                class="text-xs {{ $isCctv ? 'text-purple-600' : 'text-teal-600' }} uppercase font-bold tracking-wide">
                                Anda tergabung dalam
                            </div>
                            <div class="text-xl font-bold text-gray-800">{{ $myTeam->name }}</div>
                            <div class="text-xs text-gray-500 mt-0.5">
                                {{ $teamMembers->count() }} anggota
                            </div>
                        </div>
                    </div>

                    {{-- Anggota Tim --}}
                    <div>
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                            Anggota Tim
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach ($teamMembers as $member)
                                @php
                                    $isMe = $member->user_id === $user->id;
                                    $memberUser = $member->user;
                                @endphp
                                <div
                                    class="flex items-center gap-3 px-3 py-2 rounded-lg border transition
                                            {{ $isMe
                                                ? ($isCctv
                                                    ? 'bg-purple-100 border-purple-300 ring-2 ring-purple-200'
                                                    : 'bg-teal-100 border-teal-300 ring-2 ring-teal-200')
                                                : 'bg-gray-50 border-gray-200' }}">

                                    @if ($memberUser && $memberUser->avatar_url)
                                        <img src="{{ $memberUser->avatar_url }}" alt="{{ $memberUser->name }}"
                                            class="w-9 h-9 rounded-full object-cover border-2 border-white shadow-sm shrink-0">
                                    @else
                                        <div
                                            class="w-9 h-9 rounded-full flex items-center justify-center
                                                    text-white font-bold text-sm shrink-0
                                                    {{ $isMe ? ($isCctv ? 'bg-purple-500' : 'bg-teal-500') : 'bg-gray-400' }}">
                                            {{ strtoupper(substr($memberUser->name ?? '?', 0, 1)) }}
                                        </div>
                                    @endif

                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm font-semibold text-gray-800 truncate">
                                            {{ $memberUser->name ?? 'User Dihapus' }}
                                            @if ($isMe)
                                                <span
                                                    class="text-[10px] font-bold
                                                            {{ $isCctv ? 'text-purple-600' : 'text-teal-600' }}">
                                                    (Anda)
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-[11px] text-gray-400 truncate">
                                            {{ $memberUser->username ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Info Rotasi --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 flex items-start gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600 shrink-0 mt-0.5"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-xs text-blue-700">
                            <b>Info:</b> Tim akan di-rotate otomatis setiap awal bulan.
                            Anda akan bergilir dengan rekan-rekan satu divisi.
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ==================== CARD GANTI PASSWORD ==================== --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-semibold text-gray-800">Ganti Password</h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Masukkan password lama untuk verifikasi
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('profile.password') }}" class="p-6 space-y-4">
                @csrf @method('PUT')

                {{-- Password Lama --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                        Password Lama <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="password" name="current_password" id="current_password" required
                            placeholder="Masukkan password saat ini"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 pr-11 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
                        <button type="button" data-toggle-password="current_password"
                            class="toggle-password absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-amber-500 transition"
                            aria-label="Tampilkan password">
                            <svg data-eye-open xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg data-eye-closed xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 012.163-3.592M6.223 6.223A9.956 9.956 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.97 9.97 0 01-4.293 5.068M6.223 6.223L3 3m3.223 3.223l3.353 3.353m5.091 5.091l3.353 3.353M9.88 9.88a3 3 0 004.24 4.24" />
                            </svg>
                        </button>
                    </div>
                    @error('current_password')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Password Baru --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            Password Baru <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" name="password" id="password" required minlength="6"
                                placeholder="Min. 6 karakter"
                                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 pr-11 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
                            <button type="button" data-toggle-password="password"
                                class="toggle-password absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-amber-500 transition"
                                aria-label="Tampilkan password">
                                <svg data-eye-open xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg data-eye-closed xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 012.163-3.592M6.223 6.223A9.956 9.956 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.97 9.97 0 01-4.293 5.068M6.223 6.223L3 3m3.223 3.223l3.353 3.353m5.091 5.091l3.353 3.353M9.88 9.88a3 3 0 004.24 4.24" />
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Konfirmasi Password --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            Konfirmasi Password <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                minlength="6" placeholder="Ulangi password baru"
                                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 pr-11 text-sm
                                       focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition">
                            <button type="button" data-toggle-password="password_confirmation"
                                class="toggle-password absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-amber-500 transition"
                                aria-label="Tampilkan password">
                                <svg data-eye-open xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg data-eye-closed xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 012.163-3.592M6.223 6.223A9.956 9.956 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.97 9.97 0 01-4.293 5.068M6.223 6.223L3 3m3.223 3.223l3.353 3.353m5.091 5.091l3.353 3.353M9.88 9.88a3 3 0 004.24 4.24" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white
                               bg-gradient-to-br from-amber-500 to-orange-600
                               hover:from-amber-600 hover:to-orange-700
                               shadow-lg shadow-amber-500/30 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Ganti Password
                    </button>
                </div>
            </form>
        </div>

    </div>

    {{-- ==================== LIGHTBOX AVATAR ==================== --}}
    @if ($user->avatar_url)
        <div id="avatarLightbox" class="hidden fixed inset-0 z-50 items-center justify-center p-4"
            style="background-color: rgba(0,0,0,0.85); backdrop-filter: blur(4px);">

            <button type="button" onclick="closeAvatarLightbox()"
                class="absolute top-4 right-4 text-white/80 hover:text-white bg-white/10 hover:bg-white/20
                       rounded-full p-2.5 transition z-10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <div class="relative max-w-2xl w-full flex flex-col items-center" onclick="event.stopPropagation()">
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                    class="max-w-full max-h-[75vh] rounded-2xl shadow-2xl object-contain border-4 border-white/10
                           animate-[zoomIn_0.2s_ease-out]">

                <div class="mt-4 text-center">
                    <div class="text-white font-semibold text-lg">{{ $user->name }}</div>
                    <div class="text-white/60 text-sm">{{ $user->email }}</div>
                </div>
            </div>
        </div>
    @endif

    {{-- ==================== FORM HAPUS AVATAR (hidden) ==================== --}}
    <form id="removeAvatarForm" method="POST" action="{{ route('profile.remove-avatar') }}" class="hidden">
        @csrf @method('DELETE')
    </form>

@endsection

@push('scripts')
    <script>
        const avatarLightbox = document.getElementById('avatarLightbox');

        function openAvatarLightbox() {
            if (!avatarLightbox) return;
            avatarLightbox.classList.remove('hidden');
            avatarLightbox.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeAvatarLightbox() {
            if (!avatarLightbox) return;
            avatarLightbox.classList.add('hidden');
            avatarLightbox.classList.remove('flex');
            document.body.style.overflow = '';
        }

        avatarLightbox?.addEventListener('click', function(e) {
            if (e.target === avatarLightbox) {
                closeAvatarLightbox();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && avatarLightbox && !avatarLightbox.classList.contains('hidden')) {
                closeAvatarLightbox();
            }
        });
        const avatarInput = document.getElementById('avatarInput');
        const avatarPreviewWrapper = document.getElementById('avatarPreviewWrapper');
        const avatarPreview = document.getElementById('avatarPreview');
        const avatarFileName = document.getElementById('avatarFileName');

        avatarInput?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;

            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran file maksimal 2 MB.');
                this.value = '';
                return;
            }

            const allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!allowed.includes(file.type)) {
                alert('Format harus JPG, PNG, atau WEBP.');
                this.value = '';
                return;
            }

            avatarPreview.src = URL.createObjectURL(file);
            avatarFileName.textContent = file.name;
            avatarPreviewWrapper.classList.remove('hidden');
        });

        function cancelAvatar() {
            avatarInput.value = '';
            avatarPreview.src = '';
            avatarFileName.textContent = '—';
            avatarPreviewWrapper.classList.add('hidden');
        }

        function confirmRemoveAvatar() {
            if (confirm('Yakin hapus foto profile? Anda bisa upload lagi nanti.')) {
                document.getElementById('removeAvatarForm').submit();
            }
        }

        // ============================================
        // TOGGLE SHOW/HIDE PASSWORD (semua input sekaligus)
        // ============================================
        document.querySelectorAll('.toggle-password').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const inputId = this.getAttribute('data-toggle-password');
                const input = document.getElementById(inputId);
                if (!input) return;

                const eyeOpen = this.querySelector('[data-eye-open]');
                const eyeClosed = this.querySelector('[data-eye-closed]');

                const isPassword = input.type === 'password';
                input.type = isPassword ? 'text' : 'password';

                eyeOpen.classList.toggle('hidden', isPassword);
                eyeClosed.classList.toggle('hidden', !isPassword);
            });
        });
    </script>
@endpush
