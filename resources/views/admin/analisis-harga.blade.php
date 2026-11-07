<x-layouts.app title="Analisis harga" active="analisis-harga">
  <x-layouts.page-header title="Analisis harga" subtitle="Susun harga jual dari biaya dan target laba yang sesuai untuk warung Anda.">
    <x-ui.badge>Keputusan pemilik</x-ui.badge>
  </x-layouts.page-header>

  <x-ui.card padding="p-5" class="mt-6 flex flex-wrap items-center justify-between gap-4">
    <div class="flex items-center gap-5">
      <span class="grid size-12 place-items-center rounded-xl bg-tint text-brand"><x-ui.icon name="box" /></span>
      <div>
        <label for="pSel" class="text-[11px] text-muted">Produk yang dianalisis</label>
        <select id="pSel" class="mt-0.5 block max-w-[300px] rounded-lg border border-line bg-surface px-2 py-1.5 text-[16px] font-extrabold outline-none focus:border-brand"></select>
        <p id="pMeta" class="mt-1 text-[11px] text-muted"></p>
      </div>
    </div>
    <div class="flex items-center gap-5">
      <div class="text-right">
        <p class="text-[11px] text-muted">Harga jual saat ini</p>
        <p id="curPrice" class="text-[22px] font-extrabold"></p>
      </div>
      <x-forms.button id="pLink" href="/produk-stok">Lihat di Produk &amp; stok</x-forms.button>
    </div>
  </x-ui.card>

  <div id="empty" class="mt-5 hidden rounded-xl bg-line/50 px-5 py-8 text-center text-[13px] text-muted">
    Belum ada produk. Tambahkan lewat <a class="font-semibold text-brand underline" href="/produk-stok">Produk &amp; stok</a>.
  </div>

  <div id="body" class="mt-5 grid items-start gap-5 xl:grid-cols-2">
    <x-ui.card radius="2xl" title="Dasar perhitungan" subtitle="Sesuaikan biaya dan target laba per unit produk. Hasil dihitung langsung." title-size="text-[20px]">
      <div class="mt-5 flex flex-col gap-5">
        <x-forms.input id="iCost" type="number" prefix="Rp" min="0" step="1" inputmode="numeric" hint="Diambil dari data produk; dapat disesuaikan untuk simulasi.">
          <x-slot:label>Biaya perolehan per <span class="unit"></span></x-slot:label>
        </x-forms.input>

        <x-forms.input id="iExtra" type="number" prefix="Rp" min="0" step="1" inputmode="numeric" hint-id="extraHint">
          <x-slot:label>Biaya tambahan per <span class="unit"></span></x-slot:label>
        </x-forms.input>

        <div>
          <label for="iMk" class="text-[12px] font-bold">Target laba (markup)</label>
          <div class="mt-2 flex flex-wrap items-center gap-4">
            <div class="flex w-44 items-center justify-between rounded-lg border border-line px-3.5 py-3 text-[14px] focus-within:border-brand">
              <input id="iMk" type="number" min="0" max="500" step="1" inputmode="numeric" class="w-20 min-w-0 flex-1 bg-transparent outline-none">
              <span class="text-muted">%</span>
            </div>
            <div>
              <x-ui.badge tone="brand" class="inline-block">Markup, bukan margin</x-ui.badge>
              <p id="mkHint" class="mt-1.5 text-[11px] text-muted"></p>
            </div>
          </div>
        </div>

        <div>
          <x-forms.select id="iRound" label="Pembulatan harga ke atas" size="md" class="w-44" :options="['1' => 'Tanpa pembulatan', '100' => 'Rp100', '500' => 'Rp500', '1000' => 'Rp1.000']" selected="100" />
        </div>
      </div>
      <div class="mt-6 flex items-center justify-between">
        <x-forms.button id="btnReset" variant="link">Atur ulang</x-forms.button>
      </div>
    </x-ui.card>

    <x-ui.card tone="tint" radius="2xl" title="Rekomendasi harga jual" title-size="text-[16px]">
      <p id="rec" class="mt-6 text-[56px] font-extrabold leading-none tracking-tight text-brand"></p>
      <p id="recDelta" class="mt-4 text-[12px] text-brand"></p>
      <div class="mt-5 border-t border-brand-soft/40 pt-4">
        <div class="flex items-center justify-between py-1.5 text-[13px]"><span class="text-muted">Biaya perolehan</span><span id="bCost" class="font-bold"></span></div>
        <div class="flex items-center justify-between py-1.5 text-[13px]"><span class="text-muted">Biaya tambahan</span><span id="bExtra" class="font-bold"></span></div>
        <div class="flex items-center justify-between py-1.5 text-[13px]"><span class="font-bold">Total biaya per unit</span><span id="bTotal" class="font-bold"></span></div>
        <div class="flex items-center justify-between py-1.5 text-[13px]"><span id="bMkLbl" class="text-muted"></span><span id="bMk" class="font-bold"></span></div>
      </div>
      <p id="bSum" class="mt-4 rounded-lg bg-surface px-4 py-3.5 text-center text-[12px] font-bold text-brand"></p>
      <p class="mt-4 text-[11px] text-muted">Simulasi belum mengubah harga tersimpan. Tinjau sebelum menetapkan harga baru.</p>
      <div class="mt-4 flex flex-wrap items-center gap-3">
        <x-forms.button id="btnApply" variant="primary" size="lg" />
        <a id="discLink" href="/rekomendasi-diskon" class="text-[12px] font-bold text-brand hover:underline">Tinjau diskon stok lambat →</a>
      </div>
    </x-ui.card>
  </div>

  <x-ui.card id="cmp" class="mt-5" title="Apa bedanya dengan harga sekarang?">
    <div class="mt-4 grid gap-4 md:grid-cols-2">
      <div class="rounded-lg bg-canvas px-4 py-3.5">
        <p id="cmpA" class="text-[11px] text-muted"></p>
        <div class="mt-2 flex items-center justify-between text-[13px]"><span>Estimasi sisa setelah seluruh biaya</span><b id="cmpAv" class="text-[18px]"></b></div>
      </div>
      <div class="rounded-lg bg-tint px-4 py-3.5">
        <p id="cmpB" class="text-[11px] text-muted"></p>
        <div class="mt-2 flex items-center justify-between text-[13px]"><span>Estimasi sisa setelah seluruh biaya</span><b id="cmpBv" class="text-[18px]"></b></div>
      </div>
    </div>
  </x-ui.card>

  <x-ui.alert variant="note" class="mt-5">Rekomendasi memakai biaya dan target laba Anda, bukan harga pasar langsung. Pemilik tetap menentukan harga akhir; pertimbangkan juga kondisi pelanggan dan warung.</x-ui.alert>

