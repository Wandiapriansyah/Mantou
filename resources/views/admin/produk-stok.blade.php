<x-layouts.app title="Produk & stok" active="produk-stok">
  <x-layouts.page-header title="Produk & stok" subtitle="Harga tersimpan dan persediaan warung, dikelola dalam satu tempat.">
    <x-forms.button id="btnScan" icon="barcode">Pindai &amp; tambah</x-forms.button>
    <x-forms.button id="btnAdd" variant="primary" icon="plus">Tambah produk</x-forms.button>
  </x-layouts.page-header>

  <section id="kpis" class="mt-6 grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4" aria-label="Ringkasan stok"></section>

  <x-ui.card class="mt-5" flush>
    <div class="p-6 pb-5">
      <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
          <h2 class="text-[18px] font-semibold">Daftar produk</h2>
          <p class="mt-1 text-[12px] text-muted">Harga jual hanya diubah oleh pengguna berizin.</p>
        </div>
        <div class="flex gap-2.5">
          <x-forms.button id="btnIn">Barang masuk</x-forms.button>
          <x-forms.button id="btnAdj">Penyesuaian stok</x-forms.button>
        </div>
      </div>
      <div class="mt-5 flex flex-wrap gap-3">
        <label class="flex min-w-[240px] flex-1 items-center gap-3 rounded-lg border border-line px-3.5 py-2.5 text-[13px] text-muted focus-within:border-brand">
          <x-ui.icon name="search" size="size-[18px]" />
          <input id="q" type="search" autocomplete="off" placeholder="Cari nama produk atau barcode" class="w-full bg-transparent text-ink outline-none">
        </label>
        <x-forms.select id="fCat" aria-label="Kategori" />
        <x-forms.select id="fSts" aria-label="Status stok" :options="['' => 'Semua status', 'habis' => 'Habis', 'rendah' => 'Rendah', 'cukup' => 'Cukup']" />
      </div>
    </div>
    <x-ui.table :headers="['Produk', ['Barcode', 'left', 'hidden md:table-cell'], ['Kategori', 'left', 'hidden md:table-cell'], ['Biaya perolehan', 'right', 'hidden md:table-cell'], ['Harga jual', 'right'], ['Stok', 'right'], 'Status stok', ['Aksi', 'right']]" body-id="rows" :min-width="980" :md-min-width="980" />
    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-line px-6 py-4 text-[12px] text-muted">
      <span id="foot"></span>
      <span class="flex items-center gap-3">
        <button type="button" id="prev" class="rounded-lg border border-line px-4 py-2 font-bold text-brand-dark disabled:cursor-not-allowed disabled:opacity-40">Sebelumnya</button>
        <span id="pg" class="grid size-8 place-items-center rounded-md bg-tint font-bold text-brand-dark">1</span>
        <button type="button" id="next" class="rounded-lg border border-line px-4 py-2 font-bold text-brand-dark disabled:cursor-not-allowed disabled:opacity-40">Berikutnya</button>
      </span>
    </div>
  </x-ui.card>

  <x-ui.card class="mt-5" flush>
    <div class="p-6 pb-3">
      <h2 class="text-[18px] font-semibold">Riwayat stok terbaru</h2>
      <p id="mvSub" class="mt-1 text-[12px] text-muted"></p>
    </div>
    <ul id="moves" class="px-6 pb-3"></ul>
  </x-ui.card>

  <p class="mt-6 flex items-center gap-2 text-[12px] text-muted">
    <span><x-ui.icon name="info" size="size-4" /></span>Stok tercatat = stok awal + barang masuk – penjualan ± penyesuaian. Data tersimpan di browser ini; cocokkan dengan stok fisik secara berkala.
  </p>

  <x-ui.modal />

