<?php

namespace Database\Factories;

use App\Models\Program;
use App\Models\ScheduleSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScheduleSlot>
 */
class ScheduleSlotFactory extends Factory
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
            'weekdays' => [1, 2, 3, 4, 5],
            'start_time' => '21:00:00',
            'duration_minutes' => 60,
            'tag' => null,
            'valid_from' => null,
            'valid_until' => null,
            'is_enabled' => true,
        ];
    }

    /**
     * @param  list<int>  $weekdays  ISO-8601 weekday numbers.
     */
    public function on(array $weekdays, string $startTime): static
    {
        return $this->state(['weekdays' => $weekdays, 'start_time' => $startTime]);
    }
}
