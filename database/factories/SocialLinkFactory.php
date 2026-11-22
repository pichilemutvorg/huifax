<?php

namespace Database\Factories;

use App\Enums\SocialPlatform;
use App\Models\SocialLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SocialLink>
 */
class SocialLinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'platform' => fake()->randomElement(SocialPlatform::cases()),
            'handle' => '@'.fake()->userName(),
            'url' => null,
            'sort_order' => 0,
            'is_enabled' => true,
        ];
    }
}
