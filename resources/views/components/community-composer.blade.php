@auth
    @if ($errors->hasAny(['kind', 'title', 'body', 'url', 'tags', 'attachments', 'attachments.*']))
        <span x-data x-init="$nextTick(() => $dispatch('modal-show', { name: 'community-composer' }))"></span>
    @endif

    <flux:modal name="community-composer" variant="floating" :closable="false" class="w-[calc(100vw-1.5rem)] max-w-2xl overflow-hidden! p-0! shadow-2xl!" scroll="body">
        <form
            method="POST"
            action="{{ route('posts.store') }}"
            enctype="multipart/form-data"
            x-data="composerForm({ body: @js(old('body', '')), showDetails: {{ $errors->hasAny(['kind', 'title', 'url', 'tags', 'attachments', 'attachments.*']) || old('kind', 'note') !== 'note' ? 'true' : 'false' }} })"
            x-on:submit="submitting = true"
        >
            <div class="absolute right-3 top-3 z-10">
                <flux:modal.close>
                    <flux:button type="button" variant="ghost" icon="x-mark" aria-label="Close composer" class="size-10! rounded-full! text-zinc-500! hover:bg-zinc-100! hover:text-zinc-900! dark:hover:bg-white/10! dark:hover:text-white!" />
                </flux:modal.close>
            </div>

            @csrf

            <div class="flex items-start gap-3 px-5 pb-4 pt-6 sm:gap-4 sm:px-6">
                <img class="loom-avatar mt-1 shrink-0" src="{{ auth()->user()->avatarUrl() }}" alt="" />

                <div class="min-w-0 flex-1">
                    <div x-data="composerHighlighter" data-composer-editor class="relative">
                        <div
                            x-ref="highlighter"
                            aria-hidden="true"
                            class="composer-highlighter absolute overflow-hidden whitespace-pre-wrap break-words text-lg text-zinc-700 dark:text-zinc-200"
                        ></div>
                        <flux:textarea
                            name="body"
                            :value="old('body')"
                            x-model="body"
                            x-on:paste="pasteAttachments($event)"
                            data-composer-textarea
                            autofocus
                            placeholder="What’s happening in Laravel?"
                            rows="auto"
                            resize="none"
                            class="relative z-10 min-h-32! border-transparent! bg-transparent! px-0! text-lg! placeholder:text-zinc-400! dark:border-transparent! dark:bg-transparent!"
                        />
                    </div>
                    <livewire:composer-autocomplete />
                    @error('body')<flux:error name="body" />@enderror
                    <flux:error name="attachments" />

                    <p class="sr-only" aria-live="polite" aria-atomic="true" x-text="attachments.length === 0 ? 'No attachments selected' : `${attachments.length} ${attachments.length === 1 ? 'attachment' : 'attachments'} selected`"></p>

                    <div x-cloak x-show="attachmentItems.length" class="mt-3 flex flex-wrap gap-2">
                        <template x-for="attachment in attachmentItems" :key="attachment.id">
                            <div class="group relative size-20 overflow-hidden rounded-xl border border-zinc-200 bg-zinc-100 dark:border-white/10 dark:bg-white/6">
                                <template x-if="attachment.url">
                                    <img x-bind:src="attachment.url" x-bind:alt="attachment.name" class="size-full object-cover" />
                                </template>
                                <template x-if="! attachment.url">
                                    <span class="grid size-full place-items-center px-2 text-center text-xs text-zinc-500" x-text="attachment.name"></span>
                                </template>
                                <button type="button" x-on:click="removeAttachment(attachment.index)" x-bind:aria-label="`Remove ${attachment.name}`" class="absolute right-1 top-1 grid size-7 place-items-center rounded-full bg-zinc-950/75 text-white shadow-sm transition hover:bg-zinc-950 focus:outline-none focus:ring-2 focus:ring-white">
                                    <flux:icon name="x-mark" class="size-4" />
                                </button>
                            </div>
                        </template>
                    </div>

                    <p x-cloak x-show="attachmentError" x-text="attachmentError" role="alert" class="mt-2 text-sm text-red-600 dark:text-red-400"></p>

                    <div class="mt-3 flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                        <flux:icon name="globe-alt" class="size-4" />
                        <span>Visible to the Laravel community</span>
                    </div>
                </div>
            </div>

            <div x-cloak x-show="showDetails" x-collapse class="px-5 pb-4 sm:px-6">
                <div class="grid gap-4 rounded-2xl bg-zinc-100/80 p-4 dark:bg-white/6 sm:grid-cols-2">
                    <flux:select name="kind" label="Type" badge="Post by default">
                        @foreach (App\PostKind::cases() as $kind)
                            <flux:select.option :value="$kind->value" :selected="old('kind', 'note') === $kind->value">{{ $kind === App\PostKind::Note ? 'Post' : str($kind->value)->headline() }}</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:input name="title" label="Title" badge="Optional" variant="filled" :value="old('title')" maxlength="180" placeholder="Add a headline if useful" />
                    <flux:input name="url" label="Link" badge="Optional" variant="filled" type="url" :value="old('url')" placeholder="https://…" />
                    <flux:input name="tags" label="Topics" badge="Optional" variant="filled" :value="old('tags')" maxlength="240" placeholder="Laravel, Livewire, AI" />
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 px-5 py-3 sm:px-6">
                <div class="flex min-w-0 items-center gap-2">
                    <label class="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-full px-3 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 hover:text-zinc-950 focus-within:ring-2 focus-within:ring-[#ff4d73] dark:text-zinc-300 dark:hover:bg-white/10 dark:hover:text-white">
                        <flux:icon name="photo" class="size-5" />
                        <span class="hidden sm:inline" x-text="attachments.length ? `${attachments.length} selected` : 'Media'">Media</span>
                        <input x-ref="attachments" type="file" name="attachments[]" accept="image/jpeg,image/png,image/webp,image/gif,image/heic,image/heif,video/mp4,video/quicktime,video/webm" multiple class="sr-only" x-on:change="selectAttachments($event)" />
                    </label>
                    <flux:button type="button" variant="ghost" size="sm" icon="adjustments-horizontal" class="min-h-11! rounded-full!" x-on:click="showDetails = ! showDetails">
                        <span x-text="showDetails ? 'Hide details' : 'Details'">Details</span>
                    </flux:button>
                </div>
                <flux:button type="submit" variant="primary" :loading="false" x-bind:disabled="submitting || (! body.trim() && attachments.length === 0)" class="rounded-full! bg-[#ff4d73]! px-5! hover:bg-[#ff6382]! disabled:bg-zinc-300! disabled:text-zinc-500! dark:disabled:bg-white/10! dark:disabled:text-zinc-500!">
                    <span x-show="! submitting" class="contents">
                        <flux:icon name="paper-airplane" class="size-4" />
                        <span>Post</span>
                    </span>
                    <span x-cloak x-show="submitting" class="contents">
                        <flux:icon name="loading" class="size-4" />
                        <span>Posting…</span>
                    </span>
                </flux:button>
            </div>
        </form>
    </flux:modal>
@endauth
