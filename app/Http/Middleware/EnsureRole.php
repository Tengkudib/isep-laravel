<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pastikan pengguna login DAN mempunyai peranan yang dibenarkan.
 * Contoh: ->middleware('role:admin') atau ->middleware('role:admin,lecturer')
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! in_array($user->role, $roles, true)) {
            abort(403, t('Akses ditolak. Anda tidak mempunyai kebenaran untuk mengakses halaman ini.', 'Access denied. You do not have permission to access this page.'));
        }

        return $next($request);
    }
}
