@extends('layouts.community', ['title' => 'Messages', 'wideMain' => true])

@section('content')
    @php($hasSelection = $selectedConversation instanceof \App\Models\DirectConversation)
    @php($selectedPerson = $hasSelection ? $selectedConversation->otherParticipant($viewer) : null)

    <section
        data-direct-messages
        data-user-id="{{ $viewer->id }}"
        @if ($hasSelection) data-conversation-id="{{ $selectedConversation->id }}" @endif
        class="overflow-hidden rounded-[1.75rem] border border-zinc-200/90 bg-white shadow-xl shadow-zinc-950/5 dark:border-white/10 dark:bg-[#101218] dark:shadow-2xl dark:shadow-black/20"
    >
        <div class="grid h-[calc(100dvh-7.5rem)] min-h-[40rem] lg:grid-cols-[22rem_minmax(0,1fr)]">
            <aside @class([
                'min-h-0 flex-col border-zinc-200 dark:border-white/8 lg:flex lg:border-r',
                'flex' => ! $hasSelection,
                'hidden' => $hasSelection,
            ])>
                <header class="flex h-[4.75rem] shrink-0 items-center justify-between gap-4 border-b border-zinc-200 px-5 dark:border-white/8">
                    <div>
                        <p class="text-[11px] font-semibold uppercase tracking-[.2em] text-[#ff6d91]">Private</p>
                        <h1 class="mt-1 text-xl font-semibold tracking-[-.035em]">Messages</h1>
                    </div>

                    <flux:modal.trigger name="new-direct-message">
                        <flux:button
                            variant="primary"
                            icon="plus"
                            square
                            class="rounded-full! bg-[#ff4d73]! hover:bg-[#ff6382]!"
                            aria-label="Start a conversation"
                        />
                    </flux:modal.trigger>
                </header>

                <div class="min-h-0 flex-1 overflow-y-auto">
                    @forelse ($conversations as $conversation)
                        @php($person = $conversation->otherParticipant($viewer))
                        @php($latestMessage = $conversation->latestMessage)
                        @php($isUnread = $conversation->isUnreadFor($viewer))

                        <a
                            href="{{ route('direct-messages.show', $conversation) }}"
                            @if ($selectedConversation?->is($conversation)) aria-current="page" @endif
                            @class([
                                'group flex gap-3 border-b border-zinc-100 px-4 py-4 transition hover:bg-zinc-50 dark:border-white/6 dark:hover:bg-white/[.045]',
                                'bg-zinc-100/80 dark:bg-white/[.055]' => $selectedConversation?->is($conversation),
                            ])
                        >
                            <x-user-avatar :user="$person" />

                            <span class="min-w-0 flex-1">
                                <span class="flex items-baseline justify-between gap-2">
                                    <span @class(['flex items-center gap-1.5 truncate text-sm text-zinc-900 dark:text-zinc-100', 'font-semibold' => $isUnread, 'font-medium' => ! $isUnread])>
                                        {{ $person->name }} <x-verified-badge :user="$person" />
                                    </span>
                                    @if ($latestMessage?->created_at)
                                        <time class="shrink-0 text-[10px] text-zinc-600" datetime="{{ $latestMessage->created_at->toIso8601String() }}">
                                            {{ $latestMessage->created_at->diffForHumans(short: true) }}
                                        </time>
                                    @endif
                                </span>

                                <span class="mt-1 flex items-center gap-2">
                                    <span @class(['truncate text-xs', 'text-zinc-700 dark:text-zinc-300' => $isUnread, 'text-zinc-500' => ! $isUnread])>
                                        @if ($latestMessage)
                                            {{ $latestMessage->sender_id === $viewer->id ? 'You: ' : '' }}{{ str($latestMessage->body)->squish()->limit(58) }}
                                        @else
                                            Say hello
                                        @endif
                                    </span>
                                    @if ($isUnread)
                                        <span data-unread-indicator class="size-2 shrink-0 rounded-full bg-[#ff4d73] shadow-[0_0_10px_rgba(255,77,115,.6)]" aria-label="Unread"></span>
                                    @endif
                                </span>
                            </span>
                        </a>
                    @empty
                        <div class="grid min-h-80 place-items-center px-8 text-center">
                            <div>
                                <span class="mx-auto grid size-14 place-items-center rounded-full bg-zinc-100 text-2xl dark:bg-white/5">✉</span>
                                <h2 class="mt-4 font-semibold text-zinc-800 dark:text-zinc-200">Your inbox is quiet</h2>
                                <p class="mt-2 text-sm leading-6 text-zinc-500">Start a private conversation with someone who follows you.</p>
                                <flux:modal.trigger name="new-direct-message">
                                    <flux:button variant="primary" class="mt-5 rounded-full! bg-[#ff4d73]!">New message</flux:button>
                                </flux:modal.trigger>
                            </div>
                        </div>
                    @endforelse
                </div>
            </aside>

            <article @class([
                'min-h-0 flex-col bg-zinc-50/60 dark:bg-[#0c0e13]',
                'flex' => $hasSelection,
                'hidden lg:flex' => ! $hasSelection,
            ])>
                @if ($hasSelection && $selectedPerson)
                    <header class="flex h-[4.75rem] shrink-0 items-center gap-3 border-b border-zinc-200 bg-white/90 px-4 backdrop-blur-xl dark:border-white/8 dark:bg-[#101218]/90 sm:px-6">
                        <a href="{{ route('direct-messages.index') }}" class="grid size-10 place-items-center rounded-full text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-950 dark:text-zinc-400 dark:hover:bg-white/8 dark:hover:text-white lg:hidden" aria-label="Back to conversations">
                            <flux:icon name="arrow-left" class="size-5" />
                        </a>
                        <a href="{{ route('profiles.show', $selectedPerson) }}" class="flex min-w-0 items-center gap-3">
                            <x-user-avatar :user="$selectedPerson" size="size-10" />
                            <span class="min-w-0">
                                <span class="flex items-center gap-1.5 truncate text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $selectedPerson->name }} <x-verified-badge :user="$selectedPerson" /></span>
                                <span class="block truncate text-xs text-zinc-500">{{ '@'.$selectedPerson->username }}</span>
                            </span>
                        </a>
                        <span class="ml-auto hidden shrink-0 items-center gap-1.5 text-[10px] text-zinc-500 sm:flex" title="Encrypted in transit and at rest; not end-to-end encrypted.">
                            <flux:icon name="lock-closed" class="size-3.5" /> Stored encrypted
                        </span>
                    </header>

                    <div data-message-scroll role="log" aria-live="polite" aria-relevant="additions" aria-label="Conversation with {{ $selectedPerson->name }}" class="min-h-0 flex-1 overflow-y-auto px-4 py-5 sm:px-7 sm:py-7">
                        <div class="mx-auto flex min-h-full w-full max-w-4xl flex-col">
                            <div class="mb-5 flex items-center gap-3 rounded-2xl bg-white/75 px-4 py-3 text-left shadow-sm ring-1 ring-zinc-200/70 dark:bg-white/[.035] dark:ring-white/8">
                                <x-user-avatar :user="$selectedPerson" size="size-11" />
                                <div class="min-w-0">
                                    <p class="flex items-center gap-1.5 truncate text-sm font-semibold text-zinc-800 dark:text-zinc-200">Your conversation with {{ $selectedPerson->name }} <x-verified-badge :user="$selectedPerson" /></p>
                                    <p class="mt-0.5 text-xs text-zinc-500">Private to both participants · encrypted in transit and at rest, not end-to-end encrypted.</p>
                                </div>
                            </div>

                            <div class="mt-auto flex flex-col gap-2.5 pt-10">
                                @foreach ($messages as $message)
                                    @php($isMine = $message->sender_id === $viewer->id)
                                    <div id="message-{{ $message->id }}" @class(['flex', 'justify-end' => $isMine, 'justify-start' => ! $isMine])>
                                        <div @class([
                                            'max-w-[min(82%,38rem)] rounded-[1.35rem] px-4 py-2.5 text-sm leading-6 shadow-sm',
                                            'rounded-br-md bg-[#d92855] text-white' => $isMine,
                                            'rounded-bl-md bg-zinc-100 text-zinc-800 ring-1 ring-zinc-200 dark:bg-white/[.075] dark:text-zinc-200 dark:ring-white/8' => ! $isMine,
                                        ])>
                                            <p class="whitespace-pre-wrap break-words">{{ $message->body }}</p>
                                            <time @class(['mt-1 block text-[9px]', 'text-white/75' => $isMine, 'text-zinc-600 dark:text-zinc-400' => ! $isMine]) datetime="{{ $message->created_at?->toIso8601String() }}">
                                                {{ $message->created_at?->format('H:i') }}
                                            </time>
                                        </div>
                                    </div>
                                @endforeach
                                <span id="latest" aria-hidden="true"></span>
                            </div>
                        </div>
                    </div>

                    @if ($selectedConversation->isUnreadFor($viewer))
                        <form data-mark-read method="POST" action="{{ route('direct-messages.read', $selectedConversation) }}" class="border-t border-zinc-200 px-4 py-2 text-center dark:border-white/6">
                            @csrf
                            @method('PUT')
                            <button class="text-xs font-medium text-[#ff8ca6] hover:text-white">Mark conversation as read</button>
                        </form>
                    @endif

                    <footer class="shrink-0 border-t border-zinc-200 bg-white/95 p-3 backdrop-blur-xl dark:border-white/8 dark:bg-[#101218]/95 sm:px-7 sm:py-4">
                        @if ($selectedConversation->hasActiveFollow())
                            <form method="POST" action="{{ route('direct-messages.messages.store', $selectedConversation) }}" class="mx-auto flex max-w-4xl items-end gap-2">
                                @csrf
                                <input type="hidden" name="client_id" value="{{ str()->uuid() }}">
                                <flux:textarea
                                    name="body"
                                    :value="old('body')"
                                    rows="auto"
                                    resize="none"
                                    maxlength="4000"
                                    placeholder="Message {{ $selectedPerson->name }}"
                                    class="max-h-36! min-h-11! rounded-[1.35rem]! bg-white! dark:bg-white/[.065]!"
                                />
                                <flux:button type="submit" variant="primary" icon="paper-airplane" square class="size-11! shrink-0 rounded-full! bg-[#ff4d73]! hover:bg-[#ff6382]!" aria-label="Send message" />
                            </form>
                            @error('body')<p class="mx-auto mt-2 max-w-4xl text-xs text-red-600 dark:text-red-300">{{ $message }}</p>@enderror
                        @else
                            <p class="mx-auto max-w-4xl rounded-2xl bg-zinc-100 px-4 py-3 text-center text-xs leading-5 text-zinc-500 dark:bg-white/5">
                                This conversation is read-only until one of you follows the other again.
                            </p>
                        @endif
                    </footer>
                @else
                    <div class="grid h-full place-items-center px-10 text-center">
                        <div class="max-w-sm">
                            <span class="mx-auto grid size-16 place-items-center rounded-full border border-zinc-200 bg-zinc-50 text-2xl dark:border-white/10 dark:bg-white/[.035]">✦</span>
                            <h2 class="mt-5 text-xl font-semibold tracking-[-.03em] text-zinc-800 dark:text-zinc-200">Choose a conversation</h2>
                            <p class="mt-2 text-sm leading-6 text-zinc-500">Private messages stay between participants and are stored encrypted.</p>
                        </div>
                    </div>
                @endif
            </article>
        </div>
    </section>

    <flux:modal name="new-direct-message" variant="flyout" position="right" class="w-[calc(100vw-1.5rem)] md:w-[30rem]">
        <div class="space-y-5">
            <div>
                <flux:heading size="lg">New message</flux:heading>
                <flux:text class="mt-1">You can start a conversation with people who follow you.</flux:text>
            </div>

            <div class="max-h-96 divide-y divide-zinc-200 overflow-y-auto dark:divide-white/8">
                @forelse ($recipients as $recipient)
                    <form method="POST" action="{{ route('direct-messages.store', ['recipient' => $recipient]) }}">
                        @csrf
                        <button class="flex w-full items-center gap-3 px-1 py-3 text-left transition hover:bg-zinc-50 dark:hover:bg-white/[.045]">
                            <x-user-avatar :user="$recipient" />
                            <span class="min-w-0">
                                <span class="flex items-center gap-1.5 truncate text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $recipient->name }} <x-verified-badge :user="$recipient" /></span>
                                <span class="block truncate text-xs text-zinc-500">{{ '@'.$recipient->username }}</span>
                            </span>
                            <flux:icon name="chevron-right" class="ml-auto size-4 text-zinc-600" />
                        </button>
                    </form>
                @empty
                    <div class="py-10 text-center">
                        <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">No eligible recipients yet</p>
                        <p class="mt-2 text-xs leading-5 text-zinc-500">When someone follows you, they will appear here.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </flux:modal>
@endsection
