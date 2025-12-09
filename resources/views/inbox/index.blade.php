@extends('layouts.app')

@section('title', 'Inbox - Dokumen Menunggu Persetujuan')

@push('styles')
<style>
/* ============================================
   MODERN INBOX DESIGN - CLEAN & PROFESSIONAL
   ============================================ */

:root {
    --primary-color: #083E40;
    --primary-light: #0a4f52;
    --secondary-color: #889717;
    --accent-blue: #4299e1;
    --accent-green: #48bb78;
    --accent-orange: #f6ad55;
    --accent-red: #f56565;
    --bg-light: #f7fafc;
    --bg-white: #ffffff;
    --text-primary: #1a202c;
    --text-secondary: #4a5568;
    --text-muted: #718096;
    --border-color: #e2e8f0;
    --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.07);
    --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
    --shadow-xl: 0 20px 25px rgba(0, 0, 0, 0.1);
}

/* Base Container */
.inbox-container {
    background: var(--bg-light);
    min-height: calc(100vh - 80px);
    padding: 2rem 1.5rem;
}

/* ============================================
   HEADER SECTION - Clean & Professional
   ============================================ */
.inbox-header {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-light) 100%);
    border-radius: 16px;
    padding: 2rem 2.5rem;
    margin-bottom: 1.5rem;
    box-shadow: var(--shadow-lg);
    position: relative;
    overflow: hidden;
}

.inbox-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 300px;
    height: 300px;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 50%;
}

.inbox-header-content {
    position: relative;
    z-index: 10;
}

.greeting-section {
    color: white;
}

