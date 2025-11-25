<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Dokumen;

class DocumentReturned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $dokumen;
    public $alasan_pengembalian;
    public $returned_at;
    public $returned_by;

    /**
     * Create a new event instance.
     */
    public function __construct(Dokumen $dokumen, string $alasan_pengembalian, string $returned_by = 'ibuB')
    {
        $this->dokumen = $dokumen;
        $this->alasan_pengembalian = $alasan_pengembalian;
        $this->returned_at = now();
        $this->returned_by = $returned_by;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('documents.ibuA'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'document.returned';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'document_id' => $this->dokumen->id,
            'nomor_agenda' => $this->dokumen->nomor_agenda,
            'nomor_spp' => $this->dokumen->nomor_spp,
            'uraian_spp' => $this->dokumen->uraian_spp,
            'alasan_pengembalian' => $this->alasan_pengembalian,
            'returned_at' => $this->returned_at->format('Y-m-d H:i:s'),
            'returned_by' => $this->returned_by,
            'created_by' => $this->dokumen->created_by,
        ];
    }
}