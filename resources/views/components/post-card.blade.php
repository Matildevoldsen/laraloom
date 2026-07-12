@props(['post', 'compact' => true])

<article class="loom-card group overflow-visible" data-realtime-post data-post-id="{{ $post->id }}">
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

        <footer class="mt-4 flex items-center gap-1 border-t border-zinc-200 pt-3 text-xs text-zinc-500 dark:border-white/6">
            <a href="{{ route('posts.show', $post) }}#conversation" class="icon-button" title="Replies">◯ <span>{{ $post->comments_count ?? 0 }}</span></a>
            @auth
                <form method="POST" action="{{ route('posts.repost', $post) }}">@csrf<button class="icon-button" title="Repost">⇄ <span>{{ $post->reposting_users_count ?? 0 }}</span></button></form>
                <form method="POST" action="{{ route('posts.reaction', $post) }}">@csrf<button class="icon-button" title="Appreciate">♡ <span>{{ $post->reacting_users_count ?? 0 }}</span></button></form>
                <form method="POST" action="{{ route('posts.bookmark', $post) }}">@csrf<button class="icon-button" title="Bookmark">⌑</button></form>
            @else
                <span class="icon-button">⇄ <span>{{ $post->reposting_users_count ?? 0 }}</span></span>
                <span class="icon-button">♡ <span>{{ $post->reacting_users_count ?? 0 }}</span></span>
            @endauth
            @if ($post->url)<a href="{{ $post->url }}" target="_blank" rel="noopener noreferrer" class="icon-button ml-auto">Source ↗</a>@endif
        </footer>
    </div>
</article>
