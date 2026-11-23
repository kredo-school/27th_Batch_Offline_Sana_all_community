<?php

namespace App\Models;

use App\Support\Search;
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
        'application_deadline' => 'date',
    ];

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
        return $this->hasMany(HelpBookmark::class);
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(HelpCategory::class, 'category_id');
    }
}
