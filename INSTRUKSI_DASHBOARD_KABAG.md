# INSTRUKSI REDESIGN: Dashboard Kabag Keuangan

> Baca file `dashboard-kabag.jsx` sebagai referensi visual utama.
> Semua warna, layout, dan struktur komponen mengacu ke prototipe tersebut.

---

## STACK & ATURAN UMUM
- Framework: Laravel + Blade (atau Inertia.js jika digunakan)
- Chart library: Chart.js via CDN jika belum ada
- Jangan ubah route, middleware, auth, atau halaman lain
- Semua data harus dari database, tidak ada yang hardcode
- Sesuaikan nama field dengan yang benar-benar ada di tabel `dokumens`

---

## PERUBAHAN 1 — HEADER BANNER

### Greeting Dinamis
Tambahkan greeting di atas judul berdasarkan jam server:
```php
$hour = now()->hour;
$greeting = match(true) {
    $hour < 12 => 'Selamat Pagi',
    $hour < 15 => 'Selamat Siang',
    $hour < 18 => 'Selamat Sore',
    default    => 'Selamat Malam',
};
```
Tampilkan: `{{ $greeting }}, <strong>{{ auth()->user()->name }}</strong>`

### Ringkasan Hari Ini (pojok kanan banner)
Glassmorphism card dengan `backdrop-filter: blur(10px)`, berisi:
```php
$hariIni = [
    'masuk'     => Dokumen::whereDate('created_at', today())->count(),
    'mendekati' => Dokumen::whereBetween('created_at', [now()->subDays(3), now()->subDay()])
                      ->whereNull('tanggal_selesai')->count(),
    'terlambat' => Dokumen::where('created_at', '<', now()->subDays(3))
                      ->whereNull('tanggal_selesai')->count(),
];
```
Tampilkan 3 baris dengan dot berwarna: 🟢 masuk · 🟡 mendekati · 🔴 terlambat

### Dekorasi Banner
Tambahkan 3 lingkaran concentric di pojok kanan atas banner
menggunakan div absolute dengan `border: 1px solid rgba(255,255,255,0.08)`.

---

## PERUBAHAN 2 — STAT CARDS (Improve yang ada)

**A. Border-top berwarna per card:**
- Total Dokumen → `#0D9488`
- Belum Dibayar → `#F59E0B`
- Siap Dibayar  → `#3B82F6`
- Sudah Dibayar → `#10B981`
- Total Nilai   → card full gradient teal (bukan border)

**B. Persentase perubahan vs bulan lalu:**
```php
$bulanIni  = Dokumen::whereMonth('created_at', now()->month)->count();
$bulanLalu = Dokumen::whereMonth('created_at', now()->subMonth()->month)->count();
$perubahan = $bulanLalu > 0
    ? round((($bulanIni - $bulanLalu) / $bulanLalu) * 100) : 0;
```
Tampilkan di bawah angka: `↑ +12%` (hijau) atau `↓ -3%` (merah) vs bulan lalu.

**C. Animasi counter saat halaman load:**
```javascript
function animateCounter(el, target, duration = 1200) {
    const start = performance.now();
    const update = (ts) => {
        const p = Math.min((ts - start) / duration, 1);
        const eased = 1 - Math.pow(1 - p, 3);
        el.textContent = Math.floor(eased * target).toLocaleString('id-ID');
        if (p < 1) requestAnimationFrame(update);
    };
    requestAnimationFrame(update);
}
document.querySelectorAll('[data-counter]').forEach(el => {
    animateCounter(el, parseInt(el.dataset.counter));
});
```
Tambahkan `data-counter="{{ $value }}"` pada elemen angka stat card.

---

## PERUBAHAN 3 — SECTION CHARTS (BARU, di antara stat cards & bagian)

Grid layout: `2fr 1fr`

### Chart Kiri — Area Chart Tren 30 Hari
```php
$trend = Dokumen::selectRaw('DATE(created_at) as tgl, COUNT(*) as total')
    ->where('created_at', '>=', now()->subDays(30))
    ->groupBy('tgl')->orderBy('tgl')->get();
$chartLabels = $trend->pluck('tgl')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))->toArray();
$chartData   = $trend->pluck('total')->toArray();
```
```javascript
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: @json($chartLabels),
        datasets: [{
            label: 'Dokumen Masuk',
            data: @json($chartData),
            borderColor: '#0D9488',
            backgroundColor: 'rgba(13,148,136,0.08)',
            borderWidth: 2, fill: true, tension: 0.4, pointRadius: 0
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 10 } } },
            y: { grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', font: { size: 10 } } }
        }
    }
});
```

