@php
$allColumns = [
    'nomor_agenda' => 'Nomor Agenda', 'bulan' => 'Bulan', 'tahun' => 'Tahun',
    'kategori' => 'Kriteria CF', 'jenis_dokumen' => 'Sub Kriteria',
    'jenis_sub_pekerjaan' => 'Item Sub Kriteria', 'jenis_pembayaran' => 'Jenis Pembayaran',
    'nomor_spp' => 'Nomor SPP', 'tanggal_spp' => 'Tanggal SPP',
    'tanggal_masuk' => 'Tanggal Masuk', 'dibayar_kepada' => 'Dibayar Kepada',
    'uraian_spp' => 'Uraian SPP', 'nilai_rupiah' => 'Nilai Rupiah',
    'tanggal_paraf' => 'Tanggal Paraf', 'pemaraf' => 'Pemaraf',
    'tanggal_selesai_diproses' => 'Tgl Selesai Diproses', 'kepala_sub_bagian' => 'Kepala Sub Bagian',
    'status_dokumen_custom' => 'Status Dokumen', 'tanggal_dibayar' => 'Tanggal Bayar',
    'bagian' => 'Bagian', 'nama_pengirim' => 'Nama Pengirim',
    'no_spk' => 'No SPK', 'tanggal_spk' => 'Tanggal SPK',
    'tanggal_berakhir_spk' => 'Tgl Berakhir SPK', 'kebun' => 'Kebun',
    'no_berita_acara' => 'No Berita Acara', 'tanggal_berita_acara' => 'Tgl Berita Acara',
    'status' => 'Status', 'no_faktur' => 'No Faktur', 'tanggal_faktur' => 'Tgl Faktur',
    'nomor_miro' => 'Nomor MIRO',
    'npwp' => 'NPWP', 'link_dokumen_pajak' => 'Link Dokumen Pajak',
];
@endphp

        <tr>
          <th>No</th>
          @foreach($allColumns as $key => $label)
            <th style="white-space: nowrap;">{{ $label }}</th>
          @endforeach
        </tr>
      </thead>
      <tbody id="dokumenTableBody">
        @forelse($dokumens as $index => $dokumen)
          <tr onclick="if(typeof openViewDocumentModal === 'function') openViewDocumentModal({{ $dokumen->id }});" style="cursor: pointer;">
            <td style="text-align: center; white-space: nowrap;">{{ ($dokumens->currentPage() - 1) * $dokumens->perPage() + $index + 1 }}</td>
            @foreach($allColumns as $col => $label)
              <td style="white-space: nowrap;">
                @if($col === 'nomor_agenda')
                  <strong>{{ $dokumen->nomor_agenda ?? '-' }}</strong>
                @elseif($col === 'bulan')
                  <span style="font-weight: 600; color: #083E40;">{{ $dokumen->bulan ?? '-' }}</span>
                @elseif($col === 'tahun')
                  <span style="font-weight: 600; color: #083E40;">{{ $dokumen->tahun ?? '-' }}</span>
                @elseif($col === 'tanggal_masuk')
                  {{ $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d/m/Y') : '-' }}
                @elseif($col === 'nilai_rupiah')
                  <strong>Rp {{ number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.') }}</strong>
                @elseif($col === 'tanggal_spp')
                  {{ $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y') : '-' }}
                @elseif($col === 'uraian_spp')
                  {{ Str::limit($dokumen->uraian_spp ?? '-', 50) }}
                @elseif($col === 'dibayar_kepada')
                  @if($dokumen->dibayarKepadas && $dokumen->dibayarKepadas->count() > 0)
                    {{ $dokumen->dibayarKepadas->pluck('nama_penerima')->join(', ') }}
                  @else
                    {{ $dokumen->dibayar_kepada ?? '-' }}
                  @endif
                @elseif($col === 'tanggal_paraf')
                  {{ $dokumen->tanggal_paraf ? $dokumen->tanggal_paraf->format('d/m/Y H:i') : '-' }}
                @elseif($col === 'pemaraf')
                  @if($dokumen->pemaraf)
                    <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;background:linear-gradient(135deg,#22c55e,#16a34a);color:white;border-radius:6px;font-size:11px;font-weight:600;"><i class="fa-solid fa-check-circle"></i> {{ $dokumen->pemaraf }}</span>
                  @else - @endif
                @elseif($col === 'status')
                  @php
                    $statusLabel = $dokumen->status_for_user ?? $dokumen->getStatusForUser('operator');
                  @endphp
                  @if($statusLabel == 'Belum Dikirim' || in_array($dokumen->status, ['draft', 'returned_to_operator']))
                    <span class="badge" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); color: white; padding: 4px 12px; border-radius: 12px;">Belum Dikirim</span>
                  @elseif($statusLabel == 'Menunggu Approval Reviewer' || $statusLabel == 'Menunggu Approval')
                    <span class="badge" style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white; padding: 4px 12px; border-radius: 12px;">{{ $dokumen->getDetailedApprovalText() }}</span>
                  @elseif($statusLabel == 'Terkirim')
                    <span class="badge" style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white; padding: 4px 12px; border-radius: 12px;">Terkirim</span>
                  @elseif($statusLabel == 'Dikembalikan untuk Revisi')
                    <span class="badge" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 4px 12px; border-radius: 12px;">Dikembalikan</span>
                  @else
                    <span class="badge" style="background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); color: white; padding: 4px 12px; border-radius: 12px;">{{ $statusLabel }}</span>
                  @endif
                @elseif(in_array($col, ['tanggal_berakhir_spk','tanggal_berita_acara','tanggal_faktur','tanggal_spk']))
                  {{ $dokumen->$col ? $dokumen->$col->format('d/m/Y') : '-' }}
                @elseif($col === 'link_dokumen_pajak')
                  @if($dokumen->link_dokumen_pajak)
                    <a href="{{ $dokumen->link_dokumen_pajak }}" target="_blank" class="text-primary"><i class="fa-solid fa-link"></i> Link</a>
                  @else - @endif
                @else
                  {{ $dokumen->$col ?? '-' }}
                @endif
              </td>
            @endforeach
          </tr>
        @empty
          <tr>
            <td colspan="{{ count($allColumns) + 1 }}" class="empty-state" style="text-align: center;">
              <i class="fa-solid fa-inbox"></i>
              <h5>Belum ada dokumen</h5>
              <p>Tidak ada dokumen untuk periode yang dipilih</p>
            </td>
          </tr>
        @endforelse
