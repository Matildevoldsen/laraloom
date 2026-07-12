@extends('layouts.community', ['title' => 'Admin'])

@section('content')
    <div class="mb-7 flex items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[.18em] text-[#ff7693]">Community operations</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-[-.04em]">Sourcefolk admin</h1>
            <p class="mt-2 text-sm text-zinc-500">Moderate submissions without losing attribution or context.</p>
        </div>
        <div class="flex items-center gap-2">
            <a class="rounded-full border border-zinc-300 px-3 py-1.5 text-xs font-medium hover:border-zinc-500 dark:border-white/10 dark:hover:border-white/25" href="{{ route('admin.users.index') }}">Manage members</a>
            <span class="rounded-full border border-emerald-500/20 bg-emerald-500/10 px-3 py-1.5 text-xs text-emerald-700 dark:text-emerald-300">Protected</span>
        </div>
    </div>

    <div class="mb-7 grid grid-cols-2 gap-3 sm:grid-cols-5">
        @foreach (['pending_posts' => 'Pending', 'posts' => 'Posts', 'projects' => 'Projects', 'members' => 'Members', 'content_requests' => 'Requests'] as $key => $label)
            <div class="loom-card p-4">
                <p class="text-2xl font-semibold text-zinc-950 dark:text-white">{{ $counts[$key] }}</p>
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
                            <span class="rounded-full border border-zinc-200 px-2 py-0.5 text-zinc-500 dark:border-white/10 dark:text-zinc-400">{{ $post->status->value }}</span>
                            <span>{{ $post->user?->name ?? $post->source_name ?? 'Automated discovery' }}</span>
                            <span>·</span>
                            <time>{{ $post->created_at?->diffForHumans() }}</time>
                        </div>
                        <h2 class="mt-2 font-semibold text-zinc-900 dark:text-zinc-100">{{ $post->title ?: 'Community note' }}</h2>
                        <p class="mt-1 line-clamp-2 text-sm leading-6 text-zinc-500">{{ $post->body ?: $post->summary }}</p>
                    </div>
                    <details class="relative">
                        <summary class="grid size-9 cursor-pointer list-none place-items-center rounded-full text-lg text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-950 dark:text-zinc-600 dark:hover:bg-white/5 dark:hover:text-white" aria-label="Moderation actions">•••</summary>
                        <div class="absolute right-0 z-20 mt-2 w-48 overflow-hidden rounded-xl border border-zinc-200 bg-white p-1.5 text-sm shadow-2xl dark:border-white/10 dark:bg-[#17191f]">
                            @if ($post->status !== App\PostStatus::Published)
                                <form method="POST" action="{{ route('admin.posts.status', $post) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="published" /><button class="w-full rounded-lg px-3 py-2 text-left text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-white/5">Publish</button></form>
                            @endif
                            <a class="block rounded-lg px-3 py-2 text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-white/5" href="{{ route('posts.edit', $post) }}">Edit post</a>
                            @if ($post->status !== App\PostStatus::Rejected)
                                <form method="POST" action="{{ route('admin.posts.status', $post) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="rejected" /><button class="w-full rounded-lg px-3 py-2 text-left text-zinc-700 hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-white/5">Reject</button></form>
                            @endif
                            <form method="POST" action="{{ route('posts.destroy', $post) }}" onsubmit="return confirm('Delete this post permanently?')">@csrf @method('DELETE')<button class="w-full rounded-lg px-3 py-2 text-left text-red-400 hover:bg-red-400/10">Delete permanently</button></form>
                        </div>
                    </details>
                </div>
            </article>
        @empty
            <div class="loom-empty"><span>✓</span><h2>All clear</h2><p>There is nothing to moderate.</p></div>
        @endforelse
    </div>

    <div class="mt-6">{{ $posts->links() }}</div>

    <section class="mt-10" aria-labelledby="requests-heading">
        <div class="mb-4 flex items-end justify-between gap-4">
            <div><p class="text-xs font-semibold uppercase tracking-[.18em] text-[#ff7693]">Rights and safety</p><h2 id="requests-heading" class="mt-1 text-xl font-semibold">Open requests</h2></div>
            <span class="text-xs text-zinc-500">Restricted to administrators</span>
        </div>

        <div class="space-y-3">
            @forelse ($contentRequests as $contentRequest)
                <article class="loom-card p-5">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2 text-xs text-zinc-500">
                                <span class="rounded-full bg-[#ff4d73]/10 px-2 py-0.5 font-semibold text-[#d92855] dark:text-[#ff8ba3]">{{ $contentRequest->type->label() }}</span>
                                <span>{{ $contentRequest->reference() }}</span><span>·</span><time>{{ $contentRequest->created_at?->diffForHumans() }}</time>
                            </div>
                            <p class="mt-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $contentRequest->content_url }}</p>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ $contentRequest->details }}</p>
                            <p class="mt-3 text-xs text-zinc-500">{{ $contentRequest->requester_name }} · {{ $contentRequest->requester_email }} · {{ $contentRequest->relationship }}</p>
                        </div>
                        <form method="POST" action="{{ route('admin.content-requests.status', $contentRequest) }}" class="w-full space-y-2 sm:w-72">
                            @csrf
                            @method('PATCH')
                            <select class="form-input py-2" name="status" aria-label="Request status">
                                @foreach (App\ContentRequestStatus::cases() as $status)<option value="{{ $status->value }}" @selected($contentRequest->status === $status)>{{ str($status->value)->headline() }}</option>@endforeach
                            </select>
                            <textarea class="form-input min-h-20 py-2" name="resolution_notes" maxlength="2000" placeholder="Resolution notes (required to close)"></textarea>
                            <button class="loom-button w-full" type="submit">Update request</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="loom-empty py-10"><span>✓</span><h2>No open rights or safety requests</h2></div>
            @endforelse
        </div>
    </section>
@endsection
