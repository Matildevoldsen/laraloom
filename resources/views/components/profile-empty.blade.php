@props(['icon', 'message'])

<div {{ $attributes->merge(['class' => 'loom-empty sm:col-span-2']) }}>
    <span>{{ $icon }}</span>
    <h2>Quiet here—for now</h2>
    <p>{{ $message }}</p>
</div>
