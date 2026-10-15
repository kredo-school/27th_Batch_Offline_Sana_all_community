<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bookmark extends Model
{
    protected $fillable = ['user_id', 'event_id'];

    public const UPDATED_AT = null;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}