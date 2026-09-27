<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        // The demo tech catalog (it seeds its own brands) and the home page banners
        $this->call([
            CategorySeeder::class,
            AttributeSeeder::class,
            DemoCatalogSeeder::class,
            BannerSeeder::class,
        ]);
    }
}
