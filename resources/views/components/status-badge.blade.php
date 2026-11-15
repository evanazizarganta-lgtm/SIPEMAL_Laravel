@props(['status', 'late' => false])
@php
$map = [
  'Menunggu'     => ['badge-warn', 'clock'],
  'Disetujui'    => ['badge-ok', 'check-circle'],
  'Ditolak'      => ['badge-bad', 'x-circle'],
  'Dikembalikan' => ['badge-done', 'undo'],
];
[$cls, $ico] = $map[$status] ?? ['badge-neutral', 'info'];
@endphp
<span {{ $attributes->merge(['class' => "badge $cls"]) }}><x-icon :name="$ico"/>{{ $status }}</span>@if($late)<span class="badge badge-bad" title="Melewati tanggal rencana kembali"><x-icon name="alert"/>Terlambat</span>@endif