@push('scripts')
<script>
  (function () {
    var M = window.Mantou, rp = M.fmt.rupiah;
    var $ = function (id) { return document.getElementById(id); };
    var esc = function (t) { return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };
    var pct1 = function (n) { return (Math.round(n * 10) / 10).toString().replace('.', ','); };
    var qs = new URLSearchParams(location.search);
    var ICON = function (d) { return '<svg class="size-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + d + '</svg>'; };

    var pid = qs.get('pid') || '', hint = M.extraCostHint();
    function prod() { var s = M.get(); return M.find(s.products, pid) || s.products[0] || null; }
    function num(id) { var v = Number($(id).value); return v >= 0 ? v : 0; }
    function loadProduct(keep) {
      var p = prod(); $('body').classList.toggle('hidden', !p); $('cmp').classList.toggle('hidden', !p); $('empty').classList.toggle('hidden', !!p);
      if (!p) { $('pSel').innerHTML = ''; $('pMeta').textContent = ''; $('curPrice').textContent = '—'; return; }
      pid = p.id;
      $('pSel').innerHTML = M.get().products.map(function (x) { return '<option value="' + x.id + '"' + (x.id === pid ? ' selected' : '') + '>' + esc(x.name) + '</option>'; }).join('');
      $('pMeta').textContent = (p.barcode ? p.barcode + ' · ' : '') + p.category + ' · stok ' + p.stock + ' ' + p.unit + (p.promo ? ' · diskon ' + p.promo.pct + ' persen berjalan' : '');
      $('curPrice').textContent = rp(p.price); $('pLink').href = '/produk-stok?cari=' + encodeURIComponent(p.barcode || p.name);
      $('discLink').href = '/rekomendasi-diskon?pid=' + p.id;
      Array.prototype.forEach.call(document.querySelectorAll('.unit'), function (e) { e.textContent = p.unit; });
      if (!keep) { $('iCost').value = p.cost; $('iExtra').value = hint.perUnit; $('iMk').value = 25; }
      $('extraHint').textContent = hint.units ? 'Saran dari catatan: pengeluaran ongkos angkut & kemasan 7 hari terakhir ' + rp(hint.total) + ' ÷ ' + hint.units + ' unit terjual = ' + rp(hint.perUnit) + ' per unit. Boleh diubah.' : 'Isi ongkos angkut, kemasan, atau biaya lain yang dibebankan per unit.';
      calc();
    }
    function calc() {
      var p = prod(); if (!p) return;
      var cost = num('iCost'), extra = num('iExtra'), mk = num('iMk'), step = +$('iRound').value, total = cost + extra, profit = total * mk / 100;
      var rec = Math.ceil((total + profit) / step - 1e-9) * step, d = rec - p.price;
      $('mkHint').textContent = mk + '% dari total biaya ' + rp(total) + ', bukan dari harga jual.';
      $('rec').textContent = rp(rec);
      $('recDelta').textContent = 'per ' + p.unit + ' · ' + (d === 0 ? 'sama dengan harga saat ini' : (d > 0 ? '+' : '–') + rp(Math.abs(d)).replace('–', '') + ' (' + (d > 0 ? '+' : '–') + pct1(Math.abs(d) / p.price * 100) + '%) dari harga saat ini');
      $('bCost').textContent = rp(cost); $('bExtra').textContent = rp(extra); $('bTotal').textContent = rp(total);
      $('bMkLbl').textContent = 'Target laba · ' + mk + '% × ' + rp(total); $('bMk').textContent = rp(profit);
      $('bSum').textContent = rp(total) + ' + ' + rp(profit) + ' = ' + rp(total + profit) + (rec !== Math.round(total + profit) ? ' → dibulatkan ' + rp(rec) : '');
      $('cmpA').textContent = 'Harga saat ini · ' + rp(p.price); $('cmpAv').textContent = rp(p.price - total);
      $('cmpB').textContent = 'Harga rekomendasi · ' + rp(rec); $('cmpBv').textContent = rp(rec - total);
      $('cmpAv').className = 'text-[18px] ' + (p.price - total < 0 ? 'text-warn' : '');
      $('btnApply').innerHTML = '<svg class="size-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>Terapkan harga ' + rp(rec);
      $('btnApply').disabled = !(rec >= 1 && rec !== p.price);
    }
    ['iCost', 'iExtra', 'iMk'].forEach(function (id) { $(id).addEventListener('input', calc); });
    $('iRound').addEventListener('change', calc);
    $('pSel').addEventListener('change', function () { pid = this.value; hint = M.extraCostHint(); loadProduct(false); });
    $('btnReset').onclick = function () { hint = M.extraCostHint(); loadProduct(false); };
    $('btnApply').onclick = function () {
      var p = prod(), rec = $('rec').textContent;
      if (!confirm('Tetapkan harga jual ' + p.name + ' dari ' + rp(p.price) + ' menjadi ' + rec + '? Harga di Cek harga dan Produk & stok ikut berubah.')) return;
      var r = M.applyPrice(p.id, Number(rec.replace(/\D/g, '')), 0);
      if (!r.ok) { alert(r.error); return; } toast('Harga ' + p.name + ' sekarang ' + rec); loadProduct(true);
    };
    M.onChange(function () { loadProduct(true); });
    loadProduct(false);
  })();
</script>
@endpush
</x-layouts.app>
