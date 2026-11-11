<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GambarToko extends Model
{
    protected $table = 'gambar_toko';
    protected $primaryKey = 'id_gambar_toko';
    public $timestamps = false;
    protected $guarded = [];

    public function toko()
    {
        return $this->belongsTo(Toko::class, 'toko_id_toko', 'id_toko');
    }
}