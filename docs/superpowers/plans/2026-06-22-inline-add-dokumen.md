# Tambah Dokumen Inline di Daftar Dokumen (Operator) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Operator dapat menambah dokumen langsung dari halaman Daftar Dokumen (`/documents`) lewat tombol "+ Tambah Baris", tanpa membuka halaman form terpisah.

**Architecture:** Pendekatan "Opsi A" — baris baru ditahan di browser sebagai input biasa; saat Nomor Agenda terisi, satu endpoint baru `inline-create` membuat record draft dan mengembalikan HTML baris (di-render dari partial `_tableRowsAjax` yang sudah ada). Field lain yang sudah diketik di-flush memakai endpoint `inline-update` yang SUDAH ada. Tidak menyentuh `store()` / `StoreDokumenRequest` (form lama tetap utuh).

**Tech Stack:** Laravel 12 (PHP 8.3), Blade, JavaScript vanilla, PHPUnit (sqlite :memory:).

## Global Constraints

- Bahasa UI & komentar domain: **Bahasa Indonesia**; identifier kode: English.
- Commit **per-file** (JANGAN `git add .` / `git add -A`); pesan commit Bahasa Indonesia.
- Satu commit = satu perubahan logis.
- Tabel operator adalah HTML `<table>` server-rendered (`<tbody id="dokumenTableBody">`), bukan Tabulator. Inline-edit via `.ie-cell` + global click listener.
- Endpoint `inline-update` (PATCH `/documents/{dokumen}/inline-update`) sudah ada dan menangani parsing field (nilai_rupiah, tanggal, dibayar_kepada pisah koma, sanitasi URL). REUSE, jangan duplikasi.
- Default record baru (samakan dengan `store()`): `status='draft'`, `created_by='operator'`, `current_handler='operator'`, `tanggal_masuk=now()`, `bulan` (nama bulan Indonesia) & `tahun` dari `now()`.
- Field pemicu pembuatan baris: **`nomor_agenda` saja** (`required|string|unique:dokumens,nomor_agenda`).

## File Structure

- **Modify** `routes/web.php` — tambah route `POST /documents/inline-create` di grup `role:admin,operator` (sekitar baris 374, sesudah `all-data`, sebelum blok `{dokumen}`).
- **Modify** `app/Http/Controllers/DokumenController.php` — tambah method `inlineCreate(Request $request)` (pola mengikuti `ajaxRows()` untuk render partil + `store()` untuk default field).
- **Create** `tests/Feature/InlineCreateDokumenTest.php` — feature test endpoint.
- **Modify** `resources/views/operator/dokumens/daftarDokumen.blade.php` — tombol "+ Tambah Baris" (toolbar ~baris 2620), lalu blok `<style>` + `<script>` modul inline-add + `window.inlineAddColumns` (sesudah penutup container tabel ~baris 2704).

Partial `_tableRowsAjax.blade.php` TIDAK diubah (dipakai apa adanya untuk render baris baru).

---

## Task 1: Backend — endpoint `inline-create` + tests

**Files:**
- Modify: `routes/web.php` (sekitar baris 374)
- Modify: `app/Http/Controllers/DokumenController.php` (tambah method baru; gunakan helper yang sudah ada: `operatorDocumentColumns()`, `defaultOperatorDocumentColumns()`)
- Test: `tests/Feature/InlineCreateDokumenTest.php`

**Interfaces:**
- Produces (route name): `documents.inline-create` → `POST /documents/inline-create`.
- Produces (controller): `DokumenController@inlineCreate(\Illuminate\Http\Request $request): \Illuminate\Http\JsonResponse`.
  - Input JSON: `{ "nomor_agenda": string }`.
  - Sukses (200): `{ "success": true, "id": int, "html": string }` — `html` berisi `<tr class="main-row" data-id="…">…</tr>` + `<tr class="detail-row">`.
  - Gagal validasi (422): format validasi Laravel `{ "message": …, "errors": { "nomor_agenda": [ … ] } }`.
  - Non-operator: redirect ke `/login` (middleware `role:admin,operator`).

- [ ] **Step 1: Tulis feature test yang gagal**

Create `tests/Feature/InlineCreateDokumenTest.php`:

