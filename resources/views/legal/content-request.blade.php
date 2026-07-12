@extends('layouts.community', ['title' => 'Rights and safety request'])

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-7">
            <p class="text-xs font-semibold uppercase tracking-[.18em] text-[#e73562] dark:text-[#ff7693]">Rights, privacy, and safety</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-[-.04em]">Request an action</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-zinc-600 dark:text-zinc-400">Report illegal content, request intimate-image removal, exercise privacy rights, appeal moderation, or ask for a correction. You do not need an account.</p>
        </div>

        <div class="mb-5 rounded-2xl border border-amber-300/60 bg-amber-50 px-5 py-4 text-sm leading-6 text-amber-950 dark:border-amber-300/15 dark:bg-amber-300/8 dark:text-amber-100">
            For non-consensual intimate imagery, do not upload or reproduce the image here. Give us the exact post, message, or URL reference. Eligible requests may be subject to a 48-hour legal deadline; submitting this form records the request but does not mean the material has already been removed.
        </div>

        <form method="POST" action="{{ route('legal.content-request.store') }}" class="loom-card grid gap-6 p-6 sm:grid-cols-2 sm:p-8">
            @csrf
            <div>
                <label class="form-label" for="type">What do you need?</label>
                <select class="form-input" id="type" name="type">
                    @foreach (App\ContentRequestType::cases() as $type)
                        <option value="{{ $type->value }}" @selected(old('type') === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
                @error('type')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label" for="content_url">Content reference</label>
                <input class="form-input" id="content_url" name="content_url" value="{{ old('content_url') }}" maxlength="2048" placeholder="URL, post ID, or message reference" />
                <p class="form-help">Required for content-specific requests; optional for a general privacy-rights request or complaint.</p>
                @error('content_url')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label" for="requester_name">Your name</label>
                <input class="form-input" id="requester_name" name="requester_name" value="{{ old('requester_name') }}" required maxlength="100" />
                @error('requester_name')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="form-label" for="requester_email">Your email</label>
                <input class="form-input" id="requester_email" type="email" name="requester_email" value="{{ old('requester_email') }}" required maxlength="254" />
                @error('requester_email')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label class="form-label" for="relationship">Your relationship to the content or data</label>
                <input class="form-input" id="relationship" name="relationship" value="{{ old('relationship') }}" required maxlength="120" placeholder="Subject, author, recipient, rights holder, reporter…" />
                @error('relationship')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label class="form-label" for="details">What happened and what outcome do you need?</label>
                <textarea class="form-input min-h-40" id="details" name="details" required minlength="20" maxlength="3000" placeholder="Give enough context for a careful decision. Do not include passwords or reproduce intimate imagery.">{{ old('details') }}</textarea>
                @error('details')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center justify-between gap-4 border-t border-zinc-200 pt-6 sm:col-span-2 dark:border-white/8">
                <p class="text-xs leading-5 text-zinc-500">We use these details to assess, respond to, and document this request. See <a href="{{ route('legal.privacy') }}" class="font-medium text-zinc-700 hover:text-zinc-950 dark:text-zinc-300 dark:hover:text-white">Privacy</a>.</p>
                <button class="loom-button shrink-0">Submit request</button>
            </div>
        </form>
    </div>
@endsection
