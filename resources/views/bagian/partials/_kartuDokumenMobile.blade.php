{{-- Kartu dokumen untuk layar ponsel (role Bagian).

     Dirender BERDAMPINGAN dengan tabel: tabel tampil di desktop, kartu tampil
     di ponsel. Keduanya independen — mengubah kartu tak menyentuh tabel.

     Kenapa bukan mengubah <table> jadi kartu lewat CSS: tabel Bagian punya 11
     kolom, sel ber-onclick, dan navigasi keyboard (_activeCellNav) yang memasang
     cache baris pada tbody. Memaksa display:block di sana rapuh.

     NOL fungsi JS baru — kartu memanggil tampilkanPerjalanan() dan
     showRejectionModal() yang sudah ada di daftarDokumen.blade.php.

     Butuh: $dokumens (paginator), $perjalanan (array ber-key id dokumen).
--}}

@push('styles')
<style>
  .mob-cards {
    display: none;
    gap: 12px;
  }

  /* Desain Kartu Dokumen (Konsisten di ponsel 1 layar & ponsel lipat 2 layar) */
  .mob-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 16px 18px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06), 0 4px 12px rgba(0, 0, 0, 0.03);
    cursor: pointer;
    transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    display: flex;
    flex-direction: column;
    text-align: left;
    box-sizing: border-box;
  }
  .mob-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 18px rgba(0, 0, 0, 0.09);
    border-color: #cbd5e1;
  }
  .dark .mob-card {
    background: #1e293b;
    border-color: #334155;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
  }
  .dark .mob-card:hover {
    border-color: #475569;
  }

  .mob-card__judul {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: baseline;
    gap: 8px;
    margin-bottom: 8px;
  }
  .mob-card__agenda {
    font-size: 15px;
    font-weight: 700;
    color: #083E40;
    letter-spacing: -0.01em;
  }
  .dark .mob-card__agenda {
    color: #38bdf8;
  }
  .mob-card__spp {
    font-size: 12px;
    font-weight: 500;
    color: #64748b;
    white-space: normal;
    overflow-wrap: anywhere;
    min-width: 0;
  }
  .dark .mob-card__spp {
    color: #94a3b8;
  }

  .mob-card__penerima {
    font-size: 14px;
    font-weight: 500;
    color: #334155;
    margin-bottom: 6px;
    line-height: 1.4;
  }
  .dark .mob-card__penerima {
    color: #cbd5e1;
  }

  .mob-card__nilai {
    font-family: 'Sora', 'Plus Jakarta Sans', sans-serif;
    font-size: 16.5px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 10px;
  }
  .dark .mob-card__nilai {
    color: #f8fafc;
  }

  .mob-card__status,
  .mob-card__pengembalian {
    margin-bottom: 10px;
  }

  .mob-card__kaki {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    color: #64748b;
    border-top: 1px solid #f1f5f9;
    padding-top: 10px;
    margin-top: auto;
  }
  .dark .mob-card__kaki {
    border-top-color: #334155;
    color: #94a3b8;
  }

  .mob-card__detail {
    color: #083E40;
    font-weight: 600;
    font-size: 12.5px;
  }
  .dark .mob-card__detail {
    color: #38bdf8;
  }

  /* Grid layout untuk kartu di layar lipat/tablet/desktop */
  #bagianDaftarTable.mode-cards-active .mob-cards {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)) !important;
    gap: 14px !important;
    margin-bottom: 16px;
  }
  #bagianDaftarTable.mode-cards-active .table-wrapper {
    display: none !important;
  }
  #bagianDaftarTable.mode-table-active .mob-cards {
    display: none !important;
  }
  #bagianDaftarTable.mode-table-active .table-wrapper {
    display: block !important;
  }
</style>
@endpush

