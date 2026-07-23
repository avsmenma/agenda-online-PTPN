# Operator `/documents` → Tabulator-only Cleanup Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menghapus seluruh kode tabel classic operator sehingga `/documents` hanya dilayani view Tabulator.

**Architecture:** Pembuangan bertahap dengan urutan aman: (1) paksa controller selalu Tabulator, (2) lepas render `_tableRowsAjax` dari endpoint bersama `inlineCreate()`, (3) baru hapus berkas view classic + cabang `virtual_chunk`, (4-5) hapus route+method yatim (`detail`, `send-to-verifikasi`). Tiap tugas menjaga suite hijau dan meninggalkan grep bersih.

**Tech Stack:** Laravel 12, PHP ^8.2, Blade, PHPUnit (SQLite in-memory untuk test), tabel Tabulator.js (view yang bertahan).

Spec sumber: `docs/superpowers/specs/2026-07-23-operator-tabulator-only-cleanup-design.md`.

> **Nomor baris bersifat indikatif** (snapshot pra-cleanup, sebelum task mana pun jalan).
> Karena task-task ini menambah/menghapus baris, offset akan bergeser di task berikutnya.
> **Jangkar sebenarnya adalah blok kode yang ditampilkan** di tiap langkah — cari & ganti
> berdasarkan isinya, bukan berdasarkan nomor baris.

## Global Constraints

- `git add` **per-file** — JANGAN `git add .` / `git add -A`.
- Pesan commit **Bahasa Indonesia**. Satu commit = satu perubahan logis.
- Komentar **Indonesia**, identifier **English**.
- `php artisan test` **harus hijau** sebelum tiap commit.
- `node --check public/js/operator-tabulator.js` **exit 0** (file ini TIDAK diubah, hanya sanity check di gate akhir).
- **Hapus-saja tanpa rename**: `operator-tabulator.js`, `OPERATOR_TABULATOR_CONFIG`, id `#operatorTabulatorTable` DIBIARKAN utuh.
- **JANGAN sentuh**: partial bersama (`partials/_documentTableStickyCells`, `virtual-document-table`, `_inlineEditEngine`, `_activeCellNav`, `_compactDocumentTable`, `document-handler-select`, global `compact-document-ui` & `document-workbench-ui`); endpoint bersama (`documents.inline-update`, `documents.inline-create`, `documents.destroy`, `documents.handler.update`, `documents.data`); controller/view role lain (team_verifikasi, akutansi, perpajakan, pembayaran, bagian).
- **JANGAN** jalankan perintah destruktif DB. Deploy hanya SETELAH QA visual user lolos: commit → `git push origin codinggemini` → pull di server → `php artisan route:clear && view:clear && config:clear`.
- Di luar lingkup (JANGAN dihapus): route yatim pra-ada `documents.progress`, `documents.approve`, `documents.bulk-send-to-verifikasi`.

---

### Task 1: Paksa operator index selalu Tabulator (matikan flag `?classic`)

**Files:**
- Modify: `app/Http/Controllers/DokumenController.php:185-187` (guard sesi sort) dan `:321-323` (pemilih view)
- Test: `tests/Feature/OperatorTabulatorViewTest.php` (docblock kelas + 2 metode classic)

**Interfaces:**
- Consumes: —
- Produces: `DokumenController::index()` selalu mengembalikan `view('operator.dokumens.daftarDokumenTabulator', $data)`; sesi `operator_sort_column`/`operator_sort_order` selalu dibersihkan.

- [ ] **Step 1: Tulis ulang test classic menjadi "flag diabaikan"**

Di `tests/Feature/OperatorTabulatorViewTest.php`, ganti docblock kelas (baris 10-14):

```php
/**
 * Menguji cabang view pada DokumenController@index:
 * - operator selalu disajikan view Tabulator (daftarDokumenTabulator) + memuat aset dist.
 * - flag ?classic sudah tidak berpengaruh: tabel classic dihapus (2026-07-23).
 */
```

