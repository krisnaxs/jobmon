<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WorkOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'description',
        'category',
        'division',
        'source',
        'priority',
        'completion_mode',
        'status',
        'created_by',
        'building',
        'location',
        'resolution_note',
        'photo',
        'started_at',
        'completed_at',
        'deadline_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'deadline_at' => 'datetime',
    ];

    protected $appends = ['photo_url'];

    public function technicians()
    {
        return $this->belongsToMany(
            User::class,
            'work_order_technicians',
            'work_order_id',
            'user_id'
        )
            ->withPivot([
                'assigned_by',
                'assigned_at',
                'team_id',
                'team_name_snapshot',
                'status',
                'started_at',
                'completed_at',
                'resolution_note',
                'no_participation',   // ← TAMBAH
                'skipped_at',         // ← TAMBAH
                'skipped_by',         // ← TAMBAH
            ]);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function logs()
    {
        return $this->hasMany(WorkOrderLog::class)->latest();
    }
    public function media()
    {
        return $this->hasMany(WorkOrderMedia::class)->orderBy('type')->orderBy('id');
    }

    public function mediaBefore()
    {
        return $this->hasMany(WorkOrderMedia::class)->where('type', 'before');
    }

    public function mediaAfter()
    {
        return $this->hasMany(WorkOrderMedia::class)->where('type', 'after');
    }
    protected static function booted(): void
    {
        static::creating(function (WorkOrder $wo) {
            if (empty($wo->code)) {
                $last = WorkOrder::orderByDesc('id')->first();
                $next = $last ? $last->id + 1 : 1;
                $wo->code = 'WO-' . str_pad($next, 5, '0', STR_PAD_LEFT);
            }
        });
    }
    public function isCollective(): bool
    {
        return $this->completion_mode === 'collective';
    }

    public function isIndividual(): bool
    {
        return $this->completion_mode === 'individual';
    }
    protected function getTeamSnapshotForUser(int $userId): array
    {
        $now = now();

        $membership = TeamMember::where('user_id', $userId)
            ->where('period_month', $now->month)
            ->where('period_year', $now->year)
            ->with('team:id,name')
            ->first();

        return [
            'team_id' => $membership?->team_id,
            'team_name' => $membership?->team?->name,
        ];
    }
    public function assignTo(int $userId, ?int $assignedBy = null): void
    {
        $snapshot = $this->getTeamSnapshotForUser($userId);
        $technician = User::find($userId);
        $by = $assignedBy ?? auth()->id();

        $this->technicians()->syncWithoutDetaching([
            $userId => [
                'assigned_by' => $by,
                'assigned_at' => now(),
                'team_id' => $snapshot['team_id'],
                'team_name_snapshot' => $snapshot['team_name'],
                'status' => 'pending',
                'started_at' => null,
                'completed_at' => null,
                'resolution_note' => null,
                'no_participation' => false,   // ← TAMBAH
                'skipped_at' => null,          // ← TAMBAH
                'skipped_by' => null,          // ← TAMBAH
            ]
        ]);

        $name = $technician?->name ?? "User #{$userId}";
        $this->log('assigned', null, $this->status, "Ditugaskan ke {$name}");
    }

    public function assignMany(array $userIds, ?int $assignedBy = null): void
    {
        $by = $assignedBy ?? auth()->id();
        $now = now();
        $sync = [];

        foreach ($userIds as $uid) {
            $snapshot = $this->getTeamSnapshotForUser($uid);

            $sync[$uid] = [
                'assigned_by' => $by,
                'assigned_at' => $now,
                'team_id' => $snapshot['team_id'],
                'team_name_snapshot' => $snapshot['team_name'],
                'status' => 'pending',
                'started_at' => null,
                'completed_at' => null,
                'resolution_note' => null,
                'no_participation' => false,   // ← TAMBAH
                'skipped_at' => null,          // ← TAMBAH
                'skipped_by' => null,          // ← TAMBAH
            ];
        }

        $this->technicians()->syncWithoutDetaching($sync);

        $names = User::whereIn('id', $userIds)->pluck('name')->implode(', ');
        $this->log('assigned', null, $this->status, "Ditugaskan ke: {$names}");
    }
    public function startForUser(int $userId): void
    {
        $this->technicians()->updateExistingPivot($userId, [
            'status' => 'in_progress',
            'started_at' => now(),
            'no_participation' => false,   // ← RESET jika sebelumnya di-flag
            'skipped_at' => null,
            'skipped_by' => null,
        ]);

        if ($this->status === 'pending') {
            $this->update([
                'status' => 'in_progress',
                'started_at' => $this->started_at ?? now(),
            ]);
        }

        $this->log('started', null, 'in_progress', 'Mulai dikerjakan', $userId);
    }

    public function start(?int $userId = null): void
    {
        $old = $this->status;
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
        $this->log('started', $old, 'in_progress', 'Mulai dikerjakan', $userId);
    }
    public function finishForUser(int $userId, ?string $note = null): void
    {
        $this->technicians()->updateExistingPivot($userId, [
            'status' => 'completed',
            'completed_at' => now(),
            'resolution_note' => $note,
            'no_participation' => false,   // ← pastikan false
        ]);

        if ($this->isCollective()) {
            DB::table('work_order_technicians')
                ->where('work_order_id', $this->id)
                ->where('status', '!=', 'completed')
                ->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

            $this->update([
                'status' => 'completed',
                'completed_at' => $this->completed_at ?? now(),
                'resolution_note' => $note,
            ]);

            $this->log(
                'completed',
                'in_progress',
                'completed',
                ($note ?? '') . ' (mode global — semua teknisi otomatis selesai)',
                $userId
            );
        } else {
            $allCompleted = !$this->technicians()
                ->wherePivot('status', '!=', 'completed')
                ->exists();

            if ($allCompleted) {
                $this->update([
                    'status' => 'completed',
                    'completed_at' => $this->completed_at ?? now(),
                ]);

                $this->log(
                    'completed',
                    'in_progress',
                    'completed',
                    'Semua teknisi telah menyelesaikan tugas',
                    $userId
                );
            } else {
                $this->log(
                    'completed',
                    null,
                    null,
                    ($note ?? '') . ' (menunggu teknisi lain)',
                    $userId
                );
            }
        }
    }
    public function adminForceComplete(?int $userId = null, ?string $note = null): void
    {
        $oldStatus = $this->status;
        DB::table('work_order_technicians')
            ->where('work_order_id', $this->id)
            ->where('status', '!=', 'completed')
            ->update([
                'no_participation' => true,
                'skipped_at' => now(),
                'skipped_by' => $userId,
            ]);
        $this->update([
            'status' => 'completed',
            'completed_at' => $this->completed_at ?? now(),
            'resolution_note' => $note,
        ]);
        $this->log(
            'completed',
            $oldStatus,
            'completed',
            ($note ?? '') . ' (diselesaikan oleh admin — teknisi lain di-flag tidak partisipasi)',
            $userId
        );
    }


    public function finish(?int $userId = null, ?string $note = null, ?string $photoPath = null): void
    {
        $old = $this->status;

        $payload = [
            'status' => 'completed',
            'completed_at' => now(),
            'resolution_note' => $note,
        ];

        if ($photoPath) {
            $payload['photo'] = $photoPath;
        }

        $this->update($payload);

        DB::table('work_order_technicians')
            ->where('work_order_id', $this->id)
            ->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

        $this->log('completed', $old, 'completed', $note, $userId);
    }
    public function log(
        string $action,
        ?string $from = null,
        ?string $to = null,
        ?string $note = null,
        ?int $userId = null
    ): void {
        WorkOrderLog::create([
            'work_order_id' => $this->id,
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'from_status' => $from,
            'to_status' => $to,
            'note' => $note,
        ]);
    }
    public function isAssignedTo(int $userId): bool
    {
        return $this->technicians()->where('user_id', $userId)->exists();
    }

    public function isAssigned(): bool
    {
        return $this->technicians()->exists();
    }

    public function statusForUser(int $userId): ?string
    {
        return $this->technicians()
            ->where('user_id', $userId)
            ->first()?->pivot?->status;
    }

    public function isCompletedBy(int $userId): bool
    {
        return $this->statusForUser($userId) === 'completed';
    }


    public function noParticipationForUser(int $userId): bool
    {
        return (bool) $this->technicians()
            ->where('user_id', $userId)
            ->first()?->pivot?->no_participation;
    }


    public function hasParticipated(int $userId): bool
    {
        $status = $this->statusForUser($userId);
        return in_array($status, ['in_progress', 'completed']);
    }


    public function participatingCount(): int
    {
        return $this->technicians()
            ->wherePivot('no_participation', false)
            ->count();
    }


    public function noParticipationCount(): int
    {
        return $this->technicians()
            ->wherePivot('no_participation', true)
            ->count();
    }
    public function scopeActive($q)
    {
        return $q->whereIn('status', ['pending', 'in_progress']);
    }

    public function scopeCompleted($q)
    {
        return $q->where('status', 'completed');
    }

    public function scopeForTechnician($q, int $userId)
    {
        return $q->whereHas('technicians', fn($q2) => $q2->where('user_id', $userId));
    }

    public function scopeByPriority($q)
    {
        return $q->orderByRaw("FIELD(priority, 'urgent', 'high', 'normal', 'low')");
    }

    public function scopeForDivision($q, string $division)
    {
        return $q->where('division', $division);
    }

    public function scopeCctv($q)
    {
        return $q->where('division', 'cctv');
    }

    public function scopeHelpdesk($q)
    {
        return $q->where('division', 'helpdesk');
    }

    public function scopeVisibleForTechnician($q, User $user)
    {
        return $q->where('division', $user->division)
            ->where(function ($sub) use ($user) {
                $sub->whereHas('technicians', fn($q2) => $q2->where('user_id', $user->id))
                    ->orWhereDoesntHave('technicians');
            });
    }
    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'red',
            'high' => 'orange',
            'normal' => 'blue',
            'low' => 'gray',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'in_progress' => 'Dikerjakan',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => $this->status,
        };
    }

    public function getDivisionLabelAttribute(): string
    {
        return match ($this->division) {
            'cctv' => 'CCTV',
            'helpdesk' => 'Helpdesk',
            default => '-',
        };
    }

    public function getDivisionColorAttribute(): string
    {
        return match ($this->division) {
            'cctv' => 'purple',
            'helpdesk' => 'teal',
            default => 'gray',
        };
    }

    public function getCompletionModeLabelAttribute(): string
    {
        return match ($this->completion_mode) {
            'collective' => 'Global (Tim)',
            'individual' => 'Individual',
            default => '-',
        };
    }

    public function getFullLocationAttribute(): ?string
    {
        $parts = array_filter([$this->building, $this->location]);
        return $parts ? implode(' — ', $parts) : null;
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/' . $this->photo) : null;
    }

    public function hasPhoto(): bool
    {
        return !empty($this->photo);
    }
}
