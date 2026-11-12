<?php

namespace App\Http\Controllers;

use App\Models\Toko;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Detail toko (popup), rating & ulasan, favorit, dan pencatatan klik (analitik).
 * Role: 2 = User, 3-6 = Mitra.
 */
class TokoDetailController extends Controller
{
    private const MITRA_IDS = [3, 4, 5, 6];

    // >>> Nama tabel favorit di database kamu. Ubah di sini saja jika berbeda. <<<
    public const TBL_FAVORIT = 'favorit_toko';

    private function isMitra($user): bool
    {
        return in_array((int) $user->jenis_akun_id_jenis_akun, self::MITRA_IDS, true);
    }

    private function onlyUser($user)
    {
        if ($this->isMitra($user) || (int) $user->jenis_akun_id_jenis_akun === 1) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur ini hanya untuk akun User.',
            ], 403);
        }
        return null;
    }

    /** GET /api/toko/{id}/detail */
    public function show(Request $request, $id)
    {
        $user  = $request->user();
        $toko  = Toko::with(['gambarToko', 'promo', 'kategori'])->find($id);

        if (!$toko) {
            return response()->json(['success' => false, 'message' => 'Toko tidak ditemukan.'], 404);
        }

        $isMitra = $this->isMitra($user);
        $isOwner = (int) $toko->akun_id_akun === (int) $user->id_akun;

        // Analitik: setiap klik dicatat, kecuali mitra membuka tokonya sendiri
        if (!$isOwner) {
            DB::table('analitik')->insert([
                'diklik_pada'  => now(),
                'akun_id_akun' => $user->id_akun,
                'toko_id_toko' => $toko->id_toko,
            ]);
        }

        $ulasan = DB::table('rating_ulasan')
            ->join('akun', 'akun.id_akun', '=', 'rating_ulasan.akun_id_akun')
            ->where('rating_ulasan.toko_id_toko', $toko->id_toko)
            ->orderByDesc('rating_ulasan.created_at')
            ->select('rating_ulasan.id_rating_ulasan', 'rating_ulasan.rating', 'rating_ulasan.ulasan',
                     'rating_ulasan.created_at', 'rating_ulasan.akun_id_akun', 'akun.nama_lengkap')
            ->get()
            ->map(fn ($r) => [
                'id_rating_ulasan' => $r->id_rating_ulasan,
                'rating'           => (int) $r->rating,
                'ulasan'           => $r->ulasan,
                'created_at'       => $r->created_at,
                'nama'             => $r->nama_lengkap,
                'is_mine'          => !$isMitra && (int) $r->akun_id_akun === (int) $user->id_akun,
            ])->values();

        $promo = null;
        if ($toko->promo && ($toko->promo->deskripsi_promo || $toko->promo->path_promo)) {
            $promo = [
                'deskripsi' => $toko->promo->deskripsi_promo,
                'gambar'    => $toko->promo->path_promo ? Storage::disk('s3')->url(ltrim($toko->promo->path_promo, '/')) : null,
                'aktif'     => (int) $toko->promo->keadaan_promo === 1,
            ];
        }

        $data = [
            'id_toko'        => $toko->id_toko,
            'nama_toko'      => $toko->nama_toko,
            'deskripsi_toko' => $toko->deskripsi_toko,
            'gambar'         => $toko->gambarToko->map(fn ($g) => Storage::disk('s3')->url(ltrim($g->path_gambar, '/')))->values(),
            'kategori'       => $toko->kategori->pluck('nama_kategori')->values(),
            'lat'            => $toko->lattitude,
            'lng'            => $toko->longtitude,
            'maps_url'       => "https://www.google.com/maps?q={$toko->lattitude},{$toko->longtitude}",
            'jam_buka'       => $toko->jam_buka,
            'jam_tutup'      => $toko->jam_tutup,
            'hari_tutup'     => $toko->hari_tutup,
            'harga_mulai'    => $toko->harga_mulai,
            'no_kontak'      => DB::table('akun')->where('id_akun', $toko->akun_id_akun)->value('no_telefon'),
            'promo'          => $promo,
            'is_owner'       => $isOwner,
            'ulasan'         => $ulasan,
        ];

        // Khusus User: rata-rata bintang, status favorit, ulasan milik sendiri
        if (!$isMitra) {
            $avg = DB::table('rating_ulasan')->where('toko_id_toko', $toko->id_toko)->avg('rating');
            $data['rating_rata_rata'] = $avg === null ? 5.0 : round((float) $avg, 1); // default 5
            $data['jumlah_rating']    = $ulasan->count();
            $data['is_favorit']       = DB::table(self::TBL_FAVORIT)
                ->where('akun_id_akun', $user->id_akun)->where('toko_id_toko', $toko->id_toko)->exists();
        }

        return response()->json(['success' => true, 'data' => $data], 200);
    }

    /** POST /api/toko/{id}/ulasan  (rating wajib, ulasan opsional, 1x per toko) */
    public function storeUlasan(Request $request, $id)
    {
        $user = $request->user();
        if ($deny = $this->onlyUser($user)) return $deny;

        $request->validate([
            'rating' => 'required|integer|between:1,5',
            'ulasan' => 'nullable|string|max:255',
        ], [
            'rating.required' => 'Silakan pilih rating bintang terlebih dahulu.',
            'rating.between'  => 'Rating harus antara 1 sampai 5.',
            'ulasan.max'      => 'Ulasan maksimal 255 karakter.',
        ]);

        if (!Toko::where('id_toko', $id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Toko tidak ditemukan.'], 404);
        }

        $sudah = DB::table('rating_ulasan')
            ->where('akun_id_akun', $user->id_akun)->where('toko_id_toko', $id)->exists();
        if ($sudah) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah memberi ulasan pada toko ini. Hapus ulasan lama untuk menulis ulang.',
            ], 409);
        }

        DB::table('rating_ulasan')->insert([
            'rating'       => (int) $request->rating,
            'ulasan'       => $request->filled('ulasan') ? trim($request->ulasan) : null,
            'created_at'   => now(),
            'akun_id_akun' => $user->id_akun,
            'toko_id_toko' => $id,
        ]);

        return response()->json(['success' => true, 'message' => 'Terima kasih, ulasan Anda terkirim!'], 201);
    }

    /** DELETE /api/toko/{id}/ulasan  (hapus ulasan milik sendiri) */
    public function destroyUlasan(Request $request, $id)
    {
        $user = $request->user();
        if ($deny = $this->onlyUser($user)) return $deny;

        $deleted = DB::table('rating_ulasan')
            ->where('akun_id_akun', $user->id_akun)->where('toko_id_toko', $id)->delete();

        if (!$deleted) {
            return response()->json(['success' => false, 'message' => 'Ulasan Anda tidak ditemukan.'], 404);
        }
        return response()->json(['success' => true, 'message' => 'Ulasan berhasil dihapus.'], 200);
    }

    /** POST /api/toko/{id}/favorit  (toggle love) */
    public function toggleFavorit(Request $request, $id)
    {
        $user = $request->user();
        if ($deny = $this->onlyUser($user)) return $deny;

        if (!Toko::where('id_toko', $id)->exists()) {
            return response()->json(['success' => false, 'message' => 'Toko tidak ditemukan.'], 404);
        }

        try {
            $q = DB::table(self::TBL_FAVORIT)->where('akun_id_akun', $user->id_akun)->where('toko_id_toko', $id);

            if ($q->exists()) {
                $q->delete();
                $isFavorit = false;
            } else {
                DB::table(self::TBL_FAVORIT)->insert([
                    'timestamped'  => now(),
                    'akun_id_akun' => $user->id_akun,
                    'toko_id_toko' => $id,
                ]);
                $isFavorit = true;
            }
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan favorit: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success'    => true,
            'is_favorit' => $isFavorit,
            'message'    => $isFavorit ? 'Ditambahkan ke daftar favorit.' : 'Dihapus dari daftar favorit.',
        ], 200);
    }

    /** GET /api/favorit  (daftar favorit milik user, untuk section "Daftar Favorit") */
    public function favoritIndex(Request $request)
    {
        $user = $request->user();
        if ($deny = $this->onlyUser($user)) return $deny;

        $ids = DB::table(self::TBL_FAVORIT)->where('akun_id_akun', $user->id_akun)
            ->orderByDesc('timestamped')->pluck('toko_id_toko');

        $tokos   = Toko::with(['gambarToko', 'promo'])->whereIn('id_toko', $ids)->get()->keyBy('id_toko');
        $jenis   = DB::table('akun')->whereIn('id_akun', $tokos->pluck('akun_id_akun')->all())
            ->pluck('jenis_akun_id_jenis_akun', 'id_akun');
        $ratings = DB::table('rating_ulasan')->whereIn('toko_id_toko', $ids->all())
            ->select('toko_id_toko', DB::raw('AVG(rating) as avg_rating'))
            ->groupBy('toko_id_toko')->pluck('avg_rating', 'toko_id_toko');

        // Format sama dengan kartu katalog; urut dari yang terakhir di-love
        $data = $ids->map(fn ($id) => $tokos->get($id))->filter()->map(function ($t) use ($jenis, $ratings) {
            $j = (int) ($jenis[$t->akun_id_akun] ?? 0);
            return [
                'id_toko'          => $t->id_toko,
                'nama_toko'        => $t->nama_toko,
                'gambar_utama'     => ($g = $t->gambarToko->first()) ? Storage::disk('s3')->url(ltrim($g->path_gambar, '/')) : null,
                'jam_buka'         => $t->jam_buka,
                'jam_tutup'        => $t->jam_tutup,
                'harga_mulai'      => $t->harga_mulai,
                'status_buka'      => $this->isOpen($t),
                'ada_promo'        => $t->promo && (int) $t->promo->keadaan_promo === 1,
                'is_premium'       => in_array($j, [4, 5, 6], true),
                'jarak_km'         => null,
                'rating_rata_rata' => isset($ratings[$t->id_toko]) ? round((float) $ratings[$t->id_toko], 1) : 5.0,
            ];
        })->values();

        return response()->json(['success' => true, 'data' => $data], 200);
    }

    /** Mitra premium aktif (4,5,6 dan belum kedaluwarsa) */
    private function isPremiumAktif($user): bool
    {
        return in_array((int) $user->jenis_akun_id_jenis_akun, [4, 5, 6], true)
            && (!$user->masa_berakhir || !now()->greaterThan(Carbon::parse($user->masa_berakhir)));
    }

    /** GET /api/mitra/toko/ulasan  (semua ulasan untuk toko milik mitra yang login) */
    public function ulasanMitra(Request $request)
    {
        $user = $request->user();
        if (!$this->isMitra($user)) {
            return response()->json(['success' => false, 'message' => 'Khusus akun Mitra.'], 403);
        }

        $toko = Toko::where('akun_id_akun', $user->id_akun)->first();
        if (!$toko) {
            return response()->json(['success' => false, 'message' => 'Data toko belum diisi.'], 404);
        }

        $data = DB::table('rating_ulasan')
            ->join('akun', 'akun.id_akun', '=', 'rating_ulasan.akun_id_akun')
            ->where('rating_ulasan.toko_id_toko', $toko->id_toko)
            ->orderByDesc('rating_ulasan.created_at')
            ->select('rating_ulasan.id_rating_ulasan', 'rating_ulasan.rating', 'rating_ulasan.ulasan',
                     'rating_ulasan.created_at', 'akun.nama_lengkap as nama')
            ->get();

        return response()->json(['success' => true, 'data' => $data], 200);
    }

    /** DELETE /api/mitra/toko/ulasan/{idUlasan}  (hanya Mitra Premium, hanya ulasan di tokonya sendiri) */
    public function hapusUlasanMitra(Request $request, $idUlasan)
    {
        $user = $request->user();
        if (!$this->isMitra($user)) {
            return response()->json(['success' => false, 'message' => 'Khusus akun Mitra.'], 403);
        }

        if (!$this->isPremiumAktif($user)) {
            return response()->json([
                'success' => false,
                'locked'  => true,
                'message' => 'Mohon maaf, tidak dapat dilakukan. Untuk akses lebih luas anda dapat bergabung ke Premium.',
            ], 403);
        }

        $toko = Toko::where('akun_id_akun', $user->id_akun)->first();
        if (!$toko) {
            return response()->json(['success' => false, 'message' => 'Data toko belum diisi.'], 404);
        }

        $deleted = DB::table('rating_ulasan')
            ->where('id_rating_ulasan', $idUlasan)
            ->where('toko_id_toko', $toko->id_toko)   // hanya ulasan milik tokonya sendiri
            ->delete();

        if (!$deleted) {
            return response()->json(['success' => false, 'message' => 'Ulasan tidak ditemukan.'], 404);
        }
        return response()->json(['success' => true, 'message' => 'Rating dan ulasan berhasil dihapus.'], 200);
    }

    /** Cek buka/tutup (WIB) berdasarkan hari tutup + jam operasional. */
    private function isOpen($toko): bool
    {
        $now  = Carbon::now('Asia/Jakarta');
        $hari = ['minggu', 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'][$now->dayOfWeek];
        $hariTutup = array_map(fn ($h) => strtolower(trim($h)), explode(',', (string) $toko->hari_tutup));
        if (in_array($hari, $hariTutup, true)) return false;

        $buka  = substr((string) $toko->jam_buka, 0, 5);
        $tutup = substr((string) $toko->jam_tutup, 0, 5);
        $cur   = $now->format('H:i');
        if ($buka === '' || $tutup === '') return false;

        return $buka <= $tutup ? ($cur >= $buka && $cur <= $tutup) : ($cur >= $buka || $cur <= $tutup);
    }
}