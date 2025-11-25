<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dokumen;

class DashboardController extends Controller
{
    public function index(){
        // Get statistics for IbuA (only documents created by ibuA)
        $totalDokumen = Dokumen::where('created_by', 'ibuA')->count();

        // Total dokumen belum dikirim = dokumen yang masih draft atau belum dikirim ke ibuB
        $totalBelumDikirim = Dokumen::where('created_by', 'ibuA')
            ->whereNull('sent_to_ibub_at')
            ->count();

        // Total dokumen sudah dikirim = dokumen yang sudah dikirim ke ibuB (sent_to_ibub_at tidak null)
        $totalSudahDikirim = Dokumen::where('created_by', 'ibuA')
            ->whereNotNull('sent_to_ibub_at')
            ->count();

        // Get latest documents (5 most recent) created by ibuA
        $dokumenTerbaru = Dokumen::where('created_by', 'ibuA')
            ->with(['dibayarKepadas'])
            ->latest('tanggal_masuk')
            ->take(5)
            ->get();

        $data = array(
            "title" => "Dashboard",
            "module" => "IbuA",
            "menuDashboard" => "Active",
            'menuDokumen' => '',
            'menuRekapan' => '',
            'totalDokumen' => $totalDokumen,
            'totalBelumDikirim' => $totalBelumDikirim,
            'totalSudahDikirim' => $totalSudahDikirim,
            'dokumenTerbaru' => $dokumenTerbaru,
        );
        return view('IbuA.dashboard',$data);
    }
}
