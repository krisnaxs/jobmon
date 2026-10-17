<?php

namespace App\Http\Controllers;

use App\Exports\WorkOrdersExport;
use App\Exports\WorkOrdersPerTechnicianExport;
use App\Models\TeamMember;
use App\Models\User;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index()
    {
        $technicians = User::where('role', 'technician')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'division']);

        return view('report.index', compact('technicians'));
    }

    public function data(Request $request)
    {
        $filters = $this->parseFilters($request);
        $query = WorkOrder::with([
            'creator:id,name',
            'technicians' => function ($q) {
                $q->select('users.id', 'users.name', 'users.username', 'users.division')
                    ->withPivot([
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
            'media' => function ($q) {
                $q->select('id', 'work_order_id', 'uploaded_by', 'type', 'media_type', 'path', 'caption');
            },
        ]);

        $this->applyFilters($query, $filters);

        $workOrders = $query->orderByDesc('created_at')->limit(1000)->get();

        $summary = [
            'total' => $workOrders->count(),
            'completed' => $workOrders->where('status', 'completed')->count(),
            'in_progress' => $workOrders->where('status', 'in_progress')->count(),
            'pending' => $workOrders->where('status', 'pending')->count(),
            'cancelled' => $workOrders->where('status', 'cancelled')->count(),
        ];

        $byDivision = [];
        foreach (['cctv', 'helpdesk'] as $div) {
            $subset = $workOrders->where('division', $div);
            $byDivision[$div] = [
                'total' => $subset->count(),
                'completed' => $subset->where('status', 'completed')->count(),
                'in_progress' => $subset->where('status', 'in_progress')->count(),
                'pending' => $subset->where('status', 'pending')->count(),
            ];
        }

        $byTechnician = [];
        foreach ($workOrders as $wo) {
            foreach ($wo->technicians as $t) {
                $techId = $t->id;
                $pivotStatus = $t->pivot->status ?? 'pending';
                $isSkipped = (bool) ($t->pivot->no_participation ?? false);

                if (!isset($byTechnician[$techId])) {
                    $byTechnician[$techId] = [
                        'id' => $techId,
                        'name' => $t->name,
                        'username' => $t->username,
                        'division' => $t->division,
                        'total' => 0,
                        'completed' => 0,
                        'in_progress' => 0,
                        'pending' => 0,
                        'skipped' => 0,
                        'participating' => 0,
                    ];
                }

                if ($isSkipped) {
                    $byTechnician[$techId]['skipped']++;
                    continue;
                }

                $byTechnician[$techId]['total']++;
                $byTechnician[$techId]['participating']++;

                if ($pivotStatus === 'completed') {
                    $byTechnician[$techId]['completed']++;
                } elseif ($pivotStatus === 'in_progress') {
                    $byTechnician[$techId]['in_progress']++;
                } else {
                    $byTechnician[$techId]['pending']++;
                }
            }
        }

        foreach ($byTechnician as &$tech) {
            $tech['progress_pct'] = $tech['participating'] > 0
                ? round(($tech['completed'] / $tech['participating']) * 100, 2)
                : 0;
        }
        unset($tech);

        $byTeamSnapshot = [];
        foreach ($workOrders as $wo) {
            $teams = $wo->technicians
                ->filter(fn($t) => ($t->pivot->no_participation ?? false) === false)
                ->pluck('pivot.team_name_snapshot')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            if (empty($teams)) {
                if (!isset($byTeamSnapshot['Tanpa Tim'])) {
                    $byTeamSnapshot['Tanpa Tim'] = [
                        'total' => 0,
                        'completed' => 0,
                        'in_progress' => 0,
                        'pending' => 0,
                        'skipped' => 0,
                    ];
                }
                $byTeamSnapshot['Tanpa Tim']['total']++;

                if ($wo->status === 'completed') {
                    $byTeamSnapshot['Tanpa Tim']['completed']++;
                } elseif ($wo->status === 'in_progress') {
                    $byTeamSnapshot['Tanpa Tim']['in_progress']++;
                } else {
                    $byTeamSnapshot['Tanpa Tim']['pending']++;
                }
                continue;
            }

            foreach ($teams as $team) {
                if (!isset($byTeamSnapshot[$team])) {
                    $byTeamSnapshot[$team] = [
                        'total' => 0,
                        'completed' => 0,
                        'in_progress' => 0,
                        'pending' => 0,
                        'skipped' => 0,
                    ];
                }

                $techsInTeam = $wo->technicians->filter(
                    fn($t) => $t->pivot->team_name_snapshot === $team
                        && ($t->pivot->no_participation ?? false) === false
                );

                foreach ($techsInTeam as $t) {
                    $byTeamSnapshot[$team]['total']++;
                    $pivotStatus = $t->pivot->status ?? 'pending';

                    if ($pivotStatus === 'completed') {
                        $byTeamSnapshot[$team]['completed']++;
                    } elseif ($pivotStatus === 'in_progress') {
                        $byTeamSnapshot[$team]['in_progress']++;
                    } else {
                        $byTeamSnapshot[$team]['pending']++;
                    }
                }

                $skippedInTeam = $wo->technicians->filter(
                    fn($t) => $t->pivot->team_name_snapshot === $team
                        && ($t->pivot->no_participation ?? false) === true
                );
                $byTeamSnapshot[$team]['skipped'] += $skippedInTeam->count();
            }
        }

        $byTeamLive = [];
        $now = now();

        foreach ($workOrders as $wo) {
            $participatingTechs = $wo->technicians
                ->filter(fn($t) => ($t->pivot->no_participation ?? false) === false);

            $techIds = $participatingTechs->pluck('id')->toArray();

            if (empty($techIds)) {
                if (!isset($byTeamLive['Tanpa Tim'])) {
                    $byTeamLive['Tanpa Tim'] = [
                        'total' => 0,
                        'completed' => 0,
                        'in_progress' => 0,
                        'pending' => 0,
                        'skipped' => 0,
                    ];
                }
                $byTeamLive['Tanpa Tim']['total']++;

                if ($wo->status === 'completed') {
                    $byTeamLive['Tanpa Tim']['completed']++;
                } elseif ($wo->status === 'in_progress') {
                    $byTeamLive['Tanpa Tim']['in_progress']++;
                } else {
                    $byTeamLive['Tanpa Tim']['pending']++;
                }
                continue;
            }

            $memberships = TeamMember::whereIn('user_id', $techIds)
                ->where('period_month', $now->month)
                ->where('period_year', $now->year)
                ->with('team:id,name')
                ->get()
                ->keyBy('user_id');

            foreach ($participatingTechs as $t) {
                $membership = $memberships->get($t->id);
                $teamName = $membership?->team?->name;

                if (!$teamName) {
                    if (!isset($byTeamLive['Tanpa Tim'])) {
                        $byTeamLive['Tanpa Tim'] = [
                            'total' => 0,
                            'completed' => 0,
                            'in_progress' => 0,
                            'pending' => 0,
                            'skipped' => 0,
                        ];
                    }
                    $byTeamLive['Tanpa Tim']['total']++;

                    $pivotStatus = $t->pivot->status ?? 'pending';
                    if ($pivotStatus === 'completed') {
                        $byTeamLive['Tanpa Tim']['completed']++;
                    } elseif ($pivotStatus === 'in_progress') {
                        $byTeamLive['Tanpa Tim']['in_progress']++;
                    } else {
                        $byTeamLive['Tanpa Tim']['pending']++;
                    }
                    continue;
                }

                if (!isset($byTeamLive[$teamName])) {
                    $byTeamLive[$teamName] = [
                        'total' => 0,
                        'completed' => 0,
                        'in_progress' => 0,
                        'pending' => 0,
                        'skipped' => 0,
                    ];
                }

                $byTeamLive[$teamName]['total']++;
                $pivotStatus = $t->pivot->status ?? 'pending';

                if ($pivotStatus === 'completed') {
                    $byTeamLive[$teamName]['completed']++;
                } elseif ($pivotStatus === 'in_progress') {
                    $byTeamLive[$teamName]['in_progress']++;
                } else {
                    $byTeamLive[$teamName]['pending']++;
                }
            }
        }

        $detail = $workOrders->map(function ($wo) {
            $teams = $wo->technicians
                ->filter(fn($t) => ($t->pivot->no_participation ?? false) === false)
                ->pluck('pivot.team_name_snapshot')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            $participating = $wo->technicians
                ->filter(fn($t) => ($t->pivot->no_participation ?? false) === false);
            $skipped = $wo->technicians
                ->filter(fn($t) => ($t->pivot->no_participation ?? false) === true);

            $totalTechs = $participating->count();
            $completedTechs = $participating
                ->filter(fn($t) => ($t->pivot->status ?? null) === 'completed')
                ->count();
            $skippedTechs = $skipped->count();

            return [
                'id' => $wo->id,
                'code' => $wo->code,
                'title' => $wo->title,
                'description' => $wo->description,
                'division' => $wo->division,
                'category' => $wo->category,
                'priority' => $wo->priority,
                'status' => $wo->status,
                'completion_mode' => $wo->completion_mode,
                'source' => $wo->source,
                'building' => $wo->building,
                'location' => $wo->location,

                'technicians' => $wo->technicians->map(fn($t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'username' => $t->username,
                    'team_name' => $t->pivot->team_name_snapshot,
                    'pivot_status' => $t->pivot->status,
                    'pivot_started_at' => $t->pivot->started_at,
                    'pivot_completed_at' => $t->pivot->completed_at,
                    'pivot_resolution_note' => $t->pivot->resolution_note,
                    'pivot_no_participation' => (bool) $t->pivot->no_participation,
                    'pivot_skipped_at' => $t->pivot->skipped_at,
                    'pivot_skipped_by' => $t->pivot->skipped_by,
                ])->toArray(),

                'team_progress' => [
                    'completed' => $completedTechs,
                    'total' => $totalTechs,
                    'skipped' => $skippedTechs,
                ],

                'teams' => $teams,
                'creator' => $wo->creator?->name,
                'created_at' => $wo->created_at?->toIso8601String(),
                'started_at' => $wo->started_at?->toIso8601String(),
                'completed_at' => $wo->completed_at?->toIso8601String(),
                'deadline_at' => $wo->deadline_at?->toIso8601String(),
                'resolution_note' => $wo->resolution_note,
            ];
        });

        return response()->json([
            'summary' => $summary,
            'byDivision' => $byDivision,
            'byTechnician' => array_values($byTechnician),
            'byTeamSnapshot' => $byTeamSnapshot,
            'byTeamLive' => $byTeamLive,
            'detail' => $detail,
            'filters' => $filters,
        ]);
    }

    public function export(Request $request)
    {
        $filters = $this->parseFilters($request);
        $mode = $request->input('export_mode', 'wo');
        $includePhotos = $request->boolean('include_photos', false);
        if ($includePhotos) {
            set_time_limit(300);
            ini_set('memory_limit', '1512M');
        }

        $filename = 'report-wo-' . $mode . '-' . now()->format('Ymd-His') . '.xlsx';

        if ($mode === 'technician') {
            return Excel::download(new WorkOrdersPerTechnicianExport($filters), $filename);
        }

        return Excel::download(new WorkOrdersExport($filters), $filename);
    }

    public function technicianIndex()
    {
        $user = auth()->user();

        abort_unless($user->isTechnician(), 403, 'Hanya teknisi yang bisa akses halaman ini.');

        return view('report.technician');
    }

    public function technicianData(Request $request)
    {
        $user = auth()->user();
        abort_unless($user->isTechnician(), 403, 'Hanya teknisi yang bisa akses laporan ini.');
        $request->merge([
            'technician_id' => $user->id,
            'division' => $user->division ?: 'all',
        ]);
        $request->merge([
            'team' => 'all',
            'team_mode' => 'snapshot',
            'hide_future' => true,
        ]);

        $response = $this->data($request);
        $data = $response->getData(true);

        // ✅ TAMBAH: filter detail — cuma tampilkan teknisi yang login
        $data['detail'] = collect($data['detail'] ?? [])->map(function ($wo) use ($user) {
            $wo['technicians'] = collect($wo['technicians'] ?? [])
                ->where('id', $user->id)
                ->values()
                ->toArray();

            // Recalculate team_progress berdasarkan teknisi yang login saja
            $myTech = $wo['technicians'][0] ?? null;
            $wo['team_progress'] = [
                'completed' => ($myTech && $myTech['pivot_status'] === 'completed') ? 1 : 0,
                'total' => $myTech ? 1 : 0,
                'skipped' => ($myTech && $myTech['pivot_no_participation']) ? 1 : 0,
            ];

            return $wo;
        })->toArray();

        $data['byTechnician'] = collect($data['byTechnician'] ?? [])
            ->where('id', $user->id)
            ->values()
            ->toArray();

        return response()->json($data);
    }

    public function technicianExport(Request $request)
    {
        $user = auth()->user();
        abort_unless($user->isTechnician(), 403, 'Hanya teknisi yang bisa export laporan ini.');
        $request->merge([
            'technician_id' => $user->id,
            'division' => $user->division ?: 'all',
            'team' => 'all',
            'team_mode' => 'snapshot',
            'include_photos' => true,
            'hide_future' => true,   //  TAMBAH: sembunyikan WO masa depan
        ]);

        return $this->export($request);
    }

    private function parseFilters(Request $request): array
    {
        return [
            'range' => $request->input('range', 'all'),
            'from' => $request->input('from'),
            'to' => $request->input('to'),
            'division' => $request->input('division', 'all'),
            'team' => $request->input('team', 'all'),
            'team_mode' => $request->input('team_mode', 'snapshot'),
            'status' => $request->input('status', 'all'),
            'technician_id' => $request->input('technician_id', 'all'),
            'completion_mode' => $request->input('completion_mode', 'all'),
            'no_participation' => $request->input('no_participation', 'all'),
            'include_photos' => $request->boolean('include_photos', false),
            'hide_future' => $request->boolean('hide_future', false),   //  TAMBAH
        ];
    }

    private function applyFilters($query, array $filters): void
    {
        $range = $filters['range'];
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
                if (!empty($filters['from']) && !empty($filters['to'])) {
                    $start = Carbon::parse($filters['from'])->startOfDay();
                    $end = Carbon::parse($filters['to'])->endOfDay();
                }
                break;
        }

        if ($start && $end) {
            $query->where(function ($q) use ($start, $end) {
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

        if ($filters['division'] !== 'all') {
            $query->where('division', $filters['division']);
        }
        if ($filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['completion_mode']) && $filters['completion_mode'] !== 'all') {
            $query->where('completion_mode', $filters['completion_mode']);
        }
        if ($filters['technician_id'] !== 'all') {
            $techId = $filters['technician_id'];

            $query->where(function ($q) use ($techId) {
                $q->whereHas('technicians', fn($q2) => $q2->where('user_id', $techId))
                    ->orWhere(function ($q2) use ($techId) {
                        $q2->where('created_by', $techId)
                            ->where('source', 'technician');
                    });
            });
        }
        if ($filters['team'] !== 'all') {
            $teamValue = $filters['team'];
            $teamMode = $filters['team_mode'];

            if ($teamValue === 'none') {
                if ($teamMode === 'snapshot') {
                    $query->whereDoesntHave(
                        'technicians',
                        fn($q) =>
                            $q->whereNotNull('team_name_snapshot')
                    );
                } else {
                    $now = now();
                    $userIdsWithTeam = TeamMember::where('period_month', $now->month)
                        ->where('period_year', $now->year)
                        ->pluck('user_id')
                        ->toArray();

                    $query->where(function ($q) use ($userIdsWithTeam) {
                        $q->whereDoesntHave('technicians')
                            ->orWhereHas(
                                'technicians',
                                fn($q2) =>
                                    $q2->whereNotIn('user_id', $userIdsWithTeam)
                            );
                    });
                }
            } else {
                if ($teamMode === 'snapshot') {
                    $query->whereHas(
                        'technicians',
                        fn($q) =>
                            $q->where('team_name_snapshot', $teamValue)
                    );
                } else {
                    $now = now();
                    $userIds = TeamMember::whereHas(
                        'team',
                        fn($q) =>
                            $q->where('name', $teamValue)
                    )
                        ->where('period_month', $now->month)
                        ->where('period_year', $now->year)
                        ->pluck('user_id')
                        ->toArray();

                    $query->whereHas(
                        'technicians',
                        fn($q) =>
                            $q->whereIn('user_id', $userIds)
                    );
                }
            }
        }
        if (isset($filters['no_participation']) && $filters['no_participation'] !== 'all') {
            if ($filters['no_participation'] === 'yes') {
                $query->whereHas(
                    'technicians',
                    fn($q) =>
                        $q->where('no_participation', true)
                );
            } elseif ($filters['no_participation'] === 'no') {
                $query->whereDoesntHave(
                    'technicians',
                    fn($q) =>
                        $q->where('no_participation', true)
                );
            }
        }

        //  TAMBAH: sembunyikan WO masa depan (khusus teknisi)
        if (!empty($filters['hide_future'])) {
            $query->whereDate('created_at', '<=', today());
        }
    }
}
