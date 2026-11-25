<?php

namespace App\Models;

use App\Support\Search;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Help extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'requester_type',
        'category_id',
        'title',
        'company_name',
        'description',
        'image_path',
        'image_crop',
        'location',
        'event_start_at',
        'event_end_at',
        'reward_type',
        'reward_amount',
        'capacity',
        'requirements',
        'application_deadline',
        'status',
        'published_at',
    ];

    protected $casts = [
        'event_start_at' => 'datetime',
        'event_end_at' => 'datetime',
        'image_crop' => 'array',
        'application_deadline' => 'datetime',
    ];

        /**
     * カバー画像の位置とズームを、img タグの style 用の文字列にする
     * 位置を保存していない画像は、今までどおり枠いっぱいに表示
     */
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

    /**
     * 検索（サイト共通のルール App\Support\Search）: タイトル・会社名・説明・場所・カテゴリー名
     */
    public function scopeSearch($query, ?string $keyword)
    {
        return Search::apply($query, $keyword, function ($query, string $like) {
            $query->where($this->qualifyColumn('title'), 'like', $like)
                ->orWhere($this->qualifyColumn('company_name'), 'like', $like)
                ->orWhere($this->qualifyColumn('description'), 'like', $like)
                ->orWhere($this->qualifyColumn('location'), 'like', $like)
                ->orWhereHas('category', fn ($category) => $category->where('name', 'like', $like));
        });
    }

    /**
     * 求人を作成したユーザー
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * この求人への応募
     */
    public function applications(): HasMany
    {
        return $this->hasMany(HelpApplication::class, 'help_id');
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(HelpBookmark::class, 'help_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(HelpCategory::class, 'category_id', 'id');
    }

    public function getApplicationDeadlineBadgeAttribute(): array
    {
        if (!$this->application_deadline) {
            return [
                'label' => '-',
                'class' => 'status-closed',
            ];
        }

        $today = Carbon::today(config('app.timezone'));

        $applicationDeadline = Carbon::parse(
            $this->application_deadline
        )->startOfDay();

        // 申込期限終了
        if ($applicationDeadline->lt($today)) {
            return [
                'label' => __('Application closed'),
                'class' => 'status-closed',
            ];
        }

        $daysLeft = $today->diffInDays($applicationDeadline);

        // 今日まで
        if ($daysLeft <= 0) {
            return [
                'label' => __('Today'),
                'class' => 'status-today',
            ];
        }

        // 残り1〜3日
        if ($daysLeft <= 3) {
            return [
                'label' => __(':count days left', ['count' => $daysLeft]),
                'class' => 'status-urgent',
            ];
        }

        // 残り4〜7日
        if ($daysLeft <= 7) {
            return [
                'label' => __(':count days left', ['count' => $daysLeft]),
                'class' => 'status-info',
            ];
        }

        // 8日以上先
        return [
            'label' => $applicationDeadline
                ->locale(app()->getLocale())
                ->isoFormat('ll'),
            'class' => 'status-info',
        ];
    }
}
