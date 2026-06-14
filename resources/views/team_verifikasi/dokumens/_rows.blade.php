@php $showActionColumn = $showActionColumn ?? false; @endphp
          @forelse($dokumens ?? [] as $dokumen)
            @php
              // Get deadline from roleData relationship
              $roleData = $dokumen->getDataForRole('team_verifikasi');
              $hasDeadline = false;

              if ($roleData && $roleData->deadline_at) {
                $hasDeadline = true;
              } elseif ($dokumen->deadline_at) {
                // Fallback: check alias value
                $hasDeadline = !is_null($dokumen->deadline_at);
              }

              // Check rejection status from roleStatuses
              // Dokumen ditolak jika:
              // 1. Ditolak oleh Team Verifikasi sendiri, ATAU
              // 2. Ditolak oleh perpajakan/akutansi dan dikembalikan ke verifikasi (current_handler = Team Verifikasi)
              // Gunakan koleksi roleStatuses yang sudah di-eager-load (hindari query per baris / N+1)
              $isRejectedByTeamVerifikasi = $dokumen->roleStatuses
                ->where('role_code', 'team_verifikasi')
                ->where('status', 'rejected')
                ->isNotEmpty();

              $isRejectedByOtherRole = false;
              $rejectedByRole = null;
              if ($dokumen->current_handler === 'team_verifikasi') {
                // Cek apakah ada rejection dari perpajakan atau akutansi
                $rejectedStatus = $dokumen->roleStatuses
                  ->whereIn('role_code', ['perpajakan', 'akutansi'])
                  ->where('status', 'rejected')
                  ->sortByDesc('status_changed_at')
                  ->first();

                if ($rejectedStatus) {
                  $isRejectedByOtherRole = true;
                  $rejectedByRole = $rejectedStatus->role_code;
                }
              }

              $isRejected = $isRejectedByTeamVerifikasi || $isRejectedByOtherRole;

              // Check pending status from roleStatuses
              $isPending = $dokumen->roleStatuses
                ->where('role_code', 'team_verifikasi')
                ->where('status', 'pending')
                ->isNotEmpty();

              // Document is NO LONGER locked after approval
              // Deadline is now determined by database config and calculated from received_at (count up)
              // Documents can be edited immediately after approval
              $isLocked = false;

              $isReturnedStatus = Str::startsWith($dokumen->status, 'returned_')
                || in_array($dokumen->status, ['returned_to_department']);

              if ($isReturnedStatus || $isRejected) {
                $isLocked = false;
              }

              // Check if document has been approved by Perpajakan or Akutansi
              $perpajakanStatus = $dokumen->roleStatuses->firstWhere('role_code', 'perpajakan');
              $akutansiStatus = $dokumen->roleStatuses->firstWhere('role_code', 'akutansi');
              $isApprovedByPerpajakan = $perpajakanStatus && $perpajakanStatus->status === 'approved';
              $isApprovedByAkutansi = $akutansiStatus && $akutansiStatus->status === 'approved';
              $isApprovedByOtherRole = $isApprovedByPerpajakan || $isApprovedByAkutansi;

              // NEW SIMPLIFIED APPROACH: Use display_status from dokumen_role_data
              // The display_status is set by backend (sendToRoleInbox/approveFromRoleInbox)
              // and is FINAL/frozen - it won't change when downstream roles act
              $teamVerifikasiRoleData = $roleData; // roleData is already set for 'team_verifikasi' above
              $displayStatus = $teamVerifikasiRoleData?->display_status;

              // Get human-readable label from display_status
              $displayStatusLabel = $displayStatus ? \App\Models\Dokumen::getFinalStatusLabel($displayStatus) : null;

              // FALLBACK: If display_status is not set yet, use legacy detection
              // This handles documents created before this feature was implemented
              $sentToTeamLabel = null;
              $isPendingPerpajakan = false;
              $isPendingAkuntansi = false;

              if (!$displayStatus) {
                // FIRST: Check if document is waiting for approval (bulk send)
                // These should show "Menunggu Approve" NOT "Terkirim"
                if ($dokumen->status === 'waiting_approval_perpajakan') {
                  $isPendingPerpajakan = true;
                } elseif ($dokumen->status === 'waiting_approval_akuntansi') {
                  $isPendingAkuntansi = true;
                } else {
                  // Legacy detection for backward compatibility
                  // FIX: For Verifikasi, ALWAYS show "Terkirim ke Team Perpajakan" once sent
                  // We don't care about downstream status (akutansi, pembayaran)
                  $perpajakanRoleData = $dokumen->getDataForRole('perpajakan');

                  // Check if document was ever sent to perpajakan
                  // NOTE: Do NOT include waiting_approval_* here - those are handled above
                  $wasSentToPerpajakan = (
                    ($perpajakanRoleData && $perpajakanRoleData->received_at) ||
                    in_array($dokumen->status, ['sent_to_perpajakan', 'pending_approval_perpajakan', 'sent_to_akutansi', 'sent_to_pembayaran']) ||
                    in_array($dokumen->current_handler, ['perpajakan', 'akutansi', 'pembayaran'])
                  );

                  if ($wasSentToPerpajakan && !$isRejected) {
                    // Check if perpajakan is still pending (document in perpajakan inbox)
                    $perpajakanIsPendingInbox = $dokumen->roleStatuses
                      ->where('role_code', 'perpajakan')
                      ->where('status', 'pending')
                      ->isNotEmpty();

                    if ($perpajakanIsPendingInbox) {
                      $isPendingPerpajakan = true;
                    } else {
                      // Document has passed perpajakan - show FINAL status
                      $sentToTeamLabel = 'Team Perpajakan';
                    }
                  }
                }
              }
            @endphp
            @php
              $rowClass = $isLocked ? 'locked-row' : '';
              // Editable jika dokumen sedang berada di tangan team verifikasi.
              // Gunakan is_at_my_role (dihitung controller) ATAU status yang menandakan
              // dokumen sedang diproses oleh team verifikasi — tidak bergantung pada
              // nilai current_handler saja karena bisa berbeda alias ('verifikasi', dll.)
              $editableStatuses = [
                  'sent_to_team_verifikasi',
                  'sedang diproses',
                  'sedang_diproses',
                  'returned_to_verifikasi',
                  'returned_to_department',
                  'menunggu_di_approve',
              ];
              $canInlineEdit = ($dokumen->is_at_my_role ?? false)
                  && in_array($dokumen->status, $editableStatuses);
              $dateCols = ['tanggal_spp','tanggal_berita_acara','tanggal_spk','tanggal_berakhir_spk','tanggal_faktur','tanggal_paraf','tanggal_miro','tanggal_selesai_verifikasi_pajak'];
            @endphp
            <tr class="main-row document-row {{ $rowClass }}" data-id="{{ $dokumen->id }}"
              data-editable="{{ $canInlineEdit ? 'true' : 'false' }}"
              data-dokumen-id="{{ $dokumen->id }}"
              data-kategori="{{ $dokumen->kategori ?? '' }}"
              data-jenis-dokumen="{{ $dokumen->jenis_dokumen ?? '' }}"
              data-jenis-sub-pekerjaan="{{ $dokumen->jenis_sub_pekerjaan ?? '' }}"
              ondblclick="handleRowClick(event, {{ $dokumen->id }})" title="Double klik untuk melihat detail">
              {{-- No Column --}}
              <td class="col-no" style="width: 40px;">
                {{ $loop->iteration + ($dokumens->currentPage() - 1) * $dokumens->perPage() }}
              </td>
              @foreach($selectedColumns as $col)
                @if($col !== 'status')
                  @php
                    $nonEditableCols = ['tanggal_masuk','status','nomor_mirror','keterangan','npwp','link_dokumen_pajak'];
                    $isCellEditable  = $canInlineEdit && !in_array($col, $nonEditableCols);
                    if ($isCellEditable) {
                      if (in_array($col, ['nilai_rupiah','dpp_pph','ppn_terhutang'])) { $ieRaw = $dokumen->$col ?? ''; }
                      elseif (in_array($col, $dateCols)) { $ieRaw = $dokumen->$col ? $dokumen->$col->format('Y-m-d') : ''; }
                      elseif ($col === 'dibayar_kepada') {
                        $ieRaw = $dokumen->dibayarKepadas->pluck('nama_penerima')->implode(', ');
                        // Fallback: jika relasi kosong, gunakan field langsung
                        if (empty($ieRaw) && !empty($dokumen->dibayar_kepada)) {
                          $ieRaw = $dokumen->dibayar_kepada;
                        }
                      }
                      else { $ieRaw = $dokumen->$col ?? ''; }
                    }
                  @endphp
                  <td class="col-{{ $col }}{{ $isCellEditable ? ' ie-cell' : '' }}"
                      @if($isCellEditable) data-field="{{ $col }}" data-raw="{{ $ieRaw }}" @endif>
                    @if($col == 'nomor_agenda')
                      <span class="select-text">{{ $dokumen->nomor_agenda }}</span>
                    @elseif($col == 'nomor_spp')
                      <span class="select-text">{{ $dokumen->nomor_spp }}</span>
                    @elseif($col == 'tanggal_masuk')
                      <span
                        class="select-text">{{ $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d/m/Y H:i') : '-' }}</span>
                    @elseif($col == 'nilai_rupiah')
                      <strong
                        class="select-text">{{ $dokumen->formatted_nilai_rupiah ?? 'Rp. ' . number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.') }}</strong>
                    @elseif($col == 'nomor_mirror')
                      {{ $dokumen->nomor_mirror ?? '-' }}
                    @elseif($col == 'tanggal_spp')
                      {{ $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y H:i') : '-' }}
                    @elseif($col == 'uraian_spp')
                      <span title="{{ $dokumen->uraian_spp ?? '-' }}"
                        style="display: block; word-wrap: break-word; white-space: normal; overflow-wrap: break-word; line-height: 1.5; width: 100%;">
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
                    @elseif($col == 'bagian')
                      {{ $dokumen->bagian ?? '-' }}
                    @elseif($col == 'tanggal_paraf')
                      {{ $dokumen->tanggal_paraf ? $dokumen->tanggal_paraf->format('d/m/Y H:i') : '-' }}
                    @elseif($col == 'pemaraf')
                      @if($dokumen->pemaraf)
                        <span class="badge-paraf-done">
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
                        <span class="badge-status {{ $dokumen->status_dokumen_csv == 'Selesai Dibayar' ? 'badge-selesai' : ($dokumen->status_dokumen_csv == 'Dikembalikan' ? 'badge-dikembalikan' : 'badge-proses') }}" style="font-size: 10px; padding: 4px 8px;">
                          {{ $dokumen->status_dokumen_csv }}
                        </span>
                      @else
                        -
                      @endif
                    @elseif($col == 'tanggal_dibayar')
                      {{ $dokumen->tanggal_dibayar ? \Carbon\Carbon::parse($dokumen->tanggal_dibayar)->format('d/m/Y') : '-' }}
                    @elseif($col == 'nomor_po')
                      @if($dokumen->dokumenPos && $dokumen->dokumenPos->count() > 0)
                        {{ $dokumen->dokumenPos->pluck('nomor_po')->join(', ') }}
                      @else
                        {{ $dokumen->NO_PO ?? '-' }}
                      @endif
                    @elseif($col == 'nomor_miro')
                      {{ $dokumen->nomor_miro_display ?? $dokumen->nomor_miro ?? '-' }}
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
                    @elseif($col == 'npwp')
                      {{ $dokumen->npwp ?? '-' }}
                    @elseif($col == 'link_dokumen_pajak')
                      @if($dokumen->link_dokumen_pajak)
                        <a href="{{ $dokumen->link_dokumen_pajak }}" target="_blank" rel="noopener noreferrer"
                          class="ie-link-anchor" onclick="event.stopPropagation();"
                          title="{{ $dokumen->link_dokumen_pajak }}">
                          <i class="fa-solid fa-link fa-sm"></i> Link Pajak
                        </a>
                      @else
                        -
                      @endif
                    @else
                      -
                    @endif
                  </td>
                @endif
              @endforeach
              <!-- Kolom Deadline -->
              <td class="col-deadline">
                @php
                  // Get received_at from roleData to calculate document age (count up)
                  $roleData = $dokumen->getDataForRole('team_verifikasi');
                  $receivedAt = $roleData?->received_at;

                  // Pause only when the document leaves Team Verifikasi.
                  // If another role returns it to Team Verifikasi, the timer must continue.
                  $isReturnedToVerifikasi = $dokumen->status === 'returned_to_verifikasi';
                  $isReturnedAwayFromVerifikasi = in_array($dokumen->status, ['returned_to_operator', 'returned_to_bidang'])
                    || $dokumen->current_handler === 'operator'
                    || str_starts_with((string) $dokumen->current_handler, 'bagian_');
                  $isPaused = $isReturnedAwayFromVerifikasi;

                  // Check if document is already sent to other roles (including waiting approval statuses)
                  $isSent = in_array($dokumen->status, [
                    'sent_to_perpajakan',
                    'sent_to_akutansi',
                    'sent_to_pembayaran',
                    'pending_approval_perpajakan',
                    'pending_approval_akutansi',
                    'pending_approval_pembayaran',
                    'waiting_approval_perpajakan',
                    'waiting_approval_akuntansi',
                  ]) || $isPaused; // Pause deadline for returned documents

                  // Also check frozen display_status - for tembak documents, the actual
                  // status may have progressed past this role, but display_status is frozen
                  if (!$isSent && $displayStatus && str_starts_with($displayStatus, 'terkirim')) {
                    $isSent = true;
                  }

                  // Check if document is completed
                  $isCompleted = in_array($dokumen->status, [
                    'selesai',
                    'completed',
                    'approved_data_sudah_terkirim',
                  ]) || ($dokumen->status_pembayaran === 'sudah_dibayar');

                  // Calculate document age from received_at (count up)
                  $ageText = '-';
                  $ageLabel = '-';
                  $ageColor = 'gray';
                  $ageIcon = 'fa-clock';
                  $ageDays = 0;

                  if ($receivedAt) {
                    // For sent/completed documents, use processed_at as end time
                    // For active documents, use current time
                    $processedAt = $roleData?->processed_at;

                    if (($isSent || $isCompleted || $isPaused) && $processedAt) {
                      // Document is sent/completed/returned - calculate time taken (frozen, not counting)
                      $endTime = \Carbon\Carbon::parse($processedAt);
                      $diff = $receivedAt->diff($endTime);
                    } else {
                      // Document still active - count up from received_at to now
                      $now = \Carbon\Carbon::now();
                      $diff = $receivedAt->diff($now);
                    }

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
                    if ($isSent || $isCompleted || $isPaused) {
                      $ageColor = 'gray';
                    } elseif ($totalHours >= 72) {
                      $ageColor = 'red';
                    } elseif ($totalHours >= 24) {
                      $ageColor = 'yellow';
                    } else {
                      $ageColor = 'green';
                    }
                  }

                  // Determine deadline type: 'active' (masih diproses), 'sent' (sudah terkirim), 'completed' (selesai)
                  // Prioritize $isSent over $isCompleted - from Team Verifikasi's perspective,
                  // a document they sent is "Terkirim", even if completed downstream
                  $deadlineType = 'active';
                  if ($isPaused) {
                    $deadlineType = 'paused';
                  } elseif ($isSent) {
                    $deadlineType = 'sent';
                  } elseif ($isCompleted) {
                    $deadlineType = 'completed';
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
                    @if($isPaused)
                      <div class="deadline-label" style="font-size: 8px; color: #6b7280; margin-top: 4px; font-weight: 600;">
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
                @else
                  <div class="no-deadline">
                    <i class="fa-solid fa-clock"></i>
                    <span>Belum diterima</span>
                  </div>
                @endif
              </td>
              <!-- Kolom Status: Menampilkan status badge -->
              <td class="col-status" style="text-align: center;" onclick="event.stopPropagation()">
                @if($isReturnedToVerifikasi)
                  {{-- Dokumen selesai diproses role lain dan kembali ke Verifikasi --}}
                  @php
                    $returnedByLabel = match ($dokumen->return_source) {
                      'perpajakan' => 'Team Perpajakan',
                      'akutansi' => 'Team Akutansi',
                      'pembayaran' => 'Team Pembayaran',
                      default => Str::title($dokumen->return_source ?? 'Role Terkait'),
                    };
                  @endphp
                  <span class="badge-status badge-proses"
                    style="position: relative;"
                    title="{{ $dokumen->return_reason ? 'Catatan: ' . $dokumen->return_reason : 'Kembali ke Team Verifikasi' }}">
                    <i class="fa-solid fa-inbox me-1"></i>
                    <span>Kembali dari {{ $returnedByLabel }}</span>
                  </span>
                @elseif($isRejected)
                  {{-- Dokumen ditolak dari inbox atau dari perpajakan/akutansi --}}
                  @if($isRejectedByOtherRole && $rejectedByRole)
                    {{-- Dokumen ditolak oleh perpajakan/akutansi dan dikembalikan ke verifikasi --}}
                    <span class="badge-status badge-dikembalikan" style="position: relative;">
                      <i class="fa-solid fa-times-circle me-1"></i>
                      <span>Dokumen ditolak,
                        <a href="{{ route('returns.verifikasi.index') }}?search={{ $dokumen->nomor_agenda }}"
                          class="text-white text-decoration-underline fw-bold" onclick="event.stopPropagation();"
                          style="color: #fff !important; text-decoration: underline !important; font-weight: 600 !important;">
                          cek disini
                        </a>
                      </span>
                    </span>
                  @else
                    {{-- Dokumen ditolak dari inbox (oleh Team Verifikasi sendiri) --}}
                    {{-- Status "Draft": sama persis dengan tampilan draft pertama kali --}}
                    <span class="badge-status badge-proses">⏳ Draft</span>
                  @endif
                @elseif($dokumen->status == 'selesai' || $dokumen->status == 'approved_Team Verifikasi')
                  {{-- Dokumen yang benar-benar sudah selesai diproses --}}
                  <span class="badge-status badge-selesai">✓
                    {{ $dokumen->status == 'approved_Team Verifikasi' ? 'Approved' : 'Selesai' }}</span>
                @elseif($dokumen->status == 'rejected_Team Verifikasi')
                  <span class="badge-status badge-dikembalikan">Rejected</span>
                @elseif($dokumen->status == 'returned_to_bidang')
                  <span class="badge-status badge-dikembalikan">
                    Dikembalikan ke {{ $dokumen->return_source ? strtoupper($dokumen->return_source) : 'Bagian' }}
                  </span>
                @elseif($displayStatusLabel)
                  {{-- NEW: Use display_status from dokumen_role_data (FINAL/frozen status) --}}
                  @if(str_starts_with($displayStatus, 'terkirim'))
                    <span class="badge-status badge-sent">📤 {{ $displayStatusLabel }}</span>
                  @elseif(str_starts_with($displayStatus, 'menunggu'))
                    <span class="badge-status badge-warning">
                      <i class="fa-solid fa-clock me-1"></i>
                      {{ $displayStatusLabel }}
                    </span>
                  @elseif($displayStatus === 'sedang_diproses')
                    <span class="badge-status badge-proses">⏳ {{ $displayStatusLabel }}</span>
                  @elseif($displayStatus === 'terkunci')
                    <span class="badge-status badge-locked">🔒 {{ $displayStatusLabel }}</span>
                  @else
                    <span class="badge-status badge-proses">{{ $displayStatusLabel }}</span>
                  @endif
                @elseif($isPendingPerpajakan)
                  {{-- FALLBACK: Legacy detection for documents without display_status --}}
                  <span class="badge-status badge-warning"
                    style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white;">
                    <i class="fa-solid fa-clock me-1"></i>
                    Menunggu Approval dari Team Perpajakan
                  </span>
                @elseif($isPendingAkuntansi)
                  {{-- FALLBACK: Legacy detection for documents without display_status --}}
                  <span class="badge-status badge-warning"
                    style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white;">
                    <i class="fa-solid fa-clock me-1"></i>
                    Menunggu Approval dari Team Akutansi
                  </span>
                @elseif($sentToTeamLabel)
                  {{-- FALLBACK: Legacy detection --}}
                  <span class="badge-status badge-sent">📤 Terkirim ke {{ $sentToTeamLabel }}</span>
                @elseif($dokumen->status == 'sent_to_perpajakan')
                  <span class="badge-status badge-sent">📤 Terkirim ke Team Perpajakan</span>
                @elseif($dokumen->status == 'sent_to_akutansi')
                  <span class="badge-status badge-sent">📤 Terkirim ke Team Akutansi</span>
                @elseif($dokumen->status == 'sent_to_pembayaran')
                  <span class="badge-status badge-sent">📤 Terkirim ke Team Pembayaran</span>
                @elseif($dokumen->status == 'waiting_approval_perpajakan')
                  {{-- Bulk send: Menunggu Approve dari Team Perpajakan --}}
                  <span class="badge-status badge-warning"
                    style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white;">
                    <i class="fa-solid fa-clock me-1"></i>
                    <span>Menunggu Approve Team Perpajakan</span>
                  </span>
                @elseif($dokumen->status == 'waiting_approval_akuntansi')
                  {{-- Bulk send: Menunggu Approve dari Team Akuntansi --}}
                  <span class="badge-status badge-warning"
                    style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white;">
                    <i class="fa-solid fa-clock me-1"></i>
                    <span>Menunggu Approve Team Akuntansi</span>
                  </span>
                @elseif($dokumen->status == 'waiting_approval_pembayaran')
                  {{-- Bulk send: Menunggu Approve dari Team Pembayaran --}}
                  <span class="badge-status badge-warning"
                    style="background: linear-gradient(135deg, #6610f2 0%, #9b4dca 100%); color: white;">
                    <i class="fa-solid fa-clock me-1"></i>
                    <span>Menunggu Approve Team Pembayaran</span>
                  </span>
                @elseif(in_array($dokumen->status, ['menunggu_di_approve', 'waiting_reviewer_approval', 'pending_approval_perpajakan', 'pending_approval_akutansi', 'pending_approval_team_verifikasi']) || $isPending)
                  <span class="badge-status"
                    style="background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%); color: white;">
                    <i class="fa-solid fa-clock me-1"></i>
                    <span>{{ $dokumen->getDetailedApprovalText() }}</span>
                  </span>
                @elseif($dokumen->status == 'sedang diproses' && $isLocked)
                  {{-- Dokumen yang baru di-approve dari inbox tapi belum di-set deadline --}}
                  <span class="badge-status badge-locked">🔒 Terkunci</span>
                @elseif($dokumen->status == 'sedang diproses')
                  {{-- Dokumen yang baru di-approve dari inbox dan sudah di-set deadline --}}
                  <span class="badge-status badge-proses">⏳ Sedang Diproses</span>
                @elseif(in_array($dokumen->status, ['sent_to_team_verifikasi']) && !$isLocked)
                  {{-- Dokumen yang sedang diproses (status lama) --}}
                  <span class="badge-status badge-proses">⏳ Diproses</span>
                @elseif($dokumen->status == 'sent_to_team_verifikasi' && $isLocked)
                  <span class="badge-status badge-locked">🔒 Terkunci</span>
                @elseif($dokumen->status == 'returned_to_operator')
                  <span class="badge-status badge-dikembalikan">Dikembalikan ke Ibu A</span>
                @elseif($dokumen->status == 'returned_to_department')
                  @php
                    $workflowReturnSources = ['perpajakan', 'akutansi', 'pembayaran'];
                    $isWorkflowReturn = in_array($dokumen->return_source, $workflowReturnSources, true)
                      && $dokumen->current_handler === 'team_verifikasi'
                      && !$isRejected;
                    $workflowReturnLabel = match ($dokumen->return_source) {
                      'perpajakan' => 'Team Perpajakan',
                      'akutansi' => 'Team Akutansi',
                      'pembayaran' => 'Team Pembayaran',
                      default => Str::title($dokumen->return_source ?? 'Bagian Terkait'),
                    };
                  @endphp
                  @if($isWorkflowReturn)
                    <span class="badge-status badge-proses">
                      <i class="fa-solid fa-inbox me-1"></i>
                      Kembali dari {{ $workflowReturnLabel }}
                    </span>
                  @else
                    <span class="badge-status badge-dikembalikan">
                      Dikembalikan dari {{ Str::title($dokumen->return_source ?? 'Bagian Terkait') }}
                    </span>
                  @endif
                @elseif(Str::startsWith($dokumen->status, 'returned_from_'))
                  @php
                    $source = Str::after($dokumen->status, 'returned_from_');
                    $sourceLabel = match ($source) {
                      'akutansi' => 'Team Akutansi',
                      'perpajakan' => 'Team Perpajakan',
                      default => Str::title(str_replace('_', ' ', $source)),
                    };
                  @endphp
                  <span class="badge-status badge-dikembalikan">Dikembalikan dari {{ $sourceLabel }}</span>
                @else
                  <span class="badge-status badge-proses">⏳ {{ ucfirst($dokumen->status) }}</span>
                @endif
              </td>
              <td class="col-handler" onclick="event.stopPropagation()">
                @include('partials.document-handler-select', ['dokumen' => $dokumen])
              </td>
              @if($showActionColumn)
              <td class="col-action" onclick="event.stopPropagation()">
                @if(!$dokumen->is_at_my_role)
                  {{-- Cross-role visibility: document not yet at Team Verifikasi --}}
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
                    @if($isReturnedToVerifikasi)
                      <!-- Dokumen dikembalikan oleh Perpajakan/Akutansi - tampilkan Kirim Data + Edit + Balik -->
                      <button type="button" class="btn-action btn-kirim btn-full-width"
                        onclick="openSendToNextModal({{ $dokumen->id }})" title="Kirim ke Team Perpajakan/Team Akutansi">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Kirim Data</span>
                      </button>
                      <div class="action-row">
                        <a href="{{ route('documents.verifikasi.edit', $dokumen->id) }}" title="Edit Dokumen"
                          style="flex: 1; text-decoration: none;">
                          <button class="btn-action btn-edit" style="width: 100%;">
                            <i class="fa-solid fa-pen"></i>
                            <span>Edit</span>
                          </button>
                        </a>
                        <button type="button" class="btn-action btn-kembalikan" style="flex: 1;"
                          onclick="openReturnToBidangModal({{ $dokumen->id }}, '{{ $dokumen->bagian ?? 'Unknown' }}')"
                          title="Kembalikan Dokumen ke Bidang">
                          <i class="fa-solid fa-undo"></i>
                          <span>Balik</span>
                        </button>
                      </div>
                    @elseif($isRejected)
                      <!-- Dokumen ditolak dari inbox - tampilkan Kirim (full width), Edit dan Kembalikan di bawah -->
                      <button type="button" class="btn-action btn-kirim btn-full-width"
                        onclick="openSendToNextModal({{ $dokumen->id }})" title="Kirim ke Team Perpajakan/Team Akutansi">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Kirim Data</span>
                      </button>
                      <div class="action-row">
                        <a href="{{ route('documents.verifikasi.edit', $dokumen->id) }}" title="Edit Dokumen"
                          style="flex: 1; text-decoration: none;">
                          <button class="btn-action btn-edit" style="width: 100%;">
                            <i class="fa-solid fa-pen"></i>
                            <span>Edit</span>
                          </button>
                        </a>
                        <button type="button" class="btn-action btn-kembalikan" style="flex: 1;"
                          onclick="alert('Fitur kembalikan untuk Team Verifikasi akan segera tersedia')"
                          title="Kembalikan Dokumen">
                          <i class="fa-solid fa-undo"></i>
                          <span>Balik</span>
                        </button>
                      </div>
                    @elseif(in_array($dokumen->status, ['sent_to_perpajakan', 'sent_to_akutansi', 'sent_to_pembayaran', 'menunggu_di_approve', 'waiting_reviewer_approval', 'pending_approval_perpajakan', 'pending_approval_akutansi', 'pending_approval_team_verifikasi', 'waiting_approval_perpajakan', 'waiting_approval_akuntansi', 'waiting_approval_pembayaran', 'completed', 'selesai']) || $isPending || $isPendingPerpajakan || $isPendingAkuntansi || $dokumen->status_pembayaran === 'sudah_dibayar')
                      {{-- Document already sent, waiting approval, or completed - show single 'Terkirim' button --}}
                      <button class="btn-action btn-edit locked btn-full-width" disabled title="Dokumen sudah terkirim">
                        <i class="fa-solid fa-check-circle"></i>
                        <span>Terkirim</span>
                      </button>
                    @else
                      <!-- Unlocked state - buttons enabled -->
                      @if(in_array($dokumen->status, ['sent_to_team_verifikasi', 'approved_Team Verifikasi', 'sedang diproses', 'returned_to_department', 'returned_from_akutansi']) && !$isApprovedByOtherRole)
                        <!-- Only show "Kirim Data" button if document hasn't been approved by other roles yet -->
                        <button type="button" class="btn-action btn-kirim btn-full-width"
                          onclick="openSendToNextModal({{ $dokumen->id }})" title="Kirim ke Team Perpajakan/Team Akutansi">
                          <i class="fa-solid fa-paper-plane"></i>
                          <span>Kirim Data</span>
                        </button>
                      @elseif($isApprovedByOtherRole)
                        <!-- Document has been approved by Perpajakan/Akutansi - show approved status -->
                        <button class="btn-action btn-edit locked btn-full-width" disabled
                          title="Dokumen sudah di-approve oleh {{ $isApprovedByPerpajakan ? 'Team Perpajakan' : 'Team Akutansi' }}">
                          <i class="fa-solid fa-check-circle"></i>
                          <span>Terkirim</span>
                        </button>
                      @endif
                      <div class="action-row">
                        <a href="{{ route('documents.verifikasi.edit', $dokumen->id) }}" title="Edit Dokumen"
                          style="flex: 1; text-decoration: none;">
                          <button class="btn-action btn-edit" style="width: 100%;">
                            <i class="fa-solid fa-pen"></i>
                            <span>Edit</span>
                          </button>
                        </a>
                        <button type="button" class="btn-action btn-kembalikan" style="flex: 1;"
                          onclick="openReturnToBidangModal({{ $dokumen->id }}, '{{ $dokumen->bagian ?? 'Unknown' }}')"
                          title="Kembalikan Dokumen ke Bidang">
                          <i class="fa-solid fa-undo"></i>
                          <span>Balik</span>
                        </button>
                      </div>
                      {{-- Paraf Button --}}
                      <div style="margin-top: 6px;" id="paraf-container-{{ $dokumen->id }}">
                        @if($dokumen->tanggal_paraf)
                          <span class="badge-paraf-done" style="width: 100%; justify-content: center; padding: 8px;">
                            <i class="fa-solid fa-stamp"></i>
                            Diparaf: {{ $dokumen->pemaraf }}
                          </span>
                        @else
                          <button type="button" class="btn-paraf" style="width: 100%;"
                            onclick="event.stopPropagation(); openParafModal({{ $dokumen->id }}, '{{ $dokumen->nomor_agenda }}')">
                            <i class="fa-solid fa-stamp"></i>
                            <span>Paraf</span>
                          </button>
                        @endif
                      </div>
                    @endif
                  </div>
                @endif
                  </td>
              @endif
                </tr>
                <tr class="detail-row" id="detail-{{ $dokumen->id }}">
                  <td colspan="8">
                    <div class="detail-content" id="detail-content-{{ $dokumen->id }}">
                      <div class="text-center p-4">
                        <i class="fa-solid fa-spinner fa-spin me-2"></i> Loading detail...
                      </div>
                    </div>
                  </td>
                </tr>
          @empty
              <tr>
                <td colspan="{{ count($selectedColumns) + 3 }}" class="text-center" style="padding: 40px;">
                  <i class="fa-solid fa-inbox" style="font-size: 48px; color: #ccc; margin-bottom: 16px;"></i>
                  <p style="color: #999; font-size: 14px;">Belum ada dokumen</p>
                </td>
              </tr>
            @endforelse
