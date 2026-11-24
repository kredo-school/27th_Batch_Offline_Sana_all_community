<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('helps', function (Blueprint $table) {
            // カバー画像の表示位置とズーム（例：{"zoom":1.3,"x":40,"y":60}）
            $table->json('image_crop')->nullable()->after('image_path');
        });
    }

    public function down(): void
    {
        Schema::table('helps', function (Blueprint $table) {
            $table->dropColumn('image_crop');
        });
    }
};
