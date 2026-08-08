# Larangan Jalur Mundur ke Operator & Bagian — Rencana Implementasi

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Perpajakan, akutansi, dan pembayaran tidak lagi bisa memindahkan dokumen ke Operator maupun ke Bagian — dropdown dipangkas **dan** server menolak.

**Architecture:** Aturannya tinggal di satu tempat (`App\Support\HandlerOptions::bolehMenunjuk()`) dan dipakai dua sisi: penyusun opsi dropdown memakainya untuk memangkas, `DocumentHandlerController::update()` memakainya untuk menolak dengan 403. Opsi yang kebetulan merupakan pengurus yang sedang ditampilkan untuk sebuah baris **dipertahankan dalam keadaan non-aktif**, supaya `<select>` tidak kehilangan nilai terpilihnya dan menampilkan pengurus yang keliru.

**Tech Stack:** Laravel 12, PHP 8.2, PHPUnit, Tabulator.js (`public/js/document-tabulator.js`), MySQL 8 (produksi) / SQLite `:memory:` (test).

**Spec:** `docs/superpowers/specs/2026-08-09-larangan-mundur-perpajakan-akutansi-pembayaran-design.md`

## Global Constraints

- Pesan commit **Bahasa Indonesia**; `git add` **per-berkas** — JANGAN `git add .` / `git add -A`.
- UI & komentar Bahasa Indonesia, identifier English (nama method Indonesia di kelas ini mengikuti pola yang sudah ada: `tolakBilaBukanBagianAsal`, `namaBagian`).
- **Jangan tambah CSS inline baru.**
- **Setiap assertion baru wajib dibuktikan menggigit:** rusakkan kode yang dijaga → test GAGAL → pulihkan → LULUS → `git diff <berkas>` **kosong** sebelum lanjut.
- Assertion yang mencari string di seluruh berkas biasanya hampa — persempit ke badan fungsi.
- Saat iterasi jalankan **terfilter** (`php artisan test --filter=NamaTest`). Suite penuh **sekali** sebelum push (Task 6). `--parallel` tidak tersedia (paratest tak terpasang).
- Perbandingan peran **selalu** lewat `App\Support\Role::normalize()` — `Role::ALIASES` memuat `'akuntansi' => 'akutansi'`, jadi perbandingan mentah membuat larangan gagal tanpa suara.
- Role kanonik yang dilarang, persis: `['perpajakan', 'akutansi', 'pembayaran']`. Operator & `team_verifikasi` **tidak** berubah.
- Perubahan pada `public/js/document-tabulator.js` **wajib aditif** — mesin ini dipakai 5 role.
- Jangan jalankan perintah destruktif atau seeder apa pun terhadap database produksi.

---

## File Structure

| Berkas | Tanggung jawab | Task |
|---|---|---|
| `app/Support/HandlerOptions.php` | Aturan larangan + penyusun & pemangkas opsi | 1, 4 |
| `app/Support/DocumentRow.php` | Perhitungan nilai pengurus yang ditampilkan | 2 |
| `public/js/document-tabulator.js` | Render `<option disabled>` | 3 |
| 5 controller (10 call site) | Mengoper role & nilai yang dipertahankan | 4 |
| `app/Http/Controllers/DocumentHandlerController.php` | Penolakan 403 | 5 |
| `tests/Unit/HandlerOptionsTest.php` | Aturan + pemangkasan | 1, 4 |
| `tests/Unit/HandlerTampilanPengembalianTest.php` | Nilai tampil (sudah ada; jadi jaring pengaman Task 2) | 2, 4 |
| `tests/Feature/LaranganJalurMundurTest.php` | Render JS + penegakan server | 3, 5 |

---

### Task 1: Aturan larangan `bolehMenunjuk()`

Penambahan murni — belum ada satu pun pemakai, jadi suite wajib tetap hijau setelahnya.

**Files:**
- Modify: `app/Support/HandlerOptions.php`
- Test: `tests/Unit/HandlerOptionsTest.php`

**Interfaces:**
- Consumes: `App\Support\Role::normalize(?string): string` (sudah ada)
- Produces: `HandlerOptions::bolehMenunjuk(?string $rolePengguna, string $target): bool` — dipakai Task 4 (pemangkasan) & Task 5 (penolakan server)

- [ ] **Step 1: Tulis test yang gagal**

Tambahkan ke `tests/Unit/HandlerOptionsTest.php` (di dalam kelas yang sudah ada):

```php
    public function test_tiga_role_tak_boleh_menunjuk_operator_maupun_bagian(): void
    {
        foreach (['perpajakan', 'akutansi', 'pembayaran'] as $role) {
            $this->assertFalse(
                HandlerOptions::bolehMenunjuk($role, 'operator'),
                "Role {$role} masih boleh menunjuk Operator"
            );
            $this->assertFalse(
                HandlerOptions::bolehMenunjuk($role, 'bagian_akn'),
                "Role {$role} masih boleh menunjuk Bagian"
            );
        }
    }

    public function test_role_terlarang_tetap_boleh_menunjuk_peran_lain(): void
    {
        foreach (['team_verifikasi', 'perpajakan', 'akutansi', 'pembayaran'] as $target) {
            $this->assertTrue(
                HandlerOptions::bolehMenunjuk('perpajakan', $target),
                "Perpajakan seharusnya masih boleh menunjuk {$target}"
            );
        }
    }

    public function test_operator_dan_verifikasi_tidak_ikut_dilarang(): void
    {
        foreach (['operator', 'team_verifikasi'] as $role) {
            $this->assertTrue(HandlerOptions::bolehMenunjuk($role, 'operator'), $role);
            $this->assertTrue(HandlerOptions::bolehMenunjuk($role, 'bagian_akn'), $role);
        }
    }

    public function test_alias_peran_dan_target_ikut_dinormalisasi(): void
    {
        // Role::ALIASES memuat 'akuntansi' => 'akutansi'. Tanpa normalisasi, akun
        // yang kolom role-nya berisi alias LOLOS dari larangan tanpa suara.
        foreach (['akuntansi', 'Akutansi', ' PEMBAYARAN ', 'Tim Perpajakan'] as $alias) {
            $this->assertFalse(
                HandlerOptions::bolehMenunjuk($alias, 'operator'),
                "Alias peran '{$alias}' lolos dari larangan"
            );
        }

        // Sisi target juga: 'Operator' berhuruf besar harus tetap tertangkap.
        $this->assertFalse(HandlerOptions::bolehMenunjuk('perpajakan', 'Operator'));
    }

    public function test_peran_kosong_tidak_dilarang(): void
    {
        // null/'' = permintaan tanpa sesi. Bukan anggota daftar, jadi tak dilarang.
        $this->assertTrue(HandlerOptions::bolehMenunjuk(null, 'operator'));
        $this->assertTrue(HandlerOptions::bolehMenunjuk('', 'operator'));
    }
```

