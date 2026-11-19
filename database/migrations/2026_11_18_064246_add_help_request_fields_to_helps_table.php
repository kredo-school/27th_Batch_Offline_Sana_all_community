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
        // Schema::table('helps', function (Blueprint $table) {
        //     $table->string('requester_type', 20)
        //         ->nullable()
        //         ->after('user_id');

        //     $table->foreignId('category_id')
        //         ->nullable()
        //         ->after('description')
        //         ->constrained('help_categories')
        //         ->nullOnDelete();

        //     $table->dateTime('event_start_at')
        //         ->nullable()
        //         ->after('location');

        //     $table->dateTime('event_end_at')
        //         ->nullable()
        //         ->after('event_start_at');

        //     $table->string('reward_type', 20)
        //         ->nullable()
        //         ->after('salary');

        //     $table->decimal('reward_amount', 10, 2)
        //         ->nullable()
        //         ->after('reward_type');

        //     $table->unsignedInteger('capacity')
        //         ->default(1)
        //         ->after('reward_amount');

        //     $table->text('requirements')
        //         ->nullable()
        //         ->after('capacity');

        //     $table->index([
        //         'status',
        //         'event_start_at',
        //     ]);
        // });

        if (! Schema::hasColumn('helps', 'category_id')) {
            Schema::table('helps', function (Blueprint $table) {
                $table->foreignId('category_id')
                    ->nullable()
                    ->after('description')
                    ->constrained('help_categories')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('helps', function (Blueprint $table) {
        //     $table->dropIndex([
        //         'status',
        //         'event_start_at',
        //     ]);

        //     $table->dropForeign([
        //         'category_id',
        //     ]);

        //     $table->dropColumn([
        //         'requester_type',
        //         'category_id',
        //         'event_start_at',
        //         'event_end_at',
        //         'reward_type',
        //         'reward_amount',
        //         'capacity',
        //         'requirements',
        //     ]);
        // });

        if (Schema::hasColumn('helps', 'category_id')) {
            Schema::table('helps', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            });
        }
    }
};