.greeting-time {
    font-size: 0.8125rem;
    font-weight: 500;
    opacity: 0.85;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.greeting-name {
    font-size: 1.875rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.greeting-name .wave {
    font-size: 1.875rem;
    animation: wave 2s ease-in-out infinite;
}

@keyframes wave {
    0%, 100% { transform: rotate(0deg); }
    10%, 30% { transform: rotate(14deg); }
    20% { transform: rotate(-8deg); }
    40%, 60% { transform: rotate(0deg); }
    50% { transform: rotate(-10deg); }
}

.greeting-subtitle {
    font-size: 0.9375rem;
    opacity: 0.9;
    line-height: 1.6;
    margin: 0;
}

.breadcrumb-custom {
    background: rgba(255, 255, 255, 0.12);
    backdrop-filter: blur(10px);
    border-radius: 10px;
    padding: 0.625rem 1rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.breadcrumb-custom a {
    color: white;
    text-decoration: none;
    opacity: 0.85;
    transition: opacity 0.2s;
    font-size: 0.875rem;
}

.breadcrumb-custom a:hover {
    opacity: 1;
}

.breadcrumb-custom .separator {
    opacity: 0.6;
    margin: 0 0.375rem;
}

.breadcrumb-custom .current {
    color: white;
    font-weight: 600;
    opacity: 1;
    font-size: 0.875rem;
}

/* ============================================
   STATS CARDS - Clean & Organized
   ============================================ */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.25rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: var(--bg-white);
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border-color);
    transition: all 0.2s ease;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: var(--accent-color);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    border-color: var(--accent-color);
}

.stat-card:hover::before {
    transform: scaleX(1);
}

.stat-card.waiting {
    --accent-color: var(--accent-orange);
}

.stat-card.approved {
    --accent-color: var(--accent-green);
}

.stat-card.total {
    --accent-color: var(--accent-blue);
}

.stat-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.stat-icon-wrapper {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.375rem;
    color: white;
    flex-shrink: 0;
}

.stat-card.waiting .stat-icon-wrapper {
    background: linear-gradient(135deg, #f6ad55 0%, #fed7aa 100%);
}

.stat-card.approved .stat-icon-wrapper {
    background: linear-gradient(135deg, #48bb78 0%, #9ae6b4 100%);
}

.stat-card.total .stat-icon-wrapper {
    background: linear-gradient(135deg, #4299e1 0%, #90cdf4 100%);
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1.2;
    margin-bottom: 0.375rem;
}

.stat-label {
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* ============================================
   DOCUMENT SECTION - Clean Card Design
   ============================================ */
.documents-section {
    background: var(--bg-white);
    border-radius: 16px;
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--border-color);
    overflow: hidden;
}

.documents-header {
    padding: 1.75rem 2rem;
    border-bottom: 1px solid var(--border-color);
    background: var(--bg-white);
}

.documents-title {
    font-size: 1.375rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.375rem;
    display: flex;
    align-items: center;
    gap: 0.625rem;
}

.documents-title i {
    color: var(--accent-blue);
    font-size: 1.25rem;
}

.documents-subtitle {
    color: var(--text-muted);
    font-size: 0.875rem;
    margin: 0;
}

.documents-count {
    background: linear-gradient(135deg, var(--accent-blue) 0%, #63b3ed 100%);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.8125rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 2px 8px rgba(66, 153, 225, 0.25);
}

/* ============================================
   SEARCH & FILTER - Clean Input Design
   ============================================ */
.search-filter-section {
    padding: 1.5rem 2rem;
    background: var(--bg-light);
    border-bottom: 1px solid var(--border-color);
}

.search-filter-wrapper {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}

.search-input-wrapper {
    flex: 1;
    min-width: 280px;
    position: relative;
}

.search-input {
    width: 100%;
    padding: 0.75rem 1rem 0.75rem 2.75rem;
    border: 1.5px solid var(--border-color);
    border-radius: 10px;
    font-size: 0.875rem;
    transition: all 0.2s ease;
    background: var(--bg-white);
    color: var(--text-primary);
}

.search-input:focus {
    outline: none;
    border-color: var(--accent-blue);
    box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
}

.search-input::placeholder {
    color: var(--text-muted);
}

.search-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 0.875rem;
}

.filter-select {
    padding: 0.75rem 1rem;
    border: 1.5px solid var(--border-color);
    border-radius: 10px;
    font-size: 0.875rem;
    background: var(--bg-white);
    color: var(--text-primary);
    cursor: pointer;
    transition: all 0.2s ease;
    min-width: 160px;
}

.filter-select:focus {
    outline: none;
    border-color: var(--accent-blue);
    box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
}

/* ============================================
   DOCUMENT CARDS - Clean Card Layout
   ============================================ */
.documents-list {
    padding: 1.5rem 2rem;
}

.document-card {
    background: var(--bg-white);
    border: 1.5px solid var(--border-color);
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1rem;
    transition: all 0.2s ease;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.document-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 3px;
    height: 100%;
    background: var(--accent-orange);
    transform: scaleY(0);
    transform-origin: top;
    transition: transform 0.2s ease;
}

.document-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    border-color: var(--accent-blue);
}

.document-card:hover::before {
    transform: scaleY(1);
}

.document-card:last-child {
    margin-bottom: 0;
}

.document-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
    gap: 1rem;
}

.document-number {
    flex: 1;
    min-width: 0;
}

.document-agenda {
    font-size: 1rem;
    font-weight: 700;
    color: var(--accent-blue);
    margin-bottom: 0.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.document-agenda i {
    font-size: 0.875rem;
    flex-shrink: 0;
}

.document-spp {
    font-size: 0.8125rem;
    color: var(--text-secondary);
    font-weight: 500;
}

.document-status-badge {
    background: linear-gradient(135deg, var(--accent-orange) 0%, #fed7aa 100%);
    color: white;
    padding: 0.4375rem 0.875rem;
    border-radius: 20px;
    font-size: 0.6875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
    box-shadow: 0 2px 6px rgba(246, 173, 85, 0.25);
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
}

.document-status-badge i {
    font-size: 0.625rem;
}

.document-body {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1.25rem;
    margin-bottom: 1rem;
}

.document-info-item {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.document-info-label {
    font-size: 0.6875rem;
    font-weight: 600;
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.document-info-value {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.document-info-value i {
    color: var(--accent-blue);
    font-size: 0.8125rem;
    flex-shrink: 0;
}

.document-info-value.text-success {
    color: var(--accent-green);
    font-size: 0.9375rem;
}

.document-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
    border-top: 1px solid var(--border-color);
    gap: 1rem;
    flex-wrap: wrap;
}

.document-time {
    font-size: 0.8125rem;
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.document-time i {
    font-size: 0.75rem;
}

.btn-view-detail {
    background: linear-gradient(135deg, var(--accent-blue) 0%, #63b3ed 100%);
    color: white;
    padding: 0.625rem 1.25rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.8125rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(66, 153, 225, 0.25);
}

.btn-view-detail:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(66, 153, 225, 0.35);
    color: white;
    text-decoration: none;
}

.btn-view-detail i {
    font-size: 0.75rem;
}

/* ============================================
   EMPTY STATE - Clean & Encouraging
   ============================================ */
.empty-state {
    padding: 3.5rem 2rem;
    text-align: center;
}

.empty-state-icon {
    width: 140px;
    height: 140px;
    margin: 0 auto 1.5rem;
    background: linear-gradient(135deg, #f0f4f8 0%, #e2e8f0 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3.5rem;
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-8px); }
}

.empty-state-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.empty-state-subtitle {
    font-size: 0.9375rem;
    color: var(--text-muted);
    line-height: 1.6;
    max-width: 480px;
    margin: 0 auto 1.5rem;
}

.btn-refresh {
    background: linear-gradient(135deg, var(--accent-blue) 0%, #63b3ed 100%);
    color: white;
    padding: 0.75rem 1.75rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.875rem;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
    box-shadow: 0 2px 8px rgba(66, 153, 225, 0.25);
}

.btn-refresh:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(66, 153, 225, 0.35);
}

/* ============================================
   PAGINATION - Clean Design
   ============================================ */
.pagination-wrapper {
    padding: 1.5rem 2rem;
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: center;
    background: var(--bg-light);
}

.pagination-wrapper .pagination {
    margin: 0;
}

.pagination-wrapper .page-link {
    border-radius: 8px;
    margin: 0 0.25rem;
    border: 1.5px solid var(--border-color);
    color: var(--text-primary);
    padding: 0.5rem 0.875rem;
    transition: all 0.2s ease;
    font-size: 0.875rem;
}

.pagination-wrapper .page-link:hover {
    background: var(--accent-blue);
    color: white;
    border-color: var(--accent-blue);
}

.pagination-wrapper .page-item.active .page-link {
    background: var(--accent-blue);
    border-color: var(--accent-blue);
    color: white;
}

/* ============================================
   RESPONSIVE DESIGN
   ============================================ */
@media (max-width: 768px) {
    .inbox-container {
        padding: 1rem;
    }

    .inbox-header {
        padding: 1.5rem;
        border-radius: 12px;
    }

    .greeting-name {
        font-size: 1.5rem;
    }

    .stats-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .search-filter-wrapper {
        flex-direction: column;
    }

    .search-input-wrapper {
        min-width: 100%;
    }

    .filter-select {
        width: 100%;
    }

    .documents-header {
        padding: 1.25rem 1.5rem;
    }

    .documents-list {
        padding: 1.25rem 1.5rem;
    }

    .document-body {
        grid-template-columns: 1fr;
        gap: 1rem;
    }

    .document-footer {
        flex-direction: column;
        align-items: stretch;
    }

    .btn-view-detail {
        width: 100%;
        justify-content: center;
    }

    .search-filter-section {
        padding: 1.25rem 1.5rem;
    }
}

/* ============================================
   UTILITY CLASSES
   ============================================ */
.text-truncate-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* ============================================
   NOTIFICATION BADGE
   ============================================ */
.new-documents-badge {
    background: linear-gradient(135deg, #f56565 0%, #fc8181 100%);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.8125rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 2px 8px rgba(245, 101, 101, 0.3);
    animation: pulse 2s ease-in-out infinite;
}

.new-documents-badge i {
    font-size: 0.875rem;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 2px 8px rgba(245, 101, 101, 0.3);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(245, 101, 101, 0.5);
    }
}

.new-badge {
    background: linear-gradient(135deg, #48bb78 0%, #9ae6b4 100%);
    color: white;
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
    box-shadow: 0 2px 6px rgba(72, 187, 120, 0.3);
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    animation: newBadgePulse 2s ease-in-out infinite;
}

.new-badge i {
    font-size: 0.5rem;
}

@keyframes newBadgePulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.8;
    }
}
</style>
@endpush

@section('content')
<div class="inbox-container">
    <div class="container-fluid px-0">
        <!-- Header Section -->
        <div class="inbox-header">
            <div class="inbox-header-content">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <div class="greeting-section">
                            <div class="greeting-time">
                                @php
                                    $hour = date('H');
                                    $greeting = $hour < 12 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 18 ? 'Selamat Sore' : 'Selamat Malam'));
                                    $userName = $userRole === 'IbuB' ? 'Ibu Yuni' : ucfirst($userRole);
                                @endphp
                                {{ $greeting }}
                            </div>
                            <h1 class="greeting-name">
                                <span>{{ $userName }}</span>
                                <span class="wave">👋</span>
                            </h1>
                            <p class="greeting-subtitle">
                                Kelola dan persetujui dokumen yang masuk ke inbox Anda dengan mudah dan efisien.
                            </p>
                        </div>
                    </div>
                    <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                        <div class="breadcrumb-custom">
                            @php
                                $dashboardUrl = match($userRole) {
                                    'IbuB' => '/dashboardB',
                                    'Perpajakan' => '/dashboardPerpajakan',
                                    'Akutansi' => '/dashboardAkutansi',
                                    default => '/dashboard'
                                };
                            @endphp
                            <a href="{{ url($dashboardUrl) }}">Home</a>
                            <span class="separator">/</span>
                            <span class="current">Inbox</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card waiting">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-value">{{ $documents->total() }}</div>
                        <div class="stat-label">Menunggu Persetujuan</div>
                    </div>
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card approved">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-value">{{ $approvedToday ?? 0 }}</div>
                        <div class="stat-label">Disetujui Hari Ini</div>
                    </div>
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>

            <div class="stat-card total">
                <div class="stat-card-header">
                    <div>
                        <div class="stat-value">{{ $totalProcessed ?? 0 }}</div>
                        <div class="stat-label">Total Diproses</div>
                    </div>
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents Section -->
        <div class="documents-section">
            <div class="documents-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="documents-title">
                            <i class="fas fa-inbox"></i>
                            Dokumen Menunggu Persetujuan
                        </h2>
                        <p class="documents-subtitle">Tinjau dan berikan persetujuan untuk dokumen yang masuk</p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <div class="d-flex align-items-center justify-content-md-end gap-2 flex-wrap">
                            @if($newDocumentsCount > 0)
                            <span class="new-documents-badge" id="newDocumentsBadge">
                                <i class="fas fa-bell"></i>
                                <span id="newDocumentsCount">{{ $newDocumentsCount }}</span> Baru
                            </span>
                            @endif
                            <span class="documents-count">
                                <i class="fas fa-file-alt"></i>
                                {{ $documents->total() }} Dokumen
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            @if($documents->count() > 0)
                <!-- Search & Filter -->
                <div class="search-filter-section">
                    <div class="search-filter-wrapper">
                        <div class="search-input-wrapper">
                            <i class="fas fa-search search-icon"></i>
                            <input type="text" 
                                   class="search-input" 
                                   id="searchInput"
                                   placeholder="Cari berdasarkan nomor agenda, SPP, atau uraian...">
                        </div>
                        <select class="filter-select" id="filterSelect">
                            <option value="">Semua Status</option>
                            <option value="pending">Menunggu Persetujuan</option>
                            <option value="approved">Disetujui</option>
                            <option value="rejected">Ditolak</option>
                        </select>
                    </div>
                </div>

                <!-- Documents List -->
                <div class="documents-list" id="documentsList">
                    @foreach($documents as $dokumen)
                    <div class="document-card clickable-card" 
                         data-agenda="{{ strtolower($dokumen->nomor_agenda) }}" 
                         data-spp="{{ strtolower($dokumen->nomor_spp) }}" 
                         data-uraian="{{ strtolower($dokumen->uraian_spp ?? '') }}"
                         onclick="handleItemClick(event, '{{ route('inbox.show', $dokumen) }}')">
                        <div class="document-card-header">
                            <div class="document-number">
                                <div class="document-agenda">
                                    <i class="fas fa-file-invoice"></i>
                                    <span>{{ $dokumen->nomor_agenda }}</span>
                                </div>
                                <div class="document-spp select-text">{{ $dokumen->nomor_spp }}</div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @php
                                    $isNew = $dokumen->inbox_approval_sent_at && $dokumen->inbox_approval_sent_at->gte(now()->subHours(24));
                                @endphp
                                @if($isNew)
                                <span class="new-badge">
                                    <i class="fas fa-star"></i>
                                    NEW
                                </span>
                                @endif
                                <div class="document-status-badge">
                                    <i class="fas fa-clock"></i>
                                    Menunggu
                                </div>
                            </div>
                        </div>

                        <div class="document-body">
                            <div class="document-info-item">
                                <div class="document-info-label">Uraian</div>
                                <div class="document-info-value text-truncate-2">
                                    {{ $dokumen->uraian_spp ?? '-' }}
                                </div>
                            </div>

                            <div class="document-info-item">
                                <div class="document-info-label">Pengirim</div>
                                <div class="document-info-value">
                                    <i class="fas fa-user"></i>
                                    <span>{{ $dokumen->getSenderDisplayName() }}</span>
                                </div>
                            </div>

                            <div class="document-info-item">
                                <div class="document-info-label">Tanggal Kirim</div>
                                <div class="document-info-value">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>{{ $dokumen->inbox_approval_sent_at ? $dokumen->inbox_approval_sent_at->format('d/m/Y H:i') : '-' }}</span>
                                </div>
                            </div>

                            <div class="document-info-item">
                                <div class="document-info-label">Nilai</div>
                                <div class="document-info-value text-success">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <span class="select-text">Rp {{ number_format($dokumen->nilai_rupiah, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="document-footer">
                            <div class="document-time">
                                <i class="far fa-clock"></i>
                                <span>Diterima {{ $dokumen->inbox_approval_sent_at ? $dokumen->inbox_approval_sent_at->diffForHumans() : '-' }}</span>
                            </div>
                            <a href="{{ route('inbox.show', $dokumen) }}" 
                               class="btn-view-detail"
                               onclick="event.stopPropagation();">
                                <i class="fas fa-eye"></i>
                                Lihat Detail
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                @if($documents->hasPages())
                <div class="pagination-wrapper">
                    {{ $documents->links() }}
                </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-check-circle" style="color: var(--accent-green);"></i>
                    </div>
                    <h3 class="empty-state-title">Semua Dokumen Telah Diproses! 🎉</h3>
                    <p class="empty-state-subtitle">
                        Tidak ada dokumen yang perlu diperiksa saat ini. 
                        Anda telah menyelesaikan semua persetujuan dokumen dengan baik.
                    </p>
                    <button class="btn-refresh" onclick="window.location.reload()">
                        <i class="fas fa-sync-alt"></i>
                        Refresh Halaman
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Notification Toast Container -->
<div id="notificationContainer"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle flash messages from server
    @if(session('success'))
        showNotification('success', 'Berhasil!', '{{ session('success') }}');
    @endif

    @if(session('error'))
        showNotification('error', 'Error!', '{{ session('error') }}');
    @endif

    // Initialize notification polling
    initNotificationPolling();

    const searchInput = document.getElementById('searchInput');
    const filterSelect = document.getElementById('filterSelect');
    const documentCards = document.querySelectorAll('.document-card');

    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            filterDocuments(searchTerm, filterSelect?.value || '');
        });
    }

    // Filter functionality
    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            const filterValue = this.value;
            filterDocuments(searchInput?.value.toLowerCase().trim() || '', filterValue);
        });
    }

    function filterDocuments(searchTerm, filterValue) {
        let visibleCount = 0;

        documentCards.forEach(card => {
            const agenda = card.getAttribute('data-agenda') || '';
            const spp = card.getAttribute('data-spp') || '';
            const uraian = card.getAttribute('data-uraian') || '';
            
            const matchesSearch = !searchTerm || 
                agenda.includes(searchTerm) || 
                spp.includes(searchTerm) || 
                uraian.includes(searchTerm);
            
            // For now, all cards are pending, so filter only affects search
            const matchesFilter = !filterValue || filterValue === 'pending';
            
            if (matchesSearch && matchesFilter) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Show message if no results
        const documentsList = document.getElementById('documentsList');
        if (documentsList) {
            let noResultsMsg = documentsList.querySelector('.no-results-message');
            if (visibleCount === 0 && documentCards.length > 0) {
                if (!noResultsMsg) {
                    noResultsMsg = document.createElement('div');
                    noResultsMsg.className = 'empty-state';
                    noResultsMsg.innerHTML = `
                        <div class="empty-state-icon">
                            <i class="fas fa-search" style="color: var(--text-muted);"></i>
                        </div>
                        <h3 class="empty-state-title">Tidak Ada Hasil</h3>
                        <p class="empty-state-subtitle">Tidak ada dokumen yang sesuai dengan pencarian Anda.</p>
                    `;
                    documentsList.appendChild(noResultsMsg);
                }
            } else if (noResultsMsg) {
                noResultsMsg.remove();
            }
        }
    }

    // Add smooth scroll to top when clicking pagination
    document.querySelectorAll('.pagination a').forEach(link => {
        link.addEventListener('click', function() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });
});

// Notification Polling System
function initNotificationPolling() {
    // Get last check time from localStorage
    let lastCheckTime = localStorage.getItem('inbox_last_check_time');
    if (!lastCheckTime) {
        // Set initial check time to now
        lastCheckTime = new Date().toISOString();
        localStorage.setItem('inbox_last_check_time', lastCheckTime);
    }

    // Function to check for new documents
    function checkNewDocuments() {
        fetch('{{ route("inbox.checkNew") }}?last_check_time=' + encodeURIComponent(lastCheckTime), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            credentials: 'same-origin'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update last check time
                if (data.current_time) {
                    lastCheckTime = data.current_time;
                    localStorage.setItem('inbox_last_check_time', lastCheckTime);
                }

                // If there are new documents
                if (data.new_documents_count > 0) {
                    // Update badge counter
                    updateNewDocumentsBadge(data.new_documents_count, data.pending_count);

                    // Track shown notifications to prevent duplicates
                    let shownNotificationIds = new Set(JSON.parse(localStorage.getItem('inbox_shown_notifications') || '[]'));
                    
                    // Filter out already shown notifications
                    const newDocsToShow = data.new_documents.filter(doc => {
                        if (shownNotificationIds.has(doc.id)) {
                            return false;
                        }
                        shownNotificationIds.add(doc.id);
                        return true;
                    });

                    // Save shown notification IDs to localStorage
                    localStorage.setItem('inbox_shown_notifications', JSON.stringify(Array.from(shownNotificationIds)));

                    // Show notification only for truly new documents
                    if (newDocsToShow.length > 0) {
                        // Show notification for each new document (only show one at a time)
                        if (newDocsToShow.length === 1) {
                            showNewDocumentNotification(newDocsToShow[0]);
                        } else {
                            // If multiple new documents, show notification for the latest one
                            showNewDocumentNotification(newDocsToShow[0]);
                        }

                        // Play notification sound only once
                        playNotificationSound();
                    }
                } else {
                    // Update badge even if no new documents (to sync count)
                    updateNewDocumentsBadge(0, data.pending_count);
                }
            }
        })
        .catch(error => {
            console.error('Error checking new documents:', error);
        });
    }

    // Check immediately on page load
    checkNewDocuments();

    // Poll every 30 seconds
    setInterval(checkNewDocuments, 30000);
}

