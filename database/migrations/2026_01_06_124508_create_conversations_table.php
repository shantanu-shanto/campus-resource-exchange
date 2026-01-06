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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            
            // Users involved in conversation
            $table->foreignId('user_id_1')
                ->constrained('users')
                ->onDelete('cascade');
            
            $table->foreignId('user_id_2')
                ->constrained('users')
                ->onDelete('cascade');
            
            // Timestamps
            $table->timestamps();
            
            // Soft delete for archiving conversations
            $table->softDeletes();
            
            // Index for faster queries
            $table->index(['user_id_1', 'user_id_2']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
