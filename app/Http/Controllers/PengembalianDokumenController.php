<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dokumen;

class PengembalianDokumenController extends Controller
{
    public function index()
    {
        // Operator hanya melihat dokumen yang dikembalikan kepadanya (status = returned_to_operator)
        // R10: Perbaiki filter dari created_by='operator' ke current_handler / status yang tepat
        $dokumens = Dokumen::where('status', 'returned_to_operator')
            ->latest('returned_at')
            ->paginate(10);

        // R10: Perbaiki statistik duplikat
        // SEBELUM: $totalDibaca dan $totalDikembalikan keduanya query IDENTIK (duplikat nyata)
        // SESUDAH: masing-masing statistik memiliki makna berbeda

        // Total dokumen yang sedang dalam status dikembalikan ke operator (saat ini)
        $totalDikembalikan = Dokumen::where('status', 'returned_to_operator')->count();

        // Total dokumen yang pernah dikembalikan (return_reason tercatat),
        // termasuk yang sudah dikirim ulang (status sudah berubah)
        $totalPernah = Dokumen::whereNotNull('return_reason')
            ->whereNotNull('returned_at')
            ->count();

        // Total dokumen yang sudah dikirim ulang ke Team Verifikasi (setelah dikembalikan)
        $totalDikirimUlang = Dokumen::where('status', 'sent_to_team_verifikasi')
            ->whereNotNull('returned_at')
            ->count();

        // Total dokumen operator yang sedang aktif diproses (tidak dikembalikan)
        $totalDikirim = Dokumen::whereIn('status', [
                'sent_to_team_verifikasi',
                'sedang diproses',
                'sent_to_perpajakan',
                'sent_to_akutansi',
                'sent_to_pembayaran',
                'completed',
                'selesai',
            ])
            ->whereNull('returned_at')
            ->count();

        $data = [
            "title"                           => "Daftar Dokumen Dikembalikan",
            "module"                          => "Operator",
            "menuDokumen"                     => "active",
            "menuDaftarDokumenDikembalikan"   => "Active",
            "menuDaftarDokumen"               => "",
            "menuDashboard"                   => "",
            "dokumens"                        => $dokumens,
            "totalDikembalikan"               => $totalDikembalikan,
            "totalPernah"                     => $totalPernah,
            "totalDikirimUlang"               => $totalDikirimUlang,
            "totalDikirim"                    => $totalDikirim,
        ];

        return view('operator.dokumens.pengembalianDokumen', $data);
    }
}