<div class="mob-cards">
  @foreach($dokumens as $doc)
    @php
      $status = \App\Support\StatusPembayaranBagian::untuk($doc);
      $jalan = $perjalanan[$doc->id] ?? null;
      $dikembalikan = strtolower($doc->status ?? '') === 'returned_to_bidang';
    @endphp

    <div class="mob-card"
      @if($jalan)
        data-perjalanan="{{ json_encode($jalan) }}"
        {{-- Uraian SPP sengaja TIDAK dirender di badan kartu (terlalu panjang,
             akan mendominasi); dibawa ke modal lewat atribut ini. --}}
        data-uraian="{{ $doc->uraian_spp }}"
        onclick="tampilkanPerjalanan(this)"
        role="button"
        tabindex="0"
        title="Ketuk untuk melihat perjalanan dokumen"
      @endif
    >
      <div class="mob-card__judul">
        <strong class="mob-card__agenda">{{ $doc->nomor_agenda }}</strong>
        @if($doc->nomor_spp)
          <span class="mob-card__spp">{{ $doc->nomor_spp }}</span>
        @endif
      </div>

      <div class="mob-card__penerima">
        {{ $doc->dibayar_kepada ?: ($doc->dibayarKepadas->pluck('nama_penerima')->join(', ') ?: '-') }}
      </div>

      <div class="mob-card__nilai">
        Rp {{ number_format($doc->nilai_rupiah ?? 0, 0, ',', '.') }}
      </div>

      <div class="mob-card__status">
        <span class="payment-status-badge {{ $status['kelas'] }}">
          <i class="fa-solid {{ $status['ikon'] }}"></i>
          {{ $status['teks'] }}
        </span>
      </div>

      @if($dikembalikan)
        {{-- stopPropagation: tanpa ini ketukan badge ikut membuka modal
             perjalanan milik kartu induknya. --}}
        <div class="mob-card__pengembalian">
          <span class="badge-status badge-dikembalikan"
            onclick="event.stopPropagation(); showRejectionModal({{ $doc->id }})">
            <i class="fa-solid fa-undo"></i>
            Dikembalikan, <span style="text-decoration: underline; font-weight: 700;">Alasan</span>
          </span>
        </div>
      @endif

      <div class="mob-card__kaki">
        <span>
          <i class="fa-solid fa-calendar-day"></i>
          {{ $doc->tanggal_masuk ? $doc->tanggal_masuk->format('d M Y') : '-' }}
        </span>
        @if($jalan)
          <span class="mob-card__detail">Detail &rsaquo;</span>
        @endif
      </div>
    </div>
  @endforeach
</div>

@push('scripts')
<script>
  // Aksesibilitas keyboard untuk kartu ber-role="button": <div role="button"
  // tabindex="0"> TIDAK dapat aktivasi Enter/Space gratis seperti <button> asli,
  // jadi harus ditangani manual di sini — tanpa ini kartu bisa difokus dengan
  // Tab (janji role="button") tapi menekan Enter/Space tak melakukan apa pun.
  //
  // Event delegation: SATU listener untuk ratusan kartu, bukan satu per kartu.
  //
  // Listener dipasang di DOCUMENT, bukan di .mob-cards. Dua jebakan nyata
  // (keduanya terbukti di produksi 2026-08-13, keduanya GAGAL SENYAP tanpa
  // satu pun error di konsol) memaksa pola ini:
  //
  //   1. Dibungkus DOMContentLoaded -> callback tak pernah jalan bila skrip
  //      dieksekusi setelah event itu menyala.
  //   2. Menyasar .mob-cards langsung -> "if (!pembungkus) return" menendang
  //      keluar diam-diam bila skrip dieksekusi SEBELUM markup kartu ada.
  //      Posisi render stack scripts relatif terhadap markup TIDAK dijamin,
  //      dan di halaman ini nyatanya skrip menang duluan (dibuktikan dengan
  //      menjalankan ulang isi skrip yang sama di konsol: listener langsung
  //      terpasang dan Enter berfungsi).
  //
  // Menyasar document menghilangkan kedua ketergantungan itu: document selalu
  // ada, dan closest() di dalam handler yang menentukan apakah event berasal
  // dari kartu.
  (function () {
    document.addEventListener('keydown', function (event) {
      if (event.key !== 'Enter' && event.key !== ' ') return;

      // Hanya kartu yang memang punya data-perjalanan yang ber-role="button"
      // (lihat kondisi $jalan di markup) — kartu lain tak boleh bereaksi.
      const kartu = event.target.closest('.mob-card[data-perjalanan]');
      if (!kartu) return;

      if (event.key === ' ') {
        event.preventDefault(); // cegah halaman ikut menggulir
      }

      tampilkanPerjalanan(kartu);
    });
  })();
</script>
@endpush
