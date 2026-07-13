<?php

namespace App\Services;

use App\Data\GitHubProfileActivity;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Throwable;

final class GitHubProfileClient
{
    private const int CacheSuccessSeconds = 21600;

    private const int CacheFailureSeconds = 300;

    private const string CacheVersion = 'v2';

    public function __construct(private readonly Factory $http) {}

    public function activity(string $login): ?GitHubProfileActivity
    {
        $login = Str::of($login)->trim()->lower()->toString();

        if (preg_match('/\A(?!-)[a-z0-9-]{1,39}(?<!-)\z/', $login) !== 1) {
            return null;
        }

        $cacheKey = $this->cacheKey($login);
        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            $activity = GitHubProfileActivity::fromArray($cached);

            if ($activity instanceof GitHubProfileActivity) {
                return $activity;
            }

            Cache::forget($cacheKey);
        }

        if (Cache::has($this->failureCacheKey($login))) {
            return null;
        }

        $activity = $this->fetch($login);

        if (! $activity instanceof GitHubProfileActivity) {
            Cache::put($this->failureCacheKey($login), true, self::CacheFailureSeconds);

            return null;
        }

        Cache::put($cacheKey, $activity->toArray(), self::CacheSuccessSeconds);
        Cache::forget($this->failureCacheKey($login));

        return $activity;
    }

    private function fetch(string $login): ?GitHubProfileActivity
    {
        try {
            $responses = $this->http->pool(fn (Pool $pool): array => [
                $this->request($pool->as('profile'))->get('/users/'.rawurlencode($login)),
                $this->request($pool->as('commits'))->get('/search/commits', [
                    'q' => "author:{$login} user:{$login} is:public",
                    'sort' => 'author-date',
                    'order' => 'desc',
                    'per_page' => 5,
                ]),
            ]);
        } catch (Throwable) {
            return null;
        }

        $profileResponse = $responses['profile'] ?? null;
        $commitsResponse = $responses['commits'] ?? null;

        if (
            ! $profileResponse instanceof Response
            || ! $commitsResponse instanceof Response
            || ! $profileResponse->successful()
            || ! $commitsResponse->successful()
        ) {
            return null;
        }

        $profile = $profileResponse->json();
        $commits = $commitsResponse->json();

        if (! is_array($profile) || ! is_array($commits)) {
            return null;
        }

        return GitHubProfileActivity::fromApi($profile, $commits);
    }

    private function request(PendingRequest $request): PendingRequest
    {
        $request = $request
            ->baseUrl('https://api.github.com')
            ->withHeaders([
                'Accept' => 'application/vnd.github+json',
                'User-Agent' => 'Sourcefolk/1.0 (+https://sourcefolk.com)',
                'X-GitHub-Api-Version' => '2022-11-28',
            ])
            ->connectTimeout(2)
            ->timeout(4);

        $clientId = config('services.github.client_id');
        $clientSecret = config('services.github.client_secret');

        if (
            is_string($clientId)
            && trim($clientId) !== ''
            && is_string($clientSecret)
            && trim($clientSecret) !== ''
        ) {
            return $request->withBasicAuth($clientId, $clientSecret);
        }

        return $request;
    }

    private function cacheKey(string $login): string
    {
        return 'github-profile-activity:'.self::CacheVersion.':'.hash('sha256', $login);
    }

    private function failureCacheKey(string $login): string
    {
        return $this->cacheKey($login).':unavailable';
    }
}
