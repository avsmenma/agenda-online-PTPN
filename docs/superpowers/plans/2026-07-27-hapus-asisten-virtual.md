# Hapus Total Fitur Asisten Virtual — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development to execute this plan task-by-task. Steps use checkbox (`- [ ]`) syntax.

**Goal:** Menghapus fitur "Asisten Virtual" (chat owner/pembayaran + evaluasi programmer) **tanpa menyisakan satu baris kode / satu file pun**, sesuai perintah eksplisit user. Kode/file/migrasi dihapus; 2 tabel DB di server di-**dump lalu DROP** (keputusan user 2026-07-27).

**Sumber kebenaran footprint:** inventaris terverifikasi (read-only) di scratchpad —
`asisten-virtual-inventory.md` (Bucket A = 17 file hapus utuh, Bucket B = 6 file kode scrub + CLAUDE.md, Bucket C = 17 false-positive JANGAN disentuh).

**Arsitektur:** murni penghapusan. Nol fitur baru. Kopling terverifikasi satu-arah (fitur → CashBank/Dokumen read-only); nol kode non-fitur bergantung pada file yang dihapus. Nol test, nol provider, nol binding, nol package composer terkait.

## Global Constraints

- **NOL SISA.** Acceptance akhir = grep-gate lintas `app/`, `routes/`, `resources/`, `config/`, `database/`, `.env.example`, `tests/` untuk pola fitur (`VirtualAssistant`, `virtual_assistant`, `virtual-assistant`, `asisten_virtual`, `asisten-virtual`, `assistant-evaluation`, `AssistantEvaluation`, `AssistantTest`) → **nol hit** (kecuali `docs/` historis & `CLAUDE.md` yang justru diperbarui di Task 2).
- **JANGAN sentuh Bucket C (false-positive):** Tabulator vendor (`virtual DOM`), `virtual-scroll-spacer`/`virtual-table-*` di partial tabel, "Virtual Account" perbankan di CashBank (`getSaldoPerVirtualAccount`), komentar "Virtual field" di 2 event Inbox, "peran virtual" di `User.php`, seluruh `docs/*.md` historis. Ini BUKAN fitur asisten. Grep kata telanjang "virtual"/"asisten" akan menabraknya — filter pakai pola fitur di atas, bukan kata mentah.
- **God-file `layouts/app.blade.php` (gerbang kritis §6):** perubahan HANYA menghapus 2 link menu + fungsi JS cleanup + rewrite 1 variabel turunan. Variabel `$isPaymentDashboardActive` (baris ~3399) DITURUNKAN dari `$isPaymentAssistantActive` (baris ~3398) — **rewrite, jangan hapus mentah**, atau layout error untuk SEMUA role pembayaran. Baca konteks penuh sebelum mengedit.
- **Blok JS logout (`layouts/app.blade.php`) & login (`auth/login.blade.php`):** fungsi/blok pembersih `localStorage` chat asisten dipanggil di listener logout/load. Hapus HANYA bagian asisten; JANGAN ikut menghapus listener logout / logika lain di sekitarnya. Baca dulu.
- **`git add` per-file** (JANGAN `git add .`/`-A`); pesan commit Bahasa Indonesia.
- **Suite hijau sebelum commit:** `php artisan test` harus tetap hijau (fitur tak punya test; penghapusan tak boleh memecah suite yang ada).
- **Baris bergeser:** semua nomor baris di inventaris adalah snapshot — lokasikan blok by content (grep identifier), bukan by nomor baris.
- **Nomor baris drift antar-scrub:** menghapus di satu file menggeser baris file itu; edit dari bawah ke atas ATAU re-grep setelah tiap edit.
- **Server DB (dump+drop) TIDAK dikerjakan subagent** — operasi produksi, dikerjakan controller saat deploy (lihat bagian Deploy).

---

### Task 1: Hapus semua kode fitur (Bucket A file + Bucket B scrub) — app tetap konsisten & suite hijau

**Files:**
- Delete (17, Bucket A): `app/Http/Controllers/OwnerVirtualAssistantController.php`, `app/Http/Controllers/ProgrammerAssistantEvaluationController.php`, `app/Services/VirtualAssistantService.php`, `app/Services/VirtualAssistantQueryService.php`, `app/Services/VirtualAssistantAiProvider.php`, `app/Models/VirtualAssistantInteraction.php`, `app/Models/VirtualAssistantTestCase.php`, `app/Console/Commands/AssistantTestCommand.php`, `config/asisten_virtual.php`, `resources/views/owner/asisten-virtual.blade.php`, `resources/views/programmer/assistant-evaluation.blade.php`, `database/migrations/2026_05_20_100000_create_virtual_assistant_interactions_table.php`, `database/migrations/2026_05_20_100100_create_virtual_assistant_test_cases_table.php`, `database/migrations/2026_05_20_100200_insert_virtual_assistant_regression_test_cases.php`, `database/migrations/2026_05_20_100300_insert_virtual_assistant_output_quality_test_cases.php`, `database/migrations/2026_05_22_100000_insert_virtual_assistant_exact_amount_test_cases.php`, `database/migrations/2026_05_25_090000_add_source_context_to_virtual_assistant_interactions_table.php`
- Scrub (6 code, Bucket B): `routes/web.php`, `resources/views/layouts/app.blade.php`, `resources/views/layouts/programmer.blade.php`, `resources/views/programmer/dashboard.blade.php`, `resources/views/auth/login.blade.php`, `.env.example`

