# RAW PROPOSAL TUGAS AKHIR — AGENDA ONLINE PTPN
> **Catatan:** Dokumen ini adalah konten mentah berdasarkan analisis codebase. Bagian `<!-- PLACEHOLDER -->` harus diganti referensi asli. Bagian `[GAMBAR ...]` dilampirkan manual oleh mahasiswa.

---

## BAGIAN 0: JUDUL

**Rancang Bangun Sistem Informasi Agenda Online Pengelolaan Dokumen Keuangan Berbasis Web Menggunakan Laravel pada PT Perkebunan Nusantara IV Regional V**

---

## BAB I — PENDAHULUAN

### 1.1 Latar Belakang

PT Perkebunan Nusantara IV Regional V merupakan salah satu unit regional dari PT Perkebunan Nusantara IV, yaitu Badan Usaha Milik Negara (BUMN) yang bergerak di bidang usaha perkebunan kelapa sawit dan karet. Sebagai perusahaan BUMN berskala besar, PTPN IV Regional V memiliki berbagai unit kerja (bagian) yang masing-masing menghasilkan dokumen keuangan berupa Surat Perintah Pembayaran (SPP) dalam jumlah yang signifikan setiap bulannya. Dokumen-dokumen tersebut perlu dikelola secara tertib melalui alur persetujuan bertingkat yang melibatkan beberapa pihak, mulai dari unit kerja pengusul, tim verifikasi, perpajakan, akuntansi, hingga pembayaran, sebelum proses pembayaran dapat dilaksanakan [1].

Sebelum sistem ini dibangun, pengelolaan dokumen agenda keuangan di PTPN IV Regional V dilakukan secara manual dan tidak terintegrasi. Dokumen fisik berpindah dari satu meja ke meja lain tanpa catatan perpindahan yang terstruktur, sehingga sulit memantau posisi dokumen secara *real-time*. Tidak adanya sistem pencatatan status dokumen yang standar menyebabkan proses persetujuan berlangsung tanpa batas waktu yang jelas dan sering terjadi keterlambatan pembayaran yang sulit ditelusuri penyebabnya. Ketika terjadi kesalahan data, proses pengembalian dan perbaikan dokumen tidak terdokumentasi dengan baik, sehingga menyulitkan audit dan rekonsiliasi keuangan [2].

Kebutuhan akan digitalisasi proses bisnis di lingkungan BUMN semakin mendesak seiring tuntutan tata kelola perusahaan yang baik (*good corporate governance*). Sistem informasi manajemen dokumen yang terintegrasi mampu mengurangi risiko kehilangan dokumen, mempercepat siklus persetujuan, dan memberikan visibilitas penuh kepada pimpinan terhadap status seluruh dokumen yang sedang diproses. Penerapan sistem digital di lingkungan BUMN terbukti meningkatkan efisiensi operasional dan akuntabilitas organisasi [3].

Untuk menjawab permasalahan tersebut, dirancang sebuah sistem informasi bernama **Agenda Online PTPN** sebagai platform pengelolaan dokumen agenda keuangan berbasis web. Sistem ini dilengkapi fitur-fitur utama yang ditemukan dalam codebase, antara lain: pengelolaan data dokumen SPP secara digital dengan *auto-generate* nomor agenda, alur persetujuan bertingkat (*multi-level approval*) melalui sistem Inbox, paraf digital oleh Tim Verifikasi, manajemen tenggat waktu per peran, notifikasi *real-time* via *WebSocket* (Pusher), notifikasi WhatsApp via Fonnte API, impor data massal via CSV, ekspor laporan ke Excel, dasbor analitik per peran, pelacakan dokumen (*document tracking*) lengkap, serta sistem peringatan urgensi (*urgency alert*) oleh Admin/Owner.

Sistem Agenda Online PTPN dibangun menggunakan *framework* **Laravel 12** (PHP ^8.2) sebagai *backend*, **MySQL** sebagai basis data relasional, **Tailwind CSS v4** sebagai *front-end framework*, serta **Pusher** dan **Laravel Echo** untuk komunikasi *real-time*. Keamanan diperkuat dengan mekanisme **Autentikasi Dua Faktor (2FA)** menggunakan Google Authenticator melalui pustaka `pragmarx/google2fa`. Pemilihan Laravel didasarkan pada arsitektur MVC yang terstruktur, dukungan ekosistem yang matang (*Eloquent ORM*, *Queue*, *Broadcasting*), serta kemudahan pengembangan fitur kompleks secara modular.

Berdasarkan uraian di atas, penulis menyusun Tugas Akhir dengan membangun Sistem Informasi Agenda Online Pengelolaan Dokumen Keuangan Berbasis Web sebagai hasil dari kegiatan Praktik Kerja Lapangan (PKL) di PT Perkebunan Nusantara IV Regional V. Sistem ini diharapkan menjadi solusi nyata bagi permasalahan pengelolaan dokumen keuangan yang selama ini dihadapi perusahaan.

---

### 1.2 Rumusan Masalah

1. Bagaimana merancang dan membangun sistem informasi pengelolaan agenda dokumen keuangan berbasis web yang mengintegrasikan seluruh alur kerja dari unit kerja (Bagian) hingga proses pembayaran di PT Perkebunan Nusantara IV Regional V?
2. Bagaimana mengimplementasikan alur persetujuan bertingkat (*multi-level approval*) secara digital yang melibatkan peran Operator, Tim Verifikasi, Perpajakan, Akuntansi, dan Pembayaran dalam satu platform terintegrasi?
3. Bagaimana memastikan keamanan akses sistem melalui mekanisme *Role-Based Access Control* (RBAC) dan Autentikasi Dua Faktor (2FA) agar setiap pengguna hanya dapat mengakses fitur yang sesuai perannya?
4. Bagaimana menyediakan fitur pemantauan dan notifikasi *real-time* sehingga pemangku kepentingan dapat mengetahui status terkini dokumen tanpa menunggu konfirmasi manual?
5. Bagaimana menghasilkan laporan dan analitik kinerja pengelolaan dokumen yang dapat digunakan pimpinan sebagai dasar evaluasi proses bisnis keuangan di PTPN IV Regional V?

---

### 1.3 Batasan Masalah

1. Sistem hanya digunakan di lingkungan internal PT Perkebunan Nusantara IV Regional V dan tidak diperuntukkan pengguna di luar organisasi.
2. Sistem bersifat berbasis web (*web-based*) dan hanya dapat diakses melalui *browser*; tidak mencakup aplikasi *mobile* (Android/iOS).
3. Peran pengguna yang didukung terbatas pada: **Admin**, **Owner**, **Programmer**, **Operator**, **Tim Verifikasi**, **Perpajakan**, **Akuntansi**, **Pembayaran**, dan **Bagian** (delapan sub-bagian: AKN, DPM, KPL, PMO, SDM, SKH, TAN, TEP).
4. Dokumen yang dikelola terbatas pada dokumen agenda keuangan berupa SPP beserta atribut perpajakan dan akuntansi yang menyertainya.
5. Fitur notifikasi WhatsApp menggunakan layanan pihak ketiga (Fonnte API) dan memerlukan konfigurasi nomor telepon pada akun pengguna.
6. Fitur ekspor laporan tersedia dalam format Microsoft Excel dan CSV; sistem tidak mendukung ekspor PDF secara langsung.

---

### 1.4 Tujuan Tugas Akhir

1. **Merancang** sistem informasi pengelolaan agenda dokumen keuangan berbasis web yang mencakup seluruh alur kerja pengelolaan SPP di PTPN IV Regional V, dari pembuatan oleh Bagian/Operator hingga pencatatan pembayaran.
2. **Mengimplementasikan** sistem menggunakan Laravel 12 dengan fitur alur persetujuan bertingkat, sistem *inbox*, manajemen tenggat waktu, dan notifikasi *real-time* berbasis Pusher WebSocket serta WhatsApp.
3. **Menerapkan** mekanisme keamanan berupa RBAC dengan sepuluh peran pengguna dan 2FA menggunakan Google Authenticator untuk memastikan hak akses yang tepat.
4. **Menguji** sistem menggunakan metode *Black Box Testing* untuk memastikan seluruh fitur berfungsi sesuai kebutuhan fungsional yang ditetapkan.

---

### 1.5 Manfaat Tugas Akhir

**A. Manfaat Teoritis:**
- Penelitian ini dapat menjadi referensi akademik bagi mahasiswa atau peneliti lain yang mengembangkan sistem informasi manajemen dokumen berbasis web dengan Laravel, khususnya dalam implementasi alur persetujuan bertingkat dan notifikasi *real-time*.
- Memberikan kontribusi terhadap pemahaman implementasi arsitektur MVC dalam pengembangan sistem informasi untuk lingkungan BUMN.

**B. Manfaat Praktis:**

*Bagi PT Perkebunan Nusantara IV Regional V:*
- Mempercepat dan meningkatkan transparansi alur persetujuan dokumen keuangan SPP, meminimalkan keterlambatan pembayaran.
- Memberikan visibilitas penuh kepada pimpinan terhadap posisi dan status seluruh dokumen secara *real-time*.
- Mengurangi risiko kehilangan dokumen dan ketidakakuratan data melalui sistem pencatatan digital yang terstruktur.
- Menyediakan laporan dan analitik kinerja sebagai dasar evaluasi proses bisnis keuangan.

*Bagi Mahasiswa:*
- Menambah pengalaman praktis mengembangkan sistem informasi skala produksi menggunakan teknologi web modern (Laravel 12, Tailwind CSS, Pusher, 2FA).
- Meningkatkan kompetensi dalam merancang arsitektur sistem *multi-role* dengan *real-time communication* dan keamanan aplikasi web.

*Bagi Politeknik Negeri Pontianak:*
- Menjadi bukti nyata keterkaitan antara kegiatan PKL mahasiswa dengan pengembangan sistem informasi yang bermanfaat bagi industri, meningkatkan relevansi institusi di mata dunia kerja.

---

### 1.6 Metodologi Pelaksanaan Tugas Akhir

Pengembangan sistem menggunakan metodologi **SDLC (System Development Life Cycle)** dengan model **Waterfall**. Model Waterfall dipilih karena kebutuhan sistem dapat diidentifikasi secara relatif lengkap sejak awal melalui observasi langsung di PTPN IV Regional V.

