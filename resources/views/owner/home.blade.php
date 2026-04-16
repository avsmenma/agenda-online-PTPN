@extends('layouts.app')

@section('content')
<style>
  /* ================================================
     OWNER DASHBOARD — Sesuai Referensi Mockup
     ================================================ */
  :root {
    --bg: #f4f6fb;
    --card: #ffffff;
    --border: #e8ecf4;
    --text-primary: #1a2340;
    --text-secondary: #6b7a99;
    --text-muted: #a0aec0;
    --accent: #2563eb;
    --accent-light: #eff4ff;
    --green: #10b981;
    --green-light: #ecfdf5;
    --orange: #f59e0b;
    --orange-light: #fffbeb;
    --red: #ef4444;
    --red-light: #fef2f2;
    --teal: #0f766e;
    --radius: 14px;
    --shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.05);
  }

  .owner-dash {
    padding: 0;
    font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
    font-size: 13.5px;
    color: var(--text-primary);
    background: var(--bg);
    min-height: 100vh;
  }

  /* ── TOPBAR ── */
  .owner-topbar {
    background: var(--card);
    border-bottom: 1px solid var(--border);
    padding: 14px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 5;
  }
  .owner-topbar-title {
    font-family: 'Sora', 'Plus Jakarta Sans', sans-serif;
    font-size: 16px;
    font-weight: 700;
    color: var(--text-primary);
  }
  .owner-topbar-subtitle { font-size: 12px; color: var(--text-muted); margin-top: 1px; }
  .owner-topbar-controls { display: flex; align-items: center; gap: 10px; }
  .ctrl-pill {
    display: flex; align-items: center; gap: 6px;
    padding: 7px 13px; border-radius: 8px;
    border: 1px solid var(--border); background: white;
    font-size: 12px; color: var(--text-secondary); font-weight: 500;
    cursor: pointer; transition: border-color .15s;
    white-space: nowrap;
  }
  .ctrl-pill:hover { border-color: var(--accent); color: var(--accent); }
  .ctrl-pill svg { width: 13px; height: 13px; flex-shrink: 0; }
  .badge-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }

  /* ── CONTENT AREA ── */
  .owner-content { padding: 24px 28px; }

  /* ── STAT CARDS ── */
  .stats-row {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 14px;
    margin-bottom: 20px;
  }
  @media (max-width: 1200px) { .stats-row { grid-template-columns: repeat(3, 1fr); } }
  @media (max-width: 768px)  { .stats-row { grid-template-columns: 1fr 1fr; } }

  .stat-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 18px 18px 14px;
    box-shadow: var(--shadow);
    position: relative; overflow: hidden;
    transition: transform .2s, box-shadow .2s;
    animation: fadeUp .4s ease both;
  }
  .stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 20px rgba(0,0,0,.09); }
  .stat-card.accent {
    background: linear-gradient(135deg, #0f766e 0%, #059669 100%);
    border-color: transparent;
  }
  .stat-card:nth-child(1) { animation-delay: .05s; }
  .stat-card:nth-child(2) { animation-delay: .1s; }
  .stat-card:nth-child(3) { animation-delay: .15s; }
  .stat-card:nth-child(4) { animation-delay: .2s; }
  .stat-card:nth-child(5) { animation-delay: .25s; }

  .stat-label {
    font-size: 10.5px; font-weight: 600;
    text-transform: uppercase; letter-spacing: .06em;
    color: var(--text-muted); margin-bottom: 10px;
  }
  .stat-card.accent .stat-label { color: rgba(255,255,255,.7); }
  .stat-value {
    font-family: 'Sora', sans-serif;
    font-size: 26px; font-weight: 700;
    color: var(--text-primary); line-height: 1; margin-bottom: 6px;
  }
  .stat-card.accent .stat-value { color: white; font-size: 20px; }
  .stat-sub { font-size: 11px; color: var(--text-muted); }
  .stat-card.accent .stat-sub { color: rgba(255,255,255,.65); }
  .stat-icon {
    position: absolute; right: 16px; top: 16px;
    width: 36px; height: 36px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
  }
  .stat-icon svg { width: 18px; height: 18px; }

  /* ── CARD ── */
  .dash-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 20px;
    box-shadow: var(--shadow);
    animation: fadeUp .5s ease both;
    animation-delay: .3s;
  }
  .dash-card-header {
    display: flex; align-items: flex-start;
    justify-content: space-between; margin-bottom: 16px;
  }
  .dash-card-title {
    font-family: 'Sora', 'Plus Jakarta Sans', sans-serif;
    font-size: 14px; font-weight: 700; color: var(--text-primary);
  }
  .dash-card-sub { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
  .see-all {
    font-size: 11.5px; color: var(--accent); font-weight: 600;
    cursor: pointer; text-decoration: none; white-space: nowrap;
  }
  .see-all:hover { text-decoration: underline; }

  /* ── CHARTS ROW ── */
  .charts-row {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 14px; margin-bottom: 20px;
  }
  @media (max-width: 1024px) { .charts-row { grid-template-columns: 1fr; } }

  /* Period tabs */
  .period-tabs {
    display: flex; background: var(--bg);
    border-radius: 8px; padding: 3px; gap: 2px;
  }
  .period-tab {
    padding: 5px 12px; border-radius: 6px;
    font-size: 11.5px; font-weight: 500;
    color: var(--text-muted); cursor: pointer;
    transition: all .15s;
  }
  .period-tab.active {
    background: white; color: var(--text-primary);
    font-weight: 600; box-shadow: 0 1px 4px rgba(0,0,0,.1);
  }

  /* ── TOP BAGIAN TABLE ── */
  .top-table { width: 100%; border-collapse: collapse; }
  .top-table thead th {
    font-size: 10.5px; font-weight: 600;
    text-transform: uppercase; letter-spacing: .06em;
    color: var(--text-muted); padding: 0 0 10px; text-align: left;
  }
  .top-table thead th:not(:first-child) { text-align: right; }
  .top-table tbody tr { border-top: 1px solid var(--border); }
  .top-table tbody td { padding: 10px 0; font-size: 12.5px; }
  .top-table tbody td:not(:first-child) { text-align: right; }
  .bagian-name { font-weight: 600; color: var(--text-primary); }
  .bagian-dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 6px; flex-shrink: 0; }
  .return-val { font-weight: 600; }
  .val-up   { color: var(--green); }
  .val-down { color: var(--red); }

  /* ── BOTTOM ROW ── */
  .bottom-row {
    display: grid;
    grid-template-columns: 1.3fr 1fr 1fr;
    gap: 14px; margin-bottom: 20px;
  }
  @media (max-width: 1100px) { .bottom-row { grid-template-columns: 1fr 1fr; } }
  @media (max-width: 768px)  { .bottom-row { grid-template-columns: 1fr; } }

  /* Recent Docs Table */
  .doc-table { width: 100%; border-collapse: collapse; }
  .doc-table th {
    font-size: 10.5px; font-weight: 600; text-transform: uppercase;
    letter-spacing: .06em; color: var(--text-muted);
    padding-bottom: 10px; text-align: left;
    border-bottom: 1px solid var(--border);
  }
  .doc-table td {
    padding: 10px 0; font-size: 12px;
    border-bottom: 1px solid var(--border); vertical-align: middle;
  }
  .doc-table tr:last-child td { border-bottom: none; }
  .doc-name {
    font-weight: 600; color: var(--text-primary);
    max-width: 160px; white-space: nowrap;
    overflow: hidden; text-overflow: ellipsis;
  }
  .doc-no { font-size: 10.5px; color: var(--text-muted); }
  .status-pill {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 9px; border-radius: 20px;
    font-size: 10.5px; font-weight: 600;
  }
  .status-belum              { background: var(--orange-light); color: var(--orange); }
  .status-siap               { background: var(--accent-light); color: var(--accent); }
  .status-sudah              { background: var(--green-light);  color: var(--green); }
  /* Role-based status pills */
  .status-input              { background: #f1f5f9; color: #475569; }  /* slate  — operator */
  .status-verifikasi         { background: #f5f3ff; color: #7c3aed; }  /* purple — team verifikasi */
  .status-perpajakan         { background: #eff6ff; color: #2563eb; }  /* blue   — perpajakan */
  .status-akutansi           { background: #ecfeff; color: #0891b2; }  /* cyan   — akutansi */
  .status-pembayaran-role    { background: #fffbeb; color: #d97706; }  /* amber  — menunggu bayar */
  .doc-amount { font-weight: 600; font-size: 12px; text-align: right; }

  /* Per Bagian horizontal bars */
  .types-list { display: flex; flex-direction: column; gap: 0; }
  .type-row {
    display: flex; align-items: center; gap: 8px;
    padding: 9px 0; border-bottom: 1px solid var(--border);
  }
  .type-row:last-child { border-bottom: none; }
  .type-bar-wrap { flex: 1; height: 5px; background: var(--border); border-radius: 9px; overflow: hidden; }
  .type-bar { height: 100%; border-radius: 9px; transition: width .5s ease; }
  .type-name { font-size: 12px; color: var(--text-secondary); min-width: 36px; }
  .type-pct { font-size: 11.5px; font-weight: 600; color: var(--text-primary); min-width: 36px; text-align: right; }
  .type-count { font-size: 11px; color: var(--text-muted); min-width: 55px; text-align: right; }

  /* Donut */
  .donut-wrap { position: relative; width: 160px; height: 160px; margin: 0 auto 16px; }
  .donut-center {
    position: absolute; inset: 0;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
  }
  .donut-center-val {
    font-family: 'Sora', sans-serif; font-size: 22px;
    font-weight: 700; color: var(--text-primary);
  }
  .donut-center-lbl { font-size: 10px; color: var(--text-muted); }
  .legend-list { display: flex; flex-direction: column; gap: 8px; }
  .legend-row { display: flex; align-items: center; justify-content: space-between; }
  .legend-left { display: flex; align-items: center; gap: 7px; }
  .legend-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
  .legend-name { font-size: 12px; color: var(--text-secondary); }
  .legend-pct { font-size: 12px; font-weight: 600; color: var(--text-primary); }

  /* Real-time indicator */
  .realtime-badge {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 10.5px; color: var(--green); font-weight: 500;
  }
  .realtime-dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: var(--green); animation: pulse-dot 2s infinite;
    flex-shrink: 0;
  }
  @keyframes pulse-dot {
    0%,100% { opacity: 1; transform: scale(1); }
    50%      { opacity: .5; transform: scale(.8); }
  }

  /* Animations */
  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
  }
  @keyframes rowFadeIn {
    from { opacity: 0; transform: translateY(-4px); }
    to   { opacity: 1; transform: translateY(0); }
  }
  .doc-row-new { animation: rowFadeIn .4s ease both; }
</style>

{{-- Load Chart.js --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

<div class="owner-dash">

  {{-- TOPBAR --}}
  <div class="owner-topbar">
    <div>
      <div class="owner-topbar-title">Overview Keuangan</div>
      <div class="owner-topbar-subtitle">Pantau dan kelola semua dokumen perusahaan</div>
    </div>
    <div class="owner-topbar-controls">
      <div class="ctrl-pill">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
          <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
          <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        {{ now()->format('d M Y') }}
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:11px;height:11px">
          <polyline points="6 9 12 15 18 9"/>
        </svg>
      </div>
      <div class="ctrl-pill">
        <span class="badge-dot" style="background:var(--green)"></span>
        Semua Bagian
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:11px;height:11px">
          <polyline points="6 9 12 15 18 9"/>
        </svg>
      </div>
      <div class="ctrl-pill">
        30 Hari Terakhir
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:11px;height:11px">
          <polyline points="6 9 12 15 18 9"/>
        </svg>
      </div>
    </div>
  </div>

  {{-- CONTENT --}}
  <div class="owner-content">

    {{-- STAT CARDS ROW --}}
    <div class="stats-row">

      {{-- Total Dokumen --}}
      <div class="stat-card">
        <div class="stat-label">Total Dokumen</div>
        <div class="stat-icon" style="background:#f0f4ff">
          <svg viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2">
            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
            <polyline points="14,2 14,8 20,8"/>
          </svg>
        </div>
        <div class="stat-value">{{ number_format($totalDokumen) }}</div>
        <div class="stat-sub">Total seluruh dokumen</div>
      </div>

      {{-- Belum Dibayar --}}
      <div class="stat-card">
        <div class="stat-label">Belum Dibayar</div>
        <div class="stat-icon" style="background:#fffbeb">
          <svg viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
        </div>
        <div class="stat-value" style="color:#f59e0b">{{ number_format($dokumenProses) }}</div>
        <div class="stat-sub" style="font-weight:600;color:#f59e0b">
          Rp {{ number_format($nilaiBelumDibayar, 0, ',', '.') }}
        </div>
      </div>

      {{-- Siap Dibayar --}}
      <div class="stat-card">
        <div class="stat-label">Siap Dibayar</div>
        <div class="stat-icon" style="background:#eff4ff">
          <svg viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
        </div>
        <div class="stat-value" style="color:#2563eb">{{ number_format($dokumenSiapBayar) }}</div>
        <div class="stat-sub">Rp {{ number_format($nilaiSiapDibayar, 0, ',', '.') }}</div>
      </div>

      {{-- Sudah Dibayar --}}
      <div class="stat-card">
        <div class="stat-label">Sudah Dibayar</div>
        <div class="stat-icon" style="background:#ecfdf5">
          <svg viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2">
            <rect x="2" y="5" width="20" height="14" rx="2"/>
            <line x1="2" y1="10" x2="22" y2="10"/>
          </svg>
        </div>
        <div class="stat-value" style="color:#10b981">{{ number_format($dokumenSelesai) }}</div>
        <div class="stat-sub" style="font-weight:600;color:#10b981">
          Rp {{ number_format($nilaiSudahDibayar, 0, ',', '.') }}
        </div>
      </div>

      {{-- Total Nilai (accent) --}}
      <div class="stat-card accent">
        <div class="stat-label">Total Nilai</div>
        @php
          $totalNilaiNum = (float)($totalNilai ?? 0);
          $totalNilaiShort = $totalNilaiNum >= 1_000_000_000
            ? 'Rp ' . number_format($totalNilaiNum / 1_000_000_000, 1, ',', '.') . ' M'
            : 'Rp ' . number_format($totalNilaiNum / 1_000_000, 1, ',', '.') . ' Jt';
        @endphp
        <div class="stat-value">{{ $totalNilaiShort }}</div>
        <div class="stat-sub">Rp {{ number_format($totalNilaiNum, 0, ',', '.') }}</div>
        <div style="margin-top:12px;font-size:11px;color:rgba(255,255,255,.7)">Per {{ now()->format('d F Y') }}</div>
      </div>

    </div>{{-- /.stats-row --}}

    {{-- CHARTS ROW --}}
    <div class="charts-row">

      {{-- Tren Dokumen Masuk --}}
      <div class="dash-card">
        <div class="dash-card-header">
          <div>
            <div class="dash-card-title">Tren Dokumen Masuk</div>
            <div class="dash-card-sub">Volume dokumen 30 hari terakhir</div>
          </div>
          <div style="display:flex;gap:8px;align-items:center">
            <div class="period-tabs">
              <div class="period-tab" data-days="7">7H</div>
              <div class="period-tab active" data-days="30">30H</div>
              <div class="period-tab" data-days="90">90H</div>
            </div>
          </div>
        </div>
        <canvas id="trendChart" height="90"></canvas>
      </div>

      {{-- Top Bagian Table --}}
      <div class="dash-card">
        <div class="dash-card-header">
          <div>
            <div class="dash-card-title">Top Bagian</div>
            <div class="dash-card-sub">Volume & keterlambatan</div>
          </div>
          <a class="see-all" href="{{ url('/owner/analytics') }}">Lihat Semua →</a>
        </div>
        <table class="top-table">
          <thead>
            <tr>
              <th>Bagian</th>
              <th>Dokumen</th>
              <th>Terlambat</th>
            </tr>
          </thead>
          <tbody>
            @php
              $topBagian = collect($bagianStats ?? [])->take(5);
            @endphp
            @foreach($topBagian as $b)
              <tr>
                <td>
                  <span style="display:flex;align-items:center">
                    <span class="bagian-dot" style="background:{{ $b['color'] ?? '#94a3b8' }}; width:8px;height:8px;border-radius:50%;display:inline-block;margin-right:7px"></span>
                    <span class="bagian-name">{{ $b['code'] }}</span>
                  </span>
                </td>
                <td>{{ number_format($b['count']) }}</td>
                <td class="return-val {{ ($b['terlambat'] ?? 0) > 0 ? 'val-down' : 'val-up' }}">
                  {{ ($b['terlambat'] ?? 0) > 0 ? '↓ '.$b['terlambat'] : '✓ 0' }}
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

    </div>{{-- /.charts-row --}}

    {{-- BOTTOM ROW --}}
    <div class="bottom-row">

      {{-- Dokumen Terbaru (Real-time) --}}
      <div class="dash-card">
        <div class="dash-card-header">
          <div>
            <div class="dash-card-title">Dokumen Terbaru</div>
            <div class="dash-card-sub">
              <span class="realtime-badge">
                <span class="realtime-dot"></span> Live — update otomatis
              </span>
            </div>
          </div>
          <a class="see-all" href="{{ url('/owner/dokumen') }}">Lihat Semua →</a>
        </div>
        <table class="doc-table" id="recentDocsTable">
          <thead>
            <tr>
              <th>Dokumen</th>
              <th>Bagian</th>
              <th>Status</th>
              <th style="text-align:right">Nilai</th>
            </tr>
          </thead>
          <tbody id="recentDocsTbody">
            @php
              $bagianColors = [
                'AKN' => '#7C3AED', 'DPM' => '#22c55e', 'KPL' => '#f59e0b',
                'PMO' => '#06b6d4', 'SDM' => '#8b5cf6', 'SKH' => '#ec4899',
                'TAN' => '#10b981', 'TEP' => '#6366f1', 'PTI' => '#3B82F6',
              ];
            @endphp
            @forelse($recentDocuments ?? [] as $doc)
              <tr class="doc-row-new">
                <td>
                  <div class="doc-name" title="{{ $doc['uraian_spp'] ?? '-' }}">
                    {{ Str::limit($doc['uraian_spp'] ?? '-', 28) }}
                  </div>
                  <div class="doc-no">{{ $doc['nomor_spp'] ?? '-' }}</div>
                </td>
                <td>
                  @php $bColor = $bagianColors[$doc['bagian'] ?? ''] ?? '#94a3b8'; @endphp
                  <span style="font-size:11.5px;font-weight:600;color:{{ $bColor }}">
                    {{ $doc['bagian'] ?? '-' }}
                  </span>
                </td>
                <td>
                  <span class="status-pill {{ $doc['status_class'] ?? 'status-belum' }}">
                    {{ $doc['status_label'] ?? 'Menunggu' }}
                  </span>
                </td>
                <td class="doc-amount">
                  @php
                    $val = (float)($doc['nilai_rupiah'] ?? 0);
                    echo $val >= 1_000_000
                      ? 'Rp '.number_format($val/1_000_000, 1, ',', '.').' Jt'
                      : 'Rp '.number_format($val, 0, ',', '.');
                  @endphp
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" style="text-align:center;color:var(--text-muted);padding:24px 0">
                  Belum ada dokumen
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Per Bagian Horizontal Bars --}}
      <div class="dash-card">
        <div class="dash-card-header">
          <div>
            <div class="dash-card-title">Per Bagian</div>
            <div class="dash-card-sub">Distribusi volume dokumen</div>
          </div>
          <a class="see-all" href="{{ url('/owner/analytics') }}">Detail →</a>
        </div>
        <div class="types-list">
          @php
            $maxCount = collect($bagianStats ?? [])->max('count') ?: 1;
            $totalAll = collect($bagianStats ?? [])->sum('count') ?: 1;
          @endphp
          @foreach($bagianStats ?? [] as $b)
            @php
              $pct = round(($b['count'] / $totalAll) * 100, 1);
              $barWidth = round(($b['count'] / $maxCount) * 100, 1);
            @endphp
            <div class="type-row">
              <span class="type-name" style="font-weight:600;color:{{ $b['color'] ?? '#94a3b8' }}">{{ $b['code'] }}</span>
              <div class="type-bar-wrap">
                <div class="type-bar" style="width:{{ $barWidth }}%;background:{{ $b['color'] ?? '#94a3b8' }}"></div>
              </div>
              <span class="type-pct">{{ $pct }}%</span>
              <span class="type-count">{{ number_format($b['count']) }} dok</span>
            </div>
          @endforeach
        </div>
      </div>

      {{-- Status Pembayaran Donut --}}
      <div class="dash-card">
        <div class="dash-card-header">
          <div>
            <div class="dash-card-title">Status Pembayaran</div>
            <div class="dash-card-sub">Distribusi status dokumen</div>
          </div>
        </div>
        <div class="donut-wrap">
          <canvas id="donutChart"></canvas>
          <div class="donut-center">
            <div class="donut-center-val">{{ number_format($totalDokumen) }}</div>
            <div class="donut-center-lbl">Total Dok</div>
          </div>
        </div>
        <div class="legend-list">
          @php
            $totalDonut = $totalDokumen ?: 1;
            $pctSudah = round(($dokumenSelesai / $totalDonut) * 100, 1);
            $pctBelum = round(($dokumenProses / $totalDonut) * 100, 1);
            $pctSiap  = round(($dokumenSiapBayar / $totalDonut) * 100, 1);
          @endphp
          <div class="legend-row">
            <div class="legend-left">
              <div class="legend-dot" style="background:#10b981"></div>
              <span class="legend-name">Sudah Dibayar</span>
            </div>
            <span class="legend-pct">{{ $pctSudah }}%</span>
          </div>
          <div class="legend-row">
            <div class="legend-left">
              <div class="legend-dot" style="background:#f59e0b"></div>
              <span class="legend-name">Belum Dibayar</span>
            </div>
            <span class="legend-pct">{{ $pctBelum }}%</span>
          </div>
          <div class="legend-row">
            <div class="legend-left">
              <div class="legend-dot" style="background:#2563eb"></div>
              <span class="legend-name">Siap Dibayar</span>
            </div>
            <span class="legend-pct">{{ $pctSiap }}%</span>
          </div>
        </div>
      </div>

    </div>{{-- /.bottom-row --}}

  </div>{{-- /.owner-content --}}
</div>{{-- /.owner-dash --}}

<script>
// ── Trend Chart ──────────────────────────────────────────
const trendLabels = @json($chartLabels ?? []);
const trendData   = @json($chartData ?? []);

const tCtx = document.getElementById('trendChart')?.getContext('2d');
let trendChart;
if (tCtx) {
  trendChart = new Chart(tCtx, {
    type: 'line',
    data: {
      labels: trendLabels,
      datasets: [{
        label: 'Dokumen Masuk',
        data: trendData,
        borderColor: '#0f766e',
        backgroundColor: 'rgba(15,118,110,.08)',
        fill: true,
        tension: 0.45,
        pointRadius: 0,
        pointHoverRadius: 5,
        pointHoverBackgroundColor: '#0f766e',
        borderWidth: 2.5,
      }]
    },
    options: {
      plugins: {
        legend: { display: false },
        tooltip: {
          mode: 'index', intersect: false,
          callbacks: { label: ctx => ` ${ctx.raw} dokumen` }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { font: { size: 10.5, family: 'Plus Jakarta Sans' }, color: '#a0aec0', maxTicksLimit: 6 }
        },
        y: {
          grid: { color: '#f1f5f9' },
          ticks: { font: { size: 10.5, family: 'Plus Jakarta Sans' }, color: '#a0aec0' },
          border: { display: false }
        }
      },
      interaction: { mode: 'index', intersect: false }
    }
  });
}

// Period tab switching
document.querySelectorAll('.period-tab').forEach(tab => {
  tab.addEventListener('click', function() {
    document.querySelectorAll('.period-tab').forEach(t => t.classList.remove('active'));
    this.classList.add('active');
    const days = parseInt(this.dataset.days);
    fetch(`/owner/api/trend-chart?days=${days}`, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(r => r.json()).then(data => {
      if (data.labels && trendChart) {
        trendChart.data.labels = data.labels;
        trendChart.data.datasets[0].data = data.data;
        trendChart.update();
      }
    }).catch(() => {}); // Fail silently
  });
});

// ── Donut Chart ───────────────────────────────────────────
const dCtx = document.getElementById('donutChart')?.getContext('2d');
if (dCtx) {
  new Chart(dCtx, {
    type: 'doughnut',
    data: {
      labels: ['Sudah Dibayar', 'Belum Dibayar', 'Siap Dibayar'],
      datasets: [{
        data: [{{ $dokumenSelesai }}, {{ $dokumenProses }}, {{ $dokumenSiapBayar }}],
        backgroundColor: ['#10b981', '#f59e0b', '#2563eb'],
        borderWidth: 0,
        hoverOffset: 6,
      }]
    },
    options: {
      cutout: '72%',
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: { label: ctx => ` ${ctx.raw.toLocaleString('id-ID')} dokumen` }
        }
      }
    }
  });
}

// ── Real-time Dokumen Terbaru ─────────────────────────────
const bagianColors = {
  'AKN': '#7C3AED', 'DPM': '#22c55e', 'KPL': '#f59e0b',
  'PMO': '#06b6d4', 'SDM': '#8b5cf6', 'SKH': '#ec4899',
  'TAN': '#10b981', 'TEP': '#6366f1', 'PTI': '#3B82F6',
};

function formatCurrency(val) {
  val = parseFloat(val) || 0;
  if (val >= 1_000_000) return 'Rp ' + (val / 1_000_000).toFixed(1).replace('.', ',') + ' Jt';
  return 'Rp ' + val.toLocaleString('id-ID');
}

function truncate(str, len) {
  if (!str) return '-';
  return str.length > len ? str.substring(0, len) + '...' : str;
}

function buildDocRow(doc, isNew = false) {
  const color = bagianColors[doc.bagian] || '#94a3b8';
  return `
    <tr class="doc-row-new${isNew ? '' : ''}">
      <td>
        <div class="doc-name" title="${(doc.uraian_spp || '-').replace(/"/g,'&quot;')}">${truncate(doc.uraian_spp, 28)}</div>
        <div class="doc-no">${doc.nomor_spp || '-'}</div>
      </td>
      <td><span style="font-size:11.5px;font-weight:600;color:${color}">${doc.bagian || '-'}</span></td>
      <td><span class="status-pill ${doc.status_class || 'status-belum'}">${doc.status_label || 'Menunggu'}</span></td>
      <td class="doc-amount">${formatCurrency(doc.nilai_rupiah)}</td>
    </tr>`;
}

let lastSeenIds = [];

function pollRecentDocs() {
  fetch('/owner/api/recent-documents', {
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
    }
  })
  .then(r => r.json())
  .then(data => {
    if (!data.success || !Array.isArray(data.documents)) return;
    const docs = data.documents;
    const newIds = docs.map(d => d.id);

    // Check if anything changed
    const hasChanged = JSON.stringify(newIds) !== JSON.stringify(lastSeenIds);
    if (!hasChanged) return;

    const tbody = document.getElementById('recentDocsTbody');
    if (!tbody) return;

    const isFirstLoad = lastSeenIds.length === 0;
    lastSeenIds = newIds;

    if (docs.length === 0) {
      tbody.innerHTML = `<tr><td colspan="4" style="text-align:center;color:var(--text-muted);padding:24px 0">Belum ada dokumen</td></tr>`;
      return;
    }

    tbody.innerHTML = docs.map((doc, i) => buildDocRow(doc, !isFirstLoad && i < 1)).join('');
  })
  .catch(() => {}); // Fail silently
}

// Initial poll + every 10 seconds
pollRecentDocs();
setInterval(pollRecentDocs, 10000);
</script>
@endsection