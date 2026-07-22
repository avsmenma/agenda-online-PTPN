<?php

namespace App\Support;

/**
 * Cache-busting untuk aset lokal (css/js) yang disajikan nginx.
 *
 * nginx (`/etc/nginx/sites-enabled/agenda:21-26`) mengirim
 * `Cache-Control: public, max-age=31536000, immutable` untuk semua .css/.js.
 * Header itu hanya aman kalau nama file berubah setiap isinya berubah —
 * aset proyek ini justru punya nama stabil (mis. `tabulator-agenda.css`),
 * jadi browser lama tidak pernah mengambil versi baru meski di-refresh biasa.
 *
 * Solusinya BUKAN mengubah konfigurasi nginx (sengaja tidak disentuh),
 * melainkan menambah query string `?v=<mtime>` supaya URL berubah setiap
 * file berubah — nginx tetap menganggapnya "aset baru" dan mengirim body
 * terbaru, sedangkan file yang tidak berubah tetap kena cache normal.
 */
class Asset
{
    /**
     * Kembalikan URL asset() dengan suffix `?v=<mtime>` bila berkasnya ada.
     *
     * Kontrak:
     * - berkas ADA    -> "{asset(path)}?v={filemtime}" (stabil selama file
     *                     tidak berubah, berubah begitu file ditulis ulang).
     * - berkas TIDAK ADA -> asset(path) apa adanya, TANPA warning PHP dan
     *                     TANPA exception. Halaman tidak boleh gagal render
     *                     hanya karena satu aset hilang.
     *
     * @param  string  $relativePath  path relatif terhadap public/, mis. 'css/app.css'
     */
    public static function versioned(string $relativePath): string
    {
        $url = asset($relativePath);

        // "@" membungkam warning bila berkas tidak ada/tidak terbaca — filemtime()
        // di PHP tetap mengeluarkan E_WARNING untuk kasus itu, bukan exception.
        $mtime = @filemtime(public_path($relativePath));

        if ($mtime === false) {
            return $url;
        }

        return $url . '?v=' . $mtime;
    }
}
