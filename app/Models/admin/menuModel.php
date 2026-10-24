<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Model;
// use Illuminate\Foundation\Auth\User as Authenticatable;

class menuModel extends Model
{
    protected $table = 'menu';
    protected $primaryKey = 'id_menu';
    public $incrementing = false;
     protected $keyType = 'string';
    public $timestamps = true;

    protected $fillable = [
        'id_menu',
        'id_pengelola',
        'nama_menu',
        'kategori',
        'harga',
        'deskripsi',
        'gambar',
        'stok',
        'jenis',
    ];
}
