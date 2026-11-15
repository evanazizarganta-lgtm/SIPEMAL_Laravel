@props(['name' => ''])
@php
$parts = preg_split('/\s+/', trim($name)) ?: [];
$ini = mb_strtoupper(mb_substr($parts[0] ?? '?', 0, 1) . (isset($parts[1]) ? mb_substr($parts[1], 0, 1) : ''));
@endphp
<span {{ $attributes->merge(['class' => 'avatar']) }} aria-hidden="true">{{ $ini }}</span>
