<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Profile;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'status',
        'preferred_language',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function blocks()
    {
        return $this->hasMany(UserBlock::class);
    }

    public function blocksMade()
    {
        return $this->hasMany(UserBlock::class, 'blocked_by');
    }

    public function communitiesOwned()
    {
        return $this->hasMany(Community::class, 'owner_id');
    }

    public function communityMemberships()
    {
        return $this->hasMany(CommunityMember::class);
    }

    public function communities()
    {
        return $this->belongsToMany(Community::class, 'community_members')
            ->withPivot(['role', 'status', 'joined_at'])
            ->withTimestamps();
    }

    public function eventsOwned()
    {
        return $this->hasMany(Event::class, 'owner_id');
    }

    public function eventParticipations()
    {
        return $this->hasMany(EventParticipant::class);
    }

    public function attendingEvents()
    {
        return $this->belongsToMany(Event::class, 'event_participants')
            ->withPivot(['status', 'requested_at', 'responded_at'])
            ->withTimestamps();
    }

    public function eventReviews()
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

    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'following_id', 'follower_id');
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'follows', 'follower_id', 'following_id');
    }

    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function bookmarkedEvents()
    {
        return $this->belongsToMany(Event::class, 'bookmarks', 'user_id', 'event_id')->withPivot('created_at');
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class);
    }
}
