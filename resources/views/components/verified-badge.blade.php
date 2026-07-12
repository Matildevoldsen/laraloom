@props(['user', 'size' => 'sm'])

@if ($user->is_verified)
    <span
        {{ $attributes->class([
            'inline-flex shrink-0 items-center justify-center rounded-full bg-sky-500 text-white shadow-sm shadow-sky-500/20',
            'size-4' => $size === 'sm',
            'size-5' => $size === 'md',
        ]) }}
        title="Verified community member"
    >
        <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" class="size-[70%]" stroke="currentColor" stroke-width="2.4">
            <path d="m5.5 10 2.8 2.8 6.2-6.1" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="sr-only">Verified community member</span>
    </span>
@endif
