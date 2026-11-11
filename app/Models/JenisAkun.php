<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisAkun extends Model
{
    protected $table = 'jenis_akun';
    protected $primaryKey = 'id_jenis_akun';
    public $timestamps = false; 
    public function akuns()
    {
        return $this->hasMany(Akun::class, 'jenis_akun_id_jenis_akun', 'id_jenis_akun');
    }
}