<?php

namespace Database\Factories;

use App\Models\DirectConversation;
use App\Models\DirectConversationState;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DirectConversationState> */
class DirectConversationStateFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'direct_conversation_id' => DirectConversation::factory(),
            'user_id' => fn (array $attributes): int => DirectConversation::query()
                ->findOrFail((int) $attributes['direct_conversation_id'])
                ->participant_one_id,
            'last_read_message_id' => null,
            'last_read_at' => null,
            'archived_at' => null,
        ];
    }
}
