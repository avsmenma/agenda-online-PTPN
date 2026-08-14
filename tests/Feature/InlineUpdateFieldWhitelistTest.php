<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menjaga whitelist field inline-edit (DokumenController::inlineUpdate).
 *
 * Lahir dari bug produksi 2026-08-06: lima kolom tampil sebagai sel bisa-diedit di
 * tabel Tabulator, tetapi tak pernah masuk whitelist — sehingga setiap penyimpanan
 * ditolak 422 tanpa alasan yang terlihat user. Tidak ada satu pun test yang
 * menjaga daftar itu, sehingga kelalaiannya bertahan lama.
 *
 * Dua peta HARUS sejalan: whitelist server di sini, dan FIELD_TYPE di
 * public/js/document-tabulator.js yang menentukan jenis editornya.
 */
class InlineUpdateFieldWhitelistTest extends TestCase
{
    use RefreshDatabase;

    private function operator(): User
    {
        return User::factory()->create(['role' => 'operator']);
    }

    private function dokumen(): Dokumen
    {
        return Dokumen::create([
            'nomor_agenda'    => '1_2026',
            'bulan'           => 'Agustus',
            'tahun'           => 2026,
            'tanggal_masuk'   => '2026-08-01',
            'status'          => 'draft',
            'created_by'      => 'operator',
            'current_handler' => 'operator',
        ]);
    }

    /** @return array<int, array{0:string, 1:string}> field => nilai uji */
    public static function fieldYangHarusBisaDiedit(): array
    {
        return [
            'tanggal selesai diproses' => ['tanggal_selesai_diproses', '2026-08-12'],
            'keterangan'               => ['keterangan', 'catatan uji'],
            'kepala sub bagian'        => ['kepala_sub_bagian', 'Budi'],
        ];
    }

    /**
     * @dataProvider fieldYangHarusBisaDiedit
     */
    public function test_field_yang_tampil_bisa_diedit_memang_diterima_server(string $field, string $nilai): void
    {
        $dokumen = $this->dokumen();

        $this->actingAs($this->operator())
            ->patchJson(route('documents.inline-update', $dokumen), [
                'field' => $field,
                'value' => $nilai,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertNotNull(
            $dokumen->fresh()->{$field},
            "Field {$field} diterima server tapi tidak tersimpan."
        );
    }

    public function test_field_di_luar_whitelist_tetap_ditolak(): void
    {
        // Whitelist tetap menjadi pagar: field acak & kolom sensitif tidak boleh
        // ikut terbuka hanya karena lima field di atas ditambahkan.
        $dokumen = $this->dokumen();

        foreach (['current_handler', 'status', 'field_yang_tidak_ada'] as $field) {
            $this->actingAs($this->operator())
                ->patchJson(route('documents.inline-update', $dokumen), [
                    'field' => $field,
                    'value' => 'apa saja',
                ])
                ->assertStatus(422)
                ->assertJsonFragment(['message' => 'Field tidak dapat diedit.']);
        }
    }

    public function test_kolom_tanggal_selesai_diproses_memakai_editor_date_di_klien(): void
    {
        // Whitelist server saja tidak cukup: bila FIELD_TYPE tidak menyebutnya,
        // editornya jatuh ke 'text' dan user mengetik tanggal ke input teks bebas.
        $js = file_get_contents(public_path('js/document-tabulator.js'));

        $this->assertStringContainsString(
            "    tanggal_selesai_diproses: 'date',",
            $js,
            "FIELD_TYPE tidak memetakan tanggal_selesai_diproses ke 'date' — editornya akan jadi teks biasa."
        );
    }

    public function test_inline_update_nomor_po_menyimpan_ke_relasi_dokumen_po(): void
    {
        $dokumen = $this->dokumen();

        $res = $this->actingAs($this->operator())
            ->patchJson(route('documents.inline-update', $dokumen), [
                'field' => 'nomor_po',
                'value' => 'PO-001/VIII/2026, PO-002/VIII/2026',
            ]);

        $res->assertOk()
            ->assertJson([
                'success'       => true,
                'display_value' => 'PO-001/VIII/2026, PO-002/VIII/2026',
                'raw_value'     => 'PO-001/VIII/2026, PO-002/VIII/2026',
            ]);

        $this->assertCount(2, $dokumen->fresh()->dokumenPos);
        $this->assertEquals(['PO-001/VIII/2026', 'PO-002/VIII/2026'], $dokumen->fresh()->dokumenPos->pluck('nomor_po')->all());
    }

    public function test_inline_update_pemaraf_oleh_verifikasi_otomatis_mengisi_tanggal_paraf(): void
    {
        $verifikator = User::factory()->create(['role' => 'team_verifikasi']);
        $dokumen = Dokumen::create([
            'nomor_agenda'    => '2_2026',
            'bulan'           => 'Agustus',
            'tahun'           => 2026,
            'tanggal_masuk'   => '2026-08-01',
            'status'          => 'sedang diproses',
            'created_by'      => 'operator',
            'current_handler' => 'team_verifikasi',
        ]);

        $res = $this->actingAs($verifikator)
            ->patchJson(route('documents.inline-update', $dokumen), [
                'field' => 'pemaraf',
                'value' => 'Yuni',
            ]);

        $res->assertOk()
            ->assertJson([
                'success'       => true,
                'display_value' => 'Yuni',
                'raw_value'     => 'Yuni',
            ]);

        $fresh = $dokumen->fresh();
        $this->assertSame('Yuni', $fresh->pemaraf);
        $this->assertNotNull($fresh->tanggal_paraf);
    }

    public function test_document_tabulator_js_memetakan_pemaraf_ke_select_pemaraf(): void
    {
        $js = file_get_contents(public_path('js/document-tabulator.js'));

        $this->assertStringContainsString(
            "pemaraf: 'select_pemaraf',",
            $js,
            "FIELD_TYPE tidak memetakan pemaraf ke 'select_pemaraf'."
        );

        $this->assertStringContainsString(
            "{ value: 'Yuni', label: 'Yuni' }",
            $js,
            "buildSelectValues tidak memuat opsi Yuni."
        );

        $this->assertStringContainsString(
            "{ value: 'Sekar', label: 'Sekar' }",
            $js,
            "buildSelectValues tidak memuat opsi Sekar."
        );
    }
}
