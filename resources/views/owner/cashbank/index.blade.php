@extends('layouts/app')
@section('content')

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Sora:wght@600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
// Verify Chart.js loaded immediately
if (typeof Chart !== 'undefined') {
  console.log('✅ Chart.js loaded successfully, version:', Chart.version || 'unknown');
} else {
  console.error('❌ Chart.js failed to load from CDN');
}
</script>

<style>
*,*::before,*::after{box-sizing:border-box}
:root{
  --bg:#f5f6fa;--card:#fff;--border:#e9ecf3;--primary:#1a2340;--muted:#8492a6;
  --accent:#0d9488;--accent2:#2563eb;--green:#16a34a;--green-bg:#f0fdf4;
  --yellow:#d97706;--yellow-bg:#fffbeb;--red:#dc2626;--red-bg:#fef2f2;
  --r:12px;--sh:0 1px 3px rgba(0,0,0,.06),0 4px 12px rgba(0,0,0,.05);
}
.cb-wrap{
  font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg);
  padding:24px 28px;min-height:100vh;font-size:13.5px;color:var(--primary);
}

/* ── HEADER ── */
.cb-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:22px;flex-wrap:wrap;gap:14px}
.cb-title{font-family:'Sora',sans-serif;font-size:21px;font-weight:700;color:var(--primary);margin:0 0 4px}
.cb-sub{font-size:12.5px;color:var(--muted);margin:0}
.cb-filter-form{display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end}
.cb-filter-form select,.cb-filter-form input{
  border:1px solid var(--border);border-radius:7px;padding:7px 10px;font-size:12px;
  font-family:inherit;color:var(--primary);background:#fff;outline:none;cursor:pointer;
}
.cb-btn{
  padding:7px 16px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;
  border:none;background:var(--accent);color:#fff;transition:.15s;
}
.cb-btn:hover{opacity:.88}

/* ── STAT CARDS ── */
.cb-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px}
@media(max-width:1100px){.cb-stats{grid-template-columns:repeat(2,1fr)}}
@media(max-width:600px){.cb-stats{grid-template-columns:1fr 1fr}}

