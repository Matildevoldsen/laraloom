<?php

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component
{
    private const int PAGE_SIZE = 20;

    #[Url(as: 'filter', except: 'all')]
    public string $filter = 'all';

    public int $limit = self::PAGE_SIZE;

    public function mount(): void
    {
        abort_unless(in_array($this->filter, ['all', 'unread'], true), 404);
    }

    /** @return array<string, string> */
    public function getListeners(): array
    {
        return [
            "echo-private:sourcefolk.users.{$this->user()->id}.notifications,.notification.created" => 'notificationCreated',
        ];
    }

    public function showAll(): void
    {
        $this->filter = 'all';
        $this->limit = self::PAGE_SIZE;
        $this->forgetResults();
    }

    public function showUnread(): void
    {
        $this->filter = 'unread';
        $this->limit = self::PAGE_SIZE;
        $this->forgetResults();
    }

    public function loadMore(): void
    {
        if ($this->hasMore) {
            $this->limit += self::PAGE_SIZE;
            $this->forgetResults();
        }
    }

    public function markAllAsRead(): void
    {
        $this->user()->unreadNotifications()->update(['read_at' => now()]);
        $this->forgetResults();
        $this->dispatch('notifications-read');
    }

    /** @param array{user_id: int, notification_id: string, occurred_at: string} $event */
    public function notificationCreated(array $event): void
    {
        abort_unless($event['user_id'] === $this->user()->id, 403);

        $this->forgetResults();
    }

    /** @return Collection<int, DatabaseNotification> */
    #[Computed]
    public function notificationResults(): Collection
    {
        return $this->user()->notifications()
            ->when($this->filter === 'unread', fn ($query) => $query->whereNull('read_at'))
            ->latest()
            ->limit($this->limit + 1)
            ->get();
    }

    #[Computed]
    public function hasMore(): bool
    {
        return $this->notificationResults->count() > $this->limit;
    }

    #[Computed]
    public function unreadCount(): int
    {
        return $this->user()->unreadNotifications()->count();
    }

    private function user(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        return $user;
    }

    private function forgetResults(): void
    {
        unset($this->notificationResults, $this->hasMore, $this->unreadCount);
    }
};
?>