### Chart Kanan — Donut Chart Proporsi Status
```javascript
new Chart(document.getElementById('donutChart'), {
    type: 'doughnut',
    data: {
        labels: ['Sudah Dibayar', 'Belum Dibayar', 'Siap Bayar'],
        datasets: [{
            data: [@json($sudahDibayar), @json($belumDibayar), @json($siapDibayar)],
            backgroundColor: ['#10B981', '#F59E0B', '#3B82F6'],
            borderWidth: 3, borderColor: 'white'
        }]
    },
    options: { responsive: true, cutout: '65%', plugins: { legend: { display: false } } }
});
```
Tambahkan legend manual di bawah chart (dot + label + angka).

---

## PERUBAHAN 4 — SECTION BAGIAN (BARU, di atas grid card bagian)

Grid layout: `1fr 1fr`

### Panel Kiri — Bar Chart Volume per Bagian
```php
$bagianStats = Dokumen::selectRaw('bagian, COUNT(*) as total,
    SUM(CASE WHEN created_at < NOW() - INTERVAL 3 DAY
        AND tanggal_selesai IS NULL THEN 1 ELSE 0 END) as terlambat')
    ->groupBy('bagian')->orderByDesc('total')->get();
$maxBagian = $bagianStats->max('total') ?: 1;
```
Render sebagai Bar chart Chart.js dengan warna berbeda per bagian.

### Panel Kanan — List Progress Bar per Bagian
Untuk setiap bagian tampilkan:
- Badge nama (colored)
- Jumlah dokumen
- `⚠ N terlambat` jika ada (merah)
- Progress bar tipis: lebar proporsional, merah jika ada keterlambatan

```html
@foreach($bagianStats as $b)
<div class="bagian-row">
  <div class="bagian-badge" style="background:{{ $b->warna }}20; color:{{ $b->warna }}">
    {{ $b->bagian }}
  </div>
  <div class="bagian-info">
    <div class="bagian-meta">
      <span>{{ number_format($b->total) }} dokumen</span>
      @if($b->terlambat > 0)
        <span style="color:#EF4444; font-size:11px; font-weight:600">
          ⚠ {{ $b->terlambat }} terlambat
        </span>
      @endif
    </div>
    <div class="progress-bar">
      <div class="progress-fill" style="
        width: {{ ($b->total / $maxBagian) * 100 }}%;
        background: {{ $b->terlambat > 0
          ? 'linear-gradient(90deg,'.$b->warna.',#EF4444)'
          : $b->warna }}">
      </div>
    </div>
  </div>
</div>
@endforeach
```

---

## PERUBAHAN 5 — GRID CARD BAGIAN (Improve yang ada)

Pertahankan grid yang sudah ada, tingkatkan tampilannya:
- Background: `#F8FAFC` dengan border `1px solid #E2E8F0`
- Hover: background berubah ke warna accent ringan + `translateY(-2px)`
- Warna angka jumlah dokumen = warna accent per bagian
- Tambahkan teks "Lihat →" di bawah setiap card

---

## CSS TAMBAHAN

```css
.stat-change         { font-size: 11px; font-weight: 600; margin-top: 8px; }
.stat-change.pos     { color: #10B981; }
.stat-change.neg     { color: #EF4444; }
.progress-bar        { height: 5px; background: #f1f5f9; border-radius: 99px; overflow: hidden; }
.progress-fill       { height: 100%; border-radius: 99px; transition: width 1s ease; }
.bagian-row          { display: flex; align-items: center; gap: 12px; margin-bottom: 8px; }
.bagian-badge        { width:32px; height:32px; border-radius:8px; display:flex;
                       align-items:center; justify-content:center;
                       font-size:11px; font-weight:700; flex-shrink:0; }
.bagian-info         { flex: 1; }
.bagian-meta         { display:flex; justify-content:space-between;
                       margin-bottom:4px; font-size:12px; }
.donut-legend        { display:flex; flex-direction:column; gap:6px; margin-top:8px; }
.legend-item         { display:flex; align-items:center; gap:6px; font-size:12px; }
.legend-item .dot    { width:10px; height:10px; border-radius:3px; flex-shrink:0; }
.legend-item strong  { margin-left:auto; }
```

---

## CHECKLIST VERIFIKASI

- [ ] Greeting berubah sesuai waktu (pagi/siang/sore/malam)
- [ ] Ringkasan Hari Ini di banner menampilkan data real
- [ ] Stat card punya border-top berwarna + % perubahan bulan lalu
- [ ] Animasi counter berjalan saat halaman pertama dimuat
- [ ] Area chart tren 30 hari tampil dengan data real
- [ ] Donut chart proporsi status tampil dengan data real
- [ ] Bar chart volume per bagian tampil
- [ ] Progress bar per bagian + indikator terlambat (merah jika ada)
- [ ] Grid card bagian existing tetap ada dan tampilannya diimprove
- [ ] Responsive di zoom 75% dan 100%
- [ ] Tidak ada halaman atau fitur lain yang terpengaruh
