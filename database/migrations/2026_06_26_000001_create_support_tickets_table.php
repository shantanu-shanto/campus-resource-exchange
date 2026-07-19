<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('university_id')
                  ->constrained('universities')
                  ->cascadeOnDelete();

            $table->foreignId('transaction_id')
                  ->nullable()
                  ->constrained('transactions')
                  ->nullOnDelete();

            $table->foreignId('item_id')
                  ->nullable()
                  ->constrained('items')
                  ->nullOnDelete();

            $table->string('subject');
            $table->text('description');

            $table->enum('category', [
                'transaction_issue',
                'item_condition',
                'penalty_dispute',
                'user_behaviour',
                'account_issue',
                'other',
            ])->default('other');

            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');

            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');

            $table->foreignId('resolved_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['university_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
