<?php

namespace Tests\Feature;

use App\Support\SafeUrl;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Membuktikan aturan validasi sisi-tulis (store & update) untuk kolom link:
 * nilai berbahaya seperti `javascript:alert(1)` HARUS ditolak, URL tanpa skema
 * di-prefix https://, URL http(s) yang valid diterima.
 *
 * Tidak memakai RefreshDatabase: hanya butuh container untuk rule Laravel
 * (url / starts_with), tidak menyentuh database.
 */
class LinkStorageSanitizationTest extends TestCase
{
    public function test_javascript_scheme_is_rejected_on_store(): void
    {
        $this->expectException(ValidationException::class);
        SafeUrl::sanitizeForStorage('javascript:alert(document.cookie)');
    }

    public function test_data_uri_scheme_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        SafeUrl::sanitizeForStorage('data:text/html;base64,PHNjcmlwdD4=');
    }

    public function test_obfuscated_javascript_scheme_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        SafeUrl::sanitizeForStorage("java\tscript:alert(1)");
    }

    public function test_valid_https_url_is_accepted_unchanged(): void
    {
        $this->assertSame(
            'https://drive.example.com/file/abc.pdf',
            SafeUrl::sanitizeForStorage('https://drive.example.com/file/abc.pdf')
        );
    }

    public function test_schemeless_url_is_prefixed_with_https(): void
    {
        $this->assertSame('https://contoh.com/berkas', SafeUrl::sanitizeForStorage('contoh.com/berkas'));
    }

    public function test_empty_value_is_stored_as_null(): void
    {
        $this->assertNull(SafeUrl::sanitizeForStorage(null));
        $this->assertNull(SafeUrl::sanitizeForStorage(''));
        $this->assertNull(SafeUrl::sanitizeForStorage('   '));
    }

    public function test_validation_error_message_is_in_indonesian(): void
    {
        try {
            SafeUrl::sanitizeForStorage('javascript:alert(1)');
            $this->fail('Seharusnya melempar ValidationException.');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('http', strtolower($e->validator->errors()->first()));
        }
    }
}
