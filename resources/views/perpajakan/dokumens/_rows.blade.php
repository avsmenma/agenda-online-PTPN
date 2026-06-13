@php $showActionColumn = $showActionColumn ?? false; @endphp
          @forelse($dokumens as $index => $dokumen)
            @php
              // Use is_locked from controller (based on DokumenHelper logic)
              $isLocked = $dokumen->is_locked ?? false;
              $isSentToAkutansi = $dokumen->status == 'sent_to_akutansi' || in_array($dokumen->status, ['completed', 'selesai']) || $dokumen->status_pembayaran === 'sudah_dibayar';
              $isSentToPembayaran = $dokumen->status == 'sent_to_pembayaran' || in_array($dokumen->status, ['completed', 'selesai']) || $dokumen->status_pembayaran === 'sudah_dibayar';
              $isPendingApprovalAkutansi = $dokumen->status == 'pending_approval_akutansi';
              $isPendingApprovalPembayaran = $dokumen->status == 'pending_approval_pembayaran' || $dokumen->status == 'menunggu_di_approve';
              $isPending = $dokumen->roleStatuses
                ->where('role_code', 'perpajakan')
                ->where('status', 'pending')
                ->isNotEmpty();
              // Check if document is rejected by perpajakan OR returned from akutansi/pembayaran
              $isRejectedByPerpajakan = $dokumen->roleStatuses
                ->where('role_code', 'perpajakan')
                ->where('status', 'rejected')
                ->isNotEmpty();

              // Check if document was rejected by akutansi and returned to department (perpajakan)
              $isReturnedFromAkutansi = $dokumen->status == 'returned_to_department'
                && $dokumen->return_source == 'akutansi';

              // Check if akutansi rejected this document (using roleStatuses)
              $isRejectedByAkutansi = $dokumen->roleStatuses
                ->where('role_code', 'akutansi')
                ->where('status', 'rejected')
                ->isNotEmpty();

              // Document is rejected if it was rejected by perpajakan, or by akutansi and returned to department
              $isRejected = $isRejectedByPerpajakan || $isReturnedFromAkutansi || $isRejectedByAkutansi;

              // === PERBAIKAN: Gunakan display_status dari dokumen_role_data untuk stabilitas ===
              // Perpajakan memiliki display_status tersendiri yang TIDAK berubah setelah Akutansi approve
              // Saat Akutansi approve, display_status Perpajakan menjadi 'terkirim_akutansi' (FINAL)
              $perpajakanDisplayStatus = $dokumen->getDisplayStatusForRole('perpajakan');

              // Get role data for fallback logic
              $perpajakanRoleData = $dokumen->getDataForRole('perpajakan');
              $akutansiRoleData = $dokumen->getDataForRole('akutansi');
              $pembayaranRoleData = $dokumen->getDataForRole('pembayaran');

              // Check Akutansi and Pembayaran status for fallback
              $akutansiHasApproved = $dokumen->roleStatuses
                ->where('role_code', 'akutansi')
                ->where('status', 'approved')
                ->isNotEmpty();

              $pembayaranHasApproved = $dokumen->roleStatuses
                ->where('role_code', 'pembayaran')
                ->where('status', 'approved')
                ->isNotEmpty();

              $akutansiIsPending = $dokumen->roleStatuses
                ->where('role_code', 'akutansi')
                ->where('status', 'pending')
                ->isNotEmpty();

              $pembayaranIsPending = $dokumen->roleStatuses
                ->where('role_code', 'pembayaran')
                ->where('status', 'pending')
                ->isNotEmpty();

              // Determine status using display_status first, then fallback
              $sentToTeamFromPerpajakan = null;
              $isPendingDownstream = false;
              $pendingDownstreamTeam = null;

              // PRIORITAS 1: Gunakan display_status jika sudah FINAL (terkirim_*)
              if ($perpajakanDisplayStatus && str_starts_with($perpajakanDisplayStatus, 'terkirim')) {
                // Status sudah final, gunakan nilai ini dan ABAIKAN downstream status
                $sentToTeamFromPerpajakan = match ($perpajakanDisplayStatus) {
                  'terkirim_akutansi' => 'Team Akutansi',
                  'terkirim_pembayaran' => 'Team Pembayaran',
                  'terkirim' => 'Team Akutansi',
                  default => 'Team Akutansi'
                };
                // Tidak ada pending downstream karena sudah final
                $isPendingDownstream = false;
              }
              // PRIORITAS 2: Jika display_status menunjukkan menunggu approval
              elseif ($perpajakanDisplayStatus && str_starts_with($perpajakanDisplayStatus, 'menunggu_approval')) {
                $isPendingDownstream = true;
                $pendingDownstreamTeam = match ($perpajakanDisplayStatus) {
                  'menunggu_approval_akutansi' => 'Team Akutansi',
                  'menunggu_approval_pembayaran' => 'Team Pembayaran',
                  default => 'Team Akutansi'
                };
              }
              // PRIORITAS 3: Fallback ke logika lama jika display_status belum diset
              else {
                // Check for documents that BYPASSED Perpajakan (Bulk Direct to Payment)
                // These documents went: Operator -> Team Verifikasi -> Pembayaran
                // They never entered Perpajakan workflow, so show them as already at Pembayaran
                $isBypassedToPembayaran = (
                  $dokumen->current_handler === 'pembayaran' ||
                  $dokumen->status === 'completed' ||
                  $dokumen->status_pembayaran === 'sudah_dibayar' ||
                  ($pembayaranRoleData && $pembayaranRoleData->received_at)
                ) && !$perpajakanRoleData?->received_at; // Perpajakan never received this document

                if ($isBypassedToPembayaran) {
                  $sentToTeamFromPerpajakan = 'Team Pembayaran';
                }
                // Check if Akutansi approved (final sent state)
                // Note: For Perpajakan, once Akutansi approves, status becomes FINAL
                // We do NOT check pembayaran because Perpajakan never sends directly to Pembayaran
                elseif ($akutansiHasApproved || ($akutansiRoleData && $akutansiRoleData->received_at && !$akutansiIsPending)) {
                  $sentToTeamFromPerpajakan = 'Team Akutansi';
                }

                // Check if pending in Akutansi inbox ONLY
                // Do NOT check pembayaranIsPending here - that's Akutansi's concern, not Perpajakan's
                if ($akutansiIsPending && !$sentToTeamFromPerpajakan) {
                  $isPendingDownstream = true;
                  $pendingDownstreamTeam = 'Team Akutansi';
                }
              }

              $canSend = $dokumen->status != 'sent_to_akutansi'
                && $dokumen->status != 'sent_to_pembayaran'
                && $dokumen->status != 'pending_approval_akutansi'
                && $dokumen->status != 'pending_approval_pembayaran'
                && $dokumen->status != 'menunggu_di_approve'
                && $dokumen->status != 'returned_to_department'
                && $dokumen->current_handler == 'perpajakan'
                && !$isRejected;
              $perpajakanRequiredFields = [
                'npwp' => 'NPWP',
                'no_faktur' => 'Nomor Faktur',
                'tanggal_faktur' => 'Tanggal Faktur',
                'tanggal_selesai_verifikasi_pajak' => 'Tanggal Selesai Verifikasi Pajak',
                'jenis_pph' => 'Jenis PPh',
                'dpp_pph' => 'Nilai DPP PPh',
                'ppn_terhutang' => 'PPN Terhutang',
                'link_dokumen_pajak' => 'Link Dokumen Pajak',
              ];
              $missingPerpajakanFields = [];
              foreach ($perpajakanRequiredFields as $fieldKey => $fieldLabel) {
                if (empty($dokumen->{$fieldKey})) {
                  $missingPerpajakanFields[] = $fieldLabel;
                }
              }

              // Determine send button tooltip message
              $sendButtonTooltip = 'Kirim ke Team Akutansi atau Pembayaran';
              if ($isSentToAkutansi) {
                $sendButtonTooltip = 'Dokumen sudah dikirim ke Team Akutansi';
              } elseif ($isSentToPembayaran) {
                $sendButtonTooltip = 'Dokumen sudah dikirim ke Team Pembayaran';
              } elseif (!$canSend) {
                if ($dokumen->current_handler != 'perpajakan') {
                  $sendButtonTooltip = 'Dokumen tidak sedang ditangani oleh perpajakan';
                } else {
                  $sendButtonTooltip = 'Dokumen tidak dapat dikirim';
                }
              }
              $canInlineEdit = $dokumen->current_handler === 'perpajakan';
              $dateCols = ['tanggal_spp','tanggal_berita_acara','tanggal_spk','tanggal_berakhir_spk','tanggal_faktur','tanggal_paraf','tanggal_miro','tanggal_selesai_verifikasi_pajak'];
            @endphp
            <tr class="main-row clickable-row {{ $isLocked ? 'locked-row' : '' }}"
              data-editable="{{ $canInlineEdit ? 'true' : 'false' }}"
              data-dokumen-id="{{ $dokumen->id }}"
              data-kategori="{{ $dokumen->kategori ?? '' }}"
              data-jenis-dokumen="{{ $dokumen->jenis_dokumen ?? '' }}"
              data-jenis-sub-pekerjaan="{{ $dokumen->jenis_sub_pekerjaan ?? '' }}"
              ondblclick="handleRowClick(event, {{ $dokumen->id }})" title="Double klik untuk melihat detail">
              <td class="col-checkbox" onclick="event.stopPropagation();">
                @if($canSend && !$isSentToAkutansi && !$isSentToPembayaran && !$isPendingApprovalAkutansi && !$isPendingApprovalPembayaran)
                  <input type="checkbox" class="doc-checkbox bulk-checkbox" data-id="{{ $dokumen->id }}"
                    data-agenda="{{ $dokumen->nomor_agenda }}">
                @endif
              </td>
              <td class="col-no" style="text-align: center;">{{ $dokumens->firstItem() + $index }}</td>
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
                    @elseif($col == 'nomor_mirror')
                      {{ $dokumen->nomor_mirror ?? '-' }}
                    @elseif($col == 'nomor_miro')
                      {{ $dokumen->nomor_miro ?? '-' }}
                    @elseif($col == 'nomor_po')
                      @if($dokumen->dokumenPos && $dokumen->dokumenPos->count() > 0)
                        {{ $dokumen->dokumenPos->pluck('nomor_po')->join(', ') }}
                      @else
                        -
                      @endif
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
                      @if($dokumen->link_dokumen_pajak)
                        <a href="{{ $dokumen->link_dokumen_pajak }}" target="_blank" rel="noopener noreferrer"
                          title="{{ $dokumen->link_dokumen_pajak }}" style="color: #0d6efd; text-decoration: none;">
                          <i class="fa-solid fa-link me-1"></i>Lihat Dokumen
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
                        <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;background:linear-gradient(135deg,#22c55e 0%,#16a34a 100%);color:white;border-radius:6px;font-size:11px;font-weight:600;white-space:nowrap;">
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
                      @php
                        $workflowStatusLabel = null;
                        $workflowStatusClass = 'badge-proses';

                        if ($isRejected) {
                          $workflowStatusLabel = 'Ditolak / Dikembalikan';
                          $workflowStatusClass = 'badge-dikembalikan';
                        } elseif ($isPendingDownstream) {
                          $workflowStatusLabel = 'Menunggu Approval dari ' . $pendingDownstreamTeam;
                          $workflowStatusClass = 'badge-warning';
                        } elseif ($sentToTeamFromPerpajakan) {
                          $workflowStatusLabel = 'Terkirim ke ' . $sentToTeamFromPerpajakan;
                          $workflowStatusClass = 'badge-sent';
                        } elseif ($dokumen->status == 'sent_to_akutansi' && !$akutansiIsPending) {
                          $workflowStatusLabel = 'Terkirim ke Team Akutansi';
                          $workflowStatusClass = 'badge-sent';
                        } elseif ($dokumen->status == 'sent_to_pembayaran' && !$pembayaranIsPending) {
                          $workflowStatusLabel = 'Terkirim ke Team Pembayaran';
                          $workflowStatusClass = 'badge-sent';
                        } elseif ($dokumen->status == 'pending_approval_perpajakan') {
                          $workflowStatusLabel = 'Menunggu Approval Perpajakan';
                          $workflowStatusClass = 'badge-warning';
                        } elseif ($dokumen->status == 'sent_to_perpajakan' && $dokumen->current_handler == 'perpajakan') {
                          $workflowStatusLabel = 'Diproses Team Perpajakan';
                          $workflowStatusClass = 'badge-proses';
                        } elseif ($dokumen->status == 'returned_to_verifikasi') {
                          $workflowStatusLabel = 'Kembali ke Team Verifikasi';
                          $workflowStatusClass = 'badge-sent';
                        } elseif ($dokumen->status == 'sedang diproses') {
                          $workflowStatusLabel = 'Sedang Diproses';
                          $workflowStatusClass = 'badge-proses';
                        } elseif (in_array($dokumen->status, ['completed', 'selesai']) || $dokumen->status_pembayaran === 'sudah_dibayar') {
                          $workflowStatusLabel = 'Selesai Dibayar';
                          $workflowStatusClass = 'badge-selesai';
                        }

                        $displayStatusDokumen = $workflowStatusLabel ?: $dokumen->status_dokumen_csv;
                        $displayStatusClass = $workflowStatusLabel
                          ? $workflowStatusClass
                          : ($dokumen->status_dokumen_csv == 'Selesai Dibayar' ? 'badge-selesai' : ($dokumen->status_dokumen_csv == 'Dikembalikan' ? 'badge-dikembalikan' : 'badge-proses'));
                      @endphp
                      @if($displayStatusDokumen)
                        <span class="badge-status {{ $displayStatusClass }}" style="font-size: 10px; padding: 4px 8px;">
                          {{ $displayStatusDokumen }}
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
              <!-- Kolom Deadline -->
              <td class="col-deadline">
                @php
                  // Get received_at from roleData to calculate document age (count up)
                  $roleData = $dokumen->getDataForRole('perpajakan');
                  $receivedAt = $roleData?->received_at;

                  // Check if document is already sent to other roles
                  $isSent = in_array($dokumen->status, [
                    'sent_to_akutansi',
                    'sent_to_pembayaran',
                    'pending_approval_akutansi',
                    'pending_approval_pembayaran',
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
                @elseif($isBypassedToPembayaran)
                  {{-- Document bypassed Perpajakan - show proper deadline card like Team Verifikasi --}}
                  @php
                    // Try multiple timestamp sources for bypass documents
                    $bypassPembayaranData = $dokumen->getDataForRole('pembayaran');
                    $bypassVerifikasiData = $dokumen->getDataForRole('team_verifikasi');
                    
                    // Priority: pembayaran received_at > verifikasi processed_at > document tanggal_masuk
                    $bypassTimestamp = $bypassPembayaranData?->received_at 
                      ?? $bypassVerifikasiData?->processed_at 
                      ?? $dokumen->tanggal_masuk;
                    
                    $bypassAgeText = '-';
                    $bypassAgeLabel = '-';
                    $bypassAgeColor = 'gray';
                    $bypassAgeIcon = 'fa-clock';
                    $bypassAgeDays = 0;

                    if ($bypassTimestamp) {
                      $bypassStartTime = $bypassTimestamp instanceof \Carbon\Carbon 
                        ? $bypassTimestamp 
                        : \Carbon\Carbon::parse($bypassTimestamp);
                      
                      // For bypassed docs, ALWAYS freeze time at processed_at (like Team Verifikasi)
                      // This ensures bypassed docs show "0 menit" consistently
                      $bypassProcessedAt = $bypassVerifikasiData?->processed_at ?? $bypassPembayaranData?->received_at;
                      if ($bypassProcessedAt) {
                        $bypassEndTime = $bypassProcessedAt instanceof \Carbon\Carbon 
                          ? $bypassProcessedAt 
                          : \Carbon\Carbon::parse($bypassProcessedAt);
                      } else {
                        // Fallback: use the same timestamp (0 time difference)
                        $bypassEndTime = $bypassStartTime;
                      }
                      
                      $bypassDiff = $bypassStartTime->diff($bypassEndTime);
                      $bypassAgeDays = $bypassDiff->days;

                      $bypassElapsedParts = [];
                      if ($bypassDiff->days > 0) $bypassElapsedParts[] = $bypassDiff->days . ' hari';
                      if ($bypassDiff->h > 0) $bypassElapsedParts[] = $bypassDiff->h . ' jam';
                      if ($bypassDiff->i > 0 || empty($bypassElapsedParts)) $bypassElapsedParts[] = $bypassDiff->i . ' menit';
                      $bypassAgeText = implode(' ', $bypassElapsedParts) . ' ⏸️';

                      $bypassTotalHours = ($bypassDiff->days * 24) + $bypassDiff->h;
                      if ($bypassTotalHours >= 72) { $bypassAgeLabel = 'TERLAMBAT'; $bypassAgeIcon = 'fa-times-circle'; }
                      elseif ($bypassTotalHours >= 24) { $bypassAgeLabel = 'PERINGATAN'; $bypassAgeIcon = 'fa-exclamation-triangle'; }
                      else { $bypassAgeLabel = 'AMAN'; $bypassAgeIcon = 'fa-check-circle'; }

                      // Always grey for bypassed/sent documents
                      $bypassAgeColor = 'gray';
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
                @if($isRejected)
                  {{-- Dokumen ditolak oleh perpajakan --}}
                  <span class="badge-status badge-dikembalikan" style="position: relative;">
                    <i class="fa-solid fa-times-circle me-1"></i>
                    <span>Dokumen ditolak,
                      <a href="{{ route('returns.perpajakan.index') }}?search={{ $dokumen->nomor_agenda }}"
                        class="text-white text-decoration-underline fw-bold" onclick="event.stopPropagation();"
                        style="color: #fff !important; text-decoration: underline !important; font-weight: 600 !important;">
                        cek disini
                      </a>
                    </span>
                  </span>
                @elseif($isPendingDownstream)
                  {{-- FIX: Document is in downstream inbox (Akutansi/Pembayaran) waiting approval --}}
                  {{-- This should show "Menunggu Approval" NOT "Sudah Terkirim" --}}
                  <span class="badge-status badge-warning">⏳ Menunggu Approval dari {{ $pendingDownstreamTeam }}</span>
                @elseif($sentToTeamFromPerpajakan)
                  {{-- Document has been APPROVED by downstream (not just pending) --}}
                  <span class="badge-status badge-sent">📤 Terkirim ke {{ $sentToTeamFromPerpajakan }}</span>
                @elseif($dokumen->status == 'sent_to_akutansi' && !$akutansiIsPending)
                  <span class="badge-status badge-sent">📤 Terkirim ke Team Akutansi</span>
                @elseif($dokumen->status == 'sent_to_pembayaran' && !$pembayaranIsPending)
                  <span class="badge-status badge-sent">📤 Terkirim ke Team Pembayaran</span>
                @elseif($dokumen->status == 'sent_to_perpajakan' && $dokumen->current_handler == 'perpajakan')
                  <span class="badge-status badge-proses">⏳ Sedang Diproses</span>
                @elseif($isLocked)
                  <span class="badge-status badge-locked">🔒 Terkunci - Menunggu Deadline</span>
                @elseif($dokumen->status == 'pending_approval_perpajakan')
                  <span class="badge-status badge-warning">📥 Baru Diterima</span>
                @elseif($dokumen->status == 'returned_to_verifikasi')
                  <span class="badge-status badge-sent">
                    <i class="fa-solid fa-paper-plane me-1"></i>
                    Kembali ke Team Verifikasi
                  </span>
                @elseif($dokumen->status == 'sedang diproses')
                  <span class="badge-status badge-proses">⏳ Sedang Diproses</span>
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
                  {{-- Cross-role visibility: document not yet at Perpajakan --}}
                  @php
                    $handlerLabel = match($dokumen->current_handler) {
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
                  @if($isLocked)
                    {{-- Locked state - show as Terkirim (matching Team Verifikasi style) --}}
                    <button class="btn-action btn-edit locked btn-full-width" disabled title="Dokumen sudah terkirim">
                      <i class="fa-solid fa-check-circle"></i>
                      <span>Terkirim</span>
                    </button>
                  @elseif($sentToTeamFromPerpajakan || $isPendingDownstream)
                    {{-- Document has been sent/approved by downstream - FINAL state for Perpajakan --}}
                    <button class="btn-action btn-edit locked btn-full-width" disabled title="Dokumen sudah terkirim">
                      <i class="fa-solid fa-check-circle"></i>
                      <span>Terkirim</span>
                    </button>
                  @elseif($isRejected)
                    {{-- Document was rejected --}}
                    <button class="btn-action btn-edit locked btn-full-width" disabled title="Dokumen ditolak">
                      <i class="fa-solid fa-times-circle"></i>
                      <span>Ditolak</span>
                    </button>
                  @elseif($dokumen->status == 'returned_to_verifikasi')
                    {{-- Document has been handed back to Verifikasi - disable all actions --}}
                    <button class="btn-action btn-edit locked btn-full-width" disabled title="Dokumen sudah kembali ke Verifikasi">
                      <i class="fa-solid fa-check-circle"></i>
                      <span>Kembali ke Verifikasi</span>
                    </button>
                  @else
                    <!-- Unlocked state - buttons enabled -->
                    <button type="button" class="btn-action btn-send btn-full-width"
                      onclick="handleSendToNext({{ $dokumen->id }})" data-doc-id="{{ $dokumen->id }}"
                      data-missing-fields="{{ e(implode('||', $missingPerpajakanFields)) }}" title="{{ $sendButtonTooltip }}"
                      @if(!$canSend) disabled @endif>
                      <i class="fa-solid fa-paper-plane"></i>
                      <span>Kirim Data</span>
                    </button>
                    <div class="action-row">
                      <a href="{{ route('documents.perpajakan.edit', $dokumen->id) }}" title="Edit Dokumen"
                        style="flex: 1; text-decoration: none;">
                        <button class="btn-action btn-edit" style="width: 100%;">
                          <i class="fa-solid fa-pen"></i>
                          <span>Edit</span>
                        </button>
                      </a>
                      <button type="button" class="btn-action btn-kembalikan" style="flex: 1;"
                        onclick="openReturnModal({{ $dokumen->id }})" title="Kirim kembali ke Team Verifikasi">
                        <i class="fa-solid fa-paper-plane"></i>
                        <span>Balik</span>
                      </button>
                    </div>
                  @endif
                </div>
                @endif
              </td>
              @endif
            </tr>
          @empty
            <tr>
              <td colspan="{{ count($selectedColumns) + 4 }}" class="text-center" style="padding: 40px;">
                <i class="fa-solid fa-inbox" style="font-size: 48px; color: #ccc; margin-bottom: 16px;"></i>
                <p style="color: #999; font-size: 14px;">Belum ada dokumen</p>
              </td>
            </tr>
          @endforelse
