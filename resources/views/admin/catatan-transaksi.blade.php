<x-layouts.app title="Catatan transaksi" active="catatan-transaksi">
  <x-layouts.page-header title="Catatan transaksi" subtitle="Dokumentasikan penjualan dan pengeluaran yang terjadi di warung.">
    <x-forms.button href="/laporan-keuangan" icon="list">Semua catatan</x-forms.button>
  </x-layouts.page-header>

  <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
    <div class="inline-flex rounded-xl bg-tint p-1 text-[13px] font-bold text-brand-dark" role="tablist" aria-label="Jenis catatan">
      <button type="button" role="tab" id="tabSale" aria-selected="true" class="inline-flex items-center gap-2 rounded-lg bg-surface px-4 py-2 text-ink">
        <x-ui.icon name="document" size="size-[18px]" />Penjualan
      </button>
      <button type="button" role="tab" id="tabExp" aria-selected="false" class="inline-flex items-center gap-2 rounded-lg px-4 py-2">
        <x-ui.icon name="arrow-up-right" size="size-[18px]" />Pengeluaran
      </button>
    </div>
    <p class="text-[12px] text-muted">Pencatatan manual · bukan layanan pembayaran</p>
  </div>

  <div class="mt-5 grid items-start gap-5 xl:grid-cols-[1fr_340px]">
    <div>
      <x-ui.card id="saleForm" radius="2xl" title="Catat penjualan" subtitle="Isi produk yang sudah terjual, lalu simpan catatannya.">
        <x-slot:aside>
          <x-ui.badge id="saleBadge">Draf</x-ui.badge>
        </x-slot:aside>
        <div class="mt-5 grid gap-4 sm:grid-cols-2">
          <x-forms.input id="saleTs" type="datetime-local" label="Tanggal & waktu penjualan" />
          <div>
            <label class="text-[12px] font-bold">Dicatat oleh</label>
            <div class="mt-2 flex items-center rounded-lg border border-line bg-surface px-3.5 py-3 text-[14px] text-muted" data-mantou-user>Pengguna</div>
          </div>
        </div>
        <div class="mt-6 hidden grid-cols-[1fr_88px_90px_130px] gap-x-4 pb-3 text-[12px] text-muted sm:grid">
          <span>Produk</span><span class="text-center">Jumlah</span><span class="text-center">Harga tersimpan</span><span class="text-right">Subtotal</span>
        </div>
        <div id="saleRows" class="mt-4 sm:mt-0"></div>
        <div class="border-t border-line pt-4">
          <x-forms.button id="addRow" icon="plus">Tambah produk</x-forms.button>
        </div>
        <x-forms.input id="saleNote" label="Catatan tambahan (opsional)" container="mt-5" maxlength="120" placeholder="Contoh: penjualan sore" />
        <div class="mt-5 flex items-center justify-between rounded-xl border border-line bg-surface px-6 py-5">
          <div>
            <p class="text-[13px] font-bold">Total penjualan</p>
            <p id="saleCount" class="mt-1 text-[12px] text-muted">0 jenis produk · 0 unit</p>
          </div>
          <p id="saleTotal" class="text-[34px] font-bold tracking-tight">Rp0</p>
        </div>
        <x-ui.alert variant="info" class="mt-5" icon="info" title="Apa yang terjadi setelah disimpan?" id="saleInfo">Pilih produk dan jumlahnya untuk melihat dampaknya pada stok dan laporan.</x-ui.alert>
        <x-ui.alert variant="error" id="saleErr" class="mt-4" />
        <x-ui.alert variant="success" id="saleDone" class="mt-4" />
        <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
          <p class="text-[12px] text-muted">Draf ini belum mengubah stok atau laporan.</p>
          <div class="flex items-center gap-5">
            <x-forms.button id="saleCancel" variant="link">Batal</x-forms.button>
            <x-forms.button id="saleSave" variant="primary" size="lg" icon="check">Simpan penjualan</x-forms.button>
          </div>
        </div>
      </x-ui.card>

      <x-ui.card id="expForm" class="hidden" radius="2xl" title="Catat pengeluaran" subtitle="Biaya operasional seperti ongkos angkut, listrik, atau perlengkapan.">
        <x-slot:aside>
          <x-ui.badge id="expBadge">Draf</x-ui.badge>
        </x-slot:aside>
        <div class="mt-5 grid gap-4 sm:grid-cols-2">
          <x-forms.input id="expTs" type="datetime-local" label="Tanggal & waktu pengeluaran" />
          <div>
            <label class="text-[12px] font-bold">Dicatat oleh</label>
            <div class="mt-2 flex items-center rounded-lg border border-line bg-surface px-3.5 py-3 text-[14px] text-muted" data-mantou-user>Pengguna</div>
          </div>
          <div>
            <x-forms.select id="expCat" label="Kategori" size="md" class="w-full" :options="['' => 'Pilih kategori…', 'Ongkos angkut' => 'Ongkos angkut', 'Listrik & air' => 'Listrik & air', 'Sewa tempat' => 'Sewa tempat', 'Upah pekerja' => 'Upah pekerja', 'Kemasan & plastik' => 'Kemasan & plastik', 'Perlengkapan' => 'Perlengkapan', 'Lainnya' => 'Lainnya']" />
          </div>
          <x-forms.input id="expAmt" label="Jumlah" prefix="Rp" inputmode="numeric" autocomplete="off" placeholder="0" />
        </div>
        <x-forms.input id="expDesc" label="Keterangan (opsional)" container="mt-4" maxlength="80" placeholder="Contoh: ongkos angkut dari pasar" />
        <x-forms.input id="expNote" label="Catatan tambahan (opsional)" container="mt-4" maxlength="120" />
        <x-ui.alert variant="info" class="mt-5" icon="info" title="Apa yang terjadi setelah disimpan?" id="expInfo">Isi kategori dan jumlah untuk melihat dampaknya pada laporan.</x-ui.alert>
        <x-ui.alert variant="error" id="expErr" class="mt-4" />
        <x-ui.alert variant="success" id="expDone" class="mt-4" />
        <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
          <p class="text-[12px] text-muted">Pengeluaran tidak mengubah stok produk.</p>
          <div class="flex items-center gap-5">
            <x-forms.button id="expCancel" variant="link">Batal</x-forms.button>
            <x-forms.button id="expSave" variant="primary" size="lg" icon="check">Simpan pengeluaran</x-forms.button>
          </div>
        </div>
      </x-ui.card>
    </div>

    <div class="flex flex-col gap-5">
      <x-ui.card title="Catatan terbaru" subtitle-id="recentSub">
        <ul id="recent" class="mt-3"></ul>
        <a href="/laporan-keuangan" class="mt-1 inline-block text-[13px] font-bold text-brand-dark hover:underline">Buka seluruh catatan →</a>
      </x-ui.card>

      <x-ui.card radius="2xl">
        <span class="text-brand-dark"><x-ui.icon name="clipboard-check" size="size-6" /></span>
        <h2 class="mt-4 text-[18px] font-semibold">Catat sesuai kejadian</h2>
        <p class="mt-3 text-[12px] leading-relaxed text-muted">Gunakan harga yang sudah disimpan pemilik. Untuk biaya angkut, listrik, atau kebutuhan operasional lain, pilih tab Pengeluaran.</p>
        <p class="mt-3 text-[12px] leading-relaxed text-muted">Barang masuk dicatat melalui <a href="/produk-stok" class="font-semibold text-brand-dark underline">Produk &amp; stok</a>, bukan sebagai penjualan.</p>
      </x-ui.card>

      {{-- <x-ui.card title="Data contoh" title-size="text-[15px]">
        <p class="mt-2 text-[12px] leading-relaxed text-muted">Catatan disimpan di browser ini saja (tanpa server) dan langsung terbaca oleh Laporan keuangan.</p>
        <div class="mt-4 flex flex-wrap gap-2.5">
          <x-forms.button id="resetData" size="sm" class="text-brand-dark">Kembalikan data contoh</x-forms.button>
          <x-forms.button id="clearData" size="sm" class="text-muted">Kosongkan catatan</x-forms.button>
        </div>
      </x-ui.card> --}}
    </div>
  </div>

