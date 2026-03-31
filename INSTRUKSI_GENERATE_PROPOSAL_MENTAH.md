# INSTRUKSI: Generate Laporan Proposal Tugas Akhir (Mentah/Raw)

> **Tujuan:** Kamu diminta untuk menghasilkan **konten mentah** proposal Tugas Akhir dalam format Markdown (`.md`) berdasarkan pemahaman kamu terhadap project Agenda Online PTPN yang ada di codebase ini. Konten ini akan digunakan oleh AI lain untuk diformat menjadi dokumen Word (.docx) sesuai standar Politeknik Negeri Pontianak.
>
> **Yang kamu harus lakukan:** Baca, pahami, dan analisis seluruh codebase project ini (routes, models, controllers, migrations, views, middleware, dll.), lalu tulis isi proposal berdasarkan apa yang benar-benar ada di dalam kode — bukan asumsi.

---

## IDENTITAS MAHASISWA & PROJECT

Isi bagian ini dengan data yang sudah diketahui. Sisakan placeholder `[...]` untuk data yang harus diisi manual oleh mahasiswa.

```
Nama Mahasiswa  : Febri Adithya
NIM             : [NIM MAHASISWA]
Program Studi   : D-III Teknik Informatika
Jurusan         : Teknik Elektro
Institusi       : Politeknik Negeri Pontianak
Tahun           : 2025
Dosen Pembimbing: Neny Firdyanti, S.T., M.T.
NIP Pembimbing  : [NIP PEMBIMBING]
Tempat PKL/Magang: PT Perkebunan Nusantara IV Regional V
```

---

## FORMAT OUTPUT

Tulis output dalam format Markdown dengan struktur berikut. Gunakan heading yang **persis sama** seperti di bawah ini agar mudah diparsing nantinya.

---

## STRUKTUR KONTEN YANG HARUS DIHASILKAN

### BAGIAN 0: JUDUL

Tulis satu judul proposal yang:
- Menarik, positif, singkat, spesifik
- Maksimal 16 kata (tidak termasuk kata sambung/depan)
- Mencerminkan sistem yang dibangun, teknologi utama, dan lokasi/konteks penggunaannya
- Tidak mengandung singkatan dalam judul
- Contoh format: "Rancang Bangun Sistem Informasi [Nama Sistem] Berbasis Web Menggunakan [Framework] pada [Instansi]"

---

### BAB I — PENDAHULUAN

#### 1.1 Latar Belakang

Tulis 4–6 paragraf yang mencakup:

1. **Konteks organisasi**: Jelaskan PT Perkebunan Nusantara IV Regional V — jenis perusahaan, bidang usaha, dan relevansinya dengan kebutuhan sistem informasi pengelolaan agenda/dokumen keuangan.

2. **Permasalahan yang ada**: Jelaskan kondisi sebelum sistem dibangun — apa yang tidak efisien, tidak terstruktur, atau manual. Kamu bisa infer ini dari fitur-fitur yang ada di sistem (misalnya: jika ada fitur approval multi-level, berarti sebelumnya proses approval tidak terstruktur/manual).

3. **Urgensi digitalisasi**: Jelaskan mengapa pengelolaan agenda/dokumen keuangan secara digital penting, terutama di lingkungan BUMN seperti PTPN.

4. **Solusi yang diusulkan**: Perkenalkan sistem Agenda Online PTPN sebagai solusi — sebutkan fitur-fitur utama yang kamu temukan di codebase.

5. **Landasan teknologi**: Sebutkan stack teknologi yang digunakan (Laravel, MySQL, Tailwind CSS, Alpine.js, Pusher, dll.) dan alasan singkat pemilihannya.

6. **Penutup latar belakang**: Kalimat penegasan bahwa sistem ini dibangun sebagai Tugas Akhir berdasarkan kegiatan PKL di PTPN IV Regional V.

> **Instruksi khusus:** Sertakan minimal 3 sitasi format IEEE (`[1]`, `[2]`, `[3]`) yang relevan. Buat referensi placeholder di bagian Daftar Pustaka. Topik sitasi yang cocok: sistem informasi manajemen dokumen, e-government/digitalisasi BUMN, Laravel framework, atau sistem approval elektronik.

---

#### 1.2 Rumusan Masalah

Tulis 3–5 poin rumusan masalah dalam bentuk **pertanyaan**, berdasarkan permasalahan yang kamu identifikasi dari fitur-fitur sistem. Contoh pola:
- "Bagaimana merancang sistem pengelolaan agenda [X] yang dapat [Y]?"
- "Bagaimana mengimplementasikan alur persetujuan [X] secara digital di [Y]?"
- "Bagaimana memastikan keamanan akses sistem dengan [teknologi] untuk [konteks]?"

