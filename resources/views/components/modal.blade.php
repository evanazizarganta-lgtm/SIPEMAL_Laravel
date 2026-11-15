@props(['id', 'title', 'subtitle' => null, 'size' => '', 'open' => false, 'icon' => 'info', 'tone' => 'ok'])
<div class="modal {{ $size }}" id="{{ $id }}" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title" aria-hidden="true" @if($open) data-open-on-load @endif>
    <div class="modal-backdrop" data-modal-close></div>
    <div class="modal-box">
        <button type="button" class="btn btn-ghost btn-icon btn-sm modal-close" data-modal-close aria-label="Tutup"><x-icon name="x"/></button>
        <div class="modal-ico {{ $tone }}"><x-icon :name="$icon" class="icon-lg"/></div>
        <h3 id="{{ $id }}-title">{{ $title }}</h3>
        @if($subtitle)<p class="msg" style="margin-bottom:18px">{{ $subtitle }}</p>@endif
        {{ $slot }}
    </div>
</div>
