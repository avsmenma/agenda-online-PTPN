# PROMPT: Integrasi Data Cash Bank → Menu Pimpinan di Agenda Online
> Untuk AI Coding Agent (Cursor / Antigravity)  
> Proyek: **Buku Agenda Online PTPN IV Regional V** (Laravel 12)  
> Tanggal: April 2025

---

## 🎯 MISI UTAMA

Kamu bertugas untuk:
1. **Membaca dan memahami** seluruh kode proyek Cash Bank (port 8080)
2. **Memetakan skema database** Cash Bank yang terhubung ke MySQL instance yang sama
3. **Menganalisis data** apa saja yang ditampilkan di Cash Bank
4. **Merancang dan mengimplementasikan** menu khusus "Laporan Cash Bank" di role **Admin / Pimpinan** pada proyek Agenda Online
5. Data yang ditampilkan harus **siap saji untuk keperluan laporan pimpinan** — bukan raw data mentah

---

## 🖥️ KONTEKS INFRASTRUKTUR

```
VPS        : Ubuntu — IP 163.61.58.92
MySQL      : Instance bersama (shared database server)
Agenda     : Laravel 12, port 80/443, domain utama
Cash Bank  : Laravel (versi baca dari composer.json), port 8080
```

**Penting:** Kedua proyek menggunakan MySQL instance yang sama. Kemungkinan besar database Cash Bank bisa diakses langsung dari proyek Agenda dengan menambahkan koneksi database kedua di `config/database.php`.

---

## 📋 FASE 1 — EKSPLORASI KODE CASH BANK

### Langkah 1.1 — Baca Struktur Direktori

```bash
# Temukan root directory Cash Bank
ls /var/www/        # atau lokasi lain
find / -name "artisan" 2>/dev/null | grep -v agenda
```

Setelah menemukan root Cash Bank, baca struktur berikut:

```
[root-cashbank]/
├── app/
│   ├── Models/          # ← PRIORITAS: baca semua Model
│   ├── Http/
│   │   ├── Controllers/ # ← baca semua Controller
│   │   └── Livewire/    # jika ada
│   └── Services/        # jika ada
├── database/
│   ├── migrations/      # ← WAJIB BACA SEMUA
│   └── seeders/
├── resources/views/     # ← baca untuk memahami tampilan
├── routes/web.php
└── .env                 # untuk nama database
```

### Langkah 1.2 — Baca File .env Cash Bank

Ambil nilai berikut:
```
DB_DATABASE=   ← nama database Cash Bank
DB_USERNAME=
DB_PASSWORD=
DB_HOST=
```

### Langkah 1.3 — Baca Semua Migration

```bash
cat [root-cashbank]/database/migrations/*.php
```

Untuk setiap tabel, catat:
- Nama tabel
- Semua kolom beserta tipe datanya
- Foreign key relationships
- Kolom yang tampak sebagai nominal/uang (decimal, double, bigInteger)
- Kolom tanggal (date, datetime, timestamp)
- Kolom status (enum, string yang kemungkinan berisi status)

### Langkah 1.4 — Baca Semua Model

```bash
cat [root-cashbank]/app/Models/*.php
```

Untuk setiap Model, catat:
- `$fillable` atau `$guarded`
- Relasi (`belongsTo`, `hasMany`, `belongsToMany`, `hasOne`)
- `$casts` — terutama untuk kolom uang dan tanggal
- Scope yang didefinisikan (`scopeXxx`)
- Accessor/mutator jika ada

### Langkah 1.5 — Baca Controller Utama

```bash
cat [root-cashbank]/app/Http/Controllers/*.php
```

Untuk setiap Controller, pahami:
- Method apa saja yang ada
- Query apa yang dijalankan (terutama yang melibatkan `sum()`, `count()`, `groupBy()`, `where status`)
- Data apa yang di-pass ke view
- Filter/parameter yang diterima (tanggal, kategori, dll)

### Langkah 1.6 — Baca Routes

```bash
cat [root-cashbank]/routes/web.php
```

Petakan: `[HTTP Method] [URI] → [Controller@Method] → [View]`

### Langkah 1.7 — Baca View Utama (Dashboard / Laporan)

```bash
ls [root-cashbank]/resources/views/
cat [root-cashbank]/resources/views/dashboard* 2>/dev/null
cat [root-cashbank]/resources/views/laporan* 2>/dev/null
cat [root-cashbank]/resources/views/report* 2>/dev/null
```

