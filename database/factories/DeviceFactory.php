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
            'data' => '
            {
                  "vlans": [
                    {
                      "id": 0,
                      "tagged": [
                        37,
                        32
                      ],
                      "untagged": [
                        30,
                        31
                      ]
                    }
                  ],
                  "trunks": [
                    {
                      "id": "Trk1",
                      "ports": [
                        30,
                        31,
                        32,
                        33
                      ]
                    }
                  ]
                }',
            'building' => fake()->numberBetween(0,5),
            'location' => fake()->numberBetween(0,20),
            'details' => fake()->text(20),
            'number' => fake()->numberBetween(1,4),

        ];
    }
}
