<?php

namespace App\Support;

use App\Models\Dokumen;
use Illuminate\Support\Facades\Cache;

/**
 * Cache PER-BARIS untuk DTO tabel dokumen (App\Support\*DocumentRow).
 *
 * Alasan ada: profil produksi 2026-08-20 menunjukkan fromDokumen() memakan porsi
 * terbesar tiap request endpoint /documents/*\/data — 54,7 ms per 100 baris setelah
 * perampingan (sebelumnya 85 ms). Padahal datanya nyaris tak pernah berubah:
 * 46 dokumen diubah per 24 jam dari 6.068 baris.
 *
 * === KENAPA PER-BARIS, BUKAN PER-HALAMAN ===
 * Per-halaman jauh lebih cepat (0,7 ms vs 9,1 ms untuk 100 baris), TAPI kuncinya
 * harus memuat setiap parameter yang memengaruhi hasil: search, year, status_filter,
 * tujuh filter lanjutan, page, size, sampai urutan sortir operator yang disimpan di
 * SESI (bukan URL). Satu parameter terlewat = user melihat hasil filter milik user
 * lain. Dengan kunci per-baris, query tetap dijalankan penuh untuk menentukan baris
 * MANA yang tampil — filter & pencarian tidak pernah menjadi bagian dari kunci,
 * jadi kelas kesalahan itu tidak mungkin terjadi. Keputusan user 2026-08-20.
 *
 * === TIGA LAPIS INVALIDASI ===
 * 1. Sidik jari `updated_at` di kunci — perubahan dokumen lewat Eloquent MAUPUN
 *    lewat DB::table()->update() yang menyetel updated_at langsung menghasilkan
 *    kunci baru. (OperatorCsvImportController menyetel updated_at, jadi tercakup.)
 * 2. Versi global — dinaikkan oleh event model pada Dokumen, DokumenStatus,
 *    DokumenRoleData, DokumenPO, DibayarKepada, dan Bagian. Ini yang menangkap
 *    perubahan RELASI: badge status, deadline, nomor PO, penerima, dan peta bagian
 *    penyusun handler_options — semuanya tidak menyentuh dokumens.updated_at karena
 *    model anak tidak punya $touches (diperiksa 2026-08-20).
 * 3. TTL — jaring terakhir untuk tulis mentah yang melewati keduanya.
 *
 * Batas yang diketahui: penulisan massal lewat query builder (`Dokumen::where(...)
 * ->update([...])`) tidak memicu event model. Selama isian update menyertakan
 * updated_at, lapis 1 menangkapnya; kalau tidak, lapis 3 membatasi basi ke TTL.
 *
 * Matikan lewat env `DOCUMENT_ROW_CACHE=false` (config document_columns.cache.enabled)
 * tanpa perlu deploy ulang kode.
 */
class DocumentRowCache
{
    private const KUNCI_VERSI = 'docrow:versi';

    /** Versi global. Dibuat sekali, lalu hanya dinaikkan. */
    public static function versi(): int
    {
        $versi = Cache::get(self::KUNCI_VERSI);

        if ($versi === null) {
            Cache::forever(self::KUNCI_VERSI, 1);

            return 1;
        }

        return (int) $versi;
    }

    /**
     * Naikkan versi global — membuat SELURUH baris ter-cache jadi tak terjangkau
     * sekaligus (entri lamanya kedaluwarsa sendiri lewat TTL, tidak perlu dihapus).
     */
    public static function naikkanVersi(): void
    {
        if (Cache::get(self::KUNCI_VERSI) === null) {
            Cache::forever(self::KUNCI_VERSI, 1);
        }

        Cache::increment(self::KUNCI_VERSI);
    }

    public static function aktif(): bool
    {
        return (bool) config('document_columns.cache.enabled', true);
    }

    /**
     * Petakan koleksi Dokumen menjadi array baris DTO, memakai cache bila boleh.
     *
     * @param  iterable<Dokumen>        $dokumens
     * @param  string                   $penanda  pembeda bentuk baris — WAJIB unik per
     *                                            kombinasi kelas DTO + viewerRole, sebab
     *                                            handler_options & can_change_handler
     *                                            berbeda antar peran penonton.
     * @param  callable(Dokumen): array $hitung   penghitung baris saat cache meleset
     * @return array<int, array>                  urutannya mengikuti $dokumens
     */
    public static function petakan(iterable $dokumens, string $penanda, callable $hitung): array
    {
        $daftar = is_array($dokumens) ? $dokumens : iterator_to_array($dokumens);

        if (! self::aktif()) {
            return array_values(array_map($hitung, $daftar));
        }

        $versi = self::versi();
        $ttl   = (int) config('document_columns.cache.ttl', 300);

        $kunci = [];
        foreach ($daftar as $i => $dokumen) {
            $kunci[$i] = self::kunci($versi, $penanda, $dokumen);
        }

        $terambil = empty($kunci) ? [] : Cache::many(array_values($kunci));

        $hasil = [];
        $baru  = [];
        foreach ($daftar as $i => $dokumen) {
            $k = $kunci[$i];

            if (isset($terambil[$k]) && is_array($terambil[$k])) {
                $hasil[] = $terambil[$k];
                continue;
            }

            $baris    = $hitung($dokumen);
            $hasil[]  = $baris;
            $baru[$k] = $baris;
        }

        if (! empty($baru)) {
            Cache::putMany($baru, $ttl);
            self::pangkasKedaluwarsaSesekali();
        }

        return $hasil;
    }

    /**
     * Buang baris cache kedaluwarsa, sesekali saja (lotere 1:50).
     *
     * Driver cache `database` TIDAK punya pemangkas bawaan di Laravel: entri hanya
     * dihapus kalau kebetulan dibaca lagi setelah lewat waktu. Padahal begitu versi
     * global naik, SELURUH kunci lama jadi tak akan pernah dibaca lagi — jadi tanpa
     * pemangkasan mereka mengendap selamanya. Di server ini itu berbahaya: disk
     * tinggal 4,5 GB dari 20 GB, dan cron `schedule:run` untuk project ini TIDAK
     * terpasang (crontab root hanya memuat project lain — diperiksa 2026-08-20),
     * sehingga tugas terjadwal bukan pilihan. Karena itu pemangkasan menumpang
     * request, seperti garbage collection sesi.
     *
     * Dibatasi 1.000 baris per jalan supaya tak pernah menahan satu request lama,
     * dan dibungkus try/catch karena ini jalur kebersihan — bukan jalur kritis.
     */
    private static function pangkasKedaluwarsaSesekali(): void
    {
        if (config('cache.default') !== 'database') {
            return;
        }

        try {
            if (random_int(1, 50) !== 1) {
                return;
            }

            \Illuminate\Support\Facades\DB::table(config('cache.stores.database.table', 'cache'))
                ->where('expiration', '<=', time())
                ->limit(1000)
                ->delete();
        } catch (\Throwable $e) {
            // Sengaja dibiarkan: gagal memangkas tidak boleh menggagalkan respons tabel.
        }
    }

    /**
     * Kunci satu baris. `updated_at` dibaca dari atribut MENTAH supaya tidak
     * bergantung pada cast dan tetap berupa string stabil untuk dijadikan kunci.
     */
    private static function kunci(int $versi, string $penanda, Dokumen $dokumen): string
    {
        $mentah = $dokumen->getAttributes();
        $stamp  = $mentah['updated_at'] ?? '0';

        if (! is_string($stamp)) {
            $stamp = (string) $stamp;
        }

        return 'docrow:' . $versi . ':' . $penanda . ':' . $dokumen->getKey() . ':' . $stamp;
    }
}