// Update new documents badge
function updateNewDocumentsBadge(newCount, pendingCount) {
    const badge = document.getElementById('newDocumentsBadge');
    const countElement = document.getElementById('newDocumentsCount');
    
    if (newCount > 0) {
        if (!badge) {
            // Create badge if it doesn't exist
            const documentsHeader = document.querySelector('.documents-header .col-md-4');
            if (documentsHeader) {
                const badgeHtml = `
                    <span class="new-documents-badge" id="newDocumentsBadge">
                        <i class="fas fa-bell"></i>
                        <span id="newDocumentsCount">${newCount}</span> Baru
                    </span>
                `;
                documentsHeader.querySelector('.d-flex').insertAdjacentHTML('afterbegin', badgeHtml);
            }
        } else {
            // Update existing badge
            if (countElement) {
                countElement.textContent = newCount;
            }
            badge.style.display = 'inline-flex';
        }
    } else {
        // Hide badge if no new documents
        if (badge) {
            badge.style.display = 'none';
        }
    }
}

// Show notification for new document
function showNewDocumentNotification(doc) {
    const message = `Dokumen baru: ${doc.nomor_agenda} - ${doc.uraian_spp}`;
    showNotificationWithAction('info', 'Dokumen Baru Masuk!', message, doc.url, 'Lihat Dokumen');
}

