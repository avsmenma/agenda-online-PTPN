<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji jalur virtual_chunk pada daftar dokumen Operator:
 * respons ringan (tanpa layout) berisi hanya baris tabel untuk lazy-load
 * saat user scroll mendekati akhir tabel.
 */
class VirtualChunkDokumenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Query daftar operator memakai fungsi MySQL (REGEXP, SUBSTRING_INDEX)
        // yang tidak tersedia di SQLite — daftarkan polyfill untuk test.
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

    private function operator(): User
    {
        return User::factory()->create(['role' => 'operator']);
    }

    private function buatDokumen(int $jumlah): void
    {
        for ($i = 1; $i <= $jumlah; $i++) {
            Dokumen::create([
                'nomor_agenda'    => $i . '_2026',
                'bulan'           => 'Juli',
                'tahun'           => 2026,
                'status'          => 'draft',
                'created_by'      => 'operator',
                'current_handler' => 'operator',
            ]);
        }
    }

    public function test_virtual_chunk_mengembalikan_potongan_ringan_tanpa_layout(): void
    {
        $this->buatDokumen(5);

        $response = $this->actingAs($this->operator())
            ->get('/documents?virtual_chunk=1&per_page=100&page=1');

        $response->assertStatus(200);
        $html = $response->getContent();

        // Respons chunk harus ringan: tidak membawa layout/sidebar halaman penuh.
        $this->assertStringNotContainsString('<html', $html);
        $this->assertStringNotContainsString('sidebar', $html);

        // Harus tetap memuat kontainer + baris dokumen agar JS bisa mengekstrak tbody.
        $this->assertStringContainsString('documentTableContainer', $html);
        $this->assertStringContainsString('<tbody', $html);
        $this->assertStringContainsString('5_2026', $html);
    }

    public function test_virtual_chunk_halaman_dua_memuat_dokumen_101_dst(): void
    {
        $this->buatDokumen(105);

        $response = $this->actingAs($this->operator())
            ->get('/documents?virtual_chunk=1&per_page=100&page=2');

        $response->assertStatus(200);
        $html = $response->getContent();

        // Urut default nomor_agenda desc: halaman 2 berisi 5 dokumen terkecil.
        $this->assertStringContainsString('1_2026', $html);
        // Dokumen milik halaman 1 (nomor terbesar) tidak boleh ikut.
        $this->assertStringNotContainsString('105_2026', $html);
    }
}
