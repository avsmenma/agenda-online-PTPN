<?php

/**
 * Create Test Document untuk Lock System Testing
 * Membuat dokumen baru sebagai IbuA lalu mengirim ke IbuB
 */

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CREATE TEST DOCUMENT FOR LOCK SYSTEM ===\n\n";

use App\Models\Dokumen;
use Illuminate\Support\Facades\DB;

try {
    DB::beginTransaction();

    // 1. Create dokumen baru sebagai IbuA
    echo "📝 Step 1: Membuat dokumen baru sebagai IbuA...\n";

    $dokumen = Dokumen::create([
        'nomor_agenda' => 'TEST-' . date('Ymd-His'),
        'bulan' => date('F'),
        'tahun' => date('Y'),
        'tanggal_masuk' => now(),
        'nomor_spp' => 'SPP-TEST-' . date('Ymd'),
        'tanggal_spp' => now(),
        'uraian_spp' => 'Test document for lock system verification',
        'nilai_rupiah' => 1000000,
        'kategori' => 'Investasi on farm',
        'jenis_dokumen' => 'Test Document',
        'status' => 'draft',
        'created_by' => 'ibuA',
        'current_handler' => 'ibuA',
    ]);

    echo "   ✅ Dokumen created: ID {$dokumen->id}, Nomor Agenda: {$dokumen->nomor_agenda}\n";
    echo "   Status: {$dokumen->status}, Handler: {$dokumen->current_handler}\n";
    echo "   Deadline: " . ($dokumen->deadline_at ? $dokumen->deadline_at->format('Y-m-d H:i:s') : 'NULL') . "\n\n";

    // 2. Kirim ke IbuB (simulasi sendToIbuB method)
    echo "📤 Step 2: Mengirim dokumen ke IbuB...\n";

    $dokumen->update([
        'status' => 'sent_to_ibub',
        'current_handler' => 'ibuB',
        'sent_to_ibub_at' => now(),
        // PERHATIKAN: TIDAK set deadline_at - ini yang memunculkan lock
    ]);

    echo "   ✅ Dokumen sent to IbuB\n";
    echo "   Status: {$dokumen->status}, Handler: {$dokumen->current_handler}\n";
    echo "   Deadline: " . ($dokumen->deadline_at ? $dokumen->deadline_at->format('Y-m-d H:i:s') : 'NULL') . "\n\n";

    // 3. Test lock logic
    echo "🔒 Step 3: Testing lock logic...\n";

    $isLocked = is_null($dokumen->deadline_at) && in_array($dokumen->status, ['sent_to_ibub']);

    echo "   Lock Logic: \$isLocked = is_null(\$dokumen->deadline_at) && in_array(\$dokumen->status, ['sent_to_ibub'])\n";
    echo "   is_null(deadline_at): " . (is_null($dokumen->deadline_at) ? 'TRUE' : 'FALSE') . "\n";
    echo "   in_array('sent_to_ibub'): " . (in_array($dokumen->status, ['sent_to_ibub']) ? 'TRUE' : 'FALSE') . "\n";
    echo "   Result: " . ($isLocked ? '🔒 LOCKED' : '🔓 UNLOCKED') . "\n";
    echo "   Expected: 🔒 LOCKED\n";
    echo "   Status: " . ($isLocked ? '✅ CORRECT' : '❌ INCORRECT') . "\n\n";

    DB::commit();

    echo "🎯 SUCCESS!\n";
    echo "   📄 Test document created: ID {$dokumen->id}\n";
    echo "   🔗 Nomor Agenda: {$dokumen->nomor_agenda}\n";
    echo "   📍 Go to IbuB dashboard and check if this document shows LOCKED state\n";
    echo "   ⏰ Try to set deadline for this document\n";
    echo "   🔓 After setting deadline, document should be UNLOCKED\n\n";

    echo "📋 Manual Testing Steps:\n";
    echo "   1. Login as IbuA → create document → send to IbuB\n";
    echo "   2. Login as IbuB → go to document list\n";
    echo "   3. Look for document: {$dokumen->nomor_agenda}\n";
    echo "   4. Expected: 🔒 Locked state with disabled buttons\n";
    echo "   5. Click 'Tetapkan Deadline' → set 1-3 days\n";
    echo "   6. Expected: 🔓 Unlocked state with enabled buttons\n";

} catch (Exception $e) {
    DB::rollback();
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}