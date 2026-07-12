<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    public int $unreadCount = 0;

    public function mount(): void
    {
        $this->refreshUnreadCount();
    }

    /** @return array<string, string> */
    public function getListeners(): array
    {
        return [
            "echo-private:sourcefolk.users.{$this->user()->id}.notifications,.notification.created" => 'notificationCreated',
        ];
    }

    /** @param array{user_id: int, notification_id: string, occurred_at: string} $event */
    public function notificationCreated(array $event): void
    {
        abort_unless($event['user_id'] === $this->user()->id, 403);

        $this->unreadCount++;
    }

    #[On('notifications-read')]
    public function refreshUnreadCount(): void
    {
        $this->unreadCount = $this->user()->unreadNotifications()->count();
    }

    private function user(): User
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);

        return $user;
    }
};
?>

<a
    href="{{ route('notifications.index') }}"
    @class([
        'relative grid size-10 place-items-center rounded-full text-zinc-500 transition hover:bg-zinc-100 hover:text-zinc-950 dark:hover:bg-white/5 dark:hover:text-white',
        'bg-zinc-100 text-zinc-950 dark:bg-white/5 dark:text-white' => request()->routeIs('notifications.*'),
    ])
    aria-label="Notifications{{ $unreadCount > 0 ? ' ('.$unreadCount.' unread)' : '' }}"
    title="Notifications"
>
    <flux:icon name="bell" class="size-5" />
    @if ($unreadCount > 0)
        <span class="absolute right-0.5 top-0.5 grid min-h-4 min-w-4 place-items-center rounded-full bg-[#ff4d73] px-1 text-[9px] font-bold leading-none text-white ring-2 ring-white dark:ring-[#090b0f]">
            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
        </span>
    @endif
</a>