Ganti metode `test_flag_classic_menyajikan_view_lama()` (baris 99-107) menjadi:

```php
    public function test_flag_classic_diabaikan_menyajikan_tabulator(): void
    {
        $response = $this->actingAs($this->operator())
            ->get(route('documents.index', ['classic' => 1]));

        $response->assertOk();
        // Flag ?classic tak lagi berpengaruh — tabel classic dihapus, selalu Tabulator.
        $response->assertSee('operatorTabulatorTable', false);
        $response->assertDontSee('id="btnTambahBarisInline"', false);
    }
```

Ganti docblock (baris 130-134) + metode `test_flag_classic_tidak_membersihkan_sesi_sort_lama()` (baris 135-147) menjadi:

```php
    /**
     * Flag ?classic tak lagi punya jalur sort sendiri (tabel classic dihapus), jadi
     * mengunjungi index dengan ?classic=1 pun tetap membersihkan sesi sort lama —
     * tidak ada lagi kondisi yang mempertahankannya.
     */
    public function test_flag_classic_diabaikan_tetap_membersihkan_sesi_sort(): void
    {
        $response = $this->actingAs($this->operator())
            ->withSession([
                'operator_sort_column' => 'nomor_spp',
                'operator_sort_order'  => 'asc',
            ])
            ->get(route('documents.index', ['classic' => 1]));

        $response->assertOk();
        $response->assertSessionMissing('operator_sort_column');
        $response->assertSessionMissing('operator_sort_order');
    }
```

- [ ] **Step 2: Jalankan test → pastikan GAGAL**

Run: `php artisan test --filter=OperatorTabulatorViewTest`
Expected: FAIL — `test_flag_classic_diabaikan_menyajikan_tabulator` gagal (controller masih menyajikan view classic saat `?classic=1`), `test_flag_classic_diabaikan_tetap_membersihkan_sesi_sort` gagal (sesi masih dipertahankan saat `?classic=1`).

- [ ] **Step 3: Ubah controller — hapus cabang `?classic`**

Di `app/Http/Controllers/DokumenController.php`, ganti blok baris 185-187:

```php
        if (! $request->boolean('classic')) {
            session()->forget(['operator_sort_column', 'operator_sort_order']);
        }
```

menjadi:

```php
        // Tabel Tabulator tak pernah mengirim sort/order. Sesi sort lama (peninggalan
        // tabel classic yang sudah dihapus 2026-07-23) selalu dibersihkan agar tak
        // mengunci urutan tabel baru. Harus jalan SEBELUM buildOperatorQuery().
        session()->forget(['operator_sort_column', 'operator_sort_order']);
```

Lalu ganti blok baris 321-323:

```php
        $useClassic = $request->boolean('classic');
        $view = $useClassic ? 'operator.dokumens.daftarDokumen' : 'operator.dokumens.daftarDokumenTabulator';
        return view($view, $data);
```

menjadi:

```php
        // Tabel classic dihapus (2026-07-23) — operator selalu dilayani view Tabulator.
        // Flag ?classic tidak lagi berpengaruh (query param sisa dibiarkan no-op).
        return view('operator.dokumens.daftarDokumenTabulator', $data);
```

- [ ] **Step 4: Jalankan test → pastikan LULUS**

