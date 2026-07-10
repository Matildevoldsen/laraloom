@props(['project'])

<article class="loom-card flex h-full flex-col p-5">
    <div class="flex items-start gap-4">
        <div class="grid size-12 shrink-0 place-items-center overflow-hidden rounded-xl border border-white/10 bg-gradient-to-br from-[#ff4d73]/20 to-violet-500/10 text-xl font-bold text-[#ff7693]">
            @if ($project->logo_url)<img src="{{ $project->logo_url }}" alt="" class="size-full object-cover" />@else{{ str($project->name)->substr(0, 1) }}@endif
        </div>
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2"><h3 class="font-semibold tracking-tight text-white"><a href="{{ route('projects.show', $project) }}" class="hover:text-[#ff7693]">{{ $project->name }}</a></h3>@if ($project->laravel_cloud_url)<span class="cloud-badge">Cloud</span>@endif</div>
            <p class="mt-1 line-clamp-2 text-sm leading-5 text-zinc-400">{{ $project->tagline }}</p>
        </div>
    </div>
    <div class="mt-5 flex flex-wrap gap-2">@foreach (($project->tags ?? []) as $tag)<span class="loom-tag">{{ $tag }}</span>@endforeach</div>
    <div class="mt-auto flex items-center justify-between pt-5 text-xs text-zinc-600">
        <a href="{{ route('profiles.show', $project->user) }}" class="hover:text-zinc-300">by {{ $project->user->name }}</a>
        <span class="capitalize">{{ $project->kind->value }}</span>
    </div>
</article>
