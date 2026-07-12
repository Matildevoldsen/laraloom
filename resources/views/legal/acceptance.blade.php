<x-layouts::auth :title="__('Review the Laraloom terms')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('One last step')"
            :description="__('Confirm your eligibility and review the rules before continuing.')"
        />

        <form method="POST" action="{{ route('legal.acceptance.store') }}" class="flex flex-col gap-5">
            @csrf

            <div class="flex min-h-11 items-start gap-3 py-1">
                <flux:checkbox id="terms_accepted" name="terms_accepted" value="1" :checked="old('terms_accepted')" aria-label="Agree to the Terms of Service" class="mt-0.5 shrink-0" />
                <div class="min-w-0 text-sm leading-6">
                    <label for="terms_accepted" class="text-zinc-800 dark:text-zinc-200">
                        I have read and agree to the Terms of Service (effective {{ $effectiveDate }}).
                    </label>
                    <div>
                        <flux:link href="{{ route('legal.terms') }}" target="_blank" rel="noreferrer">
                            Read the Terms of Service<span class="sr-only"> (opens in a new tab)</span>
                        </flux:link>
                    </div>
                    <flux:error name="terms_accepted" />
                </div>
            </div>

            <div class="flex min-h-11 items-start gap-3 py-1">
                <flux:checkbox id="age_confirmed" name="age_confirmed" value="1" :checked="old('age_confirmed')" aria-label="Confirm that I am at least {{ $minimumAge }} years old" class="mt-0.5 shrink-0" />
                <div class="min-w-0 text-sm leading-6">
                    <label for="age_confirmed" class="text-zinc-800 dark:text-zinc-200">I confirm that I am at least {{ $minimumAge }} years old.</label>
                    <flux:error name="age_confirmed" />
                </div>
            </div>

            <div class="rounded-xl bg-zinc-100 px-4 py-3 text-sm leading-6 text-zinc-600 dark:bg-white/[.055] dark:text-zinc-400">
                Laraloom uses your personal information to create and operate your account as described in the
                <flux:link href="{{ route('legal.privacy') }}" target="_blank" rel="noreferrer">Privacy Policy<span class="sr-only"> (opens in a new tab)</span></flux:link>.
                The Privacy Policy is a notice, not a request for consent.
            </div>

            <p class="text-xs leading-5 text-zinc-500 dark:text-zinc-400">
                Terms version {{ $termsVersion }}. If the Terms change materially, Laraloom will ask you to review the new version.
            </p>

            <flux:button type="submit" variant="primary" class="w-full" data-test="accept-legal-terms">
                {{ __('Agree and continue') }}
            </flux:button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="text-center">
            @csrf
            <flux:button type="submit" variant="ghost" size="sm">{{ __('Not now — log out') }}</flux:button>
        </form>
    </div>
</x-layouts::auth>
