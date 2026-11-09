<x-layouts.app title="Ringkasan" active="index">
  <x-layouts.page-header title="Ringkasan" subtitle="Selamat datang" subtitle-id="greet">
    <x-forms.button href="/catatan-transaksi" variant="primary" icon="plus">Catat transaksi</x-forms.button>
  </x-layouts.page-header>

  <x-ui.alert variant="error" id="dashError" class="mt-5" />

  <div class="mt-6 flex flex-wrap items-center justify-between gap-x-4 gap-y-2">
    <div class="flex w-full rounded-xl bg-line p-1 text-[13px] font-semibold text-muted sm:w-auto" role="group" aria-label="Periode">
      <button type="button" data-p="today" aria-pressed="false" class="flex-1 whitespace-nowrap rounded-lg px-4 py-2 sm:flex-none">Hari ini</button>
      <button type="button" data-p="7" aria-pressed="false" class="flex-1 whitespace-nowrap rounded-lg px-4 py-2 sm:flex-none">7 hari terakhir</button>
      <button type="button" data-p="month" aria-pressed="false" class="flex-1 whitespace-nowrap rounded-lg px-4 py-2 sm:flex-none">Bulan ini</button>
    </div>
    <p id="rangeLabel" class="text-[13px] text-muted" aria-live="polite"></p>
  </div>

  <section class="mt-5 grid gap-4 sm:grid-cols-3" aria-label="Angka utama">
    <x-ui.stat-card id="kNet" size="lg" highlight class="sm:col-span-3" label="Laba bersih tercatat" value="–" caption="Laba kotor – beban operasional" />
    <x-ui.stat-card id="kRev" size="sm" label="Pendapatan" value="–" caption="" />
    <x-ui.stat-card id="kGross" size="sm" label="Laba kotor" value="–" caption="" />
    <x-ui.stat-card id="kOpex" size="sm" label="Beban operasional" value="–" caption="" />
  </section>

  <div class="mt-5 grid gap-4 xl:grid-cols-[1fr_360px]">
    <x-ui.card class="min-w-0" title="Pendapatan" subtitle-id="chartSub">
      <x-slot:aside>
        <x-ui.badge id="chartBadge">–</x-ui.badge>
      </x-slot:aside>
      <x-widgets.chart-container :height="200" empty-text="Belum ada penjualan pada periode ini. Catat penjualan untuk mengisi grafik." />
    </x-ui.card>

    <x-ui.card class="min-w-0" title="Stok perlu perhatian" subtitle="Di bawah batas minimum">
      <x-slot:aside>
        <x-ui.badge id="lowBadge">–</x-ui.badge>
      </x-slot:aside>
      <ul id="lowList" class="mt-3"></ul>
      <a href="/produk-stok" class="mt-2 inline-block text-[13px] font-bold text-brand-dark hover:underline">Kelola barang masuk →</a>
    </x-ui.card>

    <x-ui.card flush class="min-w-0">
      <div class="flex items-center justify-between px-5 py-5">
        <h2 class="text-[18px] font-semibold">Catatan terbaru</h2>
        <a href="/catatan-transaksi" class="text-[13px] font-bold text-brand-dark hover:underline">Lihat semua →</a>
      </div>
      <x-ui.table :headers="['Waktu', 'Catatan', 'Jenis', ['Nominal', 'right']]" body-id="recentBody" :min-width="520" />
    </x-ui.card>

    <section class="min-w-0 rounded-xl border border-line bg-surface p-6" aria-labelledby="todayTitle">
      <h2 id="todayTitle" class="text-[16px] font-semibold">Hari ini di warung</h2>
      <p id="tDate" class="mt-1 text-[12px] text-muted"></p>
      <p class="mt-6 text-[12px] text-muted">Pendapatan</p>
      <p id="tRev" class="mt-2 break-words text-[34px] font-bold leading-none tracking-tight">–</p>
      <p id="tCap" class="mt-3 text-[12px] text-muted"></p>
      <div class="mt-5 flex justify-between gap-3 border-t border-line pt-5">
        <div>
          <p class="text-[12px] text-muted">Beban operasional</p>
          <p id="tOpex" class="mt-1.5 text-[15px] font-bold">–</p>
        </div>
        <div class="text-right">
          <p class="text-[12px] text-muted">Laba kotor</p>
          <p id="tGross" class="mt-1.5 text-[15px] font-bold">–</p>
        </div>
      </div>
    </section>
  </div>

  <p class="mt-6 text-[12px] text-muted">Ringkasan hanya menghitung aktivitas yang dicatat di Catatan transaksi.</p>

