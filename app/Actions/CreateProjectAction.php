<?php

namespace App\Actions;

use App\Models\Project;
use App\Models\User;
use App\ProjectStatus;
use App\Services\UrlNormalizer;
use Illuminate\Support\Str;

class CreateProjectAction
{
    public function __construct(private readonly UrlNormalizer $urlNormalizer) {}

    /** @param array<string, mixed> $attributes */
    public function execute(User $user, array $attributes): Project
    {
        $name = (string) $attributes['name'];

        return Project::create([
            'user_id' => $user->id,
            'kind' => $attributes['kind'],
            'status' => ProjectStatus::Published,
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(5)),
            'tagline' => $attributes['tagline'],
            'description' => $attributes['description'],
            'url' => $this->urlNormalizer->normalize((string) $attributes['url']),
            'repository_url' => $this->normalizeOptionalUrl($attributes['repository_url'] ?? null),
            'laravel_cloud_url' => $this->normalizeOptionalUrl($attributes['laravel_cloud_url'] ?? null),
            'logo_url' => $this->normalizeOptionalUrl($attributes['logo_url'] ?? null),
            'screenshot_url' => $this->normalizeOptionalUrl($attributes['screenshot_url'] ?? null),
            'tags' => $this->parseTags($attributes['tags'] ?? null),
            'is_open_source' => (bool) ($attributes['is_open_source'] ?? false),
            'published_at' => now(),
        ]);
    }

    private function normalizeOptionalUrl(mixed $url): ?string
    {
        return is_string($url) && filled($url) ? $this->urlNormalizer->normalize($url) : null;
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
