# Spec: Topbar Global Agenda Online (Semua Role)

Tanggal: 2026-07-16
Status: disetujui user (brainstorming 2026-07-16)

## Latar Belakang

Dua project lain (Cash Bank, LM Reporting) punya top bar berisi hamburger +
logo + judul aplikasi. Agenda Online tidak punya — setelah pembersihan sidebar
legacy (commit `bdda763`, `0bd0abd`), brand + tombol collapse menempel di
bagian atas sidebar modern (`.owner-sidebar-logo`). User ingin topbar serupa
CB/LM, berlaku untuk **semua role**.

## Keputusan Desain (hasil tanya-jawab)

| Aspek | Keputusan |
|---|---|
| Isi topbar | HANYA kiri: hamburger (buka/tutup sidebar) + logo + judul. Kanan: kosong. |
| Judul | **Agenda Online** (tebal) + subjudul **Sistem Monitoring Dokumen Keuangan** |
| Logo | `images/logoPTPNNew.png` (sama dengan yang dipakai sidebar sekarang) |
| Posisi brand | Pindah ke topbar; **dihapus dari sidebar** — sidebar langsung mulai dari menu |
| Warna | Putih bersih + `border-bottom: 1px solid #E2E8F0`; dark-mode aware (`.dark` → `#1e293b` / border `#334155`) |
| User & Logout | TETAP di kartu bawah sidebar (tidak pindah ke topbar) |
| Cakupan | Semua role via modern shell `layouts/app` (owner, admin, operator, pembayaran, verifikasi, perpajakan, akutansi, bagian_*, programmer di halaman berbagi). `layouts.programmer` TIDAK disentuh. |

## Rancangan Teknis

Satu file berubah: `resources/views/layouts/app.blade.php`.

### 1. Markup topbar (baru)

Ditaruh tepat setelah `<body>`/awal shell, di luar `.sidebar-owner`:

```html
<header class="app-topbar">
  <button type="button" class="app-topbar-burger" data-sidebar-toggle
          aria-label="Buka/tutup sidebar" title="Buka/tutup sidebar">
    <i class="fa-solid fa-bars"></i>
  </button>
  <div class="app-topbar-brand">
    <img src="{{ asset('images/logoPTPNNew.png') }}" alt="Logo PTPN">
    <div>
      <div class="app-topbar-title">Agenda Online</div>
      <div class="app-topbar-sub">Sistem Monitoring Dokumen Keuangan</div>
    </div>
  </div>
</header>
```

- Hamburger memakai atribut `data-sidebar-toggle` yang **sudah** di-bind oleh
  JS existing (`querySelectorAll('[data-sidebar-toggle]')`) → tidak perlu JS baru.
- Brand bukan link (tidak ada navigasi per-role di brand).

### 2. CSS topbar (baru)

- `.app-topbar`: `position:fixed; top:0; left:0; right:0; height:60px;
  display:flex; align-items:center; gap:12px; padding:0 16px; background:#fff;
  border-bottom:1px solid #E2E8F0; z-index:1100` (di atas sidebar).
- `.app-topbar-burger`: tombol kotak polos (border tipis, hover abu) — gaya
  serupa `.owner-sidebar-toggle` lama.
- `.app-topbar-title`: tebal, ~14px, warna gelap `#1a2340`.
- `.app-topbar-sub`: ~11px, abu `#a0aec0`.
- Dark mode: `.dark .app-topbar { background:#1e293b; border-color:#334155 }`
  + warna teks terang.
- Mobile (≤768px): topbar tetap; teks brand boleh truncate (white-space nowrap
  + overflow hidden), tanpa perilaku khusus lain.

### 3. Penyesuaian sidebar

- **Hapus** blok `.owner-sidebar-logo` (logo + teks + tombol collapse) dari
  markup sidebar.
- `.sidebar-owner`: `top:60px; height:calc(100vh - 60px)`.
- CSS `.owner-sidebar-logo*`, `.owner-logo-*`, `.owner-sidebar-toggle*` yang
  jadi dead code ikut dihapus/disesuaikan (aturan `.sidebar-collapsed` yang
  merujuk elemen brand sidebar dibuang; perilaku collapsed sidebar lain tetap).

### 4. Penyesuaian konten

- `body.owner-layout .content` (dan turunannya): tambah `margin-top: 60px`
  agar tidak tertutup topbar.

## Yang TIDAK Berubah

- Isi menu tiap role, kartu user + Logout di bawah sidebar, semua logika lain.
- `layouts.programmer` (halaman khusus programmer).
- Mekanisme collapse sidebar (JS `data-sidebar-toggle` + localStorage) — hanya
  pindah pemicunya ke topbar.

## Acceptance

1. `php artisan view:cache` bersih (tanpa error compile).
2. Render server per role (tinker, seperti verifikasi sebelumnya): halaman
   utama tiap role + `/2fa/setup` programmer → status 200, `app-topbar`
   muncul tepat 1x, `owner-sidebar-logo` = 0.
3. Klik hamburger mengecilkan/melebarkan sidebar (cek visual user).
4. Konten tidak tertutup topbar (cek visual user, terang & gelap).
5. Commit pesan Bahasa Indonesia, deploy, cache clear.
