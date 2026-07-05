@extends('layouts/app')
@section('content')

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Sora:wght@600;700&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<style>
/* ── CSS Variables ── */
*,*::before,*::after{box-sizing:border-box}
:root{
  --bg:#f5f6fa;
  --card:#fff;
  --border:#e9ecf3;
  --primary:#1a2340;
  --muted:#8492a6;
  --accent:#2563eb;
  --green:#16a34a;
  --green-bg:#f0fdf4;
  --yellow:#d97706;
  --yellow-bg:#fffbeb;
  --red:#dc2626;
  --red-bg:#fef2f2;
  --teal:#0d9488;
  --teal-bg:#f0fdfa;
  --r:12px;
  --sh:0 1px 3px rgba(0,0,0,.06),0 4px 12px rgba(0,0,0,.05);
}

/* ── CONTENT ── */
.rk-wrap{
  font-family:'Plus Jakarta Sans',sans-serif;
  background:var(--bg);
  padding:24px 28px;
  min-height:100vh;
  font-size:13.5px;
  color:var(--primary);
}

/* ── FILTER BAR ── */
.filter-bar{display:flex;align-items:center;gap:8px;margin-bottom:20px;flex-wrap:wrap}
.filter-pill{display:flex;align-items:center;gap:5px;padding:7px 13px;border-radius:8px;border:1px solid var(--border);background:#fff;font-size:12px;color:var(--muted);font-weight:500;cursor:pointer;transition:.15s;white-space:nowrap}
.filter-pill:hover{border-color:var(--accent);color:var(--accent)}
.filter-pill svg{width:13px;height:13px;flex-shrink:0}
.filter-pill select{border:none;background:transparent;outline:none;font-size:12px;font-family:inherit;color:var(--primary);cursor:pointer;max-width:130px}
.filter-spacer{flex:1}
.export-btn{display:flex;align-items:center;gap:5px;padding:7px 14px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;transition:.15s;border:none}
.btn-excel{background:#16a34a;color:#fff}.btn-excel:hover{background:#15803d}
.btn-pdf{background:#dc2626;color:#fff}.btn-pdf:hover{background:#b91c1c}
.btn-icon{width:13px;height:13px;margin-right:2px}

/* ── CARDS ROW ── */
.cards-row{display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:22px}
@media(max-width:1280px){.cards-row{grid-template-columns:repeat(3,1fr)}}
@media(max-width:768px){.cards-row{grid-template-columns:1fr 1fr}}

/* ── SCORE CARD ── */
.score-card{background:#fff;border:1px solid var(--border);border-radius:var(--r);padding:18px;box-shadow:var(--sh);transition:transform .2s,box-shadow .2s,border-color .2s,ring-color .2s;cursor:pointer;animation:fadeUp .4s ease both;position:relative}
.score-card:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,0,0,.09)}
.score-card:nth-child(1){animation-delay:.05s}.score-card:nth-child(2){animation-delay:.1s}
.score-card:nth-child(3){animation-delay:.15s}.score-card:nth-child(4){animation-delay:.2s}
.score-card:nth-child(5){animation-delay:.25s}
.score-card.card-active{border-color:var(--accent);box-shadow:0 0 0 3px rgba(37,99,235,.12),0 6px 20px rgba(0,0,0,.09);transform:translateY(-3px)}
.score-card.card-active::after{content:'✓ Dipilih';position:absolute;top:8px;right:8px;font-size:9.5px;font-weight:700;padding:2px 7px;border-radius:20px;background:var(--accent);color:#fff}
.score-formula{display:flex;align-items:center;gap:9px;margin:-4px 0 18px;padding:9px 14px;background:#f8fafc;border:1px solid var(--border);border-radius:9px;font-size:12px;color:var(--muted);line-height:1.45}
.score-formula svg{width:15px;height:15px;flex-shrink:0;color:#0f766e}
.score-formula strong{color:var(--primary);font-weight:700}
.active-filter-bar{display:flex;align-items:center;gap:8px;margin-bottom:10px;padding:8px 14px;background:#eff4ff;border:1px solid #bfdbfe;border-radius:9px;font-size:12px;color:var(--accent);font-weight:600}
.active-filter-bar .clear-filter{margin-left:auto;cursor:pointer;font-size:11px;color:var(--accent);text-decoration:underline;white-space:nowrap}
.active-filter-bar svg{width:14px;height:14px;flex-shrink:0}
.sc-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.sc-title{font-size:11.5px;font-weight:700;color:var(--primary)}
.sc-badge{font-size:10px;font-weight:600;padding:3px 8px;border-radius:20px}
.badge-recap{background:#eff4ff;color:#2563eb}
.badge-verif{background:#f0fdf4;color:#16a34a}
.badge-pajak{background:#fdf4ff;color:#9333ea}
.badge-akun{background:#fffbeb;color:#d97706}
.badge-bayar{background:#f0fdfa;color:#0d9488}

.sc-donut{position:relative;width:100px;height:100px;margin:0 auto 14px}
.sc-donut-center{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center}
.sc-score-val{font-family:'Sora',sans-serif;font-size:19px;font-weight:700;color:var(--primary);line-height:1}
.sc-score-lbl{font-size:9.5px;color:var(--muted);margin-top:2px}

.sc-stats{display:flex;flex-direction:column;gap:7px}
.sc-stat-row{display:flex;align-items:center;justify-content:space-between}
.sc-stat-left{display:flex;align-items:center;gap:6px}
.sc-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0}
.sc-stat-name{font-size:11px;color:var(--muted)}
.sc-stat-val{font-size:11.5px;font-weight:700;color:var(--primary)}

/* ── SECTION TOOLBAR ── */
.section-toolbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px}
.view-toggle{display:flex;gap:2px;background:var(--bg);border:1px solid var(--border);border-radius:9px;padding:3px}
.vt-btn{display:flex;align-items:center;gap:5px;padding:6px 13px;border-radius:7px;font-size:12px;font-weight:500;color:var(--muted);cursor:pointer;transition:.15s;border:none;background:transparent}
.vt-btn.active{background:#fff;color:var(--primary);font-weight:600;box-shadow:0 1px 4px rgba(0,0,0,.1)}
.vt-btn svg{width:14px;height:14px}

/* ── TABLE WRAPPER ── */
.table-wrapper{background:#fff;border:1px solid var(--border);border-radius:var(--r);box-shadow:var(--sh);overflow:hidden}
.status-tabs{display:flex;gap:0;border-bottom:1px solid var(--border)}
.stab{padding:10px 18px;font-size:12.5px;font-weight:500;color:var(--muted);cursor:pointer;border-bottom:2px solid transparent;transition:.15s;display:flex;align-items:center;gap:6px}
.stab:hover{color:var(--primary)}
.stab.active{color:var(--accent);border-color:var(--accent);font-weight:600}
.stab-count{font-size:10.5px;font-weight:700;padding:2px 7px;border-radius:20px}
.count-green{background:var(--green-bg);color:var(--green)}
.count-yellow{background:var(--yellow-bg);color:var(--yellow)}
.count-red{background:var(--red-bg);color:var(--red)}

.table-top{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-bottom:1px solid var(--border)}
.table-search{display:flex;align-items:center;gap:6px;background:var(--bg);border:1px solid var(--border);border-radius:7px;padding:6px 10px;font-size:12px;color:var(--muted);width:200px}
.table-search svg{width:12px;height:12px;flex-shrink:0}
.table-search input{border:none;background:transparent;outline:none;font-size:12px;color:var(--primary);font-family:inherit;width:100%}
.tbl-actions{display:flex;gap:6px;align-items:center}
.tbl-btn{display:flex;align-items:center;gap:4px;padding:5px 11px;border:1px solid var(--border);border-radius:7px;background:#fff;font-size:11.5px;color:var(--muted);font-weight:500;cursor:pointer}
.tbl-btn svg{width:12px;height:12px}
.rows-ctrl{display:flex;align-items:center;gap:6px;font-size:12px;color:var(--muted)}
.rows-ctrl select{border:1px solid var(--border);border-radius:6px;padding:4px 8px;font-size:12px;font-family:inherit;color:var(--primary);background:#fff;outline:none;cursor:pointer}

/* ── TABLE ── */
table{width:100%;border-collapse:collapse}
thead th{font-size:10.5px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:var(--muted);padding:11px 12px;text-align:center;background:#fafbfd;border-bottom:1px solid var(--border);white-space:nowrap;cursor:pointer;user-select:none}
thead th:hover{color:var(--primary)}
thead th.sort-asc::after{content:' ↑'}
thead th.sort-desc::after{content:' ↓'}
tbody tr{transition:background .1s;border-bottom:1px solid var(--border)}
tbody tr:last-child{border-bottom:none}
tbody tr:hover{background:#fafbfd}
tbody td{padding:10px 12px;font-size:12px;vertical-align:middle;text-align:center}
thead th:nth-child(2),
tbody td.doc-cell{text-align:left}

/* No. Dokumen cell */
.doc-no{font-weight:700;color:var(--primary);font-size:11.5px;line-height:1.2}
.doc-title{font-size:11px;color:var(--muted);margin-top:4px;max-width:280px;white-space:normal;overflow:visible;text-overflow:clip;line-height:1.35;cursor:default}

/* Bagian tag */
.bagian-tag{display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:700}

/* Vendor */
.vendor-cell{font-size:11.5px;color:var(--primary);max-width:180px;white-space:normal;overflow:visible;text-overflow:clip;line-height:1.35;display:inline-block}

/* Tim */
.team-name{font-size:11.5px;color:var(--primary);font-weight:500}

/* Amount */
.amount{font-weight:600;font-size:11.5px;text-align:center;white-space:nowrap}

/* Date */
.date-cell{font-size:11.5px;color:var(--muted);white-space:nowrap}
.date-cell.highlight{color:var(--primary);font-weight:500}

/* Status pills */
.pill{display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:600;white-space:nowrap}
.pill-green{background:var(--green-bg);color:var(--green)}
.pill-yellow{background:var(--yellow-bg);color:var(--yellow)}
.pill-red{background:var(--red-bg);color:var(--red)}
.pill::before{content:'';width:5px;height:5px;border-radius:50%;background:currentColor;display:inline-block}

/* Hari badge */
.hari-badge{display:inline-flex;align-items:center;font-size:11.5px;font-weight:700;padding:3px 8px;border-radius:6px;white-space:nowrap}
.hari-ok{background:var(--green-bg);color:var(--green)}
.hari-warn{background:var(--yellow-bg);color:var(--yellow)}
.hari-late{background:var(--red-bg);color:var(--red)}
.hari-done{background:#f1f5f9;color:#64748b}

/* ── CARD VIEW ── */
.card-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;padding:16px}
@media(max-width:1024px){.card-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:640px){.card-grid{grid-template-columns:1fr}}
.doc-card{background:#fff;border:1px solid var(--border);border-radius:10px;padding:16px;box-shadow:var(--sh);transition:transform .2s;border-top:3px solid transparent}
.doc-card:hover{transform:translateY(-2px)}
.doc-card.aman-card{border-top-color:var(--green)}
.doc-card.warn-card{border-top-color:var(--yellow)}
.doc-card.late-card{border-top-color:var(--red)}
.dc-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:8px}
.dc-no{font-size:11px;font-weight:700;color:var(--muted);margin-bottom:2px}
.dc-title{font-size:12.5px;font-weight:600;color:var(--primary);line-height:1.3}
.dc-meta{display:flex;flex-direction:column;gap:5px;margin-top:10px;border-top:1px solid var(--border);padding-top:10px}
.dc-row{display:flex;align-items:center;justify-content:space-between}
.dc-key{font-size:11px;color:var(--muted)}
.dc-val{font-size:11.5px;font-weight:600;color:var(--primary);text-align:right;max-width:130px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}

/* ── PAGINATION ── */
.pagination{display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-top:1px solid var(--border);background:#fafbfd}
.pag-info{font-size:12px;color:var(--muted)}
.pag-controls{display:flex;gap:4px}
.pag-btn{width:30px;height:30px;border-radius:6px;border:1px solid var(--border);background:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:500;color:var(--muted);cursor:pointer;transition:.15s}
.pag-btn:hover,.pag-btn.active{background:var(--accent);color:#fff;border-color:var(--accent)}
.pag-btn svg{width:12px;height:12px}

/* ── TOAST ── */
.toast-rk{position:fixed;bottom:24px;right:24px;display:flex;align-items:center;gap:10px;padding:12px 18px;background:#1a2340;color:#fff;border-radius:10px;font-size:13px;font-weight:500;box-shadow:0 8px 24px rgba(0,0,0,.2);z-index:9999;transform:translateY(80px);opacity:0;transition:all .3s ease}
.toast-rk.show{transform:translateY(0);opacity:1}
.toast-icon-rk{width:20px;height:20px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:#16a34a}
.toast-icon-rk svg{width:11px;height:11px;color:#fff}

@keyframes fadeUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}

/* ── Empty state ── */
.empty-state{text-align:center;padding:48px 24px;color:var(--muted)}
.empty-state svg{width:48px;height:48px;margin:0 auto 12px;display:block;opacity:.3}
.empty-state p{font-size:13px}
</style>

<div class="rk-wrap">
  @php
    $isRoleScoped = $isRoleScoped ?? false;
    $roleCode = $roleCode ?? null;
    $roleScopedLabel = $roleScopedLabel ?? null;
    $rekapanFilterUrl = $rekapanFilterUrl ?? route('rekapan-keterlambatan.index');
    $rekapanExportUrl = $rekapanExportUrl ?? null;
  @endphp

  {{-- FILTER BAR --}}
  <div class="filter-bar">
    {{-- Bulan Filter --}}
    <div class="filter-pill">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      <select id="filterBulan" onchange="applyFilter()">
        <option value="">Semua Bulan</option>
        @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $bln)
          <option value="{{ $i+1 }}" {{ $filterBulan == ($i+1) ? 'selected' : '' }}>{{ $bln }}</option>
        @endforeach
      </select>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:10px;height:10px"><polyline points="6 9 12 15 18 9"/></svg>
    </div>

    {{-- Bagian Filter --}}
    <div class="filter-pill">
      <select id="filterBagian" onchange="applyFilter()">
        <option value="">Semua Bagian</option>
        @foreach($bagianList as $b)
          <option value="{{ $b }}" {{ $filterBagian == $b ? 'selected' : '' }}>{{ $b }}</option>
        @endforeach
      </select>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:10px;height:10px"><polyline points="6 9 12 15 18 9"/></svg>
    </div>

    {{-- Tahun Filter --}}
    <div class="filter-pill">
      <select id="filterTahun" onchange="applyFilter()">
        @foreach($tahunList as $t)
          <option value="{{ $t }}" {{ $filterTahun == $t ? 'selected' : '' }}>{{ $t }}</option>
        @endforeach
      </select>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:10px;height:10px"><polyline points="6 9 12 15 18 9"/></svg>
    </div>

    <div class="filter-spacer"></div>

    <button class="export-btn btn-excel" onclick="exportExcel()">
      <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/></svg>
      Export Excel
    </button>
    <button class="export-btn btn-pdf" onclick="exportPdf()">
      <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>
      Export PDF
    </button>
  </div>

  {{-- SCORE CARDS --}}
  <div class="cards-row">

    {{-- Keseluruhan --}}
    @unless($isRoleScoped)
    <div class="score-card card-active" data-team="" onclick="filterByTeam(this,'','Semua Tim')">
      <div class="sc-header">
        <div class="sc-title">Rekapan Semua Tim</div>
        <span class="sc-badge badge-recap">{{ $keseluruhan['label'] }}</span>
      </div>
      <div class="sc-donut">
        <canvas id="donut0"></canvas>
        <div class="sc-donut-center">
          <div class="sc-score-val">{{ $keseluruhan['score'] }}</div>
          <div class="sc-score-lbl">Skor</div>
        </div>
      </div>
      <div class="sc-stats">
        <div class="sc-stat-row"><div class="sc-stat-left"><div class="sc-dot" style="background:#16a34a"></div><span class="sc-stat-name">Tepat Waktu</span></div><span class="sc-stat-val" style="color:#16a34a">{{ $keseluruhan['aman'] }}</span></div>
        <div class="sc-stat-row"><div class="sc-stat-left"><div class="sc-dot" style="background:#d97706"></div><span class="sc-stat-name">Peringatan</span></div><span class="sc-stat-val" style="color:#d97706">{{ $keseluruhan['warn'] }}</span></div>
        <div class="sc-stat-row"><div class="sc-stat-left"><div class="sc-dot" style="background:#dc2626"></div><span class="sc-stat-name">Terlambat</span></div><span class="sc-stat-val" style="color:#dc2626">{{ $keseluruhan['late'] }}</span></div>
      </div>
    </div>
    @endunless

    {{-- Per Tim --}}
    @php $donutIdx = $isRoleScoped ? 0 : 1; $badgeClasses = ['team_verifikasi'=>'badge-verif','perpajakan'=>'badge-pajak','akutansi'=>'badge-akun','pembayaran'=>'badge-bayar']; @endphp
    @foreach($teamScores as $code => $ts)
    <div class="score-card {{ $isRoleScoped ? 'card-active' : '' }}" data-team="{{ $code }}" onclick="filterByTeam(this,'{{ $code }}','{{ $ts['label'] }}')">
      <div class="sc-header">
        <div class="sc-title">{{ $ts['label'] }}</div>
        <span class="sc-badge {{ $badgeClasses[$code] ?? 'badge-recap' }}">{{ $ts['score'] }}</span>
      </div>
      <div class="sc-donut">
        <canvas id="donut{{ $donutIdx }}"></canvas>
        <div class="sc-donut-center">
          <div class="sc-score-val">{{ $ts['score'] }}</div>
          <div class="sc-score-lbl">Skor</div>
        </div>
      </div>
      <div class="sc-stats">
        <div class="sc-stat-row"><div class="sc-stat-left"><div class="sc-dot" style="background:#16a34a"></div><span class="sc-stat-name">Tepat Waktu</span></div><span class="sc-stat-val" style="color:#16a34a">{{ $ts['aman'] }}</span></div>
        <div class="sc-stat-row"><div class="sc-stat-left"><div class="sc-dot" style="background:#d97706"></div><span class="sc-stat-name">Peringatan</span></div><span class="sc-stat-val" style="color:#d97706">{{ $ts['warn'] }}</span></div>
        <div class="sc-stat-row"><div class="sc-stat-left"><div class="sc-dot" style="background:#dc2626"></div><span class="sc-stat-name">Terlambat</span></div><span class="sc-stat-val" style="color:#dc2626">{{ $ts['late'] }}</span></div>
      </div>
    </div>
    @php $donutIdx++; @endphp
    @endforeach

  </div>

  {{-- RUMUS SKOR --}}
  <div class="score-formula">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
    <span><strong>Rumus Skor:</strong> 100 − ((Terlambat × 2 + Peringatan × 0,5) ÷ Total Dokumen) × 100</span>
  </div>

  {{-- SECTION TOOLBAR --}}
  <div class="section-toolbar">
    <div class="view-toggle">
      <button class="vt-btn active" id="btnTable" onclick="switchView('table')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        Tabel
      </button>
      <button class="vt-btn" id="btnCard" onclick="switchView('card')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        Card
      </button>
    </div>
  </div>

  {{-- ACTIVE TEAM FILTER BAR --}}
  <div class="active-filter-bar" id="activeFilterBar" style="display:none">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
    <span>Menampilkan keterlambatan: <strong id="activeFilterLabel"></strong></span>
    @unless($isRoleScoped)
    <span class="clear-filter" onclick="filterByTeam(document.querySelector('.score-card'),'','Semua Tim')">✕ Tampilkan semua tim</span>
    @endunless
  </div>

  {{-- TABLE WRAPPER --}}
  <div class="table-wrapper">
    {{-- STATUS TABS --}}
    <div class="status-tabs">
      <div class="stab active" onclick="switchTab(this,'aman')">
        Tepat Waktu <span class="stab-count count-green" id="cnt-aman">{{ $countAman }}</span>
      </div>
      <div class="stab" onclick="switchTab(this,'warn')">
        Peringatan <span class="stab-count count-yellow" id="cnt-warn">{{ $countWarn }}</span>
      </div>
      <div class="stab" onclick="switchTab(this,'late')">
        Terlambat <span class="stab-count count-red" id="cnt-late">{{ $countLate }}</span>
      </div>
    </div>

    {{-- TABLE TOP --}}
    <div class="table-top">
      <div class="table-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" placeholder="Cari nomor / judul dokumen…" id="searchInput" oninput="renderTable()">
      </div>
      <div class="tbl-actions">
        <div class="tbl-btn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
          Filter
        </div>
        <div class="tbl-btn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="21" y1="10" x2="3" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="21" y1="18" x2="3" y2="18"/></svg>
          Kelompokkan
        </div>
        <div class="rows-ctrl">
          <span>Baris</span>
          <select onchange="rowsPerPage=+this.value;currentPage=1;renderTable()">
            <option>10</option><option selected>25</option><option>50</option>
          </select>
        </div>
      </div>
    </div>

    {{-- TABLE VIEW --}}
    <div id="tableView">
      <div style="overflow-x:auto">
        <table id="mainTable">
          <thead>
            <tr>
              <th onclick="sortTable('nomor_spp')">No. Dokumen</th>
              <th onclick="sortTable('bagian')">Bagian</th>
              <th onclick="sortTable('vendor')">Dibayar Kepada/Vendor</th>
              <th onclick="sortTable('nilai_rupiah')">Nilai (Rp)</th>
              <th onclick="sortTable('tim')">Tim</th>
              <th onclick="sortTable('tanggal_masuk')">Tgl Masuk</th>
              <th onclick="sortTable('tanggal_selesai')">Tgl Selesai</th>
              <th onclick="sortTable('elapsed_seconds')">Waktu Pengerjaan</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody id="tableBody"></tbody>
        </table>
      </div>
    </div>

    {{-- CARD VIEW --}}
    <div id="cardView" style="display:none">
      <div class="card-grid" id="cardGrid"></div>
    </div>

    {{-- PAGINATION --}}
    <div class="pagination">
      <div class="pag-info" id="pagInfo">Memuat…</div>
      <div class="pag-controls" id="pagControls"></div>
    </div>
  </div>

</div>

{{-- TOAST --}}
<div class="toast-rk" id="toast">
  <div class="toast-icon-rk"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
  <span id="toastMsg"></span>
</div>

<script>
/* ─── DATA FROM PHP ─── */
const DOCS_RAW = @json($classified->values()->all());

const BAGIAN_COLOR = @json($bagianColors);
const IS_ROLE_SCOPED = @json($isRoleScoped);
const ROLE_SCOPED_CODE = @json($roleCode);
const REKAPAN_FILTER_URL = @json($rekapanFilterUrl);
const REKAPAN_EXPORT_URL = @json($rekapanExportUrl);
const WARNING_AFTER_SECONDS = @json(($roleCode ?? '') === 'pembayaran' ? 168 * 3600 : 24 * 3600);
const LATE_AFTER_SECONDS = @json(($roleCode ?? '') === 'pembayaran' ? 504 * 3600 : (($isRoleScoped ?? false) ? 72 * 3600 : 48 * 3600));

/* ─── HELPERS ─── */
function fmtNum(n){ return new Intl.NumberFormat('id-ID').format(n||0); }
function esc(str){
  return String(str ?? '-')
    .replace(/&/g,'&amp;')
    .replace(/</g,'&lt;')
    .replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;')
    .replace(/'/g,'&#039;');
}
function formatDuration(totalSeconds) {
  totalSeconds = Math.max(0, Math.floor(totalSeconds || 0));
  const d = Math.floor(totalSeconds / 86400);
  const h = Math.floor((totalSeconds % 86400) / 3600);
  const m = Math.floor((totalSeconds % 3600) / 60);
  const s = totalSeconds % 60;
  if (d > 0) return `${d}h ${String(h).padStart(2,'0')}j ${String(m).padStart(2,'0')}m`;
  if (h > 0) return `${h}j ${String(m).padStart(2,'0')}m ${String(s).padStart(2,'0')}d`;
  if (m > 0) return `${m}m ${String(s).padStart(2,'0')}d`;
  return `${s}d`;
}
function statusFromSeconds(totalSeconds){
  const seconds = Math.max(0, totalSeconds || 0);
  if(seconds >= LATE_AFTER_SECONDS) return 'late';
  if(seconds >= WARNING_AFTER_SECONDS) return 'warn';
  return 'aman';
}
function truncate(str,len){ if(!str||str==='-') return '-'; return str.length>len?str.substring(0,len)+'…':str; }

/* ─── STATE ─── */
let currentTab    = 'aman';
let currentView   = 'table';
let rowsPerPage   = 25;
let currentPage   = 1;
let sortKey       = null;
let sortDir       = 1;
let activeTeam    = IS_ROLE_SCOPED ? ROLE_SCOPED_CODE : '';   // '' = semua tim, otherwise tim_code

/* ─── DONUT CHARTS ─── */
const donutDatas = [
  @unless($isRoleScoped)
  [{{ $keseluruhan['aman'] }}, {{ $keseluruhan['warn'] }}, {{ $keseluruhan['late'] }}],
  @endunless
  @foreach($teamScores as $ts)
  [{{ $ts['aman'] }},{{ $ts['warn'] }},{{ $ts['late'] }}],
  @endforeach
];
const donutScores = [
  @unless($isRoleScoped)
  {{ $keseluruhan['score'] }},
  @endunless
  @foreach($teamScores as $ts)
  {{ $ts['score'] }},
  @endforeach
];
donutDatas.forEach((d,i)=>{
  const ctx = document.getElementById('donut'+i)?.getContext('2d');
  if(!ctx) return;
  const total = d.reduce((a,b)=>a+b,0);
  // If all data is zero, show a grey placeholder arc with score 100
  const isBlank = total === 0;
  new Chart(ctx,{
    type:'doughnut',
    data:{
      labels:isBlank ? ['Tidak ada data'] : ['Tepat Waktu','Peringatan','Terlambat'],
      datasets:[{
        data: isBlank ? [1] : d,
        backgroundColor: isBlank ? ['#e2e8f0'] : ['#16a34a','#d97706','#dc2626'],
        borderWidth:0,
        hoverOffset:4
      }]
    },
    options:{
      cutout:'72%',
      plugins:{
        legend:{display:false},
        tooltip:{callbacks:{label:c=> isBlank ? ' Belum ada dokumen' : ` ${c.raw} dokumen`}}
      }
    }
  });
});

/* ─── FILTER REDIRECT ─── */
function applyFilter(){
  const b = document.getElementById('filterBulan').value;
  const g = document.getElementById('filterBagian').value;
  const t = document.getElementById('filterTahun').value;
  let url = REKAPAN_FILTER_URL + '?';
  if(b) url+='bulan='+b+'&';
  if(g) url+='bagian='+g+'&';
  if(t) url+='tahun='+t+'&';
  window.location.href = url;
}

/* ─── TAB SWITCH ─── */
function switchTab(el, tab){
  document.querySelectorAll('.stab').forEach(s=>s.classList.remove('active'));
  el.classList.add('active');
  currentTab = tab;
  currentPage = 1;
  renderTable();
}

/* ─── VIEW SWITCH ─── */
function switchView(v){
  currentView = v;
  document.getElementById('btnTable').classList.toggle('active',v==='table');
  document.getElementById('btnCard').classList.toggle('active',v==='card');
  document.getElementById('tableView').style.display = v==='table'?'block':'none';
  document.getElementById('cardView').style.display  = v==='card'?'block':'none';
  renderTable();
}

/* ─── SORT ─── */
function sortTable(key){
  if(sortKey===key) sortDir*=-1;
  else{ sortKey=key; sortDir=1; }
  document.querySelectorAll('thead th').forEach(th=>{th.classList.remove('sort-asc','sort-desc')});
  if(event?.target) event.target.classList.add(sortDir===1?'sort-asc':'sort-desc');
  renderTable();
}

/* ─── FILTER BY TEAM (card click) ─── */
function filterByTeam(cardEl, teamValue, teamLabel){
  // deactivate all cards
  document.querySelectorAll('.score-card').forEach(c=>c.classList.remove('card-active'));
  cardEl.classList.add('card-active');
  activeTeam = teamValue;

  // show / hide filter bar
  const bar = document.getElementById('activeFilterBar');
  if(teamValue){
    bar.style.display = 'flex';
    document.getElementById('activeFilterLabel').textContent = teamLabel;
  } else {
    bar.style.display = 'none';
  }

  // reset to page 1 & re-render
  currentPage = 1;
  renderTable();

  // smooth scroll to table
  document.querySelector('.section-toolbar')?.scrollIntoView({behavior:'smooth',block:'nearest'});
}

/* ─── GET FILTERED ─── */
function getFiltered(){
  const q = (document.getElementById('searchInput')?.value||'').toLowerCase();
  return DOCS_RAW.filter(d=>{
    if(d.status!==currentTab) return false;
    if(activeTeam && d.tim_code !== activeTeam) return false;
    if(q && !d.nomor_spp?.toLowerCase().includes(q) && !d.uraian_spp?.toLowerCase().includes(q)) return false;
    return true;
  });
}

function getActiveTeamDocs(){
  return DOCS_RAW.filter(d => !activeTeam || d.tim_code === activeTeam);
}

function updateStatusTabCounts(){
  const docs = getActiveTeamDocs();
  const counts = {
    aman: docs.filter(d => d.status === 'aman').length,
    warn: docs.filter(d => d.status === 'warn').length,
    late: docs.filter(d => d.status === 'late').length,
  };

  document.getElementById('cnt-aman').textContent = counts.aman;
  document.getElementById('cnt-warn').textContent = counts.warn;
  document.getElementById('cnt-late').textContent = counts.late;
}

/* ─── HARI LABEL ─── */
function hariLabel(d){
  const start = d.created_at ? new Date(d.created_at).getTime() : null;
  const end = d.processed_at ? new Date(d.processed_at).getTime() : Date.now();
  const secs = start ? Math.floor((end - start) / 1000) : (d.elapsed_seconds || 0);
  const status = statusFromSeconds(secs);
  const cls = d.processed_at ? 'hari-done' : (status === 'aman' ? 'hari-ok' : (status === 'warn' ? 'hari-warn' : 'hari-late'));
  return `<span class="hari-badge ${cls} timer-age" data-created="${esc(d.created_at || '')}" data-processed="${esc(d.processed_at || '')}">${formatDuration(secs)}</span>`;
}

/* ─── STATUS PILL ─── */
function statusPill(s){
  if(s==='aman') return `<span class="pill pill-green">Tepat Waktu</span>`;
  if(s==='warn') return `<span class="pill pill-yellow">Peringatan</span>`;
  return `<span class="pill pill-red">Terlambat</span>`;
}

/* ─── RENDER ─── */
function renderTable(){
  updateStatusTabCounts();
  let filtered = getFiltered();
  if(sortKey){
    filtered.sort((a,b)=>{
      let av=a[sortKey]??'', bv=b[sortKey]??'';
      if(typeof av==='string') return av.localeCompare(bv)*sortDir;
      return (av-bv)*sortDir;
    });
  }
  const total = filtered.length;
  const totalPages = Math.max(1,Math.ceil(total/rowsPerPage));
  if(currentPage>totalPages) currentPage=totalPages;
  const start=(currentPage-1)*rowsPerPage;
  const paged = filtered.slice(start,start+rowsPerPage);

  if(currentView==='table'){
    const tbody = document.getElementById('tableBody');
    if(!paged.length){
      tbody.innerHTML=`<tr><td colspan="9" style="text-align:center;padding:48px;color:#94a3b8">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:40px;height:40px;display:block;margin:0 auto 8px;opacity:.3"><circle cx="12" cy="12" r="10"/><path d="M9 9h.01M15 9h.01M8 14s1.5 2 4 2 4-2 4-2"/></svg>
        Tidak ada data</td></tr>`;
    } else {
      tbody.innerHTML = paged.map(d=>{
        const bColor = BAGIAN_COLOR[d.bagian]||'#94a3b8';
        const selesaiDisplay = d.tanggal_selesai || '<span style="color:#94a3b8">—</span>';
        return `<tr data-doc-id="${d.id}">
          <td class="doc-cell">
            <div class="doc-no">${esc(d.nomor_spp||'-')}</div>
            <div class="doc-title">${esc(d.uraian_spp||'-')}</div>
          </td>
          <td><span class="bagian-tag" style="color:${bColor}">● ${d.bagian||'-'}</span></td>
          <td><span class="vendor-cell">${esc(d.vendor||'-')}</span></td>
          <td class="amount">Rp ${fmtNum(d.nilai_rupiah)}</td>
          <td><span class="team-name">${esc(d.tim||'-')}</span></td>
          <td class="date-cell">${esc(d.tanggal_masuk||'-')}</td>
          <td class="date-cell ${d.tanggal_selesai?'highlight':''}">${selesaiDisplay}</td>
          <td>${hariLabel(d)}</td>
          <td class="status-cell">${statusPill(d.status)}</td>
        </tr>`;
      }).join('');
    }
  } else {
    const grid = document.getElementById('cardGrid');
    if(!paged.length){
      grid.innerHTML=`<div style="grid-column:1/-1;text-align:center;padding:48px;color:#94a3b8">Tidak ada data</div>`;
    } else {
      grid.innerHTML = paged.map(d=>{
        const bColor = BAGIAN_COLOR[d.bagian]||'#94a3b8';
        const selesaiDisplay = d.tanggal_selesai || '—';
        const vendorShort = truncate(d.vendor, 22);
        return `<div class="doc-card ${d.status}-card">
          <div class="dc-header">
            <div>
              <div class="dc-no">${d.nomor_spp||'-'}</div>
              <div class="dc-title" title="${(d.uraian_spp||'-').replace(/"/g,'&quot;')}">${truncate(d.uraian_spp,40)}</div>
            </div>
            ${statusPill(d.status)}
          </div>
          <div class="dc-meta">
            <div class="dc-row"><span class="dc-key">Bagian</span><span class="dc-val" style="color:${bColor}">${d.bagian||'-'}</span></div>
            <div class="dc-row"><span class="dc-key">Vendor</span><span class="dc-val" title="${(d.vendor||'-').replace(/"/g,'&quot;')}">${vendorShort}</span></div>
            <div class="dc-row"><span class="dc-key">Nilai</span><span class="dc-val">Rp ${fmtNum(d.nilai_rupiah)}</span></div>
            <div class="dc-row"><span class="dc-key">Tim</span><span class="dc-val">${d.tim||'-'}</span></div>
            <div class="dc-row"><span class="dc-key">Tgl Masuk</span><span class="dc-val">${d.tanggal_masuk||'-'}</span></div>
            <div class="dc-row"><span class="dc-key">Tgl Selesai</span><span class="dc-val">${selesaiDisplay}</span></div>
            <div class="dc-row"><span class="dc-key">Waktu Pengerjaan</span>${hariLabel(d)}</div>
          </div>
        </div>`;
      }).join('');
    }
  }

  /* pagination info */
  document.getElementById('pagInfo').textContent =
    total===0?'Tidak ada data':`Menampilkan ${start+1}–${Math.min(start+rowsPerPage,total)} dari ${total} entri`;

  /* pagination controls */
  const ctrl = document.getElementById('pagControls');
  ctrl.innerHTML='';
  const addBtn=(label,page,active=false,disabled=false)=>{
    const btn=document.createElement('div');
    btn.className='pag-btn'+(active?' active':'');
    btn.innerHTML=label;
    if(!disabled) btn.onclick=()=>{currentPage=page;renderTable()};
    else btn.style.opacity='.4';
    ctrl.appendChild(btn);
  };
  addBtn('<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>',currentPage-1,false,currentPage===1);
  for(let p=1;p<=totalPages;p++){
    if(p===1||p===totalPages||Math.abs(p-currentPage)<=1) addBtn(p,p,p===currentPage);
    else if(Math.abs(p-currentPage)===2) addBtn('…',p,false,true);
  }
  addBtn('<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>',currentPage+1,false,currentPage===totalPages);
}

/* ─── EXPORT EXCEL ─── */
function exportExcel(){
  const filtered = getFiltered();
  const ws_data=[['No. Dokumen','Uraian SPP','Bagian','Dibayar Kepada/Vendor','Nilai (Rp)','Tim','Tgl Masuk','Tgl Selesai','Waktu Pengerjaan','Status']];
  filtered.forEach(d=>ws_data.push([
    d.nomor_spp, d.uraian_spp, d.bagian, d.vendor,
    d.nilai_rupiah, d.tim, d.tanggal_masuk, d.tanggal_selesai||'-', formatDuration(d.elapsed_seconds || 0),
    d.status==='aman'?'Tepat Waktu':d.status==='warn'?'Peringatan':'Terlambat'
  ]));
  const wb=XLSX.utils.book_new();
  const ws=XLSX.utils.aoa_to_sheet(ws_data);
  ws['!cols']=[{wch:20},{wch:35},{wch:8},{wch:25},{wch:18},{wch:14},{wch:14},{wch:14},{wch:8},{wch:14}];
  XLSX.utils.book_append_sheet(wb,ws,'Rekapan Keterlambatan');
  XLSX.writeFile(wb,'Rekapan_Keterlambatan.xlsx');
  showToast('✓ File Excel berhasil diunduh');
}

/* ─── EXPORT PDF ─── */
function exportPdf(){
  showToast('⟳ Menyiapkan PDF…');
  setTimeout(()=>window.print(),600);
}

/* ─── TOAST ─── */
function showToast(msg){
  const t=document.getElementById('toast');
  document.getElementById('toastMsg').textContent=msg;
  t.classList.add('show');
  setTimeout(()=>t.classList.remove('show'),3000);
}

function refreshLiveDurations(){
  let changed = false;
  const now = Date.now();

  DOCS_RAW.forEach(d => {
    if(!d.created_at) return;
    const end = d.processed_at ? new Date(d.processed_at).getTime() : now;
    const seconds = Math.max(0, Math.floor((end - new Date(d.created_at).getTime()) / 1000));
    const nextStatus = statusFromSeconds(seconds);
    d.elapsed_seconds = seconds;
    d.hari = Math.floor(seconds / 86400);
    if(d.status !== nextStatus){
      d.status = nextStatus;
      changed = true;
    }
  });

  if(changed){
    renderTable();
    return;
  }

  document.querySelectorAll('.timer-age[data-created]').forEach(el => {
    const created = el.dataset.created ? new Date(el.dataset.created).getTime() : null;
    if(!created) return;
    const processed = el.dataset.processed ? new Date(el.dataset.processed).getTime() : null;
    const seconds = Math.max(0, Math.floor(((processed || now) - created) / 1000));
    const status = statusFromSeconds(seconds);
    el.textContent = formatDuration(seconds);
    el.classList.toggle('hari-done', !!processed);
    el.classList.toggle('hari-ok', !processed && status === 'aman');
    el.classList.toggle('hari-warn', !processed && status === 'warn');
    el.classList.toggle('hari-late', !processed && status === 'late');
  });
}

// Init
renderTable();
refreshLiveDurations();
setInterval(refreshLiveDurations, 1000);
</script>

@endsection
