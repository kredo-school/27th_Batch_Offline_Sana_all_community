<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    protected $table = 'notifications';

    // ヘッダーのコミュニティ通知欄に出す種類（ベルの一般通知からは外す）
    public const COMMUNITY_TYPES = [
        'community_message',
        'community_join_request',
        'community_join_approved',
        'community_join_declined',
        'community_invite',
        'event_cancelled',
    ];

    protected $fillable = ['user_id', 'recipient_label', 'sender_name', 'type', 'title', 'message', 'related_type', 'related_id', 'read_at'];

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
}