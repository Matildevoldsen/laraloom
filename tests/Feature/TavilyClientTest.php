<?php

namespace Tests\Feature;

use App\Services\TavilyClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TavilyClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_is_metadata_only_allowlisted_and_score_filtered(): void
    {
        config()->set('services.tavily.key', 'test-key');
        Http::fake([
            'api.tavily.com/search' => Http::response(['results' => [
                ['title' => 'Official', 'url' => 'https://laravel.com/blog/release', 'content' => 'Short search snippet.', 'score' => 0.92],
                ['title' => 'Unapproved copy', 'url' => 'https://scraper.example/laravel', 'content' => 'Copied.', 'score' => 0.99],
                ['title' => 'Low relevance', 'url' => 'https://laravel.com/blog/old', 'content' => 'Weak.', 'score' => 0.2],
            ]]),
        ]);

        $results = app(TavilyClient::class)->search('recent Laravel releases', ['laravel.com']);

        $this->assertCount(1, $results);
        $this->assertSame('Official', $results[0]['title']);
        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://api.tavily.com/search'
                && $request['include_domains'] === ['laravel.com']
                && $request['include_raw_content'] === false
                && $request['include_answer'] === false
                && $request['max_results'] === 8;
        });
    }

    public function test_subdomains_of_an_approved_source_are_allowed(): void
    {
        config()->set('services.tavily.key', 'test-key');
        Http::fake(['api.tavily.com/search' => Http::response(['results' => [[
            'title' => 'Documentation',
            'url' => 'https://blog.laravel.com/item',
            'content' => 'A snippet.',
            'score' => 0.8,
        ]]])]);

        $this->assertCount(1, app(TavilyClient::class)->search('Laravel', ['laravel.com']));
    }
}
