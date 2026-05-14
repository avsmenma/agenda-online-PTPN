# Requirements Document

## Introduction

Menyesuaikan tabel dokumen pada halaman "/dashboard/pembayaran" agar mengikuti pola dan struktur tabel yang digunakan oleh tim verifikasi, perpajakan, dan akuntansi. Saat ini, halaman pembayaran menggunakan DataTables (jQuery plugin) dengan AJAX server-side rendering dan desain "premium SaaS" yang berbeda secara fundamental dari pola yang digunakan tim lain. Tim verifikasi, perpajakan, dan akuntansi menggunakan pendekatan Blade server-side rendering dengan fitur-fitur seperti dynamic column selection, inline editing, bulk operations, deadline cards, active cell navigation, dan virtual document table. Tujuan utama adalah konsistensi arsitektur dan UX antar tim.

## Glossary

- **Tabel_Pembayaran**: Komponen tabel dokumen pada halaman `/dashboard/pembayaran` yang menampilkan daftar dokumen pembayaran
- **Pola_Tim_Referensi**: Struktur dan metode tabel yang digunakan oleh tim verifikasi (`daftarDokumen.blade.php`), perpajakan (`daftarPerpajakan.blade.php`), dan akuntansi (`daftarAkutansi.blade.php`) sebagai referensi
- **Table_Enhanced**: CSS class utama (`table-enhanced`) yang digunakan oleh Pola_Tim_Referensi untuk styling tabel dengan horizontal scroll, sticky headers, dan column widths
- **Dynamic_Column_Selection**: Fitur yang memungkinkan user memilih kolom mana yang ditampilkan di tabel melalui variabel `$selectedColumns` dan `$availableColumns`
- **Inline_Edit_Engine**: Partial Blade (`_inlineEditEngine`) yang memungkinkan user mengedit data langsung di dalam sel tabel tanpa membuka halaman edit terpisah
- **Active_Cell_Navigation**: Partial Blade (`_activeCellNav`) yang menyediakan navigasi sel ala spreadsheet menggunakan tombol panah keyboard
- **Virtual_Document_Table**: Partial Blade (`virtual-document-table`) yang melakukan lazy loading/chunking untuk performa tabel dengan banyak data
- **Auto_Refresh**: Partial Blade (`auto-refresh-documents`) yang secara otomatis memperbarui data tabel ketika ada perubahan dokumen baru
- **Deadline_Card**: Komponen visual yang menampilkan umur dokumen sejak diterima (count up) dengan indikator warna hijau/kuning/merah
- **Bulk_Operations**: Fitur yang memungkinkan user memilih beberapa dokumen sekaligus menggunakan checkbox untuk aksi massal
- **Document_Handler_Select**: Partial Blade (`document-handler-select`) yang menampilkan dropdown untuk memilih pengurus dokumen
- **Sticky_Cells**: Partial Blade (`_documentTableStickyCells`) yang membuat kolom tertentu tetap terlihat saat scroll horizontal

## Requirements

### Requirement 1: Migrasi Rendering dari DataTables ke Blade Server-Side

**User Story:** Sebagai developer, saya ingin tabel pembayaran menggunakan Blade server-side rendering, sehingga arsitektur konsisten dengan tim lain dan lebih mudah di-maintain.

#### Acceptance Criteria

1. THE Tabel_Pembayaran SHALL menggunakan Blade template rendering (server-side) untuk menampilkan data dokumen, tanpa menggunakan DataTables jQuery plugin, DataTables CSS, DataTables JS CDN, maupun AJAX-based server-side rendering dari DataTables
2. THE Tabel_Pembayaran SHALL menggunakan struktur HTML `<table class="table table-enhanced mb-0">` yang identik dengan Pola_Tim_Referensi
3. THE Tabel_Pembayaran SHALL menggunakan Laravel pagination (`$dokumens->appends(request()->query())->links()`) untuk navigasi halaman data, sehingga parameter query string aktif (filter, search, sort, per_page) tetap dipertahankan saat berpindah halaman
4. THE Tabel_Pembayaran SHALL menampilkan default 10 item per halaman, dengan opsi per_page yang dapat dipilih user: 10, 25, 50, 100, atau semua data
5. THE Tabel_Pembayaran SHALL menampilkan informasi pagination berupa "Menampilkan [firstItem] - [lastItem] dari [total] dokumen" di area bawah tabel
6. IF tidak ada dokumen yang tersedia (jumlah dokumen = 0), THEN THE Tabel_Pembayaran SHALL menampilkan empty state berupa ikon folder kosong dan teks "Belum ada dokumen" menggantikan tabel, tanpa menampilkan elemen tabel maupun pagination
7. IF halaman yang diminta melebihi jumlah halaman yang tersedia, THEN THE Tabel_Pembayaran SHALL mengarahkan user ke halaman terakhir yang valid

