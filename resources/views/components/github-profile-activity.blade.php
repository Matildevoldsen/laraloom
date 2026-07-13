@props(['activity', 'user'])

<section class="loom-card overflow-hidden" aria-labelledby="github-activity-heading">
    <div class="flex flex-col gap-4 border-b border-zinc-200 px-5 py-5 sm:flex-row sm:items-end sm:justify-between sm:px-6 dark:border-white/8">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[.16em] text-[#e73562] dark:text-[#ff7693]">Public GitHub activity</p>
            <h2 id="github-activity-heading" class="mt-1 text-xl font-semibold tracking-[-.025em]">Top commits</h2>
            <p class="mt-1 text-xs text-zinc-500">Newest indexed commits first</p>
        </div>
        <div class="flex gap-6 text-sm">
            <div>
                <p class="font-semibold tabular-nums text-zinc-950 dark:text-white">{{ number_format($activity->publicRepositories) }}</p>
                <p class="text-xs text-zinc-500">Public repos</p>
            </div>
            <div>
                <p class="font-semibold tabular-nums text-zinc-950 dark:text-white">{{ $activity->commitCountIncomplete ? '≥ ' : '' }}{{ number_format($activity->indexedCommitsInOwnedRepositories) }}</p>
                <p class="text-xs text-zinc-500">Owned-repo commits</p>
            </div>
        </div>
    </div>

    <div class="divide-y divide-zinc-200 dark:divide-white/8">
        @forelse ($activity->latestCommits as $commit)
            <a class="group grid gap-2 px-5 py-4 transition hover:bg-zinc-50 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center sm:px-6 dark:hover:bg-white/[.025]" href="{{ $commit->url }}" rel="noopener noreferrer" target="_blank">
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-zinc-900 group-hover:text-[#d92855] dark:text-zinc-100 dark:group-hover:text-[#ff7693]">{{ $commit->message }}</p>
                    <p class="mt-1 truncate text-xs text-zinc-500">{{ $commit->repository }} · {{ $commit->authoredAt->diffForHumans() }}</p>
                </div>
                <span class="inline-flex w-fit items-center rounded-full bg-zinc-100 px-2.5 py-1 font-mono text-[11px] text-zinc-500 dark:bg-white/6 dark:text-zinc-400">{{ $commit->shortSha }} ↗</span>
            </a>
        @empty
            <p class="px-6 py-10 text-center text-sm text-zinc-500">No indexed public commits yet.</p>
        @endforelse
    </div>

    <div class="flex flex-col gap-2 border-t border-zinc-200 bg-zinc-50/70 px-5 py-4 text-xs text-zinc-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-white/8 dark:bg-white/[.015]">
        <p>GitHub indexes default-branch commits in public repositories owned by this account; totals can be incomplete.</p>
        <a class="font-medium text-zinc-700 hover:text-zinc-950 dark:text-zinc-300 dark:hover:text-white" href="https://github.com/{{ $user->github_username }}" rel="me noopener noreferrer" target="_blank">View GitHub profile ↗</a>
    </div>
</section>
