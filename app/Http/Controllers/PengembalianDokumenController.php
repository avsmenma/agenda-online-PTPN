<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PengembalianDokumenController extends Controller
{
     public function index(){
        // Operator only sees returned documents (status = returned_to_Operator)
        $dokumens = \App\Models\Dokumen::where('created_by', 'operator')
            ->where('status', 'returned_to_Operator')
            ->latest('returned_to_Operator_at')
            ->select(['*', 'alasan_pengembalian']) // Ensure alasan_pengembalian is loaded
            ->paginate(10);

        // Get statistics
        $totalDibaca = \App\Models\Dokumen::where('created_by', 'operator')
            ->where('status', 'returned_to_Operator')
            ->count();
        $totalDikembalikan = \App\Models\Dokumen::where('created_by', 'operator')
            ->where('status', 'returned_to_Operator')
            ->count();
        $totalDikirim = \App\Models\Dokumen::where('created_by', 'operator')
            ->where('status', 'sent_to_team_verifikasi')
            ->count();

        $data = array(
            "title" => "Daftar Dokumen Dikembalikan",
            "module" => "Operator",
            "menuDokumen" => "active",
            "menuDaftarDokumenDikembalikan" => "Active",
            "menuDaftarDokumen" => "",
            "menuDashboard" => "",
            "dokumens" => $dokumens,
            "totalDibaca" => $totalDibaca,
            "totalDikembalikan" => $totalDikembalikan,
            "totalDikirim" => $totalDikirim,
        );
        return view('operator.dokumens.pengembalianDokumen', $data);
    }
}






