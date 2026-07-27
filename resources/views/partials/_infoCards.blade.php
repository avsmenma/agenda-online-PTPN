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
            <div class="wd-card-value" style="color:{{ $valueColor }}">{{ number_format($card['value'], 0, ',', '.') }}</div>
            <div class="wd-card-sub">{{ $card['sub'] }}</div>
        </{{ $tag }}>
    @endforeach
</div>
