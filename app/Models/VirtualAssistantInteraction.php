<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VirtualAssistantInteraction extends Model
{
    public const STATUS_ANSWERED = 'answered';
    public const STATUS_AMBIGUOUS = 'ambiguous';
    public const STATUS_UNSUPPORTED = 'unsupported_intent';
    public const STATUS_NO_DATA = 'no_data';
    public const STATUS_ERROR = 'error';
    public const STATUS_LOW_CONFIDENCE = 'low_confidence';
    public const STATUS_NEEDS_REVIEW = 'needs_review';

    protected $fillable = [
        'user_id',
        'user_role',
        'source_context',
        'question',
        'normalized_question',
        'intent',
        'confidence',
        'detected_params',
        'internal_service',
        'result_count',
        'result_total',
        'answer',
        'result_status',
        'failure_category',
        'failure_reason',
        'ai_provider',
        'ai_model',
        'ai_called',
        'ai_skipped_reason',
        'latency_ms',
        'feedback',
        'feedback_reason',
        'feedback_at',
        'fixed_at',
        'fixed_by',
        'fix_note',
    ];

    protected $casts = [
        'detected_params' => 'array',
        'ai_called' => 'boolean',
        'feedback_at' => 'datetime',
        'fixed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fixedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'fixed_by');
    }
}
