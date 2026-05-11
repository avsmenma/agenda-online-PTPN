@extends('layouts/app')
@section('content')

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Sora:wght@600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js?v={{ time() }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js?v={{ time() }}"></script>

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

  {{-- ── ROW 3: PENERIMAAN per KOMODITAS + Donut ── --}}
  <div class="cb-grid-2" style="margin-bottom:18px">

    {{-- Chart Donut: Komposisi Penerimaan --}}
    <div class="cb-card" style="animation-delay:.36s">
      <div class="cb-card-header">
        <div class="cb-card-title" style="color:var(--green)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0110 10"/>
          </svg>
          Komposisi Penerimaan per Komoditas
        </div>
      </div>
      <div class="cb-card-body" style="display:flex;gap:20px;align-items:center;flex-wrap:wrap">
        <div style="flex:0 0 280px;min-height:280px;position:relative;border:2px dashed #0d9488;display:flex;align-items:center;justify-content:center">
          <canvas id="chartPenerimaan"></canvas>
          <div id="chartDebug" style="position:absolute;top:10px;left:10px;font-size:10px;background:yellow;padding:4px;z-index:999">Chart Loading...</div>
        </div>
        <div style="flex:1;min-width:160px">
          @foreach($penerimaanKategori as $pk)
            @php $pct = $ringkasan['total_penerimaan'] > 0
              ? round($pk->total / $ringkasan['total_penerimaan'] * 100, 1) : 0; @endphp
            <div style="margin-bottom:8px">
              <div style="display:flex;justify-content:space-between;font-size:11.5px;margin-bottom:3px">
                <span style="font-weight:600;color:var(--primary)">{{ $pk->nama_kriteria }}</span>
                <span style="color:var(--muted)">{{ $pct }}%</span>
              </div>
              <div style="background:#f1f5f9;border-radius:4px;height:6px;overflow:hidden">
                <div style="height:6px;border-radius:4px;background:linear-gradient(90deg,#16a34a,#0d9488);width:{{ $pct }}%"></div>
              </div>
              <div style="font-size:10.5px;color:var(--muted);margin-top:1px">
                {{ rupiahFull((float)$pk->total) }}
              </div>
            </div>
          @endforeach
          @if(empty($penerimaanKategori))
            <div class="cb-empty">Belum ada data penerimaan</div>
          @endif
        </div>
      </div>
    </div>

    {{-- Chart Bar: Rencana Pengeluaran per Kategori --}}
    <div class="cb-card" style="animation-delay:.4s">
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
        @php $maxPermintaan = !empty($permintaanKategori) ? max(array_column((array)$permintaanKategori, 'total_rencana')) : 1 @endphp
        @foreach($permintaanKategori as $pk)
          @php $pctP = $maxPermintaan > 0 ? round($pk->total_rencana / $maxPermintaan * 100, 1) : 0; @endphp
          <div class="cb-progress-row">
            <div class="cb-progress-label" style="font-size:11px">{{ Str::limit($pk->nama_kriteria, 30) }}</div>
            <div class="cb-progress-bar-wrap">
              <div class="cb-progress-bar" style="width:{{ $pctP }}%;background:linear-gradient(90deg,#7c3aed,#6d28d9)"></div>
            </div>
            <div class="cb-progress-val">{{ rupiah((float)$pk->total_rencana) }}</div>
          </div>
        @endforeach
        @if(empty($permintaanKategori))
          <div class="cb-empty">Belum ada data rencana pengeluaran</div>
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
const trenData = @json($trenBulanan);
const penerimaanData = @json($penerimaanKategori);

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

// ── CHART PIE PENERIMAAN dengan Leader Lines ──────────────────────────
const ctxPen = document.getElementById('chartPenerimaan')?.getContext('2d');
const debugEl = document.getElementById('chartDebug');

console.log('Chart Initialization:', {
  canvas: !!document.getElementById('chartPenerimaan'),
  context: !!ctxPen,
  dataLength: penerimaanData?.length || 0,
  data: penerimaanData
});

if (ctxPen && penerimaanData.length) {
  // Calculate total for percentage
  const totalPenerimaan = penerimaanData.reduce((sum, d) => sum + parseFloat(d.total), 0);

  if (debugEl) {
    debugEl.textContent = 'Creating chart...';
    debugEl.style.background = 'lightblue';
  }

  new Chart(ctxPen, {
    type: 'pie',
    data: {
      labels: penerimaanData.map(d => d.nama_kriteria),
      datasets: [{
        data: penerimaanData.map(d => parseFloat(d.total)),
        backgroundColor: COLORS_PIE,
        borderWidth: 3,
        borderColor: '#fff',
        hoverBorderWidth: 4,
        hoverBorderColor: '#f8fafc',
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      layout: {
        padding: {
          top: 20,
          right: 80,
          bottom: 20,
          left: 80
        }
      },
      plugins: {
        legend: { display: false },
        tooltip: {
          enabled: true,
          backgroundColor: 'rgba(0,0,0,0.8)',
          padding: 12,
          bodyFont: { size: 12, family: 'Plus Jakarta Sans' },
          callbacks: {
            title: (ctx) => ctx[0].label,
            label: (ctx) => {
              const pct = ((ctx.raw / totalPenerimaan) * 100).toFixed(1);
              return ' ' + pct + '% • Rp ' + ctx.raw.toLocaleString('id-ID');
            }
          }
        },
        datalabels: {
          color: '#1a2340',
          font: {
            size: 11,
            weight: '600',
            family: 'Plus Jakarta Sans'
          },
          formatter: (value, ctx) => {
            const pct = ((value / totalPenerimaan) * 100).toFixed(1);
            const label = ctx.chart.data.labels[ctx.dataIndex];
            // Only show label if percentage > 0.5%
            return pct > 0.5 ? label + '\n' + pct + '%' : '';
          },
          anchor: 'end',
          align: 'end',
          offset: 8,
          clamp: false,
          clip: false,
          textAlign: 'center',
          backgroundColor: 'rgba(255,255,255,0.95)',
          borderColor: (ctx) => COLORS_PIE[ctx.dataIndex] || '#999',
          borderWidth: 2,
          borderRadius: 6,
          padding: 6,
          display: true,
        }
      },
      interaction: {
        mode: 'nearest',
        intersect: true
      },
      animation: {
        animateRotate: true,
        animateScale: true,
        duration: 800,
        easing: 'easeInOutQuart'
      }
    },
    plugins: [{
      // Custom plugin to draw leader lines
      id: 'leaderLines',
      afterDatasetDraw(chart) {
        const ctx = chart.ctx;
        const meta = chart.getDatasetMeta(0);

        meta.data.forEach((element, index) => {
          const model = element;
          const pct = ((chart.data.datasets[0].data[index] / totalPenerimaan) * 100);

          // Only draw lines for segments > 0.5%
          if (pct <= 0.5) return;

          // Get center and outer points
          const centerX = model.x;
          const centerY = model.y;
          const startAngle = model.startAngle;
          const endAngle = model.endAngle;
          const midAngle = startAngle + (endAngle - startAngle) / 2;
          const radius = model.outerRadius;

          // Calculate points for leader line
          const x1 = centerX + Math.cos(midAngle) * (radius - 5);
          const y1 = centerY + Math.sin(midAngle) * (radius - 5);
          const x2 = centerX + Math.cos(midAngle) * (radius + 12);
          const y2 = centerY + Math.sin(midAngle) * (radius + 12);

          // Draw leader line
          ctx.save();
          ctx.strokeStyle = COLORS_PIE[index] || '#999';
          ctx.lineWidth = 2;
          ctx.beginPath();
          ctx.moveTo(x1, y1);
          ctx.lineTo(x2, y2);
          ctx.stroke();
          ctx.restore();
        });
      }
    }]
  });

  // Remove debug element after chart is created
  if (debugEl) {
    setTimeout(() => debugEl.remove(), 1000);
  }
  console.log('Chart created successfully');

} else {
  console.error('Chart cannot be created:', {
    hasCanvas: !!document.getElementById('chartPenerimaan'),
    hasContext: !!ctxPen,
    hasData: penerimaanData?.length > 0
  });
  if (debugEl) {
    debugEl.textContent = 'Chart Error: ' + (!ctxPen ? 'No canvas context' : 'No data');
    debugEl.style.background = 'red';
    debugEl.style.color = 'white';
  }
}
</script>
@endpush

@endsection
