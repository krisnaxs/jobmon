<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PublicMonitorController extends Controller
{
    public function index()
    {
        return view('monitor');
    }

    public function data(Request $request)
    {
        $range = $request->input('range', 'today');

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
                $request->validate([
                    'from' => 'required|date',
                    'to' => 'required|date|after_or_equal:from',
                ]);

                $start = Carbon::parse($request->from)->startOfDay();
                $end = Carbon::parse($request->to)->endOfDay();
                break;

            case 'all':
            default:
                $start = null;
                $end = null;
                break;
        }
        $jobs = WorkOrder::with([
            'technicians' => function ($q) {
                $q->select('users.id', 'users.name', 'users.division')
                    ->withPivot([
                        'team_id',
                        'team_name_snapshot',
                        'status',
                        'started_at',
                        'completed_at',
                        'resolution_note',
                        'no_participation',   // ← TAMBAH
                        'skipped_at',         // ← TAMBAH
                    ]);
            },
        ])
            ->where(function ($q) use ($start, $end) {
                $q->where('status', 'pending');

                if ($start && $end) {
                    $q->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('status', 'in_progress')
                            ->whereBetween('started_at', [$start, $end]);
                    });

                    $q->orWhere(function ($q3) use ($start, $end) {
                        $q3->where('status', 'completed')
                            ->whereBetween('completed_at', [$start, $end]);
                    });
                } else {
                    $q->orWhereIn('status', ['in_progress', 'completed']);
                }
            })
            ->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')")
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();
        $jobs->each(function ($job) {
            $job->technicians->each(function ($t) {
                $t->team_name = $t->pivot->team_name_snapshot;
                $t->pivot_status = $t->pivot->status;
                $t->pivot_started_at = $t->pivot->started_at;
                $t->pivot_completed_at = $t->pivot->completed_at;
                $t->pivot_resolution_note = $t->pivot->resolution_note;
                $t->pivot_no_participation = (bool) $t->pivot->no_participation;  // ← TAMBAH
                $t->pivot_skipped_at = $t->pivot->skipped_at;                     // ← TAMBAH
                unset($t->pivot);
            });
            $participating = $job->technicians->where('pivot_no_participation', false);
            $skipped = $job->technicians->where('pivot_no_participation', true);

            $job->team_progress = [
                'completed' => $participating->where('pivot_status', 'completed')->count(),
                'total' => $participating->count(),
                'skipped' => $skipped->count(),   // ← TAMBAH
            ];
        });

        $jobsByStatus = [
            'in_progress' => $jobs->where('status', 'in_progress')->values(),
            'pending' => $jobs->where('status', 'pending')->values(),
            'completed' => $jobs->where('status', 'completed')->values(),
        ];
        $stats = [
            'total' => $this->totalQuery($start, $end)->count(),
            'completed' => $this->statsQuery('completed', $start, $end),
            'progress' => $this->statsQuery('in_progress', $start, $end),
            'pending' => WorkOrder::where('status', 'pending')->count(),
        ];
        $statsByDivision = [];
        foreach (['cctv', 'helpdesk'] as $div) {
            $statsByDivision[$div] = [
                'total' => $this->totalQuery($start, $end, $div)->count(),
                'completed' => $this->statsQuery('completed', $start, $end, $div),
                'progress' => $this->statsQuery('in_progress', $start, $end, $div),
                'pending' => WorkOrder::where('division', $div)
                    ->where('status', 'pending')
                    ->count(),
            ];
        }
        $now = now();

        $technicians = User::where('role', 'technician')
            ->where('is_active', true)
            ->with([
                'teamMemberships' => fn($q) => $q
                    ->where('period_month', $now->month)
                    ->where('period_year', $now->year)
                    ->with('team:id,name')
            ])
            ->withCount([
                'assignedWorkOrders as total_wo' => function ($q) use ($start, $end) {
                    if ($start && $end) {
                        $q->where(function ($q2) use ($start, $end) {
                            $q2->where('work_orders.status', 'pending')
                                ->orWhere(function ($q3) use ($start, $end) {
                                    $q3->where('work_orders.status', 'in_progress')
                                        ->whereBetween('work_orders.started_at', [$start, $end]);
                                })
                                ->orWhere(function ($q3) use ($start, $end) {
                                    $q3->where('work_orders.status', 'completed')
                                        ->whereBetween('work_orders.completed_at', [$start, $end]);
                                });
                        });
                    }
                },
                'assignedWorkOrders as completed_wo' => function ($q) use ($start, $end) {
                    $q->where('work_order_technicians.status', 'completed');

                    if ($start && $end) {
                        $q->where(function ($q2) use ($start, $end) {
                            $q2->whereBetween('work_order_technicians.completed_at', [$start, $end])
                                ->orWhere(function ($q3) use ($start, $end) {
                                    $q3->whereNull('work_order_technicians.completed_at')
                                        ->whereBetween('work_orders.completed_at', [$start, $end]);
                                });
                        });
                    }
                },
                'assignedWorkOrders as progress_wo' => function ($q) use ($start, $end) {
                    $q->where('work_order_technicians.status', 'in_progress');

                    if ($start && $end) {
                        $q->where(function ($q2) use ($start, $end) {
                            $q2->whereBetween('work_order_technicians.started_at', [$start, $end])
                                ->orWhere(function ($q3) use ($start, $end) {
                                    $q3->whereNull('work_order_technicians.started_at')
                                        ->whereBetween('work_orders.started_at', [$start, $end]);
                                });
                        });
                    }
                },
                'assignedWorkOrders as pending_wo' => function ($q) {
                    $q->where('work_order_technicians.status', 'pending')
                        ->where('work_order_technicians.no_participation', false);
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
            ->get(['id', 'name', 'division']);

        $technicians->each(function ($tech) {
            $membership = $tech->teamMemberships->first();
            $tech->team_name = $membership?->team?->name;
            unset($tech->teamMemberships);
            $participating = $tech->total_wo - $tech->no_participation_wo;
            $tech->participating_wo = $participating;

            $tech->progress_pct = $participating > 0
                ? round(($tech->completed_wo / $participating) * 100, 2)
                : 0;
        });

        $techniciansByDivision = [
            'cctv' => $technicians->where('division', 'cctv')->values(),
            'helpdesk' => $technicians->where('division', 'helpdesk')->values(),
        ];

        return response()->json(compact(
            'jobs',
            'jobsByStatus',
            'stats',
            'statsByDivision',
            'technicians',
            'techniciansByDivision',
            'range'
        ) + [
            'rangeLabel' => $this->rangeLabel($range, $start, $end),
        ]);
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
                if ($start && $end) {
                    return $start->format('d M Y') . ' — ' . $end->format('d M Y');
                }
                return 'Custom';
            default:
                return '-';
        }
    }

    private function totalQuery($start, $end, ?string $division = null)
    {
        $q = WorkOrder::query();

        if ($division) {
            $q->where('division', $division);
        }

        $q->where(function ($q2) use ($start, $end) {
            $q2->where('status', 'pending');

            if ($start && $end) {
                $q2->orWhere(function ($q3) use ($start, $end) {
                    $q3->where('status', 'in_progress')
                        ->whereBetween('started_at', [$start, $end]);
                })->orWhere(function ($q3) use ($start, $end) {
                    $q3->where('status', 'completed')
                        ->whereBetween('completed_at', [$start, $end]);
                });
            } else {
                $q2->orWhereIn('status', ['in_progress', 'completed']);
            }
        });

        return $q;
    }

    private function statsQuery(string $status, $start, $end, ?string $division = null): int
    {
        $q = WorkOrder::where('status', $status);

        if ($division) {
            $q->where('division', $division);
        }

        if ($start && $end) {
            if ($status === 'completed') {
                $q->whereBetween('completed_at', [$start, $end]);
            } elseif ($status === 'in_progress') {
                $q->whereBetween('started_at', [$start, $end]);
            }
        }

        return $q->count();
    }
}
