# Desain — Satukan Modal Kustomisasi Kolom untuk 5 Role Keuangan

**Tanggal:** 2026-07-28
**Status:** disetujui user (per bagian), siap masuk penyusunan rencana
**Cakupan:** operator, akutansi, perpajakan, verifikasi, **pembayaran**

---

## 1. Tujuan

Kelima role keuangan memakai **satu** modal Kustomisasi Kolom, mengikuti versi
pembayaran yang lebih kaya (2 tab: **Kolom Tabel** + **Kolom Beku**). Hari ini:

| | Modal | Lokasi |
|---|---|---|
| operator, akutansi, perpajakan, verifikasi | 1 tab | `partials/_columnCustomizationModal.blade.php` + `public/js/column-customization.js` (bersama, sejak 2026-07-28) |
| pembayaran | 2 tab + Kolom Beku | inline di god-file `pembayaranNEW/dashboardPembayaran.blade.php` — CSS 2336–2744, markup 2747–2901, JS 2903–3206 (**~868 baris**) |

Target akhir user: seluruh role keuangan seragam — tabel, kustomisasi kolom, dan
seterusnya. Spec ini mengerjakan potongan **kustomisasi kolom**.

### Kenapa ini didahulukan ketimbang "satukan 4 view Tabulator jadi 1 view + config"

Penyatuan view **tidak menyentuh pembayaran** (view-nya dashboard god-file, bukan
`daftar*Tabulator`), jadi hasilnya "4 seragam + 1 beda" — persis masalah yang ingin
dihilangkan. Penyatuan modal mencakup kelima role sekaligus, dan menghapus satu
perbedaan besar antar-view sehingga penyatuan view nanti jadi lebih ringan.

---

## 2. Keputusan user

1. **Template Agenda DIHAPUS** — termasuk dari pembayaran. Modal benar-benar identik di 5 role.
2. **Rollout bertahap**: Deploy 1 = 4 role; Deploy 2 = pembayaran.
3. **Nomor Agenda terkunci beku** — kontrolnya ditampilkan non-aktif di tab Kolom Beku.

---

## 3. Arsitektur

Empat lapis. Tiga sudah ada; hanya lapis modal yang dikerjakan, plus satu kelas baru.

| Lapis | Berkas | Perubahan |
|---|---|---|
| Mesin tabel | `public/js/document-tabulator.js` | **Nol.** `buildColumns()` sudah membaca `cfg.frozen = {left,right}`; role tanpa itu dapat daftar kosong → perilaku lama utuh. |
| Tata letak beku | `App\Support\FrozenColumnLayout` | **Nol.** `normalize()` + `renderOrder()` sudah bersama & ada unit test. Tinggal dipakai 4 role lain. |
| Modal | `partials/_columnCustomizationModal.blade.php` + `public/js/column-customization.js` | **Di sini pekerjaannya** — tambah tab Kolom Beku. |
| Preferensi server | **BARU** `App\Support\ColumnCustomization` | Kelas bersama. |

### 3.1 Kelas `App\Support\ColumnCustomization`

Logika baca-simpan preferensi kolom + beku ~60 baris. Menyalinnya ke 4 controller =
salinan ke-5 = memperparah penyakit utama (CLAUDE.md §3).

