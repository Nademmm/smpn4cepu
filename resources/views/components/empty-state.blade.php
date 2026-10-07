@props(['title', 'text' => null])
<div {{ $attributes->merge(['class' => 'card text-center py-10']) }}>
    <p class="text-base font-extrabold text-navy">{{ $title }}</p>
    @if ($text)
        <p class="mx-auto mt-2 max-w-md text-sm text-ink-soft">{{ $text }}</p>
    @endif
    {{ $slot }}
</div>
