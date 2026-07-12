<?php

namespace App\Observers;

use App\CommunityActivityType;
use App\Events\CommunityActivity;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\Repost;
use App\PostStatus;

class CommunityActivityObserver
{
    public bool $afterCommit = true;

    public function created(Post|Comment|Reaction|Repost $activity): void
    {
        $this->dispatch($activity, match (true) {
            $activity instanceof Post => CommunityActivityType::PostCreated,
            $activity instanceof Comment => CommunityActivityType::CommentCreated,
            $activity instanceof Reaction => CommunityActivityType::ReactionCreated,
            $activity instanceof Repost => CommunityActivityType::RepostCreated,
        });
    }

    public function updated(Post|Comment|Reaction|Repost $activity): void
    {
        if (! $activity instanceof Post || ! $activity->wasChanged([
            'body', 'published_at', 'status', 'summary', 'title',
        ])) {
            return;
        }

        $this->dispatch($activity, CommunityActivityType::PostUpdated);
    }

    public function deleted(Post|Comment|Reaction|Repost $activity): void
    {
        $this->dispatch($activity, match (true) {
            $activity instanceof Post => CommunityActivityType::PostDeleted,
            $activity instanceof Comment => CommunityActivityType::CommentDeleted,
            $activity instanceof Reaction => CommunityActivityType::ReactionDeleted,
            $activity instanceof Repost => CommunityActivityType::RepostDeleted,
        });
    }

    private function dispatch(
        Post|Comment|Reaction|Repost $activity,
        CommunityActivityType $type,
    ): void {
        $post = $activity instanceof Post ? $activity : $activity->post;

        if (! $post instanceof Post) {
            return;
        }

        broadcast(new CommunityActivity(
            $type,
            $post->id,
            $post->status === PostStatus::Published && $post->published_at?->isPast() === true,
        ))->toOthers();
    }
}
