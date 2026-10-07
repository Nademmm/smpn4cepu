@props(['type' => 'blue'])
@php
    $class = match ($type) {
        'green' => 'badge-green',
        'orange' => 'badge-orange',
        default => 'badge-blue',
    };
@endphp
<span {{ $attributes->merge(['class' => $class]) }}>{{ $slot }}</span>
