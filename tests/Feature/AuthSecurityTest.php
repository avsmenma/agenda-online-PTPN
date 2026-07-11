<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Basic authentication and security feature tests.
 *
 * These tests verify:
 * 1. Login page accessibility
 * 2. Login validation (rate limiting, password policy)
 * 3. Role-based routing after login
 * 4. Protected route access control
 * 5. Guest redirect behavior
 */
class AuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // Login Page Tests
    // =========================================================================

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_root_redirects_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    // =========================================================================
    // Login Validation Tests
    // =========================================================================

    public function test_login_requires_username_and_password(): void
    {
        $response = $this->post('/login', []);
        $response->assertSessionHasErrors(['username', 'password']);
    }

    public function test_login_requires_minimum_8_character_password(): void
    {
        $response = $this->post('/login', [
            'username' => 'testuser',
            'password' => 'short',   // Only 5 chars, minimum is 8
        ]);
        $response->assertSessionHasErrors('password');
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        User::factory()->create([
            'username' => 'testuser',
            'password' => bcrypt('password123'),
            'role' => 'operator',
        ]);

        $response = $this->post('/login', [
            'username' => 'testuser',
            'password' => 'wrongpassword',
        ]);

        $this->assertGuest();
    }

    public function test_login_succeeds_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'username' => 'testuser',
            'password' => bcrypt('password123'),
            'role' => 'operator',
        ]);

        $response = $this->post('/login', [
            'username' => 'testuser',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
    }

    // =========================================================================
    // Rate Limiting Tests
    // =========================================================================

    public function test_login_is_rate_limited_after_5_attempts(): void
    {
        User::factory()->create([
            'username' => 'testuser',
            'password' => bcrypt('password123'),
            'role' => 'operator',
        ]);

        // Make 5 failed login attempts
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'username' => 'testuser',
                'password' => 'wrongpassword1',
            ]);
        }

        // 6th attempt should be rate limited
        $response = $this->post('/login', [
            'username' => 'testuser',
            'password' => 'wrongpassword1',
        ]);

        // Should see validation error about too many attempts
        $response->assertSessionHasErrors();
    }

    // =========================================================================
    // Protected Route Tests — Guest Access
    // =========================================================================

    public function test_guest_cannot_access_documents(): void
    {
        $response = $this->get('/documents');
        $response->assertRedirect('/login');
    }

    public function test_guest_cannot_access_dashboard(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_guest_cannot_access_owner_home(): void
    {
        $response = $this->get('/owner/home');
        $response->assertRedirect('/login');
    }

    public function test_guest_cannot_access_programmer_dashboard(): void
    {
        $response = $this->get('/programmer/dashboard');
        $response->assertRedirect('/login');
    }

    // =========================================================================
    // Role-Based Access Control Tests
    // =========================================================================

    public function test_operator_can_access_documents(): void
    {
        $user = User::factory()->create([
            'username' => 'operator1',
            'role' => 'operator',
        ]);

        $response = $this->actingAs($user)->get('/documents');
        $response->assertStatus(200);
    }

    public function test_operator_cannot_access_owner_dashboard(): void
    {
        $user = User::factory()->create([
            'username' => 'operator1',
            'role' => 'operator',
        ]);

        $response = $this->actingAs($user)->get('/owner/home');
        $response->assertRedirect('/login'); // CheckRole redirects to login on mismatch
    }

    public function test_owner_can_access_owner_home(): void
    {
        $user = User::factory()->create([
            'username' => 'owner1',
            'role' => 'owner',
        ]);

        $response = $this->actingAs($user)->get('/owner/home');
        $response->assertStatus(200);
    }

    public function test_programmer_can_access_programmer_dashboard(): void
    {
        $user = User::factory()->create([
            'username' => 'programmer1',
            'role' => 'programmer',
        ]);

        $response = $this->actingAs($user)->get('/programmer/dashboard');
        $response->assertStatus(200);
    }

    // =========================================================================
    // API Security Tests
    // =========================================================================

    public function test_api_welcome_message_requires_auth(): void
    {
        $response = $this->getJson('/api/welcome-message');
        $response->assertUnauthorized();
    }

    /**
     * Route check-updates dihapus 2026-07-09 bersama sistem notifikasi popup
     * lama (pemanggil satu-satunya). Test ini menjaga agar tidak muncul kembali.
     */
    public function test_api_check_updates_sudah_dihapus(): void
    {
        $this->getJson('/api/documents/verifikasi/check-updates')->assertNotFound();
        $this->getJson('/api/documents/perpajakan/check-updates')->assertNotFound();
        $this->getJson('/api/documents/akutansi/check-updates')->assertNotFound();
        $this->getJson('/api/documents/pembayaran/check-updates')->assertNotFound();
    }
}
