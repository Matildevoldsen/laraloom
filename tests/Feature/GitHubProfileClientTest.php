<?php

use App\Data\GitHubCommit;
use App\Data\GitHubProfileActivity;
use App\Services\GitHubProfileClient;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Cache::flush();
    config()->set('services.github.client_id');
    config()->set('services.github.client_secret');
    Http::preventStrayRequests();
});

$commitPayload = static fn (array $overrides = []): array => array_replace_recursive([
    'sha' => '0123456789abcdef0123456789abcdef01234567',
    'html_url' => 'https://github.com/laravel/framework/commit/0123456789abcdef0123456789abcdef01234567',
    'commit' => [
        'message' => "Improve the HTTP client\n\nMore detail belongs below the title.",
        'author' => ['date' => '2026-07-12T14:30:00Z'],
    ],
    'repository' => ['full_name' => 'laravel/framework'],
], $overrides);

$fakeSuccessfulGitHub = static function (array $commits, array $profile = []): void {
    Http::fake([
        'api.github.com/users/*' => Http::response(array_replace([
            'public_repos' => 27,
        ], $profile)),
        'api.github.com/search/commits*' => Http::response([
            'total_count' => 1842,
            'incomplete_results' => true,
            'items' => $commits,
        ]),
    ]);
};

test('it returns typed public GitHub profile activity', function () use ($commitPayload, $fakeSuccessfulGitHub) {
    $fakeSuccessfulGitHub([$commitPayload()]);

    $activity = app(GitHubProfileClient::class)->activity('TaylorOtwell');

    expect($activity)->toBeInstanceOf(GitHubProfileActivity::class);
    $activity ?? throw new RuntimeException('Expected GitHub activity to be available.');
    $commit = $activity->latestCommits[0] ?? throw new RuntimeException('Expected a latest commit.');

    expect($activity->publicRepositories)->toBe(27)
        ->and($activity->indexedCommitsInOwnedRepositories)->toBe(1842)
        ->and($activity->commitCountIncomplete)->toBeTrue()
        ->and($activity->latestCommits)->toHaveCount(1)
        ->and($commit)->toBeInstanceOf(GitHubCommit::class)
        ->and($commit->message)->toBe('Improve the HTTP client')
        ->and($commit->repository)->toBe('laravel/framework')
        ->and($commit->shortSha)->toBe('0123456')
        ->and($commit->url)->toBe('https://github.com/laravel/framework/commit/0123456789abcdef0123456789abcdef01234567')
        ->and($commit->authoredAt)->toBeInstanceOf(CarbonImmutable::class)
        ->and($commit->authoredAt->toIso8601String())
        ->toBe('2026-07-12T14:30:00+00:00');

    Http::assertSentCount(2);
    Http::assertSent(function (Request $request): bool {
        return $request->hasHeader('Accept', 'application/vnd.github+json')
            && $request->hasHeader('User-Agent', 'Sourcefolk/1.0 (+https://sourcefolk.com)')
            && $request->hasHeader('X-GitHub-Api-Version', '2022-11-28')
            && ! $request->hasHeader('Authorization');
    });

    Http::assertSent(function (Request $request): bool {
        if (! str_starts_with($request->url(), 'https://api.github.com/search/commits?')) {
            return false;
        }

        parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

        return $query === [
            'q' => 'author:taylorotwell user:taylorotwell is:public',
            'sort' => 'author-date',
            'order' => 'desc',
            'per_page' => '5',
        ];
    });
});

test('it caches normalized activity using a case insensitive versioned key', function () use ($commitPayload, $fakeSuccessfulGitHub) {
    $fakeSuccessfulGitHub([$commitPayload()]);
    $client = app(GitHubProfileClient::class);

    expect($client->activity('TaylorOtwell'))->toBeInstanceOf(GitHubProfileActivity::class)
        ->and($client->activity('taylorotwell'))->toBeInstanceOf(GitHubProfileActivity::class);

    Http::assertSentCount(2);

    expect(Cache::has('github-profile-activity:v2:'.hash('sha256', 'taylorotwell')))->toBeTrue();
});

test('it applies basic authentication only when both OAuth credentials are configured', function () use ($commitPayload, $fakeSuccessfulGitHub) {
    config()->set('services.github.client_id', 'client-id');
    config()->set('services.github.client_secret', 'client-secret');
    $fakeSuccessfulGitHub([$commitPayload()]);

    expect(app(GitHubProfileClient::class)->activity('octocat'))->toBeInstanceOf(GitHubProfileActivity::class);

    Http::assertSent(fn (Request $request): bool => $request->hasHeader(
        'Authorization',
        'Basic '.base64_encode('client-id:client-secret'),
    ));
});

test('it does not send partial basic authentication credentials', function (?string $clientId, ?string $clientSecret) use ($commitPayload, $fakeSuccessfulGitHub) {
    config()->set('services.github.client_id', $clientId);
    config()->set('services.github.client_secret', $clientSecret);
    $fakeSuccessfulGitHub([$commitPayload()]);

    expect(app(GitHubProfileClient::class)->activity('octocat'))->toBeInstanceOf(GitHubProfileActivity::class);

    Http::assertSent(fn (Request $request): bool => ! $request->hasHeader('Authorization'));
})->with([
    'client id only' => ['client-id', null],
    'client secret only' => [null, 'client-secret'],
    'blank secret' => ['client-id', '  '],
]);

