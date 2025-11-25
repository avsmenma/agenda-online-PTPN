# Setup WhatsApp Notification

## Overview
Sistem notifikasi WhatsApp untuk deadline dokumen yang terintegrasi dengan sistem notifikasi web.

## Prerequisites
- WhatsApp API provider (Fonte, Fonnte, Twilio, atau Custom API)
- Nomor WhatsApp untuk setiap user yang akan menerima notifikasi

## Installation

### 1. Run Migration
```bash
php artisan migrate
```

### 2. Setup Environment Variables
Tambahkan ke file `.env`:

```env
# WhatsApp Notification Settings
WHATSAPP_ENABLED=true
WHATSAPP_METHOD=fonte  # fonte, fonnte, twilio, custom

# Nomor Pengirim WhatsApp
WHATSAPP_SENDER_NUMBER=089524429935

# For Fonte API (https://fonte.id)
WHATSAPP_API_URL=https://api.fonte.id/send
WHATSAPP_API_KEY=your_api_key_here

# For Fonnte API (https://fonnte.com)
# WHATSAPP_API_URL=https://api.fonnte.com/send
# WHATSAPP_API_KEY=your_api_key_here

# For Twilio WhatsApp API
# WHATSAPP_METHOD=twilio
# TWILIO_ACCOUNT_SID=your_account_sid
# TWILIO_WHATSAPP_FROM_NUMBER=whatsapp:+14155238886
# WHATSAPP_API_KEY=your_auth_token
```

### 3. Setup User WhatsApp Numbers
Tambahkan nomor WhatsApp untuk setiap user di database:

```sql
UPDATE users SET whatsapp_number = '081234567890' WHERE role = 'Akutansi';
UPDATE users SET whatsapp_number = '081234567891' WHERE role = 'Perpajakan';
UPDATE users SET whatsapp_number = '081234567892' WHERE role = 'IbuA';
```

Atau melalui aplikasi admin jika tersedia.

## Supported Providers

### 1. Fonte API (https://fonte.id)
- Daftar di https://fonte.id
- Dapatkan API key
- Set `WHATSAPP_METHOD=fonte`

### 2. Fonnte API (https://fonnte.com)
- Daftar di https://fonnte.com
- Dapatkan API key
- Set `WHATSAPP_METHOD=fonnte`

### 3. Twilio WhatsApp API
- Daftar di https://www.twilio.com
- Setup WhatsApp Business API
- Set `WHATSAPP_METHOD=twilio`
- Isi `TWILIO_ACCOUNT_SID` dan `TWILIO_WHATSAPP_FROM_NUMBER`

### 4. Custom API
- Set `WHATSAPP_METHOD=custom`
- Sesuaikan method `sendViaCustom()` di `WhatsAppNotificationService.php`

## How It Works

1. **Command `deadline:check`** berjalan setiap 6 jam
2. Mengecek semua dokumen dengan deadline yang terlambat
3. Membuat/update notifikasi di database
4. Mengirim notifikasi WhatsApp ke semua user di handler tersebut
5. Notifikasi berhenti jika dokumen sudah dikirim ke handler berikutnya

## Message Format

Notifikasi WhatsApp akan berisi:
- Handler (Akuntansi/Perpajakan/Ibu A)
- Nomor Agenda
- Nomor SPP
- Status (Kuning/Merah)
- Jumlah hari terlambat
- Tanggal deadline
- Pesan reminder

## Testing

Test manual:
```bash
php artisan tinker
```

```php
$service = new \App\Services\WhatsAppNotificationService();
$service->sendNotification('081234567890', 'Test message');
```

## Troubleshooting

1. **Notifikasi tidak terkirim**
   - Cek `WHATSAPP_ENABLED=true` di `.env`
   - Cek API key dan URL sudah benar
   - Cek log: `storage/logs/laravel.log`

2. **Format nomor salah**
   - Pastikan nomor dalam format: 081234567890 (tanpa +62)
   - Atau: +6281234567890 (dengan +62)
   - Sistem akan otomatis format ke format yang benar

3. **API Error**
   - Cek dokumentasi provider API yang digunakan
   - Pastikan API key masih valid
   - Cek quota/limit API

