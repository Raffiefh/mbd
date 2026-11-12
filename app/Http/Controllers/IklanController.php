<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Pengajuan & penayangan Iklan (khusus Mitra Premium 4,5,6).
 * Data disimpan di tabel pembayaran dengan jenis_pembayaran 4..15 (= kursi iklan 1..12):
 *   snap_token = URL tujuan iklan | path_iklan = file gambar/GIF/video
 *   status_pembayaran: proses_verifikasi -> disetujui -> penayangan -> berakhir (atau ditolak)
 *
 * CATATAN: verifikasi admin disimulasikan otomatis 10 detik; pembayaran belum lewat payment gateway.
 */
class IklanController extends Controller
{
    private const KURSI = [
        4  => ['nama' => 'Urutan Pertama',    'harga' => 400000],
        5  => ['nama' => 'Urutan Kedua',      'harga' => 350000],
        6  => ['nama' => 'Urutan Ketiga',     'harga' => 325000],
        7  => ['nama' => 'Urutan Keempat',    'harga' => 300000],
        8  => ['nama' => 'Urutan Kelima',     'harga' => 275000],
        9  => ['nama' => 'Urutan Keenam',     'harga' => 250000],
        10 => ['nama' => 'Urutan Ketujuh',    'harga' => 225000],
        11 => ['nama' => 'Urutan Kedelapan',  'harga' => 200000],
        12 => ['nama' => 'Urutan Kesembilan', 'harga' => 175000],
        13 => ['nama' => 'Urutan Kesepuluh',  'harga' => 150000],
        14 => ['nama' => 'Urutan Kesebelas',  'harga' => 125000],
        15 => ['nama' => 'Urutan Terakhir',   'harga' => 100000],
    ];

    private const ST_PROSES = 'proses_verifikasi';
    private const ST_SETUJU = 'disetujui';
    private const ST_TAYANG = 'penayangan';
    private const ST_AKHIR  = 'berakhir';
    private const ST_TOLAK  = 'ditolak';

    private const LABEL = [
        'proses_verifikasi' => 'Proses Verifikasi Admin',
        'disetujui'         => 'Disetujui Admin',
        'penayangan'        => 'Penayangan',
        'berakhir'          => 'Berakhir',
        'ditolak'           => 'Ditolak',
    ];

    private const ALASAN_TOLAK = 'Kursi urutan ini masih dalam masa tayang. Pengajuan baru dapat dilakukan setelah masa tayangnya berakhir.';

    // ---------- helpers ----------
    private function c(string $k): string { return PremiumController::COL[$k]; }
    private function t(): string { return PremiumController::TBL_PEMBAYARAN; }
    private function today(): string { return Carbon::now('Asia/Jakarta')->toDateString(); }
    private function iklanQuery() { return DB::table($this->t())->whereBetween($this->c('jenis'), [4, 15]); }

    private function guard($user)
    {
        $premium = in_array((int) $user->jenis_akun_id_jenis_akun, [4, 5, 6], true)
            && (!$user->masa_berakhir || !now()->greaterThan(Carbon::parse($user->masa_berakhir)));
        if (!$premium) {
            return response()->json(['success' => false, 'locked' => true, 'message' => 'Fitur Pengajuan Iklan khusus Mitra Premium.'], 403);
        }
        return null;
    }

    /** Perbarui status otomatis: tayang yang lewat masa -> berakhir; proses >= 10 detik -> disetujui */
    private function sinkron(): void
    {
        $this->iklanQuery()->where($this->c('status'), self::ST_TAYANG)
            ->where($this->c('akhir'), '<', $this->today())
            ->update([$this->c('status') => self::ST_AKHIR]);

        $pending = $this->iklanQuery()->where($this->c('status'), self::ST_PROSES)->get();
        foreach ($pending as $r) {
            $key = 'iklan_submit_' . $r->{'id_pembayaran'};
            $ts  = Cache::get($key);
            if (!$ts || (now()->timestamp - (int) $ts) >= 10) {
                DB::table($this->t())->where('id_pembayaran', $r->{'id_pembayaran'})
                    ->where($this->c('status'), self::ST_PROSES)
                    ->update([$this->c('status') => self::ST_SETUJU]);
                Cache::forget($key);
            }
        }
    }

