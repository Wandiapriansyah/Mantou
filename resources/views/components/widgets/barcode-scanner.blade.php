<x-ui.card radius="2xl" title="Pindai barcode" subtitle="Arahkan barcode kemasan ke dalam bingkai." title-size="text-[20px]">
  <x-slot:aside>
    <x-ui.badge id="camBadge">Kamera mati</x-ui.badge>
  </x-slot:aside>

  <div id="viewfinder" class="relative mt-5 aspect-[567/299] overflow-hidden rounded-xl bg-brand-dark">
    <img id="camIdle" src="{{ asset('img/camera.jpg') }}" alt="" onerror="this.style.display='none'" class="size-full object-cover">
    <video id="camVideo" class="absolute inset-0 hidden size-full object-cover" playsinline muted autoplay></video>
    <canvas id="camSnap" class="absolute inset-0 hidden size-full object-cover"></canvas>
    <div id="camShade" class="absolute inset-0 bg-brand-dark/55"></div>

    <div id="camFrame" class="absolute inset-x-[14%] inset-y-[24%] hidden" aria-hidden="true">
      <span class="absolute left-0 top-0 size-7 rounded-tl-md border-l-[3px] border-t-[3px] border-tint"></span>
      <span class="absolute right-0 top-0 size-7 rounded-tr-md border-r-[3px] border-t-[3px] border-tint"></span>
      <span class="absolute bottom-0 left-0 size-7 rounded-bl-md border-b-[3px] border-l-[3px] border-tint"></span>
      <span class="absolute bottom-0 right-0 size-7 rounded-br-md border-b-[3px] border-r-[3px] border-tint"></span>
      <span id="scanLine" class="absolute inset-x-0 top-1/2 h-px bg-tint"></span>
    </div>

    <div id="camStart" class="absolute inset-0 grid place-items-center">
      <button type="button" id="camStartBtn" class="inline-flex items-center gap-2 rounded-full bg-surface px-5 py-2.5 text-[13px] font-bold text-brand shadow-sm transition hover:bg-tint">
        <x-ui.icon name="scan" size="size-[18px]" />Aktifkan kamera
      </button>
    </div>

    <span id="camStatus" class="absolute bottom-3 left-1/2 hidden max-w-[90%] -translate-x-1/2 items-center gap-2 rounded-full bg-brand-dark/90 px-4 py-2 text-[12px] font-bold text-white"></span>
  </div>

  <div class="mt-4 flex items-center justify-between gap-3">
    <p id="camNote" class="text-[11px] text-muted">Kamera belum diaktifkan</p>
    <x-forms.button id="scanBtn" size="compact" icon="scan" class="shrink-0"><span id="scanBtnText">Mulai pindai</span></x-forms.button>
  </div>

  <div class="my-5 flex items-center gap-3 text-[11px] text-muted"><span class="h-px flex-1 bg-line"></span>atau cari secara manual<span class="h-px flex-1 bg-line"></span></div>

  <x-forms.search-bar label="Barcode atau nama produk terdaftar" placeholder="Contoh: 089686170707 atau Indomie" />

  <div class="mt-3.5 flex flex-wrap items-center gap-2">
    <span class="text-[11px] text-muted">Coba contoh:</span>
    <span id="chips" class="flex flex-wrap gap-2"></span>
  </div>
</x-ui.card>

