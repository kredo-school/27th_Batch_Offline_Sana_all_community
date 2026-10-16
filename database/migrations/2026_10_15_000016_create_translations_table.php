<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->string('translatable_type', 50);
            $table->unsignedBigInteger('translatable_id');
            $table->string('field', 50);
            $table->string('source_language', 5);
            $table->string('target_language', 5);
            $table->text('translated_text');
            $table->timestamps();
            $table->unique(['translatable_type', 'translatable_id', 'field', 'target_language'], 'translations_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};