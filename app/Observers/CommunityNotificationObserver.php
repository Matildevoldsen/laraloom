<?php

namespace App\Observers;

use App\CommunityNotificationType;
use App\Events\CommunityNotificationCreated;
use App\Models\Comment;
use App\Models\Follow;
use App\Models\Mention;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\Repost;
use App\Models\User;
use App\Notifications\CommunityInteractionNotification;

class CommunityNotificationObserver
{
    public function created(Follow|Comment|Mention|Reaction|Repost $activity): void
    {
        match (true) {
            $activity instanceof Follow => $this->notifyAboutFollow($activity),
            $activity instanceof Comment => $this->notifyAboutComment($activity),
            $activity instanceof Mention => $this->notifyAboutMention($activity),
            $activity instanceof Reaction => $this->notifyPostAuthor($activity, CommunityNotificationType::Reaction),
            $activity instanceof Repost => $this->notifyPostAuthor($activity, CommunityNotificationType::Repost),
        };
    }

    private function notifyAboutMention(Mention $mention): void
    {
        $mention->loadMissing(['mentionedUser', 'post.user']);
        $actor = $mention->post->user;

        if ($actor instanceof User) {
            $this->notify(
                $mention->mentionedUser,
                $actor,
                CommunityNotificationType::Mention,
                $mention->post,
            );
        }
    }

    private function notifyAboutFollow(Follow $follow): void
    {
        $follow->loadMissing(['follower', 'following']);

        $this->notify(
            $follow->following,
            $follow->follower,
            CommunityNotificationType::Follow,
        );
    }

    private function notifyAboutComment(Comment $comment): void
    {
        $comment->loadMissing(['user', 'post.user', 'parent.user']);
        $actor = $comment->user;
        $post = $comment->post;
        $parentAuthor = $comment->parent?->user;

        if ($parentAuthor instanceof User && ! $parentAuthor->is($actor)) {
            $this->notify($parentAuthor, $actor, CommunityNotificationType::Reply, $post, $comment);
        }

        if ($post->user instanceof User && ! $post->user->is($parentAuthor)) {
            $this->notify($post->user, $actor, CommunityNotificationType::Comment, $post, $comment);
        }
    }

    private function notifyPostAuthor(
        Reaction|Repost $activity,
        CommunityNotificationType $interaction,
    ): void {
        $activity->loadMissing(['user', 'post.user']);

        $this->notify($activity->post->user, $activity->user, $interaction, $activity->post);
    }

    private function notify(
        ?User $recipient,
        User $actor,
        CommunityNotificationType $interaction,
        ?Post $post = null,
        ?Comment $comment = null,
    ): void {
        if (! $recipient instanceof User || $recipient->is($actor)) {
            return;
        }

        $notification = new CommunityInteractionNotification(
            $interaction,
            $actor,
            $post,
            $comment,
        );
        $recipient->notify($notification);

        CommunityNotificationCreated::dispatch(
            $recipient->id,
            (string) $notification->id,
            now()->toIso8601String(),
        );
    }
}
