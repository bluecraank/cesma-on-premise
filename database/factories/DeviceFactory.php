<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Device>
 */
class DeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->domainName(),
            'hostname' => fake()->ipv4(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'data' => '{
                "_id": "637d1ce0518b95ceba74dff7",
                "index": 0,
                "guid": "72f14a47-7bac-439a-b7fb-28dafc99da01",
                "isActive": true,
                "age": 36,
                "eyeColor": "blue",
                "name": "Marshall Higgins",
                "registered": "2017-01-18T04:08:22 -01:00",
                "latitude": -25.452026,
                "longitude": -111.859179
              }',
            'building' => fake()->numberBetween(0,5),
            'location' => fake()->numberBetween(0,20),
            'details' => fake()->text(20),
            'number' => fake()->numberBetween(1,4),

        ];
    }
}