    private function mapRow($r): array
    {
        $kursi = (int) $r->{$this->c('jenis')};
        $harga = self::KURSI[$kursi]['harga'] ?? 1;
        $path  = $r->{$this->c('iklan')};
        $ext   = strtolower(pathinfo((string) $path, PATHINFO_EXTENSION));
        $st    = $r->{$this->c('status')};

        return [
            'id'              => $r->id_pembayaran,
            'kursi'           => $kursi,
            'kursi_nama'      => self::KURSI[$kursi]['nama'] ?? '-',
            'bulan'           => (int) round($r->{$this->c('jumlah')} / $harga),
            'jumlah'          => (float) $r->{$this->c('jumlah')},
            'status'          => $st,
            'status_label'    => self::LABEL[$st] ?? $st,
            'tanggal_mulai'   => $r->{$this->c('mulai')},
            'tanggal_berakhir' => $r->{$this->c('akhir')},
            'media_url'       => $path ? Storage::disk('s3')->url(ltrim($path, '/')) : null,
            'tipe'            => in_array($ext, ['mp4', 'webm'], true) ? 'video' : 'gambar',
            'url'             => $r->{$this->c('snap')},
            'alasan'          => $st === self::ST_TOLAK ? self::ALASAN_TOLAK : null,
        ];
    }

    // ---------- endpoints ----------

    /** GET /api/mitra/iklan/kursi */
    public function kursi(Request $request)
    {
        if ($deny = $this->guard($request->user())) return $deny;
        $this->sinkron();

        $tayang = $this->iklanQuery()->where($this->c('status'), self::ST_TAYANG)
            ->where($this->c('akhir'), '>=', $this->today())
            ->pluck($this->c('akhir'), $this->c('jenis'));

        $data = collect(self::KURSI)->map(fn ($k, $id) => [
            'kursi'        => $id,
            'nama'         => $k['nama'],
            'harga'        => $k['harga'],
            'sedang_tayang' => isset($tayang[$id]),
            'tayang_sampai' => $tayang[$id] ?? null,
        ])->values();

        return response()->json(['success' => true, 'data' => $data], 200);
    }

    /** GET /api/mitra/iklan/saya  -> iklan aktif (proses/disetujui/tayang) + riwayat (berakhir/ditolak) */
    public function saya(Request $request)
    {
        $user = $request->user();
        if ($deny = $this->guard($user)) return $deny;
        $this->sinkron();

        $rows = $this->iklanQuery()->where($this->c('akun'), $user->id_akun)->orderByDesc('id_pembayaran')->get();
        $aktif = $rows->first(fn ($r) => in_array($r->{$this->c('status')}, [self::ST_PROSES, self::ST_SETUJU, self::ST_TAYANG], true));
        $riwayat = $rows->filter(fn ($r) => in_array($r->{$this->c('status')}, [self::ST_AKHIR, self::ST_TOLAK], true))->values();

        return response()->json([
            'success' => true,
            'data' => [
                'aktif'   => $aktif ? $this->mapRow($aktif) : null,
                'riwayat' => $riwayat->map(fn ($r) => $this->mapRow($r))->values(),
            ],
        ], 200);
    }

