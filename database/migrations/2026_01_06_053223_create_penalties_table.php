<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penalties', function (Blueprint $table) {
            $table->id();

            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');

            $table->integer('days_late');
            $table->decimal('amount', 8, 2);

            // FIX: added 'waiver_requested' status to support requestWaiver() in TransactionController
            $table->enum('status', ['pending', 'paid', 'waived', 'waiver_requested'])->default('pending');

            // FIX: stores the reason submitted by the borrower when requesting a waiver
            $table->text('waiver_reason')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penalties');
    }
};