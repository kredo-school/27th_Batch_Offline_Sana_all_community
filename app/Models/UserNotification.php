<?php

namespace App\Models;

use App\Support\LocalizedValue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    protected $table = 'notifications';

    // ヘッダーのコミュニティ通知欄に出す種類（ベルの一般通知からは外す）
    public const COMMUNITY_TYPES = [
        'community_join_request',
        'community_join_approved',
        'community_join_declined',
        'community_invite',
        'community_moderator_added',
        'community_ownership_transferred',
        'event_cancelled',
    ];

    protected $fillable = ['user_id', 'recipient_label', 'sender_name', 'type', 'title', 'message', 'related_type', 'related_id', 'read_at'];

    public function scopeCommunityFilter(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->where('type', 'like', '%community%')
                ->orWhereIn('type', self::COMMUNITY_TYPES);
        })->where('type', '!=', 'community_message');
    }

    public function scopeNotCommunityFilter(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->where('type', 'not like', '%community%')
                ->whereNotIn('type', self::COMMUNITY_TYPES);
        });
    }

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function related()
    {
        return $this->morphTo();
    }

    public function getRecipientNameAttribute(): ?string
    {
        return $this->recipient_label ?: $this->user?->name;
    }

    public function getTypeLabelAttribute(): string
    {
        return LocalizedValue::notificationType($this->type);
    }
}