```php
<?php

namespace Tests\Feature;

use App\Models\Dokumen;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Menguji endpoint inline-create: operator membuat baris dokumen draft
 * langsung dari daftar dokumen hanya dengan Nomor Agenda.
 */
class InlineCreateDokumenTest extends TestCase
{
    use RefreshDatabase;

    private function operator(): User
    {
        return User::factory()->create(['role' => 'operator']);
    }

    public function test_operator_dapat_membuat_baris_via_nomor_agenda(): void
    {
        $response = $this->actingAs($this->operator())
            ->postJson('/documents/inline-create', ['nomor_agenda' => 'AG-001']);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['success', 'id', 'html']);

        $this->assertDatabaseHas('dokumens', [
            'nomor_agenda'    => 'AG-001',
            'status'          => 'draft',
            'created_by'      => 'operator',
            'current_handler' => 'operator',
        ]);

        $dokumen = Dokumen::where('nomor_agenda', 'AG-001')->first();
        $this->assertNotNull($dokumen->tanggal_masuk);
        $this->assertNotEmpty($dokumen->bulan);
        $this->assertSame((string) now()->year, (string) $dokumen->tahun);
    }

    public function test_html_respon_memuat_data_id(): void
    {
        $response = $this->actingAs($this->operator())
            ->postJson('/documents/inline-create', ['nomor_agenda' => 'AG-HTML']);

        $response->assertStatus(200);
        $id = $response->json('id');
        $this->assertStringContainsString('data-id="' . $id . '"', $response->json('html'));
    }

    public function test_nomor_agenda_duplikat_ditolak(): void
    {
        Dokumen::create(['nomor_agenda' => 'AG-DUP', 'status' => 'draft']);

        $response = $this->actingAs($this->operator())
            ->postJson('/documents/inline-create', ['nomor_agenda' => 'AG-DUP']);

        $response->assertStatus(422)->assertJsonValidationErrors('nomor_agenda');
    }

    public function test_nomor_agenda_kosong_ditolak(): void
    {
        $response = $this->actingAs($this->operator())
            ->postJson('/documents/inline-create', ['nomor_agenda' => '']);

        $response->assertStatus(422)->assertJsonValidationErrors('nomor_agenda');
    }

    public function test_non_operator_diblokir(): void
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $response = $this->actingAs($owner)
            ->post('/documents/inline-create', ['nomor_agenda' => 'AG-X']);

        $response->assertRedirect('/login');
    }
}
```

- [ ] **Step 2: Jalankan test, pastikan GAGAL**

Run: `php artisan test --filter=InlineCreateDokumenTest`
Expected: FAIL — route `/documents/inline-create` belum ada (404/405 atau method tidak ditemukan).

- [ ] **Step 3: Tambah route**

Di `routes/web.php`, sesudah baris `Route::get('/all-data', ...)->name('all-data');` (baris 374) dan SEBELUM komentar `// Routes with {dokumen} parameter`, sisipkan:

```php
    // Inline create — tambah baris dokumen langsung di tabel daftar dokumen
    Route::post('/inline-create', [DokumenController::class, 'inlineCreate'])->name('inline-create');
```

- [ ] **Step 4: Tambah method `inlineCreate` di controller**

Di `app/Http/Controllers/DokumenController.php`, tambahkan method baru (mis. tepat sesudah method `ajaxRows()` agar berdekatan dengan kode render partial yang serupa). `Carbon`, `Dokumen`, dan `ActivityLogHelper` sudah di-import di file ini (dipakai `store()`/`ajaxRows()`).

```php
    /**
     * Membuat satu baris dokumen draft langsung dari daftar dokumen (inline add).
     * Hanya butuh nomor_agenda; field lain di-flush via inline-update dari sisi klien.
     */
    public function inlineCreate(Request $request)
    {
        $validated = $request->validate([
            'nomor_agenda' => 'required|string|unique:dokumens,nomor_agenda',
        ], [
            'nomor_agenda.required' => 'Nomor agenda harus diisi.',
            'nomor_agenda.unique'   => 'Nomor agenda sudah digunakan. Silakan gunakan nomor lain.',
        ]);

        $now = Carbon::now();
        $bulanIndonesia = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $dokumen = Dokumen::create([
            'nomor_agenda'    => $validated['nomor_agenda'],
            'bulan'           => $bulanIndonesia[$now->month],
            'tahun'           => $now->year,
            'tanggal_masuk'   => $now,
            'status'          => 'draft',
            'created_by'      => 'operator',
            'current_handler' => 'operator',
        ]);

        try {
            ActivityLogHelper::logCreated($dokumen);
        } catch (\Exception $logException) {
            \Log::warning('Gagal mencatat activity log inline-create: ' . $logException->getMessage());
        }

        // Eager-load relasi yang dipakai partial agar markup baris identik
        $dokumen->load(['roleStatuses', 'dibayarKepadas', 'dokumenPos']);

        // Resolusi kolom sama seperti ajaxRows()
        $availableColumns = $this->operatorDocumentColumns();
        $defaultColumns   = $this->defaultOperatorDocumentColumns($availableColumns);
        $selectedColumns  = session('dokumens_table_columns', $defaultColumns);
        $selectedColumns  = array_values(array_filter($selectedColumns, fn ($c) => isset($availableColumns[$c])));

        $html = view('operator.dokumens._tableRowsAjax', [
            'dokumens'         => collect([$dokumen]),
            'selectedColumns'  => $selectedColumns,
            'availableColumns' => $availableColumns,
        ])->render();

        return response()->json([
            'success' => true,
            'id'      => $dokumen->id,
            'html'    => $html,
        ]);
    }
```