---

#### 1.3 Batasan Masalah

Tulis 4–6 poin batasan yang jelas. Analisis codebase untuk menentukan batasan yang realistis, misalnya:
- Sistem hanya digunakan di lingkungan PTPN IV Regional V
- Sistem berbasis web (tidak mencakup aplikasi mobile)
- Role dan hak akses yang didukung (sebutkan role yang kamu temukan di codebase/migrations/middleware)
- Batasan fitur: fitur apa yang ada dan apa yang tidak ada
- Batasan teknologi: browser/OS yang didukung (jika ada)
- Data yang dikelola terbatas pada [jenis dokumen/agenda yang ada di sistem]

---

#### 1.4 Tujuan Tugas Akhir

Tulis 3–4 poin tujuan yang selaras dengan rumusan masalah. Setiap tujuan harus mengacu pada satu rumusan masalah. Gunakan kata kerja aktif: "Merancang...", "Mengimplementasikan...", "Menguji...", "Menghasilkan..."

---

#### 1.5 Manfaat Tugas Akhir

Tulis manfaat dalam **dua kelompok**:

**A. Manfaat Teoritis:**
- Kontribusi terhadap keilmuan/referensi akademik

**B. Manfaat Praktis:**
- Manfaat bagi PTPN IV Regional V (operasional)
- Manfaat bagi mahasiswa (pengalaman/kompetensi)
- Manfaat bagi institusi pendidikan (Politeknik Negeri Pontianak)

---

#### 1.6 Metodologi Pelaksanaan Tugas Akhir

Jelaskan metode **System Development Life Cycle (SDLC)** dengan pendekatan **Waterfall** (atau sesuaikan jika kamu menemukan indikasi metode lain di codebase/struktur project). Jabarkan tahapan:

1. **Analisis Kebutuhan** — pengumpulan data melalui observasi dan wawancara di PTPN IV Regional V
2. **Perancangan Sistem** — perancangan arsitektur, database, UI/UX
3. **Implementasi** — pengkodean menggunakan Laravel, MySQL, dll.
4. **Pengujian** — metode pengujian yang akan/telah digunakan (Black Box Testing, UAT, dll.)
5. **Pemeliharaan** — rencana maintenance pasca-deployment

---

### BAB II — DASAR TEORI

#### 2.1 Tinjauan Pustaka

**A. Ringkasan Penelitian Terdahulu**

Buat ringkasan 3–4 penelitian terdahulu yang relevan. Karena kamu tidak memiliki akses ke database jurnal, **buat placeholder** dengan format:
```
[PENELITIAN 1]: [Penulis, Tahun] — Topik: Sistem Informasi Manajemen Dokumen/Agenda — [Ringkasan singkat dan relevansinya]
[PENELITIAN 2]: [Penulis, Tahun] — Topik: Sistem Approval Elektronik — [...]
[PENELITIAN 3]: [Penulis, Tahun] — Topik: Implementasi RBAC pada Sistem Web — [...]
[PENELITIAN 4]: [Penulis, Tahun] — Topik: Digitalisasi Proses Bisnis BUMN — [...]
```
Tandai dengan komentar: `<!-- MAHASISWA: Ganti placeholder ini dengan referensi jurnal asli yang relevan -->`

**B. Tabel Perbandingan Sistem**

Buat tabel perbandingan dalam format Markdown antara sistem yang diusulkan (Agenda Online PTPN) dengan minimal 2 sistem/penelitian terdahulu. Kolom yang wajib ada:

| No | Aspek | Penelitian A | Penelitian B | Sistem yang Diusulkan |
|---|---|---|---|---|
| 1 | Teknologi | ... | ... | Laravel 12, MySQL, Tailwind CSS, Alpine.js, Pusher |
| 2 | Fitur Utama | ... | ... | [dari codebase] |
| 3 | Manajemen Role | ... | ... | RBAC dengan [X] role |
| 4 | Real-time Notifikasi | ... | ... | Ya (Pusher) |
| 5 | Autentikasi | ... | ... | 2FA |
| 6 | Kelebihan | ... | ... | [dari codebase] |
| 7 | Kekurangan/Batasan | ... | ... | [batasan sistem] |

**C. Kebaruan Sistem yang Diusulkan**

Jelaskan apa yang membedakan sistem Agenda Online PTPN dari sistem-sistem yang pernah ada sebelumnya, berdasarkan fitur unik yang kamu temukan di codebase.

