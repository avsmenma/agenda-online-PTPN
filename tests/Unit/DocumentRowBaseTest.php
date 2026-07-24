<?php

namespace Tests\Unit;

use App\Models\Dokumen;
use App\Support\DocumentRow;
use Illuminate\Support\Collection;
use Tests\TestCase;

class DocumentRowBaseTest extends TestCase
{
    /** Subclass stub: mengekspos baseRow() yang protected untuk pengujian unit. */
    private function rowFor(Dokumen $dokumen, array $handlerOptions = [], ?string $viewerRole = null): array
    {
        $probe = new class extends DocumentRow {
            public static function call(Dokumen $d, array $h, ?string $v): array
            {
                return static::baseRow($d, $h, $v);
            }
        };

        return $probe::call($dokumen, $handlerOptions, $viewerRole);
    }

    public function test_base_row_memuat_id_kolom_base_dan_turunan_format(): void
    {
        $dokumen = new Dokumen([
            'nomor_agenda'  => '12_A',
            'bulan'         => 'Januari',
            'tahun'         => '2026',
            'nilai_rupiah'  => 1500000,
            'dpp_pph'       => 1000000,
            'ppn_terhutang' => null,
            'current_handler' => 'operator',
            'status'        => 'draft',
        ]);
        $dokumen->id = 77;
        // Relasi kosong yang wajib ter-load agar tak ada query.
        $dokumen->setRelation('roleStatuses', new Collection());
        $dokumen->setRelation('dibayarKepadas', new Collection());
        $dokumen->setRelation('dokumenPos', new Collection());

        $handlerOptions = [['value' => 'operator', 'label' => 'Operator']];
        $row = $this->rowFor($dokumen, $handlerOptions, 'operator');

        // Identitas + sebagian kolom base disalin apa adanya.
        $this->assertSame(77, $row['id']);
        $this->assertSame('12_A', $row['nomor_agenda']);
        $this->assertSame('Januari', $row['bulan']);

        // Turunan format server.
        $this->assertSame('Rp 1.000.000', $row['dpp_pph_formatted']);
        $this->assertSame('-', $row['ppn_terhutang_formatted']);

        // Handler ditanam apa adanya.
        $this->assertSame('operator', $row['handler']);
        $this->assertSame($handlerOptions, $row['handler_options']);

        // Kunci khas subclass TIDAK boleh ada di basis.
        $this->assertArrayNotHasKey('display_status', $row);
        $this->assertArrayNotHasKey('can_edit', $row);
        $this->assertArrayNotHasKey('reject_reason', $row);

        // dates selalu ada sebagai peta.
        $this->assertIsArray($row['dates']);
    }

    public function test_can_change_handler_true_hanya_bila_viewer_adalah_pengurus_saat_ini_tanpa_pending(): void
    {
        $dokumen = new Dokumen(['current_handler' => 'akutansi', 'status' => 'draft']);
        $dokumen->id = 5;
        $dokumen->setRelation('roleStatuses', new Collection());
        $dokumen->setRelation('dibayarKepadas', new Collection());
        $dokumen->setRelation('dokumenPos', new Collection());

        $rowMatch = $this->rowFor($dokumen, [], 'akutansi');
        $this->assertTrue($rowMatch['can_change_handler']);

        $rowMismatch = $this->rowFor($dokumen, [], 'perpajakan');
        $this->assertFalse($rowMismatch['can_change_handler']);
    }
}