@push('scripts')
<script>
  (function () {
    var rp = function (value) {
      return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
      }).format(Number(value) || 0);
    };
    var transactions = {{ Illuminate\Support\Js::from($transactions) }};
    var expenses = {{ Illuminate\Support\Js::from($expenses) }};
    var products = {{ Illuminate\Support\Js::from(
        $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->product_name,
                'price' => (float) $product->selling_price,
                'cost' => (float) $product->purchase_price,
                'stock' => (int) $product->stock,
                'unit' => $product->unit,
            ];
        })->values()
    ) }};
    var $ = function (id) { return document.getElementById(id); };
    var esc = function (t) { return String(t).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); };
    var HINT = 'mt-1.5 text-[12px] ', rows = [{ pid: '', qty: 1 }];
    var TRASH = '<svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 7h14M10 7V4h4v3M7 7l1 13h8l1-13"/></svg>';

    function setMode(m) {
      var sale = m === 'sale';
      $('saleForm').classList.toggle('hidden', !sale); $('expForm').classList.toggle('hidden', sale);
      $('tabSale').className = 'inline-flex items-center gap-2 rounded-lg px-4 py-2' + (sale ? ' bg-surface text-ink' : '');
      $('tabExp').className = 'inline-flex items-center gap-2 rounded-lg px-4 py-2' + (sale ? '' : ' bg-surface text-ink');
      $('tabSale').setAttribute('aria-selected', sale); $('tabExp').setAttribute('aria-selected', !sale);
    }
    $('tabSale').onclick = function () { setMode('sale'); };
    $('tabExp').onclick = function () { setMode('exp'); };

    function showErr(id, msg) { var e = $(id); e.textContent = msg || ''; e.classList.toggle('hidden', !msg); }
    function badge(id, kind, text) {
      var c = { draft: 'bg-line text-muted', ready: 'bg-tint text-brand-dark', warn: 'border border-warning bg-warn-bg text-warn' }[kind];
      $(id).className = 'inline-block rounded-md px-2.5 py-1 text-[12px] font-bold ' + c; $(id).textContent = text;
    }

    function renderRows() {
        var used = rows.map(function (r) {
            return r.pid;
        }).filter(Boolean);

        $('saleRows').innerHTML = rows.map(function (r, i) {
            var opts = '<option value="">Pilih produk…</option>' + products.map(function (p) {
                var dis = p.stock <= 0 || (used.indexOf(String(p.id)) > -1 && String(r.pid) !== String(p.id));

                return '<option value="' + p.id + '"' +
                    (String(r.pid) === String(p.id) ? ' selected' : '') +
                    (dis ? ' disabled' : '') + '>' +
                    esc(p.name) + ' — ' +
                    (p.stock > 0 ? 'stok ' + p.stock : 'habis') +
                    '</option>';
            }).join('');

            return '<div data-i="' + i + '" class="grid grid-cols-[1fr_88px] items-start gap-x-4 gap-y-2 border-t border-line py-4 sm:grid-cols-[1fr_88px_90px_130px]">' +
                '<div><select data-f="pid" aria-label="Produk" class="w-full rounded-lg border border-line bg-surface px-3 py-2.5 text-[13px] font-bold outline-none focus:border-brand">' + opts + '</select><p data-f="hint" class="' + HINT + 'text-muted">Pilih produk untuk melihat stok</p></div>' +
                '<input data-f="qty" type="number" min="1" step="1" inputmode="numeric" aria-label="Jumlah" value="' + esc(r.qty) + '" class="h-10 w-full rounded-lg border border-line bg-surface text-center text-[13px] outline-none focus:border-brand">' +
                '<p data-f="price" class="pt-2.5 text-[13px] text-muted sm:text-center">—</p>' +
                '<p class="flex items-center justify-end gap-3 pt-2.5 text-[13px] font-bold"><span data-f="sub">—</span><button type="button" data-del="' + i + '" aria-label="Hapus baris" class="text-muted hover:text-brand-dark">' + TRASH + '</button></p></div>';
        }).join('');

        refresh();
    }

    function joinId(a) { return a.length > 1 ? a.slice(0, -1).join(', ') + ' dan ' + a[a.length - 1] : a[0]; }

    function refresh() {
        var kids = $('saleRows').children, types = 0, units = 0, rev = 0, bad = false, parts = [];

        rows.forEach(function (r, i) {
            var el = kids[i];
            if (!el) return;

            var p = products.find(function (item) {
                return String(item.id) === String(r.pid);
            });

            var q = el.querySelector('[data-f=qty]');
            var hint = el.querySelector('[data-f=hint]');
            var pr = el.querySelector('[data-f=price]');
            var sub = el.querySelector('[data-f=sub]');

            if (!p) {
                q.disabled = true;
                hint.textContent = 'Pilih produk untuk melihat stok';
                hint.className = HINT + 'text-muted';
                pr.textContent = '—';
                sub.textContent = '—';
                return;
            }

            q.disabled = false;
            q.max = p.stock;
            pr.textContent = rp(p.price);

            var qty = Number(r.qty);
            var ok = qty >= 1 && qty === Math.floor(qty) && qty <= p.stock;

            if (ok) {
                hint.textContent = 'Stok tersedia: ' + p.stock + ' ' + p.unit;
                hint.className = HINT + 'text-muted';
                sub.textContent = rp(p.price * qty);
                types++;
                units += qty;
                rev += p.price * qty;
                parts.push(p.name + ' menjadi ' + (p.stock - qty));
            } else {
                bad = true;
                sub.textContent = '—';
                hint.className = HINT + 'font-semibold text-danger-dark';
                hint.textContent = qty > p.stock
                    ? 'Melebihi stok tersedia (' + p.stock + ' ' + p.unit + ')'
                    : 'Jumlah minimal 1';
            }
        });

        $('saleTotal').textContent = rp(rev);
        $('saleCount').textContent = types + ' jenis produk · ' + units + ' unit';

        if (bad) badge('saleBadge', 'warn', 'Perlu diperbaiki');
        else if (types) badge('saleBadge', 'ready', 'Siap disimpan');
        else badge('saleBadge', 'draft', 'Draf');

        $('saleInfo').textContent = types && !bad
            ? 'Stok ' + joinId(parts) + '. Pendapatan tercatat bertambah ' + rp(rev) + '. Pertukaran uang terjadi di luar aplikasi.'
            : 'Pilih produk dan jumlahnya untuk melihat dampaknya pada stok dan laporan.';

        showErr('saleErr', '');
    }

    $('saleRows').addEventListener('change', function (e) {
      if (e.target.getAttribute('data-f') !== 'pid') return;
      var i = +e.target.closest('[data-i]').getAttribute('data-i');
      rows[i].pid = e.target.value; if (!(Number(rows[i].qty) >= 1)) rows[i].qty = 1;
      renderRows();
    });
    $('saleRows').addEventListener('input', function (e) {
      if (e.target.getAttribute('data-f') !== 'qty') return;
      rows[+e.target.closest('[data-i]').getAttribute('data-i')].qty = e.target.value;
      refresh();
    });
    $('saleRows').addEventListener('click', function (e) {
      var b = e.target.closest('[data-del]'); if (!b) return;
      var i = +b.getAttribute('data-del');
      if (rows.length > 1) rows.splice(i, 1); else rows[0] = { pid: '', qty: 1 };
      renderRows();
    });
    $('addRow').onclick = function () { rows.push({ pid: '', qty: 1 }); renderRows(); };

    function showDone(id, text) {
      var el = $(id);
      el.innerHTML = '';
      el.appendChild(document.createTextNode(text));
      var link = document.createElement('a');
      link.href = '/laporan-keuangan'; link.className = 'underline'; link.textContent = 'Lihat di laporan';
      el.appendChild(link);
      el.classList.remove('hidden');
    }
    function hideDone(id) { $(id).classList.add('hidden'); }
    $('saleForm').addEventListener('input', function () { hideDone('saleDone'); });
    $('expForm').addEventListener('input', function () { hideDone('expDone'); });

    function resetSale() {
      rows = [{ pid: '', qty: 1 }];
      $('saleNote').value = '';
      $('saleTs').value = (function () {
        var d = new Date();
        d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
        return d.toISOString().slice(0, 16);
      })();
      renderRows();
    }
    $('saleCancel').onclick = resetSale;
    $('saleSave').onclick = async function () {
      var ts = new Date($('saleTs').value).getTime();

      if (ts > Date.now() + 60000) {
          showErr('saleErr', 'Waktu penjualan tidak boleh di masa depan.');
          return;
      }

      var items = rows.filter(function (r) {
          return r.pid;
      }).map(function (r) {
          return {
              product_id: r.pid,
              purchase_quantity: Number(r.qty)
          };
      });

      if (!items.length) {
          showErr('saleErr', 'Pilih minimal satu produk.');
          return;
      }

      try {
          var response = await fetch(@json(route('catatan-transaksi.sale.store', [], false)), {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/json',
                  'Accept': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
              },
              body: JSON.stringify({
                  transaction_date: new Date(ts).toISOString().slice(0, 19).replace('T', ' '),
                  note: $('saleNote').value.trim(),
                  items: items
              })
          });

          var result = await response.json();

          if (!response.ok) {
              showErr('saleErr', result.message || 'Penjualan gagal disimpan.');
              return;
          }

          toast(result.message || 'Penjualan berhasil disimpan.');
          resetSale();
          location.reload();

      } catch (error) {
          showErr('saleErr', 'Terjadi kesalahan saat menghubungi server.');
      }
    };

    function amtVal() { return Number(($('expAmt').value || '').replace(/\D/g, '')) || 0; }
    function refreshExp() {
      var a = amtVal(), ready = a > 0 && $('expCat').value;
      badge('expBadge', ready ? 'ready' : 'draft', ready ? 'Siap disimpan' : 'Draf');
      $('expInfo').textContent = a > 0
        ? 'Beban operasional bertambah ' + rp(a) + ' pada laporan keuangan. Pengeluaran tidak mengubah stok.'
        : 'Isi kategori dan jumlah untuk melihat dampaknya pada laporan.';
      showErr('expErr', '');
    }
    $('expAmt').addEventListener('input', function (e) {
      var d = e.target.value.replace(/\D/g, '').replace(/^0+/, '');
      e.target.value = d ? d.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : ''; refreshExp();
    });
    $('expCat').addEventListener('change', refreshExp);
    function resetExp() {
      $('expTs').value = (function () {
        var d = new Date();
        d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
        return d.toISOString().slice(0, 16);
      })();

      ['expCat', 'expAmt', 'expDesc', 'expNote'].forEach(function (id) {
        $(id).value = '';
      });

      refreshExp();
    }
    $('expCancel').onclick = resetExp;
    $('expSave').onclick = function () {
        var ts = new Date($('expTs').value).getTime();

        if (ts > Date.now() + 60000) {
            showErr('expErr', 'Waktu pengeluaran tidak boleh di masa depan.');
            return;
        }

        var amount = amtVal();

        if (!amount || amount < 1) {
            showErr('expErr', 'Jumlah pengeluaran harus lebih dari 0.');
            return;
        }

        fetch(@json(route('catatan-transaksi.expense.store')), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                expense_date: new Date(ts).toISOString().slice(0, 19).replace('T', ' '),
                category: $('expCat').value,
                amount: amount,
                description: $('expDesc').value.trim(),
                note: $('expNote').value.trim()
            })
        })
        .then(function (response) {
            return response.json().then(function (data) {
                if (!response.ok) {
                    throw new Error(data.message || 'Gagal menyimpan pengeluaran.');
                }
                return data;
            });
        })
        .then(function (data) {
            toast('Pengeluaran #' + data.expense_id + ' berhasil disimpan.');

            resetExp();
            location.reload();
        })
        .catch(function (error) {
            showErr('expErr', error.message);
        });
    };

    function stamp(ts) {
      var date = new Date(ts);
      var today = new Date();

      var sameDay =
        date.getDate() === today.getDate() &&
        date.getMonth() === today.getMonth() &&
        date.getFullYear() === today.getFullYear();

      return sameDay
        ? date.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit'
          })
        : date.toLocaleString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
          });
    }

    function renderRecent() {
        var list = [];

        transactions.forEach(function (t) {
            var details = t.transaction_details || t.transactionDetails || [];

            var total = details.reduce(function (sum, detail) {
                return sum + Number(detail.subtotal || 0);
            }, 0);

            var summary = details.map(function (detail) {
                var productName = detail.product
                    ? detail.product.product_name
                    : 'Produk tidak tersedia';

                return productName + ' x' + detail.purchase_quantity;
            }).join(', ');

            list.push({
                id: t.id,
                type: 'sale',
                ts: new Date(t.transaction_date).getTime(),
                amount: total,
                summary: summary || 'Penjualan'
            });
        });

        expenses.forEach(function (e) {
            list.push({
                id: e.id,
                type: 'expense',
                ts: new Date(e.expense_date).getTime(),
                amount: Number(e.amount || 0),
                summary: e.description || e.category
            });
        });

        list.sort(function (a, b) {
            return b.ts - a.ts;
        });

        var top = list.slice(0, 6);

        $('recentSub').textContent = list.length
            ? 'Terbaru dari ' + list.length + ' catatan tersimpan'
            : 'Belum ada catatan';

        $('recent').innerHTML = top.length ? top.map(function (t, i) {
            var sale = t.type === 'sale';

            return '<li class="py-4' + (i ? ' border-t border-line' : '') + '">' +
                '<div class="flex items-center justify-between">' +
                    '<span class="inline-block rounded-md px-2.5 py-1 text-[12px] font-bold ' +
                    (sale ? 'bg-tint text-brand-dark' : 'bg-line text-muted') + '">' +
                        (sale ? 'Penjualan' : 'Pengeluaran') +
                    '</span>' +
                    '<span class="text-[12px] text-muted">' +
                        stamp(t.ts) + ' · #' + t.id +
                    '</span>' +
                '</div>' +
                '<div class="mt-2.5 flex items-center justify-between gap-3 text-[13px] font-bold">' +
                    '<span class="min-w-0 truncate">' + esc(t.summary) + '</span>' +
                    '<span class="shrink-0">' + rp(t.amount) + '</span>' +
                '</div>' +
                '<div class="mt-1.5 text-right">' +
                    '<button type="button" data-void="' + t.id + '" data-type="' + t.type + '" class="-mr-2 rounded-md px-2 py-1.5 text-[12px] font-semibold text-muted hover:bg-danger-bg hover:text-danger-dark">Batalkan</button>' +
                '</div>' +
                '</li>';
        }).join('') : '<li class="py-6 text-[12px] text-muted">Catatan yang disimpan akan muncul di sini.</li>';
    }

    $('recent').addEventListener('click', function (e) {
        var b = e.target.closest('[data-void]');
        if (!b) return;

        var id = b.getAttribute('data-void');
        var sale = b.getAttribute('data-type') === 'sale';

        if (!confirm(
            'Batalkan catatan #' + id + '? ' +
            (sale
                ? 'Stok produk akan dikembalikan dan laporan ikut berubah.'
                : 'Catatan pengeluaran akan dihapus dan laporan ikut berubah.')
        )) return;

        var type = sale ? 'sale' : 'expense';

        fetch(@json(route('catatan-transaksi.void', ['type' => '__TYPE__', 'id' => '__ID__']))
            .replace('__TYPE__', type)
            .replace('__ID__', id), {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(function (response) {
            return response.json().then(function (data) {
                if (!response.ok) {
                    throw new Error(data.message || 'Gagal membatalkan catatan.');
                }
                return data;
            });
        })
        .then(function (data) {
            toast(data.message);
            location.reload();
        })
        .catch(function (error) {
            toast(error.message);
        });
    });

    resetSale(); resetExp(); renderRecent();
  })();
</script>
@endpush
</x-layouts.app>
