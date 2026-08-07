{{--
  ============================ FITUR SEMENTARA ============================
  Modal uji kiriman WhatsApp untuk sesi UJI COBA PENGGUNA (2026-08-07).
  Tombol pemicunya disisipkan terpisah di toolbar filter daftarDokumen.blade.php
  (cari id="btnUjiWhatsApp").

  Daftar pencabutan lengkap ada di docblock
  App\Http\Controllers\UjiWhatsAppBagianController.
  =========================================================================
--}}
@push('styles')
<style>
  /* Kelas ber-scope .uwa-*, NOL !important — mengikuti pola .notif-pengembalian
     di halaman yang sama. Jangan berperang spesifisitas dengan Bootstrap CDN. */

  /* Bentuk meniru .btn-refresh (tinggi 44px, radius 8px, inline-flex) supaya
     sebaris rapi dengannya. Warnanya sengaja beda: ini tombol uji, bukan aksi
     sehari-hari. */
  .uwa-tombol {
    padding: 10px 20px;
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 2px 6px rgba(245, 158, 11, 0.3);
    min-height: 44px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
  }
  .uwa-tombol:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
    background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
  }
  .uwa-tombol:active { transform: translateY(0); }

  .uwa-ket {
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: 10px;
    padding: 12px 14px;
    font-size: 13.5px;
    line-height: 1.6;
    color: #78350f;
    margin-bottom: 16px;
  }
  .uwa-ket strong { color: #92400e; }

  .uwa-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 6px;
  }
  .uwa-input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    color: #1a2340;
  }
  .uwa-input:focus {
    outline: 2px solid #f59e0b;
    outline-offset: 1px;
    border-color: #f59e0b;
  }

  .uwa-hasil {
    font-size: 13px;
    line-height: 1.5;
    margin-top: 10px;
    min-height: 1em;
  }
  .uwa-hasil--ok    { color: #047857; }
  .uwa-hasil--gagal { color: #b91c1c; }

  .uwa-kirim:disabled { opacity: .6; cursor: progress; }

  /* Bentuk cangkang modal. Dua modal lain di berkas ini memakai atribut style=
     inline untuk hal yang sama; DI SINI TIDAK — CLAUDE.md aturan 4 melarang CSS
     inline baru, dan style= adalah spesifisitas tertinggi yang justru memulai
     perang yang dilarang itu. Selektor .uwa-modal .modal-* (0,2,0) sudah menang
     atas .modal-* milik Bootstrap (0,1,0) tanpa satu pun !important. */
  .uwa-modal .modal-content { border: none; border-radius: 16px; }
  .uwa-modal .modal-header  { border-bottom: 1px solid #e2e8f0; }
  .uwa-modal .modal-footer  { border-top: 1px solid #e2e8f0; }
  .uwa-modal .modal-body    { padding: 1.25rem 1.5rem; }
  .uwa-modal .modal-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 1.05rem;
    font-weight: 700;
    color: #1f2937;
  }
  .uwa-modal .modal-title i { color: #d97706; }
</style>
@endpush

{{-- Markup statis + dibuka lewat instance bootstrap.Modal eksplisit — pola yang
     SAMA dengan #perjalananModal & #rejectionDetailModal di berkas ini. Jangan
     mengarang mekanisme ketiga. --}}
<div class="modal fade uwa-modal" id="ujiWhatsAppModal" tabindex="-1"
  aria-labelledby="ujiWhatsAppModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ujiWhatsAppModalLabel">
          <i class="fa-solid fa-flask" aria-hidden="true"></i>Uji Kirim Pesan WhatsApp
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>

      <div class="modal-body">
        <div class="uwa-ket">
          Uji coba ini akan mengirim <strong>satu pesan WhatsApp</strong> berisi
          pemberitahuan <strong>&ldquo;dokumen dikembalikan&rdquo;</strong> ke nomor yang
          Bapak/Ibu masukkan &mdash; bentuknya sama persis dengan pemberitahuan sungguhan.
          <br><br>
          <strong>Tidak ada dokumen yang benar-benar dikembalikan.</strong> Pesannya
          memakai data contoh dan bertanda <strong>[UJI COBA]</strong>.
        </div>

        <label class="uwa-label" for="uwaNomor">Nomor WhatsApp</label>
        <input type="tel" id="uwaNomor" class="uwa-input"
               placeholder="Contoh: 081234567890" autocomplete="tel" inputmode="numeric">

        <div class="uwa-hasil" id="uwaHasil" role="status" aria-live="polite"></div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="uwa-tombol uwa-kirim" id="uwaKirim">
          <i class="fa-brands fa-whatsapp"></i> Kirim
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  (function () {
    const pemicu = document.getElementById('btnUjiWhatsApp');
    const modalEl = document.getElementById('ujiWhatsAppModal');
    const tombol = document.getElementById('uwaKirim');
    const input  = document.getElementById('uwaNomor');
    const hasil  = document.getElementById('uwaHasil');
    if (!pemicu || !modalEl || !tombol || !input || !hasil) return;

    const URL_KIRIM = @json(route('bagian.uji-whatsapp'));

    pemicu.addEventListener('click', function () {
      hasil.textContent = '';
      hasil.className = 'uwa-hasil';
      new bootstrap.Modal(modalEl).show();
      setTimeout(function () { input.focus(); }, 300);
    });

    function tulisHasil(teks, ok) {
      // textContent, BUKAN innerHTML — pesan galat Fonnte adalah teks dari pihak
      // luar dan tidak boleh diperlakukan sebagai HTML.
      hasil.textContent = teks;
      hasil.className = 'uwa-hasil ' + (ok ? 'uwa-hasil--ok' : 'uwa-hasil--gagal');
    }

    tombol.addEventListener('click', async function () {
      const nomor = input.value.trim();
      if (nomor === '') {
        tulisHasil('Nomor WhatsApp wajib diisi.', false);
        input.focus();
        return;
      }

      tombol.disabled = true;
      const isiAsli = tombol.innerHTML;
      tombol.textContent = 'Mengirim…';
      hasil.textContent = '';

      try {
        const res = await fetch(URL_KIRIM, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
          },
          body: JSON.stringify({ nomor_hp: nomor }),
        });

        if (res.status === 429) {
          tulisHasil('Terlalu sering. Tunggu sebentar sebelum mencoba lagi.', false);
          return;
        }

        const data = await res.json().catch(function () { return {}; });

        if (res.status === 422) {
          tulisHasil(data.errors?.nomor_hp?.[0] || 'Nomor WhatsApp tidak sah.', false);
          return;
        }

        if (!res.ok) {
          tulisHasil('Terjadi kesalahan di server (' + res.status + ').', false);
          return;
        }

        tulisHasil(data.pesan || 'Tidak ada keterangan dari server.', data.ok === true);
      } catch (e) {
        tulisHasil('Gagal menghubungi server: ' + e.message, false);
      } finally {
        tombol.disabled = false;
        tombol.innerHTML = isiAsli;
      }
    });

    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') { e.preventDefault(); tombol.click(); }
    });
  })();
</script>
