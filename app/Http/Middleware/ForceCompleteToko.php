<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Toko;

class ForceCompleteToko
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if ($user) {
            // Cek jika akun ber-role Mitra
            $isMitra = in_array($user->jenis_akun_id_jenis_akun, [3, 4, 5, 6]);

            if ($isMitra) {
                // Pastikan pencarian toko menggunakan foreign key yang benar (akun_id_akun)
                $toko = Toko::where('akun_id_akun', $user->id_akun)->first();

                // Daftar rute yang dibolehkan diakses meskipun toko belum diisi
                $isTokoRoute = $request->is('mitra/toko*') || 
                               $request->is('api/mitra/toko*') || 
                               $request->is('mitra/lengkapi-toko*');

                // Jika belum ada data toko dan mencoba buka menu lain, PAKSA REDIRECT ke form toko
                if (!$toko && !$isTokoRoute) {
                    // Jika request dari AJAX / API, kembalikan JSON
                    if ($request->expectsJson() || $request->is('api/*')) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Anda wajib melengkapi data toko terlebih dahulu.',
                        ], 403);
                    }

                    // Jika request dari browser biasa, lakukan redirect HTTP murni ke halaman isi toko
                    return redirect()->route('mitra.toko.create')->with('error', 'Anda wajib melengkapi data toko terlebih dahulu.');
                }
            }
        }

        return $next($request);
    }
}