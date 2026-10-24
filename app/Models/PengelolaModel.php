<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengelolaModel extends Model
{
    protected $table = 'pengelola';
    protected $primaryKey = 'id_pengelola';
    public $incrementing = true;
    protected $fillable = [
        'id_pelanggan',
        'nama_pengelola',
        'username',
        'password',
        'created_at',
        'updated_at',
    ];
}
