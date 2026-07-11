@extends('layouts.community', ['title' => 'Admin'])

@section('content')
    <div class="mb-7 flex items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[.18em] text-[#ff7693]">Community operations</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-[-.04em]">Laraloom admin</h1>
            <p class="mt-2 text-sm text-zinc-500">Moderate submissions without losing attribution or context.</p>
        </div>
        <span class="rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-1.5 text-xs text-emerald-300">Protected</span>
    </div>

    <div class="mb-7 grid grid-cols-2 gap-3 sm:grid-cols-5">
        @foreach (['pending_posts' => 'Pending', 'posts' => 'Posts', 'projects' => 'Projects', 'members' => 'Members', 'content_requests' => 'Requests'] as $key => $label)
            <div class="loom-card p-4">
                <p class="text-2xl font-semibold text-white">{{ $counts[$key] }}</p>
                <p class="mt-1 text-xs text-zinc-600">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    <div class="space-y-3">
        @forelse ($posts as $post)
            <article class="loom-card p-5">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2 text-xs text-zinc-600">
                            <span class="rounded-full border border-white/10 px-2 py-0.5 text-zinc-400">{{ $post->status->value }}</span>
                            <span>{{ $post->user?->name ?? $post->source_name ?? 'Automated discovery' }}</span>
                            <span>·</span>
                            <time>{{ $post->created_at?->diffForHumans() }}</time>
                        </div>
                        <h2 class="mt-2 font-semibold text-zinc-100">{{ $post->title ?: 'Community note' }}</h2>
                        <p class="mt-1 line-clamp-2 text-sm leading-6 text-zinc-500">{{ $post->body ?: $post->summary }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @foreach ([[App\PostStatus::Published, 'Publish'], [App\PostStatus::Rejected, 'Reject']] as [$status, $label])
                            <form method="POST" action="{{ route('admin.posts.status', $post) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $status->value }}" />
                                <button class="rounded-full border border-white/10 px-3 py-1.5 text-xs text-zinc-300 transition hover:border-[#ff4d73]/50 hover:text-white">{{ $label }}</button>
                            </form>
                        @endforeach
                        <form method="POST" action="{{ route('posts.destroy', $post) }}" onsubmit="return confirm('Delete this post permanently?')">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-full border border-red-400/20 px-3 py-1.5 text-xs text-red-300 hover:bg-red-400/10">Delete</button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="loom-empty"><span>✓</span><h2>All clear</h2><p>There is nothing to moderate.</p></div>
        @endforelse
    </div>

    <div class="mt-6">{{ $posts->links() }}</div>
@endsection
