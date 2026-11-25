<?php

namespace App\Models;

use App\Support\LocalizedValue;
use App\Support\Search;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'owner_id',
        'community_id',
        'title',
        'description',
        'language',
        'region',
        'location',
        'start_at',
        'join_till',
        'end_at',
        'capacity',
        'price',
        'fee_note',
        'approval_required',
        'visibility',
        'status',
        'image',
        'image_crop',
        'owner_id',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'join_till' => 'datetime',
            'end_at' => 'datetime',
            'price' => 'decimal:2',
            'approval_required' => 'boolean',
            'image_crop' => 'array',
        ];
    }
        
    public function imageStyle(): string
    {
        $crop = $this->image_crop;

        if (empty($crop)) {
            return 'object-fit: cover;';
        }

        $zoom = (float) ($crop['zoom'] ?? 1);
        $x = (float) ($crop['x'] ?? 50);
        $y = (float) ($crop['y'] ?? 50);

        return "object-fit: contain; object-position: {$x}% {$y}%; "
            . "transform: scale({$zoom}); transform-origin: {$x}% {$y}%;";
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    /**
     * 検索（サイト共通のルール App\Support\Search）: タイトル・説明・場所・主催者・カテゴリー（どの言語の名前でも）
     */
    public function scopeSearch($query, ?string $keyword)
    {
        return Search::apply($query, $keyword, function ($query, string $like, string $word) {
            $categoryIds = Search::categoryIds($word);
            $query->where($this->qualifyColumn('title'), 'like', $like)
                ->orWhere($this->qualifyColumn('description'), 'like', $like)
                ->orWhere($this->qualifyColumn('location'), 'like', $like)
                ->orWhereHas('owner', fn ($owner) => $owner->where('name', 'like', $like)->orWhere('username', 'like', $like))
                ->when($categoryIds !== [], fn ($query) => $query->orWhereHas('categories', fn ($category) => $category->whereIn('categories.id', $categoryIds)));
        });
    }

    /**
     * 検索の候補や全体検索に出してよいイベント: 公開中で公開設定のもの（自分のイベントは下書きや非公開も出す）
     */
    public function scopeListedFor($query, ?User $user)
    {
        return $query->visibleTo($user)->where(function ($query) use ($user) {
            $query->where(fn ($query) => $query->where($this->qualifyColumn('status'), 'published')->where($this->qualifyColumn('visibility'), 'public'))
                ->when($user, fn ($query) => $query->orWhere($this->qualifyColumn('owner_id'), $user->id));
        });
    }

    // コミュニティ外のイベントは誰にでも見せる。コミュニティのイベントは、そのコミュニティが見える人にだけ見せる
    // （招待のみのコミュニティのイベントを、関係ない人の一覧や詳細ページに出さない）
    public function scopeVisibleTo($query, ?User $user)
    {
        return $query->where(function ($query) use ($user) {
            $query->whereNull($this->qualifyColumn('community_id'))
                ->orWhereHas('community', fn ($query) => $query->visibleTo($user));
        });
    }

    public function isVisibleTo(?User $user): bool
    {
        return ! $this->community_id || (bool) $this->community?->isVisibleTo($user);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'event_categories')->using(EventCategory::class);
    }

    public function getCategoryNamesAttribute(): string
    {
        return $this->categories->pluck('localized_name')->implode(', ');
    }

    public function participants()
    {
        return $this->hasMany(EventParticipant::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'event_participants')
            ->withPivot(['status', 'requested_at', 'responded_at'])
            ->withTimestamps();
    }

    public function reviews()
    {
        return $this->hasMany(EventReview::class);
    }

    public function userReviews()
    {
        return $this->hasMany(UserReview::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function bookmarkedBy()
    {
        return $this->belongsToMany(User::class, 'bookmarks', 'event_id', 'user_id')->withPivot('created_at');
    }

    public function bookmarkedUsers()
    {
        return $this->belongsToMany(User::class, 'bookmarks', 'event_id', 'user_id')->withPivot('created_at');
    }

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    public function getStatusLabelAttribute(): string
    {
        return LocalizedValue::status($this->status);
    }

    public function getVisibilityLabelAttribute(): string
    {
        return LocalizedValue::visibility($this->visibility);
    }

    public function getLanguageLabelAttribute(): string
    {
        return LocalizedValue::language($this->language);
    }

    public function getApprovalLabelAttribute(): string
    {
        return LocalizedValue::approval($this->approval_required);
    }

    /////////////////    Event Page - Change badge color of join_till. /////////////
    public function getJoinTillBadgeAttribute(): array
    {
        if (!$this->join_till) {
            return [
                'label' => '-',
                'class' => 'status-closed',
            ];
        }

        $today = Carbon::today(config('app.timezone'));
        $joinTill = Carbon::parse($this->join_till)->startOfDay();

        // regester closed
        if ($joinTill->lt($today)) {
            return [
                'label' => __('Registration closed'),
                'class' => 'status-closed',
            ];
        }

        $daysLeft = $today->diffInDays($joinTill);

        // Today
        if ($daysLeft <= 0) {
            return [
                'label' => __('Today'),
                'class' => 'status-today',
            ];
        }

        // 1~3 days left
        if ($daysLeft <= 3) {
            return [
                'label' => __(':count days left', ['count' => $daysLeft]),
                'class' => 'status-urgent',
            ];
        }

        // 4~7 days left
        if ($daysLeft <= 7) {
            return [
                'label' => __(':count days left', ['count' => $daysLeft]),
                'class' => 'status-info',
            ];
        }

        // after 8 days left
        $localizedDate = $joinTill->copy()
            ->locale(app()->getLocale())
            ->isoFormat('LL') ?: $joinTill->format('F j, Y');

        return [
            'label' => $localizedDate,
            'class' => 'status-info',
        ];
    }
}
