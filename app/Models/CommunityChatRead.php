<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// ユーザーがコミュニティチャットをどこまで読んだか（未読数の計算に使う）
class CommunityChatRead extends Model
{
    protected $fillable = ['community_id', 'user_id', 'last_read_message_id'];
}
