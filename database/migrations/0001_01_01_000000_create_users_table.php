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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Role system: super_admin > uni_admin > user
            $table->enum('role', ['super_admin', 'uni_admin', 'user'])->default('user');

            // Account status: pending until verified by uni_admin (or super_admin for uni_admins)
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');

            // University association (nullable: super_admin has no university)
            // FIX: foreign key constraint removed from here — universities table is created in
            // 0000_01_01_000000_create_universities_table.php which runs just before this file,
            // so the constraint is safe to define directly here now.
            $table->foreignId('university_id')->nullable()->constrained('universities')->nullOnDelete();

            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};