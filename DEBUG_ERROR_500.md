# Cara Melihat Error 500 yang Sebenarnya

## Masalah
Error 500 tidak menampilkan detail error karena:
1. `APP_DEBUG=false` di production
2. Error handling di controller hanya menampilkan pesan generic

## Solusi 1: Cek Log Laravel (RECOMMENDED)

Error sebenarnya tersimpan di log file. Cek dengan cara berikut:

### Di Server Production:
```bash
# Masuk ke direktori project
cd /var/www/agenda_online_ptpn

# Lihat 50 baris terakhir dari log
tail -n 50 storage/logs/laravel.log

# Atau cari error terbaru
tail -f storage/logs/laravel.log
```

### Di Windows (Local):
```powershell
# Masuk ke direktori project
cd C:\Users\Administrator\Downloads\agenda-online-PTPN

# Buka file log dengan notepad atau editor lain
notepad storage\logs\laravel.log

# Atau gunakan PowerShell untuk melihat baris terakhir
Get-Content storage\logs\laravel.log -Tail 50
```

## Solusi 2: Enable Debug Mode Sementara (UNTUK DEVELOPMENT SAJA)

**PERINGATAN: Jangan enable di production!**

1. Buka file `.env`
2. Ubah:
   ```
   APP_DEBUG=true
   APP_ENV=local
   ```
3. Clear cache:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   php artisan view:clear
   ```
4. Refresh halaman - sekarang error akan ditampilkan dengan detail

5. **PENTING:** Setelah selesai debugging, kembalikan ke:
   ```
   APP_DEBUG=false
   APP_ENV=production
   ```

## Solusi 3: Perbaiki Error Handling di Controller

Saya sudah memperbaiki error handling di `InboxController` agar menampilkan error message yang lebih detail saat development, tapi tetap aman di production.

## Cara Mencari Error di Log

Cari dengan keyword:
- `Error rejecting document from inbox`
- `Error loading inbox index`
- `SQLSTATE`
- `Column not found`
- `Call to undefined`

Contoh error yang mungkin muncul:
```
[2025-01-XX XX:XX:XX] local.ERROR: Error rejecting document from inbox: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'inbox_approval_sent_at' in 'field list'
```

Setelah menemukan error, kirimkan error message lengkapnya untuk diperbaiki.

