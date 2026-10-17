<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamMember;
use App\Models\TeamSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class TeamSettingController extends Controller
{
    public function index()
    {
        $settings = TeamSetting::all()->keyBy('division');

        foreach (['cctv', 'helpdesk'] as $div) {
            if (!isset($settings[$div])) {
                $settings[$div] = TeamSetting::create([
                    'division' => $div,
                    'shuffle_enabled' => false,
                ]);
            }
        }

        $teams = Team::orderBy('division')->orderBy('order')->get()->groupBy('division');

        $technicians = User::where('role', 'technician')
            ->where('is_active', true)
            ->orderBy('division')
            ->orderBy('name')
            ->get()
            ->groupBy('division');

        $now = now();
        $currentMembers = TeamMember::forPeriod($now->month, $now->year)
            ->with(['team', 'user'])
            ->get()
            ->groupBy(fn($m) => $m->team->division);

        return view('team-settings.index', compact(
            'settings',
            'teams',
            'technicians',
            'currentMembers'
        ));
    }

    public function update(Request $request, string $division)
    {
        $data = $request->validate([
            'shuffle_enabled' => 'required|boolean',
            'members_per_team' => 'nullable|integer|min:1|max:20',
        ]);

        $setting = TeamSetting::firstOrCreate(['division' => $division]);
        $setting->update($data);

        $status = $data['shuffle_enabled'] ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Fitur shuffle tim divisi " . strtoupper($division) . " berhasil {$status}.");
    }

    public function shuffleNow(Request $request, string $division)
    {
        $setting = TeamSetting::forDivision($division);

        if (!$setting || !$setting->shuffle_enabled) {
            return back()->withErrors(['msg' => 'Fitur shuffle tidak aktif untuk divisi ini.']);
        }

        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        Artisan::call('teams:shuffle', [
            '--month' => $month,
            '--year' => $year,
            '--division' => $division,
            '--force' => true,
        ]);

        return back()->with('success', "Tim divisi " . strtoupper($division) . " berhasil di-rotate untuk {$month}/{$year}.");
    }
    public function storeTeam(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50',
            'division' => 'required|in:cctv,helpdesk',
        ]);
        $maxOrder = Team::where('division', $data['division'])->max('order') ?? 0;

        Team::create([
            'name' => $data['name'],
            'division' => $data['division'],
            'order' => $maxOrder + 1,
            'is_active' => true,
        ]);

        return back()->with('success', "Tim \"{$data['name']}\" berhasil ditambahkan ke divisi " . strtoupper($data['division']) . ".");
    }

    public function updateTeam(Request $request, Team $team)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50',
        ]);

        $team->update(['name' => $data['name']]);

        return back()->with('success', "Tim berhasil di-rename menjadi \"{$data['name']}\".");
    }

    public function destroyTeam(Team $team)
    {
        $division = strtoupper($team->division);
        $name = $team->name;

        $team->delete();

        return back()->with('success', "Tim \"{$name}\" berhasil dihapus dari divisi {$division}.");
    }

    public function autoGenerateTeams(Request $request)
    {
        $division = $request->input('division');

        if (!in_array($division, ['cctv', 'helpdesk'])) {
            return back()->withErrors(['msg' => 'Divisi tidak valid.']);
        }
        $existing = Team::where('division', $division)->count();
        if ($existing > 0) {
            return back()->withErrors(['msg' => "Divisi " . strtoupper($division) . " sudah punya {$existing} tim."]);
        }
        Team::create([
            'name' => 'Tim A',
            'division' => $division,
            'order' => 1,
            'is_active' => true,
        ]);

        Team::create([
            'name' => 'Tim B',
            'division' => $division,
            'order' => 2,
            'is_active' => true,
        ]);

        return back()->with('success', "Berhasil generate 2 tim default (Tim A & Tim B) untuk divisi " . strtoupper($division) . ".");
    }
}
