<?php

namespace Tests\Unit;

use App\Models\Dokumen;
use App\Support\StatusPembayaranBagian;
use Illuminate\Database\Capsule\Manager as Capsule;
use PHPUnit\Framework\TestCase;

/**
 * Menguji aturan status pembayaran 3-state milik role Bagian.
 *
 * PHPUnit\TestCase (bukan Tests\TestCase): helper murni, tanpa DB.
 * Model dibuat via `new Dokumen()` + isi atribut — tidak disimpan.
 */
class StatusPembayaranBagianTest extends TestCase
{
    /**
     * Infrastruktur murni (bukan aturan bisnis): PHPUnit\Framework\TestCase tidak
     * memasang connection resolver Eloquent seperti Illuminate\Foundation\Testing\TestCase.
     * Tanpa ini, MENGISI atribut ber-cast 'date' (tanggal_dibayar) maupun memanggil
     * getDataForRole() (relasi roleData()) meledak "Call to a member function
     * connection() on null" — gagal di infrastruktur Eloquent, bukan di logika
     * StatusPembayaranBagian. Sqlite in-memory mandiri (Capsule, tanpa app container
     * Laravel) dipasang di sini; model tetap TIDAK PERNAH disimpan (->save() tak
     * dipanggil di test manapun pada berkas ini).
     */
    protected function setUp(): void
    {
        parent::setUp();

        $capsule = new Capsule();
        $capsule->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        $capsule->schema()->create('dokumen_role_data', function ($table) {
            $table->id();
            $table->unsignedBigInteger('dokumen_id');
            $table->string('role_code', 50);
            $table->dateTime('received_at')->nullable();
            $table->dateTime('processed_at')->nullable();
            $table->dateTime('deadline_at')->nullable();
            $table->integer('deadline_days')->nullable();
            $table->string('deadline_note', 500)->nullable();
            $table->json('role_specific_data')->nullable();
            $table->timestamps();
        });
    }

    public function test_sudah_dibayar_saat_status_pembayaran_final(): void
    {
        $doc = new Dokumen();
        $doc->status_pembayaran = 'sudah_dibayar';
        $doc->tanggal_dibayar = '2026-08-11 10:00:00';

        $hasil = StatusPembayaranBagian::untuk($doc);

        $this->assertSame('sudah-dibayar', $hasil['kelas']);
        $this->assertSame('Sudah Dibayar', $hasil['teks']);
        $this->assertSame('fa-check-circle', $hasil['ikon']);
        $this->assertSame('2026-08-11 10:00:00', $hasil['tanggal']);
    }

    public function test_sudah_dibayar_saat_hanya_tanggal_dibayar_terisi(): void
    {
        // Cabang OR: tanggal_dibayar terisi TAPI status_pembayaran belum final.
        // Mudah pecah saat diekstrak kalau OR keliru jadi AND.
        $doc = new Dokumen();
        $doc->status_pembayaran = null;
        $doc->tanggal_dibayar = '2026-08-12 08:30:00';
        $doc->current_handler = 'team_verifikasi';

        $hasil = StatusPembayaranBagian::untuk($doc);

        $this->assertSame('sudah-dibayar', $hasil['kelas']);
        $this->assertSame('Sudah Dibayar', $hasil['teks']);
    }

    public function test_belum_siap_dibayar_saat_masih_di_role_lain(): void
    {
        $doc = new Dokumen();
        $doc->status_pembayaran = null;
        $doc->tanggal_dibayar = null;
        $doc->current_handler = 'team_verifikasi';
        $doc->sent_at = '2026-08-01 09:00:00';

        $hasil = StatusPembayaranBagian::untuk($doc);

        $this->assertSame('belum-dibayar', $hasil['kelas']);
        $this->assertSame('Belum Siap Dibayar', $hasil['teks']);
        $this->assertSame('fa-clock', $hasil['ikon']);
        $this->assertSame('2026-08-01 09:00:00', $hasil['tanggal']);
    }

    public function test_handler_pembayaran_dikenali_case_insensitive(): void
    {
        // Kode lama memakai str_contains(strtolower(...), 'pembayaran').
        // Handler produksi pernah ditulis 'Team Pembayaran' berkapital.
        $doc = new Dokumen();
        $doc->status_pembayaran = null;
        $doc->tanggal_dibayar = null;
        $doc->current_handler = 'Team Pembayaran';

        $hasil = StatusPembayaranBagian::untuk($doc);

        $this->assertSame('siap-dibayar', $hasil['kelas']);
        $this->assertSame('Siap Dibayar', $hasil['teks']);
        $this->assertSame('fa-money-bill-wave', $hasil['ikon']);
    }

    public function test_current_handler_null_tidak_meledak(): void
    {
        $doc = new Dokumen();
        $doc->status_pembayaran = null;
        $doc->tanggal_dibayar = null;
        $doc->current_handler = null;
        $doc->sent_at = null;

        $hasil = StatusPembayaranBagian::untuk($doc);

        $this->assertSame('belum-dibayar', $hasil['kelas']);
    }
}
