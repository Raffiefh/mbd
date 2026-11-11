<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Akun extends Model
{
    use HasApiTokens;
    
    protected $table = 'akun';
    protected $primaryKey = 'id_akun';
    public $timestamps = false;
    protected $guarded = [];

    public function jenisAkun()
    {
        return $this->belongsTo(JenisAkun::class, 'jenis_akun_id_jenis_akun', 'id_jenis_akun');
    }

    public function toko()
    {
        return $this->hasOne(Toko::class, 'akun_id_akun', 'id_akun');
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'akun_id_akun', 'id_akun');
    }
}