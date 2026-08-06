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
     * @param  array<int,string>  $roleCodeMenunggu  role_code yang baris dokumen_statuses-nya
     *                                                masih 'pending' (sudah dikirim, belum
     *                                                diterima tahap tujuan)
     * @param  bool  $lunas  dokumen sudah dibayar lunas — menang MUTLAK atas semua
     *                        aturan lain (termasuk $dikembalikan): seluruh tahap
     *                        'selesai', nol titik biru.
     */
    public static function forDokumen(
        Dokumen $dokumen,
        array $roleCodeTerlacak = [],
        array $roleCodeMenunggu = [],
        bool $lunas = false
    ): array {
        if ($lunas) {
            return self::hasilLunas($dokumen);
        }

        $sekarang     = self::indeksTahap($dokumen->current_handler);
        $dikembalikan = strtolower((string) $dokumen->status) === 'returned_to_bidang';
        $terlacak     = self::tahapTerlacak($roleCodeTerlacak);

        // Dokumen dikembalikan menang mutlak — tahap tujuan pending tidak relevan lagi
        // begitu bola kembali ke Bagian.
        $indeksMenunggu = $dikembalikan ? null : self::indeksMenunggu($roleCodeMenunggu);
        $efektif        = $indeksMenunggu ?? $sekarang;

        $stages = [];
        foreach (self::STAGES as $i => $tahap) {
            $stages[] = [
                'key'   => $tahap['key'],
                'label' => self::label($tahap, $dokumen),
                'state' => self::keadaan($i, $efektif, $tahap['key'], $dokumen, $terlacak, $dikembalikan, $indeksMenunggu),
            ];
        }

        $labelSekarang = self::STAGES[$efektif]['label'];
        if ($indeksMenunggu !== null) {
            // Jujur ke user: dokumen SUDAH DIKIRIM tapi BELUM DITERIMA tahap tujuan.
            $labelSekarang = 'Menunggu ' . $labelSekarang;
        }

        return [
            'current_label' => $dikembalikan ? 'Perlu diperbaiki' : $labelSekarang,
            'current_index' => $efektif,
            'needs_action'  => $dikembalikan,
            'stages'        => $stages,
        ];
    }

    /**
     * Dokumen lunas: seluruh tahap 'selesai' termasuk simpul Bagian, nol titik biru.
     * Menang mutlak atas $dikembalikan — begitu dokumen terbayar, status
     * returned_to_bidang jadi riwayat, bukan lagi state aktif yang perlu ditindak.
     */
    private static function hasilLunas(Dokumen $dokumen): array
    {
        $stages = [];
        foreach (self::STAGES as $tahap) {
            $stages[] = [
                'key'   => $tahap['key'],
                'label' => self::label($tahap, $dokumen),
                'state' => 'selesai',
            ];
        }

        return [
            'current_label' => 'Selesai dibayar',
            'current_index' => count(self::STAGES) - 1,
            'needs_action'  => false,
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

    /**
     * Indeks tahap TERKECIL di antara role_code yang statusnya masih pending. Dipakai
     * (bukan diduga) sebagai posisi efektif dokumen — sendToRoleInbox() menulis baris
     * dokumen_statuses 'pending' untuk tahap tujuan TANPA memajukan current_handler,
     * jadi current_handler saja bisa telat sampai berhari-hari.
     *
     * "Lebih dari satu tahap pending" seharusnya tak terjadi, tapi bila terjadi dipilih
     * indeks terkecil — STAGES sudah berurutan menaik jadi cukup ambil kecocokan pertama.
     */
    private static function indeksMenunggu(array $roleCodeMenunggu): ?int
    {
        $tahapMenunggu = self::tahapTerlacak($roleCodeMenunggu);

        foreach (self::STAGES as $i => $tahap) {
            if (isset($tahapMenunggu[$tahap['key']])) {
                return $i;
            }
        }

        return null;
    }

    private static function keadaan(
        int $i,
        int $efektif,
        string $kunci,
        Dokumen $dokumen,
        array $terlacak,
        bool $dikembalikan,
        ?int $indeksMenunggu
    ): string {
        // Dokumen dikembalikan: bola di tangan Bagian, simpul Bagian menyala oranye.
        // Tahap lain dihitung dengan aturan NORMAL (selesai bila ada jejak, belum
        // bila tidak) — BUKAN lagi 'netral' seragam: tahap yang sudah dilalui
        // sebelum dikembalikan tetap harus tampak hijau, bukan disamaratakan abu-abu.
        if ($dikembalikan) {
            if ($i === 0) {
                return 'perlu_diperbaiki';
            }

            return self::adaJejak($kunci, $dokumen, $terlacak) ? 'selesai' : 'belum';
        }

        if ($i === 0) {
            return 'belum';
        }

        if ($i === $efektif) {
            // $efektif == $indeksMenunggu setiap kali tahap tujuan masih pending
            // (lihat forDokumen()) — jadi cukup periksa apakah nilainya ada.
            return $indeksMenunggu !== null ? 'menunggu_diterima' : 'sekarang';
        }

        // Tahap DI DEPAN posisi sekarang bisa saja sudah pernah dilalui — dokumen
        // MUNDUR (mis. DocumentHandlerController::returnDirectlyToOperator() /
        // moveDirectlyToTeamVerifikasi() dari role hilir) tetap meninggalkan jejak
        // received_at di tahap yang kini berada di depan current_handler. Jangan
        // menghapus jejak itu jadi 'belum' begitu saja.
        if ($i > $efektif) {
            return self::adaJejak($kunci, $dokumen, $terlacak) ? 'selesai' : 'belum';
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
