<?php

namespace Database\Factories;

use App\Models\DirectConversation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DirectConversation> */
class DirectConversationFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'participant_one_id' => User::factory(),
            'participant_two_id' => User::factory(),
            'initiated_by_id' => fn (array $attributes): int => (int) $attributes['participant_one_id'],
            'last_message_at' => null,
        ];
    }

    public function between(User $first, User $second, ?User $initiator = null): static
    {
        [$participantOneId, $participantTwoId] = collect([$first->id, $second->id])
            ->sort()
            ->values()
            ->all();

        return $this->state([
            'participant_one_id' => $participantOneId,
            'participant_two_id' => $participantTwoId,
            'initiated_by_id' => ($initiator ?? $first)->id,
        ]);
    }
}