// Play notification sound
function playNotificationSound() {
    // Create a simple beep sound using Web Audio API
    try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        oscillator.frequency.value = 800;
        oscillator.type = 'sine';
        
        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);
        
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.2);
    } catch (e) {
        // Fallback: browser notification if available
        if ('Notification' in window && Notification.permission === 'granted') {
            new Notification('Dokumen Baru Masuk', {
                body: 'Ada dokumen baru yang masuk ke inbox Anda',
                icon: '/favicon.ico'
            });
        }
    }
}

// Request notification permission on page load
if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
}

// Notification Toast Function
function showNotification(type, title, message) {
    const container = document.getElementById('notificationContainer');
    if (!container) {
        // Create container if it doesn't exist
        const newContainer = document.createElement('div');
        newContainer.id = 'notificationContainer';
        document.body.appendChild(newContainer);
        container = newContainer;
    }

    const toast = document.createElement('div');
    toast.className = `notification-toast ${type}`;
    
    const icons = {
        success: '<i class="fas fa-check-circle"></i>',
        error: '<i class="fas fa-times-circle"></i>',
        warning: '<i class="fas fa-exclamation-triangle"></i>',
        info: '<i class="fas fa-info-circle"></i>'
    };

    toast.innerHTML = `
        <button class="notification-close" onclick="this.parentElement.remove()">&times;</button>
        <div class="notification-content">
            <div class="notification-icon">
                ${icons[type] || icons.success}
            </div>
            <div class="notification-body">
                <div class="notification-title">${title}</div>
                <div class="notification-message">${message}</div>
            </div>
        </div>
    `;

    container.appendChild(toast);

    // Trigger animation
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);

    // Auto-remove untuk notifikasi success/error biasa (dari session) setelah 4 detik
    // Notifikasi dokumen masuk/reject tetap permanen (dipanggil dengan showNotificationWithAction)
    if (type === 'success' || type === 'error') {
        setTimeout(() => {
            toast.classList.add('hide');
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 300);
        }, 4000); // 4 detik untuk notifikasi success/error biasa
    }
    // Jika type info/warning atau dokumen masuk/reject, tetap permanen
}

