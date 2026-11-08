<x-layouts.guest title="Masuk">
  <div class="flex min-h-screen flex-col">
    <header class="mx-auto flex h-16 w-full max-w-[1200px] items-center justify-between px-5 md:px-10">
      <a href="/" class="flex items-center gap-2.5">
        <span class="grid size-8 place-items-center rounded-lg bg-brand-dark text-surface"><x-ui.icon name="store" size="size-[18px]" /></span>
        <span class="text-[20px] font-semibold tracking-tight">Mantou</span>
      </a>
      <a href="/" class="text-[14px] font-semibold text-brand-dark hover:underline">Kembali ke beranda</a>
    </header>

    <main class="flex flex-1 items-center justify-center px-5 pb-20 pt-8">
      <div class="w-full max-w-[400px]">
        <h1 class="text-[30px] font-semibold tracking-tight md:text-[34px]">Selamat datang kembali</h1>
        <p class="mt-3 text-[16px] leading-relaxed text-muted">Masuk dengan akun pemilik atau penjaga warung.</p>

        <form id="loginForm" method="POST" action="/login" class="mt-9 flex flex-col gap-6" novalidate>
          @csrf
          <x-forms.input id="email" name="email" type="email" label="Email" :value="old('email')" :error="$errors->first('email')" placeholder="nama@email.com" autocomplete="email" required :autofocus="! $errors->any()" />

          <div>
            <label for="password" class="text-[12px] font-bold">Kata sandi</label>
            <div @class(['mt-2 flex items-center gap-3 rounded-lg border bg-surface px-3.5 py-3 text-[14px] focus-within:border-brand', 'border-danger' => $errors->has('password'), 'border-line' => ! $errors->has('password')])>
              <input id="password" name="password" type="password" autocomplete="current-password" required class="min-w-0 flex-1 bg-transparent outline-none" @if ($errors->has('password')) aria-invalid="true" aria-describedby="password-error" @endif>
              <button type="button" id="togglePassword" aria-controls="password" aria-pressed="false" class="text-[13px] font-semibold text-brand-dark hover:underline">Tampilkan</button>
            </div>
            @error('password')
              <p id="password-error" class="mt-2 text-[13px] font-semibold text-danger-dark">{{ $message }}</p>
            @enderror
          </div>

          <label class="flex items-center gap-3 text-[14px] text-muted">
            <input type="checkbox" name="remember" class="size-4 accent-brand">Ingat saya di perangkat ini
          </label>

          <x-forms.button type="submit" id="loginSubmit" variant="primary" size="lg" class="w-full justify-center"><span id="loginSubmitText">Masuk</span></x-forms.button>
        </form>
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
        toggle.setAttribute('aria-pressed', hidden ? 'true' : 'false');
      });

      var form = document.getElementById('loginForm');
      var submit = document.getElementById('loginSubmit');
      var label = document.getElementById('loginSubmitText');
      form.addEventListener('submit', function (e) {
        if (submit.disabled) { e.preventDefault(); return; }
        submit.disabled = true;
        submit.setAttribute('aria-busy', 'true');
        label.textContent = 'Memproses…';
      });
    })();
  </script>
  @endpush
</x-layouts.guest>
