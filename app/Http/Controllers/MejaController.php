<?php
namespace App\Http\Controllers;

use App\Models\Meja;
use Carbon\Carbon;

use Illuminate\Http\Request;

class MejaController extends Controller
{
    // KONDISI MEJA (KOSONG, TERISI)
    public function showMeja()
    {
        $mejaIndoor = Meja::where('ruangan', 'Indoor')
            ->leftJoin('reservasi_meja', 'meja.id_meja', '=', 'reservasi_meja.id_meja')
            ->select(
                'meja.*',
                DB::raw("
                    CASE 
                        WHEN reservasi_meja.id_meja IS NULL 
                        THEN 'KOSONG' 
                        ELSE 'TERBOOKING' 
                    END AS status
                ")
            )
            ->get();

        $mejaOutdoor = Meja::where('ruangan', 'Outdoor')
            ->leftJoin('reservasi_meja', 'meja.id_meja', '=', 'reservasi_meja.id_meja')
            ->select(
                'meja.*',
                DB::raw("
                    CASE 
                        WHEN reservasi_meja.id_meja IS NULL 
                        THEN 'KOSONG' 
                        ELSE 'TERBOOKING' 
                    END AS status
                ")
            )
            ->get();

        return view('admin.dataMeja', compact('mejaIndoor', 'mejaOutdoor'));
    }
}
