<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Create the single, primary administrator account.
     *
     * This seeder is intentionally idempotent: it can be run repeatedly
     * without creating duplicate accounts or resetting anything else.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Site Administrator',
                'username' => 'admin',
                'password' => 'admin123',
                'role' => UserRole::SuperAdmin,
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
                'job_title' => 'Administrator',
                'bio' => 'Primary administrator account for the Samadi Blogs back office.',
            ],
        );
    }
}
