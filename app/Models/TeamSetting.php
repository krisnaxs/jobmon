<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamSetting extends Model
{
    protected $fillable = ['division', 'shuffle_enabled', 'members_per_team'];

    protected $casts = [
        'shuffle_enabled' => 'boolean',
        'members_per_team' => 'integer',
    ];

    public static function forDivision(string $division): ?self
    {
        return static::where('division', $division)->first();
    }

    public static function isShuffleEnabled(string $division): bool
    {
        return (bool) static::where('division', $division)->value('shuffle_enabled');
    }

    public static function enabledDivisions(): array
    {
        return static::where('shuffle_enabled', true)->pluck('division')->toArray();
    }
}