**1. Analisis Kebutuhan** — Pengumpulan data melalui observasi terhadap proses pengelolaan dokumen keuangan yang berjalan dan wawancara dengan staf keuangan PTPN IV Regional V. Mengidentifikasi kebutuhan fungsional dan non-fungsional sistem.

**2. Perancangan Sistem** — Merancang arsitektur MVC, ERD dan skema basis data, antarmuka pengguna (UI/UX) per halaman dan per peran, serta *flowchart* proses bisnis.

**3. Implementasi** — Mengkodekan sistem menggunakan Laravel 12, MySQL, Tailwind CSS v4, dan Pusher secara modular (basis data → model → controller → view → middleware → routing).

**4. Pengujian** — Menguji sistem menggunakan **Black Box Testing** untuk memverifikasi fungsionalitas dan **User Acceptance Testing (UAT)** bersama staf keuangan PTPN IV Regional V.

**5. Pemeliharaan** — *Deployment* ke server produksi (Nginx), pemantauan kinerja, perbaikan *bug*, dan penyesuaian terhadap perubahan kebutuhan pasca-*deployment*.

---

## BAB II — DASAR TEORI

### 2.1 Tinjauan Pustaka

#### A. Ringkasan Penelitian Terdahulu

<!-- MAHASISWA: Ganti empat placeholder di bawah ini dengan referensi jurnal asli yang relevan -->

**Penelitian 1:** [PERLU VERIFIKASI — Nama Penulis, Tahun]
- **Judul:** Rancang Bangun Sistem Informasi Manajemen Dokumen Berbasis Web pada [Instansi]
- **Teknologi:** [teknologi yang digunakan]
- **Relevansi:** Membahas pengelolaan dokumen digital dan alur persetujuan berbasis web.
- **Perbedaan:** Belum mengimplementasikan notifikasi *real-time* WebSocket dan manajemen multi-peran kompleks seperti pada Agenda Online PTPN.

**Penelitian 2:** [PERLU VERIFIKASI — Nama Penulis, Tahun]
- **Judul:** Implementasi Sistem Persetujuan Elektronik (*e-Approval*) pada Proses Pengadaan di [Instansi]
- **Teknologi:** [teknologi yang digunakan]
- **Relevansi:** Membahas konsep *multi-level approval* secara digital untuk mempercepat proses bisnis.
- **Perbedaan:** Belum dilengkapi fitur pelacakan dokumen *real-time*, notifikasi WhatsApp, dan manajemen tenggat waktu per peran.

**Penelitian 3:** [PERLU VERIFIKASI — Nama Penulis, Tahun]
- **Judul:** Penerapan *Role-Based Access Control* (RBAC) pada Sistem Informasi [Nama Sistem]
- **Teknologi:** [teknologi yang digunakan]
- **Relevansi:** Mengkaji implementasi RBAC sebagai mekanisme kontrol akses pengguna berbasis web.
- **Perbedaan:** Tidak mengintegrasikan 2FA sebagai lapisan keamanan tambahan, tidak memiliki peran Programmer dengan akses teknis khusus.

**Penelitian 4:** [PERLU VERIFIKASI — Nama Penulis, Tahun]
- **Judul:** Transformasi Digital Proses Bisnis Keuangan pada Perusahaan BUMN Berbasis Sistem Informasi Web
- **Teknologi:** [teknologi yang digunakan]
- **Relevansi:** Membahas digitalisasi proses bisnis keuangan di lingkungan BUMN, konteks serupa dengan PTPN IV Regional V.
- **Perbedaan:** Tidak membahas WebSocket *real-time* dan integrasi WhatsApp Gateway.

#### B. Tabel Perbandingan Penelitian Terdahulu

| No | Aspek | Penelitian 1 | Penelitian 2 | Penelitian 3 | Penelitian 4 | Sistem yang Diusulkan |
|---|---|---|---|---|---|---|
| 1 | Teknologi Backend | [PERLU VERIFIKASI] | [PERLU VERIFIKASI] | [PERLU VERIFIKASI] | [PERLU VERIFIKASI] | Laravel 12, PHP ^8.2 |
| 2 | Basis Data | [PERLU VERIFIKASI] | [PERLU VERIFIKASI] | [PERLU VERIFIKASI] | [PERLU VERIFIKASI] | MySQL |
| 3 | Manajemen Peran | [PERLU VERIFIKASI] | [PERLU VERIFIKASI] | RBAC | [PERLU VERIFIKASI] | RBAC, 10 peran, custom middleware |
| 4 | Alur Persetujuan | [PERLU VERIFIKASI] | Ada | Tidak | [PERLU VERIFIKASI] | Multi-level 5 tahap + Inbox |
| 5 | Notifikasi Real-time | Tidak | Tidak | Tidak | Tidak | Ya (Pusher WebSocket + Laravel Echo) |
| 6 | Notifikasi WhatsApp | Tidak | Tidak | Tidak | Tidak | Ya (Fonnte API) |
| 7 | Two-Factor Auth (2FA) | Tidak | Tidak | Tidak | Tidak | Ya (TOTP — Google Authenticator) |
| 8 | Impor Data Massal | [PERLU VERIFIKASI] | Tidak | Tidak | [PERLU VERIFIKASI] | Ya (impor CSV) |
| 9 | Ekspor Laporan | [PERLU VERIFIKASI] | [PERLU VERIFIKASI] | Tidak | [PERLU VERIFIKASI] | Ya (Excel via maatwebsite/excel) |
| 10 | Tracking Dokumen | Tidak | Tidak | Tidak | [PERLU VERIFIKASI] | Ya (riwayat lengkap per dokumen) |

#### C. Kebaruan Sistem yang Diusulkan

1. **Alur persetujuan bertingkat terintegrasi penuh** — Satu platform mengintegrasikan seluruh rantai: Bagian → Operator → Tim Verifikasi → Perpajakan/Akuntansi → Pembayaran dengan sistem Inbox terpusat.
2. **Notifikasi multi-channel** — Kombinasi WebSocket (Pusher/Laravel Echo) dan WhatsApp (Fonnte API).
3. **Paraf digital terlacak** — Mencatat nama pemaraf dan waktu paraf secara otomatis (kolom `tanggal_paraf`, `pemaraf` di tabel `dokumens`).
4. **Sistem urgensi** — Admin/Owner dapat menandai dokumen sebagai urgen dan mengirimkan peringatan *real-time* (kolom `urgency_active`, `urgency_sent_at`).
5. **Peran Programmer khusus** — Akses ke manajemen pengguna, alat basis data, dan operasi massal yang tidak tersedia di peran lain.

---

### 2.2 Dasar Teori

#### 2.2.1 Sistem Informasi

Sistem informasi adalah kombinasi manusia, perangkat keras, perangkat lunak, jaringan komunikasi, dan sumber daya data yang mengumpulkan, mengubah, dan mendistribusikan informasi dalam sebuah organisasi. Aktivitas utama mencakup *input*, *proses*, dan *output*. Dalam organisasi bisnis, sistem informasi mendukung pengambilan keputusan, koordinasi, dan pengendalian operasional [1]. <!-- PLACEHOLDER: ganti dengan referensi yang digunakan -->

#### 2.2.2 Sistem Informasi Manajemen Dokumen

*Document Management System* (DMS) adalah sistem untuk menyimpan, mengelola, melacak, dan mendistribusikan dokumen secara digital, termasuk kontrol akses, riwayat versi, dan alur kerja (*workflow*) persetujuan. Penerapan DMS terbukti mengurangi penggunaan dokumen fisik, mempercepat proses bisnis, dan meningkatkan akurasi data [2]. <!-- PLACEHOLDER -->

#### 2.2.3 Laravel Framework

*Laravel* adalah *framework* PHP *open-source* dengan pola arsitektur **MVC** (*Model-View-Controller*). Sistem ini menggunakan **Laravel 12** (`laravel/framework ^12.0`) dengan PHP 8.2+, dilengkapi *Eloquent ORM*, *Artisan CLI*, *Queue*, *Broadcasting* (Pusher), *Middleware*, dan *Blade Template Engine* [3]. <!-- PLACEHOLDER -->

#### 2.2.4 PHP (*Hypertext Preprocessor*)

PHP adalah bahasa pemrograman *server-side scripting* untuk pengembangan web, bersifat *open-source* dan lintas platform. Proyek ini menggunakan **PHP ^8.2** yang mendukung *named arguments*, *readonly properties*, *enums*, dan peningkatan performa signifikan [4]. <!-- PLACEHOLDER -->

#### 2.2.5 MySQL

MySQL adalah RDBMS (*Relational Database Management System*) *open-source* yang menggunakan SQL. MySQL mendukung transaksi, *trigger*, dan *indexing* untuk optimasi performa. Dalam sistem ini, MySQL menyimpan seluruh data: dokumen, pengguna, riwayat aktivitas, log WhatsApp, dan konfigurasi deadline [5]. <!-- PLACEHOLDER -->

#### 2.2.6 Tailwind CSS

Tailwind CSS adalah *utility-first CSS framework* yang menyediakan kelas utilitas tingkat rendah untuk membangun antarmuka kustom langsung di HTML. Sistem ini menggunakan **Tailwind CSS v4** (`tailwindcss ^4.0.0`) dengan integrasi Vite via plugin `@tailwindcss/vite ^4.0.0` [6]. <!-- PLACEHOLDER -->

#### 2.2.7 Bootstrap

Bootstrap adalah *front-end framework* berbasis HTML, CSS, dan JavaScript yang menyediakan komponen UI responsif siap pakai. Sistem ini menggunakan **Bootstrap 5.3** (`twbs/bootstrap ^5.3`) sebagai komponen pendukung elemen UI tertentu [7]. <!-- PLACEHOLDER -->

#### 2.2.8 Pusher dan Laravel Echo

Pusher menyediakan infrastruktur WebSocket terkelola untuk komunikasi *real-time*. Laravel Echo (`laravel-echo ^2.2.6`) mempermudah langganan *channel* dari sisi klien. Kombinasi `pusher-js ^8.4.0` dan `pusher/pusher-php-server ^7.2` digunakan untuk notifikasi dokumen baru, pengembalian dokumen, dan peringatan urgensi secara *real-time* [8]. <!-- PLACEHOLDER -->

#### 2.2.9 Vite

