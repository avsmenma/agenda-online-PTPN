<?php

namespace App\Console\Commands;

use App\Services\LateDocumentNotificationService;
use Illuminate\Console\Command;

class SendLateDocumentNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-late-documents 
                            {--dry-run : Run without actually sending notifications}
                            {--role= : Only process specific role (team_verifikasi, perpajakan, akutansi, pembayaran)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for late documents and send WhatsApp notifications to responsible roles';

    /**
     * Execute the console command.
     */
    public function handle(LateDocumentNotificationService $service): int
    {
        $dryRun = $this->option('dry-run');
        $specificRole = $this->option('role');

        $this->info('================================');
        $this->info('Late Document Notification Check');
        $this->info('================================');
        $this->info('Time: ' . now()->format('Y-m-d H:i:s'));

        if ($dryRun) {
            $this->warn('>>> DRY RUN MODE - No messages will be sent <<<');
        }

        if ($specificRole) {
            $this->info("Processing only role: {$specificRole}");
        }

        $this->newLine();

        // Check if WhatsApp service is configured
        if (!config('fonnte.enabled')) {
            $this->error('WhatsApp notifications are disabled in configuration.');
            $this->info('Set WHATSAPP_NOTIFICATIONS_ENABLED=true in .env to enable.');
            return Command::FAILURE;
        }

        if (empty(config('fonnte.api_token'))) {
            $this->error('Fonnte API token not configured.');
            $this->info('Set FONNTE_API_TOKEN in .env file.');
            return Command::FAILURE;
        }

        try {
            $results = $service->checkAndNotifyLateDocuments($dryRun);

            $this->displayResults($results);

            if ($results['errors'] > 0) {
                return Command::FAILURE;
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }

    /**
     * Display the results in a formatted table
     */
    protected function displayResults(array $results): void
    {
        $this->newLine();
        $this->info('=== Summary ===');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Documents Checked', $results['total_checked']],
                ['Notifications Sent', $results['notifications_sent']],
                ['Notifications Skipped', $results['notifications_skipped']],
                ['Errors', $results['errors']],
            ]
        );

        // Show per-role breakdown
        if (!empty($results['details'])) {
            $this->newLine();
            $this->info('=== Per Role Breakdown ===');

            foreach ($results['details'] as $role => $roleResult) {
                $this->newLine();
                $this->line("📋 <fg=cyan>{$this->getRoleDisplayName($role)}</>");
                $this->line("   Checked: {$roleResult['checked']} | Sent: {$roleResult['sent']} | Skipped: {$roleResult['skipped']} | Errors: {$roleResult['errors']}");

                // Show document details if any were processed
                if (!empty($roleResult['documents'])) {
                    foreach ($roleResult['documents'] as $doc) {
                        $status = $doc['sent'] ? '✅' : ($doc['skipped'] ? '⏭️' : '❌');
                        $reason = $doc['reason'] ?? 'unknown';
                        $type = $doc['message_type'] ?? 'N/A';
                        $this->line("   {$status} Dok #{$doc['dokumen_id']} ({$doc['nomor_agenda']}) - Type: {$type} - {$reason}");
                    }
                }
            }
        }

        $this->newLine();
        $this->info('Completed at: ' . now()->format('Y-m-d H:i:s'));
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
