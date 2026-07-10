<?php

namespace App\Actions;

use App\Models\Post;
use App\Models\User;
use App\PostStatus;
use App\Services\UrlNormalizer;
use Illuminate\Support\Str;

class CreatePostAction
{
    public function __construct(private readonly UrlNormalizer $urlNormalizer) {}

    /** @param array<string, mixed> $attributes */
    public function execute(User $user, array $attributes): Post
    {
        $url = is_string($attributes['url'] ?? null)
            ? $this->urlNormalizer->normalize($attributes['url'])
            : null;
        $title = is_string($attributes['title'] ?? null) ? trim($attributes['title']) : null;

        return Post::create([
            'user_id' => $user->id,
            'kind' => $attributes['kind'],
            'status' => PostStatus::Published,
            'title' => $title,
            'slug' => $title ? Str::slug($title).'-'.Str::lower(Str::random(6)) : null,
            'body' => $attributes['body'] ?? null,
            'url' => $url,
            'canonical_url_hash' => $url ? hash('sha256', $url) : null,
            'tags' => $this->parseTags($attributes['tags'] ?? null),
            'is_ai_curated' => false,
            'published_at' => now(),
        ]);
    }

    /** @return array<int, string> */
    private function parseTags(mixed $tags): array
    {
        if (! is_string($tags)) {
            return [];
        }

        return collect(explode(',', $tags))
            ->map(fn (string $tag): string => trim($tag))
            ->filter()
            ->unique(fn (string $tag): string => strtolower($tag))
            ->take(8)
            ->values()
            ->all();
    }
}
