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

class DocumentSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $document;
    public $sentBy;
    public $sentTo;
    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct(Dokumen $document, string $sentBy, string $sentTo)
    {
        $this->document = [
            'id' => $document->id,
            'nomor_agenda' => $document->nomor_agenda,
            'nomor_spp' => $document->nomor_spp,
            'uraian_spp' => $document->uraian_spp,
            'nilai_rupiah' => $document->nilai_rupiah,
            'status' => $document->status,
            'tanggal_masuk' => $document->tanggal_masuk?->format('d/m/Y H:i'),
            'sent_to_team_verifikasi_at' => $document->sent_to_team_verifikasi_at?->format('d/m/Y H:i'),
        ];
        $this->sentBy = $sentBy;
        $this->sentTo = $sentTo;
        $this->message = "Dokumen baru diterima dari {$sentBy}";
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('documents.' . $this->sentTo), // Public channel for testing
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'document.sent';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'document' => $this->document,
            'sentBy' => $this->sentBy,
            'sentTo' => $this->sentTo,
            'message' => $this->message,
        ];
    }
}



