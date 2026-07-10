<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        <meta name="description" content="The living front page of the Laravel community." />
    </head>
    <body class="min-h-screen bg-[#090b0f] text-zinc-100 antialiased">
        <div class="loom-glow" aria-hidden="true"></div>

        <header class="sticky top-0 z-50 border-b border-white/8 bg-[#090b0f]/85 backdrop-blur-xl">
            <div class="mx-auto flex h-16 max-w-[1480px] items-center gap-6 px-4 sm:px-6">
                <a href="{{ route('home') }}" class="group flex items-center gap-3" aria-label="Laraloom home">
                    <span class="loom-mark"><i></i><i></i><i></i></span>
                    <span class="text-lg font-semibold tracking-[-0.04em]">Laraloom</span>
                </a>

                <form action="{{ route('home') }}" class="mx-auto hidden w-full max-w-xl md:block">
                    <label class="relative block">
                        <span class="sr-only">Search Laraloom</span>
                        <svg class="absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-zinc-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                        <input name="q" value="{{ request('q') }}" class="h-10 w-full rounded-full border border-white/10 bg-white/[.045] pl-10 pr-4 text-sm text-white placeholder:text-zinc-600 focus:border-[#ff4d73]/60 focus:outline-none" placeholder="Search news, packages, people…" />
                    </label>
                </form>

                <nav class="ml-auto flex items-center gap-2">
                    @auth
                        <a href="{{ route('posts.create') }}" class="loom-button hidden sm:inline-flex">Share something</a>
                        <a href="{{ route('profiles.show', auth()->user()) }}" class="loom-avatar" title="Your profile">{{ str(auth()->user()->name)->substr(0, 1)->upper() }}</a>
                    @else
                        <a href="{{ route('login') }}" class="hidden px-3 py-2 text-sm text-zinc-400 transition hover:text-white sm:inline">Log in</a>
                        <a href="{{ route('register') }}" class="loom-button"><span class="sm:hidden">Join</span><span class="hidden sm:inline">Join the community</span></a>
                    @endauth
                </nav>
            </div>
        </header>

        @if (session('status'))
            <div class="mx-auto mt-4 max-w-[1480px] px-4 sm:px-6"><div class="rounded-xl border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-200">{{ session('status') }}</div></div>
        @endif

        <div class="mx-auto grid max-w-[1480px] grid-cols-1 gap-6 px-4 py-7 sm:px-6 lg:grid-cols-[190px_minmax(0,1fr)] xl:grid-cols-[190px_minmax(0,1fr)_270px]">
            <aside class="hidden lg:block">
                <nav class="sticky top-24 space-y-1 text-sm">
                    <p class="mb-3 px-3 text-[11px] font-semibold uppercase tracking-[.18em] text-zinc-600">Explore</p>
                    <a href="{{ route('home') }}" @class(['side-link', 'is-active' => request()->routeIs('home') && !request('feed')])>✦ <span>Today in Laravel</span></a>
                    <a href="{{ route('home', ['feed' => 'following']) }}" @class(['side-link', 'is-active' => request('feed') === 'following'])>◎ <span>Following</span></a>
                    <a href="{{ route('home', ['feed' => 'packages']) }}" @class(['side-link', 'is-active' => request('feed') === 'packages'])>⌘ <span>Packages</span></a>
                    <a href="{{ route('projects.index') }}" @class(['side-link', 'is-active' => request()->routeIs('projects.*')])>◇ <span>Made with Laravel</span></a>

                    <div class="my-5 border-t border-white/8"></div>
                    @auth
                        <a href="{{ route('projects.create') }}" class="side-link">＋ <span>Submit a project</span></a>
                        <a href="{{ route('profiles.edit', auth()->user()) }}" class="side-link">↗ <span>Edit profile</span></a>
                    @endauth
                    <a href="{{ route('legal.content-policy') }}" class="side-link">✓ <span>Content principles</span></a>
                </nav>
            </aside>

            <main class="min-w-0">@yield('content')</main>

            <aside class="hidden xl:block">@yield('rail')</aside>
        </div>

        <footer class="border-t border-white/8 px-4 py-8 text-center text-xs text-zinc-600">
            Built in public for the Laravel community · <a class="hover:text-zinc-300" href="{{ route('legal.content-policy') }}">Content policy</a> · <a class="hover:text-zinc-300" href="{{ route('legal.privacy') }}">Privacy</a> · <a class="hover:text-zinc-300" href="{{ route('legal.terms') }}">Terms</a>
        </footer>
        @fluxScripts
    </body>
</html>
