<x-layouts.app title="Rekomendasi diskon" active="rekomendasi-diskon">
  <x-layouts.page-header title="Rekomendasi diskon" subtitle="Tinjau peluang diskon untuk stok lambat, sambil menjaga sisa margin.">
    <x-forms.select id="per" aria-label="Periode" :options="['7' => '7 hari terakhir', '14' => '14 hari terakhir', '30' => '30 hari terakhir', 'month' => 'Bulan ini']" selected="7" />
    <x-forms.select id="cover" aria-label="Batas stok berlebih" :options="['7' => 'Stok cukup > 7 hari', '10' => 'Stok cukup > 10 hari', '14' => 'Stok cukup > 14 hari', '21' => 'Stok cukup > 21 hari', '30' => 'Stok cukup > 30 hari']" selected="10" />
  </x-layouts.page-header>

  <div id="banner" class="mt-5 flex gap-4 rounded-xl bg-tint px-6 py-5"></div>

  <div id="cards" class="mt-5 grid gap-5 lg:grid-cols-3"></div>

  <x-ui.card id="tinjau" class="mt-5 hidden" title="Tinjau perubahan harga" subtitle-id="rvSub">
    <x-slot:aside>
      <x-ui.badge id="rvBadge">Menunggu keputusan</x-ui.badge>
    </x-slot:aside>
    <div class="mt-5 grid gap-6 lg:grid-cols-[1fr_1.1fr]">
      <div>
        <div class="grid gap-4 sm:grid-cols-2">
          <x-forms.input id="dPct" type="number" label="Diskon yang ditinjau" suffix="%" min="1" max="50" step="1" inputmode="numeric" />
          <div>
            <p class="text-[12px] font-bold">Harga setelah diskon</p>
            <div class="mt-2 flex items-center gap-3 rounded-lg border border-line bg-surface px-3.5 py-3 text-[14px] focus-within:border-brand">
              <span class="text-[13px] text-muted">Rp</span>
              <span id="dPrice" class="flex-1"></span>
            </div>
          </div>
        </div>
        <p id="rvCalc" class="mt-3 text-[12px] leading-relaxed text-muted"></p>
        <p class="mt-2 text-[12px] text-muted">Ingin menghitung dari target laba? <a id="rvPriceLink" href="/analisis-harga" class="font-bold text-brand-dark hover:underline">Buka analisis harga</a>.</p>
      </div>
      <div>
        <p class="text-[12px] font-bold">Mengapa produk ini disarankan?</p>
        <p id="rvWhy" class="mt-3 text-[12px] leading-relaxed text-muted"></p>
        <p id="rvMargin" class="mt-3 rounded-lg border border-line bg-surface px-4 py-3 text-[12px] font-semibold"></p>
      </div>
    </div>
    <div class="mt-6 flex flex-wrap items-center justify-between gap-4 border-t border-line pt-5">
      <label class="flex items-center gap-3 text-[12px] text-muted">
        <input id="ack" type="checkbox" class="size-4 accent-brand">Saya sudah meninjau usulan harga dan sisa margin produk ini.
      </label>
      <div class="flex items-center gap-6">
        <x-forms.button id="btnSkip" variant="link">Lewati</x-forms.button>
        <x-forms.button id="btnApply" variant="primary" />
      </div>
    </div>
  </x-ui.card>

  <x-ui.card id="activeSec" class="mt-5 hidden" flush>
    <div class="p-6 pb-3">
      <h2 class="text-[18px] font-semibold">Diskon yang sedang berjalan</h2>
      <p class="mt-1 text-[12px] text-muted">Harga di Cek harga dan Produk &amp; stok sudah memakai harga diskon.</p>
    </div>
    <ul id="activeList" class="px-6 pb-3"></ul>
  </x-ui.card>

  <p class="mt-6 flex items-center gap-2 text-[12px] text-muted">Margin kotor = (harga setelah diskon – biaya perolehan) ÷ harga setelah diskon. Belum mengurangi beban operasional. Hanya pemilik atau pengguna berizin yang dapat menerapkan harga.</p>