    /** POST /api/mitra/iklan/ajukan (multipart) */
    public function ajukan(Request $request)
    {
        $user = $request->user();
        if ($deny = $this->guard($user)) return $deny;

        $request->validate([
            'kursi' => 'required|integer|between:4,15',
            'bulan' => 'required|integer|between:1,12',
            'url'   => ['required', 'url', 'max:255', 'regex:/^https?:\/\//i'],
            'media' => 'required|file|mimes:jpg,jpeg,png,gif,mp4,webm|max:15360',
        ], [
            'media.required' => 'File iklan wajib diunggah.',
            'media.mimes'    => 'File harus JPG, PNG, GIF, MP4, atau WebM.',
            'media.max'      => 'Ukuran file maksimal 15 MB.',
            'url.url'        => 'URL iklan tidak valid (awali dengan https://).',
            'url.regex'      => 'URL iklan harus diawali http:// atau https://.',
        ]);

        $this->sinkron();

        // 1 akun hanya boleh punya 1 iklan yang sedang berjalan
        $sudahAda = $this->iklanQuery()->where($this->c('akun'), $user->id_akun)
            ->whereIn($this->c('status'), [self::ST_PROSES, self::ST_SETUJU, self::ST_TAYANG])->exists();
        if ($sudahAda) {
            return response()->json(['success' => false, 'message' => 'Anda masih memiliki iklan yang sedang diproses atau tayang. Pengajuan baru dapat dilakukan setelah statusnya berakhir.'], 409);
        }

        // Kursi sedang masa tayang -> ditolak
        $kursi = (int) $request->kursi;
        $tayang = $this->iklanQuery()->where($this->c('jenis'), $kursi)->where($this->c('status'), self::ST_TAYANG)
            ->where($this->c('akhir'), '>=', $this->today())->first();
        if ($tayang) {
            return response()->json([
                'success' => false, 'ditolak' => true, 'status' => 'Ditolak',
                'message' => self::ALASAN_TOLAK . ' (Tayang sampai ' . $tayang->{$this->c('akhir')} . ')',
            ], 409);
        }

        $file = $request->file('media');
        if ($err = $this->cekMedia($file)) {
            return response()->json(['success' => false, 'message' => $err], 422);
        }

        $path  = $file->store('iklan_media', 's3');
        $today = $this->today();
        $harga = self::KURSI[$kursi]['harga'];
        $bulan = (int) $request->bulan;

        $id = DB::table($this->t())->insertGetId([
            $this->c('jumlah') => $harga * $bulan,
            $this->c('status') => self::ST_PROSES,
            $this->c('mulai')  => $today,
            $this->c('akhir')  => Carbon::parse($today)->addMonthsNoOverflow($bulan)->toDateString(),
            $this->c('snap')   => $request->url,    // URL tujuan iklan
            $this->c('iklan')  => $path,            // file media iklan
            $this->c('jenis')  => $kursi,
            $this->c('akun')   => $user->id_akun,
        ], 'id_pembayaran');

        Cache::put('iklan_submit_' . $id, now()->timestamp, now()->addDay());

        $row = DB::table($this->t())->where('id_pembayaran', $id)->first();
        return response()->json(['success' => true, 'message' => 'Pengajuan terkirim. Status: Proses Verifikasi Admin.', 'data' => $this->mapRow($row)], 201);
    }

    /** POST /api/mitra/iklan/{id}/bayar */
    public function bayar(Request $request, $id)
    {
        $user = $request->user();
        if ($deny = $this->guard($user)) return $deny;
        $this->sinkron();

        $hasil = DB::transaction(function () use ($user, $id) {
            $r = $this->iklanQuery()->where('id_pembayaran', $id)->where($this->c('akun'), $user->id_akun)->lockForUpdate()->first();
            if (!$r) return ['code' => 404, 'body' => ['success' => false, 'message' => 'Pengajuan tidak ditemukan.']];
            if ($r->{$this->c('status')} !== self::ST_SETUJU) {
                return ['code' => 422, 'body' => ['success' => false, 'message' => 'Pengajuan ini belum disetujui atau sudah dibayar.']];
            }

            $kursi = (int) $r->{$this->c('jenis')};
            $bentrok = $this->iklanQuery()->where($this->c('jenis'), $kursi)->where($this->c('status'), self::ST_TAYANG)
                ->where($this->c('akhir'), '>=', $this->today())->where('id_pembayaran', '!=', $r->id_pembayaran)->exists();
            if ($bentrok) {
                DB::table($this->t())->where('id_pembayaran', $r->id_pembayaran)->update([$this->c('status') => self::ST_TOLAK]);
                return ['code' => 409, 'body' => ['success' => false, 'ditolak' => true, 'status' => 'Ditolak', 'message' => self::ALASAN_TOLAK]];
            }

            $bulan = max(1, (int) round($r->{$this->c('jumlah')} / self::KURSI[$kursi]['harga']));
            $mulai = Carbon::parse($this->today());
            $akhir = $mulai->copy()->addMonthsNoOverflow($bulan);

            DB::table($this->t())->where('id_pembayaran', $r->id_pembayaran)->update([
                $this->c('status') => self::ST_TAYANG,
                $this->c('mulai')  => $mulai->toDateString(),
                $this->c('akhir')  => $akhir->toDateString(),
            ]);

            return ['code' => 200, 'body' => ['success' => true, 'message' => 'Pembayaran berhasil. Iklan Anda mulai tayang.', 'data' => ['tanggal_mulai' => $mulai->toDateString(), 'tanggal_berakhir' => $akhir->toDateString()]]];
        });

        return response()->json($hasil['body'], $hasil['code']);
    }

