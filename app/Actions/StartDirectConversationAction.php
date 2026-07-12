<?php

namespace App\Actions;

use App\Models\DirectConversation;
use App\Models\DirectConversationState;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class StartDirectConversationAction
{
    public function execute(User $sender, User $recipient): DirectConversation
    {
        Gate::forUser($sender)->authorize('create', [DirectConversation::class, $recipient]);

        [$participantOneId, $participantTwoId] = collect([$sender->id, $recipient->id])
            ->sort()
            ->values()
            ->all();

        return DB::transaction(function () use (
            $sender,
            $participantOneId,
            $participantTwoId,
        ): DirectConversation {
            $conversation = DirectConversation::query()->createOrFirst([
                'participant_one_id' => $participantOneId,
                'participant_two_id' => $participantTwoId,
            ], [
                'initiated_by_id' => $sender->id,
            ]);

            foreach ($conversation->participantIds() as $participantId) {
                DirectConversationState::query()->createOrFirst([
                    'direct_conversation_id' => $conversation->id,
                    'user_id' => $participantId,
                ]);
            }

            return $conversation->load(['participantOne', 'participantTwo', 'states']);
        });
    }
}
