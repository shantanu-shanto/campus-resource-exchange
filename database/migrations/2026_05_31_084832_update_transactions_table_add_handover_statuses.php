<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1 — temporary column to hold existing values during migration
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('status_temp')->default('pending')->after('status');
        });

        // Step 2 — copy current values into temp column
        DB::table('transactions')->update([
            'status_temp' => DB::raw('status'),
        ]);

        // Step 3 — drop the index first explicitly, then the column
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['status', 'due_date']);
            $table->dropColumn('status');
        });

        // Step 4 — recreate enum with two new transitional statuses
        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'awaiting_handover',
                'active',
                'awaiting_return',
                'completed',
                'late',
                'cancelled',
            ])->default('pending')->after('status_temp');
        });

        // Step 5 — copy values back from temp column
        DB::table('transactions')->update([
            'status' => DB::raw('status_temp'),
        ]);

        // Step 6 — drop temp column and restore the index
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('status_temp');
            $table->index(['status', 'due_date']);
        });
    }

    public function down(): void
    {
        // Roll back awaiting statuses to their nearest safe equivalent
        DB::table('transactions')
            ->where('status', 'awaiting_handover')
            ->update(['status' => 'pending']);

        DB::table('transactions')
            ->where('status', 'awaiting_return')
            ->update(['status' => 'active']);

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('status_temp')->default('pending')->after('status');
        });

        DB::table('transactions')->update([
            'status_temp' => DB::raw('status'),
        ]);

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['status', 'due_date']);
            $table->dropColumn('status');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->enum('status', [
                'pending',
                'active',
                'completed',
                'late',
                'cancelled',
            ])->default('pending')->after('status_temp');
        });

        DB::table('transactions')->update([
            'status' => DB::raw('status_temp'),
        ]);

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('status_temp');
            $table->index(['status', 'due_date']);
        });
    }
};