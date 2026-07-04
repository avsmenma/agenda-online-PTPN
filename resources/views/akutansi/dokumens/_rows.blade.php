@php $showActionColumn = $showActionColumn ?? false; @endphp
          @forelse($dokumens as $index => $dokumen)
            @php
              $canInlineEdit = $dokumen->current_handler === 'akutansi';
              $dateCols = ['tanggal_spp','tanggal_berita_acara','tanggal_spk','tanggal_berakhir_spk','tanggal_faktur','tanggal_paraf','tanggal_miro','tanggal_selesai_verifikasi_pajak'];
            @endphp
            <tr class="main-row clickable-row {{ $dokumen->lock_status_class }}"
              data-editable="{{ $canInlineEdit ? 'true' : 'false' }}"
              data-dokumen-id="{{ $dokumen->id }}"
              data-kategori="{{ $dokumen->kategori ?? '' }}"
              data-jenis-dokumen="{{ $dokumen->jenis_dokumen ?? '' }}"
              data-jenis-sub-pekerjaan="{{ $dokumen->jenis_sub_pekerjaan ?? '' }}"
              ondblclick="handleRowClick(event, {{ $dokumen->id }})" title="Double klik untuk melihat detail">
              <td class="col-number">
                @php
                  // Jangan tampilkan icon kunci untuk dokumen yang sudah dikirim ke pembayaran
                  $isSentToPembayaran = in_array($dokumen->status, [
                    'sent_to_pembayaran',
                    'pending_approval_pembayaran',
                    'menunggu_di_approve',
                    'completed',
                    'selesai',
                  ]) || $dokumen->status_pembayaran === 'sudah_dibayar';
                  $shouldShowLock = $dokumen->is_locked && !$isSentToPembayaran;
                @endphp
                @if($shouldShowLock)
                  <i class="fa-solid fa-lock text-warning me-1" style="font-size: 0.8em;"
                    title="Terkunci: {{ $dokumen->lock_status_message }}"></i>
                @endif
                {{ $dokumens->firstItem() + $index }}
              </td>
              @foreach($selectedColumns as $col)
                @if($col !== 'status')
                  @php
                    $nonEditableCols = ['tanggal_masuk','status','nomor_mirror','keterangan'];
                    $isCellEditable  = $canInlineEdit && !in_array($col, $nonEditableCols);
                    if ($isCellEditable) {
                      if (in_array($col, ['nilai_rupiah','dpp_pph','ppn_terhutang'])) { $ieRaw = $dokumen->$col ?? ''; }
                      elseif (in_array($col, $dateCols)) { $ieRaw = $dokumen->$col ? $dokumen->$col->format('Y-m-d') : ''; }
                      elseif ($col === 'dibayar_kepada') { $ieRaw = $dokumen->dibayarKepadas->pluck('nama_penerima')->implode("\n"); }
                      else { $ieRaw = $dokumen->$col ?? ''; }
                    }
                  @endphp
                  <td class="col-{{ $col }}{{ $isCellEditable ? ' ie-cell' : '' }}"
                      @if($isCellEditable) data-field="{{ $col }}" data-raw="{{ $ieRaw }}" @endif>
                    @if($col == 'nomor_agenda')
                      <strong>{{ $dokumen->nomor_agenda }}</strong>
                      <br>
                      <small class="text-muted">{{ $dokumen->bulan }} {{ $dokumen->tahun }}</small>
                    @elseif($col == 'nomor_spp')
                      <span class="select-text">{{ $dokumen->nomor_spp }}</span>
                    @elseif($col == 'tanggal_masuk')
                      <span
                        class="select-text">{{ $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d/m/Y H:i') : '-' }}</span>
                    @elseif($col == 'nilai_rupiah')
                      <strong
                        class="select-text">{{ $dokumen->formatted_nilai_rupiah ?? 'Rp. ' . number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.') }}</strong>
                    @elseif($col == 'nomor_miro')
                      {{ $dokumen->nomor_miro ?? '-' }}
                    @elseif($col == 'tanggal_spp')
                      {{ $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y') : '-' }}
                    @elseif($col == 'uraian_spp')
                      <span title="{{ $dokumen->uraian_spp ?? '-' }}"
                        style="display: block; word-wrap: break-word; white-space: normal; overflow-wrap: break-word; line-height: 1.6; width: 100%;">
                        {{ $dokumen->uraian_spp ?? '-' }}
                      </span>
                    @elseif($col == 'kategori')
                      {{ $dokumen->kategori ?? '-' }}
                    @elseif($col == 'kebun')
                      {{ $dokumen->kebun ?? '-' }}
                    @elseif($col == 'bulan')
                      {{ $dokumen->bulan ?? '-' }}
                    @elseif($col == 'tahun')
                      {{ $dokumen->tahun ?? '-' }}
                    @elseif($col == 'jenis_dokumen')
                      {{ $dokumen->jenis_dokumen ?? '-' }}
                    @elseif($col == 'jenis_sub_pekerjaan')
                      {{ $dokumen->jenis_sub_pekerjaan ?? '-' }}
                    @elseif($col == 'jenis_pembayaran')
                      {{ $dokumen->jenis_pembayaran ?? '-' }}
                    @elseif($col == 'nama_pengirim')
                      {{ $dokumen->nama_pengirim ?? '-' }}
                    @elseif($col == 'dibayar_kepada')
                      @if($dokumen->dibayarKepadas && $dokumen->dibayarKepadas->count() > 0)
                        {{ $dokumen->dibayarKepadas->pluck('nama_penerima')->join(', ') }}
                      @else
                        {{ $dokumen->dibayar_kepada ?? '-' }}
                      @endif
                    @elseif($col == 'no_berita_acara')
                      {{ $dokumen->no_berita_acara ?? '-' }}
                    @elseif($col == 'tanggal_berita_acara')
                      {{ $dokumen->tanggal_berita_acara ? $dokumen->tanggal_berita_acara->format('d/m/Y') : '-' }}
                    @elseif($col == 'no_spk')
                      {{ $dokumen->no_spk ?? '-' }}
                    @elseif($col == 'tanggal_spk')
                      {{ $dokumen->tanggal_spk ? $dokumen->tanggal_spk->format('d/m/Y') : '-' }}
                    @elseif($col == 'tanggal_berakhir_spk')
                      {{ $dokumen->tanggal_berakhir_spk ? $dokumen->tanggal_berakhir_spk->format('d/m/Y') : '-' }}
                    @elseif($col == 'npwp')
                      {{ $dokumen->npwp ?? '-' }}
                    @elseif($col == 'no_faktur')
                      {{ $dokumen->no_faktur ?? '-' }}
                    @elseif($col == 'tanggal_faktur')
                      {{ $dokumen->tanggal_faktur ? $dokumen->tanggal_faktur->format('d/m/Y') : '-' }}
                    @elseif($col == 'tanggal_selesai_verifikasi_pajak')
                      {{ $dokumen->tanggal_selesai_verifikasi_pajak ? $dokumen->tanggal_selesai_verifikasi_pajak->format('d/m/Y') : '-' }}
                    @elseif($col == 'jenis_pph')
                      {{ $dokumen->jenis_pph ?? '-' }}
                    @elseif($col == 'dpp_pph')
                      {{ $dokumen->dpp_pph ? number_format($dokumen->dpp_pph, 0, ',', '.') : '-' }}
                    @elseif($col == 'ppn_terhutang')
                      {{ $dokumen->ppn_terhutang ? number_format($dokumen->ppn_terhutang, 0, ',', '.') : '-' }}
                    @elseif($col == 'link_dokumen_pajak')
                      @php $safeLink = \App\Support\SafeUrl::external($dokumen->link_dokumen_pajak); @endphp
                      @if($safeLink)
                        <a href="{{ $safeLink }}" target="_blank" rel="noopener noreferrer"
                          title="{{ $safeLink }}" style="color: #0d6efd; text-decoration: none;">
                          <i class="fa-solid fa-link me-1"></i>Lihat Dokumen
                        </a>
                      @else
                        -
                      @endif
                    @elseif($col == 'link')
                      @php $safeLink = \App\Support\SafeUrl::external($dokumen->link); @endphp
                      @if($safeLink)
                        <a href="{{ $safeLink }}" target="_blank" rel="noopener noreferrer"
                          onclick="event.stopPropagation();"
                          title="{{ $safeLink }}" style="color: #0d6efd; text-decoration: none;">
                          <i class="fa-solid fa-link me-1"></i>Lihat
                        </a>
                      @else
                        -
                      @endif
                    @elseif($col == 'bagian')
                      {{ $dokumen->bagian ?? '-' }}
                    @elseif($col == 'tanggal_paraf')
                      {{ $dokumen->tanggal_paraf ? $dokumen->tanggal_paraf->format('d/m/Y H:i') : '-' }}
                    @elseif($col == 'pemaraf')
                      @if($dokumen->pemaraf)
                        <span
                          style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;background:linear-gradient(135deg,#22c55e 0%,#16a34a 100%);color:white;border-radius:6px;font-size:11px;font-weight:600;white-space:nowrap;">
                          <i class="fa-solid fa-check-circle"></i>
                          {{ $dokumen->pemaraf }}
                        </span>
                      @else
                        -
                      @endif
                    @elseif($col == 'tanggal_selesai_diproses')
                      {{ $dokumen->tanggal_selesai_diproses ? $dokumen->tanggal_selesai_diproses->format('d/m/Y H:i') : '-' }}
                    @elseif($col == 'kepala_sub_bagian')
                      {{ $dokumen->kepala_sub_bagian ?? '-' }}
                    @elseif($col == 'status_dokumen_custom')
                      @if($dokumen->status_dokumen_csv)
                        <span
                          class="badge-status {{ $dokumen->status_dokumen_csv == 'Selesai Dibayar' ? 'badge-selesai' : ($dokumen->status_dokumen_csv == 'Dikembalikan' ? 'badge-dikembalikan' : 'badge-proses') }}"
                          style="font-size: 10px; padding: 4px 8px;">
                          {{ $dokumen->status_dokumen_csv }}
                        </span>
                      @else
                        -
                      @endif
                    @elseif($col == 'tanggal_dibayar')
                      {{ $dokumen->tanggal_dibayar ? \Carbon\Carbon::parse($dokumen->tanggal_dibayar)->format('d/m/Y') : '-' }}
                    @else
                      -
                    @endif
                  </td>
                @endif
              @endforeach
              <td class="col-deadline">
                @php
                  // Get received_at from roleData to calculate document age (count up)
                  $roleData = $dokumen->getDataForRole('akutansi');
                  $receivedAt = $roleData?->received_at;

                  // Get pembayaran role data for bypass detection
                  $pembayaranRoleData = $dokumen->getDataForRole('pembayaran');

                  // Check if document BYPASSED Akutansi (Bulk Direct to Payment)
                  // These documents went: Operator -> Team Verifikasi -> Pembayaran
                  // They never entered Akutansi workflow
                  $isBypassedToPaymentDeadline = (
                    $dokumen->current_handler === 'pembayaran' ||
                    $dokumen->status === 'completed' ||
                    $dokumen->status_pembayaran === 'sudah_dibayar' ||
                    ($pembayaranRoleData && $pembayaranRoleData->received_at)
                  ) && !$roleData?->received_at; // Akutansi never received this document

                  // Check if document is already sent to other roles
                  $isSent = in_array($dokumen->status, [
                    'sent_to_pembayaran',
                    'pending_approval_pembayaran',
                    'menunggu_di_approve', // Status setelah dikirim ke pembayaran via sendToInbox
                  ]);

                  // Check if document is completed
                  $isCompleted = in_array($dokumen->status, [
                    'selesai',
                    'completed',
                    'approved_data_sudah_terkirim',
                  ]) || ($dokumen->status_pembayaran === 'sudah_dibayar');

                  // Check if document is returned to verifikasi (deadline paused)
                  $isReturned = $dokumen->status === 'returned_to_verifikasi';

                  // Calculate document age from received_at (count up)
                  $ageText = '-';
                  $ageLabel = '-';
                  $ageColor = 'gray';
                  $ageIcon = 'fa-clock';
                  $ageDays = 0;
                  $timeFrozen = false;

                  if ($receivedAt) {
                    // For sent/completed documents, calculate time from received_at to processed_at (frozen time)
                    // For active documents, calculate time from received_at to now (live time)
                    $processedAt = $roleData?->processed_at;

                    if (($isSent || $isCompleted || $isReturned) && $processedAt) {
                      // Document is sent/completed/returned - freeze the time at processed_at
                      $endTime = \Carbon\Carbon::parse($processedAt);
                      $timeFrozen = true;
                    } else if ($isReturned) {
                      // Returned but no processed_at - freeze at now
                      $endTime = \Carbon\Carbon::now();
                      $timeFrozen = true;
                    } else {
                      // Document is still active - use current time
                      $endTime = \Carbon\Carbon::now();
                    }

                    $diff = $receivedAt->diff($endTime);
                    $ageDays = $diff->days;

                    // Format elapsed time as "X hari Y jam Z menit"
                    $elapsedParts = [];
                    if ($diff->days > 0) {
                      $elapsedParts[] = $diff->days . ' hari';
                    }
                    if ($diff->h > 0) {
                      $elapsedParts[] = $diff->h . ' jam';
                    }
                    if ($diff->i > 0 || empty($elapsedParts)) {
                      $elapsedParts[] = $diff->i . ' menit';
                    }
                    $ageText = implode(' ', $elapsedParts);

                    // Add frozen indicator if time is frozen
                    if ($timeFrozen) {
                      $ageText .= ' ⏸️';
                    }

                    // Determine label and color based on elapsed time (in hours)
                    // Green: < 24 hours, Yellow: 24-72 hours, Red: > 72 hours
                    $totalHours = ($diff->days * 24) + $diff->h;

                    // Calculate label based on actual processing time
                    if ($totalHours >= 72) {
                      $ageLabel = 'TERLAMBAT';
                      $ageIcon = 'fa-times-circle';
                    } elseif ($totalHours >= 24) {
                      $ageLabel = 'PERINGATAN';
                      $ageIcon = 'fa-exclamation-triangle';
                    } else {
                      $ageLabel = 'AMAN';
                      $ageIcon = 'fa-check-circle';
                    }

                    // For sent/completed/returned documents, use grey color
                    // For active documents, use color based on time
                    if ($isSent || $isCompleted || $isReturned) {
                      $ageColor = 'gray';
                    } elseif ($totalHours >= 72) {
                      $ageColor = 'red';
                    } elseif ($totalHours >= 24) {
                      $ageColor = 'yellow';
                    } else {
                      $ageColor = 'green';
                    }
                  }

                  // Determine deadline type: 'active' (masih diproses), 'sent' (sudah terkirim), 'completed' (selesai), 'paused' (dikembalikan)
                  $deadlineType = 'active';
                  if ($isReturned) {
                    $deadlineType = 'paused';
                  } elseif ($isCompleted) {
                    $deadlineType = 'completed';
                  } elseif ($isSent) {
                    $deadlineType = 'sent';
                  }
                @endphp
                @if($receivedAt)
                  <div class="deadline-card deadline-{{ $deadlineType }} deadline-{{ $ageColor }}"
                    data-received-at="{{ $receivedAt->format('Y-m-d H:i:s') }}" data-age-days="{{ $ageDays }}"
                    data-sent="{{ $isSent ? 'true' : 'false' }}" data-completed="{{ $isCompleted ? 'true' : 'false' }}">
                    <div class="deadline-time">
                      <i class="fa-solid fa-calendar"></i>
                      <span>{{ $receivedAt->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="deadline-indicator deadline-{{ $ageColor }}">
                      <i class="fa-solid {{ $ageIcon }}"></i>
                      <span class="status-text">{{ $ageLabel }}</span>
                    </div>
                    <div class="deadline-age" style="font-size: 10px; color: #6b7280; margin-top: 4px;">
                      <i class="fa-solid fa-hourglass-half"></i>
                      <span>{{ $ageText }}</span>
                    </div>
                    @if($isReturned)
                      <div class="deadline-paused-label">
                        <i class="fa-solid fa-pause-circle"></i> Berhenti Sementara
                      </div>
                    @elseif($isSent)
                      <div class="deadline-label" style="font-size: 8px; color: #6b7280; margin-top: 4px; font-weight: 600;">
                        <i class="fa-solid fa-paper-plane"></i> Terkirim
                      </div>
                    @elseif($isCompleted)
                      <div class="deadline-label" style="font-size: 8px; color: #6b7280; margin-top: 4px; font-weight: 600;">
                        <i class="fa-solid fa-check-circle"></i> Selesai
                      </div>
                    @endif
                  </div>
                @elseif($isBypassedToPaymentDeadline)
                  {{-- Document bypassed Akutansi - show proper deadline card like Team Verifikasi --}}
                  @php
                    $bypassVerifikasiData = $dokumen->getDataForRole('team_verifikasi');

                    // Priority: pembayaran received_at > verifikasi processed_at > document tanggal_masuk
                    $bypassTimestamp = $pembayaranRoleData?->received_at
                      ?? $bypassVerifikasiData?->processed_at
                      ?? $dokumen->tanggal_masuk;

                    $bypassAgeText = '0 menit';
                    $bypassAgeLabel = 'AMAN';
                    $bypassAgeColor = 'gray';
                    $bypassAgeIcon = 'fa-check-circle';
                    $bypassAgeDays = 0;

                    if ($bypassTimestamp) {
                      $bypassStartTime = $bypassTimestamp instanceof \Carbon\Carbon
                        ? $bypassTimestamp
                        : \Carbon\Carbon::parse($bypassTimestamp);

                      // ALWAYS freeze time at processed_at (like Team Verifikasi)
                      $bypassProcessedAt = $bypassVerifikasiData?->processed_at ?? $pembayaranRoleData?->received_at;
                      if ($bypassProcessedAt) {
                        $bypassEndTime = $bypassProcessedAt instanceof \Carbon\Carbon
                          ? $bypassProcessedAt
                          : \Carbon\Carbon::parse($bypassProcessedAt);
                      } else {
                        $bypassEndTime = $bypassStartTime;
                      }

                      $bypassDiff = $bypassStartTime->diff($bypassEndTime);
                      $bypassAgeDays = $bypassDiff->days;

                      $bypassElapsedParts = [];
                      if ($bypassDiff->days > 0)
                        $bypassElapsedParts[] = $bypassDiff->days . ' hari';
                      if ($bypassDiff->h > 0)
                        $bypassElapsedParts[] = $bypassDiff->h . ' jam';
                      if ($bypassDiff->i > 0 || empty($bypassElapsedParts))
                        $bypassElapsedParts[] = $bypassDiff->i . ' menit';
                      $bypassAgeText = implode(' ', $bypassElapsedParts) . ' ⏸️';

                      $bypassTotalHours = ($bypassDiff->days * 24) + $bypassDiff->h;
                      if ($bypassTotalHours >= 72) {
                        $bypassAgeLabel = 'TERLAMBAT';
                        $bypassAgeIcon = 'fa-times-circle';
                      } elseif ($bypassTotalHours >= 24) {
                        $bypassAgeLabel = 'PERINGATAN';
                        $bypassAgeIcon = 'fa-exclamation-triangle';
                      } else {
                        $bypassAgeLabel = 'AMAN';
                        $bypassAgeIcon = 'fa-check-circle';
                      }
                    }
                  @endphp
                  @if($bypassTimestamp)
                    @php
                      $bypassDisplayTime = $bypassTimestamp instanceof \Carbon\Carbon
                        ? $bypassTimestamp
                        : \Carbon\Carbon::parse($bypassTimestamp);
                    @endphp
                    <div class="deadline-card deadline-sent deadline-{{ $bypassAgeColor }}"
                      data-received-at="{{ $bypassDisplayTime->format('Y-m-d H:i:s') }}" data-age-days="{{ $bypassAgeDays }}"
                      data-sent="true" data-completed="false">
                      <div class="deadline-time">
                        <i class="fa-solid fa-calendar"></i>
                        <span>{{ $bypassDisplayTime->format('d M Y, H:i') }}</span>
                      </div>
                      <div class="deadline-indicator deadline-{{ $bypassAgeColor }}">
                        <i class="fa-solid {{ $bypassAgeIcon }}"></i>
                        <span class="status-text">{{ $bypassAgeLabel }}</span>
                      </div>
                      <div class="deadline-age" style="font-size: 10px; color: #6b7280; margin-top: 4px;">
                        <i class="fa-solid fa-hourglass-half"></i>
                        <span>{{ $bypassAgeText }}</span>
                      </div>
                      <div class="deadline-label" style="font-size: 8px; color: #6b7280; margin-top: 4px; font-weight: 600;">
                        <i class="fa-solid fa-paper-plane"></i> Terkirim
                      </div>
                    </div>
                  @else
                    <div class="deadline-card deadline-sent deadline-gray">
                      <div class="deadline-label" style="font-size: 10px; color: #6b7280; font-weight: 600;">
                        <i class="fa-solid fa-paper-plane"></i> Terkirim ke Pembayaran
                      </div>
                    </div>
                  @endif
                @else
                  <div class="no-deadline">
                    <i class="fa-solid fa-clock"></i>
                    <span>Belum diterima</span>
                  </div>
                @endif
              </td>
              <td class="col-status" style="text-align: center;" onclick="event.stopPropagation()">
                @php
                  // Check if document is rejected by akutansi or pembayaran
                  $isRejected = $dokumen->roleStatuses
                    ->whereIn('role_code', ['akutansi', 'pembayaran'])
                    ->where('status', 'rejected')
                    ->isNotEmpty();

                  // FIX: Akutansi needs to see ACTUAL workflow state (like Perpajakan)
                  // - If document is in Pembayaran inbox (pending) → "Menunggu Approval dari Pembayaran"
                  // - If document was approved by Pembayaran → "Terkirim ke Pembayaran"
                  $akutansiRoleData = $dokumen->getDataForRole('akutansi');
                  $pembayaranRoleData = $dokumen->getDataForRole('pembayaran');

                  // Check if Pembayaran has APPROVED the document (not just pending)
                  $pembayaranHasApproved = $dokumen->roleStatuses
                    ->where('role_code', 'pembayaran')
                    ->where('status', 'approved')
                    ->isNotEmpty();

                  // Check if document is PENDING in Pembayaran inbox
                  $pembayaranIsPending = $dokumen->roleStatuses
                    ->where('role_code', 'pembayaran')
                    ->where('status', 'pending')
                    ->isNotEmpty();

                  // Check if document BYPASSED Akutansi (Bulk Direct to Payment)
                  // These documents went: Operator -> Team Verifikasi -> Pembayaran
                  // They never entered Akutansi workflow
                  $isBypassedToPayment = (
                    $dokumen->current_handler === 'pembayaran' ||
                    $dokumen->status === 'completed' ||
                    $dokumen->status_pembayaran === 'sudah_dibayar' ||
                    ($pembayaranRoleData && $pembayaranRoleData->received_at)
                  ) && !$akutansiRoleData?->received_at; // Akutansi never received this document

                  // Document is truly sent from akutansi if pembayaran has APPROVED (not just pending)
                  $sentFromAkutansi = (
                    $isBypassedToPayment ||
                    $pembayaranHasApproved ||
                    ($pembayaranRoleData && $pembayaranRoleData->received_at && !$pembayaranIsPending)
                  ) && !$isRejected;
                @endphp
                @if($isRejected)
                  {{-- Dokumen ditolak oleh akutansi --}}
                  <span class="badge-status badge-dikembalikan" style="position: relative;">
                    <i class="fa-solid fa-times-circle me-1"></i>
                    <span>Dokumen ditolak,
                      <a href="{{ route('returns.akutansi.index') }}?search={{ $dokumen->nomor_agenda }}"
                        class="text-white text-decoration-underline fw-bold" onclick="event.stopPropagation();"
                        style="color: #fff !important; text-decoration: underline !important; font-weight: 600 !important;">
                        cek disini
                      </a>
                    </span>
                  </span>
                @elseif($pembayaranIsPending)
                  {{-- FIX: Document is in Pembayaran inbox waiting approval --}}
                  <span class="badge-status badge-warning">
                    <i class="fa-solid fa-clock me-1"></i>
                    Menunggu Approval dari Pembayaran
                  </span>
                @elseif($sentFromAkutansi)
                  {{-- Document has been APPROVED by Pembayaran (not just pending) --}}
                  <span class="badge-status badge-sent">📤 Terkirim ke Pembayaran</span>
                @elseif($dokumen->status == 'sent_to_pembayaran' && !$pembayaranIsPending)
                  <span class="badge-status badge-sent">📤 Terkirim ke Pembayaran</span>
                @elseif($dokumen->is_locked)
                  <span class="badge-status badge-locked">🔒 Terkunci</span>
                @elseif($dokumen->status == 'selesai')
                  <span class="badge-status badge-selesai">✓ Selesai</span>
                @elseif($dokumen->status == 'returned_to_verifikasi')
                  <span class="badge-status badge-sent">
                    <i class="fa-solid fa-paper-plane me-1"></i>
                    Kembali ke Team Verifikasi
                  </span>
                @elseif($dokumen->current_handler == 'akutansi' && !in_array($dokumen->status, ['sent_to_pembayaran', 'selesai', 'completed', 'menunggu_di_approve', 'pending_approval_pembayaran']))
                  {{-- Dokumen yang sedang ditangani akutansi dan bukan status khusus --}}
                  <span class="badge-status badge-proses">⏳ Sedang Diproses</span>
                @elseif($dokumen->status == 'sent_to_akutansi' && $dokumen->current_handler != 'akutansi')
                  {{-- Dokumen yang baru dikirim ke akutansi dan belum diproses --}}
                  <span class="badge-status badge-belum">⏳ Belum Diproses</span>
                @elseif(in_array($dokumen->status, ['returned_to_operator', 'returned_to_department', 'dikembalikan']))
                  <span class="badge-status badge-dikembalikan">← Dikembalikan</span>
                @elseif($dokumen->status == 'completed')
                  <span class="badge-status badge-selesai">✓ Selesai - Sudah Dibayar</span>
                @else
                  <span class="badge-status badge-proses">⏳ Sedang Diproses</span>
                @endif
              </td>
              <td class="col-handler" onclick="event.stopPropagation()">
                @include('partials.document-handler-select', ['dokumen' => $dokumen])
              </td>
              @if($showActionColumn)
              <td class="col-action" onclick="event.stopPropagation()">
                @if(!$dokumen->is_at_my_role)
                  {{-- Cross-role visibility: document not yet at Akutansi --}}
                  @php
                    $handlerLabel = match ($dokumen->current_handler) {
                      'operator' => 'Operator',
                      'bidang' => 'Bidang',
                      'team_verifikasi' => 'Verifikasi',
                      'perpajakan' => 'Perpajakan',
                      'akutansi' => 'Akutansi',
                      'pembayaran' => 'Pembayaran',
                      default => ucfirst(str_replace('_', ' ', $dokumen->current_handler ?? 'Unknown')),
                    };
                  @endphp
                  <div class="action-buttons-hybrid" style="display: flex; align-items: center; justify-content: center;">
                    <span class="badge-status badge-proses" style="font-size: 11px; padding: 6px 12px; white-space: nowrap;">
                      <i class="fa-solid fa-hourglass-half me-1"></i>
                      Di {{ $handlerLabel }}
                    </span>
                  </div>
                @else
                  <div class="action-buttons-hybrid">
                    @php
                      $isSentToPembayaran = in_array($dokumen->status, [
                        'sent_to_pembayaran',
                        'pending_approval_pembayaran',
                        'menunggu_di_approve', // Status setelah dikirim ke pembayaran via sendToInbox
                        'completed',
                        'selesai',
                      ]) || $dokumen->status_pembayaran === 'sudah_dibayar' || $isBypassedToPayment;
                    @endphp
                    <!-- Unlocked state - buttons enabled -->
                    @if($dokumen->status == 'returned_to_verifikasi')
                      {{-- Document has been handed back to Verifikasi - disable all actions --}}
                      <button class="btn-action btn-edit locked btn-full-width" disabled
                        title="Dokumen sudah kembali ke Verifikasi">
                        <i class="fa-solid fa-check-circle"></i>
                        <span>Kembali ke Verifikasi</span>
                      </button>
                    @elseif(!$isSentToPembayaran)
                      <!-- Tombol Kirim Data - selalu muncul untuk dokumen yang tidak terkunci dan belum terkirim -->
                      <button type="button" class="btn-action btn-send btn-full-width"
                        onclick="sendToPembayaran({{ $dokumen->id }})" title="Kirim ke Team Pembayaran">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Kirim Data</span>
                      </button>
                      <div class="action-row">
                        @if($dokumen->can_edit)
                          <a href="{{ route('documents.akutansi.edit', $dokumen->id) }}" title="Edit Dokumen"
                            style="flex: 1; text-decoration: none;">
                            <button class="btn-action btn-edit" style="width: 100%;">
                              <i class="fa-solid fa-pen"></i>
                              <span>Edit</span>
                            </button>
                          </a>
                        @endif
                        @if($dokumen->can_edit)
                          <button type="button" class="btn-action btn-kembalikan" style="flex: 1;"
                            onclick="openReturnModal({{ $dokumen->id }})" title="Kirim kembali ke Team Verifikasi">
                            <i class="fa-solid fa-paper-plane"></i>
                            <span>Balik</span>
                          </button>
                        @endif
                      </div>
                    @else
                      <!-- Dokumen sudah terkirim - tampilkan status Terkirim konsisten -->
                      <button type="button" class="btn-action btn-sent btn-full-width" disabled>
                        <i class="fa-solid fa-check-circle"></i>
                        <span>Terkirim</span>
                      </button>
                    @endif
                  </div>
                @endif
              </td>
              @endif
            </tr>
            <tr class="detail-row" id="detail-{{ $dokumen->id }}">
              <td colspan="{{ count($selectedColumns) + 4 }}">
                <div class="detail-content" id="detail-content-{{ $dokumen->id }}">
                  <div class="text-center p-4">
                    <i class="fa-solid fa-spinner fa-spin me-2"></i> Loading detail...
                  </div>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="{{ count($selectedColumns) + 4 }}" class="text-center py-5">
                <i class="fa-solid fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">Tidak ada data dokumen yang tersedia.</p>
              </td>
            </tr>
          @endforelse
