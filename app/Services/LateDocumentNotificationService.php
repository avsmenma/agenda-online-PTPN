<?php

namespace App\Services;

use App\Models\Dokumen;
use App\Models\DokumenRoleData;
use App\Models\User;
use App\Models\WhatsAppNotificationLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class LateDocumentNotificationService
{
    protected FonnteWhatsAppService $whatsAppService;
    protected array $thresholds;
    protected array $notifyRoles;
    protected int $cooldownHours;

    public function __construct(FonnteWhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
        $this->thresholds = config('fonnte.thresholds', []);
        $this->notifyRoles = config('fonnte.notify_roles', []);
        $this->cooldownHours = config('fonnte.cooldown_hours', 24);
    }

    /**
     * Check all roles for late documents and send notifications
     */
    public function checkAndNotifyLateDocuments(bool $dryRun = false): array
    {
        $results = [
            'total_checked' => 0,
            'notifications_sent' => 0,
            'notifications_skipped' => 0,
            'errors' => 0,
            'details' => [],
        ];

        foreach ($this->notifyRoles as $roleCode) {
            Log::info("[LateDocumentNotification] Checking role: {$roleCode}");

            $roleResult = $this->processRoleNotifications($roleCode, $dryRun);

            $results['total_checked'] += $roleResult['checked'];
            $results['notifications_sent'] += $roleResult['sent'];
            $results['notifications_skipped'] += $roleResult['skipped'];
            $results['errors'] += $roleResult['errors'];
            $results['details'][$roleCode] = $roleResult;
        }

        return $results;
    }

    /**
     * Process notifications for a specific role
     */
    protected function processRoleNotifications(string $roleCode, bool $dryRun = false): array
    {
        $result = [
            'checked' => 0,
            'sent' => 0,
            'skipped' => 0,
            'errors' => 0,
            'documents' => [],
        ];

        // Get late documents for this role
        $lateDocuments = $this->getLateDocumentsByRole($roleCode);
        $result['checked'] = $lateDocuments->count();

        if ($lateDocuments->isEmpty()) {
            Log::info("[LateDocumentNotification] No late documents for role: {$roleCode}");
            return $result;
        }

        // Get users for this role with phone numbers
        $users = $this->getUsersByRole($roleCode);

        if ($users->isEmpty()) {
            Log::warning("[LateDocumentNotification] No users with phone numbers for role: {$roleCode}");
            return $result;
        }

        // Process each late document
        foreach ($lateDocuments as $dokumen) {
            $docResult = $this->processDocumentNotification($dokumen, $roleCode, $users, $dryRun);

            if ($docResult['sent']) {
                $result['sent']++;
            } elseif ($docResult['skipped']) {
                $result['skipped']++;
            } elseif ($docResult['error']) {
                $result['errors']++;
            }

            $result['documents'][] = $docResult;
        }

        return $result;
    }

    /**
     * Process notification for a single document
     */
    protected function processDocumentNotification(
        object $dokumen,
        string $roleCode,
        Collection $users,
        bool $dryRun = false
    ): array {
        $result = [
            'dokumen_id' => $dokumen->id,
            'nomor_agenda' => $dokumen->nomor_agenda,
            'message_type' => null,
            'sent' => false,
            'skipped' => false,
            'error' => false,
            'reason' => null,
        ];

        // Determine message type based on age
        $messageType = $this->determineMessageType($dokumen, $roleCode);
        $result['message_type'] = $messageType;

        if (!$messageType) {
            $result['skipped'] = true;
            $result['reason'] = 'below_threshold';
            return $result;
        }

        // Check cooldown
        if ($this->wasRecentlyNotified($dokumen->id, $roleCode, $messageType)) {
            $result['skipped'] = true;
            $result['reason'] = 'cooldown_active';
            return $result;
        }

        // Generate message
        $message = $this->generateNotificationMessage($dokumen, $roleCode, $messageType);

        // Dry run mode - don't actually send
        if ($dryRun) {
            Log::info("[LateDocumentNotification][DRY-RUN] Would send notification", [
                'dokumen_id' => $dokumen->id,
                'role' => $roleCode,
                'type' => $messageType,
                'users' => $users->pluck('phone_number')->toArray(),
            ]);
            $result['sent'] = true;
            $result['reason'] = 'dry_run';
            return $result;
        }

        // Send to all users of this role
        $sentToAtLeastOne = false;
        foreach ($users as $user) {
            $sendResult = $this->sendNotification($dokumen, $user, $roleCode, $messageType, $message);
            if ($sendResult) {
                $sentToAtLeastOne = true;
            }
        }

        if ($sentToAtLeastOne) {
            $result['sent'] = true;
            $result['reason'] = 'success';
        } else {
            $result['error'] = true;
            $result['reason'] = 'send_failed';
        }

        return $result;
    }

    /**
     * Get documents that are late for a specific role (active documents only)
     */
    public function getLateDocumentsByRole(string $roleCode): Collection
    {
        $now = Carbon::now();
        $thresholds = $this->thresholds[$roleCode] ?? ['warning' => 24, 'danger' => 72];
        $warningThreshold = $thresholds['warning'];

        // Map roleCode to expected current_handler value
        $roleHandlerMapping = [
            'team_verifikasi' => 'team_verifikasi',
            'perpajakan' => 'perpajakan',
            'akutansi' => 'akutansi',
            'pembayaran' => 'pembayaran',
        ];
        $expectedHandler = $roleHandlerMapping[$roleCode] ?? $roleCode;

        // Query documents that:
        // 1. Have been received by this role (received_at is set)
        // 2. Are still active (current_handler matches this role)
        // 3. Have NOT been processed yet (processed_at is null)
        // 4. Age >= warning threshold
        $query = Dokumen::query()
            ->join('dokumen_role_data', 'dokumens.id', '=', 'dokumen_role_data.dokumen_id')
            ->whereRaw('LOWER(dokumen_role_data.role_code) = ?', [strtolower($roleCode)])
            ->whereNotNull('dokumen_role_data.received_at')
            ->whereNull('dokumen_role_data.processed_at')
            ->whereRaw('LOWER(dokumens.current_handler) = ?', [strtolower($expectedHandler)]);

        // For pembayaran, also check status_pembayaran is not sudah_dibayar
        if ($roleCode === 'pembayaran') {
            $query->where(function ($q) {
                $q->whereNull('dokumens.status_pembayaran')
                    ->orWhere('dokumens.status_pembayaran', '!=', 'sudah_dibayar');
            });
        }

        $query->select(
            'dokumens.*',
            'dokumen_role_data.received_at as role_received_at',
            'dokumen_role_data.processed_at as role_processed_at'
        );

        $documents = $query->get();

        // Filter by age and calculate additional data
        return $documents->filter(function ($doc) use ($now, $warningThreshold) {
            if (!$doc->role_received_at) {
                return false;
            }

            $receivedAt = Carbon::parse($doc->role_received_at);
            $ageHours = $receivedAt->diffInHours($now);

            // Only include documents that have reached at least warning threshold
            return $ageHours >= $warningThreshold;
        })->map(function ($doc) use ($now) {
            // Add calculated fields
            $receivedAt = Carbon::parse($doc->role_received_at);
            $ageHours = $receivedAt->diffInHours($now);
            $ageDays = floor($ageHours / 24);
            $remainingHours = $ageHours % 24;

            $doc->age_hours = $ageHours;
            $doc->age_days = $ageDays;
            $doc->age_formatted = $this->formatAge($ageDays, $remainingHours);

            return $doc;
        });
    }

    /**
     * Get users by role who have phone numbers
     */
    protected function getUsersByRole(string $roleCode): Collection
    {
        // Map roleCode to database role values
        $roleMapping = [
            'team_verifikasi' => ['team_verifikasi', 'verifikasi', 'Team Verifikasi'],
            'perpajakan' => ['perpajakan', 'Perpajakan'],
            'akutansi' => ['akutansi', 'Akutansi'],
            'pembayaran' => ['pembayaran', 'Pembayaran'],
        ];

        $roles = $roleMapping[$roleCode] ?? [$roleCode];

        return User::whereIn('role', $roles)
            ->whereNotNull('phone_number')
            ->where('phone_number', '!=', '')
            ->get();
    }

    /**
     * Determine message type based on document age
     */
    protected function determineMessageType(object $dokumen, string $roleCode): ?string
    {
        $thresholds = $this->thresholds[$roleCode] ?? ['warning' => 24, 'danger' => 72];
        $ageHours = $dokumen->age_hours ?? 0;

        if ($ageHours >= $thresholds['danger']) {
            return 'danger';
        }

        if ($ageHours >= $thresholds['warning']) {
            return 'warning';
        }

        return null;
    }

    /**
     * Check if notification was recently sent (within cooldown period)
     */
    protected function wasRecentlyNotified(int $dokumenId, string $roleCode, string $messageType): bool
    {
        return WhatsAppNotificationLog::wasRecentlySent(
            $dokumenId,
            $roleCode,
            $messageType,
            $this->cooldownHours
        );
    }

    /**
     * Generate notification message
     */
    public function generateNotificationMessage(object $dokumen, string $roleCode, string $messageType): string
    {
        $roleName = $this->getRoleDisplayName($roleCode);
        $statusIcon = $messageType === 'danger' ? '🔴' : '🟡';
        $statusText = $messageType === 'danger' ? 'TERLAMBAT' : 'PERINGATAN';

        $nilaiFormatted = 'Rp ' . number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.');

        $message = "{$statusIcon} *{$statusText} - DOKUMEN PERLU DIPROSES*\n\n";
        $message .= "📋 *No. Agenda:* {$dokumen->nomor_agenda}\n";
        $message .= "📝 *Uraian:* " . ($dokumen->uraian_spp ?? '-') . "\n";
        $message .= "💰 *Nilai:* {$nilaiFormatted}\n";
        $message .= "🏢 *Bagian:* " . ($dokumen->bagian ?? '-') . "\n";
        $message .= "⏰ *Sudah di {$roleName}:* " . ($dokumen->age_formatted ?? '-') . "\n\n";

        if ($messageType === 'danger') {
            $message .= "⚠️ Dokumen ini sudah melewati batas waktu normal. ";
            $message .= "Mohon segera diproses untuk menghindari keterlambatan lebih lanjut.\n\n";
        } else {
            $message .= "⏳ Dokumen ini mendekati batas waktu. ";
            $message .= "Mohon segera diproses.\n\n";
        }

        $message .= "🔗 _Sistem Agenda Online PTPN_";

        return $message;
    }

    /**
     * Send notification to a user
     */
    protected function sendNotification(
        object $dokumen,
        User $user,
        string $roleCode,
        string $messageType,
        string $message
    ): bool {
        // Create log entry
        $log = WhatsAppNotificationLog::create([
            'dokumen_id' => $dokumen->id,
            'role_code' => $roleCode,
            'user_id' => $user->id,
            'phone_number' => $user->phone_number,
            'message_type' => $messageType,
            'message' => $message,
            'status' => 'pending',
        ]);

        // Send message
        $result = $this->whatsAppService->sendMessage($user->phone_number, $message);

        if ($result['success']) {
            $log->markAsSuccess(json_encode($result['response'] ?? []));
            Log::info("[LateDocumentNotification] Notification sent", [
                'dokumen_id' => $dokumen->id,
                'user_id' => $user->id,
                'phone' => $user->phone_number,
            ]);
            return true;
        }

        $log->markAsFailed($result['message'] ?? 'Unknown error');
        Log::warning("[LateDocumentNotification] Failed to send notification", [
            'dokumen_id' => $dokumen->id,
            'user_id' => $user->id,
            'phone' => $user->phone_number,
            'error' => $result['message'] ?? 'Unknown error',
        ]);

        return false;
    }

    /**
     * Format age as human-readable string
     */
    protected function formatAge(int $days, int $hours): string
    {
        $parts = [];

        if ($days > 0) {
            $parts[] = "{$days} hari";
        }

        if ($hours > 0 || empty($parts)) {
            $parts[] = "{$hours} jam";
        }

        return implode(' ', $parts);
    }

    /**
     * Get role display name
     */
    protected function getRoleDisplayName(string $roleCode): string
    {
        $names = [
            'team_verifikasi' => 'Team Verifikasi',
            'perpajakan' => 'Team Perpajakan',
            'akutansi' => 'Team Akutansi',
            'pembayaran' => 'Tim Pembayaran',
        ];

        return $names[$roleCode] ?? ucfirst($roleCode);
    }
}
