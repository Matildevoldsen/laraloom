<flux:dropdown position="bottom" align="end">
    <button
        type="button"
        class="loom-avatar cursor-pointer transition hover:ring-2 hover:ring-[#ff4d73]/30 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#ff4d73]"
        aria-label="Open account menu"
        title="Account menu"
        data-test="community-user-menu"
    >
        <img class="size-full object-cover" src="{{ auth()->user()->avatarUrl() }}" alt="" />
    </button>

    <flux:menu class="min-w-56">
        <div class="flex items-center gap-3 px-2 py-2">
            <x-user-avatar :user="auth()->user()" size="size-10" />
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ auth()->user()->name }}</p>
                <p class="truncate text-xs text-zinc-500">{{ '@'.auth()->user()->username }}</p>
            </div>
        </div>

        <flux:menu.separator />

        <flux:menu.item :href="route('profiles.show', auth()->user())" icon="user">
            View profile
        </flux:menu.item>
        <flux:menu.item :href="route('profiles.edit', auth()->user())" icon="pencil-square">
            Edit profile
        </flux:menu.item>

        <flux:menu.separator />

        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <flux:menu.item
                as="button"
                type="submit"
                icon="arrow-right-start-on-rectangle"
                class="w-full cursor-pointer"
                data-test="community-logout-button"
            >
                Log out
            </flux:menu.item>
        </form>
    </flux:menu>
</flux:dropdown>