### Requirement 2: Dynamic Column Selection

**User Story:** Sebagai user pembayaran, saya ingin memilih kolom mana yang ditampilkan di tabel, sehingga saya bisa fokus pada informasi yang relevan.

#### Acceptance Criteria

1. THE Tabel_Pembayaran SHALL mendukung Dynamic_Column_Selection menggunakan variabel `$selectedColumns` dan `$availableColumns` dari controller, dengan minimum 1 kolom dan maksimum seluruh kolom yang tersedia di `$availableColumns` dapat dipilih secara bersamaan
2. WHEN user memilih kolom yang ingin ditampilkan, THE Tabel_Pembayaran SHALL menyimpan preferensi kolom ke key `pembayaran_dashboard` pada field `table_columns_preferences` di model User, dan menyimpan fallback ke session `pembayaran_dashboard_table_columns`
3. THE Tabel_Pembayaran SHALL menyediakan modal kustomisasi kolom yang memungkinkan user memilih/membatalkan pilihan kolom individual, memilih semua kolom, membatalkan semua pilihan, dan mengatur urutan kolom melalui drag-and-drop
4. THE Tabel_Pembayaran SHALL memiliki default columns berikut ketika user belum menyimpan preferensi: `nomor_agenda`, `nomor_spp`, `dibayar_kepada`, `uraian_spp`, `nilai_rupiah`, `status_pembayaran`
5. IF user mengirimkan pilihan kolom yang mengandung key tidak valid (tidak ada di `$availableColumns`), THEN THE Tabel_Pembayaran SHALL memfilter key tersebut dan hanya menampilkan kolom yang valid, serta fallback ke default columns jika semua key tidak valid

### Requirement 3: Struktur Kolom Tabel

**User Story:** Sebagai user pembayaran, saya ingin tabel memiliki kolom-kolom standar yang konsisten dengan tim lain, sehingga informasi dokumen mudah dibaca.

#### Acceptance Criteria

1. THE Tabel_Pembayaran SHALL memiliki kolom checkbox sebagai kolom pertama, berisi input checkbox per baris dengan value dokumen ID, dan satu checkbox "Pilih Semua" di header yang men-toggle seluruh checkbox pada halaman aktif
2. THE Tabel_Pembayaran SHALL memiliki kolom "No" (nomor urut) yang dihitung dengan formula: index baris + (halaman saat ini - 1) × jumlah item per halaman, sehingga nomor urut berlanjut antar halaman
3. WHEN `$selectedColumns` berisi minimal 1 kolom, THE Tabel_Pembayaran SHALL merender kolom-kolom dari `$selectedColumns` secara berurutan menggunakan `@foreach` loop, di mana setiap kolom menampilkan header dari `$availableColumns[$col]` dan data dari atribut dokumen yang sesuai, dengan kolom berstatus 'status' dilewati dari loop ini
4. THE Tabel_Pembayaran SHALL memiliki kolom "Deadline" yang menampilkan Deadline_Card berisi: tanggal diterima (received_at) dalam format "dd MMM YYYY, HH:mm", waktu elapsed dalam format "X hari Y jam Z menit", dan indikator warna berdasarkan total jam elapsed (hijau: kurang dari 24 jam, kuning: 24-72 jam, merah: lebih dari 72 jam)
5. THE Tabel_Pembayaran SHALL memiliki kolom "Status" yang menampilkan badge dengan teks dan style berbeda untuk setiap status pembayaran: "Belum Siap Bayar" (status default), "Siap Bayar", dan "Sudah Dibayar"
6. THE Tabel_Pembayaran SHALL memiliki kolom "Pengurus Dokumen" yang merender partial `partials.document-handler-select` dengan parameter dokumen aktif
7. THE Tabel_Pembayaran SHALL memiliki kolom "Aksi" yang menampilkan tombol kontekstual: tombol lihat tracking (ikon mata) jika status "belum_siap_bayar", tombol Edit jika status "siap_bayar" atau "sudah_dibayar" dan data pembayaran belum lengkap (tanggal_dibayar atau link_bukti_pembayaran kosong), atau tombol Selesai (disabled) jika kedua field sudah terisi
8. IF `$selectedColumns` kosong (count = 0), THEN THE Tabel_Pembayaran SHALL tetap menampilkan kolom-kolom tetap (checkbox, No, Status, Pengurus Dokumen, Aksi) tanpa kolom dinamis
9. IF tidak ada dokumen dalam daftar, THEN THE Tabel_Pembayaran SHALL menampilkan satu baris dengan colspan mencakup seluruh kolom (count selectedColumns + 4 kolom tetap) berisi pesan "Belum ada dokumen pembayaran"

