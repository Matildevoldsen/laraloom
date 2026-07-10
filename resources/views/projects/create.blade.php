@extends('layouts.community', ['title' => 'Submit a project'])

@section('content')
    <div class="mb-7"><p class="text-xs font-semibold uppercase tracking-[.18em] text-[#ff7693]">Made with Laravel</p><h1 class="mt-2 text-3xl font-semibold tracking-[-.04em]">Add your project</h1><p class="mt-2 text-sm text-zinc-500">Show the community what you shipped. Open-source and Laravel Cloud projects are called out.</p></div>
    <form method="POST" action="{{ route('projects.store') }}" class="loom-card grid gap-6 p-6 sm:grid-cols-2 sm:p-8">@csrf
        <div><label class="form-label" for="kind">Kind</label><select class="form-input" id="kind" name="kind">@foreach (App\ProjectKind::cases() as $kind)<option value="{{ $kind->value }}">{{ str($kind->value)->headline() }}</option>@endforeach</select>@error('kind')<p class="form-error">{{ $message }}</p>@enderror</div>
        <div><label class="form-label" for="name">Name</label><input class="form-input" id="name" name="name" value="{{ old('name') }}" required />@error('name')<p class="form-error">{{ $message }}</p>@enderror</div>
        <div class="sm:col-span-2"><label class="form-label" for="tagline">Tagline</label><input class="form-input" id="tagline" name="tagline" value="{{ old('tagline') }}" maxlength="180" required />@error('tagline')<p class="form-error">{{ $message }}</p>@enderror</div>
        <div class="sm:col-span-2"><label class="form-label" for="description">Description</label><textarea class="form-input min-h-36" id="description" name="description" maxlength="3000" required>{{ old('description') }}</textarea>@error('description')<p class="form-error">{{ $message }}</p>@enderror</div>
        @foreach (['url' => 'Project URL', 'repository_url' => 'Repository URL', 'laravel_cloud_url' => 'Laravel Cloud URL', 'logo_url' => 'Logo URL', 'screenshot_url' => 'Screenshot URL'] as $field => $label)<div @class(['sm:col-span-2' => $field === 'url'])><label class="form-label" for="{{ $field }}">{{ $label }}</label><input class="form-input" id="{{ $field }}" type="url" name="{{ $field }}" value="{{ old($field) }}" placeholder="https://…" @required($field === 'url') />@error($field)<p class="form-error">{{ $message }}</p>@enderror</div>@endforeach
        <div class="sm:col-span-2"><label class="form-label" for="tags">Tags</label><input class="form-input" id="tags" name="tags" value="{{ old('tags') }}" placeholder="saas, developer tools, livewire" /></div>
        <label class="sm:col-span-2 flex items-center gap-3 text-sm text-zinc-300"><input type="checkbox" name="is_open_source" value="1" @checked(old('is_open_source')) class="rounded border-white/10 bg-black/20 text-[#ff4d73]" /> This project is open source</label>
        <div class="flex justify-end border-t border-white/8 pt-6 sm:col-span-2"><button class="loom-button" type="submit">Publish project</button></div>
    </form>
@endsection