Run: `php artisan test --filter=OperatorTabulatorViewTest`
Expected: PASS (semua metode di file ini).

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/DokumenController.php
git add tests/Feature/OperatorTabulatorViewTest.php
git commit -m "refactor(operator): index selalu Tabulator, flag ?classic dimatikan"
```

---

### Task 2: Lepas render `_tableRowsAjax` dari `inlineCreate()`

**Files:**
- Modify: `app/Http/Controllers/DokumenController.php:366-385` (blok resolusi kolom + render `$html` + key `html` pada JSON)
- Test: `tests/Feature/InlineCreateDokumenTest.php` dan `tests/Feature/OperatorInlineCreateRowTest.php`

**Interfaces:**
- Consumes: —
- Produces: `POST documents.inline-create` membalas JSON `{ success, id, row }` — TANPA key `html`. `row` = `App\Support\OperatorDocumentRow::fromDokumen(...)` (tak berubah).

- [ ] **Step 1: Sesuaikan test inline-create ke balasan tanpa `html`**

Di `tests/Feature/InlineCreateDokumenTest.php`, pada `test_operator_dapat_membuat_baris_via_nomor_agenda()` ganti baris 28-30:

```php
        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['success', 'id', 'html']);
```

menjadi (tambah `assertJsonMissingPath('html')` agar test benar-benar GAGAL sebelum controller diubah, LULUS sesudahnya — red-green jujur):

```php
        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['success', 'id', 'row'])
            ->assertJsonMissingPath('html');
```

Hapus seluruh metode `test_html_respon_memuat_data_id()` (baris 45-53) — tujuannya khusus payload `html` yang kini tiada; identitas baris baru sudah dicakup `OperatorInlineCreateRowTest::test_inline_create_balas_objek_row`.

Di `tests/Feature/OperatorInlineCreateRowTest.php`, ganti docblock kelas (baris 10-14):

```php
/**
 * Menguji endpoint `POST /documents/inline-create` (name `documents.inline-create`).
 * Setelah tabel classic dihapus (2026-07-23) balasan hanya menyertakan objek `row`
 * (OperatorDocumentRow) untuk konsumsi Tabulator — key `html` sudah tidak ada.
 */
```

Hapus seluruh metode `test_inline_create_tetap_balas_html()` (baris 39-49) — perilaku "tetap balas html" kini sengaja tidak berlaku.

- [ ] **Step 2: Jalankan test → pastikan GAGAL**

Run (berurutan — PHPUnit hanya memakai `--filter` terakhir bila ditumpuk):
```bash
php artisan test --filter=InlineCreateDokumenTest
php artisan test --filter=OperatorInlineCreateRowTest
```
Expected: FAIL — `test_operator_dapat_membuat_baris_via_nomor_agenda` gagal pada `assertJsonMissingPath('html')` karena controller MASIH mengirim key `html`. Sisa metode (mis. `test_inline_create_balas_objek_row`) tetap hijau.

- [ ] **Step 3: Ubah `inlineCreate()` — buang resolusi kolom + render html**

Di `app/Http/Controllers/DokumenController.php`, hapus blok baris 366-376:

```php
        // Resolusi kolom sama seperti index()
        $availableColumns = $this->operatorDocumentColumns();
        $defaultColumns   = $this->defaultOperatorDocumentColumns($availableColumns);
        $selectedColumns  = session('dokumens_table_columns', $defaultColumns);
        $selectedColumns  = array_values(array_filter($selectedColumns, fn ($c) => isset($availableColumns[$c])));

        $html = view('operator.dokumens._tableRowsAjax', [
            'dokumens'         => collect([$dokumen]),
            'selectedColumns'  => $selectedColumns,
            'availableColumns' => $availableColumns,
        ])->render();
```

Lalu ganti blok response (baris 378-385):

```php
        return response()->json([
            'success' => true,
            'id'      => $dokumen->id,
            'html'    => $html,
            // Objek baris JSON untuk Tabulator (konsumen tunggal ke depan);
            // `html` dipertahankan selama view lama masih dipakai (fase fallback).
            'row'     => \App\Support\OperatorDocumentRow::fromDokumen($dokumen, $this->buildHandlerOptions(), auth()->user()?->role),
        ]);
```

menjadi:

```php
        return response()->json([
            'success' => true,
            'id'      => $dokumen->id,
            // Objek baris JSON untuk Tabulator — satu-satunya konsumen setelah tabel
            // classic dihapus (2026-07-23). Partial _tableRowsAjax tak lagi dirender.
            'row'     => \App\Support\OperatorDocumentRow::fromDokumen($dokumen, $this->buildHandlerOptions(), auth()->user()?->role),
        ]);
