<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'avatar', 'bio', 'region', 'country', 'gender', 'age', 'birthday', 'occupation',
    ];

    protected function casts(): array
    {
        return ['birthday' => 'date'];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}