.cb-stat{
  border-radius:var(--r);padding:18px 20px;display:flex;align-items:center;gap:16px;
  box-shadow:var(--sh);animation:fadeUp .4s ease both;
}
.cb-stat:nth-child(1){background:linear-gradient(135deg,#0d9488,#0891b2);animation-delay:.05s}
.cb-stat:nth-child(2){background:linear-gradient(135deg,#16a34a,#15803d);animation-delay:.1s}
.cb-stat:nth-child(3){background:linear-gradient(135deg,#7c3aed,#6d28d9);animation-delay:.15s}
.cb-stat:nth-child(4){background:linear-gradient(135deg,#ea580c,#dc2626);animation-delay:.2s}

.cb-stat-icon{
  width:44px;height:44px;border-radius:10px;background:rgba(255,255,255,.2);
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.cb-stat-icon svg{width:20px;height:20px;color:#fff}
.cb-stat-val{font-family:'Sora',sans-serif;font-size:16px;font-weight:700;color:#fff;line-height:1.2;word-break:break-all}
.cb-stat-lbl{font-size:11px;color:rgba(255,255,255,.8);margin-top:3px}
.cb-stat-sub{font-size:10px;color:rgba(255,255,255,.65);margin-top:2px}

/* ── CARD GENERIC ── */
.cb-card{
  background:#fff;border:1px solid var(--border);border-radius:var(--r);
  box-shadow:var(--sh);overflow:hidden;animation:fadeUp .4s ease both;
}
.cb-card-header{
  display:flex;align-items:center;justify-content:space-between;
  padding:14px 18px;border-bottom:1px solid var(--border);
}
.cb-card-title{
  display:flex;align-items:center;gap:8px;font-size:13px;font-weight:700;color:var(--primary);
}
.cb-card-title svg{width:15px;height:15px}
.cb-card-body{padding:18px}

/* ── 2-COL GRID ── */
.cb-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px}
@media(max-width:900px){.cb-grid-2{grid-template-columns:1fr}}
.cb-grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:18px}
@media(max-width:900px){.cb-grid-3{grid-template-columns:1fr}}

/* ── TABLE ── */
table.cb-table{width:100%;border-collapse:collapse}
table.cb-table thead th{
  font-size:10.5px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;
  color:var(--muted);padding:10px 14px;text-align:left;background:#fafbfd;
  border-bottom:1px solid var(--border);white-space:nowrap;
}
table.cb-table tbody tr{border-bottom:1px solid var(--border);transition:background .1s}
table.cb-table tbody tr:last-child{border-bottom:none}
table.cb-table tbody tr:hover{background:#fafbfd}
table.cb-table tbody td{padding:10px 14px;font-size:12px;vertical-align:middle}

/* ── BADGE ── */
.cb-badge{
  display:inline-flex;align-items:center;padding:3px 9px;border-radius:20px;
  font-size:11px;font-weight:700;white-space:nowrap;
}
.cb-badge-green{background:var(--green-bg);color:var(--green)}
.cb-badge-blue{background:#eff4ff;color:var(--accent2)}
.cb-badge-teal{background:#f0fdfa;color:var(--accent)}
.cb-badge-yellow{background:var(--yellow-bg);color:var(--yellow)}

/* ── SALDO REKENING ── */
.rek-row{
  display:flex;align-items:center;justify-content:space-between;
  padding:11px 16px;border-bottom:1px solid var(--border);
}
.rek-row:last-child{border-bottom:none}
.rek-info{}
.rek-nama{font-size:12px;font-weight:600;color:var(--primary)}
.rek-no{font-size:11px;color:var(--muted);font-family:monospace}
.rek-saldo{font-family:'Sora',sans-serif;font-size:13px;font-weight:700;text-align:right}
.rek-saldo.positif{color:var(--green)}
.rek-saldo.negatif{color:var(--red)}

/* ── PROGRESS BAR ── */
.cb-progress-row{display:flex;align-items:center;gap:10px;margin-bottom:10px}
.cb-progress-label{font-size:12px;font-weight:500;min-width:180px;color:var(--primary)}
.cb-progress-bar-wrap{flex:1;background:#f1f5f9;border-radius:6px;height:8px;overflow:hidden}
.cb-progress-bar{height:8px;border-radius:6px;background:linear-gradient(90deg,#0d9488,#0891b2);transition:width .6s ease}
.cb-progress-val{font-size:11px;font-weight:600;min-width:100px;text-align:right;color:var(--muted)}

/* ── EMPTY ── */
.cb-empty{text-align:center;padding:36px 24px;color:var(--muted);font-size:12px}

/* ── CHIP PERIODE ── */
.cb-periode-chip{
  display:inline-flex;align-items:center;gap:5px;padding:4px 10px;
  border-radius:6px;background:#f0fdfa;border:1px solid #99f6e4;
  color:var(--accent);font-size:11px;font-weight:600;
}

/* ── REALISASI GAUGE ── */
.gauge-wrap{text-align:center;padding:16px 0 8px}
.gauge-pct{font-family:'Sora',sans-serif;font-size:36px;font-weight:700;line-height:1;color:var(--accent)}
.gauge-lbl{font-size:11px;color:var(--muted);margin-top:4px}

/* Komposisi Penerimaan per Komoditas */
.commodity-summary-grid{
  display:grid;
  grid-template-columns:repeat(3,minmax(0,1fr));
  gap:12px;
  margin-bottom:22px;
}
.commodity-summary-card{min-width:0;border-radius:10px;padding:13px 14px}
.commodity-summary-card.green{background:#f0fdf4;border:1px solid #bbf7d0}
.commodity-summary-card.blue{background:#eff6ff;border:1px solid #bfdbfe}
.commodity-summary-card.yellow{background:#fef3c7;border:1px solid #fde047}
.commodity-summary-label{
  margin-bottom:5px;font-size:10px;font-weight:700;letter-spacing:.05em;text-transform:uppercase;
}
.commodity-summary-card.green .commodity-summary-label{color:#15803d}
.commodity-summary-card.blue .commodity-summary-label{color:#1e40af}
.commodity-summary-card.yellow .commodity-summary-label{color:#a16207}
.commodity-summary-value{
  min-width:0;font-family:'Sora',sans-serif;font-size:16px;font-weight:700;line-height:1.25;
  overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
}
.commodity-summary-card.green .commodity-summary-value{color:#15803d}
.commodity-summary-card.blue .commodity-summary-value{color:#1e40af;font-size:14px}
.commodity-summary-card.yellow .commodity-summary-value{color:#a16207}
.commodity-composition{
  display:grid;
  grid-template-columns:minmax(240px,.85fr) minmax(300px,1.15fr);
  gap:22px;align-items:center;min-width:0;
}
.commodity-chart-panel{
  min-width:0;display:flex;align-items:center;justify-content:center;padding:16px;
  border:1px solid #e3efed;border-radius:14px;background:linear-gradient(180deg,#fbfdfd,#f8fafc);
}
.commodity-chart-box{
  position:relative;width:min(100%,280px);max-width:300px;aspect-ratio:1;
  display:flex;align-items:center;justify-content:center;
}
.commodity-chart-box canvas{width:100% !important;height:100% !important;max-width:100%;max-height:100%}
.commodity-loading{
  position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;
  gap:8px;color:var(--muted);text-align:center;font-size:12px;font-weight:700;pointer-events:none;
}
.commodity-loading-icon{font-size:22px;line-height:1}
.commodity-legend{min-width:0;max-height:356px;overflow-y:auto;overflow-x:hidden;padding-right:4px}
.commodity-legend::-webkit-scrollbar{width:6px}
.commodity-legend::-webkit-scrollbar-track{background:#f1f5f9;border-radius:999px}
.commodity-legend::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:999px}
.commodity-legend-list{display:grid;grid-template-columns:1fr;gap:9px;min-width:0}
.legend-item{
  --item-color:#0d9488;
  display:grid;grid-template-columns:12px minmax(0,1fr) auto;gap:12px;align-items:center;min-width:0;
  padding:10px 12px;border:1px solid #edf1f7;border-radius:11px;background:#fafbfd;cursor:pointer;
  transition:background .16s ease,border-color .16s ease,box-shadow .16s ease,transform .16s ease;
}
.legend-item:hover,.legend-item.active{border-color:var(--item-color);background:#fff;box-shadow:0 6px 18px rgba(15,23,42,.07)}
.legend-item:hover{transform:translateY(-1px)}
.legend-marker{width:12px;height:12px;border-radius:4px;background:var(--item-color)}
.legend-main{min-width:0}
.legend-name{
  color:var(--primary);font-size:12.5px;font-weight:700;line-height:1.3;
  overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
}
.legend-value{margin-top:3px;color:var(--muted);font-size:11px;font-weight:600;white-space:nowrap}
.legend-percent{
  justify-self:end;max-width:none;padding:4px 8px;border-radius:999px;background:#fff;
  border:1px solid color-mix(in srgb,var(--item-color) 25%,#e5e7eb);
  color:var(--item-color);font-family:'Sora',sans-serif;font-size:12px;font-weight:700;line-height:1;white-space:nowrap;
}
@media(max-width:1180px){
  .commodity-composition{grid-template-columns:1fr}
  .commodity-chart-panel{padding:14px}
  .commodity-legend{max-height:320px}
}
@media(max-width:640px){
  .cb-wrap{padding-left:16px;padding-right:16px}
  .commodity-summary-grid{grid-template-columns:1fr}
  .commodity-composition{gap:16px}
  .commodity-chart-box{width:min(100%,250px)}
  .legend-item{padding:10px;gap:10px}
}

/* Rencana Pengeluaran per Kategori */
.expense-plan-card .cb-card-body{padding:20px 22px 22px}
.expense-summary-grid{
  display:grid;
  grid-template-columns:repeat(3,minmax(0,1fr));
  gap:10px;
  margin-bottom:16px;
}
.expense-summary-card{
  min-width:0;
  padding:12px;
  border:1px solid #ede9fe;
  border-radius:10px;
  background:linear-gradient(180deg,#fbfaff,#f8fafc);
}
.expense-summary-card.accent{
  border-color:#ddd6fe;
  background:linear-gradient(135deg,#f5f3ff,#faf5ff);
}
.expense-summary-label{
  margin-bottom:5px;
  color:#7c6aa6;
  font-size:10px;
  font-weight:800;
  letter-spacing:.05em;
  text-transform:uppercase;
}
.expense-summary-value{
  min-width:0;
  color:#2d2350;
  font-family:'Sora',sans-serif;
  font-size:15px;
  font-weight:800;
  line-height:1.25;
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
}
.expense-summary-sub{
  margin-top:4px;
  color:#8b7aa8;
  font-size:10.5px;
  font-weight:600;
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
}
.expense-insight{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:14px;
  margin-bottom:14px;
  padding:12px 14px;
  border:1px solid #ede9fe;
  border-radius:12px;
  background:#faf8ff;
}
.expense-insight-copy{min-width:0}
.expense-insight-label{
  color:#7c3aed;
  font-size:11px;
  font-weight:800;
  text-transform:uppercase;
  letter-spacing:.04em;
}
.expense-insight-title{
  margin-top:3px;
  color:var(--primary);
  font-size:13px;
  font-weight:800;
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
}
.expense-insight-pill{
  flex:0 0 auto;
  padding:7px 10px;
  border-radius:999px;
  background:#fff;
  border:1px solid #ddd6fe;
  color:#7c3aed;
  font-family:'Sora',sans-serif;
  font-size:12px;
  font-weight:800;
  white-space:nowrap;
}
.expense-list{
  display:grid;
  gap:10px;
  max-height:390px;
  overflow-y:auto;
  overflow-x:hidden;
  padding-right:4px;
}
.expense-list::-webkit-scrollbar{width:6px}
.expense-list::-webkit-scrollbar-track{background:#f1f5f9;border-radius:999px}
.expense-list::-webkit-scrollbar-thumb{background:#d8d2ea;border-radius:999px}
.expense-category-item{
  --rank-color:#7c3aed;
  --bar-width:0%;
  display:grid;
  grid-template-columns:34px minmax(0,1fr) auto;
  gap:12px;
  align-items:center;
  min-width:0;
  padding:12px;
  border:1px solid #edf1f7;
  border-radius:12px;
  background:#fff;
  box-shadow:0 1px 2px rgba(15,23,42,.03);
}
.expense-rank{
  width:34px;
  height:34px;
  display:flex;
  align-items:center;
  justify-content:center;
  border-radius:10px;
  background:color-mix(in srgb,var(--rank-color) 13%,white);
  color:var(--rank-color);
  font-family:'Sora',sans-serif;
  font-size:11px;
  font-weight:800;
}
.expense-main{min-width:0}
.expense-name{
  color:var(--primary);
  font-size:12.5px;
  font-weight:800;
  line-height:1.3;
  overflow:hidden;
  text-overflow:ellipsis;
  white-space:nowrap;
}
.expense-meta{
  margin-top:3px;
  color:var(--muted);
  font-size:10.5px;
  font-weight:600;
}
.expense-track{
  margin-top:8px;
  height:8px;
  overflow:hidden;
  border-radius:999px;
  background:#f1f5f9;
}
.expense-fill{
  display:block;
  width:var(--bar-width);
  min-width:0;
  height:100%;
  border-radius:999px;
  background:linear-gradient(90deg,var(--rank-color),#6d28d9);
}
.expense-value{
  justify-self:end;
  min-width:92px;
  text-align:right;
}
.expense-value strong{
  display:block;
  color:#2d2350;
  font-family:'Sora',sans-serif;
  font-size:12px;
  font-weight:800;
  white-space:nowrap;
}
.expense-share{
  display:inline-flex;
  margin-top:5px;
  padding:3px 7px;
  border-radius:999px;
  border:1px solid #e9d5ff;
  background:#faf5ff;
  color:#7c3aed;
  font-size:10.5px;
  font-weight:800;
  white-space:nowrap;
}
.expense-empty{
  padding:40px 20px;
  border:1px dashed #ddd6fe;
  border-radius:12px;
  background:#faf8ff;
  color:var(--muted);
  text-align:center;
  font-size:12px;
  font-weight:700;
}
@media(max-width:1180px){
  .expense-summary-grid{grid-template-columns:1fr}
}
@media(max-width:640px){
  .expense-plan-card .cb-card-body{padding:18px 16px}
  .expense-insight{align-items:flex-start;flex-direction:column}
  .expense-category-item{grid-template-columns:30px minmax(0,1fr);align-items:start}
  .expense-rank{width:30px;height:30px}
  .expense-value{grid-column:2;justify-self:start;text-align:left;min-width:0}
}

@keyframes fadeUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
</style>

@php
function rupiah(float $n): string {
    if ($n >= 1_000_000_000) return 'Rp ' . number_format($n / 1_000_000_000, 2, ',', '.') . ' M';
    if ($n >= 1_000_000)     return 'Rp ' . number_format($n / 1_000_000, 2, ',', '.') . ' Jt';
    return 'Rp ' . number_format($n, 0, ',', '.');
}
function rupiahFull(float $n): string {
    return 'Rp ' . number_format($n, 0, ',', '.');
}
@endphp

<div class="cb-wrap">

  {{-- ── HEADER ── --}}
  <div class="cb-header">
    <div>
      <h1 class="cb-title">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
             style="width:22px;height:22px;vertical-align:middle;margin-right:8px;color:#0d9488">
          <rect x="2" y="3" width="20" height="14" rx="2"/>
          <path d="M8 21h8M12 17v4"/>
        </svg>
        Laporan Cash Bank
      </h1>
      <p class="cb-sub">
        Data real-time dari sistem Cash Bank PTPN IV Regional V &nbsp;·&nbsp;
        <span class="cb-periode-chip">
          {{ $bulanList[$bulanDari] }} – {{ $bulanList[$bulanSampai] }} {{ $tahun }}
        </span>
      </p>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ request()->url() }}" class="cb-filter-form">
      <div style="display:flex;flex-direction:column;gap:3px">
        <label style="font-size:10.5px;font-weight:600;color:var(--muted)">Tahun</label>
        <input type="number" name="tahun" value="{{ $tahun }}"
               min="2020" max="2035" style="width:80px">
      </div>
      <div style="display:flex;flex-direction:column;gap:3px">
        <label style="font-size:10.5px;font-weight:600;color:var(--muted)">Dari Bulan</label>
        <select name="bulan_dari">
          @foreach($bulanList as $no => $nm)
            <option value="{{ $no }}" @selected($no == $bulanDari)>{{ $nm }}</option>
          @endforeach
        </select>
      </div>
      <div style="display:flex;flex-direction:column;gap:3px">
        <label style="font-size:10.5px;font-weight:600;color:var(--muted)">Sampai Bulan</label>
        <select name="bulan_sampai">
          @foreach($bulanList as $no => $nm)
            <option value="{{ $no }}" @selected($no == $bulanSampai)>{{ $nm }}</option>
          @endforeach
        </select>
      </div>
      <button type="submit" class="cb-btn" style="align-self:flex-end">Terapkan</button>
    </form>
  </div>

  {{-- ── STAT CARDS ── --}}
  <div class="cb-stats">
    {{-- Total Penerimaan --}}
    <div class="cb-stat">
      <div class="cb-stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>
        </svg>
      </div>
      <div>
        <div class="cb-stat-val">{{ rupiah((float)$ringkasan['total_penerimaan']) }}</div>
        <div class="cb-stat-lbl">Total Penerimaan</div>
        <div class="cb-stat-sub">Penjualan CPO, Kernel, TBS, dll.</div>
      </div>
    </div>
    {{-- Total Dropping (realisasi) --}}
    <div class="cb-stat">
      <div class="cb-stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/>
        </svg>
      </div>
      <div>
        <div class="cb-stat-val">{{ rupiah((float)$ringkasan['total_dropping']) }}</div>
        <div class="cb-stat-lbl">Realisasi Pengeluaran</div>
        <div class="cb-stat-sub">Dana yang sudah dicairkan</div>
      </div>
    </div>
    {{-- Total Permintaan --}}
    <div class="cb-stat">
      <div class="cb-stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
          <polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/>
          <line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>
        </svg>
      </div>
      <div>
        <div class="cb-stat-val">{{ rupiah((float)$ringkasan['total_permintaan']) }}</div>
        <div class="cb-stat-lbl">Rencana Pengeluaran</div>
        <div class="cb-stat-sub">Anggaran yang diajukan</div>
      </div>
    </div>
    {{-- % Realisasi --}}
    <div class="cb-stat">
      <div class="cb-stat-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/>
          <polyline points="12 6 12 12 16 14"/>
        </svg>
      </div>
      <div>
        <div class="cb-stat-val">{{ $ringkasan['realisasi_pct'] }}%</div>
        <div class="cb-stat-lbl">Tingkat Realisasi</div>
        <div class="cb-stat-sub">Realisasi ÷ Rencana Pengeluaran</div>
      </div>
    </div>
  </div>

  {{-- ── ROW 2: CHART + SALDO REKENING ── --}}
  <div class="cb-grid-2" style="animation-delay:.25s">

    {{-- Chart: Tren Rencana vs Realisasi --}}
    <div class="cb-card" style="animation-delay:.28s">
      <div class="cb-card-header">
        <div class="cb-card-title" style="color:#0d9488">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
          </svg>
          Tren Rencana vs Realisasi Pengeluaran
        </div>
      </div>
      <div class="cb-card-body">
        <canvas id="chartTren" height="130"></canvas>
      </div>
    </div>

    {{-- Saldo per Rekening --}}
    <div class="cb-card" style="animation-delay:.32s">
      <div class="cb-card-header">
        <div class="cb-card-title" style="color:var(--accent2)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="2" y="3" width="20" height="14" rx="2"/>
            <path d="M8 21h8M12 17v4"/>
          </svg>
          Saldo Rekening Bank
        </div>
        @php $totalSaldo = array_sum(array_column($saldoRekening, 'saldo_bersih')) @endphp
        <span style="font-size:12px;font-weight:700;color:var(--green)">
          Total: {{ rupiahFull($totalSaldo) }}
        </span>
      </div>
      @if(count($saldoRekening) > 0)
        @foreach($saldoRekening as $rek)
          <div class="rek-row">
            <div class="rek-info">
              <div class="rek-nama">{{ $rek->nama_singkat }}</div>
              @if($rek->no_rekening)
                <div class="rek-no">No. Rek: {{ $rek->no_rekening }}</div>
              @endif
            </div>
            <div class="rek-saldo {{ $rek->saldo_bersih >= 0 ? 'positif' : 'negatif' }}">
              {{ rupiahFull((float)$rek->saldo_bersih) }}
            </div>
          </div>
        @endforeach
      @else
        <div class="cb-empty">Belum ada data rekening</div>
      @endif
    </div>
  </div>

  {{-- ── ROW 3: PENERIMAAN per KOMODITAS (Donut Chart Interaktif) ── --}}
  <div class="cb-grid-2" style="margin-bottom:18px">

    {{-- Donut Chart Interaktif: Komposisi Penerimaan --}}
    <div class="cb-card" style="animation-delay:.36s">
      <div class="cb-card-header">
        <div class="cb-card-title" style="color:var(--green)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0110 10"/>
          </svg>
          Komposisi Penerimaan per Komoditas
        </div>
      </div>
      <div class="cb-card-body" style="padding:24px">
        {{-- Metric Cards --}}
        <div class="commodity-summary-grid">
          @php
            $topKomoditas = collect($penerimaanKategori)->sortByDesc('total')->first();
            $jumlahKomoditas = count($penerimaanKategori);
          @endphp
          <div class="commodity-summary-card green">
            <div class="commodity-summary-label">Total Penerimaan</div>
            <div class="commodity-summary-value">{{ rupiah((float)$ringkasan['total_penerimaan']) }}</div>
          </div>
          <div class="commodity-summary-card blue">
            <div class="commodity-summary-label">Komoditas Utama</div>
            <div class="commodity-summary-value" title="{{ $topKomoditas ? $topKomoditas->nama_kriteria : '-' }}">{{ $topKomoditas ? $topKomoditas->nama_kriteria : '-' }}</div>
          </div>
          <div class="commodity-summary-card yellow">
            <div class="commodity-summary-label">Jumlah Komoditas</div>
            <div class="commodity-summary-value">{{ $jumlahKomoditas }} item</div>
          </div>
        </div>

        {{-- Chart + Legend Container --}}
        <div class="commodity-composition">
          {{-- Donut Chart --}}
          <div class="commodity-chart-panel">
            <div class="commodity-chart-box">
              <canvas id="chartPenerimaan" aria-label="Komposisi penerimaan per komoditas dalam bentuk donut chart">
              Grafik menampilkan komposisi penerimaan dari berbagai komoditas
              </canvas>
              <div id="chartLoadingIndicator" class="commodity-loading">
                <div class="commodity-loading-icon">Chart</div>
                <div>Loading chart...</div>
              </div>
            </div>
          </div>

          {{-- Custom Legend --}}
          <div id="customLegend" class="commodity-legend">
            <!-- Legend will be generated by JavaScript -->
          </div>
        </div>
      </div>
    </div>

    {{-- Chart Bar: Rencana Pengeluaran per Kategori --}}
    <div class="cb-card expense-plan-card" style="animation-delay:.4s">
      <div class="cb-card-header">
        <div class="cb-card-title" style="color:#7c3aed">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/>
            <line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/>
          </svg>
          Rencana Pengeluaran per Kategori
        </div>
      </div>
      <div class="cb-card-body">
        @php
          $pengeluaranKategori = collect($permintaanKategori ?? [])
            ->sortByDesc(fn ($item) => (float) ($item->total_rencana ?? 0))
            ->values();
          $totalRencanaKategori = (float) $pengeluaranKategori->sum(fn ($item) => (float) ($item->total_rencana ?? 0));
          $topPengeluaran = $pengeluaranKategori->first();
          $maxPengeluaran = max(1, (float) ($topPengeluaran->total_rencana ?? 0));
          $topPengeluaranPct = $totalRencanaKategori > 0
            ? round(((float) ($topPengeluaran->total_rencana ?? 0) / $totalRencanaKategori) * 100, 1)
            : 0;
          $expenseColors = ['#7c3aed', '#6d28d9', '#8b5cf6', '#a855f7', '#9333ea', '#5b21b6'];
        @endphp

        @if($pengeluaranKategori->isNotEmpty())
          <div class="expense-summary-grid">
            <div class="expense-summary-card accent">
              <div class="expense-summary-label">Total Rencana</div>
              <div class="expense-summary-value">{{ rupiah($totalRencanaKategori) }}</div>
              <div class="expense-summary-sub">{{ $bulanList[$bulanDari] }} - {{ $bulanList[$bulanSampai] }} {{ $tahun }}</div>
            </div>
            <div class="expense-summary-card">
              <div class="expense-summary-label">Kategori Dominan</div>
              <div class="expense-summary-value" title="{{ $topPengeluaran->nama_kriteria ?? '-' }}">{{ $topPengeluaran->nama_kriteria ?? '-' }}</div>
              <div class="expense-summary-sub">{{ $topPengeluaranPct }}% dari total rencana</div>
            </div>
            <div class="expense-summary-card">
              <div class="expense-summary-label">Jumlah Kategori</div>
              <div class="expense-summary-value">{{ $pengeluaranKategori->count() }} kategori</div>
              <div class="expense-summary-sub">Diurutkan dari nilai terbesar</div>
            </div>
          </div>

          <div class="expense-insight">
            <div class="expense-insight-copy">
              <div class="expense-insight-label">Kategori terbesar</div>
              <div class="expense-insight-title" title="{{ $topPengeluaran->nama_kriteria ?? '-' }}">
                {{ $topPengeluaran->nama_kriteria ?? '-' }}
              </div>
            </div>
            <div class="expense-insight-pill">{{ rupiah((float) ($topPengeluaran->total_rencana ?? 0)) }} · {{ $topPengeluaranPct }}%</div>
          </div>

          <div class="expense-list">
            @foreach($pengeluaranKategori as $index => $pk)
              @php
                $value = (float) ($pk->total_rencana ?? 0);
                $share = $totalRencanaKategori > 0 ? round(($value / $totalRencanaKategori) * 100, 1) : 0;
                $barWidth = $maxPengeluaran > 0 ? round(($value / $maxPengeluaran) * 100, 1) : 0;
                $rankColor = $expenseColors[$index % count($expenseColors)];
              @endphp
              <div class="expense-category-item" style="--bar-width:{{ $barWidth }}%;--rank-color:{{ $rankColor }}">
                <div class="expense-rank">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</div>
                <div class="expense-main">
                  <div class="expense-name" title="{{ $pk->nama_kriteria }}">{{ $pk->nama_kriteria }}</div>
                  <div class="expense-meta">Kontribusi terhadap total rencana pengeluaran</div>
                  <div class="expense-track" aria-hidden="true">
                    <span class="expense-fill"></span>
                  </div>
                </div>
                <div class="expense-value">
                  <strong>{{ rupiah($value) }}</strong>
                  <span class="expense-share">{{ $share }}%</span>
                </div>
              </div>
            @endforeach
          </div>
        @else
          <div class="expense-empty">
            Belum ada data rencana pengeluaran untuk periode ini.
          </div>
        @endif
      </div>
    </div>
  </div>

  {{-- ── ROW 4: TABEL PENERIMAAN TERBARU + SALDO VA ── --}}
  <div class="cb-grid-2" style="margin-bottom:0">

    {{-- Penerimaan Terbaru --}}
    <div class="cb-card" style="animation-delay:.44s">
      <div class="cb-card-header">
        <div class="cb-card-title">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:#0d9488">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          </svg>
          Penerimaan Terbaru
        </div>
        <span style="font-size:11px;color:var(--muted)">10 transaksi terakhir</span>
      </div>
      <div style="overflow-x:auto">
        <table class="cb-table">
          <thead>
            <tr>
              <th>Tanggal</th>
              <th>Komoditas</th>
              <th>Pembeli</th>
              <th style="text-align:right">Nilai</th>
            </tr>
          </thead>
          <tbody>
            @forelse($penerimaanTerbaru as $pt)
              <tr>
                <td style="white-space:nowrap;color:var(--muted)">
                  {{ \Carbon\Carbon::parse($pt->tanggal)->format('d/m/Y') }}
                </td>
                <td>
                  <span class="cb-badge cb-badge-green" style="font-size:10px">
                    {{ Str::limit($pt->nama_kriteria, 20) }}
                  </span>
                </td>
                <td style="font-size:11.5px">{{ $pt->pembeli }}</td>
                <td style="text-align:right;font-weight:600;color:var(--green);white-space:nowrap">
                  {{ rupiahFull((float)$pt->nilai_inc_ppn) }}
                </td>
              </tr>
            @empty
              <tr><td colspan="4" class="cb-empty">Belum ada data penerimaan</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    {{-- Saldo Virtual Account per Kebun/Unit --}}
    <div class="cb-card" style="animation-delay:.48s">
      <div class="cb-card-header">
        <div class="cb-card-title" style="color:var(--accent2)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
            <polyline points="9 22 9 12 15 12 15 22"/>
          </svg>
          Saldo Virtual Account per Kebun / Unit
        </div>
      </div>
      <div style="overflow-x:auto">
        <table class="cb-table">
          <thead>
            <tr>
              <th>Nama VA / Kebun</th>
              <th style="text-align:right">Total Masuk</th>
              <th style="text-align:right">Total Keluar</th>
              <th style="text-align:right">Saldo</th>
            </tr>
          </thead>
          <tbody>
            @forelse($saldoVA as $va)
              <tr>
                <td style="font-size:12px;font-weight:500">{{ $va->nama_tujuan }}</td>
                <td style="text-align:right;font-size:11.5px;color:var(--green)">
                  {{ rupiah((float)$va->total_masuk) }}
                </td>
                <td style="text-align:right;font-size:11.5px;color:var(--red)">
                  {{ rupiah((float)$va->total_keluar) }}
                </td>
                <td style="text-align:right;font-weight:700;white-space:nowrap;font-size:12px;
                    color:{{ $va->saldo >= 0 ? 'var(--green)' : 'var(--red)' }}">
                  {{ rupiahFull((float)$va->saldo) }}
                </td>
              </tr>
            @empty
              <tr><td colspan="4" class="cb-empty">Belum ada data virtual account</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

@push('scripts')
<script>
// ── DATA DARI CONTROLLER ──
const trenData = {!! json_encode($trenBulanan ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?: '[]' !!};
const penerimaanData = {!! json_encode($penerimaanKategori ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE) ?: '[]' !!};

console.log('🔍 Script loaded, data:', {
  trenData: trenData?.length || 0,
  penerimaanData: penerimaanData?.length || 0,
  chartJsLoaded: typeof Chart !== 'undefined'
});

// Wrap semua dalam DOMContentLoaded untuk memastikan DOM ready
document.addEventListener('DOMContentLoaded', function() {
  console.log('✅ DOM Content Loaded - Initializing charts...');

// ── WARNA CHART ──
const COLORS_PIE = ['#0d9488','#16a34a','#2563eb','#7c3aed','#ea580c','#dc2626','#0891b2','#d97706'];
const COLOR_RENCANA = 'rgba(124,58,237,.8)';
const COLOR_REALISASI = 'rgba(13,148,136,.85)';

// ── CHART TREN ─────────────────────────────────────
const ctxTren = document.getElementById('chartTren')?.getContext('2d');
if (ctxTren && trenData.length) {
  new Chart(ctxTren, {
    type: 'bar',
    data: {
      labels: trenData.map(d => d.label),
      datasets: [
        {
          label: 'Rencana Pengeluaran',
          data: trenData.map(d => d.permintaan),
          backgroundColor: COLOR_RENCANA,
          borderRadius: 6,
          borderSkipped: false,
        },
        {
          label: 'Realisasi (Dropping)',
          data: trenData.map(d => d.dropping),
          backgroundColor: COLOR_REALISASI,
          borderRadius: 6,
          borderSkipped: false,
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: { position: 'top', labels: { font: { size: 11, family: 'Plus Jakarta Sans' } } },
        tooltip: {
          callbacks: {
            label: ctx => ' Rp ' + ctx.raw.toLocaleString('id-ID')
          }
        }
      },
      scales: {
        x: { grid: { display: false }, ticks: { font: { size: 11 } } },
        y: {
          grid: { color: '#f1f5f9' },
          ticks: {
            font: { size: 10 },
            callback: v => 'Rp ' + (v >= 1e6 ? (v/1e6).toFixed(1)+'Jt' : v.toLocaleString('id-ID'))
          }
        }
      }
    }
  });
}

// ── DONUT CHART INTERAKTIF: Komposisi Penerimaan ──────────────────────────
console.log('🍩 Initializing donut chart...');

// Color palette sesuai spesifikasi
const CHART_COLORS = ['#1D9E75', '#378ADD', '#7F77DD', '#EF9F27', '#D4537E', '#888780', '#85B7EB', '#C0DD97'];

// Format Rupiah Indonesia
function formatRupiah(value) {
  if (value >= 1_000_000_000_000) return 'Rp ' + (value / 1_000_000_000_000).toFixed(2) + ' T';
  if (value >= 1_000_000_000) return 'Rp ' + (value / 1_000_000_000).toFixed(2) + ' M';
  if (value >= 1_000_000) return 'Rp ' + (value / 1_000_000).toFixed(2) + ' Jt';
  return 'Rp ' + value.toLocaleString('id-ID');
}

function escapeHtml(value) {
  return String(value ?? '-')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

// Check Canvas element
const canvasEl = document.getElementById('chartPenerimaan');
console.log('🔍 Canvas element:', {
  exists: !!canvasEl,
  width: canvasEl?.width || 0,
  height: canvasEl?.height || 0,
  offsetWidth: canvasEl?.offsetWidth || 0,
  offsetHeight: canvasEl?.offsetHeight || 0,
  parentHeight: canvasEl?.parentElement?.offsetHeight || 0
});

// Initialize chart
const ctxPen = canvasEl?.getContext('2d');
let chartInstance = null;
let selectedIndex = null;

console.log('🔍 Chart context:', {
  hasContext: !!ctxPen,
  hasData: !!(penerimaanData && penerimaanData.length > 0),
  dataLength: penerimaanData?.length || 0,
  chartJsAvailable: typeof Chart !== 'undefined'
});

// Check if Chart.js is available
if (typeof Chart === 'undefined') {
  console.error('❌ Chart.js library not loaded!');
  const loadingIndicator = document.getElementById('chartLoadingIndicator');
  if (loadingIndicator) {
    loadingIndicator.innerHTML = `
      <div style="color:#dc2626;text-align:center">
        <div style="font-size:32px;margin-bottom:8px">⚠️</div>
        <div style="font-weight:600;margin-bottom:4px">Chart.js Not Loaded</div>
        <div style="font-size:10px;color:#8492a6">CDN script failed to load</div>
      </div>
    `;
  }
} else if (ctxPen && penerimaanData && penerimaanData.length > 0) {
  console.log('✅ Creating donut chart with data:', penerimaanData);

  try {
    // Calculate total
    const totalPenerimaan = penerimaanData.reduce((sum, d) => sum + parseFloat(d.total), 0);
    console.log('Total penerimaan:', totalPenerimaan);

    // Prepare data dengan persentase
    const chartData = penerimaanData
    .map((d) => ({
      label: d.nama_kriteria,
      value: parseFloat(d.total),
    }))
    .sort((a, b) => b.value - a.value)
    .map((d, i) => ({
      ...d,
      pct: totalPenerimaan > 0 ? ((d.value / totalPenerimaan) * 100).toFixed(1) : '0.0',
      color: CHART_COLORS[i % CHART_COLORS.length]
    }));
    console.log('Chart data prepared:', chartData.length, 'items');

    // Center Label Plugin
    const centerLabelPlugin = {
      id: 'centerLabel',
      beforeDraw(chart) {
        const ctx = chart.ctx;
        const chartArea = chart.chartArea;
        if (!chartArea) return;

        const width = chartArea.width;
        const height = chartArea.height;
        ctx.save();

        const centerX = width / 2;
        const centerY = height / 2;

        // Data yang akan ditampilkan di center
        let displayText = '';
        let displayValue = '';
        let displayPct = '';

        if (selectedIndex !== null && chartData[selectedIndex]) {
          const item = chartData[selectedIndex];
          displayText = item.label;
          displayValue = formatRupiah(item.value);
          displayPct = item.pct + '%';
        } else {
          // Default: tampilkan total
          displayText = 'Total';
          displayValue = formatRupiah(totalPenerimaan);
          displayPct = '100%';
        }

        // Draw text di center
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        // Persentase (besar)
        ctx.font = 'bold 32px Sora, sans-serif';
        ctx.fillStyle = '#1a2340';
        ctx.fillText(displayPct, centerX, centerY - 20);

        // Label komoditas
        ctx.font = '600 13px Plus Jakarta Sans, sans-serif';
        ctx.fillStyle = '#8492a6';
        const maxWidth = 120;
        const truncatedText = displayText.length > 18 ? displayText.substring(0, 18) + '...' : displayText;
        ctx.fillText(truncatedText, centerX, centerY + 8);

        // Nilai Rupiah
        ctx.font = '500 11px Plus Jakarta Sans, sans-serif';
        ctx.fillStyle = '#8492a6';
        ctx.fillText(displayValue, centerX, centerY + 26);

        ctx.restore();
      }
    };

    // Create chart
    chartInstance = new Chart(ctxPen, {
    type: 'doughnut',
    data: {
      labels: chartData.map(d => d.label),
      datasets: [{
        data: chartData.map(d => d.value),
        backgroundColor: chartData.map(d => d.color),
        borderWidth: 3,
        borderColor: '#fff',
        hoverBorderWidth: 4,
        hoverBorderColor: '#f8fafc',
        hoverOffset: 12, // Slice keluar saat hover/klik
        offset: chartData.map((_, i) => selectedIndex === i ? 12 : 0)
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '62%', // Donut hole size
      plugins: {
        legend: { display: false }, // Pakai custom legend
        tooltip: {
          enabled: true,
          backgroundColor: 'rgba(0,0,0,0.85)',
          padding: 14,
          bodyFont: { size: 13, family: 'Plus Jakarta Sans' },
          titleFont: { size: 14, family: 'Plus Jakarta Sans', weight: '600' },
          callbacks: {
            title: (ctx) => ctx[0].label,
            label: (ctx) => {
              const pct = ((ctx.raw / totalPenerimaan) * 100).toFixed(1);
              return [
                ' Nilai: ' + formatRupiah(ctx.raw),
                ' Persentase: ' + pct + '%'
              ];
            }
          }
        }
      },
      onClick: (event, activeElements) => {
        if (activeElements.length > 0) {
          const index = activeElements[0].index;
          selectedIndex = selectedIndex === index ? null : index; // Toggle selection

          // Update offset untuk highlight
          chartInstance.data.datasets[0].offset = chartData.map((_, i) => selectedIndex === i ? 12 : 0);
          chartInstance.update();

          // Update custom legend
          updateLegendSelection(index);
        }
      },
      animation: {
        animateRotate: true,
        animateScale: true,
        duration: 600,
        easing: 'easeInOutQuart'
      }
    },
    plugins: [centerLabelPlugin]
    });

    // Generate Custom Legend
    function generateCustomLegend() {
      const legendContainer = document.getElementById('customLegend');
      if (!legendContainer) return;

      let html = '<div class="commodity-legend-list">';

      chartData.forEach((item, index) => {
        const safeLabel = escapeHtml(item.label);
        const safeValue = escapeHtml(formatRupiah(item.value));
        html += `
          <div class="legend-item" data-index="${index}" style="--item-color:${item.color}"
               onclick="handleLegendClick(${index})">
            <div class="legend-marker"></div>
            <div class="legend-main">
              <div class="legend-name" title="${safeLabel}">${safeLabel}</div>
              <div class="legend-value">${safeValue}</div>
            </div>
            <div class="legend-percent">${item.pct}%</div>
          </div>
        `;
      });

      html += '</div>';
      legendContainer.innerHTML = html;
    }

    // Handle legend click
    window.handleLegendClick = function(index) {
      selectedIndex = selectedIndex === index ? null : index;

      // Update chart
      chartInstance.data.datasets[0].offset = chartData.map((_, i) => selectedIndex === i ? 12 : 0);
      chartInstance.update();

      // Update legend selection
      updateLegendSelection(index);
    };

    // Update legend selection visual
    function updateLegendSelection(index) {
      const items = document.querySelectorAll('.legend-item');
      items.forEach((item, i) => {
        if (i === selectedIndex) {
          item.classList.add('active');
        } else {
          item.classList.remove('active');
        }
      });
    }

    // Generate legend on load
    generateCustomLegend();

    // Hide loading indicator
    const loadingIndicator = document.getElementById('chartLoadingIndicator');
    if (loadingIndicator) {
      loadingIndicator.style.display = 'none';
    }

    console.log('✅ Donut chart created successfully with', chartData.length, 'items');

  } catch (error) {
    // Catch any error during chart creation
    console.error('❌ Error creating chart:', error);
    console.error('Error stack:', error.stack);

    // Update loading indicator to show error
    const loadingIndicator = document.getElementById('chartLoadingIndicator');
    if (loadingIndicator) {
      loadingIndicator.innerHTML = `
        <div style="color:#dc2626;text-align:center">
          <div style="font-size:32px;margin-bottom:8px">⚠️</div>
          <div style="font-weight:600;margin-bottom:4px">Chart Creation Error</div>
          <div style="font-size:10px;color:#8492a6">${error.message || 'Unknown error'}</div>
        </div>
      `;
    }

    // Show error in legend area
    const legendContainer = document.getElementById('customLegend');
    if (legendContainer) {
      legendContainer.innerHTML = `<div style="color:#dc2626;font-size:12px;padding:20px;text-align:center;background:#fef2f2;border-radius:8px;border:1px solid #fecaca">⚠️ Error: ${error.message}</div>`;
    }
  }

} else {
  console.error('❌ Chart initialization failed:', {
    hasCanvas: !!canvasEl,
    hasContext: !!ctxPen,
    hasData: penerimaanData && penerimaanData.length > 0,
    dataLength: penerimaanData?.length || 0
  });

  // Update loading indicator to show error
  const loadingIndicator = document.getElementById('chartLoadingIndicator');
  if (loadingIndicator) {
    loadingIndicator.innerHTML = `
      <div style="color:#dc2626;text-align:center">
        <div style="font-size:32px;margin-bottom:8px">⚠️</div>
        <div style="font-weight:600;margin-bottom:4px">Chart Error</div>
        <div style="font-size:10px;color:#8492a6">
          ${!canvasEl ? 'Canvas not found' :
            !ctxPen ? 'Context unavailable' :
            !penerimaanData ? 'No data' :
            'Check console for details'}
        </div>
      </div>
    `;
  }

  // Show error in legend area
  const legendContainer = document.getElementById('customLegend');
  if (legendContainer) {
    legendContainer.innerHTML = '<div style="color:#dc2626;font-size:12px;padding:20px;text-align:center;background:#fef2f2;border-radius:8px;border:1px solid #fecaca">⚠️ Tidak ada data untuk ditampilkan</div>';
  }
}

}); // End DOMContentLoaded

console.log('📊 Chart script fully loaded');
</script>
@endpush

@endsection
