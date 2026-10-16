<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'description'];

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_categories');
    }
}