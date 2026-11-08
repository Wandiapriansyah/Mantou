<x-layouts.guest title="Masuk">
  <div class="grid min-h-screen lg:grid-cols-[1fr_1.1fr]">
    <aside class="relative hidden flex-col justify-between bg-brand-dark p-12 text-surface lg:flex">
      <a href="/" class="flex items-center gap-2.5">
        <span class="grid size-9 place-items-center rounded-lg border border-surface"><x-ui.icon name="store" /></span>
        <span class="text-[22px] font-bold tracking-tight">Mantou</span>
      </a>
      <div>
        <h2 class="text-[38px] font-semibold leading-tight tracking-tight">Usaha kecil,<br>lebih tertata.</h2>
        <p class="mt-4 max-w-[380px] text-[14px] leading-relaxed text-surface">Cek harga, kelola stok, dan catat transaksi warung dari satu tempat.</p>
        <ul class="mt-8 flex flex-col gap-3 text-[13px] text-surface">
          <li class="flex items-center gap-3"><x-ui.icon name="check" size="size-[18px]" />Harga tersimpan selalu mengikuti keputusan pemilik</li>
          <li class="flex items-center gap-3"><x-ui.icon name="check" size="size-[18px]" />Stok berkurang otomatis dari penjualan</li>
          <li class="flex items-center gap-3"><x-ui.icon name="check" size="size-[18px]" />Laporan laba dari catatan harian</li>
        </ul>
      </div>
      <p class="text-[12px] text-surface">Warung Bu Ratna · Sukamaju, Bandung</p>
    </aside>

    <main class="flex items-center justify-center px-5 py-12 sm:px-8">
      <div class="w-full max-w-[400px]">
        <a href="/" class="mb-8 flex items-center gap-2.5 lg:hidden">
          <span class="grid size-9 place-items-center rounded-lg bg-brand-dark text-surface"><x-ui.icon name="store" /></span>
          <span class="text-[22px] font-bold tracking-tight">Mantou</span>
        </a>

        <h1 class="text-[30px] font-semibold tracking-tight">Masuk</h1>
        <p class="mt-2 text-[13px] text-muted">Gunakan akun pemilik atau penjaga warung.</p>

        @if ($errors->any())
          <x-ui.alert variant="note" class="mt-6 !border border-warning bg-warn-bg !text-warn">{{ $errors->first() }}</x-ui.alert>
        @endif

        <form method="POST" action="/login" class="mt-7 flex flex-col gap-5">
          @csrf
          <x-forms.input id="email" name="email" type="email" label="Email" :value="old('email')" placeholder="nama@email.com" autocomplete="email" required autofocus />

          <div>
            <div class="flex items-center justify-between">
              <label for="password" class="text-[12px] font-bold">Kata sandi</label>
              <a href="/forgot-password" class="text-[12px] font-bold text-brand-dark hover:underline">Lupa kata sandi?</a>
            </div>
            <div class="mt-2 flex items-center gap-3 rounded-lg border border-line bg-surface px-3.5 py-3 text-[14px] focus-within:border-brand">
              <input id="password" name="password" type="password" autocomplete="current-password" placeholder="Masukkan kata sandi" required class="min-w-0 flex-1 bg-transparent outline-none">
              <button type="button" id="togglePassword" class="text-[12px] font-bold text-brand-dark">Tampilkan</button>
            </div>
          </div>

          <label class="flex items-center gap-3 text-[13px] text-muted">
            <input type="checkbox" name="remember" class="size-4 accent-brand">Ingat saya di perangkat ini
          </label>

          <x-forms.button type="submit" variant="primary" size="lg" class="w-full justify-center">Masuk</x-forms.button>
        </form>

        <p class="mt-8 text-center text-[12px] text-muted">Belum punya akun? Hubungi pemilik warung untuk dibuatkan akses.</p>
        <a href="/" class="mt-3 block text-center text-[12px] font-bold text-brand-dark hover:underline">← Kembali ke beranda</a>
      </div>
    </main>
  </div>

  @push('scripts')
  <script>
    (function () {
      var field = document.getElementById('password');
      var toggle = document.getElementById('togglePassword');
      toggle.addEventListener('click', function () {
        var hidden = field.type === 'password';
        field.type = hidden ? 'text' : 'password';
        toggle.textContent = hidden ? 'Sembunyikan' : 'Tampilkan';
      });
    })();
  </script>
  @endpush
</x-layouts.guest>
