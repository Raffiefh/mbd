<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Pembelian Premium untuk Mitra.
 * Harga & durasi SELALU diambil dari konfigurasi di bawah (server), bukan dari kiriman browser.
 *
 * CATATAN: belum ada payment gateway. Endpoint "beli" langsung menandai pembayaran "selesai".
 * Sebelum dipakai uang sungguhan, sambungkan ke gateway (mis. Midtrans -> kolom snap_token).
 */
class PremiumController extends Controller
{
    // >>> SESUAIKAN dengan nama tabel & kolom pembayaran di database kamu <<<
    public const TBL_PEMBAYARAN = 'pembayaran';
    public const COL = [
        'jumlah' => 'jumlah_pembayaran',
        'status' => 'status_pembayaran',
        'mulai'  => 'tanggal_mulai',
        'akhir'  => 'tanggal_berakhir',
        'snap'   => 'snap_token',
        'iklan'  => 'path_iklan',
        'jenis'  => 'jenis_pembayaran_id_jenis_pembayaran', // nama terpotong di gambar ERD; cek di database
        'akun'   => 'akun_id_akun',
    ];
    private const STATUS_SELESAI = 'selesai';

    private const MITRA_IDS    = [3, 4, 5, 6];
    private const PREMIUM_IDS  = [4, 5, 6];

    // jenis_akun: 4 = 1 bulan, 5 = 3 bulan, 6 = 6 bulan | jenis_pembayaran: 1, 2, 3
    private const PAKET = [
        'mingguan' => ['nama' => 'Premium Mingguan', 'harga_normal' => 30000,  'harga' => 30000,  'bulan' => 0, 'jenis_akun' => null, 'jenis_pembayaran' => null, 'tersedia' => false],
        'bulan_1'  => ['nama' => 'Premium 1 Bulan',  'harga_normal' => 100000, 'harga' => 49000,  'bulan' => 1, 'jenis_akun' => 4, 'jenis_pembayaran' => 1, 'tersedia' => true],
        'bulan_3'  => ['nama' => 'Premium 3 Bulan',  'harga_normal' => 240000, 'harga' => 147000, 'bulan' => 3, 'jenis_akun' => 5, 'jenis_pembayaran' => 2, 'tersedia' => true],
        'bulan_6'  => ['nama' => 'Premium 6 Bulan',  'harga_normal' => 410000, 'harga' => 294000, 'bulan' => 6, 'jenis_akun' => 6, 'jenis_pembayaran' => 3, 'tersedia' => true],
    ];

    private function deny($user)
    {
        if (!in_array((int) $user->jenis_akun_id_jenis_akun, self::MITRA_IDS, true)) {
            return response()->json(['success' => false, 'message' => 'Pembelian Premium khusus akun Mitra.'], 403);
        }
        return null;
    }

    private function premiumAktif($akun): bool
    {
        return in_array((int) $akun->jenis_akun_id_jenis_akun, self::PREMIUM_IDS, true)
            && $akun->masa_berakhir
            && Carbon::parse($akun->masa_berakhir)->greaterThan(now());
    }

    /** GET /api/mitra/premium/paket */
    public function paket(Request $request)
    {
        $user = $request->user();
        if ($deny = $this->deny($user)) return $deny;

        $paket = collect(self::PAKET)->map(fn ($p, $key) => [
            'key'          => $key,
            'nama'         => $p['nama'],
            'harga_normal' => $p['harga_normal'],
            'harga'        => $p['harga'],
            'hemat_persen' => $p['tersedia'] ? (int) round((1 - $p['harga'] / $p['harga_normal']) * 100) : 0,
            'durasi_bulan' => $p['bulan'],
            'tersedia'     => $p['tersedia'],
        ])->values();

        return response()->json([
            'success' => true,
            'data' => [
                'paket'          => $paket,
                'premium_aktif'  => $this->premiumAktif($user),
                'jenis_akun_id'  => (int) $user->jenis_akun_id_jenis_akun,
                'masa_berakhir'  => $this->premiumAktif($user) ? (string) $user->masa_berakhir : null,
            ],
        ], 200);
    }

    /** POST /api/mitra/premium/beli  {paket: bulan_1 | bulan_3 | bulan_6} */
    public function beli(Request $request)
    {
        $user = $request->user();
        if ($deny = $this->deny($user)) return $deny;

        $request->validate([
            'paket' => 'required|string|in:' . implode(',', array_keys(self::PAKET)),
        ], [
            'paket.required' => 'Silakan pilih paket premium terlebih dahulu.',
            'paket.in'       => 'Paket tidak valid.',
        ]);

        $p = self::PAKET[$request->paket];
        if (!$p['tersedia']) {
            return response()->json(['success' => false, 'message' => 'Mohon maaf, paket ini sedang habis (sold out).'], 422);
        }

        try {
            $hasil = DB::transaction(function () use ($user, $p) {
                // Kunci baris akun supaya klik ganda tidak menambah masa aktif dua kali
                $akun = DB::table('akun')->where('id_akun', $user->id_akun)->lockForUpdate()->first();
                $now  = now();

                if ($this->premiumAktif($akun)) {
                    // Perpanjang: masa_awal TETAP, masa_berakhir ditambah sesuai paket
                    $masaAwal    = Carbon::parse($akun->masa_awal);
                    $masaBerakhir = Carbon::parse($akun->masa_berakhir)->addMonthsNoOverflow($p['bulan']);
                } else {
                    // Baru / sudah kedaluwarsa: mulai hari ini
                    $masaAwal    = $now->copy();
                    $masaBerakhir = $now->copy()->addMonthsNoOverflow($p['bulan']);
                }

                DB::table('akun')->where('id_akun', $akun->id_akun)->update([
                    'jenis_akun_id_jenis_akun' => $p['jenis_akun'],
                    'masa_awal'                => $masaAwal->format('Y-m-d H:i:s'),
                    'masa_berakhir'            => $masaBerakhir->format('Y-m-d H:i:s'),
                ]);

                $c = self::COL;
                DB::table(self::TBL_PEMBAYARAN)->insert([
                    $c['jumlah'] => $p['harga'],
                    $c['status'] => self::STATUS_SELESAI,
                    $c['mulai']  => $now->format('Y-m-d'),                    // tanggal pembelian (kolom bertipe date)
                    $c['akhir']  => $masaBerakhir->format('Y-m-d'),           // = tanggal masa_berakhir akun
                    $c['snap']   => null,
                    $c['iklan']  => null,
                    $c['jenis']  => $p['jenis_pembayaran'],
                    $c['akun']   => $akun->id_akun,
                ]);

                return [
                    'jenis_akun_id' => $p['jenis_akun'],
                    'masa_awal'     => $masaAwal->format('Y-m-d H:i:s'),
                    'masa_berakhir' => $masaBerakhir->format('Y-m-d H:i:s'),
                ];
            });
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pembelian gagal diproses: ' . $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pembelian Premium anda berhasil',
            'data'    => $hasil + ['paket' => $p['nama'], 'jumlah' => $p['harga']],
        ], 200);
    }
}