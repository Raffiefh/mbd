<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPromo extends Model
{
    protected $table = 'detail_promo';
    protected $primaryKey = 'id_detail_promo';
    public $timestamps = false;
    protected $guarded = [];

    // Ubah dari hasMany menjadi hasOne karena 1 promo spesifik untuk 1 toko
    public function toko()
    {
        return $this->hasOne(Toko::class, 'detail_promo_id_detail_promo', 'id_detail_promo');
    }
}