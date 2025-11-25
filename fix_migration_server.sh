#!/bin/bash

# Script untuk fix migration error di server
# Jalankan di server: bash fix_migration_server.sh

echo "🔧 Fixing Migration Error..."

# Step 1: Rollback migration yang gagal
echo "Step 1: Rolling back failed migration..."
php artisan migrate:rollback --step=1

# Step 2: Cek apakah kolom status_pembayaran sudah ada
echo "Step 2: Checking if status_pembayaran column exists..."
php artisan tinker --execute="echo Schema::hasColumn('dokumens', 'status_pembayaran') ? 'EXISTS' : 'NOT EXISTS';"

# Step 3: Jika kolom belum ada, tambahkan secara manual
echo "Step 3: Adding missing columns if needed..."
php artisan tinker --execute="
if (!Schema::hasColumn('dokumens', 'sent_to_pembayaran_at')) {
    DB::statement('ALTER TABLE dokumens ADD COLUMN sent_to_pembayaran_at TIMESTAMP NULL');
    echo 'Added sent_to_pembayaran_at\n';
}
if (!Schema::hasColumn('dokumens', 'status_pembayaran')) {
    DB::statement('ALTER TABLE dokumens ADD COLUMN status_pembayaran ENUM(\"siap_dibayar\", \"sudah_dibayar\") NULL');
    echo 'Added status_pembayaran\n';
}
"

# Step 4: Tandai migration 2025_11_23_232000 sudah dijalankan (jika belum)
echo "Step 4: Marking migration as completed..."
php artisan tinker --execute="
if (!DB::table('migrations')->where('migration', '2025_11_23_232000_add_pembayaran_fields_to_dokumens_table')->exists()) {
    \$batch = DB::table('migrations')->max('batch') + 1;
    DB::table('migrations')->insert([
        'migration' => '2025_11_23_232000_add_pembayaran_fields_to_dokumens_table',
        'batch' => \$batch
    ]);
    echo 'Migration marked as completed\n';
} else {
    echo 'Migration already exists\n';
}
"

# Step 5: Jalankan migration lagi
echo "Step 5: Running migrations..."
php artisan migrate --force

echo "✅ Done! Check if migration was successful."

