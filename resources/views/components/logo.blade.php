@props(['size' => 40])
@php
    $px = (int) $size;
    $font = max(11, (int) round($px * 0.38));
@endphp
@if (file_exists(public_path('images/logo.png')))
    <img
        src="{{ asset('images/logo.png') }}"
        alt="Logo SMP Negeri 4 Cepu"
        width="{{ $px }}"
        height="{{ $px }}"
        {{ $attributes->merge(['class' => 'inline-block shrink-0 object-contain']) }}
        style="width: {{ $px }}px; height: {{ $px }}px;"
    />
@else
    <span
        aria-hidden="true"
        {{ $attributes->merge(['class' => 'inline-flex shrink-0 items-center justify-center rounded-full bg-sun font-extrabold text-navy']) }}
        style="width: {{ $px }}px; height: {{ $px }}px; font-size: {{ $font }}px;"
    >4C</span>
@endif
