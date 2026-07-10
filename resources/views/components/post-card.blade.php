@props(['post'])

<article class="loom-card group overflow-hidden">
    <div class="flex gap-3 p-5 sm:p-6">
        <a href="{{ $post->user ? route('profiles.show', $post->user) : '#' }}" class="loom-avatar mt-0.5 shrink-0">
            {{ str($post->user?->name ?? $post->source_name ?? 'L')->substr(0, 1)->upper() }}
        </a>
        <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-zinc-500">
                <span class="font-medium text-zinc-300">{{ $post->user?->name ?? $post->source_name ?? 'Laraloom' }}</span>
                @if ($post->source_author)<span>by {{ $post->source_author }}</span>@endif
                <span>·</span>
                <time datetime="{{ $post->published_at?->toIso8601String() }}">{{ $post->published_at?->diffForHumans() }}</time>
                @if ($post->is_ai_curated)
                    <span class="ai-badge"><i></i> AI curated</span>
                @endif
            </div>

            @if ($post->title)
                <h2 class="mt-3 text-xl font-semibold leading-snug tracking-[-0.025em] text-white sm:text-[22px]">
                    @if ($post->url)<a href="{{ $post->url }}" rel="noopener noreferrer" target="_blank" class="transition group-hover:text-[#ff7693]">{{ $post->title }}</a>@else{{ $post->title }}@endif
                </h2>
            @endif
            @if ($post->body)<p class="mt-2 whitespace-pre-line text-[15px] leading-6 text-zinc-300">{{ $post->body }}</p>@endif
            @if ($post->summary)<p class="mt-2 text-[15px] leading-6 text-zinc-400">{{ $post->summary }}</p>@endif
            @if ($post->why_it_matters)
                <div class="mt-4 border-l-2 border-[#ff4d73] pl-3 text-sm leading-5 text-zinc-400"><span class="font-semibold text-zinc-200">Why it matters:</span> {{ $post->why_it_matters }}</div>
            @endif

            @if ($post->tags)
                <div class="mt-4 flex flex-wrap gap-2">@foreach ($post->tags as $tag)<span class="loom-tag">{{ $tag }}</span>@endforeach</div>
            @endif

            <div class="mt-5 flex items-center gap-1 text-xs text-zinc-500">
                @auth
                    <form method="POST" action="{{ route('posts.reaction', $post) }}">@csrf<button class="icon-button" title="Appreciate">♡ <span>{{ $post->reacting_users_count ?? 0 }}</span></button></form>
                    <form method="POST" action="{{ route('posts.bookmark', $post) }}">@csrf<button class="icon-button" title="Bookmark">⌑ <span>{{ $post->bookmarking_users_count ?? 0 }}</span></button></form>
                @else
                    <span class="icon-button">♡ <span>{{ $post->reacting_users_count ?? 0 }}</span></span>
                @endauth
                @if ($post->url)<a href="{{ $post->url }}" target="_blank" rel="noopener noreferrer" class="icon-button ml-auto">Visit source ↗</a>@endif
            </div>
        </div>
    </div>
</article>
