@extends('layouts.community', ['title' => 'Member verification'])

@section('content')
    <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[.18em] text-[#d92855] dark:text-[#ff7693]">Community trust</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-[-.04em]">Member verification</h1>
            <p class="mt-2 text-sm text-zinc-500">Verification is a public endorsement. It never grants administrative access.</p>
        </div>
        <a class="rounded-full border border-zinc-300 px-4 py-2 text-sm font-medium transition hover:border-zinc-500 dark:border-white/10 dark:hover:border-white/25" href="{{ route('admin.dashboard') }}">Back to moderation</a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-white/8 dark:bg-white/[.025]">
        <div class="divide-y divide-zinc-200 dark:divide-white/8">
            @foreach ($users as $user)
                <article class="flex flex-wrap items-center gap-4 px-4 py-4 sm:px-5">
                    <x-user-avatar :$user size="size-11" />
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-1.5">
                            <a class="truncate text-sm font-semibold text-zinc-900 hover:text-[#d92855] dark:text-zinc-100 dark:hover:text-[#ff7693]" href="{{ route('profiles.show', $user) }}">{{ $user->name }}</a>
                            <x-verified-badge :$user />
                        </div>
                        <p class="mt-0.5 truncate text-xs text-zinc-500">{{ '@'.$user->username }} · joined {{ $user->created_at?->diffForHumans() }}</p>
                    </div>
                    @if ($user->is_admin)
                        <span class="rounded-full bg-violet-500/10 px-3 py-1 text-xs font-medium text-violet-700 dark:text-violet-300">Administrator</span>
                    @endif
                    <form method="POST" action="{{ route('admin.users.verification', $user) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="is_verified" value="{{ $user->is_verified ? '0' : '1' }}" />
                        <flux:button type="submit" :variant="$user->is_verified ? 'ghost' : 'primary'" class="rounded-full! {{ $user->is_verified ? '' : 'bg-[#d92855]!' }}">
                            {{ $user->is_verified ? 'Remove verification' : 'Verify member' }}
                        </flux:button>
                    </form>
                </article>
            @endforeach
        </div>
    </div>

    <div class="mt-6">{{ $users->links() }}</div>
@endsection
