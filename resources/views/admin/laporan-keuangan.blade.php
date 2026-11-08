<x-layouts.app title="Laporan keuangan" active="laporan-keuangan">
  <x-layouts.page-header title="Laporan keuangan" subtitle="Lihat hasil usaha dari pendapatan dan pengeluaran yang sudah dicatat.">
    <x-forms.button id="exportBtn" icon="download">Ekspor laporan</x-forms.button>
  </x-layouts.page-header>

  <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
    <p class="flex items-center gap-3 text-[15px] font-bold">
      <span id="rptTitle">Laporan mingguan</span>
      <x-ui.badge id="rptCount">0 catatan</x-ui.badge>
    </p>
    <div class="flex flex-wrap items-center gap-2.5">
      <x-forms.select id="preset" aria-label="Periode" class="font-bold" :options="['today' => 'Hari ini', '7' => '7 hari terakhir', '30' => '30 hari terakhir', 'month' => 'Bulan ini', 'custom' => 'Rentang kustom']" selected="7" />
      <x-forms.input id="from" type="date" size="sm" aria-label="Dari tanggal" />
      <span class="text-[12px] text-muted">–</span>
      <x-forms.input id="to" type="date" size="sm" aria-label="Sampai tanggal" />
      <x-forms.button id="applyBtn" class="text-brand-dark">Terapkan</x-forms.button>
    </div>
  </div>

  <section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <x-ui.stat-card id="kRev" label="Pendapatan penjualan" caption="Total 0 catatan penjualan" />
    <x-ui.stat-card id="kCogs" label="Harga pokok terjual (HPP)" caption="Biaya perolehan produk yang terjual" hint="HPP: modal barang yang terjual." />
    <x-ui.stat-card id="kOpex" label="Beban operasional" caption="Biaya usaha di luar HPP · 0 catatan" />
    <x-ui.stat-card id="kNet" label="Laba bersih tercatat" caption="Rp0 – Rp0" hint="Laba kotor dikurangi biaya operasional." highlight />
  </section>

  <div class="mt-5 grid gap-5 xl:grid-cols-[1.35fr_1fr]">
    <x-ui.card title="Pendapatan & beban harian" subtitle-id="chartSub">
      <div class="mt-4 flex gap-5 text-[12px] text-muted">
        <span class="flex items-center gap-2"><i class="size-2 rounded-sm bg-brand"></i>Pendapatan penjualan</span>
        <span class="flex items-center gap-2"><i class="size-2 rounded-sm bg-brand-soft"></i>Beban operasional</span>
      </div>
      <x-widgets.chart-container :height="200" :align-end="false" empty-text="Belum ada catatan pada periode ini" />
    </x-ui.card>

    <x-ui.card title="Dari penjualan ke laba" subtitle="Perhitungan untuk periode terpilih">
      <div class="mt-5">
        <div class="flex items-center justify-between py-1.5 text-[13px]"><span class="font-bold">Pendapatan penjualan</span><span id="wRev" class="font-bold">Rp0</span></div>
        <div class="flex justify-between py-1.5 text-[13px] text-muted"><span>Dikurangi HPP (modal barang terjual)</span><span id="wCogs">– Rp0</span></div>
        <div class="my-2 flex justify-between rounded-lg border border-line bg-surface px-4 py-3 text-[13px] font-bold"><span>Laba kotor</span><span id="wGross">Rp0</span></div>
        <div class="flex justify-between py-1.5 text-[13px] text-muted"><span>Dikurangi beban operasional</span><span id="wOpex">– Rp0</span></div>
      </div>
      <div class="mt-4 flex items-center justify-between border-t border-line pt-5">
        <span class="text-[14px] font-bold">Laba bersih tercatat</span>
        <span id="wNet" class="text-[26px] font-bold text-brand-dark">Rp0</span>
      </div>
    </x-ui.card>
  </div>

  <x-ui.card class="mt-5" flush title="Rincian catatan keuangan" subtitle-id="tblSub">
    <x-slot:aside>
      <x-forms.select id="typeFilter" aria-label="Jenis catatan" class="font-bold" :options="['all' => 'Semua jenis', 'sale' => 'Penjualan', 'expense' => 'Pengeluaran']" />
    </x-slot:aside>
    <x-ui.table :headers="['Tanggal & waktu', 'Keterangan', 'Jenis', ['Pendapatan', 'right'], ['HPP', 'right'], ['Beban operasional', 'right']]" body-id="tbody" :min-width="760" />
    <div id="moreWrap" class="hidden border-t border-line px-6 py-4 text-center">
      <x-forms.button id="moreBtn" size="compact" class="text-brand-dark">Tampilkan lebih banyak</x-forms.button>
    </div>
  </x-ui.card>

  <p class="mt-6 flex items-center gap-2 text-[12px] text-muted">Hanya mencakup data yang dicatat di Catatan transaksi. HPP memakai biaya perolehan saat penjualan dicatat. Ringkasan ini bukan layanan akuntansi profesional atau pelaporan pajak.</p>