### Requirement 4: Deadline Card System

**User Story:** Sebagai user pembayaran, saya ingin melihat indikator visual umur dokumen, sehingga saya bisa memprioritaskan dokumen yang sudah lama menunggu.

#### Acceptance Criteria

1. THE Tabel_Pembayaran SHALL menampilkan Deadline_Card yang menghitung umur dokumen sejak `received_at` (count up) dari `dokumen_role_data` untuk role pembayaran, dengan format tampilan "X hari Y jam Z menit"
2. WHEN umur dokumen kurang dari 24 jam (dihitung dari `received_at` hingga waktu saat ini), THE Deadline_Card SHALL menampilkan indikator hijau dengan class `deadline-green` dan label "AMAN"
3. WHEN umur dokumen lebih dari atau sama dengan 24 jam dan kurang dari 72 jam, THE Deadline_Card SHALL menampilkan indikator kuning dengan class `deadline-yellow` dan label "PERINGATAN"
4. WHEN umur dokumen lebih dari atau sama dengan 72 jam, THE Deadline_Card SHALL menampilkan indikator merah dengan class `deadline-red` dan label "TERLAMBAT"
5. WHEN dokumen memiliki `status_pembayaran` = `sudah_dibayar`, THE Deadline_Card SHALL menampilkan indikator completed dengan class `deadline-completed`, menghentikan penghitungan waktu (freeze), dan menampilkan durasi pemrosesan yang dihitung dari `received_at` hingga `processed_at` (atau `updated_at` jika `processed_at` tidak tersedia)
6. IF `received_at` bernilai null untuk role pembayaran, THEN THE Deadline_Card SHALL menampilkan tanda strip "-" tanpa indikator warna
7. THE Deadline_Card SHALL menggunakan CSS styling yang identik dengan Pola_Tim_Referensi (class `deadline-card`, `deadline-green`, `deadline-yellow`, `deadline-red`, `deadline-completed`)

### Requirement 5: Inline Editing

**User Story:** Sebagai user pembayaran, saya ingin mengedit data dokumen langsung di tabel, sehingga proses update data lebih cepat tanpa membuka halaman edit.

#### Acceptance Criteria

1. THE Tabel_Pembayaran SHALL menggunakan Inline_Edit_Engine partial (`@include('partials._inlineEditEngine')`) untuk fitur edit langsung di sel tabel
2. WHEN user melakukan single-click pada sel yang memiliki class `ie-cell`, THE Inline_Edit_Engine SHALL mengaktifkan mode edit pada sel tersebut dengan menampilkan input field sesuai tipe data kolom (text, number, date, textarea, atau select)
3. THE Tabel_Pembayaran SHALL menandai sel yang editable dengan class `ie-cell`, atribut `data-field` berisi nama kolom, dan atribut `data-raw` berisi nilai mentah dari database
4. THE Tabel_Pembayaran SHALL menandai baris yang editable dengan atribut `data-editable="true"` hanya pada dokumen yang `current_handler`-nya adalah `pembayaran`
5. WHEN user menekan tombol Escape saat mode edit aktif, THE Inline_Edit_Engine SHALL membatalkan perubahan dan mengembalikan sel ke tampilan semula tanpa mengirim request ke server
6. WHEN user menekan tombol Enter (untuk input text/number/date) atau memilih opsi (untuk select), THE Inline_Edit_Engine SHALL mengirim PATCH request ke endpoint `/documents/{id}/inline-update` dalam waktu maksimal 100ms setelah aksi user, dan menampilkan spinner selama proses penyimpanan berlangsung
7. IF server mengembalikan response sukses (`success: true`), THEN THE Inline_Edit_Engine SHALL memperbarui tampilan sel dengan `display_value` dari response, menambahkan class `ie-saved` selama 700ms sebagai indikator visual hijau, dan memperbarui atribut `data-raw` dengan nilai baru
8. IF server mengembalikan response error (HTTP 403, 422, atau 500), THEN THE Inline_Edit_Engine SHALL mengembalikan sel ke tampilan semula, menambahkan class `ie-error` selama 700ms sebagai indikator visual merah, dan menampilkan toast notification di bagian bawah layar dengan pesan error dari server selama 3500ms
9. IF user melakukan single-click pada sel `ie-cell` yang berisi elemen anchor (`<a>`), THEN THE Inline_Edit_Engine SHALL membuka link tersebut tanpa mengaktifkan mode edit
10. WHEN user menekan tombol Tab saat mode edit aktif, THE Inline_Edit_Engine SHALL menyimpan perubahan pada sel aktif dan memindahkan fokus edit ke sel `ie-cell` berikutnya (atau sebelumnya jika Shift+Tab) dalam urutan DOM

