<?php

namespace App\Http\Controllers;

use App\Models\TeamMember;
use App\Models\TeamSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $now = now();
        $myTeamMembership = null;
        $teamMembers = collect();
        $shuffleEnabled = false;

        if ($user->division) {
            $shuffleEnabled = TeamSetting::isShuffleEnabled($user->division);

            if ($shuffleEnabled) {
                $myTeamMembership = TeamMember::where('user_id', $user->id)
                    ->where('period_month', $now->month)
                    ->where('period_year', $now->year)
                    ->with(['team'])
                    ->first();

                if ($myTeamMembership && $myTeamMembership->team) {
                    $teamMembers = TeamMember::where('team_id', $myTeamMembership->team_id)
                        ->where('period_month', $now->month)
                        ->where('period_year', $now->year)
                        ->with(['user:id,name,username,avatar,is_active'])
                        ->get();
                }
            }
        }

        return view('profile.index', compact(
            'user',
            'myTeamMembership',
            'teamMembers',
            'shuffleEnabled'
        ));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => [
                'required',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function removeAvatar()
    {
        $user = auth()->user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->update(['avatar' => null]);

        return back()->with('success', 'Foto profile berhasil dihapus.');
    }

    public function changePassword(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed|different:current_password',
        ], [
            'password.different' => 'Password baru tidak boleh sama dengan password lama.',
        ]);

        if (!Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'Password lama tidak sesuai.',
            ]);
        }

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        return back()->with('success', 'Password berhasil diubah.');
    }
}
