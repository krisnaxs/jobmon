@extends('layouts.app')

@section('title', 'Import Work Order')

@section('content')

    @php
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
    @endphp

    <div class="max-w-2xl mx-auto space-y-5">

        {{-- ============ HEADER ============ --}}
        <div>
            <a href="{{ route('admin.wo.index') }}"
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
            <h1 class="text-2xl font-bold text-gray-800 mt-3">Import Work Order</h1>
            <p class="text-sm text-gray-500 mt-1">
                Upload file Excel sesuai template untuk membuat work order secara massal
            </p>
        </div>

        {{-- ============ INFO TEMPLATE ============ --}}
        <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <div class="text-sm font-bold text-indigo-900">Belum punya template?</div>
                    <p class="text-xs text-indigo-700 mt-1 leading-relaxed">
                        Download template dulu, isi sesuai panduan di dalamnya, lalu upload di sini.
                        Pastikan format tanggal <b>dd/mm/yyyy</b> dan kolom tanggal diformat
                        sebagai <b>Text</b> di Excel.
                    </p>
                    <a href="{{ route('admin.wo.template') }}"
                        class="inline-flex items-center gap-1.5 mt-2.5 px-3 py-1.5 rounded-lg
                               bg-white border border-indigo-200
                               text-xs font-semibold text-indigo-700
                               hover:bg-indigo-100 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Download Template
                    </a>
                </div>
            </div>
        </div>

        {{-- ============ ALERT SUCCESS / WARNING ============ --}}
        @if (session('success'))
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl px-4 py-3 flex items-start gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600 shrink-0" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm text-emerald-700">{{ session('success') }}</div>
            </div>
        @endif

        @if (session('warning'))
            <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 flex items-start gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600 shrink-0" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="text-sm text-amber-700">{{ session('warning') }}</div>
            </div>
        @endif

        {{-- ============ DETAIL ERROR ============ --}}
        @if (session('import_errors'))
            <div class="bg-rose-50 border border-rose-200 rounded-xl p-4">
                <div class="flex items-center gap-2 mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-rose-600" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="text-sm font-bold text-rose-900">
                        Detail Error ({{ count(session('import_errors')) }} baris)
                    </div>
                </div>
                <ul
                    class="space-y-1 text-xs text-rose-700 max-h-60 overflow-y-auto
                           bg-white rounded-lg p-3 border border-rose-100">
                    @foreach (session('import_errors') as $err)
                        <li class="flex items-start gap-1.5 leading-relaxed">
                            <span class="text-rose-400 mt-0.5">•</span>
                            <span>{{ $err }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- ============ FORM IMPORT ============ --}}
        <form action="{{ route('admin.wo.import') }}" method="POST" enctype="multipart/form-data"
            class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">
                    File Excel <span class="text-rose-500">*</span>
                </label>

                <div id="dropZone"
                    class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center
                           hover:border-indigo-400 hover:bg-indigo-50/30
                           transition cursor-pointer">

                    <input type="file" name="file" id="fileInput" accept=".xlsx,.xls,.csv" required class="hidden">

                    {{-- Placeholder --}}
                    <div id="filePlaceholder">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mx-auto mb-3" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                        </svg>
                        <div class="text-sm font-semibold text-gray-700">
                            Klik untuk pilih file
                        </div>
                        <div class="text-xs text-gray-400 mt-1">
                            atau drag & drop file ke sini
                        </div>
                    </div>

                    {{-- File selected --}}
                    <div id="fileSelected" class="hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-emerald-500 mx-auto mb-3"
                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-sm font-semibold text-emerald-700" id="fileName"></div>
                        <div class="text-xs text-gray-400 mt-1" id="fileSize"></div>
                        <button type="button" id="removeFile"
                            class="mt-2 text-xs text-rose-600 hover:text-rose-800 font-semibold">
                            Ganti file
                        </button>
                    </div>
                </div>

                <p class="text-xs text-gray-500 mt-2">
                    Format: <b>.xlsx, .xls, .csv</b> · Maksimal <b>5MB</b>
                </p>
            </div>

            {{-- Panduan singkat --}}
            <div class="bg-gray-50 border border-gray-100 rounded-xl p-4 space-y-2">
                <div class="text-xs font-bold text-gray-700 uppercase tracking-wide mb-2">
                    📋 Panduan Singkat
                </div>
                <ul class="text-xs text-gray-600 space-y-1">
                    <li class="flex items-start gap-1.5">
                        <span class="text-indigo-500 mt-0.5">•</span>
                        <span>Format tanggal: <b>dd/mm/yyyy</b> (contoh: <code
                                class="px-1 py-0.5 bg-gray-200 rounded text-[10px]">01/12/2026</code> =
                            1 Desember 2026)</span>
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="text-indigo-500 mt-0.5">•</span>
                        <span>Kolom <b>created_at</b> & <b>deadline</b> di Excel harus diformat sebagai
                            <b>Text</b></span>
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="text-indigo-500 mt-0.5">•</span>
                        <span>Kolom <b>divisi_pekerjaan</b>, <b>prioritas</b>, <b>mode_penyelesaian</b>
                            harus <b>lowercase</b></span>
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="text-indigo-500 mt-0.5">•</span>
                        <span><b>tugaskan_ke</b> diisi <b>username</b> (bukan nama). Pisah koma untuk
                            multiple: <code class="px-1 py-0.5 bg-gray-200 rounded text-[10px]">teknisi.a,
                                teknisi.b</code></span>
                    </li>
                    <li class="flex items-start gap-1.5">
                        <span class="text-indigo-500 mt-0.5">•</span>
                        <span>Kosongkan <b>tugaskan_ke</b> jika ingin WO muncul ke <b>semua teknisi</b>
                            di divisi tersebut</span>
                    </li>
                </ul>
            </div>

            {{-- Tombol --}}
            <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                <a href="{{ route('admin.wo.index') }}"
                    class="px-4 py-2 rounded-xl text-sm font-semibold text-gray-600
                           border border-gray-200 hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit" id="submitBtn"
                    class="inline-flex items-center gap-2 px-5 py-2 rounded-xl
                           text-sm font-semibold text-white
                           bg-gradient-to-br from-emerald-500 to-emerald-600
                           hover:from-emerald-600 hover:to-emerald-700
                           shadow-lg shadow-emerald-500/30
                           disabled:opacity-50 disabled:cursor-not-allowed
                           transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <span id="submitText">Import</span>
                </button>
            </div>
        </form>

    </div>

