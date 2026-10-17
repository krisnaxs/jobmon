<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderMedia;
use Carbon\Carbon;
use App\Imports\WorkOrderImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TemplateWorkOrderExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WorkOrderController extends Controller
{
    public function adminIndex()
    {
        $techniciansByDivision = [
            'cctv' => User::where('role', 'technician')
                ->where('division', 'cctv')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'username']),
            'helpdesk' => User::where('role', 'technician')
                ->where('division', 'helpdesk')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'username']),
        ];

        return view('work-order.index', compact('techniciansByDivision'));
    }

    public function create()
    {
        $techniciansByDivision = [
            'cctv' => User::where('role', 'technician')
                ->where('division', 'cctv')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'username']),
            'helpdesk' => User::where('role', 'technician')
                ->where('division', 'helpdesk')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'username']),
        ];

        $now = now();
        $teamsByDivision = [];

        foreach (['cctv', 'helpdesk'] as $div) {
            $teams = Team::where('division', $div)
                ->where('is_active', true)
                ->orderBy('order')
                ->get()
                ->map(function ($team) use ($now) {
                    $memberIds = TeamMember::where('team_id', $team->id)
                        ->where('period_month', $now->month)
                        ->where('period_year', $now->year)
                        ->pluck('user_id')
                        ->toArray();

                    return [
                        'id' => $team->id,
                        'name' => $team->name,
                        'member_ids' => $memberIds,
                    ];
                })
                ->filter(fn($t) => count($t['member_ids']) > 0)
                ->values()
                ->toArray();

            $teamsByDivision[$div] = $teams;
        }

        return view('work-order.create', compact('techniciansByDivision', 'teamsByDivision'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $import = new WorkOrderImport;

        Excel::import($import, $request->file('file'));

        $errors = $import->getErrors();
        $success = $import->getSuccessCount();

        if (count($errors) > 0) {
            return back()
                ->with('warning', "{$success} WO berhasil diimpor. " . count($errors) . ' baris gagal.')
                ->with('import_errors', $errors);
        }

        return back()->with('success', "{$success} WO berhasil diimpor.");
    }

    public function downloadTemplate()
    {
        return Excel::download(
            new TemplateWorkOrderExport,
            'template-import-wo-' . now()->format('Ymd') . '.xlsx'
        );
    }

    public function importForm()
    {
        return view('work-order.import');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:50',
            'division' => 'required|in:cctv,helpdesk',
            'priority' => 'required|in:low,normal,high,urgent',
            'completion_mode' => 'required|in:collective,individual',
            'technician_ids' => 'nullable|array',
            'technician_ids.*' => 'exists:users,id',
            'building' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'deadline_at' => 'nullable|date',
        ]);

        $techIds = $data['technician_ids'] ?? [];

        if (!empty($techIds)) {
            $techs = User::whereIn('id', $techIds)->get();
            foreach ($techs as $tech) {
                if (!$tech->isTechnician() || $tech->division !== $data['division']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Teknisi {$tech->name} tidak sesuai dengan divisi pekerjaan.",
                    ], 422);
                }
            }
        }

        $data['created_by'] = auth()->id();
        $data['source'] = 'admin';
        $data['status'] = 'pending';

        unset($data['technician_ids']);

        $wo = WorkOrder::create($data);

        if (!empty($techIds)) {
            $wo->assignMany($techIds, auth()->id());
        }

        $modeLabel = $wo->completion_mode === 'collective'
            ? 'global (1 selesai = semua selesai)'
            : 'individual';

        $wo->log(
            'created',
            null,
            'pending',
            "Work order dibuat oleh admin (mode: {$modeLabel})"
        );

        return response()->json([
            'success' => true,
            'data' => $wo->load('technicians:id,name,division'),
        ]);
    }

    public function adminData(Request $request)
    {
        $range = $request->input('range', 'all');
        [$start, $end] = $this->resolveRange($range, $request);

        $query = WorkOrder::with([
            'creator:id,name',
            'technicians' => function ($q) {
                $q->select('users.id', 'users.name', 'users.username', 'users.division')
                    ->withPivot([
                        'team_id',
                        'team_name_snapshot',
                        'status',
                        'started_at',
                        'completed_at',
                        'resolution_note',
                        'no_participation',
                        'skipped_at',
                    ]);
            },
        ])
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')")
            ->orderByDesc('created_at');

        if ($request->filled('division') && in_array($request->division, ['cctv', 'helpdesk'])) {
            $query->where('division', $request->division);
        }

        if ($request->filled('status') && in_array($request->status, ['pending', 'in_progress', 'completed', 'cancelled'])) {
            $query->where('status', $request->status);
        }

        $this->applyRange($query, $start, $end);

        $wo = $query->limit(300)->get();

        $wo->each(function ($w) {
            $w->technicians->each(function ($t) {
                $t->team_name = $t->pivot->team_name_snapshot;
                $t->pivot_status = $t->pivot->status;
                $t->pivot_started_at = $t->pivot->started_at;
                $t->pivot_completed_at = $t->pivot->completed_at;
                $t->pivot_resolution_note = $t->pivot->resolution_note;
                $t->pivot_no_participation = (bool) $t->pivot->no_participation;
                $t->pivot_skipped_at = $t->pivot->skipped_at;
                unset($t->pivot);
            });
            $participating = $w->technicians->where('pivot_no_participation', false);
            $skipped = $w->technicians->where('pivot_no_participation', true);

            $w->team_progress = [
                'completed' => $participating->where('pivot_status', 'completed')->count(),
                'total' => $participating->count(),
                'skipped' => $skipped->count(),
            ];
        });

        return response()->json($wo);
    }

    public function technicianIndex()
    {
        return view('work-order.index');
    }

    public function createMandiri()
    {
        return view('work-order.create-mandiri');
    }

    public function storeMandiri(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:50',
            'priority' => 'required|in:low,normal,high,urgent',
            'building' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'started_at' => 'required|date|before_or_equal:now',
            'is_completed' => 'boolean',
            'completed_at' => 'nullable|required_if:is_completed,true|date|before_or_equal:now|after_or_equal:started_at',
            'resolution_note' => 'nullable|string|max:2000',

            'media' => 'nullable|array|max:20',
            'media.*' => 'file|mimes:jpg,jpeg,png,webp,mp4,mov,avi,webm|max:20480',
            'media_types' => 'nullable|array',
            'media_types.*' => 'in:before,after,other',
            'media_captions' => 'nullable|array',
            'media_captions.*' => 'nullable|string|max:255',
        ]);

        $isCompleted = $request->boolean('is_completed');

        $data['created_by'] = $user->id;
        $data['division'] = $user->division;
        $data['source'] = 'technician';
        $data['completion_mode'] = 'individual';

        if ($isCompleted) {
            $data['status'] = 'completed';
            $data['completed_at'] = $data['completed_at'];
            $data['resolution_note'] = $data['resolution_note'] ?? null;
        } else {
            $data['status'] = 'in_progress';
        }

        unset($data['is_completed'], $data['media'], $data['media_types'], $data['media_captions']);

        $wo = WorkOrder::create($data);

        $wo->assignTo($user->id, $user->id);

        $wo->technicians()->updateExistingPivot($user->id, [
            'status' => $isCompleted ? 'completed' : 'in_progress',
            'started_at' => $data['started_at'],
            'completed_at' => $isCompleted ? $data['completed_at'] : null,
            'resolution_note' => $data['resolution_note'] ?? null,
        ]);

        $this->saveMedia($request, $wo, $user->id);

        $logNote = $isCompleted
            ? 'Work order dibuat & langsung diselesaikan oleh teknisi'
            : 'Work order dibuat sendiri oleh teknisi';

        $wo->log('created', null, $data['status'], $logNote, $user->id);

        return response()->json([
            'success' => true,
            'data' => $wo->fresh()->load('media', 'technicians:id,name'),
        ]);
    }

    public function technicianData(Request $request)
    {
        $user = auth()->user();
        $range = $request->input('range', 'all');
        [$start, $end] = $this->resolveRange($range, $request);

        $query = WorkOrder::with([
            'creator:id,name',
            'technicians' => function ($q) {
                $q->select('users.id', 'users.name', 'users.username', 'users.division')
                    ->withPivot([
                        'team_id',
                        'team_name_snapshot',
                        'status',
                        'started_at',
                        'completed_at',
                        'resolution_note',
                        'no_participation',
                        'skipped_at',
                    ]);
            },
        ])
            ->where('division', $user->division)
            ->where(function ($q) use ($user) {
                $q->whereHas('technicians', fn($q2) => $q2->where('user_id', $user->id))
                    ->orWhere(function ($q2) use ($user) {
                        $q2->where('created_by', $user->id)
                            ->where('source', 'technician');
                    })
                    ->orWhereDoesntHave('technicians');
            })
            ->whereDate('created_at', '<=', today())   //  TAMBAH INI
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')")
            ->orderByDesc('created_at');

        $this->applyRange($query, $start, $end);

        $wo = $query->limit(300)->get();

        $wo->each(function ($w) use ($user) {
            $w->technicians->each(function ($t) {
                $t->team_name = $t->pivot->team_name_snapshot;
                $t->pivot_status = $t->pivot->status;
                $t->pivot_started_at = $t->pivot->started_at;
                $t->pivot_completed_at = $t->pivot->completed_at;
                $t->pivot_resolution_note = $t->pivot->resolution_note;
                $t->pivot_no_participation = (bool) $t->pivot->no_participation;
                $t->pivot_skipped_at = $t->pivot->skipped_at;
                unset($t->pivot);
            });
            $my = $w->technicians->firstWhere('id', $user->id);
            $w->my_status = $my->pivot_status ?? $w->status;
            $w->my_no_participation = $my ? (bool) $my->pivot_no_participation : false;
            $participating = $w->technicians->where('pivot_no_participation', false);
            $skipped = $w->technicians->where('pivot_no_participation', true);

            $w->team_progress = [
                'completed' => $participating->where('pivot_status', 'completed')->count(),
                'total' => $participating->count(),
                'skipped' => $skipped->count(),
            ];
        });

        return response()->json($wo);
    }

    public function availableData()
    {
        $user = auth()->user();

        $wo = WorkOrder::with(['creator:id,name'])
            ->where('division', $user->division)
            ->whereDoesntHave('technicians')
            ->where('status', 'pending')
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')")
            ->latest()
            ->limit(100)
            ->get();

        return response()->json($wo);
    }

    public function claim(WorkOrder $workOrder)
    {
        $user = auth()->user();

        abort_unless($user->isTechnician(), 403, 'Hanya teknisi yang bisa mengambil tugas.');

        abort_unless(
            $user->division === $workOrder->division,
            403,
            'Work order ini bukan untuk divisi Anda.'
        );

        abort_unless(
            !$workOrder->isAssigned(),
            422,
            'Work order ini sudah di-assign ke teknisi lain.'
        );

        abort_unless(
            $workOrder->status === 'pending',
            422,
            'Hanya work order pending yang bisa diambil.'
        );

        $workOrder->assignTo($user->id, $user->id);

        return response()->json([
            'success' => true,
            'data' => $workOrder->fresh()->load('technicians:id,name'),
        ]);
    }

    public function start(WorkOrder $workOrder)
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            abort_if(
                $workOrder->status !== 'pending',
                422,
                'Hanya work order pending yang bisa dimulai.'
            );

            $workOrder->update([
                'status' => 'in_progress',
                'started_at' => $workOrder->started_at ?? now(),
            ]);

            $workOrder->log(
                'started',
                'pending',
                'in_progress',
                'Dimulai oleh admin',
                $user->id
            );

            return response()->json([
                'success' => true,
                'data' => $workOrder->fresh()->load('technicians:id,name'),
            ]);
        }
        abort_unless(
            $user->division === $workOrder->division,
            403,
            'Work order ini bukan untuk divisi Anda.'
        );
        if (!$workOrder->isAssigned()) {
            $workOrder->assignTo($user->id, $user->id);
            $workOrder->refresh();
        }

        abort_unless(
            $workOrder->isAssignedTo($user->id),
            403,
            'Work order ini sudah ditugaskan ke teknisi lain.'
        );

        $myStatus = $workOrder->statusForUser($user->id);

        abort_if(
            $myStatus === 'completed',
            422,
            'Anda sudah menyelesaikan tugas ini.'
        );

        abort_if(
            $myStatus === 'in_progress',
            422,
            'Anda sudah memulai tugas ini.'
        );

        $workOrder->startForUser($user->id);

        return response()->json([
            'success' => true,
            'data' => $workOrder->fresh()->load('technicians:id,name'),
        ]);
    }

    public function show(WorkOrder $workOrder)
    {
        $user = auth()->user();

        if (!$user->isAdmin()) {
            abort_unless(
                $user->division === $workOrder->division,
                403,
                'Work order ini bukan untuk divisi Anda.'
            );
        }

        $workOrder->load([
            'technicians' => function ($q) {
                $q->select('users.id', 'users.name', 'users.username', 'users.division')
                    ->withPivot([
                        'team_id',
                        'team_name_snapshot',
                        'status',
                        'started_at',
                        'completed_at',
                        'resolution_note',
                        'no_participation',
                        'skipped_at',
                        'skipped_by',
                    ]);
            },
            'creator:id,name,username',
            'logs.user:id,name',
            'media',
        ]);

        return view('work-order.show', compact('workOrder'));
    }

    public function finish(Request $request, WorkOrder $workOrder)
    {
        $user = auth()->user();
        if ($user->isAdmin()) {
            abort_if(
                $workOrder->status === 'completed',
                422,
                'Work order ini sudah selesai.'
            );

            abort_if(
                $workOrder->status === 'cancelled',
                422,
                'Work order ini sudah dibatalkan.'
            );
        } else {
            abort_unless(
                $workOrder->isAssignedTo($user->id),
                403,
                'Anda tidak berhak menyelesaikan tugas ini.'
            );

            abort_unless(
                $user->division === $workOrder->division,
                403,
                'Work order ini bukan untuk divisi Anda.'
            );

            $myStatus = $workOrder->statusForUser($user->id);

            abort_if(
                $myStatus === 'completed',
                422,
                'Anda sudah menyelesaikan tugas ini.'
            );

            abort_if(
                $myStatus !== 'in_progress',
                422,
                'Mulai dulu sebelum menyelesaikan.'
            );
        }
        $validated = $request->validate([
            'resolution_note' => 'nullable|string|max:2000',
            'media' => 'nullable|array|max:20',
            'media.*' => 'file|mimes:jpg,jpeg,png,webp,mp4,mov,avi,webm|max:20480',
            'media_types' => 'nullable|array',
            'media_types.*' => 'in:before,after,other',
            'media_captions' => 'nullable|array',
            'media_captions.*' => 'nullable|string|max:255',
        ]);

        $this->saveMedia($request, $workOrder, $user->id);
        if ($user->isAdmin()) {
            $workOrder->adminForceComplete(
                $user->id,
                $validated['resolution_note'] ?? null
            );
        } else {
            $workOrder->finishForUser(
                $user->id,
                $validated['resolution_note'] ?? null
            );
        }

        return response()->json([
            'success' => true,
            'data' => $workOrder->fresh()->load('media', 'technicians:id,name'),
        ]);
    }

    // ============================================================
    // ============ MEDIA EDIT / ADD / DELETE =====================
    // ============================================================

    /**
     * Cek apakah user boleh mengedit media WO ini.
     * - Admin: selalu boleh.
     * - Teknisi: harus ter-assign & WO minimal in_progress/completed.
     */
    private function canEditMedia(WorkOrder $workOrder, $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->division !== $workOrder->division) {
            return false;
        }

        if (!$workOrder->isAssignedTo($user->id)) {
            return false;
        }

        return in_array($workOrder->status, ['in_progress', 'completed']);
    }

    public function addMedia(Request $request, WorkOrder $workOrder)
    {
        $user = auth()->user();
        abort_unless($this->canEditMedia($workOrder, $user), 403, 'Tidak berhak menambah media.');

        $request->validate([
            'media' => 'required|array|min:1|max:20',
            'media.*' => 'required|file|mimes:jpg,jpeg,png,webp,mp4,mov,avi,webm|max:20480',
            'media_types' => 'nullable|array',
            'media_types.*' => 'nullable|in:before,after,other',
            'media_captions' => 'nullable|array',
            'media_captions.*' => 'nullable|string|max:255',
        ]);

        $this->saveMedia($request, $workOrder, $user->id);

        $workOrder->log(
            'media_added',
            $workOrder->status,
            $workOrder->status,
            'Menambahkan media baru ke work order',
            $user->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Media berhasil ditambahkan.',
            'media' => $workOrder->fresh()->load('media')->media,
        ]);
    }

    public function updateMedia(Request $request, WorkOrder $workOrder, WorkOrderMedia $media)
    {
        $user = auth()->user();
        abort_unless($this->canEditMedia($workOrder, $user), 403, 'Tidak berhak mengubah media.');
        abort_unless($media->work_order_id === $workOrder->id, 404);

        $data = $request->validate([
            'caption' => 'nullable|string|max:255',
            'type' => 'nullable|in:before,after,other',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,webp,mp4,mov,avi,webm|max:20480',
        ]);

        // Ganti file jika ada upload baru
        if ($request->hasFile('file')) {
            $oldPath = $media->path;
            $newPath = $request->file('file')->store('work-orders/' . $workOrder->id, 'public');

            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            $media->path = $newPath;
            $media->original_name = $request->file('file')->getClientOriginalName();
            $media->mime_type = $request->file('file')->getMimeType();
            $media->media_type = str_starts_with($media->mime_type, 'video') ? 'video' : 'image';
            $media->size = $request->file('file')->getSize();
        }

        if (array_key_exists('caption', $data)) {
            $media->caption = $data['caption'];
        }
        if (!empty($data['type'])) {
            $media->type = $data['type'];
        }
        $media->save();

        $workOrder->log(
            'media_updated',
            $workOrder->status,
            $workOrder->status,
            "Mengubah media #{$media->id}",
            $user->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Media berhasil diperbarui.',
            'media' => $media,
        ]);
    }

    public function deleteMedia(WorkOrder $workOrder, WorkOrderMedia $media)
    {
        $user = auth()->user();
        abort_unless($this->canEditMedia($workOrder, $user), 403, 'Tidak berhak menghapus media.');
        abort_unless($media->work_order_id === $workOrder->id, 404);

        if ($media->path && Storage::disk('public')->exists($media->path)) {
            Storage::disk('public')->delete($media->path);
        }

        $mediaId = $media->id;
        $media->delete();

        $workOrder->log(
            'media_deleted',
            $workOrder->status,
            $workOrder->status,
            "Menghapus media #{$mediaId}",
            $user->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Media berhasil dihapus.',
        ]);
    }

    // ============================================================
    // ============ HELPERS =======================================
    // ============================================================

    private function saveMedia(Request $request, WorkOrder $wo, int $uploaderId): void
    {
        if (!$request->hasFile('media')) {
            return;
        }

        $types = $request->input('media_types', []);
        $captions = $request->input('media_captions', []);

        foreach ($request->file('media') as $i => $file) {
            if (!$file->isValid()) {
                continue;
            }

            $mime = $file->getMimeType();
            $mediaType = str_starts_with($mime, 'video') ? 'video' : 'image';
            $type = $types[$i] ?? 'other';

            $path = $file->store('work-orders/' . $wo->id, 'public');

            $wo->media()->create([
                'uploaded_by' => $uploaderId,
                'type' => $type,
                'media_type' => $mediaType,
                'path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $mime,
                'size' => $file->getSize(),
                'caption' => $captions[$i] ?? null,
            ]);
        }
    }

    private function resolveRange(string $range, Request $request): array
    {
        $start = null;
        $end = null;

        switch ($range) {
            case 'today':
                $start = now()->startOfDay();
                $end = now()->endOfDay();
                break;
            case 'week':
                $start = now()->subDays(7)->startOfDay();
                $end = now()->endOfDay();
                break;
            case 'month':
                $start = now()->subDays(30)->startOfDay();
                $end = now()->endOfDay();
                break;
            case 'custom':
                $start = Carbon::parse($request->input('from'))->startOfDay();
                $end = Carbon::parse($request->input('to'))->endOfDay();
                break;
            case 'all':
            default:
                break;
        }

        return [$start, $end];
    }

    private function applyRange($query, $start, $end)
    {
        if (!$start || !$end) {
            return $query;
        }

        return $query->where(function ($q) use ($start, $end) {
            $q->where('status', 'pending')
                ->orWhere(function ($q2) use ($start, $end) {
                    $q2->where('status', 'in_progress')
                        ->whereBetween('started_at', [$start, $end]);
                })
                ->orWhere(function ($q2) use ($start, $end) {
                    $q2->where('status', 'completed')
                        ->whereBetween('completed_at', [$start, $end]);
                });
        });
    }

    public function destroy(WorkOrder $workOrder)
    {
        $user = auth()->user();
        abort_unless($user->isAdmin(), 403, 'Hanya admin yang bisa menghapus work order.');
        $code = $workOrder->code;
        $title = $workOrder->title;
        $deletedMediaCount = 0;

        foreach ($workOrder->media as $media) {
            if ($media->path && Storage::disk('public')->exists($media->path)) {
                Storage::disk('public')->delete($media->path);
                $deletedMediaCount++;
            }
        }

        $folder = 'work-orders/' . $workOrder->id;
        if (Storage::disk('public')->exists($folder)) {
            Storage::disk('public')->deleteDirectory($folder);
        }

        if ($workOrder->photo && Storage::disk('public')->exists($workOrder->photo)) {
            Storage::disk('public')->delete($workOrder->photo);
        }

        $workOrder->delete();
        $msg = "Work order {$code} ({$title}) berhasil dihapus permanen.";
        if ($deletedMediaCount > 0) {
            $msg .= " {$deletedMediaCount} file media ikut dihapus.";
        }

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'deleted_media' => $deletedMediaCount,
            ]);
        }

        return redirect()
            ->route('admin.wo.index')
            ->with('success', $msg);
    }
}
