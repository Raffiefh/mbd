<?php

namespace App\Http\Controllers;

use App\Models\Toko;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Analitik toko — KHUSUS Mitra Premium (jenis akun 4, 5, 6).
 * Mitra biasa (3) ditolak di sisi server, bukan hanya disembunyikan di tampilan.
 */
class AnalitikController extends Controller
{
    private const PREMIUM_IDS = [4, 5, 6];
    private const BULAN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                           'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    /** GET /api/mitra/analitik */
    public function index(Request $request)
    {
        $user = $request->user();

        $premium = in_array((int) $user->jenis_akun_id_jenis_akun, self::PREMIUM_IDS, true)
            && (!$user->masa_berakhir || !now()->greaterThan(Carbon::parse($user->masa_berakhir)));

        if (!$premium) {
            return response()->json([
                'success' => false,
                'locked'  => true,
                'message' => 'Fitur Analitik khusus Mitra Premium.',
            ], 403);
        }

        $toko = Toko::where('akun_id_akun', $user->id_akun)->first();
        if (!$toko) {
            return response()->json(['success' => false, 'message' => 'Data toko belum diisi.'], 404);
        }

        $now   = now();
        $tahun = $now->year;
        $bulanIni = $now->month;

        // Klik dari akun lain, 3 tahun terakhir (klik pemilik sendiri memang tidak dicatat)
        $waktu = DB::table('analitik')
            ->where('toko_id_toko', $toko->id_toko)
            ->where('akun_id_akun', '!=', $user->id_akun)
            ->where('diklik_pada', '>=', Carbon::create($tahun - 2, 1, 1)->startOfDay())
            ->pluck('diklik_pada');

        $perTahun  = [$tahun => 0, $tahun - 1 => 0, $tahun - 2 => 0];
        $bulanIniArr = array_fill(1, 12, 0);   // tahun ini
        $bulanLaluArr = array_fill(1, 12, 0);  // tahun lalu (untuk pertumbuhan Jan & YoY)

        foreach ($waktu as $w) {
            $c = Carbon::parse($w);
            if (isset($perTahun[$c->year])) $perTahun[$c->year]++;
            if ($c->year === $tahun)     $bulanIniArr[$c->month]++;
            if ($c->year === $tahun - 1) $bulanLaluArr[$c->month]++;
        }

        // Pertumbuhan: (sekarang - sebelumnya) / sebelumnya x 100. Sebelumnya 0 & sekarang >0 => null ("Baru")
        $growth = fn ($cur, $prev) => $prev > 0 ? round(($cur - $prev) / $prev * 100, 1) : ($cur > 0 ? null : 0.0);

        $bulanan = [];
        for ($m = 1; $m <= 12; $m++) {
            $prev = $m === 1 ? $bulanLaluArr[12] : $bulanIniArr[$m - 1];
            $bulanan[] = [
                'bulan'      => self::BULAN[$m - 1],
                'total_klik' => $bulanIniArr[$m],
                'pertumbuhan_persen' => $m > $bulanIni ? null : $growth($bulanIniArr[$m], $prev),
                'akan_datang' => $m > $bulanIni,
            ];
        }

        // YoY adil: bandingkan Januari..bulan ini dengan periode yang sama tahun lalu
        $ytdSekarang = array_sum(array_slice($bulanIniArr, 0, $bulanIni));
        $ytdLalu     = array_sum(array_slice($bulanLaluArr, 0, $bulanIni));

        $avg = DB::table('rating_ulasan')->where('toko_id_toko', $toko->id_toko)->avg('rating');

        return response()->json([
            'success' => true,
            'data' => [
                'tahun'            => $tahun,
                'nama_toko'        => $toko->nama_toko,
                'bulanan'          => $bulanan,
                'tahunan'          => collect($perTahun)->map(fn ($v, $k) => ['tahun' => $k, 'total_klik' => $v])->values(),
                'rating_rata_rata' => $avg === null ? 5.0 : round((float) $avg, 1),   // default 5
                'jumlah_rating'    => DB::table('rating_ulasan')->where('toko_id_toko', $toko->id_toko)->count(),
                'total_disimpan'   => DB::table(TokoDetailController::TBL_FAVORIT)->where('toko_id_toko', $toko->id_toko)->count(),
                'pertumbuhan_tahunan_persen' => $growth($ytdSekarang, $ytdLalu),
                'bulan_sekarang'   => $bulanIni,
            ],
        ], 200);
    }
}