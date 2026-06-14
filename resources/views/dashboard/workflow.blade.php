@extends('layouts.app')

@section('content')
@php
  $rupiah = fn($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');

  // Ukuran bubble proporsional terhadap jumlah (akar kuadrat agar tidak terlalu timpang).
  $bubbleMax = max($aman, $peringatan, $terlambat, 1);
  $bubbleSize = function ($val) use ($bubbleMax) {
      $min = 70; $max = 150;
      $ratio = $bubbleMax > 0 ? sqrt($val) / sqrt($bubbleMax) : 0;
      return (int) round($min + ($max - $min) * $ratio);
  };

  $waitLabel = function ($hours) {
      if ($hours < 24) return $hours . ' jam';
      return floor($hours / 24) . ' hari';
  };
  $waitClass = function ($hours) {
      if ($hours < 24) return 'wd-badge--safe';
      if ($hours < 72) return 'wd-badge--warn';
      return 'wd-badge--late';
  };
@endphp

<div class="wd-wrap">
  {{-- ====================== 4 KARTU INFORMASI ====================== --}}
  @php
    $fourthIsReturn = ($fourthLabel === 'Dikembalikan');
    // Kartu 2 & 3 bisa di-override per role (mis. pembayaran pakai status bayar).
    // Tanpa override → perilaku default (sama spt verifikasi/perpajakan/akutansi).
    $card2Label = $card2Label ?? $cfg['label'];
    $card2Value = $card2Value ?? $totalDokumenRole;
    $card2Sub   = $card2Sub   ?? 'ditangani tim ini';
    $card3Label = $card3Label ?? 'Selesai';
    $card3Value = $card3Value ?? $totalSelesai;
    $card3Sub   = $card3Sub   ?? 'completed / selesai';
    // Warna kartu (angka + ikon) — bisa di-override per role.
    $card3Color   = $card3Color   ?? '#10b981';
    $card3IconBg  = $card3IconBg  ?? '#ecfdf5';
    $fourthColor  = $fourthColor  ?? ($fourthIsReturn ? '#ef4444' : '#f59e0b');
    $fourthIconBg = $fourthIconBg ?? ($fourthIsReturn ? '#fef2f2' : '#fffbeb');
  @endphp
  <div class="wd-cards">
    {{-- Total Dokumen Agenda --}}
    <a href="{{ route($cfg['docRouteName']) }}" class="wd-card">
      <div class="wd-card-label">Total Dokumen Agenda</div>
      <div class="wd-card-icon" style="background:#f0f4ff">
        <svg viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2">
          <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
          <polyline points="14,2 14,8 20,8"/>
        </svg>
      </div>
      <div class="wd-card-value">{{ number_format($totalDokumenAgenda, 0, ',', '.') }}</div>
      <div class="wd-card-sub">seluruh dokumen sistem</div>
    </a>

    {{-- Kartu 2 (default: Total Dokumen Role; pembayaran: Belum Siap Bayar) --}}
    <a href="{{ route($cfg['docRouteName']) }}" class="wd-card">
      <div class="wd-card-label">Total Dokumen {{ $card2Label }}</div>
      <div class="wd-card-icon" style="background:#ecfeff">
        <svg viewBox="0 0 24 24" fill="none" stroke="#0891b2" stroke-width="2">
          <path d="M9 11l3 3L22 4"/>
          <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
        </svg>
      </div>
      <div class="wd-card-value">{{ number_format($card2Value, 0, ',', '.') }}</div>
      <div class="wd-card-sub">{{ $card2Sub }}</div>
    </a>

    {{-- Kartu 3 (default: Total Dokumen Selesai; pembayaran: Siap Bayar) --}}
    <a href="{{ route($cfg['docRouteName'], ['status' => 'terkirim']) }}" class="wd-card">
      <div class="wd-card-label">Total Dokumen {{ $card3Label }}</div>
      <div class="wd-card-icon" style="background:{{ $card3IconBg }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="{{ $card3Color }}" stroke-width="2">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
      </div>
      <div class="wd-card-value" style="color:{{ $card3Color }}">{{ number_format($card3Value, 0, ',', '.') }}</div>
      <div class="wd-card-sub">{{ $card3Sub }}</div>
    </a>

    {{-- Kartu ke-4: Dikembalikan / Hari Ini --}}
    <div class="wd-card">
      <div class="wd-card-label">Total Dokumen {{ $fourthLabel }}</div>
      <div class="wd-card-icon" style="background:{{ $fourthIconBg }}">
        @if($fourthIsReturn)
          <svg viewBox="0 0 24 24" fill="none" stroke="{{ $fourthColor }}" stroke-width="2">
            <polyline points="9 14 4 9 9 4"/>
            <path d="M20 20v-7a4 4 0 00-4-4H4"/>
          </svg>
        @else
          <svg viewBox="0 0 24 24" fill="none" stroke="{{ $fourthColor }}" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
        @endif
      </div>
      <div class="wd-card-value" style="color:{{ $fourthColor }}">{{ number_format($fourthCount, 0, ',', '.') }}</div>
      <div class="wd-card-sub">{{ $fourthSub }}</div>
    </div>
  </div>

  {{-- ====================== CHART AREA ====================== --}}
  <div class="wd-charts">
    {{-- Bar chart: total dokumen per bagian --}}
    <div class="wd-panel">
      <div class="wd-panel-head">
        <h2 class="wd-panel-title">Total Dokumen per Bagian</h2>
        <span class="wd-panel-note">{{ count($bagianLabels) }} bagian</span>
      </div>
      @if(count($bagianLabels))
        <div class="wd-chartbox"><canvas id="wdBagianChart"></canvas></div>
      @else
        <div class="wd-empty">Belum ada dokumen yang ditangani.</div>
      @endif
    </div>

    {{-- Bubble: aman / peringatan / terlambat --}}
    <div class="wd-panel">
      <div class="wd-panel-head">
        <h2 class="wd-panel-title">Status Keterlambatan</h2>
        <span class="wd-panel-note">dokumen tim ini</span>
      </div>
      <div class="wd-bubble-area">
        <div class="wd-bubble wd-bubble--late"
             style="width:{{ $bubbleSize($terlambat) }}px;height:{{ $bubbleSize($terlambat) }}px;">
          <span class="wd-bubble-val">{{ $terlambat }}</span>
        </div>
        <div class="wd-bubble wd-bubble--warn"
             style="width:{{ $bubbleSize($peringatan) }}px;height:{{ $bubbleSize($peringatan) }}px;">
          <span class="wd-bubble-val">{{ $peringatan }}</span>
        </div>
        <div class="wd-bubble wd-bubble--safe"
             style="width:{{ $bubbleSize($aman) }}px;height:{{ $bubbleSize($aman) }}px;">
          <span class="wd-bubble-val">{{ $aman }}</span>
        </div>
      </div>
      <div class="wd-bubble-legend">
        <div class="wd-legend-item"><span class="wd-dot wd-dot--safe"></span> Aman <b>{{ $aman }}</b></div>
        <div class="wd-legend-item"><span class="wd-dot wd-dot--warn"></span> Peringatan <b>{{ $peringatan }}</b></div>
        <div class="wd-legend-item"><span class="wd-dot wd-dot--late"></span> Terlambat <b>{{ $terlambat }}</b></div>
      </div>
    </div>
  </div>

  {{-- ====================== REKOMENDASI ====================== --}}
  <div class="wd-panel">
    <div class="wd-panel-head">
      <h2 class="wd-panel-title">Rekomendasi Dokumen untuk Diselesaikan</h2>
      <span class="wd-panel-note">paling lama menunggu &amp; belum dikerjakan</span>
    </div>

    @if($recommendations->isEmpty())
      <div class="wd-empty">Tidak ada dokumen yang menunggu. Kerja bagus! 🎉</div>
    @else
      <div class="wd-table-scroll">
        <table class="wd-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Nomor Agenda</th>
              <th>Uraian / SPP</th>
              <th>Bagian</th>
              <th class="wd-num">Nilai</th>
              <th>Lama Menunggu</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach($recommendations as $i => $doc)
              <tr>
                <td class="wd-muted">{{ $i + 1 }}</td>
                <td><span class="wd-agenda">{{ $doc->nomor_agenda ?? '-' }}</span></td>
                <td>
                  <div class="wd-uraian">{{ \Illuminate\Support\Str::limit($doc->uraian_spp ?? '-', 60) }}</div>
                  <div class="wd-muted">{{ $doc->nomor_spp ?? '-' }}</div>
                </td>
                <td>{{ $doc->bagian ?: '-' }}</td>
                <td class="wd-num">{{ $rupiah($doc->nilai_rupiah) }}</td>
                <td>
                  <span class="wd-badge {{ $waitClass($doc->wait_hours) }}">
                    <i class="fas fa-clock"></i> {{ $waitLabel($doc->wait_hours) }}
                  </span>
                </td>
                <td>
                  <a href="{{ route($cfg['docRouteName'], ['search' => $doc->nomor_agenda]) }}" class="wd-procbtn">
                    Proses <i class="fas fa-arrow-right"></i>
                  </a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  </div>
</div>

<style>
  .wd-wrap { font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; padding: 0.25rem 0.1rem 2rem; }

  /* Cards — gaya "kartu informasi" mengikuti /owner/home (stat-card) */
  .wd-cards { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 20px; }
  .wd-card {
    position: relative; overflow: hidden;
    background: #fff; border: 1px solid #e8ecf4; border-radius: 14px;
    padding: 18px 18px 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.05);
    text-decoration: none; color: #1a2340;
    transition: transform .2s, box-shadow .2s;
    animation: wdFadeUp .4s ease both;
  }
  .wd-card:hover { transform: translateY(-2px); box-shadow: 0 4px 20px rgba(0,0,0,.09); color: #1a2340; }
  .wd-card:nth-child(1) { animation-delay: .05s; }
  .wd-card:nth-child(2) { animation-delay: .1s; }
  .wd-card:nth-child(3) { animation-delay: .15s; }
  .wd-card:nth-child(4) { animation-delay: .2s; }

  .wd-card-label { font-size: 10.5px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em;
    color: #a0aec0; margin-bottom: 10px; padding-right: 44px; line-height: 1.3; }
  .wd-card-value { font-family: 'Sora', 'Plus Jakarta Sans', sans-serif; font-size: 26px; font-weight: 700;
    color: #1a2340; line-height: 1; margin-bottom: 6px; }
  .wd-card-sub { font-size: 11px; font-weight: 500; color: #a0aec0; }
  .wd-card-icon { position: absolute; right: 16px; top: 16px; width: 36px; height: 36px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center; }
  .wd-card-icon svg { width: 18px; height: 18px; }

  @keyframes wdFadeUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

  /* Charts */
  .wd-charts { display: grid; grid-template-columns: 1.9fr 1fr; gap: 0.85rem; margin-bottom: 1rem; }
  .wd-panel { background: #fff; border: 1px solid #e8eef4; border-radius: 16px; padding: 1.1rem 1.2rem;
    box-shadow: 0 5px 18px rgba(15,23,42,.06); }
  .wd-panel-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.9rem; }
  .wd-panel-title { font-size: 0.98rem; font-weight: 700; color: #0f172a; margin: 0; }
  .wd-panel-note { font-size: 0.7rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: .02em; }
  .wd-chartbox { position: relative; height: 300px; }
  .wd-empty { padding: 2.2rem 1rem; text-align: center; color: #94a3b8; font-size: 0.88rem; }

  /* Bubble */
  .wd-bubble-area { position: relative; height: 240px; display: flex; align-items: center; justify-content: center; }
  .wd-bubble { position: absolute; border-radius: 50%; display: flex; align-items: center; justify-content: center;
    color: #fff; font-weight: 700; box-shadow: 0 8px 22px rgba(15,23,42,.12); }
  .wd-bubble-val { font-size: 1.5rem; }
  .wd-bubble--safe { background: rgba(16,185,129,.85); left: 18%; top: 46%; transform: translate(-50%,-50%); z-index: 3; }
  .wd-bubble--warn { background: rgba(245,158,11,.85); left: 50%; top: 30%; transform: translate(-50%,-50%); z-index: 2; }
  .wd-bubble--late { background: rgba(244,63,94,.82); left: 70%; top: 60%; transform: translate(-50%,-50%); z-index: 1; }
  .wd-bubble-legend { display: flex; flex-direction: column; gap: 0.5rem; margin-top: 0.5rem; border-top: 1px solid #f1f5f9; padding-top: 0.85rem; }
  .wd-legend-item { display: flex; align-items: center; gap: 0.5rem; font-size: 0.82rem; color: #475569; }
  .wd-legend-item b { margin-left: auto; color: #0f172a; }
  .wd-dot { width: 11px; height: 11px; border-radius: 50%; flex: 0 0 auto; }
  .wd-dot--safe { background: #10b981; }
  .wd-dot--warn { background: #f59e0b; }
  .wd-dot--late { background: #f43f5e; }

  /* Table */
  .wd-table-scroll { overflow-x: auto; }
  .wd-table { width: 100%; border-collapse: collapse; font-size: 0.84rem; }
  .wd-table thead th { text-align: left; font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em;
    color: #94a3b8; padding: 0.55rem 0.7rem; border-bottom: 1px solid #e8eef4; white-space: nowrap; }
  .wd-table tbody td { padding: 0.7rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; color: #334155; }
  .wd-table tbody tr:hover { background: #f8fafc; }
  .wd-num { text-align: right; white-space: nowrap; }
  .wd-muted { color: #94a3b8; font-size: 0.75rem; }
  .wd-agenda { font-weight: 700; color: #083E40; }
  .wd-uraian { font-weight: 600; color: #0f172a; }
  .wd-badge { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.72rem; font-weight: 700;
    padding: 0.25rem 0.55rem; border-radius: 999px; white-space: nowrap; }
  .wd-badge--safe { background: rgba(16,185,129,.12); color: #059669; }
  .wd-badge--warn { background: rgba(245,158,11,.14); color: #d97706; }
  .wd-badge--late { background: rgba(244,63,94,.12); color: #e11d48; }
  .wd-procbtn { display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.76rem; font-weight: 600;
    color: #083E40; text-decoration: none; white-space: nowrap; }
  .wd-procbtn:hover { color: #0d5b59; }

  @media (max-width: 1100px) { .wd-charts { grid-template-columns: 1fr; } .wd-cards { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 560px) { .wd-cards { grid-template-columns: 1fr; } }
</style>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('wdBagianChart');
    if (!canvas || typeof Chart === 'undefined') return;

    const labels = @json($bagianLabels);
    const counts = @json($bagianCounts);

    new Chart(canvas.getContext('2d'), {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Jumlah Dokumen',
          data: counts,
          backgroundColor: 'rgba(13, 91, 89, 0.85)',
          hoverBackgroundColor: 'rgba(8, 62, 64, 1)',
          borderRadius: 8,
          maxBarThickness: 46,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { backgroundColor: '#0f172a' } },
        scales: {
          x: { grid: { display: false }, ticks: { color: '#64748b', font: { size: 11 } } },
          y: { beginAtZero: true, ticks: { precision: 0, color: '#94a3b8' }, grid: { color: '#f1f5f9' } }
        }
      }
    });
  });
</script>
@endpush
@endsection