Vite adalah *build tool* generasi berikutnya dengan *Hot Module Replacement* (HMR) sangat cepat. Proyek ini menggunakan **Vite v7** (`vite ^7.0.7`) melalui `laravel-vite-plugin ^2.0.0` untuk memproses dan mengoptimalkan aset CSS dan JavaScript [9]. <!-- PLACEHOLDER -->

#### 2.2.10 Axios

Axios (`axios ^1.11.0`) adalah pustaka JavaScript berbasis *Promise* untuk permintaan HTTP dari *browser*. Dalam sistem ini, Axios digunakan untuk komunikasi AJAX termasuk fitur *autocomplete*, *inline edit*, dan pemuatan data dinamis [10]. <!-- PLACEHOLDER -->

#### 2.2.11 *Role-Based Access Control* (RBAC)

RBAC mengatur hak akses pengguna berdasarkan peran. Setiap pengguna mendapat satu peran dengan sekumpulan izin tertentu. RBAC efektif untuk sistem keuangan BUMN dengan banyak peran [11]. <!-- PLACEHOLDER --> Diimplementasikan via *middleware* `CheckRole` (verifikasi peran *case-insensitive*) dan `CheckBagianRole` (verifikasi peran `bagian_*`).

#### 2.2.12 *Two-Factor Authentication* (2FA)

2FA mengharuskan dua bukti verifikasi: kata sandi + kode OTP dari perangkat pengguna. Sistem menggunakan `pragmarx/google2fa ^8.0` yang mengimplementasikan standar **TOTP** kompatibel Google Authenticator. Data 2FA disimpan terenkripsi di kolom `two_factor_secret` dan `two_factor_recovery_codes` tabel `users` [12]. <!-- PLACEHOLDER -->

#### 2.2.13 Maatwebsite/Excel

`maatwebsite/excel ^1.1` menyediakan antarmuka *fluent* untuk ekspor ke Excel dan impor dari CSV/Excel di Laravel. Digunakan untuk fitur ekspor laporan rekapan dokumen ke format Excel [13]. <!-- PLACEHOLDER -->

#### 2.2.14 WhatsApp Gateway (Fonnte API)

Fonnte menyediakan REST API untuk mengirimkan pesan WhatsApp secara programatik (dikonfigurasi via `FONNTE_API_TOKEN` dan `FONNTE_API_URL=https://api.fonnte.com/send`). Log pengiriman disimpan di tabel `whatsapp_notification_logs` [14]. <!-- PLACEHOLDER -->

#### 2.2.15 *Black Box Testing*

*Black Box Testing* menguji fungsionalitas sistem dari perspektif pengguna akhir tanpa memperhatikan kode sumber — memberikan *input* dan memverifikasi *output* terhadap spesifikasi kebutuhan fungsional [15]. <!-- PLACEHOLDER -->

#### 2.2.16 SDLC Model Waterfall

Model Waterfall adalah SDLC sekuensial dengan fase: analisis kebutuhan → desain → implementasi → pengujian → pemeliharaan. Cocok saat kebutuhan sudah jelas dan stabil sejak awal pengembangan [16]. <!-- PLACEHOLDER -->

---

## BAB III — RANCANGAN AWAL SISTEM

### 3.1 Analisis Kebutuhan Sistem

#### A. Kebutuhan Fungsional

Kebutuhan fungsional berikut diidentifikasi berdasarkan analisis `routes/web.php`, *controllers*, *migrations*, dan file `laporan_fitur.txt`:

| Kode | Kebutuhan Fungsional | Sumber Verifikasi |
|---|---|---|
| KF-01 | Sistem menyediakan halaman login dengan validasi *username* dan *password*, serta redirect ke dasbor sesuai peran pengguna | `routes/web.php` — `GET /login`, `POST /login`; `User::DASHBOARD_ROUTES` |
| KF-02 | Pengguna dapat mengaktifkan, mengkonfigurasi, dan menonaktifkan 2FA menggunakan Google Authenticator; sistem menyediakan kode pemulihan (*recovery codes*) | `routes/web.php` — `GET|POST /2fa/*`; migrasi `add_two_factor_columns_to_users_table` |
| KF-03 | Pengguna dapat mengubah profil (*username*, email, kata sandi) melalui halaman pengaturan akun | `routes/web.php` — `GET|PUT /profile`; `views/profile/` |
| KF-04 | Operator dan Bagian dapat membuat, melihat, mengubah, dan menghapus dokumen agenda SPP (CRUD) | `routes/web.php` — `GET|POST /documents`, `/documents/{id}/edit`; `views/operator/`, `views/bagian/` |
| KF-05 | Sistem secara otomatis menghasilkan nomor agenda berikutnya berdasarkan nomor tertinggi pada tahun berjalan | `routes/web.php` — `GET /api/dokumen/next-nomor-agenda` |
| KF-06 | Sistem menyediakan *autocomplete* untuk kolom: nama penerima pembayaran, pengirim dokumen, uraian SPP, nomor PO, dan nomor PR | `routes/web.php` — `GET /api/autocomplete/*` |
| KF-07 | Operator dapat mengirim dokumen ke Tim Verifikasi, satu per satu atau secara massal (*bulk send*) | `routes/web.php` — `POST /documents/{id}/send-to-verifikasi`, `POST /documents/bulk-send` |
| KF-08 | Setiap peran memiliki kotak masuk (*inbox*) untuk menerima, melihat detail, menyetujui, atau menolak dokumen yang masuk | `routes/web.php` — `GET /inbox`, `POST /inbox/{id}/approve`, `POST /inbox/{id}/reject`; `views/inbox/` |
| KF-09 | Tim Verifikasi, Perpajakan, Akuntansi, dan Pembayaran dapat menyetujui beberapa dokumen sekaligus (*bulk approve*) | `routes/web.php` — `POST /inbox/bulk-approve`; `views/inbox/` |
| KF-10 | Dokumen mengalir secara berurutan: [Bagian →] Operator → Tim Verifikasi → Perpajakan → Akuntansi → Pembayaran | `routes/web.php` — endpoint send ke setiap peran; tabel `dokumen_statuses`, `dokumen_role_data` |
| KF-11 | Setiap peran dapat menolak dokumen dan mengembalikannya ke peran sebelumnya dengan alasan penolakan | `routes/web.php` — `POST /documents/{id}/return`; kolom `alasan_pengembalian`, `return_reason` |
| KF-12 | Tim Verifikasi dapat memberikan paraf; sistem mencatat nama pemaraf dan waktu paraf secara otomatis | `routes/web.php` — `POST /documents/{id}/paraf`; kolom `tanggal_paraf`, `pemaraf` di tabel `dokumens` |
| KF-13 | Setiap peran dapat menetapkan tenggat waktu; sistem memiliki konfigurasi tenggat *default* per peran (3 hari) | `routes/web.php` — `POST /documents/{id}/set-deadline`; tabel `role_deadline_configs` |
| KF-14 | Sistem mengirim notifikasi langsung ke *browser* pengguna saat dokumen baru masuk, dikembalikan, atau ada urgensi (WebSocket) | `routes/web.php` — Pusher *broadcasting*; `pusher/pusher-php-server ^7.2`, `laravel-echo ^2.2.6` |
| KF-15 | Sistem mengirim notifikasi WhatsApp via Fonnte API ketika dokumen melewati tenggat waktu | `.env.example` — `FONNTE_API_TOKEN`; tabel `whatsapp_notification_logs` |
| KF-16 | Admin/Owner dapat menandai dokumen sebagai urgen; peran terkait menerima notifikasi urgensi | `routes/web.php` — `POST /owner/documents/{id}/urgency`; kolom `urgency_active`, `urgency_sent_at` |
| KF-17 | Seluruh pengguna dapat melihat riwayat lengkap perjalanan dokumen melalui halaman *tracking* | `routes/web.php` — `GET /tracking/{nomor_agenda}`; tabel `document_trackings`, `dokumen_activity_logs` |
| KF-18 | Setiap peran memiliki dasbor dengan statistik dokumen (total, sedang diproses, selesai) yang relevan | `routes/web.php` — `/dashboard`, `/documents/verifikasi`, `/dashboard/perpajakan`, dst; `views/operator/`, `views/team_verifikasi/`, dll. |
| KF-19 | Owner/Admin memiliki dasbor *God View* untuk memantau seluruh dokumen dan kinerja semua peran | `routes/web.php` — `GET /owner/home`; `views/owner/` |
| KF-20 | Setiap peran dapat mengakses laporan/rekapan dokumen dan mengekspornya ke Excel | `routes/web.php` — `GET /*/rekapan`, `GET /*/export`; `maatwebsite/excel ^1.1` |
| KF-21 | Owner/Admin dapat melihat dan mengekspor laporan keterlambatan dokumen per peran | `routes/web.php` — `GET /owner/laporan-keterlambatan` |
| KF-22 | Owner/Admin mengakses dasbor analitik kinerja pemrosesan dokumen per peran | `routes/web.php` — `GET /owner/analytics`; `views/owner/` |
| KF-23 | Operator dapat mengimpor data dokumen secara massal dari file CSV | `routes/web.php` — `GET|POST /documents/import-csv`; `views/operator/` |
| KF-24 | Pembayaran dapat mengimpor data pembayaran dari CSV | `routes/web.php` — `GET|POST /dashboard/pembayaran/import-csv` |
| KF-25 | Pengguna dapat mencari dan menyaring dokumen dengan banyak parameter serta menyimpan konfigurasi sebagai *preset* | `routes/web.php` — `GET|POST /search-presets`; tabel `search_presets` |
| KF-26 | Pengguna tertentu dapat mengubah data dokumen secara *inline* dari tampilan tabel tanpa berpindah halaman | `routes/web.php` — `PATCH /api/dokumen/{id}/inline-edit` |
| KF-27 | Tim Verifikasi dapat mengembalikan dokumen ke Bagian; laporan dokumen yang dikembalikan tersedia | `routes/web.php` — `POST /documents/{id}/return-to-bagian`; `views/team_verifikasi/` |
| KF-28 | Pembayaran dapat mengunggah tautan bukti pembayaran pada dokumen yang telah dibayar | `routes/web.php` — PATCH pembayaran; kolom `link_bukti_pembayaran`, `tanggal_dibayar` |
| KF-29 | Programmer dapat melihat dan mengubah data pengguna (nama, *username*, peran) | `routes/web.php` — `GET|POST /programmer/users`; `views/programmer/` |
| KF-30 | Programmer dapat menggunakan alat basis data dan melakukan operasi massal | `routes/web.php` — `GET /programmer/database-tools`, `/programmer/bulk-operations` |

