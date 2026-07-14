@extends('layouts.community', ['title' => 'Share something'])

@section('content')
    <div class="mb-7"><p class="text-xs font-semibold uppercase tracking-[.18em] text-[#ff7693]">Community publishing</p><h1 class="mt-2 text-3xl font-semibold tracking-[-.04em]">Share something useful</h1><p class="mt-2 text-sm text-zinc-500">Your own words, with a link back to the original source where relevant.</p></div>
    <form method="POST" action="{{ route('posts.store') }}" class="loom-card space-y-6 p-6 sm:p-8">
        @csrf
        <div><label class="form-label" for="kind">What is it?</label><select class="form-input" id="kind" name="kind">@foreach (App\PostKind::cases() as $kind)<option value="{{ $kind->value }}" @selected(old('kind') === $kind->value)>{{ str($kind->value)->headline() }}</option>@endforeach</select>@error('kind')<p class="form-error">{{ $message }}</p>@enderror</div>
        <div><label class="form-label" for="title">Title</label><input class="form-input" id="title" name="title" value="{{ old('title') }}" maxlength="180" placeholder="A clear, useful headline" />@error('title')<p class="form-error">{{ $message }}</p>@enderror</div>
        <div><label class="form-label" for="body">Your take</label><textarea class="form-input min-h-36 resize-y" id="body" name="body" maxlength="1500" data-composer-textarea placeholder="What should the Laravel community know?">{{ old('body') }}</textarea><livewire:composer-autocomplete /><p class="form-help">Summarise or comment in your own words. Do not paste someone else's article.</p>@error('body')<p class="form-error">{{ $message }}</p>@enderror</div>
        <div><label class="form-label" for="url">Original link</label><input class="form-input" id="url" type="url" name="url" value="{{ old('url') }}" placeholder="https://…" />@error('url')<p class="form-error">{{ $message }}</p>@enderror</div>
        <div><label class="form-label" for="tags">Tags</label><input class="form-input" id="tags" name="tags" value="{{ old('tags') }}" placeholder="laravel, livewire, open-source" /><p class="form-help">Comma separated.</p>@error('tags')<p class="form-error">{{ $message }}</p>@enderror</div>
        <div class="flex items-center justify-between gap-4 border-t border-zinc-200 pt-6 dark:border-white/8"><p class="text-xs leading-5 text-zinc-500 dark:text-zinc-600">By publishing, you agree to our <a class="text-zinc-700 hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-white" href="{{ route('legal.content-policy') }}">content principles</a>.</p><button class="loom-button shrink-0" type="submit">Publish post</button></div>
    </form>
@endsection
