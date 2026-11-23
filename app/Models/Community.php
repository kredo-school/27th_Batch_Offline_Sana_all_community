<?php

namespace App\Models;

use App\Support\LocalizedValue;
use App\Support\Search;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Community extends Model
{
    use SoftDeletes;

    // 作成フォームと一覧の絞り込みで使うカテゴリー
    public const CATEGORIES = ['Creative', 'Technology', 'Outdoors', 'Learning', 'Wellness', 'Other'];

    private const LOCALIZED_CATEGORIES = [
        'ja' => ['Creative' => 'クリエイティブ', 'Technology' => 'テクノロジー', 'Outdoors' => 'アウトドア', 'Learning' => '学習', 'Wellness' => 'ウェルネス', 'Other' => 'その他'],
        'es' => ['Creative' => 'Creatividad', 'Technology' => 'Tecnología', 'Outdoors' => 'Aire libre', 'Learning' => 'Aprendizaje', 'Wellness' => 'Bienestar', 'Other' => 'Otros'],
        'pt' => ['Creative' => 'Criatividade', 'Technology' => 'Tecnologia', 'Outdoors' => 'Ar livre', 'Learning' => 'Aprendizagem', 'Wellness' => 'Bem-estar', 'Other' => 'Outro'],
        'ru' => ['Creative' => 'Творчество', 'Technology' => 'Технологии', 'Outdoors' => 'Активный отдых', 'Learning' => 'Обучение', 'Wellness' => 'Здоровье', 'Other' => 'Другое'],
        'ar' => ['Creative' => 'الإبداع', 'Technology' => 'التقنية', 'Outdoors' => 'الأنشطة الخارجية', 'Learning' => 'التعلم', 'Wellness' => 'العافية', 'Other' => 'أخرى'],
        'ko' => ['Creative' => '창작', 'Technology' => '기술', 'Outdoors' => '야외 활동', 'Learning' => '학습', 'Wellness' => '웰니스', 'Other' => '기타'],
        'zh' => ['Creative' => '创意', 'Technology' => '科技', 'Outdoors' => '户外', 'Learning' => '学习', 'Wellness' => '健康', 'Other' => '其他'],
        'vi' => ['Creative' => 'Sáng tạo', 'Technology' => 'Công nghệ', 'Outdoors' => 'Ngoài trời', 'Learning' => 'Học tập', 'Wellness' => 'Sức khỏe', 'Other' => 'Khác'],
        'th' => ['Creative' => 'สร้างสรรค์', 'Technology' => 'เทคโนโลยี', 'Outdoors' => 'กิจกรรมกลางแจ้ง', 'Learning' => 'การเรียนรู้', 'Wellness' => 'สุขภาพ', 'Other' => 'อื่นๆ'],
    ];

    protected $fillable = [
        'owner_id', 'name', 'description', 'category', 'location', 'guidelines',
        'image', 'icon', 'visibility', 'language',
    ];

    public static function getLocalizedCategoryName(?string $category): string
    {
        if ($category === null || $category === '') {
            return '';
        }

        return self::LOCALIZED_CATEGORIES[app()->getLocale()][$category]
            ?? __($category);
    }

    /**
     * 検索（サイト共通のルール App\Support\Search）: 名前・説明・場所・カテゴリー・主催者（オーナー）
     * カテゴリーは英語で保存しているので、どの言語の表示名（スポーツ など）で探しても見つかる
     */
    public function scopeSearch($query, ?string $keyword)
    {
        return Search::apply($query, $keyword, function ($query, string $like, string $word) {
            $categories = Search::communityCategories($word);
            // 主催者はイベントと同じく、名前かユーザー名（@ を付けてもよい）で探せる
            $username = '%'.ltrim($word, '@').'%';
            $query->where($this->qualifyColumn('name'), 'like', $like)
                ->orWhere($this->qualifyColumn('description'), 'like', $like)
                ->orWhere($this->qualifyColumn('location'), 'like', $like)
                ->orWhere($this->qualifyColumn('category'), 'like', $like)
                ->when($categories !== [], fn ($query) => $query->orWhereIn($this->qualifyColumn('category'), $categories))
                ->orWhereHas('owner', fn ($owner) => $owner->where('name', 'like', $like)->orWhere('username', 'like', $username));
        });
    }

    /**
     * プログラムに書いてある、各言語でのカテゴリーの表示名（Creative → クリエイティブ など）
     */
    public static function localizedCategoryNames(string $category): array
    {
        return array_values(array_filter(array_map(
            fn (array $names) => $names[$category] ?? null,
            self::LOCALIZED_CATEGORIES,
        )));
    }

    public function getLocalizedCategoryAttribute(): string
    {
        return self::getLocalizedCategoryName($this->category);
    }

    public function getVisibilityLabelAttribute(): string
    {
        return LocalizedValue::visibility($this->visibility);
    }

    public function getLanguageLabelAttribute(): string
    {
        return LocalizedValue::language($this->language);
    }

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

    // 副管理者（モデレーター）: オーナーが承認済みメンバーの中から選ぶ
    public function isModerator(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->memberships()
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('role', 'moderator')
            ->exists();
    }

    // オーナーか副管理者なら、参加申請の承認とチャットの削除ができる
    public function canManage(?User $user): bool
    {
        return $user !== null && ($this->owner_id === $user->id || $this->isModerator($user));
    }

    // 参加申請の通知を受け取る人（オーナーと副管理者）
    public function managerIds()
    {
        return $this->memberships()
            ->where('status', 'approved')
            ->where('role', 'moderator')
            ->pluck('user_id')
            ->push($this->owner_id)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
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