<?php

namespace Database\Factories;

use App\Models\Source;
use App\SourceMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Source>
 */
class SourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'domain' => fake()->unique()->domainName(),
            'method' => SourceMethod::Rss,
            'homepage_url' => fake()->url(),
            'feed_url' => fake()->url(),
            'allows_search' => true,
            'allows_summary' => false,
            'is_active' => true,
            'permission_checked_at' => now(),
            'last_discovered_at' => null,
            'notes' => null,
        ];
    }
}