#### B. Kebutuhan Non-Fungsional

| Kode | Aspek | Kebutuhan |
|---|---|---|
| KNF-01 | Keamanan | Autentikasi *session*-based Laravel + RBAC via *middleware* `CheckRole`/`CheckBagianRole` + opsional 2FA TOTP. CSRF token di semua form. HTTP Security Headers via *middleware* `SecurityHeaders`. Proteksi manipulasi URL via `PreventUrlManipulation` |
| KNF-02 | Performa | *Indexing* basis data dioptimalkan pada kolom yang sering diquery (`status`, `current_handler`, `urgency_active`, `dokumen_id`). *Database caching* (`CACHE_STORE=database`). Queue asinkron untuk notifikasi WhatsApp (`QUEUE_CONNECTION=database`) |
| KNF-03 | Ketersediaan | Sistem di-*deploy* menggunakan Nginx sebagai web server. Tersedia selama jam kerja operasional PTPN IV Regional V |
| KNF-04 | Kegunaan | Antarmuka responsif berbasis Tailwind CSS v4. Fitur *autocomplete* dan auto-generate nomor agenda meminimalkan input manual. Modal *popup* untuk detail dokumen tanpa perpindahan halaman |
| KNF-05 | Kompatibilitas | Dapat diakses melalui *browser* modern (Chrome, Firefox, Edge) pada Windows, Linux, macOS |
| KNF-06 | Auditabilitas | Setiap perubahan status dan perpindahan dokumen dicatat otomatis di `document_trackings` dan `dokumen_activity_logs`. Log aktivitas *viewing/editing* disimpan di `document_activities` |

#### C. Daftar Peran Pengguna (Role)

| No | Kode Role | Nama Tampilan | Deskripsi Singkat | Dasbor Default |
|---|---|---|---|---|
| 1 | `admin` | Administrator | Mengelola seluruh sistem; akses setara Owner | `/owner/home` |
| 2 | `owner` | Owner/Pimpinan | *God View*: pantau semua dokumen & peran, analitik, urgensi | `/owner/home` |
| 3 | `programmer` | Programmer | Manajemen pengguna, alat DB, operasi massal teknis | `/programmer/dashboard` |
| 4 | `operator` | Operator | Input dokumen SPP, kirim ke Tim Verifikasi, impor CSV | `/dashboard` |
| 5 | `team_verifikasi` | Tim Verifikasi | Verifikasi & paraf dokumen, teruskan ke Perpajakan/Akuntansi, kembalikan ke Bagian | `/documents/verifikasi` |
| 6 | `perpajakan` | Perpajakan | Proses aspek perpajakan (faktur, DPP, PPN), teruskan ke Akuntansi/Pembayaran | `/documents/perpajakan` |
| 7 | `akutansi` | Akuntansi | Proses aspek akuntansi, teruskan ke Pembayaran | `/documents/akutansi` |
| 8 | `pembayaran` | Pembayaran | Catat pembayaran, unggah bukti, impor CSV pembayaran | `/dashboard/pembayaran` |
| 9 | `bagian_akn` | Bagian AKN | Buat & kirim dokumen unit kerja AKN ke Operator/Tim Verifikasi | `/bagian/dashboard` |
| 9 | `bagian_dpm` | Bagian DPM | Buat & kirim dokumen unit kerja DPM ke Operator/Tim Verifikasi | `/bagian/dashboard` |
| 9 | `bagian_kpl` | Bagian KPL | Buat & kirim dokumen unit kerja KPL ke Operator/Tim Verifikasi | `/bagian/dashboard` |
| 9 | `bagian_pmo` | Bagian PMO | Buat & kirim dokumen unit kerja PMO ke Operator/Tim Verifikasi | `/bagian/dashboard` |
| 9 | `bagian_sdm` | Bagian SDM | Buat & kirim dokumen unit kerja SDM ke Operator/Tim Verifikasi | `/bagian/dashboard` |
| 9 | `bagian_skh` | Bagian SKH | Buat & kirim dokumen unit kerja SKH ke Operator/Tim Verifikasi | `/bagian/dashboard` |
| 9 | `bagian_tan` | Bagian TAN | Buat & kirim dokumen unit kerja TAN ke Operator/Tim Verifikasi | `/bagian/dashboard` |
| 9 | `bagian_tep` | Bagian TEP | Buat & kirim dokumen unit kerja TEP ke Operator/Tim Verifikasi | `/bagian/dashboard` |

---

### 3.2 Arsitektur Sistem

#### A. Pola Arsitektur

Sistem menggunakan pola **MVC (Model-View-Controller)** yang difasilitasi Laravel 12:
- **Model** — Merepresentasikan data dan logika bisnis; berinteraksi dengan MySQL via *Eloquent ORM* (contoh: `Dokumen`, `User`, `DokumenStatus`, `DokumenRoleData`, `DocumentTracking`).
- **View** — Lapisan presentasi menggunakan *Blade Template Engine* dengan Tailwind CSS v4 dan Bootstrap 5.3 (direktori `resources/views/`).
- **Controller** — Memproses permintaan HTTP dan mengorkestrasi interaksi Model-View (direktori `app/Http/Controllers/`).

#### B. Komponen Arsitektur

- **Web Browser (Client)** — Pengguna mengakses sistem via HTTPS. Aset di-*bundle* Vite v7.
- **Nginx (Web Server)** — Menerima HTTP/HTTPS dan meneruskan ke PHP-FPM.
- **Laravel Application** — Routing (`routes/web.php`) → Middleware (autentikasi, RBAC, `SecurityHeaders`, `PreventUrlManipulation`) → Controller → Model → Blade View.
- **MySQL Database** — Penyimpanan data persistan (20+ tabel).
- **Laravel Queue** (`QUEUE_CONNECTION=database`) — Menangani tugas asinkron: notifikasi WhatsApp, pemrosesan *auto-forward* (`dokumen_auto_forward_queue`).
- **Pusher Cloud (WebSocket)** — Menerima *broadcast event* dari Laravel dan mendistribusikan notifikasi *real-time* ke klien via `laravel-echo` + `pusher-js`.
- **Fonnte API (WhatsApp Gateway)** — Layanan eksternal untuk pengiriman notifikasi WhatsApp deadline; dipanggil via Laravel Queue.

#### C. Alur Request

```
Browser → Nginx → PHP-FPM → Laravel Router (web.php)
  → Middleware [auth, RBAC, CSRF, SecurityHeaders]
  → Controller
    → Model (Eloquent ORM) ↔ MySQL
    → [opsional] Broadcast Event → Pusher → Browser lain (real-time)
    → [opsional] Queue Job → Fonnte API (WhatsApp)
  → Blade View → HTML Response → Browser
```

[GAMBAR 3.1: Diagram Arsitektur Sistem Agenda Online PTPN — dibuat manual]

---

### 3.3 Perancangan Basis Data

#### A. Entity Relationship Diagram (ERD)

[GAMBAR 3.2: ERD Sistem Agenda Online PTPN — dibuat manual berdasarkan daftar tabel di bawah]

Relasi antar entitas utama:
- `users` 1—N `search_presets`
- `users` 1—N `document_activities`
- `users` 1—N `whatsapp_notification_logs`
- `dokumens` 1—N `dokumen_pos`
- `dokumens` 1—N `dokumen_prs`
- `dokumens` 1—N `dibayar_kepadas`
- `dokumens` 1—N `dokumen_statuses`
- `dokumens` 1—N `dokumen_role_data`
- `dokumens` 1—N `document_trackings`
- `dokumens` 1—N `dokumen_activity_logs`
- `dokumens` 1—N `document_activities`
- `dokumens` 1—N `whatsapp_notification_logs`
- `dokumens` 1—1 `dokumen_auto_forward_queue`
- `roles` 1—N `dokumen_statuses` (via `role_code`)
- `roles` 1—N `dokumen_role_data` (via `role_code`)
- `roles` 1—1 `role_deadline_configs`

#### B. Skema Tabel

---

**Tabel `users`** — Data pengguna sistem

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint unsigned PK | Primary key |
| username | varchar(255) UNIQUE | Username login |
| name | varchar(255) | Nama lengkap |
| email | varchar(255) UNIQUE | Email |
| password | varchar(255) | Password hash bcrypt |
| role | varchar(255) INDEX | Peran pengguna |
| bagian_code | varchar(255) NULL | Kode bagian untuk peran `bagian_*` |
| phone_number | varchar(255) NULL | Nomor telepon WhatsApp |
| two_factor_enabled | boolean DEFAULT false | Status 2FA |
| two_factor_secret | text NULL | Secret TOTP (terenkripsi) |
| two_factor_confirmed_at | timestamp NULL | Waktu konfirmasi 2FA |
| two_factor_recovery_codes | text NULL | Recovery codes (terenkripsi) |
| table_columns_preferences | json NULL | Preferensi tampilan kolom tabel |
| remember_token | varchar(100) NULL | Token "Ingat Saya" |
| created_at / updated_at | timestamp | Timestamps |

---

**Tabel `sessions`** — Sesi login aktif

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | varchar(255) PK | ID sesi |
| user_id | bigint unsigned NULL INDEX | FK ke users.id |
| ip_address | varchar(45) NULL | Alamat IP |
| user_agent | text NULL | Browser info |
| payload | longtext | Data sesi |
| last_activity | integer INDEX | Unix timestamp aktivitas terakhir |

---

**Tabel `password_reset_tokens`** — Token reset password

| Kolom | Tipe | Keterangan |
|---|---|---|
| email | varchar(255) PK | Email pengguna |
| token | varchar(255) | Token reset |
| created_at | timestamp NULL | Waktu pembuatan |

---

**Tabel `dokumens`** — Tabel utama dokumen agenda SPP