- [ ] **Step 1: Hapus 17 file Bucket A** (per-file; gunakan `git rm <path>` untuk yang tracked). Verifikasi tiap path ada sebelum hapus.

- [ ] **Step 2: Scrub `routes/web.php`** — hapus baris 20 `use App\Http\Controllers\OwnerVirtualAssistantController;` + 10 definisi route (owner: `owner.asisten-virtual`(.chat/.feedback); pembayaran: `pembayaran.asisten-virtual`(.chat/.feedback); programmer group: `assistant-evaluation`(.export/.fixed/.test-cases.store)). Route programmer pakai FQCN inline `\App\Http\Controllers\ProgrammerAssistantEvaluationController::class` (tak ada `use` terpisah). Locate by controller name / route name, bukan nomor baris. JANGAN sentuh route lain, JANGAN ubah struktur/middleware grup programmer selain menghapus 4 baris route asisten di dalamnya.

- [ ] **Step 3: Scrub `resources/views/layouts/app.blade.php` (HATI-HATI, God-file)** — (a) hapus blok link sidebar owner "Asisten Virtual" (`@php $isAsistenVirtualActive = request()->routeIs('owner.asisten-virtual'); @endphp` + `<a href="{{ route('owner.asisten-virtual') }}" ...>...</a>` + komentar `{{-- ... Asisten Virtual --}}`); (b) hapus blok link sidebar pembayaran "Asisten Virtual" (`<a href="{{ route('pembayaran.asisten-virtual') }}" ...>...</a>`); (c) **rewrite** baris `$isPaymentAssistantActive = request()->routeIs('pembayaran.asisten-virtual*');` + baris turunannya `$isPaymentDashboardActive = !$isPaymentAssistantActive && ...` → hapus `$isPaymentAssistantActive` DAN ubah `$isPaymentDashboardActive` menjadi bentuk mandiri tanpa referensi variabel terhapus (mis. `$isPaymentDashboardActive = (request()->routeIs('dashboard.pembayaran') || request()->is('*dashboard/pembayaran*'));` — sesuaikan dengan kondisi asli yang ada di sana; BACA baris aslinya dulu); (d) hapus fungsi JS `clearVirtualAssistantChatStorage()` + 2 pemanggilnya di listener logout — hapus HANYA baris pemanggil + definisi fungsi, JANGAN hapus listener logout itu sendiri.

- [ ] **Step 4: Scrub `resources/views/layouts/programmer.blade.php`** — hapus blok link sidebar `<a href="{{ route('programmer.assistant-evaluation') }}" ...>...Evaluasi Asisten Virtual</a>`.

- [ ] **Step 5: Scrub `resources/views/programmer/dashboard.blade.php`** — hapus 1 entri array kartu menu `['route'=>'programmer.assistant-evaluation', ...'title'=>'Evaluasi Asisten Virtual', ...]`. Pastikan array PHP tetap valid (koma).

- [ ] **Step 6: Scrub `resources/views/auth/login.blade.php`** — hapus blok `<script>` pembersih `localStorage` chat asisten (filter `virtual_assistant_chat_`). BACA blok script sekitarnya dulu: jika script HANYA berisi try/catch cleanup asisten → hapus seluruh `<script>...</script>`; jika ada logika non-asisten menyusul → hapus hanya blok try/catch asisten.

- [ ] **Step 7: Scrub `.env.example`** — hapus 7 key `VIRTUAL_ASSISTANT_*` (PROVIDER/OPENAI_API_KEY/OPENAI_MODEL/GEMINI_API_KEY/GEMINI_MODEL/DEFAULT_LIMIT/MAX_MESSAGE_LENGTH). **Verifikasi dulu**: grep repo untuk `OPENAI_API_KEY`/`GEMINI_API_KEY` GENERIK — jika hanya `config/asisten_virtual.php` (dihapus) yang memakainya, key generik itu (bila ada di `.env.example`) juga boleh dihapus; jika ada pemakai lain, biarkan key generik. Hapus HANYA yang terbukti milik fitur.

