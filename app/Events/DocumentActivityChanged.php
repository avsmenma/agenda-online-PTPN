<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentActivityChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $dokumenId;
    public $userId;
    public $userName;
    public $userRole;
    public $activityType; // 'viewing', 'editing', 'left'
    public $timestamp;

    /**
     * Create a new event instance.
     */
    public function __construct(
        int $dokumenId,
        int $userId,
        string $userName,
        ?string $userRole,
        string $activityType,
        string $timestamp
    ) {
        $this->dokumenId = $dokumenId;
        $this->userId = $userId;
        $this->userName = $userName;
        $this->userRole = $userRole;
        $this->activityType = $activityType;
        $this->timestamp = $timestamp;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel("document.{$this->dokumenId}"),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'document.activity.changed';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'dokumen_id' => $this->dokumenId,
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'user_role' => $this->userRole,
            'activity_type' => $this->activityType,
            'timestamp' => $this->timestamp,
        ];
    }
}