- [ ] **Step 5: Jalankan test, pastikan LULUS**

Run: `php artisan test --filter=InlineCreateDokumenTest`
Expected: PASS (5 test hijau).

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/DokumenController.php
git add routes/web.php
git add tests/Feature/InlineCreateDokumenTest.php
git commit -m "feat(dokumen): endpoint inline-create untuk tambah baris di daftar dokumen"
```

---

## Task 2: Frontend — tombol "+ Tambah Baris" + modul inline-add

**Files:**
- Modify: `resources/views/operator/dokumens/daftarDokumen.blade.php`

**Interfaces:**
- Consumes (route): `documents.inline-create` (POST) dan `documents.inline-update` (PATCH, sudah ada).
- Consumes (DOM): `#dokumenTableBody` (tbody tabel), variabel Blade `$operatorTableColumns` (urutan kolom yang dirender, didefinisikan ~baris 2650).
- Consumes (controller JSON): `inline-create` → `{ success, id, html }`; `inline-update` → `{ success, display_value, ... }`.
- Produces (DOM): tombol `#btnTambahBarisInline`, baris klien `<tr class="na-row">` dengan input `.na-input[data-field]`, global `window.inlineAddColumns`.

- [ ] **Step 1: Tambah tombol "+ Tambah Baris" di toolbar**

Di `resources/views/operator/dokumens/daftarDokumen.blade.php`, tepat SESUDAH tombol "Kustomisasi Kolom Tabel" (baris 2617–2620) dan SEBELUM `</form>` (baris 2621), sisipkan:

```blade
      <button type="button" class="btn-refresh" id="btnTambahBarisInline" onclick="tambahBarisInline()"
        style="background:linear-gradient(135deg,#0f4c3a,#16a34a);color:#fff;">
        <i class="fa-solid fa-plus me-2"></i>Tambah Baris
      </button>
```

- [ ] **Step 2: Tambah blok style + script + data kolom sesudah tabel**

Di file yang sama, tepat SESUDAH penutup container tabel (baris 2704, yaitu `</div>` penutup `#documentTableContainer`), sisipkan blok berikut. (`$operatorTableColumns` masih dalam scope Blade di titik ini.)

