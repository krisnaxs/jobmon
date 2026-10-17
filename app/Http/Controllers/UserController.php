<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        return view('users.index');
    }
    public function data(Request $request)
    {
        $now = now();

        $users = User::with([
            'teamMemberships' => fn($q) => $q
                ->where('period_month', $now->month)
                ->where('period_year', $now->year)
                ->with('team'),
        ])
            ->orderBy('role')
            ->orderBy('name')
            ->get();

        return response()->json($users->map(function ($u) use ($now) {
            $currentMembership = $u->teamMemberships->first();
            $team = $currentMembership && $currentMembership->team
                ? [
                    'id' => $currentMembership->team->id,
                    'name' => $currentMembership->team->name,
                ]
                : null;

            return [
                'id' => $u->id,
                'name' => $u->name,
                'username' => $u->username,
                'email' => $u->email,
                'role' => $u->role,
                'division' => $u->division,
                'is_active' => (bool) $u->is_active,
                'avatar_url' => $u->avatar_url,
                'team' => $team,  // ← Info tim bulan ini
            ];
        }));
    }


    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:100|unique:users,username|alpha_dash',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:admin,technician',
            'division' => 'nullable|required_if:role,technician|in:cctv,helpdesk',
            'is_active' => 'boolean',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048', // ← TAMBAH
        ]);
        if ($data['role'] === 'admin') {
            $data['division'] = null;
        }
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $this->storeAvatar($request->file('avatar'));
        }

        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['email_verified_at'] = now();

        $user = User::create($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User {$user->name} berhasil dibuat.");
    }

    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
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
            'role' => 'required|in:admin,technician',
            'division' => 'nullable|required_if:role,technician|in:cctv,helpdesk',
            'is_active' => 'boolean',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048', // ← TAMBAH
        ]);

        if ($data['role'] === 'admin') {
            $data['division'] = null;
        }

        $data['is_active'] = $request->boolean('is_active');
        if ($user->id === auth()->id()) {
            if ($data['role'] !== 'admin' || !$data['is_active']) {
                return back()->withErrors([
                    'role' => 'Anda tidak bisa mengubah role / menonaktifkan akun Anda sendiri.',
                ])->withInput();
            }
        }
        if ($request->hasFile('avatar')) {
            $this->deleteAvatarFile($user->avatar);
            $data['avatar'] = $this->storeAvatar($request->file('avatar'));
        }

        $user->update($data);

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User {$user->name} berhasil diperbarui.");
    }
    public function removeAvatar(User $user)
    {
        $this->deleteAvatarFile($user->avatar);

        $user->update(['avatar' => null]);

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Avatar {$user->name} berhasil dihapus.",
            ]);
        }

        return back()->with('success', "Avatar {$user->name} berhasil dihapus.");
    }

    public function resetPassword(Request $request, User $user)
    {
        $data = $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        return back()->with('success', "Password {$user->name} berhasil direset.");
    }

    public function toggleActive(User $user)
    {
        if ($user->id === auth()->id()) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak bisa menonaktifkan akun Anda sendiri.',
                ], 422);
            }

            return back()->withErrors(['msg' => 'Anda tidak bisa menonaktifkan akun Anda sendiri.']);
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "User {$user->name} berhasil {$status}.",
                'is_active' => (bool) $user->is_active,
            ]);
        }

        return back()->with('success', "User {$user->name} berhasil {$status}.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak bisa menghapus akun Anda sendiri.',
                ], 422);
            }

            return back()->withErrors(['msg' => 'Anda tidak bisa menghapus akun Anda sendiri.']);
        }
        if ($user->createdWorkOrders()->exists()) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ini memiliki work order terkait. Nonaktifkan saja, jangan dihapus.',
                ], 422);
            }

            return back()->withErrors([
                'msg' => 'User ini memiliki work order terkait. Nonaktifkan saja, jangan dihapus.',
            ]);
        }
        if ($user->assignedWorkOrders()->exists()) {
            if (request()->expectsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ini masih di-assign ke work order. Nonaktifkan saja, jangan dihapus.',
                ], 422);
            }

            return back()->withErrors([
                'msg' => 'User ini masih di-assign ke work order. Nonaktifkan saja, jangan dihapus.',
            ]);
        }
        $this->deleteAvatarFile($user->avatar);

        $name = $user->name;
        $user->delete();

        if (request()->expectsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "User {$name} berhasil dihapus.",
            ]);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', "User {$name} berhasil dihapus.");
    }


    private function storeAvatar($file): string
    {
        return $file->store('avatars', 'public');
    }


    private function deleteAvatarFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
