<?php

namespace App\Actions;

use App\Events\DirectMessageCreated;
use App\Models\DirectConversation;
use App\Models\DirectMessage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SendDirectMessageAction
{
    public function execute(
        User $sender,
        DirectConversation $conversation,
        string $body,
        ?string $clientId = null,
    ): DirectMessage {
        Gate::forUser($sender)->authorize('send', $conversation);

        $validated = Validator::make([
            'body' => trim($body),
            'client_id' => $clientId ?? Str::uuid()->toString(),
        ], [
            'body' => ['required', 'string', 'max:4000'],
            'client_id' => ['required', 'uuid'],
        ])->validate();

        return DB::transaction(function () use ($conversation, $sender, $validated): DirectMessage {
            $message = DirectMessage::query()->createOrFirst([
                'client_id' => $validated['client_id'],
            ], [
                'direct_conversation_id' => $conversation->id,
                'sender_id' => $sender->id,
                'body' => $validated['body'],
            ]);

            if ($message->direct_conversation_id !== $conversation->id
                || $message->sender_id !== $sender->id) {
                throw ValidationException::withMessages([
                    'client_id' => 'The message identifier has already been used.',
                ]);
            }

            if ($message->wasRecentlyCreated) {
                $conversation->forceFill(['last_message_at' => $message->created_at])->save();
                DirectMessageCreated::dispatch(
                    $conversation->id,
                    $message->id,
                    $sender->id,
                    $conversation->participantIds(),
                    $message->created_at?->toIso8601String() ?? now()->toIso8601String(),
                );
            }

            return $message->load('sender');
        });
    }
}