Pahami:
- Widget/card apa saja yang ditampilkan
- Chart apa saja yang ada dan datanya dari mana
- Tabel apa saja yang ditampilkan
- Filter/form apa yang tersedia untuk user

---

## 📋 FASE 2 — PEMETAAN DATABASE LANGSUNG

### Langkah 2.1 — Baca Skema via MySQL

Jalankan query ini di MySQL (gunakan credentials dari .env Cash Bank):

```sql
-- Lihat semua tabel
SHOW TABLES FROM `[DB_DATABASE_CASHBANK]`;

-- Untuk setiap tabel penting, lihat struktur
DESCRIBE `[nama_tabel]`;

-- Lihat sample data (5 baris) tiap tabel
SELECT * FROM `[nama_tabel]` LIMIT 5;

-- Lihat total data tiap tabel
SELECT 
    table_name,
    table_rows,
    ROUND(data_length/1024/1024, 2) AS 'Size (MB)'
FROM information_schema.tables
WHERE table_schema = '[DB_DATABASE_CASHBANK]'
ORDER BY table_rows DESC;
```

### Langkah 2.2 — Identifikasi Tabel Inti

Setelah mengeksplorasi, kelompokkan tabel ke dalam kategori:

| Kategori | Kemungkinan Nama Tabel |
|---|---|
| **Transaksi utama** | `transactions`, `cash_transactions`, `journals`, `entries` |
| **Kas/Bank** | `accounts`, `cash_accounts`, `bank_accounts`, `coa` |
| **Kategori/Jenis** | `categories`, `types`, `account_types` |
| **Referensi** | `users`, `branches`, `departments` |
| **Log/History** | `*_logs`, `*_histories` |

### Langkah 2.3 — Temukan Query Kunci untuk Dashboard

Dari kode Controller dan View yang sudah dibaca, identifikasi query-query yang menghasilkan:
- **Total saldo kas/bank**
- **Total pemasukan periode tertentu**
- **Total pengeluaran periode tertentu**
- **Transaksi terbaru**
- **Grafik tren kas**
- **Breakdown per kategori/akun**

---

## 📋 FASE 3 — ANALISIS DATA UNTUK PIMPINAN

Setelah Fase 1 dan 2 selesai, buat dokumen analisis dalam format:

```
=== HASIL ANALISIS CASH BANK ===

TABEL UTAMA:
- [nama_tabel]: [deskripsi singkat, jumlah kolom penting]

DATA YANG DITAMPILKAN DI CASH BANK:
1. [Widget/Card]: [query yang menghasilkannya]
2. ...

DATA YANG RELEVAN UNTUK PIMPINAN:
1. [Metric]: [sumber query] → [cara penyajian yang cocok]
2. ...

TABEL YANG AKAN DI-QUERY DARI AGENDA:
- [nama_tabel]: [kolom yang dibutuhkan]
```

---

## 📋 FASE 4 — IMPLEMENTASI DI AGENDA ONLINE

### Langkah 4.1 — Tambahkan Koneksi Database Cash Bank

Edit `config/database.php` di proyek Agenda Online:

```php
'connections' => [
    // ... koneksi mysql yang sudah ada (agenda) ...

    'cashbank' => [
        'driver'    => 'mysql',
        'host'      => env('CASHBANK_DB_HOST', '127.0.0.1'),
        'port'      => env('CASHBANK_DB_PORT', '3306'),
        'database'  => env('CASHBANK_DB_DATABASE', ''),
        'username'  => env('CASHBANK_DB_USERNAME', ''),
        'password'  => env('CASHBANK_DB_PASSWORD', ''),
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix'    => '',
        'strict'    => true,
    ],
],
```

Tambahkan ke `.env` Agenda Online:
```env
CASHBANK_DB_HOST=127.0.0.1
CASHBANK_DB_PORT=3306
CASHBANK_DB_DATABASE=[nama_database_cashbank]
CASHBANK_DB_USERNAME=[username]
CASHBANK_DB_PASSWORD=[password]
```

### Langkah 4.2 — Buat Model Read-Only untuk Cash Bank

Buat folder `app/Models/CashBank/` di proyek Agenda Online.

Untuk setiap tabel penting Cash Bank, buat Model:

```php
<?php
// app/Models/CashBank/CashTransaction.php
namespace App\Models\CashBank;

use Illuminate\Database\Eloquent\Model;

class CashTransaction extends Model
{
    protected $connection = 'cashbank';
    protected $table = '[nama_tabel_di_cashbank]'; // sesuaikan

    // Read-only: tidak ada fillable, tidak bisa write
    protected $guarded = ['*'];

    // Sesuaikan cast dengan kolom yang ada
    protected $casts = [
        'amount'     => 'decimal:2', // sesuaikan nama kolom
        'date'       => 'date',      // sesuaikan nama kolom
        'created_at' => 'datetime',
    ];
}
```

> ⚠️ PENTING: Semua Model CashBank bersifat **read-only**. Jangan pernah menambahkan method write (create, update, delete) pada model ini. Agenda Online hanya membaca data Cash Bank, tidak menulis.

### Langkah 4.3 — Buat Service Class

```php
<?php
// app/Services/CashBankReportService.php
namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CashBankReportService
{
    private string $conn = 'cashbank';

    /**
     * Ringkasan saldo kas & bank
     * Sesuaikan nama tabel dan kolom dengan hasil eksplorasi
     */
    public function getSaldoRingkasan(string $periode = null): array
    {
        // TODO: implementasikan berdasarkan hasil analisis Fase 2
        // Contoh kerangka:
        return DB::connection($this->conn)
            ->table('[tabel_utama]')
            ->when($periode, fn($q) => $q->whereMonth('[kolom_tanggal]', Carbon::parse($periode)->month))
            ->select(
                DB::raw('SUM(CASE WHEN [kolom_jenis] = "masuk" THEN [kolom_nominal] ELSE 0 END) as total_masuk'),
                DB::raw('SUM(CASE WHEN [kolom_jenis] = "keluar" THEN [kolom_nominal] ELSE 0 END) as total_keluar'),
                DB::raw('COUNT(*) as total_transaksi')
            )
            ->first()
            ->toArray();
    }

    /**
     * Tren transaksi per bulan (untuk chart)
     */
    public function getTrenBulanan(int $tahun): array
    {
        // TODO: implementasikan
        return DB::connection($this->conn)
            ->table('[tabel_utama]')
            ->whereYear('[kolom_tanggal]', $tahun)
            ->select(
                DB::raw('MONTH([kolom_tanggal]) as bulan'),
                DB::raw('SUM(CASE WHEN [kolom_jenis] = "masuk" THEN [kolom_nominal] ELSE 0 END) as masuk'),
                DB::raw('SUM(CASE WHEN [kolom_jenis] = "keluar" THEN [kolom_nominal] ELSE 0 END) as keluar')
            )
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get()
            ->toArray();
    }

    /**
     * Breakdown per kategori/akun
     */
    public function getBreakdownKategori(string $periode = null): array
    {
        // TODO: implementasikan berdasarkan hasil analisis
        return [];
    }

    /**
     * Transaksi terbaru
     */
    public function getTransaksiTerbaru(int $limit = 10): array
    {
        // TODO: implementasikan
        return DB::connection($this->conn)
            ->table('[tabel_utama]')
            ->orderBy('[kolom_tanggal]', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Saldo per akun kas/bank
     */
    public function getSaldoPerAkun(): array
    {
        // TODO: implementasikan jika ada tabel akun/COA
        return [];
    }
}
```

### Langkah 4.4 — Buat Controller

```php
<?php
// app/Http/Controllers/Admin/CashBankPimpinanController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CashBankReportService;
use Illuminate\Http\Request;

class CashBankPimpinanController extends Controller
{
    public function __construct(private CashBankReportService $service)
    {
        // Pastikan hanya role admin/pimpinan yang bisa akses
        $this->middleware(['auth', 'role:admin|owner|kabag_keuangan']);
    }

    public function index(Request $request)
    {
        $periode = $request->get('periode', now()->format('Y-m'));
        $tahun   = $request->get('tahun', now()->year);

        $data = [
            'saldo_ringkasan'   => $this->service->getSaldoRingkasan($periode),
            'tren_bulanan'      => $this->service->getTrenBulanan($tahun),
            'breakdown_kategori'=> $this->service->getBreakdownKategori($periode),
            'transaksi_terbaru' => $this->service->getTransaksiTerbaru(10),
            'saldo_per_akun'    => $this->service->getSaldoPerAkun(),
            'periode'           => $periode,
            'tahun'             => $tahun,
        ];

        return view('admin.cashbank.index', $data);
    }

    /**
     * Endpoint untuk chart (AJAX)
     */
    public function chartData(Request $request)
    {
        return response()->json([
            'tren' => $this->service->getTrenBulanan($request->tahun ?? now()->year),
        ]);
    }
}
```

