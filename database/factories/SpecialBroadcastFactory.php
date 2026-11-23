<?php

namespace Database\Factories;

use App\Models\Program;
use App\Models\SpecialBroadcast;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SpecialBroadcast>
 */
class SpecialBroadcastFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'program_id' => Program::factory(),
            'starts_at' => now()->addDay()->setTime(20, 0),
            'duration_minutes' => 90,
            'tag' => null,
        ];
    }
}
