<x-layouts::auth :title="__('Log in')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Welcome')" :description="__('One secure account for the Laravel community.')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <div class="rounded-[1.75rem] border border-zinc-200 bg-zinc-50/80 p-5 shadow-sm dark:border-white/10 dark:bg-white/[.035] dark:shadow-none">
            <div class="flex items-start gap-3">
                <div class="grid size-11 shrink-0 place-items-center rounded-2xl bg-zinc-950 text-white dark:bg-white dark:text-zinc-950">
                    <flux:icon.code-bracket class="size-5" />
                </div>
                <div class="min-w-0">
                    <p class="font-semibold text-zinc-950 dark:text-white">{{ __('Continue with GitHub') }}</p>
                    <p class="mt-1 text-sm leading-5 text-zinc-500 dark:text-zinc-400">
                        {{ __('Sign in or create your profile using your verified GitHub account.') }}
                    </p>
                </div>
            </div>

            <flux:button
                :href="route('auth.github.redirect')"
                variant="primary"
                icon="code-bracket"
                class="mt-5 min-h-12 w-full"
                data-test="github-login-button"
            >
                {{ __('Continue with GitHub') }}
            </flux:button>

            @error('github')
                <p class="text-center text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror

            <div class="mt-5 border-t border-zinc-200 pt-4 text-xs leading-5 text-zinc-500 dark:border-white/10 dark:text-zinc-400">
                {{ __('We request your public profile and verified primary email. We never receive your GitHub password.') }}
            </div>
        </div>

        <p class="text-center text-xs leading-5 text-zinc-500 dark:text-zinc-400">
            {{ __('By continuing, you agree to our') }}
            <flux:link :href="route('legal.terms')">{{ __('Terms') }}</flux:link>
            {{ __('and acknowledge our') }}
            <flux:link :href="route('legal.privacy')">{{ __('Privacy Policy') }}</flux:link>.
        </p>
    </div>
</x-layouts::auth>