```blade
  {{-- ====== Inline Add: tambah baris dokumen langsung di tabel ====== --}}
  <style>
    tr.na-row > td { padding: 6px 8px; background: #f0fdf4; }
    .na-input {
      width: 100%; box-sizing: border-box; border: 1px solid #bbf7d0;
      border-radius: 6px; padding: 6px 8px; font-size: 13px; background: #fff;
    }
    .na-input:focus { outline: none; border-color: #16a34a; box-shadow: 0 0 0 2px rgba(22,163,74,.15); }
    .na-input.na-error { border-color: #dc3545; box-shadow: 0 0 0 2px rgba(220,53,69,.15); }
    textarea.na-input { resize: vertical; min-height: 34px; }
  </style>

  <script>
    window.inlineAddColumns = @json(array_values($operatorTableColumns));
  </script>

  <script>
    (function () {
      const COLUMNS = window.inlineAddColumns || [];
      const NON_EDITABLE = ['tanggal_masuk', 'status', 'nomor_mirror', 'keterangan'];
      const DATE_FIELDS = [
        'tanggal_spp', 'tanggal_berita_acara', 'tanggal_spk', 'tanggal_berakhir_spk',
        'tanggal_faktur', 'tanggal_paraf', 'tanggal_miro', 'tanggal_selesai_verifikasi_pajak',
      ];
      const TEXTAREA_FIELDS = ['uraian_spp'];

      function csrf() {
        return document.querySelector('meta[name="csrf-token"]').getAttribute('content') || '';
      }

      function tbody() {
        return document.getElementById('dokumenTableBody');
      }

      function naToast(message, isError) {
        const existing = document.getElementById('na-toast');
        if (existing) existing.remove();
        const t = document.createElement('div');
        t.id = 'na-toast';
        t.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);'
          + 'background:' + (isError ? '#dc3545' : '#16a34a') + ';color:#fff;padding:10px 20px;'
          + 'border-radius:8px;font-size:13px;z-index:99999;box-shadow:0 4px 12px rgba(0,0,0,.2);';
        t.textContent = message;
        document.body.appendChild(t);
        setTimeout(function () { t.remove(); }, 3500);
      }

      function makeInput(field) {
        let el;
        if (TEXTAREA_FIELDS.indexOf(field) !== -1) {
          el = document.createElement('textarea');
          el.rows = 1;
        } else {
          el = document.createElement('input');
          el.type = DATE_FIELDS.indexOf(field) !== -1 ? 'date' : 'text';
        }
        el.className = 'na-input';
        el.dataset.field = field;
        return el;
      }

      function buildRow() {
        const tr = document.createElement('tr');
        tr.className = 'na-row';

        const tdNo = document.createElement('td');
        tdNo.className = 'col-no';
        tdNo.innerHTML = '<i class="fa-solid fa-plus" style="color:#16a34a;"></i>';
        tr.appendChild(tdNo);

        COLUMNS.forEach(function (col) {
          const td = document.createElement('td');
          td.className = 'col-' + col;
          if (NON_EDITABLE.indexOf(col) === -1) {
            td.appendChild(makeInput(col));
          }
          tr.appendChild(td);
        });

        const tdHandler = document.createElement('td');
        tdHandler.className = 'col-handler';
        tr.appendChild(tdHandler);

        return tr;
      }

      function collectBuffer(tr, exceptField) {
        const buffer = {};
        tr.querySelectorAll('.na-input').forEach(function (inp) {
          const f = inp.dataset.field;
          if (f === exceptField) return;
          const v = (inp.value || '').trim();
          if (v !== '') buffer[f] = v;
        });
        return buffer;
      }

      // Flush field buffer satu per satu (berurutan) lewat inline-update yang sudah ada.
      function flushBuffer(id, buffer, rowEl) {
        return Object.keys(buffer).reduce(function (chain, field) {
          return chain.then(function () {
            return fetch('/documents/' + id + '/inline-update', {
              method: 'PATCH',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf(),
                'Accept': 'application/json',
              },
              body: JSON.stringify({ field: field, value: buffer[field] }),
            })
              .then(function (r) { return r.json(); })
              .then(function (data) {
                if (data && data.success) {
                  const cell = rowEl.querySelector('.ie-cell[data-field="' + field + '"]');
                  if (cell) cell.dataset.raw = (data.raw_value != null ? data.raw_value : buffer[field]);
                } else {
                  naToast('Sebagian data baris gagal disimpan: ' + field, true);
                }
              })
              .catch(function () { naToast('Koneksi gagal saat menyimpan: ' + field, true); });
          });
        }, Promise.resolve());
      }

      function born(tr, agendaInput) {
        const nomor = (agendaInput.value || '').trim();
        if (nomor === '') return;
        if (tr.dataset.creating === '1') return;
        tr.dataset.creating = '1';
        agendaInput.disabled = true;
        agendaInput.classList.remove('na-error');

        const buffer = collectBuffer(tr, 'nomor_agenda');

        fetch('/documents/inline-create', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf(),
            'Accept': 'application/json',
          },
          body: JSON.stringify({ nomor_agenda: nomor }),
        })
          .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
          .then(function (res) {
            if (!res.ok || !res.d || !res.d.success) {
              tr.dataset.creating = '';
              agendaInput.disabled = false;
              agendaInput.classList.add('na-error');
              let msg = 'Gagal membuat baris.';
              if (res.d && res.d.errors && res.d.errors.nomor_agenda) {
                msg = res.d.errors.nomor_agenda[0];
              } else if (res.d && res.d.message) {
                msg = res.d.message;
              }
              naToast(msg, true);
              agendaInput.focus();
              return;
            }

            // Ganti baris klien dengan baris asli dari server (sudah punya data-id, .ie-cell)
            const tmp = document.createElement('tbody');
            tmp.innerHTML = (res.d.html || '').trim();
            const newMain = tmp.querySelector('tr.main-row');
            const newDetail = tmp.querySelector('tr.detail-row');
            if (!newMain) { naToast('Respon server tidak valid.', true); return; }

            tr.replaceWith(newMain);
            if (newDetail) newMain.after(newDetail);

            // Flush field lain yang sudah diketik, lalu spawn baris kosong berikutnya
            flushBuffer(res.d.id, buffer, newMain).then(function () {
              tambahBarisInline();
            });
          })
          .catch(function () {
            tr.dataset.creating = '';
            agendaInput.disabled = false;
            agendaInput.classList.add('na-error');
            naToast('Koneksi gagal. Coba lagi.', true);
          });
      }

      function attachHandlers(tr) {
        const agenda = tr.querySelector('.na-input[data-field="nomor_agenda"]');
        if (!agenda) return;
        agenda.addEventListener('blur', function () { born(tr, agenda); });
        agenda.addEventListener('keydown', function (e) {
          if (e.key === 'Enter') { e.preventDefault(); born(tr, agenda); }
        });
      }

      // Dipanggil dari tombol toolbar (onclick) — jadikan global.
      window.tambahBarisInline = function () {
        const body = tbody();
        if (!body) return;
        if (COLUMNS.indexOf('nomor_agenda') === -1) {
          naToast('Tampilkan kolom "Nomor Agenda" lewat Kustomisasi Kolom untuk menambah baris.', true);
          return;
        }
        const tr = buildRow();
        body.insertBefore(tr, body.firstChild);
        attachHandlers(tr);
        const first = tr.querySelector('.na-input[data-field="nomor_agenda"]');
        if (first) first.focus();
      };
    })();
  </script>
```

