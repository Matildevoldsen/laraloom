<?php

namespace App\Actions;

use App\Data\CuratedItem;
use App\Models\Post;
use App\PostStatus;
use App\Services\UrlNormalizer;
use Illuminate\Support\Str;

class PublishCuratedPostAction
{
    public function __construct(private readonly UrlNormalizer $urlNormalizer) {}

    public function execute(CuratedItem $item): ?Post
    {
        if (! $item->include || $item->confidence < config('curation.minimum_confidence', 80)) {
            return null;
        }

        $url = $this->urlNormalizer->normalize($item->url);
        $hash = hash('sha256', $url);

        if (Post::query()->where('canonical_url_hash', $hash)->where('is_ai_curated', true)->exists()) {
            return null;
        }

        return Post::create([
            'kind' => $item->kind,
            'status' => PostStatus::Published,
            'title' => $item->title,
            'slug' => Str::slug($item->title).'-'.Str::lower(Str::random(6)),
            'summary' => $item->summary,
            'why_it_matters' => $item->whyItMatters,
            'url' => $url,
            'canonical_url_hash' => $hash,
            'source_name' => $item->sourceName,
            'source_author' => $item->sourceAuthor,
            'tags' => array_values(array_unique(array_slice($item->tags, 0, 8))),
            'is_ai_curated' => true,
            'ai_confidence' => $item->confidence,
            'published_at' => now(),
        ]);
    }
}
