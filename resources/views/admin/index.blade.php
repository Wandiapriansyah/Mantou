<x-layouts.app title="Ringkasan" active="index">
  <x-layouts.page-header title="Ringkasan" subtitle="Selamat datang, Bu Ratna. Ini kabar warung dari catatan usaha Anda." subtitle-id="greet">
    <x-forms.button href="/cek-harga" icon="barcode">Cek harga</x-forms.button>
    <x-forms.button href="/catatan-transaksi" variant="primary" icon="plus">Catat transaksi</x-forms.button>
  </x-layouts.page-header>

  <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
    <div class="inline-flex rounded-xl bg-line p-1 text-[13px] font-semibold text-muted" role="tablist" aria-label="Periode">
      <button type="button" role="tab" data-p="today" class="rounded-lg px-4 py-2">Hari ini</button>
      <button type="button" role="tab" data-p="7" class="rounded-lg px-4 py-2">7 hari terakhir</button>
      <button type="button" role="tab" data-p="month" class="rounded-lg px-4 py-2">Bulan ini</button>
    </div>
    <div class="flex min-w-[180px] items-center gap-3 rounded-lg border border-line bg-surface px-3.5 py-2.5 text-[13px]">
      <span class="text-muted"><x-ui.icon name="calendar" size="size-[18px]" /></span>
      <span id="rangeLabel" class="flex-1 text-left"></span>
    </div>
  </div>

  <section class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-5" aria-label="Angka utama">
    <x-ui.stat-card id="kNet" size="lg" highlight class="sm:col-span-2" label="Laba bersih tercatat" icon="wallet" caption="Laba kotor – beban operasional" hint="Laba setelah dikurangi biaya operasional." />
    <x-ui.stat-card id="kRev" size="sm" label="Pendapatan penjualan" icon="document" caption="0 catatan penjualan" />
    <x-ui.stat-card id="kGross" size="sm" label="Laba kotor" icon="chart-line" caption="Pendapatan – HPP Rp0" hint="HPP: modal barang yang terjual." />
    <x-ui.stat-card id="kOpex" size="sm" label="Beban operasional" icon="arrow-up-right" caption="0 catatan pengeluaran · di luar HPP" />
  </section>

  <div class="mt-5 grid gap-4 xl:grid-cols-[1fr_360px]">
    <x-ui.card title="Pendapatan harian" subtitle-id="chartSub">
      <x-slot:aside>
        <x-ui.badge id="chartBadge">7 hari</x-ui.badge>
      </x-slot:aside>
      <x-widgets.chart-container :height="200" empty-text="Belum ada penjualan pada periode ini" />
    </x-ui.card>

    <x-ui.card title="Stok perlu perhatian" subtitle="Di bawah batas minimum">
      <x-slot:aside>
        <x-ui.badge id="lowBadge" tone="warn">0 produk</x-ui.badge>
      </x-slot:aside>
      <ul id="lowList" class="mt-3"></ul>
      <a href="/produk-stok" class="mt-2 inline-block text-[13px] font-bold text-brand-dark hover:underline">Kelola barang masuk →</a>
    </x-ui.card>

    <x-ui.card flush>
      <div class="flex items-center justify-between px-5 py-5">
        <h2 class="text-[18px] font-semibold">Catatan terbaru</h2>
        <a href="/catatan-transaksi" class="text-[13px] font-bold text-brand-dark hover:underline">Lihat semua →</a>
      </div>
      <x-ui.table :headers="['Waktu', 'Catatan', 'Jenis', ['Nominal', 'right']]" body-id="recentBody" :min-width="520" />
    </x-ui.card>

    <section class="rounded-xl border border-line bg-surface p-6">
      <div class="flex items-center justify-between">
        <h2 class="text-[16px] font-semibold">Hari ini di warung</h2>
        <span class="text-brand-dark"><x-ui.icon name="sun" /></span>
      </div>
      <p id="tRev" class="mt-7 text-[34px] font-bold leading-none tracking-tight">Rp0</p>
      <p id="tCap" class="mt-5 text-[12px] text-muted">Belum ada penjualan hari ini</p>
      <div class="mt-5 flex justify-between border-t border-line pt-5">
        <div>
          <p class="text-[12px] text-muted">Beban operasional</p>
          <p id="tOpex" class="mt-1.5 text-[15px] font-bold">Rp0</p>
        </div>
        <div class="text-right">
          <p class="text-[12px] text-muted">Laba kotor</p>
          <p id="tGross" class="mt-1.5 text-[15px] font-bold">Rp0</p>
        </div>
      </div>
    </section>
  </div>

  <p class="mt-6 text-[12px] text-muted">Ringkasan hanya berasal dari aktivitas yang dicatat di Catatan transaksi. Stok fisik dan transaksi di luar catatan tidak terpantau otomatis.</p>

