<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DetailPesananController extends Controller
{
    //
    public function showDetailPesanan()
    {
       return view('User.detail-pesanan');
    }
}
