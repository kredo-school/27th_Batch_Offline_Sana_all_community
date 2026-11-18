<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

// コミュニティ内チャットの1件のメッセージ
class CommunityMessage extends Model
{
    use SoftDeletes;

    protected $fillable = ['community_id', 'user_id', 'message', 'image'];

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 送った画像の URL（画像が無ければ null）
    public function imageUrl(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    // 一覧や通知で使う短い本文（画像だけのメッセージは「Photo」）
    public function previewText(): string
    {
        if ($this->message !== null && trim($this->message) !== '') {
            return $this->message;
        }

        return $this->image ? '📷 Photo' : '';
    }

    // チャットに表示する送信者の名前
    public function senderName(): string
    {
        return $this->user?->username ?: $this->user?->name ?: 'Unknown user';
    }
}
