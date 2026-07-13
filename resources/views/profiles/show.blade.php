@extends('layouts.community', [
    'title' => $user->name.' (@'.$user->username.')',
    'description' => $user->bio ?: ($user->headline ?: 'See '.$user->name.'\'s posts, packages, projects, and activity on Sourcefolk.'),
])

@section('content')
    <div data-realtime-profile>
        <section class="loom-card mb-6 overflow-hidden">
            <div class="h-28 bg-[radial-gradient(circle_at_15%_20%,rgba(255,77,115,.28),transparent_28%),radial-gradient(circle_at_80%_0%,rgba(139,92,246,.2),transparent_32%),#f4f4f5] dark:bg-[radial-gradient(circle_at_15%_20%,rgba(255,77,115,.35),transparent_28%),radial-gradient(circle_at_80%_0%,rgba(139,92,246,.28),transparent_32%),#111319]"></div>
            <div class="px-5 pb-5 sm:px-7">
                <div class="-mt-10 flex items-end justify-between gap-4">
                    <img class="size-20 rounded-full border-4 border-white bg-zinc-100 object-cover shadow-sm dark:border-[#111319] dark:bg-zinc-900" src="{{ $user->avatarUrl() }}" alt="{{ $user->name }}" />
                    <div class="mb-1">
                        @auth
                            @if (auth()->id() === $user->id)
                                <a class="rounded-full border border-zinc-300 px-4 py-2 text-sm font-medium hover:border-zinc-400 dark:border-white/10 dark:hover:border-white/20" href="{{ route('profiles.edit', $user) }}">Edit profile</a>
                            @else
                                <div class="flex items-center gap-2">
                                    @if ($user->can_message)
                                        <form method="POST" action="{{ route('direct-messages.store', ['recipient' => $user]) }}">
                                            @csrf
                                            <flux:button type="submit" variant="ghost" icon="chat-bubble-left" class="rounded-full! border border-zinc-300! dark:border-white/10!">Message</flux:button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('profiles.follow', $user) }}">@csrf
                                        <button @class(['loom-button', 'bg-zinc-200! text-zinc-800! shadow-none dark:bg-white/10! dark:text-white!' => $user->is_following])>{{ $user->is_following ? 'Unfollow' : 'Follow' }}</button>
                                    </form>
                                </div>
                            @endif
                        @endauth
                    </div>
                </div>
                <h1 class="mt-4 flex items-center gap-2 text-2xl font-semibold tracking-[-.035em]">{{ $user->name }} <x-verified-badge :$user size="md" /></h1>
                <p class="mt-0.5 text-sm text-zinc-500">{{ '@'.$user->username }}</p>
                @if ($user->headline)<p class="mt-3 text-zinc-800 dark:text-zinc-300">{{ $user->headline }}</p>@endif
                @if ($user->bio)<p class="mt-3 max-w-2xl whitespace-pre-line text-sm leading-6 text-zinc-600 dark:text-zinc-400">{{ $user->bio }}</p>@endif
                <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-zinc-500">
                    @if ($user->location)<span>⌖ {{ $user->location }}</span>@endif
                    @if ($user->website_url)<a class="hover:text-zinc-900 dark:hover:text-zinc-300" href="{{ $user->website_url }}" rel="me noopener noreferrer" target="_blank">Website ↗</a>@endif
                    <flux:modal.trigger name="profile-followers"><button><b class="font-medium text-zinc-800 dark:text-zinc-200">{{ $user->followers_count }}</b> followers</button></flux:modal.trigger>
                    <flux:modal.trigger name="profile-following"><button><b class="font-medium text-zinc-800 dark:text-zinc-200">{{ $user->following_count }}</b> following</button></flux:modal.trigger>
                    @if ($user->is_available_for_work)<span class="text-emerald-400">● Available for work</span>@endif
                </div>
                @if ($user->stack)<div class="mt-4 flex flex-wrap gap-2">@foreach ($user->stack as $item)<flux:badge size="sm" color="violet" inset="top bottom">{{ $item }}</flux:badge>@endforeach</div>@endif

                @if ($user->github_id && $user->github_username)
                    <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-2 border-t border-zinc-200 pt-4 text-xs dark:border-white/8">
                        <a class="inline-flex items-center gap-2 font-medium text-zinc-700 transition hover:text-zinc-950 dark:text-zinc-300 dark:hover:text-white" href="https://github.com/{{ $user->github_username }}" rel="me noopener noreferrer" target="_blank">
                            <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 .7a11.5 11.5 0 0 0-3.64 22.4c.58.1.79-.25.79-.56v-2.23c-3.22.7-3.9-1.37-3.9-1.37-.53-1.34-1.29-1.7-1.29-1.7-1.05-.72.08-.71.08-.71 1.17.08 1.78 1.2 1.78 1.2 1.04 1.77 2.72 1.26 3.38.96.1-.75.4-1.26.74-1.55-2.57-.29-5.27-1.28-5.27-5.68 0-1.25.45-2.28 1.19-3.08-.12-.29-.52-1.46.11-3.04 0 0 .97-.31 3.16 1.18a10.9 10.9 0 0 1 5.75 0c2.2-1.49 3.16-1.18 3.16-1.18.63 1.58.23 2.75.11 3.04.74.8 1.19 1.83 1.19 3.08 0 4.41-2.7 5.38-5.28 5.67.42.36.79 1.07.79 2.16v3.2c0 .31.21.67.8.56A11.5 11.5 0 0 0 12 .7Z" /></svg>
                            {{ '@'.$user->github_username }}
                            <span aria-hidden="true">↗</span>
                        </a>
                        @if ($gitHubActivity)
                            <a class="text-zinc-500 transition hover:text-zinc-900 dark:hover:text-zinc-200" href="{{ route('profiles.show', [$user, 'tab' => 'github']) }}"><b class="font-semibold tabular-nums text-zinc-800 dark:text-zinc-200">{{ number_format($gitHubActivity->publicRepositories) }}</b> public repos</a>
                            <a class="text-zinc-500 transition hover:text-zinc-900 dark:hover:text-zinc-200" href="{{ route('profiles.show', [$user, 'tab' => 'github']) }}"><b class="font-semibold tabular-nums text-zinc-800 dark:text-zinc-200">{{ $gitHubActivity->commitCountIncomplete ? 'At least ' : '' }}{{ number_format($gitHubActivity->indexedCommitsInOwnedRepositories) }}</b> indexed commits in owned repos</a>
                        @endif
                    </div>
                @endif
            </div>
        </section>

        @php($activeTab = request('tab', 'posts'))
        <nav class="mb-5 flex gap-1 overflow-x-auto border-b border-zinc-200 dark:border-white/8" aria-label="Profile content">
            @foreach ([
                'posts' => 'Posts',
                'replies' => 'Replies',
                'reposts' => 'Reposts',
                'likes' => 'Likes',
                'projects' => 'Packages',
                ...($user->github_id && $user->github_username ? ['github' => 'GitHub'] : []),
            ] as $tab => $label)
                <a href="{{ route('profiles.show', [$user, 'tab' => $tab]) }}" @class(['border-b-2 px-4 py-3 text-sm font-medium transition', 'border-[#ff4d73] text-zinc-950 dark:text-white' => $activeTab === $tab, 'border-transparent text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-200' => $activeTab !== $tab])>{{ $label }}</a>
            @endforeach
        </nav>

        @if ($activeTab === 'github' && $user->github_id && $user->github_username)
            @if ($gitHubActivity)
                <x-github-profile-activity :activity="$gitHubActivity" :user="$user" />
            @else
                <x-profile-empty icon="↗" message="GitHub activity is temporarily unavailable. The linked profile is still available." />
            @endif
        @elseif ($activeTab === 'projects')
            <div class="grid gap-4 sm:grid-cols-2">@forelse ($projects as $project)<x-project-card :$project />@empty<x-profile-empty icon="◇" :message="$user->name.' has not shared a package or project yet.'" />@endforelse</div>
        @elseif ($activeTab === 'replies')
            <div class="space-y-3">@forelse ($replies as $reply)<a href="{{ route('posts.show', $reply->post) }}#conversation" class="loom-card block p-5"><p class="text-xs text-zinc-500">Replied in {{ $reply->post->title ?: 'a conversation' }}</p><p class="mt-2 text-sm text-zinc-800 dark:text-zinc-200">{{ $reply->body }}</p></a>@empty<x-profile-empty icon="◯" :message="$user->name.' has not replied yet.'" />@endforelse</div>
        @elseif (in_array($activeTab, ['likes', 'reposts'], true))
            @php($activityPosts = $activeTab === 'likes' ? $likedPosts : $repostedPosts)
            <div class="space-y-4">@forelse ($activityPosts as $post)<x-post-card :$post />@empty<x-profile-empty :icon="$activeTab === 'likes' ? '♡' : '⇄'" :message="$user->name.' has no '.$activeTab.' yet.'" />@endforelse</div>
        @else
            <div class="space-y-4">@forelse ($posts as $post)<x-post-card :$post />@empty<x-profile-empty icon="◎" :message="$user->name.' has not posted yet.'" />@endforelse</div>
        @endif

        @foreach (['followers' => $followers, 'following' => $following] as $relationship => $people)
            <flux:modal name="profile-{{ $relationship }}" variant="flyout" position="right" class="md:w-[32rem]">
                <div class="space-y-4"><flux:heading size="lg">{{ ucfirst($relationship) }}</flux:heading><div class="divide-y divide-zinc-200 dark:divide-white/8">@forelse ($people as $person)<a href="{{ route('profiles.show', $person) }}" class="flex items-center gap-3 py-4"><x-user-avatar :user="$person" size="size-11" /><div><p class="flex items-center gap-1.5 font-medium text-zinc-900 dark:text-zinc-100">{{ $person->name }} <x-verified-badge :user="$person" /></p><p class="text-xs text-zinc-500">{{ '@'.$person->username }}</p></div></a>@empty<p class="py-8 text-center text-sm text-zinc-500">Nobody here yet.</p>@endforelse</div></div>
            </flux:modal>
        @endforeach
    </div>
@endsection