- [ ] **Step 2: Jalankan test, pastikan GAGAL**

```bash
php artisan test --filter=HandlerOptionsTest
```

Diharapkan: FAIL — `Call to undefined method App\Support\HandlerOptions::bolehMenunjuk()`.

- [ ] **Step 3: Implementasi**

Di `app/Support/HandlerOptions.php`, tambahkan konstanta tepat di bawah `ROLE_OPTIONS`, dan method di bawah `bagianMap()`. `App\Support\Role` sekelas namespace — **tidak perlu** statement `use`.

```php
    /**
     * Peran yang TIDAK punya jalur mundur: tak boleh menunjuk Operator maupun
     * Bagian. Dokumen bermasalah dikembalikan lewat Tim Verifikasi.
     *
     * Melengkapi keputusan 2026-07-24 yang menghapus halaman "Pengembalian"
     * perpajakan & akutansi — halamannya hilang, tapi dropdown Pengurus Dokumen
     * masih menyediakan jalurnya sampai perubahan ini.
     */
    private const TANPA_JALUR_MUNDUR = [
        'perpajakan',
        'akutansi',
        'pembayaran',
    ];

    /**
     * Bolehkah $rolePengguna memindahkan dokumen ke $target?
     *
     * Dipakai DUA sisi supaya UI dan server tak pernah berbeda aturan:
     * forDokumen() memangkas opsi dengannya, DocumentHandlerController::update()
     * menolak dengan 403 memakainya.
     *
     * Kedua argumen dinormalisasi: Role::ALIASES memuat 'akuntansi' => 'akutansi'
     * (ejaan proyek ini memang menyimpang), jadi perbandingan mentah akan
     * meloloskan akun beralias tanpa suara.
     */
    public static function bolehMenunjuk(?string $rolePengguna, string $target): bool
    {
        if (!in_array(Role::normalize($rolePengguna), self::TANPA_JALUR_MUNDUR, true)) {
            return true;
        }

        $tujuan = Role::normalize($target);

        return $tujuan !== 'operator' && !str_starts_with($tujuan, 'bagian_');
    }
```

- [ ] **Step 4: Jalankan test, pastikan LULUS**

```bash
php artisan test --filter=HandlerOptionsTest
```

Diharapkan: PASS (5 test baru + 6 test lama = 11).

- [ ] **Step 5: Buktikan assertion menggigit (3 mutasi)**

Lakukan satu per satu; tiap kali: ubah → jalankan → catat MERAH → pulihkan.

1. Ganti `Role::normalize($rolePengguna)` jadi `strtolower((string) $rolePengguna)`
   → `test_alias_peran_dan_target_ikut_dinormalisasi` MERAH (alias `akuntansi` lolos).
2. Hapus `'pembayaran'` dari `TANPA_JALUR_MUNDUR`
   → `test_tiga_role_tak_boleh_menunjuk_operator_maupun_bagian` MERAH.
3. Ganti `&& !str_starts_with($tujuan, 'bagian_')` jadi `&& true`
   → test yang sama MERAH pada target `bagian_akn`.

Setelah ketiganya: `git diff app/Support/HandlerOptions.php` **wajib kosong**.

- [ ] **Step 6: Commit**

```bash
git add app/Support/HandlerOptions.php tests/Unit/HandlerOptionsTest.php
git commit -m "feat(handler): aturan bolehMenunjuk - 3 role tanpa jalur mundur"
```

---

### Task 2: Pisahkan perhitungan nilai pengurus yang ditampilkan

Refaktor **behavior-preserving**. Jaring pengamannya adalah `HandlerTampilanPengembalianTest` yang sudah ada (6 test) — jalankan sebelum & sesudah.

**Files:**
- Modify: `app/Support/DocumentRow.php:98-114`
- Test: `tests/Unit/HandlerTampilanPengembalianTest.php`

**Interfaces:**
- Produces: `DocumentRow::handlerTampilanMentah(Dokumen $dokumen): ?string` — dipakai 10 call site di Task 4

**Mengapa dipisah:** penyusun opsi (Task 4) perlu tahu nilai ini untuk mempertahankannya saat memangkas, sementara `handlerUntukTampilan()` sendiri butuh daftar opsi. Tanpa pemisahan, keduanya saling menunggu.

- [ ] **Step 1: Jalankan jaring pengaman lebih dulu**

```bash
php artisan test --filter=HandlerTampilanPengembalianTest
```

Diharapkan: PASS (6 test). Catat angkanya — harus sama persis setelah refaktor.

- [ ] **Step 2: Tulis test baru yang gagal**

Tambahkan ke `tests/Unit/HandlerTampilanPengembalianTest.php`:

```php
    public function test_handler_tampilan_mentah_tak_butuh_daftar_opsi(): void
    {
        // Nilai yang sama dengan yang dipakai handlerUntukTampilan(), tapi dihitung
        // tanpa daftar opsi — inilah yang dioper ke HandlerOptions::forDokumen()
        // supaya opsi tersebut tidak ikut terpangkas.
        Bagian::create(['kode' => 'AKN', 'nama' => 'AKN']);

        $dikembalikan = $this->dokumen([
            'nomor_agenda'  => '11_2026',
            'status'        => 'returned_to_bidang',
            'return_source' => 'AKN',
        ]);
        $this->assertSame('bagian_akn', VerifikasiDocumentRow::handlerTampilanMentah($dikembalikan));

        $biasa = $this->dokumen(['nomor_agenda' => '12_2026']);
        $this->assertSame('team_verifikasi', VerifikasiDocumentRow::handlerTampilanMentah($biasa));

        $tanpaSumber = $this->dokumen([
            'nomor_agenda'  => '13_2026',
            'status'        => 'returned_to_bidang',
            'return_source' => null,
        ]);
        $this->assertSame('team_verifikasi', VerifikasiDocumentRow::handlerTampilanMentah($tanpaSumber));

        $sumberBasi = $this->dokumen([
            'nomor_agenda'  => '14_2026',
            'status'        => 'sedang diproses',
            'return_source' => 'AKN',
        ]);
        $this->assertSame('team_verifikasi', VerifikasiDocumentRow::handlerTampilanMentah($sumberBasi));
    }
```

