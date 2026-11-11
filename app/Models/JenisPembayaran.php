<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisPembayaran extends Model
{
    protected $table = 'jenis_pembayaran';
    protected $primaryKey = 'id_jenis_pembayaran';
    public $timestamps = false;
}