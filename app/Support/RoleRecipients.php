<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Sumber TUNGGAL pencarian penerima notifikasi WhatsApp per kode peran.
 *
 * Diekstrak dari LateDocumentNotificationService::getUsersByRole() saat
 * PendingApprovalReminderService dibuat, supaya kedua layanan pengingat memakai
 * aturan pemetaan peran yang sama — bukan dua salinan yang lambat laun menyimpang.
 *
 * Pemetaan alias diperlukan karena kolom `users.role` di produksi menyimpan
 * beberapa ejaan lama untuk peran yang sama.
 */
class RoleRecipients
{
    /**
     * Alias ejaan kolom users.role untuk tiap kode peran alur kerja.
     * Dipertahankan apa adanya dari layanan lama (jangan dipersempit tanpa
     * memeriksa data produksi lebih dulu).
     */
    private const ALIAS = [
        'team_verifikasi' => ['team_verifikasi', 'verifikasi', 'Team Verifikasi'],
        'perpajakan'      => ['perpajakan', 'Perpajakan'],
        'akutansi'        => ['akutansi', 'Akutansi'],
        'pembayaran'      => ['pembayaran', 'Pembayaran'],
        'operator'        => ['operator', 'Operator'],
    ];

    /**
     * User pemegang peran ini YANG PUNYA nomor HP. Kosong bukan kondisi error —
     * per 2026-08-06 role akutansi & pembayaran belum satu pun mengisi nomor,
     * sehingga pengingat untuk mereka memang belum bisa terkirim.
     */
    public static function withPhone(string $roleCode): Collection
    {
        return self::query($roleCode)
            ->whereNotNull('phone_number')
            ->where('phone_number', '!=', '')
            ->get();
    }

    /** Semua user pemegang peran ini, terlepas dari ada tidaknya nomor HP. */
    public static function all(string $roleCode): Collection
    {
        return self::query($roleCode)->get();
    }

    private static function query(string $roleCode)
    {
        return User::whereIn('role', self::ALIAS[$roleCode] ?? [$roleCode]);
    }
}
