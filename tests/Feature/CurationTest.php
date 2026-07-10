<?php

namespace Tests\Feature;

use App\Actions\PublishCuratedPostAction;
use App\Data\CuratedItem;
use App\Models\Post;
use App\PostKind;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_curated_item_is_published_with_provenance(): void
    {
        $item = new CuratedItem(
            title: 'Laravel ships a useful release',
            url: 'https://laravel.com/blog/release?utm_source=feed',
            summary: 'A concise original summary.',
            whyItMatters: 'It improves everyday Laravel work.',
            kind: PostKind::Article,
            sourceName: 'Laravel',
            sourceAuthor: 'Laravel Team',
            tags: ['Laravel', 'Release'],
            confidence: 94,
            include: true,
        );

        $post = app(PublishCuratedPostAction::class)->execute($item);

        $this->assertInstanceOf(Post::class, $post);
        $this->assertTrue($post->is_ai_curated);
        $this->assertSame('https://laravel.com/blog/release', $post->url);
        $this->assertSame('Laravel', $post->source_name);
        $this->assertSame(94, $post->ai_confidence);
    }

    public function test_low_confidence_excluded_and_duplicate_items_are_not_published(): void
    {
        $lowConfidence = new CuratedItem(
            title: 'Weak item',
            url: 'https://laravel.com/weak',
            summary: 'A summary.',
            whyItMatters: 'Maybe useful.',
            kind: PostKind::Article,
            sourceName: 'Laravel',
            sourceAuthor: null,
            tags: [],
            confidence: 50,
            include: true,
        );
        $action = app(PublishCuratedPostAction::class);

        $this->assertNull($action->execute($lowConfidence));

        $strong = new CuratedItem(
            title: 'Strong item',
            url: 'https://laravel.com/strong',
            summary: 'A summary.',
            whyItMatters: 'Clearly useful.',
            kind: PostKind::Article,
            sourceName: 'Laravel',
            sourceAuthor: null,
            tags: [],
            confidence: 95,
            include: true,
        );

        $this->assertInstanceOf(Post::class, $action->execute($strong));
        $this->assertNull($action->execute($strong));
        $this->assertDatabaseCount('posts', 1);
    }

    public function test_malformed_agent_output_is_rejected_before_publishing(): void
    {
        $this->assertNull(CuratedItem::fromArray([
            'title' => 'Missing evidence',
            'url' => 'https://example.com',
            'kind' => 'invented-kind',
        ]));
    }
}
