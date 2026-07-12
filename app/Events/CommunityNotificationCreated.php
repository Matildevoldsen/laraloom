<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class CommunityNotificationCreated implements ShouldBroadcastNow, ShouldDispatchAfterCommit
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public readonly int $userId,
        public readonly string $notificationId,
        public readonly string $occurredAt,
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel("sourcefolk.users.{$this->userId}.notifications")];
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    /** @return array{user_id: int, notification_id: string, occurred_at: string} */
    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'notification_id' => $this->notificationId,
            'occurred_at' => $this->occurredAt,
        ];
    }
}
