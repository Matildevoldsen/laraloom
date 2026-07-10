<div class="space-y-4">
    <div class="relative flex items-center justify-center w-full">
        <div class="absolute inset-0 w-full h-px top-1/2 bg-stone-200 dark:bg-stone-600"></div>
        <span class="relative px-2 text-sm bg-white dark:bg-stone-800 text-stone-600 dark:text-stone-400">
            {{ __('or, enter the code manually') }}
        </span>
    </div>

    <div
        class="flex items-center space-x-2"
        x-data="{
            copied: false,
            async copy() {
                try {
                    await navigator.clipboard.writeText('{{ $manualSetupKey }}');
                    this.copied = true;
                    setTimeout(() => this.copied = false, 1500);
                } catch (e) {
                    console.warn('Could not copy to clipboard');
                }
            }
        }"
    >
        <div class="flex items-stretch w-full border rounded-xl dark:border-stone-700">
            @empty($manualSetupKey)
                <div class="flex items-center justify-center w-full p-3 bg-stone-100 dark:bg-stone-700">
                    <flux:icon.loading variant="mini"/>
                </div>
            @else
                <input
                    type="text"
                    readonly
                    value="{{ $manualSetupKey }}"
                    class="w-full p-3 bg-transparent outline-none text-stone-900 dark:text-stone-100"
                />

                <button
                    @click="copy()"
                    class="px-3 transition-colors border-l cursor-pointer border-stone-200 dark:border-stone-600"
                >
                    <flux:icon.document-duplicate x-show="!copied" variant="outline"></flux:icon>
                    <flux:icon.check
                        x-show="copied"
                        variant="solid"
                        class="text-green-500"
                    ></flux:icon>
                </button>
            @endempty
        </div>
    </div>
</div>
