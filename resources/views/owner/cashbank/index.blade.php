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
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:24px">
          @php
            $topKomoditas = collect($penerimaanKategori)->sortByDesc('total')->first();
            $jumlahKomoditas = count($penerimaanKategori);
          @endphp
          <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px">
            <div style="font-size:10px;color:#15803d;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px">Total Penerimaan</div>
            <div style="font-family:'Sora',sans-serif;font-size:16px;font-weight:700;color:#15803d">{{ rupiah((float)$ringkasan['total_penerimaan']) }}</div>
          </div>
          <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:12px">
            <div style="font-size:10px;color:#1e40af;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px">Komoditas Utama</div>
            <div style="font-family:'Sora',sans-serif;font-size:14px;font-weight:700;color:#1e40af;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="{{ $topKomoditas ? $topKomoditas->nama_kriteria : '-' }}">{{ $topKomoditas ? $topKomoditas->nama_kriteria : '-' }}</div>
          </div>
          <div style="background:#fef3c7;border:1px solid #fde047;border-radius:8px;padding:12px">
            <div style="font-size:10px;color:#a16207;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px">Jumlah Komoditas</div>
            <div style="font-family:'Sora',sans-serif;font-size:16px;font-weight:700;color:#a16207">{{ $jumlahKomoditas }} item</div>
          </div>
        </div>

        {{-- Chart + Legend Container --}}
        <div style="display:flex;gap:24px;align-items:center;flex-wrap:wrap;justify-content:center">
          {{-- Donut Chart --}}
          <div style="flex:0 0 280px;height:280px;position:relative;border:2px dashed #0d9488;border-radius:8px;display:flex;align-items:center;justify-content:center;background:#fafbfd">
            <canvas id="chartPenerimaan" aria-label="Komposisi penerimaan per komoditas dalam bentuk donut chart" style="max-width:100%;max-height:100%">
              Grafik menampilkan komposisi penerimaan dari berbagai komoditas
            </canvas>
            <div id="chartLoadingIndicator" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-size:12px;color:#8492a6;text-align:center;font-weight:600">
              <div style="margin-bottom:8px;font-size:24px">📊</div>
              Loading chart...
            </div>
          </div>

          {{-- Custom Legend --}}
          <div id="customLegend" style="flex:1;min-width:200px;max-width:360px">
            <!-- Legend will be generated by JavaScript -->
          </div>
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
    const chartData = penerimaanData.map((d, i) => ({
      label: d.nama_kriteria,
      value: parseFloat(d.total),
      pct: ((parseFloat(d.total) / totalPenerimaan) * 100).toFixed(1),
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

      let html = '<div style="display:grid;grid-template-columns:1fr;gap:8px">';

      chartData.forEach((item, index) => {
        html += `
          <div class="legend-item" data-index="${index}"
               style="display:flex;align-items:center;gap:10px;padding:10px;border-radius:8px;cursor:pointer;transition:all 0.2s;background:#fafbfd;border:2px solid transparent"
               onmouseover="this.style.background='#f1f5f9';this.style.borderColor='${item.color}'"
               onmouseout="if(!this.classList.contains('active')){this.style.background='#fafbfd';this.style.borderColor='transparent'}"
               onclick="handleLegendClick(${index})">
            <div style="width:12px;height:12px;border-radius:3px;background:${item.color};flex-shrink:0"></div>
            <div style="flex:1;min-width:0">
              <div style="font-size:12px;font-weight:600;color:#1a2340;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${item.label}</div>
              <div style="font-size:10px;color:#8492a6;margin-top:2px">${formatRupiah(item.value)}</div>
            </div>
            <div style="font-size:13px;font-weight:700;color:${item.color};font-family:Sora,sans-serif">${item.pct}%</div>
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
          item.style.background = '#eff6ff';
          item.style.borderColor = chartData[i].color;
        } else {
          item.classList.remove('active');
          item.style.background = '#fafbfd';
          item.style.borderColor = 'transparent';
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
