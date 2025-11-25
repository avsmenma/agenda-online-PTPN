<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Dokumen;
use App\Models\DeadlineNotification;
use App\Services\WhatsAppNotificationService;
use Carbon\Carbon;

class CheckDeadlineNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deadline:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check deadline notifications for akuntansi, perpajakan, and ibu_a every 6 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking deadline notifications...');
        
        $now = Carbon::now();
        $sixHoursAgo = $now->copy()->subHours(6);
        
        // Check Akuntansi deadlines
        $this->checkHandlerDeadlines('akutansi', 'deadline_at', $now, $sixHoursAgo);
        
        // Check Perpajakan deadlines
        $this->checkHandlerDeadlines('perpajakan', 'deadline_perpajakan_at', $now, $sixHoursAgo);
        
        // Check Ibu A deadlines (using deadline_at)
        $this->checkHandlerDeadlines('ibu_a', 'deadline_at', $now, $sixHoursAgo);
        
        $this->info('Deadline check completed!');
        
        return 0;
    }

    /**
     * Check deadlines for a specific handler
     */
    private function checkHandlerDeadlines($handler, $deadlineField, $now, $sixHoursAgo)
    {
        // Get all notifications for this handler first
        $existingNotifications = DeadlineNotification::where('handler', $handler)
            ->where('deadline_type', $deadlineField)
            ->get();
        
        // Remove notifications for documents that are no longer in this handler
        foreach ($existingNotifications as $notification) {
            $dokumen = Dokumen::find($notification->dokumen_id);
            if (!$dokumen || $dokumen->current_handler !== $handler) {
                $notification->delete();
                $this->info("Removed notification for dokumen {$notification->dokumen_id} - no longer in {$handler}");
            }
        }
        
        // Get documents that are still in this handler and have overdue deadlines
        $dokumens = Dokumen::where('current_handler', $handler)
            ->whereNotNull($deadlineField)
            ->where($deadlineField, '<', $now) // Only overdue deadlines
            ->get();

        foreach ($dokumens as $dokumen) {
            $deadlineAt = $dokumen->$deadlineField;
            $daysOverdue = $now->diffInDays($deadlineAt);
            
            // Determine status: 
            // warning (kuning) = lewat dari 1 hari (>=1 hari)
            // danger (merah) = lewat lebih dari 1 hari (>1 hari, jadi >=2 hari)
            $status = $daysOverdue > 1 ? 'danger' : 'warning';
            
            // Check if notification exists and needs update
            $notification = DeadlineNotification::where('dokumen_id', $dokumen->id)
                ->where('handler', $handler)
                ->where('deadline_type', $deadlineField)
                ->first();
            
            if ($notification) {
                // Update existing notification if needed
                $shouldNotify = !$notification->last_notified_at || 
                               $notification->last_notified_at->lt($sixHoursAgo);
                
                if ($shouldNotify) {
                    $notification->update([
                        'deadline_at' => $deadlineAt,
                        'status' => $status,
                        'days_overdue' => $daysOverdue,
                        'last_notified_at' => $now,
                        'is_read' => false, // Mark as unread when updated
                    ]);
                    $this->info("Updated notification for dokumen {$dokumen->id} ({$handler})");
                    
                    // Send WhatsApp notification
                    $this->sendWhatsAppNotification($handler, $dokumen, $daysOverdue, $status, $deadlineField);
                }
            } else {
                // Create new notification
                DeadlineNotification::create([
                    'dokumen_id' => $dokumen->id,
                    'handler' => $handler,
                    'deadline_type' => $deadlineField,
                    'deadline_at' => $deadlineAt,
                    'status' => $status,
                    'days_overdue' => $daysOverdue,
                    'last_notified_at' => $now,
                    'is_read' => false,
                ]);
                $this->info("Created notification for dokumen {$dokumen->id} ({$handler})");
                
                // Send WhatsApp notification
                $this->sendWhatsAppNotification($handler, $dokumen, $daysOverdue, $status, $deadlineField);
            }
        }
    }
    
    /**
     * Send WhatsApp notification for deadline
     */
    private function sendWhatsAppNotification($handler, $dokumen, $daysOverdue, $status, $deadlineField = null)
    {
        if (!config('whatsapp.enabled')) {
            return;
        }
        
        try {
            $whatsappService = new WhatsAppNotificationService();
            
            // Format handler name
            $handlerNames = [
                'akutansi' => 'Akuntansi',
                'perpajakan' => 'Perpajakan',
                'ibu_a' => 'Ibu A',
            ];
            $handlerName = $handlerNames[$handler] ?? $handler;
            
            // Get deadline date based on field
            $deadlineDate = null;
            if ($deadlineField === 'deadline_perpajakan_at') {
                $deadlineDate = $dokumen->deadline_perpajakan_at;
            } else {
                $deadlineDate = $dokumen->deadline_at;
            }
            
            // Create message
            $statusText = $status === 'danger' ? '⚠️ MERAH (Lewat >1 hari)' : '⚠️ KUNING (Lewat 1 hari)';
            $message = "🔔 *Notifikasi Deadline Dokumen*\n\n";
            $message .= "Handler: *{$handlerName}*\n";
            $message .= "Nomor Agenda: *{$dokumen->nomor_agenda}*\n";
            $message .= "Nomor SPP: {$dokumen->nomor_spp}\n";
            $message .= "Status: {$statusText}\n";
            $message .= "Terlambat: *{$daysOverdue} hari*\n";
            if ($deadlineDate) {
                $message .= "Deadline: " . $deadlineDate->format('d/m/Y H:i') . "\n";
            }
            $message .= "\nSilakan segera proses dokumen ini untuk menghindari keterlambatan lebih lanjut.";
            
            // Send to all users in this handler
            $results = $whatsappService->sendToHandler($handler, $message);
            
            foreach ($results as $result) {
                if ($result['sent']) {
                    $this->info("WhatsApp sent to {$result['user']} ({$result['phone']})");
                } else {
                    $this->warn("Failed to send WhatsApp to {$result['user']} ({$result['phone']})");
                }
            }
        } catch (\Exception $e) {
            $this->error("WhatsApp notification error: " . $e->getMessage());
        }
    }
}
