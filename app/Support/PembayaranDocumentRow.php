<?php

namespace App\Support;

use App\Models\Dokumen;

/**
 * DTO baris tabel pembayaran (Tabulator, Rollout 4 — outlier). Mewarisi
 * derivasi bersama dari App\Support\DocumentRow dan menambah bit khas
 * pembayaran:
 *
 *   - can_edit: SELALU true (edit-anywhere). Team Pembayaran boleh mengedit
 *     dokumen kapan pun, TERMASUK sebelum dokumen sampai di role pembayaran.
 *     Ini aturan bisnis pembayaran yang DISENGAJA — JANGAN panggil
 *     DokumenHelper::canEditDocument() atau gate is_at_my_role di sini
 *     (beda dari akutansi/verifikasi yang menggerbang can_edit).
 *   - status_badge: pill status 3-state, reimplementasi logika lama
 *     getPembayaranComputedStatus() + renderPembayaranStatusPill() (method
 *     controller sudah dihapus, lihat buildStatusBadge() di bawah). Bentuk
 *     {state, class, text} — BUKAN {class, icon, text, link} seperti role
 *     lain (verifikasi/akutansi/perpajakan).
 *
 * TANPA deadline: pembayaran tak punya kolom deadline per-baris — row ini
 * TIDAK PERNAH mengeluarkan key 'deadline' (beda dari akutansi/perpajakan/
 * verifikasi yang punya buildDeadline()).
 *
 * DENGAN forward: base menaruh handler/handler_options/can_change_handler dan
 * ketiganya kini DIPAKAI — kolom "Pengurus Dokumen" dihidupkan untuk pembayaran
 * (dashboardPembayaran.blade.php: showHandler => true). Kalimat lama di sini yang
 * menyatakan kolomnya disembunyikan sudah tidak berlaku. Sejak 2026-08-09 opsi
 * Operator & Bagian dipangkas untuk role ini (App\Support\HandlerOptions).
 *
 * Kolom katalog (getPembayaranDashboardAvailableColumns(),
 * DashboardPembayaranController.php:911-961): sebagian besar sudah dipasok
 * DocumentRow::baseRow() lewat config('document_columns.base') + turunan
 * rupiah/tanggal/join. Sisa kolom khas pembayaran (bukan bagian base)
 * ditambahkan RAW di sini — klien (engine getFormatter, Task 3) yang
 * memformat, DTO ini nol logika tampilan untuk kolom-kolom tsb:
 *   - status_pembayaran, link_bukti_pembayaran: dipakai juga oleh
 *     buildStatusBadge() di bawah, disediakan mentah untuk kolom tabel.
 *   - nomor_mirror: kolom mentah TERPISAH dari nomor_miro/nomor_miro_display
 *     (sudah dipasok baseRow() via accessor gabungan) — nomor_mirror adalah
 *     kolom legacy sendiri dari CSV import awal, BUKAN alias nomor_miro.
 *   - tanggal_berakhir_ba: kolom legacy yang TIDAK ADA di skema tabel
 *     `dokumens` (bukan typo di sini — cek migrations: nihil). Akses
 *     Eloquent magic getter aman (selalu null), port apa adanya seperti
 *     DashboardPembayaranController::getPembayaranDashboardCellValue()
 *     lama yang juga selalu jatuh ke '-'.
 *   - umur_dokumen_tanggal_masuk/spp/ba: BUKAN kolom database — nilai umur
 *     (hari) dihitung dari tanggal mentah (tanggal_masuk/tanggal_spp/
 *     tanggal_berita_acara, sudah tersedia raw di row via base) di sisi
 *     KLIEN (Task 3). Di sini hanya key placeholder (null) — perhitungan
 *     umur BUKAN tugas DTO ini, hindari menebak rumus bisnis ulang di server.
 *
 * Prasyarat data — WAJIB DISEDIAKAN PEMANGGIL (endpoint Task 2):
 *   - roleStatuses, dibayarKepadas, dokumenPos ter-eager-load (dipakai
 *     DocumentRow::baseRow(); basis TIDAK query DB sendiri).
 *   - roleData eager-load role_code='pembayaran' (disiapkan untuk pemakaian
 *     masa depan endpoint/deadline-cards; DTO ini SENDIRI tidak memakainya).
 *   - Endpoint WAJIB INCLUDE dokumen CSV-import (imported_from_csv=true) —
 *     BEDA dari role lain (akutansi/perpajakan/team_verifikasi) yang
 *     MENGECUALIKAN CSV secara default. Pembayaran melihat SEMUA dokumen
 *     tanpa batas current_handler (paritas
 *     DashboardPembayaranController::index():338 — query dasar hanya
 *     whereNotNull('nomor_agenda'), tanpa scope role atau CSV).
 */
class PembayaranDocumentRow extends DocumentRow
{
    public static function fromDokumen(Dokumen $dokumen, array $handlerOptions = [], ?string $viewerRole = 'pembayaran'): array
    {
        $row = static::baseRow($dokumen, $handlerOptions, $viewerRole);

        // Edit-anywhere: aturan bisnis pembayaran yang disengaja (bukan lupa gate).
        $row['can_edit'] = true;

        // === Kolom katalog pembayaran yang belum dipasok baseRow() (raw). ===
        $row['status_pembayaran']          = $dokumen->status_pembayaran;
        $row['link_bukti_pembayaran']      = $dokumen->link_bukti_pembayaran;
        $row['nomor_mirror']               = $dokumen->nomor_mirror;
        $row['tanggal_berakhir_ba']        = $dokumen->tanggal_berakhir_ba;
        $row['umur_dokumen_tanggal_masuk'] = $dokumen->umur_dokumen_tanggal_masuk;
        $row['umur_dokumen_tanggal_spp']   = $dokumen->umur_dokumen_tanggal_spp;
        $row['umur_dokumen_tanggal_ba']    = $dokumen->umur_dokumen_tanggal_ba;

        $row['status_badge'] = static::buildStatusBadge($dokumen);

        return $row;
    }

    /**
     * Reimplementasi logika lama getPembayaranComputedStatus() +
     * renderPembayaranStatusPill() (method controller sudah dihapus) → pill
     * 3-state siap-render. Bentuk {state, class, text}. Toleransi casing CSV
     * lama: "SUDAH DIBAYAR" (spasi) / "SUDAH_DIBAYAR" (underscore), keduanya
     * uppercase, turut dipetakan ke paid — bukan hanya "sudah_dibayar"
     * lowercase kanonik.
     */
    protected static function buildStatusBadge(Dokumen $dokumen): array
    {
        $sp = strtoupper(trim((string) ($dokumen->status_pembayaran ?? '')));

        if ($dokumen->tanggal_dibayar || $dokumen->link_bukti_pembayaran
            || $sp === 'SUDAH_DIBAYAR' || $sp === 'SUDAH DIBAYAR' || $dokumen->status_pembayaran === 'sudah_dibayar') {
            return ['state' => 'sudah_dibayar', 'class' => 'status-pill--paid', 'text' => 'Sudah Dibayar'];
        }

        if ($dokumen->current_handler === 'pembayaran' || $dokumen->status === 'sent_to_pembayaran') {
            return ['state' => 'siap_dibayar', 'class' => 'status-pill--ready', 'text' => 'Siap Dibayar'];
        }

        return ['state' => 'belum_siap_dibayar', 'class' => 'status-pill--pending', 'text' => 'Belum Siap'];
    }
}
