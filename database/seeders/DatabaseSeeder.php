<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * The administrator account is always created. Demo content is seeded
     * outside of production, and in production only into an empty database
     * so existing live content is never wiped.
     *
     * Note: model events are intentionally enabled so the HasSlug trait can
     * generate slugs for seeded records.
     */
    public function run(): void
    {
        $this->call(AdminUserSeeder::class);
        $this->call(EditorialContentSeeder::class);
    }
}
