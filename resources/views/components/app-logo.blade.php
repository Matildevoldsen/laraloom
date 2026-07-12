@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="Laraloom" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center">
            <span class="loom-mark scale-75"><i></i><i></i><i></i></span>
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="Laraloom" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center">
            <span class="loom-mark scale-75"><i></i><i></i><i></i></span>
        </x-slot>
    </flux:brand>
@endif
