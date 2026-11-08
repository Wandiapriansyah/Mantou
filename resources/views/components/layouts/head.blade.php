@props(['title'])

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>{{ $title }} · Mantou</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4.1.4"></script>
<style type="text/tailwindcss">
  @layer base {
    ::placeholder { color: var(--color-faint); opacity: 1; }
    a, button, select, input { transition: color .15s ease, background-color .15s ease, border-color .15s ease, box-shadow .15s ease; }
    body { font-variant-numeric: tabular-nums; }
    @media (prefers-reduced-motion: reduce) { *, *::before, *::after { transition: none !important; animation: none !important; } }
    :focus-visible { outline: 2px solid var(--color-brand-dark); outline-offset: 2px; }
  }
  @theme {
    --radius-md: 8px;
    --radius-lg: 10px;
    --radius-xl: 14px;
    --radius-2xl: 16px;
    --shadow-sm: 0 1px 2px rgb(0 0 0 / 0.05);
    --shadow-lg: 0 8px 24px rgb(0 0 0 / 0.12);
    --shadow-xl: 0 16px 40px rgb(0 0 0 / 0.16);
    --font-sans: "Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif;
    --color-brand: #598e5c;
    --color-brand-dark: #3f6e43;
    --color-brand-soft: #a8c8aa;
    --color-tint: #eaf2ea;
    --color-ink: #26332a;
    --color-muted: #59635b;
    --color-faint: #6a756d;
    --color-line: #e3e9e3;
    --color-canvas: #f7f9f7;
    --color-surface: #ffffff;
    --color-warning: #e5a12a;
    --color-warn: #7a5200;
    --color-warn-bg: #fdf3dc;
    --color-danger: #d94b45;
    --color-danger-dark: #b3322d;
    --color-danger-bg: #fcedec;
    --color-success: #39804a;
    --color-success-bg: #f6fbf7;
  }
</style>
@stack('styles')
