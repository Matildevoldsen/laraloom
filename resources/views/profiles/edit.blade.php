@extends('layouts.community', ['title' => 'Edit profile'])

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-7">
            <p class="text-xs font-semibold uppercase tracking-[.18em] text-[#e73562] dark:text-[#ff7693]">Community profile</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-[-.04em]">Make it unmistakably yours</h1>
            <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">Your name, photo, and description appear beside everything you share.</p>
        </div>

        <form method="POST" action="{{ route('profiles.update', $user) }}" enctype="multipart/form-data" class="loom-card grid gap-6 p-6 sm:grid-cols-2 sm:p-8">
            @csrf
            @method('PUT')

            <div class="sm:col-span-2" x-data="{ preview: null }">
                <label class="form-label" for="avatar">Profile photo</label>
                <div class="flex items-center gap-5">
                    <img
                        class="size-20 rounded-full border-4 border-white object-cover shadow-sm dark:border-zinc-800"
                        src="{{ $user->avatarUrl() }}"
                        x-bind:src="preview || '{{ $user->avatarUrl() }}'"
                        alt="{{ $user->name }}"
                    />
                    <div>
                        <label for="avatar" class="inline-flex cursor-pointer rounded-full border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-800 transition hover:border-zinc-400 dark:border-white/10 dark:bg-white/5 dark:text-zinc-200 dark:hover:border-white/20">
                            Choose photo
                        </label>
                        <input
                            class="sr-only"
                            id="avatar"
                            name="avatar"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            x-on:change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                        />
                        <p class="form-help">JPG, PNG, or WebP up to 5 MB.</p>
                        @error('avatar')<p class="form-error">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div>
                <label class="form-label" for="name">Display name</label>
                <input class="form-input" id="name" name="name" value="{{ old('name', $user->name) }}" required maxlength="100" />
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label" for="username">Username</label>
                <div class="relative"><span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-zinc-500">@</span><input class="form-input pl-8" id="username" name="username" value="{{ old('username', $user->username) }}" required minlength="3" maxlength="30" /></div>
                @if ($user->username_changed_at?->copy()->addMonthNoOverflow()->isFuture())
                    <p class="form-help">Next change available {{ $user->username_changed_at->copy()->addMonthNoOverflow()->format('j F Y') }}.</p>
                @else
                    <p class="form-help">Choose carefully—you can change this once per month.</p>
                @endif
                @error('username')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label class="form-label" for="headline">Profile description</label>
                <input class="form-input" id="headline" name="headline" value="{{ old('headline', $user->headline) }}" maxlength="120" placeholder="What do you make in the Laravel world?" />
                @error('headline')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label class="form-label" for="bio">About you</label>
                <textarea class="form-input min-h-32 resize-y" id="bio" name="bio" maxlength="600" placeholder="Share the work, interests, and ideas people can talk to you about.">{{ old('bio', $user->bio) }}</textarea>
                @error('bio')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            @foreach (['location' => 'Location', 'website_url' => 'Website', 'x_username' => 'X username'] as $field => $label)
                <div>
                    <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                    <input class="form-input" id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $user->{$field}) }}" @if ($field === 'website_url') type="url" @endif />
                    @error($field)<p class="form-error">{{ $message }}</p>@enderror
                </div>
            @endforeach

            @if ($user->github_username)
                <div>
                    <span class="form-label">GitHub account</span>
                    <a class="form-input flex items-center justify-between" href="https://github.com/{{ $user->github_username }}" rel="me noopener noreferrer" target="_blank">
                        <span>{{ '@'.$user->github_username }}</span>
                        <span class="text-zinc-400" aria-hidden="true">↗</span>
                    </a>
                    <p class="form-help">Managed by your verified GitHub sign-in.</p>
                </div>
            @endif

            <fieldset class="sm:col-span-2">
                <legend class="form-label">Your stack</legend>
                <div class="flex flex-wrap gap-2">
                    @foreach (['Laravel', 'Livewire', 'Filament', 'Inertia', 'Vue', 'React', 'Alpine.js', 'Laravel AI'] as $technology)
                        <label class="cursor-pointer rounded-full border border-zinc-200 bg-white px-3 py-2 text-xs text-zinc-600 transition has-checked:border-[#ff4d73] has-checked:bg-[#ff4d73]/8 has-checked:text-[#d92855] dark:border-white/10 dark:bg-white/[.025] dark:text-zinc-400 dark:has-checked:text-[#ff8ba3]"><input class="sr-only" type="checkbox" name="stack[]" value="{{ $technology }}" @checked(in_array($technology, old('stack', $user->stack ?? []), true)) />{{ $technology }}</label>
                    @endforeach
                </div>
                @error('stack')<p class="form-error">{{ $message }}</p>@enderror
            </fieldset>

            <label class="sm:col-span-2 flex items-center gap-3 text-sm text-zinc-700 dark:text-zinc-300"><input class="rounded border-zinc-300 text-[#ff4d73] focus:ring-[#ff4d73] dark:border-white/10 dark:bg-black/20" type="checkbox" name="is_available_for_work" value="1" @checked(old('is_available_for_work', $user->is_available_for_work)) /> Available for work</label>

            <div class="flex justify-end border-t border-zinc-200 pt-6 sm:col-span-2 dark:border-white/8">
                <button class="loom-button" type="submit">Save profile</button>
            </div>
        </form>
    </div>
@endsection
