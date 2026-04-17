<?php

namespace App\Events;

use App\Models\Dokumen;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentSentToInbox implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $dokumen;
    public $recipientRole;

    /**
     * Create a new event instance.
     */
    public function __construct(Dokumen $dokumen, $recipientRole)
    {
        $this->dokumen = [
            'id' => $dokumen->id,
            'nomor_agenda' => $dokumen->nomor_agenda,
            'nomor_spp' => $dokumen->nomor_spp,
            'uraian_spp' => $dokumen->uraian_spp,
            'nilai_rupiah' => $dokumen->nilai_rupiah,
            'status' => $dokumen->status,
            'inbox_approval_sent_at' => $dokumen->inbox_approval_sent_at?->format('d/m/Y H:i'),
        ];
        $this->recipientRole = $recipientRole;
    }

    /**
     * Get the channels the event should broadcast on.
     * Using Public Channel for development - no authentication required
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new \Illuminate\Broadcasting\Channel('inbox-updates'), // Public channel
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'document.sent.to.inbox';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'dokumen' => $this->dokumen,
            'recipientRole' => $this->recipientRole,
            'message' => "Dokumen baru menunggu persetujuan di inbox {$this->recipientRole}",
            'timestamp' => now()->toISOString(),
        ];
    }
}





