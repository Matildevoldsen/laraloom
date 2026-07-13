<?php

namespace App\Data;

use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Throwable;

final readonly class GitHubCommit
{
    public function __construct(
        public string $message,
        public string $repository,
        public CarbonImmutable $authoredAt,
        public string $shortSha,
        public string $url,
    ) {}

    /** @param array<array-key, mixed> $attributes */
    public static function fromApi(array $attributes): ?self
    {
        $message = Arr::get($attributes, 'commit.message');
        $repository = Arr::get($attributes, 'repository.full_name');
        $authoredAt = Arr::get($attributes, 'commit.author.date');
        $sha = $attributes['sha'] ?? null;
        $url = $attributes['html_url'] ?? null;

        if (
            ! is_string($message)
            || ! is_string($repository)
            || ! is_string($authoredAt)
            || ! is_string($sha)
            || ! is_string($url)
        ) {
            return null;
        }

        return self::fromValues(
            message: self::firstLine($message),
            repository: $repository,
            authoredAt: $authoredAt,
            sha: $sha,
            url: $url,
        );
    }

    /** @param array<array-key, mixed> $attributes */
    public static function fromArray(array $attributes): ?self
    {
        $message = $attributes['message'] ?? null;
        $repository = $attributes['repository'] ?? null;
        $authoredAt = $attributes['authored_at'] ?? null;
        $shortSha = $attributes['short_sha'] ?? null;
        $url = $attributes['url'] ?? null;

        if (
            ! is_string($message)
            || ! is_string($repository)
            || ! is_string($authoredAt)
            || ! is_string($shortSha)
            || ! is_string($url)
        ) {
            return null;
        }

        return self::fromValues(
            message: self::firstLine($message),
            repository: $repository,
            authoredAt: $authoredAt,
            sha: $shortSha,
            url: $url,
        );
    }

    /** @return array{message: string, repository: string, authored_at: string, short_sha: string, url: string} */
    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'repository' => $this->repository,
            'authored_at' => $this->authoredAt->toIso8601String(),
            'short_sha' => $this->shortSha,
            'url' => $this->url,
        ];
    }

    private static function fromValues(
        string $message,
        string $repository,
        string $authoredAt,
        string $sha,
        string $url,
    ): ?self {
        if (
            $message === ''
            || preg_match('/\A[^\/\s]+\/[^\/\s]+\z/u', $repository) !== 1
            || preg_match('/\A[a-f0-9]{7,40}\z/i', $sha) !== 1
            || ! self::isGitHubUrl($url)
        ) {
            return null;
        }

        try {
            $authoredDate = CarbonImmutable::parse($authoredAt);
        } catch (Throwable) {
            return null;
        }

        return new self(
            message: $message,
            repository: $repository,
            authoredAt: $authoredDate,
            shortSha: Str::substr($sha, 0, 7),
            url: $url,
        );
    }

    private static function firstLine(string $message): string
    {
        return Str::of($message)
            ->replace("\r\n", "\n")
            ->before("\n")
            ->trim()
            ->toString();
    }

    private static function isGitHubUrl(string $url): bool
    {
        $parts = parse_url($url);

        return is_array($parts)
            && ($parts['scheme'] ?? null) === 'https'
            && ($parts['host'] ?? null) === 'github.com'
            && is_string($parts['path'] ?? null)
            && $parts['path'] !== ''
            && ! isset($parts['user'])
            && ! isset($parts['pass']);
    }
}
