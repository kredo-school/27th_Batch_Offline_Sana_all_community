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
        if ($this->supportsNamedForeignKeys()) {
            Schema::table('help_applications', function (Blueprint $table) {
                $table->dropForeign('job_applications_job_id_foreign');
            });
        }

        Schema::table('help_applications', function (Blueprint $table) {
            $table->dropUnique('job_applications_job_id_user_id_unique');
        });

        Schema::table('help_applications', function (Blueprint $table) {
            $table->renameColumn('job_id', 'help_id');
        });

        Schema::table('help_applications', function (Blueprint $table) {
            $table->unique(
                ['help_id', 'user_id'],
                'help_applications_help_id_user_id_unique'
            );
        });

        if ($this->supportsNamedForeignKeys()) {
            Schema::table('help_applications', function (Blueprint $table) {
                $table->foreign(
                    'help_id',
                    'help_applications_help_id_foreign'
                )
                    ->references('id')
                    ->on('helps')
                    ->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($this->supportsNamedForeignKeys()) {
            Schema::table('help_applications', function (Blueprint $table) {
                $table->dropForeign('help_applications_help_id_foreign');
            });
        }

        Schema::table('help_applications', function (Blueprint $table) {
            $table->dropUnique('help_applications_help_id_user_id_unique');
        });

        Schema::table('help_applications', function (Blueprint $table) {
            $table->renameColumn('help_id', 'job_id');
        });

        Schema::table('help_applications', function (Blueprint $table) {
            $table->unique(
                ['job_id', 'user_id'],
                'job_applications_job_id_user_id_unique'
            );
        });

        if ($this->supportsNamedForeignKeys()) {
            Schema::table('help_applications', function (Blueprint $table) {
                $table->foreign(
                    'job_id',
                    'job_applications_job_id_foreign'
                )
                    ->references('id')
                    ->on('helps')
                    ->cascadeOnDelete();
            });
        }
    }

    /**
     * テストで使う SQLite は、名前を指定した外部キーの削除ができない。
     * SQLite では列の名前を変えても外部キーはそのまま残るので、外部キーの削除・追加は飛ばす。
     */
    private function supportsNamedForeignKeys(): bool
    {
        return Schema::getConnection()->getDriverName() !== 'sqlite';
    }
};
