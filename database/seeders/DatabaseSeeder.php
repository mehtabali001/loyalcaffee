<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => strtolower(env('ADMIN_EMAIL', 'admin@loyalcafe.ae'))],
            ['name' => env('ADMIN_NAME', 'Loyal Admin'), 'password' => env('ADMIN_PASSWORD', 'ChangeMe!2026'), 'role' => 'owner'],
        );

        $this->call([
            SettingsSeeder::class,
            SectionsSeeder::class,
            MenuSeeder::class,
            GallerySeeder::class,
            EventsSeeder::class,
            OffersSeeder::class,
            TestimonialsSeeder::class,
        ]);
    }
}
