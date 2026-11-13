<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Community extends Model
{
    use SoftDeletes;

    // 作成フォームと一覧の絞り込みで使うカテゴリー
    public const CATEGORIES = ['Creative', 'Technology', 'Outdoors', 'Learning', 'Wellness', 'Other'];

    protected $fillable = [
        'owner_id', 'name', 'description', 'category', 'location', 'guidelines',
        'image', 'icon', 'visibility', 'language',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function memberships()
    {
        return $this->hasMany(CommunityMember::class);
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'community_members')
            ->withPivot(['role', 'status', 'joined_at'])
            ->withTimestamps();
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function messages()
    {
        return $this->hasMany(CommunityMessage::class);
    }

    public function chatReads()
    {
        return $this->hasMany(CommunityChatRead::class);
    }

    // まだ読んでいない他の人のメッセージの数
    public function unreadMessagesCountFor(User $user): int
    {
        return $this->unreadMessages($user)->count();
    }

    // 未読メッセージを送った人の名前（最近送った人から順に）
    public function unreadSenderNamesFor(User $user): array
    {
        return $this->unreadMessages($user)
            ->with('user:id,name,username')
            ->select('user_id')
            ->selectRaw('MAX(id) as latest_id')
            ->groupBy('user_id')
            ->orderByDesc('latest_id')
            ->get()
            ->map(fn (CommunityMessage $message) => $message->senderName())
            ->all();
    }

    private function unreadMessages(User $user)
    {
        $lastReadId = $this->chatReads()->where('user_id', $user->id)->value('last_read_message_id') ?? 0;

        return $this->messages()
            ->where('id', '>', $lastReadId)
            ->where('user_id', '!=', $user->id);
    }

    // 招待のみ（private）のコミュニティは、オーナー・メンバー・招待中の人にしか見せない
    public function scopeVisibleTo($query, ?User $user)
    {
        return $query->where(function ($query) use ($user) {
            $query->where('visibility', '!=', 'private');
            if ($user) {
                $query->orWhere('owner_id', $user->id)
                    ->orWhereHas('memberships', fn ($query) => $query
                        ->where('user_id', $user->id)
                        ->whereIn('status', ['approved', 'invited']));
            }
        });
    }

    public function isVisibleTo(?User $user): bool
    {
        if ($this->visibility !== 'private') {
            return true;
        }
        if (! $user) {
            return false;
        }

        return $this->owner_id === $user->id
            || $this->memberships()->where('user_id', $user->id)->whereIn('status', ['approved', 'invited'])->exists();
    }

    // オーナーか承認済みメンバーならチャットに参加できる
    public function hasMember(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->owner_id === $user->id
            || $this->memberships()->where('user_id', $user->id)->where('status', 'approved')->exists();
    }

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
    }
}