@push('scripts')
<script>
  window.LaravelUser = @json(auth()->user());
  (function () {
    var M = window.Mantou, period = '7';
    var $ = function (id) { return document.getElementById(id); };
    function setText(id, t) { var el = $(id); if (el) el.textContent = t; }
    function setHtml(id, h) { var el = $(id); if (el) el.innerHTML = h; }
    var esc = function (t) { return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };
    function pad(n) { return (n < 10 ? '0' : '') + n; }
    function dayStart(ts) { var d = new Date(ts); return new Date(d.getFullYear(), d.getMonth(), d.getDate()).getTime(); }
    function addDays(ts, n) { var d = new Date(ts); return new Date(d.getFullYear(), d.getMonth(), d.getDate() + n).getTime(); }
    function niceStep(v) { if (v <= 0) return 1; var e = Math.pow(10, Math.floor(Math.log(v) / Math.LN10)), f = v / e; return (f <= 1 ? 1 : f <= 2 ? 2 : f <= 2.5 ? 2.5 : f <= 5 ? 5 : 10) * e; }
    function dec(n) { return String(Math.round(n * 10) / 10).replace('.', ','); }
    function axis(v) { if (v === 0) return '0'; if (v >= 1e6) return dec(v / 1e6) + ' jt'; if (v >= 1000) return dec(v / 1000) + ' rb'; return String(Math.round(v)); }

    function showError(msg) { var el = $('dashError'); if (!el) return; el.textContent = msg; el.classList.remove('hidden'); }
    function clearError() { var el = $('dashError'); if (el) el.classList.add('hidden'); }
    // satu widget yang gagal tidak boleh menjatuhkan seluruh dashboard
    function guard(name, fn) { try { fn(); } catch (e) { if (window.console) console.error('Ringkasan: ' + name, e); showError('Sebagian data tidak dapat ditampilkan. Muat ulang halaman; jika berulang, hubungi pengelola.'); } }

    function greeting() {
        var h = new Date().getHours();
        var w = h < 11 ? 'pagi' : h < 15 ? 'siang' : h < 18 ? 'sore' : 'malam';

        var userName = window.LaravelUser ? window.LaravelUser.name : '';

        setText('greet', 'Selamat ' + w + (userName ? ', ' + userName : ''));
    }

    function kpis(r, sum) {
      var rp = M.fmt.rupiah, net = $('kNet');
      setText('rangeLabel', r.label);
      setText('kRev', rp(sum.revenue));   setText('kRevCap', sum.saleCount + ' penjualan');
      setText('kGross', rp(sum.gross));   setText('kGrossCap', 'Pendapatan – HPP ' + rp(sum.cogs));
      setText('kOpex', rp(sum.opex));     setText('kOpexCap', sum.expenseCount + ' pengeluaran · di luar HPP');
      setText('kNet', rp(sum.net));
      // negatif ditandai tanda minus pada angka dan teks "Rugi", bukan hanya warna
      setText('kNetCap', sum.net < 0 ? 'Rugi · beban melebihi laba kotor' : 'Laba kotor – beban operasional');
      if (net) { net.classList.toggle('text-danger-dark', sum.net < 0); net.classList.toggle('text-ink', sum.net >= 0); }
    }

    // periode "Hari ini" digambar per jam; lainnya per hari. Semua batang berasal dari transaksi yang sama dengan KPI.
    function buckets(r, sum) {
      var out = [], i, t, k;
      if (period === 'today') {
        var byH = {}, lo = 6, hi = 21, nowH = new Date().getHours();
        sum.sales.forEach(function (t) { var h = new Date(t.ts).getHours(); byH[h] = (byH[h] || 0) + t.revenue; lo = Math.min(lo, h); hi = Math.max(hi, h); });
        for (i = lo; i <= hi; i++) out.push({ label: pad(i), short: pad(i), title: pad(i) + '.00–' + pad(i) + '.59', value: byH[i] || 0, current: i === nowH });
        return out;
      }
      var byD = {}, today = dayStart(Date.now());
      sum.sales.forEach(function (t) { k = dayStart(t.ts); byD[k] = (byD[k] || 0) + t.revenue; });
      for (t = dayStart(r.from); t <= today; t = addDays(t, 1)) out.push({ label: M.fmt.shortDate(t), short: String(new Date(t).getDate()), title: M.fmt.shortDate(t), value: byD[t] || 0, current: t === today });
      return out;
    }

    function chart(r, sum) {
      var rp = M.fmt.rupiah, list = buckets(r, sum), max = 0;
      list.forEach(function (b) { max = Math.max(max, b.value); });
      var step = niceStep(max > 0 ? max / 3 : 100000), top = step * 3, few = list.length <= 8, gap = few ? '1.25rem' : '3px';
      var every = few ? 1 : Math.ceil(list.length / 7);
      setHtml('yAxis', [3, 2, 1, 0].map(function (k) { return '<span>' + axis(step * k) + '</span>'; }).join(''));
      var bars = $('bars'), x = $('xAxis');
      if (bars) {
        bars.style.gap = gap;
        bars.setAttribute('role', 'img');
        bars.setAttribute('aria-label', 'Grafik pendapatan ' + r.label + ', total ' + rp(sum.revenue));
        bars.innerHTML = list.map(function (b) {
          var h = b.value > 0 ? Math.max(2, b.value / top * 100) : 0;
          return '<div class="flex h-full min-w-0 flex-1 flex-col items-center justify-end"><div title="' + esc(b.title + ' · ' + rp(b.value)) + '" class="w-full max-w-[34px] rounded-t-sm ' + (b.current ? 'bg-brand-dark' : 'bg-brand') + '" style="height:' + h + '%"></div></div>';
        }).join('');
      }
      if (x) {
        x.style.gap = gap;
        x.innerHTML = list.map(function (b, k) {
          var txt = k % every === 0 ? (few ? b.label : b.short) : '';
          return '<span class="min-w-0 flex-1 whitespace-nowrap text-center' + (b.current ? ' font-bold text-ink' : '') + '">' + esc(txt) + '</span>';
        }).join('');
      }
      var empty = $('chartEmpty');
      if (empty) { empty.classList.toggle('hidden', max > 0); empty.classList.toggle('grid', max === 0); }
      setText('chartSub', (period === 'today' ? 'Per jam · ' : '') + r.label);
      setText('chartBadge', period === 'today' ? 'Per jam' : list.length + ' hari');
    }

    function stock(products) {
      var list = Array.isArray(products) ? products.filter(function (p) { return p && typeof p === 'object'; }) : [];
      var key = function (p) { var st = M.num(p.stock), mn = M.num(p.min); return st <= 0 ? -1 : (mn > 0 ? st / mn : 1); };
      var low = list.filter(function (p) { return M.status(p) !== 'cukup'; }).sort(function (a, b) { return key(a) - key(b) || String(a.name).localeCompare(String(b.name)); });
      var badge = $('lowBadge');
      setText('lowBadge', low.length + ' produk');
      if (badge) badge.className = 'rounded-md px-2.5 py-1 text-[12px] font-bold ' + (low.length ? 'border border-warning bg-warn-bg text-warn' : 'bg-line text-muted');
      var rows = low.slice(0, 5).map(function (p, i) {
        var st = Math.max(0, M.num(p.stock)), mn = M.num(p.min), name = esc(p.name || 'Produk tanpa nama'), unit = p.unit ? ' ' + esc(p.unit) : '';
        return '<li class="flex items-center gap-3.5 py-3.5' + (i ? ' border-t border-line' : '') + '"><span class="grid size-10 shrink-0 place-items-center rounded-lg border border-warning bg-warn-bg text-[13px] font-bold text-warn">' + st + '</span>' +
          '<a href="/produk-stok" class="group min-w-0"><span class="block truncate text-[13px] font-bold text-ink group-hover:text-brand-dark group-hover:underline">' + name + '</span><span class="block text-[12px] text-muted">' + (st === 0 ? 'Habis' : st + unit + ' tersisa') + ' · minimum ' + mn + '</span></a></li>';
      });
      if (low.length > 5) rows.push('<li class="border-t border-line pt-3.5 text-[12px] text-muted">+' + (low.length - 5) + ' produk lainnya</li>');
      setHtml('lowList', rows.length ? rows.join('') : '<li class="py-6 text-[12px] text-muted">Semua produk masih di atas batas minimum.</li>');
    }

    function recent(txs) {
      var F = M.fmt, rp = F.rupiah, todayK = F.dateInput(Date.now());
      var list = txs.slice().sort(function (a, b) { return b.ts - a.ts; }).slice(0, 5);
      setHtml('recentBody', list.length ? list.map(function (t) {
        var sale = t.type === 'sale', when = F.dateInput(t.ts) === todayK ? F.time(t.ts) : F.dateTime(t.ts);
        return '<tr class="border-t border-line"><td class="whitespace-nowrap px-5 py-4 text-[13px] font-bold">' + esc(when) + '</td>' +
          '<td class="px-3 py-4"><span class="block text-[13px] font-bold">' + esc(M.summary(t)) + '</span><span class="block text-[12px] text-muted">' + esc(t.id) + ' · ' + (sale ? Math.round(t.units) + ' unit' : esc(t.category)) + '</span></td>' +
          '<td class="px-3 py-4 text-[13px] text-muted">' + (sale ? 'Penjualan' : 'Pengeluaran') + '</td>' +
          '<td class="whitespace-nowrap px-5 py-4 text-right text-[13px] font-semibold">' + (sale ? rp(t.revenue) : rp(-t.amount)) + '</td></tr>';
      }).join('') : '<tr class="border-t border-line"><td colspan="4" class="px-5 py-8 text-center text-[13px] text-muted">Belum ada catatan. Mulai dari <a href="/catatan-transaksi" class="font-semibold text-brand-dark underline">Catat transaksi</a>.</td></tr>');
    }

    // panel ini selalu hari ini, tidak ikut filter periode
    function today(txs) {
      var r = M.range('today'), sum = M.summarize(r.from, r.to, txs), rp = M.fmt.rupiah;
      setText('tDate', M.fmt.fullDate(Date.now()));
      setText('tRev', rp(sum.revenue));
      setText('tCap', sum.saleCount ? 'Dari ' + sum.saleCount + ' penjualan' : 'Belum ada penjualan hari ini');
      setText('tOpex', rp(sum.opex)); setText('tGross', rp(sum.gross));
    }

    function render() {
      var txs, products;
      try { txs = M.txList(); products = M.get().products; } catch (e) { if (window.console) console.error(e); showError('Data tidak dapat dimuat. Muat ulang halaman.'); return; }
      clearError();
      var r = M.range(period), sum = M.summarize(r.from, r.to, txs);
      Array.prototype.forEach.call(document.querySelectorAll('[data-p]'), function (b) {
        var on = b.getAttribute('data-p') === period;
        b.className = 'flex-1 whitespace-nowrap rounded-lg px-4 py-2 sm:flex-none' + (on ? ' bg-surface text-ink shadow-sm' : ' hover:text-ink');
        b.setAttribute('aria-pressed', on ? 'true' : 'false');
      });
      guard('greeting', greeting);
      guard('kpi', function () { kpis(r, sum); });
      guard('chart', function () { chart(r, sum); });
      guard('stok', function () { stock(products); });
      guard('catatan', function () { recent(txs); });
      guard('hari ini', function () { today(txs); });
    }

    if (!M || typeof M.txList !== 'function' || typeof M.summarize !== 'function') {
      showError('Data tidak dapat dimuat: file mantou-store.js di public/ masih versi lama. Salin resources/js/mantou-store.js ke public/mantou-store.js, lalu muat ulang (Ctrl+F5).');
      return;
    }
    Array.prototype.forEach.call(document.querySelectorAll('[data-p]'), function (b) { b.addEventListener('click', function () { period = b.getAttribute('data-p'); render(); }); });
    M.onChange(render);
    window.addEventListener('pageshow', function (e) { if (e.persisted) render(); });
    document.addEventListener('visibilitychange', function () { if (!document.hidden) render(); });
    render();
  })();
</script>
@endpush
</x-layouts.app>
