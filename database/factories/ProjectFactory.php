<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use App\ProjectKind;
use App\ProjectStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'kind' => fake()->randomElement(ProjectKind::cases()),
            'status' => ProjectStatus::Published,
            'name' => fake()->unique()->company(),
            'slug' => fn (array $attributes): string => Str::slug((string) $attributes['name']).'-'.Str::lower(Str::random(4)),
            'tagline' => fake()->sentence(8),
            'description' => fake()->paragraphs(3, true),
            'url' => fake()->url(),
            'repository_url' => 'https://github.com/'.fake()->userName().'/'.fake()->slug(),
            'laravel_cloud_url' => fake()->boolean(60) ? 'https://'.fake()->slug().'.laravel.cloud' : null,
            'logo_url' => null,
            'screenshot_url' => null,
            'tags' => fake()->randomElements(['Laravel', 'Livewire', 'Open source', 'AI', 'Cloud'], 3),
            'is_open_source' => true,
            'featured_at' => null,
            'published_at' => now()->subDays(fake()->numberBetween(0, 30)),
        ];
    }
}
