<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('avatar_original')->nullable();
            $table->string('cover_image_original')->nullable();
            $table->json('avatar_crop')->nullable();
            $table->json('cover_image_crop')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['avatar_original', 'cover_image_original', 'avatar_crop', 'cover_image_crop']);
        });
    }
};
