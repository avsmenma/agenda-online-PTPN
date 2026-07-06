@extends('layouts/app')
@section('content')

  <style>
    h2 {
      background: linear-gradient(135deg, #083E40 0%, #889717 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    /* Dark mode override for h2 */
    .dark h2 {
      background: none !important;
      -webkit-background-clip: unset !important;
      -webkit-text-fill-color: #ffffff !important;
      background-clip: unset !important;
      color: #ffffff !important;
    }

    .dark h2 i {
      color: #ffffff !important;
    }


    .search-box {
      background: #ffffff;
      padding: 20px;
      border-radius: 12px;
      margin-bottom: 20px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      border: 1px solid #e9ecef;
    }

    .search-filter-form {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
    }

    .search-input-group {
      flex: 1;
      min-width: 250px;
    }

    .search-box .input-group-text {
      background: white;
      border: 1px solid #dee2e6;
      border-right: none;
      border-radius: 8px 0 0 8px;
      padding: 10px 14px;
    }

    .search-box .form-control {
      border: 1px solid #dee2e6;
      border-left: none;
      border-radius: 0 8px 8px 0;
      padding: 10px 14px;
      font-size: 14px;
      transition: all 0.3s ease;
    }

    .search-box .form-control:focus {
      outline: none;
      border-color: #889717;
      box-shadow: 0 0 0 3px rgba(136, 151, 23, 0.1);
    }

    .btn-year-select,
    .btn-status-select {
      padding: 10px 16px;
      background: white;
      color: #495057;
      border: 1px solid #dee2e6;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 500;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      min-height: 44px;
    }

    .btn-year-select:hover,
    .btn-status-select:hover {
      border-color: #889717;
      background: #f8f9fa;
    }

    .btn-filter {
      padding: 10px 20px;
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 2px 6px rgba(8, 62, 64, 0.2);
      min-height: 44px;
    }

    .btn-filter:hover {
      transform: translateY(-1px);
    }

    .btn-customize-columns-inline {
      padding: 10px 20px;
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 2px 6px rgba(8, 62, 64, 0.2);
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      min-height: 44px;
      white-space: nowrap;
    }

    .btn-customize-columns-inline:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
      background: linear-gradient(135deg, #0a4f52 0%, #0c6065 100%);
      color: white;
    }

    .btn-customize-columns-inline:active {
      transform: translateY(0);
      box-shadow: 0 2px 6px rgba(8, 62, 64, 0.2);
    }

    /* Column Customization Modal */
    .customization-modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      z-index: 9999;
      overflow-y: auto;
      padding: 20px;
      box-sizing: border-box;
    }

    .customization-modal.show {
      display: flex;
      align-items: center;
      justify-content: center;
      animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
      }

      to {
        opacity: 1;
      }
    }

    .customization-modal .modal-content-custom {
      background: white;
      border-radius: 20px;
      box-shadow: 0 25px 80px rgba(0, 0, 0, 0.25);
      max-width: 600px;
      width: 100%;
      max-height: 90vh;
      display: flex;
      flex-direction: column;
      overflow: hidden;
    }

    .customization-modal .modal-header-custom {
      background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);
      color: white;
      padding: 20px 24px;
      border-radius: 16px 16px 0 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .customization-modal .modal-header-custom h3 {
      margin: 0;
      font-size: 18px;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .customization-modal .modal-body-custom {
      padding: 24px;
      overflow-y: auto;
    }

    .column-selection-list {
      display: flex;
      flex-direction: column;
      gap: 10px;
      max-height: 400px;
      overflow-y: auto;
      padding: 8px;
      background: #f8f9fa;
      border-radius: 8px;
    }

    .column-item {
      display: flex;
      align-items: center;
      padding: 12px 16px;
      background: #ffffff;
      border-radius: 8px;
      border: 2px solid #e9ecef;
      transition: all 0.2s ease;
      gap: 12px;
    }

    .column-item:hover {
      border-color: #889717;
      background: #f8f9ff;
    }

    .column-item.selected {
      border-color: #28a745;
      background: #f0f9f4;
    }

    .column-item-checkbox {
      width: 20px;
      height: 20px;
      cursor: pointer;
    }

    .column-item-label {
      font-size: 14px;
      color: #212529;
      font-weight: 500;
      flex: 1;
    }

    .customization-modal .modal-footer-custom {
      padding: 16px 24px;
      border-top: 1px solid #e9ecef;
      display: flex;
      justify-content: flex-end;
      gap: 12px;
    }

    .btn-modal {
      padding: 10px 24px;
      border: none;
      border-radius: 8px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .btn-cancel {
      background: #6c757d;
      color: white;
    }

    .btn-cancel:hover {
      background: #5a6268;
    }

    .btn-save {
      background: #28a745;
      color: white;
    }

    .btn-save:hover {
      background: #218838;
      box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
    }

    .table-container {
      background: white;
      border-radius: 16px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      overflow: hidden;
    }

    .table-wrapper {
      overflow-x: auto;
    }

    .data-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0;
    }

    /* ── Tata letak lebar kolom (pengganti penataan dari partial global
       compact-document-ui yang DULU ikut menata tabel ini lewat id bersama;
       kini tabel Bagian terisolasi, jadi diatur sendiri di sini).
       Tabel selebar isi → kolom tak berdesakan + gulir horizontal. ── */
    #bagianDaftarTable .data-table {
      width: max-content;
      min-width: 100%;
    }
    #bagianDaftarTable .data-table th,
    #bagianDaftarTable .data-table td {
      white-space: nowrap;
    }
    /* Teks panjang boleh membungkus dalam lebar terbatas. */
    #bagianDaftarTable .data-table th.col-uraian_spp,
    #bagianDaftarTable .data-table td.col-uraian_spp {
      white-space: normal;
      min-width: 300px;
      max-width: 380px;
    }
    #bagianDaftarTable .data-table th.col-dibayar_kepada,
    #bagianDaftarTable .data-table td.col-dibayar_kepada {
      white-space: normal;
      min-width: 170px;
      max-width: 240px;
    }
    #bagianDaftarTable .data-table th.col-nomor_agenda,
    #bagianDaftarTable .data-table td.col-nomor_agenda {
      min-width: 150px;
    }
    #bagianDaftarTable .data-table th.col-bulan,
    #bagianDaftarTable .data-table td.col-bulan,
    #bagianDaftarTable .data-table th.col-tahun,
    #bagianDaftarTable .data-table td.col-tahun {
      min-width: 74px;
    }
    #bagianDaftarTable .data-table th.col-nilai_rupiah,
    #bagianDaftarTable .data-table td.col-nilai_rupiah {
      min-width: 150px;
    }
    #bagianDaftarTable .data-table th.col-umur_dokumen,
    #bagianDaftarTable .data-table td.col-umur_dokumen {
      min-width: 185px;
    }
    #bagianDaftarTable .data-table th.col-pengurus,
    #bagianDaftarTable .data-table td.col-pengurus {
      min-width: 160px;
    }

    /* Header tabel hijau solid seperti gambar */
    .data-table thead {
      background: #083E40;
    }

    .data-table th {
      padding: 16px 12px;
      color: white;
      font-size: 13px;
      font-weight: 600;
      text-align: center;
      vertical-align: middle;
      letter-spacing: 0.5px;
      white-space: nowrap;
      border-right: 1px solid rgba(255, 255, 255, 0.15);
    }

    .data-table th:last-child {
      border-right: none;
    }

    .data-table td {
      padding: 14px 12px;
      vertical-align: middle;
      border-bottom: 1px solid rgba(8, 62, 64, 0.05);
      border-right: 1px solid #e9ecef;
      font-size: 13px;
      text-align: center;
    }

    .data-table td:last-child {
      border-right: none;
    }

    .data-table tbody tr {
      transition: all 0.3s ease;
      cursor: default;
    }

    .data-table tbody tr:hover {
      background: linear-gradient(90deg, rgba(136, 151, 23, 0.05) 0%, transparent 100%);
    }

    /* Kolom beku Bagian (tema hijau #083E40).
       KIRI : No + Nomor SPP.  KANAN : Status Pembayaran (kolom paling kanan,
       tunggal — tak ada kolom beku tambahan). Lebar dikunci agar header & body
       sejajar dan offset sticky presisi. */
    #bagianDaftarTable .data-table th.col-no,
    #bagianDaftarTable .data-table td.col-no {
      position: -webkit-sticky !important;
      position: sticky !important;
      left: 0 !important;
      width: 64px;
      min-width: 64px;
      max-width: 64px;
      white-space: nowrap;
      z-index: 5;
    }
    #bagianDaftarTable .data-table th.col-nomor_spp,
    #bagianDaftarTable .data-table td.col-nomor_spp {
      position: -webkit-sticky !important;
      position: sticky !important;
      left: 64px !important; /* selebar kolom No */
      width: 160px;
      min-width: 160px;
      max-width: 160px;
      z-index: 5;
      box-shadow: 6px 0 8px -6px rgba(0, 0, 0, 0.25);
    }
    #bagianDaftarTable .data-table th.col-status_pembayaran,
    #bagianDaftarTable .data-table td.col-status_pembayaran {
      position: -webkit-sticky !important;
      position: sticky !important;
      right: 0 !important;
      width: 185px;
      min-width: 185px;
      max-width: 185px;
      z-index: 5;
      box-shadow: -6px 0 8px -6px rgba(0, 0, 0, 0.25);
    }

    /* Latar opaque agar isi kolom lain tak tembus saat scroll horizontal */
    #bagianDaftarTable .data-table tbody td.col-no,
    #bagianDaftarTable .data-table tbody td.col-nomor_spp,
    #bagianDaftarTable .data-table tbody td.col-status_pembayaran {
      background: #ffffff;
    }
    #bagianDaftarTable .data-table thead th.col-no,
    #bagianDaftarTable .data-table thead th.col-nomor_spp,
    #bagianDaftarTable .data-table thead th.col-status_pembayaran {
      background: #083E40; /* samakan dengan thead hijau */
      z-index: 6;          /* header di atas sel body yang beku */
    }
    /* Paksa header kiri ikut beku (spesifisitas thead th.col- + !important agar
       tak kalah dari aturan lain). Cell-nya sudah beku; ini khusus header-nya. */
    #bagianDaftarTable .data-table thead th.col-no {
      position: -webkit-sticky !important;
      position: sticky !important;
      left: 0 !important;
      z-index: 7 !important;
    }
    #bagianDaftarTable .data-table thead th.col-nomor_spp {
      position: -webkit-sticky !important;
      position: sticky !important;
      left: 64px !important;
      z-index: 7 !important;
    }
    #bagianDaftarTable .data-table tbody tr:hover td.col-no,
    #bagianDaftarTable .data-table tbody tr:hover td.col-nomor_spp,
    #bagianDaftarTable .data-table tbody tr:hover td.col-status_pembayaran {
      background: #f3faf9;
    }

    .badge-status {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 14px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 600;
      white-space: nowrap;
    }

    .badge-draft {
      background: linear-gradient(135deg, #6c757d 0%, #868e96 100%);
      color: white;
    }

    .badge-terkirim {
      background: linear-gradient(135deg, #28a745 0%, #34c759 100%);
      color: white;
    }

    .badge-selesai {
      background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
      color: white;
    }

    /* Action Buttons - Horizontal Layout */
    .action-buttons {
      display: flex;
      gap: 6px;
      justify-content: center;
      align-items: center;
      flex-wrap: nowrap;
    }

    .btn-action {
      width: 36px;
      height: 36px;
      padding: 0;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
      transition: all 0.2s ease;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      text-decoration: none;
    }

    .btn-action:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .btn-edit {
      background: #083E40;
      color: white;
    }

    .btn-edit:hover {
      background: #0a4f52;
      color: white;
    }

    .btn-send {
      background: #083E40;
      color: white;
    }

    .btn-send:hover {
      background: #0a4f52;
      color: white;
    }

    .btn-delete {
      background: #dc3545;
      color: white;
    }

    .btn-delete:hover {
      background: #c82333;
      color: white;
    }

    .btn-create {
      padding: 12px 24px;
      background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(8, 62, 64, 0.25);
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .btn-create:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(8, 62, 64, 0.35);
      color: white;
    }

    .pagination-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 16px 20px;
      border-top: 1px solid #e9ecef;
      flex-wrap: wrap;
      gap: 16px;
    }

    .per-page-select {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .per-page-select label {
      font-size: 14px;
      color: #495057;
    }

    .per-page-select select {
      padding: 6px 12px;
      border: 1px solid #dee2e6;
      border-radius: 6px;
      font-size: 14px;
    }

    .empty-state {
      text-align: center;
      padding: 60px 20px;
    }

    .empty-state i {
      font-size: 80px;
      color: #dee2e6;
      margin-bottom: 20px;
    }

    .empty-state h4 {
      color: #6c757d;
      margin-bottom: 10px;
    }

    .empty-state p {
      color: #adb5bd;
      margin-bottom: 20px;
    }

    /* Modal Popup */
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.5);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 9999;
      padding: 20px;
    }

    .modal-overlay.show {
      display: flex;
    }

    .modal-content-custom {
      background: white;
      border-radius: 20px;
      max-width: 90%;
      width: 90%;
      max-height: 90vh;
      overflow-y: auto;
      box-shadow: 0 25px 80px rgba(0, 0, 0, 0.25);
    }

    .modal-header-custom {
      background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);
      color: white;
      padding: 20px 24px;
      border-radius: 16px 16px 0 0;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .modal-header-custom h4 {
      margin: 0;
      font-size: 18px;
      font-weight: 600;
    }

    .modal-close {
      background: rgba(255, 255, 255, 0.2);
      border: none;
      color: white;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      cursor: pointer;
      font-size: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
    }

    .modal-close:hover {
      background: rgba(255, 255, 255, 0.3);
    }

    .modal-body-custom {
      padding: 24px;
    }

    .detail-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 16px;
    }

    .detail-item {
      background: #f8f9fa;
      padding: 14px;
      border-radius: 10px;
      border-left: 4px solid #083E40;
    }

    .detail-item.full-width {
      grid-column: span 2;
    }

    .detail-label {
      font-size: 11px;
      font-weight: 700;
      color: #6c757d;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 4px;
    }

    .detail-value {
      font-size: 14px;
      font-weight: 600;
      color: #212529;
    }

    .detail-value.highlight {
      color: #212529;
      font-size: 18px;
    }

    .modal-footer-custom {
      padding: 16px 24px;
      border-top: 1px solid #e9ecef;
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }

    @media (max-width: 768px) {
      .detail-grid {
        grid-template-columns: 1fr;
      }

      .detail-item.full-width {
        grid-column: span 1;
      }

      .action-buttons {
        flex-wrap: wrap;
      }
    }

    /* Document Age Badge */
    .document-age-badge {
      display: flex;
      flex-direction: column;
      gap: 6px;
      padding: 10px 14px;
      border-radius: 12px;
      min-width: 160px;
    }

    .document-age-badge.active {
      background: linear-gradient(135deg, #d4edda 0%, #c8e6c9 100%);
      border-left: 4px solid #28a745;
    }

    .document-age-badge.completed {
      background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
      border-left: 4px solid #6c757d;
    }

    .age-date {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 12px;
      font-weight: 600;
    }

    .document-age-badge.active .age-date {
      color: #155724;
    }

    .document-age-badge.completed .age-date {
      color: #495057;
    }

    .age-dot {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      animation: pulse 2s infinite;
    }

    .document-age-badge.active .age-dot {
      background: #28a745;
      box-shadow: 0 0 8px rgba(40, 167, 69, 0.5);
    }

    .document-age-badge.completed .age-dot {
      background: #6c757d;
      animation: none;
    }

    .age-duration {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 12px;
      font-weight: 500;
    }

    .document-age-badge.active .age-duration {
      color: #155724;
    }

    .document-age-badge.completed .age-duration {
      color: #6c757d;
    }

    @keyframes pulse {

      0%,
      100% {
        opacity: 1;
      }

      50% {
        opacity: 0.5;
      }
    }

    /* Payment Status Badge */
    .payment-status-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 14px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      white-space: nowrap;
    }

    .payment-status-badge.belum-dibayar {
      background: linear-gradient(135deg, #fff3cd 0%, #ffe0b2 100%);
      color: #856404;
      border: 1px solid #ffc107;
    }

    .payment-status-badge.siap-dibayar {
      background: linear-gradient(135deg, #cce5ff 0%, #b3d4fc 100%);
      color: #004085;
      border: 1px solid #007bff;
    }

    .payment-status-badge.sudah-dibayar {
      background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
      color: #155724;
      border: 1px solid #28a745;
    }

    /* Document Status Badge */
    .badge-status {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 8px 16px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      white-space: nowrap;
    }

    /* Belum Dikirim - Grey with shimmer animation */
    .badge-status.badge-draft {
      background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
      color: white;
      position: relative;
      overflow: hidden;
    }

    .badge-status.badge-draft::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg,
          transparent,
          rgba(255, 255, 255, 0.3),
          transparent);
      animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
      0% {
        left: -100%;
      }

      100% {
        left: 100%;
      }
    }

    /* Terkirim - Premium dark green with auto shimmer animation */
    .badge-status.badge-success,
    .badge-status.badge-terkirim {
      background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);
      color: white;
      box-shadow: 0 2px 8px rgba(8, 62, 64, 0.3);
      position: relative;
      overflow: hidden;
      transition: all 0.3s ease;
    }

    .badge-status.badge-success::before,
    .badge-status.badge-terkirim::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg,
          transparent,
          rgba(255, 255, 255, 0.4),
          transparent);
      animation: shimmer-terkirim 2.5s infinite;
    }

    .badge-status.badge-success:hover,
    .badge-status.badge-terkirim:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(8, 62, 64, 0.4);
      background: linear-gradient(135deg, #0a5f52 0%, #0c7066 100%);
    }

    @keyframes shimmer-terkirim {
      0% {
        left: -100%;
      }

      50% {
        left: 100%;
      }

      100% {
        left: 100%;
      }
    }

    /* Dikembalikan - Orange/Amber with shimmer */
    .badge-status.badge-dikembalikan {
      background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
      color: white;
      box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3);
      position: relative;
      overflow: hidden;
      transition: all 0.3s ease;
    }

    .badge-status.badge-dikembalikan::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg,
          transparent,
          rgba(255, 255, 255, 0.4),
          transparent);
      animation: shimmer-dikembalikan 2.5s infinite;
    }

    .badge-status.badge-dikembalikan:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);
      background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
    }

    @keyframes shimmer-dikembalikan {
      0% {
        left: -100%;
      }

      50% {
        left: 100%;
      }

      100% {
        left: 100%;
      }
    }

    /* Dark mode overrides for dikembalikan badge */
    .dark .badge-status.badge-dikembalikan {
      background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
      color: #1a1a1a;
      box-shadow: 0 2px 8px rgba(251, 191, 36, 0.4);
    }

    .dark .badge-status.badge-dikembalikan:hover {
      box-shadow: 0 4px 15px rgba(251, 191, 36, 0.5);
      background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
      color: white;
    }

    /* Refresh Button */
    .btn-refresh {
      padding: 10px 20px;
      background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 2px 6px rgba(23, 162, 184, 0.3);
      min-height: 44px;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .btn-refresh:hover {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(23, 162, 184, 0.4);
      background: linear-gradient(135deg, #138496 0%, #117a8b 100%);
    }

    .btn-refresh:active {
      transform: translateY(0);
    }

    .btn-refresh.loading {
      opacity: 0.8;
      cursor: wait;
    }

    .btn-refresh.loading i {
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      from {
        transform: rotate(0deg);
      }

      to {
        transform: rotate(360deg);
      }
    }

    .refresh-toast {
      position: fixed;
      top: 20px;
      right: 20px;
      padding: 12px 20px;
      border-radius: 10px;
      color: white;
      font-size: 14px;
      font-weight: 500;
      z-index: 99999;
      display: flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
      animation: slideInRight 0.3s ease, fadeOut 0.3s ease 2.5s forwards;
    }

    .refresh-toast.success {
      background: linear-gradient(135deg, #28a745, #218838);
    }

    .refresh-toast.error {
      background: linear-gradient(135deg, #dc3545, #c82333);
    }

    @keyframes slideInRight {
      from {
        transform: translateX(100%);
        opacity: 0;
      }

      to {
        transform: translateX(0);
        opacity: 1;
      }
    }

    @keyframes fadeOut {
      to {
        opacity: 0;
        transform: translateY(-10px);
      }
    }

    .dark .btn-refresh {
      background: linear-gradient(135deg, #138496 0%, #0d6d7e 100%);
      box-shadow: 0 2px 6px rgba(19, 132, 150, 0.4);
    }

    .dark .btn-refresh:hover {
      background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    }

    /* ── Kartu informasi Bagian (terinspirasi kartu owner, dibuat sendiri) ── */
    .bagian-info-cards {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 18px;
      margin-bottom: 24px;
    }
    .bic-card {
      display: flex;
      align-items: center;
      gap: 16px;
      background: #ffffff;
      border: 1px solid #e8eef0;
      border-radius: 16px;
      padding: 20px 22px;
      box-shadow: 0 2px 10px rgba(8, 62, 64, 0.05);
      position: relative;
      overflow: hidden;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .bic-card::before {
      content: '';
      position: absolute;
      left: 0; top: 0; bottom: 0;
      width: 5px;
    }
    .bic-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 22px rgba(8, 62, 64, 0.10);
    }
    .bic-icon {
      flex: 0 0 auto;
      width: 52px; height: 52px;
      border-radius: 14px;
      display: flex; align-items: center; justify-content: center;
      font-size: 22px; color: #fff;
    }
    .bic-value { font-size: 28px; font-weight: 800; line-height: 1.1; color: #0f2f2b; }
    .bic-label { font-size: 13px; font-weight: 600; color: #6b7f7c; margin-top: 2px; }
    .bic-total::before { background: #083E40; }
    .bic-total .bic-icon { background: linear-gradient(135deg, #083E40, #0a5a5e); }
    .bic-belum::before { background: #d97706; }
    .bic-belum .bic-icon { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .bic-sudah::before { background: #16a34a; }
    .bic-sudah .bic-icon { background: linear-gradient(135deg, #22c55e, #16a34a); }
    .dark .bic-card { background: #14201f; border-color: #24403c; }
    .dark .bic-value { color: #e6f2ef; }
    .dark .bic-label { color: #93a9a4; }
    @media (max-width: 900px) {
      .bagian-info-cards { grid-template-columns: 1fr; }
    }
  </style>

  <div class="container-fluid py-4">
    <!-- Kartu Informasi -->
    <div class="bagian-info-cards">
      <div class="bic-card bic-total">
        <div class="bic-icon"><i class="fa-solid fa-folder-open"></i></div>
        <div class="bic-body">
          <div class="bic-value">{{ number_format($totalDokumen, 0, ',', '.') }}</div>
          <div class="bic-label">Total Dokumen {{ $bagianCode }}</div>
        </div>
      </div>
      <div class="bic-card bic-belum">
        <div class="bic-icon"><i class="fa-solid fa-hourglass-half"></i></div>
        <div class="bic-body">
          <div class="bic-value">{{ number_format($totalBelumDibayar, 0, ',', '.') }}</div>
          <div class="bic-label">Dokumen Belum Dibayar</div>
        </div>
      </div>
      <div class="bic-card bic-sudah">
        <div class="bic-icon"><i class="fa-solid fa-circle-check"></i></div>
        <div class="bic-body">
          <div class="bic-value">{{ number_format($totalSudahDibayar, 0, ',', '.') }}</div>
          <div class="bic-label">Dokumen Sudah Dibayar</div>
        </div>
      </div>
    </div>

    <!-- Search & Filter -->
    <div class="search-box">
      <form action="{{ route('bagian.documents.index') }}" method="GET" class="search-filter-form">
        <div class="search-input-group">
          <div class="input-group">
            <span class="input-group-text">
              <i class="fa-solid fa-search text-muted"></i>
            </span>
            <input type="text" name="search" class="form-control" placeholder="Cari nomor agenda, SPP, atau uraian..."
              value="{{ request('search') }}">
          </div>
        </div>

        <select name="tahun" class="btn-year-select">
          <option value="">Semua Tahun</option>
          @php
            $currentYear = date('Y');
            for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
              $selected = request('tahun') == $y ? 'selected' : '';
              echo "<option value=\"{$y}\" {$selected}>{$y}</option>";
            }
          @endphp
        </select>

        <select name="status" class="btn-status-select">
          <option value="">Semua Status</option>
          <option value="belum_dikirim" {{ request('status') == 'belum_dikirim' ? 'selected' : '' }}>Belum Dikirim</option>
          <option value="menunggu_approve" {{ request('status') == 'menunggu_approve' ? 'selected' : '' }}>Menunggu Approve
          </option>
          <option value="terkirim" {{ request('status') == 'terkirim' ? 'selected' : '' }}>Terkirim</option>
          <option value="belum_dibayar" {{ request('status') == 'belum_dibayar' ? 'selected' : '' }}>Belum Siap Dibayar
          </option>
          <option value="siap_dibayar" {{ request('status') == 'siap_dibayar' ? 'selected' : '' }}>Siap Dibayar</option>
          <option value="sudah_dibayar" {{ request('status') == 'sudah_dibayar' ? 'selected' : '' }}>Sudah Dibayar</option>
          <option value="dikembalikan" {{ request('status') == 'dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
        </select>

        <button type="submit" class="btn-filter">
          <i class="fa-solid fa-filter me-1"></i>Filter
        </button>
        <button type="button" class="btn-refresh" id="btnRefreshTable" onclick="refreshDocumentTable()">
          <i class="fa-solid fa-arrows-rotate"></i> Refresh
        </button>
        <button type="button" class="btn-customize-columns-inline" onclick="openColumnCustomizationModal()">
          <i class="fa-solid fa-table-columns me-2"></i>
          Kustomisasi Kolom Tabel
        </button>
      </form>
    </div>

    <!-- Document Table -->
    <div class="table-container" id="bagianDaftarTable">
      @if($dokumens->count() > 0)
        <!-- Per-page dropdown at the top -->
        @include('partials.pagination-perpage-top', ['paginator' => $dokumens])
        <div class="table-wrapper">
          <table class="data-table">
            <thead>
              <tr>
                <th class="col-no">No</th>
                @foreach($selectedColumns as $col)
                  <th class="col-{{ $col }}">{{ $availableColumns[$col] ?? $col }}</th>
                @endforeach
                <th class="col-pengurus">Pengurus Dokumen</th>
                <th class="col-status_pembayaran">Status Pembayaran</th>
              </tr>
            </thead>
            <tbody>
              @foreach($dokumens as $index => $doc)
              @php
                $statusLower = strtolower($doc->status ?? '');
                // Bagian bisa edit saat status belum dikirim atau dikembalikan
                $canInlineEdit = false; // Bagian bersifat view-only: inline-edit dinonaktifkan
              @endphp
              <tr>
                        <td class="col-no">{{ $dokumens->firstItem() + $index }}</td>
                        @foreach($selectedColumns as $col)
                          @if(in_array($col, ['nomor_spp', 'uraian_spp', 'nilai_rupiah', 'tanggal_spp']) && $canInlineEdit)
                            <td class="ie-cell"
                                data-id="{{ $doc->id }}"
                                data-field="{{ $col }}"
                                @if($col === 'nilai_rupiah') data-raw="{{ $doc->nilai_rupiah ?? '' }}"
                                @elseif($col === 'tanggal_spp') data-raw="{{ $doc->tanggal_spp ? $doc->tanggal_spp->format('Y-m-d') : '' }}"
                                @else data-raw="{{ $doc->$col ?? '' }}"
                                @endif
                                onclick="event.stopPropagation()"
                                title="Klik dua kali untuk mengedit">
                              @if($col === 'uraian_spp')
                                <span class="ie-display" style="display: block; white-space: normal; word-wrap: break-word; line-height: 1.5; max-width: 300px;">{{ $doc->uraian_spp ?? '-' }}</span>
                              @elseif($col === 'nilai_rupiah')
                                <span class="ie-display"><strong style="color: #000000;">Rp. {{ number_format($doc->nilai_rupiah, 0, ',', '.') }}</strong></span>
                              @elseif($col === 'tanggal_spp')
                                <span class="ie-display">{{ $doc->tanggal_spp ? $doc->tanggal_spp->format('d-m-Y') : '-' }}</span>
                              @else
                                <span class="ie-display">{{ $doc->$col ?? '-' }}</span>
                              @endif
                            </td>
                          @else
                          <td class="col-{{ $col }}">
                            @if($col == 'nomor_agenda')
                              <strong style="color: #000000;">{{ $doc->nomor_agenda }}</strong>
                              <br>
                              <small class="text-muted">{{ $doc->bulan ?? '' }} {{ $doc->tahun ?? '' }}</small>
                            @elseif($col == 'nomor_spp')
                              {{ $doc->nomor_spp }}
                            @elseif($col == 'tanggal_masuk')
                              {{ $doc->tanggal_masuk ? $doc->tanggal_masuk->format('d-m-Y H:i') : '-' }}
                            @elseif($col == 'nilai_rupiah')
                              <strong style="color: #000000;">Rp. {{ number_format($doc->nilai_rupiah, 0, ',', '.') }}</strong>
                            @elseif($col == 'status')
                              @php
                                // Simplified status for Bagian view
                                $displayStatus = 'terkirim';
                                $statusClass = 'badge-terkirim';
                                $statusIcon = 'fa-check';
                                $statusText = 'Terkirim';

                                if ($statusLower == 'belum dikirim') {
                                  $displayStatus = 'belum_dikirim';
                                  $statusClass = 'badge-draft';
                                  $statusIcon = 'fa-file-lines';
                                  $statusText = 'Belum Dikirim';
                                } elseif ($statusLower == 'menunggu_approval_keuangan') {
                                  $displayStatus = 'menunggu_approve';
                                  $statusClass = 'badge-warning';
                                  $statusIcon = 'fa-clock';
                                  $statusText = 'Menunggu Approve';
                                } elseif ($statusLower == 'returned_to_bidang') {
                                  $displayStatus = 'dikembalikan';
                                  $statusClass = 'badge-dikembalikan';
                                  $statusIcon = 'fa-undo';
                                  $statusText = 'Dikembalikan';
                                }
                              @endphp
                              @if($displayStatus == 'belum_dikirim')
                                <span class="badge-status {{ $statusClass }}">
                                  <i class="fa-solid {{ $statusIcon }}"></i>
                                  <span>{{ $statusText }}</span>
                                </span>
                              @elseif($displayStatus == 'menunggu_approve')
                                <span class="badge-status {{ $statusClass }}"
                                  style="background: linear-gradient(135deg, #ffc107, #e0a800); color: #212529;">
                                  <i class="fa-solid {{ $statusIcon }}"></i>
                                  <span>{{ $statusText }}</span>
                                </span>
                              @elseif($displayStatus == 'dikembalikan')
                                <span class="badge-status {{ $statusClass }}"
                                  style="cursor: pointer;"
                                  onclick="event.stopPropagation(); showRejectionModal({{ $doc->id }})">
                                  <i class="fa-solid {{ $statusIcon }}"></i>
                                  <span>Dikembalikan,
                                    <span style="text-decoration: underline; font-weight: 700;">Alasan</span>
                                  </span>
                                </span>
                              @else
                                <span class="badge-status {{ $statusClass }}">
                                  <i class="fa-solid {{ $statusIcon }}"></i>
                                  <span>{{ $statusText }}</span>
                                </span>
                              @endif
                            @elseif($col == 'uraian_spp')
                              <span
                                style="display: block; white-space: normal; word-wrap: break-word; line-height: 1.5; max-width: 300px;">{{ $doc->uraian_spp ?? '-' }}</span>
                            @elseif($col == 'tanggal_spp')
                              {{ $doc->tanggal_spp ? $doc->tanggal_spp->format('d-m-Y') : '-' }}
                            @elseif($col == 'kebun')
                              {{ $doc->kebun ?? '-' }}
                            @elseif($col == 'nama_pengirim')
                              {{ $doc->nama_pengirim ?? '-' }}
                            @elseif($col == 'jenis_pembayaran')
                              {{ $doc->jenis_pembayaran ?? '-' }}
                            @elseif($col == 'umur_dokumen')
                              @php
                                // Determine if document is paid
                                $isPaid = $doc->status_pembayaran === 'sudah_dibayar' || !empty($doc->tanggal_dibayar);

                                // Calculate age
                                $startDate = $doc->created_at;
                                $endDate = $isPaid && $doc->tanggal_dibayar ? \Carbon\Carbon::parse($doc->tanggal_dibayar) : now();

                                if ($startDate) {
                                  $diff = $startDate->diff($endDate);
                                  $days = $diff->days;
                                  $hours = $diff->h;
                                  $minutes = $diff->i;

                                  $durationParts = [];
                                  if ($days > 0)
                                    $durationParts[] = $days . ' hari';
                                  if ($hours > 0)
                                    $durationParts[] = $hours . ' jam';
                                  if ($minutes > 0 || empty($durationParts))
                                    $durationParts[] = $minutes . ' menit';
                                  $durationText = implode(' ', $durationParts);
                                } else {
                                  $durationText = '-';
                                }
                              @endphp
                              <div class="document-age-badge {{ $isPaid ? 'completed' : 'active' }}">
                                <div class="age-date">
                                  <span class="age-dot"></span>
                                  {{ $startDate ? $startDate->format('d M Y, H:i') : '-' }}
                                </div>
                                <div class="age-duration">
                                  <i class="fa-solid fa-clock"></i>
                                  {{ $durationText }}
                                </div>
                              </div>
                            @elseif($col == 'status_pembayaran')
                              @php
                                // Determine payment status based on document position
                                // Check if already paid
                                $isPaid = $doc->status_pembayaran === 'sudah_dibayar' || !empty($doc->tanggal_dibayar);

                                // Check if in pembayaran role using current_handler
                                $currentHandlerLower = strtolower($doc->current_handler ?? '');
                                $isInPembayaran = str_contains($currentHandlerLower, 'pembayaran');

                                // Determine payment status change date
                                $statusChangeDate = null;

                                if ($isPaid) {
                                  $paymentStatusClass = 'sudah-dibayar';
                                  $paymentStatusText = 'Sudah Dibayar';
                                  $paymentStatusIcon = 'fa-check-circle';
                                  // Use tanggal_dibayar for paid status
                                  $statusChangeDate = $doc->tanggal_dibayar;
                                } elseif ($isInPembayaran) {
                                  $paymentStatusClass = 'siap-dibayar';
                                  $paymentStatusText = 'Siap Dibayar';
                                  $paymentStatusIcon = 'fa-money-bill-wave';
                                  // Get pembayaran role data for received_at date
                                  $pembayaranRoleData = $doc->getDataForRole('pembayaran');
                                  $statusChangeDate = $pembayaranRoleData?->received_at;
                                } else {
                                  $paymentStatusClass = 'belum-dibayar';
                                  $paymentStatusText = 'Belum Siap Dibayar';
                                  $paymentStatusIcon = 'fa-clock';
                                  // Use sent_at or created_at for initial status
                                  $statusChangeDate = $doc->sent_at ?? $doc->created_at;
                                }
                              @endphp
                              <div class="payment-status-container"
                                style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                                <span class="payment-status-badge {{ $paymentStatusClass }}">
                                  <i class="fa-solid {{ $paymentStatusIcon }}"></i>
                                  {{ $paymentStatusText }}
                                </span>
                                @if($statusChangeDate)
                                  <small style="font-size: 10px; color: #6c757d; text-align: center;">
                                    {{ \Carbon\Carbon::parse($statusChangeDate)->format('d M Y, H:i') }}
                                  </small>
                                @endif
                              </div>
                            @elseif($col == 'tanggal_paraf')
                              {{ $doc->tanggal_paraf ? $doc->tanggal_paraf->format('d/m/Y H:i') : '-' }}
                            @elseif($col == 'pemaraf')
                              @if($doc->pemaraf)
                                <span
                                  style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;background:linear-gradient(135deg,#22c55e 0%,#16a34a 100%);color:white;border-radius:6px;font-size:11px;font-weight:600;white-space:nowrap;">
                                  <i class="fa-solid fa-check-circle"></i>
                                  {{ $doc->pemaraf }}
                                </span>
                              @else
                                -
                              @endif
                            @elseif($col == 'tanggal_selesai_diproses')
                              {{ $doc->tanggal_selesai_diproses ? $doc->tanggal_selesai_diproses->format('d/m/Y H:i') : '-' }}
                            @elseif($col == 'kepala_sub_bagian')
                              {{ $doc->kepala_sub_bagian ?? '-' }}
                            @elseif($col == 'status_dokumen_custom')
                              @if($doc->status_dokumen_csv)
                                <span
                                  class="badge-status {{ $doc->status_dokumen_csv == 'Selesai Dibayar' ? 'badge-selesai' : ($doc->status_dokumen_csv == 'Dikembalikan' ? 'badge-dikembalikan' : 'badge-proses') }}"
                                  style="font-size: 10px; padding: 4px 8px;">
                                  {{ $doc->status_dokumen_csv }}
                                </span>
                              @else
                                -
                              @endif
                            @elseif($col == 'tanggal_dibayar')
                              {{ $doc->tanggal_dibayar ? \Carbon\Carbon::parse($doc->tanggal_dibayar)->format('d/m/Y') : '-' }}
                            @elseif($col == 'bulan')
                              {{ $doc->bulan ?? '-' }}
                            @elseif($col == 'tahun')
                              {{ $doc->tahun ?? '-' }}
                            @elseif($col == 'npwp')
                              {{ $doc->npwp ?? '-' }}
                            @elseif($col == 'link_dokumen_pajak')
                              @php $safeLink = \App\Support\SafeUrl::external($doc->link_dokumen_pajak); @endphp
                              @if($safeLink)
                                <a href="{{ $safeLink }}" target="_blank" rel="noopener noreferrer"
                                  class="ie-link-anchor" onclick="event.stopPropagation();"
                                  title="{{ $safeLink }}">
                                  <i class="fa-solid fa-link fa-sm"></i> Link Pajak
                                </a>
                              @else
                                -
                              @endif
                            @else
                              -
                            @endif
                          </td>
                          @endif {{-- end @if(in_array...) / @else non-editable --}}
                        @endforeach
                        <td class="col-pengurus" onclick="event.stopPropagation()">
                          {{-- Bagian view-only: posisi dokumen ditampilkan read-only (bukan dropdown yang bisa mengubah) --}}
                          <span class="text-muted">{{ \App\Models\Dokumen::getRoleDisplayNameIndo($doc->current_handler ?? 'operator') }}</span>
                        </td>
                        <td class="col-status_pembayaran">
                          @php
                            // Status pembayaran (kolom tetap paling kanan, beku).
                            $isPaid = $doc->status_pembayaran === 'sudah_dibayar' || !empty($doc->tanggal_dibayar);
                            $isInPembayaran = str_contains(strtolower($doc->current_handler ?? ''), 'pembayaran');
                            $statusChangeDate = null;
                            if ($isPaid) {
                              $paymentStatusClass = 'sudah-dibayar';
                              $paymentStatusText = 'Sudah Dibayar';
                              $paymentStatusIcon = 'fa-check-circle';
                              $statusChangeDate = $doc->tanggal_dibayar;
                            } elseif ($isInPembayaran) {
                              $paymentStatusClass = 'siap-dibayar';
                              $paymentStatusText = 'Siap Dibayar';
                              $paymentStatusIcon = 'fa-money-bill-wave';
                              $pembayaranRoleData = $doc->getDataForRole('pembayaran');
                              $statusChangeDate = $pembayaranRoleData?->received_at;
                            } else {
                              $paymentStatusClass = 'belum-dibayar';
                              $paymentStatusText = 'Belum Siap Dibayar';
                              $paymentStatusIcon = 'fa-clock';
                              $statusChangeDate = $doc->sent_at ?? $doc->created_at;
                            }
                          @endphp
                          <div class="payment-status-container"
                            style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                            <span class="payment-status-badge {{ $paymentStatusClass }}">
                              <i class="fa-solid {{ $paymentStatusIcon }}"></i>
                              {{ $paymentStatusText }}
                            </span>
                            @if($statusChangeDate)
                              <small style="font-size: 10px; color: #6c757d; text-align: center;">
                                {{ \Carbon\Carbon::parse($statusChangeDate)->format('d M Y, H:i') }}
                              </small>
                            @endif
                          </div>
                        </td>
                      </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-container">
          <div class="per-page-select">
            <label>Baris per halaman:</label>
            <select onchange="changePerPage(this.value)">
              <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
              <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
              <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
              <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
              <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>Semua</option>
            </select>
            <span class="text-muted">
              Menampilkan {{ $dokumens->firstItem() }} - {{ $dokumens->lastItem() }} dari {{ $dokumens->total() }} hasil
            </span>
          </div>
          <div>
            {{ $dokumens->appends(request()->query())->links('pagination::bootstrap-5') }}
          </div>
        </div>
      @else
        <div class="empty-state">
          <i class="fa-solid fa-folder-open"></i>
          <h4>Belum ada dokumen</h4>
          <p>Belum ada dokumen untuk Bagian ini di keuangan.</p>
        </div>
      @endif
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div id="deleteConfirmModal" class="confirm-modal-overlay">
    <div class="confirm-modal">
      <div class="confirm-icon delete-icon">
        <i class="fa-solid fa-trash-can"></i>
      </div>
      <h3 class="confirm-title">Hapus Dokumen?</h3>
      <p class="confirm-message">Apakah anda yakin ingin menghapus dokumen ini? Tindakan ini tidak dapat dibatalkan.</p>
      <div class="confirm-actions">
        <button type="button" class="btn-confirm-cancel" onclick="closeDeleteModal()">
          <i class="fa-solid fa-times"></i> Batal
        </button>
        <button type="button" class="btn-confirm-delete" id="confirmDeleteBtn">
          <i class="fa-solid fa-trash"></i> Ya, Hapus
        </button>
      </div>
    </div>
  </div>

  <!-- Send Confirmation Modal -->
  <div id="sendConfirmModal" class="confirm-modal-overlay">
    <div class="confirm-modal send-modal">
      <div class="confirm-icon send-icon">
        <i class="fa-solid fa-paper-plane"></i>
      </div>
      <h3 class="confirm-title">Kirim Dokumen?</h3>
      <p class="confirm-message">Apakah anda yakin dokumen ini dikirim ke Bidang Keuangan dan Akutansi?</p>
      <div class="confirm-actions">
        <button type="button" class="btn-confirm-cancel" onclick="closeSendModal()">
          <i class="fa-solid fa-times"></i> Batal
        </button>
        <button type="button" class="btn-confirm-send" id="confirmSendBtn">
          <i class="fa-solid fa-paper-plane"></i> Ya, Kirim
        </button>
      </div>
    </div>
  </div>

  <!-- Send Success Modal -->
  <div id="sendSuccessModal" class="success-modal-overlay">
    <div class="success-modal">
      <div class="success-icon-container">
        <div class="success-circle">
          <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
            <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none" />
            <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" />
          </svg>
        </div>
        <div class="confetti">
          <div class="confetti-piece"></div>
          <div class="confetti-piece"></div>
          <div class="confetti-piece"></div>
          <div class="confetti-piece"></div>
          <div class="confetti-piece"></div>
          <div class="confetti-piece"></div>
        </div>
      </div>
      <h2 class="success-title">Berhasil Terkirim!</h2>
      <div class="success-details">
        <div class="success-stat">
          <span class="stat-number">1</span>
          <span class="stat-label">Dokumen</span>
        </div>
        <div class="success-arrow">
          <i class="fa-solid fa-arrow-right"></i>
        </div>
        <div class="success-destination">
          <i class="fa-solid fa-inbox"></i>
          <span>Bidang Keuangan</span>
        </div>
      </div>
      <p class="success-message">
        <i class="fa-solid fa-info-circle"></i>
        Dokumen telah masuk ke <strong>inbox</strong> dan menunggu persetujuan
      </p>
      <button type="button" class="btn-success-close" onclick="closeSuccessAndReload()">
        <i class="fa-solid fa-check"></i> Mengerti
      </button>
    </div>
  </div>

  <!-- Column Customization Modal - Operator Style -->
  <div class="customization-modal" id="columnCustomizationModal">
    <div class="modal-content-custom" style="max-width: 90%; width: 90%;">
      <!-- Header -->
      <div class="modal-header-custom"
        style="background: #f8f9fa; border-bottom: 1px solid #e9ecef; justify-content: space-between;">
        <h3 style="display: flex; align-items: center; gap: 12px; color: #212529; margin: 0;">
          <i class="fa-solid fa-table-columns"></i>
          Kustomisasi Kolom Tabel
        </h3>
        <button class="modal-close" onclick="closeColumnCustomizationModal()"
          style="background: none; border: none; font-size: 20px; cursor: pointer; color: #6c757d;">
          <i class="fa-solid fa-times"></i>
        </button>
      </div>

      <!-- Body -->
      <div class="modal-body-custom" style="display: flex; flex-direction: column; gap: 24px;">
        <!-- Selection Panel -->
        <div class="selection-panel"
          style="background: #f8f9fa; border-radius: 12px; padding: 24px; border: 1px solid #e9ecef;">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <div class="panel-title"
              style="font-size: 18px; font-weight: 600; color: #212529; margin-bottom: 0; display: flex; align-items: center; gap: 10px;">
              <i class="fa-solid fa-check-square"></i>
              Pilih Kolom
            </div>
            <div style="display: flex; gap: 8px;">
              <button type="button" onclick="selectAllColumns()"
                style="padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 500; border: 1px solid #e5e7eb; background: #fff; color: #374151; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-check-double"></i> Pilih Semua
              </button>
              <button type="button" onclick="removeAllColumns()"
                style="padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 500; border: 1px solid #e5e7eb; background: #fff; color: #374151; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-times"></i> Hapus Semua
              </button>
            </div>
          </div>
          <div class="panel-description" style="font-size: 13px; color: #6c757d; margin-bottom: 16px; line-height: 1.6;">
            Centang kolom yang ingin ditampilkan pada tabel. Urutan akan mengikuti urutan pemilihan Anda.
          </div>
          <div class="column-selection-list" id="columnSelectionList"
            style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; max-height: 200px; overflow-y: auto; padding: 8px; background: white; border-radius: 8px; border: 1px solid #dee2e6;">
            @foreach($availableColumns as $key => $label)
              <div class="column-item {{ in_array($key, $selectedColumns) ? 'selected' : '' }}" data-column="{{ $key }}"
                onclick="toggleColumn(this)">
                <input type="checkbox" class="column-item-checkbox" value="{{ $key }}" {{ in_array($key, $selectedColumns) ? 'checked' : '' }} onclick="event.stopPropagation()">
                <label class="column-item-label"
                  style="cursor: pointer; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $label }}</label>
                <span class="column-item-order"
                  style="width: 24px; height: 24px; background: #28a745; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 600; {{ in_array($key, $selectedColumns) ? 'opacity: 1;' : 'opacity: 0; transform: scale(0);' }} transition: all 0.2s ease;">
                  {{ in_array($key, $selectedColumns) ? array_search($key, $selectedColumns) + 1 : '' }}
                </span>
              </div>
            @endforeach
          </div>
        </div>

        <!-- Preview Panel -->
        <div class="preview-panel"
          style="background: #ffffff; border-radius: 12px; padding: 24px; border: 1px solid #e9ecef;">
          <div class="panel-title"
            style="font-size: 18px; font-weight: 600; color: #212529; margin-bottom: 12px; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-eye"></i>
            Preview Hasil
          </div>
          <div class="panel-description" style="font-size: 13px; color: #6c757d; margin-bottom: 16px; line-height: 1.6;">
            Preview tabel akan menampilkan <span style="color: #28a745; font-weight: 600;">kolom yang Anda pilih</span>
            sesuai urutan.
          </div>
          <div class="preview-container"
            style="overflow-x: auto; background: #f8f9fa; border-radius: 8px; padding: 16px; min-height: 200px;">
            <div id="tablePreview">
              @if(count($selectedColumns) > 0)
                <table class="preview-table"
                  style="width: 100%; min-width: 100%; border-collapse: separate; border-spacing: 0; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); font-size: 13px;">
                  <thead>
                    <tr>
                      <th
                        style="background: #212529; color: white; padding: 14px 12px; text-align: center; font-weight: 600; font-size: 12px;">
                        No</th>
                      @foreach($selectedColumns as $col)
                        <th
                          style="background: #212529; color: white; padding: 14px 12px; text-align: center; font-weight: 600; font-size: 12px;">
                          {{ $availableColumns[$col] ?? $col }}
                        </th>
                      @endforeach
                      <th
                        style="background: #212529; color: white; padding: 14px 12px; text-align: center; font-weight: 600; font-size: 12px;">
                        Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    @for($i = 1; $i <= 5; $i++)
                      <tr style="border-bottom: 1px solid #e9ecef;">
                        <td style="padding: 12px; text-align: center; color: #495057;">{{ $i }}</td>
                        @foreach($selectedColumns as $col)
                          <td style="padding: 12px; text-align: center; color: #495057;">
                            @if($col == 'nomor_agenda')
                              0100{{ $i }}_2026
                            @elseif($col == 'nomor_spp')
                              {{ 200 + $i }}/M/SPP/8/04/2026
                            @elseif($col == 'tanggal_masuk')
                              {{ date('d-m-Y') }}
                            @elseif($col == 'nilai_rupiah')
                              Rp. {{ number_format(1000000 * $i, 0, ',', '.') }}
                            @elseif($col == 'status')
                              <span style="color: #28a745;">✓ Terkirim</span>
                            @else
                              Contoh Data {{ $i }}
                            @endif
                          </td>
                        @endforeach
                        <td style="padding: 12px; text-align: center; color: #495057;">Edit, Kirim</td>
                      </tr>
                    @endfor
                  </tbody>
                </table>
              @else
                <div class="empty-preview" style="text-align: center; padding: 60px 20px; color: #6c757d;">
                  <i class="fa-solid fa-table"
                    style="font-size: 48px; color: #adb5bd; margin-bottom: 16px; display: block;"></i>
                  <p style="font-size: 16px; font-weight: 500; margin-bottom: 8px;">Belum ada kolom yang dipilih</p>
                  <small style="font-size: 14px; color: #868e96;">Silakan pilih minimal satu kolom untuk melihat
                    preview</small>
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="modal-footer-custom"
        style="padding: 20px 40px; border-top: 1px solid #e9ecef; background: #ffffff; display: flex; justify-content: space-between; align-items: center;">
        <div class="selected-count" style="font-size: 15px; color: #495057; font-weight: 500;">
          <strong id="selectedColumnCount" style="color: #28a745; font-size: 18px;">{{ count($selectedColumns) }}</strong>
          kolom dipilih
          @if(count($selectedColumns) > 0)
                  <br><small style="color: #6c757d;">Kolom: {{ implode(', ', array_map(function ($col) use ($availableColumns) {
              return $availableColumns[$col] ?? $col;
            }, $selectedColumns)) }}</small>
          @endif
        </div>
        <div class="modal-actions" style="display: flex; gap: 12px;">
          <button type="button" class="btn-modal btn-cancel" onclick="closeColumnCustomizationModal()">
            <i class="fa-solid fa-times"></i> Batal
          </button>
          <button type="button" class="btn-modal btn-save" id="saveCustomizationBtn" onclick="saveColumnCustomization()">
            <i class="fa-solid fa-save"></i> Simpan Perubahan
          </button>
        </div>
      </div>
    </div>
  </div>


  <style>
    /* Modern Modal Styles */
    .modal-content-custom {
      background: #ffffff;
      border-radius: 24px;
      max-width: 900px;
      width: 95%;
      max-height: 90vh;
      overflow: hidden;
      box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
      display: flex;
      flex-direction: column;
    }

    .modal-header-custom {
      background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);
      color: white;
      padding: 24px 28px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-radius: 24px 24px 0 0;
    }

    .header-content {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .header-icon {
      width: 56px;
      height: 56px;
      background: rgba(255, 255, 255, 0.15);
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
    }

    .header-text h4 {
      margin: 0;
      font-size: 20px;
      font-weight: 700;
    }

    .doc-id {
      font-size: 14px;
      opacity: 0.85;
      font-weight: 500;
    }

    .header-actions {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .status-pill {
      background: rgba(255, 255, 255, 0.2);
      padding: 8px 16px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .modal-close {
      background: rgba(255, 255, 255, 0.15);
      border: none;
      color: white;
      width: 40px;
      height: 40px;
      border-radius: 12px;
      cursor: pointer;
      font-size: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s;
    }

    .modal-close:hover {
      background: rgba(255, 255, 255, 0.25);
      transform: scale(1.05);
    }

    /* Tabs */
    .modal-tabs {
      display: flex;
      background: #f8f9fa;
      padding: 12px 28px;
      gap: 8px;
      border-bottom: 1px solid #e9ecef;
    }

    .tab-btn {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 12px 20px;
      border: none;
      background: transparent;
      border-radius: 12px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      color: #6c757d;
      transition: all 0.3s ease;
    }

    .tab-btn:hover {
      background: rgba(8, 62, 64, 0.08);
      color: #083E40;
    }

    .tab-btn.active {
      background: #083E40;
      color: white;
      box-shadow: 0 4px 15px rgba(8, 62, 64, 0.3);
    }

    .tab-btn i {
      font-size: 16px;
    }

    /* Modal Body */
    .modal-body-custom {
      padding: 28px;
      overflow-y: auto;
      flex: 1;
    }

    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
      animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Stats Row */
    .stats-row {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 16px;
      margin-bottom: 28px;
    }

    .stat-card {
      display: flex;
      align-items: center;
      gap: 16px;
      padding: 20px;
      border-radius: 16px;
      background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
      border: 1px solid #e9ecef;
    }

    .stat-card.primary {
      background: linear-gradient(135deg, #e3f2fd 0%, #f8f9fa 100%);
      border-color: #bbdefb;
    }

    .stat-card.success {
      background: linear-gradient(135deg, #e8f5e9 0%, #f8f9fa 100%);
      border-color: #c8e6c9;
    }

    .stat-card.info {
      background: linear-gradient(135deg, #fff3e0 0%, #f8f9fa 100%);
      border-color: #ffe0b2;
    }

    .stat-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
    }

    .stat-card.primary .stat-icon {
      background: #1976d2;
      color: white;
    }

    .stat-card.success .stat-icon {
      background: #388e3c;
      color: white;
    }

    .stat-card.info .stat-icon {
      background: #f57c00;
      color: white;
    }

    .stat-info {
      display: flex;
      flex-direction: column;
    }

    .stat-label {
      font-size: 12px;
      color: #6c757d;
      text-transform: uppercase;
      font-weight: 600;
      letter-spacing: 0.5px;
    }

    .stat-value {
      font-size: 16px;
      font-weight: 700;
      color: #212529;
    }

    /* Detail Sections */
    .detail-section {
      margin-bottom: 24px;
    }

    .section-header {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 16px;
      padding-bottom: 10px;
      border-bottom: 2px solid #e9ecef;
    }

    .section-header i {
      width: 32px;
      height: 32px;
      background: #083E40;
      color: white;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
    }

    .section-header h5 {
      margin: 0;
      font-size: 16px;
      font-weight: 700;
      color: #212529;
    }

    .section-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 12px;
    }

    .section-grid.cols-3 {
      grid-template-columns: repeat(3, 1fr);
    }

    .section-grid.cols-2 {
      grid-template-columns: repeat(2, 1fr);
    }

    .info-card {
      background: #f8f9fa;
      padding: 16px;
      border-radius: 12px;
      border-left: 4px solid #083E40;
    }

    .info-card.highlight {
      background: linear-gradient(135deg, #e8f5e9 0%, #f8f9fa 100%);
      border-left-color: #28a745;
    }

    .info-label {
      display: block;
      font-size: 11px;
      font-weight: 700;
      color: #6c757d;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 6px;
    }

    .info-value {
      display: block;
      font-size: 14px;
      font-weight: 600;
      color: #212529;
    }

    .info-value.mono {
      font-family: 'Consolas', 'Monaco', monospace;
      background: #e9ecef;
      padding: 4px 8px;
      border-radius: 6px;
      display: inline-block;
    }

    .info-value.tag {
      background: #083E40;
      color: white;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 12px;
    }

    .info-value.status-badge {
      display: inline-block;
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 12px;
      background: linear-gradient(135deg, #28a745 0%, #34c759 100%);
      color: white;
    }

    /* Uraian Box */
    .uraian-box {
      background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
      border: 1px solid #e9ecef;
      border-radius: 12px;
      padding: 20px;
      font-size: 14px;
      line-height: 1.7;
      color: #495057;
      min-height: 80px;
    }

    /* Money Display */
    .money-display {
      background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);
      border-radius: 16px;
      padding: 28px;
      text-align: center;
      color: white;
    }

    .money-amount {
      font-size: 32px;
      font-weight: 700;
      margin-bottom: 8px;
    }

    .money-words {
      font-size: 14px;
      opacity: 0.9;
      font-style: italic;
    }

    /* Vendor Card */
    .vendor-card {
      display: flex;
      align-items: center;
      gap: 20px;
      background: #f8f9fa;
      border-radius: 16px;
      padding: 24px;
      border: 1px solid #e9ecef;
    }

    .vendor-icon {
      width: 60px;
      height: 60px;
      background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      color: white;
    }

    .vendor-info {
      display: flex;
      flex-direction: column;
    }

    .vendor-label {
      font-size: 12px;
      color: #6c757d;
      text-transform: uppercase;
      font-weight: 600;
      margin-bottom: 4px;
    }

    .vendor-name {
      font-size: 18px;
      font-weight: 700;
      color: #212529;
    }

    /* Footer */
    .modal-footer-custom {
      padding: 20px 28px;
      border-top: 1px solid #e9ecef;
      display: flex;
      justify-content: flex-end;
      background: #f8f9fa;
    }

    .btn-footer {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 12px 24px;
      border: none;
      border-radius: 12px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .btn-footer.secondary {
      background: #6c757d;
      color: white;
    }

    .btn-footer.secondary:hover {
      background: #5a6268;
      transform: translateY(-2px);
    }

    /* Responsive */
    @media (max-width: 768px) {
      .stats-row {
        grid-template-columns: 1fr;
      }

      .section-grid,
      .section-grid.cols-2,
      .section-grid.cols-3 {
        grid-template-columns: 1fr;
      }

      .modal-tabs {
        overflow-x: auto;
        padding: 12px 16px;
      }

      .tab-btn span {
        display: none;
      }

      .header-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
      }

      .modal-header-custom {
        flex-direction: column;
        gap: 16px;
        align-items: flex-start;
      }

      .stat-card {
        flex-direction: column;
        text-align: center;
      }
    }

    /* Confirmation Modal Styles */
    .confirm-modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.6);
      backdrop-filter: blur(4px);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 10000;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .confirm-modal-overlay.show {
      display: flex;
      opacity: 1;
    }

    .confirm-modal {
      background: white;
      border-radius: 16px;
      padding: 32px;
      text-align: center;
      max-width: 400px;
      width: 90%;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
      animation: modalSlideIn 0.3s ease;
    }

    @keyframes modalSlideIn {
      from {
        transform: scale(0.9) translateY(-20px);
        opacity: 0;
      }

      to {
        transform: scale(1) translateY(0);
        opacity: 1;
      }
    }

    .confirm-icon {
      width: 64px;
      height: 64px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      font-size: 28px;
    }

    .delete-icon {
      background: linear-gradient(135deg, #fce4ec 0%, #ffcdd2 100%);
      color: #e53935;
    }

    .send-icon {
      background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
      color: #1976d2;
    }

    .confirm-title {
      font-size: 22px;
      font-weight: 700;
      color: #1f2937;
      margin: 0 0 12px 0;
    }

    .confirm-message {
      font-size: 14px;
      color: #6b7280;
      margin: 0 0 24px 0;
      line-height: 1.5;
    }

    .confirm-actions {
      display: flex;
      gap: 12px;
      justify-content: center;
    }

    .btn-confirm-cancel {
      padding: 12px 24px;
      border: 1px solid #e5e7eb;
      background: white;
      color: #374151;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .btn-confirm-cancel:hover {
      background: #f3f4f6;
      border-color: #d1d5db;
    }

    .btn-confirm-delete {
      padding: 12px 24px;
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 4px 14px rgba(239, 68, 68, 0.3);
    }

    .btn-confirm-delete:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
    }

    .btn-confirm-send {
      padding: 12px 24px;
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
      color: white;
      border: none;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      gap: 8px;
      box-shadow: 0 4px 14px rgba(8, 62, 64, 0.3);
    }

    .btn-confirm-send:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(8, 62, 64, 0.4);
    }

    /* Success Modal Styles */
    .success-modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      backdrop-filter: blur(4px);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 10000;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .success-modal-overlay.show {
      display: flex;
      opacity: 1;
    }

    .success-modal {
      background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
      border-radius: 20px;
      padding: 40px;
      text-align: center;
      max-width: 420px;
      width: 90%;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
      animation: modalSlideIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .success-icon-container {
      position: relative;
      margin-bottom: 24px;
    }

    .success-circle {
      width: 80px;
      height: 80px;
      margin: 0 auto;
    }

    .checkmark {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      display: block;
      stroke-width: 2;
      stroke: #10b981;
      stroke-miterlimit: 10;
      box-shadow: inset 0px 0px 0px #10b981;
      animation: fill 0.4s ease-in-out 0.4s forwards, scale 0.3s ease-in-out 0.9s both;
    }

    .checkmark-circle {
      stroke-dasharray: 166;
      stroke-dashoffset: 166;
      stroke-width: 2;
      stroke-miterlimit: 10;
      stroke: #10b981;
      fill: none;
      animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
    }

    .checkmark-check {
      transform-origin: 50% 50%;
      stroke-dasharray: 48;
      stroke-dashoffset: 48;
      animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
    }

    @keyframes stroke {
      100% {
        stroke-dashoffset: 0;
      }
    }

    @keyframes fill {
      100% {
        box-shadow: inset 0px 0px 0px 30px rgba(16, 185, 129, 0.1);
      }
    }

    @keyframes scale {

      0%,
      100% {
        transform: none;
      }

      50% {
        transform: scale3d(1.1, 1.1, 1);
      }
    }

    .confetti {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      pointer-events: none;
    }

    .confetti-piece {
      position: absolute;
      width: 10px;
      height: 10px;
      border-radius: 2px;
      animation: confetti-fall 1s ease-out forwards;
      opacity: 0;
    }

    .confetti-piece:nth-child(1) {
      background: #f59e0b;
      animation-delay: 0.2s;
      --tx: -60px;
      --ty: -40px;
      --rot: 180deg;
    }

    .confetti-piece:nth-child(2) {
      background: #10b981;
      animation-delay: 0.3s;
      --tx: 70px;
      --ty: -50px;
      --rot: -200deg;
    }

    .confetti-piece:nth-child(3) {
      background: #3b82f6;
      animation-delay: 0.4s;
      --tx: -50px;
      --ty: 60px;
      --rot: 150deg;
    }

    .confetti-piece:nth-child(4) {
      background: #ec4899;
      animation-delay: 0.5s;
      --tx: 60px;
      --ty: 50px;
      --rot: -180deg;
    }

    .confetti-piece:nth-child(5) {
      background: #8b5cf6;
      animation-delay: 0.6s;
      --tx: -30px;
      --ty: -60px;
      --rot: 220deg;
    }

    .confetti-piece:nth-child(6) {
      background: #ef4444;
      animation-delay: 0.7s;
      --tx: 40px;
      --ty: 70px;
      --rot: -150deg;
    }

    @keyframes confetti-fall {
      0% {
        opacity: 1;
        transform: translate(0, 0) rotate(0deg) scale(1);
      }

      100% {
        opacity: 0;
        transform: translate(var(--tx, 50px), var(--ty, 80px)) rotate(var(--rot, 360deg)) scale(0.5);
      }
    }

    .success-title {
      color: #10b981;
      font-size: 28px;
      font-weight: 700;
      margin: 0 0 24px 0;
    }

    .success-details {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 20px;
      margin-bottom: 24px;
      padding: 20px;
      background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
      border-radius: 12px;
      border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .success-stat {
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .stat-number {
      font-size: 36px;
      font-weight: 800;
      color: #059669;
      line-height: 1;
    }

    .stat-label {
      font-size: 13px;
      color: #6b7280;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-top: 4px;
    }

    .success-arrow {
      color: #10b981;
      font-size: 20px;
      animation: arrowPulse 1s ease-in-out infinite;
    }

    @keyframes arrowPulse {

      0%,
      100% {
        transform: translateX(0);
        opacity: 1;
      }

      50% {
        transform: translateX(5px);
        opacity: 0.7;
      }
    }

    .success-destination {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 6px;
    }

    .success-destination i {
      font-size: 24px;
      color: #059669;
    }

    .success-destination span {
      font-size: 14px;
      font-weight: 600;
      color: #374151;
    }

    .success-message {
      color: #6b7280;
      font-size: 14px;
      margin: 0 0 28px 0;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .success-message i {
      color: #3b82f6;
    }

    .btn-success-close {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      color: white;
      border: none;
      padding: 14px 40px;
      font-size: 16px;
      font-weight: 600;
      border-radius: 12px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      transition: all 0.2s ease;
      box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
    }

    .btn-success-close:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5);
    }
  </style>

  <script>
    function changePerPage(value) {
      const url = new URL(window.location.href);
      url.searchParams.set('per_page', value);
      url.searchParams.delete('page');
      window.location.href = url.toString();
    }


    // Close modal on Escape key
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        closeDeleteModal();
        closeSendModal();
      }
    });

    // ============ DELETE MODAL FUNCTIONS ============
    let deleteFormId = null;

    function showDeleteModal(docId) {
      deleteFormId = docId;
      document.getElementById('deleteConfirmModal').classList.add('show');
      document.body.style.overflow = 'hidden';
    }

    function closeDeleteModal() {
      document.getElementById('deleteConfirmModal').classList.remove('show');
      document.body.style.overflow = '';
      deleteFormId = null;
    }

    document.getElementById('confirmDeleteBtn').addEventListener('click', function () {
      if (deleteFormId) {
        document.getElementById('deleteForm-' + deleteFormId).submit();
      }
    });

    // Close on overlay click
    document.getElementById('deleteConfirmModal').addEventListener('click', function (e) {
      if (e.target === this) closeDeleteModal();
    });

    // ============ SEND MODAL FUNCTIONS ============
    let sendFormId = null;

    function showSendModal(docId) {
      sendFormId = docId;
      document.getElementById('sendConfirmModal').classList.add('show');
      document.body.style.overflow = 'hidden';
    }

    function closeSendModal() {
      document.getElementById('sendConfirmModal').classList.remove('show');
      document.body.style.overflow = '';
      sendFormId = null;
    }

    document.getElementById('confirmSendBtn').addEventListener('click', function () {
      if (sendFormId) {
        const form = document.getElementById('sendForm-' + sendFormId);
        const formData = new FormData(form);

        // Show loading state
        this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengirim...';
        this.disabled = true;

        fetch(form.action, {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          }
        })
          .then(response => {
            if (!response.ok) {
              return response.json().then(data => {
                throw new Error(data.message || 'Gagal mengirim dokumen');
              });
            }
            return response.json();
          })
          .then(data => {
            closeSendModal();
            // Reset button
            this.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Ya, Kirim';
            this.disabled = false;

            if (data.success) {
              // Update destination text dynamically based on server response
              const destinationEl = document.querySelector('#sendSuccessModal .success-destination span');
              if (destinationEl && data.destination) {
                destinationEl.textContent = data.destination;
              }

              // Show success modal
              document.getElementById('sendSuccessModal').classList.add('show');
              document.body.style.overflow = 'hidden';
            } else {
              alert(data.message || 'Gagal mengirim dokumen');
              window.location.reload();
            }
          })
          .catch(error => {
            console.error('Error:', error);
            closeSendModal();
            // Reset button
            this.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Ya, Kirim';
            this.disabled = false;
            alert(error.message || 'Terjadi kesalahan saat mengirim dokumen');
            window.location.reload();
          });
      }
    });

    // Close on overlay click
    document.getElementById('sendConfirmModal').addEventListener('click', function (e) {
      if (e.target === this) closeSendModal();
    });

    // ============ SUCCESS MODAL FUNCTIONS ============
    function closeSuccessAndReload() {
      document.getElementById('sendSuccessModal').classList.remove('show');
      document.body.style.overflow = '';
      window.location.reload();
    }

    // Close on overlay click
    document.getElementById('sendSuccessModal').addEventListener('click', function (e) {
      if (e.target === this) closeSuccessAndReload();
    });

    // ============ COLUMN CUSTOMIZATION MODAL FUNCTIONS ============
    let selectedColumnsOrder = [];

    function initializeColumnOrder() {
      selectedColumnsOrder = [];
      document.querySelectorAll('#columnCustomizationModal .column-item.selected').forEach((item) => {
        selectedColumnsOrder.push(item.dataset.column);
      });
      updateColumnOrderBadges();
      updateSelectedCount();
    }

    function openColumnCustomizationModal() {
      document.getElementById('columnCustomizationModal').classList.add('show');
      document.body.style.overflow = 'hidden';
      initializeColumnOrder();
    }

    function closeColumnCustomizationModal() {
      document.getElementById('columnCustomizationModal').classList.remove('show');
      document.body.style.overflow = '';
    }

    function toggleColumn(columnElement) {
      const columnKey = columnElement.dataset.column;
      const checkbox = columnElement.querySelector('.column-item-checkbox');
      const isChecked = checkbox.checked;

      if (!isChecked) {
        // Add to selection
        if (!selectedColumnsOrder.includes(columnKey)) {
          selectedColumnsOrder.push(columnKey);
        }
        checkbox.checked = true;
        columnElement.classList.add('selected');
      } else {
        // Remove from selection
        selectedColumnsOrder = selectedColumnsOrder.filter(key => key !== columnKey);
        checkbox.checked = false;
        columnElement.classList.remove('selected');
      }

      updateColumnOrderBadges();
      updateSelectedCount();
    }

    function selectAllColumns() {
      const allItems = document.querySelectorAll('#columnCustomizationModal .column-item');
      selectedColumnsOrder = [];
      allItems.forEach(item => {
        selectedColumnsOrder.push(item.dataset.column);
        item.classList.add('selected');
        item.querySelector('.column-item-checkbox').checked = true;
      });
      updateColumnOrderBadges();
      updateSelectedCount();
    }

    function removeAllColumns() {
      selectedColumnsOrder = [];
      document.querySelectorAll('#columnCustomizationModal .column-item').forEach(item => {
        item.classList.remove('selected');
        item.querySelector('.column-item-checkbox').checked = false;
      });
      updateColumnOrderBadges();
      updateSelectedCount();
    }

    function updateColumnOrderBadges() {
      document.querySelectorAll('#columnCustomizationModal .column-item').forEach(item => {
        const columnKey = item.dataset.column;
        const orderBadge = item.querySelector('.column-item-order');
        if (orderBadge) {
          const orderIndex = selectedColumnsOrder.indexOf(columnKey);
          if (orderIndex !== -1) {
            orderBadge.textContent = orderIndex + 1;
            orderBadge.style.opacity = '1';
            orderBadge.style.transform = 'scale(1)';
          } else {
            orderBadge.textContent = '';
            orderBadge.style.opacity = '0';
            orderBadge.style.transform = 'scale(0)';
          }
        }
      });
    }

    function updateSelectedCount() {
      const countEl = document.getElementById('selectedColumnCount');
      if (countEl) {
        countEl.textContent = selectedColumnsOrder.length;
      }
    }

    function saveColumnCustomization() {
      if (selectedColumnsOrder.length === 0) {
        alert('Silakan pilih minimal satu kolom untuk ditampilkan.');
        return;
      }

      // Get the filter form
      const filterForm = document.querySelector('.search-filter-form');

      // Remove any existing columns[] hidden inputs
      filterForm.querySelectorAll('input[name="columns[]"]').forEach(input => input.remove());

      // Add hidden inputs for selected columns
      selectedColumnsOrder.forEach(columnKey => {
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'columns[]';
        hiddenInput.value = columnKey;
        filterForm.appendChild(hiddenInput);
      });

      // Close modal and submit form
      closeColumnCustomizationModal();
      filterForm.submit();
    }

    // Close column customization modal on overlay click
    document.getElementById('columnCustomizationModal').addEventListener('click', function (e) {
      if (e.target === this) closeColumnCustomizationModal();
    });

    // Close modals on Escape key (add column customization)
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') {
        closeColumnCustomizationModal();
      }
    });

    // AJAX Refresh Document Table
    function refreshDocumentTable() {
      const btn = document.getElementById('btnRefreshTable');
      const container = document.getElementById('bagianDaftarTable');

      if (!btn || !container) return;

      btn.classList.add('loading');
      btn.disabled = true;

      fetch(window.location.href, {
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'text/html'
        }
      })
        .then(response => {
          if (!response.ok) throw new Error('Network response was not ok');
          return response.text();
        })
        .then(html => {
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');
          const newTable = doc.getElementById('bagianDaftarTable');
          if (newTable) {
            container.innerHTML = newTable.innerHTML;
            showRefreshToast('success', 'Data berhasil diperbarui!');
          } else {
            showRefreshToast('error', 'Gagal memperbarui data.');
          }
        })
        .catch(error => {
          console.error('Refresh error:', error);
          showRefreshToast('error', 'Gagal memperbarui data. Coba lagi.');
        })
        .finally(() => {
          btn.classList.remove('loading');
          btn.disabled = false;
        });
    }

    function showRefreshToast(type, message) {
      // Remove existing toasts
      document.querySelectorAll('.refresh-toast').forEach(t => t.remove());

      const toast = document.createElement('div');
      toast.className = `refresh-toast ${type}`;
      toast.innerHTML = `<i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;
      document.body.appendChild(toast);

      setTimeout(() => {
        if (toast.parentNode) toast.remove();
      }, 3000);
    }
  </script>

  <!-- Modal: Rejection Detail - Bagian -->
  <div class="modal fade" id="rejectionDetailModal" tabindex="-1" aria-labelledby="rejectionDetailModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content" style="border-radius: 16px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <div class="modal-header"
          style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; border: none; padding: 1.5rem 2rem;">
          <h5 class="modal-title" id="rejectionDetailModalLabel" style="font-size: 1.25rem; font-weight: 600;">
            <i class="fa-solid fa-times-circle me-2"></i>Detail Penolakan Dokumen
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
            style="opacity: 0.9;"></button>
        </div>
        <div class="modal-body" style="padding: 2rem;">
          <div id="rejectionModalLoading" class="text-center py-4">
            <div class="spinner-border text-danger" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Memuat detail penolakan...</p>
          </div>
          <div id="rejectionModalContent" style="display: none;">
            <div class="card mb-4" style="border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-radius: 12px;">
              <div class="card-body" style="padding: 1.5rem;">
                <h6 class="card-title mb-3"
                  style="color: #083E40; font-weight: 600; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px;">
                  <i class="fa-solid fa-file-lines me-2" style="color: #889717;"></i>Informasi Dokumen
                </h6>
                <div class="row g-3">
                  <div class="col-md-6">
                    <div class="mb-2">
                      <small class="text-muted text-uppercase fw-semibold" style="font-size:0.75rem;">Nomor Agenda</small>
                      <div class="fw-semibold" id="rejectionNomorAgenda">-</div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-2">
                      <small class="text-muted text-uppercase fw-semibold" style="font-size:0.75rem;">Nomor SPP</small>
                      <div class="fw-semibold" id="rejectionNomorSpp">-</div>
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="mb-2">
                      <small class="text-muted text-uppercase fw-semibold" style="font-size:0.75rem;">Uraian SPP</small>
                      <div id="rejectionUraianSpp">-</div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-2">
                      <small class="text-muted text-uppercase fw-semibold" style="font-size:0.75rem;">Nilai Rupiah</small>
                      <div class="text-success fw-bold" id="rejectionNilaiRupiah">-</div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-2">
                      <small class="text-muted text-uppercase fw-semibold" style="font-size:0.75rem;">Tanggal Ditolak</small>
                      <div id="rejectionTanggal">-</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="card"
              style="border: none; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-radius: 12px; background: linear-gradient(135deg, #fff5f5 0%, #ffe5e5 100%); border-left: 4px solid #dc3545;">
              <div class="card-body" style="padding: 1.5rem;">
                <h6 class="card-title mb-3"
                  style="color: #dc3545; font-weight: 600; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 0.5px;">
                  <i class="fa-solid fa-user-xmark me-2"></i>Informasi Penolakan
                </h6>
                <div class="mb-3">
                  <small class="text-muted text-uppercase fw-semibold" style="font-size:0.75rem;">Ditolak Oleh</small>
                  <div class="mt-1">
                    <span class="badge"
                      style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; padding: 0.5rem 1rem; font-size: 0.875rem; border-radius: 8px;">
                      <i class="fa-solid fa-user-shield me-2"></i>
                      <span id="rejectionBy">-</span>
                    </span>
                  </div>
                </div>
                <div>
                  <small class="text-muted text-uppercase fw-semibold d-block mb-2" style="font-size:0.75rem;">Alasan Penolakan</small>
                  <div id="rejectionReason"
                    style="background: white; padding: 1.25rem; border-radius: 10px; border: 1px solid rgba(220, 53, 69, 0.2); min-height: 80px; line-height: 1.6; color: #333; white-space: pre-wrap; word-wrap: break-word;">
                    -
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div id="rejectionModalError" style="display: none;" class="text-center py-4">
            <i class="fa-solid fa-exclamation-triangle" style="font-size: 48px; color: #dc3545; margin-bottom: 1rem;"></i>
            <p class="text-danger mb-0" id="rejectionErrorMessage">Gagal memuat detail penolakan</p>
          </div>
        </div>
        <div class="modal-footer border-0 justify-content-center" style="padding: 1.5rem 2rem; background: #f8f9fa;">
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal"
            style="border-radius: 8px; font-weight: 500;">
            <i class="fa-solid fa-times me-2"></i>Tutup
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    window.showRejectionModal = function(dokumenId) {
      const modalEl = document.getElementById('rejectionDetailModal');
      if (!modalEl) return;
      const modal = new bootstrap.Modal(modalEl);
      const loadingEl = document.getElementById('rejectionModalLoading');
      const contentEl = document.getElementById('rejectionModalContent');
      const errorEl   = document.getElementById('rejectionModalError');
      loadingEl.style.display = 'block';
      contentEl.style.display = 'none';
      errorEl.style.display   = 'none';
      modal.show();
      fetch(`/api/bagian/documents/${dokumenId}/return-detail`, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
          'Accept': 'application/json'
        }
      })
      .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
      .then(data => {
        if (data.success) {
          document.getElementById('rejectionNomorAgenda').textContent = data.dokumen.nomor_agenda || '-';
          document.getElementById('rejectionNomorSpp').textContent    = data.dokumen.nomor_spp    || '-';
          document.getElementById('rejectionUraianSpp').textContent   = data.dokumen.uraian_spp   || '-';
          document.getElementById('rejectionNilaiRupiah').textContent = data.dokumen.nilai_rupiah  || '-';
          document.getElementById('rejectionTanggal').textContent     = data.rejected_at           || '-';
          document.getElementById('rejectionBy').textContent          = data.rejected_by           || 'Unknown';
          document.getElementById('rejectionReason').textContent      = data.rejection_reason      || 'Tidak ada alasan yang diberikan';
          loadingEl.style.display = 'none';
          contentEl.style.display = 'block';
        } else throw new Error(data.message || 'Gagal memuat data');
      })
      .catch(err => {
        loadingEl.style.display = 'none';
        errorEl.style.display   = 'block';
        document.getElementById('rejectionErrorMessage').textContent =
          'Gagal memuat detail: ' + (err.message || 'Terjadi kesalahan');
      });
    };
  </script>

{{-- Inline Edit Engine (Bagian) --}}
@php
  $ieKategoriList = [];
  $ieSubKriteriaList = [];
  $ieItemSubKriteriaList = [];
  $ieJenisPembayaranList = [];
  try {
    $ieKategoriList = \App\Models\KategoriKriteria::where('tipe', 'Keluar')->get(['id_kategori_kriteria as id', 'nama_kriteria'])->toArray();
    $ieSubKriteriaList = \App\Models\SubKriteria::all(['id_sub_kriteria as id', 'nama_sub_kriteria', 'id_kategori_kriteria'])->toArray();
    $ieItemSubKriteriaList = \App\Models\ItemSubKriteria::all(['id_item_sub_kriteria as id', 'nama_item_sub_kriteria', 'id_sub_kriteria'])->toArray();
    $ieJenisPembayaranList = \App\Models\JenisPembayaran::orderBy('nama_jenis_pembayaran')->get(['id_jenis_pembayaran', 'nama_jenis_pembayaran'])->toArray();
  } catch (\Exception $e) {}
@endphp
@include('partials._inlineEditEngine', [
  'ieKategoriList'        => $ieKategoriList,
  'ieSubKriteriaList'     => $ieSubKriteriaList,
  'ieItemSubKriteriaList' => $ieItemSubKriteriaList,
  'ieJenisPembayaranList' => $ieJenisPembayaranList,
])

{{-- Active Cell Navigation (Spreadsheet-style arrow key navigation) --}}
@include('partials._activeCellNav', ['tableSelector' => '.data-table'])

@endsection
