<?php

namespace Tests\Feature;

use App\Services\DocumentReturnNotifier;
use Tests\TestCase;

/**
 * SEMENTARA — menguji tombol uji kiriman WhatsApp di halaman role Bagian.
 * Hapus seluruh berkas ini saat fitur uji coba dicabut (lihat daftar pencabutan
 * di docblock App\Http\Controllers\UjiWhatsAppBagianController).
 */
class UjiWhatsAppBagianTest extends TestCase
{
    public function test_pesan_uji_memakai_template_yang_sama_dengan_pesan_sungguhan(): void
    {
        // Pesan uji yang MENYIMPANG dari pesan sungguhan akan menipu responden
        // tanpa seorang pun menyadarinya. Karena itu bukan cuma "mengandung kata
        // yang mirip" — badannya wajib byte-per-byte hasil susunPesan().
        $tautan = 'http://contoh.test/bagian/documents';

        $uji = DocumentReturnNotifier::pesanUjiCoba('Tanaman', $tautan);

        $susunPesan = new \ReflectionMethod(DocumentReturnNotifier::class, 'susunPesan');
        $susunPesan->setAccessible(true);
        $badan = $susunPesan->invoke(
            null,
            '9999_2026',
            'Tanaman',
            'Lampiran faktur belum lengkap. (contoh)',
            $tautan
        );

        $this->assertStringEndsWith(
            $badan,
            $uji,
            'pesanUjiCoba() tidak berakhir dengan hasil susunPesan() — templatenya tersalin, bukan dipakai bersama.'
        );

        $this->assertStringStartsWith(
            '🧪',
            $uji,
            'Penanda uji coba hilang — responden bisa mengira ini pengembalian sungguhan.'
        );

        $this->assertStringContainsString('[UJI COBA', $uji);
    }
}