- [ ] **Step 3: Verifikasi manual di browser (build assets jika perlu)**

Run (jika proyek memakai Vite/asset build): `npm run build` — jika tidak, lewati.
Lalu jalankan app & login sebagai operator, buka `/documents`:
- Klik **"+ Tambah Baris"** → baris hijau muncul di atas tabel, kursor di Nomor Agenda.
- Ketik Nomor Agenda → Enter → baris berubah jadi baris dokumen normal (draft), dan baris kosong baru otomatis muncul (input beruntun).
- Isi Uraian dulu baru Nomor Agenda → setelah baris lahir, nilai Uraian ikut tersimpan (cek via refresh halaman).
- Isi Nomor Agenda yang sudah ada → sel merah + toast "Nomor agenda sudah digunakan…", baris tetap bisa diperbaiki.
- Baris hasil inline-add bisa di-edit per sel (double/klik sel) dan dikirim ke verifikasi seperti draft biasa.

Expected: semua perilaku di atas berfungsi tanpa error di Console.

- [ ] **Step 4: Commit**

```bash
git add resources/views/operator/dokumens/daftarDokumen.blade.php
git commit -m "feat(dokumen): tombol & UI tambah baris inline di daftar dokumen operator"
```

---

## Catatan Integrasi & Deploy

- Sesuai memori proyek: setelah perubahan diterima, lakukan commit + push, lalu pull di server VPS dan jalankan clear cache (`php artisan route:clear`, `view:clear`, `config:clear`). Konfirmasi ke user sebelum menyentuh server.
- Form lama `/documents/create` sengaja DIBIARKAN; penghapusan menjadi fase berikutnya setelah inline-add terbukti stabil.

## Self-Review (penulis rencana)

- **Cakupan spec:** UX baris (Task 2 Step 2) ✓; endpoint + default field + validasi (Task 1) ✓; pemicu nomor_agenda (Task 1 validate + Task 2 `born`) ✓; buffer + flush via inline-update (Task 2 `collectBuffer`/`flushBuffer`) ✓; field multi-nilai sebagai teks koma (dibiarkan ke parsing inline-update yang sudah memecah koma) ✓; form lama tetap ada (tidak disentuh) ✓; edge case duplikat/kosong/batal/non-operator (Task 1 tests + Task 2 guard) ✓; testing (Task 1) ✓.
- **Placeholder scan:** tidak ada TBD/TODO; semua langkah berisi kode/command nyata.
- **Konsistensi tipe/nama:** `documents.inline-create`, `inlineCreate`, `#btnTambahBarisInline`, `#dokumenTableBody`, `window.inlineAddColumns`, `tambahBarisInline()`, `.na-row`/`.na-input` konsisten antara Task 1 & Task 2.
