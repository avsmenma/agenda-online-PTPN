<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Dokumen;
use Carbon\Carbon;

echo "=== Testing Notification System ===\n\n";

// Check if there are any returned documents
$returned = Dokumen::where('created_by', 'ibuA')
    ->where('status', 'returned_to_ibua')
    ->orderBy('returned_to_ibua_at', 'desc')
    ->get();

echo "Found " . $returned->count() . " returned documents:\n";
foreach ($returned as $doc) {
    echo "- ID: {$doc->id} | Agenda: {$doc->nomor_agenda} | Returned: {$doc->returned_to_ibua_at} | Status: {$doc->status}\n";
}

echo "\n=== Testing API Response ===\n";

// Simulate API call
$lastChecked = time() - 3600; // 1 hour ago
$lastCheckedDate = date('Y-m-d H:i:s', $lastChecked);

$filtered = Dokumen::where('created_by', 'ibuA')
    ->where('status', 'returned_to_ibua')
    ->where('returned_to_ibua_at', '>', $lastCheckedDate)
    ->latest('returned_to_ibua_at')
    ->take(10)
    ->get();

$total = Dokumen::where('created_by', 'ibuA')
    ->where('status', 'returned_to_ibua')
    ->count();

$response = [
    'has_updates' => $filtered->count() > 0,
    'new_count' => $filtered->count(),
    'total_documents' => $total,
    'returned_documents' => $filtered->map(function($doc) {
        return [
            'id' => $doc->id,
            'nomor_agenda' => $doc->nomor_agenda,
            'nomor_spp' => $doc->nomor_spp,
            'uraian_spp' => $doc->uraian_spp,
            'nilai_rupiah' => $doc->nilai_rupiah,
            'alasan_pengembalian' => $doc->alasan_pengembalian,
            'status' => $doc->status,
            'returned_at' => $doc->returned_to_ibua_at?->format('d/m/Y H:i'),
        ];
    })->toArray(),
    'last_checked' => time()
];

echo "API Response:\n";
echo json_encode($response, JSON_PRETTY_PRINT) . "\n";

if (!$returned->count()) {
    echo "\n=== Creating Test Document ===\n";
    // Create a test returned document
    $doc = Dokumen::where('created_by', 'ibuA')
        ->where('status', '!=', 'returned_to_ibua')
        ->first();

    if ($doc) {
        echo "Updating document: {$doc->nomor_agenda}\n";
        $doc->update([
            'status' => 'returned_to_ibua',
            'current_handler' => 'ibuA',
            'alasan_pengembalian' => 'Test notifikasi real-time - ' . date('Y-m-d H:i:s'),
            'returned_to_ibua_at' => Carbon::now()->subMinutes(5)
        ]);
        echo "Test document created successfully!\n";
    } else {
        echo "No document found for testing\n";
    }
}

echo "\n=== Test Complete ===\n";