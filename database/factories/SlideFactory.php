<?php

namespace Database\Factories;

use App\Models\Slide;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Slide>
 */
class SlideFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'notices',
            'name' => null,
            'sort_order' => 0,
            'duration' => null,
            'is_enabled' => true,
            'settings' => null,
        ];
    }

    public function ofType(string $type): static
    {
        return $this->state(['type' => $type]);
    }

    public function disabled(): static
    {
        return $this->state(['is_enabled' => false]);
    }
}
