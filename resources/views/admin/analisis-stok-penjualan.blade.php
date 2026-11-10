<x-layouts.app title="Analisis stok & penjualan" active="analisis-stok-penjualan">
  <x-layouts.page-header title="Analisis stok & penjualan" subtitle="Kenali produk cepat dan lambat bergerak dari riwayat penjualan yang dicatat.">
    <x-forms.select id="per" aria-label="Periode" :options="['7' => '7 hari terakhir', '14' => '14 hari terakhir', '30' => '30 hari terakhir', 'month' => 'Bulan ini']" selected="7" />
    <x-forms.select id="cover" aria-label="Batas stok berlebih" :options="['7' => 'Stok cukup > 7 hari', '10' => 'Stok cukup > 10 hari', '14' => 'Stok cukup > 14 hari', '21' => 'Stok cukup > 21 hari', '30' => 'Stok cukup > 30 hari']" selected="10" />
  </x-layouts.page-header>

  <p id="rangeLbl" class="mt-3 text-[12px] text-muted"></p>

  <section id="kpis" class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4"></section>

  <div class="mt-5 grid items-start gap-5 xl:grid-cols-[1.6fr_1fr]">
    <x-ui.card title="Penjualan harian" subtitle="Unit terjual dari seluruh produk · bukan stok fisik">
      <x-slot:aside>
        <x-ui.badge id="chartBadge" tone="brand"></x-ui.badge>
      </x-slot:aside>
      <x-widgets.chart-container :height="180" compact empty-text="Belum ada penjualan pada periode ini." />
    </x-ui.card>

    <div id="insights" class="flex flex-col gap-4"></div>
  </div>

  <x-ui.card class="mt-5" flush title="Pergerakan produk" subtitle-id="tblSub">
    <x-slot:aside>
      <x-forms.select id="cat" aria-label="Kategori" />
    </x-slot:aside>
    <x-ui.table :headers="['Produk', ['Unit terjual', 'right'], ['Stok kini', 'right'], ['Minimum', 'right'], ['Stok cukup', 'right'], 'Pergerakan', 'Tindak lanjut']" body-id="rows" :min-width="820" />
    <p id="tblFoot" class="border-t border-line px-6 py-4 text-[12px] text-muted"></p>
  </x-ui.card>

  <p class="mt-6 flex items-center gap-2 text-[12px] text-muted">Analisis berdasarkan riwayat penjualan yang dicatat pada periode terpilih. Laju jual rata-rata bukan prediksi permintaan; periksa kondisi dan stok fisik sebelum mengambil keputusan.</p>

