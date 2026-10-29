<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $name
 * @property string|null $username
 * @property string $email
 * @property string $detail
 * @property \Illuminate\Support\Carbon|null $replied_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 */
class Contact extends Model
{
    protected $fillable = ['user_id', 'name', 'username', 'email', 'detail', 'replied_at'];

    protected function casts(): array
    {
        return ['replied_at' => 'datetime'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Resolve the registered user this inquiry belongs to,
     * either by the stored user_id or by matching username.
     */
    public function resolvedUser(): ?User
    {
        if ($this->user) {
            return $this->user;
        }

        if (! $this->username) {
            return null;
        }

        return User::query()->where('username', $this->username)->first();
    }
}