- [ ] **Step 3: Jalankan test, pastikan GAGAL**

```bash
php artisan test --filter=HandlerTampilanPengembalianTest
```

Diharapkan: FAIL — `Call to undefined method ...::handlerTampilanMentah()`.

- [ ] **Step 4: Implementasi**

Di `app/Support/DocumentRow.php`, **ganti** method `handlerUntukTampilan()` (baris 98-114) dengan dua method berikut. Docblock panjang di atasnya (baris 79-97) **dipertahankan apa adanya** — pindahkan ke atas `handlerUntukTampilan()` yang baru.

```php
    /**
     * Nilai pengurus yang AKAN ditampilkan untuk baris ini, dihitung TANPA daftar
     * opsi.
     *
     * Dipisah dari handlerUntukTampilan() karena penyusun opsi
     * (App\Support\HandlerOptions::forDokumen) perlu mengetahui nilai ini agar
     * tidak memangkasnya — sementara handlerUntukTampilan() sendiri butuh daftar
     * opsi untuk memeriksa padanan. Tanpa pemisahan, keduanya saling menunggu.
     */
    public static function handlerTampilanMentah(Dokumen $dokumen): ?string
    {
        if (strtolower((string) $dokumen->status) !== 'returned_to_bidang') {
            return $dokumen->current_handler;
        }

        $kode = strtolower(trim((string) $dokumen->return_source));

        return $kode === '' ? $dokumen->current_handler : 'bagian_' . $kode;
    }

    protected static function handlerUntukTampilan(Dokumen $dokumen, array $handlerOptions): ?string
    {
        $nilai = static::handlerTampilanMentah($dokumen);

        // Nilai yang sama dengan current_handler tak perlu dibuktikan punya opsi:
        // kedua cabang di bawah akan menghasilkan nilai yang identik.
        if ($nilai === $dokumen->current_handler) {
            return $nilai;
        }

        return static::opsiHandlerAda((string) $nilai, $handlerOptions)
            ? $nilai
            : $dokumen->current_handler;
    }
```

- [ ] **Step 5: Jalankan test, pastikan LULUS**

```bash
php artisan test --filter=HandlerTampilanPengembalianTest
```

Diharapkan: PASS (7 test — 6 lama tetap hijau + 1 baru). **Kalau ada satu saja test lama yang merah, refaktor ini tidak behavior-preserving — jangan lanjut, perbaiki dulu.**

- [ ] **Step 6: Buktikan assertion menggigit (2 mutasi)**

1. Di `handlerTampilanMentah()`, ganti `'bagian_' . $kode` jadi `$kode`
   → `test_handler_tampilan_mentah_tak_butuh_daftar_opsi` **dan**
   `test_dokumen_dikembalikan_menampilkan_bagian_tujuan` MERAH.
2. Hapus penjaga status (baris `if (strtolower(...) !== 'returned_to_bidang')`)
   → `test_return_source_basi_diabaikan_bila_status_bukan_dikembalikan` MERAH.

Pulihkan; `git diff app/Support/DocumentRow.php` **wajib kosong** sebelum lanjut.

- [ ] **Step 7: Commit**

```bash
git add app/Support/DocumentRow.php tests/Unit/HandlerTampilanPengembalianTest.php
git commit -m "refactor(row): pisahkan handlerTampilanMentah dari handlerUntukTampilan"
```

---

### Task 3: Mesin Tabulator merender `<option disabled>`

Perubahan **aditif** di mesin bersama 5 role. Sebelum Task 4 tak ada satu pun opsi yang membawa penanda `disabled`, jadi perilaku semua role tetap identik — itu disengaja supaya perubahan mesin bisa masuk lebih dulu tanpa risiko.

**Files:**
- Modify: `public/js/document-tabulator.js` (fungsi `optionHtml` di dalam `fmtHandler`, sekitar baris 631)
- Create: `tests/Feature/LaranganJalurMundurTest.php`

**Interfaces:**
- Consumes: —
- Produces: opsi ber-`disabled: true` dirender sebagai `<option disabled>` (dipakai Task 4)

- [ ] **Step 1: Tulis test yang gagal**

Buat `tests/Feature/LaranganJalurMundurTest.php`:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Menguji larangan jalur mundur ke Operator & Bagian untuk perpajakan, akutansi,
 * dan pembayaran.
 *
 * Pemangkasan dropdown saja tidak cukup: opsi ditanam di data baris yang dikirim
 * ke klien, jadi bisa diakali lewat devtools. Penegakan sesungguhnya ada di
 * DocumentHandlerController::update().
 */
