@extends('layouts.community', ['title' => 'Bookmarks', 'wideMain' => true])

@section('content')
    <section class="mb-6 flex items-start gap-4 border-b border-zinc-200 pb-6 dark:border-white/8">
        <div class="grid size-12 shrink-0 place-items-center rounded-2xl bg-[#ff4d73]/10 text-[#d92855] dark:text-[#ff8ba3]">
            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M6 3.75h12v17l-6-3.75-6 3.75v-17Z" />
            </svg>
        </div>
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[.18em] text-[#d92855] dark:text-[#ff8ba3]">Your private collection</p>
            <h1 class="mt-1 text-3xl font-semibold tracking-[-.04em] sm:text-4xl">Bookmarks</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-600 dark:text-zinc-400">Posts you save are kept here for you to revisit.</p>
        </div>
    </section>

    <div class="space-y-4" data-realtime-feed>
        @forelse ($posts as $post)
            <x-post-card :$post />
        @empty
            <div class="loom-empty">
                <svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M6 3.75h12v17l-6-3.75-6 3.75v-17Z" />
                </svg>
                <h2>No bookmarks yet</h2>
                <p>Use the bookmark button on a post to save it here.</p>
                <a href="{{ route('home') }}" class="loom-button mt-4">Explore posts</a>
            </div>
        @endforelse
    </div>

    @if ($posts->nextPageUrl())
        <div class="mt-6 flex justify-center" data-infinite-feed data-next-url="{{ $posts->nextPageUrl() }}">
            <button type="button" class="rounded-full border border-zinc-200 px-4 py-2 text-xs text-zinc-500 dark:border-white/10">Loading more…</button>
        </div>
    @endif
@endsection
