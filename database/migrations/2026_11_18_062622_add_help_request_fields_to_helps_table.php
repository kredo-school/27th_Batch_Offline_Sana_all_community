<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('helps', function (Blueprint $table) {
            $table->string('requester_type', 20)
                ->nullable();

            $table->string('category', 50)
                ->nullable();

            $table->dateTime('event_start_at')
                ->nullable();

            $table->dateTime('event_end_at')
                ->nullable();

            $table->string('reward_type', 20)
                ->nullable();

            $table->decimal('reward_amount', 10, 2)
                ->nullable();

            $table->unsignedInteger('capacity')
                ->default(1);

            $table->text('requirements')
                ->nullable();

            $table->index([
                'status',
                'event_start_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('helps', function (Blueprint $table) {
            $table->dropIndex([
                'status',
                'event_start_at',
            ]);

            $table->dropColumn([
                'requester_type',
                'category',
                'event_start_at',
                'event_end_at',
                'reward_type',
                'reward_amount',
                'capacity',
                'requirements',
            ]);
        });
    }
};
