<?php

namespace App\Services;

use App\Models\Bagian;
use App\Models\Dokumen;
use App\Models\User;
use App\Notifications\DokumenDikembalikanNotification;
use Illuminate\Support\Facades\Log;

/**
 * Sumber TUNGGAL pemberitahuan "dokumen dikembalikan ke Bagian".
 *
 * Sebelumnya logika ini hanya ada di TeamVerifikasiController::returnToBidang()
 * (blok "R6"), sementara jalur dropdown Pengurus Dokumen tidak memberi tahu siapa
 * pun. Diekstrak ke sini supaya kedua pintu memakai satu perilaku — bukan disalin
 * (lihat aturan "jangan tambah salinan" di CLAUDE.md).
 *
 * PERUBAHAN PERILAKU dibanding blok R6 lama: notifikasi in-app (database) kini
 * SELALU ditulis, bukan hanya sebagai cadangan saat user tak punya nomor HP.
 * Alasannya konkret — pemeriksaan produksi 2026-08-06 menemukan 0 dari 8 user
 * Bagian memiliki phone_number, sehingga cabang WhatsApp tidak pernah jalan dan
 * cabang cadangan tak pernah tersentuh. In-app adalah catatan persisten; WhatsApp
 * adalah dorongan tambahan di atasnya, bukan penggantinya.
 *
 * Kegagalan notifikasi TIDAK BOLEH menggagalkan pengembalian dokumen — semua
 * dibungkus try/catch dan hanya dicatat ke log.
 */
class DocumentReturnNotifier
{
    /**
     * SEMENTARA (2026-08-07) — penanda pesan uji coba. Hapus bersama pesanUjiCoba()
     * saat fitur uji coba dicabut.
     */
    private const PENANDA_UJI = "🧪 *[UJI COBA — BUKAN PENGEMBALIAN SUNGGUHAN]*\n\n";

    /**
     * @param  string  $bagianCode  kode bagian tujuan (huruf besar, mis. 'AKN')
     */
    public static function kirim(Dokumen $dokumen, string $bagianCode, string $alasan): void
    {
        try {
            $bagianCode = strtoupper(trim($bagianCode));

            $penerima = User::where('bagian_code', $bagianCode)->get();

            if ($penerima->isEmpty()) {
                Log::info('[pengembalian] Tidak ada user Bagian untuk dinotifikasi', [
                    'dokumen_id'  => $dokumen->id,
                    'bagian_code' => $bagianCode,
                ]);

                return;
            }

            $namaBagian = self::namaBagian($bagianCode);

            // 1) In-app — SELALU, untuk semua user bagian tersebut.
            foreach ($penerima as $user) {
                try {
                    $user->notify(new DokumenDikembalikanNotification($dokumen, $alasan, $namaBagian));
                } catch (\Throwable $e) {
                    Log::error('[pengembalian] Gagal menulis notifikasi in-app: ' . $e->getMessage(), [
                        'dokumen_id' => $dokumen->id,
                        'user_id'    => $user->id,
                    ]);
                }
            }

            // 2) WhatsApp — hanya untuk yang punya nomor HP. Belum ada satu pun
            // user Bagian yang mengisinya di produksi, jadi cabang ini praktis
            // menganggur sampai nomor diisi. Dibiarkan hidup supaya begitu nomor
            // masuk, notifikasi langsung berjalan tanpa perubahan kode.
            $berponsel = $penerima->filter(
                fn ($u) => trim((string) $u->phone_number) !== ''
            );

            if ($berponsel->isEmpty()) {
                return;
            }

            $pesan = self::susunPesan(
                $dokumen->nomor_agenda ?: 'N/A',
                $namaBagian,
                $alasan,
                url(route('inbox.show', $dokumen->id, false))
            );
            $wa    = app(FonnteWhatsAppService::class);

            foreach ($berponsel as $user) {
                try {
                    $wa->sendMessage($user->phone_number, $pesan);
                } catch (\Throwable $e) {
                    Log::error('[pengembalian] Gagal kirim WhatsApp: ' . $e->getMessage(), [
                        'dokumen_id' => $dokumen->id,
                        'user_id'    => $user->id,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Jaring terakhir: pengembalian dokumen tetap harus dianggap berhasil.
            Log::error('[pengembalian] Notifikasi gagal total: ' . $e->getMessage(), [
                'dokumen_id' => $dokumen->id,
            ]);
        }
    }

    /**
     * Nama bagian dibaca dari tabel `bagians`, BUKAN peta kode→nama yang di-hardcode
     * seperti dulu di returnToBidang(). Peta hardcode itu ikut membusuk setiap kali
     * daftar bagian berubah.
     *
     * PUBLIK sejak 2026-08-07: dipakai juga UjiWhatsAppBagianController supaya panel
     * uji tidak melahirkan peta kode→nama ketiga.
     */
    public static function namaBagian(string $bagianCode): string
    {
        $bagian = Bagian::whereRaw('UPPER(TRIM(kode)) = ?', [$bagianCode])->first();

        $nama = trim((string) ($bagian->nama ?? ''));

        return $nama !== '' ? $nama : $bagianCode;
    }

    /**
     * SEMENTARA (2026-08-07) — pesan contoh untuk panel uji di halaman Bagian.
     *
     * Sengaja memanggil susunPesan() yang sama dengan jalur produksi, bukan menyalin
     * templatenya: pesan uji yang menyimpang dari pesan sungguhan akan menipu
     * responden uji coba tanpa ada yang menyadarinya.
     *
     * Nomor agenda 9999_2026 dipilih karena mustahil bertabrakan dengan dokumen nyata.
     *
     * Hapus method ini + konstanta PENANDA_UJI saat fitur uji coba dicabut.
     */
    public static function pesanUjiCoba(string $namaBagian, string $tautan): string
    {
        return self::PENANDA_UJI . self::susunPesan(
            '9999_2026',
            $namaBagian,
            'Lampiran faktur belum lengkap. (contoh)',
            $tautan
        );
    }

    private static function susunPesan(
        string $agenda,
        string $namaBagian,
        string $alasan,
        string $tautan
    ): string {
        return "🔔 *NOTIFIKASI SISTEM AGENDA ONLINE*\n\n"
            . "Dokumen dengan nomor agenda *{$agenda}* telah *dikembalikan* ke Bagian {$namaBagian}.\n\n"
            . "📋 *Alasan Pengembalian:*\n{$alasan}\n\n"
            . "Silakan lakukan perbaikan dan kirim ulang dokumen.\n\n"
            . "🔗 Lihat dokumen: {$tautan}";
    }
}