@push('scripts')
<script>
  (function () {
    var products = @json($analysisProducts);
    var transactions = @json($analysisTransactions);
    var products = @json($analysisProducts);
    var transactions = @json($analysisTransactions);
    var $ = function (id) { return document.getElementById(id); };
    var esc = function (t) { return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };
    var pct1 = function (n) { return (Math.round(n * 10) / 10).toString().replace('.', ','); };
    var qs = new URLSearchParams(location.search);
    var ICON = function (d) { return '<svg class="size-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + d + '</svg>'; };

    var MV = { cepat: ['bg-tint text-brand-dark', 'Cepat'], normal: ['bg-line text-muted', 'Normal'], lambat: ['border border-warning bg-warn-bg text-warn', 'Lambat'] };
    var DAY = 86400000;
    function range(p) {
        var now = new Date();
        var y = now.getFullYear();
        var m = now.getMonth();
        var d = now.getDate();

        var days = p === '14' ? 14 : p === '30' ? 30 : p === 'month' ? d : 7;

        var start = new Date(y, m, d - days + 1);
        var from = start.getTime();
        var to = new Date(y, m, d + 1).getTime() - 1;

        var formatter = new Intl.DateTimeFormat('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        });

        return {
            key: p,
            from: from,
            to: to,
            days: days,
            label: formatter.format(start) + ' – ' + formatter.format(new Date(to))
        };
    }
    function shortDate(ts) {
        var d = new Date(ts);

        return d.getDate() + ' ' +
            new Intl.DateTimeFormat('id-ID', {
                month: 'short'
            }).format(d);
    }
    function shortName(n) {
        return String(n).split(' ')[0];
    }
    function soldUnits(from, to) {
        var out = {};

        transactions.forEach(function (t) {
            if (t.ts < from || t.ts > to) return;

            t.items.forEach(function (item) {
                out[item.pid] = (out[item.pid] || 0) + item.qty;
            });
        });

        return out;
    }
    function analyze(r, coverDays) {
        var sold = soldUnits(r.from, r.to);
        var first = Infinity;

        transactions.forEach(function (t) {
            if (t.ts < first) first = t.ts;
        });

        var startDate = new Date(r.from);
        var firstDate = new Date(Math.max(r.from, first));

        startDate = new Date(
            startDate.getFullYear(),
            startDate.getMonth(),
            startDate.getDate()
        );

        firstDate = new Date(
            firstDate.getFullYear(),
            firstDate.getMonth(),
            firstDate.getDate()
        );

        var effectiveDays = first === Infinity
            ? r.days
            : Math.max(1, Math.min(
                r.days,
                Math.round((new Date(r.to).setHours(0, 0, 0, 0) - firstDate.getTime()) / DAY) + 1
            ));

        var list = products.map(function (p) {
            var units = sold[p.id] || 0;
            var rate = units / effectiveDays;

            return {
                p: p,
                sold: units,
                rate: rate,
                days: effectiveDays,
                cover: rate > 0
                    ? p.stock / rate
                    : (p.stock > 0 ? Infinity : 0),
                move: 'normal'
            };
        });

        var fast = list
            .filter(function (x) {
                return x.sold > 0;
            })
            .sort(function (a, b) {
                return b.sold - a.sold;
            })
            .slice(0, 3);

        list.forEach(function (x) {
            if (x.p.stock > 0 && x.p.stock >= x.p.min && x.cover > coverDays) {
                x.move = 'lambat';
            } else if (fast.indexOf(x) > -1) {
                x.move = 'cepat';
            }
        });

        return list;
    }
    function niceStep(v) { if (v <= 0) return 1; var e = Math.pow(10, Math.floor(Math.log(v) / Math.LN10)), f = v / e; return (f <= 1 ? 1 : f <= 2 ? 2 : f <= 2.5 ? 2.5 : f <= 5 ? 5 : 10) * e; }
    function card(label, val, cap, icon, hl) {
      return '<div class="rounded-xl border ' + (hl ? 'border-brand bg-tint' : 'border-line bg-surface') + ' p-5"><div class="flex items-center justify-between text-[12px] text-muted"><span>' + label + '</span><span class="text-brand-dark">' + ICON(icon) + '</span></div><p class="mt-4 text-[28px] font-bold leading-none tracking-tight text-ink">' + val + '</p><p class="mt-4 text-[12px] text-muted">' + cap + '</p></div>';
    }
    function chart(R) {
      var days = [], map = {}, d, i;
      for (i = 0; i < R.days; i++) { d = new Date(R.from + i * DAY); d = new Date(d.getFullYear(), d.getMonth(), d.getDate()).getTime(); days.push(d); map[d] = 0; }
      transactions.forEach(function (t) {
          if (t.ts < R.from || t.ts > R.to) return;

          var k = new Date(t.ts);
          k = new Date(k.getFullYear(), k.getMonth(), k.getDate()).getTime();

          if (map[k] !== undefined) map[k] += t.units;
      });
      var max = 0, tot = 0; days.forEach(function (x) { max = Math.max(max, map[x]); tot += map[x]; });
      var step = niceStep(max > 0 ? max / 3 : 10), top = step * 3, few = days.length <= 8, today = new Date(); today = new Date(today.getFullYear(), today.getMonth(), today.getDate()).getTime();
      $('yAxis').innerHTML = [3, 2, 1, 0].map(function (k) { return '<span>' + step * k + '</span>'; }).join('');
      $('bars').style.gap = few ? '1.25rem' : '3px'; $('xAxis').style.gap = few ? '1.25rem' : '3px';
      $('bars').innerHTML = days.map(function (x) {
        var h = map[x] > 0 ? Math.max(2, map[x] / top * 100) : 0;
        return '<div class="flex h-full min-w-0 flex-1 items-end justify-center" title="' + shortDate(x) + ' · ' + map[x] + ' unit"><div class="flex w-full max-w-[40px] justify-center rounded-t-sm ' + (x === today ? 'bg-brand' : 'bg-brand-soft') + '" style="height:' + h + '%">' + (few && map[x] ? '<span class="pt-1.5 text-[11px] font-bold text-surface">' + map[x] + '</span>' : '') + '</div></div>';
      }).join('');
      var every = few ? 1 : Math.ceil(days.length / 7);
      $('xAxis').innerHTML = days.map(function (x, k) { return '<span class="min-w-0 flex-1 whitespace-nowrap text-center' + (x === today ? ' font-bold text-ink' : '') + '">' + (k % every === 0 ? (few ? shortDate(x) : new Date(x).getDate()) : '') + '</span>'; }).join('');
      $('chartEmpty').classList.toggle('hidden', max > 0); $('chartEmpty').classList.toggle('grid', max === 0);
      $('chartBadge').textContent = tot + ' unit';
    }
    function render() {
      var R = range($('per').value);
      var A = analyze(R, +$('cover').value);
      $('rangeLbl').textContent = R.label + ' · laju jual dihitung dari ' + (A[0] ? A[0].days : R.days) + ' hari riwayat penjualan';
      var tot = 0, top = null, slow = A.filter(function (x) { return x.move === 'lambat'; }), low = A.filter(function (x) { return x.p.stock < x.p.min; });
      A.forEach(function (x) { tot += x.sold; if (x.sold > 0 && (!top || x.sold > top.sold)) top = x; });
      $('kpis').innerHTML =
        card('Unit terjual', tot.toLocaleString('id-ID') + ' unit', A.length + ' produk · ' + R.label.replace(/ \d{4}$/, ''), '<path d="M4 20h16M8 20v-8M12 20V6M16 20v-5"/>', true) +
        card('Produk paling laris', top ? top.sold + ' unit' : '—', top ? esc(top.p.name) : 'Belum ada penjualan', '<path d="M5 12.5l4.5 4.5L19 7.5"/>') +
        card('Bergerak lambat', slow.length + ' produk', 'Stok lebih dari minimum &amp; cukup &gt; ' + $('cover').value + ' hari', '<circle cx="12" cy="12" r="8.5"/><path d="M12 7v5l3 2"/>') +
        card('Perlu tambah stok', low.length + ' produk', 'Stok di bawah batas minimum', '<path d="M12 4l9 16H3L12 4z"/><path d="M12 10v4M12 17v.5"/>');
      chart(R);

      var ins = '', need = A.filter(function (x) { return x.p.stock < x.p.min; }).sort(function (a, b) { return a.cover - b.cover; })[0];
      if (need) ins += '<section class="rounded-xl bg-tint p-5"><p class="flex items-center gap-3 text-[15px] font-bold"><span class="text-brand-dark">' + ICON('<path d="M12 3l7 4v5M4 7l8 4.5M4 7v9l8 4.5M12 11.5V21"/><path d="M18 15v6M15 18h6"/>') + '</span>Prioritaskan ' + esc(shortName(need.p.name)) + '</p><p class="mt-3 text-[12px] leading-relaxed text-muted">' + (need.sold ? 'Terjual ' + need.sold + ' unit (rata-rata ' + pct1(need.rate) + '/hari), ' : 'Belum terjual pada periode ini, ') + 'stok tersisa ' + need.p.stock + ' dari minimum ' + need.p.min + (need.sold ? ' — cukup sekitar ' + pct1(need.cover) + ' hari.' : '.') + ' Tinjau kebutuhan barang masuk berikutnya.</p><a href="/produk-stok?aksi=masuk&pid=' + need.p.id + '" class="mt-3 inline-block text-[12px] font-bold text-brand-dark hover:underline">Catat barang masuk →</a></section>';
      else ins += '<section class="rounded-xl bg-tint p-5"><p class="text-[15px] font-bold">Stok aman</p><p class="mt-3 text-[12px] leading-relaxed text-muted">Semua produk berada di atas batas minimum.</p></section>';
      if (slow.length) {
      var u = 0, st = 0;
      slow.forEach(function (x) {
          u += x.sold;
          st += x.p.stock;
      });

      ins += '<section class="rounded-xl border border-warning bg-warn-bg p-5"><p class="flex items-center gap-3 text-[15px] font-bold"><span class="text-warn">' + ICON('<circle cx="12" cy="12" r="8.5"/><path d="M9 15l6-6"/>') + '</span>Tinjau stok ' + slow.length + ' produk lambat</p><p class="mt-3 text-[12px] leading-relaxed text-muted">' + slow.slice(0, 3).map(function (x) {
          return esc(shortName(x.p.name));
      }).join(', ') + (slow.length > 3 ? ' dan lainnya' : '') + ': total stok ' + st + ' unit, hanya ' + u + ' unit terjual dalam periode ini.</p><a href="/rekomendasi-diskon?p=' + R.key + '" class="mt-3 inline-block text-[12px] font-bold text-brand-dark hover:underline">Lihat rekomendasi diskon →</a></section>';
      } else ins += '<section class="rounded-xl border border-line bg-canvas p-5"><p class="text-[15px] font-bold">Tidak ada produk lambat</p><p class="mt-3 text-[12px] leading-relaxed text-muted">Tidak ada produk dengan stok berlebih untuk batas yang dipilih.</p></section>';
      $('insights').innerHTML = ins;

      var cur = $('cat').value, cats = []; products.forEach(function (p) {if (cats.indexOf(p.category) < 0) cats.push(p.category); }); cats.sort();
      $('cat').innerHTML = '<option value="">Semua kategori</option>' + cats.map(function (c) { return '<option' + (c === cur ? ' selected' : '') + '>' + esc(c) + '</option>'; }).join('');
      var shown = A.filter(function (x) { return !$('cat').value || x.p.category === $('cat').value; }).sort(function (a, b) { return b.sold - a.sold; });
      $('tblSub').textContent = 'Cepat: 3 teratas berdasarkan unit · Lambat: stok di atas minimum dan cukup > ' + $('cover').value + ' hari';
      $('rows').innerHTML = shown.length ? shown.map(function (x) {
        var m = MV[x.move], p = x.p, act = p.stock < p.min ? '<a class="font-semibold text-brand-dark hover:underline" href="/produk-stok?aksi=masuk&pid=' + p.id + '">Tinjau barang masuk</a>' : x.move === 'lambat' ? '<a class="font-semibold text-brand-dark hover:underline" href="/rekomendasi-diskon?pid=' + p.id + '&p=' + R.key + '">Tinjau diskon, jaga margin</a>' : '<span class="text-muted">Pantau stok rutin</span>';
        return '<tr class="border-t border-line"><td class="px-5 py-4 text-[13px]"><b>' + esc(p.name) + '</b></td><td class="px-3 py-4 text-right text-[13px]">' + x.sold + '</td><td class="px-3 py-4 text-right text-[13px]' + (p.stock < p.min ? ' font-bold text-warn' : '') + '">' + p.stock + '</td><td class="px-3 py-4 text-right text-[13px]">' + p.min + '</td><td class="px-3 py-4 text-right text-[13px]">' + (x.cover === Infinity ? '—' : pct1(x.cover) + ' hr') + '</td><td class="px-3 py-4"><span class="inline-block rounded-md px-2.5 py-1 text-[12px] font-bold ' + m[0] + '">' + m[1] + '</span></td><td class="px-5 py-4 text-[13px]">' + act + '</td></tr>';
      }).join('') : '<tr class="border-t border-line"><td colspan="7" class="px-5 py-10 text-center text-[13px] text-muted">Belum ada produk. Tambahkan lewat <a class="font-semibold text-brand-dark underline" href="/produk-stok">Produk &amp; stok</a>.</td></tr>';
      var su = shown.reduce(function (a, x) { return a + x.sold; }, 0);
      $('tblFoot').textContent = shown.length + ' produk ditampilkan dari ' + A.length + ' · ' + su + ' dari ' + tot + ' unit terjual pada periode.';
    }
    var P0 = qs.get('p'); if (P0) $('per').value = P0; if (!$('per').value) $('per').value = '7';
    ['per', 'cover', 'cat'].forEach(function (id) { $(id).addEventListener('change', render); });
    window.addEventListener('pageshow', render);
    render();
  })();
</script>
@endpush
</x-layouts.app>
