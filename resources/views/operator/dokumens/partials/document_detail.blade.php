<div class="detail-grid">
  <div class="detail-item">
    <span class="detail-label">Tanggal Masuk</span>
    <span class="detail-value">{{ $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d/m/Y H:i:s') : '-' }}</span>
  </div>
  <div class="detail-item">
    <span class="detail-label">Bulan</span>
    <span class="detail-value">{{ $dokumen->bulan }}</span>
  </div>
  <div class="detail-item">
    <span class="detail-label">Tahun</span>
    <span class="detail-value">{{ $dokumen->tahun }}</span>
  </div>
  <div class="detail-item">
    <span class="detail-label">No SPP</span>
    <span class="detail-value">{{ $dokumen->nomor_spp }}</span>
  </div>
  <div class="detail-item">
    <span class="detail-label">Tanggal SPP</span>
    <span class="detail-value">{{ $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y') : '-' }}</span>
  </div>
  <div class="detail-item">
    <span class="detail-label">Uraian SPP</span>
    <span class="detail-value">{{ $dokumen->uraian_spp }}</span>
  </div>
  <div class="detail-item">
    <span class="detail-label">Nilai Rupiah</span>
    <span class="detail-value">{{ $dokumen->formatted_nilai_rupiah }}</span>
  </div>
  <div class="detail-item">
    <span class="detail-label">Kriteria CF</span>
    <span class="detail-value">{{ $dokumen->kategori ?? '-' }}</span>
  </div>
  <div class="detail-item">
    <span class="detail-label">Sub Kriteria</span>
    <span class="detail-value">{{ $dokumen->jenis_dokumen ?? '-' }}</span>
  </div>
  <div class="detail-item">
    <span class="detail-label">Item Sub Kriteria</span>
    <span class="detail-value">{{ $dokumen->jenis_sub_pekerjaan ?? '-' }}</span>
  </div>
  <div class="detail-item">
    <span class="detail-label">Jenis Pembayaran</span>
    <span class="detail-value">{{ $dokumen->jenis_pembayaran ?? '-' }}</span>
  </div>
  <div class="detail-item">
    <span class="detail-label">Kebun</span>
    <span class="detail-value">{{ $dokumen->kebun ?? '-' }}</span>
  </div>
  <div class="detail-item">
    <span class="detail-label">Dibayar Kepada</span>
    <span class="detail-value">
      @if($dokumen->dibayarKepadas->count() > 0)
        {{ $dokumen->dibayarKepadas->pluck('nama_penerima')->join(', ') }}
      @else
        -
      @endif
    </span>
  </div>
  <div class="detail-item">
    <span class="detail-label">No Berita Acara</span>
    <span class="detail-value">{{ $dokumen->no_berita_acara ?? '-' }}</span>
  </div>
  <div class="detail-item">
    <span class="detail-label">Tanggal Berita Acara</span>
    <span class="detail-value">{{ $dokumen->tanggal_berita_acara ? $dokumen->tanggal_berita_acara->format('d/m/Y') : '-' }}</span>
  </div>
  <div class="detail-item">
    <span class="detail-label">No SPK</span>
    <span class="detail-value">{{ $dokumen->no_spk ?? '-' }}</span>
  </div>
  <div class="detail-item">
    <span class="detail-label">Tanggal SPK</span>
    <span class="detail-value">{{ $dokumen->tanggal_spk ? $dokumen->tanggal_spk->format('d/m/Y') : '-' }}</span>
  </div>
  <div class="detail-item">
    <span class="detail-label">Tanggal Akhir SPK</span>
    <span class="detail-value">{{ $dokumen->tanggal_berakhir_spk ? $dokumen->tanggal_berakhir_spk->format('d/d/Y') : '-' }}</span>
  </div>
  @if($dokumen->alasan_pengembalian)
  <div class="detail-item">
    <span class="detail-label">Alasan Pengembalian</span>
    <span class="detail-value text-danger">{{ $dokumen->alasan_pengembalian }}</span>
  </div>
  @endif
  <div class="detail-item">
    <span class="detail-label">No PO</span>
    <span class="detail-value">
      @if($dokumen->dokumenPos->count() > 0)
        {{ $dokumen->dokumenPos->pluck('nomor_po')->join(', ') }}
      @else
        -
      @endif
    </span>
  </div>
  <div class="detail-item">
    <span class="detail-label">No PR</span>
    <span class="detail-value">
      @if($dokumen->dokumenPrs->count() > 0)
        {{ $dokumen->dokumenPrs->pluck('nomor_pr')->join(', ') }}
      @else
        -
      @endif
    </span>
  </div>
  <div class="detail-item">
    <span class="detail-label">Nomor Miro</span>
    <span class="detail-value">{{ $dokumen->nomor_miro ?? '-' }}</span>
  </div>
  <div class="detail-item">
    <span class="detail-label">Status</span>
    <span class="detail-value">
      @if(in_array($dokumen->status, ['draft', 'returned_to_Operator']))
        <span class="badge badge-status badge-yellow">⏳ Belum Dikirim</span>
      @else
        <span class="badge badge-status badge-green">✓ Sudah Dikirim ke Ibu Yuni</span>
      @endif
    </span>
  </div>
  @if($dokumen->created_at)
  <div class="detail-item">
    <span class="detail-label">DOperatort</span>
    <span class="detail-value">{{ $dokumen->created_at->format('d M Y H:i') }}</span>
  </div>
  @endif
  @if($dokumen->sent_to_team_verifikasi_at)
  <div class="detail-item">
    <span class="detail-label">Dikirim ke Ibu Yuni</span>
    <span class="detail-value">{{ $dokumen->sent_to_team_verifikasi_at->format('d M Y H:i') }}</span>
  </div>
  @endif
  @if($dokumen->processed_at)
  <div class="detail-item">
    <span class="detail-label">Diproses</span>
    <span class="detail-value">{{ $dokumen->processed_at ? $dokumen->processed_at->format('d M Y H:i') : '-' }}</span>
  </div>
  @endif
  @if($dokumen->returned_to_Operator_at)
  <div class="detail-item">
    <span class="detail-label">Dikembalikan</span>
    <span class="detail-value">{{ $dokumen->returned_to_Operator_at ? $dokumen->returned_to_Operator_at->format('d M Y H:i') : '-' }}</span>
  </div>
  @endif
  @if($dokumen->keterangan)
  <div class="detail-item">
    <span class="detail-label">Keterangan</span>
    <span class="detail-value">{{ $dokumen->keterangan }}</span>
  </div>
  @endif
</div>



