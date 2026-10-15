<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'owner_id', 'community_id', 'title', 'description', 'language', 'region', 'location',
        'start_at', 'join_till', 'end_at', 'capacity', 'price', 'approval_required', 'visibility', 'status', 'image',
    ];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'join_till' => 'datetime',
            'end_at' => 'datetime',
            'price' => 'decimal:2',
            'approval_required' => 'boolean',
        ];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'event_categories')->using(EventCategory::class);
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

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
    }
}