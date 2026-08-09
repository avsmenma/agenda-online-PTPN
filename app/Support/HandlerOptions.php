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
 * Pembayaran IKUT memakai kelas ini sejak kolom "Pengurus Dokumen" dihidupkan
 * untuk role tersebut (dashboardPembayaran.blade.php: showHandler => true);
 * kalimat lama yang menyatakan sebaliknya sudah tidak berlaku.
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
     * Peran yang TIDAK punya jalur mundur: tak boleh menunjuk Operator maupun
     * Bagian. Dokumen bermasalah dikembalikan lewat Tim Verifikasi.
     *
     * Melengkapi keputusan 2026-07-24 yang menghapus halaman "Pengembalian"
     * perpajakan & akutansi — halamannya hilang, tapi dropdown Pengurus Dokumen
     * masih menyediakan jalurnya sampai perubahan ini.
     */
    private const TANPA_JALUR_MUNDUR = [
        'perpajakan',
        'akutansi',
        'pembayaran',
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
     * Bolehkah $rolePengguna memindahkan dokumen ke $target?
     *
     * Dipakai DUA sisi supaya UI dan server tak pernah berbeda aturan:
     * forDokumen() memangkas opsi dengannya, DocumentHandlerController::update()
     * menolak dengan 403 memakainya.
     *
     * Kedua argumen dinormalisasi: Role::ALIASES memuat 'akuntansi' => 'akutansi'
     * (ejaan proyek ini memang menyimpang), jadi perbandingan mentah akan
     * meloloskan akun beralias tanpa suara.
     */
    public static function bolehMenunjuk(?string $rolePengguna, string $target): bool
    {
        if (!in_array(Role::normalize($rolePengguna), self::TANPA_JALUR_MUNDUR, true)) {
            return true;
        }

        $tujuan = Role::normalize($target);

        return $tujuan !== 'operator' && !str_starts_with($tujuan, 'bagian_');
    }

    /**
     * Opsi untuk SATU dokumen: 5 peran + optgroup Bagian berisi PALING BANYAK satu
     * entri, yaitu bagian milik dokumen itu — lalu dipangkas sesuai wewenang
     * $rolePengguna.
     *
     * Bagian kosong atau tak dikenal => tanpa optgroup Bagian sama sekali. Itu
     * disengaja: lebih baik tidak menawarkan pilihan daripada menawarkan bagian
     * yang keliru. Dokumen tanpa bagian memang sudah dihadang guard "Kolom Bagian
     * wajib diisi" di DocumentHandlerController::update() sebelum meninggalkan
     * Operator.
     *
     * @param  array<string, array{value: string, label: string}>  $bagianMap  hasil bagianMap()
     * @param  ?string  $rolePengguna        peran penonton; null = tanpa sesi, tak dilarang
     * @param  ?string  $handlerDipertahankan nilai pengurus yang SEDANG DITAMPILKAN untuk
     *                                        baris ini (DocumentRow::handlerTampilanMentah)
     */
    public static function forDokumen(
        ?string $bagian,
        array $bagianMap,
        ?string $rolePengguna,
        ?string $handlerDipertahankan
    ): array {
        $options = self::ROLE_OPTIONS;

        $kunci = strtoupper(trim((string) $bagian));

        if ($kunci !== '' && isset($bagianMap[$kunci])) {
            $options[] = [
                'optgroup' => 'Bagian',
                'options'  => [$bagianMap[$kunci]],
            ];
        }

        return self::pangkasTerlarang($options, $rolePengguna, $handlerDipertahankan);
    }

    /**
     * Buang opsi yang tak boleh ditunjuk $rolePengguna.
     *
     * Opsi yang kebetulan merupakan pengurus yang SEDANG DITAMPILKAN untuk baris
     * itu TIDAK dibuang, melainkan ditandai 'disabled' => true. Kalau dibuang,
     * <select> kehilangan nilai terpilihnya dan browser jatuh ke opsi pertama —
     * tabel lalu menampilkan pengurus yang keliru. Bahaya itu sudah pernah
     * terjadi; lihat catatan di DocumentRow::handlerUntukTampilan().
     */
    private static function pangkasTerlarang(
        array $options,
        ?string $rolePengguna,
        ?string $handlerDipertahankan
    ): array {
        $hasil = [];

        foreach ($options as $opsi) {
            if (isset($opsi['optgroup'])) {
                $anak = [];

                foreach (($opsi['options'] ?? []) as $o) {
                    $disaring = self::saringOpsi($o, $rolePengguna, $handlerDipertahankan);

                    if ($disaring !== null) {
                        $anak[] = $disaring;
                    }
                }

                if ($anak !== []) {
                    $hasil[] = ['optgroup' => $opsi['optgroup'], 'options' => $anak];
                }

                continue;
            }

            $disaring = self::saringOpsi($opsi, $rolePengguna, $handlerDipertahankan);

            if ($disaring !== null) {
                $hasil[] = $disaring;
            }
        }

        return $hasil;
    }

    /** Null bila opsi harus dibuang; array opsi (mungkin ber-'disabled') bila dipertahankan. */
    private static function saringOpsi(
        array $opsi,
        ?string $rolePengguna,
        ?string $handlerDipertahankan
    ): ?array {
        $nilai = (string) ($opsi['value'] ?? '');

        if (self::bolehMenunjuk($rolePengguna, $nilai)) {
            return $opsi;
        }

        if ($handlerDipertahankan !== null && $nilai === $handlerDipertahankan) {
            return $opsi + ['disabled' => true];
        }

        return null;
    }
}
