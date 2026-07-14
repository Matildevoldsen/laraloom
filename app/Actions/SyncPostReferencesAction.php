<?php

namespace App\Actions;

use App\Models\Hashtag;
use App\Models\Post;
use App\Models\User;
use App\Services\PostReferenceExtractor;
use Illuminate\Database\Eloquent\Collection;

final class SyncPostReferencesAction
{
    public function __construct(private readonly PostReferenceExtractor $extractor) {}

    public function execute(Post $post): void
    {
        $references = $this->extractor->extract($post->body);

        $this->syncHashtags($post, $references->hashtags);
        $this->syncMentions($post, $references->mentions);
    }

    /** @param array<string, string> $hashtags */
    private function syncHashtags(Post $post, array $hashtags): void
    {
        if ($hashtags === []) {
            $post->hashtags()->detach();

            return;
        }

        $timestamp = now();
        $rows = [];

        foreach ($hashtags as $slug => $name) {
            $rows[] = [
                'name' => $name,
                'slug' => $slug,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        Hashtag::query()->insertOrIgnore($rows);

        $hashtagIds = Hashtag::query()
            ->whereIn('slug', array_keys($hashtags))
            ->pluck('id')
            ->all();

        $post->hashtags()->sync($hashtagIds);
    }

    /** @param list<string> $handles */
    private function syncMentions(Post $post, array $handles): void
    {
        if ($handles === []) {
            $post->mentions()->delete();

            return;
        }

        /** @var Collection<int, User> $mentionedUsers */
        $mentionedUsers = User::query()
            ->select(['id', 'username'])
            ->whereIn('username', $handles)
            ->get();

        $mentionedUserIds = $mentionedUsers->modelKeys();

        if ($mentionedUserIds === []) {
            $post->mentions()->delete();

            return;
        }

        $post->mentions()
            ->whereNotIn('mentioned_user_id', $mentionedUserIds)
            ->delete();

        foreach ($mentionedUsers as $mentionedUser) {
            $handle = (string) $mentionedUser->username;
            $mention = $post->mentions()->firstOrCreate(
                ['mentioned_user_id' => $mentionedUser->id],
                ['handle' => $handle],
            );

            if (! $mention->wasRecentlyCreated && $mention->handle !== $handle) {
                $mention->update(['handle' => $handle]);
            }
        }
    }
}