<section class="mx-auto w-full max-w-4xl">
    <header class="mb-6 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[.18em] text-[#ff7693]">Your activity</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-[-.04em] sm:text-4xl">Notifications</h1>
            <p class="mt-2 text-sm text-zinc-500">Follows, mentions, likes, comments, replies, and reposts from the community.</p>
        </div>

        @if ($this->unreadCount > 0)
            <flux:button wire:click="markAllAsRead" wire:loading.attr="disabled" icon="check" class="rounded-full!">
                Mark all as read
            </flux:button>
        @endif
    </header>

    <nav class="mb-4 flex items-center gap-1 rounded-full bg-zinc-100 p-1 dark:bg-white/[.045]" aria-label="Notification filters">
        <button
            type="button"
            wire:click="showAll"
            @class([
                'flex-1 rounded-full px-4 py-2 text-center text-sm font-medium transition',
                'bg-white text-zinc-950 shadow-sm dark:bg-white/10 dark:text-white' => $filter === 'all',
                'text-zinc-500 hover:text-zinc-950 dark:hover:text-white' => $filter !== 'all',
            ])
        >
            All
        </button>
        <button
            type="button"
            wire:click="showUnread"
            @class([
                'flex-1 rounded-full px-4 py-2 text-center text-sm font-medium transition',
                'bg-white text-zinc-950 shadow-sm dark:bg-white/10 dark:text-white' => $filter === 'unread',
                'text-zinc-500 hover:text-zinc-950 dark:hover:text-white' => $filter !== 'unread',
            ])
        >
            Unread @if ($this->unreadCount > 0)<span class="ml-1 text-[#e73562]">{{ $this->unreadCount }}</span>@endif
        </button>
    </nav>

    <div class="overflow-hidden rounded-[1.75rem] border border-zinc-200/90 bg-white shadow-xl shadow-zinc-950/5 dark:border-white/10 dark:bg-[#101218] dark:shadow-black/20">
        @forelse ($this->notificationResults->take($limit) as $notification)
            @php($data = $notification->data)
            @php($iconClasses = match ($data['kind']) {
                'follow' => 'bg-violet-500 text-white',
                'mention' => 'bg-[#ff4d73] text-white',
                'reaction' => 'bg-[#ff4d73] text-white',
                'comment', 'reply' => 'bg-sky-500 text-white',
                'repost' => 'bg-emerald-500 text-white',
                default => 'bg-zinc-500 text-white',
            })

            <form
                method="POST"
                action="{{ route('notifications.read', $notification->id) }}"
                wire:key="notification-{{ $notification->id }}"
                class="border-b border-zinc-100 last:border-0 dark:border-white/6"
            >
                @csrf
                <button
                    type="submit"
                    @class([
                        'group flex w-full items-start gap-4 px-4 py-4 text-left transition hover:bg-zinc-50 dark:hover:bg-white/[.04] sm:px-6 sm:py-5',
                        'bg-[#ff4d73]/[.035] dark:bg-[#ff4d73]/[.045]' => $notification->unread(),
                    ])
                >
                    <span class="relative shrink-0">
                        <img class="size-12 rounded-full object-cover ring-1 ring-zinc-200 dark:ring-white/10" src="{{ $data['actor_avatar_url'] }}" alt="" />
                        <span class="absolute -bottom-1 -right-1 grid size-6 place-items-center rounded-full ring-2 ring-white dark:ring-[#101218] {{ $iconClasses }}">
                            <flux:icon :name="$data['icon']" class="size-3.5" />
                        </span>
                    </span>

                    <span class="min-w-0 flex-1">
                        <span class="block text-sm leading-6 text-zinc-600 dark:text-zinc-300">
                            <strong class="font-semibold text-zinc-950 dark:text-white">{{ $data['actor_name'] }}</strong>
                            {{ $data['verb'] }}
                            @if (filled($data['post_title']))
                                <span class="font-medium text-zinc-800 dark:text-zinc-200">“{{ str($data['post_title'])->limit(80) }}”</span>
                            @endif
                        </span>

                        @if (filled($data['comment_excerpt']))
                            <span class="mt-1 block truncate text-xs text-zinc-500">{{ $data['comment_excerpt'] }}</span>
                        @endif

                        <span class="mt-1.5 flex items-center gap-2 text-[11px] text-zinc-500">
                            <time datetime="{{ $notification->created_at?->toIso8601String() }}">
                                {{ $notification->created_at?->diffForHumans() }}
                            </time>
                            <span aria-label="Notification status">{{ $notification->unread() ? 'Unread' : 'Read' }}</span>
                        </span>
                    </span>

                    @if ($notification->unread())
                        <span class="mt-2 size-2.5 shrink-0 rounded-full bg-[#ff4d73] shadow-[0_0_12px_rgba(255,77,115,.55)]" aria-label="Unread"></span>
                    @else
                        <flux:icon name="chevron-right" class="mt-1 size-4 shrink-0 text-zinc-400 opacity-0 transition group-hover:opacity-100" />
                    @endif
                </button>
            </form>
        @empty
            <div class="grid min-h-80 place-items-center px-8 py-16 text-center">
                <div>
                    <span class="mx-auto grid size-16 place-items-center rounded-full bg-zinc-100 text-[#ff4d73] dark:bg-white/5">
                        <flux:icon name="bell" class="size-7" />
                    </span>
                    <h2 class="mt-5 font-semibold text-zinc-800 dark:text-zinc-200">
                        {{ $filter === 'unread' ? 'You’re all caught up' : 'Nothing here yet' }}
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-zinc-500">
                        {{ $filter === 'unread' ? 'You have no unread notifications.' : 'When people interact with you or your posts, you’ll see it here.' }}
                    </p>
                </div>
            </div>
        @endforelse
    </div>

    @if ($this->hasMore)
        <div wire:intersect="loadMore" class="flex h-24 items-center justify-center" aria-label="Loading more notifications">
            <flux:icon wire:loading.class="animate-spin" name="arrow-path" class="size-5 text-[#ff4d73]" />
        </div>
    @elseif ($this->notificationResults->isNotEmpty())
        <p class="py-8 text-center text-xs text-zinc-500">You’re all caught up.</p>
    @endif
</section>
