<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class ApiAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        // 🔍 Ambil header Authorization
        $authHeader = $request->header('Authorization');

        // ❌ Jika tidak ada header
        if (!$authHeader) {
            return response()->json([
                'message' => 'Token tidak ditemukan'
            ], 401);
        }

        // ❌ Validasi format harus "Bearer TOKEN"
        if (!str_starts_with($authHeader, 'Bearer ')) {
            return response()->json([
                'message' => 'Format token salah'
            ], 401);
        }

        // 🔐 Ambil token dari header
        $plainToken = str_replace('Bearer ', '', $authHeader);

        // 🔒 HASH token (HARUS sama dengan AuthApiController)
        $hashedToken = hash('sha256', $plainToken);

        // 🔍 Cari user berdasarkan token
        $user = User::where('api_token', $hashedToken)->first();

        // ❌ Jika token tidak valid
        if (!$user) {
            return response()->json([
                'message' => 'Token tidak valid'
            ], 401);
        }

        // ✅ Set user login
        auth()->login($user);

        return $next($request);
    }
}