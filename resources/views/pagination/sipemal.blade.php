@if ($paginator->hasPages())
<nav class="paginator" role="navigation" aria-label="Navigasi halaman">
    <span class="small muted fw-6">
        @if(method_exists($paginator, 'total')) Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} data
        @else Halaman {{ $paginator->currentPage() }} @endif
    </span>
    <div class="pager">
        @if ($paginator->onFirstPage())
            <span class="pg disabled" aria-disabled="true" aria-label="Halaman sebelumnya">‹</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya">‹</a>
        @endif

        @foreach (($elements ?? []) as $element)
            @if (is_string($element))
                <span class="pg disabled" aria-hidden="true">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="pg active" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" aria-label="Ke halaman {{ $page }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya">›</a>
        @else
            <span class="pg disabled" aria-disabled="true" aria-label="Halaman berikutnya">›</span>
        @endif
    </div>
</nav>
@endif
