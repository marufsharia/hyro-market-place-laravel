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

        $createdFavorites = 0;

        // Define user types with different favorite patterns
        $userTypes = [
            // 1. Power User - Favorites many plugins (20-30%)
            'power' => 0.25,
            // 2. Active User - Favorites several plugins (10-20%)
            'active' => 0.15,
            // 3. Casual User - Favorites a few plugins (5-10%)
            'casual' => 0.08,
            // 4. New User - Favorites 1-2 plugins (1-5%)
            'new' => 0.03,
            // 5. No favorites
            'none' => 0,
        ];

        foreach ($users as $user) {
            // Randomly assign user type
            $rand = rand(1, 100) / 100;
            
            if ($rand < 0.10) {
                $type = 'power';
            } elseif ($rand < 0.30) {
                $type = 'active';
            } elseif ($rand < 0.60) {
                $type = 'casual';
            } elseif ($rand < 0.85) {
                $type = 'new';
            } else {
                $type = 'none';
            }

            if ($type === 'none') {
                continue;
            }

            // Calculate number of favorites based on user type
            $favoriteCount = (int) ceil($plugins->count() * $userTypes[$type]);
            $favoriteCount = max(1, min($favoriteCount, $plugins->count()));

            // Randomly select plugins to favorite
            $favoritedPlugins = $plugins->random(min($favoriteCount, $plugins->count()));

            foreach ($favoritedPlugins as $plugin) {
                try {
                    Favorite::create([
                        'user_id' => $user->id,
                        'plugin_id' => $plugin->id,
                        'created_at' => now()->subDays(rand(1, 120)),
                    ]);
                    $createdFavorites++;
                } catch (\Exception $e) {
                    // Skip if duplicate (unique constraint)
                    continue;
                }
            }
        }

        $this->command->info("Favorites seeded successfully ($createdFavorites total).");
    }
}