| Kelompok Kolom | Kolom Utama | Tipe & Keterangan |
|---|---|---|
| **Identitas** | id | bigint unsigned PK AI |
| | nomor_agenda | varchar UNIQUE — nomor agenda unik per tahun |
| | created_by | varchar INDEX — peran pembuat (`operator`/`bagian_*`) |
| | current_handler | varchar INDEX — peran pemegang dokumen saat ini |
| **Data SPP** | bulan | varchar — bulan dokumen |
| | tahun | integer — tahun dokumen |
| | tanggal_masuk | datetime — tanggal masuk dokumen |
| | nomor_spp | varchar — nomor SPP |
| | tanggal_spp | datetime — tanggal SPP |
| | uraian_spp | text — uraian pekerjaan/pembayaran |
| | nilai_rupiah | decimal(15,2) — nilai nominal (Rp) |
| | kategori | varchar — kategori dokumen |
| | jenis_dokumen | varchar — jenis dokumen |
| | jenis_sub_pekerjaan | varchar NULL — sub-jenis pekerjaan |
| | jenis_pembayaran | varchar NULL — jenis pembayaran |
| | dibayar_kepada | varchar NULL — nama penerima |
| | no_berita_acara | varchar NULL — nomor berita acara |
| | tanggal_berita_acara | date NULL |
| | no_spk | varchar NULL — nomor SPK |
| | tanggal_spk / tanggal_berakhir_spk | date NULL |
| | nomor_miro | varchar NULL — nomor MIRO |
| | tanggal_miro | date NULL |
| | kebun | varchar NULL — nama kebun |
| | status | varchar DEFAULT 'sedang diproses' — status alur kerja |
| | keterangan | text NULL |
| **Workflow** | sent_to_team_verifikasi_at | timestamp NULL |
| | processed_at | timestamp NULL |
| | returned_to_operator_at | timestamp NULL |
| | deadline_at / deadline_days / deadline_note | tenggat waktu Tim Verifikasi |
| **Pengembalian** | alasan_pengembalian | text NULL |
| | return_reason / returned_by / returned_from | pengembalian terpadu |
| | returned_at | timestamp NULL |
| | target_department / return_to_department | tujuan pengembalian ke bagian |
| **Paraf** | tanggal_paraf | timestamp NULL — waktu paraf |
| | pemaraf | varchar(50) NULL — nama pemaraf |
| | tanggal_selesai_diproses | timestamp NULL |
| **Perpajakan** | npwp / alamat_pembeli / no_kontrak | data wajib pajak |
| | no_invoice / tanggal_invoice | data invoice |
| | dpp_invoice / ppn_invoice / dpp_ppn_invoice | decimal(20,2) |
| | no_faktur / tanggal_faktur | data faktur pajak |
| | dpp_faktur / ppn_faktur / selisih_pajak | decimal(20,2) |
| | jenis_pph / dpp_pph / ppn_terhutang | PPH |
| | komoditi_perpajakan / keterangan_pajak | teks perpajakan |
| | penggantian_pajak / dpp_penggantian / ppn_penggantian / selisih_ppn | decimal(20,2) |
| | status_perpajakan | enum(sedang_diproses, selesai) NULL |
| | link_dokumen_pajak | text NULL |
| | tanggal_selesai_verifikasi_pajak / tanggal_pengajuan_pajak | date NULL |
| | sent_to_perpajakan_at / processed_perpajakan_at / returned_from_perpajakan_at | timestamp NULL |
| | deadline_perpajakan_at / deadline_perpajakan_days / deadline_perpajakan_note | tenggat Perpajakan |
| **Akuntansi** | sent_to_akutansi_at / processed_akutansi_at / returned_from_akutansi_at | timestamp NULL |
| **Pembayaran** | sent_to_pembayaran_at | timestamp NULL |
| | status_pembayaran | enum(siap_dibayar, sudah_dibayar) NULL |
| | tanggal_dibayar | date NULL |
| | link_bukti_pembayaran | text NULL |
| | auto_forwarded_at | timestamp NULL — auto-forward via MySQL trigger |
| **Inbox Approval** | inbox_approval_status / inbox_approval_for / inbox_approved_at / inbox_approved_by | data persetujuan inbox |
| **Urgensi** | urgency_active | boolean DEFAULT false INDEX |
| | urgency_sent_at | timestamp NULL |
| | urgency_sent_by | bigint NULL |
| **CSV Import** | imported_from_csv / csv_import_batch_id / csv_import_date | tracking impor CSV |
| **Timestamps** | created_at / updated_at | timestamp |

---

**Tabel `dokumen_pos`** — Nomor PO terkait dokumen (1-N)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint unsigned PK AI | |
| dokumen_id | bigint unsigned FK→dokumens(cascade) | |
| nomor_po | varchar(255) | Nomor Purchase Order |
| created_at / updated_at | timestamp | |

---

**Tabel `dokumen_prs`** — Nomor PR terkait dokumen (1-N)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint unsigned PK AI | |
| dokumen_id | bigint unsigned FK→dokumens(cascade) | |
| nomor_pr | varchar(255) | Nomor Purchase Request |
| created_at / updated_at | timestamp | |

---

**Tabel `dibayar_kepadas`** — Nama penerima pembayaran per dokumen

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint unsigned PK AI | |
| dokumen_id | bigint unsigned FK→dokumens(cascade) | |
| nama_penerima | varchar(255) | Nama penerima pembayaran |
| created_at / updated_at | timestamp | |

---

**Tabel `roles`** — Definisi peran dalam alur kerja

| Kolom | Tipe | Keterangan |
|---|---|---|
| code | varchar(50) PK | Kode peran (operator, team_verifikasi, perpajakan, akutansi, pembayaran) |
| name | varchar(100) | Nama tampilan |
| sequence | integer DEFAULT 0 | Urutan dalam alur kerja |
| created_at / updated_at | timestamp | |

*Data awal (seeder):* operator(seq:1), team_verifikasi(seq:2), perpajakan(seq:3), akutansi(seq:4), pembayaran(seq:5)

---

**Tabel `dokumen_statuses`** — Status dokumen per peran (jejak approval)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint unsigned PK AI | |
| dokumen_id | bigint unsigned FK→dokumens INDEX | |
| role_code | varchar(50) FK→roles INDEX | |
| status | enum(pending,received,processing,approved,rejected,completed,returned) DEFAULT pending | |
| status_changed_at | datetime NOT NULL | |
| changed_by | varchar(100) NULL | |
| notes | text NULL | |
| created_at / updated_at | timestamp | |

*Unique: (dokumen_id, role_code)*

---

**Tabel `dokumen_role_data`** — Data pemrosesan spesifik per peran per dokumen

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint unsigned PK AI | |
| dokumen_id | bigint unsigned FK→dokumens | |
| role_code | varchar(50) FK→roles | |
| received_at | datetime NULL | Waktu dokumen diterima peran ini |
| processed_at | datetime NULL | Waktu selesai diproses |
| deadline_at | datetime NULL | Tenggat waktu |
| deadline_days | integer NULL | Jumlah hari tenggat |
| deadline_note | varchar(500) NULL | |
| role_specific_data | json NULL | Data spesifik peran (format JSON) |
| created_at / updated_at | timestamp | |

*Unique: (dokumen_id, role_code)*

---

**Tabel `bagians`** — Definisi unit kerja/bagian organisasi

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint unsigned PK AI | |
| kode | varchar(10) UNIQUE | Kode singkat (AKN, DPM, KPL, PMO, SDM, SKH, TAN, TEP) |
| nama | varchar(100) | Nama lengkap bagian |
| deskripsi | text NULL | |
| is_active | boolean DEFAULT true | |
| created_at / updated_at | timestamp | |

---

**Tabel `bidangs`** — Tabel bidang/departemen (lama, referensi historis)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint unsigned PK AI | |
| kode_bidang | varchar UNIQUE INDEX | Kode bidang (DPM, SKH, SDM, TEP, KPL, AKN, TAN) |
| nama_bidang | varchar(255) | Nama lengkap bidang |
| deskripsi | text NULL | |
| is_active | boolean DEFAULT true | |
| created_at / updated_at | timestamp | |

---

**Tabel `document_trackings`** — Riwayat aksi pada dokumen (audit trail)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint unsigned PK AI | |
| document_id | bigint unsigned FK→dokumens INDEX | |
| action | varchar(100) | Jenis aksi (created, sent, deadline_set, approved, rejected, dll.) |
| actor | varchar(100) INDEX | Peran pelaku |
| metadata | text NULL | Konteks tambahan (JSON) |
| action_at | timestamp INDEX | Waktu aksi |
| created_at / updated_at | timestamp | |

---

**Tabel `dokumen_activity_logs`** — Log aktivitas detail per dokumen

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint unsigned PK AI | |
| dokumen_id | bigint unsigned FK→dokumens | |
| stage | varchar(255) NULL | Tahap alur kerja |
| action | varchar(255) | Jenis aksi |
| action_description | varchar(255) | Deskripsi bahasa Indonesia |
| performed_by | varchar(255) NULL | Peran pelaku |
| details | text NULL | Detail JSON/teks |
| action_at | timestamp INDEX | Waktu aksi |
| created_at / updated_at | timestamp | |

---

**Tabel `document_activities`** — Aktivitas pengguna aktif (viewing/editing)

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint unsigned PK AI | |
| dokumen_id | bigint unsigned FK→dokumens INDEX | |
| user_id | bigint unsigned FK→users | |
| activity_type | varchar(255) | 'viewing' atau 'editing' |
| last_activity_at | timestamp INDEX | Waktu aktivitas terakhir |
| created_at / updated_at | timestamp | |

*Unique: (dokumen_id, user_id, activity_type)*

---

**Tabel `role_deadline_configs`** — Konfigurasi tenggat waktu default per peran

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint unsigned PK AI | |
| role_code | varchar(50) UNIQUE FK→roles(cascade) | |
| default_deadline_days | integer DEFAULT 3 | Hari tenggat default |
| description | text NULL | |
| is_active | boolean DEFAULT true | |
| created_at / updated_at | timestamp | |

*Data awal:* team_verifikasi(3 hari), perpajakan(3 hari), akutansi(3 hari)

---

**Tabel `search_presets`** — Preset pencarian tersimpan per pengguna

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint unsigned PK AI | |
| user_id | bigint unsigned FK→users(cascade) INDEX | |
| name | varchar(255) | Nama preset (mis: "Dokumen Urgen") |
| role | varchar(255) DEFAULT 'team_verifikasi' | Peran pemilik preset |
| filters | json | Konfigurasi filter |
| usage_count | integer DEFAULT 0 | Frekuensi penggunaan |
| last_used_at | timestamp NULL INDEX | |
| created_at / updated_at | timestamp | |

