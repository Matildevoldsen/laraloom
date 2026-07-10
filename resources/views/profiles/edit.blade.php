@extends('layouts.community', ['title' => 'Edit profile'])

@section('content')
    <div class="mb-7"><p class="text-xs font-semibold uppercase tracking-[.18em] text-[#ff7693]">Community profile</p><h1 class="mt-2 text-3xl font-semibold tracking-[-.04em]">Make it yours</h1></div>
    <form method="POST" action="{{ route('profiles.update', $user) }}" class="loom-card grid gap-6 p-6 sm:grid-cols-2 sm:p-8">@csrf @method('PUT')
        @foreach (['name' => 'Name', 'username' => 'Username', 'headline' => 'Headline', 'location' => 'Location', 'website_url' => 'Website', 'github_username' => 'GitHub username', 'x_username' => 'X username'] as $field => $label)<div @class(['sm:col-span-2' => in_array($field, ['headline'], true)])><label class="form-label" for="{{ $field }}">{{ $label }}</label><input class="form-input" id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $user->{$field}) }}" @if ($field === 'website_url') type="url" @endif />@error($field)<p class="form-error">{{ $message }}</p>@enderror</div>@endforeach
        <div class="sm:col-span-2"><label class="form-label" for="bio">Bio</label><textarea class="form-input min-h-32" id="bio" name="bio" maxlength="500">{{ old('bio', $user->bio) }}</textarea>@error('bio')<p class="form-error">{{ $message }}</p>@enderror</div>
        <fieldset class="sm:col-span-2"><legend class="form-label">Your stack</legend><div class="flex flex-wrap gap-3">@foreach (['Laravel', 'Livewire', 'Filament', 'Inertia', 'Vue', 'React', 'Alpine.js', 'Laravel AI'] as $technology)<label class="flex items-center gap-2 rounded-full border border-white/10 px-3 py-2 text-xs text-zinc-400"><input type="checkbox" name="stack[]" value="{{ $technology }}" @checked(in_array($technology, old('stack', $user->stack ?? []), true)) /> {{ $technology }}</label>@endforeach</div>@error('stack')<p class="form-error">{{ $message }}</p>@enderror</fieldset>
        <label class="sm:col-span-2 flex items-center gap-3 text-sm text-zinc-300"><input type="checkbox" name="is_available_for_work" value="1" @checked(old('is_available_for_work', $user->is_available_for_work)) /> Available for work</label>
        <div class="flex justify-end border-t border-white/8 pt-6 sm:col-span-2"><button class="loom-button">Save profile</button></div>
    </form>
@endsection
