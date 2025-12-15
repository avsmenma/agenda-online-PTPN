@extends('layouts/app');
@section('content')

<style>
  h2 {
    background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 30px;
    font-weight: 700;
    font-size: 28px;
  }

  /* Statistics Cards - Inspired by IbuB's Design */
  .stat-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 8px 32px rgba(26, 77, 62, 0.1), 0 2px 8px rgba(15, 61, 46, 0.05);
    border: 1px solid rgba(26, 77, 62, 0.08);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    height: 140px;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }

  .stat-card::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(26, 77, 62, 0.05) 0%, transparent 70%);
    transition: all 0.5s ease;
  }

  .stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 40px rgba(26, 77, 62, 0.2), 0 4px 16px rgba(15, 61, 46, 0.1);
    border-color: rgba(26, 77, 62, 0.15);
  }

  .stat-card:hover::before {
    top: -60%;
    right: -60%;
  }

  .stat-card-body {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
  }

  .stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    flex-shrink: 0;
  }

  .stat-icon.total {
    background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
  }

  .stat-icon.pending {
    background: linear-gradient(135deg, #2d6a4f 0%, #1b5e3f 100%);
  }

  .stat-icon.sent {
    background: linear-gradient(135deg, #40916c 0%, #2d6a4f 100%);
  }

  .stat-icon.rejected {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
  }

  .stat-content {
    flex: 1;
    min-width: 0;
  }

  .stat-title {
    font-size: 13px;
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .stat-value {
    font-size: 32px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 4px;
    line-height: 1;
  }

  .stat-description {
    font-size: 11px;
    color: #868e96;
    opacity: 0.8;
  }

  .stat-card:hover .stat-value {
    color: #1a4d3e;
  }

  /* Card Icon Animation */
  .stat-card:hover .stat-icon {
    transform: scale(1.1) rotate(5deg);
    transition: all 0.3s ease;
  }

  .search-box {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    padding: 24px;
    border-radius: 16px;
    margin-bottom: 30px;
    box-shadow: 0 8px 32px rgba(26, 77, 62, 0.1), 0 2px 8px rgba(15, 61, 46, 0.05);
    border: 1px solid rgba(26, 77, 62, 0.08);
  }

  .search-box .input-group {
    max-width: 400px;
    margin: 0 auto;
  }

  .search-box .input-group-text {
    background: white;
    border: 2px solid rgba(26, 77, 62, 0.1);
    border-right: none;
    border-radius: 12px 0 0 12px;
    padding: 12px 16px;
  }

  .search-box .form-control {
    border: 2px solid rgba(26, 77, 62, 0.1);
    border-left: none;
    border-radius: 0 12px 12px 0;
    padding: 12px 16px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: white;
  }

  .search-box .form-control:focus {
    outline: none;
    border-color: #1a4d3e;
    box-shadow: 0 0 0 4px rgba(26, 77, 62, 0.1);
  }

  .search-box .form-control::placeholder {
    color: #adb5bd;
  }

  .table-container {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(26, 77, 62, 0.1), 0 2px 8px rgba(15, 61, 46, 0.05);
    border: 1px solid rgba(26, 77, 62, 0.08);
  }

  .table-container h6 {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 2px solid rgba(26, 77, 62, 0.1);
  }

  .table-container h6 span {
    background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 700;
    font-size: 20px;
  }

  .table-container h6 a {
    color: #1a4d3e;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    padding: 8px 20px;
    border-radius: 20px;
    border: 2px solid #1a4d3e;
    background: transparent;
  }

  .table-container h6 a:hover {
    color: white;
    background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
    border-color: #0f3d2e;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(26, 77, 62, 0.3);
  }

  .table {
    margin-bottom: 0;
  }

  .table thead {
    background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
  }

  .table thead th {
    color: white;
    font-weight: 600;
    font-size: 13px;
    letter-spacing: 0.5px;
    padding: 18px 16px;
    border: none;
    text-transform: uppercase;
  }

  .table tbody tr {
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
  }

  .table tbody tr:hover {
    background: linear-gradient(90deg, rgba(26, 77, 62, 0.05) 0%, transparent 100%);
    border-left: 3px solid #1a4d3e;
    transform: scale(1.002);
  }

  .table tbody tr.highlight-row {
    background: linear-gradient(90deg, rgba(26, 77, 62, 0.15) 0%, transparent 100%);
    border-left: 3px solid #1a4d3e;
  }

  .table tbody td {
    padding: 16px;
    font-size: 13px;
    vertical-align: middle;
    border-bottom: 1px solid rgba(26, 77, 62, 0.05);
  }

  .badge {
    padding: 8px 20px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.3px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }

  .badge-pending {
    background: linear-gradient(135deg, #ffc107 0%, #ff8c00 100%);
    color: #333;
    box-shadow: 0 2px 8px rgba(255, 193, 7, 0.3);
  }

  .badge-sent {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
  }

  .badge:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  }

  /* Action Buttons - Modern Design */
  .action-buttons {
    display: flex;
    gap: 8px;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
  }

  .btn-action {
    padding: 10px 14px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.3s ease;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    gap: 6px;
  }

  .btn-edit {
    background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%);
    color: white;
  }

  .btn-edit:hover {
    background: linear-gradient(135deg, #0f3d2e 0%, #0a2e1f 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(26, 77, 62, 0.3);
    color: white;
  }

  .btn-send {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
  }

  .btn-send:hover {
    background: linear-gradient(135deg, #20c997 0%, #1cb384 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    color: white;
  }

  .btn-sent {
    background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
    color: white;
    cursor: default;
  }

  .btn-action:hover:not(.btn-sent) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
  }

  .btn-action:active:not(.btn-sent) {
    transform: translateY(-1px);
  }

  /* Responsive adjustments */
  @media (max-width: 1200px) {
    .stat-card {
      height: 120px;
    }

    .stat-value {
      font-size: 28px;
    }

    .stat-icon {
      width: 50px;
      height: 50px;
      font-size: 20px;
    }
  }

  @media (max-width: 768px) {
    .stat-card {
      height: 110px;
      padding: 20px;
    }

    .stat-value {
      font-size: 24px;
    }

    .stat-icon {
      width: 45px;
      height: 45px;
      font-size: 18px;
    }

    .stat-title {
      font-size: 11px;
    }

    .stat-description {
      font-size: 10px;
    }

    .action-buttons {
      gap: 6px;
    }

    .btn-action {
      padding: 8px 12px;
      font-size: 11px;
      min-width: 36px;
      height: 36px;
    }

    .table thead th {
      padding: 14px 10px;
      font-size: 11px;
    }

    .table tbody td {
      padding: 12px 10px;
      font-size: 12px;
    }

    .badge {
      padding: 6px 12px;
      font-size: 11px;
    }
  }

  @media (max-width: 576px) {
    .stat-card-body {
      flex-direction: column;
      text-align: center;
      gap: 12px;
    }

    .stat-value {
      font-size: 20px;
    }

    .btn-action {
      padding: 6px 10px;
      font-size: 10px;
      min-width: 32px;
      height: 32px;
    }

    .action-buttons {
      flex-direction: column;
      width: 100%;
    }

    .btn-action {
      width: 100%;
      justify-content: center;
    }
  }
</style>

<h2>{{ $title }}</h2>

<!-- Statistics Cards - Modern Design -->
<div class="row mb-4">
    <div class="col-xl-4 col-lg-6 mb-4">
        <div class="stat-card">
            <div class="stat-card-body">
                <div class="stat-icon total">
                    <i class="fas fa-folder-open"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-title">Total Dokumen</div>
                    <div class="stat-value">{{ number_format($totalDokumen, 0, ',', '.') }}</div>
                    <div class="stat-description">Semua dokumen yang dibuat</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-6 mb-4">
        <div class="stat-card">
            <div class="stat-card-body">
                <div class="stat-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-title">Total Dokumen Belum Dikirim</div>
                    <div class="stat-value">{{ number_format($totalBelumDikirim, 0, ',', '.') }}</div>
                    <div class="stat-description">Menunggu untuk dikirim ke Ibu Yuni</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-lg-6 mb-4">
        <div class="stat-card">
            <div class="stat-card-body">
                <div class="stat-icon sent">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-title">Total Dokumen Sudah Dikirim</div>
                    <div class="stat-value">{{ number_format($totalSudahDikirim, 0, ',', '.') }}</div>
                    <div class="stat-description">Telah dikirim ke Ibu Yuni</div>
                </div>
            </div>
        </div>
    </div>

    @if(isset($totalDitolakInbox) && $totalDitolakInbox > 0)
    <div class="col-xl-4 col-lg-6 mb-4">
        <div class="stat-card">
            <div class="stat-card-body">
                <div class="stat-icon rejected">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-title">Ditolak dari Inbox</div>
                    <div class="stat-value">{{ number_format($totalDitolakInbox, 0, ',', '.') }}</div>
                    <div class="stat-description">Dokumen yang ditolak dan dikembalikan</div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Search Box -->
    <div class="search-box">
      <div class="input-group">
        <span class="input-group-text">
          <i class="fa-solid fa-magnifying-glass text-muted"></i>
        </span>
        <input type="text" class="form-control" placeholder="Search...">
      </div>
    </div>

       <!-- Tabel Dokumen Terbaru -->
    <div class="table-container">
      <h6>
        <span style="color: #1a4d3e; text-decoration: none; font-size: 24px;">Dokumen Terbaru</span>
        <a href="{{ route('dokumens.index') }}" style="color: #1a4d3e; text-decoration: none; font-size: 14px;">View All</a>
      </h6>
      <div class="table-responsive">
        <div>
            <table class="table table-hover align-middle mb-0">
          <thead>
            <tr class="table table-dark">
              <th>No</th>
              <th>Nomor Agenda</th>
              <th>Nomor SPP</th>
              <th>Tanggal Masuk</th>
              <th>Nilai Rupiah</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @forelse($dokumenTerbaru as $index => $dokumen)
            <tr>
              <td>{{ $index + 1 }}</td>
              <td>
                <strong>{{ $dokumen->nomor_agenda }}</strong>
                <br>
                <small class="text-muted">{{ $dokumen->bulan }} {{ $dokumen->tahun }}</small>
              </td>
              <td>{{ $dokumen->nomor_spp }}</td>
              <td>{{ $dokumen->tanggal_masuk->format('d/m/Y H:i') }}</td>
              <td>
                <strong>{{ $dokumen->formatted_nilai_rupiah }}</strong>
              </td>
              <td>
                @if(in_array($dokumen->status, ['draft', 'returned_to_ibua']))
                  <span class="badge badge-pending">
                    <i class="fas fa-clock"></i>
                    Belum Dikirim
                  </span>
                @php
                  $ibuBStatus = $dokumen->getStatusForRole('ibub');
                  $isApproved = $ibuBStatus && $ibuBStatus->status === 'approved';
                  $isPending = $ibuBStatus && $ibuBStatus->status === 'pending';
                @endphp
                @elseif($isApproved)
                  {{-- PRIORITY: Dokumen sudah di-approve oleh Ibu Yuni - harus ditampilkan sebagai Terkirim --}}
                  <span class="badge badge-sent">
                    <i class="fas fa-check-circle"></i>
                    Terkirim
                  </span>
                @elseif($dokumen->status == 'menunggu_di_approve' && $isPending)
                  <span class="badge badge-sent">
                    <i class="fas fa-check-circle"></i>
                    Terkirim, sedang menunggu approve
                  </span>
                @elseif($dokumen->status == 'sent_to_ibub' && $isApproved)
                  <span class="badge badge-sent">
                    <i class="fas fa-check-circle"></i>
                    Terkirim
                  </span>
                @else
                  <span class="badge badge-sent">
                    <i class="fas fa-check-circle"></i>
                    Sudah Dikirim ke Ibu Yuni
                  </span>
                @endif
              </td>
              <td>
                <div class="action-buttons">
                  @php
                    $canSend = in_array($dokumen->status ?? 'draft', ['draft', 'returned_to_ibua', 'sedang diproses'])
                              && ($dokumen->current_handler ?? 'ibuA') == 'ibuA'
                              && ($dokumen->created_by ?? 'ibuA') == 'ibuA';
                    $isSent = ($dokumen->status ?? '') == 'sent_to_ibub'
                             || (($dokumen->current_handler ?? 'ibuA') == 'ibuB' && ($dokumen->status ?? '') != 'returned_to_ibua')
                             || !empty($dokumen->sent_to_ibub_at);
                    $canEdit = !$isSent && in_array($dokumen->status ?? 'draft', ['draft', 'returned_to_ibua', 'sedang diproses'])
                              && ($dokumen->current_handler ?? 'ibuA') == 'ibuA';
                  @endphp
                  @if($canEdit)
                  <a href="{{ route('dokumens.edit', $dokumen->id) }}" class="btn-action btn-edit" title="Edit Dokumen">
                    <i class="fa-solid fa-edit"></i>
                    Edit
                  </a>
                  @endif
                  @if($canSend)
                  <button type="button" class="btn-action btn-send" onclick="sendToIbuB({{ $dokumen->id }})" title="Kirim ke Ibu Yuni">
                    <i class="fa-solid fa-paper-plane"></i>
                    Kirim
                  </button>
                  @elseif($isSent)
                  <span class="btn-action btn-sent" title="Sudah Dikirim">
                    <i class="fa-solid fa-check"></i>
                    Terkirim
                  </span>
                  @endif
                </div>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="7" class="text-center py-4">
                <i class="fa-solid fa-inbox fa-2x text-muted mb-2"></i>
                <p class="text-muted mb-0">Belum ada dokumen yang tersedia.</p>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
        </div>
        
      </div>
    </div>

<script>
function sendToIbuB(dokumenId) {
  if (!confirm('Apakah Anda yakin ingin mengirim dokumen ini ke Ibu Yuni?')) {
    return;
  }

  // Show loading state
  const btn = event.target.closest('button');
  if (!btn) return;
  
  const originalHTML = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

  fetch(`/dokumens/${dokumenId}/send-to-ibub`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert(data.message);
      location.reload();
    } else {
      alert(data.message || 'Terjadi kesalahan saat mengirim dokumen.');
      btn.disabled = false;
      btn.innerHTML = originalHTML;
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Terjadi kesalahan saat mengirim dokumen.');
    btn.disabled = false;
    btn.innerHTML = originalHTML;
  });
}
</script>

@endsection