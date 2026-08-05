<?php

namespace App\Support;

use App\Models\Bagian;

/**
 * Sumber TUNGGAL opsi dropdown "Pengurus Dokumen" untuk 4 role Tabulator
 * (operator/akutansi/perpajakan/verifikasi). Menggantikan 4 salinan
 * build*HandlerOptions() yang dulu identik di masing-masing controller
 * (DokumenController, DashboardAkutansiController, DashboardPerpajakanController,
 * TeamVerifikasiController) — komentar di sana pun sudah mengakui "bentuk identik".
 *
 * Pembayaran TIDAK memakai kelas ini: PembayaranDocumentRow berjalan dengan
 * showHandler: false dan menerima $handlerOptions = [] (default).
 *
 * PERUBAHAN PERILAKU (keputusan user 2026-08-06): optgroup Bagian dipersempit
 * ke bagian MILIK DOKUMEN ITU SENDIRI — bukan lagi seluruh bagian aktif. Dulu
 * user harus memilih bagian tujuan secara manual dari 9 pilihan saat
 * mengembalikan dokumen, dan salah pilih sangat mungkin terjadi. Dengan hanya
 * satu pilihan yang memang milik dokumen tersebut, salah pilih jadi mustahil.
 *
 * Penegakan sisi-server ada di DocumentHandlerController::update() — pemangkasan
 * dropdown saja hanya kosmetik karena opsi ditanam di data baris (bisa diakali).
 */
class HandlerOptions
{
    /**
     * 5 opsi peran alur kerja. Urutan & label dipertahankan persis seperti
     * keempat salinan lama agar tampilan dropdown tidak berubah.
     */
    private const ROLE_OPTIONS = [
        ['value' => 'operator',        'label' => 'Operator'],
        ['value' => 'team_verifikasi', 'label' => 'Tim Verifikasi'],
        ['value' => 'perpajakan',      'label' => 'Tim Perpajakan'],
        ['value' => 'akutansi',        'label' => 'Tim Akuntansi'],
        ['value' => 'pembayaran',      'label' => 'Tim Pembayaran'],
    ];

    /**
     * Peta bagian aktif — dibangun SEKALI per-request lalu dioper ke forDokumen()
     * untuk tiap baris, supaya tidak ada query per-baris (N+1). Ini menggantikan
     * alasan lama "bangun handler options sekali per-request": query tetap sekali,
     * yang jadi per-baris hanyalah pemilihan entri dari peta yang sudah di memori.
     *
     * Diindeks oleh kode DAN nama (keduanya huruf besar) supaya baris lama yang
     * menyimpan nama bagian, bukan kodenya, tetap dikenali. Editor inline
     * (document-tabulator.js: select_bagian) menulis `kode`, jadi data baru selalu
     * berbentuk kode — indeks nama murni pengaman data lama.
     *
     * @return array<string, array{value: string, label: string}>
     */
    public static function bagianMap(): array
    {
        $map = [];

        foreach (Bagian::active()->ordered()->get(['kode', 'nama']) as $bagian) {
            $kode = trim((string) $bagian->kode);
            if ($kode === '') {
                continue;
            }

            $nama  = trim((string) $bagian->nama);
            $entri = [
                'value' => 'bagian_' . strtolower($kode),
                'label' => $nama !== '' ? $nama : $kode,
            ];

            $map[strtoupper($kode)] = $entri;

            if ($nama !== '') {
                // Jangan biarkan nama menimpa kode milik bagian lain.
                $map[strtoupper($nama)] ??= $entri;
            }
        }

        return $map;
    }

    /**
     * Opsi untuk SATU dokumen: 5 peran + optgroup Bagian berisi PALING BANYAK satu
     * entri, yaitu bagian milik dokumen itu.
     *
     * Bagian kosong atau tak dikenal => tanpa optgroup Bagian sama sekali. Itu
     * disengaja: lebih baik tidak menawarkan pilihan daripada menawarkan bagian
     * yang keliru. Dokumen tanpa bagian memang sudah dihadang guard "Kolom Bagian
     * wajib diisi" di DocumentHandlerController::update() sebelum meninggalkan
     * Operator.
     *
     * @param  array<string, array{value: string, label: string}>  $bagianMap  hasil bagianMap()
     */
    public static function forDokumen(?string $bagian, array $bagianMap): array
    {
        $options = self::ROLE_OPTIONS;

        $kunci = strtoupper(trim((string) $bagian));

        if ($kunci !== '' && isset($bagianMap[$kunci])) {
            $options[] = [
                'optgroup' => 'Bagian',
                'options'  => [$bagianMap[$kunci]],
            ];
        }

        return $options;
    }
}
