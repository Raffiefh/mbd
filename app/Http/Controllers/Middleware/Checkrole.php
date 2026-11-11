<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Pastikan user sedang login via Sanctum/Auth
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        // Ambil nama jenis akun dari relasi (sesuaikan dengan relasi di model Akun Anda)
        $namaJenisAkun = strtolower($user->jenisAkun->nama_jenis_akun ?? '');

        // Cek apakah role user saat ini diizinkan masuk
        $allowed = false;
        foreach ($roles as $role) {
            if (str_contains($namaJenisAkun, strtolower($role))) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            // Jika tidak diizinkan, tolak akses (bisa diarahkan ke halaman 403 atau return JSON)
            return response()->json([
                'message' => 'Akses ditolak! Anda tidak memiliki hak akses ke halaman ini.'
            ], 403);
        }

        return $next($request);
    }
}