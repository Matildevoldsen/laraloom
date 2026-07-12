<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class FollowChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public readonly int $followerId,
        public readonly int $followingId,
        public readonly bool $isFollowing,
    ) {}

    /** @return array<int, Channel> */
    public function broadcastOn(): array
    {
        return [new Channel("laraloom.profiles.{$this->followingId}")];
    }

    public function broadcastAs(): string
    {
        return 'follow.changed';
    }

    /** @return array{follower_id: int, following_id: int, is_following: bool, occurred_at: string} */
    public function broadcastWith(): array
    {
        return [
            'follower_id' => $this->followerId,
            'following_id' => $this->followingId,
            'is_following' => $this->isFollowing,
            'occurred_at' => now()->toIso8601String(),
        ];
    }
}
