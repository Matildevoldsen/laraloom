<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class DirectMessageCreated implements ShouldBroadcastNow, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets;

    /**
     * @param  array{0: int, 1: int}  $recipientIds
     */
    public function __construct(
        public readonly int $conversationId,
        public readonly int $messageId,
        public readonly int $senderId,
        public readonly array $recipientIds,
        public readonly string $occurredAt,
    ) {}

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return array_map(
            fn (int $userId): PrivateChannel => new PrivateChannel("laraloom.users.{$userId}.messages"),
            $this->recipientIds,
        );
    }

    public function broadcastAs(): string
    {
        return 'message.created';
    }

    /** @return array{conversation_id: int, message_id: int, sender_id: int, occurred_at: string} */
    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversationId,
            'message_id' => $this->messageId,
            'sender_id' => $this->senderId,
            'occurred_at' => $this->occurredAt,
        ];
    }
}
