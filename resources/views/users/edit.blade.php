@extends('layouts.app')

@section('title', 'Edit User')

@section('content')

    @php
        $isSelf = $user->id === auth()->id();
    @endphp

    <div class="max-w-3xl mx-auto space-y-6">

        {{-- HEADER --}}
        <div>
            <a href="{{ url()->previous() }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl
               bg-white border border-gray-200
               text-sm font-semibold text-gray-700
               hover:bg-gray-50 hover:border-gray-300
               shadow-sm transition-all duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali
            </a>
            <h1 class="text-2xl font-bold text-gray-800 mt-3">Edit User</h1>
            <p class="text-sm text-gray-500 mt-1">
                Update data <span class="font-semibold text-gray-700">{{ $user->name }}</span>
            </p>
        </div>

        {{-- SELF-EDIT WARNING --}}
        @if ($isSelf)
            <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600 shrink-0 mt-0.5" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01M5.07 19h13.86a2 2 0 001.74-2.98L13.74 4.02a2 2 0 00-3.48 0L3.33 16.02A2 2 0 005.07 19z" />
                </svg>
                <div class="text-xs text-amber-800">
                    Ini akun Anda sendiri. <b>Role</b> dan <b>status aktif</b> tidak bisa diubah dari sini.
                    Gunakan halaman Profile untuk ubah data pribadi.
                </div>
            </div>
        @endif

        {{-- FLASH --}}
        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- ERROR --}}
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm">
                @foreach ($errors->all() as $err)
                    <div>{{ $err }}</div>
                @endforeach
            </div>
        @endif

        {{-- FORM EDIT --}}
        <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data"
            class="bg-white rounded-2xl shadow-sm border border-gray-100">
            @csrf @method('PUT')

            @include('users.partials.form')

            <div class="p-6 bg-gray-50 flex justify-end gap-2 rounded-b-2xl border-t border-gray-100">
                <a href="{{ route('admin.users.index') }}"
                    class="px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-600 border border-gray-200 hover:bg-white transition">
                    Batal
                </a>
                <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white
                           bg-gradient-to-br from-indigo-500 to-violet-600
                           hover:from-indigo-600 hover:to-violet-700
                           shadow-lg shadow-indigo-500/30 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Simpan Perubahan
                </button>
            </div>
        </form>

        {{-- ==================== RESET PASSWORD SECTION ==================== --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-red-50 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-600" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                </div>
                <div>
                    <h2 class="font-semibold text-gray-800">Reset Password</h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Set password baru untuk {{ $user->name }}
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" class="p-6 space-y-4">
                @csrf @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            Password Baru <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password" required minlength="6" placeholder="Min. 6 karakter"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            Konfirmasi Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password_confirmation" required minlength="6"
                            placeholder="Ulangi password baru"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition">
                    </div>
                </div>

                @if ($errors->has('password'))
                    <div class="text-xs text-red-600">{{ $errors->first('password') }}</div>
                @endif

                <div class="flex justify-end">
                    <button type="button" onclick="confirmResetPassword()"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white
               bg-gradient-to-br from-red-500 to-red-600
               hover:from-red-600 hover:to-red-700
               shadow-lg shadow-red-500/30 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Reset Password
                    </button>
                </div>
            </form>
        </div>

    </div>

    {{-- ==================== FORM HAPUS AVATAR (HIDDEN) ==================== --}}
    @if ($user->avatar_url)
        <form id="removeAvatarForm" method="POST" action="{{ route('admin.users.remove-avatar', $user) }}" class="hidden">
            @csrf @method('DELETE')
        </form>
    @endif

@endsection

@push('scripts')
    <script>
        const roleSelect = document.getElementById('roleSelect');
        const divisionWrapper = document.getElementById('divisionWrapper');
        const divisionSelect = document.getElementById('divisionSelect');

        function updateDivisionVisibility() {
            const isAdmin = roleSelect.value === 'admin';
            divisionWrapper.style.display = isAdmin ? 'none' : '';
            divisionSelect.required = !isAdmin;
            if (isAdmin) divisionSelect.value = '';
        }

        roleSelect.addEventListener('change', updateDivisionVisibility);
        updateDivisionVisibility();
        const avatarInput = document.getElementById('avatarInput');
        const avatarPreviewImg = document.getElementById('avatarPreviewImg');
        const avatarInitial = document.getElementById('avatarInitial');

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
            avatarPreviewImg.src = URL.createObjectURL(file);
            avatarPreviewImg.classList.remove('hidden');
            avatarInitial?.classList.add('hidden');
        });
        async function confirmResetPassword() {
            const userPassword = document.querySelector('input[name="password"]');
            const userPasswordConfirm = document.querySelector('input[name="password_confirmation"]');
            if (!userPassword.value || userPassword.value.length < 6) {
                showToast('Password baru minimal 6 karakter.', 'warning');
                userPassword.focus();
                return;
            }
            if (userPassword.value !== userPasswordConfirm.value) {
                showToast('Konfirmasi password tidak cocok.', 'warning');
                userPasswordConfirm.focus();
                return;
            }

            const ok = await showConfirm({
                title: 'Reset Password?',
                subtitle: 'Tindakan ini akan mengubah password user',
                message: `Password untuk <b>{{ $user->name }}</b> akan diganti. User harus login ulang dengan password baru.`,
                okText: 'Ya, Reset',
                cancelText: 'Batal',
                type: 'danger',
            });

            if (!ok) return;
            document.querySelector('form[action*="reset-password"]').submit();
        }
    </script>
@endpush