---

**Tabel `whatsapp_notification_logs`** — Log pengiriman notifikasi WhatsApp

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint unsigned PK AI | |
| dokumen_id | bigint unsigned FK→dokumens INDEX | |
| role_code | varchar(50) INDEX | |
| user_id | bigint unsigned NULL FK→users | |
| phone_number | varchar(20) | Nomor telepon tujuan |
| message_type | varchar(20) | 'warning', 'danger', 'overdue' |
| message | text NULL | Isi pesan |
| status | varchar(20) DEFAULT 'pending' | 'pending'/'success'/'failed' |
| response | text NULL | Respons API |
| sent_at | timestamp NULL INDEX | |
| created_at / updated_at | timestamp | |

---

**Tabel `welcome_messages`** — Pesan sambutan per modul

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint unsigned PK AI | |
| module | varchar INDEX | Nama modul |
| message | varchar | Isi pesan |
| type | varchar DEFAULT 'general' | Jenis pesan |
| is_active | boolean DEFAULT true INDEX | |
| created_at / updated_at | timestamp | |

*Unique: (module, type)*

---

**Tabel `dokumen_auto_forward_queue`** — Antrian auto-forward via MySQL trigger

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint unsigned PK AI | |
| dokumen_id | bigint unsigned UNIQUE FK→dokumens | |
| triggered_at | timestamp | Waktu trigger dipicu |
| status | enum(pending,processing,done,failed) DEFAULT pending INDEX | |
| processed_at | timestamp NULL | Waktu diproses scheduler |
| error_message | text NULL | |
| created_at / updated_at | timestamp | |

---

**Tabel `payment_logs`** — Log riwayat pembayaran TU TK

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint unsigned PK AI | |
| tu_tk_kontrol | bigint unsigned INDEX | FK ke tu_tk_2023.KONTROL |
| payment_sequence | integer DEFAULT 1 | Urutan pembayaran (1–6) |
| tanggal_bayar | date | |
| jumlah | decimal(15,2) | |
| keterangan | varchar NULL | |
| created_by | varchar NULL | User pembuat |
| created_at / updated_at | timestamp | |

---

**Tabel `document_position_trackings`** — Riwayat perubahan posisi dokumen TU TK

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint unsigned PK AI | |
| tu_tk_kontrol | bigint unsigned INDEX | FK ke tu_tk_2023.KONTROL |
| posisi_lama | varchar NULL | Posisi sebelum perubahan |
| posisi_baru | varchar INDEX | Posisi sesudah perubahan |
| changed_by | varchar NULL | User pengubah |
| keterangan | text NULL | |
| changed_at | timestamp | Waktu perubahan |
| created_at / updated_at | timestamp | |

---

*Catatan: Tabel `tu_tk_dokumens`, `tu_tk_2023`, `tu_tk_pupuks`, `tu_tk_vd_2023`, `tu_tk_tan_2023`, `sync_logs` merupakan tabel data TU TK (Tanda Uang Tanaman Kelapa Sawit) yang merupakan fitur terpisah dari alur dokumen SPP utama. Detail skema mengikuti model `TuTk`, `TuTkPupuk`, `TuTkVd`, `TuTkTan` di direktori `app/Models/`.*

---

### 3.4 Perancangan Antarmuka Pengguna (UI/UX)

Antarmuka pengguna dirancang menggunakan **Tailwind CSS v4** dan **Bootstrap 5.3** dengan prinsip responsif dan konsistensi visual antar peran. Berikut daftar halaman utama yang ditemukan dari direktori `resources/views/`:

#### A. Halaman Autentikasi (`views/auth/`)

| Halaman | Deskripsi |
|---|---|
| Login | Form *username* + *password*; auto-redirect ke dasbor sesuai peran setelah berhasil |
| 2FA Setup | Tampilan QR code untuk dipindai Google Authenticator; input kode verifikasi |
| 2FA Verify | Form input kode OTP 6 digit saat login |
| 2FA Recovery | Form input kode pemulihan (*recovery code*) |
| Reset Password | Form reset kata sandi via email |

[GAMBAR 3.3: Tampilan Halaman Login — dibuat manual]
[GAMBAR 3.4: Tampilan Halaman Setup 2FA — dibuat manual]

#### B. Halaman Operator (`views/operator/`)

| Halaman | Deskripsi |
|---|---|
| Dasbor (`/dashboard`) | Kartu statistik: total dokumen, sedang diproses, selesai, dikembalikan |
| Daftar Dokumen | Tabel dokumen dengan kolom: no. agenda, no. SPP, uraian, nilai (Rp), status, tanggal |
| Tambah Dokumen | Form lengkap dengan validasi: input SPP, *autocomplete* penerima/uraian/PO/PR, auto-generate nomor agenda |
| Edit Dokumen | Form edit data dokumen yang sudah ada |
| Detail Dokumen | Modal *popup* tampilan detail lengkap dokumen |
| Impor CSV | Form *upload* file CSV untuk impor data dokumen massal |
| Laporan/Rekapan | Tabel rekapan dokumen per periode; tombol ekspor ke Excel |

[GAMBAR 3.5: Tampilan Dasbor Operator — dibuat manual]
[GAMBAR 3.6: Tampilan Form Tambah Dokumen dengan Autocomplete — dibuat manual]
[GAMBAR 3.7: Tampilan Daftar Dokumen Operator — dibuat manual]

#### C. Halaman Tim Verifikasi (`views/team_verifikasi/`)

| Halaman | Deskripsi |
|---|---|
| Dasbor Verifikasi (`/documents/verifikasi`) | Statistik dokumen masuk, diproses, diparaf, diteruskan |
| Daftar Dokumen | Tabel dengan kolom tambahan: deadline, paraf, status per peran |
| Inbox | Daftar dokumen menunggu persetujuan; tombol Setujui/Tolak per dokumen; *bulk approve* |
| Form Paraf | Dialog konfirmasi paraf dengan input nama pemaraf |
| Dokumen Dikembalikan ke Bagian | Laporan dokumen yang dikembalikan ke unit kerja |
| Laporan/Rekapan | Rekapan dokumen per periode dengan ekspor Excel |

[GAMBAR 3.8: Tampilan Inbox Tim Verifikasi — dibuat manual]
[GAMBAR 3.9: Tampilan Fitur Paraf Dokumen — dibuat manual]

#### D. Halaman Perpajakan (`views/perpajakan/`)

| Halaman | Deskripsi |
|---|---|
| Dasbor Perpajakan (`/documents/perpajakan`) | Statistik dokumen: masuk, sedang diproses, selesai |
| Daftar Dokumen | Tabel dengan kolom perpajakan: no. faktur, DPP, PPN, status pajak |
| Form Input Perpajakan | Form pengisian data: NPWP, invoice, faktur pajak, DPP, PPN, komoditi, dll. |
| Inbox | Daftar dokumen menunggu persetujuan Perpajakan |
| Laporan/Rekapan | Rekapan dengan ekspor Excel |

[GAMBAR 3.10: Tampilan Form Input Data Perpajakan — dibuat manual]

#### E. Halaman Akuntansi (`views/akutansi/`)

| Halaman | Deskripsi |
|---|---|
| Dasbor Akuntansi | Statistik dokumen per status |
| Daftar Dokumen | Tabel dokumen di tahap Akuntansi |
| Inbox | Persetujuan dokumen masuk |
| Laporan/Rekapan | Rekapan dengan ekspor Excel |

#### F. Halaman Pembayaran (`views/pembayaran/` dan `views/pembayaranNEW/`)

| Halaman | Deskripsi |
|---|---|
| Dasbor Pembayaran | Statistik + ringkasan rekapan bulanan (klik untuk rincian per minggu) |
| Daftar Dokumen | Tabel dokumen dengan status pembayaran (siap dibayar / sudah dibayar) |
| Form Pembayaran | Input tanggal bayar dan tautan bukti pembayaran |
| Impor CSV | Impor data pembayaran dari CSV |
| Laporan/Rekapan | Rekapan per bulan/minggu dengan ekspor Excel |

[GAMBAR 3.11: Tampilan Dasbor Pembayaran dengan Ringkasan Bulanan — dibuat manual]

#### G. Halaman Owner/Admin (`views/owner/`)

| Halaman | Deskripsi |
|---|---|
| Dasbor Owner (`/owner/home`) | Statistik keseluruhan per peran + statistik per unit kerja (Bagian) |
| Monitoring Semua Dokumen | Tabel seluruh dokumen dari semua peran; filter lanjutan |
| Analitik | Grafik kinerja pemrosesan per peran; waktu rata-rata per tahap |
| Laporan Keterlambatan | Daftar dokumen yang melewati tenggat; ekspor ke Excel |
| Kirim Urgensi | Form penandaan urgensi pada dokumen tertentu |
| Tracking Dokumen | Tampilan riwayat perjalanan dokumen (timeline) |

[GAMBAR 3.12: Tampilan Dasbor Owner (God View) — dibuat manual]
[GAMBAR 3.13: Tampilan Halaman Tracking Dokumen — dibuat manual]

#### H. Halaman Bagian (`views/bagian/`)

| Halaman | Deskripsi |
|---|---|
| Dasbor Bagian (`/bagian/dashboard`) | Statistik dokumen milik unit kerja sendiri |
| Daftar Dokumen Bagian | Tabel dokumen unit kerja: dibuat, dikirim, dikembalikan |
| Tambah Dokumen Bagian | Form input dokumen baru (terbatas pada unit kerja sendiri) |
| Edit Dokumen Dikembalikan | Form edit dan kirim ulang dokumen yang dikembalikan Tim Verifikasi |

#### I. Halaman Programmer (`views/programmer/`)

| Halaman | Deskripsi |
|---|---|
| Dasbor Programmer | Akses ke semua alat teknis |
| Manajemen Pengguna | CRUD pengguna: tambah, edit, lihat, ubah peran dan bagian_code |
| Alat Basis Data | Pembersihan data, ekspor DB, sinkronisasi |
| Operasi Massal | *Bulk update* status dokumen, *reset* dokumen |
| Log Aktivitas | Tampilan log sistem |

#### J. Halaman Lain

