@extends('layouts.community', ['title' => 'Today in Laravel'])

@section('content')
    <section class="mb-7 overflow-hidden rounded-2xl border border-[#ff4d73]/20 bg-gradient-to-br from-[#ff4d73]/12 via-white/[.035] to-violet-500/10 p-6 sm:p-8">
        <div class="flex items-start justify-between gap-5">
            <div>
                <div class="mb-3 flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[.2em] text-[#ff7693]"><span class="size-1.5 rounded-full bg-[#ff4d73] shadow-[0_0_12px_#ff4d73]"></span> The Laravel signal</div>
                <h1 class="max-w-xl text-3xl font-semibold tracking-[-.045em] text-white sm:text-4xl">Everything happening in Laravel, woven together.</h1>
                <p class="mt-3 max-w-xl text-sm leading-6 text-zinc-400">A community front page for the work, writing, packages, and people moving Laravel forward.</p>
            </div>
            <div class="hidden rounded-full border border-white/10 bg-black/20 px-3 py-1.5 text-xs text-zinc-400 sm:block">Updated continuously</div>
        </div>
    </section>

    <div class="mb-5 flex gap-1 overflow-x-auto border-b border-white/8 pb-px text-sm">
        @foreach (['today' => 'Today', 'following' => 'Following', 'packages' => 'Packages', 'cloud' => 'On Cloud'] as $value => $label)
            <a href="{{ route('home', $value === 'today' ? [] : ['feed' => $value]) }}" @class(['feed-tab', 'is-active' => $feed === $value])>{{ $label }}</a>
        @endforeach
    </div>

    @if ($search)<p class="mb-4 text-sm text-zinc-500">Results for <span class="text-zinc-200">“{{ $search }}”</span></p>@endif

    <div class="space-y-4" data-realtime-feed>
        @forelse ($posts as $post)
            <x-post-card :$post />
        @empty
            <div class="loom-empty"><span>✦</span><h2>No threads here yet</h2><p>Be the first to add something worth knowing.</p>@auth<a class="loom-button mt-4" href="{{ route('posts.create') }}">Share with Laravel</a>@endauth</div>
        @endforelse
    </div>
    <div class="mt-6">{{ $posts->links() }}</div>
@endsection

@section('rail')
    <div class="sticky top-24 space-y-5">
        <section class="rail-card">
            <div class="rail-title"><span>Made with Laravel</span><a href="{{ route('projects.index') }}">See all</a></div>
            <div class="mt-4 space-y-4">@foreach ($projects as $project)<a href="{{ route('projects.show', $project) }}" class="group flex gap-3"><div class="grid size-9 shrink-0 place-items-center rounded-lg bg-[#ff4d73]/10 font-semibold text-[#ff7693]">{{ str($project->name)->substr(0, 1) }}</div><div><p class="text-sm font-medium text-zinc-200 group-hover:text-white">{{ $project->name }}</p><p class="mt-0.5 line-clamp-1 text-xs text-zinc-600">{{ $project->tagline }}</p></div></a>@endforeach</div>
        </section>
        <section class="rail-card">
            <div class="rail-title"><span>People to know</span></div>
            <div class="mt-4 space-y-4">@foreach ($people as $person)<a href="{{ route('profiles.show', $person) }}" class="group flex items-center gap-3"><span class="loom-avatar">{{ str($person->name)->substr(0, 1) }}</span><span class="min-w-0"><span class="block truncate text-sm text-zinc-200 group-hover:text-white">{{ $person->name }}</span><span class="block truncate text-xs text-zinc-600">{{ $person->headline ?: '@'.$person->username }}</span></span></a>@endforeach</div>
        </section>
        <p class="px-2 text-[11px] leading-5 text-zinc-700">Laraloom stores links, attribution, and short original summaries—not copied articles. Publishers can opt out at any time.</p>
    </div>
@endsection
