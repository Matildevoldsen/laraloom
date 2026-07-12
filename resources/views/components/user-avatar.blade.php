@props([
    'user',
    'size' => 'size-11',
    'alt' => '',
])

@php($hasAvatar = (filled($user->avatar_disk) && filled($user->avatar_path)) || filled($user->avatar_url))

@if ($hasAvatar)
    <img
        {{ $attributes->class([$size, 'shrink-0 rounded-full object-cover ring-1 ring-zinc-200 dark:ring-white/10']) }}
        src="{{ $user->avatarUrl() }}"
        alt="{{ $alt }}"
    />
@else
    <span
        {{ $attributes->class([$size, 'grid shrink-0 place-items-center rounded-full bg-gradient-to-br from-[#ff4d73] to-[#8b5cf6] font-semibold text-white shadow-sm ring-1 ring-white/20']) }}
        @if ($alt !== '') role="img" aria-label="{{ $alt }}" @else aria-hidden="true" @endif
    >
        {{ $user->initials() }}
    </span>
@endif
