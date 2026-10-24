<?php

// app/Models/Meja.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Meja extends Model
{
    use HasFactory;

    protected $table = 'meja';
    protected $primaryKey = 'id_meja';
    public $incrementing = true;


    protected $fillable = [
        'kode_meja',
        'ruangan',
    ];


    public function reservasi()
    {
        return $this->belongsToMany(
            reservasi::class,
            'reservasi_meja',
            'id_meja',
            'id_reservasi'
        );
    }

    public function reservasiMeja()
    {
        return $this->hasMany(
            ReservasiMeja::class,
            'id_meja',
            'id_meja'
        );
    }
}

