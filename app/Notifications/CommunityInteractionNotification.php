<?php

namespace App\Notifications;

use App\CommunityNotificationType;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class CommunityInteractionNotification extends Notification
{
    public function __construct(
        public readonly CommunityNotificationType $interaction,
        public readonly User $actor,
        public readonly ?Post $post = null,
        public readonly ?Comment $comment = null,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function databaseType(object $notifiable): string
    {
        return 'community.'.$this->interaction->value;
    }

    /**
     * @return array{
     *     kind: string,
     *     verb: string,
     *     icon: string,
     *     actor_id: int,
     *     actor_name: string,
     *     actor_username: string|null,
     *     actor_avatar_url: string,
     *     actor_verified: bool,
     *     post_id: int|null,
     *     post_title: string|null,
     *     comment_id: int|null,
     *     comment_excerpt: string|null,
     *     url: string
     * }
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'kind' => $this->interaction->value,
            'verb' => $this->interaction->verb(),
            'icon' => $this->interaction->icon(),
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->name,
            'actor_username' => $this->actor->username,
            'actor_avatar_url' => $this->actor->avatarUrl(),
            'actor_verified' => $this->actor->is_verified,
            'post_id' => $this->post?->id,
            'post_title' => $this->post?->title,
            'comment_id' => $this->comment?->id,
            'comment_excerpt' => $this->comment instanceof Comment
                ? Str::of($this->comment->body)->squish()->limit(140)->toString()
                : null,
            'url' => $this->targetUrl(),
        ];
    }

    private function targetUrl(): string
    {
        if ($this->interaction === CommunityNotificationType::Follow) {
            return route('profiles.show', $this->actor, absolute: false);
        }

        abort_unless($this->post instanceof Post, 500);

        $url = route('posts.show', $this->post, absolute: false);

        return $this->comment instanceof Comment ? "{$url}#comment-{$this->comment->id}" : $url;
    }
}
