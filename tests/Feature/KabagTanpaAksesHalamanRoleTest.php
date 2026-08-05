<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KabagTanpaAksesHalamanRoleTest extends TestCase
{
    use Refreshdatabase;

    private function kabag(): User
    {
        return User::factory()->create([
            'role' => 'admin',
        ]);
    }

    public function test_kabag_ditolak_di_halaman_kerja_tim(): void
    {
        $halaman = [
            'documents.index',
            'documents.verifikasi.index',
            'documents.perpajakan.index',
            'documents.akutansi.index',
            'documents.pembayaran.index',
        ];

        foreach ($halaman as $namaRute) {
            $this->actingAs($this->kabag())
                ->get(route($namaRute))
                ->assertRedirect('/login');
        }
    }

    public function test_kabag_tetap_bisa_membuka_halaman_owner(): void
    {
        $kabag = $this->kabag();

        $this->actingAs($kabag)->get(route('owner.home'))->assertOk();
        $this->actingAs($kabag)->get(route('owner.dokumen'))->assertOk();
    }
}