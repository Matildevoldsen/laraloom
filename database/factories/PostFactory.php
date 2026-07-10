<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use App\PostKind;
use App\PostStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
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
            'kind' => fake()->randomElement(PostKind::cases()),
            'status' => PostStatus::Published,
            'title' => fake()->sentence(8),
            'slug' => fn (array $attributes): string => Str::slug((string) $attributes['title']).'-'.Str::lower(Str::random(5)),
            'body' => fake()->paragraphs(2, true),
            'summary' => fake()->paragraph(),
            'why_it_matters' => fake()->sentence(),
            'url' => fake()->url(),
            'canonical_url_hash' => fn (array $attributes): string => hash('sha256', (string) $attributes['url']),
            'source_name' => fake()->company(),
            'source_author' => fake()->name(),
            'source_published_at' => now()->subHours(fake()->numberBetween(1, 72)),
            'tags' => fake()->randomElements(['Laravel', 'Livewire', 'AI', 'Cloud', 'Packages'], 2),
            'metadata' => [],
            'is_ai_curated' => false,
            'ai_confidence' => null,
            'published_at' => now()->subHours(fake()->numberBetween(1, 48)),
        ];
    }

    public function curated(): static
    {
        return $this->state(fn (): array => [
            'user_id' => null,
            'is_ai_curated' => true,
            'ai_confidence' => 92,
        ]);
    }
}
