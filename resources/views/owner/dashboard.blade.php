@extends('layouts/app')

@section('content')
<style>
/* ========================================
   WORLD-CLASS EXECUTIVE DASHBOARD DESIGN
   ======================================== */

:root {
  --primary: #083E40;
  --primary-light: #0a4f52;
  --primary-dark: #062d2f;
  --accent: #889717;
  --accent-light: #9ab01f;
  --success: #10b981;
  --warning: #f59e0b;
  --danger: #ef4444;
  --info: #3b82f6;
  --gray-50: #f9fafb;
  --gray-100: #f3f4f6;
  --gray-200: #e5e7eb;
  --gray-300: #d1d5db;
  --gray-600: #4b5563;
  --gray-700: #374151;
  --gray-900: #111827;
  --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

body {
  background: var(--gray-50) !important;
  font-family: 'Inter', 'Segoe UI', system-ui, -apple-system, sans-serif;
  color: var(--gray-900);
  font-size: 14px;
}

/* ========== EXECUTIVE HEADER ========== */
.executive-header {
  background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
  padding: 2.5rem 0;
  margin-bottom: 2rem;
  box-shadow: var(--shadow-lg);
  position: relative;
  overflow: hidden;
}

.executive-header::before {
  content: '';
  position: absolute;
  top: 0;
  right: 0;
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, rgba(136, 151, 23, 0.1) 0%, transparent 70%);
  border-radius: 50%;
}

.executive-title {
  color: white;
  font-size: 32px;
  font-weight: 700;
  margin: 0 0 0.5rem 0;
  letter-spacing: -0.5px;
}

.executive-subtitle {
  color: rgba(255, 255, 255, 0.8);
  font-size: 16px;
  margin: 0;
  font-weight: 400;
}

.executive-timestamp {
  color: rgba(255, 255, 255, 0.6);
  font-size: 13px;
  margin-top: 0.5rem;
}

/* ========== KPI CARDS ========== */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.kpi-card {
  background: white;
  border-radius: 16px;
  padding: 1.75rem;
  box-shadow: var(--shadow);
  border: 1px solid var(--gray-200);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.kpi-card:hover {
  transform: translateY(-4px);
  box-shadow: var(--shadow-xl);
}

.kpi-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  background: var(--card-color, var(--primary));
}

.kpi-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.kpi-label {
  font-size: 13px;
  font-weight: 600;
  color: var(--gray-600);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.kpi-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  background: var(--icon-bg, var(--gray-100));
  color: var(--icon-color, var(--primary));
}

.kpi-value {
  font-size: 36px;
  font-weight: 700;
  color: var(--gray-900);
  line-height: 1;
  margin-bottom: 0.5rem;
}

.kpi-value.large {
  font-size: 28px;
}

.kpi-trend {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 13px;
  font-weight: 500;
}

.trend-up {
  color: var(--success);
}

.trend-down {
  color: var(--danger);
}

.trend-neutral {
  color: var(--gray-600);
}

/* ========== ANALYTICS SECTION ========== */
.analytics-section {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.analytics-card {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: var(--shadow);
  border: 1px solid var(--gray-200);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid var(--gray-100);
}

.card-title {
  font-size: 18px;
  font-weight: 700;
  color: var(--gray-900);
  margin: 0;
}

.card-action {
  font-size: 13px;
  color: var(--primary);
  text-decoration: none;
  font-weight: 600;
  transition: color 0.2s;
}

.card-action:hover {
  color: var(--primary-light);
}

/* ========== DEPARTMENT METRICS ========== */
.department-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.department-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem;
  background: var(--gray-50);
  border-radius: 12px;
  border: 1px solid var(--gray-200);
  transition: all 0.2s;
}

.department-item:hover {
  background: white;
  box-shadow: var(--shadow-sm);
}

