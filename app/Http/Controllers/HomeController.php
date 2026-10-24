<?php

namespace App\Http\Controllers;
use App\Models\pelangganModel;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    // landing page / home 
    public function home()
    {   
        // return halaman home
        return view('home');
    }
    public function homepelanggan()
    {   
        // return halaman home
        return view('homeLogin');
    }
}
