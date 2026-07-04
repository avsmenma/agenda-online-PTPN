<?php

namespace Tests\Unit;

use App\Support\SafeUrl;
use PHPUnit\Framework\TestCase;

/**
 * Unit test untuk App\Support\SafeUrl — pertahanan terhadap stored-XSS pada
 * kolom link dokumen. SafeUrl adalah satu-satunya sumber kebenaran yang
 * menentukan apakah sebuah nilai aman dirender ke atribut href.
 */
class SafeUrlTest extends TestCase
{
    // =====================================================================
    // external() — sanitasi untuk dirender ke href
    // =====================================================================

    public function test_external_returns_null_for_empty_values(): void
    {
        $this->assertNull(SafeUrl::external(null));
        $this->assertNull(SafeUrl::external(''));
        $this->assertNull(SafeUrl::external('   '));
    }

    public function test_external_keeps_valid_http_and_https_urls(): void
    {
        $this->assertSame('https://example.com/doc.pdf', SafeUrl::external('https://example.com/doc.pdf'));
        $this->assertSame('http://example.com', SafeUrl::external('http://example.com'));
    }

    public function test_external_prefixes_schemeless_url_with_https(): void
    {
        $this->assertSame('https://contoh.com/berkas', SafeUrl::external('contoh.com/berkas'));
        $this->assertSame('https://evil.com', SafeUrl::external('//evil.com'));
    }

    public function test_external_neutralizes_javascript_scheme(): void
    {
        // Inti kerentanan: nilai javascript: TIDAK BOLEH pernah menjadi href
        // berskema javascript. Harus dipaksa menjadi https:// (inert).
        $out = SafeUrl::external('javascript:alert(document.cookie)');
        $this->assertStringStartsWith('https://', $out);
        $this->assertDoesNotMatchRegularExpression('/^\s*javascript:/i', $out);
    }

    public function test_external_neutralizes_obfuscated_and_other_dangerous_schemes(): void
    {
        foreach ([
            'JavaScript:alert(1)',
            '  javascript:alert(1)  ',
            "java\tscript:alert(1)",
            'data:text/html;base64,PHNjcmlwdD4=',
            'vbscript:msgbox(1)',
        ] as $payload) {
            $out = SafeUrl::external($payload);
            $this->assertMatchesRegularExpression('#^https?://#i', $out, "Gagal menetralkan: {$payload}");
        }
    }

    /**
     * Kontrak keamanan utama: untuk input APA PUN, external() hanya boleh
     * mengembalikan null atau URL berskema http(s). Tidak pernah skema lain.
     */
    public function test_external_output_is_always_null_or_http_scheme(): void
    {
        foreach ([
            null, '', 'foo', 'contoh.com', 'https://a.b', 'http://a.b',
            'javascript:alert(1)', 'data:x', 'vbscript:x', 'file:///etc/passwd',
            'mailto:a@b.com', '//host', 'JaVaScRiPt:alert(1)',
        ] as $payload) {
            $out = SafeUrl::external($payload);
            if ($out !== null) {
                $this->assertMatchesRegularExpression('#^https?://#i', $out, 'Skema tidak aman lolos: ' . var_export($payload, true));
            }
            $this->assertTrue(true);
        }
    }

    // =====================================================================
    // hasDangerousScheme() — penjaga sisi tulis (WRITE)
    // =====================================================================

    public function test_has_dangerous_scheme_flags_script_schemes(): void
    {
        $this->assertTrue(SafeUrl::hasDangerousScheme('javascript:alert(1)'));
        $this->assertTrue(SafeUrl::hasDangerousScheme('JavaScript:alert(1)'));
        $this->assertTrue(SafeUrl::hasDangerousScheme("java\tscript:alert(1)"));
        $this->assertTrue(SafeUrl::hasDangerousScheme('data:text/html,<script>'));
        $this->assertTrue(SafeUrl::hasDangerousScheme('vbscript:msgbox(1)'));
    }

    public function test_has_dangerous_scheme_allows_safe_and_schemeless_values(): void
    {
        $this->assertFalse(SafeUrl::hasDangerousScheme(null));
        $this->assertFalse(SafeUrl::hasDangerousScheme(''));
        $this->assertFalse(SafeUrl::hasDangerousScheme('https://example.com'));
        $this->assertFalse(SafeUrl::hasDangerousScheme('http://example.com'));
        $this->assertFalse(SafeUrl::hasDangerousScheme('contoh.com'));            // tanpa skema -> nanti di-prefix
        $this->assertFalse(SafeUrl::hasDangerousScheme('contoh.com:8080/path'));  // host:port, bukan skema
        $this->assertFalse(SafeUrl::hasDangerousScheme('192.168.1.1:80'));        // ip:port
    }
}
