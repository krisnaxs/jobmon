<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{

    public function handle(Request $request, Closure $next, string $role, ?string $division = null): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        if ($user->role !== $role) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin.');
        }
        if ($division !== null) {
            if ($user->division !== $division) {
                abort(403, 'Akses ditolak. Divisi Anda tidak sesuai.');
            }
        }

        return $next($request);
    }
}