.department-info {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.department-avatar {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  background: var(--dept-color);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 700;
  font-size: 16px;
}

.department-name {
  font-weight: 600;
  color: var(--gray-900);
  margin-bottom: 0.25rem;
}

.department-count {
  font-size: 13px;
  color: var(--gray-600);
}

.department-progress {
  flex: 1;
  max-width: 200px;
}

.progress-bar {
  height: 8px;
  background: var(--gray-200);
  border-radius: 999px;
  overflow: hidden;
  margin-bottom: 0.25rem;
}

.progress-fill {
  height: 100%;
  background: var(--dept-color);
  border-radius: 999px;
  transition: width 0.5s ease;
}

.progress-label {
  font-size: 12px;
  color: var(--gray-600);
  font-weight: 600;
}

/* ========== BOTTLENECK ALERTS ========== */
.alert-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.alert-item {
  padding: 1rem;
  background: #fef3c7;
  border-left: 4px solid var(--warning);
  border-radius: 8px;
  font-size: 13px;
}

.alert-item.critical {
  background: #fee2e2;
  border-left-color: var(--danger);
}

.alert-title {
  font-weight: 600;
  color: var(--gray-900);
  margin-bottom: 0.25rem;
}

.alert-desc {
  color: var(--gray-700);
}

/* ========== STATUS BADGES ========== */
.status-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 999px;
  font-size: 13px;
  font-weight: 600;
}

.status-badge.success {
  background: #d1fae5;
  color: #065f46;
}

.status-badge.warning {
  background: #fef3c7;
  color: #92400e;
}

.status-badge.danger {
  background: #fee2e2;
  color: #991b1b;
}

.status-badge.info {
  background: #dbeafe;
  color: #1e40af;
}

/* ========== QUICK STATS ========== */
.quick-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-item {
  text-align: center;
  padding: 1.5rem;
  background: white;
  border-radius: 12px;
  border: 1px solid var(--gray-200);
}

.stat-value {
  font-size: 32px;
  font-weight: 700;
  color: var(--stat-color);
  margin-bottom: 0.5rem;
}

.stat-label {
  font-size: 13px;
  color: var(--gray-600);
  font-weight: 500;
}

/* ========== SEARCH BOX ========== */
.search-container {
  background: white;
  padding: 1.5rem;
  border-radius: 16px;
  box-shadow: var(--shadow);
  border: 1px solid var(--gray-200);
  margin-bottom: 2rem;
}

.search-input {
  width: 100%;
  padding: 0.875rem 1rem 0.875rem 3rem;
  border: 2px solid var(--gray-300);
  border-radius: 12px;
  font-size: 15px;
  transition: all 0.2s;
  background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236b7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'%3E%3C/path%3E%3C/svg%3E") no-repeat 12px center;
  background-size: 20px;
}

.search-input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(8, 62, 64, 0.1);
}

/* ========== RESPONSIVE ========== */
@media (max-width: 1024px) {
  .analytics-section {
    grid-template-columns: 1fr;
  }
  
  .kpi-grid {
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  }
}

@media (max-width: 768px) {
  .executive-title {
    font-size: 24px;
  }
  
  .kpi-value {
    font-size: 28px;
  }
  
  .quick-stats {
    grid-template-columns: 1fr;
  }
}

/* ========== ANIMATIONS ========== */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.fade-in-up {
  animation: fadeInUp 0.5s ease-out forwards;
}

.fade-in-up:nth-child(1) { animation-delay: 0.1s; }
.fade-in-up:nth-child(2) { animation-delay: 0.2s; }
.fade-in-up:nth-child(3) { animation-delay: 0.3s; }
.fade-in-up:nth-child(4) { animation-delay: 0.4s; }
</style>

<!-- EXECUTIVE HEADER -->
<div class="executive-header">
  <div class="container">
    <h1 class="executive-title">
      <i class="fas fa-chart-line me-3"></i>Dashboard Eksekutif
    </h1>
    <p class="executive-subtitle">Monitoring dan Analisis Sistem Dokumen SPP PTPN</p>
    <p class="executive-timestamp">
      <i class="far fa-clock me-2"></i>
      Terakhir diperbarui: {{ now()->locale('id')->isoFormat('dddd, D MMMM YYYY - HH:mm') }} WIB
    </p>
  </div>
</div>

