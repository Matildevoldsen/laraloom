<?php

namespace Database\Factories;

use App\Models\Mention;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Mention>
 */
class MentionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'mentioned_user_id' => User::factory(),
            'handle' => Str::lower(fake()->userName()),
        ];
    }
}