### Requirement 6: Active Cell Navigation

**User Story:** Sebagai user pembayaran, saya ingin menavigasi sel tabel menggunakan tombol panah keyboard, sehingga pengalaman seperti spreadsheet.

#### Acceptance Criteria

1. THE Tabel_Pembayaran SHALL menggunakan Active_Cell_Navigation partial (`@include('partials._activeCellNav', ['tableSelector' => '.table-enhanced'])`)
2. WHEN user menekan tombol panah (ArrowUp, ArrowDown, ArrowLeft, ArrowRight) pada keyboard, THE Active_Cell_Navigation SHALL memindahkan fokus ke sel yang bersebelahan sesuai arah panah, dengan posisi baris dan kolom di-clamp pada batas tabel (baris 0 sampai jumlah baris data - 1, kolom 0 sampai jumlah kolom - 1) sehingga fokus tidak keluar dari area tabel
3. WHEN sel aktif berpindah, THE Active_Cell_Navigation SHALL menampilkan indikator visual berupa outline pada sel aktif, highlight pada baris aktif, highlight pada header kolom aktif, dan menampilkan referensi sel (format huruf kolom + nomor baris, contoh: "B3") pada indicator bar di bawah tabel
4. IF user sedang fokus pada elemen input, textarea, atau select, atau jika modal/overlay sedang terbuka, THEN THE Active_Cell_Navigation SHALL tidak memproses event keyboard navigasi dan membiarkan perilaku default browser
5. WHEN halaman selesai dimuat dan tabel berisi minimal 1 baris data, THE Active_Cell_Navigation SHALL secara otomatis menetapkan sel aktif awal pada baris pertama kolom kedua (atau kolom pertama jika hanya ada 1 kolom)

### Requirement 7: Virtual Document Table dan Auto Refresh

**User Story:** Sebagai user pembayaran, saya ingin tabel tetap responsif meskipun data banyak dan otomatis memperbarui data baru, sehingga pengalaman kerja tidak terganggu.

#### Acceptance Criteria

1. THE Tabel_Pembayaran SHALL menggunakan Virtual_Document_Table partial (`@include('partials.virtual-document-table')`) untuk merender data dalam chunk sebanyak 100 baris per chunk melalui scroll-based lazy loading
2. WHEN jumlah dokumen melebihi ukuran chunk (100 baris), THE Virtual_Document_Table SHALL menampilkan scroll container dengan tinggi maksimum 72vh (maksimal 760px) dan merender hanya chunk yang terlihat di viewport
3. THE Tabel_Pembayaran SHALL menggunakan Auto_Refresh partial (`@include('partials.auto-refresh-documents')`) untuk memperbarui data secara otomatis dengan interval 10 detik setelah user idle selama 5 detik tanpa interaksi (keydown, mousedown, touchstart, wheel, input, change, focusin)
4. IF user sedang berinteraksi dengan form input, modal terbuka, dropdown aktif, atau checkbox tercentang, THEN THE Auto_Refresh SHALL menunda pembaruan otomatis hingga kondisi blocking tersebut selesai
5. WHEN ada dokumen baru masuk ke pembayaran dan auto-refresh berhasil memperbarui tabel, THE Auto_Refresh SHALL memperbarui isi tabel secara silent tanpa mengganggu posisi scroll user
6. IF auto-refresh gagal memuat data, THEN THE Auto_Refresh SHALL mencatat error ke console dan mempertahankan data tabel yang sedang ditampilkan tanpa perubahan

