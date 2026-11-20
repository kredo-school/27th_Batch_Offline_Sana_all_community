<?php

namespace App\Models;

use App\Support\LocalizedValue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventReview extends Model
{
    use SoftDeletes;

    protected $fillable = ['event_id', 'user_id', 'rating', 'comment'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getRatingLabelAttribute(): string
    {
        return LocalizedValue::reviewRating((int) $this->rating);
    }
}