@extends('layouts.community', ['title' => $project->name])

@section('content')
    <article>
        @if ($project->screenshot_url)<div class="mb-7 aspect-[16/8] overflow-hidden rounded-2xl border border-zinc-200 bg-zinc-100 dark:border-white/10 dark:bg-white/[.03]"><img src="{{ $project->screenshot_url }}" alt="Screenshot of {{ $project->name }}" class="size-full object-cover" /></div>@endif
        <div class="flex items-start gap-5">
            <div class="grid size-16 shrink-0 place-items-center overflow-hidden rounded-2xl border border-zinc-200 bg-gradient-to-br from-[#ff4d73]/20 to-violet-500/10 text-2xl font-bold text-[#e73562] dark:border-white/10 dark:text-[#ff7693]">@if ($project->logo_url)<img src="{{ $project->logo_url }}" class="size-full object-cover" alt="" />@else{{ str($project->name)->substr(0, 1) }}@endif</div>
            <div><div class="flex flex-wrap items-center gap-2"><h1 class="text-3xl font-semibold tracking-[-.045em] text-zinc-950 dark:text-white sm:text-4xl">{{ $project->name }}</h1>@if ($project->laravel_cloud_url)<span class="cloud-badge">Laravel Cloud</span>@endif @if ($project->is_open_source)<span class="rounded-full border border-emerald-400/15 bg-emerald-400/10 px-2 py-0.5 text-[9px] font-semibold uppercase tracking-wider text-emerald-600 dark:text-emerald-300">Open source</span>@endif</div><p class="mt-2 text-lg text-zinc-600 dark:text-zinc-400">{{ $project->tagline }}</p></div>
        </div>
        <div class="loom-card mt-8 p-6 sm:p-8"><div class="whitespace-pre-line text-[15px] leading-7 text-zinc-700 dark:text-zinc-300">{{ $project->description }}</div><div class="mt-6 flex flex-wrap gap-2">@foreach (($project->tags ?? []) as $tag)<span class="loom-tag">{{ $tag }}</span>@endforeach</div></div>
        <div class="mt-6 flex flex-wrap gap-3"><a class="loom-button" href="{{ $project->url }}" target="_blank" rel="noopener noreferrer">Visit project ↗</a>@if ($project->repository_url)<a class="inline-flex items-center rounded-full border border-zinc-300 px-4 py-2 text-sm text-zinc-700 hover:border-zinc-400 hover:text-zinc-950 dark:border-white/10 dark:text-zinc-300 dark:hover:border-white/20 dark:hover:text-white" href="{{ $project->repository_url }}" target="_blank" rel="noopener noreferrer">View source</a>@endif</div>
    </article>
@endsection

@section('rail')
    <div class="rail-card sticky top-24"><p class="text-xs uppercase tracking-[.16em] text-zinc-500 dark:text-zinc-600">Maker</p><a href="{{ route('profiles.show', $project->user) }}" class="mt-4 flex items-center gap-3"><x-user-avatar :user="$project->user" /><span><span class="flex items-center gap-1.5 text-sm text-zinc-800 dark:text-zinc-200">{{ $project->user->name }} <x-verified-badge :user="$project->user" /></span><span class="block text-xs text-zinc-500 dark:text-zinc-600">{{ '@'.$project->user->username }}</span></span></a><p class="mt-4 text-xs leading-5 text-zinc-500 dark:text-zinc-600">Published {{ $project->published_at?->format('j M Y') }}</p></div>
@endsection