### Requirement 8: Bulk Operations

**User Story:** Sebagai user pembayaran, saya ingin memilih beberapa dokumen sekaligus untuk aksi massal, sehingga proses kerja lebih efisien.

#### Acceptance Criteria

1. THE Tabel_Pembayaran SHALL menyediakan checkbox pada setiap baris dokumen yang `current_handler`-nya adalah `pembayaran` dan belum selesai diproses, serta menonaktifkan (disabled) checkbox pada dokumen yang tidak memenuhi syarat tersebut
2. THE Tabel_Pembayaran SHALL menyediakan checkbox "Select All" pada header tabel yang memilih atau membatalkan semua dokumen eligible pada halaman yang sedang aktif
3. WHEN user memilih satu atau lebih dokumen, THE Tabel_Pembayaran SHALL menampilkan floating action bar di bagian bawah layar dengan animasi slide-up, dan menyembunyikannya kembali ketika tidak ada dokumen yang dipilih
4. THE floating action bar SHALL menampilkan jumlah dokumen yang dipilih, dropdown pilihan aksi (kirim ke perpajakan, kirim ke akuntansi), tombol eksekusi, dan tombol batal yang membatalkan seluruh seleksi
5. WHEN user menekan tombol eksekusi pada floating action bar tanpa memilih aksi dari dropdown, THEN THE Tabel_Pembayaran SHALL menampilkan pesan error yang mengindikasikan bahwa user harus memilih aksi terlebih dahulu
6. WHEN user mengkonfirmasi aksi massal, THE Tabel_Pembayaran SHALL menampilkan modal konfirmasi yang mencantumkan nama aksi dan daftar dokumen yang akan diproses sebelum mengeksekusi operasi
7. IF operasi massal gagal sebagian atau seluruhnya, THEN THE Tabel_Pembayaran SHALL menampilkan modal hasil yang menunjukkan jumlah dokumen berhasil, jumlah dokumen gagal, dan detail error untuk setiap kegagalan

### Requirement 9: Sortable Columns

**User Story:** Sebagai user pembayaran, saya ingin mengurutkan data berdasarkan kolom tertentu, sehingga saya bisa menemukan dokumen dengan cepat.

#### Acceptance Criteria

1. THE Tabel_Pembayaran SHALL menampilkan kolom `nomor_agenda` sebagai sortable column dengan dua ikon panah (caret-up dan caret-down) di samping label header, di mana ikon arah aktif ditampilkan dengan opacity 1 dan ikon arah tidak aktif ditampilkan dengan opacity 0.3
2. WHEN halaman dimuat tanpa parameter `sort` dan `order` di URL, THE Tabel_Pembayaran SHALL mengurutkan data berdasarkan `nomor_agenda` descending sebagai default sort dan menampilkan ikon caret-down dengan opacity 1
3. WHEN user mengklik header kolom `nomor_agenda`, THE Tabel_Pembayaran SHALL melakukan toggle sort order (dari ascending ke descending atau sebaliknya), memperbarui URL dengan parameter `sort` dan `order`, menghapus parameter `page` dari URL, dan me-reload halaman dengan hasil pengurutan baru
4. WHEN parameter `sort` atau `order` ada di URL, THE Tabel_Pembayaran SHALL menyimpan preferensi sort ke session (`pembayaran_sort_column` dan `pembayaran_sort_order`) sehingga preferensi dipertahankan pada navigasi halaman berikutnya tanpa parameter sort
5. IF parameter `order` berisi nilai selain `asc` atau `desc`, THEN THE Tabel_Pembayaran SHALL menggunakan `desc` sebagai default sort order

### Requirement 10: Row Interaction dan Document Detail

**User Story:** Sebagai user pembayaran, saya ingin melihat detail dokumen dengan double-click pada baris, sehingga akses informasi lebih cepat.

#### Acceptance Criteria

