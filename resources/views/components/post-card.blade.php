@props(['post', 'compact' => true])

@php
    $isBookmarked = (bool) ($post->getAttribute('is_bookmarked') ?? false);
    $isReacted = (bool) ($post->getAttribute('is_reacted') ?? false);
    $isReposted = (bool) ($post->getAttribute('is_reposted') ?? false);
@endphp

<article class="loom-card group overflow-visible" data-realtime-post data-post-id="{{ $post->id }}" data-refresh-url="{{ route('posts.show', $post) }}">
    <div class="p-5 sm:p-6">
        <header class="flex items-center gap-3">
            <a href="{{ $post->user ? route('profiles.show', $post->user) : '#' }}" class="loom-avatar shrink-0">
                @if ($post->user)<img class="size-full object-cover" src="{{ $post->user->avatarUrl() }}" alt="" />@else{{ str($post->source_name ?? 'L')->substr(0, 1)->upper() }}@endif
            </a>
            <div class="min-w-0 flex-1">
                @if ($post->user)
                    <a href="{{ route('profiles.show', $post->user) }}" class="flex items-center gap-1.5 truncate text-sm font-semibold text-zinc-800 hover:text-[#d92855] dark:text-zinc-200 dark:hover:text-[#ff7693]">{{ $post->user->name }} <x-verified-badge :user="$post->user" /></a>
                    <a href="{{ route('profiles.show', $post->user) }}" class="mt-0.5 block truncate text-xs text-zinc-500 hover:text-zinc-800 dark:text-zinc-600 dark:hover:text-zinc-300">{{ '@'.$post->user->username }} · {{ $post->published_at?->diffForHumans() }}</a>
                @else
                    <p class="truncate text-sm font-semibold text-zinc-800 dark:text-zinc-200">{{ $post->source_name ?? 'Sourcefolk' }}</p>
                @endif
                <p class="mt-0.5 truncate text-xs text-zinc-600">
                    {{ str($post->kind->value)->headline() }}
                    @unless ($post->user) · {{ $post->published_at?->diffForHumans() }} @endunless
                    @if ($post->is_ai_curated) · AI curated @endif
                </p>
            </div>

            @if (auth()->user()?->can('update', $post) || auth()->user()?->can('delete', $post) || auth()->user()?->can('access-admin'))
                <details class="relative">
                    <summary class="grid size-9 cursor-pointer list-none place-items-center rounded-full text-lg text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-950 dark:text-zinc-600 dark:hover:bg-white/5 dark:hover:text-white" aria-label="Post actions">•••</summary>
                    <div class="absolute right-0 z-20 mt-2 w-44 overflow-hidden rounded-xl border border-zinc-200 bg-white p-1.5 text-sm shadow-2xl dark:border-white/10 dark:bg-[#17191f]">
                        @can('update', $post)<a class="block rounded-lg px-3 py-2 text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-white/5" href="{{ route('posts.edit', $post) }}">Edit post</a>@endcan
                        @can('access-admin')<a class="block rounded-lg px-3 py-2 text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-white/5" href="{{ route('admin.dashboard') }}">Moderate</a>@endcan
                        @can('delete', $post)
                            <form method="POST" action="{{ route('posts.destroy', $post) }}" onsubmit="return confirm('Delete this post permanently?')">
                                @csrf
                                @method('DELETE')
                                <button class="w-full rounded-lg px-3 py-2 text-left text-red-400 hover:bg-red-400/10">Delete</button>
                            </form>
                        @endcan
                    </div>
                </details>
            @endif
        </header>

        <a href="{{ route('posts.show', $post) }}" class="mt-4 block">
            @if ($post->title)
                <h2 class="text-xl font-semibold leading-snug tracking-[-0.025em] text-zinc-950 transition group-hover:text-[#d92855] dark:text-white dark:group-hover:text-[#ff7693] sm:text-[22px]">{{ $post->title }}</h2>
            @endif
            @if ($post->body)
                <p @class(['mt-2 whitespace-pre-line text-[15px] leading-6 text-zinc-600 dark:text-zinc-400', 'line-clamp-4' => $compact])>{{ $post->body }}</p>
            @elseif ($post->summary)
                <p @class(['mt-2 text-[15px] leading-6 text-zinc-600 dark:text-zinc-400', 'line-clamp-4' => $compact])>{{ $post->summary }}</p>
            @endif
        </a>

        @if ($post->relationLoaded('attachments') && $post->attachments->isNotEmpty())
            <div @class(['mt-4 grid overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-100 dark:border-white/8 dark:bg-black/20', 'grid-cols-2' => $post->attachments->count() > 1])>
                @foreach ($post->attachments as $attachment)
                    @if ($attachment->media_type === 'video')
                        <video controls playsinline preload="metadata" class="max-h-[34rem] w-full bg-black object-contain" src="{{ route('post-attachments.show', $attachment) }}"></video>
                    @else
                        <img loading="lazy" class="max-h-[34rem] h-full w-full object-cover" src="{{ route('post-attachments.show', $attachment) }}" alt="Photo attached by {{ $post->user?->name ?? 'the author' }}" />
                    @endif
                @endforeach
            </div>
        @endif

        @if ($post->tags)
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach (array_slice($post->tags, 0, $compact ? 3 : 8) as $tag)<flux:badge size="sm" color="pink" inset="top bottom">{{ $tag }}</flux:badge>@endforeach
            </div>
        @endif

        <footer class="mt-4 flex items-center gap-3 border-t border-zinc-200 pt-3 text-xs text-zinc-500 dark:border-white/6" data-post-interactions>
            <div class="flex min-w-0 max-w-md flex-1 items-center justify-between">
                <a
                    href="{{ route('posts.show', $post) }}#conversation"
                    class="post-action-button post-action-reply"
                    aria-label="View conversation, {{ $post->comments_count ?? 0 }} replies"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20.25 11.5a7.75 7.75 0 0 1-8.05 7.74 9.6 9.6 0 0 1-3.18-.55L4 20l1.35-4.4A7.75 7.75 0 1 1 20.25 11.5Z" />
                    </svg>
                    <span>{{ $post->comments_count ?? 0 }}</span>
                </a>

                @auth
                    <form method="POST" action="{{ route('posts.repost', $post) }}" data-post-action data-action="repost" data-post-id="{{ $post->id }}">
                        @csrf
                        <button
                            type="submit"
                            @class(['post-action-button post-action-repost', 'is-active' => $isReposted])
                            data-post-action-button
                            data-active-label="Undo repost"
                            data-inactive-label="Repost"
                            aria-label="{{ $isReposted ? 'Undo repost' : 'Repost' }}"
                            aria-pressed="{{ $isReposted ? 'true' : 'false' }}"
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m17 3 4 4-4 4" />
                                <path d="M3 7h18M7 21l-4-4 4-4" />
                                <path d="M21 17H3" />
                            </svg>
                            <span data-post-action-count>{{ $post->reposting_users_count ?? 0 }}</span>
                        </button>
                    </form>

                    <form method="POST" action="{{ route('posts.reaction', $post) }}" data-post-action data-action="reaction" data-post-id="{{ $post->id }}">
                        @csrf
                        <button
                            type="submit"
                            @class(['post-action-button post-action-reaction', 'is-active' => $isReacted])
                            data-post-action-button
                            data-active-label="Unlike"
                            data-inactive-label="Like"
                            aria-label="{{ $isReacted ? 'Unlike' : 'Like' }}"
                            aria-pressed="{{ $isReacted ? 'true' : 'false' }}"
                        >
                            <svg data-post-action-inactive-icon @class(['hidden' => $isReacted]) viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78Z" />
                            </svg>
                            <svg data-post-action-active-icon @class(['hidden' => ! $isReacted]) viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round" aria-hidden="true">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78Z" />
                            </svg>
                            <span data-post-action-count>{{ $post->reacting_users_count ?? 0 }}</span>
                        </button>
                    </form>

                    <form method="POST" action="{{ route('posts.bookmark', $post) }}" data-post-action data-action="bookmark" data-post-id="{{ $post->id }}">
                        @csrf
                        <button
                            type="submit"
                            @class(['post-action-button post-action-bookmark', 'is-active' => $isBookmarked])
                            data-post-action-button
                            data-active-label="Remove bookmark"
                            data-inactive-label="Bookmark"
                            aria-label="{{ $isBookmarked ? 'Remove bookmark' : 'Bookmark' }}"
                            aria-pressed="{{ $isBookmarked ? 'true' : 'false' }}"
                        >
                            <svg data-post-action-inactive-icon @class(['hidden' => $isBookmarked]) viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M6 3.75h12v17l-6-3.75-6 3.75v-17Z" />
                            </svg>
                            <svg data-post-action-active-icon @class(['hidden' => ! $isBookmarked]) viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.2" stroke-linejoin="round" aria-hidden="true">
                                <path d="M6 3.75h12v17l-6-3.75-6 3.75v-17Z" />
                            </svg>
                        </button>
                    </form>
                @else
                    <span class="post-action-button" aria-label="{{ $post->reposting_users_count ?? 0 }} reposts">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="m17 3 4 4-4 4M3 7h18M7 21l-4-4 4-4M21 17H3" /></svg>
                        <span>{{ $post->reposting_users_count ?? 0 }}</span>
                    </span>
                    <span class="post-action-button" aria-label="{{ $post->reacting_users_count ?? 0 }} likes">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78Z" /></svg>
                        <span>{{ $post->reacting_users_count ?? 0 }}</span>
                    </span>
                @endauth
            </div>
            @if ($post->url)<a href="{{ $post->url }}" target="_blank" rel="noopener noreferrer" class="icon-button ml-auto">Source ↗</a>@endif
        </footer>
    </div>
</article>
