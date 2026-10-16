<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users');
            $table->foreignId('community_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('language', 5)->default('en');
            $table->string('region', 100)->nullable();
            $table->string('location')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('join_till');
            $table->dateTime('end_at');
            $table->unsignedInteger('capacity')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('approval_required')->default(false);
            $table->string('visibility', 20)->default('public');
            $table->string('status', 20)->default('published');
            $table->string('image')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};