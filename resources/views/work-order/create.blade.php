@extends('layouts.app')

@section('title', 'Buat Work Order')

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
            <h1 class="text-2xl font-bold text-gray-800 mt-3">Buat Work Order</h1>
            <p class="text-sm text-gray-500 mt-1">
                Buat tugas baru untuk divisi CCTV atau Helpdesk
            </p>
        </div>

        {{-- FORM --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
            <form id="woForm" class="divide-y divide-gray-100">

                {{-- SECTION 1: INFO --}}
                <div class="p-6 space-y-5">

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            Judul Tugas <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="title" required maxlength="255"
                            placeholder="Contoh: Perbaikan kamera lobi / Ganti mouse rusak"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-800 placeholder:text-gray-400
                                  focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            Deskripsi
                        </label>
                        <textarea name="description" rows="3" placeholder="Detail pekerjaan, alat yang dibutuhkan, dsb."
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-800 placeholder:text-gray-400
                                     focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition resize-none"></textarea>
                    </div>

                    {{-- ===== DIVISI ===== --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            Divisi Pekerjaan <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 gap-3">

                            <label class="division-option cursor-pointer">
                                <input type="radio" name="division" value="cctv" class="sr-only peer" required>
                                <div
                                    class="border-2 border-gray-200 rounded-xl p-4
                                            hover:border-purple-300 hover:bg-purple-50/30
                                            peer-checked:border-purple-500 peer-checked:bg-purple-50
                                            transition">
                                    <div class="flex items-center gap-2 mb-1">
                                        <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-purple-600"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <div class="font-bold text-purple-900">CCTV</div>
                                    </div>
                                    <div class="text-xs text-gray-500">Pekerjaan terkait kamera & monitoring</div>
                                </div>
                            </label>

                            <label class="division-option cursor-pointer">
                                <input type="radio" name="division" value="helpdesk" class="sr-only peer" required>
                                <div
                                    class="border-2 border-gray-200 rounded-xl p-4
                                            hover:border-teal-300 hover:bg-teal-50/30
                                            peer-checked:border-teal-500 peer-checked:bg-teal-50
                                            transition">
                                    <div class="flex items-center gap-2 mb-1">
                                        <div class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-teal-600"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                        </div>
                                        <div class="font-bold text-teal-900">Helpdesk</div>
                                    </div>
                                    <div class="text-xs text-gray-500">IT support & perbaikan komputer</div>
                                </div>
                            </label>

                        </div>
                    </div>

                    {{-- ===== KATEGORI + GEDUNG + LOKASI ===== --}}
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

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                                Deadline
                            </label>
                            <input type="date" name="deadline_at"
                                class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-800
                                      focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                        </div>
                    </div>

                    {{-- ===== MODE PENYELESAIAN ===== --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            Mode Penyelesaian <span class="text-red-500">*</span>
                        </label>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

                            {{-- GLOBAL (TIM) --}}
                            <label class="cursor-pointer">
                                <input type="radio" name="completion_mode" value="collective" class="sr-only peer"
                                    required checked>
                                <div
                                    class="border-2 border-gray-200 rounded-xl p-4
                                            hover:border-blue-300 hover:bg-blue-50/30
                                            peer-checked:border-blue-500 peer-checked:bg-blue-50
                                            transition">
                                    <div class="flex items-center gap-2 mb-1">
                                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </div>
                                        <div class="font-bold text-blue-900">Global (Tim)</div>
                                    </div>
                                    <div class="text-xs text-gray-500 leading-relaxed">
                                        1 teknisi selesai → semua teknisi otomatis selesai.
                                        Cocok untuk kerja bareng.
                                    </div>
                                </div>
                            </label>

                            {{-- INDIVIDUAL --}}
                            <label class="cursor-pointer">
                                <input type="radio" name="completion_mode" value="individual" class="sr-only peer"
                                    required>
                                <div
                                    class="border-2 border-gray-200 rounded-xl p-4
                                            hover:border-violet-300 hover:bg-violet-50/30
                                            peer-checked:border-violet-500 peer-checked:bg-violet-50
                                            transition">
                                    <div class="flex items-center gap-2 mb-1">
                                        <div class="w-8 h-8 rounded-lg bg-violet-100 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-violet-600"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                        <div class="font-bold text-violet-900">Individual</div>
                                    </div>
                                    <div class="text-xs text-gray-500 leading-relaxed">
                                        Setiap teknisi menyelesaikan tugasnya sendiri.
                                        Cocok untuk penilaian performa.
                                    </div>
                                </div>
                            </label>

                        </div>
                    </div>

                </div>

                {{-- SECTION 2: ASSIGN — MULTI TEKNISI + PILIH BY TIM --}}
                <div class="p-6">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide">
                            Tugaskan Ke
                        </label>
                        <span class="text-[10px] text-gray-400 font-semibold uppercase tracking-wide">
                            Opsional · bisa pilih banyak
                        </span>
                    </div>

                    <p class="text-xs text-gray-500 mb-3" id="assignHint">
                        Pilih divisi dulu untuk menampilkan teknisi yang tersedia.
                    </p>

                    <div id="technicianWrapper" class="opacity-50 pointer-events-none transition">

                        {{-- ============ PILIH BY TIM ============ --}}
                        <div id="teamSelectorWrapper" class="hidden mb-4">
                            <div
                                class="bg-gradient-to-br from-indigo-50 to-violet-50 rounded-xl p-3 border border-indigo-100">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center gap-1.5">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-indigo-600"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <span class="text-[11px] font-bold text-indigo-900 uppercase tracking-wide">
                                            Pilih Cepat by Tim
                                        </span>
                                    </div>
                                    <span class="text-[10px] text-indigo-600 font-normal">
                                        Auto-centang semua anggota
                                    </span>
                                </div>

                                <div id="teamButtons" class="flex flex-wrap gap-2">
                                    {{-- Di-generate oleh JS --}}
                                </div>
                            </div>
                        </div>

                        {{-- Header info --}}
                        <div class="flex items-center justify-between mb-2">
                            <div id="techCountInfo" class="text-xs text-gray-500">0 teknisi dipilih</div>
                            <button type="button" onclick="toggleAllTechnicians()"
                                class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">
                                Pilih Semua
                            </button>
                        </div>

                        {{-- List checkbox --}}
                        <div id="techCheckboxList"
                            class="max-h-64 overflow-y-auto border border-gray-200 rounded-xl divide-y divide-gray-100 bg-white">
                            <div class="p-4 text-center text-xs text-gray-400">Pilih divisi dulu</div>
                        </div>
                    </div>
                </div>

                {{-- SECTION 3: ACTION --}}
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
                                stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Buat Work Order
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
        const formError = document.getElementById('formError');
        const techWrapper = document.getElementById('technicianWrapper');
        const assignHint = document.getElementById('assignHint');
        const teamSelectorWrapper = document.getElementById('teamSelectorWrapper');
        const teamButtons = document.getElementById('teamButtons');

        const TECHNICIANS_BY_DIVISION = @json($techniciansByDivision);
        const TEAMS_BY_DIVISION = @json($teamsByDivision ?? ['cctv' => [], 'helpdesk' => []]);

        let selectedDivision = null;
        let selectedTechIds = [];
        document.querySelectorAll('input[name="division"]').forEach(radio => {
            radio.addEventListener('change', () => {
                selectedDivision = radio.value;
                selectedTechIds = [];
                renderTechCheckboxes(selectedDivision);
                renderTeamButtons(selectedDivision);

                techWrapper.classList.remove('opacity-50', 'pointer-events-none');
                assignHint.innerHTML =
                    `Menampilkan teknisi divisi <b>${selectedDivision === 'cctv' ? 'CCTV' : 'Helpdesk'}</b>.`;
            });
        });

        function renderTeamButtons(division) {
            const teams = TEAMS_BY_DIVISION[division] || [];

            if (!teams.length) {
                teamSelectorWrapper.classList.add('hidden');
                return;
            }

            const isCctv = division === 'cctv';
            const colorScheme = isCctv ? 'purple' : 'teal';

            teamButtons.innerHTML = teams.map(t => `
                <button type="button"
                    onclick="selectTeam(${t.id})"
                    data-team-id="${t.id}"
                    data-member-ids="${t.member_ids.join(',')}"
                    class="team-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg
                           text-xs font-semibold border-2 transition
                           bg-white text-${colorScheme}-700 border-${colorScheme}-300
                           hover:bg-${colorScheme}-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span>${t.name}</span>
                    <span class="opacity-70 font-normal">(${t.member_ids.length})</span>
                </button>
            `).join('');

            teamSelectorWrapper.classList.remove('hidden');
        }

        function selectTeam(teamId) {
            const btn = document.querySelector(`.team-btn[data-team-id="${teamId}"]`);
            if (!btn) return;

            const memberIds = btn.dataset.memberIds.split(',').map(Number).filter(Boolean);
            const allSelected = memberIds.length > 0 && memberIds.every(id => selectedTechIds.includes(id));

            const isCctv = selectedDivision === 'cctv';
            const colorScheme = isCctv ? 'purple' : 'teal';

            if (allSelected) {
                selectedTechIds = selectedTechIds.filter(id => !memberIds.includes(id));
            } else {
                const union = new Set([...selectedTechIds, ...memberIds]);
                selectedTechIds = Array.from(union);
            }
            document.querySelectorAll('.tech-checkbox').forEach(cb => {
                const id = parseInt(cb.value);
                if (memberIds.includes(id)) {
                    cb.checked = selectedTechIds.includes(id);
                    updateCheckboxVisual(cb);
                }
            });
            updateAllTeamButtonStates();
            updateTechCount();
        }

        function updateAllTeamButtonStates() {
            const isCctv = selectedDivision === 'cctv';
            const colorScheme = isCctv ? 'purple' : 'teal';

            document.querySelectorAll('.team-btn').forEach(btn => {
                const memberIds = btn.dataset.memberIds.split(',').map(Number).filter(Boolean);
                const allSelected = memberIds.length > 0 && memberIds.every(id => selectedTechIds.includes(id));

                if (allSelected) {
                    btn.className = `team-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg
                                     text-xs font-semibold border-2 transition
                                     bg-${colorScheme}-500 text-white border-${colorScheme}-500
                                     shadow-lg shadow-${colorScheme}-500/30`;
                } else {
                    btn.className = `team-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg
                                     text-xs font-semibold border-2 transition
                                     bg-white text-${colorScheme}-700 border-${colorScheme}-300
                                     hover:bg-${colorScheme}-50`;
                }
            });
        }

        function renderTechCheckboxes(division) {
            const list = TECHNICIANS_BY_DIVISION[division] || [];
            const container = document.getElementById('techCheckboxList');

            if (!list.length) {
                container.innerHTML = '<div class="p-4 text-center text-xs text-gray-400">Belum ada teknisi</div>';
                updateTechCount();
                return;
            }

            container.innerHTML = list.map(t => {
                const teamLabel = t.team_name ?
                    `<span class="text-[10px] font-semibold ${division === 'cctv' ? 'text-purple-600' : 'text-teal-600'}">· ${t.team_name}</span>` :
                    '';

                return `
                    <label class="tech-item flex items-center gap-3 px-3 py-2.5 hover:bg-indigo-50/40 cursor-pointer transition">
                        <input type="checkbox" value="${t.id}"
                            onchange="toggleTech(${t.id}, this.checked)"
                            class="tech-checkbox w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600
                                    flex items-center justify-center text-white font-bold text-[10px] shrink-0">
                            ${t.name.charAt(0).toUpperCase()}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm text-gray-800 font-medium truncate">${t.name}</div>
                            <div class="text-xs text-gray-400 truncate">
                                @${t.username} ${teamLabel}
                            </div>
                        </div>
                    </label>
                `;
            }).join('');

            updateTechCount();
        }

        function toggleTech(id, checked) {
            if (checked) {
                if (!selectedTechIds.includes(id)) selectedTechIds.push(id);
            } else {
                selectedTechIds = selectedTechIds.filter(x => x !== id);
            }
            updateAllTeamButtonStates();
            updateTechCount();
        }

        function toggleAllTechnicians() {
            const list = TECHNICIANS_BY_DIVISION[selectedDivision] || [];
            const allChecked = selectedTechIds.length === list.length;

            selectedTechIds = allChecked ? [] : list.map(t => t.id);

            document.querySelectorAll('.tech-checkbox').forEach(cb => {
                cb.checked = selectedTechIds.includes(parseInt(cb.value));
            });
            updateAllTeamButtonStates();
            updateTechCount();
        }

        function updateTechCount() {
            const info = document.getElementById('techCountInfo');
            if (!info) return;
            info.textContent = selectedTechIds.length ?
                `${selectedTechIds.length} teknisi dipilih` :
                '0 teknisi dipilih (tugas terbuka untuk semua)';
            info.className = selectedTechIds.length ?
                'text-xs font-semibold text-indigo-600' :
                'text-xs text-gray-500';
        }

        function updateCheckboxVisual(cb) {
            const label = cb.closest('.tech-item');
            if (!label) return;

            if (cb.checked) {
                label.classList.add('bg-indigo-50');
            } else {
                label.classList.remove('bg-indigo-50');
            }
        }
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            formError.classList.add('hidden');

            if (!selectedDivision) {
                formError.textContent = 'Pilih divisi pekerjaan terlebih dahulu.';
                formError.classList.remove('hidden');
                return;
            }

            const fd = new FormData(form);
            if (!fd.get('completion_mode')) {
                formError.textContent = 'Pilih mode penyelesaian terlebih dahulu.';
                formError.classList.remove('hidden');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Menyimpan...
            `;

            const payload = {
                title: fd.get('title'),
                description: fd.get('description') || null,
                category: fd.get('category') || null,
                division: fd.get('division'),
                priority: fd.get('priority'),
                completion_mode: fd.get('completion_mode'),
                building: fd.get('building') || null,
                location: fd.get('location') || null,
                deadline_at: fd.get('deadline_at') || null,
                technician_ids: selectedTechIds,
            };

            try {
                await api.post("{{ route('admin.wo.store') }}", payload);
                showToast('Work order berhasil dibuat!', 'success');
                setTimeout(() => window.location.href = "{{ route('dashboard') }}", 800);
            } catch (err) {
                formError.textContent = err.message;
                formError.classList.remove('hidden');
                submitBtn.disabled = false;
                submitBtn.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Buat Work Order
                `;
            }
        });
    </script>
@endpush
