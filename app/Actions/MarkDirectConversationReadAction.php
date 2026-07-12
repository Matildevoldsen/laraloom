<?php

namespace App\Actions;

use App\Models\DirectConversation;
use App\Models\DirectConversationState;
use App\Models\DirectMessage;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class MarkDirectConversationReadAction
{
    public function execute(
        User $user,
        DirectConversation $conversation,
        ?DirectMessage $message = null,
    ): DirectConversationState {
        Gate::forUser($user)->authorize('markRead', $conversation);

        $message ??= $conversation->messages()->latest('id')->first();
        if ($message !== null && $message->direct_conversation_id !== $conversation->id) {
            throw ValidationException::withMessages([
                'message' => 'The read position does not belong to this conversation.',
            ]);
        }

        $state = DirectConversationState::query()->firstOrNew([
            'direct_conversation_id' => $conversation->id,
            'user_id' => $user->id,
        ]);
        $currentMessageId = (int) ($state->last_read_message_id ?? 0);
        $requestedMessageId = $message === null ? 0 : $message->id;

        if ($requestedMessageId >= $currentMessageId) {
            $state->fill([
                'last_read_message_id' => $message === null ? null : $message->id,
                'last_read_at' => now(),
            ])->save();
        }

        return $state;
    }
}
