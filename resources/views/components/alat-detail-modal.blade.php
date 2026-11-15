<div class="modal modal-lg" id="alatDetailModal" role="dialog" aria-modal="true" aria-labelledby="ad-nama" aria-hidden="true">
    <div class="modal-backdrop" data-modal-close></div>
    <div class="modal-box" style="padding:0;overflow:hidden">
        <button type="button" class="btn btn-icon btn-sm modal-close" style="background:rgba(255,255,255,.92);color:var(--ink);z-index:3" data-modal-close aria-label="Tutup"><x-icon name="x"/></button>
        <div class="alat-media skeleton-img" style="border-radius:0"><img data-ad-img src="{{ \App\Models\Alat::gambarFallback() }}" data-fallback="{{ \App\Models\Alat::gambarFallback() }}" alt=""></div>
        <div style="padding:24px 26px 26px">
            <span class="alat-cat" data-ad-kat></span>
            <h3 id="ad-nama" style="font-size:1.4rem;margin:6px 0 8px" data-ad-nama></h3>
            <p class="muted" data-ad-desc style="margin-bottom:18px"></p>
            <div class="kv" style="margin-bottom:20px">
                <div><span>Kode alat</span><b data-ad-kode></b></div>
                <div><span>Kondisi</span><b data-ad-kondisi></b></div>
                <div><span>Stok tersedia</span><b><span data-ad-stok></span> dari <span data-ad-total></span> unit</b></div>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" data-modal-close>Tutup</button>
                <a class="btn" href="#" data-ad-ajukan><x-icon name="send"/>Ajukan peminjaman</a>
            </div>
        </div>
    </div>
</div>