class LaranganJalurMundurTest extends TestCase
{
    public function test_formatter_handler_merender_opsi_nonaktif(): void
    {
        $js = file_get_contents(public_path('js/document-tabulator.js'));

        // Assertion dipersempit ke BADAN fungsi: kata 'disabled' sudah muncul di
        // banyak tempat lain di berkas ini (atribut select, judul tooltip), jadi
        // pencarian ke seluruh berkas akan hampa.
        $this->assertSame(
            1,
            substr_count($js, 'function optionHtml(o) {'),
            'optionHtml tidak lagi tunggal — persempit ulang assertion ini'
        );

        $mulai = strpos($js, 'function optionHtml(o) {');
        $badan = substr($js, $mulai, 400);

        $this->assertStringContainsString(
            "(o.disabled ? ' disabled' : '')",
            $badan,
            'optionHtml tidak merender atribut disabled'
        );
    }
}
```

- [ ] **Step 2: Jalankan test, pastikan GAGAL**

```bash
php artisan test --filter=LaranganJalurMundurTest
```

Diharapkan: FAIL — `optionHtml tidak merender atribut disabled`.

- [ ] **Step 3: Implementasi**

Di `public/js/document-tabulator.js`, ganti isi `optionHtml` (di dalam `fmtHandler`):

```js
    function optionHtml(o) {
      const val = (o.value === null || o.value === undefined) ? '' : String(o.value);
      // `disabled` dipakai opsi yang dipertahankan HANYA demi kejujuran tampilan
      // (lihat App\Support\HandlerOptions::pangkasTerlarang): nilainya adalah
      // pengurus baris ini, tapi role penonton tak boleh menunjuknya.
      return '<option value="' + esc(val) + '"' +
        (val === current ? ' selected' : '') +
        (o.disabled ? ' disabled' : '') +
        '>' + esc(o.label) + '</option>';
    }
```

- [ ] **Step 4: Jalankan test, pastikan LULUS**

```bash
php artisan test --filter=LaranganJalurMundurTest
```

Diharapkan: PASS.

- [ ] **Step 5: Buktikan assertion menggigit**

Hapus baris `(o.disabled ? ' disabled' : '') +` → test MERAH. Pulihkan → HIJAU.
`git diff public/js/document-tabulator.js` **wajib kosong** setelah pemulihan.

> **Awas CRLF:** repo ini ber-`core.autocrlf=true`. Bila menyunting berkas ini lewat skrip Python/Node, akhiran baris bisa berubah LF→CRLF dan mematahkan test pembanding sumber, sementara `git diff` terlihat kosong. Sunting lewat editor biasa.

- [ ] **Step 6: Commit**

```bash
git add public/js/document-tabulator.js tests/Feature/LaranganJalurMundurTest.php
git commit -m "feat(tabulator): dukung opsi pengurus nonaktif (aditif, nol perubahan perilaku)"
```

---

### Task 4: Pemangkasan opsi + 10 call site

Task terbesar. Tanda tangan `forDokumen()` berubah, jadi **seluruh** call site wajib ikut dalam commit yang sama — kalau tidak, aplikasi rusak di antara commit.

**Files:**
- Modify: `app/Support/HandlerOptions.php`
- Modify: `app/Http/Controllers/DokumenController.php:145,181,423`
- Modify: `app/Http/Controllers/DashboardAkutansiController.php:58,91`
- Modify: `app/Http/Controllers/DashboardPerpajakanController.php:67,100`
- Modify: `app/Http/Controllers/TeamVerifikasiController.php:85,119`
- Modify: `app/Http/Controllers/DashboardPembayaranController.php:669`
- Test: `tests/Unit/HandlerOptionsTest.php`, `tests/Unit/HandlerTampilanPengembalianTest.php`

**Interfaces:**
- Consumes: `HandlerOptions::bolehMenunjuk()` (Task 1), `DocumentRow::handlerTampilanMentah()` (Task 2)
- Produces: `HandlerOptions::forDokumen(?string $bagian, array $bagianMap, ?string $rolePengguna, ?string $handlerDipertahankan): array`

- [ ] **Step 1: Tulis test yang gagal**

Tambahkan ke `tests/Unit/HandlerOptionsTest.php`:

```php
    private const OPSI_TANPA_MUNDUR = [
        ['value' => 'team_verifikasi', 'label' => 'Tim Verifikasi'],
        ['value' => 'perpajakan',      'label' => 'Tim Perpajakan'],
        ['value' => 'akutansi',        'label' => 'Tim Akuntansi'],
        ['value' => 'pembayaran',      'label' => 'Tim Pembayaran'],
    ];

    public function test_tiga_role_kehilangan_operator_dan_optgroup_bagian(): void
    {
        Bagian::create(['kode' => 'AKN', 'nama' => 'Akuntansi']);
        $peta = HandlerOptions::bagianMap();

        foreach (['perpajakan', 'akutansi', 'pembayaran'] as $role) {
            $this->assertSame(
                self::OPSI_TANPA_MUNDUR,
                HandlerOptions::forDokumen('AKN', $peta, $role, null),
                "Role {$role} masih menawarkan jalur mundur"
            );
        }
    }

    public function test_operator_dan_verifikasi_tetap_menerima_daftar_penuh(): void
    {
        Bagian::create(['kode' => 'AKN', 'nama' => 'Akuntansi']);
        $peta = HandlerOptions::bagianMap();
        $penuh = array_merge(self::OPSI_PERAN, [[
            'optgroup' => 'Bagian',
            'options'  => [['value' => 'bagian_akn', 'label' => 'Akuntansi']],
        ]]);

        foreach (['operator', 'team_verifikasi'] as $role) {
            $this->assertSame($penuh, HandlerOptions::forDokumen('AKN', $peta, $role, null), $role);
        }
    }

    public function test_opsi_operator_bertahan_nonaktif_bila_ia_pengurus_baris_itu(): void
    {
        // Perpajakan & akutansi melihat SELURUH dokumen, termasuk yang masih di
        // Operator. Kalau opsinya dibuang, <select> kehilangan nilai terpilih dan
        // browser jatuh ke opsi pertama — tabel akan menampilkan pengurus KELIRU.
        Bagian::create(['kode' => 'AKN', 'nama' => 'Akuntansi']);

        $opsi = HandlerOptions::forDokumen('AKN', HandlerOptions::bagianMap(), 'perpajakan', 'operator');

        $this->assertSame(
            ['value' => 'operator', 'label' => 'Operator', 'disabled' => true],
            $opsi[0]
        );
    }

    public function test_optgroup_bagian_bertahan_nonaktif_untuk_pembayaran(): void
    {
        // Pembayaran satu-satunya role yang melihat dokumen returned_to_bidang,
        // dan nilai tampilnya bagian_<kode> (DocumentRow::handlerTampilanMentah).
        Bagian::create(['kode' => 'AKN', 'nama' => 'Akuntansi']);

        $opsi = HandlerOptions::forDokumen('AKN', HandlerOptions::bagianMap(), 'pembayaran', 'bagian_akn');
        $terakhir = end($opsi);

        $this->assertSame('Bagian', $terakhir['optgroup']);
        $this->assertSame(
            ['value' => 'bagian_akn', 'label' => 'Akuntansi', 'disabled' => true],
            $terakhir['options'][0]
        );
    }

    public function test_nilai_dipertahankan_tidak_menghidupkan_opsi_terlarang_lain(): void
    {
        // Yang dipertahankan HANYA nilai itu sendiri — bukan seluruh kelompok terlarang.
        Bagian::create(['kode' => 'AKN', 'nama' => 'Akuntansi']);

        $opsi = HandlerOptions::forDokumen('AKN', HandlerOptions::bagianMap(), 'perpajakan', 'operator');

        foreach ($opsi as $o) {
            $this->assertArrayNotHasKey('optgroup', $o, 'optgroup Bagian ikut terbawa');
        }
    }
