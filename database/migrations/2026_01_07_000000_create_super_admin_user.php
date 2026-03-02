<?php

// database/migrations/2026_01_07_000000_create_super_admin_user.php
//
// PURPOSE: Seeds the one and only super admin account.
// This runs as a migration (not a seeder) so it is versioned,
// runs automatically with `php artisan migrate`, and cannot be
// accidentally skipped.
//
// SECURITY REMINDER: Change SUPER_ADMIN_PASSWORD in your .env
// before deploying to production. Never commit real credentials.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * The super admin credentials.
     * Pull from .env so they can be changed without touching code.
     * Defaults are safe for local development only.
     */
    private function credentials(): array
    {
        return [
            'name'     => env('SUPER_ADMIN_NAME',     'Super Admin'),
            'email'    => env('SUPER_ADMIN_EMAIL',    'superadmin@unishare.com'),
            'password' => env('SUPER_ADMIN_PASSWORD', 'SuperAdmin@123'),
        ];
    }

    public function up(): void
    {
        $creds = $this->credentials();

        // Upsert — safe to re-run if migration is rolled back and re-run.
        // Matches on email; updates name and password if row already exists.
        DB::table('users')->updateOrInsert(
            ['email' => $creds['email']],
            [
                'name'          => $creds['name'],
                'email'         => $creds['email'],
                'password'      => Hash::make($creds['password']),
                'role'          => 'super_admin',
                'status'        => 'verified',   // Super admin is always verified
                'university_id' => null,          // Super admin belongs to no university
                'created_at'    => now(),
                'updated_at'    => now(),
            ]
        );
    }

    public function down(): void
    {
        $email = env('SUPER_ADMIN_EMAIL', 'superadmin@unishare.com');

        DB::table('users')
            ->where('email', $email)
            ->where('role', 'super_admin')
            ->delete();
    }
};