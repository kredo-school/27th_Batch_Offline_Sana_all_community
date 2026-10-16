<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBlock extends Model
{
    protected $fillable = ['user_id', 'blocked_by', 'reason', 'blocked_at', 'expires_at'];

    protected function casts(): array
    {
        return ['blocked_at' => 'datetime', 'expires_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function blocker()
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }
}