**Kelas biasa di `App\Support`, BUKAN trait `Concerns\`.** Alasannya testabilitas:
§10 menuntut unit test, dan menguji trait memerlukan kelas inang bohongan. Logika ini
murni — request masuk, preferensi keluar — tanpa konteks controller selain user yang
bisa dioper. `App\Support` juga memang tempat logika murni bersama di project ini
(`FrozenColumnLayout`, `DocumentRow`, `DocumentExporter`, `Role`, `Asset`), dan
`FrozenColumnLayout` sudah membuktikan pola itu enak diuji. Trait `Concerns\` di
project ini dipakai untuk hal yang menghasilkan respons HTTP (`ExportsDocuments`) —
bukan kasus ini.

```php
$cc = ColumnCustomization::resolve($request, $user, [
    'available'     => $availableColumns,
    'default'       => $defaultColumns,
    'prefKey'       => 'akutansi',                        // kunci table_columns_preferences
    'sessionKey'    => 'akutansi_dokumens_table_columns',
    'frozenDefault' => ['left' => ['nomor_agenda'], 'right' => []],
    'pinnedLeft'    => ['nomor_agenda'],                  // §4.2 — selalu beku kiri
]);
// → ['selected' => [...], 'frozen' => ['left' => [...], 'right' => [...]], 'render' => [...]]
```

`$user` dioper eksplisit (bukan `Auth::user()` di dalam kelas) supaya unit test bisa
memberi user palsu tanpa menyalakan sesi.

Kelas ini **membungkus pola yang sudah ada, tidak mengubah aturannya**:

- Penyimpanan tetap **DB** (`users.table_columns_preferences`) untuk akutansi,
  perpajakan, verifikasi, pembayaran.
- Tetap **sesi** untuk operator (`session('dokumens_table_columns')`) — begitu
  kondisinya sekarang; menyeragamkannya bukan bagian permintaan ini.

**Di mana preferensi beku disimpan.** Berdampingan dengan preferensi kolom, memakai
akhiran `_frozen` — konvensi yang sudah dipakai pembayaran hari ini
(`table_columns_preferences['pembayaran_dashboard']` +
`['pembayaran_dashboard_frozen']`):

| Role | Kolom | Beku |
|---|---|---|
| akutansi / perpajakan / verifikasi / pembayaran | DB `table_columns_preferences[prefKey]` | DB `table_columns_preferences[prefKey . '_frozen']` |
| operator | sesi `dokumens_table_columns` | sesi `dokumens_table_columns_frozen` |

Konsekuensi yang disengaja: preferensi beku operator hilang saat sesi berakhir,
sama seperti preferensi kolomnya sekarang. Menyeragamkan ini ke DB ada di §12.

### 3.2 Default beku per role: `left: ['nomor_agenda']`

Bukan pilihan gaya. `buildColumns()` sudah mem-freeze `nomor_agenda` tanpa syarat
untuk semua role, jadi default ini membuat tampilan awal keempat role **identik
dengan hari ini**.

---

## 4. Kontrak data

Jembatan Blade→JS bertambah satu kunci:

```js
window.COLUMN_CUSTOMIZATION_CONFIG = {
  availableColumns: { ... },                        // sudah ada
  selected:         ['nomor_agenda', ...],          // sudah ada — urutan PILIHAN user
  frozen:           { left: ['nomor_agenda'], right: [] }   // BARU
};
```

Tanpa flag on/off — kelima role dapat 2 tab, itu justru tujuannya.

### 4.1 Dua urutan kolom yang TIDAK BOLEH tertukar

Tabulator menentukan sisi beku dari **posisi kolom dalam daftar**, bukan properti
kiri/kanan eksplisit. Karena itu ada dua urutan berbeda:

| Dipakai untuk | Isinya | Sumber |
|---|---|---|
| `DOCUMENT_TABULATOR_CONFIG.columns` | beku-kiri → bebas → beku-kanan | `FrozenColumnLayout::renderOrder()` |
| `COLUMN_CUSTOMIZATION_CONFIG.selected` | urutan pilihan asli user | `$selectedColumns` apa adanya |

Pembayaran sudah memisahkan keduanya (`$renderColumns` vs `$selectedColumns`).
Keempat role lain kini memakai satu array untuk dua-duanya — **tiap view wajib
diubah**. Gejala kalau tertukar: urutan kolom di modal ikut teracak setiap kali user
membekukan kolom.

### 4.2 Nomor Agenda terkunci

Mesin tabel membekukan `nomor_agenda` tanpa syarat. Agar urutan render sejalan
dengan hardcode itu, **`ColumnCustomization` wajib memaksa `nomor_agenda` selalu ada di
`frozen.left`** (sisipkan bila hilang) sebelum `normalize()`. Di tab Kolom Beku,
barisnya dirender **non-aktif** dengan keterangan "identitas baris selalu terlihat".

Ini sekaligus memperbaiki kejanggalan lama pembayaran: di sana user bisa menyetel
Nomor Agenda ke "Bebas", menyimpannya, dan kolomnya tetap beku — pengaturan yang
tidak pernah berefek.

---

## 5. Perilaku Simpan — bangun URL, jangan submit form

Dua jalur yang ada sekarang sama-sama bocor:

- **4 role**: submit `#filterForm` (GET). Filter toolbar terbawa lewat
  `appendActiveFilterInputs()`, tapi **parameter URL yang bukan field form hilang**.