---

#### 2.2 Dasar Teori

Tuliskan sub-bab dasar teori untuk **setiap teknologi dan konsep** yang kamu temukan digunakan dalam project. Minimum yang harus ada:

Untuk setiap item di bawah, tulis **1–2 paragraf** penjelasan teori + 1 sitasi IEEE:

1. **Sistem Informasi** — definisi dan fungsinya dalam organisasi
2. **Sistem Informasi Manajemen Dokumen/Agenda** — konsep pengelolaan dokumen digital
3. **Laravel** — definisi, versi yang digunakan (Laravel 12), keunggulan framework MVC
4. **PHP** — sebagai bahasa dasar Laravel
5. **MySQL** — sistem manajemen basis data relasional
6. **Tailwind CSS** — utility-first CSS framework
7. **Alpine.js** — lightweight JavaScript framework untuk interaktivitas UI
8. **Pusher** — platform real-time WebSocket untuk notifikasi
9. **Role-Based Access Control (RBAC)** — konsep manajemen hak akses
10. **Two-Factor Authentication (2FA)** — konsep keamanan autentikasi dua faktor
11. **Black Box Testing** — metode pengujian perangkat lunak
12. **SDLC / Waterfall** — metodologi pengembangan sistem

> **Jika kamu menemukan teknologi/library tambahan di codebase** (misalnya: Chart.js, Spatie Permission, Laravel Sanctum, dll.), tambahkan sub-babnya juga.

> **Format sitasi:** Gunakan format IEEE seperti `[1]`, `[2]`, dst. Buat placeholder referensi di bagian Daftar Pustaka.

---

### BAB III — RANCANGAN AWAL SISTEM

> **PENTING:** Bagian ini adalah yang paling penting dan harus paling detail. Analisis codebase secara mendalam sebelum menulis bagian ini.

#### 3.1 Analisis Kebutuhan Sistem

**A. Kebutuhan Fungsional**

Buat daftar kebutuhan fungsional berdasarkan fitur yang benar-benar ada di codebase. Format:

| Kode | Kebutuhan Fungsional | Deskripsi |
|---|---|---|
| KF-01 | [nama fitur] | [penjelasan] |
| KF-02 | ... | ... |

Cari di: routes/web.php, controllers, views, migrations untuk menentukan fitur-fitur yang ada.

**B. Kebutuhan Non-Fungsional**

Tulis minimal 5 kebutuhan non-fungsional:
- Keamanan (autentikasi, otorisasi, 2FA)
- Performa (response time, concurrent users)
- Ketersediaan (uptime)
- Kegunaan/Usability
- Kompatibilitas browser

**C. Daftar Role Pengguna**

Buat tabel semua role yang ada di sistem berdasarkan migrations/seeder/middleware/config:

| No | Role | Deskripsi Tugas | Hak Akses Utama |
|---|---|---|---|
| 1 | [nama role] | [deskripsi] | [hak akses] |

---

#### 3.2 Arsitektur Sistem

Jelaskan arsitektur sistem secara naratif (karena ini laporan mentah — gambar akan dibuat manual oleh mahasiswa). Uraikan:

1. **Pola Arsitektur:** MVC (Model-View-Controller) dengan Laravel
2. **Komponen Utama:**
   - Client (Browser) → Web Server → Laravel Application → MySQL Database
   - Pusher untuk real-time WebSocket
   - [komponen lain yang kamu temukan]
3. **Alur Request:** Jelaskan bagaimana request dari user mengalir melalui sistem
4. **Tulis placeholder gambar:** `[GAMBAR: Diagram Arsitektur Sistem - dibuat manual]`

---

#### 3.3 Perancangan Basis Data

**A. Entity Relationship Diagram (ERD)**

Berdasarkan file migrations yang kamu temukan, tulis semua tabel dan relasinya:

`[GAMBAR: ERD - dibuat manual berdasarkan daftar tabel di bawah]`

**B. Daftar Tabel**

Untuk **setiap tabel** yang ada di migrations, buat dokumentasi:

```
Nama Tabel: [nama_tabel]
Deskripsi: [fungsi tabel ini]

| Kolom | Tipe Data | Constraint | Keterangan |
|---|---|---|---|
| id | bigint | PK, AI | Primary key |
| [kolom] | [tipe] | [constraint] | [keterangan] |
```

Lakukan ini untuk **semua tabel** yang ada. Jangan lewati satu pun.

**C. Relasi Antar Tabel**

