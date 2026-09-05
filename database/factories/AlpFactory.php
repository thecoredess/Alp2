<?php

namespace Database\Factories;

use App\Enums\AlpStatus;
use App\Models\Alp;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Alp>
 */
class AlpFactory extends Factory
{
    protected $model = Alp::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'ref_code' => 'ALP-'.fake()->unique()->numberBetween(100, 999),
            'portfolio_zone' => fake()->randomElement(['Zon Utara', 'Zon Tengah', 'Zon Selatan']),
            'appointment_start' => now()->subYear(),
            'appointment_end' => now()->addYear(),
            'status' => AlpStatus::ACTIVE,
            'phone' => fake()->numerify('01#-#######'),
            'email' => fake()->unique()->safeEmail(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => AlpStatus::INACTIVE]);
    }
}
