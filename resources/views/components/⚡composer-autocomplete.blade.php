<?php

use App\Actions\SearchComposerSuggestionsAction;
use App\ComposerSuggestionType;
use App\Data\ComposerSuggestion;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Renderless;
use Livewire\Component;

new class extends Component
{
    private SearchComposerSuggestionsAction $searchSuggestions;

    public function boot(SearchComposerSuggestionsAction $searchSuggestions): void
    {
        $this->searchSuggestions = $searchSuggestions;
    }

    /**
     * @return list<array{
     *     type: string,
     *     id: int,
     *     label: string,
     *     description: string,
     *     replacement: string,
     *     image: array{url: string, alt: string}|null,
     *     verified: bool
     * }>
     */
    #[Renderless]
    public function suggest(string $trigger, string $query): array
    {
        $user = Auth::user();
        abort_unless($user instanceof User, 401);
        Gate::forUser($user)->authorize('create', Post::class);

        $type = match ($trigger) {
            '@' => ComposerSuggestionType::Mention,
            '#' => ComposerSuggestionType::Hashtag,
            default => null,
        };

        abort_if($type === null, 422);

        $pattern = $type === ComposerSuggestionType::Mention
            ? '/^[A-Za-z0-9_-]{0,30}$/'
            : '/^[\pL\pN_]{0,100}$/u';
        abort_unless(preg_match($pattern, $query) === 1, 422);

        $rateLimitKey = 'composer-suggestions:'.$user->getAuthIdentifier();
        abort_if(RateLimiter::tooManyAttempts($rateLimitKey, 180), 429);
        RateLimiter::hit($rateLimitKey, 60);

        return array_map(
            static fn (ComposerSuggestion $suggestion): array => $suggestion->toArray(),
            $this->searchSuggestions->execute($user, $type, $query),
        );
    }
};
?>

<div x-data="composerAutocomplete($wire)" class="contents">
    <div
        x-ref="menu"
        popover="manual"
        role="listbox"
        aria-label="Composer suggestions"
        x-bind:aria-busy="loading"
        class="fixed m-0 max-h-72 w-80 overflow-y-auto rounded-2xl border border-zinc-200 bg-white p-1.5 shadow-2xl shadow-zinc-950/15 backdrop:bg-transparent dark:border-white/10 dark:bg-[#17191f] dark:shadow-black/40"
    >
        <div x-show="loading" class="flex items-center gap-2 px-3 py-3 text-sm text-zinc-500">
            <flux:icon name="arrow-path" class="size-4 animate-spin" />
            <span>Finding suggestions…</span>
        </div>

        <p x-show="! loading && failed" role="status" class="px-3 py-3 text-sm text-zinc-500">
            Couldn’t load suggestions. Keep typing to retry.
        </p>

        <p x-show="! loading && ! failed && suggestions.length === 0" class="px-3 py-3 text-sm text-zinc-500">
            No matching suggestions
        </p>

        <template x-for="(suggestion, index) in suggestions" :key="`${suggestion.type}-${suggestion.id}`">
            <button
                type="button"
                role="option"
                x-bind:id="optionId(index)"
                x-bind:aria-selected="activeIndex === index"
                x-on:mouseenter="activeIndex = index"
                x-on:mousedown.prevent
                x-on:click="choose(index)"
                class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left transition hover:bg-zinc-100 aria-selected:bg-[#ff4d73]/10 dark:hover:bg-white/5 dark:aria-selected:bg-[#ff4d73]/15"
            >
                <template x-if="suggestion.image">
                    <img class="size-10 shrink-0 rounded-full object-cover ring-1 ring-zinc-200 dark:ring-white/10" x-bind:src="suggestion.image.url" x-bind:alt="suggestion.image.alt" />
                </template>
                <template x-if="! suggestion.image">
                    <span class="grid size-10 shrink-0 place-items-center rounded-full bg-[#ff4d73]/10 text-lg font-semibold text-[#d92855] dark:text-[#ff7693]">#</span>
                </template>

                <span class="min-w-0 flex-1">
                    <span class="flex items-center gap-1.5 truncate text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                        <span class="truncate" x-text="suggestion.label"></span>
                        <span x-show="suggestion.verified" class="grid size-4 shrink-0 place-items-center rounded-full bg-sky-500 text-white" aria-label="Verified community member">
                            <flux:icon name="check" class="size-2.5" />
                        </span>
                    </span>
                    <span class="mt-0.5 block truncate text-xs text-zinc-500" x-text="suggestion.description"></span>
                </span>
            </button>
        </template>
    </div>
</div>
