<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@hyro.test',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);

        // Create a few specific test users
        User::create([
            'name' => 'John Developer',
            'email' => 'john@hyro.test',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'is_admin' => false,
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@hyro.test',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'is_admin' => false,
        ]);

        // Create additional random users
        User::factory(15)->create();

        $this->command->info('Users seeded successfully.');
    }
}