1. WHEN user melakukan double-click pada baris dokumen yang berstatus `siap_bayar` atau `sudah_dibayar`, THE Tabel_Pembayaran SHALL membuka modal detail dokumen (`viewDocumentModal`) melalui AJAX GET request ke endpoint detail dan menampilkan loading indicator selama request berlangsung
2. IF AJAX request detail dokumen gagal atau mengembalikan response tidak valid, THEN THE Tabel_Pembayaran SHALL menampilkan pesan error di dalam modal yang menjelaskan kegagalan, tanpa menutup modal
3. IF user melakukan double-click pada elemen interaktif di dalam baris (link, tombol, input, select, textarea, atau sel dengan class `ie-cell`), THEN THE Tabel_Pembayaran SHALL TIDAK membuka modal detail dan membiarkan interaksi elemen tersebut berjalan normal
4. THE Tabel_Pembayaran SHALL menggunakan class `main-row` dan `clickable-row` pada baris tabel yang dapat di-klik sesuai Pola_Tim_Referensi, dan class `no-click-row` pada baris yang berstatus `belum_siap_bayar`
5. THE Tabel_Pembayaran SHALL menyimpan atribut `data-dokumen-id` pada setiap baris untuk identifikasi dokumen yang digunakan dalam AJAX request
6. WHEN modal detail dokumen berhasil menerima response, THE Tabel_Pembayaran SHALL menampilkan data dokumen di dalam modal dan menyembunyikan loading indicator dalam waktu maksimal 5 detik sejak request dimulai

### Requirement 11: Horizontal Scroll dan Sticky Cells

**User Story:** Sebagai user pembayaran, saya ingin kolom-kolom penting tetap terlihat saat scroll horizontal, sehingga konteks data tidak hilang.

#### Acceptance Criteria

1. THE Tabel_Pembayaran SHALL menggunakan container `table-responsive` dengan `overflow-x: auto` yang membungkus elemen tabel sesuai Pola_Tim_Referensi
2. WHILE user melakukan scroll horizontal, THE Tabel_Pembayaran SHALL mempertahankan kolom checkbox (lebar 64px, posisi left: 0), kolom nomor (lebar 88px), dan kolom nomor_agenda (lebar 210px) tetap terlihat di sisi kiri menggunakan CSS `position: sticky` melalui Sticky_Cells partial
3. WHILE user melakukan scroll horizontal, THE Tabel_Pembayaran SHALL mempertahankan kolom handler tetap terlihat di sisi kanan menggunakan CSS `position: sticky` dengan `right: 0` melalui Sticky_Cells partial
4. THE Tabel_Pembayaran SHALL menggunakan `table-layout: fixed` dengan `width: max-content` dan `min-width: 100%` sehingga setiap kolom memiliki lebar minimum sesuai nilai yang didefinisikan dalam Sticky_Cells partial
5. WHEN tabel di-refresh via AJAX, THE Tabel_Pembayaran SHALL mempertahankan posisi scroll horizontal yang sama sebelum refresh dilakukan

### Requirement 12: Konsistensi Styling

**User Story:** Sebagai user pembayaran, saya ingin tampilan tabel konsisten dengan tim lain, sehingga pengalaman pengguna seragam di seluruh aplikasi.

#### Acceptance Criteria

1. THE Tabel_Pembayaran SHALL menggunakan gradient header (`linear-gradient(135deg, #083E40 0%, #0a4f52 100%)`) pada thead dengan teks berwarna `rgba(255,255,255,0.95)` sesuai Pola_Tim_Referensi
2. WHEN user melakukan hover pada baris tabel, THE Tabel_Pembayaran SHALL menampilkan `border-left: 3px solid #083E40` dan background `linear-gradient(90deg, rgba(8,62,64,0.04) 0%, transparent 100%)` pada baris tersebut
3. THE Tabel_Pembayaran SHALL menggunakan class `badge-status` dengan padding `8px 16px`, border-radius `25px`, font-size `12px`, dan font-weight `700` untuk status dokumen, serta varian class `badge-proses`, `badge-selesai`, `badge-dikembalikan`, dan `badge-locked` sesuai Pola_Tim_Referensi
4. THE Tabel_Pembayaran SHALL menggunakan font-size `13px` pada thead th dan tbody td, padding `16px 12px` pada thead th, dan padding `14px 12px` pada tbody td sesuai Pola_Tim_Referensi
