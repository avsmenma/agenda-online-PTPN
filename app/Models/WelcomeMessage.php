<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property string $module
 * @property string $message
 * @property string $type
 * @property bool $is_active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @method static Builder|WelcomeMessage active()
 * @method static Builder|WelcomeMessage byModule(string $module)
 * @method static Builder|WelcomeMessage byType(string $type)
 */
final class WelcomeMessage extends Model
{
    use HasFactory;

    public const TYPE_GENERAL = 'general';
    public const TYPE_PERSONAL = 'personal';
    public const TYPE_TEAM = 'team';

    protected $fillable = [
        'module',
        'message',
        'type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeByModule(Builder $query, string $module): Builder
    {
        return $query->where('module', $module);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }
}




