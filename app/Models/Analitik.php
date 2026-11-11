<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Analitik extends Model
{
    protected $table = 'analitik';
    protected $primaryKey = 'id_analitik';
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