<?php

// database/migrations/2026_01_05_000000_create_universities_table.php
// Run: php artisan make:migration create_universities_table
// IMPORTANT: This file must be dated BEFORE the users table migration.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('universities', function (Blueprint $table) {
            $table->id();

            // Basic university info
            $table->string('name');
            $table->string('domain')->unique()->comment('Official email domain e.g. bits-pilani.ac.in');

            // Location — state is used to filter universities on the student registration page
            $table->string('state');
            $table->string('city');
            $table->string('country')->default('India');

            $table->text('description')->nullable();

            // Application / approval workflow
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();

            // Applicant info (the person who submitted the university registration request)
            $table->string('applicant_name');
            $table->string('applicant_email');
            $table->string('applicant_phone')->nullable();

            // Credentials issued by super_admin upon approval
            // These are sent to the university representative to access the uni-admin panel
            $table->string('admin_email')->nullable()->unique();
            $table->string('admin_password_hash')->nullable()->comment('Hashed credential issued by super_admin');

            // Timestamps for audit trail
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();

            // Index for state-based filtering on student registration
            $table->index('state');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('universities');
    }
};