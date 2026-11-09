<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ユーザーごとに、コミュニティチャットをどのメッセージまで読んだかを持つ
        Schema::create('community_chat_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('last_read_message_id')->default(0);
            $table->timestamps();
            $table->unique(['community_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_chat_reads');
    }
};
