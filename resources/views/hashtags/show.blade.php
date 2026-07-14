@extends('layouts.community', [
    'title' => '#'.$hashtag->name,
    'description' => 'Published Sourcefolk posts tagged #'.$hashtag->name.'.',
    'wideMain' => true,
])

@section('content')
    <section class="mb-6 flex items-start gap-4 border-b border-zinc-200 pb-6 dark:border-white/8">
        <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-[#ff4d73]/10 text-xl font-semibold text-[#d92855] dark:text-[#ff8ba3]">#</span>
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[.18em] text-[#d92855] dark:text-[#ff8ba3]">Hashtag</p>
            <h1 class="mt-1 text-3xl font-semibold tracking-[-.04em] sm:text-4xl">#{{ $hashtag->name }}</h1>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                {{ trans_choice(':count published post|:count published posts', $hashtag->published_posts_count, ['count' => $hashtag->published_posts_count]) }}
            </p>
        </div>
    </section>

    <div class="space-y-4" data-realtime-feed>
        @forelse ($posts as $post)
            <x-post-card :$post />
        @empty
            <div class="loom-empty">
                <span>#</span>
                <h2>No published posts</h2>
                <p>There are no visible posts using this hashtag.</p>
            </div>
        @endforelse
    </div>

    @if ($posts->nextPageUrl())
        <div class="mt-6 flex justify-center" data-infinite-feed data-next-url="{{ $posts->nextPageUrl() }}">
            <button type="button" class="rounded-full border border-zinc-200 px-4 py-2 text-xs text-zinc-500 dark:border-white/10">Loading more…</button>
        </div>
    @endif
@endsection