<div class="container">

  <!-- KEY PERFORMANCE INDICATORS -->
  <div class="kpi-grid">
    <!-- Total Dokumen -->
    <div class="kpi-card fade-in-up" style="--card-color: var(--primary);">
      <div class="kpi-header">
        <div>
          <div class="kpi-label">Total Dokumen</div>
        </div>
        <div class="kpi-icon" style="--icon-bg: rgba(8, 62, 64, 0.1); --icon-color: var(--primary);">
          <i class="fas fa-file-alt"></i>
        </div>
      </div>
      <div class="kpi-value">{{ number_format($totalDokumen) }}</div>
      <div class="kpi-trend trend-neutral">
        <i class="fas fa-calendar-day"></i>
        <span>{{ $todayDokumen }} dokumen hari ini</span>
      </div>
    </div>

    <!-- Total Nilai -->
    <div class="kpi-card fade-in-up" style="--card-color: var(--success);">
      <div class="kpi-header">
        <div>
          <div class="kpi-label">Total Nilai Dokumen</div>
        </div>
        <div class="kpi-icon" style="--icon-bg: rgba(16, 185, 129, 0.1); --icon-color: var(--success);">
          <i class="fas fa-money-bill-wave"></i>
        </div>
      </div>
      <div class="kpi-value large">Rp {{ number_format($totalNilaiDokumen / 1000000, 1) }}M</div>
      <div class="kpi-trend trend-up">
        <i class="fas fa-arrow-up"></i>
        <span>Rp {{ number_format($nilaiSelesai / 1000000, 1) }}M terbayar</span>
      </div>
    </div>

    <!-- Completion Rate -->
    <div class="kpi-card fade-in-up" style="--card-color: var(--info);">
      <div class="kpi-header">
        <div>
          <div class="kpi-label">Tingkat Penyelesaian</div>
        </div>
        <div class="kpi-icon" style="--icon-bg: rgba(59, 130, 246, 0.1); --icon-color: var(--info);">
          <i class="fas fa-check-circle"></i>
        </div>
      </div>
      <div class="kpi-value">{{ $totalDokumen > 0 ? number_format(($dokumenSelesai / $totalDokumen) * 100, 1) : 0 }}%</div>
      <div class="kpi-trend trend-up">
        <i class="fas fa-arrow-up"></i>
        <span>{{ $todaySelesai }} selesai hari ini</span>
      </div>
    </div>

    <!-- Processing Time -->
    <div class="kpi-card fade-in-up" style="--card-color: var(--accent);">
      <div class="kpi-header">
        <div>
          <div class="kpi-label">Rata-rata Waktu Proses</div>
        </div>
        <div class="kpi-icon" style="--icon-bg: rgba(136, 151, 23, 0.1); --icon-color: var(--accent);">
          <i class="fas fa-clock"></i>
        </div>
      </div>
      <div class="kpi-value">{{ number_format($avgProcessingTime, 1) }}</div>
      <div class="kpi-trend trend-neutral">
        <i class="fas fa-business-time"></i>
        <span>Hari kerja</span>
      </div>
    </div>

    <!-- In Progress -->
    <div class="kpi-card fade-in-up" style="--card-color: var(--warning);">
      <div class="kpi-header">
        <div>
          <div class="kpi-label">Sedang Diproses</div>
        </div>
        <div class="kpi-icon" style="--icon-bg: rgba(245, 158, 11, 0.1); --icon-color: var(--warning);">
          <i class="fas fa-spinner"></i>
        </div>
      </div>
      <div class="kpi-value">{{ number_format($dokumenProses) }}</div>
      <div class="kpi-trend trend-neutral">
        <span>Rp {{ number_format($nilaiProses / 1000000, 1) }}M dalam proses</span>
      </div>
    </div>

    <!-- Overdue -->
    <div class="kpi-card fade-in-up" style="--card-color: var(--danger);">
      <div class="kpi-header">
        <div>
          <div class="kpi-label">Melewati Deadline</div>
        </div>
        <div class="kpi-icon" style="--icon-bg: rgba(239, 68, 68, 0.1); --icon-color: var(--danger);">
          <i class="fas fa-exclamation-triangle"></i>
        </div>
      </div>
      <div class="kpi-value">{{ number_format($dokumenOverdue) }}</div>
      <div class="kpi-trend" :class="$dokumenOverdue > 0 ? 'trend-down' : 'trend-up'">
        <i class="fas fa-{{ $dokumenOverdue > 0 ? 'exclamation-circle' : 'check-circle' }}"></i>
        <span>{{ $dokumenOverdue > 0 ? 'Perlu perhatian segera' : 'Semua terkendali' }}</span>
      </div>
    </div>
  </div>

  <!-- QUICK TODAY STATS -->
  <div class="quick-stats">
    <div class="stat-item">
      <div class="stat-value" style="--stat-color: var(--success);">
        {{ number_format($statusDistribution['selesai']) }}
      </div>
      <div class="stat-label">Dokumen Selesai</div>
    </div>
    <div class="stat-item">
      <div class="stat-value" style="--stat-color: var(--warning);">
        {{ number_format($statusDistribution['pending']) }}
      </div>
      <div class="stat-label">Menunggu Approval</div>
    </div>
    <div class="stat-item">
      <div class="stat-value" style="--stat-color: var(--danger);">
        {{ number_format($statusDistribution['returned']) }}
      </div>
      <div class="stat-label">Dokumen Dikembalikan</div>
    </div>
  </div>

  <!-- ANALYTICS SECTION -->
  <div class="analytics-section">
    <!-- Department Performance -->
    <div class="analytics-card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-users me-2"></i>Performa Departemen
        </h3>
        <a href="#" class="card-action">Lihat Detail →</a>
      </div>
      
      <div class="department-list">
        @php
          $deptColors = [
            'ibuB' => '#3b82f6',
            'perpajakan' => '#8b5cf6',
            'akutansi' => '#ec4899',
            'pembayaran' => '#10b981'
          ];
          $deptNames = [
            'ibuB' => 'Ibu Yuni (IbuB)',
            'perpajakan' => 'Perpajakan',
            'akutansi' => 'Akutansi',
            'pembayaran' => 'Pembayaran'
          ];
        @endphp
        
        @foreach($departmentMetrics as $dept => $metrics)
        <div class="department-item">
          <div class="department-info">
            <div class="department-avatar" style="background: {{ $deptColors[$dept] }};">
              {{ strtoupper(substr($dept, 0, 2)) }}
            </div>
            <div>
              <div class="department-name">{{ $deptNames[$dept] }}</div>
              <div class="department-count">{{ $metrics['total'] }} dokumen</div>
            </div>
          </div>
          <div class="department-progress">
            @php
              $percentage = $metrics['total'] > 0 ? ($metrics['completed'] / $metrics['total']) * 100 : 0;
            @endphp
            <div class="progress-bar">
              <div class="progress-fill" style="width: {{ $percentage }}%; --dept-color: {{ $deptColors[$dept] }};"></div>
            </div>
            <div class="progress-label">{{ number_format($percentage, 0) }}% selesai</div>
          </div>
        </div>
        @endforeach
      </div>
    </div>

    <!-- Bottleneck Alerts -->
    <div class="analytics-card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-exclamation-circle me-2"></i>Peringatan Sistem
        </h3>
      </div>
      
      <div class="alert-list">
        @if($dokumenOverdue > 0)
        <div class="alert-item critical">
          <div class="alert-title">🚨 {{ $dokumenOverdue }} Dokumen Melewati Deadline</div>
          <div class="alert-desc">Segera review dokumen yang telah melewati deadline untuk menghindari keterlambatan lebih lanjut.</div>
        </div>
        @endif
        
        @if(count($bottlenecks) > 0)
        <div class="alert-item">
          <div class="alert-title">⚠️ {{ count($bottlenecks) }} Dokumen Tertunda >7 Hari</div>
          <div class="alert-desc">Ada dokumen yang tertunda di sistem lebih dari 7 hari. Cek bottleneck untuk efisiensi proses.</div>
        </div>
        @endif
        
        @if($dokumenProses > ($totalDokumen * 0.7))
        <div class="alert-item">
          <div class="alert-title">📊 Volume Tinggi Dokumen Dalam Proses</div>
          <div class="alert-desc">Saat ini {{ number_format(($dokumenProses / $totalDokumen) * 100, 0) }}% dokumen sedang diproses. Pertimbangkan alokasi resource tambahan.</div>
        </div>
        @endif
        
        @if($dokumenOverdue == 0 && count($bottlenecks) == 0)
        <div class="alert-item" style="background: #d1fae5; border-left-color: var(--success);">
          <div class="alert-title">✅ Sistem Berjalan Normal</div>
          <div class="alert-desc">Tidak ada peringatan saat ini. Semua dokumen terproses dengan baik.</div>
        </div>
        @endif
      </div>
    </div>
  </div>

  <!-- SEARCH & DOCUMENTS -->
  <div class="search-container">
    <form method="GET" action="{{ url('/owner/dashboard') }}">
      <input type="text" 
             name="search" 
             class="search-input" 
             placeholder="Cari dokumen berdasarkan nomor agenda, SPP, nilai, atau field lainnya..."
             value="{{ $search ?? '' }}">
    </form>
  </div>

  <!-- Document List would go here (use existing document cards) -->
  @if($documents->isEmpty())
    <div class="analytics-card text-center" style="padding: 4rem 2rem;">
      <i class="fas fa-folder-open" style="font-size: 64px; color: var(--gray-300); margin-bottom: 1rem;"></i>
      <h4 style="color: var(--gray-600); margin-bottom: 0.5rem;">Tidak ada dokumen</h4>
      <p style="color: var(--gray-500);">
        @if(isset($search) && !empty($search))
          Tidak ditemukan dokumen dengan kata kunci "{{ $search }}"
        @else
          Belum ada dokumen dalam sistem
        @endif
      </p>
    </div>
  @else
    <!-- Existing document cards from original dashboard -->
    <div style="display: grid; gap: 1rem;">
      @foreach($documents as $index => $dokumen)
        <!-- Use your existing document card design here -->
        <div style="background: white; padding: 1.5rem; border-radius: 12px; border: 1px solid var(--gray-200);">
          <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
            <div>
              <h4 style="margin: 0 0 0.5rem 0; color: var(--gray-900);">
                #{{ $dokumen['nomor_agenda'] ?? 'N/A' }}
              </h4>
              <p style="margin: 0; color: var(--gray-600); font-size: 14px;">
                {{ $dokumen['nomor_spp'] ?? 'N/A' }}
              </p>
            </div>
            <span class="status-badge {{ $dokumen['is_overdue'] ? 'danger' : 'success' }}">
              {{ $dokumen['is_overdue'] ? 'Terlambat' : 'Normal' }}
            </span>
          </div>
          
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
            <div>
              <div style="font-size: 13px; color: var(--gray-600); margin-bottom: 0.25rem;">Nilai Dokumen</div>
              <div style="font-size: 18px; font-weight: 600; color: var(--primary);">
                Rp {{ number_format($dokumen['nilai_rupiah'] ?? 0, 0, ',', '.') }}
              </div>
            </div>
            <div>
              <div style="font-size: 13px; color: var(--gray-600); margin-bottom: 0.25rem;">Handler Saat Ini</div>
              <div style="font-size: 14px; font-weight: 600; color: var(--gray-900);">
                {{ $dokumen['current_handler_display'] ?? 'N/A' }}
              </div>
            </div>
            <div>
              <div style="font-size: 13px; color: var(--gray-600); margin-bottom: 0.25rem;">Progress</div>
              <div style="font-size: 14px; font-weight: 600; color: var(--accent);">
                {{ $dokumen['progress_percentage'] ?? 0 }}%
              </div>
            </div>
          </div>
          
          <a href="{{ url('/owner/workflow/' . $dokumen['id']) }}" 
             style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--primary); text-decoration: none; font-weight: 600; font-size: 14px;">
            Lihat Detail
            <i class="fas fa-arrow-right"></i>
          </a>
        </div>
      @endforeach
    </div>
  @endif

</div>

@endsection
