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

class DocumentApprovedInbox implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $dokumen;
    public $targetRole;

    /**
     * Create a new event instance.
     */
    public function __construct(Dokumen $dokumen, $targetRole = null)
    {
        // Fallback if targetRole not provided (try to guess or use default - risky if column deleted)
        // But for new calls we should provide it.
        $this->targetRole = $targetRole;

        $this->dokumen = [
            'id' => $dokumen->id,
            'nomor_agenda' => $dokumen->nomor_agenda,
            'nomor_spp' => $dokumen->nomor_spp,
            'status' => $dokumen->status,
            'inbox_approval_for' => $targetRole, // Virtual field for frontend
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('inbox.' . strtolower($this->targetRole)),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'document.approved.inbox';
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
            'message' => "Dokumen disetujui dan masuk ke daftar dokumen",
        ];
    }
}
