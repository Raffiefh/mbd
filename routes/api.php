<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TokoController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\TokoDetailController;
use App\Http\Controllers\AnalitikController;
use App\Http\Controllers\PremiumController;
use App\Http\Controllers\IklanController;

// --- ROUTE AUTENTIKASI (REGISTRASI & VERIFIKASI) ---
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify-token', [AuthController::class, 'verifyToken']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);

// --- ROUTE LOGIN DENGAN OTP (2 TAHAP) ---
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login/verify', [AuthController::class, 'verifyLoginOtp']);
Route::post('/login/resend-otp', [AuthController::class, 'resendLoginOtp']);


// --- ROUTE YANG MEMBUTUHKAN TOKEN SANCTUM (PROTECTED) ---
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/akun/profile', [AuthController::class, 'getProfile']);
    Route::post('/akun/update', [AuthController::class, 'updateProfile']);
    Route::get('/akun/check-membership', [AuthController::class, 'checkMembershipStatus']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Rute Form Awal Lengkapi Toko (Setelah Register)
    Route::get('/mitra/lengkapi-toko', [TokoController::class, 'create']);
    Route::post('/mitra/lengkapi-toko/simpan', [TokoController::class, 'store']);
});

// --- RUTE KELOLA TOKO (Diproteksi Middleware ForceCompleteToko) ---
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/mitra/toko', [TokoController::class, 'index']);
    Route::post('/mitra/toko', [TokoController::class, 'store']); // Untuk simpan baru
    Route::post('/mitra/toko/update', [TokoController::class, 'update']); // Untuk update
});

Route::get('/kategori', [KategoriController::class, 'index']);
Route::get('/katalog', [KatalogController::class, 'index']);   

Route::middleware('auth:sanctum')->group(function () {
    Route::get   ('/toko/{id}/detail',  [TokoDetailController::class, 'show']);
    Route::post  ('/toko/{id}/ulasan',  [TokoDetailController::class, 'storeUlasan']);
    Route::delete('/toko/{id}/ulasan',  [TokoDetailController::class, 'destroyUlasan']);
    Route::post  ('/toko/{id}/favorit', [TokoDetailController::class, 'toggleFavorit']);
    Route::get   ('/favorit',           [TokoDetailController::class, 'favoritIndex']);
});
 
// ---- Analitik Mitra Premium (tambahkan juga: use App\Http\Controllers\AnalitikController;) ----
Route::middleware('auth:sanctum')->get('/mitra/analitik', [AnalitikController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get   ('/mitra/toko/ulasan',             [TokoDetailController::class, 'ulasanMitra']);
    Route::delete('/mitra/toko/ulasan/{idUlasan}',  [TokoDetailController::class, 'hapusUlasanMitra']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get ('/mitra/premium/paket', [PremiumController::class, 'paket']);
    Route::post('/mitra/premium/beli',  [PremiumController::class, 'beli']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get ('/mitra/iklan/kursi',      [IklanController::class, 'kursi']);
    Route::get ('/mitra/iklan/saya',       [IklanController::class, 'saya']);
    Route::post('/mitra/iklan/ajukan',     [IklanController::class, 'ajukan']);
    Route::post('/mitra/iklan/{id}/bayar', [IklanController::class, 'bayar']);
    Route::get ('/iklan/tayang',           [IklanController::class, 'tayang']);   
});
