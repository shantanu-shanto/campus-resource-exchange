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
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->foreignId('borrower_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['lend', 'sell']);
            $table->date('start_date');
            $table->date('due_date')->nullable();
            $table->date('return_date')->nullable();
            $table->decimal('deposit_amount', 8, 2)->nullable();
            $table->decimal('final_price', 8, 2)->nullable();
            $table->enum('status', ['pending', 'active', 'completed', 'late', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