```

- [ ] **Step 4: Jalankan test → pastikan LULUS**

Run: `php artisan test --filter=InlineCreateDokumenTest` lalu `php artisan test --filter=OperatorInlineCreateRowTest`
Expected: PASS di kedua file.

- [ ] **Step 5: Commit**

```bash
git add app/Http/Controllers/DokumenController.php
git add tests/Feature/InlineCreateDokumenTest.php
git add tests/Feature/OperatorInlineCreateRowTest.php
git commit -m "refactor(operator): inline-create berhenti merender _tableRowsAjax"
```

---

### Task 3: Hapus berkas view classic + cabang `virtual_chunk`

**Files:**
- Delete: `resources/views/operator/dokumens/daftarDokumen.blade.php`
- Delete: `resources/views/operator/dokumens/_chunk.blade.php`
- Delete: `resources/views/operator/dokumens/_tableRowsAjax.blade.php`
- Modify: `app/Http/Controllers/DokumenController.php:246-253` (hapus cabang `virtual_chunk`)
- Delete: `tests/Feature/VirtualChunkDokumenTest.php`
- Modify (komentar): `tests/Unit/OperatorDocumentRowTest.php:16`

**Interfaces:**
- Consumes: hasil Task 2 (tak ada lagi yang merender `_tableRowsAjax`).
- Produces: tak ada berkas classic tersisa; `?virtual_chunk=1` tak lagi punya cabang khusus.

- [ ] **Step 1: Hapus cabang `virtual_chunk` di controller**

Di `app/Http/Controllers/DokumenController.php`, hapus blok baris 246-253:

```php
        // Virtual scroll: balas hanya potongan baris tabel (ringan) tanpa layout & partial berat
        if ($request->boolean('virtual_chunk')) {
            return view('operator.dokumens._chunk', [
                'dokumens'         => $dokumens,
                'selectedColumns'  => $selectedColumns,
                'availableColumns' => $availableColumns,
            ]);
        }
```

- [ ] **Step 2: Hapus test yang menguji cabang itu**

Hapus berkas `tests/Feature/VirtualChunkDokumenTest.php` seluruhnya.

```bash
git rm tests/Feature/VirtualChunkDokumenTest.php
```

- [ ] **Step 3: Hapus ketiga berkas view classic**

```bash
git rm resources/views/operator/dokumens/daftarDokumen.blade.php
git rm resources/views/operator/dokumens/_chunk.blade.php
git rm resources/views/operator/dokumens/_tableRowsAjax.blade.php
```

- [ ] **Step 4: Perbarui komentar yatim di test unit**

Di `tests/Unit/OperatorDocumentRowTest.php` baris 16, ubah komentar yang menyebut `_tableRowsAjax.blade.php` sebagai asal logika menjadi menyebut bahwa view itu sudah dihapus (sesuaikan kalimatnya agar tidak menunjuk berkas yang tak ada). Contoh:

```php
    // Logika baris ini dulu diekstrak dari _tableRowsAjax.blade.php (view classic,
    // dihapus 2026-07-23). Kini OperatorDocumentRow adalah sumber tunggalnya.
