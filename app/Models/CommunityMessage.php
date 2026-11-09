<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// コミュニティ内チャットの1件のメッセージ
class CommunityMessage extends Model
{
    use SoftDeletes;

    protected $fillable = ['community_id', 'user_id', 'message'];

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // チャットに表示する送信者の名前
    public function senderName(): string
    {
        return $this->user?->username ?: $this->user?->name ?: 'Unknown user';
    }
}
