<?php

namespace App\Data;

final readonly class GitHubProfileActivity
{
    /** @param list<GitHubCommit> $latestCommits */
    public function __construct(
        public int $publicRepositories,
        public int $indexedCommitsInOwnedRepositories,
        public bool $commitCountIncomplete,
        public array $latestCommits,
    ) {}

    /**
     * @param  array<array-key, mixed>  $profile
     * @param  array<array-key, mixed>  $commits
     */
    public static function fromApi(array $profile, array $commits): ?self
    {
        $publicRepositories = $profile['public_repos'] ?? null;
        $indexedCommitsInOwnedRepositories = $commits['total_count'] ?? null;
        $commitCountIncomplete = $commits['incomplete_results'] ?? null;
        $items = $commits['items'] ?? null;

        if (
            ! is_int($publicRepositories)
            || $publicRepositories < 0
            || ! is_int($indexedCommitsInOwnedRepositories)
            || $indexedCommitsInOwnedRepositories < 0
            || ! is_bool($commitCountIncomplete)
            || ! is_array($items)
        ) {
            return null;
        }

        $latestCommits = self::commitsFromApi(array_slice($items, 0, 5));

        if ($latestCommits === null) {
            return null;
        }

        return new self(
            publicRepositories: $publicRepositories,
            indexedCommitsInOwnedRepositories: $indexedCommitsInOwnedRepositories,
            commitCountIncomplete: $commitCountIncomplete,
            latestCommits: $latestCommits,
        );
    }

    /** @param array<array-key, mixed> $attributes */
    public static function fromArray(array $attributes): ?self
    {
        $publicRepositories = $attributes['public_repositories'] ?? null;
        $indexedCommitsInOwnedRepositories = $attributes['indexed_commits_in_owned_repositories'] ?? null;
        $commitCountIncomplete = $attributes['commit_count_incomplete'] ?? null;
        $commits = $attributes['latest_commits'] ?? null;

        if (
            ! is_int($publicRepositories)
            || $publicRepositories < 0
            || ! is_int($indexedCommitsInOwnedRepositories)
            || $indexedCommitsInOwnedRepositories < 0
            || ! is_bool($commitCountIncomplete)
            || ! is_array($commits)
        ) {
            return null;
        }

        $latestCommits = [];

        foreach ($commits as $commit) {
            if (! is_array($commit)) {
                return null;
            }

            $githubCommit = GitHubCommit::fromArray($commit);

            if (! $githubCommit instanceof GitHubCommit) {
                return null;
            }

            $latestCommits[] = $githubCommit;
        }

        return new self(
            publicRepositories: $publicRepositories,
            indexedCommitsInOwnedRepositories: $indexedCommitsInOwnedRepositories,
            commitCountIncomplete: $commitCountIncomplete,
            latestCommits: $latestCommits,
        );
    }

    /**
     * @return array{
     *     public_repositories: int,
     *     indexed_commits_in_owned_repositories: int,
     *     commit_count_incomplete: bool,
     *     latest_commits: list<array{message: string, repository: string, authored_at: string, short_sha: string, url: string}>
     * }
     */
    public function toArray(): array
    {
        return [
            'public_repositories' => $this->publicRepositories,
            'indexed_commits_in_owned_repositories' => $this->indexedCommitsInOwnedRepositories,
            'commit_count_incomplete' => $this->commitCountIncomplete,
            'latest_commits' => array_map(
                static fn (GitHubCommit $commit): array => $commit->toArray(),
                $this->latestCommits,
            ),
        ];
    }

    /**
     * @param  array<array-key, mixed>  $items
     * @return list<GitHubCommit>|null
     */
    private static function commitsFromApi(array $items): ?array
    {
        $commits = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                return null;
            }

            $commit = GitHubCommit::fromApi($item);

            if (! $commit instanceof GitHubCommit) {
                return null;
            }

            $commits[] = $commit;
        }

        return $commits;
    }
}