@push('scripts')
<script>
  (function () {
    var M = window.Mantou, F = M.fmt, rp = F.rupiah, period = '7';
    var $ = function (id) { return document.getElementById(id); };
    var esc = function (t) { return String(t).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };
    function dayStart(ts) { var d = new Date(ts); return new Date(d.getFullYear(), d.getMonth(), d.getDate()).getTime(); }
    function addDays(ts, n) { var d = new Date(ts); return new Date(d.getFullYear(), d.getMonth(), d.getDate() + n).getTime(); }
    function sum(l, k) { return l.reduce(function (a, t) { return a + t[k]; }, 0); }
    function niceStep(v) { if (v <= 0) return 1; var e = Math.pow(10, Math.floor(Math.log(v) / Math.LN10)), f = v / e; return (f <= 1 ? 1 : f <= 2 ? 2 : f <= 2.5 ? 2.5 : f <= 5 ? 5 : 10) * e; }
    function axis(v) { if (v === 0) return '0'; if (v >= 1e6) return String(Math.round(v / 1e5) / 10).replace('.', ',') + ' jt'; return Math.round(v / 1000) + ' rb'; }
    function rangeLabel(f, t) {
      var a = new Date(f), b = new Date(t);
      if (f === t) return F.longDate(f);
      if (a.getMonth() === b.getMonth() && a.getFullYear() === b.getFullYear()) return a.getDate() + '–' + b.getDate() + ' ' + F.monthName(b.getMonth()) + ' ' + b.getFullYear();
      return a.getDate() + ' ' + F.monthShort(a.getMonth()) + ' – ' + b.getDate() + ' ' + F.monthShort(b.getMonth()) + ' ' + b.getFullYear();
    }
    function periodRange(p) {
      var t = dayStart(Date.now()), f = t;
      if (p === '7') f = addDays(t, -6); else if (p === 'month') { var d = new Date(t); f = new Date(d.getFullYear(), d.getMonth(), 1).getTime(); }
      return { from: f, to: t };
    }

    function greeting() {
      var h = new Date().getHours(), w = h < 11 ? 'pagi' : h < 15 ? 'siang' : h < 18 ? 'sore' : 'malam';
      $('greet').textContent = 'Selamat ' + w + ', Bu Ratna. Ini kabar warung dari catatan usaha Anda.';
    }

    function render() {
      var s = M.get(), r = periodRange(period), end = addDays(r.to, 1) - 1;
      var within = function (list, f, e) { return list.filter(function (t) { return t.ts >= f && t.ts <= e; }); };
      var inP = within(s.tx, r.from, end);
      var sales = inP.filter(function (t) { return t.type === 'sale'; }), exps = inP.filter(function (t) { return t.type === 'expense'; });
      var rev = sum(sales, 'revenue'), cogs = sum(sales, 'cogs'), opex = sum(exps, 'amount'), gross = rev - cogs, net = gross - opex;

      Array.prototype.forEach.call(document.querySelectorAll('[data-p]'), function (b) {
        var on = b.getAttribute('data-p') === period;
        b.className = 'rounded-lg px-4 py-2' + (on ? ' bg-surface text-ink' : '');
        if (on) b.setAttribute('aria-selected', 'true'); else b.removeAttribute('aria-selected');
      });
      $('rangeLabel').textContent = rangeLabel(r.from, r.to);
      var short = period === 'today' ? 'hari ini' : rangeLabel(r.from, r.to).replace(/ \d{4}$/, '');
      $('kRev').textContent = rp(rev); $('kRevCap').textContent = sales.length + ' catatan penjualan · ' + short;
      $('kOpex').textContent = rp(opex); $('kOpexCap').textContent = exps.length + ' catatan pengeluaran · di luar HPP';
      $('kGross').textContent = rp(gross); $('kGrossCap').textContent = 'Pendapatan – HPP ' + rp(cogs);
      $('kNet').textContent = rp(net); $('kNet').classList.toggle('text-danger-dark', net < 0); $('kNet').classList.toggle('text-ink', net >= 0);

      chart(s, r);
      stock(s);
      recent(s);
      today(s);
    }

    function chart(s, r) {
      var first = period === 'today' ? addDays(r.to, -6) : Math.max(r.from, addDays(r.to, -61)), days = [], map = {};
      for (var d = first; d <= r.to; d = addDays(d, 1)) { days.push(d); map[d] = 0; }
      s.tx.forEach(function (t) { if (t.type === 'sale' && map[dayStart(t.ts)] !== undefined) map[dayStart(t.ts)] += t.revenue; });
      var max = 0; days.forEach(function (d) { max = Math.max(max, map[d]); });
      var step = niceStep(max > 0 ? max / 3 : 100000), top = step * 3, few = days.length <= 8, gap = few ? '1.25rem' : '3px', todayD = dayStart(Date.now());
      $('yAxis').innerHTML = [3, 2, 1, 0].map(function (k) { return '<span>' + axis(step * k) + '</span>'; }).join('');
      $('bars').style.gap = gap; $('xAxis').style.gap = gap;
      $('bars').innerHTML = days.map(function (d) {
        var h = map[d] > 0 ? Math.max(2, map[d] / top * 100) : 0;
        return '<div class="flex min-w-0 flex-1 flex-col items-center justify-end gap-3"><div title="' + F.shortDate(d) + ' · ' + rp(map[d]) + '" class="w-full max-w-[34px] rounded-t-sm ' + (d === todayD ? 'bg-brand-dark' : 'bg-brand') + '" style="height:' + h + '%"></div></div>';
      }).join('');
      var every = few ? 1 : Math.ceil(days.length / 7);
      $('xAxis').innerHTML = days.map(function (d, k) {
        var txt = k % every === 0 ? (few ? F.shortDate(d) : String(new Date(d).getDate())) : '';
        return '<span class="min-w-0 flex-1 whitespace-nowrap text-center' + (d === todayD ? ' font-bold text-ink' : '') + '">' + txt + '</span>';
      }).join('');
      $('chartEmpty').classList.toggle('hidden', max > 0); $('chartEmpty').classList.toggle('grid', max === 0);
      $('chartSub').textContent = rangeLabel(days[0], days[days.length - 1]) + ' · berdasarkan penjualan yang dicatat';
      $('chartBadge').textContent = days.length + ' hari';
    }

    function stock(s) {
      var low = s.products.filter(function (p) { return p.stock < p.min; }).sort(function (a, b) { return a.stock / a.min - b.stock / b.min; });
      $('lowBadge').textContent = low.length + ' produk';
      $('lowBadge').className = 'rounded-md px-2.5 py-1 text-[12px] font-bold ' + (low.length ? 'border border-warning bg-warn-bg text-warn' : 'bg-tint text-brand-dark');
      $('lowList').innerHTML = low.length ? low.slice(0, 5).map(function (p, i) {
        return '<li class="flex items-center gap-3.5 py-3.5' + (i ? ' border-t border-line' : '') + '"><span class="grid size-10 place-items-center rounded-lg border border-warning bg-warn-bg text-[13px] font-bold text-warn">' + p.stock + '</span>' +
          '<a href="/produk-stok" class="group"><span class="block text-[13px] font-bold text-ink group-hover:text-brand-dark group-hover:underline">' + esc(p.name) + '</span><span class="block text-[12px] text-muted">' + (p.stock === 0 ? 'Habis' : p.stock + ' ' + esc(p.unit) + ' tersisa') + ' · minimum ' + p.min + '</span></a></li>';
      }).join('') : '<li class="py-6 text-[12px] text-muted">Semua produk masih di atas batas minimum.</li>';
    }

    function recent(s) {
      var list = s.tx.slice().sort(function (a, b) { return b.ts - a.ts; }).slice(0, 5), todayK = F.dateInput(Date.now());
      $('recentBody').innerHTML = list.length ? list.map(function (t) {
        var sale = t.type === 'sale', when = F.dateInput(t.ts) === todayK ? F.time(t.ts) : F.dateTime(t.ts);
        return '<tr class="border-t border-line"><td class="whitespace-nowrap px-5 py-4 text-[13px] font-bold">' + when + '</td>' +
          '<td class="px-3 py-4"><span class="block text-[13px] font-bold">' + esc(M.summary(t)) + '</span><span class="block text-[12px] text-muted">' + t.id + ' · ' + (sale ? t.units + ' unit' : esc(t.category)) + '</span></td>' +
          '<td class="px-3 py-4"><span class="rounded-md px-2.5 py-1 text-[12px] font-bold ' + (sale ? 'bg-tint text-brand-dark' : 'bg-line text-muted') + '">' + (sale ? 'Penjualan' : 'Pengeluaran') + '</span></td>' +
          '<td class="px-5 py-4 text-right text-[13px] font-semibold">' + rp(sale ? t.revenue : t.amount) + '</td></tr>';
      }).join('') : '<tr class="border-t border-line"><td colspan="4" class="px-5 py-8 text-center text-[13px] text-muted">Belum ada catatan. Mulai dari <a href="/catatan-transaksi" class="font-semibold text-brand-dark underline">Catat transaksi</a>.</td></tr>';
    }

    function today(s) {
      var t0 = dayStart(Date.now()), e = addDays(t0, 1) - 1;
      var l = s.tx.filter(function (t) { return t.ts >= t0 && t.ts <= e; }), sl = l.filter(function (t) { return t.type === 'sale'; }), ex = l.filter(function (t) { return t.type === 'expense'; });
      $('tRev').textContent = rp(sum(sl, 'revenue'));
      $('tCap').textContent = sl.length ? 'Pendapatan dari ' + sl.length + ' catatan penjualan' : 'Belum ada penjualan hari ini';
      $('tOpex').textContent = rp(sum(ex, 'amount')); $('tGross').textContent = rp(sum(sl, 'revenue') - sum(sl, 'cogs'));
    }

    Array.prototype.forEach.call(document.querySelectorAll('[data-p]'), function (b) { b.addEventListener('click', function () { period = b.getAttribute('data-p'); render(); }); });
    M.onChange(render);
    window.addEventListener('pageshow', render);
    greeting(); render();
  })();
</script>
@endpush
</x-layouts.app>
