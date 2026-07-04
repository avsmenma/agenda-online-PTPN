<?php

namespace App\Support;

/**
 * Sumber tunggal kode peran (role) kanonik + normalisasi alias.
 *
 * Warisan AI/dev lama menaruh banyak alias untuk peran yang sama:
 *  - 'verifikasi' / 'ibu b' / 'ibu yuni'  → team_verifikasi
 *  - 'tarapul' / 'ibu a'                  → operator
 *  - 'akuntansi'                          → akutansi (ejaan proyek ini disengaja)
 *  - campur-case 'Admin' / 'Akutansi'     → admin / akutansi
 *
 * Semua perbandingan/normalisasi peran seharusnya lewat kelas ini agar
 * keputusan otorisasi konsisten.
 */
class Role
{
    // Kode peran kanonik.
    public const OPERATOR = 'operator';
    public const TEAM_VERIFIKASI = 'team_verifikasi';
    public const PERPAJAKAN = 'perpajakan';
    public const AKUTANSI = 'akutansi';
    public const PEMBAYARAN = 'pembayaran';
    public const ADMIN = 'admin';
    public const OWNER = 'owner';
    public const PROGRAMMER = 'programmer';
    public const SYSTEM = 'system';

    /**
     * Peta alias legacy → kode kanonik. Kunci HARUS lowercase (dicocokkan
     * setelah string di-lowercase + trim).
     */
    private const ALIASES = [
        'verifikasi'      => self::TEAM_VERIFIKASI,
        'team verifikasi' => self::TEAM_VERIFIKASI,
        'tim verifikasi'  => self::TEAM_VERIFIKASI,
        'ibu b'           => self::TEAM_VERIFIKASI,
        'ibub'            => self::TEAM_VERIFIKASI,
        'ibu_b'           => self::TEAM_VERIFIKASI,
        'ibu yuni'        => self::TEAM_VERIFIKASI,
        'ibu_yuni'        => self::TEAM_VERIFIKASI,
        'ibuyuni'         => self::TEAM_VERIFIKASI,
        'tarapul'         => self::OPERATOR,
        'ibu tarapul'     => self::OPERATOR,
        'ibu_tarapul'     => self::OPERATOR,
        'ibutarapul'      => self::OPERATOR,
        'ibu a'           => self::OPERATOR,
        'ibu_a'           => self::OPERATOR,
        'akuntansi'       => self::AKUTANSI,
        'tim akuntansi'   => self::AKUTANSI,
        'team akuntansi'  => self::AKUTANSI,
        'tim akutansi'    => self::AKUTANSI,
        'team akutansi'   => self::AKUTANSI,
        'tim perpajakan'  => self::PERPAJAKAN,
        'team perpajakan' => self::PERPAJAKAN,
        'tim pembayaran'  => self::PEMBAYARAN,
        'team pembayaran' => self::PEMBAYARAN,
    ];

    /**
     * Normalisasi string peran apa pun (case-insensitive, alias, spasi) ke kode
     * kanonik. Nilai kanonik/tak dikenal dikembalikan lowercase dengan spasi→underscore
     * (mis. 'bagian_akn' tetap 'bagian_akn').
     */
    public static function normalize(?string $role): string
    {
        $key = strtolower(trim((string) ($role ?? '')));

        if ($key === '') {
            return '';
        }

        if (isset(self::ALIASES[$key])) {
            return self::ALIASES[$key];
        }

        return str_replace(' ', '_', $key);
    }

    /**
     * True bila dua string peran menunjuk peran yang sama setelah normalisasi.
     */
    public static function matches(?string $a, ?string $b): bool
    {
        $na = self::normalize($a);

        return $na !== '' && $na === self::normalize($b);
    }
}
