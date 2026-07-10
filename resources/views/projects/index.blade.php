@extends('layouts.community', ['title' => 'Made with Laravel'])

@section('content')
    <div class="mb-8 flex items-end justify-between gap-6"><div><p class="text-xs font-semibold uppercase tracking-[.18em] text-[#ff7693]">Community directory</p><h1 class="mt-2 text-3xl font-semibold tracking-[-.04em] sm:text-4xl">Made with Laravel</h1><p class="mt-2 text-sm text-zinc-500">Products, packages, and tools shipped by people in the community.</p></div>@auth<a class="loom-button hidden shrink-0 sm:inline-flex" href="{{ route('projects.create') }}">Submit yours</a>@endauth</div>
    <form class="mb-6"><input class="form-input" name="q" value="{{ $search }}" placeholder="Search the directory…" /></form>
    <div class="grid gap-4 sm:grid-cols-2">@forelse ($projects as $project)<x-project-card :$project />@empty<div class="loom-empty sm:col-span-2"><span>◇</span><h2>No projects found</h2><p>Try another search or submit what you built.</p></div>@endforelse</div>
    <div class="mt-7">{{ $projects->links() }}</div>
@endsection
