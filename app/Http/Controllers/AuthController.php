<?php

namespace App\Http\Controllers;

use App\Models\Akun;
use App\Mail\SendOtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash; 
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    // 1. Proses Register (Menyimpan akun baru, password, OTP, & masa aktif 10 tahun)
    public function register(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|min:3|max:60',
            'email' => 'required|email|unique:akun,email',
            'no_telefon' => 'required|numeric|digits_between:10,13|unique:akun,no_telefon',
            'password' => 'required|string|min:8|max:20',
            'jenis_akun_id_jenis_akun' => 'required|integer',
        ], [
            'nama_lengkap.min' => 'Nama lengkap minimal harus 3 karakter.',
            'nama_lengkap.max' => 'Nama lengkap maksimal 60 karakter.',
            'password.min' => 'Password minimal harus 8 karakter.',
            'password.max' => 'Password maksimal 20 karakter.',
            'email.unique' => 'Alamat email ini sudah terdaftar.',
            'no_telefon.digits_between' => 'Nomor telepon harus terdiri dari 10 hingga 13 digit.', 
            'no_telefon.numeric' => 'Nomor telepon hanya boleh berisi angka.',
            'no_telefon.unique' => 'Nomor telepon ini sudah terdaftar.',
        ]);

        $otpToken = rand(100000, 999999);

        $akun = Akun::create([
            'nama_lengkap' => $request->nama_lengkap,
            'email' => $request->email,
            'no_telefon' => $request->no_telefon,
            'password' => Hash::make($request->password),
            'jenis_akun_id_jenis_akun' => $request->jenis_akun_id_jenis_akun,
            'token' => $otpToken,
            'masa_awal' => now(),
            'masa_berakhir' => now()->addYears(10),
            'path_gambar' => 'default.png',
        ]);

        Mail::to($akun->email)->send(new SendOtpMail($otpToken));

        return response()->json([
            'message' => 'Registrasi anda berhasil! Silakan cek email untuk kode OTP verifikasi.',
            'data' => $akun
        ], 200);
    }

    // 2. Verifikasi Token OTP / Aktivasi Akun (Batas 10 Menit)
    public function verifyToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|integer',
        ]);

        $akun = Akun::with('jenisAkun')->where('email', $request->email)->first();

        if (!$akun) {
            return response()->json(['message' => 'Akun dengan email tersebut tidak ditemukan!'], 404);
        }

        if ($akun->token == 0) {
            return response()->json(['message' => 'Akun ini sudah pernah diverifikasi sebelumnya.'], 400);
        }

        if (now()->diffInMinutes($akun->masa_awal) > 10) {
            $akun->delete();
            return response()->json([
                'message' => 'Token OTP sudah kedaluwarsa (lebih dari 10 menit)! Data registrasi dibatalkan, silakan daftar ulang.'
            ], 400);
        }

        if ($akun->token != $request->token) {
            return response()->json(['message' => 'Token verifikasi salah!'], 400);
        }

        $akun->update(['token' => 0]);

        $tokenResult = $akun->createToken('nongkrongid_auth_token')->plainTextToken;

        // === PENENTUAN REDIRECT BERDASARKAN ID JENIS AKUN ===
        $jenisAkunId = $akun->jenis_akun_id_jenis_akun; // Sesuaikan dengan nama kolom foreign key di model/tabel Anda
        $redirectTo = '/dashboard/user'; // Default untuk User biasa (ID 2)

        if ($jenisAkunId == 1) {
            $redirectTo = '/dashboard/admin';
        } elseif ($jenisAkunId >= 3 && $jenisAkunId <= 6) {
            $redirectTo = '/dashboard/mitra'; // Mencakup Mitra (3) & Mitra Premium (4, 5, 6)
        } elseif ($jenisAkunId == 7) {
            $redirectTo = '/dashboard/sponsor';
        }
        // ====================================================

        if ($jenisAkunId == 2) {
            Cache::put("user_profile_{$akun->id_akun}", $akun, now()->addHours(24));
        }

        return response()->json([
            'message' => 'Akun berhasil diverifikasi dan diaktifkan!',
            'access_token' => $tokenResult,
            'token_type' => 'Bearer',
            'redirect_to' => $redirectTo,
            'data' => $akun
        ], 200);
    }

    // 3. Kirim Ulang OTP (Resend OTP dengan Proteksi Cooldown 1 Menit)
    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $akun = Akun::where('email', $request->email)->first();

        if (!$akun) {
            return response()->json(['message' => 'Email tidak terdaftar!'], 404);
        }

        if ($akun->token == 0) {
            return response()->json(['message' => 'Akun ini sudah aktif dan terverifikasi.'], 400);
        }

        // Hitung selisih detik menggunakan timestamp unix agar akurat bebas masalah timezone
        $waktuAwal = strtotime($akun->masa_awal);
        $waktuSekarang = time();
        $selisihDetik = $waktuSekarang - $waktuAwal;

        if ($selisihDetik < 60) {
            $sisaWaktu = ceil(60 - $selisihDetik);
            
            return response()->json([
                'message' => "Harap tunggu {$sisaWaktu} detik lagi sebelum meminta ulang kode OTP."
            ], 429);
        }

        $newOtpToken = rand(100000, 999999);
        
        $akun->update([
            'token' => $newOtpToken,
            'masa_awal' => now(),
        ]);

        Mail::to($akun->email)->send(new SendOtpMail($newOtpToken));

        return response()->json([
            'message' => 'Kode OTP baru telah dikirim ulang ke email Anda.',
        ], 200);
    }

    // 4. Logout
    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            if ($user->jenis_akun_id_jenis_akun == 2) {
                Cache::forget("user_profile_{$user->id_akun}");
            }
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'Anda berhasil keluar',
        ], 200);
    }

    // 5. Login (Bypass OTP untuk Regular User dan Generate Cache)
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $akun = Akun::with('jenisAkun')->where('email', $request->email)->first();

        if (!$akun || !Hash::check($request->password, $akun->password)) {
            return response()->json(['message' => 'Email atau password salah!'], 401);
        }

        // Perbaikan logika: Cek apakah akun belum pernah diverifikasi sama sekali
        if ($akun->token != 0 && now()->diffInMinutes($akun->masa_awal) > 10) {
            return response()->json(['message' => 'Akun belum aktif atau belum diverifikasi! Silakan daftar ulang.'], 403);
        }

        // === USER REGULAR (ID 2): Bypass OTP dan langsung login + caching ke dashboard ===
        if ($akun->jenis_akun_id_jenis_akun == 2) {
            $akun->update(['token' => 0]);

            $tokenResult = $akun->createToken('nongkrongid_auth_token')->plainTextToken;

            // Cache profil regular user selama 24 jam untuk respon instan
            Cache::put("user_profile_{$akun->id_akun}", $akun, now()->addHours(24));

            return response()->json([
                'message' => 'Login berhasil! Selamat datang kembali.',
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'redirect_to' => '/dashboard/user',
                'requires_otp' => false,
                'data' => $akun
            ], 200);
        }

        // Generate OTP baru untuk sesi login role selain regular user (Mitra / Admin / Sponsor)
        $otpLogin = rand(100000, 999999);

        $akun->update([
            'token' => $otpLogin,
            'masa_awal' => now(), // Reset timer OTP login (berlaku 5 menit)
        ]);

        Mail::to($akun->email)->send(new SendOtpMail($otpLogin));

        return response()->json([
            'message' => 'Kredensial benar! Kode OTP untuk login telah dikirim ke email Anda.',
            'email' => $akun->email,
            'requires_otp' => true,
        ], 200);
    }

    // 6. Tahap 2 Login: Verifikasi OTP Login & Redirect ke 4 Dashboard Berbeda
    public function verifyLoginOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|integer',
        ]);

        $akun = Akun::with('jenisAkun')->where('email', $request->email)->first();

        if (!$akun) {
            return response()->json(['message' => 'Akun tidak ditemukan!'], 404);
        }

        // Cek kedaluwarsa OTP login (5 menit)
        if (now()->diffInMinutes($akun->masa_awal) > 5) {
            return response()->json(['message' => 'Kode OTP login sudah kedaluwarsa (lebih dari 5 menit)! Silakan minta ulang.'], 400);
        }

        if ($akun->token != $request->token) {
            return response()->json(['message' => 'Kode OTP salah!'], 400);
        }

        // OTP valid, bersihkan token kembali jadi 0
        $akun->update(['token' => 0]);

        $tokenResult = $akun->createToken('nongkrongid_auth_token')->plainTextToken;

        // === PENENTUAN REDIRECT BERDASARKAN ID JENIS AKUN (DATABASE) ===
        $jenisAkunId = $akun->jenis_akun_id_jenis_akun; // Sesuaikan dengan nama foreign key di model/tabel Anda
        $redirectTo = '/dashboard/user'; // Default untuk User biasa (ID 2)

        if ($jenisAkunId == 1) {
            $redirectTo = '/dashboard/admin';
        } elseif ($jenisAkunId >= 3 && $jenisAkunId <= 6) {
            $redirectTo = '/dashboard/mitra'; // Mencakup Mitra (3) & Mitra Premium (4, 5, 6)
        } elseif ($jenisAkunId == 7) {
            $redirectTo = '/dashboard/sponsor';
        }
        // ==============================================================

        if ($jenisAkunId == 2) {
            Cache::put("user_profile_{$akun->id_akun}", $akun, now()->addHours(24));
        }

        return response()->json([
            'message' => 'Login berhasil! Selamat datang kembali.',
            'access_token' => $tokenResult,
            'token_type' => 'Bearer',
            'redirect_to' => $redirectTo,
            'data' => $akun
        ], 200);
    }
    
    public function resendLoginOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $akun = Akun::where('email', $request->email)->first();

        if (!$akun) {
            return response()->json(['message' => 'Email tidak terdaftar!'], 404);
        }

        // Proteksi cooldown (60 detik / 1 menit) menggunakan timestamp Unix
        $waktuAwal = strtotime($akun->masa_awal);
        $waktuSekarang = time();
        $selisihDetik = $waktuSekarang - $waktuAwal;

        if ($selisihDetik < 60) {
            $sisaWaktu = ceil(60 - $selisihDetik);
            
            return response()->json([
                'message' => "Harap tunggu {$sisaWaktu} detik lagi sebelum meminta ulang kode OTP."
            ], 429);
        }

        $otpLogin = rand(100000, 999999);
        
        $akun->update([
            'token' => $otpLogin,
            'masa_awal' => now(),
        ]);

        Mail::to($akun->email)->send(new SendOtpMail($otpLogin));

        return response()->json([
            'message' => 'Kode OTP login baru telah dikirim ulang ke email Anda.',
        ], 200);
    }

    // 7. Melihat Data Akun Sendiri (sekaligus cek kedaluwarsa status Mitra Premium & Caching Regular User)
    public function getProfile(Request $request)
    {
        $user = $request->user();

        // Regular User (ID 2): Ambil dari cache untuk respons instan
        if ($user->jenis_akun_id_jenis_akun == 2) {
            $akun = Cache::remember("user_profile_{$user->id_akun}", now()->addHours(24), function () use ($user) {
                return $user->load('jenisAkun');
            });

            return response()->json([
                'message' => 'Data profil berhasil diambil.',
                'data' => $akun
            ], 200);
        }

        $akun = $user->load('jenisAkun');

        // Logika pengecekan masa aktif untuk Mitra Premium (ID 4, 5, 6)
        if (in_array($akun->jenis_akun_id_jenis_akun, [4, 5, 6])) {
            if ($akun->masa_berakhir && now()->greaterThan($akun->masa_berakhir)) {
                $akun->update([
                    'jenis_akun_id_jenis_akun' => 3 // Downgrade otomatis ke Mitra biasa
                ]);
                
                $akun->load('jenisAkun');
            }
        }

        return response()->json([
            'message' => 'Data profil berhasil diambil.',
            'data' => $akun
        ], 200);
    }

    // 8. Update Profil Akun (Nama, No Telepon, Password, dan Gambar)
    public function updateProfile(Request $request)
    {
        $akun = $request->user();

        $request->validate([
            'nama_lengkap' => 'required|string|min:3|max:60',
            'no_telefon' => 'required|numeric|digits_between:10,13|unique:akun,no_telefon,' . $akun->id_akun . ',id_akun',
            'password' => 'nullable|string|min:8|max:20',
            'path_gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'nama_lengkap.min' => 'Nama lengkap minimal harus 3 karakter.',
            'no_telefon.unique' => 'Nomor telepon ini sudah digunakan oleh akun lain.',
            'password.min' => 'Password minimal harus 8 karakter.',
            'path_gambar.image' => 'File yang diunggah harus berupa gambar.',
            'path_gambar.max' => 'Ukuran gambar maksimal adalah 2MB.',
        ]);

        $updateData = [
            'nama_lengkap' => $request->nama_lengkap,
            'no_telefon' => $request->no_telefon,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('path_gambar')) {
            $file = $request->file('path_gambar');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            
            Storage::disk('s3')->putFileAs('profile_images', $file, $filename);
            
            $updateData['path_gambar'] = $filename;
        }

        $akun->update($updateData);
        $updatedAkun = $akun->load('jenisAkun');

        // Perbarui cache profil jika akun adalah regular user
        if ($akun->jenis_akun_id_jenis_akun == 2) {
            Cache::put("user_profile_{$akun->id_akun}", $updatedAkun, now()->addHours(24));
        }

        return response()->json([
            'message' => 'Profil berhasil diperbarui!',
            'data' => $updatedAkun
        ], 200);
    }

    // 9. Cek Status Membership Otomatis Saat Membuka Dashboard Mitra
    public function checkMembershipStatus(Request $request)
    {
        $akun = $request->user();

        if (in_array($akun->jenis_akun_id_jenis_akun, [4, 5, 6])) {
            if ($akun->masa_berakhir && now()->greaterThan($akun->masa_berakhir)) {
                $akun->update([
                    'jenis_akun_id_jenis_akun' => 3
                ]);
                
                return response()->json([
                    'status' => 'downgraded',
                    'message' => 'Masa aktif premium Anda telah berakhir. Status akun telah diperbarui menjadi Mitra Biasa.'
                ]);
            }
        }

        return response()->json([
            'status' => 'active',
            'message' => 'Membership aman.'
        ]);
    }
}