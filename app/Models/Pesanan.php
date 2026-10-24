<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    protected $table = 'detail_pesanan';
    protected $primaryKey = 'id_detail_pesanan';

    public $incrementing = false; // karena bukan auto increment
    protected $keyType = 'int';

    protected $fillable = [
        'id_detail_pesanan',
        'id_transaksi',
        'id_menu',
    ];

    /**
     * Relasi ke Transaksi
     */
    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi', 'id_transaksi');
    }

    /**
     * Relasi ke Menu
     */
    public function menu()
    {
        return $this->belongsTo(MenuModel::class, 'id_menu', 'id_menu');
    }
}