- **Pembayaran**: bangun ulang dari `window.location.href`. `mode=rekapan_table` &
  `per_page` selamat, tapi **perubahan toolbar yang belum disubmit hilang**.

Memindahkan pembayaran mentah-mentah ke jalur pertama = **mode rekapan vendor lompat
balik ke normal setiap kali user menyimpan kolom.**

### 5.1 Kenapa form-submit ditinggalkan sama sekali

Bentuk DOM kedua kubu berbeda, dan itu membuat jalur form cacat untuk pembayaran:

| | `#filterForm` | toolbar |
|---|---|---|
| 4 role | form **kosong tersembunyi** (`class="d-none"`) | `<div class="tabulator-toolbar">` **di luar** form |
| pembayaran | **sama dengan** toolbar (`<form class="filter-section tabulator-toolbar">`) | field ada **di dalam** form |

`appendActiveFilterInputs()` melakukan `remove()` pada input bernama sama sebelum
menempelkan salinan tersembunyi. Untuk pembayaran, input yang dihapus itu adalah
**elemen toolbar yang asli** (karena berada di dalam form), sementara `<select>` tidak
ikut dihapus sehingga parameternya terkirim dobel. Nilainya kebetulan sama jadi tidak
fatal — tapi itu merusak DOM dan bergantung pada kebetulan.

### 5.2 Jalur bersama

```
url = new URL(location.href)          // param tak dikenal selamat: mode, per_page, page, sort
  ← timpa setiap kontrol .tabulator-toolbar
  ← timpa columns[], enable_customization, frozen_config=1, frozen_left[], frozen_right[]
location.href = url
```

Lebih sederhana, seragam untuk kedua bentuk DOM, **nol mutasi DOM**, nol parameter
dobel — dan mempertahankan sifat terbaik kedua jalur lama sekaligus.

Aturan rinci:

- **Nama larik dipakai apa adanya**, berkurung: `columns[]`, `frozen_left[]`,
  `frozen_right[]`. `URLSearchParams` dan `el.name` sama-sama memakai bentuk
  berkurung, jadi perbandingan nama konsisten tanpa normalisasi.
- **Nilai kosong menghapus param** (`url.searchParams.delete(name)`) — itulah
  semantik "bersihkan filter". Tanpa aturan ini, filter yang dikosongkan user akan
  hidup lagi dari URL sebelumnya.
- **Checkbox/radio tak tercentang** dilewati, seperti perilaku sekarang.
- **Nama ter-reservasi** bertambah dari 2 menjadi **5**: `columns[]`,
  `enable_customization`, `frozen_config`, `frozen_left[]`, `frozen_right[]`.
  Kontrol toolbar bernama sama tidak boleh menimpanya.

### 5.3 `#filterForm` TIDAK disentuh

Sempat terpikir menghapusnya dari 4 view karena tampak hanya dipakai modal. Grep
penuh (`resources` + `public` + `app` + `tests` + `config` + `routes`) membantah itu:
partial **global** `partials/document-workbench-ui.blade.php` memakainya di baris 424
dan 680 untuk menghitung badge "filter aktif". Menghapusnya = menyentuh partial global
(gerbang kritis CLAUDE.md §6) demi manfaat nol bagi tujuan spec ini. **Dibiarkan apa
adanya**; modal cukup berhenti memakainya.

