<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Toko;
use App\Models\DetailPromo;
use App\Models\GambarToko;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TokoController extends Controller
{
    private const PESAN_GAMBAR   = 'Teruntuk akses gambar yang lebih luas anda dapat bergabung ke premium kami ⭐';
    private const PESAN_KATEGORI = 'Teruntuk jumlah kategori yang bervariatif anda dapat bergabung ke premium kami ⭐';

    /**
     * Batas per jenis akun (ID 3 = Mitra reguler, ID 4/5/6 = Mitra Premium).
     * Angka sengaja tidak pernah ditampilkan ke user.
     */
    private function batas($jenisAkunId): array
    {
        $premium = in_array($jenisAkunId, [4, 5, 6]);
        return [
            'premium'  => $premium,
            'gambar'   => $premium ? 12 : 4,
            'kategori' => $premium ? 14 : 8,
        ];
    }

    public function index()
    {
        $user = auth()->user();

        $toko = Toko::with(['gambarToko', 'promo', 'kategori'])
                    ->where('akun_id_akun', $user->id_akun)
                    ->first();

        if (!$toko) {
            return response()->json([
                'success' => false,
                'message' => 'Data toko belum diisi.',
                'data'    => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $toko
        ], 200);
    }
    /**
     * GET: Halaman awal pengisian data toko setelah register
     */
    public function create()
    {
        $user = auth()->user();
        $tokoExist = Toko::where('akun_id_akun', $user->id_akun)->first();

        if ($tokoExist) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah memiliki toko.'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Silakan isi data toko.'
        ], 200);
    }

    /**
     * POST: Menyimpan data toko baru setelah register
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $jenisAkunId = $user->jenis_akun_id_jenis_akun;
        
        if (Toko::where('akun_id_akun', $user->id_akun)->exists()) {
        return response()->json([
            'success' => false,
            'message' => 'Anda sudah memiliki toko, gunakan fitur perbarui.'
        ], 400);
        }

        $batas = $this->batas($jenisAkunId);

        $request->validate([
            'nama_toko'         => 'required|string|max:100',
            'deskripsi_toko'    => 'required|string',
            'garis_lintang'     => 'required|numeric',
            'garis_bujur'       => 'required|numeric',
            'deskripsi_voucher' => 'nullable|string',
            'jam_buka'          => 'required',
            'jam_tutup'         => 'required',
            'hari_tutup'        => 'required|array', // Pilihan centang hari
            'harga_mulai'       => 'required|numeric|min:0',
            
            // Upload Gambar (Wajib min 1)
            'gambar_toko'       => 'required|array|min:1|max:' . $batas['gambar'],
            'gambar_toko.*'     => 'image|mimes:jpeg,png,jpg|max:2048',

            // Promo (Opsional)
            'deskripsi_promo'   => 'nullable|string',
            'keadaan_promo'     => 'nullable|integer',
            'jalur_promo'       => 'nullable|image|mimes:jpeg,png,jpg|max:2048',

            // Kategori (Wajib centang minimal 1)
            'kategori_id'       => 'required|array|min:1|max:' . $batas['kategori'],
            'kategori_id.*'     => 'exists:kategori,id_kategori'
        ], [
            'gambar_toko.max'      => $batas['premium']
                ? 'Jumlah foto sudah mencapai batas untuk akun Anda.'
                : self::PESAN_GAMBAR,
            'kategori_id.max'      => $batas['premium']
                ? 'Jumlah kategori sudah mencapai batas untuk akun Anda.'
                : self::PESAN_KATEGORI,
            'kategori_id.required' => 'Anda wajib memilih minimal 1 kategori toko.'
        ]);

        try {
            DB::beginTransaction();

            // 1. Simpan Promo jika diisi
            $promoId = null;
            if ($request->filled('deskripsi_promo') || $request->hasFile('jalur_promo')) {
                $pathPromo = null;
                if ($request->hasFile('jalur_promo')) {
                    $pathPromo = $request->file('jalur_promo')->store('promo_images', 's3');
                }

                $promo = DetailPromo::create([
                    'deskripsi_promo' => $request->deskripsi_promo,
                    'path_promo'      => $pathPromo, // Disesuaikan dengan skema database
                    'keadaan_promo'   => $request->keadaan_promo ?? 1
                ]);
                $promoId = $promo->id_detail_promo;
            }

            $hariTutupString = implode(', ', $request->hari_tutup);

            // 2. Simpan Toko
            $toko = Toko::create([
                'nama_toko'                    => $request->nama_toko,
                'deskripsi_toko'               => $request->deskripsi_toko,
                'lattitude'                    => $request->garis_lintang, // Disesuaikan dengan skema database
                'longtitude'                   => $request->garis_bujur,   // Disesuaikan dengan skema database
                'deskripsi_voucher'            => $request->deskripsi_voucher,
                'jam_buka'                     => $request->jam_buka,
                'jam_tutup'                    => $request->jam_tutup,
                'hari_tutup'                   => $hariTutupString,
                'harga_mulai'                  => $request->harga_mulai,
                'detail_promo_id_detail_promo' => $promoId,
                'akun_id_akun'                 => $user->id_akun
            ]);

            // 3. Simpan Gambar Toko
            if ($request->hasFile('gambar_toko')) {
                foreach ($request->file('gambar_toko') as $file) {
                    $pathGambar = $file->store('toko_images', 's3');
                    GambarToko::create([
                        'path_gambar'  => $pathGambar, // Disesuaikan dengan skema database (path_gambar)
                        'toko_id_toko' => $toko->id_toko
                    ]);
                }
            }

            // 4. Simpan Kategori via Pivot
            if ($request->filled('kategori_id')) {
                $toko->kategori()->sync($request->kategori_id);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data toko berhasil disimpan!',
                'data'    => $toko->load(['gambarToko', 'promo', 'kategori'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST: Memperbarui data toko secara otomatis berdasarkan token user yang login
     */
    public function update(Request $request)
    {
        $user = auth()->user();
        
        // Otomatis mencari toko berdasarkan akun mitra yang sedang login
        $toko = Toko::where('akun_id_akun', $user->id_akun)->first();

        if (!$toko) {
            return response()->json([
                'success' => false,
                'message' => 'Data toko belum ditemukan.'
            ], 404);
        }

        $batas    = $this->batas($user->jenis_akun_id_jenis_akun);
        $hapusIds = $request->input('hapus_gambar_id', []);

        $request->validate([
            'nama_toko'         => 'required|string|max:100',
            'deskripsi_toko'    => 'required|string',
            'garis_lintang'     => 'required|numeric',
            'garis_bujur'       => 'required|numeric',
            'jam_buka'          => 'required',
            'jam_tutup'         => 'required',
            'hari_tutup'        => 'required|array',
            'harga_mulai'       => 'required|numeric|min:0',
            'gambar_toko'       => 'nullable|array',
            'gambar_toko.*'     => 'image|mimes:jpeg,png,jpg|max:2048',
            'hapus_gambar_id'   => 'nullable|array',
            'hapus_gambar_id.*' => 'integer',
            'kategori_id'       => 'required|array|min:1|max:' . $batas['kategori']
        ], [
            'kategori_id.max' => $batas['premium']
                ? 'Jumlah kategori sudah mencapai batas untuk akun Anda.'
                : self::PESAN_KATEGORI,
        ]);

        // Hitung TOTAL foto setelah update (foto yang tersisa + foto baru), bukan hanya foto baru
        $sisaFoto  = $toko->gambarToko()->whereNotIn('id_gambar_toko', $hapusIds)->count();
        $totalFoto = $sisaFoto + count($request->file('gambar_toko', []));

        if ($totalFoto > $batas['gambar']) {
            return response()->json([
                'success' => false,
                'locked'  => true,
                'message' => $batas['premium']
                    ? 'Jumlah foto sudah mencapai batas untuk akun Anda.'
                    : self::PESAN_GAMBAR,
            ], 403);
        }

        if ($totalFoto < 1) {
            return response()->json([
                'success' => false,
                'message' => 'Toko wajib memiliki minimal 1 foto.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Update Promo
            if ($toko->detail_promo_id_detail_promo) {
                $promo = DetailPromo::find($toko->detail_promo_id_detail_promo);
                if ($promo) {
                    $pathPromo = $promo->path_promo; 
                    if ($request->hasFile('jalur_promo')) {
                        if ($pathPromo && Storage::disk('s3')->exists($pathPromo)) {
                            Storage::disk('s3')->delete($pathPromo);
                        }
                        $pathPromo = $request->file('jalur_promo')->store('promo_images', 's3');
                    }
                    $promo->update([
                        'deskripsi_promo' => $request->deskripsi_promo,
                        'path_promo'      => $pathPromo, 
                        'keadaan_promo'   => $request->keadaan_promo ?? $promo->keadaan_promo
                    ]);
                }
            } else if ($request->filled('deskripsi_promo') || $request->hasFile('jalur_promo')) {
                $pathPromo = null;
                if ($request->hasFile('jalur_promo')) {
                    $pathPromo = $request->file('jalur_promo')->store('promo_images', 's3');
                }
                $promo = DetailPromo::create([
                    'deskripsi_promo' => $request->deskripsi_promo,
                    'path_promo'      => $pathPromo, 
                    'keadaan_promo'   => $request->keadaan_promo ?? 1
                ]);
                $toko->detail_promo_id_detail_promo = $promo->id_detail_promo;
            }

            // Update Data Toko
            $toko->update([
                'nama_toko'         => $request->nama_toko,
                'deskripsi_toko'    => $request->deskripsi_toko,
                'lattitude'         => $request->garis_lintang, 
                'longtitude'        => $request->garis_bujur,   
                'deskripsi_voucher' => $request->deskripsi_voucher,
                'jam_buka'          => $request->jam_buka,
                'jam_tutup'         => $request->jam_tutup,
                'hari_tutup'        => implode(', ', $request->hari_tutup),
                'harga_mulai'       => $request->harga_mulai,
                'detail_promo_id_detail_promo' => $toko->detail_promo_id_detail_promo
            ]);

            // Hapus gambar yang ditandai user (file fisik + record)
            if (!empty($hapusIds)) {
                $gambarHapus = GambarToko::where('toko_id_toko', $toko->id_toko)
                    ->whereIn('id_gambar_toko', $hapusIds)
                    ->get();
                foreach ($gambarHapus as $g) {
                    Storage::disk('s3')->delete($g->path_gambar);
                    $g->delete();
                }
            }

            // Tambah gambar baru jika diupload
            if ($request->hasFile('gambar_toko')) {
                foreach ($request->file('gambar_toko') as $file) {
                    $pathGambar = $file->store('toko_images', 's3');
                    GambarToko::create([
                        'path_gambar'  => $pathGambar, 
                        'toko_id_toko' => $toko->id_toko
                    ]);
                }
            }

            // Sync Kategori
            if ($request->filled('kategori_id')) {
                $toko->kategori()->sync($request->kategori_id);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data toko berhasil diperbarui!',
                'data'    => $toko->load(['gambarToko', 'promo', 'kategori'])
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui: ' . $e->getMessage()
            ], 500);
        }
    }
}