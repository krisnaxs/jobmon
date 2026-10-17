@php
    $isEdit = isset($user);
    $isSelf = $isEdit && $user->id === auth()->id();
@endphp

<div class="p-6 space-y-5">

    {{-- ==================== AVATAR ==================== --}}
    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-3">
            Foto Profile
        </label>

        <div class="flex items-center gap-4">

            {{-- Preview Avatar --}}
            <div class="relative group shrink-0">
                @if ($isEdit && $user->avatar_url)
                    <img id="avatarPreviewImg" src="{{ $user->avatar_url }}" alt="Avatar"
                        class="w-20 h-20 rounded-full object-cover border-4 border-white shadow-lg">
                @else
                    <img id="avatarPreviewImg" src="" alt="Avatar"
                        class="w-20 h-20 rounded-full object-cover border-4 border-white shadow-lg hidden">
                @endif

                {{-- Fallback initial --}}
                <div id="avatarInitial"
                    class="w-20 h-20 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600
                            flex items-center justify-center text-white font-bold text-3xl
                            border-4 border-white shadow-lg
                            {{ $isEdit && $user->avatar_url ? 'hidden' : '' }}">
                    {{ strtoupper(substr($user->name ?? '?', 0, 1)) }}
                </div>

                {{-- Overlay Upload --}}
                <button type="button" onclick="document.getElementById('avatarInput').click()"
                    class="absolute inset-0 rounded-full bg-black/50 opacity-0 group-hover:opacity-100
                           flex items-center justify-center text-white transition cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </button>
            </div>

            {{-- Info + Tombol --}}
            <div class="flex-1 min-w-0">
                <div class="text-sm text-gray-700 font-medium">
                    JPG, PNG, atau WEBP
                </div>
                <div class="text-xs text-gray-500 mt-0.5">
                    Maksimal 2 MB. Disarankan rasio 1:1 (kotak).
                </div>

                <div class="mt-2 flex gap-2 flex-wrap">
                    <button type="button" onclick="document.getElementById('avatarInput').click()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg
                               bg-white border border-indigo-200 text-indigo-700
                               hover:bg-indigo-50 text-xs font-semibold transition shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        {{ $isEdit && $user->avatar_url ? 'Ganti Foto' : 'Pilih Foto' }}
                    </button>

                    @if ($isEdit && $user->avatar_url)
                        <button type="button" onclick="document.getElementById('removeAvatarForm').submit()"
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

        {{-- File input (hidden) --}}
        <input type="file" name="avatar" id="avatarInput" accept="image/jpeg,image/jpg,image/png,image/webp"
            class="hidden">

        @error('avatar')
            <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
        @enderror
    </div>

    {{-- NAMA --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
            Nama Lengkap <span class="text-red-500">*</span>
        </label>
        <input type="text" name="name" required maxlength="255" value="{{ old('name', $user->name ?? '') }}"
            placeholder="Contoh: Budi Santoso"
            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm
                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
        @error('name')
            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- USERNAME + EMAIL --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                Username <span class="text-red-500">*</span>
            </label>
            <input type="text" name="username" required maxlength="100"
                value="{{ old('username', $user->username ?? '') }}" placeholder="budi.santoso"
                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
            <p class="text-[11px] text-gray-400 mt-1">Huruf, angka, dash, underscore. Tanpa spasi.</p>
            @error('username')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                Email <span class="text-red-500">*</span>
            </label>
            <input type="email" name="email" required maxlength="255" value="{{ old('email', $user->email ?? '') }}"
                placeholder="budi@example.com"
                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
            @error('email')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- PASSWORD (hanya saat create) --}}
    @unless ($isEdit)
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                    Password <span class="text-red-500">*</span>
                </label>
                <input type="password" name="password" required minlength="6" placeholder="Min. 6 karakter"
                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                @error('password')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                    Konfirmasi Password <span class="text-red-500">*</span>
                </label>
                <input type="password" name="password_confirmation" required minlength="6"
                    placeholder="Ulangi password"
                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
            </div>
        </div>
    @endunless

    {{-- ROLE + DIVISION --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                Role <span class="text-red-500">*</span>
            </label>
            <select name="role" id="roleSelect" required
                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition
                       {{ $isSelf ? 'opacity-60 cursor-not-allowed' : '' }}"
                @if ($isSelf) disabled @endif>
                <option value="admin" @selected(old('role', $user->role ?? '') === 'admin')>Admin</option>
                <option value="technician" @selected(old('role', $user->role ?? 'technician') === 'technician')>Teknisi</option>
            </select>
            @if ($isSelf)
                <input type="hidden" name="role" value="{{ $user->role }}">
                <p class="text-[11px] text-amber-600 mt-1">Tidak bisa ubah role sendiri.</p>
            @endif
            @error('role')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div id="divisionWrapper">
            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                Divisi <span class="text-red-500">*</span>
            </label>
            <select name="division" id="divisionSelect"
                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm bg-white
                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                <option value="">— Pilih Divisi —</option>
                <option value="cctv" @selected(old('division', $user->division ?? '') === 'cctv')>CCTV</option>
                <option value="helpdesk" @selected(old('division', $user->division ?? '') === 'helpdesk')>Helpdesk</option>
            </select>
            @error('division')
                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- STATUS AKTIF --}}
    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 {{ $isSelf ? 'opacity-60' : '' }}">
        <label class="flex items-start gap-3 cursor-pointer">
            <input type="checkbox" name="is_active" id="isActiveCheckbox" value="1"
                @checked(old('is_active', $user->is_active ?? true)) @if ($isSelf) disabled @endif
                class="mt-0.5 w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
            @if ($isSelf)
                <input type="hidden" name="is_active" value="1">
            @endif
            <div class="flex-1">
                <div class="font-semibold text-gray-800 text-sm">Akun Aktif</div>
                <div class="text-xs text-gray-500 mt-0.5">
                    @if ($isSelf)
                        Anda tidak bisa menonaktifkan akun sendiri.
                    @else
                        User nonaktif tidak dapat login ke sistem.
                    @endif
                </div>
            </div>
        </label>
    </div>

</div>
