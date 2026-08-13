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
  /* Disembunyikan secara default (desktop). Ditampilkan kembali di
     public/css/mobile.css di dalam @media (max-width: 768px).
     WAJIB lewat stack styles (lihat direktif di atas blok ini) — bukan
     <style> inline di body — supaya ter-parse SEBELUM markup dan kartu tak
     berkedip muncul di desktop.

     DUA hal yang HARAM ditulis di komentar ini (keduanya sudah pernah lolos
     ke produksi 2026-08-13 dan keduanya gagal SENYAP):
     1. Nama direktif push Blade secara literal — Blade memprosesnya sebagai
        direktif sungguhan meski di dalam komentar CSS, membuka stack baru di
        tengah blok, sehingga tag penutup style jatuh ke push yang keliru dan
        SELURUH markup kartu tertelan ke dalam blok style: halaman ponsel
        tampil KOSONG.
     2. Tag penutup style secara literal — parser HTML mengakhiri blok style
        di situ juga, sehingga aturan di bawah komentar ini tak pernah aktif
        dan kartu ikut tampil di DESKTOP (konten dobel dengan tabel). */
  .mob-cards { display: none; }
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