- [ ] **Step 8: Grep-gate residu (WAJIB nol hit di kode)**
Gunakan Grep tool, pola fitur (bukan kata mentah "virtual"/"asisten"):
```
(VirtualAssistant|virtual_assistant|virtual-assistant|asisten_virtual|asisten-virtual|assistant-evaluation|AssistantEvaluation|AssistantTest|clearVirtualAssistantChatStorage|\$isPaymentAssistantActive|\$isAsistenVirtualActive|\$menuAsistenVirtual)
```
Scope: `app`, `routes`, `resources`, `config`, `database`, `.env.example`, `tests`. **Expected: NOL hit.** Bila ada hit tersisa (selain `docs/` & `CLAUDE.md`) → belum bersih, tuntaskan. Catat hasil grep di report.

- [ ] **Step 9: Cek Blade & PHP tak rusak**
`php artisan route:list 2>&1 | grep -i asisten` → nol hit (dan perintah tak error karena route menunjuk controller terhapus). Bila `route:list` error "class not found" → masih ada route yatim, perbaiki. Lalu `php artisan config:clear` (config `asisten_virtual` terhapus — pastikan tak ada `config('asisten_virtual...')` tersisa; grep sudah menutup ini).

- [ ] **Step 10: Suite hijau** — `php artisan test`. Expected hijau (mis. 245). Bila merah karena penghapusan → diagnosa (superpowers:systematic-debugging). Bila merah karena flaky tak terkait → catat jujur.

- [ ] **Step 11: Review mandiri** — pastikan: (a) 17 file benar-benar terhapus; (b) nol referensi route/controller/config yatim; (c) God-file `$isPaymentDashboardActive` di-rewrite benar (bukan undefined var); (d) listener logout & script login masih utuh selain bagian asisten; (e) tak ada Bucket C tersentuh.

- [ ] **Step 12: Commit** (staged per-file: 17 hapus + 6 scrub)
```
git commit -m "feat(cleanup): hapus total fitur asisten virtual (kode, view, route, migrasi, config)"
```

---

### Task 2: Sinkron CLAUDE.md — hapus asisten-virtual dari daftar KEEP

**Files:** Modify `CLAUDE.md` (§7 paragraf "Pembayaran ... Rollout 4" bagian **KEEP**, dan bila ada penyebutan lain).

- [ ] **Step 1:** Di §7, hapus `asisten-virtual (OwnerVirtualAssistantController)` dari daftar **KEEP** paragraf Pembayaran, dan tambahkan catatan singkat (Bahasa Indonesia, gaya sekitarnya, tanggal absolut 2026-07-27) bahwa fitur Asisten Virtual **dihapus total** (kode+view+route+config+migrasi; 2 tabel di-dump lalu DROP di server). Grep `CLAUDE.md` untuk penyebutan `asisten`/`assistant` lain dan bereskan agar doc tak menyesatkan. JANGAN ubah bagian lain.
- [ ] **Step 2: Commit**
```
git commit -m "docs: catat penghapusan total fitur asisten virtual di CLAUDE.md"
```

---

## Deploy + Server DB (dikerjakan controller, setelah kedua task & final review lolos)

Keputusan user: **dump dulu, lalu DROP** 2 tabel.

```bash
git push origin codinggemini
# di server (/var/www/agenda-online-PTPN):
git pull
# 1) Dump/backup 2 tabel ke file .sql (timestamped) SEBELUM drop:
mysqldump <db> virtual_assistant_interactions virtual_assistant_test_cases > /root/backup_asisten_virtual_<tanggal>.sql
# 2) DROP kedua tabel + bersihkan baris di tabel `migrations`:
#    DROP TABLE IF EXISTS virtual_assistant_interactions, virtual_assistant_test_cases;
#    DELETE FROM migrations WHERE migration LIKE '%virtual_assistant%';
# 3) Clear cache:
php artisan route:clear && php artisan view:clear && php artisan config:clear
```
Kredensial DB dibaca dari `.env` server (`DB_DATABASE`/`DB_USERNAME`/`DB_PASSWORD`). **Konfirmasi baris terhapus & backup ada sebelum lanjut.** Ini destruktif tapi ada cadangan `.sql`.

## Catatan pengujian (jujur)
- Fitur tak punya test → penghapusan diverifikasi via grep-gate (nol residu), `route:list`, `config:clear`, dan suite hijau (regresi).
- **QA visual = tanggung jawab user:** login owner/pembayaran/programmer → pastikan menu "Asisten Virtual"/"Evaluasi Asisten Virtual" HILANG, tak ada link mati, dashboard pembayaran/owner/programmer normal. Hard-refresh (Ctrl+F5) untuk layout ter-cache.