```

- [ ] **Step 5: Verifikasi grep bersih**

Run:
```bash
grep -rn "daftarDokumen\.blade\|_chunk\|_tableRowsAjax\|virtual_chunk" resources/views/operator routes/ app/Http/Controllers/DokumenController.php tests/
```
Expected: KOSONG untuk `operator/dokumens/daftarDokumen`, `operator/dokumens/_chunk`, `operator/dokumens/_tableRowsAjax`, dan `virtual_chunk`. (Rujukan `_chunk`/`_tableRowsAjax` pada folder role lain — team_verifikasi/perpajakan/akutansi — SAH dan harus tetap ada; pastikan yang muncul hanya itu, bukan path `operator/`.)

- [ ] **Step 6: Jalankan seluruh suite → pastikan LULUS**

Run: `php artisan test`
Expected: PASS semua (jumlah test berkurang karena `VirtualChunkDokumenTest` dihapus).

- [ ] **Step 7: Commit**

```bash
git add app/Http/Controllers/DokumenController.php
git add tests/Unit/OperatorDocumentRowTest.php
git commit -m "refactor(operator): hapus view classic (daftarDokumen, _chunk, _tableRowsAjax) + cabang virtual_chunk"
```

> Catatan: `git rm` pada Step 2-3 sudah men-stage penghapusan; Step 7 menambahkan perubahan controller + komentar test lalu commit semuanya sebagai satu perubahan logis (pembuangan render classic).

---

### Task 4: Hapus fitur Detail yatim (`getDocumentDetail` + route `documents.detail`)

**Files:**
- Modify: `routes/web.php:322` (hapus route)
- Modify: `app/Http/Controllers/DokumenController.php:542-606` (hapus method `getDocumentDetail`)

**Interfaces:**
- Consumes: hasil Task 3 (view classic yang memanggil `/detail` sudah tiada).
- Produces: route `documents.detail` & method `getDocumentDetail()` tidak ada.

- [ ] **Step 1: Verifikasi tak ada pemanggil tersisa**

Run:
```bash
grep -rn "documents\.detail\|/detail'\|getDocumentDetail" resources/ public/js routes/web.php app/Http/Controllers/DokumenController.php
```
Expected: KOSONG (tak ada pemanggil frontend; method `getDocumentDetail` role lain ada di controller BERBEDA — TeamVerifikasi/Pembayaran/Akutansi/Perpajakan/Bagian — dan TIDAK boleh muncul di pencarian yang dibatasi `DokumenController.php` ini).

- [ ] **Step 2: Hapus route**

Di `routes/web.php`, hapus baris 322:

```php
    Route::get('/{dokumen}/detail', [DokumenController::class, 'getDocumentDetail'])->name('detail');
