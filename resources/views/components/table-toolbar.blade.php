@props(['placeholder' => 'Cari data…'])
<div class="toolbar">
    <div class="search">
        <x-icon name="search"/>
        <label class="sr-only" for="tbl-search-{{ $attributes->get('key', 'x') }}">Cari</label>
        <input id="tbl-search-{{ $attributes->get('key', 'x') }}" type="search" data-table-search placeholder="{{ $placeholder }}" autocomplete="off">
    </div>
    {{ $slot }}
</div>