// Notification Toast Function with Action Button
function showNotificationWithAction(type, title, message, actionUrl, actionText) {
    const container = document.getElementById('notificationContainer');
    if (!container) {
        // Create container if it doesn't exist
        const newContainer = document.createElement('div');
        newContainer.id = 'notificationContainer';
        document.body.appendChild(newContainer);
        container = newContainer;
    }

    const toast = document.createElement('div');
    toast.className = `notification-toast ${type}`;
    
    const icons = {
        success: '<i class="fas fa-check-circle"></i>',
        error: '<i class="fas fa-times-circle"></i>',
        warning: '<i class="fas fa-exclamation-triangle"></i>',
        info: '<i class="fas fa-bell"></i>'
    };

    toast.innerHTML = `
        <button class="notification-close" onclick="this.parentElement.remove()">&times;</button>
        <div class="notification-content">
            <div class="notification-icon">
                ${icons[type] || icons.info}
            </div>
            <div class="notification-body">
                <div class="notification-title">${title}</div>
                <div class="notification-message">${message}</div>
                ${actionUrl ? `<a href="${actionUrl}" class="notification-action-btn">${actionText || 'Lihat'}</a>` : ''}
            </div>
        </div>
    `;

    container.appendChild(toast);

    // Trigger animation
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);

    // Notifikasi permanen - hanya hilang ketika user klik tombol X
    // Auto-remove dihapus agar notifikasi tetap muncul sampai user menutupnya
}
</script>

