@extends('layouts/app')
@section('content')

<style>
 h2 {
    background: linear-gradient(135deg, #083E40 0%, #889717 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .search-box {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    padding: 20px;
    border-radius: 16px;
    margin-bottom: 20px;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
    border: 1px solid rgba(8, 62, 64, 0.08);
  }

  .search-box .input-group {
    max-width: auto;
  }

  .search-box .input-group-text {
    background: white;
    border: 2px solid rgba(8, 62, 64, 0.1);
    border-right: none;
    border-radius: 10px 0 0 10px;
    padding: 10px 14px;
  }

  .search-box .form-control {
    border: 2px solid rgba(8, 62, 64, 0.1);
    border-left: none;
    border-radius: 0 10px 10px 0;
    padding: 10px 14px;
    font-size: 13px;
    transition: all 0.3s ease;
  }

  .search-box .form-control:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 4px rgba(136, 151, 23, 0.1);
  }

  /* Filter Dropdown */
  .filter-dropdown {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
  }

  .filter-label {
    font-weight: 600;
    color: #083E40;
    font-size: 13px;
    margin: 0;
    white-space: nowrap;
  }

  .dropdown-filter-modern {
    position: relative;
    min-width: 200px;
  }

  .dropdown-toggle-modern {
    width: 100%;
    padding: 10px 16px;
    padding-right: 40px;
    border: 2px solid rgba(8, 62, 64, 0.15);
    background: white;
    color: #083E40;
    font-size: 13px;
    font-weight: 600;
    border-radius: 10px;
    transition: all 0.3s ease;
    cursor: pointer;
    text-align: left;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 2px 8px rgba(8, 62, 64, 0.05);
  }

  .dropdown-toggle-modern:hover {
    border-color: #889717;
    box-shadow: 0 4px 12px rgba(136, 151, 23, 0.1);
    transform: translateY(-1px);
  }

  .dropdown-toggle-modern:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 4px rgba(136, 151, 23, 0.1);
  }

  .dropdown-toggle-modern::after {
    content: '\f078';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    transition: all 0.3s ease;
    font-size: 12px;
    color: #083E40;
  }

  .dropdown-toggle-modern.show::after {
    transform: translateY(-50%) rotate(180deg);
  }

  .dropdown-menu-modern {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 2px solid rgba(8, 62, 64, 0.1);
    border-radius: 10px;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.15);
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    margin-top: 8px;
    overflow: hidden;
  }

  .dropdown-menu-modern.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
  }

  .dropdown-item-modern {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    color: #083E40;
    text-decoration: none;
    font-size: 13px;
    font-weight: 500;
    transition: all 0.2s ease;
    border-bottom: 1px solid rgba(8, 62, 64, 0.05);
    cursor: pointer;
  }

  .dropdown-item-modern:last-child {
    border-bottom: none;
  }

  .dropdown-item-modern:hover {
    background: linear-gradient(135deg, rgba(136, 151, 23, 0.08) 0%, rgba(136, 151, 23, 0.04) 100%);
    color: #083E40;
    padding-left: 20px;
  }

  .dropdown-item-modern.active {
    background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
    color: white;
    font-weight: 600;
  }

  .dropdown-item-modern i {
    width: 16px;
    text-align: center;
    font-size: 12px;
  }

  /* Column Customization Button */
  .btn-customize-columns-inline {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border: 2px solid rgba(8, 62, 64, 0.15);
    background: white;
    color: #083E40;
    font-size: 13px;
    font-weight: 600;
    border-radius: 10px;
    transition: all 0.3s ease;
    text-decoration: none;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(8, 62, 64, 0.05);
  }

  .btn-customize-columns-inline:hover {
    border-color: #889717;
    background: linear-gradient(135deg, rgba(136, 151, 23, 0.08) 0%, rgba(136, 151, 23, 0.04) 100%);
    color: #083E40;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(136, 151, 23, 0.1);
  }

  .btn-customize-columns-inline:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 4px rgba(136, 151, 23, 0.1);
  }

  .btn-customize-columns-inline:active {
    transform: translateY(0);
    box-shadow: 0 2px 8px rgba(8, 62, 64, 0.1);
  }

  @media (max-width:768px) {
    .filter-dropdown {
      flex-direction: column;
      align-items: stretch;
      gap: 8px;
    }

    .filter-label {
      text-align: center;
    }

    .dropdown-filter-modern {
      min-width: 100%;
    }

    .btn-customize-columns-inline {
      width: 100%;
      justify-content: center;
    }
  }

  /* Table Container */
  .table-dokumen {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
    border: 1px solid rgba(8, 62, 64, 0.08);
    position: relative;
    overflow: hidden;
  }

  .table-responsive {
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: rgba(8, 62, 64, 0.3) transparent;
  }

  .table-responsive::-webkit-scrollbar {
    height: 12px;
  }

  .table-responsive::-webkit-scrollbar-track {
    background: rgba(8, 62, 64, 0.05);
    border-radius: 6px;
    margin: 0 20px;
  }

  .table-responsive::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, rgba(8, 62, 64, 0.3), rgba(136, 151, 23, 0.4));
    border-radius: 6px;
    border: 2px solid rgba(255, 255, 255, 0.8);
  }

  .table-enhanced {
    border-collapse: separate;
    border-spacing: 0;
    min-width: 1400px;
    width: 100%;
  }

  .table-enhanced thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 16px 12px;
    border: none;
    white-space: nowrap;
  }

  .table-enhanced tbody tr {
    transition: all 0.2s ease;
    border-left: 3px solid transparent;
    background: white;
  }

  .table-enhanced tbody tr:hover {
    background: linear-gradient(135deg, rgba(8, 62, 64, 0.02) 0%, rgba(136, 151, 23, 0.02) 100%);
  }

  .table-enhanced tbody tr.locked-row {
    background: linear-gradient(135deg, #f8f9fa 0%, #eef3f3 100%);
    border-left-color: #ffc107;
    position: relative;
  }

  .table-enhanced tbody tr.locked-row::before {
    content: '🔒';
    position: absolute;
    left: 8px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 16px;
    opacity: 0.6;
  }

  .table-enhanced tbody td {
    padding: 14px 12px;
    border-bottom: 1px solid rgba(8, 62, 64, 0.08);
    vertical-align: middle;
  }

  .table-enhanced tbody tr:last-child td {
    border-bottom: none;
  }

  .table-enhanced tbody tr.selected {
    background: linear-gradient(135deg, rgba(136, 151, 23, 0.08) 0%, rgba(136, 151, 23, 0.04) 100%);
    border-left-color: #889717;
  }

  /* Action Buttons */
  .action-buttons {
    display: flex;
    gap: 8px;
    align-items: center;
  }

  .btn-action {
    padding: 6px 12px;
    border: 1px solid rgba(8, 62, 64, 0.2);
    background: white;
    color: #083E40;
    font-size: 11px;
    font-weight: 500;
    border-radius: 6px;
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
  }

  .btn-action:hover {
    background: linear-gradient(135deg, rgba(136, 151, 23, 0.1) 0%, rgba(136, 151, 23, 0.05) 100%);
    border-color: #889717;
    color: #083E40;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(136, 151, 23, 0.2);
  }

  .btn-action:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 3px rgba(136, 151, 23, 0.1);
  }

  .btn-action i {
    font-size: 12px;
    width: 12px;
    text-align: center;
  }

  /* Status Buttons */
  .status-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
  }

  .status-button {
    padding: 6px 12px;
    border: 1px solid rgba(8, 62, 64, 0.2);
    background: white;
    color: #083E40;
    font-size: 11px;
    font-weight: 500;
    border-radius: 6px;
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
  }

  .status-button:hover {
    background: linear-gradient(135deg, rgba(136, 151, 23, 0.1) 0%, rgba(136, 151, 23, 0.05) 100%);
    border-color: #889717;
    color: #083E40;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(136, 151, 23, 0.2);
  }

  .status-button:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 3px rgba(136, 151, 23, 0.1);
  }

  .status-button.active {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    border-color: #083E40;
    box-shadow: 0 2px 8px rgba(8, 62, 64, 0.3);
  }

  .status-button.active:hover {
    background: linear-gradient(135deg, #0a4f52 0%, #083E40 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.4);
  }

  .status-button i {
    font-size: 12px;
    width: 12px;
    text-align: center;
  }

  /* Modal for Deadline Setting */
  .modal-content {
    background: white;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(8, 62, 64, 0.3);
    border: none;
    max-width: 500px;
    width: 90%;
    margin: 0 auto;
  }

  .modal-header {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    padding: 20px;
    border-radius: 16px 16px 0 0;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .modal-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
  }

  .modal-body {
    padding: 30px;
  }

  .modal-footer {
    padding: 20px;
    background: #f8faf8;
    border-radius: 0 0 16px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
  }

  .form-group {
    margin-bottom: 20px;
  }

  .form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #083E40;
  }

  .form-group textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid rgba(8, 62, 64, 0.1);
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s ease;
    resize: vertical;
  }

  .form-group textarea:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 4px rgba(136, 151, 23, 0.1);
  }

  .btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .btn-primary {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
  }

  .btn-primary:hover {
    background: linear-gradient(135deg, #0a4f52 0%, #083E40 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
  }

  .btn-secondary {
    background: white;
    color: #083E40;
    border: 2px solid rgba(8, 62, 64, 0.2);
  }

  .btn-secondary:hover {
    background: #f8faf8;
    border-color: #889717;
  }

  /* Status Badge */
  .status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-block;
  }

  .status-badge.belum-diproses {
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
    color: white;
  }

  .status-badge.sedang-diproses {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
  }

  .status-badge.selesai {
    background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
    color: white;
  }

  .status-badge.siap-dibayar {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
  }

  .status-badge.sudah-dibayar {
    background: linear-gradient(135deg, #198754 0%, #157347 100%);
    color: white;
  }

  @media (max-width: 768px) {
    .search-box {
      padding: 15px;
    }

    .table-dokumen {
      padding: 20px;
    }

    .table-enhanced {
      min-width: 1200px;
    }

    .action-buttons {
      flex-direction: column;
      align-items: stretch;
    }

    .modal-content {
      width: 95%;
    }
  }
</style>

<h2>{{ $title }}</h2>

<!-- Search Box -->
<div class="search-box">
  <div class="row g-3">
    <div class="col-md-4">
      <div class="input-group">
        <span class="input-group-text">
          <i class="fa-solid fa-magnifying-glass text-muted"></i>
        </span>
        <input type="text" id="pembayaranSearchInput" class="form-control" placeholder="Cari dokumen pembayaran...">
      </div>
    </div>
    <div class="col-md-4">
      <div class="filter-dropdown">
        <label class="filter-label">
          <i class="fa-solid fa-filter me-2"></i>Filter Status:
        </label>
        <div class="dropdown-filter-modern">
          <button class="dropdown-toggle-modern" type="button" id="statusFilterDropdown">
            <i class="fa-solid fa-filter-circle-dot"></i>
            <span id="filterText">Semua Dokumen</span>
          </button>
          <div class="dropdown-menu-modern" id="statusFilterMenu">
            <a href="#" class="dropdown-item-modern {{ !$statusFilter ? 'active' : '' }}" data-filter="">
              <i class="fa-solid fa-list"></i>
              <span>Semua Dokumen</span>
            </a>
            <a href="#" class="dropdown-item-modern {{ $statusFilter === 'belum_siap_dibayar' ? 'active' : '' }}" data-filter="belum_siap_dibayar">
              <i class="fa-solid fa-clock"></i>
              <span>Belum Siap Bayar</span>
            </a>
            <a href="#" class="dropdown-item-modern {{ $statusFilter === 'siap_dibayar' ? 'active' : '' }}" data-filter="siap_dibayar">
              <i class="fa-solid fa-check-circle"></i>
              <span>Siap Dibayar</span>
            </a>
            <a href="#" class="dropdown-item-modern {{ $statusFilter === 'sudah_dibayar' ? 'active' : '' }}" data-filter="sudah_dibayar">
              <i class="fa-solid fa-check-double"></i>
              <span>Sudah Dibayar</span>
            </a>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-4 text-end">
      <button type="button" class="btn-customize-columns-inline" onclick="openColumnCustomizationModal()">
        <i class="fa-solid fa-table-columns me-2"></i>Kustomisasi Kolom
      </button>
    </div>
  </div>
</div>

<!-- Tabel Dokumen -->
<div class="table-dokumen">
  <div class="table-responsive">
    <table class="table table-enhanced mb-0">
      <thead>
        <tr>
          <th class="col-no sticky-column">No</th>
          <th class="col-agenda sticky-column">Nomor Agenda</th>
          <th class="col-tanggal sticky-column">Tanggal Masuk</th>
          <th class="col-spp sticky-column">No SPP</th>
          <th class="col-uraian sticky-column">Uraian</th>
          <th class="col-nilai sticky-column">Nilai Rupiah</th>
          <th class="col-tanggal-spp sticky-column">Tanggal SPP</th>
          <th class="col-status sticky-column">Status</th>
          <th class="col-deadline sticky-column">Deadline</th>
          <th class="col-action sticky-column">Aksi</th>
        </tr>
      </thead>
      <tbody>
      @forelse($dokumens as $index => $dokumen)
        @php
          // Handler yang dianggap "belum siap dibayar"
          $belumSiapHandlers = ['akuntansi', 'perpajakan', 'ibu_a', 'ibu_b'];
        @endphp
        <tr class="{{ in_array($dokumen->current_handler, $belumSiapHandlers) ? 'locked-row' : '' }}" data-dokumen-id="{{ $dokumen->id }}">
          <td class="col-no">{{ $index + 1 }}</td>
          <td class="col-agenda">{{ $dokumen->nomor_agenda }}</td>
          <td class="col-tanggal">{{ $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d/m/Y H:i') : '-' }}</td>
          <td class="col-spp">{{ $dokumen->nomor_spp }}</td>
          <td class="col-uraian">{{ $dokumen->uraian_spp }}</td>
          <td class="col-nilai">{{ number_format($dokumen->nilai_rupiah, 0, ',', '.') }}</td>
          <td class="col-tanggal-spp">{{ $dokumen->tanggal_spp ? $dokumen->tanggal_spp->format('d/m/Y') : '-' }}</td>
          <td class="col-status">
            @switch($dokumen->status_pembayaran) {
              case 'siap_dibayar':
                <span class="status-badge siap-dibayar">Siap Dibayar</span>
                break;
              case 'sudah_dibayar':
                <span class="status-badge sudah-dibayar">Sudah Dibayar</span>
                break;
              default:
                <span class="status-badge belum-diproses">Belum Siap Bayar</span>
                break;
            }
          </td>
          <td class="col-deadline">
            @if($dokumen->deadline_at)
              {{ $dokumen->deadline_at->format('d/m/Y') }}
              <br><small class="text-muted">{{ $dokumen->deadline_at->format('H:i') }}</small>
            @else
              -
            @endif
          </td>
          <td class="col-action">
            <div class="action-buttons">
              @if(in_array($dokumen->current_handler, ['pembayaran']) && $dokumen->status_pembayaran !== 'sudah_dibayar')
                <button type="button" class="btn-action" onclick="setDeadline({{ $dokumen->id }})">
                  <i class="fa-solid fa-calendar-days"></i>
                  Set Deadline
                </button>
              @endif

              @if($dokumen->status_pembayaran === 'siap_dibayar')
                <button type="button" class="btn-action" onclick="updatePaymentStatus({{ $dokumen->id }}, 'sudah_dibayar')">
                  <i class="fa-solid fa-check"></i>
                  Sudah Dibayar
                </button>
              @endif

              @if($dokumen->status_pembayaran === 'sudah_dibayar')
                <button type="button" class="btn-action" onclick="uploadPaymentProof({{ $dokumen->id }})">
                  <i class="fa-solid fa-upload"></i>
                  Upload Bukti
                </button>
              @endif
            </div>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="9" class="text-center text-muted py-5">
            <i class="fa-solid fa-inbox fa-3x mb-3"></i>
            <h5>Belum ada dokumen pembayaran</h5>
            <p class="mb-0">Dokumen yang telah dikirim ke pembayaran akan muncul di sini.</p>
          </td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>

<!-- Modal untuk Set Deadline -->
<div class="modal fade" id="setDeadlineModal" tabindex="-1">
  <div class="modal-content">
    <div class="modal-header">
      <h3><i class="fa-solid fa-calendar-days me-2"></i>Set Deadline Pembayaran</h3>
    </div>
    <div class="modal-body">
      <form id="deadlineForm">
        <div class="form-group">
          <label for="deadline_days">Periode Deadline</label>
          <select name="deadline_days" id="deadline_days" class="form-control" required>
            <option value="">Pilih periode</option>
            <option value="3">3 hari</option>
            <option value="5">5 hari</option>
            <option value="7">7 hari</option>
            <option value="14">14 hari</option>
            <option value="21">21 hari</option>
            <option value="30">30 hari</option>
          </select>
        </div>
        <div class="form-group">
          <label for="deadline_note">Catatan (Opsional)</label>
          <textarea name="deadline_note" id="deadline_note" class="form-control" rows="3" placeholder="Masukkan catatan tambahan..."></textarea>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      <button type="button" class="btn btn-primary" onclick="submitDeadline({{ $dokumen->id }})">Tetapkan</button>
    </div>
  </div>
</div>

<!-- Column Customization Modal -->
<div class="customization-modal" id="columnCustomizationModal">
  <div class="modal-content-custom">
    <div class="modal-header-custom">
      <i class="fa-solid fa-table-columns"></i>
      <h3>Kostumisasi Kolom Tabel</h3>
    </div>
    <div class="modal-body-custom">
      <div class="customization-grid">
        <p>Pilih kolom yang ingin ditampilkan pada tabel. Urutan akan mengikuti urutan pilihan Anda.</p>
        <div class="column-selection-list" id="columnSelectionList">
          <!-- Kolom-kolom akan di-generate oleh JavaScript -->
        </div>
      </div>
    </div>
    <div class="modal-footer-custom">
      <div class="d-flex justify-content-between align-items-center">
        <span class="text-muted">
          <small>Pilih minimal 1 kolom untuk melihat preview</small>
        </span>
        <div>
          <button type="button" class="btn btn-sm btn-secondary me-2" onclick="closeColumnCustomizationModal()">Batal</button>
          <button type="button" class="btn btn-sm btn-primary" onclick="applyColumnCustomization()">Terapkan</button>
        </div>
      </div>
      <div class="mt-2">
        <strong id="selectedColumnCount">0</strong> kolom dipilih
      </div>
    </div>
  </div>
</div>

<!-- Column Customization CSS -->
<style>
.customization-modal {
  display: none;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  z-index: 9999;
  animation: fadeIn 0.3s ease;
}

.customization-modal.show {
  display: flex;
  align-items: center;
  justify-content: center;
}

.modal-content-custom {
  background: white;
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(8, 62, 64, 0.3);
  max-width: 600px;
  width: 90%;
  max-height: 80vh;
  overflow: hidden;
  animation: slideUp 0.3s ease;
}

.modal-header-custom {
  background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
  color: white;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 12px;
}

.modal-header-custom h3 {
  margin: 0;
  font-size: 18px;
  font-weight: 600;
}

.modal-body-custom {
  padding: 20px;
  max-height: 60vh;
  overflow-y: auto;
}

.customization-grid {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.column-selection-list {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
  padding: 16px 0;
}

.column-item {
  background: white;
  border: 2px solid rgba(8, 62, 64, 0.1);
  border-radius: 10px;
  padding: 16px;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
}

.column-item:hover {
  border-color: #889717;
  box-shadow: 0 4px 12px rgba(136, 151, 23, 0.15);
  transform: translateY(-2px);
}

.column-item.selected {
  background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
  color: white;
  border-color: #083E40;
  box-shadow: 0 4px 12px rgba(8, 62, 64, 0.25);
}

.column-item.dragging {
  opacity: 0.6;
  transform: scale(0.98);
}

.column-item-checkbox {
  width: 20px;
  height: 20px;
  margin-right: 12px;
  cursor: pointer;
}

.column-item-label {
  flex: 1;
  font-weight: 500;
  user-select: none;
}

.column-item-order {
  font-size: 12px;
  color: #083E40;
  font-weight: 600;
  background: rgba(8, 62, 64, 0.1);
  padding: 4px 8px;
  border-radius: 6px;
  min-width: 24px;
  text-align: center;
}

.column-item.selected .column-item-order {
  background: rgba(255, 255, 255, 0.2);
}

.modal-footer-custom {
  padding: 20px;
  background: #f8faf8;
  border-top: 1px solid rgba(8, 62, 64, 0.08);
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from {
    transform: translateY(50px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

@media (max-width: 768px) {
  .modal-content-custom {
    width: 95%;
    margin: 20px;
  }

  .column-selection-list {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .column-selection-list {
    grid-template-columns: 1fr;
  }
}
</style>

<!-- Column Customization JavaScript -->
<script>
// Global variables for column customization
const pembayaranAvailableColumns = {
    'nomor_agenda': 'Nomor Agenda',
    'tanggal_masuk': 'Tanggal Masuk',
    'nomor_spp': 'No SPP',
    'uraian_spp': 'Uraian SPP',
    'nilai_rupiah': 'Nilai Rupiah',
    'tanggal_spp': 'Tanggal SPP',
    'status_pembayaran': 'Status Pembayaran',
    'deadline': 'Deadline',
    'aksi': 'Aksi'
};

let selectedPembayaranColumnsOrder = ['nomor_agenda', 'tanggal_masuk', 'nomor_spp', 'uraian_spp', 'nilai_rupiah', 'status_pembayaran', 'deadline', 'aksi'];

// Load saved columns from localStorage
const savedColumns = localStorage.getItem('pembayaranSelectedColumns');
if (savedColumns) {
    selectedPembayaranColumnsOrder = JSON.parse(savedColumns);
}

function openColumnCustomizationModal() {
    const modal = document.getElementById('columnCustomizationModal');
    modal.classList.add('show');
    generateColumnSelection();
}

function closeColumnCustomizationModal() {
    const modal = document.getElementById('columnCustomizationModal');
    modal.classList.remove('show');
}

function generateColumnSelection() {
    const columnList = document.getElementById('columnSelectionList');
    columnList.innerHTML = '';

    Object.keys(pembayaranAvailableColumns).forEach((key, index) => {
        const isSelected = selectedPembayaranColumnsOrder.includes(key);
        const order = selectedPembayaranColumnsOrder.indexOf(key) + 1;

        const columnItem = document.createElement('div');
        columnItem.className = `column-item ${isSelected ? 'selected' : ''}`;
        columnItem.setAttribute('data-column', key);
        columnItem.setAttribute('draggable', 'true');

        columnItem.innerHTML = `
            <div class="d-flex align-items-center">
                <input type="checkbox" class="column-item-checkbox"
                       ${isSelected ? 'checked' : ''}
                       onchange="togglePembayaranColumn(this)"
                       data-column="${key}">
                <label class="column-item-label">${pembayaranAvailableColumns[key]}</label>
                <span class="column-item-order">${order}</span>
            </div>
        `;

        columnList.appendChild(columnItem);
    });

    updateSelectedColumnCount();
}

function togglePembayaranColumn(checkbox) {
    const columnKey = checkbox.getAttribute('data-column');
    const columnElement = checkbox.closest('.column-item');

    if (!selectedPembayaranColumnsOrder.includes(columnKey)) {
        selectedPembayaranColumnsOrder.push(columnKey);
        columnElement.classList.add('selected');
        columnElement.setAttribute('draggable', 'true');
    } else {
        selectedPembayaranColumnsOrder = selectedPembayaranColumnsOrder.filter(key => key !== columnKey);
        columnElement.classList.remove('selected');
        columnElement.setAttribute('draggable', 'false');
    }

    updateSelectedColumnCount();
}

function updateSelectedColumnCount() {
    const count = selectedPembayaranColumnsOrder.length;
    document.getElementById('selectedColumnCount').textContent = count;
}

function applyColumnCustomization() {
    if (selectedPembayaranColumnsOrder.length === 0) {
        alert('Silakan pilih minimal 1 kolom untuk ditampilkan.');
        return;
    }

    // Save to localStorage
    localStorage.setItem('pembayaranSelectedColumns', JSON.stringify(selectedPembayaranColumnsOrder));

    // Apply columns to table
    applyColumnsToTable();

    // Close modal
    closeColumnCustomizationModal();

    // Show success message
    alert('Kustomisasi kolom berhasil diterapkan!');
}

function applyColumnsToTable() {
    const tableHeaders = document.querySelectorAll('.table-enhanced thead th');
    const tableRows = document.querySelectorAll('.table-enhanced tbody tr');

    // Hide all columns first
    tableHeaders.forEach(header => {
        header.style.display = 'none';
    });

    tableRows.forEach(row => {
        const cells = row.querySelectorAll('td');
        cells.forEach(cell => {
            cell.style.display = 'none';
        });
    });

    // Show selected columns in order
    selectedPembayaranColumnsOrder.forEach((columnKey, index) => {
        // Show header
        const headerSelector = `.table-enhanced thead th[data-column="${columnKey}"]`;
        const header = document.querySelector(headerSelector);
        if (header) {
            header.style.display = '';
            header.style.order = index;
        }

        // Show cells
        const cellSelector = `.table-enhanced tbody td[data-column="${columnKey}"]`;
        const cells = document.querySelectorAll(cellSelector);
        cells.forEach(cell => {
            cell.style.display = '';
            cell.style.order = index;
        });
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    applyColumnsToTable();
});
</script>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropdownToggle = document.getElementById('statusFilterDropdown');
    const dropdownMenu = document.getElementById('statusFilterMenu');
    const filterText = document.getElementById('filterText');
    const dropdownItems = document.querySelectorAll('.dropdown-item-modern');
    const currentFilter = '{{ $statusFilter }}';

    // Set initial active state
    if (currentFilter) {
        updateActiveFilter(currentFilter);
    }

    // Toggle dropdown
    dropdownToggle.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const isOpen = dropdownMenu.classList.contains('show');

        if (isOpen) {
            closeDropdown();
        } else {
            openDropdown();
        }
    });

    // Handle dropdown item clicks
    dropdownItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();

            const filter = this.getAttribute('data-filter');

            // Auto-navigate to filter URL
            const url = new URL(window.location);
            url.searchParams.set('status_filter', filter);
            window.location.href = url.toString();
        });
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!dropdownToggle.contains(e.target) && !dropdownMenu.contains(e.target)) {
            closeDropdown();
        }
    });

    function openDropdown() {
        dropdownMenu.classList.add('show');
        dropdownToggle.classList.add('show');
        dropdownToggle.setAttribute('aria-expanded', 'true');
    }

    function closeDropdown() {
        dropdownMenu.classList.remove('show');
        dropdownToggle.classList.remove('show');
        dropdownToggle.setAttribute('aria-expanded', 'false');
    }

    function updateActiveFilter(filter) {
        // Update dropdown text
        const texts = {
            '': 'Semua Dokumen',
            'belum_siap_dibayar': 'Belum Siap Bayar',
            'siap_dibayar': 'Siap Dibayar',
            'sudah_dibayar': 'Sudah Dibayar'
        };

        filterText.textContent = texts[filter] || 'Semua Dokumen';

        // Update active states
        dropdownItems.forEach(item => {
            const itemFilter = item.getAttribute('data-filter');
            if (itemFilter === filter) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    }
});

function setDeadline(docId) {
    const modal = new bootstrap.Modal(document.getElementById('setDeadlineModal'));
    const submitBtn = modal._element.querySelector('.btn-primary');
    const originalHTML = submitBtn.innerHTML;

    // Set dokumen ID in hidden field
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = 'dokumen_id';
    hiddenInput.value = docId;
    document.getElementById('deadlineForm').appendChild(hiddenInput);

    modal.show();
}

function submitDeadline(docId) {
    const form = document.getElementById('deadlineForm');
    const formData = new FormData(form);
    const submitBtn = document.querySelector('#setDeadlineModal .btn-primary');

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Menetapkan...';

    fetch(`/dokumensPembayaran/${docId}/set-deadline`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            deadline_days: parseInt(formData.get('deadline_days'), 10),
            deadline_note: formData.get('deadline_note')
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('setDeadlineModal'));
            modal.hide();
            alert('Deadline berhasil ditetapkan! Dokumen sekarang siap untuk diproses.');
            location.reload();
        } else {
            alert(data.message || 'Gagal menetapkan deadline.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHTML;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menetapkan deadline.');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHTML;
    });
}

function updatePaymentStatus(docId, status) {
    if (!confirm('Apakah Anda yakin ingin mengubah status pembayaran?')) {
        return;
    }

    fetch(`/dokumensPembayaran/${docId}/update-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            status_pembayaran: status
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Status pembayaran berhasil diperbarui!');
            location.reload();
        } else {
            alert(data.message || 'Gagal memperbarui status pembayaran.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat memperbarui status pembayaran.');
    });
}

function uploadPaymentProof(docId) {
    const proofUrl = prompt('Masukkan link bukti pembayaran:');
    if (!proofUrl) {
        return;
    }

    fetch(`/dokumensPembayaran/${docId}/upload-bukti`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            link_bukti_pembayaran: proofUrl
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Link bukti pembayaran berhasil disimpan!');
            location.reload();
        } else {
            alert(data.message || 'Gagal menyimpan link bukti pembayaran.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan link bukti pembayaran.');
    });
}
</script>

@endsection