| Halaman | Deskripsi |
|---|---|
| Profil (`/profile`) | Ubah nama, *username*, email, kata sandi, kelola 2FA |
| Tracking Publik (`/tracking/{nomor}`) | Riwayat perjalanan dokumen berupa timeline kronologis |
| Inbox Universal (`/inbox`) | Inbox terpadu untuk semua peran |

---

### 3.5 Proses Bisnis / Alur Sistem

#### A. Alur Utama: Pengelolaan Dokumen SPP (Main Workflow)

```
[Bagian/Operator] 
    │ Buat Dokumen SPP (input form + auto-generate nomor agenda)
    │ → Kirim ke Tim Verifikasi (single/bulk)
    ▼
[Tim Verifikasi — Inbox]
    │ Terima → Setujui (atau Tolak → kembalikan ke Operator/Bagian)
    │ → Set Deadline
    │ → Paraf (catat nama + waktu paraf)
    │ → Teruskan ke: Perpajakan ATAU Akuntansi ATAU Pembayaran (langsung)
    ▼
[Perpajakan — opsional]
    │ Terima → Set Deadline → Isi data perpajakan (faktur, DPP, PPN, dll.)
    │ → Selesai → Teruskan ke Akuntansi ATAU Pembayaran
    │ (atau Tolak → kembalikan ke Tim Verifikasi)
    ▼
[Akuntansi — opsional]
    │ Terima → Set Deadline → Proses akuntansi
    │ → Selesai → Teruskan ke Pembayaran
    │ (atau Tolak → kembalikan ke Tim Verifikasi/Perpajakan)
    ▼
[Pembayaran]
    │ Terima → Proses pembayaran
    │ → Input tanggal bayar + link bukti pembayaran
    │ → Tandai "Sudah Dibayar" → SELESAI
    ▼
[Owner/Admin — pantau semua tahap]
    │ Lihat status real-time semua dokumen
    │ → Kirim urgency alert jika dokumen terlambat
```

[GAMBAR 3.14: Flowchart Alur Persetujuan Dokumen SPP — dibuat manual]

#### B. Alur Notifikasi Real-time (WebSocket + WhatsApp)

```
[Server Laravel]
    │ Dokumen baru dikirim / dikembalikan / urgensi
    │ → Broadcast Event ke Pusher (channel per-role)
    ▼
[Pusher Cloud]
    │ → Distribusi ke semua browser yang subscribe channel tersebut
    ▼
[Browser Pengguna]
    │ Laravel Echo menangkap event
    │ → Tampilkan notifikasi popup real-time

[Laravel Queue Scheduler]
    │ Cek dokumen yang melewati deadline
    │ → Kirim HTTP POST ke Fonnte API
    ▼
[Fonnte API → WhatsApp]
    │ → Pesan WhatsApp terkirim ke nomor telepon pengguna
    │ → Log tersimpan di tabel whatsapp_notification_logs
```

[GAMBAR 3.15: Flowchart Alur Notifikasi Real-time dan WhatsApp — dibuat manual]

#### C. Alur Autentikasi (Login + 2FA)

```
[Pengguna]
    │ Masukkan username + password
    ▼
[Sistem — validasi credentials]
    │ Gagal → Tampilkan pesan error
    │ Berhasil:
    │   └─ 2FA diaktifkan?
    │       ├─ Ya → Redirect ke halaman verifikasi OTP
    │       │         → Input kode 6-digit dari Google Authenticator
    │       │         → Verifikasi TOTP → Berhasil → Redirect ke dasbor
    │       │         → Gagal → Pesan error (opsi: gunakan recovery code)
    │       └─ Tidak → Redirect langsung ke dasbor sesuai role
```

[GAMBAR 3.16: Flowchart Alur Login dan 2FA — dibuat manual]

#### D. Alur Auto-forward (MySQL Trigger)

```
[Sistem Pembayaran Eksternal]
    │ Update status dokumen ke 'sudah_dibayar' via raw DB query
    ▼
[MySQL Trigger]
    │ Deteksi perubahan → Insert ke dokumen_auto_forward_queue
    ▼
[Laravel Scheduler]
    │ Cek tabel queue secara periodik
    │ → Proses antrian: update status dokumen di sistem utama
    │ → Catat ke document_trackings
    │ → Update status queue: 'done' atau 'failed'
```

---

### 3.6 Spesifikasi Teknologi

| Komponen | Teknologi | Versi | Sumber Verifikasi |
|---|---|---|---|
| **Backend Framework** | Laravel | ^12.0 | `composer.json` — `laravel/framework ^12.0` |
| **Bahasa Pemrograman** | PHP | ^8.2 | `composer.json` — `"php": "^8.2"` |
| **Basis Data** | MySQL | [PERLU VERIFIKASI — versi MySQL di server prod] | `DB_CONNECTION` config |
| **Frontend Framework** | Tailwind CSS | ^4.0.0 | `package.json` — `tailwindcss ^4.0.0` |
| **UI Component Library** | Bootstrap | ^5.3 | `composer.json` — `twbs/bootstrap ^5.3` |
| **Build Tool** | Vite | ^7.0.7 | `package.json` — `vite ^7.0.7` |
| **Vite Plugin Tailwind** | @tailwindcss/vite | ^4.0.0 | `package.json` |
| **Vite Plugin Laravel** | laravel-vite-plugin | ^2.0.0 | `package.json` |
| **HTTP Client (JS)** | Axios | ^1.11.0 | `package.json` |
| **WebSocket Server** | Pusher | ^7.2 (server) | `composer.json` — `pusher/pusher-php-server ^7.2` |
| **WebSocket Client (JS)** | pusher-js | ^8.4.0 | `package.json` |
| **WebSocket Abstraction** | Laravel Echo | ^2.2.6 | `package.json` |
| **Two-Factor Auth** | pragmarx/google2fa | ^8.0 | `composer.json` |
| **Excel/CSV Export** | maatwebsite/excel | ^1.1 | `composer.json` |
| **WhatsApp Gateway** | Fonnte API | — | `.env.example` — `FONNTE_API_URL` |
| **Session Driver** | Database Session | — | `.env.example` — `SESSION_DRIVER=database` |
| **Queue Driver** | Database Queue | — | `.env.example` — `QUEUE_CONNECTION=database` |
| **Cache Driver** | Database Cache | — | `.env.example` — `CACHE_STORE=database` |
| **Web Server** | Nginx | [PERLU VERIFIKASI] | Deployment config |
| **Template Engine** | Laravel Blade | (bawaan Laravel) | `resources/views/*.blade.php` |

---

### 3.7 Rencana Pengujian

Pengujian dilakukan menggunakan metode **Black Box Testing** dengan fokus pada validasi kebutuhan fungsional. Setiap skenario uji mendefinisikan *input*, langkah-langkah pengujian, *output* yang diharapkan, dan hasil aktual.

| No | ID Uji | Fitur yang Diuji | Skenario Input | Output yang Diharapkan |
|---|---|---|---|---|
| 1 | T-01 | Login valid | Username dan password benar | Redirect ke dasbor sesuai peran pengguna |
| 2 | T-02 | Login tidak valid | Username atau password salah | Pesan error "Kredensial tidak sesuai" |
| 3 | T-03 | Login 2FA | Login berhasil, 2FA aktif, input OTP valid | Redirect ke dasbor |
| 4 | T-04 | Login 2FA gagal | Input OTP salah | Pesan error, tetap di halaman verifikasi OTP |
| 5 | T-05 | Login 2FA recovery code | Login berhasil, 2FA aktif, input recovery code valid | Redirect ke dasbor; recovery code terpakai dihapus |
| 6 | T-06 | Akses halaman role lain | Pengguna Operator mencoba akses `/documents/verifikasi` | Redirect ke halaman login dengan pesan "Anda tidak memiliki akses" |
| 7 | T-07 | Tambah dokumen valid | Input semua field wajib dengan data valid | Dokumen tersimpan; nomor agenda di-generate otomatis; tampil di daftar dokumen |
| 8 | T-08 | Tambah dokumen — field kosong | Tidak mengisi field wajib (nomor SPP) | Validasi error: "Field ini wajib diisi" |
| 9 | T-09 | Auto-generate nomor agenda | Klik tombol generate nomor agenda | Sistem menghasilkan nomor agenda berikutnya berdasarkan nomor tertinggi tahun berjalan |
| 10 | T-10 | Autocomplete penerima pembayaran | Ketik sebagian nama penerima | Dropdown saran nama muncul dari data historis |
| 11 | T-11 | Kirim dokumen ke Tim Verifikasi | Klik "Kirim" pada dokumen berstatus draft | Status dokumen berubah; dokumen masuk inbox Tim Verifikasi; notifikasi real-time diterima |
| 12 | T-12 | Bulk send dokumen | Pilih 3 dokumen, klik "Kirim Semua" | 3 dokumen masuk inbox Tim Verifikasi sekaligus |
| 13 | T-13 | Approve dokumen di inbox | Tim Verifikasi klik "Setujui" pada dokumen di inbox | Status dokumen berubah; dokumen masuk daftar kerja Tim Verifikasi |
| 14 | T-14 | Reject dokumen di inbox | Klik "Tolak" tanpa mengisi alasan penolakan | Validasi error: "Alasan penolakan wajib diisi" |
| 15 | T-15 | Reject dokumen dengan alasan | Klik "Tolak" + isi alasan | Dokumen dikembalikan ke pengirim; kolom `alasan_pengembalian` terisi; notifikasi real-time diterima pengirim |
| 16 | T-16 | Paraf dokumen | Tim Verifikasi pilih nama pemaraf, klik "Paraf" | Kolom `tanggal_paraf` dan `pemaraf` terisi; informasi tampil di semua peran berikutnya |
| 17 | T-17 | Set deadline dokumen | Input jumlah hari tenggat (mis: 3 hari) | Kolom `deadline_at` terhitung (waktu sekarang + 3 hari); tersimpan di `dokumen_role_data` |
| 18 | T-18 | Teruskan ke Perpajakan | Tim Verifikasi klik "Teruskan ke Perpajakan" | Dokumen masuk inbox Perpajakan; `current_handler` berubah ke 'perpajakan'; notifikasi real-time |
| 19 | T-19 | Input data perpajakan | Perpajakan isi form: NPWP, no. faktur, DPP, PPN | Data perpajakan tersimpan di kolom terkait tabel `dokumens` |
| 20 | T-20 | Teruskan ke Akuntansi | Perpajakan klik "Teruskan ke Akuntansi" | Dokumen masuk inbox Akuntansi; `current_handler` berubah ke 'akutansi' |
| 21 | T-21 | Teruskan ke Pembayaran | Akuntansi klik "Teruskan ke Pembayaran" | Dokumen masuk inbox Pembayaran; `current_handler` berubah ke 'pembayaran' |
| 22 | T-22 | Catat pembayaran | Pembayaran isi tanggal bayar + link bukti | Kolom `tanggal_dibayar`, `link_bukti_pembayaran`, `status_pembayaran='sudah_dibayar'` terisi |
| 23 | T-23 | Impor CSV valid | Upload file CSV dengan format yang benar | Data dokumen berhasil diimpor; tampil notifikasi sukses dengan jumlah baris |
| 24 | T-24 | Impor CSV format salah | Upload file CSV dengan header kolom tidak sesuai | Pesan error format file tidak valid |
| 25 | T-25 | Ekspor laporan ke Excel | Klik "Ekspor Excel" pada halaman rekapan | File Excel terunduh ke browser berisi data rekapan periode yang dipilih |
| 26 | T-26 | Kirim urgency alert | Owner klik "Tandai Urgen" pada dokumen | Kolom `urgency_active=true`; notifikasi real-time terkirim ke peran pemegang dokumen |
| 27 | T-27 | Lihat tracking dokumen | Buka halaman `/tracking/{nomor_agenda}` | Timeline riwayat dokumen tampil kronologis berisi semua aksi dari `document_trackings` |
| 28 | T-28 | Simpan search preset | Atur filter pencarian, klik "Simpan Preset" + beri nama | Preset tersimpan di tabel `search_presets`; tampil di daftar preset pengguna |
| 29 | T-29 | Edit inline | Klik field nilai rupiah di tabel, ubah nilai, tekan Enter | Nilai tersimpan tanpa perpindahan halaman; kolom `updated_at` diperbarui |
| 30 | T-30 | Kelola 2FA — aktifkan | Buka profil, klik aktifkan 2FA, scan QR, input kode verifikasi | 2FA aktif; `two_factor_enabled=true`, `two_factor_confirmed_at` terisi |
| 31 | T-31 | Bulk approve inbox | Centang 5 dokumen, klik "Setujui Semua" | 5 dokumen disetujui sekaligus; semua masuk daftar kerja peran terkait |
| 32 | T-32 | Kembalikan dokumen ke Bagian | Tim Verifikasi klik "Kembalikan ke Bagian" + isi alasan | Dokumen masuk halaman "Dikembalikan" Bagian terkait; Operator/Bagian menerima notifikasi |
| 33 | T-33 | Notifikasi WhatsApp deadline | Dokumen melewati tenggat waktu | Pesan WhatsApp terkirim via Fonnte API; log tersimpan di `whatsapp_notification_logs` dengan `status='success'` |
| 34 | T-34 | Manajemen pengguna (Programmer) | Programmer ubah peran pengguna | Role pengguna di tabel `users` berubah sesuai input |
| 35 | T-35 | RBAC — proteksi URL | Pengguna Bagian mencoba akses `/programmer/dashboard` | Redirect ke halaman login dengan pesan akses ditolak |

