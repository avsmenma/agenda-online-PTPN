{{--
  Kartu informasi bersama (stat card) — dipakai kartu keuangan (dashboard.workflow)
  DAN kartu informasi bagian. Data-driven lewat array $cards. Lihat spec
  docs/superpowers/specs/2026-07-27-satukan-kartu-info-bagian-design.md untuk kontrak.
--}}
@php
    /** @var array $cards */
    $cols = count($cards);
@endphp
<div class="wd-cards wd-cards--cols-{{ $cols }}">
    @foreach ($cards as $card)
        @php
            $hasHref    = ! empty($card['href']);
            $tag        = $hasHref ? 'a' : 'div';
            $valueColor = $card['valueColor'] ?? '#1a2340';
            $isActive   = ! empty($card['active']);
        @endphp
        <{{ $tag }} class="wd-card{{ $isActive ? ' wd-card--active' : '' }}"@if ($hasHref) href="{{ $card['href'] }}"@endif>
            <div class="wd-card-label">{{ $card['label'] }}</div>
            <div class="wd-card-icon" style="background:{{ $card['iconBg'] }}">{!! $card['icon'] !!}</div>
            <div class="wd-card-value" style="color:{{ $valueColor }}">{{ $card['displayValue'] ?? (is_numeric($card['value']) ? number_format($card['value'], 0, ',', '.') : $card['value']) }}</div>
            <div class="wd-card-sub">{{ $card['sub'] }}</div>
        </{{ $tag }}>
    @endforeach
</div>

<style>
  /* Kartu informasi bersama — gaya "kartu informasi" mengikuti /owner/home (stat-card).
     Dipindah dari dashboard/workflow.blade.php agar dipakai lintas role (keuangan + bagian). */
  .wd-cards { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 20px; }
  .wd-cards--cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
  .wd-cards--cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
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
  /* Sorotan kartu aktif (dipakai bagian saat filter ?status= aktif). Aditif —
     kartu keuangan tak pernah mengirim active, jadi tak terpengaruh. */
  .wd-card--active { box-shadow: 0 0 0 2px #083E40, 0 1px 3px rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.05); }

  .wd-card-label { font-size: 10.5px; font-weight: 600; text-transform: uppercase; letter-spacing: .06em;
    color: #a0aec0; margin-bottom: 10px; padding-right: 44px; line-height: 1.3; }
  .wd-card-value { font-family: 'Sora', 'Plus Jakarta Sans', sans-serif; font-size: 24px; font-weight: 700;
    color: #1a2340; line-height: 1.15; margin-bottom: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .wd-card-sub { font-size: 11px; font-weight: 500; color: #a0aec0; }
  .wd-card-icon { position: absolute; right: 16px; top: 16px; width: 36px; height: 36px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center; }
  .wd-card-icon svg { width: 18px; height: 18px; }

  @keyframes wdFadeUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

  @media (max-width: 1100px) { .wd-cards { grid-template-columns: repeat(2, 1fr); } }
  @media (max-width: 560px)  { .wd-cards { grid-template-columns: 1fr; } }
</style>
