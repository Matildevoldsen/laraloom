<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50 text-zinc-950 antialiased dark:bg-[#090b0f] dark:text-zinc-100">
        <div class="loom-glow" aria-hidden="true"></div>

        <header class="sticky top-0 z-50 border-b border-zinc-200/80 bg-white/85 backdrop-blur-xl dark:border-white/8 dark:bg-[#090b0f]/85">
            <div class="mx-auto flex h-16 max-w-[1480px] items-center gap-6 px-4 sm:px-6">
                <a href="{{ route('home') }}" class="group flex items-center gap-3" aria-label="Sourcefolk home">
                    <span class="loom-mark"><i></i><i></i><i></i></span>
                    <span class="text-lg font-semibold tracking-[-0.04em]">Sourcefolk</span>
                </a>

                <form action="{{ route('home') }}" class="mx-auto hidden w-full max-w-xl md:block">
                    <label class="relative block">
                        <span class="sr-only">Search Sourcefolk</span>
                        <svg class="absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-zinc-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                        <input name="q" value="{{ request('q') }}" class="h-10 w-full rounded-full border border-zinc-200 bg-zinc-100/80 pl-10 pr-4 text-sm text-zinc-950 placeholder:text-zinc-500 focus:border-[#ff4d73]/60 focus:bg-white focus:outline-none dark:border-white/10 dark:bg-white/[.045] dark:text-white dark:placeholder:text-zinc-600 dark:focus:bg-white/[.06]" placeholder="Search news, packages, people…" />
                    </label>
                </form>

                <nav class="ml-auto flex items-center gap-2">
                    <button type="button" x-data x-on:click="$flux.dark = ! $flux.dark" class="grid size-10 place-items-center rounded-full text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-950 dark:hover:bg-white/5 dark:hover:text-white" aria-label="Switch color theme" title="Switch color theme">
                        <svg x-show="! $flux.dark" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v2m0 14v2M3 12h2m14 0h2M5.6 5.6 7 7m10 10 1.4 1.4M18.4 5.6 17 7M7 17l-1.4 1.4"/><circle cx="12" cy="12" r="4"/></svg>
                        <svg x-show="$flux.dark" x-cloak class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.6 15.5A8.5 8.5 0 0 1 8.5 3.4 8.5 8.5 0 1 0 20.6 15.5Z"/></svg>
                    </button>
                    @auth
                        <a
                            href="{{ route('bookmarks.index') }}"
                            @class([
                                'grid size-10 place-items-center rounded-full text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-950 dark:hover:bg-white/5 dark:hover:text-white',
                                'bg-zinc-100 text-zinc-950 dark:bg-white/5 dark:text-white' => request()->routeIs('bookmarks.*'),
                            ])
                            aria-label="Bookmarks"
                            title="Bookmarks"
                        >
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M6 3.75h12v17l-6-3.75-6 3.75v-17Z" />
                            </svg>
                        </a>
                        <livewire:notification-indicator />
                        <flux:modal.trigger name="community-composer">
                            <flux:button variant="primary" icon="pencil-square" class="hidden rounded-full! bg-[#ff4d73]! hover:bg-[#ff6382]! sm:inline-flex">Post</flux:button>
                        </flux:modal.trigger>
                        <a href="{{ route('profiles.show', auth()->user()) }}" class="loom-avatar" title="Your profile"><img class="size-full object-cover" src="{{ auth()->user()->avatarUrl() }}" alt="" /></a>
                    @else
                        <a href="{{ route('login') }}" class="hidden px-3 py-2 text-sm text-zinc-600 transition hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-white sm:inline">Log in</a>
                        <a href="{{ route('register') }}" class="loom-button"><span class="sm:hidden">Join</span><span class="hidden sm:inline">Join the community</span></a>
                    @endauth
                </nav>
            </div>
        </header>

        @if (session('status'))
            <div class="mx-auto mt-4 max-w-[1480px] px-4 sm:px-6"><div class="rounded-xl border border-emerald-500/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-200">{{ session('status') }}</div></div>
        @endif

        <button
            type="button"
            data-realtime-refresh
            @if (request()->route('post') instanceof \App\Models\Post) data-post-id="{{ request()->route('post')->id }}" @endif
            @if (request()->route('user') instanceof \App\Models\User) data-profile-id="{{ request()->route('user')->id }}" @endif
            class="pointer-events-none fixed left-1/2 top-20 z-40 flex -translate-x-1/2 translate-y-2 items-center gap-2 rounded-full border border-[#ff4d73]/25 bg-white/95 px-4 py-2 text-xs font-semibold text-[#d92855] opacity-0 shadow-2xl shadow-black/10 backdrop-blur-xl transition duration-200 hover:border-[#ff4d73]/45 dark:bg-[#171218]/95 dark:text-[#ff9aaf] dark:shadow-black/40 dark:hover:text-white"
        >
            <span class="size-1.5 rounded-full bg-[#ff4d73] shadow-[0_0_10px_#ff4d73]"></span>
            New activity · refresh
        </button>

        <div @class([
            'mx-auto grid max-w-[1480px] grid-cols-1 gap-6 px-4 py-7 sm:px-6 lg:grid-cols-[190px_minmax(0,1fr)]',
            'xl:grid-cols-[190px_minmax(0,1fr)]' => $wideMain ?? false,
            'xl:grid-cols-[190px_minmax(0,1fr)_270px]' => ! ($wideMain ?? false),
        ])>
            <aside class="hidden lg:block">
                <nav class="sticky top-24 space-y-1 text-sm">
                    <p class="mb-3 px-3 text-[11px] font-semibold uppercase tracking-[.18em] text-zinc-600">Explore</p>
                    <a href="{{ route('home') }}" @class(['side-link', 'is-active' => request()->routeIs('home') && !request('feed')])>✦ <span>Today in Laravel</span></a>
                    <a href="{{ route('home', ['feed' => 'following']) }}" @class(['side-link', 'is-active' => request('feed') === 'following'])>◎ <span>Following</span></a>
                    <a href="{{ route('home', ['feed' => 'packages']) }}" @class(['side-link', 'is-active' => request('feed') === 'packages'])>⌘ <span>Packages</span></a>
                    <a href="{{ route('projects.index') }}" @class(['side-link', 'is-active' => request()->routeIs('projects.*')])>◇ <span>Made with Laravel</span></a>

                    <div class="my-5 border-t border-zinc-200 dark:border-white/8"></div>
                    @auth
                        <a href="{{ route('projects.create') }}" class="side-link">＋ <span>Submit a project</span></a>
                        <a href="{{ route('notifications.index') }}" @class(['side-link', 'is-active' => request()->routeIs('notifications.*')])>
                            ◌ <span>Notifications</span>
                        </a>
                        <a href="{{ route('bookmarks.index') }}" @class(['side-link', 'is-active' => request()->routeIs('bookmarks.*')])>
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M6 3.75h12v17l-6-3.75-6 3.75v-17Z" />
                            </svg>
                            <span>Bookmarks</span>
                        </a>
                        <a href="{{ route('direct-messages.index') }}" @class(['side-link', 'is-active' => request()->routeIs('direct-messages.*')])>✉ <span>Messages</span></a>
                        <a href="{{ route('profiles.edit', auth()->user()) }}" class="side-link">↗ <span>Edit profile</span></a>
                        @can('access-admin')
                            <a href="{{ route('admin.dashboard') }}" @class(['side-link', 'is-active' => request()->routeIs('admin.*')])>◉ <span>Admin</span></a>
                        @endcan
                    @endauth
                    <a href="{{ route('legal.content-policy') }}" class="side-link">✓ <span>Content principles</span></a>
                </nav>
            </aside>

            <main class="min-w-0">@yield('content'){{ $slot ?? '' }}</main>

            @unless ($wideMain ?? false)
                <aside class="hidden xl:block">@yield('rail')</aside>
            @endunless
        </div>

        <footer class="border-t border-zinc-200 px-4 py-8 text-center text-xs text-zinc-500 dark:border-white/8 dark:text-zinc-600">
            Independent and not affiliated with Laravel Holdings Inc. · <a class="hover:text-zinc-900 dark:hover:text-zinc-300" href="{{ route('legal.content-policy') }}">Content policy</a> · <a class="hover:text-zinc-900 dark:hover:text-zinc-300" href="{{ route('legal.privacy') }}">Privacy</a> · <a class="hover:text-zinc-900 dark:hover:text-zinc-300" href="{{ route('legal.terms') }}">Terms</a> · <a class="inline-flex items-center gap-1 align-middle transition hover:text-zinc-900 dark:hover:text-zinc-300" href="https://github.com/Matildevoldsen/laraloom" rel="noopener noreferrer" target="_blank">
                <svg class="size-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 .7a11.5 11.5 0 0 0-3.64 22.4c.58.1.79-.25.79-.56v-2.23c-3.22.7-3.9-1.37-3.9-1.37-.53-1.34-1.29-1.7-1.29-1.7-1.05-.72.08-.71.08-.71 1.17.08 1.78 1.2 1.78 1.2 1.04 1.77 2.72 1.26 3.38.96.1-.75.4-1.26.74-1.55-2.57-.29-5.27-1.28-5.27-5.68 0-1.25.45-2.28 1.19-3.08-.12-.29-.52-1.46.11-3.04 0 0 .97-.31 3.16 1.18a10.9 10.9 0 0 1 5.75 0c2.2-1.49 3.16-1.18 3.16-1.18.63 1.58.23 2.75.11 3.04.74.8 1.19 1.83 1.19 3.08 0 4.41-2.7 5.38-5.28 5.67.42.36.79 1.07.79 2.16v3.2c0 .31.21.67.8.56A11.5 11.5 0 0 0 12 .7Z" /></svg>
                GitHub
            </a>
        </footer>
        <x-community-composer />
        @fluxScripts
    </body>
</html>
