<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Community extends Model
{
    use SoftDeletes;

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

    public function translations()
    {
        return $this->morphMany(Translation::class, 'translatable');
    }
}