<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();

            // Owner of the item
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Campus isolation: item belongs to a specific university
            $table->foreignId('university_id')->constrained('universities')->onDelete('cascade');

            $table->string('title');
            $table->text('description');

            // Fixed: 'share' added to match the project overview (Share/Rent/Sell)
            $table->enum('availability_mode', ['share', 'lend', 'sell', 'both'])->default('lend');

            // Price (null for free/shared items)
            $table->decimal('price', 8, 2)->nullable();

            // Lending duration in days (only relevant for lend/both modes)
            $table->integer('lending_duration_days')->nullable()->default(7);

            // Item status
            $table->enum('status', ['available', 'borrowed', 'sold', 'reserved'])->default('available');

            // Where to pick up the item
            $table->string('pickup_location');

            // Optional: image for the listing
            $table->string('image_path')->nullable();

            $table->timestamps();

            // Index for fast campus-scoped searches
            $table->index(['university_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};