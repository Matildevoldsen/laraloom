@auth
    @if ($errors->hasAny(['kind', 'title', 'body', 'url', 'tags']))
        <span x-data x-init="$nextTick(() => $dispatch('modal-show', { name: 'community-composer' }))"></span>
    @endif

    <flux:modal name="community-composer" variant="floating" class="w-[calc(100vw-1.5rem)] max-w-2xl p-0!" scroll="body">
        <form
            method="POST"
            action="{{ route('posts.store') }}"
            x-data="{ showDetails: {{ $errors->hasAny(['kind', 'title', 'url', 'tags']) || old('kind', 'note') !== 'note' ? 'true' : 'false' }} }"
        >
            @csrf

            <div class="flex items-start gap-3 px-5 pb-4 pt-6 sm:gap-4 sm:px-6">
                <span class="loom-avatar mt-1 shrink-0">{{ auth()->user()->initials() }}</span>

                <div class="min-w-0 flex-1">
                    <flux:textarea
                        name="body"
                        :value="old('body')"
                        placeholder="What’s happening in Laravel?"
                        rows="5"
                        resize="none"
                        class="[&_textarea]:min-h-36 [&_textarea]:border-0 [&_textarea]:bg-transparent [&_textarea]:px-0 [&_textarea]:text-lg [&_textarea]:shadow-none [&_textarea]:ring-0 focus-within:[&_textarea]:ring-0"
                    />
                    @error('body')<flux:error name="body" />@enderror

                    <div class="mt-3 flex items-center gap-2 text-xs text-[#ff7693]">
                        <flux:icon name="globe-alt" class="size-4" />
                        <span>Visible to the Laravel community</span>
                    </div>
                </div>
            </div>

            <div x-cloak x-show="showDetails" x-collapse class="border-t border-zinc-200/80 px-5 py-4 dark:border-white/8 sm:px-6">
                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:select name="kind" label="Post type">
                        @foreach (App\PostKind::cases() as $kind)
                            <flux:select.option :value="$kind->value" :selected="old('kind', 'note') === $kind->value">{{ str($kind->value)->headline() }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:input name="title" label="Headline" :value="old('title')" maxlength="180" placeholder="Optional for notes" />
                    <flux:input name="url" label="Original link" type="url" :value="old('url')" placeholder="https://…" icon="link" />
                    <flux:input name="tags" label="Tags" :value="old('tags')" maxlength="240" placeholder="Laravel, Livewire, AI" icon="tag" />
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 border-t border-zinc-200/80 px-5 py-3 dark:border-white/8 sm:px-6">
                <flux:button type="button" variant="ghost" size="sm" icon="plus" class="rounded-full!" x-on:click="showDetails = ! showDetails">
                    <span x-text="showDetails ? 'Hide details' : 'Add details'">Add details</span>
                </flux:button>
                <flux:button type="submit" variant="primary" icon="paper-airplane" class="rounded-full! bg-[#ff4d73]! px-5! hover:bg-[#ff6382]!">Post</flux:button>
            </div>
        </form>
    </flux:modal>
@endauth
