<?php

namespace App\Services;

use Illuminate\Http\Client\Factory;
use Illuminate\Support\Str;
use RuntimeException;

class TavilyClient
{
    public function __construct(private readonly Factory $http) {}

    /**
     * @param  array<int, string>  $domains
     * @return array<int, array{title: string, url: string, content: string, score: float}>
     */
    public function search(string $query, array $domains): array
    {
        $key = config('services.tavily.key');

        if (! is_string($key) || $key === '') {
            throw new RuntimeException('TAVILY_API_KEY is not configured.');
        }

        $results = $this->http
            ->baseUrl('https://api.tavily.com')
            ->withToken($key)
            ->acceptJson()
            ->timeout(30)
            ->retry(2, 250, throw: false)
            ->post('/search', [
                'query' => $query,
                'topic' => 'general',
                'search_depth' => 'basic',
                'time_range' => 'week',
                'max_results' => 8,
                'include_domains' => array_values($domains),
                'include_answer' => false,
                'include_raw_content' => false,
                'include_images' => false,
            ])
            ->throw()
            ->json('results', []);

        if (! is_array($results)) {
            return [];
        }

        return collect($results)
            ->filter(fn (mixed $result): bool => is_array($result)
                && is_string($result['title'] ?? null)
                && is_string($result['url'] ?? null)
                && is_string($result['content'] ?? null)
                && $this->isAllowedDomain($result['url'], $domains))
            ->map(fn (array $result): array => [
                'title' => $result['title'],
                'url' => $result['url'],
                'content' => Str::limit($result['content'], 1200),
                'score' => (float) ($result['score'] ?? 0),
            ])
            ->filter(fn (array $result): bool => $result['score'] >= 0.5)
            ->values()
            ->all();
    }

    /** @param array<int, string> $domains */
    private function isAllowedDomain(string $url, array $domains): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (! is_string($host)) {
            return false;
        }

        $host = strtolower($host);

        return collect($domains)->contains(
            fn (string $domain): bool => $host === strtolower($domain)
                || str_ends_with($host, '.'.strtolower($domain)),
        );
    }
}
