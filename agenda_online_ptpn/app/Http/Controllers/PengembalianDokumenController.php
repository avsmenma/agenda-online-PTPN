<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PengembalianDokumenController extends Controller
{
     public function index(){
        // IbuA only sees returned documents (status = returned_to_ibua)
        $dokumens = \App\Models\Dokumen::where('created_by', 'ibuA')
            ->where('status', 'returned_to_ibua')
            ->latest('returned_to_ibua_at')
            ->select(['*', 'alasan_pengembalian']) // Ensure alasan_pengembalian is loaded
            ->paginate(10);

        // Get statistics
        $totalDibaca = \App\Models\Dokumen::where('created_by', 'ibuA')
            ->where('status', 'returned_to_ibua')
            ->count();
        $totalDikembalikan = \App\Models\Dokumen::where('created_by', 'ibuA')
            ->where('status', 'returned_to_ibua')
            ->count();
        $totalDikirim = \App\Models\Dokumen::where('created_by', 'ibuA')
            ->where('status', 'sent_to_ibub')
            ->count();

        $data = array(
            "title" => "Daftar Dokumen Dikembalikan",
            "module" => "IbuA",
            "menuDokumen" => "active",
            "menuDaftarDokumenDikembalikan" => "Active",
            "menuDaftarDokumen" => "",
            "menuDashboard" => "",
            "dokumens" => $dokumens,
            "totalDibaca" => $totalDibaca,
            "totalDikembalikan" => $totalDikembalikan,
            "totalDikirim" => $totalDikirim,
        );
        return view('IbuA.dokumens.pengembalianDokumen', $data);
    }
}