@push('scripts')
<script>
  (function () {
    var M = window.Mantou, rp = M.fmt.rupiah;
    var $ = function (id) { return document.getElementById(id); };
    var esc = function (t) { return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };
    var pct1 = function (n) { return (Math.round(n * 10) / 10).toString().replace('.', ','); };
    var qs = new URLSearchParams(location.search);
    var ICON = function (d) { return '<svg class="size-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + d + '</svg>'; };

    var sel = qs.get('pid') || '', slowList = [], cur = null;
    function suggestWhy(x) { return 'Stok ' + x.p.stock + ' unit dengan penjualan ' + x.sold + ' unit selama ' + x.days + ' hari' + (x.cover === Infinity ? ' (belum ada yang terjual)' : ' — cukup sekitar ' + pct1(x.cover) + ' hari') + '. '; }
    function gm(price, cost) { return price > 0 ? (price - cost) / price * 100 : 0; }

    function render() {
      var R = M.range($('per').value), A = M.analyze(R, +$('cover').value), s = M.get();
      slowList = A.filter(function (x) { return x.move === 'lambat' && !x.p.promo; }).sort(function (a, b) { return (b.cover === Infinity ? 1e9 : b.cover) - (a.cover === Infinity ? 1e9 : a.cover); });
      var ok = slowList.filter(function (x) { return M.suggestDiscount(x.p) > 0; }), thin = slowList.length - ok.length;
      var st = 0, u = 0; slowList.forEach(function (x) { st += x.p.stock; u += x.sold; });
      $('banner').innerHTML = '<span class="mt-0.5 text-brand-dark">' + ICON('<circle cx="12" cy="12" r="8.5"/><path d="M9 15l6-6"/><circle cx="9.5" cy="9.5" r=".8"/><circle cx="14.5" cy="14.5" r=".8"/>') + '</span><div><p class="text-[13px] font-bold">' + (slowList.length ? slowList.length + ' produk untuk ditinjau, bukan diskon otomatis' : 'Belum ada produk yang perlu didiskon') + '</p><p class="mt-1 text-[12px] text-muted">' + (slowList.length ? 'Stok gabungan ' + st + ' unit, terjual ' + u + ' unit pada ' + R.label.replace(/ \d{4}$/, '') + '. ' + (thin ? thin + ' produk bermargin tipis tidak disarankan didiskon. ' : '') : 'Tidak ada produk dengan stok di atas minimum yang cukup lebih dari ' + $('cover').value + ' hari. Ubah batas stok atau periode di atas untuk meninjau ulang. ') + 'Usulan mempertimbangkan stok, riwayat penjualan, dan margin. Hasil penjualan tidak dijamin; keputusan akhir tetap milik pemilik.</p></div>';

      if (!sel || !ok.some(function (x) { return x.p.id === sel; })) sel = ok.length ? ok[0].p.id : '';
      $('cards').innerHTML = slowList.slice(0, 6).map(function (x) {
        var p = x.p, pc = M.suggestDiscount(p), np = M.discounted(p.price, pc), on = p.id === sel;
        return '<section class="rounded-xl ' + (on ? 'border-2 border-brand' : 'border border-line') + ' bg-surface p-5"><div class="flex items-center justify-between"><span class="rounded-md border border-warning bg-warn-bg px-2.5 py-1 text-[12px] font-bold text-warn">Gerak lambat</span><span class="text-[12px] text-muted">Belum diterapkan</span></div><h2 class="mt-5 text-[18px] font-semibold">' + esc(p.name) + '</h2><p class="mt-2 text-[12px] text-muted">Stok ' + p.stock + ' unit · terjual ' + x.sold + ' unit / ' + x.days + ' hari</p><div class="mt-4 flex justify-between text-[12px]"><span class="text-muted">Harga saat ini</span><b>' + rp(p.price) + '</b></div>' +
          (pc ? '<div class="mt-3 rounded-lg bg-tint p-4"><div class="flex justify-between text-[12px] text-muted"><span>Usulan diskon ' + pc + '%</span></div><p class="mt-2 text-[26px] font-bold text-brand-dark">' + rp(np) + '</p></div><div class="mt-4 flex flex-col gap-1.5 text-[12px] text-muted"><div class="flex justify-between"><span>Biaya perolehan / unit</span><b class="font-semibold text-ink">' + rp(p.cost) + '</b></div><div class="flex justify-between"><span>Sisa laba kotor / unit</span><b class="text-brand-dark">' + rp(np - p.cost) + '</b></div><div class="flex justify-between"><span>Margin kotor tersisa</span><b class="text-brand-dark">' + pct1(gm(np, p.cost)) + '%</b></div></div><button type="button" data-pick="' + p.id + '" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-lg ' + (on ? 'bg-tint text-brand-dark' : 'border border-line hover:border-brand') + ' px-4 py-2.5 text-[13px] font-bold">' + (on ? 'Sedang ditinjau' : 'Tinjau usulan') + '</button>'
               : '<div class="mt-3 rounded-lg border border-warning bg-warn-bg p-4 text-[12px] font-semibold text-warn">Margin terlalu tipis (' + pct1(gm(p.price, p.cost)) + '%). Diskon tidak disarankan; pertimbangkan jumlah beli atau tinjau harga.</div><a href="/analisis-harga?pid=' + p.id + '" class="mt-4 inline-block text-[12px] font-bold text-brand-dark hover:underline">Buka analisis harga →</a>') + '</section>';
      }).join('');

      var act = s.products.filter(function (p) { return p.promo; });
      $('activeSec').classList.toggle('hidden', !act.length);
      $('activeList').innerHTML = act.map(function (p, i) {
        return '<li class="flex flex-wrap items-center justify-between gap-3 py-3.5 text-[13px]' + (i ? ' border-t border-line' : '') + '"><span><b>' + esc(p.name) + '</b><span class="block text-[12px] text-muted">Diskon ' + p.promo.pct + '% sejak ' + M.fmt.shortDate(p.promo.ts) + ' · harga normal ' + rp(p.promo.base) + ' → ' + rp(p.price) + '</span></span><button type="button" data-rev="' + p.id + '" class="rounded-lg border border-line px-3.5 py-2 text-[12px] font-bold hover:border-brand">Kembalikan harga ' + rp(p.promo.base) + '</button></li>';
      }).join('');
      review(ok);
    }

    function review(ok) {
      var x = ok.filter(function (y) { return y.p.id === sel; })[0]; cur = x || null;
      $('tinjau').classList.toggle('hidden', !x); if (!x) return;
      var p = x.p; $('rvSub').textContent = p.name + (p.barcode ? ' · ' + p.barcode : '');
      $('dPct').value = M.suggestDiscount(p); $('ack').checked = false;
      $('rvWhy').textContent = suggestWhy(x) + 'Diskon dihitung sekitar 40% dari margin kotor saat ini (' + pct1(gm(p.price, p.cost)) + '%), maksimum 15%.';
      $('rvPriceLink').href = '/analisis-harga?pid=' + p.id;
      calc();
    }
    function calc() {
      if (!cur) return; var p = cur.p, pc = Number($('dPct').value), okp = pc >= 1 && pc <= 50, np = okp ? M.discounted(p.price, pc) : 0, low = okp && np <= p.cost;
      $('dPrice').textContent = okp ? np.toLocaleString('id-ID') : '—';
      $('rvCalc').textContent = okp ? rp(p.price) + ' – (' + pc + '% × ' + rp(p.price) + ') = ' + rp(np) + ' (dibulatkan ke Rp100). Harga tersimpan tetap ' + rp(p.price) + ' sampai Anda menerapkan perubahan.' : 'Isi diskon antara 1% dan 50%.';
      $('rvMargin').textContent = okp ? rp(np) + ' – ' + rp(p.cost) + ' = ' + rp(np - p.cost) + ' per unit · Margin kotor: ' + pct1(gm(np, p.cost)) + '%' + (low ? ' — tidak boleh sama dengan atau di bawah biaya perolehan!' : '') : '';
      $('rvMargin').className = 'mt-3 rounded-lg px-4 py-3 text-[12px] font-semibold ' + (low ? 'border border-warning bg-warn-bg text-warn' : 'border border-line bg-surface');
      $('btnApply').innerHTML = ICON('<path d="M5 12.5l4.5 4.5L19 7.5"/>') + 'Terapkan harga ' + (okp ? rp(np) : '');
      $('btnApply').disabled = !(okp && np > p.cost && np < p.price && $('ack').checked);
    }
    $('dPct').addEventListener('input', calc); $('ack').addEventListener('change', calc);
    $('btnApply').onclick = function () {
      if (!cur) return; var pc = Number($('dPct').value), r = M.applyPrice(cur.p.id, M.discounted(cur.p.price, pc), pc);
      if (!r.ok) { alert(r.error); return; } toast('Harga ' + r.product.name + ' sekarang ' + rp(r.product.price) + ' (diskon ' + pc + '%)'); render();
    };
    $('btnSkip').onclick = function () {
      var ok = slowList.filter(function (y) { return M.suggestDiscount(y.p) > 0; }), i = ok.findIndex(function (y) { return y.p.id === sel; });
      if (ok.length > 1) { sel = ok[(i + 1) % ok.length].p.id; render(); } else toast('Tidak ada usulan lain untuk ditinjau.');
    };
    $('cards').addEventListener('click', function (e) { var b = e.target.closest('[data-pick]'); if (!b) return; sel = b.getAttribute('data-pick'); render(); $('tinjau').scrollIntoView({ behavior: 'smooth', block: 'start' }); });
    $('activeList').addEventListener('click', function (e) {
      var b = e.target.closest('[data-rev]'); if (!b) return;
      var r = M.revertPromo(b.getAttribute('data-rev')); if (r.ok) { toast('Harga ' + r.product.name + ' dikembalikan ke ' + rp(r.product.price)); render(); }
    });
    var P0 = qs.get('p'); if (P0) $('per').value = P0; if (!$('per').value) $('per').value = '7';
    ['per', 'cover'].forEach(function (id) { $(id).addEventListener('change', render); });
    M.onChange(render); render();
  })();
</script>
@endpush
</x-layouts.app>