@endsection

@push('scripts')
    <script>
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const filePlaceholder = document.getElementById('filePlaceholder');
        const fileSelected = document.getElementById('fileSelected');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const removeFile = document.getElementById('removeFile');
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const form = document.querySelector('form');

        // Klik dropzone → buka file picker
        dropZone.addEventListener('click', (e) => {
            if (e.target.closest('#removeFile')) return;
            fileInput.click();
        });

        // Drag over
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('border-indigo-500', 'bg-indigo-50');
        });

        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('border-indigo-500', 'bg-indigo-50');
        });

        // Drop file
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('border-indigo-500', 'bg-indigo-50');

            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                handleFileChange();
            }
        });

        // File input change
        fileInput.addEventListener('change', handleFileChange);

        // Ganti file
        removeFile.addEventListener('click', (e) => {
            e.stopPropagation();
            fileInput.value = '';
            filePlaceholder.classList.remove('hidden');
            fileSelected.classList.add('hidden');
        });

        function handleFileChange() {
            if (!fileInput.files.length) {
                filePlaceholder.classList.remove('hidden');
                fileSelected.classList.add('hidden');
                return;
            }

            const file = fileInput.files[0];

            // Validasi ukuran (max 5MB)
            if (file.size > 5 * 1024 * 1024) {
                showToast('File terlalu besar. Maks 5MB.', 'error');
                fileInput.value = '';
                return;
            }

            // Validasi ekstensi
            const allowed = ['xlsx', 'xls', 'csv'];
            const ext = file.name.split('.').pop().toLowerCase();
            if (!allowed.includes(ext)) {
                showToast('Format file harus .xlsx, .xls, atau .csv', 'error');
                fileInput.value = '';
                return;
            }

            fileName.textContent = file.name;
            fileSize.textContent = formatSize(file.size);

            filePlaceholder.classList.add('hidden');
            fileSelected.classList.remove('hidden');
        }

        function formatSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
        }

        // Submit form
        form.addEventListener('submit', (e) => {
            if (!fileInput.files.length) {
                e.preventDefault();
                showToast('Pilih file dulu', 'error');
                return;
            }

            submitBtn.disabled = true;
            submitText.textContent = 'Mengimpor...';
        });
    </script>
@endpush
