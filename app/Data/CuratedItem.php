<?php

namespace App\Data;

use App\PostKind;

final readonly class CuratedItem
{
    /** @param array<int, string> $tags */
    public function __construct(
        public string $title,
        public string $url,
        public string $summary,
        public string $whyItMatters,
        public PostKind $kind,
        public string $sourceName,
        public ?string $sourceAuthor,
        public array $tags,
        public int $confidence,
        public bool $include,
    ) {}

    /** @param array<array-key, mixed> $attributes */
    public static function fromArray(array $attributes): ?self
    {
        $kind = is_string($attributes['kind'] ?? null)
            ? PostKind::tryFrom($attributes['kind'])
            : null;

        if (
            ! is_string($attributes['title'] ?? null)
            || ! is_string($attributes['url'] ?? null)
            || ! is_string($attributes['summary'] ?? null)
            || ! is_string($attributes['why_it_matters'] ?? null)
            || ! is_string($attributes['source_name'] ?? null)
            || ! is_string($attributes['source_author'] ?? null)
            || ! is_array($attributes['tags'] ?? null)
            || ! is_int($attributes['confidence'] ?? null)
            || ! is_bool($attributes['include'] ?? null)
            || ! $kind instanceof PostKind
        ) {
            return null;
        }

        $tags = array_values(array_filter(
            $attributes['tags'],
            static fn (mixed $tag): bool => is_string($tag) && $tag !== '',
        ));

        return new self(
            title: $attributes['title'],
            url: $attributes['url'],
            summary: $attributes['summary'],
            whyItMatters: $attributes['why_it_matters'],
            kind: $kind,
            sourceName: $attributes['source_name'],
            sourceAuthor: $attributes['source_author'] !== '' ? $attributes['source_author'] : null,
            tags: $tags,
            confidence: $attributes['confidence'],
            include: $attributes['include'],
        );
    }
}