```

**Perbarui juga 6 test lama** di berkas yang sama — semuanya memanggil `forDokumen()` berargumen dua. Tambahkan `, 'operator', null` pada setiap pemanggilan (peran `operator` tidak dilarang, jadi harapan lama tetap berlaku). Pemanggilan yang harus disunting ada di baris 35, 49, 50, 51, 58, 71, 81, 96.

Dan di `tests/Unit/HandlerTampilanPengembalianTest.php`, method `baris()`:

```php
        return VerifikasiDocumentRow::fromDokumen(
            $dokumen,
            HandlerOptions::forDokumen(
                $dokumen->bagian,
                HandlerOptions::bagianMap(),
                'team_verifikasi',
                VerifikasiDocumentRow::handlerTampilanMentah($dokumen)
            ),
            'team_verifikasi'
        );
```

- [ ] **Step 2: Jalankan test, pastikan GAGAL**

```bash
php artisan test --filter=HandlerOptionsTest
```

Diharapkan: FAIL — `ArgumentCountError` / jumlah argumen tak cocok.

- [ ] **Step 3: Implementasi pemangkasan**

Di `app/Support/HandlerOptions.php`, ganti `forDokumen()` dan tambahkan dua helper privat:

```php
    /**
     * Opsi untuk SATU dokumen: 5 peran + optgroup Bagian berisi PALING BANYAK satu
     * entri, yaitu bagian milik dokumen itu — lalu dipangkas sesuai wewenang
     * $rolePengguna.
     *
     * @param  array<string, array{value: string, label: string}>  $bagianMap  hasil bagianMap()
     * @param  ?string  $rolePengguna        peran penonton; null = tanpa sesi, tak dilarang
     * @param  ?string  $handlerDipertahankan nilai pengurus yang SEDANG DITAMPILKAN untuk
     *                                        baris ini (DocumentRow::handlerTampilanMentah)
     */
    public static function forDokumen(
        ?string $bagian,
        array $bagianMap,
        ?string $rolePengguna,
        ?string $handlerDipertahankan
    ): array {
        $options = self::ROLE_OPTIONS;

        $kunci = strtoupper(trim((string) $bagian));

        if ($kunci !== '' && isset($bagianMap[$kunci])) {
            $options[] = [
                'optgroup' => 'Bagian',
                'options'  => [$bagianMap[$kunci]],
            ];
        }

        return self::pangkasTerlarang($options, $rolePengguna, $handlerDipertahankan);
    }

    /**
     * Buang opsi yang tak boleh ditunjuk $rolePengguna.
     *
     * Opsi yang kebetulan merupakan pengurus yang SEDANG DITAMPILKAN untuk baris
     * itu TIDAK dibuang, melainkan ditandai 'disabled' => true. Kalau dibuang,
     * <select> kehilangan nilai terpilihnya dan browser jatuh ke opsi pertama —
     * tabel lalu menampilkan pengurus yang keliru. Bahaya itu sudah pernah
     * terjadi; lihat catatan di DocumentRow::handlerUntukTampilan().
     */
    private static function pangkasTerlarang(
        array $options,
        ?string $rolePengguna,
        ?string $handlerDipertahankan
    ): array {
        $hasil = [];

        foreach ($options as $opsi) {
            if (isset($opsi['optgroup'])) {
                $anak = [];

                foreach (($opsi['options'] ?? []) as $o) {
                    $disaring = self::saringOpsi($o, $rolePengguna, $handlerDipertahankan);

                    if ($disaring !== null) {
                        $anak[] = $disaring;
                    }
                }

                if ($anak !== []) {
                    $hasil[] = ['optgroup' => $opsi['optgroup'], 'options' => $anak];
                }

                continue;
            }

            $disaring = self::saringOpsi($opsi, $rolePengguna, $handlerDipertahankan);

            if ($disaring !== null) {
                $hasil[] = $disaring;
            }
        }

        return $hasil;
    }

    /** Null bila opsi harus dibuang; array opsi (mungkin ber-'disabled') bila dipertahankan. */
    private static function saringOpsi(
        array $opsi,
        ?string $rolePengguna,
        ?string $handlerDipertahankan
    ): ?array {
        $nilai = (string) ($opsi['value'] ?? '');

        if (self::bolehMenunjuk($rolePengguna, $nilai)) {
            return $opsi;
        }

        if ($handlerDipertahankan !== null && $nilai === $handlerDipertahankan) {
            return $opsi + ['disabled' => true];
        }

        return null;
    }
```

- [ ] **Step 4: Perbarui 10 call site**

Verifikasi daftarnya lebih dulu — nomor baris di rencana ini bisa sudah bergeser:

```bash
grep -rn "HandlerOptions::forDokumen" app/Http/Controllers/
```

Pola untuk call site yang punya `$viewerRole` (akutansi 58 & 91, perpajakan 67 & 100, operator 145 & 181):

```php
                \App\Support\HandlerOptions::forDokumen(
                    $d->bagian,
                    $bagianMap,
                    $viewerRole,
                    \App\Support\DocumentRow::handlerTampilanMentah($d)
                ),
