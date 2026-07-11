@extends('layouts.community', ['title' => $post->title ?: 'Community post'])

@section('content')
    <x-post-card :$post :compact="false" />

    @if ($post->why_it_matters)
        <div class="mt-4 rounded-2xl border border-[#ff4d73]/15 bg-[#ff4d73]/5 px-5 py-4 text-sm leading-6 text-zinc-400">
            <span class="font-semibold text-zinc-200">Why it matters:</span> {{ $post->why_it_matters }}
        </div>
    @endif

    <section id="conversation" class="mt-8 scroll-mt-24">
        <div class="mb-4 flex items-center justify-between">
            <div><p class="text-xs font-semibold uppercase tracking-[.18em] text-[#ff7693]">Conversation</p><h2 class="mt-1 text-xl font-semibold">{{ $post->comments_count }} replies</h2></div>
        </div>

        @auth
            <form method="POST" action="{{ route('posts.comments.store', $post) }}" class="loom-card mb-5 p-4">
                @csrf
                <label class="sr-only" for="reply">Reply</label>
                <textarea id="reply" name="body" maxlength="1000" required class="form-input min-h-24 resize-y" placeholder="Add something useful to the conversation…">{{ old('body') }}</textarea>
                @error('body')<p class="form-error">{{ $message }}</p>@enderror
                <div class="mt-3 flex justify-end"><button class="loom-button" type="submit">Reply</button></div>
            </form>
        @else
            <a href="{{ route('login') }}" class="mb-5 block rounded-2xl border border-white/8 px-5 py-4 text-sm text-zinc-500 hover:text-white">Sign in to join the conversation →</a>
        @endauth

        <div class="space-y-3">
            @forelse ($comments as $comment)
                <article class="loom-card p-5">
                    <div class="flex items-start gap-3">
                        <span class="loom-avatar shrink-0">{{ str($comment->user->name)->substr(0, 1)->upper() }}</span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 text-xs"><span class="font-semibold text-zinc-300">{{ $comment->user->name }}</span><span class="text-zinc-600">{{ $comment->created_at?->diffForHumans() }}</span></div>
                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-zinc-400">{{ $comment->body }}</p>
                            <div class="mt-3 flex items-center gap-3 text-xs text-zinc-600">
                                @auth
                                    <details><summary class="cursor-pointer hover:text-zinc-300">Reply</summary><form method="POST" action="{{ route('posts.comments.store', $post) }}" class="mt-3 flex gap-2">@csrf<input type="hidden" name="parent_id" value="{{ $comment->id }}" /><input class="form-input" name="body" maxlength="1000" required placeholder="Reply to {{ $comment->user->name }}" /><button class="loom-button">Send</button></form></details>
                                @endauth
                                @can('delete', $comment)<form method="POST" action="{{ route('comments.destroy', $comment) }}">@csrf @method('DELETE')<button class="text-red-400">Delete</button></form>@endcan
                            </div>

                            @foreach ($comment->replies as $reply)
                                <div class="mt-4 border-l border-white/10 pl-4">
                                    <div class="flex items-center gap-2 text-xs"><span class="font-semibold text-zinc-300">{{ $reply->user->name }}</span><span class="text-zinc-600">{{ $reply->created_at?->diffForHumans() }}</span></div>
                                    <p class="mt-1 text-sm leading-6 text-zinc-400">{{ $reply->body }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </article>
            @empty
                <div class="loom-empty"><span>◯</span><h2>Start the thread</h2><p>The best replies add context, experience or a useful question.</p></div>
            @endforelse
        </div>
    </section>
@endsection
