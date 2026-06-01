<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('handover_verifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('transaction_id')
                  ->constrained('transactions')
                  ->cascadeOnDelete();

            // pickup = item going out to borrower
            // return = item coming back to owner
            $table->enum('type', ['pickup', 'return']);

            // The unique payload encoded inside the QR code
            // Owner generates this — borrower scans it
            $table->string('token', 64)->unique();

            // Timestamps recorded when each party confirms
            // Both null initially — filled as each party scans
            $table->timestamp('owner_confirmed_at')->nullable();
            $table->timestamp('borrower_confirmed_at')->nullable();

            // Token is valid for 15 minutes from generation
            $table->timestamp('expires_at');

            // pending   = waiting for both scans
            // completed = both parties confirmed
            // expired   = 15 minute window passed before both confirmed
            $table->enum('status', ['pending', 'completed', 'expired'])
                  ->default('pending');

            $table->timestamps();

            // Indexes for the two most common lookups:
            // 1. Finding a verification by its token (scan endpoint)
            // 2. Finding all verifications for a transaction
            $table->index('token');
            $table->index('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('handover_verifications');
    }
};