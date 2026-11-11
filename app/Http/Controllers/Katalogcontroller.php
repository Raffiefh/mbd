<?php

namespace App\Http\Controllers;

use App\Models\Toko;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KatalogController extends Controller
{
    private const PREMIUM_IDS = [4, 5, 6]; // Mitra_P1, Mitra_P3, Mitra_P6
    private const MITRA_IDS   = [3, 4, 5, 6];
    private const TZ          = 'Asia/Jakarta';

    /**
     * GET /api/katalog?filter=rekomendasi|terdekat|general&lat=..&lng=..&page=1
     *
     * rekomendasi : hanya jenis akun 4,5,6  -> diurutkan terdekat (jika lat/lng ada)
     * terdekat    : jenis akun 3,4,5,6      -> diurutkan terdekat dari user
     * general     : semua toko              -> urutan data keseluruhan
     */
    public function index(Request $request)
    {
        $request->validate([
            'filter' => 'nullable|in:rekomendasi,terdekat,general',
            'lat'    => 'nullable|numeric|between:-90,90',
            'lng'    => 'nullable|numeric|between:-180,180',
            'q'      => 'nullable|string|max:100',
            'kategori'   => 'nullable|array',
            'kategori.*' => 'integer',
        ]);

        $filter   = $request->input('filter', 'rekomendasi');
        $hasCoord = $request->filled('lat') && $request->filled('lng');
        $lat      = (float) $request->lat;
        $lng      = (float) $request->lng;

        // ---- Parameter pencarian ----
        $q           = trim((string) $request->input('q', ''));
        $kategoriIds = array_values(array_filter((array) $request->input('kategori', []), fn ($v) => $v !== null && $v !== ''));
        $isSearch    = $q !== '' || count($kategoriIds) > 0;

        $tokoTable = (new Toko)->getTable();

        $query = Toko::query()
            ->join('akun', 'akun.id_akun', '=', "{$tokoTable}.akun_id_akun")
            ->with(['gambarToko', 'promo'])
            ->select("{$tokoTable}.*", 'akun.jenis_akun_id_jenis_akun as jenis_akun_id');

        // ---- Aturan filter ----
        if ($filter === 'rekomendasi') {
            $query->whereIn('akun.jenis_akun_id_jenis_akun', self::PREMIUM_IDS);
        } elseif ($filter === 'terdekat') {
            $query->whereIn('akun.jenis_akun_id_jenis_akun', self::MITRA_IDS);
        }

        // ---- Pencarian: nama toko & kategori (cocok salah satu kategori yang dipilih) ----
        if ($q !== '') {
            $query->where("{$tokoTable}.nama_toko", 'like', '%' . addcslashes($q, '%_\\') . '%');
        }
        if (count($kategoriIds) > 0) {
            $query->whereHas('kategori', function ($w) use ($kategoriIds) {
                $w->whereIn('kategori.id_kategori', $kategoriIds);
            });
        }

        // ---- Aturan urutan ----
        // Jarak dihitung di PHP (bukan di SQL) agar jalan di semua jenis database.
        // Mode pencarian + GPS aktif (lat/lng terkirim) -> hasil diurutkan terdekat
        $sortedByDistance = $hasCoord && ($filter !== 'general' || $isSearch);
        $perPage = 10;

        if ($sortedByDistance) {
            $all = $query->get()
                ->map(function ($t) use ($lat, $lng) {
                    $t->jarak_km = $this->haversine($lat, $lng, $t->lattitude, $t->longtitude);
                    return $t;
                })
                ->sort(function ($a, $b) {
                    // toko tanpa koordinat di paling bawah, sisanya dari yang terdekat
                    return (($a->jarak_km === null) <=> ($b->jarak_km === null))
                        ?: ($a->jarak_km <=> $b->jarak_km);
                })
                ->values();

            $currentPage = max(1, (int) $request->input('page', 1));
            $total       = $all->count();
            $lastPage    = max(1, (int) ceil($total / $perPage));
            $items       = $all->forPage($currentPage, $perPage);
        } else {
            $paginator   = $query->orderBy("{$tokoTable}.id_toko")->paginate($perPage);
            $items       = $paginator->getCollection();
            $currentPage = $paginator->currentPage();
            $lastPage    = $paginator->lastPage();
            $total       = $paginator->total();
        }

        // Rata-rata rating per toko (default 5 jika belum ada rating)
        $ratings = DB::table('rating_ulasan')
            ->whereIn('toko_id_toko', $items->pluck('id_toko')->all())
            ->select('toko_id_toko', DB::raw('AVG(rating) as avg_rating'))
            ->groupBy('toko_id_toko')
            ->pluck('avg_rating', 'toko_id_toko');

        $data = $items->map(function ($t) use ($ratings) {
            $gambar = $t->gambarToko->first();
            $jenis  = (int) $t->jenis_akun_id;

            return [
                'id_toko'       => $t->id_toko,
                'nama_toko'     => $t->nama_toko,
                'gambar_utama'  => $gambar ? asset('storage/' . ltrim($gambar->path_gambar, '/')) : null,
                'jam_buka'      => $t->jam_buka,
                'jam_tutup'     => $t->jam_tutup,
                'harga_mulai'   => $t->harga_mulai,
                'status_buka'   => $this->isOpen($t),
                'ada_promo'     => $t->promo && (int) $t->promo->keadaan_promo === 1,
                'jenis_akun_id' => $jenis,
                'is_premium'    => in_array($jenis, self::PREMIUM_IDS, true),
                'rating_rata_rata' => isset($ratings[$t->id_toko]) ? round((float) $ratings[$t->id_toko], 1) : 5.0,
                'jarak_km'      => isset($t->jarak_km) ? round((float) $t->jarak_km, 1) : null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data'    => $data,
            'meta'    => [
                'filter'              => $filter,
                'sorted_by_distance'  => $sortedByDistance,
                'search'              => $isSearch,
                'diterima'            => ['q' => $q, 'kategori' => $kategoriIds, 'lat' => $hasCoord ? $lat : null, 'lng' => $hasCoord ? $lng : null],
                'current_page'        => $currentPage,
                'last_page'           => $lastPage,
                'total'               => $total,
            ],
        ], 200);
    }

    /** Jarak (km) dua titik koordinat; null jika koordinat toko tidak valid. */
    private function haversine($lat1, $lng1, $lat2, $lng2): ?float
    {
        if (!is_numeric($lat2) || !is_numeric($lng2)) {
            return null;
        }
        $lat2 = (float) $lat2;
        $lng2 = (float) $lng2;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 6371 * 2 * asin(min(1, sqrt($a)));
    }

    /** Cek buka/tutup berdasarkan hari tutup + jam operasional (WIB). */
    private function isOpen($toko): bool
    {
        $now  = Carbon::now(self::TZ);
        $hari = ['minggu', 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu'][$now->dayOfWeek];

        $hariTutup = array_map(fn ($h) => strtolower(trim($h)), explode(',', (string) $toko->hari_tutup));
        if (in_array($hari, $hariTutup, true)) {
            return false;
        }

        $buka  = substr((string) $toko->jam_buka, 0, 5);
        $tutup = substr((string) $toko->jam_tutup, 0, 5);
        $cur   = $now->format('H:i');

        if ($buka === '' || $tutup === '') {
            return false;
        }

        // Jam normal (09:00-22:00) atau lewat tengah malam (18:00-02:00)
        return $buka <= $tutup
            ? ($cur >= $buka && $cur <= $tutup)
            : ($cur >= $buka || $cur <= $tutup);
    }
}