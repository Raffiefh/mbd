<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Toko extends Model
{
    use HasFactory;

    protected $table = 'toko';
    protected $primaryKey = 'id_toko';
    public $timestamps = false;
    protected $guarded = [];

    public function akun()
    {
        return $this->belongsTo(Akun::class, 'akun_id_akun', 'id_akun');
    }

    public function gambarToko()
    {
        return $this->hasMany(GambarToko::class, 'toko_id_toko', 'id_toko');
    }

    // Tambahkan fungsi relasi ini agar method ->load('promo') berfungsi
    public function promo()
    {
        return $this->belongsTo(DetailPromo::class, 'detail_promo_id_detail_promo', 'id_detail_promo');
    }

    public function kategori()
    {
        return $this->belongsToMany(
            Kategori::class, 
            'kategori_toko', 
            'toko_id_toko', 
            'kategori_id_kategori'
        );
    }
}