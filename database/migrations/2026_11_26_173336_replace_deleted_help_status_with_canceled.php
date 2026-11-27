<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('helps')
            ->where('status', 'deleted')
            ->update([
                'status' => 'canceled',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('helps')
            ->where('status', 'canceled')
            ->update([
                'status' => 'deleted',
            ]);
    }
};
