<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed users first (includes admin and regular users)
        $this->call(UserSeeder::class);

        // Seed categories
        $this->call(CategorySeeder::class);

        // Seed plugins (depends on users and categories)
        $this->call(PluginSeeder::class);

        // Seed reviews (depends on plugins and users)
        $this->call(ReviewSeeder::class);

        // Seed favorites (depends on plugins and users)
        $this->call(FavoriteSeeder::class);

        // Seed documentation
        $this->call(DocumentationSeeder::class);

        $this->command->info('Database seeding completed successfully!');
    }
}
