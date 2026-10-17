<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    protected $fillable = ['team_id', 'user_id', 'period_month', 'period_year'];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForPeriod($q, int $month, int $year)
    {
        return $q->where('period_month', $month)->where('period_year', $year);
    }
}
