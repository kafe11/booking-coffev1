<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservasiMeja extends Model
{
    protected $table = 'reservasi_meja';
    protected $primaryKey = 'id';
    public $incrementing = true;

    protected $fillable = [
        'id_reservasi',
        'id_meja'
    ];

    public $timestamps = false;

     public function reservasi()
    {
        return $this->belongsTo(
            Reservasi::class,
            'id_reservasi',
            'id_reservasi'
        );
    }

    public function meja()
    {
        return $this->belongsTo(
            Meja::class,
            'id_meja',
            'id_meja'
        );
    }
}

