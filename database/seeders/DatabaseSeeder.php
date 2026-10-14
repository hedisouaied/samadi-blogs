<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * The administrator account is always created. Demo content is only
     * seeded outside of production so live environments stay clean.
     *
     * Note: model events are intentionally enabled so the HasSlug trait can
     * generate slugs for seeded records.
     */
    public function run(): void
    {
        $this->call(AdminUserSeeder::class);

        if (! app()->environment('production')) {
            $this->call(EditorialContentSeeder::class);
        }
    }
}
