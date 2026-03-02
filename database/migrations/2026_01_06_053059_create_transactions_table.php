<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            // The item being transacted
            $table->foreignId('item_id')->constrained()->onDelete('cascade');

            // Owner (lender/seller) — added for direct access without joining items table
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');

            // The person borrowing or buying
            $table->foreignId('borrower_id')->constrained('users')->onDelete('cascade');

            // Transaction type — 'share' added to match item availability modes
            $table->enum('type', ['share', 'lend', 'sell']);

            // Dates
            $table->date('start_date');
            $table->date('due_date')->nullable()->comment('For lend transactions');
            $table->date('return_date')->nullable()->comment('Actual return date, set when item is returned');

            // Financials
            $table->decimal('deposit_amount', 8, 2)->nullable();
            $table->decimal('final_price', 8, 2)->nullable();

            // Status
            $table->enum('status', ['pending', 'active', 'completed', 'late', 'cancelled'])->default('pending');

            $table->timestamps();

            // Index for penalty checks (finding late/active lend transactions)
            $table->index(['status', 'due_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};