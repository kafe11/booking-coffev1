<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuModel extends Model
{
    //
    protected $table = 'menu';
    protected $primaryKey = 'id_menu';
    public $incrementing = true;
    protected $fillable = [
        'id_menu',
        'id_pengelola',
        'nama_menu',
        'kategori',
        'harga',
        'deskripsi',
        'gambar',
        'stok',
    ];

     public function pesanan()
    {
        return $this->hasMany(Pesanan::class, 'id_menu', 'id_menu');
    }
}
