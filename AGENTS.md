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
