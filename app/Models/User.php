<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'avatar',       // ← TAMBAH
        'role',
        'division',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'avatar_url',   // ← TAMBAH
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar
            ? Storage::disk('public')->url($this->avatar)
            : null;
    }

    public function hasAvatar(): bool
    {
        return !empty($this->avatar);
    }

    public function getAvatarInitialAttribute(): string
    {
        return strtoupper(substr($this->name ?? '?', 0, 1));
    }
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTechnician(): bool
    {
        return $this->role === 'technician';
    }
    public function isCctv(): bool
    {
        return $this->role === 'technician' && $this->division === 'cctv';
    }

    public function isHelpdesk(): bool
    {
        return $this->role === 'technician' && $this->division === 'helpdesk';
    }

    public function canHandleDivision(string $division): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->isTechnician() && $this->division === $division;
    }


    public function assignedWorkOrders()
    {
        return $this->belongsToMany(
            WorkOrder::class,
            'work_order_technicians',
            'user_id',
            'work_order_id'
        );
    }

    public function createdWorkOrders()
    {
        return $this->hasMany(WorkOrder::class, 'created_by');
    }

    public function workOrderLogs()
    {
        return $this->hasMany(WorkOrderLog::class);
    }


    public function uploadedMedia()
    {
        return $this->hasMany(WorkOrderMedia::class, 'uploaded_by');
    }
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTechnicians($query)
    {
        return $query->where('role', 'technician');
    }

    public function scopeDivision($query, string $division)
    {
        return $query->where('division', $division);
    }

    public function scopeCctv($query)
    {
        return $query->where('role', 'technician')->where('division', 'cctv');
    }

    public function scopeHelpdesk($query)
    {
        return $query->where('role', 'technician')->where('division', 'helpdesk');
    }

    public function teamMemberships()
    {
        return $this->hasMany(TeamMember::class);
    }

    public function currentTeamMembership()
    {
        $now = now();
        return $this->hasOne(TeamMember::class)
            ->where('period_month', $now->month)
            ->where('period_year', $now->year);
    }
}
