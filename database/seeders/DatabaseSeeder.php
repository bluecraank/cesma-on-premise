<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        \App\Models\Site::create([
            'name' => 'Default',
        ]);

        \App\Models\Building::create([
            'name' => 'Default',
            'site_id' => 1,
        ]);

        \App\Models\Room::create([
            'name' => 'Default',
            'building_id' => 1,
        ]);
    }
}
