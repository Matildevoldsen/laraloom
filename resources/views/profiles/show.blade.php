@extends('layouts.community', ['title' => $user->name])

@section('content')
    <section class="loom-card mb-7 overflow-hidden">
        <div class="h-28 bg-[radial-gradient(circle_at_15%_20%,rgba(255,77,115,.35),transparent_28%),radial-gradient(circle_at_80%_0%,rgba(139,92,246,.28),transparent_32%),#111319]"></div>
        <div class="px-6 pb-6 sm:px-8">
            <div class="-mt-10 flex items-end justify-between gap-4"><div class="grid size-20 place-items-center rounded-full border-4 border-[#111319] bg-gradient-to-br from-zinc-700 to-zinc-900 text-2xl font-semibold">{{ str($user->name)->substr(0, 1)->upper() }}</div><div class="mb-1 flex gap-2">@auth @if (auth()->id() === $user->id)<a class="rounded-full border border-white/10 px-4 py-2 text-sm hover:border-white/20" href="{{ route('profiles.edit', $user) }}">Edit profile</a>@else<form method="POST" action="{{ route('profiles.follow', $user) }}">@csrf<button class="loom-button">Follow</button></form>@endif @endauth</div></div>
            <h1 class="mt-4 text-2xl font-semibold tracking-[-.035em]">{{ $user->name }}</h1><p class="mt-0.5 text-sm text-zinc-600">{{ '@'.$user->username }}</p>
            @if ($user->headline)<p class="mt-3 text-zinc-300">{{ $user->headline }}</p>@endif
            @if ($user->bio)<p class="mt-3 max-w-2xl whitespace-pre-line text-sm leading-6 text-zinc-400">{{ $user->bio }}</p>@endif
            <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-zinc-600">@if ($user->location)<span>⌖ {{ $user->location }}</span>@endif @if ($user->website_url)<a class="hover:text-zinc-300" href="{{ $user->website_url }}" rel="me noopener noreferrer" target="_blank">Website ↗</a>@endif <span><b class="font-medium text-zinc-300">{{ $user->followers_count }}</b> followers</span><span><b class="font-medium text-zinc-300">{{ $user->following_count }}</b> following</span>@if ($user->is_available_for_work)<span class="text-emerald-400">● Available for work</span>@endif</div>
            @if ($user->stack)<div class="mt-4 flex flex-wrap gap-2">@foreach ($user->stack as $item)<span class="loom-tag">{{ $item }}</span>@endforeach</div>@endif
        </div>
    </section>

    @if ($projects->isNotEmpty())<div class="mb-8"><div class="mb-4 flex items-center justify-between"><h2 class="font-semibold text-zinc-200">Projects</h2><span class="text-xs text-zinc-600">{{ $projects->count() }} shipped</span></div><div class="grid gap-4 sm:grid-cols-2">@foreach ($projects as $project)<x-project-card :$project />@endforeach</div></div>@endif
    <div><h2 class="mb-4 font-semibold text-zinc-200">Posts</h2><div class="space-y-4">@forelse ($posts as $post)<x-post-card :$post />@empty<div class="loom-empty"><span>◎</span><h2>Quiet here—for now</h2><p>{{ $user->name }} has not posted yet.</p></div>@endforelse</div></div>
@endsection
