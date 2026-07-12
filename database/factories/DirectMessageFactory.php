<?php

namespace Database\Factories;

use App\Models\DirectConversation;
use App\Models\DirectMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DirectMessage> */
class DirectMessageFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'direct_conversation_id' => DirectConversation::factory(),
            'sender_id' => fn (array $attributes): int => DirectConversation::query()
                ->findOrFail((int) $attributes['direct_conversation_id'])
                ->participant_one_id,
            'client_id' => fake()->uuid(),
            'body' => fake()->paragraph(),
        ];
    }
}
