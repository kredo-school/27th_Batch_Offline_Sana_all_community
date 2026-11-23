<?php

namespace App\Models;

use App\Support\LocalizedValue;
use Illuminate\Database\Eloquent\Model;

class CommunityMember extends Model
{
    protected $fillable = ['community_id', 'user_id', 'role', 'status', 'joined_at'];

    protected function casts(): array
    {
        return ['joined_at' => 'datetime'];
    }

    public function community()
    {
        return $this->belongsTo(Community::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getRoleLabelAttribute(): string
    {
        return LocalizedValue::role($this->role);
    }

    public function getStatusLabelAttribute(): string
    {
        return LocalizedValue::status($this->status);
}

}