@props(['canAdd' => true])

<div class="flex flex-col gap-5">
  <div id="result" aria-live="polite"></div>

  <x-ui.card radius="2xl" title="Produk belum terdaftar?">
    @if ($canAdd)
    <p class="mt-2 text-[12px] leading-relaxed text-muted">Hanya produk terdaftar yang dapat dicari. Pemilik atau pengguna berizin dapat menambahkan produk baru.</p>
    <x-forms.button href="/produk-stok" icon="plus" class="mt-4">Tambah produk</x-forms.button>
    @else
      <p class="mt-2 text-[12px] leading-relaxed text-muted">Hanya produk terdaftar yang dapat dicari. Hanya pemilik warung yang dapat menambahkan produk baru.</p>
      <x-forms.button type="button" icon="plus" class="mt-4 cursor-not-allowed opacity-50 hover:border-line" disabled aria-disabled="true" title="Hanya pemilik yang dapat menambah produk">Tambah produk</x-forms.button>
    @endif
  </x-ui.card>
</div>

@push('scripts')
<script>
(function () {
  const CAN_ADD = @json($canAdd);
  const getCatalog = () => Mantou.get().products.map((p) => ({ id: p.id, barcode: p.barcode, name: p.name, category: p.category, price: p.price, unit: p.unit, stock: p.stock, min: p.min, updated: Mantou.fmt.longDate(p.upd) }));

  const $ = (id) => document.getElementById(id);
  const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const rupiah = (n) => 'Rp' + Number(n).toLocaleString('id-ID');
  const nowWib = () => { const d = new Date(); return String(d.getHours()).padStart(2, '0') + '.' + String(d.getMinutes()).padStart(2, '0') + ' WIB'; };
  const digits = (s) => String(s).replace(/\D/g, '').replace(/^0+/, '');

  async function fetchProducts(q = '') {
    const response = await fetch('/cek-harga/search', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({ q: q })
    });

    const result = await response.json();

    if (!response.ok) {
      throw new Error(result.message || 'Gagal mengambil data produk.');
    }

    return result.data;
  }

  const ICON_WARN = '<svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 8v5M12 16.5v.5"/><circle cx="12" cy="12" r="9"/></svg>';

  const SHELL = 'rounded-2xl border border-line bg-surface p-6';

  function renderEmpty() {
    $('result').innerHTML = `
      <section class="${SHELL}">
        <p class="flex items-center gap-2.5 text-[14px] font-bold text-muted">${ICON_WARN.replace('M12 8v5M12 16.5v.5', 'M12 8v5M12 16.5v.5')}Belum ada produk dipindai</p>
        <p class="mt-3 text-[12px] leading-relaxed text-muted">Pindai barcode dengan kamera atau ketik barcode / nama produk di kolom pencarian. Harga akan tampil di sini.</p>
      </section>`;
  }

  function renderNotFound(q) {
    $('result').innerHTML = `
      <section class="${SHELL}">
        <div class="flex items-center justify-between">
          <p class="flex items-center gap-2.5 text-[14px] font-bold text-warn">${ICON_WARN}Produk tidak ditemukan</p>
          <span class="text-[12px] text-muted">${nowWib()}</span>
        </div>
        <p class="mt-5 text-[12px] font-semibold text-muted">Yang dicari</p>
        <p class="mt-2 break-all text-[22px] font-bold leading-tight tracking-tight">${esc(q)}</p>
        <div class="mt-5 rounded-xl border border-warning bg-warn-bg p-5">
          <p class="text-[13px] font-bold text-warn">Belum terdaftar di warung ini</p>
          <p class="mt-1.5 text-[12px] leading-relaxed text-warn">Periksa kembali barcode atau coba pindai ulang. ${CAN_ADD ? 'Jika produk memang baru, tambahkan lewat Produk &amp; stok.' : 'Jika produk memang baru, minta pemilik warung untuk menambahkannya.'}</p>
           ${CAN_ADD ? `<a href="/produk-stok?aksi=tambah&${/^\d{6,}$/.test(q.replace(/[\s-]/g, '')) ? 'barcode=' : 'nama='}${encodeURIComponent(q)}" class="mt-3 inline-block text-[12px] font-bold text-brand-dark hover:underline">Tambah produk ini →</a>` : ''}
        </div>
      </section>`;
  }

  function renderFound(list) {
    const p = list[0];
    let badge = '<span class="rounded-md bg-tint px-2.5 py-1 text-[12px] font-bold text-brand-dark">Stok aman</span>';
    if (p.stock === 0) badge = '<span class="rounded-md border border-danger bg-danger-bg px-2.5 py-1 text-[12px] font-bold text-danger-dark">Stok habis</span>';
    else if (p.stock < p.min) badge = '<span class="rounded-md border border-warning bg-warn-bg px-2.5 py-1 text-[12px] font-bold text-warn">Stok rendah</span>';

    const others = list.slice(1).map((o) =>
      `<button type="button" data-pick="${esc(o.barcode)}" class="flex w-full items-center justify-between gap-3 rounded-lg border border-line px-3.5 py-2.5 text-left transition hover:border-brand">
         <span class="text-[12px] font-bold">${esc(o.name)}</span><span class="text-[12px] font-bold text-brand-dark">${rupiah(o.price)}</span>
       </button>`).join('');

    $('result').innerHTML = `
      <section class="${SHELL}">
        <div class="flex items-center justify-between">
          <p class="flex items-center gap-2.5 text-[14px] font-bold text-brand-dark"><svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M8 12.5l3 3 5-6"/></svg>Produk ditemukan</p>
          <span class="text-[12px] text-muted">${nowWib()}</span>
        </div>
        <p class="mt-5 text-[12px] font-semibold text-muted">${esc(p.category)}</p>
        <h2 class="mt-2 text-[26px] font-semibold leading-tight tracking-tight">${esc(p.name)}</h2>
        <p class="mt-3 text-[12px] text-muted">Barcode: ${esc(p.barcode)}</p>

        <div class="mt-5 rounded-xl bg-tint p-6">
          <p class="text-[12px] text-brand-dark">Harga jual per ${esc(p.unit)}</p>
          <p class="mt-2 text-[48px] font-bold leading-none tracking-tight text-brand-dark">${rupiah(p.price)}</p>
          <p class="mt-3 text-[12px] text-muted">Harga yang ditetapkan pemilik warung</p>
        </div>

        <div class="mt-5 flex items-end justify-between">
          <div><p class="text-[12px] text-muted">Sisa stok tercatat</p><p class="mt-1 text-[18px] font-bold">${p.stock} ${esc(p.unit)}</p></div>
          ${badge}
        </div>

        <div class="mt-5 flex items-center justify-between border-t border-line pt-4 text-[12px] text-muted">
          <span>Harga diperbarui ${esc(p.updated)}</span>
          ${CAN_ADD ? `<a href="/produk-stok?cari=${encodeURIComponent(p.barcode || p.name)}" class="text-[12px] font-bold text-brand-dark hover:underline">Lihat detail produk →</a>` : ''}
        </div>
        ${others ? `<div class="mt-5 border-t border-line pt-4"><p class="mb-2.5 text-[12px] font-semibold text-muted">Hasil lain</p><div class="flex flex-col gap-2">${others}</div></div>` : ''}
      </section>`;
  }

  async function lookup(q) {
    q = (q || '').trim();

    if (!q) {
      renderEmpty();
      return;
    }

    try {
      const list = await fetchProducts(q);

      if (list.length > 0) {
        renderFound(list);
      } else {
        renderNotFound(q);
      }
    } catch (error) {
      console.error('Gagal mencari produk:', error);

      $('result').innerHTML = `
        <section class="${SHELL}">
          <p class="text-[14px] font-bold text-danger-dark">
            Gagal mengambil data produk
          </p>
          <p class="mt-2 text-[12px] text-muted">
            Silakan periksa koneksi dan coba kembali.
          </p>
        </section>`;
    }
  }

  window.lookupProduct = lookup;

  $('searchBtn').addEventListener('click', () => lookup($('q').value));
  $('q').addEventListener('keydown', (e) => { if (e.key === 'Enter') $('searchBtn').click(); });
  $('result').addEventListener('click', (e) => {
    const b = e.target.closest('[data-pick]');
    if (b) { $('q').value = b.dataset.pick; lookup(b.dataset.pick); }
  });
  async function loadProductChips() {
    try {
      const products = await fetchProducts('');

      $('chips').innerHTML = products.map((p) =>
        `<button type="button" data-chip="${esc(p.barcode || p.name)}" class="rounded-full border border-line bg-surface px-3 py-1 text-[12px] font-semibold text-ink transition hover:border-brand hover:text-brand-dark">${esc(p.name.split(' ').slice(0, 2).join(' '))}</button>`
      ).join('');
    } catch (error) {
      console.error('Gagal memuat chip produk:', error);
      $('chips').innerHTML = '';
    }
  }

  loadProductChips();
  $('chips').addEventListener('click', (e) => {
    const b = e.target.closest('[data-chip]');
    if (!b) return;

    $('q').value = b.dataset.chip;
    lookup(b.dataset.chip);
  });

  renderEmpty();

  const preQ = new URLSearchParams(location.search).get('q');

  if (preQ) {
    $('q').value = preQ;
    lookup(preQ);
  }
})();
</script>
@endpush