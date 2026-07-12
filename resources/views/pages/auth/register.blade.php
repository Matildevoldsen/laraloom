<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Join Sourcefolk')" :description="__('GitHub is the quickest way to create your community profile.')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <div class="rounded-xl bg-zinc-100 px-4 py-3 text-sm leading-6 text-zinc-600 dark:bg-white/[.055] dark:text-zinc-400">
            Sourcefolk is for people aged 18 or over. We use your identity, profile, and security information to create and protect your account. Public profile information is visible to others. Read the
            <flux:link :href="route('legal.privacy')" target="_blank" rel="noreferrer">Privacy Policy</flux:link>;
            you will review and agree to the <flux:link :href="route('legal.terms')" target="_blank" rel="noreferrer">Terms</flux:link> before using member features.
        </div>

        <div class="grid gap-2">
            <flux:button
                :href="route('auth.github.redirect')"
                variant="primary"
                icon="code-bracket"
                class="min-h-11 w-full"
                data-test="github-register-button"
            >
                {{ __('Continue with GitHub') }}
            </flux:button>

            @error('github')
                <p class="text-center text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="relative">
            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                <div class="w-full border-t border-zinc-200 dark:border-zinc-700"></div>
            </div>
            <div class="relative flex justify-center text-xs uppercase">
                <span class="bg-white px-2 text-zinc-500 dark:bg-zinc-900 dark:text-zinc-400">
                    {{ __('Or create an account with email') }}
                </span>
            </div>
        </div>

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Name -->
            <flux:input
                name="name"
                :label="__('Name')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('Full name')"
            />

            <flux:input
                name="username"
                :label="__('Username')"
                :value="old('username')"
                type="text"
                required
                autocomplete="username"
                placeholder="taylorotwell"
                description="Your public profile will live at /@username."
            />

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Password')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Confirm password')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="register-user-button">
                    {{ __('Continue') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
