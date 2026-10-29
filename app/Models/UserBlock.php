<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserBlock extends Model
{
    protected $fillable = ['user_id', 'blocked_by', 'reason', 'blocked_at', 'expires_at'];

    protected function casts(): array
    {
        return ['blocked_at' => 'datetime', 'expires_at' => 'datetime'];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNotNull('blocked_at')
            ->where(function (Builder $query): void {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public static function blocksEitherWay(int $firstUserId, int $secondUserId): bool
    {
        return static::query()->active()
            ->where(function (Builder $query) use ($firstUserId, $secondUserId): void {
                $query->where(function (Builder $query) use ($firstUserId, $secondUserId): void {
                    $query->where('user_id', $firstUserId)
                        ->where('blocked_by', $secondUserId);
                })->orWhere(function (Builder $query) use ($firstUserId, $secondUserId): void {
                    $query->where('user_id', $secondUserId)
                        ->where('blocked_by', $firstUserId);
                });
            })
            ->exists();
    }

    public static function block(int $userId, int $blockedBy): self
    {
        return static::query()->updateOrCreate(
            ['user_id' => $userId, 'blocked_by' => $blockedBy],
            ['blocked_at' => now(), 'expires_at' => null]
        );
    }

    public static function unblock(int $userId, int $blockedBy): int
    {
        return static::query()
            ->where('user_id', $userId)
            ->where('blocked_by', $blockedBy)
            ->delete();
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