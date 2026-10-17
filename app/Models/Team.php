<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = ['name', 'division', 'order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    public function members()
    {
        return $this->hasMany(TeamMember::class);
    }

    public function membersForPeriod(int $month, int $year)
    {
        return $this->hasMany(TeamMember::class)
            ->where('period_month', $month)
            ->where('period_year', $year);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
