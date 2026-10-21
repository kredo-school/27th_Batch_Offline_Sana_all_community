<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserNotification extends Model
{
    protected $table = 'notifications';

    protected $fillable = ['user_id', 'recipient_label', 'sender_name', 'type', 'title', 'message', 'related_type', 'related_id', 'read_at'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getRecipientNameAttribute(): ?string
    {
        return $this->recipient_label ?: $this->user?->name;
    }
}