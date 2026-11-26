<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $fillable = [
        'translatable_type', 'translatable_id', 'field', 'source_language', 'source_hash', 'target_language', 'translated_text',
    ];

    public function translatable()
    {
        return $this->morphTo();
    }
}