<?php

namespace App\Support;

use App\Models\Dokumen;

/**
 * Posisi sebuah dokumen dalam alur keuangan, untuk dipantau role Bagian.
 *
 * Menjawab pertanyaan responden UAT: "apakah masih di surat masuk, verifikasi, atau
 * proses lainnya sebelum ke tahap pembayaran". Kolom Pengurus Dokumen di tabel Bagian
 * sebelumnya hanya menampilkan SATU label handler terakhir.
 *
 * Kelas murni: TIDAK query DB. Jejak role dioper pemanggil (hindari N+1) — pola yang
 * sama dengan App\Support\HandlerOptions & App\Support\ColumnCustomization, dan alasan
 * yang sama: bisa di-unit-test tanpa kelas inang.
 *
 * Sengaja menampilkan POSISI, bukan riwayat bercap waktu. Pemeriksaan produksi
 * 2026-08-06: dari 5.719 dokumen hanya 2 yang punya jejak aksi manusia di
 * dokumen_activity_logs; sisanya derau auto-forward impor CSV. Riwayat bercap waktu
 * akan tampil kosong pada hampir semua dokumen.
 */
class DocumentJourney
{
    /**
     * Urutan tahap kanonik. Simpul 'bagian' BUKAN tahap kerja keuangan — ia ada untuk
     * menjawab "apakah bola ada di tangan saya", dan hanya menyala saat dokumen
     * dikembalikan.
     */
    public const STAGES = [
        ['key' => 'bagian',     'label' => 'Bagian'],
        ['key' => 'operator',   'label' => 'Operator'],
        ['key' => 'verifikasi', 'label' => 'Verifikasi'],
        ['key' => 'perpajakan', 'label' => 'Perpajakan'],
        ['key' => 'akutansi',   'label' => 'Akuntansi'],
        ['key' => 'pembayaran', 'label' => 'Pembayaran'],
    ];

    /**
     * role_code di database => kunci tahap.
     *
     * Label TIDAK diambil dari Dokumen::getRoleDisplayNameIndo(): helper itu memetakan
     * 'operator' => 'Bagian' (Dokumen.php:221), yang akan membuat simpul Operator
     * berlabel sama dengan simpul Bagian.
     */
    private const ROLE_KE_TAHAP = [
        'operator'        => 'operator',
        'team_verifikasi' => 'verifikasi',
        'verifikasi'      => 'verifikasi',
        'perpajakan'      => 'perpajakan',
        'akutansi'        => 'akutansi',
        'akuntansi'       => 'akutansi',
        'pembayaran'      => 'pembayaran',
    ];

    private const INDEKS_OPERATOR = 1;

    /**
     * @param  array<int,string>  $roleCodeTerlacak  role_code yang punya received_at
     */
    public static function forDokumen(Dokumen $dokumen, array $roleCodeTerlacak = []): array
    {
        $sekarang     = self::indeksTahap($dokumen->current_handler);
        $dikembalikan = strtolower((string) $dokumen->status) === 'returned_to_bidang';
        $terlacak     = self::tahapTerlacak($roleCodeTerlacak);

        $stages = [];
        foreach (self::STAGES as $i => $tahap) {
            $stages[] = [
                'key'   => $tahap['key'],
                'label' => self::label($tahap, $dokumen),
                'state' => self::keadaan($i, $sekarang, $tahap['key'], $dokumen, $terlacak, $dikembalikan),
            ];
        }

        return [
            'current_label' => $dikembalikan
                ? 'Perlu diperbaiki'
                : self::STAGES[$sekarang]['label'],
            'current_index' => $sekarang,
            'needs_action'  => $dikembalikan,
            'stages'        => $stages,
        ];
    }

    private static function label(array $tahap, Dokumen $dokumen): string
    {
        $bagian = trim((string) $dokumen->bagian);

        if ($tahap['key'] === 'bagian' && $bagian !== '') {
            return 'Bagian (' . $bagian . ')';
        }

        return $tahap['label'];
    }

    /** Indeks tahap dari current_handler. Kosong/tak dikenal => Operator. */
    private static function indeksTahap(?string $handler): int
    {
        $kunci = self::ROLE_KE_TAHAP[strtolower(trim((string) $handler))] ?? null;

        if ($kunci === null) {
            return self::INDEKS_OPERATOR;
        }

        foreach (self::STAGES as $i => $tahap) {
            if ($tahap['key'] === $kunci) {
                return $i;
            }
        }

        return self::INDEKS_OPERATOR;
    }

    /** @return array<string,true> kunci tahap yang punya jejak */
    private static function tahapTerlacak(array $roleCodeTerlacak): array
    {
        $hasil = [];
        foreach ($roleCodeTerlacak as $role) {
            $kunci = self::ROLE_KE_TAHAP[strtolower(trim((string) $role))] ?? null;
            if ($kunci !== null) {
                $hasil[$kunci] = true;
            }
        }

        return $hasil;
    }

    private static function keadaan(
        int $i,
        int $sekarang,
        string $kunci,
        Dokumen $dokumen,
        array $terlacak,
        bool $dikembalikan
    ): string {
        // Dokumen dikembalikan: bola di tangan Bagian, tahap hilir tidak relevan.
        if ($dikembalikan) {
            return $i === 0 ? 'perlu_diperbaiki' : 'netral';
        }

        if ($i === 0) {
            return 'belum';
        }

        if ($i === $sekarang) {
            return 'sekarang';
        }

        if ($i > $sekarang) {
            return 'belum';
        }

        return self::adaJejak($kunci, $dokumen, $terlacak) ? 'selesai' : 'dilewati';
    }

    /**
     * Operator MEMBUAT dokumen, bukan menerimanya — baris dokumen_role_data miliknya
     * ber-received_at NULL (terbukti pada dokumen 5721 produksi). Tanpa pengecualian
     * ini, Operator akan selalu tertandai 'dilewati'.
     */
    private static function adaJejak(string $kunci, Dokumen $dokumen, array $terlacak): bool
    {
        if ($kunci === 'operator') {
            return $dokumen->tanggal_masuk !== null || $dokumen->created_at !== null;
        }

        return isset($terlacak[$kunci]);
    }
}
