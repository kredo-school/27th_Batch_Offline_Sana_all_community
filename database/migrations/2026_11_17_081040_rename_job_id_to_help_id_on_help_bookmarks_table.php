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
        Schema::table('help_bookmarks', function (Blueprint $table) {
            $table->dropForeign('job_bookmarks_job_id_foreign');
        });

        Schema::table('help_bookmarks', function (Blueprint $table) {
            $table->dropUnique('job_bookmarks_job_id_user_id_unique');
        });

        Schema::table('help_bookmarks', function (Blueprint $table) {
            $table->renameColumn('job_id', 'help_id');
        });

        Schema::table('help_bookmarks', function (Blueprint $table) {
            $table->unique(
                ['help_id', 'user_id'],
                'help_bookmarks_help_id_user_id_unique'
            );
        });

        Schema::table('help_bookmarks', function (Blueprint $table) {
            $table->foreign(
                'help_id',
                'help_bookmarks_help_id_foreign'
            )
                ->references('id')
                ->on('helps')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('help_bookmarks', function (Blueprint $table) {
            $table->dropForeign('help_bookmarks_help_id_foreign');
        });

        Schema::table('help_bookmarks', function (Blueprint $table) {
            $table->dropUnique('help_bookmarks_help_id_user_id_unique');
        });

        Schema::table('help_bookmarks', function (Blueprint $table) {
            $table->renameColumn('help_id', 'job_id');
        });

        Schema::table('help_bookmarks', function (Blueprint $table) {
            $table->unique(
                ['job_id', 'user_id'],
                'job_bookmarks_job_id_user_id_unique'
            );
        });

        Schema::table('help_bookmarks', function (Blueprint $table) {
            $table->foreign(
                'job_id',
                'job_bookmarks_job_id_foreign'
            )
                ->references('id')
                ->on('helps')
                ->cascadeOnDelete();
        });
    }
};
