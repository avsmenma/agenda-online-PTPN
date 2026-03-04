@extends('layouts.app')

@section('content')
    <style>
        /* Home Dashboard Styles */
        .home-dashboard {
            padding: 2rem;
            min-height: 100vh;
            background-color: #f8fafc;
            box-sizing: border-box;
            width: 100%;
        }

        /* Header Section */
        .dashboard-header {
            background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            color: white;
        }

        .dashboard-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .dashboard-header p {
            opacity: 0.9;
            margin-bottom: 0;
            font-size: 0.95rem;
        }

        /* Summary Cards */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2.5rem;
            width: 100%;
        }

        .summary-card {
            background: white;
            border-radius: 16px;
            padding: 1.25rem 1.25rem 2.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            min-width: 0;          /* prevent grid overflow */
            box-sizing: border-box;
            width: 100%;
        }

        .summary-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }

        .summary-card .label {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .summary-card .value {
            font-size: clamp(1.4rem, 2vw, 2rem);
            font-weight: 700;
            color: #1e293b;
            word-break: break-word;
            overflow-wrap: anywhere;
            line-height: 1.2;
        }

        .summary-card .icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            float: right;
            margin-top: -8px;
        }

        .summary-card .icon i {
            font-size: 1.5rem;
            color: white;
        }

        .summary-card.total .icon {
            background: linear-gradient(135deg, #083E40, #0a4f52);
        }

        .summary-card.proses .icon {
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
        }

        .summary-card.selesai .icon {
            background: linear-gradient(135deg, #10b981, #34d399);
        }

        .summary-card.siap_bayar .icon {
            background: linear-gradient(135deg, #0ea5e9, #38bdf8);
        }

        /* Clickable card indicator for siap_bayar card */
        .summary-card.siap_bayar.clickable {
            cursor: pointer;
            position: relative;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .summary-card.siap_bayar.clickable:hover {
            border-color: #0ea5e9;
            transform: translateY(-6px);
            box-shadow: 0 15px 30px -10px rgba(14, 165, 233, 0.25);
        }

        .summary-card.siap_bayar.clickable .click-indicator {
            position: absolute;
            bottom: 12px;
            right: 12px;
            font-size: 0.75rem;
            color: #0ea5e9;
            display: flex;
            align-items: center;
            gap: 4px;
            opacity: 0.7;
            transition: all 0.3s ease;
        }

        .summary-card.siap_bayar.clickable:hover .click-indicator {
            opacity: 1;
            transform: translateX(4px);
        }

        /* Clickable card indicator for selesai card */
        .summary-card.selesai.clickable {
            cursor: pointer;
            position: relative;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .summary-card.selesai.clickable:hover {
            border-color: #10b981;
            transform: translateY(-6px);
            box-shadow: 0 15px 30px -10px rgba(16, 185, 129, 0.25);
        }

        .summary-card.selesai.clickable .click-indicator {
            position: absolute;
            bottom: 12px;
            right: 12px;
            font-size: 0.75rem;
            color: #10b981;
            display: flex;
            align-items: center;
            gap: 4px;
            opacity: 0.7;
            transition: all 0.3s ease;
        }

        .summary-card.selesai.clickable:hover .click-indicator {
            opacity: 1;
            transform: translateX(4px);
        }

        /* Clickable card styles for Total Dokumen */
        .summary-card.total.clickable {
            cursor: pointer;
            position: relative;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .summary-card.total.clickable:hover {
            border-color: #083E40;
            transform: translateY(-6px);
            box-shadow: 0 15px 30px -10px rgba(8, 62, 64, 0.25);
        }

        .summary-card.total.clickable .click-indicator {
            position: absolute;
            bottom: 12px;
            right: 12px;
            font-size: 0.75rem;
            color: #083E40;
            display: flex;
            align-items: center;
            gap: 4px;
            opacity: 0.7;
            transition: all 0.3s ease;
        }

        .summary-card.total.clickable:hover .click-indicator {
            opacity: 1;
            transform: translateX(4px);
        }

        /* Clickable card styles for Dokumen Belum Dibayar (Proses) */
        .summary-card.proses.clickable {
            cursor: pointer;
            position: relative;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .summary-card.proses.clickable:hover {
            border-color: #f59e0b;
            transform: translateY(-6px);
            box-shadow: 0 15px 30px -10px rgba(245, 158, 11, 0.25);
        }

        .summary-card.proses.clickable .click-indicator {
            position: absolute;
            bottom: 12px;
            right: 12px;
            font-size: 0.75rem;
            color: #f59e0b;
            display: flex;
            align-items: center;
            gap: 4px;
            opacity: 0.7;
            transition: all 0.3s ease;
        }

        .summary-card.proses.clickable:hover .click-indicator {
            opacity: 1;
            transform: translateX(4px);
        }


        .summary-card.nilai .icon {
            background: linear-gradient(135deg, #6366f1, #818cf8);
        }

        .trend {
            font-size: 0.75rem;
            margin-top: 0.5rem;
        }

        .trend.up {
            color: #10b981;
        }

        .trend.down {
            color: #ef4444;
        }

        /* Section Title */
        .section-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .section-title i {
            color: #083E40;
        }

        /* Bagian Cards Grid */
        .bagian-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1.25rem;
            width: 100%;
        }

        .bagian-card {
            background: white;
            border-radius: 16px;
            padding: 1.25rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            text-decoration: none;
            display: block;
            min-width: 0;          /* prevent grid overflow */
            box-sizing: border-box;
            width: 100%;
        }

        .bagian-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.15);
            border-color: var(--card-color);
        }

        .bagian-card .card-icon {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
            background-color: var(--card-color);
            transition: transform 0.3s ease;
        }

        .bagian-card:hover .card-icon {
            transform: scale(1.1);
        }

        .bagian-card .card-icon i {
            font-size: 1.5rem;
            color: white;
        }

        .bagian-card .card-code {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .bagian-card .card-count {
            font-size: 0.875rem;
            color: #64748b;
        }

        .bagian-card .card-count strong {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--card-color);
        }

        .bagian-card .arrow {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            margin-top: 1rem;
            color: #94a3b8;
            transition: color 0.3s, transform 0.3s;
        }

        .bagian-card:hover .arrow {
            color: var(--card-color);
            transform: translateX(4px);
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .summary-cards {
                grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .home-dashboard {
                padding: 1rem;
            }

            .summary-cards {
                grid-template-columns: repeat(2, 1fr);
            }

            .bagian-cards {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .summary-cards {
                grid-template-columns: 1fr;
            }

            .bagian-cards {
                grid-template-columns: 1fr;
            }
        }

        /* Dark mode support */
        html.dark .home-dashboard {
            background-color: #0f172a;
        }

        html.dark .summary-card,
        html.dark .bagian-card {
            background: #1e293b;
        }

        html.dark .summary-card .value,
        html.dark .summary-card .label,
        html.dark .bagian-card .card-code {
            color: #f1f5f9;
        }

        html.dark .section-title {
            color: #f1f5f9;
        }
    </style>

    <div class="home-dashboard">
        <!-- Header -->
        <div class="dashboard-header">
            <h1><i class="fa-solid fa-chart-line me-2"></i> Dashboard Kabag Keuangan</h1>
            <p>Pantau dan kelola semua dokumen perusahaan dengan mudah</p>
        </div>

        <!-- Summary Cards -->
        <div class="summary-cards">
            <a href="{{ url('/owner/dokumen') }}" class="summary-card total clickable" style="text-decoration: none;">
                <div class="icon">
                    <i class="fa-solid fa-file-lines"></i>
                </div>
                <div class="label">Total Dokumen</div>
                <div class="value">{{ number_format($totalDokumen) }}</div>
                <div class="click-indicator">
                    <span>Lihat Detail</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            </a>

            <a href="{{ url('/owner/dokumen?status=belum_siap') }}" class="summary-card proses clickable"
                style="text-decoration: none;">
                <div class="icon">
                    <i class="fa-solid fa-spinner"></i>
                </div>
                <div class="label">Dokumen Belum Dibayar</div>
                <div class="value">{{ number_format($dokumenProses) }}</div>
                <div class="click-indicator">
                    <span>Lihat Detail</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            </a>

            <a href="{{ url('/owner/dokumen?status=siap_dibayar') }}" class="summary-card siap_bayar clickable"
                style="text-decoration: none;">
                <div class="icon">
                    <i class="fa-solid fa-file-invoice-dollar"></i>
                </div>
                <div class="label">Dokumen Siap Bayar</div>
                <div class="value">{{ number_format($dokumenSiapBayar) }}</div>
                <div class="click-indicator">
                    <span>Lihat Detail</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            </a>

            <a href="{{ url('/owner/dokumen?status=sudah_dibayar') }}" class="summary-card selesai clickable"
                style="text-decoration: none;">
                <div class="icon">
                    <i class="fa-solid fa-check-circle"></i>
                </div>
                <div class="label">Dokumen Sudah Dibayar</div>
                <div class="value">{{ number_format($dokumenSelesai) }}</div>
                <div class="click-indicator">
                    <span>Lihat Detail</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </div>
            </a>

            <div class="summary-card nilai">
                <div class="icon">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </div>
                <div class="label">Total Nilai (Rp)</div>
                <div class="value">{{ 'Rp ' . number_format($totalNilai, 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Bagian Section -->
        <h2 class="section-title">
            <i class="fa-solid fa-building"></i>
            Dokumen per Bagian
        </h2>

        <div class="bagian-cards">
            @foreach($bagianStats as $bagian)
                <a href="{{ url('/owner/dokumen?filter_bagian=' . $bagian['code']) }}" class="bagian-card"
                    style="--card-color: {{ $bagian['color'] }}">
                    <div class="card-icon">
                        <i class="fa-solid {{ $bagian['icon'] }}"></i>
                    </div>
                    <div class="card-code">{{ $bagian['code'] }}</div>
                    <div class="card-count">
                        <strong>{{ $bagian['count'] }}</strong> dokumen
                    </div>
                    <div class="arrow">
                        <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
@endsection