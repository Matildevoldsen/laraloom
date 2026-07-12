<?php

namespace App\Events;

use App\CommunityActivityType;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class CommunityActivity implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public readonly CommunityActivityType $type,
        public readonly int $postId,
        public readonly bool $isPublic,
    ) {}

    /** @return array<int, Channel|PrivateChannel> */
    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('sourcefolk.admin')];

        if ($this->isPublic) {
            $channels[] = new Channel('sourcefolk.feed');
            $channels[] = new Channel("sourcefolk.posts.{$this->postId}");
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'community.activity';
    }

    /** @return array{type: string, post_id: int, occurred_at: string} */
    public function broadcastWith(): array
    {
        return [
            'type' => $this->type->value,
            'post_id' => $this->postId,
            'occurred_at' => now()->toIso8601String(),
        ];
    }
}
