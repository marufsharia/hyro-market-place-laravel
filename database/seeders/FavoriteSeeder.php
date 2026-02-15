<?php

namespace Database\Seeders;

use App\Models\Favorite;
use App\Models\Plugin;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $plugins = Plugin::where('status', 'active')->get();
        $users = User::where('is_admin', false)->get();

        if ($plugins->isEmpty() || $users->isEmpty()) {
            $this->command->warn('Please seed plugins and users first.');
            return;
        }

        // Each user favorites 1-5 random plugins
        foreach ($users as $user) {
            $favoriteCount = rand(1, 5);
            $favoritePlugins = $plugins->random(min($favoriteCount, $plugins->count()));

            foreach ($favoritePlugins as $plugin) {
                // Check if favorite already exists to avoid duplicates
                Favorite::firstOrCreate([
                    'user_id' => $user->id,
                    'plugin_id' => $plugin->id,
                ]);
            }
        }

        $this->command->info('Favorites seeded successfully.');
    }
}
