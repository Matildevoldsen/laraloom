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
            </div>
        </section>

        @php($activeTab = request('tab', 'posts'))
        <nav class="mb-5 flex gap-1 overflow-x-auto border-b border-zinc-200 dark:border-white/8" aria-label="Profile content">
            @foreach (['posts' => 'Posts', 'replies' => 'Replies', 'reposts' => 'Reposts', 'likes' => 'Likes', 'projects' => 'Packages'] as $tab => $label)
                <a href="{{ route('profiles.show', [$user, 'tab' => $tab]) }}" @class(['border-b-2 px-4 py-3 text-sm font-medium transition', 'border-[#ff4d73] text-zinc-950 dark:text-white' => $activeTab === $tab, 'border-transparent text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-200' => $activeTab !== $tab])>{{ $label }}</a>
            @endforeach
        </nav>

        @if ($activeTab === 'projects')
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
