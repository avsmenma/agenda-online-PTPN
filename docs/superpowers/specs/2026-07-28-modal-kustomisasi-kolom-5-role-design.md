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

Empat lapis. Tiga sudah ada; hanya lapis modal yang dikerjakan, plus satu trait baru.

| Lapis | Berkas | Perubahan |
|---|---|---|
| Mesin tabel | `public/js/document-tabulator.js` | **Nol.** `buildColumns()` sudah membaca `cfg.frozen = {left,right}`; role tanpa itu dapat daftar kosong → perilaku lama utuh. |
| Tata letak beku | `App\Support\FrozenColumnLayout` | **Nol.** `normalize()` + `renderOrder()` sudah bersama & ada unit test. Tinggal dipakai 4 role lain. |
| Modal | `partials/_columnCustomizationModal.blade.php` + `public/js/column-customization.js` | **Di sini pekerjaannya** — tambah tab Kolom Beku. |
| Preferensi server | **BARU** `App\Http\Controllers\Concerns\CustomizesDocumentColumns` | Trait bersama. |

### 3.1 Trait `CustomizesDocumentColumns`

Logika baca-simpan preferensi kolom + beku ~60 baris. Menyalinnya ke 4 controller =
salinan ke-5 = memperparah penyakit utama (CLAUDE.md §3). Satu method bersama:

```php
$cc = $this->resolveColumnCustomization($request, [
    'available'     => $availableColumns,
    'default'       => $defaultColumns,
    'prefKey'       => 'akutansi',                        // kunci table_columns_preferences
    'sessionKey'    => 'akutansi_dokumens_table_columns',
    'frozenDefault' => ['left' => ['nomor_agenda'], 'right' => []],
]);
// → ['selected' => [...], 'frozen' => ['left' => [...], 'right' => [...]], 'render' => [...]]
```

Trait **membungkus pola yang sudah ada, tidak mengubah aturannya**:

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
dengan hardcode itu, **trait wajib memaksa `nomor_agenda` selalu ada di
`frozen.left`** (sisipkan bila hilang) sebelum `normalize()`. Di tab Kolom Beku,
barisnya dirender **non-aktif** dengan keterangan "identitas baris selalu terlihat".

Ini sekaligus memperbaiki kejanggalan lama pembayaran: di sana user bisa menyetel
Nomor Agenda ke "Bebas", menyimpannya, dan kolomnya tetap beku — pengaturan yang
tidak pernah berefek.

---

## 5. Perilaku Simpan — gabungan dua jalur, bukan salah satunya

Dua jalur yang ada sekarang sama-sama bocor:

- **4 role**: submit `#filterForm` (GET). Filter toolbar selamat, tapi **parameter URL
  yang bukan field form hilang**.
- **Pembayaran**: bangun ulang dari `window.location.href`. `mode=rekapan_table` &
  `per_page` selamat, tapi **perubahan toolbar yang belum disubmit hilang**.

Memindahkan pembayaran mentah-mentah ke jalur pertama = **mode rekapan vendor lompat
balik ke normal setiap kali user menyimpan kolom.** Jalur bersama = superset:

```
1. mulai dari #filterForm (submit GET)              → filter toolbar terbawa   [4 role]
2. salin param URL berjalan yang TIDAK diwakili
   field form apa pun → hidden input                → mode/per_page selamat    [pembayaran]
3. timpa: columns[], enable_customization,
   frozen_config=1, frozen_left[], frozen_right[]
```

Nama ter-reservasi bertambah dari 2 menjadi **5**: `columns[]`,
`enable_customization`, `frozen_config`, `frozen_left[]`, `frozen_right[]` — supaya
langkah 2 tak pernah menimpa langkah 3.

### 5.1 `frozen_config=1` wajib ikut

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

---

## 9. Rollout

**Deploy 1 — 4 role; pembayaran belum disentuh**

1. Trait `CustomizesDocumentColumns` + unit test
2. Partial bersama: tab bar + panel Kolom Beku. **CSS wajib lewat `@push('styles')`**
   — bukan `<style>` inline di body; itu pernah jadi regresi flash-of-unstyled-modal
   saat ekstraksi 2026-07-28.
3. JS bersama: state beku, `switchColumnTab`, `getFrozenState`/`setFrozenState`,
   `renderFrozenTab`, `renderFrozenWarning`, Simpan versi superset (§5)
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
| Round-trip **"lepas semua kolom beku"** | Justru alasan `frozen_config` ada (§5.1). Tanpa test, bug ini kembali diam-diam. |
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
- Menyeragamkan penyimpanan preferensi operator (sesi) ke DB.
- Kolom beku untuk role `bagian` — role view-only, tidak memakai modal ini.
- Template kolom per-role (dihapus atas keputusan user, §2).
