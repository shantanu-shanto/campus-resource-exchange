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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->enum('availability_mode', ['lend', 'sell', 'both'])->default('lend');
            $table->decimal('price', 8, 2)->nullable();
            $table->integer('lending_duration_days')->default(7);
            $table->enum('status', ['available', 'borrowed', 'sold', 'reserved'])->default('available');
            $table->string('pickup_location');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
