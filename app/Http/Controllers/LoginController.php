<?php

namespace App\Http\Controllers;

use App\Models\pelangganModel;
use App\Models\PengelolaModel;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    // show halaman login 
    public function showLogin()
    {
        return view('auth.login');
    }

    // login pelanggan
    public function loginPelanggan(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // LOGIN PELANGGAN
        $pelanggan = pelangganModel::where('username', $request->username)->first();
        if ($pelanggan && $request->password === $pelanggan->password) {
            session([
                'id_pelanggan' => $pelanggan->id_pelanggan,
                'nama_pelanggan' => $pelanggan->nama_pelanggan,
            ]);
            \Log::info('SESSION SET', session()->all());

            return redirect()->route('home-login');
        }

        // LOGIN PENGELOLA
        $pengelola = PengelolaModel::where('username', $request->username)->first();
        if ($pengelola && $request->password === $pengelola->password) {
            session([
                'id_pengelola' => $pengelola->id_pengelola,
                'nama_pengelola' => $pengelola->nama_pengelola,
            ]);
            \Log::info('SESSION SET', session()->all());
            return redirect()->route('admin.dashboard');
        }

        return back()->with('gagal', 'Username atau password salah');
    }


    // logout pelanggan
    public function logutPelanggan(Request $request)
    {
        // hapus session id dan nama pelanggan
        Session::forget('id_pelanggan');
        Session::forget('nama_pelanggan');
        session()->forget('keranjang');
        session()->forget('reservasi');
        session()->forget('menuFromReservasi');


        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // back landing page
        // return view('home');
        return Redirect()->route('home');
    }

    // logout pengelola
    public function logutPengelola(Request $request)
    {
        // hapus session id dan nama pelanggan
        Session::forget('id_pengelola');
        Session::forget('nama_pengelola');

        // back landing page
        return Redirect()->route('login');
    }
    
}
