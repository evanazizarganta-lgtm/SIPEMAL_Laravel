@props(['action', 'message', 'title' => 'Hapus data?', 'name' => '', 'iconOnly' => false])
<form method="POST" action="{{ $action }}" class="inline-form" data-confirm="{{ $message }}" data-confirm-title="{{ $title }}" data-confirm-label="Ya, hapus">
    @csrf @method('DELETE')
    <button type="submit" class="btn btn-danger-soft btn-sm {{ $iconOnly ? 'btn-icon' : '' }}" title="Hapus {{ $name }}" aria-label="Hapus {{ $name }}">
        <x-icon name="trash"/>@unless($iconOnly)<span>Hapus</span>@endunless
    </button>
</form>
