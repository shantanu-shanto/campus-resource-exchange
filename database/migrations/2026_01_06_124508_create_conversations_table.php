<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

            // Users involved in the conversation
            $table->foreignId('user_id_1')->constrained('users')->onDelete('cascade');
            $table->foreignId('user_id_2')->constrained('users')->onDelete('cascade');

            // The item this conversation is about (context for why they're messaging)
            $table->foreignId('item_id')->nullable()->constrained('items')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Prevent duplicate conversations between the same two users about the same item
            $table->unique(['user_id_1', 'user_id_2', 'item_id']);

            // Index for fast lookup
            $table->index(['user_id_1', 'user_id_2']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};