### 5.4 Kenapa muat-ulang penuh, bukan `setColumns()` client-side

Definisi kolom, formatter, dan urutan render dihitung di server (DTO `DocumentRow`
per role + `FrozenColumnLayout::renderOrder()`). Menyusun ulang kolom di klien berarti
mereplikasi logika itu di JS — melahirkan duplikasi baru, persis penyakit yang sedang
diberantas. Muat-ulang penuh dipilih sadar, bukan karena inersia.

### 5.5 `frozen_config=1` wajib ikut

Tanpa penanda ini, "user melepas SEMUA kolom beku" tidak bisa dibedakan dari
"request tak membawa konfigurasi beku" — keduanya sama-sama tidak mengirim
`frozen_left`/`frozen_right`. Server lalu membekukan ulang dari preferensi lama, dan
**user tak pernah bisa mengosongkan kolom beku**. Bug ini sudah pernah diperbaiki di
pembayaran; penanda dibawa apa adanya.

---

## 6. Invarian yang dibawa utuh dari pembayaran

1. **`renderFrozenTab()` tidak boleh menugaskan ulang state.** Dulu fungsi ini
   memangkas `frozenLeftOrder`/`frozenRightOrder`, padahal hanya jalan saat tab Beku
   dibuka — akibatnya kondisi akhir yang sama memberi hasil berbeda tergantung tab
   itu pernah dibuka atau tidak. Penyaringan kolom tersembunyi **hanya** di
   `saveColumnCustomization()`.
2. **Server memvalidasi ulang tiap request.** `FrozenColumnLayout::normalize()`
   membuang kolom beku yang sudah disembunyikan user — klien tidak dipercaya sebagai
   satu-satunya penjaga.

---

## 7. Penanganan error

- 0 kolom tercentang → `alert`, simpan dibatalkan (perilaku lama, dipertahankan).
- Kolom beku melebihi ~50% lebar layar → peringatan teks di tab Beku, **tidak
  memblokir** (perilaku lama pembayaran). Konstanta lebar (`nomor_agenda: 210`,
  default `132`, kolom No `88`) ikut pindah ke JS bersama apa adanya.
- Kolom beku yang lalu disembunyikan → otomatis lepas beku, di klien saat simpan
  **dan** di server saat normalisasi.

---

## 8. Yang dihapus

| Item | Bukti |
|---|---|
| Tombol **Template Agenda** + `applyTemplateAgenda()` | Keputusan user. Grep: hanya dipakai `dashboardPembayaran.blade.php`. |
| `localStorage.setItem('pembayaran_columns', …)` | Grep: **hanya ditulis, tak pernah dibaca** di seluruh `resources/`, `public/`, `app/`. Kode mati. |
| Modal inline pembayaran (CSS 2336–2744, markup 2747–2901, JS 2903–3206) | Digantikan partial + JS bersama. Grep-gate dijalankan ulang sebelum eksekusi. |
| `appendActiveFilterInputs()` versi tempel-hidden-input | **Diganti**, bukan sekadar dihapus: perannya (membawa filter toolbar) pindah ke penimpaan `URLSearchParams` di §5.2. Tak ada pemakai lain — grep menunjukkan fungsi ini hanya dipanggil `saveColumnCustomization()`. |

---

## 9. Rollout

**Deploy 1 — 4 role; pembayaran belum disentuh**

1. Kelas `App\Support\ColumnCustomization` + unit test
2. Partial bersama: tab bar + panel Kolom Beku. **CSS wajib lewat `@push('styles')`**
   — bukan `<style>` inline di body; itu pernah jadi regresi flash-of-unstyled-modal
   saat ekstraksi 2026-07-28.
3. JS bersama: state beku, `switchColumnTab`, `getFrozenState`/`setFrozenState`,
   `renderFrozenTab`, `renderFrozenWarning`, **dan Simpan dialihkan dari
   `filterForm.submit()` ke pembangunan URL** (§5.2). Langkah ini mengubah jalur
   simpan keempat role yang sekarang sudah jalan — jadi test round-trip filter
   toolbar yang ada wajib tetap hijau sebagai jaring paritas.
