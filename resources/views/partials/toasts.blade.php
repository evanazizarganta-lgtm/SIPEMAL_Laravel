<div class="toasts" role="region" aria-live="polite" aria-label="Pemberitahuan">
    @if(session('success'))
        <div class="toast" role="status"><x-icon name="check-circle" class="t-ico"/><div class="t-body">{{ session('success') }}</div><button type="button" class="t-x" aria-label="Tutup"><x-icon name="x" class="icon-sm"/></button></div>
    @endif
    @if(session('error'))
        <div class="toast err" role="alert"><x-icon name="x-circle" class="t-ico"/><div class="t-body">{{ session('error') }}</div><button type="button" class="t-x" aria-label="Tutup"><x-icon name="x" class="icon-sm"/></button></div>
    @endif
    @if(session('status'))
        <div class="toast warn" role="status"><x-icon name="info" class="t-ico"/><div class="t-body">{{ session('status') }}</div><button type="button" class="t-x" aria-label="Tutup"><x-icon name="x" class="icon-sm"/></button></div>
    @endif
</div>
