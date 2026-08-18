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
- **Testing Hapus Dokumen**: Jika ingin melakukan test delete, buat dokumen baru terlebih dahulu, lalu lakukan pengujian hapus pada dokumen baru tersebut.


