@extends('layouts.app')

@section('title', 'Detail ' . $workOrder->code)

@section('content')

    @php
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
        $division = $workOrder->division;
        $divisionLabel = $division === 'cctv' ? 'CCTV' : 'Helpdesk';
        $divisionBg = $division === 'cctv' ? 'bg-purple-100 text-purple-700' : 'bg-teal-100 text-teal-700';
        $myTechPivot = $workOrder->technicians->firstWhere('id', $user->id);
        $isAssignedTech = (bool) $myTechPivot;
        $myNoParticipation = (bool) ($myTechPivot?->pivot?->no_participation ?? false);
        $mySkippedAt = $myTechPivot?->pivot?->skipped_at ?? null;
        $myStatus = $myTechPivot?->pivot?->status ?? $workOrder->status;
        if ($myNoParticipation) {
            $myStatus = 'skipped';
        }
        $canStart = $myStatus === 'pending' && ($isAdmin || $user->isTechnician()) && !$myNoParticipation;

        $canFinish = $myStatus === 'in_progress' && ($isAdmin || $isAssignedTech) && !$myNoParticipation;

        // ============ HAK EDIT MEDIA ============
        $canEditMedia = $isAdmin || ($isAssignedTech && in_array($workOrder->status, ['in_progress', 'completed']));

        $statusStyle = [
            'pending' => ['bg-gray-100', 'text-gray-600', 'Pending'],
            'in_progress' => ['bg-amber-50', 'text-amber-700', 'Proses'],
            'completed' => ['bg-emerald-50', 'text-emerald-700', 'Selesai'],
            'cancelled' => ['bg-red-50', 'text-red-700', 'Batal'],
        ][$workOrder->status] ?? ['bg-gray-100', 'text-gray-600', $workOrder->status];
        $myStatusStyle = [
            'pending' => ['bg-gray-100', 'text-gray-600', 'Pending'],
            'in_progress' => ['bg-amber-50', 'text-amber-700', 'Proses'],
            'completed' => ['bg-emerald-50', 'text-emerald-700', 'Selesai'],
            'skipped' => ['bg-rose-50 border border-rose-200', 'text-rose-700', '⊘ Skip'],
        ][$myStatus] ?? ['bg-gray-100', 'text-gray-600', $myStatus];

        $priorityStyle = [
            'urgent' => ['bg-red-50', 'text-red-700', 'Urgent'],
            'high' => ['bg-amber-50', 'text-amber-700', 'High'],
            'normal' => ['bg-gray-100', 'text-gray-600', 'Normal'],
            'low' => ['bg-gray-50', 'text-gray-500', 'Low'],
        ][$workOrder->priority] ?? ['bg-gray-100', 'text-gray-600', 'Normal'];
        $isCollective = $workOrder->completion_mode === 'collective';
        $modeLabel = $isCollective ? 'Global (Tim)' : 'Individual';
        $modeBadgeClass = $isCollective
            ? 'bg-blue-50 text-blue-700 border-blue-200'
            : 'bg-violet-50 text-violet-700 border-violet-200';
        $totalTechs = $workOrder->technicians->count();

        $skippedTechs = $workOrder->technicians
            ->filter(fn($t) => ($t->pivot->no_participation ?? false) === true)
            ->count();

        $participatingTechs = $totalTechs - $skippedTechs;

        $completedTechs = $workOrder->technicians
            ->filter(
                fn($t) => ($t->pivot->status ?? null) === 'completed' &&
                    ($t->pivot->no_participation ?? false) === false,
            )
            ->count();
        $progressPct = $participatingTechs > 0 ? round(($completedTechs / $participatingTechs) * 100) : 0;
        $mediaBefore = $workOrder->media->where('type', 'before');
        $mediaAfter = $workOrder->media->where('type', 'after');
        $mediaOther = $workOrder->media->where('type', 'other');
        $techTeamMap = [];
        $allTeams = [];

        foreach ($workOrder->technicians as $t) {
            if (($t->pivot->no_participation ?? false) === true) {
                continue;
            }

            $teamName = $t->pivot->team_name_snapshot ?? null;
            if ($teamName) {
                $techTeamMap[$t->id] = $teamName;
                $allTeams[] = $teamName;
            }
        }
        $allTeams = array_values(array_unique($allTeams));
        $hasTeamInfo = count($techTeamMap) > 0;
    @endphp

    <div class="max-w-4xl mx-auto space-y-5">

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
        </div>

        {{-- MAIN CARD --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

            {{-- TOP BAR --}}
            <div class="px-6 py-5 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3 flex-wrap">
                    <span class="font-mono text-sm text-gray-500">{{ $workOrder->code }}</span>
                    <span class="text-xs px-2.5 py-1 rounded-lg font-bold uppercase tracking-wide {{ $divisionBg }}">
                        {{ $divisionLabel }}
                    </span>
                    <span
                        class="text-xs px-2.5 py-1 rounded-lg font-semibold {{ $priorityStyle[0] }} {{ $priorityStyle[1] }}">
                        {{ $priorityStyle[2] }}
                    </span>

                    {{-- BADGE MODE --}}
                    <span
                        class="inline-flex items-center gap-1 text-[10px] uppercase tracking-wide font-bold
                                 px-2 py-1 rounded-lg border {{ $modeBadgeClass }}">
                        @if ($isCollective)
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            {{ $modeLabel }}
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            {{ $modeLabel }}
                        @endif
                    </span>

                    {{-- Badge Tim --}}
                    @if ($hasTeamInfo)
                        <span
                            class="inline-flex items-center gap-1 text-[10px] uppercase tracking-wide font-bold
                                     px-2 py-1 rounded-lg border
                                     {{ $division === 'cctv' ? 'bg-purple-50 text-purple-700 border-purple-200' : 'bg-teal-50 text-teal-700 border-teal-200' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            {{ implode(' + ', $allTeams) }}
                        </span>
                    @endif
                </div>

                {{-- Status + Tombol Hapus --}}
                <div class="flex items-center gap-2 flex-wrap">
                    @if (!$isAdmin && $myTechPivot)
                        <span class="text-[10px] uppercase tracking-wide font-bold text-gray-400">Status Anda:</span>
                        <span
                            class="text-xs px-3 py-1.5 rounded-lg font-semibold
                                     {{ $myStatusStyle[0] }} {{ $myStatusStyle[1] }}">
                            {{ $myStatusStyle[2] }}
                        </span>
                        @if ($workOrder->status !== $myStatus && $myStatus !== 'skipped')
                            <span class="text-[10px] text-gray-400 italic">
                                (Global: {{ $statusStyle[2] }})
                            </span>
                        @endif
                    @else
                        <span
                            class="text-xs px-3 py-1.5 rounded-lg font-semibold {{ $statusStyle[0] }} {{ $statusStyle[1] }}">
                            {{ $statusStyle[2] }}
                        </span>
                    @endif

                    @if ($isAdmin)
                        <button type="button" onclick="openDeleteModal()" title="Hapus permanen work order ini"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg
                                   bg-rose-50 hover:bg-rose-100 border border-rose-200
                                   text-rose-700 text-xs font-semibold
                                   transition-all duration-200 ml-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Hapus
                        </button>
                    @endif
                </div>
            </div>

            {{-- ALERT: ANDA DI-SKIP --}}
            @if (!$isAdmin && $myNoParticipation)
                <div class="px-6 py-4 bg-rose-50 border-b border-rose-200">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-full bg-rose-100 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-rose-600" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="text-sm font-bold text-rose-800">Anda Tidak Berpartisipasi</div>
                            <div class="text-xs text-rose-600 mt-0.5 leading-relaxed">
                                Work order ini sudah ditutup oleh admin dan Anda di-flag
                                <b>tidak berpartisipasi</b>.
                                @if ($mySkippedAt)
                                    <br>Di-skip pada:
                                    <b>{{ \Carbon\Carbon::parse($mySkippedAt)->format('d M Y, H:i') }}</b>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- PROGRESS TIM (individual) --}}
            @if (!$isCollective && $participatingTechs > 1)
                <div class="px-6 py-3 bg-violet-50/50 border-b border-violet-100">
                    <div class="flex items-center justify-between text-xs mb-1.5 flex-wrap gap-2">
                        <span class="font-semibold text-violet-900">
                            Progress Tim — mode Individual
                        </span>
                        <span class="font-bold text-violet-700">
                            {{ $completedTechs }} / {{ $participatingTechs }} teknisi selesai
                            @if ($skippedTechs > 0)
                                <span class="text-rose-500 font-semibold"> · ⊘ {{ $skippedTechs }} skip</span>
                            @endif
                        </span>
                    </div>
                    <div class="w-full bg-violet-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-gradient-to-r from-violet-500 to-violet-600 h-full rounded-full transition-all"
                            style="width: {{ $progressPct }}%">
                        </div>
                    </div>
                </div>
            @endif

            {{-- BODY --}}
            <div class="p-6 space-y-6">

                {{-- TITLE --}}
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">{{ $workOrder->title }}</h1>
                    @if ($workOrder->description)
                        <p class="text-gray-600 mt-2 whitespace-pre-line">{{ $workOrder->description }}</p>
                    @endif
                </div>

                {{-- INFO GRID --}}
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">

                    <div>
                        <div class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-1">Kategori</div>
                        <div class="text-gray-800">{{ $workOrder->category ?? '-' }}</div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-1">Gedung</div>
                        <div class="text-gray-800">{{ $workOrder->building ?? '-' }}</div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-1">Lokasi / Ruangan</div>
                        <div class="text-gray-800">{{ $workOrder->location ?? '-' }}</div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-1">Sumber</div>
                        <div class="text-gray-800">
                            {{ $workOrder->source === 'technician' ? 'Mandiri (Teknisi)' : 'Dari Admin' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-1">Dibuat Oleh</div>
                        <div class="text-gray-800">{{ $workOrder->creator->name ?? '-' }}</div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-1">Deadline</div>
                        <div class="text-gray-800">
                            {{ $workOrder->deadline_at ? $workOrder->deadline_at->format('d M Y') : '-' }}
                        </div>
                    </div>

                    @if ($workOrder->started_at)
                        <div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-1">Mulai Global</div>
                            <div class="text-gray-800">{{ $workOrder->started_at->format('d M Y, H:i') }}</div>
                        </div>
                    @endif

                    @if ($workOrder->completed_at)
                        <div>
                            <div class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-1">Selesai Global
                            </div>
                            <div class="text-emerald-700 font-semibold">
                                {{ $workOrder->completed_at->format('d M Y, H:i') }}
                            </div>
                        </div>
                    @endif

                    <div>
                        <div class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-1">Dibuat</div>
                        <div class="text-gray-800">{{ $workOrder->created_at->format('d M Y, H:i') }}</div>
                    </div>
                </div>

                {{-- TABEL STATUS PER-TEKNISI --}}
                @if ($workOrder->technicians->count() > 0)
                    <div class="border border-gray-100 rounded-2xl overflow-hidden">
                        <div
                            class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex items-center justify-between flex-wrap gap-2">
                            <div>
                                <div class="text-sm font-bold text-gray-700">
                                    Status Per-Teknisi
                                </div>
                                <div class="text-xs text-gray-500 mt-0.5">
                                    @if ($isCollective)
                                        Mode Global — 1 teknisi selesai, semua otomatis selesai
                                    @else
                                        Mode Individual — setiap teknisi selesai sendiri
                                    @endif
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-semibold text-gray-600">
                                    {{ $completedTechs }} / {{ $participatingTechs }} selesai
                                </div>
                                @if ($skippedTechs > 0)
                                    <div class="text-[10px] text-rose-500 font-semibold mt-0.5">
                                        ⊘ {{ $skippedTechs }} tidak berpartisipasi
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="bg-white text-xs uppercase text-gray-400 border-b border-gray-100">
                                    <tr>
                                        <th class="px-4 py-2 text-left font-semibold">Teknisi</th>
                                        <th class="px-4 py-2 text-left font-semibold w-28">Tim</th>
                                        <th class="px-4 py-2 text-left font-semibold w-32">Status</th>
                                        <th class="px-4 py-2 text-left font-semibold w-36">Mulai</th>
                                        <th class="px-4 py-2 text-left font-semibold w-36">Selesai</th>
                                        <th class="px-4 py-2 text-left font-semibold">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($workOrder->technicians as $t)
                                        @php
                                            $pivotStatus = $t->pivot->status ?? 'pending';
                                            $isSkipped = (bool) ($t->pivot->no_participation ?? false);
                                            $skippedAt = $t->pivot->skipped_at ?? null;
                                            if ($isSkipped) {
                                                $pivotStyle = [
                                                    'bg-rose-50 border border-rose-200',
                                                    'text-rose-700',
                                                    '⊘ Skip',
                                                ];
                                            } else {
                                                $pivotStyle = [
                                                    'pending' => ['bg-gray-100', 'text-gray-600', 'Pending'],
                                                    'in_progress' => ['bg-amber-50', 'text-amber-700', 'Dikerjakan'],
                                                    'completed' => ['bg-emerald-50', 'text-emerald-700', 'Selesai'],
                                                ][$pivotStatus] ?? ['bg-gray-100', 'text-gray-600', $pivotStatus];
                                            }

                                            $isMe = $t->id === $user->id;
                                            $teamSnap = $t->pivot->team_name_snapshot ?? null;
                                        @endphp
                                        <tr
                                            class="{{ $isMe ? 'bg-indigo-50/40' : ($isSkipped ? 'bg-rose-50/30' : 'hover:bg-gray-50/60') }} transition">

                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-2">
                                                    <div
                                                        class="w-7 h-7 rounded-full bg-gradient-to-br
                                                                {{ $isSkipped
                                                                    ? 'from-rose-400 to-rose-500'
                                                                    : ($division === 'cctv'
                                                                        ? 'from-purple-500 to-purple-600'
                                                                        : 'from-teal-500 to-teal-600') }}
                                                                flex items-center justify-center text-white font-bold text-[11px] shrink-0
                                                                {{ $isSkipped ? 'opacity-60' : '' }}">
                                                        {{ strtoupper(substr($t->name, 0, 1)) }}
                                                    </div>
                                                    <div class="min-w-0">
                                                        <div
                                                            class="font-semibold text-gray-800 text-sm truncate
                                                                    {{ $isSkipped ? 'line-through text-gray-400' : '' }}">
                                                            {{ $t->name }}
                                                            @if ($isMe)
                                                                <span
                                                                    class="text-[10px] text-indigo-600 font-bold">(Anda)</span>
                                                            @endif
                                                        </div>
                                                        <div class="text-[11px] text-gray-400 truncate">
                                                            {{ '@' . $t->username }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="px-4 py-3">
                                                @if ($teamSnap)
                                                    <span
                                                        class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wide
                                                                 px-1.5 py-0.5 rounded border
                                                                 {{ $isSkipped
                                                                     ? 'bg-gray-100 text-gray-400 border-gray-200'
                                                                     : ($division === 'cctv'
                                                                         ? 'bg-purple-50 text-purple-700 border-purple-200'
                                                                         : 'bg-teal-50 text-teal-700 border-teal-200') }}">
                                                        {{ $teamSnap }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-300 italic text-xs">—</span>
                                                @endif
                                            </td>

                                            <td class="px-4 py-3">
                                                <span
                                                    class="inline-block text-xs px-2 py-0.5 rounded-md font-semibold
                                                             {{ $pivotStyle[0] }} {{ $pivotStyle[1] }}">
                                                    {{ $pivotStyle[2] }}
                                                </span>
                                                @if ($isSkipped && $skippedAt)
                                                    <div class="text-[10px] text-rose-500 mt-0.5">
                                                        {{ \Carbon\Carbon::parse($skippedAt)->format('d M, H:i') }}
                                                    </div>
                                                @endif
                                            </td>

                                            <td class="px-4 py-3 text-xs text-gray-600">
                                                @if ($t->pivot->started_at)
                                                    {{ \Carbon\Carbon::parse($t->pivot->started_at)->format('d M Y, H:i') }}
                                                @else
                                                    <span class="text-gray-300">—</span>
                                                @endif
                                            </td>

                                            <td class="px-4 py-3 text-xs text-gray-600">
                                                @if ($t->pivot->completed_at)
                                                    <span class="text-emerald-700 font-semibold">
                                                        {{ \Carbon\Carbon::parse($t->pivot->completed_at)->format('d M Y, H:i') }}
                                                    </span>
                                                @else
                                                    <span class="text-gray-300">—</span>
                                                @endif
                                            </td>

                                            <td class="px-4 py-3 text-xs text-gray-600">
                                                @if ($isSkipped)
                                                    <span class="italic text-rose-500">
                                                        Tidak berpartisipasi saat WO ditutup
                                                    </span>
                                                @elseif ($t->pivot->resolution_note)
                                                    <div class="whitespace-pre-line max-w-xs">
                                                        {{ $t->pivot->resolution_note }}
                                                    </div>
                                                @else
                                                    <span class="text-gray-300">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="bg-gray-50 border border-gray-100 rounded-2xl p-4 text-center">
                        <div class="text-gray-400 italic text-sm">Belum di-assign ke teknisi</div>
                    </div>
                @endif

                {{-- RESOLUTION NOTE (global) --}}
                @if ($workOrder->resolution_note && $isCollective)
                    <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4">
                        <div class="text-xs text-emerald-700 uppercase tracking-wide font-bold mb-1">Catatan Penyelesaian
                            (Global)</div>
                        <p class="text-sm text-emerald-900 whitespace-pre-line">{{ $workOrder->resolution_note }}</p>
                    </div>
                @endif

                {{-- ==================== MEDIA BUKTI (MULTI) ==================== --}}
                @if ($workOrder->media->count() || $canEditMedia)
                    <div class="space-y-5">

                        {{-- Tombol Tambah Media --}}
                        @if ($canEditMedia)
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <div>
                                    <div class="text-xs uppercase tracking-wide font-bold text-gray-600">
                                        Media Bukti
                                    </div>
                                    <div class="text-[11px] text-gray-400 mt-0.5">
                                        Anda dapat menambah, mengedit, atau menghapus media meski WO sudah selesai.
                                    </div>
                                </div>
                                <button type="button" onclick="openAddMediaModal()"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg
                                           bg-indigo-50 hover:bg-indigo-100 border border-indigo-200
                                           text-indigo-700 text-xs font-semibold transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Tambah Media
                                </button>
                            </div>
                        @endif

                        @foreach ([
            'before' => ['label' => '📷 Sebelum', 'items' => $mediaBefore, 'color' => 'text-amber-700'],
            'after' => ['label' => ' Sesudah', 'items' => $mediaAfter, 'color' => 'text-emerald-700'],
            'other' => ['label' => '📎 Lampiran Lain', 'items' => $mediaOther, 'color' => 'text-gray-600'],
        ] as $key => $group)
                            @if ($group['items']->count())
                                <div>
                                    <div class="text-xs uppercase tracking-wide font-bold mb-2 {{ $group['color'] }}">
                                        {{ $group['label'] }}
                                        <span
                                            class="text-gray-400 normal-case font-normal">({{ $group['items']->count() }})</span>
                                    </div>
                                    <div class="flex flex-wrap justify-center gap-3">
                                        @foreach ($group['items'] as $m)
                                            <div
                                                class="group relative rounded-xl overflow-hidden border border-gray-200 bg-gray-50
                    w-[calc(50%-0.375rem)] md:w-[calc(33.333%-0.5rem)]">
                                                @if ($m->is_image)
                                                    <img src="{{ $m->url }}" alt="{{ $m->caption ?? 'media' }}"
                                                        class="w-full h-40 object-cover cursor-pointer hover:opacity-95 transition"
                                                        onclick="showPhoto('{{ $m->url }}')">
                                                @else
                                                    <video src="{{ $m->url }}" controls preload="metadata"
                                                        class="w-full h-40 object-cover bg-black"></video>
                                                @endif

                                                @if ($m->caption)
                                                    <div
                                                        class="px-2 py-1.5 text-[11px] text-gray-600 truncate bg-white border-t border-gray-100">
                                                        {{ $m->caption }}
                                                    </div>
                                                @endif

                                                <span
                                                    class="absolute top-1.5 left-1.5 text-[9px] font-bold uppercase tracking-wider
                                                             px-1.5 py-0.5 rounded
                                                             {{ $m->is_video ? 'bg-black/70 text-white' : 'bg-white/90 text-gray-700' }}">
                                                    {{ $m->is_video ? 'VIDEO' : 'IMG' }}
                                                </span>

                                                {{-- Tombol Edit & Hapus --}}
                                                @if ($canEditMedia)
                                                    <div
                                                        class="absolute top-1.5 right-1.5 flex gap-1 opacity-0 group-hover:opacity-100 transition">
                                                        <button type="button"
                                                            onclick="openEditMediaModal({{ $m->id }}, @js($m->caption), @js($m->type))"
                                                            title="Edit media"
                                                            class="w-7 h-7 rounded-lg bg-white/95 hover:bg-white text-indigo-600
                                                                   shadow flex items-center justify-center transition">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5"
                                                                fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                                stroke-width="2.5">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                            </svg>
                                                        </button>
                                                        <button type="button"
                                                            onclick="confirmDeleteMedia({{ $m->id }})"
                                                            title="Hapus media"
                                                            class="w-7 h-7 rounded-lg bg-white/95 hover:bg-white text-rose-600
                                                                   shadow flex items-center justify-center transition">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5"
                                                                fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                                stroke-width="2.5">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        {{-- Jika belum ada media tapi boleh upload --}}
                        @if ($workOrder->media->count() === 0 && $canEditMedia)
                            <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center">
                                <div class="text-sm text-gray-500">
                                    Belum ada media. Klik <b>Tambah Media</b> di atas untuk mengunggah.
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- FOTO LAMA (backward compat) --}}
                @if ($workOrder->photo && !$workOrder->media->count())
                    <div>
                        <div class="text-xs text-gray-500 uppercase tracking-wide font-semibold mb-2">Foto Bukti</div>
                        <img src="{{ $workOrder->photo_url }}" alt="Foto Bukti"
                            class="max-h-80 rounded-xl border border-gray-200 shadow-sm cursor-pointer hover:opacity-95 transition"
                            onclick="showPhoto('{{ $workOrder->photo_url }}')">
                    </div>
                @endif

            </div>

            {{-- ACTION BAR --}}
            @if ($canStart || $canFinish)
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex justify-end gap-2">
                    @if ($canStart)
                        <button onclick="openStartModal()"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white
                                   bg-gradient-to-br from-indigo-500 to-violet-600
                                   hover:from-indigo-600 hover:to-violet-700
                                   shadow-lg shadow-indigo-500/30 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Mulai Pekerjaan
                        </button>
                    @endif

                    @if ($canFinish)
                        <button onclick="openFinishModal()"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white
                                   bg-gradient-to-br from-emerald-500 to-emerald-600
                                   hover:from-emerald-600 hover:to-emerald-700
                                   shadow-lg shadow-emerald-500/30 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Selesaikan Tugas
                        </button>
                    @endif
                </div>
            @endif
        </div>

        {{-- TIMELINE LOG --}}
        @if ($workOrder->logs->count())
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-800">Riwayat Aktivitas</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Log perubahan status work order</p>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @foreach ($workOrder->logs as $log)
                            <div class="flex items-start gap-3 text-sm">
                                <div
                                    class="w-8 h-8 rounded-full bg-indigo-50 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-600"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-baseline gap-2 flex-wrap">
                                        <span class="font-semibold text-gray-800 capitalize">{{ $log->action }}</span>
                                        @if ($log->from_status && $log->to_status)
                                            <span class="text-xs text-gray-400">
                                                {{ $log->from_status }} → {{ $log->to_status }}
                                            </span>
                                        @endif
                                        <span class="text-xs text-gray-400">
                                            {{ $log->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                    @if ($log->note)
                                        <p class="text-gray-600 text-xs mt-0.5">{{ $log->note }}</p>
                                    @endif
                                    <div class="text-xs text-gray-400 mt-0.5">
                                        oleh {{ $log->user->name ?? 'System' }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

    </div>

    {{-- ==================== MODAL START ==================== --}}
    <div id="modalStart" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4"
        style="background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(4px);">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 animate-modal-in">
            <div class="flex items-start gap-4 mb-4">
                <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Mulai Pekerjaan?</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Status Anda akan berubah menjadi <b class="text-amber-600">Proses</b>.
                        @if ($isCollective && $totalTechs > 1)
                            <br><span class="text-xs text-blue-600">Mode Global: teknisi lain juga akan melihat status
                                Anda.</span>
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex gap-2 justify-end">
                <button onclick="hideModal('modalStart')"
                    class="px-4 py-2 rounded-xl text-sm font-semibold text-gray-600 border border-gray-200 hover:bg-gray-50 transition">
                    Batal
                </button>
                <button id="btnStart" onclick="confirmStart()"
                    class="px-4 py-2 rounded-xl text-sm font-semibold text-white
                           bg-gradient-to-br from-indigo-500 to-violet-600
                           hover:from-indigo-600 hover:to-violet-700
                           shadow-lg shadow-indigo-500/30 transition
                           disabled:opacity-50 inline-flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Ya, Mulai
                </button>
            </div>
        </div>
    </div>

    {{-- ==================== MODAL FINISH ==================== --}}
    <div id="modalFinish" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
        style="background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(4px);">
        <div class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full my-8 overflow-hidden animate-modal-in">

            <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 px-6 py-5 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-white">Selesaikan Tugas</h3>
                    <p class="text-xs text-emerald-100">
                        @if ($isCollective)
                            Mode Global — 1 selesai semua selesai
                        @else
                            Mode Individual — hanya Anda yang selesai
                        @endif
                    </p>
                </div>
                <button onclick="hideModal('modalFinish')" class="text-white/80 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="finishForm" class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                        Catatan Penyelesaian
                    </label>
                    <textarea name="resolution_note" rows="3" maxlength="2000"
                        placeholder="Contoh: Kamera sudah diganti, diuji normal kembali."
                        class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-800
                               focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition resize-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-amber-700 uppercase tracking-wide mb-2">
                        📷 Foto / Video Sebelum
                    </label>

                    <div id="dropBefore"
                        class="border-2 border-dashed border-amber-300 rounded-xl p-3 transition
               hover:border-amber-500 hover:bg-amber-50/40">
                        <div id="previewBefore" class="grid grid-cols-3 sm:grid-cols-4 gap-2 mb-2"></div>

                        <div onclick="document.getElementById('inputBefore').click()"
                            class="cursor-pointer text-center py-3">
                            <input type="file" id="inputBefore" accept="image/*,video/*" multiple class="hidden">
                            <div class="text-sm text-gray-600 font-medium">
                                ➕ Klik / drag file ke sini
                            </div>
                            <div class="text-xs text-gray-400 mt-0.5">
                                Kondisi awal sebelum dikerjakan
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-emerald-700 uppercase tracking-wide mb-2">
                        Foto / Video Sesudah
                    </label>

                    <div id="dropAfter"
                        class="border-2 border-dashed border-emerald-300 rounded-xl p-3 transition
               hover:border-emerald-500 hover:bg-emerald-50/40">
                        <div id="previewAfter" class="grid grid-cols-3 sm:grid-cols-4 gap-2 mb-2"></div>

                        <div onclick="document.getElementById('inputAfter').click()"
                            class="cursor-pointer text-center py-3">
                            <input type="file" id="inputAfter" accept="image/*,video/*" multiple class="hidden">
                            <div class="text-sm text-gray-600 font-medium">
                                ➕ Klik / drag file ke sini
                            </div>
                            <div class="text-xs text-gray-400 mt-0.5">
                                Hasil setelah pekerjaan selesai
                            </div>
                        </div>
                    </div>
                </div>

                <div id="finishError"
                    class="hidden text-sm text-red-600 bg-red-50 border border-red-200 rounded-xl px-3 py-2"></div>

                <div class="flex gap-2 justify-end pt-2 border-t border-gray-100">
                    <button type="button" onclick="hideModal('modalFinish')"
                        class="px-4 py-2 rounded-xl text-sm font-semibold text-gray-600 border border-gray-200 hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button type="submit" id="btnFinish"
                        class="px-4 py-2 rounded-xl text-sm font-semibold text-white
                               bg-gradient-to-br from-emerald-500 to-emerald-600
                               hover:from-emerald-600 hover:to-emerald-700
                               shadow-lg shadow-emerald-500/30 transition
                               disabled:opacity-50 inline-flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Selesaikan
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- ==================== MODAL HAPUS PERMANEN ==================== --}}
    @if ($isAdmin)
        <div id="modalDelete" class="hidden fixed inset-0 z-[200] flex items-center justify-center p-4"
            style="background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(4px);">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden animate-modal-in">

                <div class="bg-gradient-to-br from-rose-500 to-red-600 px-6 py-5 text-white">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">Hapus Work Order?</h3>
                            <p class="text-xs text-rose-100">Tindakan ini tidak bisa dibatalkan</p>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-5 space-y-4">

                    <div class="bg-rose-50 border border-rose-200 rounded-xl p-3.5 space-y-1">
                        <div class="text-[10px] text-rose-500 uppercase tracking-wider font-bold">Work Order</div>
                        <div class="font-mono text-sm text-gray-700">{{ $workOrder->code }}</div>
                        <div class="text-sm text-gray-800 font-semibold leading-tight">{{ $workOrder->title }}</div>
                    </div>

                    <div class="text-sm text-gray-600 space-y-1.5">
                        <p class="font-semibold text-gray-800 text-xs uppercase tracking-wide">
                            Yang akan dihapus permanen:
                        </p>
                        <ul class="space-y-1 ml-1">
                            <li class="flex items-start gap-2 text-xs">
                                <span class="text-rose-500 mt-0.5">•</span>
                                <span>Data work order + semua log aktivitas</span>
                            </li>
                            <li class="flex items-start gap-2 text-xs">
                                <span class="text-rose-500 mt-0.5">•</span>
                                <span>Assign {{ $workOrder->technicians->count() }} teknisi + status partisipasi</span>
                            </li>
                            <li class="flex items-start gap-2 text-xs">
                                <span class="text-rose-500 mt-0.5">•</span>
                                <span>
                                    <b>{{ $workOrder->media->count() }}</b> file media
                                    @if ($workOrder->photo)
                                        + 1 foto lama
                                    @endif
                                    (foto/video bukti)
                                </span>
                            </li>
                        </ul>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-gray-600 uppercase tracking-wider mb-2">
                            Ketik <span class="text-rose-600 font-mono">HAPUS</span> untuk konfirmasi:
                        </label>
                        <input type="text" id="deleteConfirmInput" placeholder="Ketik HAPUS" autocomplete="off"
                            class="w-full border-2 border-gray-200 rounded-lg px-3 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500
                                   font-mono uppercase tracking-widest text-center">
                        <div id="deleteError" class="hidden text-xs text-rose-600 mt-2 font-semibold"></div>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex gap-2">
                    <button type="button" onclick="hideModal('modalDelete')"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-white border border-gray-200
                               hover:bg-gray-100 text-gray-700 text-sm font-semibold transition">
                        Batal
                    </button>
                    <button type="button" id="deleteConfirmBtn" onclick="confirmDelete()" disabled
                        class="flex-1 px-4 py-2.5 rounded-xl
                               bg-rose-600 hover:bg-rose-700
                               disabled:bg-gray-300 disabled:cursor-not-allowed
                               text-white text-sm font-semibold transition
                               flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        <span id="deleteBtnText">Hapus Permanen</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ==================== MODAL PHOTO ==================== --}}
    <div id="modalPhoto" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4"
        style="background: rgba(15, 23, 42, 0.85);" onclick="hideModal('modalPhoto')">
        <img id="photoLarge" src="" alt="Foto" class="max-w-3xl max-h-[85vh] rounded-2xl shadow-2xl">
    </div>

    {{-- ==================== MODAL EDIT MEDIA ==================== --}}
    @if ($canEditMedia)
        <div id="modalEditMedia" class="hidden fixed inset-0 z-[150] flex items-center justify-center p-4"
            style="background: rgba(15,23,42,0.6); backdrop-filter: blur(4px);">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 animate-modal-in">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Edit Media</h3>
                    <button type="button" onclick="hideModal('modalEditMedia')"
                        class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="editMediaForm" class="space-y-4">
                    <input type="hidden" id="editMediaId">

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            Kategori
                        </label>
                        <select id="editMediaType"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="before">📷 Sebelum</option>
                            <option value="after"> Sesudah</option>
                            <option value="other">📎 Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            Caption
                        </label>
                        <input type="text" id="editMediaCaption" maxlength="255"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            Ganti File (opsional)
                        </label>
                        <input type="file" id="editMediaFile" accept="image/*,video/*"
                            class="w-full text-sm border border-gray-200 rounded-xl px-3.5 py-2.5
                                   file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0
                                   file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700
                                   hover:file:bg-indigo-100">
                        <p class="text-[11px] text-gray-400 mt-1">Kosongkan jika hanya ingin ubah caption/kategori.</p>
                    </div>

                    <div id="editMediaError"
                        class="hidden text-sm text-red-600 bg-red-50 border border-red-200 rounded-xl px-3 py-2"></div>

                    <div class="flex gap-2 justify-end pt-2 border-t border-gray-100">
                        <button type="button" onclick="hideModal('modalEditMedia')"
                            class="px-4 py-2 rounded-xl text-sm font-semibold text-gray-600 border border-gray-200 hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" id="btnEditMedia"
                            class="px-4 py-2 rounded-xl text-sm font-semibold text-white
                                   bg-gradient-to-br from-indigo-500 to-violet-600
                                   hover:from-indigo-600 hover:to-violet-700 shadow-lg shadow-indigo-500/30">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ==================== MODAL ADD MEDIA ==================== --}}
        <div id="modalAddMedia" class="hidden fixed inset-0 z-[150] flex items-center justify-center p-4"
            style="background: rgba(15,23,42,0.6); backdrop-filter: blur(4px);">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 animate-modal-in">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-800">Tambah Media</h3>
                    <button type="button" onclick="hideModal('modalAddMedia')"
                        class="text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="addMediaForm" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            Kategori
                        </label>
                        <select id="addMediaType"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="before">📷 Sebelum</option>
                            <option value="after" selected> Sesudah</option>
                            <option value="other">📎 Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            Pilih File
                        </label>
                        <input type="file" id="addMediaFile" accept="image/*,video/*" multiple required
                            class="w-full text-sm border border-gray-200 rounded-xl px-3.5 py-2.5
                                   file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0
                                   file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700
                                   hover:file:bg-indigo-100">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                            Caption (opsional)
                        </label>
                        <input type="text" id="addMediaCaption" maxlength="255"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div id="addMediaError"
                        class="hidden text-sm text-red-600 bg-red-50 border border-red-200 rounded-xl px-3 py-2"></div>

                    <div class="flex gap-2 justify-end pt-2 border-t border-gray-100">
                        <button type="button" onclick="hideModal('modalAddMedia')"
                            class="px-4 py-2 rounded-xl text-sm font-semibold text-gray-600 border border-gray-200 hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" id="btnAddMedia"
                            class="px-4 py-2 rounded-xl text-sm font-semibold text-white
                                   bg-gradient-to-br from-emerald-500 to-emerald-600
                                   hover:from-emerald-600 hover:to-emerald-700 shadow-lg shadow-emerald-500/30">
                            Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

@endsection

@push('styles')
    <style>
        @keyframes modal-in {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(8px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .animate-modal-in {
            animation: modal-in 0.2s ease-out;
        }
    </style>
@endpush

@push('scripts')
    <script>
        const WO_ID = {{ $workOrder->id }};
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content;

        function showModal(id) {
            document.getElementById(id).classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function hideModal(id) {
            document.getElementById(id).classList.add('hidden');
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                hideModal('modalStart');
                hideModal('modalFinish');
                hideModal('modalPhoto');
                hideModal('modalDelete');
                hideModal('modalEditMedia');
                hideModal('modalAddMedia');
            }
        });

        function showPhoto(url) {
            document.getElementById('photoLarge').src = url;
            showModal('modalPhoto');
        }

        function openStartModal() {
            showModal('modalStart');
        }

        async function confirmStart() {
            const btn = document.getElementById('btnStart');
            btn.disabled = true;
            btn.innerHTML =
                '<svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Memproses...';

            try {
                const res = await fetch(`/work-orders/${WO_ID}/start`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                    },
                });

                if (!res.ok) {
                    const err = await res.json().catch(() => ({}));
                    throw new Error(err.message || 'HTTP ' + res.status);
                }

                showToast('Tugas dimulai!', 'success');
                setTimeout(() => window.location.reload(), 600);
            } catch (err) {
                showToast('Gagal: ' + err.message, 'error');
                btn.disabled = false;
                btn.innerHTML = 'Ya, Mulai';
            }
        }

        function openFinishModal() {
            showModal('modalFinish');
        }

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
        const finishForm = document.getElementById('finishForm');
        finishForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btnFinish');
            const errEl = document.getElementById('finishError');
            errEl.classList.add('hidden');
            btn.disabled = true;
            btn.innerHTML =
                '<svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Menyimpan...';

            const fd = new FormData();
            fd.append('resolution_note', finishForm.querySelector('[name="resolution_note"]').value || '');

            const allMedia = [...mediaBefore, ...mediaAfter];
            allMedia.forEach((m, i) => {
                fd.append(`media[${i}]`, m.file);
                fd.append(`media_types[${i}]`, m.type);
                fd.append(`media_captions[${i}]`, '');
            });

            try {
                const res = await fetch(`/work-orders/${WO_ID}/finish`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                    },
                    body: fd,
                });

                if (!res.ok) {
                    const err = await res.json().catch(() => ({}));
                    throw new Error(err.message || 'HTTP ' + res.status);
                }

                showToast('Tugas selesai! 🎉', 'success');
                setTimeout(() => window.location.reload(), 700);
            } catch (err) {
                errEl.textContent = err.message;
                errEl.classList.remove('hidden');
                btn.disabled = false;
                btn.innerHTML = 'Selesaikan';
            }
        });

        function openDeleteModal() {
            const modal = document.getElementById('modalDelete');
            if (!modal) return;

            document.getElementById('deleteConfirmInput').value = '';
            document.getElementById('deleteError').classList.add('hidden');
            document.getElementById('deleteConfirmBtn').disabled = true;
            document.getElementById('deleteBtnText').textContent = 'Hapus Permanen';

            showModal('modalDelete');
            setTimeout(() => document.getElementById('deleteConfirmInput').focus(), 100);
        }
        const deleteInputEl = document.getElementById('deleteConfirmInput');
        if (deleteInputEl) {
            deleteInputEl.addEventListener('input', function() {
                const val = this.value.trim().toUpperCase();
                const btn = document.getElementById('deleteConfirmBtn');
                const err = document.getElementById('deleteError');
                if (btn) btn.disabled = val !== 'HAPUS';
                if (err) err.classList.add('hidden');
            });

            deleteInputEl.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !document.getElementById('deleteConfirmBtn').disabled) {
                    confirmDelete();
                }
            });
        }

        async function confirmDelete() {
            const input = document.getElementById('deleteConfirmInput');
            const btn = document.getElementById('deleteConfirmBtn');
            const btnText = document.getElementById('deleteBtnText');
            const errEl = document.getElementById('deleteError');

            const val = (input?.value || '').trim().toUpperCase();
            if (val !== 'HAPUS') {
                if (errEl) {
                    errEl.textContent = 'Ketik "HAPUS" untuk konfirmasi';
                    errEl.classList.remove('hidden');
                }
                return;
            }
            btn.disabled = true;
            btnText.textContent = 'Menghapus...';

            try {
                const res = await fetch(`/admin/work-orders/${WO_ID}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                    },
                });

                const data = await res.json().catch(() => ({}));

                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Gagal menghapus work order.');
                }

                showToast(data.message || 'Work order berhasil dihapus.', 'success');

                setTimeout(() => {
                    window.location.href = "{{ route('admin.wo.index') }}";
                }, 800);

            } catch (err) {
                if (errEl) {
                    errEl.textContent = err.message || 'Terjadi kesalahan.';
                    errEl.classList.remove('hidden');
                }
                btn.disabled = false;
                btnText.textContent = 'Hapus Permanen';
            }
        }

        // =====================================================
        // ============ EDIT MEDIA (caption/type/file) =========
        // =====================================================
        function openEditMediaModal(id, caption, type) {
            const modal = document.getElementById('modalEditMedia');
            if (!modal) return;

            document.getElementById('editMediaId').value = id;
            document.getElementById('editMediaCaption').value = caption || '';
            document.getElementById('editMediaType').value = type || 'other';
            document.getElementById('editMediaFile').value = '';
            document.getElementById('editMediaError').classList.add('hidden');

            showModal('modalEditMedia');
        }

        const editMediaForm = document.getElementById('editMediaForm');
        if (editMediaForm) {
            editMediaForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const id = document.getElementById('editMediaId').value;
                const btn = document.getElementById('btnEditMedia');
                const errEl = document.getElementById('editMediaError');
                errEl.classList.add('hidden');
                btn.disabled = true;
                btn.textContent = 'Menyimpan...';

                const fd = new FormData();
                fd.append('_method', 'PATCH');
                fd.append('caption', document.getElementById('editMediaCaption').value);
                fd.append('type', document.getElementById('editMediaType').value);
                const file = document.getElementById('editMediaFile').files[0];
                if (file) fd.append('file', file);

                try {
                    const res = await fetch(`/work-orders/${WO_ID}/media/${id}`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                        },
                        body: fd,
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Gagal memperbarui media.');
                    showToast('Media diperbarui!', 'success');
                    setTimeout(() => window.location.reload(), 500);
                } catch (err) {
                    errEl.textContent = err.message;
                    errEl.classList.remove('hidden');
                    btn.disabled = false;
                    btn.textContent = 'Simpan Perubahan';
                }
            });
        }

        // =====================================================
        // ============ HAPUS MEDIA ============================
        // =====================================================
        async function confirmDeleteMedia(id) {
            if (!confirm('Yakin hapus media ini? File akan dihapus permanen.')) return;
            try {
                const res = await fetch(`/work-orders/${WO_ID}/media/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                    },
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Gagal menghapus media.');
                showToast('Media dihapus.', 'success');
                setTimeout(() => window.location.reload(), 500);
            } catch (err) {
                showToast('Gagal: ' + err.message, 'error');
            }
        }

        // =====================================================
        // ============ TAMBAH MEDIA ===========================
        // =====================================================
        function openAddMediaModal() {
            const modal = document.getElementById('modalAddMedia');
            if (!modal) return;
            document.getElementById('addMediaForm').reset();
            document.getElementById('addMediaError').classList.add('hidden');
            showModal('modalAddMedia');
        }

        const addMediaForm = document.getElementById('addMediaForm');
        if (addMediaForm) {
            addMediaForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const btn = document.getElementById('btnAddMedia');
                const errEl = document.getElementById('addMediaError');
                errEl.classList.add('hidden');

                const files = document.getElementById('addMediaFile').files;
                if (!files.length) {
                    errEl.textContent = 'Pilih minimal 1 file.';
                    errEl.classList.remove('hidden');
                    return;
                }

                btn.disabled = true;
                btn.textContent = 'Mengupload...';

                const fd = new FormData();
                const type = document.getElementById('addMediaType').value;
                const caption = document.getElementById('addMediaCaption').value;

                Array.from(files).forEach((f, i) => {
                    fd.append(`media[${i}]`, f);
                    fd.append(`media_types[${i}]`, type);
                    fd.append(`media_captions[${i}]`, caption);
                });

                try {
                    const res = await fetch(`/work-orders/${WO_ID}/media`, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                        },
                        body: fd,
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Gagal upload media.');
                    showToast('Media ditambahkan!', 'success');
                    setTimeout(() => window.location.reload(), 500);
                } catch (err) {
                    errEl.textContent = err.message;
                    errEl.classList.remove('hidden');
                    btn.disabled = false;
                    btn.textContent = 'Upload';
                }
            });
        }
    </script>
@endpush
