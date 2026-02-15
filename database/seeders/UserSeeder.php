<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            // 1. Super Admin
            [
                'name' => 'Super Admin',
                'email' => 'admin@hyro.dev',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'is_admin' => true,
            ],
            // 2. Plugin Developer - Active
            [
                'name' => 'Alice Developer',
                'email' => 'alice@developer.com',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'is_admin' => false,
            ],
            // 3. Plugin Developer - Prolific
            [
                'name' => 'Bob Builder',
                'email' => 'bob@builder.com',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'is_admin' => false,
            ],
            // 4. Regular User - Active Reviewer
            [
                'name' => 'Charlie Reviewer',
                'email' => 'charlie@reviewer.com',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'is_admin' => false,
            ],
            // 5. Regular User - Casual
            [
                'name' => 'Diana User',
                'email' => 'diana@user.com',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'is_admin' => false,
            ],
            // 6. New User - Unverified
            [
                'name' => 'Eve Newbie',
                'email' => 'eve@newbie.com',
                'email_verified_at' => null,
                'password' => bcrypt('password'),
                'is_admin' => false,
            ],
            // 7. Power User - Many Favorites
            [
                'name' => 'Frank Collector',
                'email' => 'frank@collector.com',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'is_admin' => false,
            ],
            // 8. Developer - Security Focused
            [
                'name' => 'Grace Security',
                'email' => 'grace@security.com',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'is_admin' => false,
            ],
            // 9. Developer - UI/UX Specialist
            [
                'name' => 'Henry Designer',
                'email' => 'henry@designer.com',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'is_admin' => false,
            ],
            // 10. Test User
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'is_admin' => false,
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        // Create 20 additional random users for variety
        User::factory(20)->create();

        $this->command->info('Users seeded successfully (30 total).');
    }
}
