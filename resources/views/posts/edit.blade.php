@extends('layouts.community', ['title' => 'Edit post'])

@section('content')
    <div class="mb-7">
        <p class="text-xs font-semibold uppercase tracking-[.18em] text-[#ff7693]">Your contribution</p>
        <h1 class="mt-2 text-3xl font-semibold tracking-[-.04em]">Edit post</h1>
        <p class="mt-2 text-sm text-zinc-500">Keep the context accurate and the original source intact.</p>
    </div>

    <form method="POST" action="{{ route('posts.update', $post) }}" class="loom-card space-y-6 p-6 sm:p-8">
        @csrf
        @method('PUT')
        <div>
            <label class="form-label" for="kind">What is it?</label>
            <select class="form-input" id="kind" name="kind">
                @foreach (App\PostKind::cases() as $kind)
                    <option value="{{ $kind->value }}" @selected(old('kind', $post->kind->value) === $kind->value)>{{ str($kind->value)->headline() }}</option>
                @endforeach
            </select>
            @error('kind')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label" for="title">Title</label>
            <input class="form-input" id="title" name="title" value="{{ old('title', $post->title) }}" maxlength="180" />
            @error('title')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label" for="body">Your take</label>
            <textarea class="form-input min-h-36 resize-y" id="body" name="body" maxlength="1500">{{ old('body', $post->body) }}</textarea>
            @error('body')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label" for="url">Original link</label>
            <input class="form-input" id="url" type="url" name="url" value="{{ old('url', $post->url) }}" />
            @error('url')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="form-label" for="tags">Tags</label>
            <input class="form-input" id="tags" name="tags" value="{{ old('tags', implode(', ', $post->tags ?? [])) }}" />
            @error('tags')<p class="form-error">{{ $message }}</p>@enderror
        </div>
        <div class="flex items-center justify-between gap-4 border-t border-white/8 pt-6">
            <a class="text-sm text-zinc-500 hover:text-white" href="{{ route('home') }}">Cancel</a>
            <button class="loom-button" type="submit">Save changes</button>
        </div>
    </form>
@endsection