```

**`DokumenController:145`** belum punya `$viewerRole` — ia menuliskan `auth()->user()?->role` langsung sebagai argumen ketiga `fromDokumen()`. Tambahkan variabel di bawah baris `$bagianMap = ...` lalu pakai di kedua tempat:

```php
        $bagianMap  = \App\Support\HandlerOptions::bagianMap();
        $viewerRole = auth()->user()?->role;
```

**`DokumenController:423`** (baris hasil quick-add, dokumen tunggal — perhatikan variabelnya `$dokumen`, bukan `$d`):

```php
            'row'     => \App\Support\OperatorDocumentRow::fromDokumen(
                $dokumen,
                \App\Support\HandlerOptions::forDokumen(
                    $dokumen->bagian,
                    \App\Support\HandlerOptions::bagianMap(),
                    auth()->user()?->role,
                    \App\Support\DocumentRow::handlerTampilanMentah($dokumen)
                ),
                auth()->user()?->role
            ),
```

**`TeamVerifikasiController:85` & `:119`** — perannya literal:

```php
                \App\Support\HandlerOptions::forDokumen(
                    $d->bagian,
                    $bagianMap,
                    'team_verifikasi',
                    \App\Support\DocumentRow::handlerTampilanMentah($d)
                ),
```

**`DashboardPembayaranController:669`** — literal `'pembayaran'`:

```php
                \App\Support\HandlerOptions::forDokumen(
                    $d->bagian,
                    $bagianMap,
                    'pembayaran',
                    \App\Support\DocumentRow::handlerTampilanMentah($d)
                ),
