<?php

// database/migrations/2026_01_08_000000_add_admin_password_plain_to_universities_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            // Stores the plain-text uni admin password so super admin can view it.
            // Intentionally plain text — super admin sets and owns these credentials.
            $table->string('admin_password_plain')
                  ->nullable()
                  ->after('admin_password_hash')
                  ->comment('Plain text credential stored for super admin visibility only');

            // Tracks when credentials were last changed — shown in the credentials card
            $table->timestamp('credentials_updated_at')
                  ->nullable()
                  ->after('admin_password_plain');
        });
    }

    public function down(): void
    {
        Schema::table('universities', function (Blueprint $table) {
            $table->dropColumn(['admin_password_plain', 'credentials_updated_at']);
        });
    }
};