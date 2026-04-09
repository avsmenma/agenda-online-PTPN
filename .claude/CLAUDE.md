# Agenda Online PTPN — Project Rules

## 🚫 ABSOLUTE RULE — BACA INI PERTAMA
DILARANG KERAS menjalankan perintah apapun di terminal, tanpa terkecuali.
Ini termasuk: php artisan, composer, npm, node, mysql, git, dan semua
perintah CLI lainnya.

JANGAN tawarkan, JANGAN tanya izin, JANGAN eksekusi.
Jika perlu menjalankan perintah, HANYA tuliskan dalam blok kode
dengan label "⚠️ Jalankan manual di server:" dan berhenti di situ.

Alasan: proyek ini hanya bisa dijalankan di VPS Ubuntu Alibaba Cloud
(IP: 163.61.58.92), bukan di mesin lokal.

## ✅ Yang boleh dilakukan lokal
- Membuat, mengedit, menghapus file
- Membaca struktur folder dan file
- Menulis kode

## 📝 Commit Message Format
Setiap selesai melakukan perubahan kode, SELALU sertakan di akhir
respons blok perintah git lengkap dengan format:

git add [file1] [file2] ...
git commit -m "feat: [deskripsi singkat perubahan dalam bahasa indonesia]"

Aturan:
- `git add` harus menyebutkan file spesifik yang diubah, BUKAN `git add .`
- Pesan commit dalam BAHASA INDONESIA
- Gunakan prefix: feat, fix, refactor, chore, docs sesuai jenis perubahan
- Contoh: `git commit -m "feat: tambah audit trail untuk role programmer"`

## 🛠️ Tech Stack
- Laravel 12, PHP ^8.2
- MySQL
- Tailwind CSS v4
- Alpine.js
- Pusher WebSocket + Laravel Echo
- Fonnte API (WhatsApp gateway)
- RBAC dengan 10 role
- 2FA via Google Authenticator (pragmarx/google2fa)

## 📁 Struktur Penting
- Middleware RBAC: CheckRole, CheckBagianRole
- Helper: ActivityLogHelper, DokumenHelper
- Trait: LogsProgrammerActivity (audit trail programmer)

## 📚 Documentation
SELALU gunakan Context7 untuk mengambil dokumentasi terbaru sebelum
menulis kode yang berkaitan dengan library berikut:
- Laravel 12
- Tailwind CSS v4
- Alpine.js
- Pusher / Laravel Echo
- pragmarx/google2fa
- maatwebsite/excel

Jangan mengandalkan pengetahuan internal — selalu resolve via Context7
terlebih dahulu.