<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HelpApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'help_id',
        'user_id',
        'cover_letter',
        'status',
        'applied_at',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
    ];

    /**
     * 応募対象の求人
     */
    public function help(): BelongsTo
    {
        return $this->belongsTo(Help::class, 'help_id');
    }

    /**
     * 応募したユーザー
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
