<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkOrder;
use App\Models\TeamMember;
use App\Models\TeamSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $myTeamMembership = null;
        $myTeamMembers = collect();
        $shuffleEnabled = false;

        if (!$user->isAdmin() && $user->division) {
            $shuffleEnabled = \App\Models\TeamSetting::isShuffleEnabled($user->division);

            if ($shuffleEnabled) {
                $now = now();
                $myTeamMembership = \App\Models\TeamMember::where('user_id', $user->id)
                    ->where('period_month', $now->month)
                    ->where('period_year', $now->year)
                    ->with('team')
                    ->first();

                if ($myTeamMembership && $myTeamMembership->team) {
                    $myTeamMembers = \App\Models\TeamMember::where('team_id', $myTeamMembership->team_id)
                        ->where('period_month', $now->month)
                        ->where('period_year', $now->year)
                        ->with(['user:id,name,username,avatar,is_active'])
                        ->get();
                }
            }
        }

        return view('dashboard.index', compact(
            'myTeamMembership',
            'myTeamMembers',
            'shuffleEnabled'
        ));
    }

    public function stats(Request $request)
    {
        $range = $request->input('range', 'all');
        [$start, $end] = $this->resolveRange($range, $request);

        $user = auth()->user();
        if ($user->isAdmin()) {
            $divisionFilter = $request->input('division', 'all');
            if (!in_array($divisionFilter, ['all', 'cctv', 'helpdesk'], true)) {
                $divisionFilter = 'all';
            }
            $total = $this->applyRange(WorkOrder::query(), $start, $end)->count();
            $completed = $this->applyRange(WorkOrder::query(), $start, $end)
                ->where('status', 'completed')->count();
            $progress = $this->applyRange(WorkOrder::query(), $start, $end)
                ->where('status', 'in_progress')->count();
            $pending = WorkOrder::where('status', 'pending')->count();

            $perDivision = [];
            foreach (['cctv', 'helpdesk'] as $div) {
                $base = fn() => $this->applyRange(
                    WorkOrder::where('division', $div),
                    $start,
                    $end
                );

                $perDivision[$div] = [
                    'total' => $base()->count(),
                    'completed' => $base()->where('status', 'completed')->count(),
                    'progress' => $base()->where('status', 'in_progress')->count(),
                    'pending' => WorkOrder::where('division', $div)
                        ->where('status', 'pending')->count(),
                ];
            }
            if ($divisionFilter === 'all') {
                $divisionTotal = [
                    'cctv' => $perDivision['cctv']['total'],
                    'helpdesk' => $perDivision['helpdesk']['total'],
                ];

                $divisionCompleted = [
                    'cctv' => $perDivision['cctv']['completed'],
                    'helpdesk' => $perDivision['helpdesk']['completed'],
                ];
            } else {
                $divisionTotal = [
                    $divisionFilter => $perDivision[$divisionFilter]['total'],
                ];

                $divisionCompleted = [
                    $divisionFilter => $perDivision[$divisionFilter]['completed'],
                ];
            }
            $now = now();

            $perTechnicianQuery = User::where('role', 'technician')
                ->where('is_active', true);
            if ($divisionFilter !== 'all') {
                $perTechnicianQuery->where('division', $divisionFilter);
            }

            $perTechnician = $perTechnicianQuery
                ->with([
                    'teamMemberships' => fn($q) => $q
                        ->where('period_month', $now->month)
                        ->where('period_year', $now->year)
                        ->with('team:id,name')
                ])
                ->withCount([
                    'assignedWorkOrders as total_wo' => fn($q) =>
                        $this->applyRangePrefixed($q, $start, $end, 'work_orders'),
                    'assignedWorkOrders as completed_wo' => function ($q) use ($start, $end) {
                        $q->where('work_order_technicians.status', 'completed');

                        if ($start && $end) {
                            $q->whereBetween('work_order_technicians.completed_at', [$start, $end]);
                        }
                    },
                    'assignedWorkOrders as progress_wo' => function ($q) use ($start, $end) {
                        $q->where('work_order_technicians.status', 'in_progress');

                        if ($start && $end) {
                            $q->whereBetween('work_order_technicians.started_at', [$start, $end]);
                        }
                    },
                    'assignedWorkOrders as pending_wo' => function ($q) use ($start, $end) {
                        $q->where('work_order_technicians.status', 'pending')
                            ->where('work_order_technicians.no_participation', false);

                        if ($start && $end) {
                            $q->whereBetween('work_orders.created_at', [$start, $end]);
                        }
                    },
                    'assignedWorkOrders as no_participation_wo' => function ($q) use ($start, $end) {
                        $q->where('work_order_technicians.no_participation', true);

                        if ($start && $end) {
                            $q->where(function ($q2) use ($start, $end) {
                                $q2->whereBetween('work_order_technicians.skipped_at', [$start, $end])
                                    ->orWhereNull('work_order_technicians.skipped_at');
                            });
                        }
                    },
                ])
                ->orderBy('division')
                ->orderBy('name')
                ->get(['id', 'name', 'username', 'division']);

            $perTechnician->map(function ($tech) use ($divisionTotal, $divisionCompleted) {
                $membership = $tech->teamMemberships->first();
                $tech->team_name = $membership?->team?->name;
                unset($tech->teamMemberships);

                $divTotal = $divisionTotal[$tech->division] ?? 0;
                $divCompleted = $divisionCompleted[$tech->division] ?? 0;
                $participating = $tech->total_wo - $tech->no_participation_wo;
                $tech->participating_wo = $participating;

                $tech->progress_pct = $participating > 0
                    ? round(($tech->completed_wo / $participating) * 100, 2)
                    : 0;

                $tech->contribution_total = $divTotal > 0
                    ? round(($tech->total_wo / $divTotal) * 100, 2)
                    : 0;

                $tech->contribution_completed = $divCompleted > 0
                    ? round(($tech->completed_wo / $divCompleted) * 100, 2)
                    : 0;

                $tech->division_total = $divTotal;
                $tech->division_completed = $divCompleted;

                return $tech;
            });

            return response()->json([
                'role' => 'admin',
                'total' => $total,
                'completed' => $completed,
                'progress' => $progress,
                'pending' => $pending,
                'perDivision' => $perDivision,
                'perTechnician' => $perTechnician,
                'divisionFilter' => $divisionFilter, //  BARU
                'rangeLabel' => $this->rangeLabel($range, $start, $end),
            ]);
        }
        $division = $user->division;

        $baseQuery = fn() => $this->applyRange(
            WorkOrder::where('division', $division)
                ->where(function ($q) use ($user) {
                    $q->whereHas('technicians', fn($q2) => $q2->where('user_id', $user->id))
                        ->orWhere(function ($q2) use ($user) {
                            $q2->where('created_by', $user->id)
                                ->where('source', 'technician');
                        })
                        ->orWhereDoesntHave('technicians');
                }),
            $start,
            $end
        );
        $total = $baseQuery()->count();
        $completed = WorkOrder::where('division', $division)
            ->where(function ($q) use ($user) {
                $q->whereHas('technicians', function ($q2) use ($user) {
                    $q2->where('user_id', $user->id)
                        ->where('work_order_technicians.status', 'completed');
                })
                    ->orWhere(function ($q2) use ($user) {
                        $q2->where('created_by', $user->id)
                            ->where('source', 'technician')
                            ->where('status', 'completed');
                    });
            })
            ->when($start && $end, function ($q) use ($start, $end) {
                $q->where(function ($q2) use ($start, $end) {
                    $q2->whereBetween('completed_at', [$start, $end])
                        ->orWhereHas('technicians', function ($q3) use ($start, $end) {
                            $q3->whereBetween('work_order_technicians.completed_at', [$start, $end]);
                        });
                });
            })
            ->count();
        $progress = WorkOrder::where('division', $division)
            ->where(function ($q) use ($user) {
                $q->whereHas('technicians', function ($q2) use ($user) {
                    $q2->where('user_id', $user->id)
                        ->where('work_order_technicians.status', 'in_progress');
                })
                    ->orWhere(function ($q2) use ($user) {
                        $q2->where('created_by', $user->id)
                            ->where('source', 'technician')
                            ->where('status', 'in_progress');
                    });
            })
            ->when($start && $end, function ($q) use ($start, $end) {
                $q->where(function ($q2) use ($start, $end) {
                    $q2->whereBetween('started_at', [$start, $end])
                        ->orWhereHas('technicians', function ($q3) use ($start, $end) {
                            $q3->whereBetween('work_order_technicians.started_at', [$start, $end]);
                        });
                });
            })
            ->count();
        $pending = WorkOrder::where('division', $division)
            ->where(function ($q) use ($user) {
                $q->whereDoesntHave('technicians')
                    ->where('status', 'pending')
                    ->orWhereHas('technicians', function ($q2) use ($user) {
                        $q2->where('user_id', $user->id)
                            ->where('work_order_technicians.status', 'pending')
                            ->where('work_order_technicians.no_participation', false);
                    });
            })
            ->count();
        $noParticipation = WorkOrder::where('division', $division)
            ->whereHas('technicians', function ($q2) use ($user) {
                $q2->where('user_id', $user->id)
                    ->where('work_order_technicians.no_participation', true);
            })
            ->count();

        return response()->json([
            'role' => 'technician',
            'division' => $division,
            'total' => $total,
            'completed' => $completed,
            'progress' => $progress,
            'pending' => $pending,
            'no_participation' => $noParticipation,
            'available' => $pending,
            'rangeLabel' => $this->rangeLabel($range, $start, $end),
        ]);
    }
    public function monthlyStats(Request $request)
    {
        $user = auth()->user();
        $months = (int) $request->input('months', 12);
        $months = max(3, min(24, $months));

        $start = now()->subMonths($months - 1)->startOfMonth();
        $end = now()->endOfMonth();

        $baseQuery = $this->buildMonthlyBaseQuery($user);

        $withPivot = !$user->isAdmin();

        $query = (clone $baseQuery)
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('created_at', [$start, $end])
                    ->orWhereBetween('started_at', [$start, $end])
                    ->orWhereBetween('completed_at', [$start, $end]);
            });

        if ($withPivot) {
            $query->with([
                'technicians' => function ($q) use ($user) {
                    $q->where('user_id', $user->id)
                        ->select('users.id')
                        ->withPivot([
                            'status',
                            'started_at',
                            'completed_at',
                            'no_participation',
                            'skipped_at',
                        ]);
                }
            ]);
        }

        $rows = $query->get([
            'id',
            'status',
            'division',
            'created_at',
            'started_at',
            'completed_at',
        ]);

        $buckets = [];
        for ($i = 0; $i < $months; $i++) {
            $date = (clone $start)->addMonths($i);
            $key = $date->format('Y-m');
            $buckets[$key] = [
                'label' => $date->translatedFormat('M Y'),
                'short' => $date->translatedFormat('M'),
                'year' => $date->year,
                'month' => $date->month,

                'cctv_total' => 0,
                'cctv_completed' => 0,
                'cctv_in_progress' => 0,
                'cctv_pending' => 0,

                'helpdesk_total' => 0,
                'helpdesk_completed' => 0,
                'helpdesk_in_progress' => 0,
                'helpdesk_pending' => 0,
            ];
        }

        foreach ($rows as $wo) {
            $refStatus = $wo->status;
            $refCompletedAt = $wo->completed_at;
            $refStartedAt = $wo->started_at;

            if (!$user->isAdmin()) {
                $pivot = $wo->technicians->first()?->pivot;

                if ($pivot) {
                    if ($pivot->no_participation) {
                        continue;
                    }

                    $refStatus = $pivot->status ?? $wo->status;
                    $refCompletedAt = $pivot->completed_at ?? $wo->completed_at;
                    $refStartedAt = $pivot->started_at ?? $wo->started_at;
                }
            }

            $refDate = match ($refStatus) {
                'completed' => $refCompletedAt ?? $wo->created_at,
                'in_progress' => $refStartedAt ?? $wo->created_at,
                default => $wo->created_at,
            };

            if (!$refDate) {
                continue;
            }

            $key = Carbon::parse($refDate)->format('Y-m');
            if (!isset($buckets[$key])) {
                continue;
            }

            $prefix = match ($wo->division) {
                'cctv' => 'cctv_',
                'helpdesk' => 'helpdesk_',
                default => null,
            };

            if (!$prefix) {
                continue;
            }

            $buckets[$key][$prefix . 'total']++;

            if (isset($buckets[$key][$prefix . $refStatus])) {
                $buckets[$key][$prefix . $refStatus]++;
            }
        }

        $labels = [];

        $cctvTotal = [];
        $cctvCompleted = [];
        $cctvProgress = [];
        $cctvPending = [];

        $helpdeskTotal = [];
        $helpdeskCompleted = [];
        $helpdeskProgress = [];
        $helpdeskPending = [];

        foreach ($buckets as $b) {
            $labels[] = $b['short'] . ' ' . substr($b['year'], 2);

            $cctvTotal[] = $b['cctv_total'];
            $cctvCompleted[] = $b['cctv_completed'];
            $cctvProgress[] = $b['cctv_in_progress'];
            $cctvPending[] = $b['cctv_pending'];

            $helpdeskTotal[] = $b['helpdesk_total'];
            $helpdeskCompleted[] = $b['helpdesk_completed'];
            $helpdeskProgress[] = $b['helpdesk_in_progress'];
            $helpdeskPending[] = $b['helpdesk_pending'];
        }

        return response()->json([
            'labels' => $labels,
            'series' => [
                'cctv' => [
                    'total' => $cctvTotal,
                    'completed' => $cctvCompleted,
                    'in_progress' => $cctvProgress,
                    'pending' => $cctvPending,
                ],
                'helpdesk' => [
                    'total' => $helpdeskTotal,
                    'completed' => $helpdeskCompleted,
                    'in_progress' => $helpdeskProgress,
                    'pending' => $helpdeskPending,
                ],
            ],
            'range' => [
                'from' => $start->toDateString(),
                'to' => $end->toDateString(),
            ],
        ]);
    }

    private function buildMonthlyBaseQuery($user)
    {
        if ($user->isAdmin()) {
            return WorkOrder::query();
        }

        $division = $user->division;

        return WorkOrder::where('division', $division)
            ->where(function ($q) use ($user) {
                $q->whereHas('technicians', fn($q2) => $q2->where('user_id', $user->id))
                    ->orWhere(function ($q2) use ($user) {
                        $q2->where('created_by', $user->id)
                            ->where('source', 'technician');
                    })
                    ->orWhereDoesntHave('technicians');
            });
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


    private function applyRangePrefixed($query, $start, $end, string $prefix = 'work_orders')
    {
        if (!$start || !$end) {
            return $query;
        }

        return $query->where(function ($q) use ($start, $end, $prefix) {
            $q->where("{$prefix}.status", 'pending')
                ->orWhere(function ($q2) use ($start, $end, $prefix) {
                    $q2->where("{$prefix}.status", 'in_progress')
                        ->whereBetween("{$prefix}.started_at", [$start, $end]);
                })
                ->orWhere(function ($q2) use ($start, $end, $prefix) {
                    $q2->where("{$prefix}.status", 'completed')
                        ->whereBetween("{$prefix}.completed_at", [$start, $end]);
                });
        });
    }

    private function rangeLabel(string $range, $start, $end): string
    {
        switch ($range) {
            case 'today':
                return 'Hari Ini';
            case 'week':
                return '7 Hari Terakhir';
            case 'month':
                return '30 Hari Terakhir';
            case 'all':
                return 'Semua Waktu';
            case 'custom':
                return ($start && $end)
                    ? $start->format('d M Y') . ' — ' . $end->format('d M Y')
                    : 'Custom';
            default:
                return '-';
        }
    }
}
