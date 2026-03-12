@extends('layouts.app')

@section('content')
    <style>
        /* ============================================
           DASHBOARD KABAG KEUANGAN — REDESIGNED
           ============================================ */

        /* BASE */
        .home-dashboard {
            padding: 0 0 40px;
            min-height: 100vh;
            background-color: #F0F4F8;
            font-family: 'DM Sans', 'Segoe UI', sans-serif;
            color: #1e293b;
        }

        /* HEADER BANNER */
        .dash-banner {
            background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
            padding: 28px 36px 24px;
            position: relative;
            overflow: hidden;
            border-radius: 16px;
            margin: 24px 24px 0;
        }
        .dash-banner .circle-decor {
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(255,255,255,0.08);
            pointer-events: none;
        }
        .dash-banner .circle-decor:nth-child(1) { width:180px; height:180px; right:-60px; top:-60px; }
        .dash-banner .circle-decor:nth-child(2) { width:300px; height:300px; right:-80px; top:-80px; }
        .dash-banner .circle-decor:nth-child(3) { width:420px; height:420px; right:-100px; top:-100px; }
        .banner-inner {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            position: relative;
            z-index: 1;
        }
        .banner-greeting { color: rgba(255,255,255,0.65); font-size: 13px; display: flex; align-items: center; gap: 10px; margin-bottom: 4px; }
        .banner-greeting strong { color: white; }
        .banner-title { color: white; font-size: 26px; font-weight: 700; margin: 0 0 4px; }
        .banner-subtitle { color: rgba(255,255,255,0.6); margin: 0; font-size: 13px; }

        /* Glassmorphism summary */
        .banner-summary {
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 12px 18px;
            border: 1px solid rgba(255,255,255,0.2);
            font-size: 12px;
            color: white;
            min-width: 240px;
            flex-shrink: 0;
        }
        .banner-summary-title { font-weight: 600; margin-bottom: 8px; color: rgba(255,255,255,0.8); }
        .banner-summary-row { display: flex; align-items: center; gap: 8px; margin-bottom: 4px; }
        .banner-summary-row .dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }
        .banner-summary-row span { color: rgba(255,255,255,0.85); }

        /* CONTENT AREA */
        .dash-content { padding: 24px 36px 0; }

        /* STAT CARDS */
        .stat-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: white;
            border-radius: 14px;
            padding: 18px 20px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            border-top: 3px solid var(--stat-color, #0D9488);
            position: relative;
            overflow: hidden;
            text-decoration: none !important;
            display: block;
            color: inherit;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            color: inherit;
        }
        .stat-card-top { display: flex; justify-content: space-between; align-items: flex-start; }
        .stat-card .stat-label {
            font-size: 11px; color: #94a3b8; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;
        }
        .stat-card .stat-value {
            font-size: 30px; font-weight: 800; color: #0f172a; line-height: 1;
        }
        .stat-card .stat-icon {
            width: 44px; height: 44px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
        }
        .stat-change { font-size: 11px; font-weight: 600; margin-top: 8px; }
        .stat-change.pos { color: #10B981; }
        .stat-change.neg { color: #EF4444; }
        .stat-change.neutral { color: #94a3b8; }
        .stat-card .stat-detail { margin-top: 12px; font-size: 12px; color: #0D9488; cursor: pointer; font-weight: 500; }
        .stat-nilai {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
            margin: 4px 0 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Total Nilai card */
        .stat-card.gradient {
            background: linear-gradient(135deg, #0D4F4A, #0D9488);
            border-top: none;
            color: white;
        }
        .stat-card.gradient .stat-label { color: rgba(255,255,255,0.65); }
        .stat-card.gradient .stat-value { color: white; font-size: 20px; font-weight: 800; line-height: 1.2; }
        .stat-card.gradient .stat-change { color: rgba(255,255,255,0.7); }
        .stat-card.gradient .stat-detail { color: rgba(255,255,255,0.7); }

        /* CHARTS ROW */
        .charts-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }
        .chart-card {
            background: white;
            border-radius: 14px;
            padding: 20px 24px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }
        .chart-card h3 { margin: 0; font-size: 15px; font-weight: 700; color: #0f172a; }
        .chart-card .chart-sub { margin: 2px 0 0; font-size: 12px; color: #94a3b8; }
        .chart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; }
        .chart-legend-inline { display: flex; gap: 16px; font-size: 11px; color: #64748b; }

        /* Donut legend */
        .donut-legend { display: flex; flex-direction: column; gap: 6px; margin-top: 8px; }
        .legend-item { display: flex; align-items: center; gap: 6px; font-size: 12px; }
        .legend-item .dot { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }
        .legend-item .lbl { color: #64748b; }
        .legend-item strong { margin-left: auto; color: #0f172a; }

        /* BAGIAN SECTION */
        .bagian-row-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 24px;
        }
        .bagian-row { display: flex; align-items: center; gap: 12px; margin-bottom: 8px; }
        .bagian-badge {
            width: 32px; height: 32px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 700; flex-shrink: 0;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .bagian-badge:hover { cursor: pointer; opacity: 0.8; transform: scale(1.05); }
        .terlambat-link {
            color: #EF4444; font-size: 11px; font-weight: 600;
            text-decoration: none; transition: all 0.15s ease;
        }
        .terlambat-link:hover { cursor: pointer; text-decoration: underline; opacity: 0.8; color: #EF4444; }
        .bagian-info { flex: 1; }
        .bagian-meta { display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 12px; }
        .progress-bar { height: 5px; background: #f1f5f9; border-radius: 99px; overflow: hidden; }
        .progress-fill { height: 100%; border-radius: 99px; transition: width 1s ease; }

        /* BOTTOM GRID CARDS */
        .bagian-grid-section {
            background: white;
            border-radius: 14px;
            padding: 20px 24px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }
        .bagian-grid-section .section-head { display: flex; align-items: center; gap: 8px; margin-bottom: 16px; }
        .bagian-grid-section .section-head h3 { margin: 0; font-size: 15px; font-weight: 700; color: #0f172a; }
        .bagian-grid-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: 12px;
        }
        .bagian-grid-card {
            border-radius: 12px;
            padding: 14px;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            cursor: pointer;
            transition: all 0.15s ease;
            text-decoration: none !important;
            display: block;
            color: inherit;
        }
        .bagian-grid-card:hover {
            transform: translateY(-2px);
            color: inherit;
        }
        .bagian-grid-card .bg-icon {
            width: 38px; height: 38px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; margin-bottom: 10px;
        }
        .bagian-grid-card .bg-name { font-weight: 700; font-size: 13px; color: #0f172a; }
        .bagian-grid-card .bg-count { font-size: 18px; font-weight: 800; margin: 2px 0; }
        .bagian-grid-card .bg-label { font-size: 11px; color: #94a3b8; }
        .bagian-grid-card .bg-link { margin-top: 8px; font-size: 11px; font-weight: 500; }

        /* Responsive */
        @media (max-width: 1024px) {
            .charts-row { grid-template-columns: 1fr; }
            .bagian-row-grid { grid-template-columns: 1fr; }
            .banner-inner { flex-direction: column; gap: 16px; }
            .banner-summary { min-width: auto; }
        }
        @media (max-width: 768px) {
            .dash-banner { padding: 20px; }
            .dash-content { padding: 16px; }
            .stat-cards { grid-template-columns: repeat(2, 1fr); }
            .bagian-grid-cards { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 480px) {
            .stat-cards { grid-template-columns: 1fr; }
            .bagian-grid-cards { grid-template-columns: 1fr; }
        }

        /* Dark mode */
        html.dark .home-dashboard { background-color: #0f172a; }
        html.dark .stat-card, html.dark .chart-card, html.dark .bagian-grid-section { background: #1e293b; }
        html.dark .stat-card .stat-value, html.dark .chart-card h3 { color: #f1f5f9; }
        html.dark .bagian-grid-card { background: #1e293b; border-color: #334155; }
    </style>

    <div class="home-dashboard">

        {{-- ============ HEADER BANNER ============ --}}
        <div class="dash-banner">
            <div class="circle-decor"></div>
            <div class="circle-decor"></div>
            <div class="circle-decor"></div>

            <div class="banner-inner">
                <div>
                    <div class="banner-greeting">
                        <span style="font-size:20px">📊</span>
                        <span>{{ $greeting }}, <strong>Pak Herry</strong></span>
                    </div>
                    <h1 class="banner-title">Dashboard Kabag Keuangan</h1>
                    <p class="banner-subtitle">Pantau dan kelola semua dokumen perusahaan dengan mudah</p>
                </div>

                {{-- Ringkasan Hari Ini --}}
                <div class="banner-summary">
                    <div class="banner-summary-title">☀️ Ringkasan Hari Ini</div>
                    <div class="banner-summary-row">
                        <div class="dot" style="background:#10B981"></div>
                        <span>{{ $hariIni['masuk'] }} dokumen baru masuk</span>
                    </div>
                    <div class="banner-summary-row">
                        <div class="dot" style="background:#F59E0B"></div>
                        <span>{{ $hariIni['mendekati'] }} mendekati batas waktu</span>
                    </div>
                    <div class="banner-summary-row">
                        <div class="dot" style="background:#EF4444"></div>
                        <span>{{ $hariIni['terlambat'] }} dokumen terlambat</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="dash-content">

            {{-- ============ STAT CARDS ============ --}}
            <div class="stat-cards">
                {{-- Total Dokumen --}}
                <a href="{{ url('/owner/dokumen') }}" class="stat-card" style="--stat-color:#0D9488">
                    <div class="stat-card-top">
                        <div>
                            <div class="stat-label">Total Dokumen</div>
                            <div class="stat-value" data-counter="{{ $totalDokumen }}">0</div>
                            @if($totalDokumenTrend != 0)
                            <div class="stat-change {{ $totalDokumenTrend > 0 ? 'pos' : 'neg' }}">
                                {{ $totalDokumenTrend > 0 ? '↑ +'.$totalDokumenTrend.'%' : '↓ '.$totalDokumenTrend.'%' }} vs bulan lalu
                            </div>
                            @endif
                        </div>
                        <div class="stat-icon" style="background:#0D948818">📄</div>
                    </div>
                    <div class="stat-detail">Lihat Detail →</div>
                </a>

                {{-- Belum Dibayar --}}
                <a href="{{ url('/owner/dokumen?status=belum_siap') }}" class="stat-card" style="--stat-color:#F59E0B">
                    <div class="stat-card-top">
                        <div>
                            <div class="stat-label">Belum Dibayar</div>
                            <div class="stat-value" data-counter="{{ $dokumenProses }}">0</div>
                            @if($prosesTrend != 0)
                            <div class="stat-change {{ $prosesTrend > 0 ? 'neg' : 'pos' }}">
                                {{ $prosesTrend > 0 ? '↑ +'.$prosesTrend.'%' : '↓ '.$prosesTrend.'%' }} vs bulan lalu
                            </div>
                            @endif
                        </div>
                        <div class="stat-icon" style="background:#F59E0B18">⏳</div>
                    </div>
                    <div class="stat-nilai">Rp {{ number_format($nilaiBelumDibayar, 0, ',', '.') }}</div>
                    <div class="stat-detail">Lihat Detail →</div>
                </a>

                {{-- Siap Dibayar --}}
                <a href="{{ url('/owner/dokumen?status=siap_dibayar') }}" class="stat-card" style="--stat-color:#3B82F6">
                    <div class="stat-card-top">
                        <div>
                            <div class="stat-label">Siap Dibayar</div>
                            <div class="stat-value" data-counter="{{ $dokumenSiapBayar }}">0</div>
                            @if($siapTrend != 0)
                            <div class="stat-change {{ $siapTrend > 0 ? 'pos' : 'neg' }}">
                                {{ $siapTrend > 0 ? '↑ +'.$siapTrend.'%' : '↓ '.$siapTrend.'%' }} vs bulan lalu
                            </div>
                            @endif
                        </div>
                        <div class="stat-icon" style="background:#3B82F618">✅</div>
                    </div>
                    <div class="stat-nilai">Rp {{ number_format($nilaiSiapDibayar, 0, ',', '.') }}</div>
                    <div class="stat-detail">Lihat Detail →</div>
                </a>

                {{-- Sudah Dibayar --}}
                <a href="{{ url('/owner/dokumen?status=sudah_dibayar') }}" class="stat-card" style="--stat-color:#10B981">
                    <div class="stat-card-top">
                        <div>
                            <div class="stat-label">Sudah Dibayar</div>
                            <div class="stat-value" data-counter="{{ $dokumenSelesai }}">0</div>
                            @if($selesaiTrend != 0)
                            <div class="stat-change {{ $selesaiTrend > 0 ? 'pos' : 'neg' }}">
                                {{ $selesaiTrend > 0 ? '↑ +'.$selesaiTrend.'%' : '↓ '.$selesaiTrend.'%' }} vs bulan lalu
                            </div>
                            @endif
                        </div>
                        <div class="stat-icon" style="background:#10B98118">💳</div>
                    </div>
                    <div class="stat-nilai">Rp {{ number_format($nilaiSudahDibayar, 0, ',', '.') }}</div>
                    <div class="stat-detail">Lihat Detail →</div>
                </a>

                {{-- Total Nilai --}}
                <div class="stat-card gradient">
                    <div class="stat-label">Total Nilai (RP)</div>
                    <div class="stat-value" data-counter="{{ $totalNilai }}" data-prefix="Rp ">0</div>
                    @if($nilaiTrend != 0)
                    <div class="stat-change">
                        {{ $nilaiTrend > 0 ? '↑ +'.$nilaiTrend.'%' : '↓ '.$nilaiTrend.'%' }} vs bulan lalu
                    </div>
                    @endif
                    <div style="margin-top:12px; font-size:20px">💰</div>
                </div>
            </div>

            {{-- ============ CHARTS ROW ============ --}}
            <div class="charts-row">
                {{-- Area Chart Tren 30 Hari --}}
                <div class="chart-card">
                    <div class="chart-header">
                        <div>
                            <h3>Tren Dokumen 30 Hari</h3>
                            <p class="chart-sub">Jumlah dokumen masuk per hari</p>
                        </div>
                        <div class="chart-legend-inline">
                            <span><span style="color:#0D9488">●</span> Masuk</span>
                        </div>
                    </div>
                    <div style="position:relative; height:200px;">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>

                {{-- Donut Chart --}}
                <div class="chart-card">
                    <h3>Overview Status</h3>
                    <p class="chart-sub">Distribusi status pembayaran</p>
                    <div style="display:flex; justify-content:center; margin-top:12px;">
                        <canvas id="donutChart" width="160" height="160"></canvas>
                    </div>
                    <div class="donut-legend">
                        <div class="legend-item">
                            <div class="dot" style="background:#10B981"></div>
                            <span class="lbl">Sudah Dibayar</span>
                            <strong>{{ number_format($dokumenSelesai) }}</strong>
                        </div>
                        <div class="legend-item">
                            <div class="dot" style="background:#F59E0B"></div>
                            <span class="lbl">Belum Dibayar</span>
                            <strong>{{ number_format($dokumenProses) }}</strong>
                        </div>
                        <div class="legend-item">
                            <div class="dot" style="background:#3B82F6"></div>
                            <span class="lbl">Siap Bayar</span>
                            <strong>{{ number_format($dokumenSiapBayar) }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============ BAGIAN SECTION ============ --}}
            <div class="bagian-row-grid">
                {{-- Bar Chart Volume per Bagian --}}
                <div class="chart-card">
                    <h3>Volume per Bagian</h3>
                    <p class="chart-sub">Total dokumen dan keterlambatan</p>
                    <div style="position:relative; height:220px; margin-top:16px;">
                        <canvas id="bagianChart"></canvas>
                    </div>
                </div>

                {{-- Progress List per Bagian --}}
                <div class="chart-card">
                    <h3>Dokumen per Bagian</h3>
                    <p class="chart-sub" style="margin-bottom:14px">Dengan indikator keterlambatan</p>
                    @foreach($bagianStats as $b)
                        <div class="bagian-row">
                            <a href="{{ url('/owner/dokumen?filter_bagian=' . $b['code']) }}"
                               class="bagian-badge" style="background:{{ $b['color'] }}20; color:{{ $b['color'] }}"
                               title="Lihat semua dokumen bagian {{ $b['code'] }}">
                                {{ $b['code'] }}
                            </a>
                            <div class="bagian-info">
                                <div class="bagian-meta">
                                    <span style="font-weight:600; color:#0f172a">{{ number_format($b['count']) }} dokumen</span>
                                    @if($b['terlambat'] > 0)
                                        <a href="{{ url('/owner/dokumen?filter_bagian=' . $b['code'] . '&filter_umur=3') }}"
                                           class="terlambat-link"
                                           title="Lihat {{ $b['terlambat'] }} dokumen terlambat di bagian {{ $b['code'] }}">
                                            ⚠ {{ $b['terlambat'] }} terlambat
                                        </a>
                                    @endif
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" style="
                                        width: {{ ($b['count'] / $maxBagian) * 100 }}%;
                                        background: {{ $b['terlambat'] > 0
                                            ? 'linear-gradient(90deg,'.$b['color'].',#EF4444)'
                                            : $b['color'] }};
                                    "></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ============ GRID CARD BAGIAN (EXISTING IMPROVED) ============ --}}
            <div class="bagian-grid-section">
                <div class="section-head">
                    <span style="font-size:16px">🗂</span>
                    <h3>Dokumen per Bagian</h3>
                </div>
                <div class="bagian-grid-cards">
                    @foreach($bagianStats as $b)
                        <a href="{{ url('/owner/dokumen?filter_bagian=' . $b['code']) }}"
                           class="bagian-grid-card"
                           style="--accent:{{ $b['color'] }}"
                           onmouseenter="this.style.background='{{ $b['color'] }}12'; this.style.borderColor='{{ $b['color'] }}40'; this.style.transform='translateY(-2px)'"
                           onmouseleave="this.style.background='#F8FAFC'; this.style.borderColor='#E2E8F0'; this.style.transform='translateY(0)'"
                        >
                            <div class="bg-icon" style="background:{{ $b['color'] }}20">{{ $b['emoji'] }}</div>
                            <div class="bg-name">{{ $b['code'] }}</div>
                            <div class="bg-count" style="color:{{ $b['color'] }}">{{ number_format($b['count']) }}</div>
                            <div class="bg-label">dokumen</div>
                            <div class="bg-link" style="color:{{ $b['color'] }}">Lihat →</div>
                        </a>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    {{-- Chart.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        // === ANIMATED COUNTER ===
        function animateCounter(el, target, duration) {
            duration = duration || 1200;
            var prefix = el.dataset.prefix || '';
            var start = performance.now();
            var update = function(ts) {
                var p = Math.min((ts - start) / duration, 1);
                var eased = 1 - Math.pow(1 - p, 3);
                el.textContent = prefix + Math.floor(eased * target).toLocaleString('id-ID');
                if (p < 1) requestAnimationFrame(update);
            };
            requestAnimationFrame(update);
        }
        document.querySelectorAll('[data-counter]').forEach(function(el) {
            animateCounter(el, parseInt(el.dataset.counter));
        });

        // === AREA CHART — TREN 30 HARI ===
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Dokumen Masuk',
                    data: @json($chartData),
                    borderColor: '#0D9488',
                    backgroundColor: 'rgba(13,148,136,0.08)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 4,
                    pointHoverBackgroundColor: '#0D9488'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 10 } } },
                    y: { grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', font: { size: 10 } }, beginAtZero: true }
                }
            }
        });

        // === DONUT CHART — PROPORSI STATUS ===
        new Chart(document.getElementById('donutChart'), {
            type: 'doughnut',
            data: {
                labels: ['Sudah Dibayar', 'Belum Dibayar', 'Siap Bayar'],
                datasets: [{
                    data: [{{ $dokumenSelesai }}, {{ $dokumenProses }}, {{ $dokumenSiapBayar }}],
                    backgroundColor: ['#10B981', '#F59E0B', '#3B82F6'],
                    borderWidth: 3,
                    borderColor: 'white'
                }]
            },
            options: {
                responsive: false,
                cutout: '65%',
                plugins: { legend: { display: false } }
            }
        });

        // === BAR CHART — VOLUME PER BAGIAN ===
        var bagianData = @json($bagianStats);
        new Chart(document.getElementById('bagianChart'), {
            type: 'bar',
            data: {
                labels: bagianData.map(function(b) { return b.code; }),
                datasets: [{
                    label: 'Total Dokumen',
                    data: bagianData.map(function(b) { return b.count; }),
                    backgroundColor: bagianData.map(function(b) { return b.color; }),
                    borderRadius: 4,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 11 } } },
                    y: { grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', font: { size: 10 } }, beginAtZero: true }
                }
            }
        });
    </script>
@endsection