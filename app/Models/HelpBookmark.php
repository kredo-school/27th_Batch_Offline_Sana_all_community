<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HelpBookmark extends Model
{
    use HasFactory;

    protected $fillable = [
        'help_id',
        'user_id',
    ];

    public function help(): BelongsTo
    {
        return $this->belongsTo(Help::class, 'help_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