### Langkah 4.5 — Tambahkan Route

Edit `routes/web.php` di Agenda Online:

```php
// Kelompok route admin/pimpinan
Route::middleware(['auth', 'role:admin|owner|kabag_keuangan'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // ... route admin yang sudah ada ...

        // Cash Bank
        Route::prefix('cashbank')->name('cashbank.')->group(function () {
            Route::get('/', [CashBankPimpinanController::class, 'index'])->name('index');
            Route::get('/chart-data', [CashBankPimpinanController::class, 'chartData'])->name('chart');
        });
    });
```

### Langkah 4.6 — Buat View Blade

Buat file: `resources/views/admin/cashbank/index.blade.php`

View ini harus menampilkan (berdasarkan data yang sudah dianalisis):

```
STRUKTUR HALAMAN:
┌─────────────────────────────────────────────────┐
│ HEADER: "Laporan Cash Bank" + filter periode    │
├─────────────────────────────────────────────────┤
│ ROW 1 — STAT CARDS (4 kolom):                  │
│  [Saldo Akhir] [Total Masuk] [Total Keluar]     │
│  [Total Transaksi]                              │
├─────────────────────────────────────────────────┤
│ ROW 2 — CHARTS (2 kolom):                      │
│  [Line Chart: Tren Masuk vs Keluar]             │
│  [Donut/Bar: Breakdown per Kategori]            │
├─────────────────────────────────────────────────┤
│ ROW 3 — BOTTOM (2 kolom):                      │
│  [Tabel Transaksi Terbaru]                      │
│  [Saldo per Akun Kas/Bank]                      │
└─────────────────────────────────────────────────┘
```

Template Blade skeleton:

```blade
@extends('layouts.app')

@section('title', 'Laporan Cash Bank – Pimpinan')

@section('content')
<div class="p-6">

    {{-- HEADER & FILTER --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Laporan Cash Bank</h1>
            <p class="text-sm text-gray-500">Data real-time dari sistem Cash Bank</p>
        </div>
        <form method="GET" class="flex items-center gap-3">
            <input type="month" name="periode" value="{{ $periode }}"
                   class="border rounded-lg px-3 py-2 text-sm">
            <button type="submit" class="bg-teal-600 text-white px-4 py-2 rounded-lg text-sm font-medium">
                Terapkan
            </button>
        </form>
    </div>

    {{-- STAT CARDS --}}
    <div class="grid grid-cols-4 gap-4 mb-6">
        {{-- TODO: Render stat cards berdasarkan $saldo_ringkasan --}}
    </div>

    {{-- CHARTS --}}
    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="bg-white rounded-xl border p-5 shadow-sm">
            <h3 class="font-semibold text-sm mb-4">Tren Kas Masuk vs Keluar</h3>
            <canvas id="chartTren" height="80"></canvas>
        </div>
        <div class="bg-white rounded-xl border p-5 shadow-sm">
            <h3 class="font-semibold text-sm mb-4">Breakdown per Kategori</h3>
            <canvas id="chartKategori" height="80"></canvas>
        </div>
    </div>

    {{-- TRANSAKSI TERBARU & SALDO AKUN --}}
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white rounded-xl border p-5 shadow-sm">
            <h3 class="font-semibold text-sm mb-4">Transaksi Terbaru</h3>
            {{-- TODO: Render tabel $transaksi_terbaru --}}
        </div>
        <div class="bg-white rounded-xl border p-5 shadow-sm">
            <h3 class="font-semibold text-sm mb-4">Saldo per Akun</h3>
            {{-- TODO: Render $saldo_per_akun --}}
        </div>
    </div>

</div>

@push('scripts')
<script>
// Data dari controller (JSON-encoded)
const trenData = @json($tren_bulanan);
const kategoriData = @json($breakdown_kategori);

// TODO: Inisialisasi Chart.js dengan data di atas
// Ikuti pola chart yang sudah ada di proyek Agenda Online
</script>
@endpush
@endsection
```