@push('scripts')
<script>
  (function () {
    var M = window.Mantou, rp = M.fmt.rupiah, F = M.fmt, DAY = 86400000;
    var $ = function (id) { return document.getElementById(id); };
    var esc = function (t) { return String(t).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };
    var range = null, limit = 10, shown = [];
    var TITLES = { today: 'Laporan harian', '7': 'Laporan mingguan', '30': 'Laporan 30 hari', month: 'Laporan bulanan', custom: 'Laporan periode' };

    function dayStart(ts) { var d = new Date(ts); return new Date(d.getFullYear(), d.getMonth(), d.getDate()).getTime(); }
    function addDays(ts, n) { var d = new Date(ts); return new Date(d.getFullYear(), d.getMonth(), d.getDate() + n).getTime(); }

    function presetRange(p) {
      var t = dayStart(Date.now()), f = t;
      if (p === '7') f = addDays(t, -6); else if (p === '30') f = addDays(t, -29); else if (p === 'month') { var d = new Date(t); f = new Date(d.getFullYear(), d.getMonth(), 1).getTime(); }
      return [f, t];
    }
    function rangeLabel(f, t) {
      var a = new Date(f), b = new Date(t);
      if (f === t) return F.longDate(f);
      if (a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth()) return a.getDate() + '–' + b.getDate() + ' ' + F.monthName(b.getMonth()) + ' ' + b.getFullYear();
      if (a.getFullYear() === b.getFullYear()) return a.getDate() + ' ' + F.monthShort(a.getMonth()) + ' – ' + b.getDate() + ' ' + F.monthShort(b.getMonth()) + ' ' + b.getFullYear();
      return F.longDate(f) + ' – ' + F.longDate(t);
    }

    function apply() {
      var f = F.parseDate($('from').value), t = F.parseDate($('to').value);
      if (isNaN(f) || isNaN(t)) return;
      if (f > t) { var x = f; f = t; t = x; $('from').value = F.dateInput(f); $('to').value = F.dateInput(t); }
      range = { from: f, to: addDays(t, 1) - 1, fromDay: f, toDay: t };
      limit = 10; render();
    }

    function niceStep(v) {
      if (v <= 0) return 1;
      var e = Math.pow(10, Math.floor(Math.log(v) / Math.LN10)), f = v / e;
      return (f <= 1 ? 1 : f <= 2 ? 2 : f <= 2.5 ? 2.5 : f <= 5 ? 5 : 10) * e;
    }
    function axis(v) {
      if (v === 0) return '0';
      if (v >= 1e6) return String(Math.round(v / 1e5) / 10).replace('.', ',') + ' jt';
      return Math.round(v / 1000) + ' rb';
    }
    function sum(list, k) { return list.reduce(function (a, t) { return a + t[k]; }, 0); }

    function render() {
      var all = M.get().tx.filter(function (t) { return t.ts >= range.from && t.ts <= range.to; });
      var sales = all.filter(function (t) { return t.type === 'sale'; }), exps = all.filter(function (t) { return t.type === 'expense'; });
      var rev = sum(sales, 'revenue'), cogs = sum(sales, 'cogs'), opex = sum(exps, 'amount'), gross = rev - cogs, net = gross - opex;

      $('rptTitle').textContent = TITLES[$('preset').value] || TITLES.custom;
      $('rptCount').textContent = all.length + ' catatan';
      $('kRev').textContent = rp(rev); $('kRevCap').textContent = 'Total ' + sales.length + ' catatan penjualan';
      $('kCogs').textContent = rp(cogs);
      $('kOpex').textContent = rp(opex); $('kOpexCap').textContent = 'Biaya usaha di luar HPP · ' + exps.length + ' catatan';
      $('kNet').textContent = rp(net); $('kNetCap').textContent = rp(gross) + ' – ' + rp(opex);
      $('wRev').textContent = rp(rev); $('wCogs').textContent = '– ' + rp(cogs); $('wGross').textContent = rp(gross);
      $('wOpex').textContent = '– ' + rp(opex); $('wNet').textContent = rp(net);
      ['kNet', 'wNet'].forEach(function (id) { $(id).classList.toggle('text-danger-dark', net < 0); $(id).classList.toggle('text-brand-dark', id === 'wNet' && net >= 0); $(id).classList.toggle('text-ink', id === 'kNet' && net >= 0); });

      renderChart(all);
      renderTable(all);
    }

    function renderChart(all) {
      var first = Math.max(range.fromDay, addDays(range.toDay, -61)), days = [], map = {}, i;
      for (var d = first; d <= range.toDay; d = addDays(d, 1)) { days.push(d); map[d] = { rev: 0, opex: 0 }; }
      all.forEach(function (t) { var m = map[dayStart(t.ts)]; if (!m) return; if (t.type === 'sale') m.rev += t.revenue; else m.opex += t.amount; });
      var max = 0; days.forEach(function (d) { max = Math.max(max, map[d].rev, map[d].opex); });
      var step = niceStep(max > 0 ? max / 3 : 100000), top = step * 3;
      $('yAxis').innerHTML = [3, 2, 1, 0].map(function (k) { return '<span>' + axis(step * k) + '</span>'; }).join('');
      var few = days.length <= 8, gap = few ? '1.25rem' : '2px', today = dayStart(Date.now());
      $('bars').style.gap = gap; $('xAxis').style.gap = gap;
      $('bars').innerHTML = days.map(function (d) {
        var m = map[d], h = function (v) { return v > 0 ? Math.max(2, v / top * 100) : 0; }, lab = F.shortDate(d);
        return '<div class="flex min-w-0 flex-1 items-end justify-center gap-px">' +
          '<div title="' + lab + ' · Pendapatan ' + rp(m.rev) + '" class="w-full rounded-t-sm bg-brand" style="max-width:34px;height:' + h(m.rev) + '%"></div>' +
          '<div title="' + lab + ' · Beban ' + rp(m.opex) + '" class="w-full rounded-t-sm bg-brand-soft" style="max-width:34px;height:' + h(m.opex) + '%"></div></div>';
      }).join('');
      var every = few ? 1 : Math.ceil(days.length / 7);
      $('xAxis').innerHTML = days.map(function (d, k) {
        var txt = (k % every === 0) ? (few ? F.shortDate(d) : String(new Date(d).getDate())) : '';
        return '<span class="min-w-0 flex-1 whitespace-nowrap text-center' + (d === today ? ' font-bold text-ink' : '') + '">' + txt + '</span>';
      }).join('');
      $('chartEmpty').classList.toggle('hidden', max > 0); $('chartEmpty').classList.toggle('grid', max === 0);
      $('chartSub').textContent = 'Dalam ribuan Rupiah · ' + rangeLabel(range.fromDay, range.toDay);
    }

    function renderTable(all) {
      var type = $('typeFilter').value;
      shown = all.filter(function (t) { return type === 'all' || t.type === type; }).sort(function (a, b) { return b.ts - a.ts; });
      var rows = shown.slice(0, limit);
      $('tblSub').textContent = shown.length ? rows.length + ' catatan terbaru dari ' + shown.length + ' pada periode ini' : 'Tidak ada catatan pada periode ini';
      $('tbody').innerHTML = rows.length ? rows.map(function (t) {
        var sale = t.type === 'sale';
        return '<tr class="border-t border-line"><td class="px-5 py-4 text-[13px]"><b>' + F.dateTime(t.ts) + '</b></td>' +
          '<td class="px-3 py-4 text-[13px]"><span class="block font-semibold">' + esc(M.summary(t)) + '</span><span class="text-[12px] text-muted">' + t.id + ' · ' + (sale ? t.units + ' unit' : esc(t.category)) + '</span></td>' +
          '<td class="px-3 py-4 text-[13px]"><span class="inline-block rounded-md px-2.5 py-1 text-[12px] font-bold ' + (sale ? 'bg-tint text-brand-dark' : 'bg-line text-muted') + '">' + (sale ? 'Penjualan' : 'Pengeluaran') + '</span></td>' +
          '<td class="px-3 py-4 text-right text-[13px]">' + (sale ? rp(t.revenue) : '—') + '</td><td class="px-3 py-4 text-right text-[13px]">' + (sale ? rp(t.cogs) : '—') + '</td>' +
          '<td class="px-5 py-4 text-right text-[13px]">' + (sale ? '—' : rp(t.amount)) + '</td></tr>';
      }).join('') : '<tr class="border-t border-line"><td colspan="6" class="px-5 py-10 text-center text-[13px] text-muted">Belum ada catatan pada periode ini. Tambahkan lewat <a href="/catatan-transaksi" class="font-semibold text-brand-dark underline">Catatan transaksi</a>.</td></tr>';
      $('moreWrap').classList.toggle('hidden', shown.length <= limit);
    }

    function exportCsv() {
      var q = function (v) { return '"' + String(v).replace(/"/g, '""') + '"'; };
      var lines = [['Tanggal', 'Waktu', 'ID', 'Jenis', 'Keterangan', 'Pendapatan', 'HPP', 'Beban operasional'].map(q).join(',')];
      shown.slice().reverse().forEach(function (t) {
        var sale = t.type === 'sale';
        lines.push([F.dateInput(t.ts), F.time(t.ts), t.id, sale ? 'Penjualan' : 'Pengeluaran', M.summary(t), sale ? t.revenue : 0, sale ? t.cogs : 0, sale ? 0 : t.amount].map(q).join(','));
      });
      var blob = new Blob(['\ufeff' + lines.join('\r\n')], { type: 'text/csv;charset=utf-8' }), a = document.createElement('a');
      a.href = URL.createObjectURL(blob); a.download = 'laporan-keuangan_' + F.dateInput(range.fromDay) + '_' + F.dateInput(range.toDay) + '.csv';
      document.body.appendChild(a); a.click(); document.body.removeChild(a);
      setTimeout(function () { URL.revokeObjectURL(a.href); }, 1000);
    }

    $('preset').addEventListener('change', function () {
      if (this.value === 'custom') return;
      var r = presetRange(this.value); $('from').value = F.dateInput(r[0]); $('to').value = F.dateInput(r[1]); apply();
    });
    ['from', 'to'].forEach(function (id) { $(id).addEventListener('change', function () { $('preset').value = 'custom'; }); });
    $('applyBtn').onclick = apply;
    $('typeFilter').addEventListener('change', function () { limit = 10; render(); });
    $('moreBtn').onclick = function () { limit += 10; render(); };
    $('exportBtn').onclick = exportCsv;
    M.onChange(render);

    var r0 = presetRange('7'); $('from').value = F.dateInput(r0[0]); $('to').value = F.dateInput(r0[1]); apply();
  })();
</script>
@endpush
</x-layouts.app>
