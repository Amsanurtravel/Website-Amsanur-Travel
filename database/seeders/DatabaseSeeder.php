<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Real packages, the mentor and draft guides from the client's brochures; only fills an empty database.
        $this->call(ContentSeeder::class);

        // Every user can sign in to the admin panel, so the known-password
        // account and the placeholder articles are for local development only.
        if (app()->environment('local')) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

            $this->call(ArticleSeeder::class);
        }
    }
}
