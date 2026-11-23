<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// キュー（後でまとめて処理する仕事）の置き場
// 元の jobs テーブルは求人機能のテーブルに作り替えられたので、キューはこのテーブルを使う（config/queue.php）
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_jobs');
    }
};
