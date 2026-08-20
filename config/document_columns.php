<?php

/**
 * Kolom Tabel Dokumen — Sumber Kebenaran Terpusat (base)
 *
 * Peta `key => label` kolom yang bisa dikustomisasi pada tabel daftar dokumen.
 * Sebelumnya array ini disalin identik di beberapa controller (Operator, Team
 * Verifikasi, Akutansi, Perpajakan) sehingga rawan drift. Sekarang satu sumber.
 *
 * Pemakaian:
 *   $availableColumns = config('document_columns.base');                        // Operator (punya 'status')
 *   $availableColumns = Arr::except(config('document_columns.base'), ['status']); // Verifikasi/Akutansi/Perpajakan(index)
 *
 * CATATAN — yang SENGAJA tidak memakai base ini (bukan duplikat, jangan dipaksa):
 *   - Pembayaran (dashboardPembayaran & varian rekapan): memakai LABEL berbeda
 *     (disingkat, mis. 'No SPP'/'TGL SPP') + kolom khusus pembayaran
 *     (nilai_siap_bayar, umur_dokumen_tanggal_*, dst).
 *   - Perpajakan (varian kedua/deadline): set kecil khusus (deadline_at, created_at).
 *   - Bagian: base −'link' +'umur_dokumen'/'status_pembayaran'.
 *   Menyeragamkan label mereka ke base akan MENGUBAH tampilan — di luar lingkup ini.
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Base — dipakai Operator (apa adanya) & Verifikasi/Akutansi/Perpajakan
    | (dengan Arr::except 'status'). Urutan menentukan urutan opsi di modal
    | "Kustomisasi Kolom", jadi JANGAN diubah tanpa alasan.
    |--------------------------------------------------------------------------
    */
    'base' => [
        'nomor_agenda' => 'Nomor Agenda',
        'bulan' => 'Bulan',
        'tahun' => 'Tahun',
        'kategori' => 'Kriteria CF',
        'jenis_dokumen' => 'Sub Kriteria',
        'jenis_sub_pekerjaan' => 'Item Sub Kriteria',
        'jenis_pembayaran' => 'Jenis Pembayaran',
        'nomor_spp' => 'Nomor SPP',
        'tanggal_spp' => 'Tanggal SPP',
        'tanggal_masuk' => 'Tanggal Masuk',
        'dibayar_kepada' => 'Dibayar Kepada',
        'uraian_spp' => 'Uraian SPP',
        'nilai_rupiah' => 'Nilai Rupiah',
        // Backend later columns
        'tanggal_paraf' => 'Tanggal Paraf',
        'pemaraf' => 'Pemaraf',
        'tanggal_selesai_diproses' => 'Tgl Selesai Diproses',
        'tanggal_kembali_ke_bagian' => 'Tgl Kembali ke Bagian',
        'tanggal_hasil_koreksi_bagian' => 'Tgl Hasil Koreksi Bagian',
        'kepala_sub_bagian' => 'Kepala Sub Bagian',
        'keterangan' => 'Keterangan',
        'status_dokumen_custom' => 'Status Dokumen',
        'tanggal_dibayar' => 'Tanggal Bayar',
        'bagian' => 'Bagian',
        'link' => 'Link',
        'nama_pengirim' => 'Nama Pengirim',
        'no_spk' => 'No SPK',
        'tanggal_spk' => 'Tanggal SPK',
        'tanggal_berakhir_spk' => 'Tanggal Akhir SPK',
        'no_berita_acara' => 'No Berita Acara (BA)',
        'tanggal_berita_acara' => 'Tanggal Berita Acara (BA)',
        'nomor_po' => 'No PO',
        'nomor_miro' => 'No Miro',
        'no_faktur' => 'No Faktur',
        'tanggal_faktur' => 'Tanggal Faktur',
        'tanggal_selesai_verifikasi_pajak' => 'Tgl Selesai Verifikasi Pajak',
        'jenis_pph' => 'Jenis PPh',
        'dpp_pph' => 'DPP PPh',
        'ppn_terhutang' => 'PPH Terhutang',
        // Role-specific columns
        'status' => 'Status',
        'kebun' => 'Kebun',
        // Perpajakan data (read-only view for Operator)
        'npwp' => 'NPWP',
        'link_dokumen_pajak' => 'Link Dokumen Pajak',
    ],

    /**
     * Cache per-baris DTO tabel dokumen — lihat App\Support\DocumentRowCache
     * untuk desain kunci & tiga lapis invalidasinya.
     *
     * `enabled` sengaja dapat dimatikan lewat env tanpa deploy ulang: kalau suatu
     * saat muncul dugaan data basi di tabel, setel DOCUMENT_ROW_CACHE=false lalu
     * `php artisan config:clear` untuk kembali ke perhitungan langsung.
     *
     * `ttl` (detik) adalah jaring TERAKHIR, bukan mekanisme utama — invalidasi
     * sebenarnya datang dari sidik jari updated_at & versi global. Menaikkannya
     * memperpanjang jendela basi untuk penulisan yang melewati event model.
     */
    'cache' => [
        'enabled' => env('DOCUMENT_ROW_CACHE', true),
        'ttl'     => (int) env('DOCUMENT_ROW_CACHE_TTL', 300),
    ],
];