Buat narasi atau tabel yang menjelaskan relasi antar tabel (one-to-one, one-to-many, many-to-many).

---

#### 3.4 Perancangan Antarmuka (UI/UX)

Jelaskan rancangan antarmuka secara naratif per modul/halaman. Untuk setiap halaman utama yang kamu temukan di views:

```
Nama Halaman: [nama]
Role yang Mengakses: [role]
Komponen UI: [daftar komponen yang ada — card, tabel, chart, form, dll.]
Fungsi: [deskripsi fungsi halaman]
[GAMBAR: Screenshot/Mockup - dilampirkan manual oleh mahasiswa]
```

Minimal harus mencakup:
- Dashboard (per role jika berbeda)
- Halaman daftar agenda/dokumen
- Halaman detail/view agenda
- Halaman form input (create/edit)
- Halaman approval/persetujuan (jika ada)
- Halaman laporan/report (jika ada)
- Halaman manajemen user (jika ada)
- Halaman login/autentikasi

---

#### 3.5 Perancangan Algoritma atau Proses Bisnis

Jelaskan alur proses bisnis utama sistem secara naratif. Minimal:

**A. Alur Pengelolaan Agenda/Dokumen**
- Bagaimana dokumen/agenda dibuat, diproses, disetujui, dan diarsipkan

**B. Alur Multi-Level Approval**
- Siapa yang mengajukan → siapa yang mereview → siapa yang menyetujui → apa yang terjadi setelah disetujui/ditolak
- Kamu bisa infer ini dari controller approval dan routing-nya

**C. Alur Notifikasi Real-time**
- Kapan notifikasi dikirim, kepada siapa, melalui channel apa (Pusher)

**D. Alur Autentikasi dan Otorisasi**
- Login → 2FA (jika ada) → Redirect berdasarkan role → Middleware check

Untuk setiap alur, tulis: `[GAMBAR: Flowchart/Activity Diagram - dibuat manual]`

---

#### 3.6 Spesifikasi Teknologi

Buat tabel lengkap spesifikasi teknologi yang digunakan:

**A. Teknologi Pengembangan**

| No | Teknologi | Versi | Fungsi |
|---|---|---|---|
| 1 | Laravel | 12.x | Backend framework (MVC) |
| 2 | PHP | [cek composer.json] | Bahasa pemrograman server-side |
| 3 | MySQL | [versi] | Sistem manajemen basis data |
| 4 | Tailwind CSS | [cek package.json] | Styling/UI framework |
| 5 | Alpine.js | [cek package.json] | JavaScript interaktivitas |
| 6 | Pusher | [cek package.json/composer.json] | Real-time WebSocket |
| [dst] | [dari package.json/composer.json] | [versi] | [fungsi] |

**B. Tools Pengembangan**

| No | Tools | Fungsi |
|---|---|---|
| 1 | Visual Studio Code | Code editor |
| 2 | Git | Version control |
| 3 | Composer | PHP package manager |
| 4 | NPM/Vite | JavaScript bundler |
| [dst] | [dari project] | [fungsi] |

**C. Spesifikasi Server/Deployment**

| No | Komponen | Spesifikasi |
|---|---|---|
| 1 | OS Server | Ubuntu 20.04 LTS |
| 2 | Web Server | Nginx |
| 3 | Database Server | MySQL |
| 4 | PHP Version | [dari project] |
| [dst] | ... | ... |

---

#### 3.7 Rencana Pengujian

Buat rencana pengujian dengan metode **Black Box Testing**. Untuk setiap fitur utama yang kamu temukan, buat tabel skenario pengujian:

| No | Skenario Uji | Input | Output yang Diharapkan | Hasil |
|---|---|---|---|---|
| 1 | Login dengan kredensial valid | Username & password benar | Redirect ke dashboard sesuai role | [diisi saat pengujian] |
| 2 | Login dengan kredensial tidak valid | Username/password salah | Pesan error muncul | [diisi saat pengujian] |
| 3 | [fitur dari codebase] | [input] | [output yang diharapkan] | [diisi saat pengujian] |

Buat minimal 15 skenario pengujian berdasarkan fitur-fitur yang kamu temukan di codebase.

---

#### 3.8 Jadwal Penyelesaian Tugas Akhir

Buat tabel jadwal dalam format Markdown dengan kolom bulan. Sesuaikan dengan tahun 2025:

