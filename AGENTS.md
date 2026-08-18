# Aturan Kerja & Workflow Deployment

## 1. Git Workflow & Commit Rules
- **Commit Per File**: Setiap kali melakukan update, lakukan commit secara terpisah per file (atomic commit) dengan pesan commit yang jelas dan deskriptif.
- **Push ke GitHub**: Setelah commit selesai, lakukan `git push origin <branch>` (branch aktif: `codinggemini`).

## 2. Otomatisasi Deploy ke Server VPS
Setelah push berhasil, selalu akses server VPS dan lakukan `git pull` serta clear cache dengan perintah berikut:

```bash
ssh -i "C:\Users\ASUS\.ssh\crypto_bot_vps" root@163.61.58.92 "cd /var/www/agenda-online-PTPN && git pull && php artisan cache:clear && php artisan view:clear && php artisan config:clear && php artisan route:clear"
```

- **Host VPS**: `163.61.58.92`
- **User**: `root`
- **SSH Key**: `C:\Users\ASUS\.ssh\crypto_bot_vps`
- **Direktori Project VPS**: `/var/www/agenda-online-PTPN`

## 3. Pembersihan File Screenshot Testing
- **Bersihkan Gambar Testing**: Setiap kali selesai melakukan testing mandiri (Playwright, dsb.), selalu hapus/bersihkan seluruh file gambar screenshot hasil pengujian dari folder project agar tidak menumpuk dan workspace tetap bersih.

## 4. Akun Uji Coba & Prosedur Testing
- **Operator**: Username `input`, Password `12345678` (URL: `/documents`)
- **Verifikasi**: Username `verifikasi`, Password `12345678` (URL: `/documents/verifikasi`)
- **Bagian (SKH)**: Username `skh`, Password `12345678` (URL: `/bagian/documents`)
- **Pembayaran**: Username `pembayaran`, Password `12345678` (URL: `/documents/pembayaran/daftar`)
- **Testing Hapus Dokumen**: Jika ingin melakukan test delete, buat baris dokumen baru melalui tombol "+ Tambah Baris" pada tabel `/documents`, lalu lakukan pengujian hapus pada dokumen baru tersebut.

## 5. Autonomous E2E Testing Protocol via MCP Playwright
Setiap kali selesai melakukan update kode atau refactor, WAJIB menjalankan sesi end-to-end testing secara otonom menggunakan MCP Playwright sebelum melaporkan penyelesaian tugas. Jangan hanya menguji "happy path". Harus memvalidasi integritas sistem secara menyeluruh dengan mematuhi 5 poin checklist berikut:

1. **BACKGROUND MONITORING (Network & Console)**:
   - Pantau console log & network responses (`page.on('console')`, `page.on('response')`).
   - GAGALKAN pengujian jika ditemukan error JavaScript baru atau unhandled 4xx/5xx HTTP responses pada request/API.
2. **STATE PERSISTENCE & DATA INTEGRITY**:
   - Setelah melakukan aksi simpan, submit, atau perubahan state, WAJIB lakukan `page.reload()` (F5).
   - Pastikan data/state terbaru tetap bertahan setelah reload untuk membuktikan data benar-benar tersimpan di database/backend, bukan sekadar optimistic UI.
3. **MULTI-ROLE & WORKFLOW ISOLATION**:
   - Jika fitur melibatkan workflow multi-step, tracking dokumen, atau hak akses multi-role, uji transisi state di minimal 2 role yang berbeda.
   - Pastikan isolasi state: tindakan di satu role/bagian TIDAK menimpa, me-reset, atau merusak status historis role sebelumnya.
4. **MODERN UI/UX REGRESSION**:
   - Periksa visual hierarchy (z-index), modal, dropdown, card, overlay agar tidak terpotong (clipping) atau tumpang tindih secara tidak wajar.
   - Uji responsivitas viewport pada ukuran mobile/layar lipat untuk memastikan layout tidak pecah.
5. **UNHAPPY PATHS & VALIDATION**:
   - Uji input kosong, format salah, atau tipe data yang tidak sesuai secara sengaja.
   - Pastikan pesan validasi muncul secara rapi dan user-friendly di UI.
   - Pastikan TIDAK ADA layar error debug mentah (seperti Laravel Ignition/Whoops error pages) yang bocor ke user.

**REPORTING**:
Sertakan ringkasan poin-poin dari 5 checklist di atas dalam laporan akhir setiap pengujian. Jika ada langkah yang gagal, langsung perbaiki kodenya dan jalankan ulang pengujian sebelum meminta review.



