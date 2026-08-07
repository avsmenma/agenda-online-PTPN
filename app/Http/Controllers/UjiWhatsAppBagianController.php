<?php

namespace App\Http\Controllers;

use App\Services\DocumentReturnNotifier;
use App\Services\FonnteWhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * ============================ FITUR SEMENTARA ============================
 * Dibuat 2026-08-07 untuk sesi UJI COBA PENGGUNA role Bagian.
 *
 * Alasannya dua: (1) butir C4 kuesioner menanyakan lewat apa responden ingin
 * diberi tahu saat dokumennya dikembalikan — tak bisa dijawab berdasar kalau
 * mereka belum pernah melihat wujud pesannya; (2) nol dari delapan akun Bagian
 * punya phone_number terisi, sehingga cabang WhatsApp di DocumentReturnNotifier
 * belum pernah sekalipun berjalan di produksi — belum ada bukti Fonnte benar-
 * benar bisa mengirim dari server.
 *
 * DAFTAR PENCABUTAN (setelah sesi uji coba selesai):
 *   1. Hapus berkas ini
 *   2. Hapus resources/views/bagian/partials/_ujiWhatsApp.blade.php. Direktori
 *      resources/views/bagian/partials/ DIBUAT BARU oleh fitur ini (ia satu-
 *      satunya isinya) — kalau jadi kosong sesudah dihapus, hapus juga
 *      direktorinya (pola sama dengan resources/views/tracking/ yang dihapus
 *      di audit dead-code 2026-07-26).
 *   3. Di resources/views/bagian/dokumens/daftarDokumen.blade.php hapus DUA
 *      sisipan: tombol id="btnUjiWhatsApp" di toolbar filter, dan baris
 *      @include('bagian.partials._ujiWhatsApp') di dekat modal lain
 *   4. Hapus baris route bernama 'bagian.uji-whatsapp' di routes/web.php
 *      BESERTA BLOK KOMENTARNYA (3 baris "SEMENTARA (2026-08-07) ..." tepat di
 *      atasnya) — komentar itu menerangkan route ini dan akan menggantung kalau
 *      routenya hilang tapi komentarnya tertinggal.
 *   5. Hapus tests/Feature/UjiWhatsAppBagianTest.php
 *   6. Di DocumentReturnNotifier: hapus pesanUjiCoba() + konstanta PENANDA_UJI;
 *      kembalikan namaBagian() ke private BILA tak ada pemakai lain.
 *      Tanda tangan susunPesan() yang menerima nilai biasa BOLEH dipertahankan —
 *      itu perbaikan yang berdiri sendiri.
 *   7. Di tests/Feature/NotifikasiPengembalianBagianTest.php, docblock test
 *      test_pesan_whatsapp_menyusun_argumen_pada_posisi_yang_benar() merujuk ke
 *      UjiWhatsAppBagianTest (dihapus di langkah 5 di atas) — perbaiki rujukan
 *      itu. TEST INI SENDIRI WAJIB TETAP HIDUP, JANGAN ikut dihapus: ia menjaga
 *      jalur produksi (DocumentReturnNotifier::kirim()), bukan fitur uji coba ini.
 *
 * Cek bagian_code kosong SENGAJA tidak ada di sini: middleware CheckBagianRole
 * sudah menolaknya dengan 403 sebelum request sampai ke controller.
 * =========================================================================
 */
class UjiWhatsAppBagianController extends Controller
{
    public function kirim(Request $request, FonnteWhatsAppService $wa): JsonResponse
    {
        $data = $request->validate([
            'nomor_hp' => ['required', 'string', 'regex:/^(\+?62|0)8[0-9]{7,13}$/'],
        ], [
            'nomor_hp.required' => 'Nomor WhatsApp wajib diisi.',
            'nomor_hp.regex'    => 'Masukkan nomor WhatsApp yang sah, contoh 081234567890.',
        ]);

        $pesan = DocumentReturnNotifier::pesanUjiCoba(
            DocumentReturnNotifier::namaBagian(strtoupper(trim((string) Auth::user()->bagian_code))),
            // Tautan diarahkan ke halaman Bagian, BUKAN inbox dokumen contoh:
            // dokumen 9999_2026 tidak ada, dan responden yang menekan tautan mati
            // akan menyimpulkan "sistemnya rusak" — kesan pertama yang mahal.
            url(route('bagian.documents.index', [], false))
        );

        $hasil = $wa->sendMessage($data['nomor_hp'], $pesan);

        if (($hasil['success'] ?? false) === true) {
            return response()->json([
                'ok'    => true,
                'pesan' => 'Pesan terkirim ke ' . $wa->formatPhoneNumber($data['nomor_hp'])
                    . '. Silakan cek WhatsApp.',
            ]);
        }

        return response()->json([
            'ok'    => false,
            'pesan' => self::alasanTerbaca($hasil),
        ]);
    }

    /**
     * Menerjemahkan hasil FonnteWhatsAppService ke kalimat yang bisa ditindaklanjuti.
     * Sengaja TIDAK diseragamkan jadi "gagal": justru alasannya yang ingin diketahui.
     */
    private static function alasanTerbaca(array $hasil): string
    {
        // $hasil['message'] berasal dari respons Fonnte apa adanya (lihat
        // FonnteWhatsAppService::sendMessage()) — bila API mengembalikan array/objek
        // di situ, (string) langsung memicu warning "Array to string conversion".
        // Nilai non-skalar diperlakukan sebagai kosong, bukan dipaksa jadi string.
        $mentah   = $hasil['message'] ?? '';
        $pesanApi = is_scalar($mentah) ? trim((string) $mentah) : '';
        $ekor     = $pesanApi !== '' ? $pesanApi : 'tanpa keterangan';

        return match ($hasil['reason'] ?? '') {
            'disabled'  => 'Notifikasi WhatsApp sedang dimatikan di server (WHATSAPP_NOTIFICATIONS_ENABLED=false).',
            'no_token'  => 'Token Fonnte belum diisi di server (FONNTE_API_TOKEN).',
            'api_error' => 'Fonnte menolak kiriman: ' . $ekor,
            'exception' => 'Gagal menghubungi Fonnte: ' . $ekor,
            default     => 'Pengiriman gagal: ' . $ekor,
        };
    }
}
