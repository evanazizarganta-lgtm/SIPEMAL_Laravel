/*! SIPEMAL UI — vanilla JS, tanpa dependensi */
(function () {
  'use strict';
  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
  var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- Sidebar (mobile drawer) ---------- */
  var sidebar = $('.sidebar'), overlay = $('.overlay');
  function setSidebar(open) {
    if (!sidebar) return;
    sidebar.classList.toggle('open', open);
    if (overlay) overlay.classList.toggle('show', open);
    $$('[data-sidebar-toggle]').forEach(function (b) { b.setAttribute('aria-expanded', open ? 'true' : 'false'); });
    document.body.classList.toggle('modal-open', open && window.innerWidth <= 1023);
  }
  document.addEventListener('click', function (e) {
    var t = e.target.closest('[data-sidebar-toggle]');
    if (t) { setSidebar(!sidebar.classList.contains('open')); return; }
    if (e.target === overlay) setSidebar(false);
    if (sidebar && sidebar.classList.contains('open') && e.target.closest('.sidebar .nav a')) setSidebar(false);
  });
  window.addEventListener('resize', function () { if (window.innerWidth > 1023) setSidebar(false); });

  /* ---------- Dropdowns ---------- */
  function closeDropdowns(except) {
    $$('.dropdown.open').forEach(function (d) {
      if (d !== except) { d.classList.remove('open'); var b = $('[data-dropdown-toggle]', d); if (b) b.setAttribute('aria-expanded', 'false'); }
    });
  }
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-dropdown-toggle]');
    if (btn) {
      var dd = btn.closest('.dropdown'); var open = !dd.classList.contains('open');
      closeDropdowns(dd); dd.classList.toggle('open', open); btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      return;
    }
    if (!e.target.closest('.dropdown')) closeDropdowns();
  });

  /* ---------- Modal ---------- */
  var lastFocus = null;
  function openModal(m) {
    if (!m) return;
    lastFocus = document.activeElement;
    m.classList.add('open'); m.setAttribute('aria-hidden', 'false');
    document.body.classList.add('modal-open');
    var f = $('[data-autofocus]', m) || $('input:not([type=hidden]), select, textarea, button', m);
    setTimeout(function () { if (f) f.focus(); }, 60);
  }
  function closeModal(m) {
    if (!m) return;
    m.classList.remove('open'); m.setAttribute('aria-hidden', 'true');
    if (!$('.modal.open')) document.body.classList.remove('modal-open');
    if (lastFocus && lastFocus.focus) lastFocus.focus();
  }
  document.addEventListener('click', function (e) {
    var o = e.target.closest('[data-modal-open]');
    if (o) { e.preventDefault(); openModal(document.getElementById(o.getAttribute('data-modal-open'))); return; }
    var c = e.target.closest('[data-modal-close]');
    if (c) { closeModal(c.closest('.modal')); }
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      var m = $$('.modal.open').pop(); if (m) closeModal(m);
      closeDropdowns(); setSidebar(false);
    }
    if (e.key === 'Tab') { // fokus tetap di dalam modal
      var m2 = $$('.modal.open').pop(); if (!m2) return;
      var f = $$('a[href],button:not([disabled]),input:not([type=hidden]):not([disabled]),select,textarea', m2).filter(function (x) { return x.offsetParent !== null; });
      if (!f.length) return;
      var first = f[0], last = f[f.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    }
  });
  $$('.modal[data-open-on-load]').forEach(function (m) { openModal(m); });

  /* ---------- Konfirmasi aksi penting (form[data-confirm]) ---------- */
  var confirmModal = $('#confirmModal'), pendingForm = null;
  document.addEventListener('submit', function (e) {
    var f = e.target;
    if (!f.hasAttribute || !f.hasAttribute('data-confirm') || f.__confirmed || !confirmModal) return;
    e.preventDefault(); e.stopImmediatePropagation();
    pendingForm = f;
    $('[data-confirm-title]', confirmModal).textContent = f.getAttribute('data-confirm-title') || 'Lanjutkan tindakan ini?';
    $('[data-confirm-message]', confirmModal).textContent = f.getAttribute('data-confirm');
    var ok = $('[data-confirm-ok]', confirmModal), tone = f.getAttribute('data-confirm-tone') || 'danger';
    ok.textContent = f.getAttribute('data-confirm-label') || 'Ya, lanjutkan';
    ok.className = 'btn ' + (tone === 'ok' ? '' : 'btn-danger');
    var ico = $('.modal-ico', confirmModal); ico.className = 'modal-ico' + (tone === 'ok' ? ' ok' : '');
    openModal(confirmModal);
  }, true);
  if (confirmModal) {
    $('[data-confirm-ok]', confirmModal).addEventListener('click', function () {
      if (!pendingForm) return;
      var f = pendingForm; pendingForm = null; f.__confirmed = true; closeModal(confirmModal);
      var b = $('button[type=submit],button:not([type])', f); if (b) b.classList.add('is-loading');
      f.submit();
    });
  }

  /* ---------- Loading state pada tombol submit ---------- */
  document.addEventListener('submit', function (e) {
    var f = e.target; if (e.defaultPrevented || f.hasAttribute('data-no-loading')) return;
    var b = e.submitter || $('button[type=submit],button:not([type])', f);
    if (!b || !b.classList) return;
    setTimeout(function () { b.classList.add('is-loading'); b.setAttribute('aria-busy', 'true'); b.disabled = true; }, 0);
    var bar = $('.page-loading'); if (bar) bar.style.width = '70%';
  });
  window.addEventListener('pageshow', function (e) {
    if (e.persisted) $$('.btn.is-loading').forEach(function (b) { b.classList.remove('is-loading'); b.disabled = false; b.removeAttribute('aria-busy'); });
    var bar = $('.page-loading'); if (bar) bar.style.width = '0';
  });
  document.addEventListener('click', function (e) {
    var a = e.target.closest('a[href]');
    if (!a || a.target === '_blank' || e.metaKey || e.ctrlKey || e.shiftKey) return;
    var h = a.getAttribute('href'); if (!h || h.charAt(0) === '#' || h.indexOf('javascript:') === 0 || a.hasAttribute('download') || a.hasAttribute('data-no-loading')) return;
    var bar = $('.page-loading'); if (bar) bar.style.width = '65%';
  });

  /* ---------- Toast ---------- */
  function dismissToast(t) { t.classList.add('out'); setTimeout(function () { if (t.parentNode) t.parentNode.removeChild(t); }, 320); }
  $$('.toast').forEach(function (t) {
    var x = $('.t-x', t); if (x) x.addEventListener('click', function () { dismissToast(t); });
    setTimeout(function () { dismissToast(t); }, t.classList.contains('err') ? 9000 : 5500);
  });

  /* ---------- Toggle tampil/sembunyi password + kekuatan ---------- */
  document.addEventListener('click', function (e) {
    var b = e.target.closest('[data-toggle-password]'); if (!b) return;
    var input = $('input', b.closest('.control')); if (!input) return;
    var show = input.type === 'password'; input.type = show ? 'text' : 'password';
    b.setAttribute('aria-label', show ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
    $$('.icon-eye', b).forEach(function (i) { i.style.display = show ? 'none' : ''; });
    $$('.icon-eye-off', b).forEach(function (i) { i.style.display = show ? '' : 'none'; });
  });
  $$('[data-strength-for]').forEach(function (m) {
    var input = document.getElementById(m.getAttribute('data-strength-for')); if (!input) return;
    input.addEventListener('input', function () {
      var v = input.value, s = 0;
      if (v.length >= 8) s++; if (/[a-z]/.test(v) && /[A-Z]/.test(v)) s++; if (/\d/.test(v)) s++; if (/[^A-Za-z0-9]/.test(v) || v.length >= 12) s++;
      m.setAttribute('data-s', v ? s : 0);
    });
  });
  document.addEventListener('click', function (e) {
    var d = e.target.closest('[data-fill-email]'); if (!d) return;
    var em = $('#email'), pw = $('#password'); if (em) em.value = d.getAttribute('data-fill-email'); if (pw) pw.value = d.getAttribute('data-fill-pass');
    if (pw) pw.focus();
  });

  /* ---------- Gambar: skeleton + fallback ---------- */
  $$('img[data-fallback]').forEach(function (img) {
    var wrap = img.closest('.skeleton-img');
    function ready() { if (wrap) wrap.classList.add('is-ready'); }
    img.addEventListener('load', ready);
    img.addEventListener('error', function () {
      var fb = img.getAttribute('data-fallback');
      if (fb && img.src.indexOf(fb) === -1) { img.src = fb; } else { ready(); }
    });
    if (img.complete) ready();
  });

  /* ---------- Tabel: cari, filter, paginasi (sisi klien) ---------- */
  $$('[data-table]').forEach(function (root) {
    var rows = $$('tbody tr[data-row], [data-item]', root);
    var search = $('[data-table-search]', root), count = $('[data-table-count]', root), pager = $('[data-pager]', root), empty = $('[data-table-empty]', root);
    var size = parseInt(root.getAttribute('data-page-size') || '10', 10), page = 1;
    var filters = {}; // key -> value
    rows.forEach(function (r) { if (!r.hasAttribute('data-search')) r.setAttribute('data-search', r.textContent.replace(/\s+/g, ' ').toLowerCase()); });

    function matched() {
      var q = search ? search.value.trim().toLowerCase() : '';
      return rows.filter(function (r) {
        if (q && r.getAttribute('data-search').indexOf(q) === -1) return false;
        for (var k in filters) { if (filters[k] && r.getAttribute('data-' + k) !== filters[k]) return false; }
        return true;
      });
    }
    function render() {
      var m = matched(), pages = Math.max(1, Math.ceil(m.length / size));
      if (page > pages) page = pages;
      rows.forEach(function (r) { r.hidden = true; });
      m.slice((page - 1) * size, page * size).forEach(function (r) { r.hidden = false; });
      if (count) {
        var from = m.length ? (page - 1) * size + 1 : 0, to = Math.min(page * size, m.length);
        count.textContent = 'Menampilkan ' + from + '–' + to + ' dari ' + m.length + ' data';
      }
      if (empty) empty.hidden = m.length > 0;
      if (pager) {
        pager.innerHTML = ''; pager.hidden = pages <= 1; if (pages <= 1) return;
        var add = function (label, p, dis, act, aria) {
          var b = document.createElement('button'); b.type = 'button'; b.textContent = label;
          if (aria) b.setAttribute('aria-label', aria);
          if (dis) { b.disabled = true; b.className = 'disabled'; } if (act) { b.className = 'active'; b.setAttribute('aria-current', 'page'); }
          b.addEventListener('click', function () { page = p; render(); }); pager.appendChild(b);
        };
        add('‹', page - 1, page === 1, false, 'Halaman sebelumnya');
        for (var i = 1; i <= pages; i++) {
          if (i === 1 || i === pages || Math.abs(i - page) <= 1) add(String(i), i, false, i === page);
          else if (Math.abs(i - page) === 2) { var s = document.createElement('span'); s.className = 'pg'; s.textContent = '…'; pager.appendChild(s); }
        }
        add('›', page + 1, page === pages, false, 'Halaman berikutnya');
      }
    }
    if (search) search.addEventListener('input', function () { page = 1; render(); });
    $$('[data-filter]', root).forEach(function (chip) {
      chip.addEventListener('click', function () {
        var key = chip.getAttribute('data-filter');
        $$('[data-filter="' + key + '"]', root).forEach(function (c) { c.classList.remove('active'); c.setAttribute('aria-pressed', 'false'); });
        chip.classList.add('active'); chip.setAttribute('aria-pressed', 'true');
        filters[key] = chip.getAttribute('data-value'); page = 1; render();
      });
    });
    $$('select[data-filter-select]', root).forEach(function (sel) {
      sel.addEventListener('change', function () { filters[sel.getAttribute('data-filter-select')] = sel.value; page = 1; render(); });
    });
    render();
  });

  /* ---------- Form peminjaman: pratinjau alat terpilih ---------- */
  var sel = $('[data-alat-select]');
  if (sel) {
    var box = $('[data-alat-preview]'), jml = $('[data-jumlah]');
    var upd = function () {
      var o = sel.options[sel.selectedIndex]; if (!box) return;
      var emp = $('[data-pv-empty]'); var none = !o || !o.value;
      box.hidden = none; if (emp) emp.hidden = !none;
      if (none) return;
      var img = $('[data-pv-img]', box); img.src = o.getAttribute('data-img'); img.alt = o.getAttribute('data-nama');
      $('[data-pv-nama]', box).textContent = o.getAttribute('data-nama');
      $('[data-pv-kat]', box).textContent = o.getAttribute('data-kat');
      $('[data-pv-stok]', box).textContent = o.getAttribute('data-stok');
      var max = parseInt(o.getAttribute('data-stok'), 10) || 1;
      if (jml) { jml.max = max; if (parseInt(jml.value, 10) > max) jml.value = max; }
      var h = $('[data-max-hint]'); if (h) h.textContent = 'Maksimal ' + max + ' unit tersedia.';
    };
    sel.addEventListener('change', upd); upd();
  }

  /* ---------- Modal detail alat ---------- */
  document.addEventListener('click', function (e) {
    var b = e.target.closest('[data-alat-detail]'); if (!b) return;
    var m = document.getElementById('alatDetailModal'); if (!m) return;
    var g = function (k) { return b.getAttribute('data-' + k); };
    var img = $('[data-ad-img]', m); img.src = g('img'); img.alt = g('nama');
    $('[data-ad-nama]', m).textContent = g('nama'); $('[data-ad-kat]', m).textContent = g('kat');
    $('[data-ad-desc]', m).textContent = g('desc'); $('[data-ad-kode]', m).textContent = g('kode');
    $('[data-ad-kondisi]', m).textContent = g('kondisi'); $('[data-ad-stok]', m).textContent = g('stok'); $('[data-ad-total]', m).textContent = g('total');
    var a = $('[data-ad-ajukan]', m); a.href = g('url');
    var habis = g('habis') === '1'; a.hidden = habis;
    openModal(m);
  });

  /* ---------- Upload foto: pratinjau, validasi ringan, ganti/batal ---------- */
  $$('[data-upload]').forEach(function (box) {
    var input = $('input[type=file]', box), img = $('[data-up-img]', box), err = $('[data-up-error]', box), nameEl = $('[data-up-name]', box);
    var cancel = $('[data-up-cancel]', box), pickText = $('[data-up-pick-text]', box), remove = $('[data-up-remove]', box);
    var initial = box.getAttribute('data-initial') || '', ph = box.getAttribute('data-placeholder'), max = parseFloat(box.getAttribute('data-max-mb') || '2') * 1048576;
    var okTypes = ['image/jpeg', 'image/png', 'image/webp'], blobUrl = null;
    function showErr(m) { err.textContent = m || ''; err.hidden = !m; box.classList.toggle('invalid', !!m); }
    function setPreview(src) { var w = img.closest('.skeleton-img'); if (w) w.classList.remove('is-ready'); img.src = src; }
    function reset() {
      if (blobUrl) { URL.revokeObjectURL(blobUrl); blobUrl = null; }
      input.value = ''; showErr(''); cancel.hidden = true; nameEl.hidden = true;
      setPreview(remove && remove.checked ? ph : (initial || ph)); pickText.textContent = initial ? 'Ganti foto' : 'Pilih foto';
    }
    input.addEventListener('change', function () {
      var f = input.files && input.files[0]; if (!f) { reset(); return; }
      if (okTypes.indexOf(f.type) === -1) { input.value = ''; showErr('Format tidak didukung. Pilih file JPG, JPEG, PNG, atau WebP.'); return; }
      if (f.size > max) { input.value = ''; showErr('Ukuran foto ' + (f.size / 1048576).toFixed(1) + ' MB melebihi batas ' + (max / 1048576) + ' MB.'); return; }
      showErr(''); if (blobUrl) URL.revokeObjectURL(blobUrl); blobUrl = URL.createObjectURL(f);
      setPreview(blobUrl); cancel.hidden = false; nameEl.hidden = false; nameEl.textContent = f.name; pickText.textContent = 'Ganti foto';
      if (remove) remove.checked = false;
    });
    cancel.addEventListener('click', reset);
    if (remove) remove.addEventListener('change', function () {
      if (remove.checked) { if (input.value) reset(); setPreview(ph); } else { setPreview(initial || ph); }
    });
  });

  /* ---------- Cetak ---------- */
  document.addEventListener('click', function (e) { if (e.target.closest('[data-print]')) window.print(); });

  /* ---------- Reveal ringan saat load (dihormati reduced motion lewat CSS) ---------- */
  if (reduce) $$('.rise').forEach(function (el) { el.style.animation = 'none'; });
})();
