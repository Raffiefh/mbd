<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatingUlasan extends Model
{
    protected $table = 'rating_ulasan';
    protected $primaryKey = 'id_rating_ulasan';
    public $timestamps = false; // Karena menggunakan created_at manual sesuai DDL
    protected $guarded = [];

    public function akun()
    {
        return $this->belongsTo(Akun::class, 'akun_id_akun', 'id_akun');
    }

    public function toko()
    {
        return $this->belongsTo(Toko::class, 'toko_id_toko', 'id_toko');
    }
}