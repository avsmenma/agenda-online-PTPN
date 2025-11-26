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

    /**
     * Create a new event instance.
     */
    public function __construct(Dokumen $dokumen, $reason)
    {
        $this->dokumen = [
            'id' => $dokumen->id,
            'nomor_agenda' => $dokumen->nomor_agenda,
            'nomor_spp' => $dokumen->nomor_spp,
            'status' => $dokumen->status,
            'inbox_approval_for' => $dokumen->inbox_approval_for,
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
        return [
            new PrivateChannel('inbox.' . strtolower($this->dokumen['inbox_approval_for'])),
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
