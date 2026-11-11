<?php

use Illuminate\Support\Facades\Route;

// Halaman Utama (Welcome)
Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return response()->json(['message' => 'Silakan login terlebih dahulu.'], 401);
})->name('login');

// --- RUTE DASHBOARD USER (Buka proteksi web-nya, amankan via JS) ---
Route::get('/dashboard/user', function () {
    return view('dashboard.user');
})->name('dashboard.user');

// --- RUTE DASHBOARD MITRA (Buka proteksi web-nya, amankan via JS) ---
Route::get('/dashboard/mitra', function () {
    return view('dashboard.mitra');
})->name('dashboard.mitra');


