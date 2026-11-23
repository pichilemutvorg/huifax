<?php

namespace Database\Factories;

use App\Models\ScheduleException;
use App\Models\ScheduleSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScheduleException>
 */
class ScheduleExceptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'schedule_slot_id' => ScheduleSlot::factory(),
            'date' => now()->toDateString(),
            'rescheduled_to' => null,
        ];
    }
}
