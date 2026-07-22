<?php

namespace Tests\Unit;

use App\Support\Asset;
use Tests\TestCase;

/**
 * Unit test untuk App\Support\Asset — cache-busting aset lokal (css/js).
 *
 * nginx menyajikan semua .css/.js dengan `Cache-Control: public,
 * max-age=31536000, immutable`, sehingga nama file yang stabil bikin
 * browser tidak pernah mengambil versi baru. Asset::versioned() menambah
 * query string `?v=<mtime>` supaya URL berubah setiap file berubah.
 *
 * Extends Tests\TestCase (bukan PHPUnit\Framework\TestCase murni) karena
 * Asset::versioned() memakai helper asset()/public_path() yang butuh
 * aplikasi Laravel ter-bootstrap.
 */
class AssetTest extends TestCase
{
    public function test_versioned_menambahkan_query_string_v_berisi_mtime_untuk_file_yang_ada(): void
    {
        $path = 'css/responsive.css';
        $absolute = public_path($path);

        $this->assertFileExists($absolute, 'Prasyarat test: berkas aset harus ada di public/.');

        $expectedMtime = filemtime($absolute);
        $url = Asset::versioned($path);

        $this->assertStringContainsString('?v=' . $expectedMtime, $url);
        $this->assertStringStartsWith(asset($path), $url);
    }

    public function test_versioned_mengembalikan_url_polos_tanpa_v_untuk_file_yang_tidak_ada(): void
    {
        $path = 'css/berkas-tidak-ada-selamanya.css';
        $absolute = public_path($path);

        $this->assertFileDoesNotExist($absolute, 'Prasyarat test: berkas ini memang sengaja tidak ada.');

        $url = Asset::versioned($path);

        $this->assertSame(asset($path), $url);
        $this->assertStringNotContainsString('?v=', $url);
    }

    public function test_versioned_stabil_untuk_state_file_yang_sama(): void
    {
        $path = 'css/responsive.css';

        $this->assertSame(Asset::versioned($path), Asset::versioned($path));
    }
}
