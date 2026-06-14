@extends('layouts/app')
@section('content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
  /* ===== Redesign /reports/analytics — mengikuti "Dashboard Monitoring Dokumen" ===== */
  .analytics-app {
    --bg: #eef2f1; --surface: #fff; --primary: #0b463a; --primary-dark: #07332a;
    --accent: #1f9d6b; --accent-strong: #178055; --accent-soft: #e7f3ed; --accent-line: #b9e0cd;
    --text: #142a23; --muted: #6c817a; --border: #e2e9e6; --row-alt: #f5f8f6;
    --hero: linear-gradient(115deg, #07332a 0%, #0b463a 46%, #1c8a62 118%);
    --font: "Plus Jakarta Sans", system-ui, sans-serif;
    --r: 18px; --r-sm: 11px;
    --shadow: 0 1px 2px rgba(13,40,32,.04), 0 8px 24px -12px rgba(13,40,32,.14);
    --shadow-sm: 0 1px 2px rgba(13,40,32,.05), 0 4px 12px -8px rgba(13,40,32,.12);

    margin: -20px;                 /* bleed melewati padding .content layout */
    background: var(--bg);
    font-family: var(--font);
    color: var(--text);
    -webkit-font-smoothing: antialiased;
    min-height: calc(100vh - 0px);
  }
  .analytics-app * { box-sizing: border-box; }
  .analytics-app .page { max-width: 1480px; margin: 0 auto; padding: 30px 30px 64px; display: flex; flex-direction: column; gap: 26px; }
  .analytics-app .card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); box-shadow: var(--shadow-sm); }

  /* ---------- Hero ---------- */
  .analytics-app .hero {
    position: relative; overflow: hidden; border-radius: var(--r); background: var(--hero);
    display: grid; grid-template-columns: 1fr auto 1fr; align-items: center;
    padding: 46px 40px; color: #fff; box-shadow: 0 18px 44px -20px rgba(7,40,32,.55);
  }
  .analytics-app .hero-deco {
    position: absolute; inset: 0; pointer-events: none;
    background:
      radial-gradient(820px 380px at 88% -40%, rgba(255,255,255,.16), transparent 60%),
      radial-gradient(520px 320px at 6% 130%, rgba(255,255,255,.08), transparent 60%);
  }
  .analytics-app .hero-col { position: relative; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 12px; }
  .analytics-app .hero-eyebrow { display: flex; align-items: center; gap: 8px; font-size: 13.5px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: rgba(255,255,255,.82); }
  .analytics-app .hero-big { font-size: clamp(38px, 5vw, 62px); font-weight: 800; letter-spacing: -.02em; line-height: 1; font-variant-numeric: tabular-nums; text-shadow: 0 2px 12px rgba(0,0,0,.12); }
  .analytics-app .hero-big--rp { font-size: clamp(26px, 3.2vw, 42px); }
  .analytics-app .hero-sub { font-size: 15px; color: rgba(255,255,255,.78); font-weight: 500; }
  .analytics-app .hero-div { width: 1px; align-self: stretch; margin: 8px 36px; background: linear-gradient(transparent, rgba(255,255,255,.28), transparent); }

  /* ---------- Filter bar ---------- */
  /* flex-direction:row WAJIB di-set eksplisit untuk menimpa Bootstrap .card (column). */
  .analytics-app .filterbar { display: flex; flex-direction: row; align-items: flex-end; gap: 30px; padding: 20px 26px; flex-wrap: wrap; }
  .analytics-app .filt-group { display: flex; flex-direction: column; gap: 8px; }
  .analytics-app .filt-label { font-size: 13px; font-weight: 700; color: var(--text); letter-spacing: .01em; }
  .analytics-app .select-wrap { position: relative; }
  .analytics-app .select-wrap::after {
    content: ""; position: absolute; right: 14px; top: 50%; width: 9px; height: 9px;
    border-right: 2px solid var(--muted); border-bottom: 2px solid var(--muted);
    transform: translateY(-65%) rotate(45deg); pointer-events: none;
  }
  .analytics-app .select-wrap select {
    appearance: none; -webkit-appearance: none; font-family: inherit; font-size: 15px; font-weight: 600;
    color: var(--text); background: var(--surface); border: 1.5px solid var(--border);
    border-radius: var(--r-sm); padding: 11px 40px 11px 16px; min-width: 190px; cursor: pointer;
    transition: border-color .18s, box-shadow .18s;
  }
  .analytics-app .select-wrap select:hover { border-color: var(--accent-line); }
  .analytics-app .select-wrap select:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-soft); }
  .analytics-app .filt-stamp { margin-left: auto; display: flex; align-items: center; gap: 9px; color: var(--muted); font-weight: 600; font-size: 15px; }
  .analytics-app .filt-stamp svg { color: var(--accent); }

  /* ---------- Section head ---------- */
  .analytics-app .section-head { margin-top: 4px; }
  .analytics-app .section-head h2 { font-size: 25px; font-weight: 700; color: var(--primary); letter-spacing: -.01em; margin-bottom: 12px; }
  .analytics-app .section-rule { height: 2px; background: linear-gradient(90deg, var(--accent), var(--accent-line) 60%, transparent); border-radius: 2px; }

  /* ---------- Monthly grid ---------- */
  .analytics-app .month-grid { display: grid; grid-template-columns: repeat(6, 1fr); gap: 18px; }
  .analytics-app .mcard {
    background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-sm);
    padding: 18px 20px 20px; box-shadow: var(--shadow-sm); position: relative; overflow: hidden; cursor: pointer;
    transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease;
  }
  .analytics-app .mcard::before { content: ""; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: var(--accent); opacity: .9; }
  .analytics-app .mcard:hover { transform: translateY(-3px); box-shadow: var(--shadow); border-color: var(--accent-line); }
  .analytics-app .mcard.active { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-soft), var(--shadow); }
  .analytics-app .mcard-month { font-size: 19px; font-weight: 700; color: var(--primary); margin-bottom: 12px; }
  .analytics-app .mcard-label { font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); }
  .analytics-app .mcard-count { font-size: 34px; font-weight: 800; color: var(--text); line-height: 1.05; margin: 3px 0 11px; font-variant-numeric: tabular-nums; }
  .analytics-app .mcard-bar { height: 5px; border-radius: 4px; background: var(--row-alt); overflow: hidden; margin-bottom: 12px; }
  .analytics-app .mcard-bar span { display: block; height: 100%; border-radius: 4px; background: linear-gradient(90deg, var(--accent), var(--accent-strong)); }
  .analytics-app .mcard-nominal { font-size: 15px; font-weight: 700; color: var(--accent-strong); font-variant-numeric: tabular-nums; }
  .analytics-app .mcard--empty::before { background: var(--border); }
  .analytics-app .mcard--empty .mcard-count { color: var(--muted); opacity: .55; }
  .analytics-app .mcard--empty .mcard-nominal { color: var(--muted); opacity: .7; }
  .analytics-app .show-all-btn {
    margin-top: 16px; align-self: flex-start; display: inline-flex; align-items: center; gap: 8px;
    border: 1.5px solid var(--border); background: var(--surface); color: var(--primary);
    border-radius: var(--r-sm); padding: 10px 18px; font-family: inherit; font-size: 14px; font-weight: 700; cursor: pointer;
    transition: all .16s;
  }
  .analytics-app .show-all-btn:hover { border-color: var(--accent); background: var(--accent-soft); }

  /* ---------- Table ---------- */
  .analytics-app .tablecard { padding: 24px 26px; }
  .analytics-app .table-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; padding-bottom: 18px; border-bottom: 1px solid var(--border); }
  .analytics-app .table-title { display: flex; align-items: center; gap: 11px; color: #000; }
  .analytics-app .table-title h2 { font-size: 22px; font-weight: 700; letter-spacing: -.01em; color: #000; }
  .analytics-app .table-total { font-size: 14px; font-weight: 700; color: #000; white-space: nowrap; }
  .analytics-app .table-scroll { margin-top: 18px; border-radius: var(--r-sm); border: 1px solid #c4cdc9; overflow: hidden; }
  .analytics-app table { width: 100%; border-collapse: collapse; table-layout: fixed; }
  .analytics-app thead th {
    background: var(--primary); color: #fff; text-align: center; font-size: 13.5px; font-weight: 700;
    letter-spacing: .01em; padding: 15px 16px; line-height: 1.3;
    border: 1px solid rgba(255,255,255,.22);   /* garis vertikal antar header */
  }
  .analytics-app tbody td { padding: 15px 16px; font-size: 14px; color: #000; border: 1px solid #c4cdc9; vertical-align: top; line-height: 1.45; word-break: break-word; overflow-wrap: anywhere; }
  .analytics-app tbody tr { cursor: pointer; }
  .analytics-app tbody tr:nth-child(even) { background: var(--row-alt); }
  .analytics-app tbody tr:hover { background: var(--accent-soft); }
  .analytics-app .c-no { color: #000; font-weight: 600; text-align: center; }
  .analytics-app thead th.c-no { color: #fff; }   /* judul header "No" tetap putih */
  .analytics-app .agenda-pill { display: inline-block; font-weight: 700; color: #000; background: var(--accent-soft); border: 1px solid var(--accent-line); border-radius: 7px; padding: 3px 9px; font-size: 13px; white-space: nowrap; }
  .analytics-app .c-nilai { font-weight: 700; color: #000; }
  .analytics-app .mono { font-variant-numeric: tabular-nums; }
  .analytics-app .mono-sm { font-size: 13px; color: #000; font-weight: 600; }
  .analytics-app .c-tgl { font-variant-numeric: tabular-nums; white-space: nowrap; color: #000; }
  .analytics-app .empty-row { text-align: center; color: var(--muted); padding: 40px; font-style: italic; }

  /* Lebar kolom (fixed → semua tampil tanpa scroll horizontal) */
  .analytics-app table th:nth-child(1), .analytics-app table td:nth-child(1) { width: 4%; }
  .analytics-app table th:nth-child(2), .analytics-app table td:nth-child(2) { width: 12%; }
  .analytics-app table th:nth-child(3), .analytics-app table td:nth-child(3) { width: 12%; }
  .analytics-app table th:nth-child(4), .analytics-app table td:nth-child(4) { width: 15%; }
  .analytics-app table th:nth-child(5), .analytics-app table td:nth-child(5) { width: 9%; }
  .analytics-app table th:nth-child(6), .analytics-app table td:nth-child(6) { width: 22%; }
  .analytics-app table th:nth-child(7), .analytics-app table td:nth-child(7) { width: 26%; }

  /* Pagination bawaan (pagination-enhanced) dibungkus tema */
  .analytics-app .pagination-wrap { padding: 18px 4px 2px; }

  @media (max-width: 1180px) { .analytics-app .month-grid { grid-template-columns: repeat(4, 1fr); } }
  @media (max-width: 820px) {
    .analytics-app .page { padding: 18px 16px 48px; }
    .analytics-app .month-grid { grid-template-columns: repeat(2, 1fr); }
    .analytics-app .hero { grid-template-columns: 1fr; gap: 24px; padding: 34px 24px; }
    .analytics-app .hero-div { display: none; }
    .analytics-app .filt-stamp { margin-left: 0; width: 100%; }
    .analytics-app table { table-layout: auto; }
    .analytics-app .table-scroll { overflow-x: auto; }
  }
</style>

@php
  $totalDokumen = $yearlySummary['total_dokumen'] ?? 0;
  $totalNominal = $yearlySummary['total_nominal'] ?? 0;
  $maxMonthCount = 1;
  foreach (($monthlyStats ?? []) as $ms) { $maxMonthCount = max($maxMonthCount, (int) ($ms['count'] ?? 0)); }
@endphp

<div class="analytics-app">
  <div class="page">

    {{-- ===== FILTER BAR (paling atas) ===== --}}
    <div class="card filterbar">
      <div class="filt-group">
        <label class="filt-label" for="yearSelect">Pilih Tahun</label>
        <div class="select-wrap">
          <select id="yearSelect" onchange="changeFilter()">
            @foreach($availableYears as $year)
              <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="filt-group">
        <label class="filt-label" for="bagianSelect">Pilih Bagian</label>
        <div class="select-wrap">
          <select id="bagianSelect" onchange="changeFilter()">
            <option value="">Semua Bagian</option>
            @foreach($bagianList as $code => $name)
              <option value="{{ $code }}" {{ $selectedBagian == $code ? 'selected' : '' }}>{{ $name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="filt-stamp">
        <i class="fa-solid fa-calendar-day"></i>
        Data tahun {{ $selectedYear }}@if($selectedBagian) — Bagian {{ $bagianList[$selectedBagian] ?? '' }}@endif
      </div>
    </div>

    {{-- ===== HERO: ringkasan tahun ===== --}}
    <div class="hero">
      <div class="hero-deco"></div>
      <div class="hero-col">
        <div class="hero-eyebrow"><i class="fa-solid fa-folder-open"></i> Total Dokumen</div>
        <div class="hero-big mono">{{ number_format($totalDokumen, 0, ',', '.') }}</div>
        <div class="hero-sub">Tahun {{ $selectedYear }}@if($selectedBagian) · {{ $bagianList[$selectedBagian] ?? '' }}@endif</div>
      </div>
      <div class="hero-div"></div>
      <div class="hero-col">
        <div class="hero-eyebrow"><i class="fa-solid fa-coins"></i> Total Nilai</div>
        <div class="hero-big hero-big--rp mono">Rp {{ number_format($totalNominal, 0, ',', '.') }}</div>
        <div class="hero-sub">Akumulasi nilai dokumen</div>
      </div>
    </div>

    {{-- ===== RINGKASAN BULANAN ===== --}}
    <div class="section-head">
      <h2>Ringkasan Bulanan</h2>
      <div class="section-rule"></div>
    </div>

    <div class="month-grid" id="monthGrid">
      @for($month = 1; $month <= 12; $month++)
        @php
          $m = $monthlyStats[$month] ?? ['name' => '', 'count' => 0, 'total_nominal' => 0];
          $count = (int) ($m['count'] ?? 0);
          $barPct = $maxMonthCount > 0 ? round($count / $maxMonthCount * 100) : 0;
          $isEmpty = $count === 0;
        @endphp
        <div class="mcard {{ $isEmpty ? 'mcard--empty' : '' }} {{ $selectedMonth == $month ? 'active' : '' }}"
             onclick="filterByMonth({{ $month }})" data-month="{{ $month }}">
          <div class="mcard-month">{{ $m['name'] ?: \Carbon\Carbon::create()->month($month)->locale('id')->monthName }}</div>
          <div class="mcard-label">Jumlah Dokumen</div>
          <div class="mcard-count mono">{{ number_format($count, 0, ',', '.') }}</div>
          <div class="mcard-bar"><span style="width: {{ $barPct }}%"></span></div>
          <div class="mcard-nominal">Rp {{ number_format($m['total_nominal'] ?? 0, 0, ',', '.') }}</div>
        </div>
      @endfor
    </div>
    @if($selectedMonth)
      <button class="show-all-btn" onclick="showAllMonths()">
        <i class="fa-solid fa-list"></i> Tampilkan Semua Bulan
      </button>
    @endif

    {{-- ===== TABEL DOKUMEN ===== --}}
    <div class="card tablecard">
      <div class="table-head">
        <div class="table-title">
          <i class="fa-solid fa-table"></i>
          <h2>
            @if($selectedMonth)
              Daftar Dokumen — {{ $monthlyStats[$selectedMonth]['name'] ?? '' }} {{ $selectedYear }}
            @else
              Daftar Dokumen — Tahun {{ $selectedYear }}
            @endif
          </h2>
        </div>
        <div class="table-total">Total: {{ number_format($dokumens->total(), 0, ',', '.') }} dokumen</div>
      </div>

      <div class="table-scroll">
        <table>
          <thead>
            <tr>
              <th class="c-no">No</th>
              <th>Nomor Agenda</th>
              <th>Nilai</th>
              <th>Nomor SPP</th>
              <th>Tanggal Masuk</th>
              <th>Dibayar Kepada</th>
              <th>Uraian</th>
            </tr>
          </thead>
          <tbody>
            @forelse($dokumens as $index => $dokumen)
              @php
                $no = ($dokumens->currentPage() - 1) * $dokumens->perPage() + $index + 1;
                $kepada = ($dokumen->dibayarKepadas && $dokumen->dibayarKepadas->count() > 0)
                    ? $dokumen->dibayarKepadas->pluck('nama_penerima')->join(', ')
                    : ($dokumen->dibayar_kepada ?? '-');
              @endphp
              <tr onclick="if(typeof openViewDocumentModal === 'function') openViewDocumentModal({{ $dokumen->id }});">
                <td class="c-no">{{ $no }}</td>
                <td><span class="agenda-pill">{{ $dokumen->nomor_agenda ?? '-' }}</span></td>
                <td class="c-nilai mono">Rp {{ number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.') }}</td>
                <td class="mono-sm">{{ $dokumen->nomor_spp ?? '-' }}</td>
                <td class="c-tgl">{{ $dokumen->tanggal_masuk ? $dokumen->tanggal_masuk->format('d/m/Y') : '-' }}</td>
                <td>{{ $kepada ?: '-' }}</td>
                <td>{{ $dokumen->uraian_spp ?? '-' }}</td>
              </tr>
            @empty
              <tr>
                <td colspan="7" class="empty-row">Tidak ada dokumen untuk periode yang dipilih.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="pagination-wrap">
        @include('partials.pagination-enhanced', ['paginator' => $dokumens])
      </div>
    </div>

  </div>
</div>

<script>
function changeFilter() {
  const year = document.getElementById('yearSelect').value;
  const bagian = document.getElementById('bagianSelect').value;
  const url = new URL(window.location.href);
  url.searchParams.set('year', year);
  if (bagian) { url.searchParams.set('bagian', bagian); } else { url.searchParams.delete('bagian'); }
  url.searchParams.delete('month');
  url.searchParams.delete('page');
  window.location.href = url.toString();
}

function filterByMonth(month) {
  const year = document.getElementById('yearSelect').value;
  const bagian = document.getElementById('bagianSelect').value;
  const url = new URL(window.location.href);
  url.searchParams.set('year', year);
  url.searchParams.set('month', month);
  if (bagian) { url.searchParams.set('bagian', bagian); } else { url.searchParams.delete('bagian'); }
  url.searchParams.delete('page');
  window.location.href = url.toString();
}

function showAllMonths() {
  const year = document.getElementById('yearSelect').value;
  const bagian = document.getElementById('bagianSelect').value;
  const url = new URL(window.location.href);
  url.searchParams.set('year', year);
  url.searchParams.delete('month');
  if (bagian) { url.searchParams.set('bagian', bagian); } else { url.searchParams.delete('bagian'); }
  url.searchParams.delete('page');
  window.location.href = url.toString();
}
</script>

@endsection
