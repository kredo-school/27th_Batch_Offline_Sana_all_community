<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class EventCategory extends Pivot
{
    protected $table = 'event_categories';

    public $incrementing = false;

    public $timestamps = false;

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}