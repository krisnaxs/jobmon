<?php

namespace App\Console\Commands;

use App\Models\Team;
use App\Models\TeamMember;
use App\Models\TeamSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ShuffleTeams extends Command
{
    protected $signature = 'teams:shuffle
                            {--month= : Bulan target (1-12)}
                            {--year= : Tahun target}
                            {--division= : Proses divisi tertentu saja}
                            {--force : Overwrite kalau sudah ada}';

    protected $description = 'Rotasi team members per bulan (round-robin per orang)';

    public function handle()
    {
        $target = ($this->option('month') && $this->option('year'))
            ? Carbon::create((int) $this->option('year'), (int) $this->option('month'), 1)
            : now();

        $month = (int) $target->month;
        $year = (int) $target->year;
        if ($division = $this->option('division')) {
            $divisions = [$division];
        } else {
            $divisions = TeamSetting::enabledDivisions();
        }

        if (empty($divisions)) {
            $this->warn('⚠ Tidak ada divisi yang mengaktifkan fitur shuffle tim.');
            return self::SUCCESS;
        }

        $this->info("🔄 Rotasi tim untuk {$month}/{$year}");
        $this->line("  Divisi aktif: " . implode(', ', $divisions));
        $this->newLine();

        foreach ($divisions as $division) {
            $this->processDivision($division, $month, $year);
        }

        $this->newLine();
        $this->info(' Selesai!');
    }

    private function processDivision(string $division, int $month, int $year)
    {
        $this->line("📌 Divisi: {$division}");

        $setting = TeamSetting::forDivision($division);
        if (!$setting || !$setting->shuffle_enabled) {
            $this->warn("  ⚠ Shuffle tidak aktif, skip.");
            return;
        }

        $teams = Team::where('division', $division)->active()->orderBy('order')->get();

        if ($teams->count() < 2) {
            $this->warn("  ⚠ Minimal 2 tim, skip.");
            return;
        }

        $users = User::where('role', 'technician')
            ->where('division', $division)
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        if ($users->count() < 2) {
            $this->warn("  ⚠ User kurang, skip.");
            return;
        }

        $existing = TeamMember::whereIn('team_id', $teams->pluck('id'))
            ->forPeriod($month, $year)
            ->exists();

        if ($existing && !$this->option('force')) {
            $this->warn("  ⚠ Sudah ada data {$month}/{$year}. Pakai --force untuk overwrite.");
            return;
        }

        DB::transaction(function () use ($teams, $users, $month, $year, $existing, $setting) {
            if ($existing) {
                TeamMember::whereIn('team_id', $teams->pluck('id'))
                    ->forPeriod($month, $year)
                    ->delete();
            }

            $monthsSinceEpoch = ($year - 2026) * 12 + ($month - 1);
            $totalUsers = $users->count();
            $offset = $monthsSinceEpoch % $totalUsers;

            $userIds = $users->pluck('id')->toArray();
            $rotated = array_merge(
                array_slice($userIds, $offset),
                array_slice($userIds, 0, $offset)
            );

            $teamCount = $teams->count();
            $perTeam = $setting->members_per_team
                ? $setting->members_per_team
                : (int) ceil($totalUsers / $teamCount);

            $cursor = 0;
            foreach ($teams as $i => $team) {
                $remaining = $totalUsers - $cursor;
                if ($remaining <= 0)
                    break;

                $size = ($i === $teamCount - 1) ? $remaining : min($perTeam, $remaining);
                $chunk = array_slice($rotated, $cursor, $size);
                $cursor += $size;

                foreach ($chunk as $userId) {
                    TeamMember::create([
                        'team_id' => $team->id,
                        'user_id' => $userId,
                        'period_month' => $month,
                        'period_year' => $year,
                    ]);
                }

                $names = User::whereIn('id', $chunk)->pluck('name')->join(', ');
                $this->line("  ✓ {$team->name}: {$names}");
            }

            $this->info("  → {$totalUsers} user, offset rotate = {$offset}");
        });
    }
}
