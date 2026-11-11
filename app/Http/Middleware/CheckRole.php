<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Paksa sanctum mengecek token bearer yang dikirimkan
        $user = $request->user('sanctum');

        if (!$user) {
            return response()->json([
                'message' => 'Silakan login terlebih dahulu.'
            ], 401);
        }

        // Ambil data jenis akun
        $akun = \App\Models\Akun::with('jenisAkun')->find($user->id_akun ?? $user->id);
        $namaJenisAkun = strtolower($akun->jenisAkun->nama_jenis_akun ?? '');

        $allowed = false;
        foreach ($roles as $role) {
            if (strtolower($role) === 'mitra' && (str_contains($namaJenisAkun, 'mitra') || in_array($akun->jenis_akun_id_jenis_akun, [3, 4, 5, 6]))) {
                $allowed = true;
                break;
            }
            if (strtolower($role) === 'user' && $akun->jenis_akun_id_jenis_akun == 2) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            return response()->json([
                'message' => 'Akses ditolak! Anda tidak memiliki hak akses ke halaman ini.'
            ], 403);
        }

        return $next($request);
    }
}