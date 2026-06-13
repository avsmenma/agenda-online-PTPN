{{--
  Respons ringan untuk virtual scroll (per_page=all). Hanya mengirim baris tabel
  di dalam #documentTableContainer agar JS virtual-document-table dapat mengekstrak
  <tbody>, tanpa merender layout/sidebar/partial berat (anti-lag pada data besar).
--}}
<div id="documentTableContainer">
  <table class="data-table document-table">
    <tbody>
      @include('team_verifikasi.dokumens._rows')
    </tbody>
  </table>
</div>
