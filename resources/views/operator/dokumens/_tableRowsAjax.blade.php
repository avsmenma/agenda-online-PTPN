{{--
  AJAX Table Rows Partial — renders only <tr> elements.
  Uses eager-loaded collections ($dokumen->roleStatuses) to avoid N+1 queries.
  Variables expected: $dokumens, $selectedColumns, $availableColumns
--}}
@foreach($dokumens as $index => $dokumen)
  @php
    // === Use eager-loaded collection (no extra DB queries) ===
    $allStatuses = $dokumen->roleStatuses;

    $tvStatus    = $allStatuses->where('role_code', 'team_verifikasi')->sortByDesc('status_changed_at')->first();
    $tvRejected  = $tvStatus && strtolower($tvStatus->status ?? '') === 'rejected';
    $tvPending   = $tvStatus && strtolower($tvStatus->status ?? '') === 'pending';
    $tvApproved  = $tvStatus && strtolower($tvStatus->status ?? '') === 'approved';

    $isRejectedForCheck = $tvRejected;
    if (!$isRejectedForCheck && strtolower($dokumen->status ?? '') === 'returned_to_operator') {
        $isRejectedForCheck = $allStatuses->where('status', 'rejected')->isNotEmpty();
    }

    $currentHandlerOperatorForCheck = in_array(strtolower($dokumen->current_handler ?? ''), ['operator']);
    $statusLowerForCheck            = strtolower($dokumen->status ?? '');

    $isSentToTeamVerifikasi = $statusLowerForCheck === 'sent_to_team_verifikasi'
        || ($dokumen->current_handler === 'team_verifikasi' && $statusLowerForCheck !== 'returned_to_operator');

    $hasOtherRoles = $allStatuses->whereIn('role_code', ['perpajakan','akutansi','pembayaran'])->isNotEmpty();
    $isSentToOther = in_array($statusLowerForCheck, ['sent_to_perpajakan','sent_to_akutansi','sent_to_pembayaran',
        'pending_approval_perpajakan','pending_approval_akutansi','pending_approval_pembayaran']);

    $isSentForCheck = ($isSentToTeamVerifikasi || ($tvApproved && $isSentToOther)) && !$isRejectedForCheck;

    $createdByOperatorForCheck = in_array(strtolower($dokumen->created_by ?? ''), ['operator']);
    $isReturnedForCheck        = $statusLowerForCheck === 'returned_to_operator';
    $isFromBagianForCheck      = $statusLowerForCheck === 'menunggu_approval_keuangan' && $currentHandlerOperatorForCheck;

    $canSendForBulk = false;
    if ($isFromBagianForCheck && !$isSentForCheck) {
        $canSendForBulk = true;
    } elseif ($isRejectedForCheck && $currentHandlerOperatorForCheck && !$isSentForCheck) {
        $canSendForBulk = true;
    } elseif ($isReturnedForCheck && $currentHandlerOperatorForCheck && !$isSentForCheck) {
        $canSendForBulk = true;
    } elseif (in_array($statusLowerForCheck, ['draft','sedang diproses']) && $currentHandlerOperatorForCheck && !$isSentForCheck) {
        $canSendForBulk = true;
    }

    $canInlineEdit = $currentHandlerOperatorForCheck
        && in_array($statusLowerForCheck, ['draft','returned_to_operator','belum_dikirim','belum dikirim','menunggu_approval_keuangan'])
        || ($isRejectedForCheck && $currentHandlerOperatorForCheck);

    $filteredColumns = array_filter($selectedColumns, fn($c) => $c !== 'nomor_mirror' && $c !== 'keterangan' && isset($availableColumns[$c]));
    $dateCols        = ['tanggal_spp','tanggal_berita_acara','tanggal_spk','tanggal_berakhir_spk','tanggal_faktur','tanggal_paraf','tanggal_miro'];
  @endphp

  <tr class="main-row clickable-row"
    data-id="{{ $dokumen->id }}"
    data-nomor-agenda="{{ $dokumen->nomor_agenda }}"
    data-nomor-spp="{{ $dokumen->nomor_spp }}"
    data-nilai-rupiah="{{ $dokumen->formatted_nilai_rupiah }}"
    data-can-send="{{ $canSendForBulk ? 'true' : 'false' }}"
    data-editable="{{ $canInlineEdit ? 'true' : 'false' }}"
    data-dokumen-id="{{ $dokumen->id }}"
    ondblclick="handleRowClick(event, {{ $dokumen->id }})" title="Double klik untuk melihat detail">

    <td class="col-checkbox" onclick="event.stopPropagation()">
      @if($canSendForBulk)
        <input type="checkbox" class="bulk-checkbox doc-checkbox"
          data-id="{{ $dokumen->id }}"
          data-nomor-agenda="{{ $dokumen->nomor_agenda }}"
          data-nomor-spp="{{ $dokumen->nomor_spp }}"
          data-nilai-rupiah="{{ $dokumen->formatted_nilai_rupiah }}"
          title="Pilih dokumen ini untuk bulk send">
      @else
        <input type="checkbox" class="bulk-checkbox" disabled title="Dokumen ini tidak dapat dikirim">
      @endif
    </td>

    <td class="col-no">{{ $dokumens->firstItem() + $index }}</td>

    @foreach($filteredColumns as $col)
      @php
        $nonEditableCols  = ['tanggal_masuk','status','nomor_mirror','keterangan'];
        $isCellEditable   = $canInlineEdit && !in_array($col, $nonEditableCols);
        if ($isCellEditable) {
            if ($col === 'nilai_rupiah') {
                $ieRaw = $dokumen->nilai_rupiah ?? '';
            } elseif (in_array($col, $dateCols)) {
                $ieRaw = $dokumen->$col ? $dokumen->$col->format('Y-m-d') : '';
            } elseif ($col === 'dibayar_kepada') {
                $ieRaw = $dokumen->dibayarKepadas->pluck('nama_penerima')->implode("\n");
            } else {
                $ieRaw = $dokumen->$col ?? '';
            }
        }
      @endphp
      <td class="col-{{ $col }}{{ $isCellEditable ? ' ie-cell' : '' }}"
        @if($isCellEditable) data-field="{{ $col }}" data-raw="{{ $ieRaw }}" @endif>

        @if($col === 'nomor_agenda')
          <strong>{{ $dokumen->nomor_agenda }}</strong><br>
          <small class="text-muted">{{ $dokumen->bulan }} {{ $dokumen->tahun }}</small>
        @elseif($col === 'nomor_spp')
          <span class="select-text">{{ $dokumen->nomor_spp }}</span>
        @elseif($col === 'tanggal_masuk')
          <span class="select-text">{{ $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d-m-Y H:i') : '-' }}</span>
        @elseif($col === 'nilai_rupiah')
          <strong class="select-text">{{ $dokumen->formatted_nilai_rupiah }}</strong>
        @elseif($col === 'status')
          @php
            // Status badge using collection (no extra DB queries)
            if ($statusLowerForCheck === 'returned_to_operator') {
                $OperatorDisplayStatus = 'dikembalikan';
            } elseif ($tvRejected) {
                $OperatorDisplayStatus = 'ditolak_verifikasi';
            } elseif ($tvPending) {
                $OperatorDisplayStatus = 'menunggu_approval_verifikasi';
            } elseif ($tvApproved || $hasOtherRoles) {
                $OperatorDisplayStatus = 'terkirim';
            } elseif (in_array($statusLowerForCheck, ['menunggu_approval_keuangan']) && $currentHandlerOperatorForCheck) {
                $OperatorDisplayStatus = 'draft';
            } elseif ($currentHandlerOperatorForCheck && in_array($statusLowerForCheck, ['draft','returned_to_operator'])) {
                $OperatorDisplayStatus = 'draft';
            } else {
                $OperatorDisplayStatus = in_array(strtolower($dokumen->current_handler ?? ''), ['team_verifikasi','verifikasi','perpajakan','akutansi','pembayaran']) ? 'terkirim' : 'draft';
            }
            $statusLabel = match($OperatorDisplayStatus) {
                'draft' => 'Belum Dikirim',
                'menunggu_approval_verifikasi' => 'Menunggu Approve Team Verifikasi',
                'ditolak_verifikasi' => 'Dokumen Ditolak oleh Team Verifikasi',
                'dikembalikan' => 'Dikembalikan',
                default => 'Terkirim',
            };
          @endphp
          @if($OperatorDisplayStatus === 'draft')
            <span class="badge-status badge-belum-dikirim">
              <i class="fa-solid fa-file-pen me-1"></i><span>Belum Dikirim</span>
            </span>
          @elseif(in_array($OperatorDisplayStatus, ['ditolak_verifikasi', 'dikembalikan']))
            <span class="badge-status badge-ditolak" style="background:linear-gradient(135deg,#dc3545,#b02a37);color:white;">
              <i class="fa-solid fa-rotate-left me-1"></i><span>{{ $OperatorDisplayStatus === 'dikembalikan' ? 'Dikembalikan' : 'Dokumen Ditolak' }}</span>
            </span>
          @elseif($OperatorDisplayStatus === 'menunggu_approval_verifikasi')
            <span class="badge-status" style="background:linear-gradient(135deg,#ffc107,#ff8c00);color:white;">
              <i class="fa-solid fa-clock me-1"></i><span>Menunggu Approve Team Verifikasi</span>
            </span>
          @else
            <span class="badge-status badge-terkirim">
              <i class="fa-solid fa-check me-1"></i><span>{{ $statusLabel }}</span>
            </span>
          @endif
        @elseif($col === 'tanggal_spp')
          {{ $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d-m-Y') : '-' }}
        @elseif($col === 'uraian_spp')
          <span title="{{ $dokumen->uraian_spp ?? '-' }}" style="display:block;word-wrap:break-word;white-space:normal;overflow-wrap:break-word;line-height:1.5;width:100%;">{{ $dokumen->uraian_spp ?? '-' }}</span>
        @elseif($col === 'kategori')
          {{ $dokumen->kategori ?? '-' }}
        @elseif($col === 'kebun')
          {{ $dokumen->kebun ?? '-' }}
        @elseif($col === 'bulan')
          {{ $dokumen->bulan ?? '-' }}
        @elseif($col === 'tahun')
          {{ $dokumen->tahun ?? '-' }}
        @elseif($col === 'nomor_miro')
          {{ $dokumen->nomor_miro_display ?? $dokumen->nomor_miro ?? '-' }}
        @elseif($col === 'jenis_dokumen')
          {{ $dokumen->jenis_dokumen ?? '-' }}
        @elseif($col === 'jenis_sub_pekerjaan')
          {{ $dokumen->jenis_sub_pekerjaan ?? '-' }}
        @elseif($col === 'jenis_pembayaran')
          {{ $dokumen->jenis_pembayaran ?? '-' }}
        @elseif($col === 'nama_pengirim')
          {{ $dokumen->nama_pengirim ?? '-' }}
        @elseif($col === 'dibayar_kepada')
          @if($dokumen->dibayarKepadas->count() > 0)
            {{ $dokumen->dibayarKepadas->pluck('nama_penerima')->join(', ') }}
          @else
            {{ $dokumen->dibayar_kepada ?? '-' }}
          @endif
        @elseif($col === 'no_berita_acara')
          {{ $dokumen->no_berita_acara ?? '-' }}
        @elseif($col === 'tanggal_berita_acara')
          {{ $dokumen->tanggal_berita_acara ? $dokumen->tanggal_berita_acara->format('d-m-Y') : '-' }}
        @elseif($col === 'no_spk')
          {{ $dokumen->no_spk ?? '-' }}
        @elseif($col === 'tanggal_spk')
          {{ $dokumen->tanggal_spk ? $dokumen->tanggal_spk->format('d-m-Y') : '-' }}
        @elseif($col === 'tanggal_berakhir_spk')
          {{ $dokumen->tanggal_berakhir_spk ? $dokumen->tanggal_berakhir_spk->format('d-m-Y') : '-' }}
        @elseif($col === 'bagian')
          {{ $dokumen->bagian ?? '-' }}
        @elseif($col === 'tanggal_paraf')
          {{ $dokumen->tanggal_paraf ? $dokumen->tanggal_paraf->format('d/m/Y H:i') : '-' }}
        @elseif($col === 'pemaraf')
          @if($dokumen->pemaraf)
            <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;background:linear-gradient(135deg,#22c55e,#16a34a);color:white;border-radius:6px;font-size:11px;font-weight:600;">
              <i class="fa-solid fa-check-circle"></i> {{ $dokumen->pemaraf }}
            </span>
          @else -
          @endif
        @elseif($col === 'no_faktur')
          {{ $dokumen->no_faktur ?? '-' }}
        @elseif($col === 'tanggal_faktur')
          {{ $dokumen->tanggal_faktur ? $dokumen->tanggal_faktur->format('d/m/Y') : '-' }}
        @else
          -
        @endif
      </td>
    @endforeach

    <td class="col-handler" onclick="event.stopPropagation()">
      @include('partials.document-handler-select', ['dokumen' => $dokumen])
    </td>

    <td class="col-action" onclick="event.stopPropagation()">
      <div class="action-buttons">
        @php
          $isFromBagian        = $currentHandlerOperatorForCheck && !$createdByOperatorForCheck;
          $isSent              = ($isSentToTeamVerifikasi || ($tvApproved && $isSentToOther)) && !$isRejectedForCheck;
          if ($isFromBagian) { $isSent = false; }
          $canEdit = false;
          $canSend = false;
          if ($isFromBagian && !$isSent)                                              { $canEdit = true; $canSend = true; }
          elseif ($isRejectedForCheck && $currentHandlerOperatorForCheck && !$isSent) { $canEdit = true; $canSend = true; }
          elseif ($isReturnedForCheck && $currentHandlerOperatorForCheck && !$isSent) { $canEdit = true; $canSend = true; }
          elseif ($statusLowerForCheck === 'draft' && $currentHandlerOperatorForCheck && !$isSent) { $canEdit = true; $canSend = true; }
        @endphp
        @if($canEdit)
          <a href="{{ route('documents.edit', $dokumen->id) }}" class="btn-action btn-edit" title="Edit Dokumen">
            <i class="fa-solid fa-edit"></i><span>Edit</span>
          </a>
        @endif
        @if($canSend)
          <form action="{{ route('documents.send-to-verifikasi', $dokumen->id) }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="btn-action btn-send" title="Kirim ke Team Verifikasi">
              <i class="fa-solid fa-paper-plane"></i><span>Kirim</span>
            </button>
          </form>
        @elseif($isSent)
          <button class="btn-action btn-send" disabled title="Dokumen sudah dikirim">
            <i class="fa-solid fa-paper-plane"></i><span>Kirim</span>
          </button>
        @endif
      </div>
    </td>
  </tr>

  <tr class="detail-row" id="detail-{{ $dokumen->id }}" style="display:none;">
    @php
      $fcAjax = array_filter($selectedColumns, fn($c) => $c !== 'nomor_mirror' && $c !== 'keterangan' && isset($availableColumns[$c]));
    @endphp
    <td colspan="{{ count($fcAjax) + 3 }}">
      <div class="detail-content" id="detail-content-{{ $dokumen->id }}">
        <div class="loading-spinner"><i class="fa-solid fa-spinner fa-spin"></i> <span>Memuat detail dokumen...</span></div>
      </div>
    </td>
  </tr>
@endforeach
