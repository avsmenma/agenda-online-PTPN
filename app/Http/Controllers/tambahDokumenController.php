<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class tambahDokumenController extends Controller
{
     public function index(){
        $data = array(
            "title" => "Tambah Dokumen",
            "menuDaftarDokumenDikembalikan" => "",
            // "menuDaftarDokumenDikembalikan" => "",
            "menuDaftarDokumen" => "",
            "menuDashboard" => "",
        );
        return view('operator.dashboard',$data);
    }
}





