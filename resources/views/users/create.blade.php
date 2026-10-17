@extends('layouts.app')

@section('title', 'Tambah User')

@section('content')

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
            <h1 class="text-2xl font-bold text-gray-800 mt-3">Tambah User</h1>
            <p class="text-sm text-gray-500 mt-1">Buat akun baru untuk admin atau teknisi</p>
        </div>

        {{-- ERROR --}}
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 text-sm">
                @foreach ($errors->all() as $err)
                    <div>{{ $err }}</div>
                @endforeach
            </div>
        @endif

        {{-- FORM --}}
        <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data"
            class="bg-white rounded-2xl shadow-sm border border-gray-100">
            @csrf

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
                    Simpan
                </button>
            </div>
        </form>

    </div>

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
    </script>
@endpush
