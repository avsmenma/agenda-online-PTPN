<?php

namespace App\Support;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Sanitasi URL untuk mencegah stored-XSS lewat skema berbahaya
 * (mis. `javascript:`, `data:`, `vbscript:`) yang ditanam pada kolom link
 * dokumen lalu dirender ke atribut href.
 *
 * Satu sumber kebenaran: dipakai di sisi tulis (validasi sebelum simpan) dan
 * di sisi render (pertahanan berlapis di Blade & response inline-edit).
 */
class SafeUrl
{
    /**
     * Kembalikan URL yang AMAN untuk dirender ke atribut href, atau null bila
     * tidak ada nilai.
     *
     * Kontrak keamanan: hasilnya SELALU null atau URL berskema http(s).
     * - kosong/null                 -> null (tidak dirender)
     * - sudah http:// / https://    -> dikembalikan apa adanya
     * - selain itu (tanpa skema, atau skema berbahaya seperti javascript:)
     *   -> dipaksa diberi prefix "https://" sehingga menjadi inert
     *      (browser memperlakukannya sebagai URL http biasa, bukan skrip).
     */
    public static function external(?string $url): ?string
    {
        $url = trim((string) ($url ?? ''));
        if ($url === '') {
            return null;
        }

        if (self::startsWithHttpScheme($url)) {
            return $url;
        }

        // Tanpa skema http(s): paksa https:// (sekaligus menetralkan
        // javascript:/data:/dll. karena tidak pernah keluar sebagai skema itu).
        return 'https://' . ltrim($url, '/');
    }

    /**
     * True bila nilai mengandung skema URI eksplisit selain http/https
     * (mis. javascript:, data:, vbscript:, file:, mailto:). Dipakai di sisi
     * tulis untuk MENOLAK input berbahaya dengan pesan jelas, alih-alih
     * menyimpan nilai inert.
     *
     * host:port (mis. "contoh.com:8080") TIDAK dianggap skema karena bagian
     * setelah ":" berupa digit.
     */
    public static function hasDangerousScheme(?string $url): bool
    {
        $probe = self::stripHiddenChars((string) ($url ?? ''));
        if ($probe === '') {
            return false;
        }

        if (self::startsWithHttpScheme($probe)) {
            return false;
        }

        // Skema URI: diawali huruf lalu [a-z0-9+.-]* lalu ":" — dan karakter
        // setelah ":" BUKAN digit (untuk membedakan dari host:port).
        return (bool) preg_match('/^[a-z][a-z0-9+.\-]*:(?![0-9])/i', $probe);
    }

    /**
     * Normalisasi + validasi nilai link untuk DISIMPAN (store/update).
     *
     * - kosong/null            -> null
     * - skema berbahaya        -> ValidationException (ditolak, pesan Bahasa Indonesia)
     * - tanpa skema            -> di-prefix https:// lalu divalidasi
     * - http(s) valid          -> dikembalikan apa adanya
     *
     * @throws ValidationException bila nilai bukan URL http(s) yang valid.
     */
    public static function sanitizeForStorage(?string $url): ?string
    {
        $raw = trim((string) ($url ?? ''));
        if ($raw === '') {
            return null;
        }

        if (self::hasDangerousScheme($raw)) {
            throw ValidationException::withMessages([
                'link' => 'Link harus diawali http:// atau https://.',
            ]);
        }

        $normalized = self::external($raw); // dijamin berskema http(s)

        Validator::make(
            ['link' => $normalized],
            ['link' => ['nullable', 'url', 'starts_with:http://,https://']],
            [
                'link.url'         => 'Format link tidak valid.',
                'link.starts_with' => 'Link harus diawali http:// atau https://.',
            ]
        )->validate();

        return $normalized;
    }

    private static function startsWithHttpScheme(string $value): bool
    {
        $probe = self::stripHiddenChars($value);

        return stripos($probe, 'http://') === 0 || stripos($probe, 'https://') === 0;
    }

    /**
     * Buang whitespace & karakter kontrol (termasuk yang tersembunyi seperti
     * tab/newline) yang bisa dipakai mengaburkan skema, contoh "java\tscript:".
     */
    private static function stripHiddenChars(string $value): string
    {
        return preg_replace('/[\x00-\x20]+/', '', $value) ?? '';
    }
}
