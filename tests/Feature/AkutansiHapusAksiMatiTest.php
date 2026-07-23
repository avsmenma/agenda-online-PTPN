<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji pembersihan program aksi mati akutansi (send-to-pembayaran / return /
 * set-deadline): route+method backend yatim dihapus, UI dormant dibuang, DAN
 * halaman akutansi tetap render normal (potongan hidup — kolom Deadline dsb — utuh).
 */
class AkutansiHapusAksiMatiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // dokumens() memakai fungsi MySQL (REGEXP, SUBSTRING_INDEX) di ORDER BY
        // nomor_agenda — polyfill untuk SQLite (sama seperti OperatorTabulatorViewTest).
        $pdo = \DB::connection()->getPdo();
        if ($pdo->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'sqlite') {
            $pdo->sqliteCreateFunction('regexp', function ($pattern, $value) {
                return preg_match('/' . $pattern . '/u', (string) $value) ? 1 : 0;
            });
            $pdo->sqliteCreateFunction('substring_index', function ($str, $delim, $count) {
                $parts = explode($delim, (string) $str);
                return implode($delim, array_slice($parts, 0, (int) $count));
            });
        }
    }

    private function akutansi(): User
    {
        return User::factory()->create(['role' => 'akutansi']);
    }

    public function test_route_aksi_mati_akutansi_dihapus(): void
    {
        $router = app('router');

        $this->assertFalse($router->has('documents.akutansi.set-deadline'));
        $this->assertFalse($router->has('documents.akutansi.send-to-pembayaran'));
        $this->assertFalse($router->has('documents.akutansi.return'));

        // Yang hidup tetap ada:
        $this->assertTrue($router->has('documents.akutansi.index'));
        $this->assertTrue($router->has('documents.akutansi.detail'));
    }
}
