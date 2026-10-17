@extends('layouts.app')

@section('title', 'Tugas Mandiri')

@section('content')

    @php
        $user = auth()->user();
        $division = $user->division;
        $divisionLabel = $division === 'cctv' ? 'CCTV' : 'Helpdesk';
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
            <h1 class="text-2xl font-bold text-gray-800 mt-3">Tugas Mandiri</h1>
            <p class="text-sm text-gray-500 mt-1">
                Catat pekerjaan yang Anda kerjakan di luar tugas dari admin
            </p>
        </div>

        {{-- INFO BANNER --}}
        <div
            class="bg-gradient-to-br from-indigo-50 to-violet-50 border border-indigo-100 rounded-2xl px-4 py-3 flex items-start gap-3">
            <div class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="text-xs text-indigo-800">
                Tugas ini akan tercatat di divisi
                <span
                    class="font-bold uppercase tracking-wide
                    @if ($division === 'cctv') text-purple-700 @else text-teal-700 @endif">
                    {{ $divisionLabel }}
                </span>
                — pilih <b>Mulai</b> kalau masih dikerjakan, atau <b>Selesai</b> kalau sudah kelar.
            </div>
        </div>

        {{-- FORM --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <form id="woForm" class="divide-y divide-gray-100">

                <div class="p-6 space-y-5">

                    {{-- DIVISI (readonly) --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            Divisi
                        </label>
                        <div
                            class="flex items-center gap-3 px-4 py-3 rounded-xl border-2
                            @if ($division === 'cctv') border-purple-200 bg-purple-50/40 @else border-teal-200 bg-teal-50/40 @endif">
                            <div
                                class="w-8 h-8 rounded-lg flex items-center justify-center
                                @if ($division === 'cctv') bg-purple-100 @else bg-teal-100 @endif">
                                @if ($division === 'cctv')
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-purple-600" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-teal-600" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                @endif
                            </div>
                            <div
                                class="font-bold
                                @if ($division === 'cctv') text-purple-900 @else text-teal-900 @endif">
                                Divisi {{ $divisionLabel }}
                            </div>
                            <span class="ml-auto text-xs text-gray-500">Otomatis dari akun Anda</span>
                        </div>
                    </div>

                    {{-- JUDUL --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            Judul Pekerjaan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="title" required maxlength="255"
                            placeholder="Contoh: Penggantian lampu koridor"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-800 placeholder:text-gray-400
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                    </div>

                    {{-- DESKRIPSI --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            Deskripsi
                        </label>
                        <textarea name="description" rows="3" placeholder="Detail pekerjaan yang Anda lakukan"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-800 placeholder:text-gray-400
                                     focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition resize-none"></textarea>
                    </div>

                    {{-- KATEGORI + GEDUNG + LOKASI --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                                Kategori
                            </label>
                            <input type="text" name="category" maxlength="50" placeholder="Contoh: Preventif"
                                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-800 placeholder:text-gray-400
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                                Gedung
                            </label>
                            <input type="text" name="building" maxlength="255" placeholder="Contoh: Gedung A"
                                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-800 placeholder:text-gray-400
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                                Lokasi / Ruangan
                            </label>
                            <input type="text" name="location" maxlength="255" placeholder="Contoh: Keuangan"
                                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-800 placeholder:text-gray-400
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                        </div>
                    </div>

                    {{-- PRIORITAS --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            Prioritas <span class="text-red-500">*</span>
                        </label>
                        <select name="priority" required
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-800
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition bg-white">
                            <option value="low">Low</option>
                            <option value="normal" selected>Normal</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>

                    {{-- WAKTU MULAI --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            Waktu Mulai Dikerjakan <span class="text-red-500">*</span>
                        </label>
                        <input type="datetime-local" name="started_at" id="startedAt" required
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-800
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">

                        <div class="flex gap-2 mt-2 flex-wrap">
                            <button type="button" onclick="setTime('startedAt', 'now')"
                                class="text-xs px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-600 font-medium transition">
                                Sekarang
                            </button>
                            <button type="button" onclick="setTime('startedAt', '-1h')"
                                class="text-xs px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-600 font-medium transition">
                                1 jam lalu
                            </button>
                            <button type="button" onclick="setTime('startedAt', '-3h')"
                                class="text-xs px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-600 font-medium transition">
                                3 jam lalu
                            </button>
                            <button type="button" onclick="setTime('startedAt', '-1d')"
                                class="text-xs px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50 text-gray-600 font-medium transition">
                                Kemarin
                            </button>
                        </div>
                    </div>

                    {{-- TOGGLE: SUDAH SELESAI? --}}
                    <div class="bg-amber-50/60 border border-amber-200 rounded-xl p-4">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="is_completed" id="isCompleted"
                                class="mt-0.5 w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer">
                            <div class="flex-1">
                                <div class="font-semibold text-amber-900 text-sm">
                                    Pekerjaan ini sudah selesai
                                </div>
                                <div class="text-xs text-amber-700 mt-0.5">
                                    Centang kalau Anda baru input tugas ini setelah selesai dikerjakan.
                                    Status akan langsung <b>Selesai</b>.
                                </div>
                            </div>
                        </label>

                        {{-- WAKTU SELESAI --}}
                        <div id="completedAtWrapper" class="hidden mt-4 pt-4 border-t border-amber-200 space-y-2">
                            <div>
                                <label class="block text-xs font-semibold text-amber-800 uppercase tracking-wide mb-2">
                                    Waktu Selesai <span class="text-red-500">*</span>
                                </label>
                                <input type="datetime-local" name="completed_at" id="completedAt"
                                    class="w-full border border-amber-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-800 bg-white
                                           focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition">
                            </div>

                            <div class="flex gap-2 flex-wrap">
                                <button type="button" onclick="setTime('completedAt', 'now')"
                                    class="text-xs px-3 py-1.5 rounded-lg border border-amber-200 hover:bg-white text-amber-700 font-medium transition">
                                    Sekarang
                                </button>
                                <button type="button" onclick="setTime('completedAt', '-30m')"
                                    class="text-xs px-3 py-1.5 rounded-lg border border-amber-200 hover:bg-white text-amber-700 font-medium transition">
                                    30 menit lalu
                                </button>
                                <button type="button" onclick="setTime('completedAt', '-2h')"
                                    class="text-xs px-3 py-1.5 rounded-lg border border-amber-200 hover:bg-white text-amber-700 font-medium transition">
                                    2 jam lalu
                                </button>
                            </div>
                        </div>

                        {{-- CATATAN PENYELESAIAN --}}
                        <div id="noteWrapper" class="hidden mt-4 pt-4 border-t border-amber-200">
                            <label class="block text-xs font-semibold text-amber-800 uppercase tracking-wide mb-2">
                                Catatan Penyelesaian
                            </label>
                            <textarea name="resolution_note" rows="2" placeholder="Contoh: Sudah diganti, kamera normal kembali."
                                class="w-full border border-amber-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-800 bg-white
                                       focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition resize-none"></textarea>
                        </div>

                        {{-- UPLOAD MULTI MEDIA (SEBELUM + SESUDAH) --}}
                        <div id="mediaWrapper" class="hidden mt-4 pt-4 border-t border-amber-200 space-y-4">

                            {{-- SEBELUM --}}
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-xs font-semibold text-amber-800 uppercase tracking-wide">
                                        📷 Foto / Video Sebelum
                                    </label>
                                    <span class="text-[10px] text-amber-600 normal-case font-normal">
                                        Opsional · max 20 file · max 20MB
                                    </span>
                                </div>

                                <div id="dropBefore"
                                    class="border-2 border-dashed border-amber-300 rounded-xl p-3 transition
                   hover:border-amber-500 hover:bg-amber-50/40 bg-white">
                                    <div id="previewBefore" class="grid grid-cols-3 sm:grid-cols-4 gap-2 mb-2"></div>

                                    <div onclick="document.getElementById('inputBefore').click()"
                                        class="cursor-pointer text-center py-3">
                                        <input type="file" id="inputBefore" accept="image/*,video/*" multiple
                                            class="hidden">
                                        <div class="text-sm text-gray-600 font-medium">➕ Klik / drag file ke sini</div>
                                        <div class="text-xs text-gray-400 mt-0.5">Kondisi awal sebelum dikerjakan</div>
                                    </div>
                                </div>
                            </div>

                            {{-- SESUDAH --}}
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-xs font-semibold text-emerald-700 uppercase tracking-wide">
                                        Foto / Video Sesudah
                                    </label>
                                    <span class="text-[10px] text-emerald-600 normal-case font-normal">
                                        Opsional · max 20 file · max 20MB
                                    </span>
                                </div>

                                <div id="dropAfter"
                                    class="border-2 border-dashed border-emerald-300 rounded-xl p-3 transition
                   hover:border-emerald-500 hover:bg-emerald-50/40 bg-white">
                                    <div id="previewAfter" class="grid grid-cols-3 sm:grid-cols-4 gap-2 mb-2"></div>

                                    <div onclick="document.getElementById('inputAfter').click()"
                                        class="cursor-pointer text-center py-3">
                                        <input type="file" id="inputAfter" accept="image/*,video/*" multiple
                                            class="hidden">
                                        <div class="text-sm text-gray-600 font-medium">➕ Klik / drag file ke sini</div>
                                        <div class="text-xs text-gray-400 mt-0.5">Hasil setelah pekerjaan selesai</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="p-6 bg-gray-50 flex justify-between items-center rounded-b-2xl flex-wrap gap-3">
                    <div id="formError" class="text-sm text-red-600 hidden"></div>
                    <div class="flex gap-2 ml-auto">
                        <a href="{{ route('dashboard') }}"
                            class="text-sm font-semibold text-gray-600 hover:text-gray-900
                              border border-gray-200 hover:border-gray-300
                              px-4 py-2.5 rounded-xl transition">
                            Batal
                        </a>
                        <button type="submit" id="submitBtn"
                            class="text-sm font-semibold text-white
                                   bg-gradient-to-br from-indigo-500 to-violet-600
                                   hover:from-indigo-600 hover:to-violet-700
                                   px-5 py-2.5 rounded-xl shadow-lg shadow-indigo-500/30
                                   transition-all duration-300
                                   disabled:opacity-50 disabled:cursor-not-allowed
                                   inline-flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span id="submitLabel">Simpan & Mulai</span>
                        </button>
                    </div>
                </div>

            </form>
        </div>

    </div>

@endsection

@push('scripts')
    <script>
        const form = document.getElementById('woForm');
        const submitBtn = document.getElementById('submitBtn');
        const submitLabel = document.getElementById('submitLabel');
        const formError = document.getElementById('formError');
        const startedAtInput = document.getElementById('startedAt');
        const isCompletedCheckbox = document.getElementById('isCompleted');
        const completedAtInput = document.getElementById('completedAt');
        const completedAtWrapper = document.getElementById('completedAtWrapper');
        const noteWrapper = document.getElementById('noteWrapper');
        const mediaWrapper = document.getElementById('mediaWrapper');

        function toLocalISO(date) {
            const pad = (n) => String(n).padStart(2, '0');
            return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
        }

        function setTime(inputId, offset) {
            const input = document.getElementById(inputId);
            const now = new Date();

            const offsets = {
                'now': 0,
                '-30m': -30 * 60 * 1000,
                '-1h': -60 * 60 * 1000,
                '-2h': -2 * 60 * 60 * 1000,
                '-3h': -3 * 60 * 60 * 1000,
                '-1d': -24 * 60 * 60 * 1000,
            };

            const ms = offsets[offset] ?? 0;
            const result = new Date(now.getTime() + ms);
            input.value = toLocalISO(result);
        }

        setTime('startedAt', 'now');
        isCompletedCheckbox.addEventListener('change', () => {
            const checked = isCompletedCheckbox.checked;

            if (checked) {
                completedAtWrapper.classList.remove('hidden');
                noteWrapper.classList.remove('hidden');
                mediaWrapper.classList.remove('hidden');
                completedAtInput.required = true;

                if (!completedAtInput.value) {
                    setTime('completedAt', 'now');
                }

                submitLabel.textContent = 'Simpan & Selesai';
                submitBtn.classList.remove('from-indigo-500', 'to-violet-600',
                    'hover:from-indigo-600', 'hover:to-violet-700',
                    'shadow-indigo-500/30');
                submitBtn.classList.add('from-emerald-500', 'to-emerald-600',
                    'hover:from-emerald-600', 'hover:to-emerald-700',
                    'shadow-emerald-500/30');
            } else {
                completedAtWrapper.classList.add('hidden');
                noteWrapper.classList.add('hidden');
                mediaWrapper.classList.add('hidden');
                completedAtInput.required = false;
                completedAtInput.value = '';
                mediaBefore.forEach(m => URL.revokeObjectURL(m.previewUrl));
                mediaAfter.forEach(m => URL.revokeObjectURL(m.previewUrl));
                mediaBefore = [];
                mediaAfter = [];
                renderPreview('previewBefore', 'before');
                renderPreview('previewAfter', 'after');

                submitLabel.textContent = 'Simpan & Mulai';
                submitBtn.classList.remove('from-emerald-500', 'to-emerald-600',
                    'hover:from-emerald-600', 'hover:to-emerald-700',
                    'shadow-emerald-500/30');
                submitBtn.classList.add('from-indigo-500', 'to-violet-600',
                    'hover:from-indigo-600', 'hover:to-violet-700',
                    'shadow-indigo-500/30');
            }
        });
        let mediaBefore = [];
        let mediaAfter = [];

        setupDropZone('dropBefore', 'inputBefore', 'previewBefore', 'before');
        setupDropZone('dropAfter', 'inputAfter', 'previewAfter', 'after');

        function setupDropZone(zoneId, inputId, previewId, type) {
            const zone = document.getElementById(zoneId);
            const input = document.getElementById(inputId);
            if (!zone || !input) return;
            zone.addEventListener('click', (e) => {
                if (e.target.closest('[data-remove-media]')) return;
                input.click();
            });

            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                zone.classList.add('border-solid', 'bg-opacity-60');
            });
            zone.addEventListener('dragleave', () => {
                zone.classList.remove('border-solid', 'bg-opacity-60');
            });
            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                zone.classList.remove('border-solid', 'bg-opacity-60');
                handleFiles(Array.from(e.dataTransfer.files), previewId, type);
            });

            input.addEventListener('change', () => {
                handleFiles(Array.from(input.files), previewId, type);
                input.value = '';
            });
        }

        function handleFiles(files, previewId, type) {
            const arr = type === 'before' ? mediaBefore : mediaAfter;
            const allowed = [
                'image/jpeg', 'image/jpg', 'image/png', 'image/webp',
                'video/mp4', 'video/quicktime', 'video/webm', 'video/x-msvideo'
            ];

            files.forEach(file => {
                if (!allowed.includes(file.type)) {
                    showToast(`Format ${file.name} tidak didukung`, 'error');
                    return;
                }
                if (file.size > 20 * 1024 * 1024) {
                    showToast(`${file.name} > 20MB`, 'error');
                    return;
                }
                if (arr.length >= 20) {
                    showToast('Maksimal 20 file per kategori', 'error');
                    return;
                }

                arr.push({
                    file,
                    type,
                    previewUrl: URL.createObjectURL(file),
                    isVideo: file.type.startsWith('video'),
                });
            });

            renderPreview(previewId, type);
        }

        function renderPreview(previewId, type) {
            const el = document.getElementById(previewId);
            const arr = type === 'before' ? mediaBefore : mediaAfter;
            if (!el) return;

            if (!arr.length) {
                el.innerHTML = '';
                return;
            }

            el.innerHTML = arr.map((m, i) => `
        <div class="relative group rounded-lg overflow-hidden border border-gray-200 bg-gray-50">
            ${m.isVideo
                ? `<video src="${m.previewUrl}" class="w-full h-20 object-cover"></video>`
                : `<img src="${m.previewUrl}" class="w-full h-20 object-cover">`}
            <button type="button" data-remove-media
                onclick="event.stopPropagation(); removeMedia('${type}', ${i})"
                class="absolute top-1 right-1 w-5 h-5 rounded-full bg-red-500 text-white text-[10px] font-bold
                       opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                ✕
            </button>
        </div>
    `).join('');
        }

        function removeMedia(type, i) {
            const arr = type === 'before' ? mediaBefore : mediaAfter;
            URL.revokeObjectURL(arr[i].previewUrl);
            arr.splice(i, 1);
            renderPreview(type === 'before' ? 'previewBefore' : 'previewAfter', type);
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            formError.classList.add('hidden');

            const isCompleted = isCompletedCheckbox.checked;
            const startedAt = new Date(startedAtInput.value);

            if (startedAt > new Date()) {
                formError.textContent = 'Waktu mulai tidak boleh di masa depan.';
                formError.classList.remove('hidden');
                return;
            }

            if (isCompleted) {
                const completedAt = new Date(completedAtInput.value);

                if (!completedAtInput.value) {
                    formError.textContent = 'Waktu selesai wajib diisi.';
                    formError.classList.remove('hidden');
                    return;
                }

                if (completedAt > new Date()) {
                    formError.textContent = 'Waktu selesai tidak boleh di masa depan.';
                    formError.classList.remove('hidden');
                    return;
                }

                if (completedAt < startedAt) {
                    formError.textContent = 'Waktu selesai tidak boleh lebih awal dari waktu mulai.';
                    formError.classList.remove('hidden');
                    return;
                }
            }

            const originalLabel = submitLabel.textContent;
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Menyimpan...
            `;

            const fd = new FormData(form);
            fd.set('is_completed', isCompleted ? '1' : '0');
            const allMedia = [...mediaBefore, ...mediaAfter];
            allMedia.forEach((m, i) => {
                fd.append(`media[${i}]`, m.file);
                fd.append(`media_types[${i}]`, m.type);
                fd.append(`media_captions[${i}]`, '');
            });

            try {
                await api.post("{{ route('technician.wo.store') }}", fd);
                showToast(
                    isCompleted ?
                    'Tugas mandiri selesai dicatat! 🎉' :
                    'Tugas mandiri dibuat & dimulai!',
                    'success'
                );
                setTimeout(() => window.location.href = "{{ route('dashboard') }}", 800);
            } catch (err) {
                formError.textContent = err.message;
                formError.classList.remove('hidden');
                submitBtn.disabled = false;
                submitBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span>${originalLabel}</span>
                `;
            }
        });
    </script>
@endpush
