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

    public function test_halaman_akutansi_render_tanpa_ui_aksi_mati(): void
    {
        // ?classic=1: sejak Tugas 7, dokumens() tanpa flag ini menyajikan view
        // Tabulator (Rollout 1). Test ini menguji potongan HIDUP/MATI milik view
        // legacy secara spesifik — pertahankan lewat ?classic sampai legacy
        // dihapus (Tugas 8).
        $response = $this->actingAs($this->akutansi())
            ->get(route('documents.akutansi.index', ['classic' => 1]));

        $response->assertOk();

        // Potongan HIDUP tetap ada — bukti tak kebablasan menghapus:
        $response->assertSee('Deadline', false);            // header kolom Deadline
        $response->assertSee('columnCustomizationModal', false); // modal kustomisasi kolom (hidup)

        // UI aksi MATI sudah tiada:
        $response->assertDontSee('setDeadlineModal', false);
        $response->assertDontSee('sendToPembayaranModal', false);
        $response->assertDontSee('returnModal', false);
        $response->assertDontSee('function sendToPembayaran(', false);
        $response->assertDontSee('function confirmReturn(', false);
        $response->assertDontSee('function confirmSetDeadline(', false);
    }
}