### Langkah 4.7 — Tambahkan Menu di Sidebar

Cari file sidebar/navigation Agenda Online (biasanya di `resources/views/layouts/` atau `resources/views/partials/`).

Tambahkan menu item untuk role admin/pimpinan:

```blade
@role(['admin', 'owner', 'kabag_keuangan'])
<li>
    <a href="{{ route('admin.cashbank.index') }}"
       class="{{ request()->routeIs('admin.cashbank.*') ? 'active' : '' }}">
        <x-icon name="banknotes" /> {{-- sesuaikan dengan icon system yang dipakai --}}
        <span>Cash Bank</span>
    </a>
</li>
@endrole
```

---

## 📋 FASE 5 — VALIDASI DAN TESTING

Setelah implementasi, lakukan:

### 5.1 — Test Koneksi Database
```bash
cd [root-agenda]
php artisan tinker
>>> DB::connection('cashbank')->select('SELECT 1');
# Harus return: array:1 [▼ 0 => {#...}]
```

### 5.2 — Test Query Service
```bash
php artisan tinker
>>> app(App\Services\CashBankReportService::class)->getSaldoRingkasan()
```

### 5.3 — Test Route
```
GET http://163.61.58.92/admin/cashbank
→ Harus menampilkan halaman dashboard Cash Bank
→ Harus ada data (tidak boleh empty jika Cash Bank sudah punya data)
```

### 5.4 — Verifikasi Angka
Buka Cash Bank di `http://163.61.58.92:8080` dan bandingkan angka yang tampil di sana dengan angka yang muncul di menu pimpinan Agenda Online. Harus identik.

---

## ⚠️ ATURAN WAJIB (JANGAN DILANGGAR)

1. **READ ONLY** — Agenda Online hanya boleh SELECT dari database Cash Bank. Tidak ada INSERT, UPDATE, DELETE apapun ke database Cash Bank
2. **Jangan ubah kode Cash Bank** — Semua perubahan hanya di proyek Agenda Online
3. **Gunakan `DB::connection('cashbank')`** setiap kali query ke database Cash Bank, jangan gunakan koneksi default
4. **Semua Model CashBank** simpan di namespace `App\Models\CashBank\` dan folder `app/Models/CashBank/`
5. **Middleware role** harus selalu ada di setiap route dan controller Cash Bank di Agenda
6. **Jangan hardcode** nama tabel, kolom, atau nilai. Sesuaikan dengan hasil eksplorasi Fase 1–2
7. **Error handling** — Jika koneksi ke database Cash Bank gagal, tampilkan pesan error yang informatif, bukan blank page

---

## 📦 OUTPUT YANG DIHARAPKAN

Setelah selesai, agent harus menghasilkan:

- [ ] Dokumen analisis (tabel, kolom, relasi, data yang ditampilkan Cash Bank)
- [ ] Entry koneksi `cashbank` di `config/database.php`
- [ ] Variabel `.env` untuk Cash Bank DB
- [ ] Model-model di `app/Models/CashBank/`
- [ ] `app/Services/CashBankReportService.php` dengan query yang sudah disesuaikan
- [ ] `app/Http/Controllers/Admin/CashBankPimpinanController.php`
- [ ] Route di `routes/web.php`
- [ ] View `resources/views/admin/cashbank/index.blade.php`
- [ ] Menu item di sidebar untuk role admin/pimpinan
- [ ] Hasil test koneksi dan query (screenshot atau output terminal)

---

## 💡 TIPS UNTUK AGENT

- Jika Cash Bank menggunakan Livewire, fokus baca file di `app/Livewire/` atau `app/Http/Livewire/`
- Jika ada file `app/Repositories/`, baca juga — biasanya berisi query kompleks
- Cek apakah ada `app/Helpers/` atau `helpers.php` untuk fungsi format nominal
- Perhatikan apakah Cash Bank menggunakan soft delete (`deleted_at`) — jika ya, tambahkan `whereNull('deleted_at')` atau gunakan `withTrashed()` sesuai kebutuhan
- Jika ada kolom `is_posted`, `is_approved`, atau sejenisnya, tanya kepada developer apakah data yang ditampilkan ke pimpinan hanya yang sudah approved/posted
- Untuk format nominal Rupiah, gunakan helper yang sudah ada di Agenda Online agar konsisten
