<?php

namespace Database\Factories;

use App\Models\FeaturedProgram;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FeaturedProgram>
 */
class FeaturedProgramFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'tagline' => fake()->sentence(6),
            'schedule' => 'Lunes a viernes, 21:00',
            'label' => null,
            'path' => FeaturedProgram::DIRECTORY.'/'.fake()->uuid().'.webp',
            'starts_at' => null,
            'ends_at' => null,
            'duration' => null,
            'sort_order' => 0,
            'is_enabled' => true,
        ];
    }

    public function disabled(): static
    {
        return $this->state(['is_enabled' => false]);
    }
}