test('it distinguishes valid zero activity from unavailable activity', function () {
    Http::fake([
        'api.github.com/users/*' => Http::response(['public_repos' => 0]),
        'api.github.com/search/commits*' => Http::response([
            'total_count' => 0,
            'incomplete_results' => false,
            'items' => [],
        ]),
    ]);

    $activity = app(GitHubProfileClient::class)->activity('newcomer');

    expect($activity)->toBeInstanceOf(GitHubProfileActivity::class);
    $activity ?? throw new RuntimeException('Expected GitHub activity to be available.');

    expect($activity->publicRepositories)->toBe(0)
        ->and($activity->indexedCommitsInOwnedRepositories)->toBe(0)
        ->and($activity->latestCommits)->toBe([]);
});

test('it returns unavailable for failed GitHub responses', function (string $failedEndpoint) {
    Http::fake(function (Request $request) use ($failedEndpoint) {
        if (str_contains($request->url(), $failedEndpoint)) {
            return Http::response(['message' => 'Unavailable'], 503);
        }

        if (str_contains($request->url(), '/users/')) {
            return Http::response(['public_repos' => 12]);
        }

        return Http::response([
            'total_count' => 8,
            'incomplete_results' => false,
            'items' => [],
        ]);
    });

    expect(app(GitHubProfileClient::class)->activity('octocat'))->toBeNull();
})->with([
    'profile request' => ['/users/'],
    'commit search' => ['/search/commits'],
]);

test('it retries shortly after a transient GitHub failure', function () {
    $githubIsAvailable = false;

    Http::fake(function (Request $request) use (&$githubIsAvailable) {
        if (str_contains($request->url(), '/users/')) {
            return Http::response(['public_repos' => 12]);
        }

        return $githubIsAvailable
            ? Http::response([
                'total_count' => 8,
                'incomplete_results' => false,
                'items' => [],
            ])
            : Http::response(['message' => 'Unavailable'], 503);
    });

    $client = app(GitHubProfileClient::class);

    expect($client->activity('octocat'))->toBeNull();
    $githubIsAvailable = true;
    expect($client->activity('octocat'))->toBeNull();
    Http::assertSentCount(2);

    $this->travel(6)->minutes();

    expect($client->activity('octocat'))->toBeInstanceOf(GitHubProfileActivity::class);
    Http::assertSentCount(4);
});

test('it returns unavailable for malformed profile or commit data', function (array $profile, array $commits) {
    Http::fake([
        'api.github.com/users/*' => Http::response($profile),
        'api.github.com/search/commits*' => Http::response($commits),
    ]);

    expect(app(GitHubProfileClient::class)->activity('octocat'))->toBeNull();
})->with([
    'repository count is not an integer' => [
        ['public_repos' => '12'],
        ['total_count' => 8, 'incomplete_results' => false, 'items' => []],
    ],
    'commit count is not an integer' => [
        ['public_repos' => 12],
        ['total_count' => '8', 'incomplete_results' => false, 'items' => []],
    ],
    'commit items are not a list' => [
        ['public_repos' => 12],
        ['total_count' => 8, 'incomplete_results' => false, 'items' => 'invalid'],
    ],
    'commit links must be on github.com' => [
        ['public_repos' => 12],
        [
            'total_count' => 8,
            'incomplete_results' => false,
            'items' => [[
                'sha' => '0123456789abcdef0123456789abcdef01234567',
                'html_url' => 'https://example.com/laravel/framework/commit/0123456',
                'commit' => [
                    'message' => 'Untrusted link',
                    'author' => ['date' => '2026-07-12T14:30:00Z'],
                ],
                'repository' => ['full_name' => 'laravel/framework'],
            ]],
        ],
    ],
    'commit links may not contain user information' => [
        ['public_repos' => 12],
        [
            'total_count' => 8,
            'incomplete_results' => false,
            'items' => [[
                'sha' => '0123456789abcdef0123456789abcdef01234567',
                'html_url' => 'https://attacker@github.com/laravel/framework/commit/0123456',
                'commit' => [
                    'message' => 'Untrusted link',
                    'author' => ['date' => '2026-07-12T14:30:00Z'],
                ],
                'repository' => ['full_name' => 'laravel/framework'],
            ]],
        ],
    ],
]);

test('it returns unavailable when GitHub cannot be reached', function () {
    Http::fake(['api.github.com/*' => Http::failedConnection()]);

    expect(app(GitHubProfileClient::class)->activity('octocat'))->toBeNull();
});

test('it rejects invalid GitHub logins before making a request', function (string $login) {
    Http::fake();

    expect(app(GitHubProfileClient::class)->activity($login))->toBeNull();

    Http::assertNothingSent();
})->with(['', '-octocat', 'octo_cat', str_repeat('a', 40)]);
