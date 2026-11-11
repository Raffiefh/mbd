<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';
    protected $primaryKey = 'id_pembayaran';
    public $timestamps = false;
    protected $guarded = [];

    public function akun()
    {
        return $this->belongsTo(Akun::class, 'akun_id_akun', 'id_akun');
    }

    public function jenisPembayaran()
    {
        return $this->belongsTo(JenisPembayaran::class, 'jenis_pembayaran_id_jenis_pembayaran', 'id_jenis_pembayaran');
    }
}