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
      $hours = (float) $hours;
      if ($hours < 1) {
          return max(1, (int) round($hours * 60)) . ' menit';
      }
      if ($hours < 24) {
          $h = (int) floor($hours);
          $m = (int) round(($hours - $h) * 60);
          return $m > 0 ? ($h . ' jam ' . $m . ' menit') : ($h . ' jam');
      }
      $days = (int) floor($hours / 24);
      $h = (int) round($hours - $days * 24);
      return $h > 0 ? ($days . ' hari ' . $h . ' jam') : ($days . ' hari');
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

    // Bangun array kartu untuk partial bersama _infoCards (output identik markup lama).
    $cards = [
      [
        'label'  => 'Total Dokumen Agenda',
        'value'  => $totalDokumenAgenda,
        'sub'    => 'seluruh dokumen sistem',
        'icon'   => '<svg viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>',
        'iconBg' => '#f0f4ff',
        'href'   => route($cfg['docRouteName']),
      ],
      [
        'label'  => 'Total Dokumen ' . $card2Label,
        'value'  => $card2Value,
        'sub'    => $card2Sub,
        'icon'   => '<svg viewBox="0 0 24 24" fill="none" stroke="#0891b2" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>',
        'iconBg' => '#ecfeff',
        'href'   => route($cfg['docRouteName']),
      ],
      [
        'label'      => 'Total Dokumen ' . $card3Label,
        'value'      => $card3Value,
        'sub'        => $card3Sub,
        'icon'       => '<svg viewBox="0 0 24 24" fill="none" stroke="' . $card3Color . '" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>',
        'iconBg'     => $card3IconBg,
        'valueColor' => $card3Color,
        'href'       => route($cfg['docRouteName'], ['status' => 'terkirim']),
      ],
      [
        'label'      => 'Total Dokumen ' . $fourthLabel,
        'value'      => $fourthCount,
        'sub'        => $fourthSub,
        'icon'       => $fourthIsReturn
          ? '<svg viewBox="0 0 24 24" fill="none" stroke="' . $fourthColor . '" stroke-width="2"><polyline points="9 14 4 9 9 4"/><path d="M20 20v-7a4 4 0 00-4-4H4"/></svg>'
          : '<svg viewBox="0 0 24 24" fill="none" stroke="' . $fourthColor . '" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
        'iconBg'     => $fourthIconBg,
        'valueColor' => $fourthColor,
        // tanpa href → kartu ke-4 tetap <div> (sama seperti sekarang)
      ],
    ];
  @endphp
  @include('partials._infoCards', ['cards' => $cards])

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
      <div class="wd-bubble-area" id="wdBubbleArea">
        <div class="wd-bubble wd-bubble--late"
             style="width:{{ $bubbleSize($terlambat) }}px;height:{{ $bubbleSize($terlambat) }}px;">
          <span class="wd-bubble-val">{{ $terlambat }}</span>
          <span class="wd-bubble-name">Terlambat</span>
        </div>
        <div class="wd-bubble wd-bubble--warn"
             style="width:{{ $bubbleSize($peringatan) }}px;height:{{ $bubbleSize($peringatan) }}px;">
          <span class="wd-bubble-val">{{ $peringatan }}</span>
          <span class="wd-bubble-name">Peringatan</span>
        </div>
        <div class="wd-bubble wd-bubble--safe"
             style="width:{{ $bubbleSize($aman) }}px;height:{{ $bubbleSize($aman) }}px;">
          <span class="wd-bubble-val">{{ $aman }}</span>
          <span class="wd-bubble-name">Aman</span>
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

  /* Charts */
  .wd-charts { display: grid; grid-template-columns: 1.9fr 1fr; gap: 0.85rem; margin-bottom: 1rem; }
  .wd-panel { background: #fff; border: 1px solid #e8eef4; border-radius: 16px; padding: 1.1rem 1.2rem;
    box-shadow: 0 5px 18px rgba(15,23,42,.06); }
  .wd-panel-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.9rem; }
  .wd-panel-title { font-size: 0.98rem; font-weight: 700; color: #0f172a; margin: 0; }
  .wd-panel-note { font-size: 0.7rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: .02em; }
  .wd-chartbox { position: relative; height: 300px; }
  .wd-empty { padding: 2.2rem 1rem; text-align: center; color: #94a3b8; font-size: 0.88rem; }

  /* Bubble — area fisika mengambang (gaya cryptobubbles) */
  .wd-bubble-area { position: relative; height: 240px; overflow: hidden; touch-action: none; }
  .wd-bubble { position: absolute; left: 0; top: 0; border-radius: 50%;
    display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 1px;
    color: #fff; font-weight: 700; box-shadow: 0 8px 22px rgba(15,23,42,.12);
    cursor: grab; user-select: none; -webkit-user-select: none; touch-action: none;
    will-change: transform; transition: box-shadow .15s ease; }
  .wd-bubble::after { content: ""; position: absolute; inset: 0; border-radius: 50%; pointer-events: none;
    background: radial-gradient(circle at 32% 28%, rgba(255,255,255,.45), rgba(255,255,255,0) 55%); }
  .wd-bubble.is-grabbing { cursor: grabbing; box-shadow: 0 14px 30px rgba(15,23,42,.28); z-index: 10; }
  .wd-bubble-val { font-size: 1.5rem; line-height: 1; }
  .wd-bubble-name { font-size: 0.62rem; font-weight: 600; letter-spacing: .02em; opacity: .92; text-transform: none; }
  .wd-bubble--safe { background: rgba(16,185,129,.9); z-index: 3; }
  .wd-bubble--warn { background: rgba(245,158,11,.9); z-index: 2; }
  .wd-bubble--late { background: rgba(244,63,94,.88); z-index: 1; }
  @media (prefers-reduced-motion: reduce) { .wd-bubble { transition: none; } }
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
  .wd-num { text-align: center; white-space: nowrap; }
  .wd-table thead th.wd-num { text-align: center; }
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

  @media (max-width: 1100px) { .wd-charts { grid-template-columns: 1fr; } }
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

{{-- Simulasi fisika bubble "Status Keterlambatan" (mengambang, memantul, bisa digeser) --}}
<script>
  (function () {
    const area = document.getElementById('wdBubbleArea');
    if (!area) return;

    const els = Array.prototype.slice.call(area.querySelectorAll('.wd-bubble'));
    if (!els.length) return;

    const reduceMotion = window.matchMedia
      && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // Batas kecepatan agar gerakan lembut & tidak bikin pusing.
    const MAX_SPEED   = 0.55;   // px per frame (dinormalisasi ke 60fps)
    const MIN_FLOAT   = 0.12;   // jaga bubble tetap "hidup" saat mengambang
    const WANDER      = 0.02;   // dorongan acak kecil tiap frame
    const WALL_DAMP   = 0.9;    // redam saat memantul dinding
    const FRICTION    = 0.992;  // gesekan lembut agar lemparan mereda
    const RESTITUTION = 0.85;   // kelentingan antar-bubble

    let W = 0, H = 0;
    const bubbles = els.map(function (el, i) {
      const r = el.offsetWidth / 2;
      // Kecepatan awal acak (deterministik per-index, tak butuh Math.random di init).
      const a = (i * 2.3999) + 0.7;
      return {
        el: el,
        r: r,
        m: Math.max(r * r, 1), // massa ~ luas
        x: 0, y: 0,
        vx: Math.cos(a) * 0.3,
        vy: Math.sin(a) * 0.3,
        dragging: false,
      };
    });

    function measure() {
      const rect = area.getBoundingClientRect();
      W = rect.width; H = rect.height;
    }

    function placeInitial() {
      measure();
      // Sebar tanpa tumpang tindih: susun horizontal lalu dorong ke tengah vertikal.
      let cursor = 0;
      bubbles.forEach(function (b, i) {
        const gap = 6;
        b.x = Math.min(Math.max(cursor + b.r + gap, b.r), Math.max(W - b.r, b.r));
        cursor = b.x + b.r;
        const bias = (i % 2 === 0) ? 0.42 : 0.62;
        b.y = Math.min(Math.max(H * bias, b.r), Math.max(H - b.r, b.r));
      });
    }

    function clampSpeed(b) {
      const sp = Math.hypot(b.vx, b.vy);
      if (sp > MAX_SPEED) { const k = MAX_SPEED / sp; b.vx *= k; b.vy *= k; }
    }

    function step() {
      for (let i = 0; i < bubbles.length; i++) {
        const b = bubbles[i];
        if (b.dragging) continue;

        if (!reduceMotion) {
          // Wander lembut (arah bervariasi per bubble & waktu, tanpa Math.random tiap frame).
          b.vx += Math.sin((tick + i * 40) * 0.017) * WANDER;
          b.vy += Math.cos((tick + i * 55) * 0.013) * WANDER;
          b.vx *= FRICTION; b.vy *= FRICTION;

          // Jaga agar tetap mengambang pelan.
          const sp = Math.hypot(b.vx, b.vy);
          if (sp < MIN_FLOAT) {
            const a = (tick + i * 90) * 0.01;
            b.vx += Math.cos(a) * MIN_FLOAT * 0.5;
            b.vy += Math.sin(a) * MIN_FLOAT * 0.5;
          }
          clampSpeed(b);
          b.x += b.vx; b.y += b.vy;
        }

        // Pantul dinding kartu.
        if (b.x - b.r < 0)  { b.x = b.r;      b.vx = Math.abs(b.vx) * WALL_DAMP; }
        if (b.x + b.r > W)  { b.x = W - b.r;  b.vx = -Math.abs(b.vx) * WALL_DAMP; }
        if (b.y - b.r < 0)  { b.y = b.r;      b.vy = Math.abs(b.vy) * WALL_DAMP; }
        if (b.y + b.r > H)  { b.y = H - b.r;  b.vy = -Math.abs(b.vy) * WALL_DAMP; }
      }

      // Tabrakan antar-bubble: dorong lepas + tukar momentum (elastis).
      for (let i = 0; i < bubbles.length; i++) {
        for (let j = i + 1; j < bubbles.length; j++) {
          resolve(bubbles[i], bubbles[j]);
        }
      }

      // Render.
      for (let k = 0; k < bubbles.length; k++) {
        const b = bubbles[k];
        b.el.style.transform = 'translate(' + (b.x - b.r) + 'px,' + (b.y - b.r) + 'px)';
      }
    }

    function resolve(a, b) {
      let dx = b.x - a.x, dy = b.y - a.y;
      let dist = Math.hypot(dx, dy);
      const minDist = a.r + b.r;
      if (dist === 0) { dx = 0.01; dy = 0.01; dist = 0.0141; }
      if (dist >= minDist) return;

      const nx = dx / dist, ny = dy / dist;
      const overlap = minDist - dist;

      // Massa efektif: bubble yang sedang digeser = tak tergoyahkan (massa tak hingga).
      const aFixed = a.dragging, bFixed = b.dragging;
      const invA = aFixed ? 0 : 1 / a.m;
      const invB = bFixed ? 0 : 1 / b.m;
      const invSum = invA + invB || 1;

      // Pisahkan overlap sesuai proporsi massa.
      a.x -= nx * overlap * (invA / invSum);
      a.y -= ny * overlap * (invA / invSum);
      b.x += nx * overlap * (invB / invSum);
      b.y += ny * overlap * (invB / invSum);

      // Impuls sepanjang normal (hanya bila saling mendekat).
      const rvx = b.vx - a.vx, rvy = b.vy - a.vy;
      const vn = rvx * nx + rvy * ny;
      if (vn > 0) return;
      const imp = -(1 + RESTITUTION) * vn / invSum;
      a.vx -= imp * invA * nx; a.vy -= imp * invA * ny;
      b.vx += imp * invB * nx; b.vy += imp * invB * ny;
      clampSpeed(a); clampSpeed(b);
    }

    // ---- Drag (mouse + sentuh via Pointer Events) ----
    let active = null, grabDX = 0, grabDY = 0, lastX = 0, lastY = 0;

    function pointFromEvent(e) {
      const rect = area.getBoundingClientRect();
      return { x: e.clientX - rect.left, y: e.clientY - rect.top };
    }

    bubbles.forEach(function (b) {
      b.el.addEventListener('pointerdown', function (e) {
        e.preventDefault();
        const p = pointFromEvent(e);
        active = b; b.dragging = true;
        b.el.classList.add('is-grabbing');
        b.el.setPointerCapture && b.el.setPointerCapture(e.pointerId);
        grabDX = p.x - b.x; grabDY = p.y - b.y;
        lastX = p.x; lastY = p.y;
        b.vx = 0; b.vy = 0;
      });
    });

    window.addEventListener('pointermove', function (e) {
      if (!active) return;
      const p = pointFromEvent(e);
      let nx = p.x - grabDX, ny = p.y - grabDY;
      nx = Math.min(Math.max(nx, active.r), Math.max(W - active.r, active.r));
      ny = Math.min(Math.max(ny, active.r), Math.max(H - active.r, active.r));
      // Kecepatan lemparan dari perpindahan pointer.
      active.vx = nx - active.x; active.vy = ny - active.y;
      active.x = nx; active.y = ny;
      lastX = p.x; lastY = p.y;
    });

    function release() {
      if (!active) return;
      active.el.classList.remove('is-grabbing');
      active.dragging = false;
      clampSpeed(active); // batasi lemparan agar tetap lembut
      active = null;
    }
    window.addEventListener('pointerup', release);
    window.addEventListener('pointercancel', release);

    // ---- Loop ----
    let tick = 0, raf = null, running = false;
    function frame() {
      tick++;
      step();
      raf = requestAnimationFrame(frame);
    }
    function start() { if (!running) { running = true; frame(); } }
    function stop()  { running = false; if (raf) cancelAnimationFrame(raf); raf = null; }

    // Hemat daya: berhenti saat tab tak terlihat / kartu di luar layar.
    document.addEventListener('visibilitychange', function () {
      if (document.hidden) stop(); else start();
    });
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
          if (en.isIntersecting) start(); else stop();
        });
      }, { threshold: 0 }).observe(area);
    }
    if ('ResizeObserver' in window) {
      new ResizeObserver(function () {
        measure();
        // Tarik bubble yang terlanjur di luar batas ke dalam.
        bubbles.forEach(function (b) {
          b.x = Math.min(Math.max(b.x, b.r), Math.max(W - b.r, b.r));
          b.y = Math.min(Math.max(b.y, b.r), Math.max(H - b.r, b.r));
        });
      }).observe(area);
    } else {
      window.addEventListener('resize', measure);
    }

    placeInitial();
    // Render awal langsung, lalu jalankan animasi.
    step();
    start();
  })();
</script>
@endpush
@endsection
