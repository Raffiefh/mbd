<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $table = 'kategori';
    protected $primaryKey = 'id_kategori';
    public $timestamps = false;
    protected $guarded = [];

    public function toko()
    {
        return $this->belongsToMany(
            Toko::class, 
            'kategori_toko', 
            'kategori_id_kategori', 
            'toko_id_toko'
        );
    }
}