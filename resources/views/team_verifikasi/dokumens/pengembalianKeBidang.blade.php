@extends('layouts/app')
@section('content')

<style>
  .form-title {
    font-size: 24px;
    font-weight: 700;
    background: linear-gradient(135deg, #fd7e14 0%, #e55a00 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .stats-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
  }

  .stat-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
    border: 1px solid rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
  }

  .stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
  }

  .stat-label {
    font-size: 13px;
    color: #666;
    font-weight: 500;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .stat-value {
    font-size: 28px;
    font-weight: 700;
    color: #333;
  }

  .stat-dept {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    margin-top: 8px;
    color: white;
  }

  .stat-dept.DPM { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
  .stat-dept.SKH { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
  .stat-dept.SDM { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
  .stat-dept.TEP { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
  .stat-dept.KPL { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
  .stat-dept.AKN { background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); }
  .stat-dept.TAN { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); }
  .stat-dept.PMO { background: linear-gradient(135deg, #ff9a56 0%, #ff6a88 100%); }

  .table-dokumen {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
    border: 1px solid rgba(8, 62, 64, 0.08);
  }

  .table-dokumen thead {
    background: linear-gradient(135deg, #fd7e14 0%, #e55a00 100%);
    color: white;
    position: relative;
  }

  .table-dokumen thead::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent 0%, #f8f9fa 50%, transparent 100%);
  }

  .table-dokumen thead th {
    padding: 16px 12px;
    font-weight: 600;
    font-size: 13px;
    border: none;
    text-align: center;
    letter-spacing: 0.5px;
  }

  .table-dokumen tbody tr {
    transition: all 0.3s ease;
    border-left: 3px solid transparent;
  }

  .table-dokumen tbody tr.main-row {
    cursor: pointer;
  }

  .table-dokumen tbody tr:hover {
    background: linear-gradient(135deg, rgba(253, 126, 20, 0.05) 0%, rgba(229, 90, 0, 0.02) 100%);
    border-left: 3px solid #fd7e14;
  }

  .table-dokumen tbody td {
    padding: 12px;
    vertical-align: middle;
    border-bottom: 1px solid #f0f0f0;
  }

  .dept-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    color: white;
    display: inline-block;
    text-transform: capitalize;
  }

  .bidang-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
    color: white;
    display: inline-block;
    text-transform: uppercase;
    background: linear-gradient(135deg, #fd7e14 0%, #e55a00 100%);
  }

  .bidang-badge.DPM { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
  .bidang-badge.SKH { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
  .bidang-badge.SDM { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
  .bidang-badge.TEP { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
  .bidang-badge.KPL { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
  .bidang-badge.AKN { background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); }
  .bidang-badge.TAN { background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); }
  .bidang-badge.PMO { background: linear-gradient(135deg, #ff9a56 0%, #ff6a88 100%); }

  .action-buttons {
    display: flex;
    gap: 6px;
    justify-content: center;
    flex-wrap: wrap;
  }

  .btn-action {
    padding: 8px 12px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 11px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    min-width: 44px;
    min-height: 36px;
  }

  .btn-send {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
  }

  .btn-send:hover {
    background: linear-gradient(135deg, #20c997 0%, #1ea085 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    color: white;
  }

  .btn-edit {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
  }

  .btn-edit:hover {
    background: linear-gradient(135deg, #0a4f52 0%, #0d5f63 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
    color: white;
  }


  .detail-content {
    padding: 20px;
    border-top: 2px solid rgba(8, 62, 64, 0.1);
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
  }

  /* Detail Grid - Horizontal Layout */
  .detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 16px;
    margin-top: 0;
  }

  .detail-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 12px;
    background: white;
    border-radius: 8px;
    border: 1px solid rgba(8, 62, 64, 0.08);
    transition: all 0.2s ease;
  }

  .detail-item:hover {
    border-color: #889717;
    box-shadow: 0 2px 8px rgba(136, 151, 23, 0.1);
    transform: translateY(-1px);
  }

  .detail-label {
    font-size: 11px;
    font-weight: 600;
    color: #083E40;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .detail-value {
    font-size: 13px;
    color: #333;
    font-weight: 500;
    word-break: break-word;
  }

  /* Badge in detail */
  .detail-value .badge {
    font-size: 11px;
    padding: 4px 12px;
    border-radius: 20px;
  }

  .badge-selesai {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
  }

  .badge-proses {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
  }

  .badge-dikembalikan {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
  }

  /* Responsive Detail Grid */
  @media (max-width: 1200px) {
    .detail-grid {
      grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
      gap: 12px;
    }
  }

  @media (max-width: 768px) {
    .detail-content {
      padding: 16px;
    }

    .detail-grid {
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 10px;
    }

    .detail-item {
      padding: 10px;
    }

    .detail-label {
      font-size: 10px;
    }

    .detail-value {
      font-size: 12px;
    }
  }

  @media (max-width: 480px) {
    .detail-grid {
      grid-template-columns: 1fr;
      gap: 8px;
    }

    .detail-item {
      padding: 8px;
    }
  }

  .empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #999;
  }

  .empty-state i {
    font-size: 48px;
    margin-bottom: 16px;
    opacity: 0.5;
  }

  .department-info {
    font-size: 11px;
    color: #666;
    margin-top: 4px;
  }

  @media (max-width: 768px) {
    .stats-container {
      grid-template-columns: 1fr;
    }

    .action-buttons {
      flex-direction: column;
      gap: 8px;
    }

    .btn-action {
      width: 100%;
      justify-content: center;
    }
  }
</style>

<div class="container-fluid">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="form-title">{{ $title }}</h2>
  </div>

  <!-- Statistics Cards -->
  <div class="stats-container">
    <div class="stat-card">
      <div class="stat-label">
        <i class="fa-solid fa-building"></i>
        Total Dokumen
      </div>
      <div class="stat-value">{{ $totalReturned }}</div>
    </div>

    @foreach($bidangStats as $stat)
    <div class="stat-card">
      <div class="stat-label">
        <i class="fa-solid fa-sitemap"></i>
        {{ $stat['nama_bidang'] }}
      </div>
      <div class="stat-value">{{ $stat['count'] }}</div>
      <div class="stat-dept {{ $stat['kode_bidang'] }}">{{ $stat['kode_bidang'] }}</div>
    </div>
    @endforeach
  </div>

  <!-- Search and Filter -->
  <div class="search-box d-flex align-items-center mb-4">
    <form action="{{ route('returns.verifikasi.bidang') }}" method="GET" class="d-flex align-items-center w-100">
      <div class="input-group me-3" style="max-width: 300px;">
        <span class="input-group-text">
          <i class="fa-solid fa-search"></i>
        </span>
        <input type="text" class="form-control" name="search" placeholder="Cari nomor agenda, nomor SPP, atau uraian..." value="{{ request('search') }}">
      </div>

      <select name="bidang" class="form-select me-3" style="width: 200px;">
        <option value="">Semua Bidang</option>
        <option value="DPM" {{ $selectedBidang == 'DPM' ? 'selected' : '' }}>DPM - Divisi Produksi dan Manufaktur</option>
        <option value="SKH" {{ $selectedBidang == 'SKH' ? 'selected' : '' }}>SKH - Sub Kontrak Hutan</option>
        <option value="SDM" {{ $selectedBidang == 'SDM' ? 'selected' : '' }}>SDM - Sumber Daya Manusia</option>
        <option value="TEP" {{ $selectedBidang == 'TEP' ? 'selected' : '' }}>TEP - Teknik dan Perencanaan</option>
        <option value="KPL" {{ $selectedBidang == 'KPL' ? 'selected' : '' }}>KPL - Keuangan dan Pelaporan</option>
        <option value="AKN" {{ $selectedBidang == 'AKN' ? 'selected' : '' }}>AKN - Akuntansi</option>
        <option value="TAN" {{ $selectedBidang == 'TAN' ? 'selected' : '' }}>TAN - Tanaman dan Perkebunan</option>
        <option value="PMO" {{ $selectedBidang == 'PMO' ? 'selected' : '' }}>PMO - Project Management Office</option>
      </select>

      <button type="submit" class="btn btn-primary">
        <i class="fa-solid fa-filter me-2"></i>Filter
      </button>
    </form>
  </div>

  <!-- Documents Table -->
  <div class="table-responsive">
    <div class="table-dokumen">
      @if($dokumens->count() > 0)
        <table class="table">
          <thead>
            <tr>
              <th style="width: 50px;">No</th>
              <th>Nomor Agenda</th>
              <th>Nomor SPP</th>
              <th>Uraian</th>
              <th>Nilai</th>
              <th>Bidang Tujuan</th>
              <th>Tanggal Return</th>
              <th>Alasan</th>
              <th style="width: 200px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach($dokumens as $index => $dokumen)
            <tr class="main-row" onclick="toggleDetail({{ $dokumen->id }})">
              <td style="text-align: center;">{{ $dokumens->firstItem() + $index }}</td>
              <td>
                <strong>{{ $dokumen->nomor_agenda }}</strong>
                <br>
                <small class="text-muted">{{ $dokumen->bulan }} {{ $dokumen->tahun }}</small>
              </td>
              <td>{{ $dokumen->nomor_spp }}</td>
              <td>{{ Str::limit($dokumen->uraian_spp ?? '-', 50) }}</td>
              <td>
                <strong>{{ $dokumen->formatted_nilai_rupiah }}</strong>
              </td>
              <td>
                <span class="bidang-badge {{ $dokumen->target_bidang }}">
                  {{ $dokumen->target_bidang }}
                </span>
              </td>
              <td>
                <small>{{ $dokumen->bidang_returned_at ? $dokumen->bidang_returned_at->format('d/m/Y H:i') : '-' }}</small>
              </td>
              <td>
                <div class="bidang-info" style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $dokumen->bidang_return_reason ?? '-' }}">
                  {{ Str::limit($dokumen->bidang_return_reason ?? '-', 30) }}
                </div>
              </td>
              <td onclick="event.stopPropagation()">
                <div class="action-buttons">
                  <a href="{{ route('dokumensB.edit', $dokumen->id) }}" class="btn-action btn-edit" title="Edit Dokumen">
                    <i class="fa-solid fa-pen"></i>
                    <span>Edit</span>
                  </a>
                  <button type="button" class="btn-action btn-send" onclick="sendBackToMainList({{ $dokumen->id }})" title="Kirim ke Daftar Utama">
                    <i class="fa-solid fa-undo"></i>
                    <span>Kembali</span>
                  </button>
                </div>
              </td>
            </tr>
            <tr class="detail-row" id="detail-{{ $dokumen->id }}" style="display: none;">
              <td colspan="9">
                <div class="detail-content" id="detail-content-{{ $dokumen->id }}">
                  <div class="text-center p-4">
                    <i class="fa-solid fa-spinner fa-spin me-2"></i> Loading detail...
                  </div>
                </div>
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="text-muted">
          Menampilkan {{ $dokumens->firstItem() }} - {{ $dokumens->lastItem() }} dari total {{ $dokumens->total() }} dokumen
        </div>
        {{ $dokumens->links() }}
      </div>
      @else
      <div class="empty-state">
        <i class="fa-solid fa-building"></i>
        <h5>Belum ada dokumen</h5>
        <p class="mt-2">Tidak ada dokumen yang dikembalikan ke bagian saat ini.</p>
        <a href="{{ route('documents.verifikasi.index') }}" class="btn btn-primary mt-3">
          <i class="fa-solid fa-arrow-left me-2"></i>Kembali ke Daftar Dokumen
        </a>
      </div>
      @endif
    </div>
  </div>
</div>

<!-- Modal for Send to Target Department -->
<div class="modal fade" id="sendToTargetDepartmentModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
        <h5 class="modal-title">
          <i class="fa-solid fa-paper-plane me-2"></i>Kirim Dokumen ke Bagian
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="sendToDepartmentForm">
          <input type="hidden" id="send-dept-doc-id" name="doc_id">

          <div class="mb-3">
            <label class="form-label">
              <i class="fa-solid fa-sitemap me-1"></i>Bagian Tujuan
            </label>
            <input type="text" class="form-control" id="send-dept-target" readonly>
            <div class="form-text">Dokumen akan dikirim ke bagian yang telah ditentukan.</div>
          </div>

          <div class="mb-3">
            <label for="deadline_days" class="form-label">
              <i class="fa-solid fa-clock me-1"></i>Deadline (Opsional)
            </label>
            <select class="form-select" id="deadline_days" name="deadline_days">
              <option value="">Tidak ada deadline</option>
              <option value="1">1 hari</option>
              <option value="3">3 hari</option>
              <option value="7">7 hari</option>
              <option value="14">14 hari</option>
              <option value="30">30 hari</option>
            </select>
            <div class="form-text">Set deadline untuk dokumen di bagian tujuan.</div>
          </div>

          <div class="mb-3">
            <label for="deadline_note" class="form-label">
              <i class="fa-solid fa-sticky-note me-1"></i>Catatan Deadline
            </label>
            <textarea class="form-control" id="deadline_note" name="deadline_note" rows="3"
                      placeholder="Tambahkan catatan untuk deadline..."></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="fa-solid fa-times me-1"></i>Batal
        </button>
        <button type="button" class="btn btn-success" id="submit-send-dept">
          <i class="fa-solid fa-paper-plane me-1"></i>Kirim Dokumen
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// Toggle detail row
function toggleDetail(docId) {
  const detailRow = document.getElementById('detail-' + docId);
  const chevron = document.getElementById('chevron-' + docId);

  if (detailRow.style.display === 'none' || !detailRow.style.display) {
    // Show detail
    loadDocumentDetail(docId);
    detailRow.style.display = 'table-row';
    if (chevron) chevron.classList.add('rotate');
  } else {
    // Hide detail
    detailRow.style.display = 'none';
    if (chevron) chevron.classList.remove('rotate');
  }
}

// Load document detail
function loadDocumentDetail(docId) {
  const detailContent = document.getElementById('detail-content-' + docId);

  // Show loading state
  detailContent.innerHTML = `
    <div class="text-center p-4">
      <i class="fa-solid fa-spinner fa-spin me-2"></i> Loading detail...
    </div>
  `;

  fetch(`/dokumens/${docId}/detail`)
    .then(response => response.text())
    .then(html => {
      detailContent.innerHTML = html;
    })
    .catch(error => {
      console.error('Error:', error);
      detailContent.innerHTML = `
        <div class="text-center p-4 text-danger">
          <i class="fa-solid fa-exclamation-triangle me-2"></i> Gagal memuat detail dokumen.
        </div>
      `;
    });
}

// Available documents data (passed from PHP to JavaScript)
const documentsData = @json($dokumens->keyBy('id'));

// Send back to main list
function sendBackToMainList(docId) {
  if (confirm('Apakah Anda yakin ingin mengirim dokumen ini kembali ke daftar utama?')) {
    fetch(`/dokumensB/${docId}/send-back-to-main-list`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showNotification(data.message, 'success');
        setTimeout(() => {
          window.location.reload();
        }, 1500);
      } else {
        alert(data.message || 'Gagal mengirim dokumen kembali ke daftar utama.');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Terjadi kesalahan saat mengirim dokumen kembali ke daftar utama.');
    });
  }
}

// Submit send to department
document.getElementById('submit-send-dept').addEventListener('click', function() {
  const docId = document.getElementById('send-dept-doc-id').value;
  const deadlineDays = document.getElementById('deadline_days').value;
  const deadlineNote = document.getElementById('deadline_note').value;

  const submitBtn = this;
  const originalText = this.innerHTML;

  // Show loading state
  submitBtn.disabled = true;
  submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Mengirim...';

  const formData = {
    deadline_days: deadlineDays || null,
    deadline_note: deadlineNote || null
  };

  fetch(`/dokumensB/${docId}/send-to-target-department`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify(formData)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      // Close modal
      const modal = bootstrap.Modal.getInstance(document.getElementById('sendToTargetDepartmentModal'));
      modal.hide();

      // Show success message
      showNotification(data.message, 'success');

      // Reload page after 2 seconds
      setTimeout(() => {
        location.reload();
      }, 2000);
    } else {
      alert(data.message || 'Gagal mengirim dokumen.');
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Terjadi kesalahan saat mengirim dokumen.');
  })
  .finally(() => {
    // Restore button
    submitBtn.disabled = false;
    submitBtn.innerHTML = originalText;
  });
});

// Notification function
function showNotification(message, type = 'info') {
  const notification = document.createElement('div');
  notification.className = `notification notification-${type}`;
  notification.innerHTML = `
    <div class="notification-content">
      <i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-info-circle'}"></i>
      <span>${message}</span>
    </div>
  `;

  document.body.appendChild(notification);

  // Trigger animation
  setTimeout(() => {
    notification.classList.add('show');
  }, 10);

  // Auto-hide untuk notifikasi success/error biasa setelah 4 detik
  // Notifikasi dokumen masuk/reject tetap permanen
  if (type === 'success' || type === 'error') {
    setTimeout(() => {
      notification.classList.remove('show');
      setTimeout(() => {
        if (notification.parentElement) {
          notification.parentElement.removeChild(notification);
        }
      }, 300);
    }, 4000); // 4 detik untuk notifikasi success/error biasa
  }
  // Jika type info atau dokumen masuk/reject, tetap permanen
}

// Auto-refresh notification badge
function updateNotificationBadge() {
  fetch('/pengembalian-dokumens-ke-bagian/stats')
    .then(response => response.json())
    .then(data => {
      const badge = document.getElementById('pengembalian-ke-bagian-badge');
      if (badge && data.total > 0) {
        badge.textContent = data.total;
        badge.style.display = 'inline-flex';
      } else if (badge) {
        badge.style.display = 'none';
      }
    })
    .catch(error => console.log('Error updating badge:', error));
}

// Update badge on page load
document.addEventListener('DOMContentLoaded', function() {
  updateNotificationBadge();

  // Update badge every 30 seconds
  setInterval(updateNotificationBadge, 30000);
});
</script>

@endsection