| No | Kegiatan | Jan | Feb | Mar | Apr | Mei | Jun | Jul | Ags |
|---|---|---|---|---|---|---|---|---|---|
| 1 | Studi Pustaka & Penulisan Proposal | ✓ | ✓ | | | | | | |
| 2 | Revisi & Seminar Judul | | ✓ | ✓ | | | | | |
| 3 | Observasi & Pengumpulan Data | | | ✓ | ✓ | | | | |
| 4 | Analisis & Desain Sistem | | | | ✓ | ✓ | | | |
| 5 | Implementasi/Pengkodean | | | | | ✓ | ✓ | | |
| 6 | Pengujian Sistem | | | | | | ✓ | ✓ | |
| 7 | Penulisan Tugas Akhir | | | | | | | ✓ | ✓ |
| 8 | Sidang Tugas Akhir | | | | | | | | ✓ |

---

### DAFTAR PUSTAKA

Buat placeholder daftar pustaka dengan format IEEE untuk semua referensi yang kamu sitasikan di atas. Format:

```
[1] [Penulis], "[Judul]," [Nama Jurnal/Prosiding], vol. [X], no. [X], pp. [X-X], [Tahun]. <!-- PLACEHOLDER - ganti dengan referensi asli -->
[2] ...
[3] ...
```

Untuk referensi teknologi resmi, bisa gunakan format website:
```
[X] Laravel, "Laravel - The PHP Framework For Web Artisans," [Online]. Available: https://laravel.com. [Accessed: 2025].
```

---

## CATATAN PENTING UNTUK AI CODING

1. **Baca seluruh codebase terlebih dahulu** sebelum menulis apapun. Prioritas file yang harus dibaca:
   - `routes/web.php` — untuk memahami semua endpoint dan fitur
   - `database/migrations/` — untuk memahami struktur database
   - `app/Models/` — untuk memahami relasi antar entitas
   - `app/Http/Controllers/` — untuk memahami logika bisnis
   - `app/Http/Middleware/` — untuk memahami sistem otorisasi
   - `composer.json` dan `package.json` — untuk versi teknologi yang digunakan
   - `config/` — untuk konfigurasi sistem
   - `resources/views/` — untuk memahami halaman-halaman yang ada

2. **Jangan mengarang** — tulis hanya apa yang kamu temukan di codebase. Jika ada sesuatu yang tidak bisa kamu verifikasi, tandai dengan `[PERLU VERIFIKASI]`.

3. **Gunakan bahasa Indonesia baku** untuk semua konten, kecuali istilah teknis yang lazim dalam bahasa Inggris (tulis *italic*).

4. **Untuk referensi/sitasi:** Buat placeholder yang jelas dan tandai dengan komentar HTML `<!-- PLACEHOLDER -->` agar mudah diganti oleh mahasiswa.

5. **Untuk gambar/diagram:** Jangan coba generate gambar. Cukup tulis placeholder seperti `[GAMBAR X.X: Nama Gambar — dibuat manual]`.

6. **Output akhir:** Simpan dalam satu file `.md` dengan nama `RAW_PROPOSAL_AGENDA_ONLINE_PTPN.md`.

---

## CHECKLIST SEBELUM SELESAI

Pastikan output kamu mencakup semua poin ini:

- [ ] Judul proposal (≤16 kata)
- [ ] BAB I: Latar Belakang (4–6 paragraf + min. 3 sitasi)
- [ ] BAB I: Rumusan Masalah (3–5 pertanyaan)
- [ ] BAB I: Batasan Masalah (4–6 poin)
- [ ] BAB I: Tujuan Tugas Akhir (3–4 poin)
- [ ] BAB I: Manfaat Tugas Akhir (teoritis + praktis)
- [ ] BAB I: Metodologi (tahapan SDLC)
- [ ] BAB II: Tinjauan Pustaka (4 penelitian + tabel perbandingan)
- [ ] BAB II: Dasar Teori (semua teknologi yang digunakan)
- [ ] BAB III: Analisis Kebutuhan (fungsional + non-fungsional + tabel role)
- [ ] BAB III: Arsitektur Sistem (naratif + placeholder gambar)
- [ ] BAB III: Perancangan Database (semua tabel dari migrations)
- [ ] BAB III: Perancangan UI (semua halaman utama)
- [ ] BAB III: Proses Bisnis/Algoritma (min. 4 alur)
- [ ] BAB III: Spesifikasi Teknologi (tabel lengkap dari composer.json + package.json)
- [ ] BAB III: Rencana Pengujian (min. 15 skenario)
- [ ] BAB III: Jadwal Penyelesaian (tabel 2025)
- [ ] Daftar Pustaka (semua referensi dalam format IEEE)