```

- [ ] **Step 5: Pastikan nol sisa pemanggilan lama**

```bash
grep -rn "forDokumen(\$d->bagian, \$bagianMap)" app/
grep -rn "forDokumen(" app/ tests/ | grep -v "handlerTampilanMentah" | grep -v "bagianMap(), '"
```

Keduanya harus kosong (perintah kedua boleh menyisakan pemanggilan multi-baris — periksa manual bahwa semuanya berargumen empat).

- [ ] **Step 6: Jalankan test, pastikan LULUS**

```bash
php artisan test --filter=HandlerOptionsTest
php artisan test --filter=HandlerTampilanPengembalianTest
php artisan test --filter=PengembalianKeBagianAsalTest
```

Diharapkan: ketiganya PASS.

- [ ] **Step 7: Buktikan assertion menggigit (3 mutasi)**

1. Di `forDokumen()`, kembalikan `$options` langsung (lewati `pangkasTerlarang`)
   → `test_tiga_role_kehilangan_operator_dan_optgroup_bagian` MERAH.
2. Di `saringOpsi()`, hapus cabang `$handlerDipertahankan` (langsung `return null`)
   → `test_opsi_operator_bertahan_nonaktif_bila_ia_pengurus_baris_itu` **dan**
   `test_optgroup_bagian_bertahan_nonaktif_untuk_pembayaran` MERAH.
3. Di `saringOpsi()`, ganti `$nilai === $handlerDipertahankan` jadi `true`
   → `test_nilai_dipertahankan_tidak_menghidupkan_opsi_terlarang_lain` MERAH.

Pulihkan; `git diff app/Support/HandlerOptions.php` **wajib kosong**.

- [ ] **Step 8: Commit**

```bash
git add app/Support/HandlerOptions.php
git add app/Http/Controllers/DokumenController.php
git add app/Http/Controllers/DashboardAkutansiController.php
git add app/Http/Controllers/DashboardPerpajakanController.php
git add app/Http/Controllers/TeamVerifikasiController.php
git add app/Http/Controllers/DashboardPembayaranController.php
git add tests/Unit/HandlerOptionsTest.php tests/Unit/HandlerTampilanPengembalianTest.php
git commit -m "feat(handler): pangkas opsi mundur di dropdown 3 role keuangan"
```

---

### Task 5: Penegakan sisi server (403)

Inti fitur. Tanpa ini semua yang di atas hanya kosmetik.

**Files:**
- Modify: `app/Http/Controllers/DocumentHandlerController.php` (sisipkan setelah blok baris 54-59)
- Test: `tests/Feature/LaranganJalurMundurTest.php`

**Interfaces:**
- Consumes: `HandlerOptions::bolehMenunjuk()` (Task 1) — `use App\Support\HandlerOptions;` sudah ada di baris 10

- [ ] **Step 1: Tulis test yang gagal**

Tambahkan ke `tests/Feature/LaranganJalurMundurTest.php`. Tambahkan pula import & trait di bagian atas kelas:

```php
use App\Models\Bagian;
use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
```

```php
    use RefreshDatabase;

    private function dokumenDi(string $handler): Dokumen
    {
        return Dokumen::create([
            'nomor_agenda'    => '1_2026',
            'bulan'           => 'Agustus',
            'tahun'           => 2026,
            'tanggal_masuk'   => '2026-08-01',
            'status'          => 'sedang diproses',
            'created_by'      => 'operator',
            'current_handler' => $handler,
            'bagian'          => 'KEU',
        ]);
    }

    public function test_perpajakan_tak_bisa_mengembalikan_ke_operator(): void
    {
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $dokumen = $this->dokumenDi('perpajakan');

        $this->actingAs(User::factory()->create(['role' => 'perpajakan']))
            ->patchJson(route('documents.handler.update', $dokumen), [
                'target_handler' => 'operator',
            ])
            ->assertStatus(403)
            ->assertJson(['success' => false]);

        $this->assertSame('perpajakan', $dokumen->fresh()->current_handler);
    }

    public function test_pembayaran_tak_bisa_mengembalikan_ke_operator(): void
    {
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $dokumen = $this->dokumenDi('pembayaran');

        $this->actingAs(User::factory()->create(['role' => 'pembayaran']))
            ->patchJson(route('documents.handler.update', $dokumen), [
                'target_handler' => 'operator',
            ])
            ->assertStatus(403);

        $this->assertSame('pembayaran', $dokumen->fresh()->current_handler);
    }

    public function test_akutansi_tak_bisa_mengembalikan_ke_bagian_dan_nol_notifikasi(): void
    {
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        // Penerima nyata WAJIB ada: cabang in-app DocumentReturnNotifier selalu
        // jalan bila ada user ber-bagian_code. Tanpa user ini assertion notifikasi
        // akan hijau meski larangan dicabut — hampa.
        User::factory()->create(['role' => 'bagian_keu', 'bagian_code' => 'KEU']);
        Notification::fake();

        $dokumen = $this->dokumenDi('akutansi');

        $this->actingAs(User::factory()->create(['role' => 'akutansi']))
            ->patchJson(route('documents.handler.update', $dokumen), [
                'target_handler' => 'bagian_keu',
                'return_reason'  => 'Lampiran faktur belum lengkap sama sekali.',
            ])
            ->assertStatus(403);

        $this->assertSame('sedang diproses', $dokumen->fresh()->status);
        $this->assertNull($dokumen->fresh()->return_reason);
        Notification::assertNothingSent();
    }

    public function test_perpajakan_tetap_bisa_meneruskan_ke_akutansi(): void
    {
        // Gerbang tidak boleh melebar ke target yang sah.
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $dokumen = $this->dokumenDi('perpajakan');

        $this->actingAs(User::factory()->create(['role' => 'perpajakan']))
            ->patchJson(route('documents.handler.update', $dokumen), [
                'target_handler' => 'akutansi',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_verifikasi_tetap_bisa_mengembalikan_ke_operator(): void
    {
        // Role di luar daftar tidak boleh ikut terkena.
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $dokumen = $this->dokumenDi('team_verifikasi');

        $this->actingAs(User::factory()->create(['role' => 'team_verifikasi']))
            ->patchJson(route('documents.handler.update', $dokumen), [
                'target_handler' => 'operator',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame('operator', $dokumen->fresh()->current_handler);
    }

    public function test_operator_tetap_bisa_mengembalikan_ke_bagian(): void
    {
        // Jalur operator utuh — ia hulu alur dan memang berwenang.
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $dokumen = $this->dokumenDi('operator');

        $this->actingAs(User::factory()->create(['role' => 'operator']))
            ->patchJson(route('documents.handler.update', $dokumen), [
                'target_handler' => 'bagian_keu',
                'return_reason'  => 'Nomor SPP belum dicantumkan pada berkas.',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame('returned_to_bidang', $dokumen->fresh()->status);
    }

    public function test_akun_beralias_akuntansi_ikut_ditolak(): void
    {
        // Kolom role produksi bisa berisi alias. Tanpa Role::normalize() di
        // bolehMenunjuk(), akun seperti ini LOLOS tanpa suara.
        Bagian::create(['kode' => 'KEU', 'nama' => 'Keuangan']);
        $dokumen = $this->dokumenDi('akutansi');

        $this->actingAs(User::factory()->create(['role' => 'akuntansi']))
            ->patchJson(route('documents.handler.update', $dokumen), [
                'target_handler' => 'operator',
            ])
            ->assertStatus(403);

        $this->assertSame('akutansi', $dokumen->fresh()->current_handler);
    }
```

- [ ] **Step 2: Jalankan test, pastikan GAGAL**

```bash
php artisan test --filter=LaranganJalurMundurTest
```

Diharapkan: FAIL — status 200 (atau 422), bukan 403; dokumen berpindah.

- [ ] **Step 3: Implementasi**

Di `app/Http/Controllers/DocumentHandlerController.php`, sisipkan **tepat setelah** blok `if (!$canReceiveReturnedBagian && $userRole !== $currentHandler) { ... }` (berakhir baris 59) dan **sebelum** `if ($this->hasPendingApproval($dokumen))`:

```php
        // Perpajakan/akutansi/pembayaran tidak punya jalur mundur — dokumen
        // bermasalah dikembalikan lewat Tim Verifikasi, bukan langsung ke Operator
        // atau Bagian. Melengkapi keputusan 2026-07-24 yang menghapus halaman
        // "Pengembalian" kedua role pertama; dropdown masih menyisakan jalurnya.
        //
        // Ditaruh SEBELUM beginTransaction() dan sebelum DocumentReturnNotifier,
        // supaya percobaan yang ditolak tidak mengubah data dan tidak mengirim
        // notifikasi apa pun.
        if (!HandlerOptions::bolehMenunjuk($userRole, $targetHandler)) {
            return response()->json([
                'success' => false,
                'message' => Dokumen::getRoleDisplayNameIndo($userRole)
                    . ' tidak dapat mengembalikan dokumen ke Operator atau Bagian. '
                    . 'Kembalikan melalui Tim Verifikasi.',
            ], 403);
        }
```

- [ ] **Step 4: Jalankan test, pastikan LULUS**

```bash
php artisan test --filter=LaranganJalurMundurTest
php artisan test --filter=PengembalianKeBagianAsalTest
php artisan test --filter=TanggalHasilKoreksiBagianTest
```

Ketiganya PASS. Dua yang terakhir menjaga jalur verifikasi yang **tidak boleh** ikut terblokir.

- [ ] **Step 5: Buktikan assertion menggigit (3 mutasi)**

1. Balik kondisi jadi `if (HandlerOptions::bolehMenunjuk(...))`
   → seluruh test penolakan MERAH **dan** `test_perpajakan_tetap_bisa_meneruskan_ke_akutansi` MERAH.
2. Pindahkan blok ini ke **bawah** `DB::beginTransaction()`
   → `test_akutansi_tak_bisa_mengembalikan_ke_bagian_dan_nol_notifikasi` MERAH
   (notifikasi terkirim sebelum penolakan).
3. Di `HandlerOptions::bolehMenunjuk()`, kembalikan `Role::normalize()` ke perbandingan mentah
   → `test_akun_beralias_akuntansi_ikut_ditolak` MERAH.

Pulihkan ketiganya; `git diff` **wajib kosong** di kedua berkas.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/DocumentHandlerController.php tests/Feature/LaranganJalurMundurTest.php
git commit -m "feat(handler): tolak 403 jalur mundur ke Operator/Bagian di server"
```

---

### Task 6: Docblock basi, suite penuh, deploy, QA browser

**Files:**
- Modify: `app/Support/HandlerOptions.php` (docblock kelas, baris 14-15)
- Modify: `app/Support/PembayaranDocumentRow.php` (docblock, baris 27)
- Modify: `CLAUDE.md` (§7)

- [ ] **Step 1: Perbaiki dua docblock yang sudah tidak benar**

`app/Support/HandlerOptions.php` — ganti kalimat *"Pembayaran TIDAK memakai kelas ini: PembayaranDocumentRow berjalan dengan showHandler: false dan menerima `$handlerOptions = []` (default)."* menjadi:

```
 * Pembayaran IKUT memakai kelas ini sejak kolom "Pengurus Dokumen" dihidupkan
 * untuk role tersebut (dashboardPembayaran.blade.php: showHandler => true);
 * kalimat lama yang menyatakan sebaliknya sudah tidak berlaku.
```

`app/Support/PembayaranDocumentRow.php` baris 27-30 — ganti paragraf ini:

```
 * TANPA forward: base menaruh handler/handler_options/can_change_handler,
 * dibiarkan apa adanya (tak dipakai view pembayaran; engine Tabulator akan
 * menyembunyikan kolomnya via showHandler:false — bagian Task 3, bukan
 * tugas file ini).
```

menjadi:

```
 * DENGAN forward: base menaruh handler/handler_options/can_change_handler dan
 * ketiganya kini DIPAKAI — kolom "Pengurus Dokumen" dihidupkan untuk pembayaran
 * (dashboardPembayaran.blade.php: showHandler => true). Kalimat lama di sini yang
 * menyatakan kolomnya disembunyikan sudah tidak berlaku. Sejak 2026-08-09 opsi
 * Operator & Bagian dipangkas untuk role ini (App\Support\HandlerOptions).
```

- [ ] **Step 2: Perbarui CLAUDE.md §7**

Tambahkan paragraf baru setelah paragraf tombol Uji WhatsApp:

```markdown
**Larangan jalur mundur ke Operator & Bagian (perpajakan, akutansi, pembayaran) —
SELESAI 2026-08-09.** Ketiga role tak lagi bisa memindahkan dokumen ke Operator
maupun ke Bagian; jalan mundur mereka lewat Tim Verifikasi. Aturannya SATU tempat:
`App\Support\HandlerOptions::bolehMenunjuk()`, dipakai penyusun dropdown (memangkas)
DAN `DocumentHandlerController::update()` (menolak 403 sebelum transaksi & sebelum
notifier). Perbandingan peran WAJIB lewat `Role::normalize()` — alias
`'akuntansi' => 'akutansi'` membuat perbandingan mentah gagal tanpa suara.
Opsi terlarang yang kebetulan merupakan pengurus baris itu TIDAK dibuang melainkan
ditandai `disabled` (dirender `<option disabled>` oleh `document-tabulator.js`):
ketiga role melihat dokumen milik role lain, dan membuang opsinya membuat `<select>`
jatuh ke opsi pertama sehingga menampilkan pengurus yang KELIRU. `forDokumen()` kini
berargumen empat — 10 call site. Operator & verifikasi TIDAK berubah.
- Spec/plan: `docs/superpowers/specs/2026-08-09-larangan-mundur-perpajakan-akutansi-pembayaran-design.md`,
  `docs/superpowers/plans/2026-08-09-larangan-mundur-perpajakan-akutansi-pembayaran.md`
```

- [ ] **Step 3: Suite penuh**

```bash
php artisan test
```

**Wajib hijau seluruhnya sebelum push** (CLAUDE.md aturan 3). Catat jumlah test & assertion. Bila ada yang merah, perbaiki sebelum lanjut — jangan push.

- [ ] **Step 4: Commit**

```bash
git add app/Support/HandlerOptions.php app/Support/PembayaranDocumentRow.php CLAUDE.md
git commit -m "docs: perbarui docblock pembayaran yang basi + catat larangan jalur mundur"
```

- [ ] **Step 5: Deploy**

```bash
git push origin codinggemini
```

Lalu di server (lihat `deploy_to_server.bat` untuk detail koneksi):

```bash
git pull
php artisan route:clear && php artisan view:clear && php artisan config:clear
```

**Clear cache tidak boleh dilewat.** Berkas JS berubah — periksa juga bahwa `Asset::versioned()` menghasilkan URL baru, atau lakukan hard-reload saat QA agar tidak menguji berkas basi dari cache browser.

- [ ] **Step 6: QA browser produksi**

Akun & URL ada di memori `browser-qa-access`. Yang **wajib** dibuktikan dengan mata, bukan hanya test:

1. Login `pajak` → `/dashboard/perpajakan`. Buka dropdown Pengurus Dokumen pada baris yang dokumennya dipegang perpajakan → **tidak ada** "Operator", **tidak ada** kelompok "Bagian".
2. Pada halaman yang sama, cari baris yang pengurusnya masih **Operator** → dropdown-nya harus tetap **menampilkan "Operator"** (dalam keadaan non-aktif), bukan melompat ke "Tim Verifikasi". **Ini pemeriksaan terpenting** — kalau gagal, tabel berbohong tentang posisi dokumen.
3. Ulangi 1 & 2 dengan akun `akuntansi` → `/dashboard/akutansi`.
4. Login `pembayaran` → `/documents/pembayaran/daftar`. Selain 1 & 2, cari baris berstatus dikembalikan → kelompok Bagian harus tetap tampil non-aktif dengan bagian yang benar.
5. Login `input` (operator) dan akun verifikasi → dropdown keduanya **masih lengkap**, termasuk Operator & Bagian.
6. Uji penolakan server: dari akun perpajakan, kirim PATCH langsung lewat konsol devtools ke `documents.handler.update` dengan `target_handler: 'operator'` → harus **403** dan dokumen tidak bergerak.

Catat hasil tiap butir apa adanya. Bila ada yang gagal, **berhenti** dan laporkan — jangan nyatakan lolos atas nama user.

---

## Catatan Eksekusi

- Ledger SDD: `.superpowers/sdd/2026-08-09-larangan-mundur-perpajakan-akutansi-pembayaran/progress.md`
- Task 3, 4, dan 5 masing-masing menyentuh berkas yang dipakai lintas-role. Bila ada temuan yang menuntut keputusan desain baru, **berhenti dan tanya user** (CLAUDE.md §6).
- Suite penuh **hanya** di Task 6. Task 1-5 cukup terfilter.
