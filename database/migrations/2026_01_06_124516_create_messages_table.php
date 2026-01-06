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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            
            // Conversation reference
            $table->foreignId('conversation_id')
                ->constrained('conversations')
                ->onDelete('cascade');
            
            // Sender
            $table->foreignId('sender_id')
                ->constrained('users')
                ->onDelete('cascade');
            
            // Receiver
            $table->foreignId('receiver_id')
                ->constrained('users')
                ->onDelete('cascade');
            
            // Message content
            $table->text('message');
            
            // Read status
            $table->timestamp('read_at')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Soft delete for user deletion
            $table->softDeletes();
            
            // Indexes for faster queries
            $table->index('conversation_id');
            $table->index('sender_id');
            $table->index('receiver_id');
            $table->index('read_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