---

### 3.8 Jadwal Pelaksanaan Tugas Akhir

<!-- MAHASISWA: Sesuaikan tanggal dengan jadwal PKL dan Tugas Akhir yang sesungguhnya -->

| No | Kegiatan | Bulan 1 | Bulan 2 | Bulan 3 | Bulan 4 | Bulan 5 |
|---|---|---|---|---|---|---|
| 1 | Observasi dan pengumpulan data kebutuhan sistem | ████ | | | | |
| 2 | Analisis kebutuhan fungsional dan non-fungsional | ████ | ██ | | | |
| 3 | Perancangan basis data (ERD + skema tabel) | | ████ | | | |
| 4 | Perancangan arsitektur sistem dan UI/UX | | ████ | | | |
| 5 | Implementasi basis data dan model Laravel | | | ████ | | |
| 6 | Implementasi autentikasi, RBAC, dan 2FA | | | ████ | | |
| 7 | Implementasi CRUD dokumen dan alur persetujuan | | | ████ | ██ | |
| 8 | Implementasi notifikasi real-time (Pusher) dan WhatsApp | | | | ████ | |
| 9 | Implementasi laporan, ekspor Excel, dan impor CSV | | | | ████ | |
| 10 | Implementasi dasbor analitik dan fitur Owner | | | | ██ | |
| 11 | Pengujian *Black Box Testing* | | | | | ████ |
| 12 | *User Acceptance Testing* (UAT) dengan pengguna | | | | | ████ |
| 13 | Perbaikan berdasarkan hasil pengujian | | | | | ████ |
| 14 | Penulisan laporan Tugas Akhir | | ██ | ██ | ██ | ████ |
| 15 | *Deployment* ke server produksi | | | | | ████ |

*Keterangan: ████ = minggu aktif kegiatan. Total durasi: 5 bulan ([PERLU VERIFIKASI: periode bulan/tahun PKL]).*

---

## DAFTAR PUSTAKA

<!-- MAHASISWA: Ganti seluruh referensi berikut dengan referensi yang benar-benar kamu baca dan gunakan. Format IEEE. -->

[1] [PERLU VERIFIKASI] Penulis, "Judul artikel/buku tentang BUMN dan pengelolaan dokumen keuangan," *Nama Jurnal/Penerbit*, vol. X, no. X, hlm. XXX–XXX, Tahun.

[2] [PERLU VERIFIKASI] Penulis, "Judul artikel tentang sistem informasi manajemen dokumen atau digitalisasi proses bisnis," *Nama Jurnal*, vol. X, no. X, hlm. XXX–XXX, Tahun.

[3] [PERLU VERIFIKASI] Penulis, "Judul artikel tentang transformasi digital / good corporate governance BUMN," *Nama Jurnal*, vol. X, no. X, hlm. XXX–XXX, Tahun.

[4] [PERLU VERIFIKASI] Penulis, "Judul buku/artikel tentang sistem informasi (O'Brien, Laudon, atau sejenisnya)," *Nama Penerbit*, Edisi, Tahun.

[5] [PERLU VERIFIKASI] Penulis, "Judul artikel tentang Document Management System / DMS," *Nama Jurnal*, vol. X, no. X, hlm. XXX–XXX, Tahun.

[6] Taylor Otwell, *Laravel: The PHP Framework for Web Artisans*, Laravel LLC. [Online]. Tersedia: https://laravel.com/docs/12.x. [Diakses: [PERLU VERIFIKASI — tanggal akses]].

[7] [PERLU VERIFIKASI] Penulis, "Judul artikel tentang PHP atau pengembangan web dengan PHP," *Nama Jurnal/Penerbit*, Tahun.

[8] [PERLU VERIFIKASI] Penulis, "Judul buku/artikel tentang MySQL atau RDBMS," *Nama Penerbit*, Tahun.

[9] Adam Wathan, *Tailwind CSS Documentation*, Tailwind Labs. [Online]. Tersedia: https://tailwindcss.com/docs. [Diakses: [PERLU VERIFIKASI]].

[10] Mark Otto dan Jacob Thornton, *Bootstrap Documentation*, The Bootstrap Authors. [Online]. Tersedia: https://getbootstrap.com/docs/5.3. [Diakses: [PERLU VERIFIKASI]].

[11] Pusher Ltd., *Pusher Channels Documentation*. [Online]. Tersedia: https://pusher.com/docs/channels. [Diakses: [PERLU VERIFIKASI]].

[12] [PERLU VERIFIKASI] Penulis, "Judul artikel/jurnal tentang Role-Based Access Control (RBAC)," *Nama Jurnal*, vol. X, no. X, hlm. XXX–XXX, Tahun.

[13] [PERLU VERIFIKASI] Penulis, "Judul artikel/jurnal tentang Two-Factor Authentication atau TOTP," *Nama Jurnal*, vol. X, no. X, hlm. XXX–XXX, Tahun.

[14] Maatwebsite, *Laravel Excel Documentation*. [Online]. Tersedia: https://laravel-excel.com/docs. [Diakses: [PERLU VERIFIKASI]].

[15] Fonnte, *Fonnte WhatsApp Gateway API Documentation*. [Online]. Tersedia: https://fonnte.com/docs. [Diakses: [PERLU VERIFIKASI]].

[16] [PERLU VERIFIKASI] Penulis, "Judul artikel/buku tentang Black Box Testing," *Nama Jurnal/Penerbit*, Tahun.

[17] [PERLU VERIFIKASI] Penulis, "Judul buku/artikel tentang SDLC model Waterfall," *Nama Penerbit*, Tahun.

[18] Evan You, *Vite Documentation*. [Online]. Tersedia: https://vite.dev/guide. [Diakses: [PERLU VERIFIKASI]].

---

## CATATAN AKHIR UNTUK MAHASISWA

Dokumen ini merupakan **konten mentah** yang dihasilkan berdasarkan analisis codebase proyek Agenda Online PTPN. Sebelum diformat ke Word oleh AI, pastikan hal-hal berikut telah dilakukan:

1. **Ganti semua placeholder `[PERLU VERIFIKASI]`** dengan informasi yang benar sesuai fakta.
2. **Tambahkan 4 referensi penelitian terdahulu** yang relevan dan benar-benar telah dibaca dari jurnal/repositori resmi.
3. **Lengkapi seluruh referensi Daftar Pustaka** dengan data bibliografi yang lengkap dan benar.
4. **Buat gambar/diagram secara manual**: ERD, diagram arsitektur, flowchart, dan screenshot UI, lalu lampirkan sesuai placeholder `[GAMBAR ...]`.
5. **Verifikasi versi MySQL** yang digunakan di server produksi.
6. **Sesuaikan jadwal** (bulan/tahun) dengan periode PKL yang sesungguhnya.
7. **Periksa identitas mahasiswa**: nama, NIM, program studi, pembimbing, dan nama institusi — yang tidak ada dalam codebase sehingga tidak dicantumkan dalam dokumen ini.
8. Seluruh konten teknis (tabel basis data, nama kolom, versi teknologi, fitur sistem) **telah diverifikasi langsung dari codebase** dan dapat digunakan sebagaimana adanya.

