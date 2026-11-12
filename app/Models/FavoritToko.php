<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FavoritToko extends Model
{
    protected $table = 'favorit_toko';
    protected $primaryKey = 'id_favorit_toko';
    public $timestamps = false;
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