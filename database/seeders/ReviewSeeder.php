<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\Plugin;
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

        $reviewTemplates = [
            // 1. Excellent Review
            [
                'rating' => 5,
                'comments' => [
                    'Absolutely fantastic plugin! This has transformed how we handle {feature}. The documentation is clear, setup was a breeze, and it works flawlessly. Highly recommended!',
                    'Best plugin I\'ve used for {feature}. The developer is very responsive and the code quality is top-notch. Worth every penny!',
                    'Outstanding work! This plugin does exactly what it promises and more. The performance is excellent and it integrates seamlessly with our existing setup.',
                ]
            ],
            // 2. Very Good Review
            [
                'rating' => 4,
                'comments' => [
                    'Great plugin overall! Works well for our needs. Only minor issue is {minor_issue}, but nothing that affects functionality. Would definitely recommend.',
                    'Very solid plugin. Easy to install and configure. The features are comprehensive and well-thought-out. Looking forward to future updates!',
                    'Really good solution for {feature}. The interface is intuitive and the performance is good. A few small improvements would make it perfect.',
                ]
            ],
            // 3. Good Review
            [
                'rating' => 4,
                'comments' => [
                    'Good plugin that gets the job done. Installation was straightforward and it works as advertised. Some features could be more polished but overall satisfied.',
                    'Solid choice for {feature}. Does what it needs to do without any major issues. Documentation could be more detailed but manageable.',
                    'Works well for basic needs. The core functionality is reliable. Would love to see more advanced features in future releases.',
                ]
            ],
            // 4. Average Review
            [
                'rating' => 3,
                'comments' => [
                    'Decent plugin but has room for improvement. Works for basic use cases but lacks some advanced features we need. Support is responsive though.',
                    'It\'s okay. Does the basics but nothing exceptional. Had some minor issues during setup but managed to work through them.',
                    'Average experience. The plugin works but feels a bit dated compared to alternatives. Could use a UI refresh and better documentation.',
                ]
            ],
            // 5. Mixed Review
            [
                'rating' => 3,
                'comments' => [
                    'Mixed feelings about this one. Some features are great, others need work. Performance could be better. Will continue using but hoping for improvements.',
                    'Has potential but needs refinement. The core idea is good but execution is lacking in some areas. Support team is helpful though.',
                    'Works but with caveats. Had to do some customization to fit our needs. Not plug-and-play as advertised but functional once configured.',
                ]
            ],
            // 6. Below Average Review
            [
                'rating' => 2,
                'comments' => [
                    'Disappointed with this plugin. Had high hopes but ran into several issues. Documentation is lacking and setup was confusing.',
                    'Not what I expected. Several features don\'t work as described. Had to reach out to support multiple times. Needs significant improvement.',
                    'Struggling to get this working properly. The concept is good but implementation is buggy. Would not recommend in current state.',
                ]
            ],
            // 7. Positive with Suggestions
            [
                'rating' => 4,
                'comments' => [
                    'Really like this plugin! Works great for our use case. Would love to see {feature_request} added in future updates. Keep up the good work!',
                    'Excellent plugin with minor room for improvement. Suggestion: add {feature_request}. Otherwise, very happy with the purchase!',
                    'Great job on this! Using it in production without issues. One feature request: {feature_request}. That would make it perfect!',
                ]
            ],
            // 8. Technical Review
            [
                'rating' => 5,
                'comments' => [
                    'From a technical standpoint, this is well-architected. Clean code, follows Laravel best practices, and excellent test coverage. Performance metrics are impressive.',
                    'Solid engineering. The plugin is well-structured, uses modern PHP features, and integrates smoothly with our CI/CD pipeline. No conflicts with other packages.',
                    'Technically sound implementation. Good use of design patterns, proper error handling, and comprehensive logging. Easy to extend and customize.',
                ]
            ],
            // 9. Business Value Review
            [
                'rating' => 5,
                'comments' => [
                    'This plugin has saved us countless hours of development time. The ROI is excellent. Our team productivity has increased significantly since implementation.',
                    'Worth every cent. Reduced our development time by 50% and improved our application performance. Highly recommend for businesses looking to scale.',
                    'Game-changer for our business. The features align perfectly with our needs and the cost savings are substantial. Best investment we\'ve made.',
                ]
            ],
            // 10. Detailed Review
            [
                'rating' => 4,
                'comments' => [
                    'After using this for 3 months, here\'s my detailed review: Pros - {pros}. Cons - {cons}. Overall, a solid choice that delivers on its promises.',
                    'Comprehensive review: Installation (5/5), Documentation (4/5), Features (4/5), Support (5/5), Performance (4/5). Would recommend with minor reservations.',
                    'Long-term user here. The plugin has evolved nicely over time. Recent updates addressed most of my concerns. Developer is committed to improvement.',
                ]
            ],
        ];

        $createdReviews = 0;

        foreach ($plugins as $plugin) {
            // Each plugin gets 3-8 reviews
            $reviewCount = rand(3, 8);
            $reviewedUsers = [];

            for ($i = 0; $i < $reviewCount; $i++) {
                // Get a user who hasn't reviewed this plugin yet and isn't the plugin owner
                $availableUsers = $users->filter(function ($user) use ($plugin, $reviewedUsers) {
                    return $user->id !== $plugin->user_id && !in_array($user->id, $reviewedUsers);
                });

                if ($availableUsers->isEmpty()) {
                    break;
                }

                $user = $availableUsers->random();
                $reviewedUsers[] = $user->id;

                // Select random review template
                $template = $reviewTemplates[array_rand($reviewTemplates)];
                $comment = $template['comments'][array_rand($template['comments'])];

                // Replace placeholders
                $comment = str_replace(
                    ['{feature}', '{minor_issue}', '{feature_request}', '{pros}', '{cons}'],
                    [
                        'authentication',
                        'the UI could be more modern',
                        'dark mode support',
                        'easy setup, great docs, responsive support',
                        'could use more examples, minor UI issues'
                    ],
                    $comment
                );

                Review::create([
                    'plugin_id' => $plugin->id,
                    'user_id' => $user->id,
                    'rating' => $template['rating'],
                    'comment' => $comment,
                    'created_at' => now()->subDays(rand(1, 90)),
                ]);

                $createdReviews++;
            }

            // Recalculate plugin rating
            $plugin->recalculateRating();
        }

        $this->command->info("Reviews seeded successfully ($createdReviews total).");
    }
}
