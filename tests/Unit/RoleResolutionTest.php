<?php

namespace Tests\Unit;

use App\Models\User;
use App\Support\Role;
use PHPUnit\Framework\TestCase;

/**
 * Jaring pengaman untuk konsolidasi penamaan peran. Membuktikan alias legacy
 * (warisan AI lama) tetap teresolusi ke kode kanonik, dan resolusi
 * dashboard/otorisasi User tahan terhadap campur-case seperti data produksi
 * ('Admin', 'Akutansi').
 */
class RoleResolutionTest extends TestCase
{
    public function test_normalize_memetakan_alias_legacy_ke_kanonik(): void
    {
        $this->assertSame('team_verifikasi', Role::normalize('verifikasi'));
        $this->assertSame('team_verifikasi', Role::normalize('Ibu B'));
        $this->assertSame('team_verifikasi', Role::normalize('ibu yuni'));
        $this->assertSame('operator', Role::normalize('tarapul'));
        $this->assertSame('akutansi', Role::normalize('akuntansi'));
        $this->assertSame('bagian_akn', Role::normalize('bagian_akn'));
    }

    public function test_normalize_case_insensitive_dan_kosong(): void
    {
        $this->assertSame('akutansi', Role::normalize('Akutansi'));
        $this->assertSame('admin', Role::normalize('Admin'));
        $this->assertSame('operator', Role::normalize('  OPERATOR  '));
        $this->assertSame('', Role::normalize(null));
        $this->assertSame('', Role::normalize('   '));
    }

    public function test_matches_menyamakan_alias(): void
    {
        $this->assertTrue(Role::matches('team_verifikasi', 'verifikasi'));
        $this->assertTrue(Role::matches('Admin', 'admin'));
        $this->assertFalse(Role::matches('operator', 'pembayaran'));
        $this->assertFalse(Role::matches('', ''));
    }

    public function test_dashboard_route_tahan_campur_case(): void
    {
        $u = new User();
        $u->role = 'Admin';
        $this->assertSame('/owner/home', $u->getDashboardRoute());

        $u->role = 'Akutansi';
        $this->assertSame('/dashboard/akutansi', $u->getDashboardRoute());

        $u->role = 'team_verifikasi';
        $this->assertSame('/dashboard/verifikasi', $u->getDashboardRoute());
    }

    public function test_hasRole_dan_isAdmin_case_insensitive(): void
    {
        $u = new User();
        $u->role = 'Admin';
        $this->assertTrue($u->hasRole('admin'));
        $this->assertTrue($u->hasRole('ADMIN'));
        $this->assertTrue($u->isAdmin());

        $u->role = 'team_verifikasi';
        $this->assertTrue($u->hasRole('verifikasi'));   // alias
        $this->assertFalse($u->isAdmin());
    }

    public function test_role_display_name(): void
    {
        $u = new User();
        $u->role = 'Akutansi';
        $this->assertSame('Akuntansi', $u->getRoleDisplayName());

        $u->role = 'operator';
        $this->assertSame('Operator', $u->getRoleDisplayName());
    }
}