<style>
/* Modern Notification Toast Styles */
#notificationContainer {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    pointer-events: none;
}

.notification-toast {
    min-width: 350px;
    max-width: 500px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    padding: 0;
    overflow: hidden;
    animation: slideInRight 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    transform: translateX(400px);
    opacity: 0;
    margin-bottom: 16px;
    pointer-events: auto;
}

.notification-toast.show {
    transform: translateX(0);
    opacity: 1;
}

.notification-toast.hide {
    animation: slideOutRight 0.3s ease-in forwards;
}

@keyframes slideInRight {
    from {
        transform: translateX(400px);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

@keyframes slideOutRight {
    from {
        transform: translateX(0);
        opacity: 1;
    }
    to {
        transform: translateX(400px);
        opacity: 0;
    }
}

.notification-toast.success {
    border-left: 5px solid #48bb78;
}

.notification-toast.error {
    border-left: 5px solid #f56565;
}

.notification-toast.warning {
    border-left: 5px solid #f6ad55;
}

.notification-toast.info {
    border-left: 5px solid #4299e1;
}

.notification-content {
    padding: 20px;
    display: flex;
    align-items: flex-start;
    gap: 16px;
}

.notification-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    flex-shrink: 0;
}

.notification-toast.success .notification-icon {
    background: linear-gradient(135deg, #48bb78 0%, #9ae6b4 100%);
    color: white;
}

.notification-toast.error .notification-icon {
    background: linear-gradient(135deg, #f56565 0%, #fc8181 100%);
    color: white;
}

.notification-toast.warning .notification-icon {
    background: linear-gradient(135deg, #f6ad55 0%, #fed7aa 100%);
    color: white;
}

.notification-toast.info .notification-icon {
    background: linear-gradient(135deg, #4299e1 0%, #90cdf4 100%);
    color: white;
}

.notification-body {
    flex: 1;
}

.notification-title {
    font-weight: 700;
    font-size: 16px;
    margin-bottom: 4px;
    color: #1a202c;
}

.notification-message {
    font-size: 14px;
    color: #4a5568;
    line-height: 1.5;
    margin-bottom: 8px;
}

.notification-action-btn {
    display: inline-block;
    margin-top: 8px;
    padding: 8px 16px;
    background: linear-gradient(135deg, #4299e1 0%, #63b3ed 100%);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.2s ease;
    box-shadow: 0 2px 6px rgba(66, 153, 225, 0.25);
}

.notification-action-btn:hover {
    background: linear-gradient(135deg, #3182ce 0%, #4299e1 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(66, 153, 225, 0.35);
    color: white;
    text-decoration: none;
}

.notification-close {
    position: absolute;
    top: 12px;
    right: 12px;
    background: none;
    border: none;
    color: #718096;
    font-size: 18px;
    cursor: pointer;
    padding: 4px;
    line-height: 1;
    transition: color 0.2s;
    z-index: 10;
}

.notification-close:hover {
    color: #2d3748;
}
</style>
@endsection