```

- [ ] **Step 3: Hapus method `getDocumentDetail()`**

Di `app/Http/Controllers/DokumenController.php`, hapus docblock + method utuh baris 542-606 (dari `/**` di atas `public function getDocumentDetail(Dokumen $dokumen)` sampai `}` penutup method sebelum docblock `getDocumentProgressForOperator`).

- [ ] **Step 4: Jalankan seluruh suite → pastikan LULUS**

Run: `php artisan test`
Expected: PASS semua (tak ada test yang merujuk `getDocumentDetail`/`documents.detail`).

- [ ] **Step 5: Commit**

```bash
git add routes/web.php
git add app/Http/Controllers/DokumenController.php
git commit -m "refactor(operator): hapus route & method getDocumentDetail yatim"
```

---

### Task 5: Hapus jalur Kirim lama (`sendToTeamVerifikasi` + route `documents.send-to-verifikasi`)

**Files:**
- Modify: `routes/web.php:325` (hapus route)
- Modify: `app/Http/Controllers/DokumenController.php:1231-1329` (hapus method `sendToTeamVerifikasi`)

**Interfaces:**
- Consumes: hasil Task 3 (tombol Kirim di view classic sudah tiada). Forward operator→verifikasi kini via `DocumentHandlerController::update` (dropdown Pengurus Dokumen) — TIDAK disentuh.
- Produces: route `documents.send-to-verifikasi` & method `sendToTeamVerifikasi()` tidak ada.

- [ ] **Step 1: Verifikasi tak ada pemanggil tersisa**

Run:
```bash
grep -rn "send-to-verifikasi\|sendToTeamVerifikasi" resources/ public/js routes/web.php app/Http/Controllers/DokumenController.php
```
Expected: KOSONG. (Method `sendToInbox` di model & `moveDirectlyToTeamVerifikasi` di `DocumentHandlerController` adalah jalur berbeda — TIDAK boleh ikut terhapus dan tidak muncul di pencarian ini.)

- [ ] **Step 2: Hapus route**

Di `routes/web.php`, hapus baris 325:

```php
    Route::post('/{dokumen}/send-to-verifikasi', [DokumenController::class, 'sendToTeamVerifikasi'])->name('send-to-verifikasi');
```

- [ ] **Step 3: Hapus method `sendToTeamVerifikasi()`**

Di `app/Http/Controllers/DokumenController.php`, hapus docblock + method utuh baris 1231-1329 (dari `/**` di atas `public function sendToTeamVerifikasi(Dokumen $dokumen)` sampai `}` penutup method, tepat sebelum docblock/method `approveDocument`).

> HATI-HATI: JANGAN menyentuh method `approveDocument()` yang mengikutinya — itu di luar lingkup (route `documents.approve` sengaja dibiarkan, lihat Global Constraints).

- [ ] **Step 4: Jalankan seluruh suite → pastikan LULUS**

Run: `php artisan test`
Expected: PASS semua.

- [ ] **Step 5: Commit**

```bash
git add routes/web.php
git add app/Http/Controllers/DokumenController.php
git commit -m "refactor(operator): hapus jalur Kirim lama (send-to-verifikasi + sendToTeamVerifikasi)"
```

---

### Task 6: Sapuan verifikasi akhir + serah-terima QA

**Files:** — (tidak ada perubahan kode; gate akhir)

**Interfaces:**
- Consumes: hasil Task 1-5.
- Produces: bukti verifikasi untuk laporan ke user.

- [ ] **Step 1: Sanity JS (harus tetap valid meski tak diubah)**

Run: `node --check public/js/operator-tabulator.js`
Expected: exit 0, tanpa output error.

- [ ] **Step 2: Suite penuh hijau**

Run: `php artisan test`
Expected: PASS semua; catat jumlah `Tests: N passed`.

- [ ] **Step 3: Sapuan grep menyeluruh — hanya rujukan sah tersisa**

Run:
```bash
grep -rn "daftarDokumen\.blade\|operator/dokumens/_chunk\|operator/dokumens/_tableRowsAjax\|virtual_chunk\|documents\.detail\|getDocumentDetail\|send-to-verifikasi\|sendToTeamVerifikasi\|'classic'\|\"classic\"" resources/ routes/ app/Http/Controllers/DokumenController.php tests/ public/js
```
Expected: hasil hanya berupa rujukan SAH — dokumentasi/komentar spec, atau nama berkas `_chunk`/`_tableRowsAjax` milik folder role lain (team_verifikasi/perpajakan/akutansi). Tidak boleh ada rujukan hidup ke berkas/route/method operator yang telah dihapus. Bila ada yang tak terduga, hentikan dan periksa.

- [ ] **Step 4: Serahkan QA visual ke user (WAJIB sebelum deploy)**

Laporkan ke user untuk QA di browser (agent tak punya sesi login):
1. Buka `/documents` → tabel Tabulator tampil normal.
2. `/documents?classic=1` → TETAP menyajikan Tabulator (bukan tabel lama, bukan error).
3. Ubah **Pengurus Dokumen → Team Verifikasi** pada satu baris (Bagian terisi) → dokumen berpindah, status operator jadi "terkirim".
4. **Tambah Baris** (inline-create) → baris baru muncul di tabel.
5. **Hapus** baris via tombol toolbar → dokumen terhapus.
6. Inline-edit satu sel → tersimpan setelah reload.

- [ ] **Step 5: Deploy SETELAH user mengonfirmasi QA lolos**

```bash
git push origin codinggemini
```
Lalu di server:
```bash
git pull
php artisan route:clear && php artisan view:clear && php artisan config:clear
```

> Clear cache tidak boleh dilewat — Blade/route ter-cache membuat perubahan tampak tak berefek.
