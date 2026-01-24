<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppNotificationLog extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_notification_logs';

    protected $fillable = [
        'dokumen_id',
        'role_code',
        'user_id',
        'phone_number',
        'message_type',
        'message',
        'status',
        'response',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    /**
     * Get the document associated with this notification.
     */
    public function dokumen(): BelongsTo
    {
        return $this->belongsTo(Dokumen::class, 'dokumen_id');
    }

    /**
     * Get the user who received this notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Check if notification was sent within cooldown period
     */
    public static function wasRecentlySent(int $dokumenId, string $roleCode, string $messageType, int $cooldownHours = 24): bool
    {
        $cutoff = now()->subHours($cooldownHours);

        return self::where('dokumen_id', $dokumenId)
            ->where('role_code', $roleCode)
            ->where('message_type', $messageType)
            ->where('status', 'success')
            ->where('sent_at', '>=', $cutoff)
            ->exists();
    }

    /**
     * Mark notification as successful
     */
    public function markAsSuccess(string $response = null): void
    {
        $this->update([
            'status' => 'success',
            'response' => $response,
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark notification as failed
     */
    public function markAsFailed(string $response = null): void
    {
        $this->update([
            'status' => 'failed',
            'response' => $response,
            'sent_at' => now(),
        ]);
    }
}
