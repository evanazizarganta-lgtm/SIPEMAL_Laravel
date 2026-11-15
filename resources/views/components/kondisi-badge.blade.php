@props(['kondisi'])
@php
$map = ['Baik' => ['badge-ok', 'shield'], 'Rusak Ringan' => ['badge-warn', 'alert'], 'Rusak Berat' => ['badge-bad', 'x-circle']];
[$cls, $ico] = $map[$kondisi] ?? ['badge-neutral', 'info'];
@endphp
<span {{ $attributes->merge(['class' => "badge $cls"]) }}><x-icon :name="$ico"/>{{ $kondisi }}</span>
