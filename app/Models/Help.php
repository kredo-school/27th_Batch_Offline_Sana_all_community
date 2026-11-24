<?php

namespace App\Models;

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
        'application_deadline' => 'date',
        'image_crop' => 'array',
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
