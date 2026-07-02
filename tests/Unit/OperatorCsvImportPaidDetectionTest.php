<?php

namespace Tests\Unit;

use App\Http\Controllers\OperatorCsvImportController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * Menguji deteksi "sudah dibayar" pada import CSV Operator.
 *
 * Regresi: setelah import diubah ke raw bulk insert (bypass Eloquent observer),
 * dokumen yang di CSV-nya sudah punya tanggal bayar / status "sudah dibayar"
 * tidak lagi diberi status_pembayaran = 'sudah_dibayar', sehingga tidak
 * di-auto-forward ke role Pembayaran dan tersangkut di Operator.
 */
class OperatorCsvImportPaidDetectionTest extends TestCase
{
    private function isMarkedPaid(array $data): bool
    {
        $method = new ReflectionMethod(OperatorCsvImportController::class, 'isMarkedPaid');
        $method->setAccessible(true);

        return $method->invoke(new OperatorCsvImportController(), $data);
    }

    public function test_row_with_tanggal_dibayar_is_marked_paid(): void
    {
        $this->assertTrue($this->isMarkedPaid([
            'tanggal_dibayar'    => '2026-05-10',
            'status_dokumen_csv' => null,
        ]));
    }

    public function test_row_with_status_sudah_dibayar_is_marked_paid(): void
    {
        $this->assertTrue($this->isMarkedPaid([
            'tanggal_dibayar'    => null,
            'status_dokumen_csv' => 'Sudah Dibayar',
        ]));
    }

    public function test_row_with_status_lunas_is_marked_paid(): void
    {
        $this->assertTrue($this->isMarkedPaid([
            'tanggal_dibayar'    => null,
            'status_dokumen_csv' => 'LUNAS',
        ]));
    }

    public function test_row_with_status_belum_dibayar_is_not_paid(): void
    {
        $this->assertFalse($this->isMarkedPaid([
            'tanggal_dibayar'    => null,
            'status_dokumen_csv' => 'Belum Dibayar',
        ]));
    }

    public function test_row_without_payment_info_is_not_paid(): void
    {
        $this->assertFalse($this->isMarkedPaid([
            'tanggal_dibayar'    => null,
            'status_dokumen_csv' => null,
        ]));
    }
}
