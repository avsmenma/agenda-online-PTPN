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

class DocumentRejectedInbox implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $dokumen;
    public $reason;
    public $targetRole;

    /**
     * Create a new event instance.
     */
    public function __construct(Dokumen $dokumen, $reason, $targetRole = null)
    {
        $this->targetRole = $targetRole;
        $this->dokumen = [
            'id' => $dokumen->id,
            'nomor_agenda' => $dokumen->nomor_agenda,
            'nomor_spp' => $dokumen->nomor_spp,
            'status' => $dokumen->status,
            'inbox_approval_for' => $targetRole, // Virtual field
        ];
        $this->reason = $reason;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // If targetRole is null, we might have a problem broadcasting to the right channel
        // But rejecting usually goes back to sender or notifies the rejector?
        // Wait, "Inbox" channel usually means the INBOX of the person causing the event?
        // Or the INBOX of the person receiving the event?
        // "new PrivateChannel('inbox.' . strtolower($this->dokumen['inbox_approval_for']))"
        // This suggests it notifies the ROLE that WAS approving it (e.g. to remove from their list?)
        // OR checks if it was for them.

        // Let's assume we broadcast to the role associated with the inbox.
        $channel = 'inbox.' . ($this->targetRole ? strtolower($this->targetRole) : 'unknown');
        return [
            new PrivateChannel($channel),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'document.rejected.inbox';
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
            'reason' => $this->reason,
            'message' => "Dokumen ditolak dan dikembalikan ke pengirim",
        ];
    }
}