@push('styles')
<style>
  @keyframes scan-sweep { 0%,100% { top: 8%; } 50% { top: 92%; } }
  .scanning #scanLine { top: 8%; height: 2px; animation: scan-sweep 2s ease-in-out infinite; box-shadow: 0 0 10px 1px #e8f0e8; }
  @media (prefers-reduced-motion: reduce) { .scanning #scanLine { animation: none; top: 50%; } }
</style>
@endpush

@push('scripts')
<script>
(function () {
  const ICON_OK = '<svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>';
  const ICON_WARN = '<svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 8v5M12 16.5v.5"/><circle cx="12" cy="12" r="9"/></svg>';

  const $ = (id) => document.getElementById(id);
  const video = $('camVideo'), snap = $('camSnap'), idle = $('camIdle');
  let stream = null, timer = null, zx = null, scanning = false;

  function setStatus(html, show = true) {
    const el = $('camStatus');
    el.innerHTML = html;
    el.classList.toggle('hidden', !show);
    el.classList.toggle('inline-flex', show);
  }

  function setUi(state, msg) {
    const vf = $('viewfinder'), badge = $('camBadge'), note = $('camNote');
    vf.classList.toggle('scanning', state === 'scanning');
    $('camStart').classList.toggle('hidden', state !== 'idle' && state !== 'error');
    $('camFrame').classList.toggle('hidden', state === 'idle' || state === 'error' || state === 'starting');
    $('camShade').classList.toggle('hidden', state === 'scanning' || state === 'success');
    video.classList.toggle('hidden', state !== 'scanning');
    snap.classList.toggle('hidden', state !== 'success');
    idle.classList.toggle('hidden', state === 'scanning' || state === 'success');

    const base = 'rounded-md px-2.5 py-1 text-[11px] font-bold ';
    const btn = $('scanBtnText');

    if (state === 'idle') {
      badge.className = base + 'bg-line/70 text-muted'; badge.textContent = 'Kamera mati';
      note.className = 'text-[11px] text-muted'; note.textContent = 'Kamera belum diaktifkan';
      setStatus('', false); btn.textContent = 'Mulai pindai';
    } else if (state === 'starting') {
      badge.className = base + 'bg-line/70 text-muted'; badge.textContent = 'Menyalakan…';
      note.className = 'text-[11px] text-muted'; note.textContent = 'Menunggu izin kamera…';
      setStatus('Menyalakan kamera…'); btn.textContent = 'Batal';
    } else if (state === 'scanning') {
      badge.className = base + 'bg-tint text-brand'; badge.textContent = 'Kamera aktif';
      note.className = 'text-[11px] text-muted'; note.textContent = 'Kamera perangkat · memindai';
      setStatus('Arahkan barcode ke dalam bingkai'); btn.textContent = 'Hentikan';
    } else if (state === 'success') {
      badge.className = base + 'bg-line/70 text-muted'; badge.textContent = 'Kamera mati';
      note.className = 'text-[11px] text-muted'; note.textContent = 'Pindai selesai · kamera dimatikan';
      setStatus(ICON_OK + 'Barcode berhasil dibaca'); btn.textContent = 'Pindai lagi';
    } else if (state === 'error') {
      badge.className = base + 'bg-warn-bg text-warn'; badge.textContent = 'Kamera tidak tersedia';
      note.className = 'text-[11px] text-warn'; note.textContent = msg || 'Kamera tidak dapat digunakan';
      setStatus(ICON_WARN + 'Gunakan pencarian manual di bawah'); btn.textContent = 'Coba lagi';
    }
  }

  function stopCamera() {
    scanning = false;
    clearTimeout(timer); timer = null;
    if (zx) { try { zx.reset(); } catch (e) {} zx = null; }
    if (stream) { stream.getTracks().forEach((t) => t.stop()); stream = null; }
    video.srcObject = null;
  }

  function loadZXing() {
    return new Promise((resolve, reject) => {
      if (window.ZXing) return resolve();
      const s = document.createElement('script');
      s.src = 'https://cdn.jsdelivr.net/npm/@zxing/library@0.21.3/umd/index.min.js';
      s.onload = resolve; s.onerror = () => reject(new Error('Gagal memuat pemindai cadangan'));
      document.head.appendChild(s);
    });
  }

  function onCode(code) {
    if (!scanning) return;
    try {
      snap.width = video.videoWidth || 640; snap.height = video.videoHeight || 360;
      snap.getContext('2d').drawImage(video, 0, 0, snap.width, snap.height);
    } catch (e) {}
    stopCamera();
    setUi('success');
    if (navigator.vibrate) navigator.vibrate(60);
    $('q').value = code;
    window.lookupProduct(code);
  }

  async function startCamera() {
    if (scanning || stream) return;
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      setUi('error', 'Browser ini tidak mendukung kamera (butuh HTTPS atau localhost).');
      return;
    }
    setUi('starting');
    scanning = true;
    try {
      stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: { ideal: 'environment' } }, audio: false });
      if (!scanning) { stopCamera(); return; }
      video.srcObject = stream;
      await video.play();
      setUi('scanning');

      if ('BarcodeDetector' in window) {
        const detector = new BarcodeDetector({ formats: ['ean_13', 'ean_8', 'upc_a', 'upc_e', 'code_128', 'code_39', 'itf'] });
        const tick = async () => {
          if (!scanning) return;
          try {
            const found = await detector.detect(video);
            if (found.length) { onCode(found[0].rawValue); return; }
          } catch (e) {}
          timer = setTimeout(tick, 200);
        };
        tick();
      } else {
        await loadZXing();
        if (!scanning) return;
        zx = new ZXing.BrowserMultiFormatReader();
        zx.decodeFromStream(stream, video, (result) => { if (result) onCode(result.getText()); });
      }
    } catch (err) {
      stopCamera();
      const denied = err && (err.name === 'NotAllowedError' || err.name === 'SecurityError');
      const none = err && (err.name === 'NotFoundError' || err.name === 'OverconstrainedError');
      setUi('error', denied ? 'Izin kamera ditolak. Aktifkan izin kamera di browser.' : none ? 'Kamera tidak ditemukan di perangkat ini.' : (err && err.message) || 'Kamera tidak dapat digunakan');
    }
  }

  function toggleScan() {
    if (scanning || stream) { stopCamera(); setUi('idle'); }
    else startCamera();
  }

  function stopScanner() {
    if (scanning) { stopCamera(); setUi('idle'); }
  }

  $('camStartBtn').addEventListener('click', startCamera);
  $('scanBtn').addEventListener('click', toggleScan);
  $('searchBtn').addEventListener('click', stopScanner);
  $('chips').addEventListener('click', (e) => { if (e.target.closest('[data-chip]')) stopScanner(); });
  window.addEventListener('pagehide', stopCamera);

  setUi('idle');
})();
</script>
@endpush