    /** GET /api/iklan/tayang  (semua akun yang login; urut kursi 4 -> 15, kursi kosong otomatis terlewati) */
    public function tayang()
    {
        $this->sinkron();
        $today = $this->today();

        $data = $this->iklanQuery()->where($this->c('status'), self::ST_TAYANG)
            ->where($this->c('mulai'), '<=', $today)->where($this->c('akhir'), '>=', $today)
            ->orderBy($this->c('jenis'))->get()
            ->map(function ($r) {
                $m = $this->mapRow($r);
                return ['id' => $m['id'], 'media_url' => $m['media_url'], 'tipe' => $m['tipe'], 'url' => $m['url']];
            })->values();

        return response()->json(['success' => true, 'data' => $data], 200);
    }

    // ---------- validasi media ----------
    private function cekMedia($file): ?string
    {
        $ext  = strtolower($file->getClientOriginalExtension());
        $path = $file->getRealPath();
        $msgRasio = 'Gambar/video wajib berformat A4 landscape (rasio 297:210, mis. 1754x1240 px).';
        $okRasio  = fn ($w, $h) => $h > 0 && ($w / $h) >= 1.371 && ($w / $h) <= 1.457;

        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'], true)) {
            $info = @getimagesize($path);
            if (!$info || !$okRasio($info[0], $info[1])) return $msgRasio;

            if ($ext === 'gif') {
                $data = file_get_contents($path);
                preg_match_all('/\x21\xF9\x04.(.)(.)/s', $data, $m);
                $cs = 0;
                foreach ($m[1] as $i => $lo) $cs += ord($lo) + 256 * ord($m[2][$i]);
                if ($cs > 305) return 'Durasi GIF maksimal 3 detik.';
            }
            return null;
        }

        // video: dicek dengan ffprobe bila tersedia di server (selain pengecekan di browser)
        $p = $this->probeVideo($path);
        if ($p) {
            if ($p['dur'] > 3.1) return 'Durasi video maksimal 3 detik.';
            if (!$okRasio($p['w'], $p['h'])) return $msgRasio;
        }
        return null;
    }

    private function probeVideo(string $path): ?array
    {
        $disabled = array_map('trim', explode(',', (string) ini_get('disable_functions')));
        if (!function_exists('shell_exec') || in_array('shell_exec', $disabled, true)) return null;
        $out = @shell_exec('ffprobe -v error -select_streams v:0 -show_entries stream=width,height:format=duration -of json ' . escapeshellarg($path) . ' 2>&1');
        $j = $out ? json_decode($out, true) : null;
        if (!$j || empty($j['streams'][0])) return null;
        return ['w' => (int) $j['streams'][0]['width'], 'h' => (int) $j['streams'][0]['height'], 'dur' => (float) ($j['format']['duration'] ?? 0)];
    }
}