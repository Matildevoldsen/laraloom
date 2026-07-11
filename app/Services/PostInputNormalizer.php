<?php

namespace App\Services;

use Illuminate\Support\Str;

class PostInputNormalizer
{
    public function __construct(private readonly UrlNormalizer $urlNormalizer) {}

    /**
     * @param  array<string, mixed>  $attributes
     * @return array{kind: mixed, title: ?string, body: ?string, url: ?string, canonical_url_hash: ?string, tags: array<int, string>}
     */
    public function normalize(array $attributes): array
    {
        $title = $this->optionalString($attributes['title'] ?? null);
        $body = $this->optionalString($attributes['body'] ?? null);
        $url = $this->optionalString($attributes['url'] ?? null);
        $url = $url === null ? null : $this->urlNormalizer->normalize($url);

        return [
            'kind' => $attributes['kind'],
            'title' => $title,
            'body' => $body,
            'url' => $url,
            'canonical_url_hash' => $url === null ? null : hash('sha256', $url),
            'tags' => $this->parseTags($attributes['tags'] ?? null),
        ];
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
            ->unique(fn (string $tag): string => Str::lower($tag))
            ->take(8)
            ->values()
            ->all();
    }

    private function optionalString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