@push('scripts')
<script>
  (function () {
      var M = window.Mantou,
      rp = M.fmt.rupiah,
      state = @json($initialState),
      PER = 10,
      page = 1;
      var $ = function (id) {
          return document.getElementById(id);
      };
      function getData() {
            return state;
        }

        function findProduct(id) {
            return state.products.find(function (p) {
                return String(p.id) === String(id);
            });
        }
      var esc = function (t) {
          return String(t == null ? "" : t).replace(
              /[&<>"']/g,
              function (c) {
                  return {
                      "&": "&amp;",
                      "<": "&lt;",
                      ">": "&gt;",
                      '"': "&quot;",
                      "'": "&#39;",
                  }[c];
              },
          );
      };
      var ICON =
          '<svg class="size-[18px] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">';
      var BADGE = {
          habis: ["bg-danger-dark text-white", "Habis"],
          rendah: ["border border-warning bg-warn-bg text-warn", "Rendah"],
          cukup: ["bg-tint text-brand-dark", "Cukup"],
      };
      var INP =
          "mt-1.5 w-full rounded-lg border border-line bg-surface px-3 py-2.5 text-[13px] outline-none focus:border-brand";
      var LBL = "block text-[12px] font-semibold text-muted";

      function kpi(label, val, cap, icon, hl) {
          return (
              '<div class="rounded-xl border ' +
              (hl
                  ? "border-brand bg-tint"
                  : "border-line bg-surface") +
              ' p-4 sm:p-5"><div class="text-[12px] text-muted">' +
              label +
              '</div><p class="mt-3 break-words text-[22px] font-bold leading-none tracking-tight text-ink sm:text-[28px]">' +
              val +
              '</p><p class="mt-3 text-[12px] text-muted">' +
              cap +
              "</p></div>"
          );
      }
      function renderKpis(P) {
          var cats = {},
              units = 0,
              low = 0,
              val = 0;
          P.forEach(function (p) {
              cats[p.category] = 1;
              units += p.stock;
              val += p.stock * p.cost;
              if (M.status(p) !== "cukup") low++;
          });
          var nc = Object.keys(cats).length;
          $("kpis").innerHTML =
              kpi(
                  "Produk terdaftar",
                  P.length,
                  P.length
                      ? "Aktif dalam " + nc + " kategori"
                      : "Belum ada produk",
                  '<path d="M12 3l8 4.5v9L12 21l-8-4.5v-9L12 3z"/><path d="M4 7.5l8 4.5 8-4.5M12 12v9"/>',
              ) +
              kpi(
                  "Stok tercatat",
                  units.toLocaleString("id-ID") + " unit",
                  "Gabungan seluruh satuan produk",
                  '<path d="M12 3l8 4.5v9L12 21l-8-4.5v-9L12 3z"/><path d="M4 7.5l8 4.5 8-4.5M12 12v9"/>',
              ) +
              kpi(
                  "Di bawah minimum",
                  low + " produk",
                  low
                      ? "Perlu ditinjau untuk barang masuk"
                      : "Semua stok di atas batas minimum",
                  '<path d="M12 4l9 16H3L12 4z"/><path d="M12 10v4M12 17v.5"/>',
              ) +
              kpi(
                  "Nilai stok tercatat",
                  rp(val),
                  "Dihitung dari biaya perolehan",
                  '<path d="M4 8a2 2 0 012-2h11v3"/><path d="M4 8v9a2 2 0 002 2h13a1 1 0 001-1V9a1 1 0 00-1-1H6"/><circle cx="16.5" cy="13.5" r="1"/>',
                  true,
              );
      }

      function filtered(P) {
          var q = $("q").value.trim().toLowerCase(),
              c = $("fCat").value,
              st = $("fSts").value;
          return P.filter(function (p) {
              return (
                  (!q ||
                      p.name.toLowerCase().indexOf(q) > -1 ||
                      (p.barcode || "").indexOf(
                          q.replace(/\s/g, ""),
                      ) > -1) &&
                  (!c || p.category === c) &&
                  (!st || M.status(p) === st)
              );
          });
      }
      function renderCats(P) {
          var cur = $("fCat").value,
              cats = [];
          P.forEach(function (p) {
              if (cats.indexOf(p.category) < 0) cats.push(p.category);
          });
          cats.sort();
          $("fCat").innerHTML =
              '<option value="">Semua kategori</option>' +
              cats
                  .map(function (c) {
                      return (
                          "<option" +
                          (c === cur ? " selected" : "") +
                          ">" +
                          esc(c) +
                          "</option>"
                      );
                  })
                  .join("");
      }
      var ACT = "rounded-md px-2 py-2 font-semibold text-muted hover:bg-tint hover:text-brand-dark";
      function renderRows(P) {
          var list = filtered(P),
              pages = Math.max(1, Math.ceil(list.length / PER));
          if (page > pages) page = pages;
          var cut = list.slice((page - 1) * PER, page * PER);
          $("rows").innerHTML = cut.length
              ? cut
                    .map(function (p) {
                        var b = BADGE[M.status(p)],
                            bad = p.price < p.cost;
                        return (
                            '<tr class="border-t border-line"><td class="px-5 py-4 text-[13px]"><a href="/cek-harga?q=' +
                            encodeURIComponent(p.barcode || p.name) +
                            '" class="font-bold hover:text-brand-dark hover:underline">' +
                            esc(p.name) +
                            '</a><span class="block text-[12px] text-muted">' +
                            esc(p.unit) +
                            " · minimum " +
                            p.min +
                            "</span></td>" +
                            '<td class="hidden px-3 py-4 text-[13px] md:table-cell">' +
                            (esc(p.barcode) ||
                                '<span class="text-muted">—</span>') +
                            '</td><td class="hidden px-3 py-4 text-[13px] md:table-cell">' +
                            esc(p.category) +
                            "</td>" +
                            '<td class="hidden px-3 py-4 text-right text-[13px] md:table-cell"><span class="text-muted">' +
                            rp(p.cost) +
                            '</span></td><td class="px-3 py-4 text-right text-[13px]">' +
                            rp(p.price) +
                            (bad
                                ? '<span class="block text-[11px] font-bold text-danger-dark">di bawah biaya</span>'
                                : "") +
                            (p.promo
                                ? '<span class="block text-[11px] font-bold text-brand-dark">diskon ' +
                                  p.promo.pct +
                                  "%</span>"
                                : "") +
                            "</td>" +
                            '<td class="px-3 py-4 text-right text-[13px] font-bold">' +
                            p.stock +
                            '</td><td class="px-3 py-4"><span class="inline-block rounded-md px-2.5 py-1 text-[12px] font-bold ' +
                            b[0] +
                            '">' +
                            b[1] +
                            "</span></td>" +
                            '<td class="px-3 py-3 text-right text-[12px] md:whitespace-nowrap"><button type="button" data-a="edit" data-id="' +
                            p.id +
                            '" class="' +
                            ACT +
                            '">Ubah</button><button type="button" data-a="in" data-id="' +
                            p.id +
                            '" class="' +
                            ACT +
                            '">Masuk</button><button type="button" data-a="adj" data-id="' +
                            p.id +
                            '" class="' +
                            ACT +
                            '">Sesuaikan</button><button type="button" data-a="del" data-id="' +
                            p.id +
                            '" class="rounded-md px-2 py-2 font-semibold text-danger-dark hover:bg-danger-bg">Hapus</button></td></tr>'
                        );
                    })
                    .join("")
              : '<tr class="border-t border-line"><td colspan="8" class="px-5 py-10 text-center text-[13px] text-muted">' +
                (P.length
                    ? "Tidak ada produk yang cocok dengan pencarian."
                    : "Belum ada produk. Klik “Tambah produk” untuk memulai.") +
                "</td></tr>";
          $("foot").textContent = list.length
              ? "Menampilkan " +
                ((page - 1) * PER + 1) +
                "–" +
                ((page - 1) * PER + cut.length) +
                " dari " +
                list.length +
                " produk"
              : "Menampilkan 0 produk";
          $("pg").textContent = page;
          $("prev").disabled = page <= 1;
          $("next").disabled = page >= pages;
      }

      function renderMoves(s) {
          var l = s.moves
              .slice()
              .sort(function (a, b) {
                  return b.ts - a.ts;
              })
              .slice(0, 8);
          $("mvSub").textContent = s.moves.length
              ? "Barang masuk dan penyesuaian · " +
                s.moves.length +
                " catatan"
              : "Belum ada barang masuk atau penyesuaian";
          $("moves").innerHTML = l.length
              ? l
                    .map(function (m, i) {
                        var inn = m.type === "in",
                            sign =
                                m.qty > 0
                                    ? "+" + m.qty
                                    : String(m.qty);
                        return (
                            '<li class="flex flex-wrap items-center justify-between gap-2 py-3.5 text-[13px]' +
                            (i ? " border-t border-line" : "") +
                            '"><span class="min-w-0"><span class="mr-2 inline-block rounded-md px-2 py-0.5 text-[12px] font-bold ' +
                            (inn
                                ? "bg-tint text-brand-dark"
                                : "bg-line text-muted") +
                            '">' +
                            (inn ? "Masuk" : "Penyesuaian") +
                            "</span><b>" +
                            esc(m.name) +
                            '</b><span class="block text-[12px] text-muted">' +
                            m.id +
                            " · " +
                            M.fmt.dateTime(m.ts) +
                            (m.note ? " · " + esc(m.note) : "") +
                            '</span></span><span class="flex items-center gap-4"><span class="font-bold">' +
                            sign +
                            ' <span class="font-normal text-muted">(' +
                            m.before +
                            " → " +
                            m.after +
                            ')</span></span><button type="button" data-v="' +
                            m.id +
                            '" class="text-[12px] font-semibold text-muted hover:text-danger-dark">Batalkan</button></span></li>'
                        );
                    })
                    .join("")
              : '<li class="py-6 text-[12px] text-muted">Catatan barang masuk dan penyesuaian akan muncul di sini.</li>';
      }

      function render() {
          var s = getData();
          renderKpis(s.products);
          renderCats(s.products);
          renderRows(s.products);
          renderMoves(s);
      }

      var stream = null,
          timer = null,
          zx = null,
          scanning = false;
      function scanMsg(t, warn) {
          var e = $("scanMsg");
          if (!e) return;
          e.textContent = t;
          e.className =
              "mt-2 text-[12px] " +
              (warn ? "font-semibold text-warn" : "text-muted");
      }
      function stopScan() {
          scanning = false;
          clearTimeout(timer);
          timer = null;
          if (zx) {
              try {
                  zx.reset();
              } catch (e) {}
              zx = null;
          }
          if (stream) {
              stream.getTracks().forEach(function (t) {
                  t.stop();
              });
              stream = null;
          }
          var v = $("scanVideo"),
              b = $("scanBox"),
              btn = $("scanBtn");
          if (v) v.srcObject = null;
          if (b) b.classList.add("hidden");
          if (btn) btn.textContent = "Pindai";
      }
      function norm(b) {
          return String(b || "")
              .replace(/\D/g, "")
              .replace(/^0+/, "");
      }
      function onCode(code, typed) {
          code = String(code).replace(/\s/g, "");
          if (!code) return;
          if (!typed && !scanning) return;
          stopScan();
          if (!typed && navigator.vibrate) navigator.vibrate(60);
          $("fBc").value = code;
          var dup = getData().products.filter(function (p) {
              return p.barcode && norm(p.barcode) === norm(code);
          })[0];
          if (dup)
              scanMsg(
                  "Barcode " +
                      code +
                      " sudah terdaftar sebagai “" +
                      dup.name +
                      "” (stok " +
                      dup.stock +
                      "). Tutup dialog ini lalu pakai Ubah atau Masuk pada produk tersebut.",
                  true,
              );
          else {
              scanMsg(
                  "Barcode terbaca: " +
                      code +
                      ". Lengkapi nama, harga, dan stok awal.",
              );
              var n = $("fName");
              if (n && !n.value) n.focus();
          }
      }
      function loadZXing() {
          return new Promise(function (ok, no) {
              if (window.ZXing) return ok();
              var s = document.createElement("script");
              s.src =
                  "https://cdn.jsdelivr.net/npm/@zxing/library@0.21.3/umd/index.min.js";
              s.onload = ok;
              s.onerror = function () {
                  no(
                      new Error(
                          "Pemindai cadangan gagal dimuat (perlu internet). Ketik barcode secara manual.",
                      ),
                  );
              };
              document.head.appendChild(s);
          });
      }
      async function startScan() {
          if (scanning || stream) return;
          if (
              !navigator.mediaDevices ||
              !navigator.mediaDevices.getUserMedia
          ) {
              scanMsg(
                  "Browser ini tidak mendukung kamera (butuh HTTPS atau localhost). Ketik barcode secara manual.",
                  true,
              );
              return;
          }
          scanning = true;
          $("scanBox").classList.remove("hidden");
          $("scanBtn").textContent = "Batal";
          scanMsg("Arahkan barcode kemasan ke kamera…");
          try {
              stream = await navigator.mediaDevices.getUserMedia({
                  video: { facingMode: { ideal: "environment" } },
                  audio: false,
              });
              if (!scanning) {
                  stopScan();
                  return;
              }
              var video = $("scanVideo");
              video.srcObject = stream;
              await video.play();
              if ("BarcodeDetector" in window) {
                  var det = new BarcodeDetector({
                      formats: [
                          "ean_13",
                          "ean_8",
                          "upc_a",
                          "upc_e",
                          "code_128",
                          "code_39",
                          "itf",
                      ],
                  });
                  var tick = async function () {
                      if (!scanning) return;
                      try {
                          var f = await det.detect(video);
                          if (f.length) {
                              onCode(f[0].rawValue);
                              return;
                          }
                      } catch (e) {}
                      timer = setTimeout(tick, 200);
                  };
                  tick();
              } else {
                  await loadZXing();
                  if (!scanning) return;
                  zx = new ZXing.BrowserMultiFormatReader();
                  zx.decodeFromStream(stream, video, function (r) {
                      if (r) onCode(r.getText());
                  });
              }
          } catch (err) {
              stopScan();
              var n = err && err.name;
              scanMsg(
                  n === "NotAllowedError" || n === "SecurityError"
                      ? "Izin kamera ditolak. Aktifkan izin kamera di browser, atau ketik barcode manual."
                      : n === "NotFoundError" ||
                          n === "OverconstrainedError"
                        ? "Kamera tidak ditemukan di perangkat ini."
                        : (err && err.message) ||
                          "Kamera tidak dapat digunakan.",
                  true,
              );
          }
      }


        function open(title, sub, body, okText, onOk) {
            $("modalBox").innerHTML =
                '<h2 class="text-[20px] font-semibold">' +
                title +
                '</h2><p class="mt-1 text-[12px] text-muted">' +
                sub +
                '</p><div class="mt-5 space-y-4">' +
                body +
                '</div><p id="mErr" class="mt-4 hidden rounded-lg border border-danger bg-danger-bg px-3 py-2 text-[12px] font-semibold text-warn"></p><div class="mt-6 flex justify-end gap-2.5"><button type="button" id="mCancel" class="rounded-lg border border-line px-4 py-2.5 text-[13px] font-bold hover:border-brand">Batal</button><button type="button" id="mOk" class="rounded-lg bg-brand px-4 py-2.5 text-[13px] font-bold text-surface hover:bg-brand-dark">' +
                okText +
                "</button></div>";

            $("modal").classList.remove("hidden");
            $("modal").classList.add("flex");

            $("mCancel").onclick = close;

            $("mOk").onclick = function () {
                var btn = $("mOk");
                var err = $("mErr");

                err.classList.add("hidden");

                function showError(message) {
                    err.textContent = message;
                    err.classList.remove("hidden");
                    btn.disabled = false;
                    btn.textContent = okText;
                }

                var result;

                try {
                    result = onOk();
                } catch (error) {
                    showError(error.message || "Terjadi kesalahan.");
                    return;
                }

                if (result && typeof result.then === "function") {
                    btn.disabled = true;
                    btn.textContent = "Menyimpan...";

                    result.then(function (r) {
                        if (r && !r.ok) {
                            showError(r.error);
                        } else {
                            close();
                        }
                    }).catch(function (error) {
                        showError(error.message || "Gagal menyimpan data.");
                    });

                } else if (result && !result.ok) {
                    showError(result.error);
                } else {
                    close();
                }
            };

            var f = $("modalBox").querySelector("input,select");
            if (f) f.focus();
        }

      function close() {
          stopScan();
          $("modal").classList.add("hidden");
          $("modal").classList.remove("flex");
      }
      $("modal").addEventListener("mousedown", function (e) {
          if (e.target === $("modal")) close();
      });
      document.addEventListener("keydown", function (e) {
          if (e.key === "Escape") close();
      });
      var v = function (id) {
          return $(id).value;
      };
      function field(id, label, val, attrs, hint) {
          return (
              '<label class="' +
              LBL +
              '">' +
              label +
              '<input id="' +
              id +
              '" value="' +
              esc(val) +
              '" ' +
              (attrs || "") +
              ' class="' +
              INP +
              '">' +
              (hint
                  ? '<span class="mt-1 block text-[12px] font-normal">' +
                    hint +
                    "</span>"
                  : "") +
              "</label>"
          );
      }
      function prodSelect(id, sel) {
          return (
              '<label class="' +
              LBL +
              '">Produk<select id="' +
              id +
              '" class="' +
              INP +
              '"><option value="">Pilih produk…</option>' +
              getData()
                  .products.map(function (p) {
                      return (
                          '<option value="' +
                          p.id +
                          '"' +
                          (p.id === sel ? " selected" : "") +
                          ">" +
                          esc(p.name) +
                          " — stok " +
                          p.stock +
                          "</option>"
                      );
                  })
                  .join("") +
              "</select></label>"
          );
      }

      function productForm(p, pre) {
          var isNew = !p;
          p = p || pre || {};
          var cats = [];
          getData().products.forEach(function (x) {
              if (cats.indexOf(x.category) < 0) cats.push(x.category);
          });
          open(
              isNew ? "Tambah produk" : "Ubah produk",
              isNew
                  ? "Daftarkan produk baru beserta stok awalnya."
                  : "Stok diubah lewat Barang masuk atau Penyesuaian stok.",
              field(
                  "fName",
                  "Nama produk",
                  p.name || "",
                  'type="text"',
              ) +
                  '<div><label for="fBc" class="' +
                  LBL +
                  '">Barcode (opsional)</label><div class="mt-1.5 flex gap-2"><input id="fBc" type="text" inputmode="numeric" autocomplete="off" value="' +
                  esc(p.barcode || "") +
                  '" placeholder="Ketik, pindai kamera, atau alat scan" class="min-w-0 flex-1 rounded-lg border border-line bg-surface px-3 py-2.5 text-[13px] outline-none focus:border-brand"><button type="button" id="scanBtn" class="shrink-0 rounded-lg border border-line px-3.5 text-[13px] font-bold text-brand-dark hover:border-brand">Pindai</button></div><p id="scanMsg" class="mt-2 hidden text-[12px] text-muted"></p><div id="scanBox" class="relative mt-3 hidden aspect-video overflow-hidden rounded-xl bg-ink"><video id="scanVideo" class="size-full object-cover" playsinline muted autoplay></video><span class="pointer-events-none absolute inset-x-6 top-1/2 h-0.5 bg-brand"></span></div></div>' +
                  '<div class="grid grid-cols-2 gap-4">' +
                  field(
                      "fUnit",
                      "Satuan",
                      p.unit || "",
                      'type="text" placeholder="bungkus, botol…"',
                  ) +
                  '<label class="' +
                  LBL +
                  '">Kategori<input id="fCatIn" list="catList" value="' +
                  esc(p.category || "") +
                  '" class="' +
                  INP +
                  '"><datalist id="catList">' +
                  cats
                      .map(function (c) {
                          return '<option value="' + esc(c) + '">';
                      })
                      .join("") +
                  "</datalist></label></div>" +
                  '<div class="grid grid-cols-2 gap-4">' +
                  field(
                      "fCost",
                      "Biaya perolehan (Rp)",
                      p.cost != null ? p.cost : "",
                      'type="number" min="0" step="1" inputmode="numeric"',
                  ) +
                  field(
                      "fPrice",
                      "Harga jual (Rp)",
                      p.price != null ? p.price : "",
                      'type="number" min="1" step="1" inputmode="numeric"',
                  ) +
                  "</div>" +
                  '<div class="grid grid-cols-2 gap-4">' +
                  (isNew
                      ? field(
                            "fStock",
                            "Stok awal",
                            0,
                            'type="number" min="0" step="1" inputmode="numeric"',
                        )
                      : '<div class="' +
                        LBL +
                        '">Stok tercatat<p class="mt-3 text-[13px] font-bold text-ink">' +
                        p.stock +
                        " " +
                        esc(p.unit) +
                        "</p></div>") +
                  field(
                      "fMin",
                      "Batas minimum stok",
                      p.min != null ? p.min : 10,
                      'type="number" min="0" step="1" inputmode="numeric"',
                  ) +
                  "</div>",
              isNew ? "Simpan produk" : "Simpan perubahan",
            async function () {
                var d = {
                    name: v("fName").trim(),
                    barcode: v("fBc").trim(),
                    unit: v("fUnit").trim(),
                    category: v("fCatIn").trim(),
                    cost: v("fCost"),
                    price: v("fPrice"),
                    min: v("fMin"),
                    stock: isNew ? v("fStock") : 0,
                };

                try {
                    var response = await fetch(
                        isNew
                            ? "/produk-stok"
                            : "/produk-stok/" + encodeURIComponent(p.id),
                        {
                            method: isNew ? "POST" : "PUT",
                            headers: {
                                "Content-Type": "application/json",
                                "Accept": "application/json",
                                "X-CSRF-TOKEN": @json(csrf_token()),
                            },
                            body: JSON.stringify(d),
                        }
                    );

                    var result = await response.json();

                    if (!response.ok) {
                        var message = result.message || "Produk gagal disimpan.";

                        if (result.errors) {
                            message = Object.values(result.errors)
                                .flat()
                                .join(" ");
                        }

                        return {
                            ok: false,
                            error: message,
                        };
                    }

                    if (isNew) {
                        state.products.unshift(result.product);

                        if (result.move) {
                            state.moves.unshift(result.move);
                        }

                        page = 1;
                        toast("Produk ditambahkan: " + result.product.name);
                    } else {
                        var index = state.products.findIndex(function (item) {
                            return String(item.id) === String(result.product.id);
                        });

                        if (index !== -1) {
                            state.products[index] = result.product;
                        }

                        toast("Produk diperbarui: " + result.product.name);
                    }

                    render();

                    return { ok: true };

                } catch (error) {
                    return {
                        ok: false,
                        error: "Tidak dapat terhubung ke server. Periksa koneksi dan coba lagi.",
                    };
                }
            },
          );
          $("scanBtn").onclick = function () {
              scanning ? stopScan() : startScan();
          };
          $("fBc").addEventListener("keydown", function (e) {
              if (e.key === "Enter") {
                  e.preventDefault();
                  onCode($("fBc").value.trim(), true);
              }
          });
          if (pre && pre.scan) startScan();
      }
      function stockInForm(pid) {
          open(
              "Barang masuk",
              "Catat barang yang diterima. Stok bertambah sebesar jumlah masuk.",
              prodSelect("iPid", pid) +
                  '<div class="grid grid-cols-2 gap-4">' +
                  field(
                      "iQty",
                      "Jumlah masuk",
                      1,
                      'type="number" min="1" step="1" inputmode="numeric"',
                  ) +
                  field(
                      "iCost",
                      "Biaya perolehan baru (opsional)",
                      "",
                      'type="number" min="0" step="1" inputmode="numeric"',
                      "Kosongkan bila tidak berubah.",
                  ) +
                  "</div>" +
                  field(
                      "iTs",
                      "Tanggal & waktu",
                      M.fmt.isoLocal(Date.now()),
                      'type="datetime-local"',
                  ) +
                  field(
                      "iNote",
                      "Catatan (opsional)",
                      "",
                      'type="text" placeholder="Mis. dari pemasok A"',
                  ),
              "Simpan barang masuk",               
                async function () {
                    var ts = M.fmt.parseDT(v("iTs"));

                    if (ts > Date.now() + 60000) {
                        return {
                            ok: false,
                            error: "Waktu tidak boleh di masa depan.",
                        };
                    }

                    try {
                        var response = await fetch("/produk-stok/stock-in", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "Accept": "application/json",
                                "X-CSRF-TOKEN": @json(csrf_token()),
                            },
                            body: JSON.stringify({
                                pid: v("iPid"),
                                qty: v("iQty"),
                                cost: v("iCost") === "" ? null : v("iCost"),
                                ts: ts,
                                note: v("iNote").trim(),
                            }),
                        });

                        var result = await response.json();

                        if (!response.ok) {
                            return {
                                ok: false,
                                error: result.message || "Gagal mencatat barang masuk.",
                            };
                        }

                        var product = result.product;
                        var move = result.move;

                        var index = state.products.findIndex(function (item) {
                            return String(item.id) === String(product.id);
                        });

                        if (index !== -1) {
                            state.products[index] = product;
                        }

                        if (move) {
                            state.moves.unshift(move);
                        }

                        toast(
                            product.name +
                                " bertambah " +
                                move.qty +
                                " · stok " +
                                product.stock
                        );

                        render();

                        return {
                            ok: true,
                            product: product,
                            move: move,
                        };

                    } catch (error) {
                        return {
                            ok: false,
                            error: "Tidak dapat terhubung ke server.",
                        };
                    }
                },
          );
      }
      function adjustForm(pid) {
          open(
              "Penyesuaian stok",
              "Isi jumlah stok fisik hasil hitung. Selisihnya dicatat sebagai penyesuaian.",
              prodSelect("aPid", pid) +
                  '<div class="grid grid-cols-2 gap-4">' +
                  field(
                      "aCnt",
                      "Stok fisik sebenarnya",
                      "",
                      'type="number" min="0" step="1" inputmode="numeric"',
                  ) +
                  field(
                      "aTs",
                      "Tanggal & waktu",
                      M.fmt.isoLocal(Date.now()),
                      'type="datetime-local"',
                  ) +
                  "</div>" +
                  field(
                      "aNote",
                      "Alasan",
                      "",
                      'type="text" placeholder="Rusak, hilang, salah hitung…"',
                  ),
              "Simpan penyesuaian",
              function () {
                  var ts = M.fmt.parseDT(v("aTs"));
                  if (ts > Date.now() + 60000)
                      return {
                          ok: false,
                          error: "Waktu tidak boleh di masa depan.",
                      };
                  var r = M.adjustStock({
                      pid: v("aPid"),
                      counted: v("aCnt"),
                      ts: ts,
                      note: v("aNote"),
                  });
                  if (r.ok) {
                      toast(
                          "Stok " +
                              r.product.name +
                              " disesuaikan: " +
                              r.move.before +
                              " → " +
                              r.move.after,
                      );
                      render();
                  }
                  return r;
              },
          );
      }

      $("btnAdd").onclick = function () {
          productForm(null);
      };
      $("btnScan").onclick = function () {
          productForm(null, { scan: true });
      };
      $("btnIn").onclick = function () {
          stockInForm("");
      };
      $("btnAdj").onclick = function () {
          adjustForm("");
      };
      ["q", "fCat", "fSts"].forEach(function (id) {
          $(id).addEventListener("input", function () {
              page = 1;
              renderRows(getData().products);
          });
      });
      $("prev").onclick = function () {
          page--;
          renderRows(getData().products);
      };
      $("next").onclick = function () {
          page++;
          renderRows(getData().products);
      };
      $("rows").addEventListener("click", async function (e) {
        var b = e.target.closest("[data-a]");
        if (!b) return;

        var p = findProduct(b.getAttribute("data-id")),
            a = b.getAttribute("data-a");

        if (!p) return;

        if (a === "edit") {
            productForm(p);
        } else if (a === "in") {
            stockInForm(p.id);
        } else if (a === "adj") {
            adjustForm(p.id);
        } else if (
            confirm(
                'Hapus "' +
                    p.name +
                    '" dari daftar produk? Riwayat penjualan lama tetap tersimpan.',
            )
        ) {
            try {
                var response = await fetch(
                    "/produk-stok/" + encodeURIComponent(p.id),
                    {
                        method: "DELETE",
                        headers: {
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": @json(csrf_token()),
                        },
                    }
                );

                var result = await response.json();

                if (!response.ok) {
                    var message = result.message || "Produk gagal dihapus.";

                    if (result.errors) {
                        message = Object.values(result.errors)
                            .flat()
                            .join(" ");
                    }

                    toast(message);
                    return;
                }

                state.products = state.products.filter(function (item) {
                    return String(item.id) !== String(p.id);
                });

                render();
                toast("Produk dihapus: " + p.name);

            } catch (error) {
                toast("Tidak dapat terhubung ke server.");
            }
        }
      });
      $("moves").addEventListener("click", function (e) {
          var b = e.target.closest("[data-v]");
          if (!b) return;
          var id = b.getAttribute("data-v");
          if (
              !confirm(
                  "Batalkan catatan stok " +
                      id +
                      "? Stok produk dikembalikan seperti sebelumnya.",
              )
          )
              return;
          var r = M.voidMove(id);
          if (r.ok) {
              toast("Catatan " + id + " dibatalkan");
              render();
          } else alert(r.error);
      });
      M.onChange(render);
      window.addEventListener("pageshow", render);

      render();
      var qs = new URLSearchParams(location.search),
          aksi = qs.get("aksi");
      if (qs.get("cari")) {
          $("q").value = qs.get("cari");
          renderRows(getData().products);
      }
      if (aksi === "tambah")
          productForm(null, {
              barcode: qs.get("barcode") || "",
              name: qs.get("nama") || "",
          });
      else if (aksi === "pindai") productForm(null, { scan: true });
      else if (aksi === "masuk") stockInForm(qs.get("pid") || "");
      else if (aksi === "sesuaikan") adjustForm(qs.get("pid") || "");
  })();
</script>
@endpush
</x-layouts.app>
