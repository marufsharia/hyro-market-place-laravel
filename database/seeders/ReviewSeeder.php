<?php

namespace Database\Seeders;

use App\Models\Plugin;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $plugins = Plugin::where('status', 'active')->get();
        $users = User::where('is_admin', false)->get();

        if ($plugins->isEmpty() || $users->isEmpty()) {
            $this->command->warn('Please seed plugins and users first.');
            return;
        }

        // Create reviews for each active plugin
        foreach ($plugins as $plugin) {
            // Get random number of reviews (between 3 and 10)
            $reviewCount = rand(3, 10);
            
            // Get random users who are not the plugin owner
            $reviewers = $users->where('id', '!=', $plugin->user_id)
                ->random(min($reviewCount, $users->count() - 1));

            foreach ($reviewers as $reviewer) {
                Review::create([
                    'plugin_id' => $plugin->id,
                    'user_id' => $reviewer->id,
                    'rating' => rand(1, 5),
                    'comment' => $this->getRandomComment(),
                ]);
            }
        }

        $this->command->info('Reviews seeded successfully.');
    }

    private function getRandomComment(): ?string
    {
        $comments = [
            'Excellent plugin! Works perfectly with my Laravel project.',
            'Great functionality, but could use better documentation.',
            'This plugin saved me hours of development time. Highly recommended!',
            'Good plugin overall, but had some minor issues with compatibility.',
            'Outstanding quality and support. Five stars!',
            'Works as advertised. Very satisfied with the results.',
            'Could be improved, but does the job.',
            'Fantastic plugin! Easy to integrate and well-maintained.',
            'Not bad, but I expected more features for the price.',
            'Perfect solution for my needs. Thank you!',
            'Very useful plugin with clean code.',
            'Had some issues initially, but support was helpful.',
            'Exactly what I was looking for. Great work!',
            'Solid plugin with good performance.',
            null, // Some reviews without comments
            null,
        ];

        return $comments[array_rand($comments)];
    }
}
