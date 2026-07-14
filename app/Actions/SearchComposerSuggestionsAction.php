<?php

namespace App\Actions;

use App\ComposerSuggestionType;
use App\Data\ComposerSuggestion;
use App\Models\Hashtag;
use App\Models\User;
use App\PostStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class SearchComposerSuggestionsAction
{
    private const int LIMIT = 8;

    /** @return list<ComposerSuggestion> */
    public function execute(User $viewer, ComposerSuggestionType $type, string $search): array
    {
        $search = Str::lower(trim($search));

        return match ($type) {
            ComposerSuggestionType::Mention => $this->mentions($viewer, $search),
            ComposerSuggestionType::Hashtag => $this->hashtags($search),
        };
    }

    /** @return list<ComposerSuggestion> */
    private function mentions(User $viewer, string $search): array
    {
        /** @var Collection<int, User> $users */
        $users = User::query()
            ->select(['id', 'name', 'username', 'avatar_url', 'avatar_disk', 'avatar_path', 'is_verified'])
            ->whereNotNull('username')
            ->whereKeyNot($viewer->getKey())
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->whereLike('username', "{$search}%", caseSensitive: false)
                        ->orWhereLike('name', "{$search}%", caseSensitive: false);
                });
            })
            ->withExists([
                'followers as is_followed_by_viewer' => fn (Builder $query): Builder => $query->whereKey($viewer->getKey()),
            ])
            ->withCount('followers')
            ->when($search !== '', fn (Builder $query): Builder => $query->orderByRaw(
                'CASE WHEN LOWER(username) = ? THEN 0 WHEN LOWER(username) LIKE ? THEN 1 ELSE 2 END',
                [$search, "{$search}%"],
            ))
            ->orderByDesc('is_followed_by_viewer')
            ->orderByDesc('is_verified')
            ->orderByDesc('followers_count')
            ->orderBy('username')
            ->limit(self::LIMIT)
            ->get();

        return array_values($users->map(static function (User $user): ComposerSuggestion {
            $username = (string) $user->username;

            return new ComposerSuggestion(
                type: ComposerSuggestionType::Mention,
                id: $user->id,
                label: $user->name,
                description: '@'.$username,
                replacement: '@'.$username,
                image: ['url' => $user->avatarUrl(), 'alt' => ''],
                verified: $user->is_verified,
            );
        })->all());
    }

    /** @return list<ComposerSuggestion> */
    private function hashtags(string $search): array
    {
        /** @var Collection<int, Hashtag> $hashtags */
        $hashtags = Hashtag::query()
            ->select(['id', 'name', 'slug'])
            ->whereHas('posts', fn (Builder $query): Builder => $query
                ->where('status', PostStatus::Published)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now()))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->whereLike('slug', "{$search}%", caseSensitive: false)
                        ->orWhereLike('name', "{$search}%", caseSensitive: false);
                });
            })
            ->withCount([
                'posts as published_posts_count' => fn (Builder $query): Builder => $query
                    ->where('status', PostStatus::Published)
                    ->whereNotNull('published_at')
                    ->where('published_at', '<=', now()),
            ])
            ->when($search !== '', fn (Builder $query): Builder => $query->orderByRaw(
                'CASE WHEN LOWER(slug) = ? THEN 0 WHEN LOWER(slug) LIKE ? THEN 1 ELSE 2 END',
                [$search, "{$search}%"],
            ))
            ->orderByDesc('published_posts_count')
            ->orderBy('slug')
            ->limit(self::LIMIT)
            ->get();

        return array_values($hashtags->map(static function (Hashtag $hashtag): ComposerSuggestion {
            $postCount = (int) $hashtag->published_posts_count;

            return new ComposerSuggestion(
                type: ComposerSuggestionType::Hashtag,
                id: $hashtag->id,
                label: '#'.$hashtag->name,
                description: trans_choice(':count post|:count posts', $postCount, ['count' => $postCount]),
                replacement: '#'.$hashtag->name,
            );
        })->all());
    }
}
