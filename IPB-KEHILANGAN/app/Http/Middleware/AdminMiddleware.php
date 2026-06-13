<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
/**
 * Fungsi: Satpam yang ngecek apakah user yang mengakses
 *         halaman /admin sudah login dan punya role 'admin'.
 *         Kalau tidak, langsung di-redirect ke /login.
 */
class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Cek: sudah login?
        if (!auth()->check()) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // Cek: role-nya admin?
        // Sesuaikan 'role' dengan nama field di collection User MongoDB kamu
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Akses ditolak. Halaman ini khusus admin.');
        }

        return $next($request);
    }
}