4. Sambungkan 4 controller + 4 view (pemisahan `renderOrder` vs `selected`)

Tugas 2–4 **wajib satu deploy**. Kalau tab Beku tayang sebelum controller siap,
tab-nya tampil tapi simpanan beku diabaikan diam-diam — kegagalan senyap.

**Deploy 2 — pembayaran menyusul**

5. Pembayaran pindah ke partial + JS bersama; hapus modal inline, Template Agenda,
   `localStorage`.

---

## 10. Testing

| Test | Kenapa |
|---|---|
| Round-trip **"lepas semua kolom beku"** | Justru alasan `frozen_config` ada (§5.5). Tanpa test, bug ini kembali diam-diam. |
| **Hasil simpan tidak bergantung pada tab Beku pernah dibuka atau tidak** | Menjaga invarian §6.1 — bug yang SUDAH pernah terjadi di pembayaran. Skenario: bekukan kolom X → sembunyikan X → tampilkan X lagi → simpan. Hasil wajib sama, baik `renderFrozenTab()` sempat jalan maupun tidak. Sebelumnya invarian ini hanya ditulis, tanpa jaring. |
| Param URL tak dikenal (`mode`, `per_page`) selamat; filter dikosongkan benar-benar hilang | Menegakkan aturan §5.2 (bangun URL + semantik nilai kosong) |
| `renderOrder` ≠ urutan modal saat ada beku kanan | Menjaga dua urutan (§4.1) tidak tertukar |
| `mode=rekapan_table` selamat setelah simpan kolom (pembayaran) | Regresi yang ditemukan saat desain (§5) — harus test, bukan harapan |
| `nomor_agenda` selalu berakhir di `frozen.left` meski request memaksa `right`/kosong | Menegakkan §4.2 |
| Urutan CSS sebelum markup di 5 halaman (`assertSeeInOrder`) | Perluasan jaring yang sudah ada ke CSS tab baru |
| Pasca-migrasi: `applyTemplateAgenda` & `pembayaran_columns` lenyap | Bukti hapus tuntas |

QA visual lewat Playwright MCP (buka tab → bekukan → simpan) dilakukan agent dan
dilaporkan apa adanya; **keputusan lolos tetap di user** (CLAUDE.md §6).

---

## 11. Risiko & gerbang

- **Tidak menyentuh**: skema DB, RBAC/route middleware, auto-forward, aturan alur dokumen.
- **Menyentuh preferensi user sungguhan** (`users.table_columns_preferences`). Default
  dirancang identik dengan tampilan hari ini → tak ada user yang tabelnya berubah
  sendiri setelah deploy.
- **Menyentuh god-file pembayaran** (Deploy 2). Grep-gate + bukti ditunjukkan ke user
  sebelum penghapusan dieksekusi.
- **Menyentuh partial bersama** yang dipakai 4 role sekaligus → perubahan wajib
  aditif; jalur lama tidak diubah.

---

## 12. Di luar cakupan (YAGNI)

- Menyatukan 4 view Tabulator jadi 1 view + config — program terpisah, jadi lebih
  ringan setelah spec ini selesai.
- **Menyeragamkan toolbar filter.** Ditemukan saat desain: 4 role memfilter
  **client-side** (Tabulator `setFilter`, URL tak berubah), pembayaran **submit form
  ke server**. Perbedaan nyata dan relevan bagi tujuan "semua role sama", tapi di luar
  cakupan spec ini. Kandidat penyatuan berikutnya.
- **Menghapus `#filterForm`** dari 4 view. Lihat §5.3 — dipakai partial global
  `document-workbench-ui`; menyentuhnya gerbang kritis dengan manfaat nol di sini.
- Menyeragamkan penyimpanan preferensi operator (sesi) ke DB.
- Kolom beku untuk role `bagian` — role view-only, tidak memakai modal ini.
- Template kolom per-role (dihapus atas keputusan user, §2).
