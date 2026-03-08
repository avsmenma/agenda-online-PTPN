@extends('layouts.app')

@section('content')
  <style>
    /* Modern Professional Workflow Design */
    * {
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    /* Custom Scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
      width: 6px;
      height: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
      background: #f1f5f9;
      border-radius: 10px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
      background: linear-gradient(135deg, #083E40 0%, #889717 100%);
      border-radius: 10px;
    }

    /* Professional Timeline Container */
    .workflow-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 40px 20px;
      display: flex;
      flex-direction: column;
    }

    /* Order: Header first, then Info Grid, then Timeline */
    .workflow-header {
      order: 1 !important;
    }

    .info-grid {
      order: 2 !important;
    }

    .workflow-timeline {
      order: 99 !important;
    }

    /* Header Section */
    .workflow-header {
      background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
      border-radius: 24px;
      padding: 32px;
      margin-bottom: 40px;
      box-shadow: 0 4px 24px rgba(8, 62, 64, 0.08);
      border: 1px solid rgba(8, 62, 64, 0.1);
    }

    .workflow-header-top {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 24px;
      flex-wrap: wrap;
      gap: 20px;
    }

    .workflow-title-section h1 {
      font-size: 32px;
      font-weight: 800;
      background: linear-gradient(135deg, #083E40 0%, #889717 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
      margin-bottom: 8px;
      letter-spacing: -0.5px;
    }

    .workflow-title-section .document-info {
      font-size: 16px;
      color: #64748b;
      display: flex;
      align-items: center;
      gap: 8px;
      margin-top: 8px;
    }

    .workflow-header-actions {
      display: flex;
      gap: 12px;
      align-items: center;
    }

    .btn-back {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 12px 24px;
      background: white;
      border: 2px solid rgba(8, 62, 64, 0.2);
      border-radius: 12px;
      color: #083E40;
      font-weight: 600;
      font-size: 14px;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .btn-back:hover {
      background: #083E40;
      color: white;
      border-color: #083E40;
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(8, 62, 64, 0.2);
    }

    /* Progress Bar */
    .progress-bar-container {
      background: #f1f5f9;
      height: 8px;
      border-radius: 10px;
      overflow: hidden;
      position: relative;
    }

    .progress-bar-fill {
      height: 100%;
      background: linear-gradient(90deg, #083E40 0%, #0a4f52 50%, #889717 100%);
      background-size: 200% 100%;
      border-radius: 10px;
      transition: width 1s ease-out;
      position: relative;
      overflow: hidden;
    }

    .progress-bar-fill::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
      animation: shimmer 2s infinite;
    }

    @keyframes shimmer {
      0% {
        transform: translateX(-100%);
      }

      100% {
        transform: translateX(100%);
      }
    }

    .progress-percentage {
      text-align: right;
      margin-top: 8px;
      font-size: 14px;
      font-weight: 600;
      color: #64748b;
    }

    /* \u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\n       TREASURE MAP CANVAS \u2014 Enhanced Visual v2
       \u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550 */

    /* \u2500\u2500 Section wrapper \u2500\u2500 */
    .map-section { margin-bottom: 12px; }
    .map-section-title {
      font-size: 13px; font-weight: 700; text-transform: uppercase;
      letter-spacing: 1.8px; color: #7a6338; margin-bottom: 14px;
      display: flex; align-items: center; gap: 10px;
    }
    .map-section-title::before, .map-section-title::after {
      content: ''; flex: 1; height: 1px;
      background: linear-gradient(90deg, transparent, #c9a96a, transparent);
    }

    /* \u2500\u2500 Outer container \u2500\u2500 */
    .map-outer {
      overflow-x: auto;
      border-radius: 20px;
      box-shadow: 0 12px 50px rgba(80,55,15,.22),
                  0 2px 8px rgba(80,55,15,.12),
                  inset 0 0 0 2px rgba(180,140,70,.35);
    }

    /* \u2500\u2500 Parchment canvas \u2500\u2500 */
    .map-canvas {
      position: relative;
      width: 100%; min-width: 700px;
      aspect-ratio: 2 / 1;
      /* Paper grain via SVG noise + warm corner gradients */
      background-color: #F5EFD7;
      background-image:
        radial-gradient(ellipse at 0%   0%,   rgba(120,80,20,.10) 0%, transparent 50%),
        radial-gradient(ellipse at 100% 0%,   rgba(120,80,20,.09) 0%, transparent 50%),
        radial-gradient(ellipse at 0%   100%, rgba(120,80,20,.11) 0%, transparent 50%),
        radial-gradient(ellipse at 100% 100%, rgba(120,80,20,.12) 0%, transparent 50%),
        radial-gradient(ellipse at 25% 75%, rgba(180,130,60,.14) 0%, transparent 55%),
        radial-gradient(ellipse at 75% 25%, rgba(160,110,50,.11) 0%, transparent 55%),
        url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.65' numOctaves='5' stitchTiles='stitch'/%3E%3CfeColorMatrix type='saturate' values='0'/%3E%3CfeComponentTransfer%3E%3CfeFuncA type='linear' slope='.11'/%3E%3C/feComponentTransfer%3E%3C/filter%3E%3Crect width='300' height='300' filter='url(%23n)'/%3E%3C/svg%3E");
      border-radius: 18px;
      overflow: hidden;
      /* Vignette via box-shadow inset */
      box-shadow: inset 0 0 80px rgba(139,100,40,.22),
                  inset 0 0 16px rgba(100,65,15,.15);
    }

    /* \u2500\u2500 SVG path overlay \u2500\u2500 */
    .map-svg {
      position: absolute; inset: 0;
      width: 100%; height: 100%;
      pointer-events: none; z-index: 1;
      filter: drop-shadow(0 2px 3px rgba(0,0,0,.12));
    }

    /* Base path style */
    .map-path {
      fill: none;
      stroke-linecap: round;
      stroke-linejoin: round;
    }

    /* DONE — solid thick line, green glow */
    .map-path.done {
      stroke: #27864e;
      stroke-width: 4;
      stroke-dasharray: none;
      opacity: .9;
      filter: drop-shadow(0 0 4px rgba(39,134,78,.5));
    }

    /* ACTIVE — amber dashed, ant-march animation */
    .map-path.active {
      stroke: #d97706;
      stroke-width: 3.5;
      stroke-dasharray: 10 7;
      opacity: 1;
      animation: ant-march .9s linear infinite;
      filter: drop-shadow(0 0 5px rgba(217,119,6,.5));
    }
    @keyframes ant-march { to { stroke-dashoffset: -34; } }

    /* WAITING — thin faded dashes */
    .map-path.waiting {
      stroke: #b5a07a;
      stroke-width: 2;
      stroke-dasharray: 7 9;
      opacity: .35;
    }

    /* Draw-on animation setup (overrides above for animating segment) */
    .map-path.draw {
      stroke-dasharray: 2000;
      stroke-dashoffset: 2000;
      transition: stroke-dashoffset 1.3s cubic-bezier(.4,0,.2,1);
    }
    /* After JS sets dashoffset to 0, restore proper visual styles */
    .map-path.done.drawn   { stroke-dasharray: none; stroke-dashoffset: 0; }
    .map-path.active.drawn { stroke-dasharray: 10 7; stroke-dashoffset: 0;
                             animation: ant-march .9s linear infinite; }
    .map-path.waiting.drawn{ stroke-dasharray: 7 9;  stroke-dashoffset: 0; }

    /* \u2500\u2500 Checkpoint nodes \u2500\u2500 */
    .map-checkpoint {
      position: absolute; z-index: 10;
      display: flex; flex-direction: column; align-items: center;
      transform: translate(-50%, -50%);
      cursor: default;
    }
    .map-checkpoint.clickable { cursor: pointer; }

    /* Base dot */
    .cp-dot {
      width: 72px; height: 72px; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 26px; color: #fff;
      position: relative;
      transition: transform .22s ease, box-shadow .22s ease;
    }

    /* DONE — double ring + green glow */
    .cp-dot.done {
      background: linear-gradient(140deg, #16a34a, #22c55e);
      box-shadow: 0 0 0 4px rgba(255,255,255,.7),
                  0 0 0 7px rgba(22,163,74,.35),
                  0 6px 20px rgba(22,163,74,.45);
    }
    .cp-dot.done:hover { transform: scale(1.1); }

    /* ACTIVE — teal + large pulse glow, slightly bigger */
    .cp-dot.active {
      background: linear-gradient(140deg, #083E40, #0e7070);
      box-shadow: 0 0 0 4px rgba(255,255,255,.65),
                  0 6px 20px rgba(8,62,64,.4);
      transform: scale(1.15);
      animation: cp-pulse-glow 2s ease-in-out infinite;
    }
    .cp-dot.active:hover { transform: scale(1.22); }
    @keyframes cp-pulse-glow {
      0%,100% { box-shadow: 0 0 0 4px rgba(255,255,255,.65), 0 0 0  0px rgba(20,184,166,.7), 0 6px 20px rgba(8,62,64,.4); }
      50%      { box-shadow: 0 0 0 4px rgba(255,255,255,.65), 0 0 0 14px rgba(20,184,166,0),   0 6px 20px rgba(8,62,64,.4); }
    }

    /* ACTIVE + PERINGATAN — amber pulse */
    .cp-dot.active.deadline-peringatan {
      background: linear-gradient(140deg, #D97706, #F59E0B);
      border: 3px solid #D97706;
      box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.2);
      animation: cp-pulse-warning 2s ease-in-out infinite;
    }
    @keyframes cp-pulse-warning {
      0%,100% { box-shadow: 0 4px 15px rgba(245,158,11,.4), 0 0 0  0px rgba(245,158,11,.6); }
      50%      { box-shadow: 0 4px 15px rgba(245,158,11,.4), 0 0 0 14px rgba(245,158,11,0); }
    }

    /* ACTIVE + TERLAMBAT — red intense pulse */
    .cp-dot.active.deadline-terlambat {
      background: linear-gradient(140deg, #DC2626, #EF4444);
      border: 3px solid #DC2626;
      box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.25);
      animation: cp-pulse-overdue 1.5s ease-in-out infinite;
    }
    @keyframes cp-pulse-overdue {
      0%,100% { box-shadow: 0 4px 20px rgba(239,68,68,.5), 0 0 0  0px rgba(239,68,68,.7); }
      50%      { box-shadow: 0 4px 20px rgba(239,68,68,.5), 0 0 0 16px rgba(239,68,68,0); }
    }

    /* Deadline time badges */
    .badge-warning-time {
      background-color: #FEF3C7; color: #92400E;
      border: 1px solid #F59E0B;
      font-size: 10px; font-weight: 700;
      padding: 2px 6px; border-radius: 4px;
      margin-top: 3px; display: inline-block;
    }
    .badge-overdue-time {
      background-color: #FEE2E2; color: #991B1B;
      border: 1px solid #EF4444;
      font-size: 10px; font-weight: 700;
      padding: 2px 6px; border-radius: 4px;
      margin-top: 3px; display: inline-block;
    }

    /* Historical deadline badges — smaller and softer */
    .badge-historis-peringatan {
      background-color: #FEF3C7; color: #B45309;
      border: 1px solid #FDE68A;
      font-size: 9px; font-weight: 600;
      padding: 1px 5px; border-radius: 4px;
      margin-top: 2px; display: inline-block;
      opacity: 0.85;
    }
    .badge-historis-terlambat {
      background-color: #FEE2E2; color: #B91C1C;
      border: 1px solid #FECACA;
      font-size: 9px; font-weight: 600;
      padding: 1px 5px; border-radius: 4px;
      margin-top: 2px; display: inline-block;
      opacity: 0.85;
    }

    /* RETURNED — amber glow */
    .cp-dot.returned {
      background: linear-gradient(140deg, #b45309, #f59e0b);
      box-shadow: 0 0 0 3px rgba(255,255,255,.6),
                  0 6px 18px rgba(180,83,9,.4);
    }

    /* WAITING — grayscale + dimmed */
    .cp-dot.waiting {
      background: linear-gradient(140deg, #9ca3af, #d1d5db);
      box-shadow: 0 4px 12px rgba(0,0,0,.12);
      opacity: .55;
      filter: grayscale(60%);
    }

    /* Hover lift for clickable nodes */
    .map-checkpoint.clickable .cp-dot:hover {
      transform: scale(1.12) translateY(-2px);
    }

    /* Checkmark mini badge */
    .cp-check {
      position: absolute; bottom: -3px; right: -3px;
      width: 22px; height: 22px; border-radius: 50%;
      background: #fff; color: #16a34a; font-size: 10px;
      display: flex; align-items: center; justify-content: center;
      box-shadow: 0 2px 8px rgba(0,0,0,.18);
      font-weight: 900; border: 1.5px solid rgba(22,163,74,.3);
    }

    /* Entry pop animation (staggered via nth-child) */
    .cp-dot {
      animation: cp-pop .5s cubic-bezier(.34,1.56,.64,1) both;
    }
    .map-checkpoint:nth-child(2)  .cp-dot { animation-delay: .30s; }
    .map-checkpoint:nth-child(3)  .cp-dot { animation-delay: .60s; }
    .map-checkpoint:nth-child(4)  .cp-dot { animation-delay: .90s; }
    .map-checkpoint:nth-child(5)  .cp-dot { animation-delay:1.20s; }
    .map-checkpoint:nth-child(6)  .cp-dot { animation-delay:1.50s; }
    @keyframes cp-pop {
      0%   { transform: scale(0) rotate(-20deg); opacity: 0; }
      100% { transform: scale(1) rotate(0deg);   opacity: 1; }
    }
    /* Preserve active scale after pop */
    .map-checkpoint .cp-dot.active { animation: cp-pop-active .5s cubic-bezier(.34,1.56,.64,1) both,
                                                cp-pulse-glow 2s ease-in-out 0.5s infinite; }
    @keyframes cp-pop-active {
      0%   { transform: scale(0) rotate(-20deg); opacity: 0; }
      100% { transform: scale(1.15) rotate(0deg); opacity: 1; }
    }

    /* \u2500\u2500 Label below dot \u2500\u2500 */
    .cp-label {
      margin-top: 10px; text-align: center; width: 130px;
    }
    .cp-name {
      display: inline-block;
      font-size: 13px; font-weight: 800; color: #2c1d08;
      line-height: 1.25; margin-bottom: 4px;
      /* frosted glass pill */
      background: rgba(255,255,255,.72);
      backdrop-filter: blur(4px);
      -webkit-backdrop-filter: blur(4px);
      border-radius: 8px; padding: 3px 9px;
      box-shadow: 0 1px 4px rgba(0,0,0,.08);
    }
    .cp-badge {
      display: inline-flex; align-items: center; gap: 4px;
      padding: 3px 9px; border-radius: 20px;
      font-size: 10px; font-weight: 700; text-transform: uppercase;
      letter-spacing: .5px;
      box-shadow: 0 1px 4px rgba(0,0,0,.12);
    }
    .cp-badge.done    { background: #dcfce7; color: #15803d;
                        border: 1px solid rgba(22,163,74,.25); }
    .cp-badge.active  { background: linear-gradient(135deg,#083E40,#0e9080);
                        color: #fff; border: none; }
    .cp-badge.returned{ background: #fef3c7; color: #92400e;
                        border: 1px solid rgba(180,83,9,.2); }
    .cp-badge.waiting { background: rgba(255,255,255,.6); color: #6b7280;
                        border: 1px solid rgba(0,0,0,.08); }
    .cp-date {
      display: inline-block; font-size: 10px; color: #6b563a;
      margin-top: 3px; line-height: 1.4;
      background: rgba(255,255,255,.6); border-radius: 6px;
      padding: 1px 6px;
    }

    /* \u2500\u2500 Tooltip \u2500\u2500 */
    .cp-tooltip {
      position: absolute; bottom: calc(100% + 16px); left: 50%;
      transform: translateX(-50%) translateY(8px);
      width: 230px;
      background: rgba(255,253,244,.97);
      border: 1px solid #d9c89a;
      border-radius: 14px;
      padding: 13px 15px;
      box-shadow: 0 10px 32px rgba(80,55,15,.2);
      opacity: 0; pointer-events: none;
      transition: opacity .18s ease, transform .18s ease;
      z-index: 99; font-size: 12px; text-align: left;
    }
    .map-checkpoint:hover .cp-tooltip {
      opacity: 1; transform: translateX(-50%) translateY(0);
      pointer-events: auto;
    }
    .cp-tooltip::after {
      content: ''; position: absolute; top: 100%; left: 50%;
      transform: translateX(-50%);
      border: 7px solid transparent;
      border-top-color: rgba(255,253,244,.97);
    }
    .tt-name { font-weight: 800; color: #1a0f00; margin-bottom: 7px; font-size: 13.5px; }
    .tt-row  { display: flex; gap: 7px; align-items: flex-start;
               margin-bottom: 4px; color: #4b3b22; line-height: 1.45; }
    .tt-row i { color: #7a5c2e; margin-top: 2px; flex-shrink: 0; }

    /* \u2500\u2500 Decorative elements \u2500\u2500 */
    .map-deco {
      position: absolute; pointer-events: none; z-index: 3;
    }

    /* Compass — slow rotate */
    .map-compass {
      top: 10px; right: 12px; opacity: .65;
      animation: compass-spin 20s linear infinite;
      transform-origin: center center;
    }
    @keyframes compass-spin {
      /* Only rotate the needle, not the ring — done via SVG group inside */
      to { } /* noop — the inner SVG <g> handles the needle */
    }
    /* We'll rotate the whole compass slowly */
    .map-compass svg { animation: compass-slow 60s linear infinite; }
    @keyframes compass-slow {
      from { transform: rotate(0deg); }
      to   { transform: rotate(360deg); }
    }

    /* Start marker */
    .map-start-marker {
      font-size: 11px; font-weight: 800;
      color: #3d2600;
      background: rgba(255,245,220,.9);
      padding: 4px 10px; border-radius: 8px;
      border: 1.5px solid #c9a050;
      white-space: nowrap;
      box-shadow: 0 2px 6px rgba(0,0,0,.1);
      backdrop-filter: blur(2px);
    }

    /* Finish flag */
    .map-finish-flag {
      font-size: 24px; line-height: 1;
      filter: drop-shadow(0 2px 3px rgba(0,0,0,.2));
    }

    /* Filler icons */
    .map-filler-icon {
      opacity: .18; font-size: 22px; color: #4a3010;
      filter: sepia(40%);
    }

    /* \u2500\u2500 Detail panel \u2500\u2500 */
    #cpDetailPanel {
      display: none; margin-top: 16px;
      background: #fff; border: 1px solid #e2d9c0;
      border-radius: 16px; padding: 20px 24px;
      box-shadow: 0 4px 20px rgba(80,60,20,.10);
      animation: panel-in .25s ease;
    }
    @keyframes panel-in {
      from { opacity:0; transform:translateY(-8px); }
      to   { opacity:1; transform:translateY(0); }
    }
    #cpDetailPanel.open { display: block; }
    .cp-detail-header {
      display: flex; justify-content: space-between; align-items: center;
      margin-bottom: 16px; padding-bottom: 12px;
      border-bottom: 1px solid #f1ead9;
    }
    .cp-detail-title { font-size: 16px; font-weight: 800; color: #0f172a; }
    .cp-detail-close {
      width: 28px; height: 28px; border-radius: 8px;
      background: #f1f5f9; border: none; cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      color: #64748b; font-size: 13px;
    }
    .cp-detail-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px,1fr));
      gap: 16px;
    }
    .cp-detail-item label {
      font-size: 10px; font-weight: 700; text-transform: uppercase;
      letter-spacing: .8px; color: #94a3b8; display: block; margin-bottom: 2px;
    }
    .cp-detail-item span { font-size: 14px; font-weight: 600; color: #0f172a; }

    /* \u2500\u2500 Mobile fallback \u2500\u2500 */
    .map-mobile-fallback { display: none; }
    @media (max-width: 640px) {
      .map-outer { display: none; }
      .map-mobile-fallback { display: block; }
      .mob-stage { display: flex; gap: 16px; margin-bottom: 20px; align-items: flex-start; }
      .mob-dot {
        width: 44px; height: 44px; border-radius: 50%;
        display:flex; align-items:center; justify-content:center;
        color:#fff; font-size:18px; flex-shrink:0;
      }
      .mob-dot.done    { background: #16a34a; }
      .mob-dot.active  { background: #083E40; }
      .mob-dot.returned{ background: #b45309; }
      .mob-dot.waiting { background: #9ca3af; }
      .mob-info { flex: 1; }
      .mob-name { font-weight: 800; color: #0f172a; font-size: 15px; }
      .mob-desc { font-size: 13px; color: #475569; margin-top: 2px; }
      .mob-date { font-size: 12px; color: #64748b; margin-top: 4px; }
    }

      padding-left: 0;
      animation: fadeInUp 0.6s ease-out;
      animation-fill-mode: both;
    }

    @keyframes fadeInUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .timeline-stage:nth-child(1) {
      animation-delay: 0.1s;
    }

    .timeline-stage:nth-child(2) {
      animation-delay: 0.2s;
    }

    .timeline-stage:nth-child(3) {
      animation-delay: 0.3s;
    }

    .timeline-stage:nth-child(4) {
      animation-delay: 0.4s;
    }

    .timeline-stage:nth-child(5) {
      animation-delay: 0.5s;
    }

    /* Stage Icon/Node */
    .timeline-node {
      position: relative;
      z-index: 10;
      flex-shrink: 0;
      width: 80px;
      height: 80px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 32px;
      font-weight: bold;
      color: white;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .timeline-node.completed {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      box-shadow: 0 8px 24px rgba(16, 185, 129, 0.4);
    }

    .timeline-node.active {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%);
      box-shadow: 0 0 0 8px rgba(8, 62, 64, 0.1), 0 12px 32px rgba(8, 62, 64, 0.3);
      animation: pulse-ring 2s ease-out infinite;
    }

    @keyframes pulse-ring {
      0% {
        box-shadow: 0 0 0 0 rgba(8, 62, 64, 0.4), 0 12px 32px rgba(8, 62, 64, 0.3);
      }

      50% {
        box-shadow: 0 0 0 12px rgba(8, 62, 64, 0), 0 12px 32px rgba(8, 62, 64, 0.3);
      }

      100% {
        box-shadow: 0 0 0 0 rgba(8, 62, 64, 0), 0 12px 32px rgba(8, 62, 64, 0.3);
      }
    }

    .timeline-node.pending {
      background: linear-gradient(135deg, #cbd5e1 0%, #94a3b8 100%);
      color: #64748b;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .timeline-node.returned {
      background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
      box-shadow: 0 8px 24px rgba(245, 158, 11, 0.4);
    }

    /* Stage Content Card */
    .timeline-content {
      flex: 1;
      background: white;
      border-radius: 20px;
      padding: 28px;
      box-shadow: 0 4px 20px rgba(8, 62, 64, 0.08);
      border: 2px solid #e2e8f0;
      transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      overflow: hidden;
    }

    .timeline-content::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
      background: linear-gradient(180deg, #083E40 0%, #889717 100%);
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .timeline-stage.active .timeline-content {
      border-color: #083E40;
      box-shadow: 0 8px 32px rgba(8, 62, 64, 0.15);
      transform: translateX(8px);
    }

    .timeline-stage .timeline-content:has(.stage-overdue-info) {
      border-color: #ef4444;
      border-width: 2px;
    }

    .timeline-stage.active .timeline-content::before {
      opacity: 1;
    }

    .timeline-stage.completed .timeline-content {
      border-color: #10b981;
      background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
    }

    .timeline-stage.returned .timeline-content {
      border-color: #f59e0b;
      background: linear-gradient(135deg, #fffbeb 0%, #ffffff 100%);
    }

    /* Deadline Status Colored Outlines - Based on received_at count up */
    .timeline-content.deadline-aman {
      border-color: #10b981 !important;
      box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15), 0 4px 20px rgba(8, 62, 64, 0.08);
    }

    .timeline-content.deadline-aman::before {
      background: linear-gradient(180deg, #10b981 0%, #059669 100%) !important;
      opacity: 1 !important;
    }

    .timeline-content.deadline-aman::after {
      content: '';
      position: absolute;
      top: 0;
      right: 0;
      width: 8px;
      height: 100%;
      background: linear-gradient(180deg, #10b981 0%, #059669 100%);
      border-radius: 0 18px 18px 0;
    }

    .timeline-content.deadline-peringatan {
      border-color: #f59e0b !important;
      box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2), 0 4px 20px rgba(8, 62, 64, 0.08);
    }

    .timeline-content.deadline-peringatan::before {
      background: linear-gradient(180deg, #f59e0b 0%, #d97706 100%) !important;
      opacity: 1 !important;
    }

    .timeline-content.deadline-peringatan::after {
      content: '';
      position: absolute;
      top: 0;
      right: 0;
      width: 8px;
      height: 100%;
      background: linear-gradient(180deg, #f59e0b 0%, #d97706 100%);
      border-radius: 0 18px 18px 0;
    }

    .timeline-content.deadline-terlambat {
      border-color: #ef4444 !important;
      box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2), 0 4px 20px rgba(239, 68, 68, 0.15);
      animation: pulse-deadline-warning 2s ease-in-out infinite;
    }

    .timeline-content.deadline-terlambat::before {
      background: linear-gradient(180deg, #ef4444 0%, #dc2626 100%) !important;
      opacity: 1 !important;
    }

    .timeline-content.deadline-terlambat::after {
      content: '';
      position: absolute;
      top: 0;
      right: 0;
      width: 8px;
      height: 100%;
      background: linear-gradient(180deg, #ef4444 0%, #dc2626 100%);
      border-radius: 0 18px 18px 0;
    }

    @keyframes pulse-deadline-warning {
      0%, 100% {
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.2), 0 4px 20px rgba(239, 68, 68, 0.15);
      }
      50% {
        box-shadow: 0 0 0 6px rgba(239, 68, 68, 0.15), 0 4px 20px rgba(239, 68, 68, 0.25);
      }
    }

    /* Stage Header */
    .stage-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 16px;
    }

    .stage-label {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1.5px;
      color: #94a3b8;
      margin-bottom: 4px;
    }

    .stage-name {
      font-size: 24px;
      font-weight: 700;
      color: #0f172a;
      margin-bottom: 8px;
    }

    .stage-status-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 14px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .stage-status-badge.active {
      background: linear-gradient(135deg, #083E40 0%, #889717 100%);
      color: white;
      box-shadow: 0 4px 12px rgba(8, 62, 64, 0.3);
    }

    .stage-status-badge.completed {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      color: white;
    }

    .stage-status-badge.returned {
      background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
      color: white;
    }

    .stage-status-badge.pending {
      background: #e2e8f0;
      color: #64748b;
    }

    /* Stage Description */
    .stage-description {
      font-size: 15px;
      color: #475569;
      line-height: 1.6;
      margin-bottom: 16px;
    }

    /* Stage Timestamp */
    .stage-timestamp {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 13px;
      color: #64748b;
      padding-top: 16px;
      border-top: 1px solid #e2e8f0;
    }

    /* Stage Duration */
    .stage-duration {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 13px;
      color: #6366f1;
      margin-top: 10px;
      padding: 8px 12px;
      background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
      border-radius: 8px;
      border: 1px solid #c7d2fe;
    }

    .stage-duration i {
      font-size: 14px;
    }

    .stage-duration strong {
      color: #4f46e5;
    }

    /* Return/Cycle Info */

    .stage-overdue-info {
      margin-top: 16px;
      padding: 16px;
      background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
      border-radius: 12px;
      border: 2px solid #ef4444;
      animation: pulse-overdue 2s ease-in-out infinite;
    }

    @keyframes pulse-overdue {

      0%,
      100% {
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4);
      }

      50% {
        box-shadow: 0 0 0 8px rgba(239, 68, 68, 0);
      }
    }

    .stage-overdue-info p {
      font-size: 14px;
      color: #991b1b;
      margin: 0;
      font-weight: 600;
      display: flex;
      align-items: center;
    }

    .stage-overdue-info .fas {
      color: #dc2626;
    }

    .overdue-deadline {
      font-size: 12px;
      color: #7f1d1d;
      font-weight: 400;
      margin-left: 8px;
    }

    .stage-return-info {
      margin-top: 16px;
      padding: 16px;
      background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
      border-radius: 12px;
      border: 1px solid #fbbf24;
    }

    .stage-return-info p {
      font-size: 13px;
      color: #92400e;
      margin: 4px 0;
    }

    /* Deadline Level Indicators - Color coded borders */
    .timeline-content.deadline-aman {
      border-color: #10b981 !important;
      box-shadow: 0 4px 20px rgba(8, 62, 64, 0.08), 0 0 0 3px rgba(16, 185, 129, 0.15);
    }

    .timeline-content.deadline-peringatan {
      border-color: #f59e0b !important;
      box-shadow: 0 4px 20px rgba(8, 62, 64, 0.08), 0 0 0 3px rgba(245, 158, 11, 0.2);
      animation: pulse-warning 2s ease-in-out infinite;
    }

    .timeline-content.deadline-terlambat {
      border-color: #ef4444 !important;
      box-shadow: 0 4px 20px rgba(8, 62, 64, 0.08), 0 0 0 3px rgba(239, 68, 68, 0.25);
    }

    @keyframes pulse-warning {
      0%, 100% {
        box-shadow: 0 4px 20px rgba(8, 62, 64, 0.08), 0 0 0 3px rgba(245, 158, 11, 0.2);
      }
      50% {
        box-shadow: 0 4px 20px rgba(8, 62, 64, 0.08), 0 0 0 5px rgba(245, 158, 11, 0.3);
      }
    }

    /* Deadline level small indicator badge */
    .deadline-indicator {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 4px 10px;
      border-radius: 12px;
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-left: 8px;
    }

    .deadline-indicator.aman {
      background: rgba(16, 185, 129, 0.15);
      color: #059669;
    }

    .deadline-indicator.peringatan {
      background: rgba(245, 158, 11, 0.15);
      color: #d97706;
    }

    .deadline-indicator.terlambat {
      background: rgba(239, 68, 68, 0.15);
      color: #dc2626;
    }

    /* Information Grid */
    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 24px;
      margin-top: 48px;
    }

    .info-card {
      background: white;
      border-radius: 20px;
      padding: 28px;
      box-shadow: 0 4px 20px rgba(8, 62, 64, 0.08);
      border: 1px solid rgba(8, 62, 64, 0.1);
    }

    .info-card-header {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 20px;
      padding-bottom: 16px;
      border-bottom: 2px solid #f1f5f9;
    }

    .info-card-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      color: white;
    }

    .info-card-title {
      font-size: 18px;
      font-weight: 700;
      color: #0f172a;
    }

    /* Hero Financial Card */
    .hero-financial-card {
      grid-column: 1 / -1;
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%);
      border-radius: 24px;
      padding: 40px;
      color: white;
      position: relative;
      overflow: hidden;
    }

    .hero-financial-card::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
      animation: rotate 20s linear infinite;
    }

    @keyframes rotate {
      from {
        transform: rotate(0deg);
      }

      to {
        transform: rotate(360deg);
      }
    }

    .hero-financial-content {
      position: relative;
      z-index: 10;
    }

    .hero-financial-label {
      font-size: 14px;
      font-weight: 600;
      color: rgba(255, 255, 255, 0.8);
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-bottom: 12px;
    }

    .hero-financial-value {
      font-size: 48px;
      font-weight: 800;
      margin-bottom: 32px;
      letter-spacing: -1px;
    }

    .hero-financial-details {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 24px;
      padding-top: 24px;
      border-top: 1px solid rgba(255, 255, 255, 0.2);
    }

    .hero-detail-item {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      padding: 16px;
      border-radius: 12px;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .hero-detail-label {
      font-size: 12px;
      font-weight: 600;
      color: rgba(255, 255, 255, 0.7);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 8px;
    }

    .hero-detail-value {
      font-size: 18px;
      font-weight: 700;
    }

    /* Activity Logs */
    .activity-log-container {
      max-height: 400px;
      overflow-y: auto;
      padding-right: 8px;
    }

    .activity-log-item {
      position: relative;
      padding-left: 32px;
      padding-bottom: 20px;
      padding: 12px 12px 12px 32px;
      border-left: 2px solid #e2e8f0;
      cursor: pointer;
      transition: all 0.2s ease;
      border-radius: 8px;
      margin-bottom: 8px;
    }

    .activity-log-item:hover {
      background: #f8fafc;
      border-left-color: #3b82f6;
      transform: translateX(4px);
    }

    .activity-log-item:last-child {
      border-left: 2px solid #e2e8f0;
    }

    .activity-log-item::before {
      content: '';
      position: absolute;
      left: -6px;
      top: 16px;
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background: #083E40;
      border: 3px solid white;
      box-shadow: 0 0 0 2px #e2e8f0;
      transition: all 0.2s ease;
    }

    .activity-log-item:hover::before {
      background: #3b82f6;
      transform: scale(1.2);
    }

    .activity-log-text {
      font-size: 14px;
      color: #0f172a;
      font-weight: 500;
      margin-bottom: 4px;
    }

    .activity-log-time {
      font-size: 12px;
      color: #64748b;
    }

    /* Clickable Card */
    .info-card.clickable {
      cursor: pointer;
      transition: all 0.3s ease;
      position: relative;
      user-select: none;
    }

    .info-card.clickable:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 30px rgba(8, 62, 64, 0.15);
      border-color: rgba(8, 62, 64, 0.3);
    }

    .info-card.clickable:active {
      transform: translateY(-2px);
    }

    .info-card.clickable * {
      pointer-events: none;
    }

    .info-card.clickable {
      pointer-events: auto;
    }

    /* Modern Modal Popup */
    .modal-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.6);
      backdrop-filter: blur(8px);
      z-index: 9999;
      opacity: 0;
      transition: opacity 0.3s ease;
    }

    .modal-overlay.show {
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 1;
    }

    .modal-container {
      background: white;
      border-radius: 24px;
      max-width: 900px;
      width: 90%;
      max-height: 90vh;
      overflow: hidden;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      transform: scale(0.9) translateY(20px);
      transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      display: flex;
      flex-direction: column;
    }

    .modal-overlay.show .modal-container {
      transform: scale(1) translateY(0);
    }

    .modal-header {
      background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%);
      padding: 24px 32px;
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: relative;
      overflow: hidden;
    }

    .modal-header::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -50%;
      width: 200%;
      height: 200%;
      background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
      animation: rotate 20s linear infinite;
    }

    .modal-header-content {
      position: relative;
      z-index: 10;
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .modal-header-icon {
      width: 56px;
      height: 56px;
      border-radius: 16px;
      background: rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(10px);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
    }

    .modal-header-text h2 {
      font-size: 24px;
      font-weight: 800;
      margin: 0;
      margin-bottom: 4px;
    }

    .modal-header-text p {
      font-size: 14px;
      opacity: 0.9;
      margin: 0;
    }

    .modal-close {
      position: relative;
      z-index: 10;
      width: 40px;
      height: 40px;
      border-radius: 12px;
      background: rgba(255, 255, 255, 0.2);
      backdrop-filter: blur(10px);
      border: none;
      color: white;
      font-size: 20px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
    }

    .modal-close:hover {
      background: rgba(255, 255, 255, 0.3);
      transform: rotate(90deg);
    }

    .modal-body {
      padding: 32px;
      overflow-y: auto;
      flex: 1;
      background: #f8faf9;
    }

    .modal-section {
      background: white;
      border-radius: 16px;
      padding: 24px;
      margin-bottom: 20px;
      box-shadow: 0 2px 8px rgba(8, 62, 64, 0.05);
      border: 1px solid rgba(8, 62, 64, 0.1);
    }

    .modal-section-title {
      font-size: 16px;
      font-weight: 700;
      color: #083E40;
      margin-bottom: 20px;
      padding-bottom: 12px;
      border-bottom: 2px solid #f1f5f9;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .modal-section-title i {
      font-size: 18px;
    }

    .modal-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
    }

    .modal-field {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }

    .modal-field-label {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: #94a3b8;
    }

    .modal-field-value {
      font-size: 15px;
      font-weight: 600;
      color: #0f172a;
      word-break: break-word;
    }

    .modal-field-value.monospace {
      font-family: 'Courier New', monospace;
      font-size: 14px;
    }

    .modal-field-value.highlight {
      color: #083E40;
      font-weight: 700;
    }

    .modal-field-value.empty {
      color: #cbd5e1;
      font-style: italic;
    }

    /* Tax Specific Styles */
    .tax-summary-card {
      background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
      border: 2px solid #86efac;
      border-radius: 16px;
      padding: 20px;
      margin-bottom: 20px;
    }

    .tax-summary-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 0;
      border-bottom: 1px solid rgba(8, 62, 64, 0.1);
    }

    .tax-summary-row:last-child {
      border-bottom: none;
    }

    .tax-summary-label {
      font-size: 14px;
      font-weight: 600;
      color: #059669;
    }

    .tax-summary-value {
      font-size: 16px;
      font-weight: 700;
      color: #083E40;
      font-family: 'Courier New', monospace;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .workflow-container {
        padding: 20px 16px;
      }

      .timeline-stage {
        flex-direction: column;
        gap: 20px;
      }

      .timeline-line,
      .timeline-line-progress {
        left: 20px;
      }

      .timeline-node {
        width: 60px;
        height: 60px;
        font-size: 24px;
      }

      .timeline-content {
        padding: 20px;
      }

      .stage-name {
        font-size: 20px;
      }

      .hero-financial-value {
        font-size: 36px;
      }

      .modal-container {
        width: 95%;
        max-height: 95vh;
      }

      .modal-header {
        padding: 20px 24px;
      }

      .modal-body {
        padding: 24px 20px;
      }

      .modal-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>

  <div class="workflow-container">
    {{-- Header --}}
    <div class="workflow-header">
      <div class="workflow-header-top">
        <div class="workflow-title-section">
          <h1>Workflow Tracking</h1>
          <div class="document-info">
            <i class="fas fa-file-invoice text-emerald-500"></i>
            <span>Memantau perjalanan dokumen <strong>{{ $dokumen->nomor_spp ?? $dokumen->nomor_agenda }}</strong></span>
          </div>
        </div>
        <div class="workflow-header-actions">
          <a href="{{ $dashboardUrl ?? '/owner/dashboard' }}" class="btn-back">
            <i class="fas fa-arrow-left"></i>
            Kembali
          </a>
      </div>
    </div>

    {{-- ═══ TREASURE MAP JOURNEY ═══ --}}
    @php
      /* Build stage data for JS in a clean JSON format */
      $stagePositions = [
        ['left'=>'8','top'=>'72'],
        ['left'=>'28','top'=>'22'],
        ['left'=>'50','top'=>'68'],
        ['left'=>'70','top'=>'18'],
        ['left'=>'88','top'=>'68'],
      ];
      $jsStages = [];
      foreach($workflowStages as $idx => $stage) {
        $st = $stage['status'] ?? 'pending';
        $isComp = ($st==='completed'||$st==='selesai');
        $isAct  = ($st==='processing'||$st==='active');
        $isRet  = ($st==='returned');
        $stateKey = $isComp ? 'done' : ($isAct ? 'active' : ($isRet ? 'returned' : 'waiting'));
        $dlLevel = $stage['deadlineLevel'] ?? null;
        $dlInfo  = $stage['deadlineInfo'] ?? null;
        $dlHours = $dlInfo['hours_elapsed'] ?? 0;
        $dlDays  = floor($dlHours / 24);
        $dlRemH  = round($dlHours - ($dlDays * 24));
        $dlReceivedAt = isset($dlInfo['received_at']) ? \Carbon\Carbon::parse($dlInfo['received_at'])->format('d M Y, H:i') : null;

        $jsStages[] = [
          'name'          => $stage['name'] ?? 'Tahap '.($idx+1),
          'description'   => $stage['description'] ?? '',
          'state'         => $stateKey,
          'timestamp'     => !empty($stage['timestamp']) ? \Carbon\Carbon::parse($stage['timestamp'])->format('d M Y, H:i') : null,
          'duration'      => $stage['duration']['display'] ?? null,
          'icon'          => $stage['icon'] ?? 'fa-circle',
          'badgeTxt'      => $isComp?'Selesai':($isAct?'Sedang Diproses':($isRet?'Dikembalikan':'Menunggu')),
          'isOverdue'     => $stage['isOverdue'] ?? false,
          'clickable'     => $isComp || $isAct,
          'left'          => $stagePositions[$idx]['left'] ?? '50',
          'top'           => $stagePositions[$idx]['top']  ?? '50',
          'deadlineLevel' => $dlLevel,
          'deadlineHours' => round($dlHours),
          'deadlineDays'  => $dlDays,
          'deadlineReceivedAt' => $dlReceivedAt,
          'isHistorical'  => ($dlInfo['is_historical'] ?? false) ? true : false,
        ];
      }
    @endphp

    <div class="map-section">
      <div class="map-section-title">🗺️ Peta Perjalanan Dokumen</div>

      {{-- DESKTOP: SVG Treasure Map --}}
      <div class="map-outer">
        <div class="map-canvas" id="mapCanvas">

          {{-- SVG path layer --}}
          <svg class="map-svg" id="mapSvg" viewBox="0 0 1000 500" preserveAspectRatio="none">
            @php
              $coords = [];
              foreach($jsStages as $s) {
                $coords[] = ['x' => (float)$s['left'] * 10, 'y' => (float)$s['top'] * 5];
              }
              /* Determine state for each path segment */
              $stateList = array_column($jsStages, 'state');
            @endphp
            @for($pi = 0; $pi < count($coords)-1; $pi++)
              @php
                $x1 = $coords[$pi]['x'];
                $y1 = $coords[$pi]['y'];
                $x2 = $coords[$pi+1]['x'];
                $y2 = $coords[$pi+1]['y'];
                $cx1 = $x1 + ($x2-$x1)*0.5;
                $cy1 = $y1;
                $cx2 = $x1 + ($x2-$x1)*0.5;
                $cy2 = $y2;
                $segState = ($stateList[$pi]==='done') ? 'done' :
                            ($stateList[$pi]==='active' ? 'active' :
                            ($stateList[$pi]==='returned' ? 'done' : 'waiting'));
              @endphp
              <path id="seg-{{ $pi }}"
                class="map-path draw {{ $segState }}"
                d="M {{ $x1 }} {{ $y1 }} C {{ $cx1 }} {{ $cy1 }}, {{ $cx2 }} {{ $cy2 }}, {{ $x2 }} {{ $y2 }}"
              />
            @endfor
          </svg>

          {{-- Checkpoints --}}
          @foreach($jsStages as $ci => $stage)
            @php
              $canClick = $stage['clickable'];
            @endphp
            <div class="map-checkpoint {{ $canClick?'clickable':'' }}"
              style="left:{{ $stage['left'] }}%; top:{{ $stage['top'] }}%;"
              @if($canClick) onclick="openCpDetail({{ $ci }})" @endif
              onmouseenter="showMapTip({{ $ci }}, this)"
              onmouseleave="hideMapTip()"
              data-ci="{{ $ci }}">

              {{-- Icon dot --}}
              @php
                $dlCssClass = '';
                if ($stage['state'] === 'active' && !empty($stage['deadlineLevel'])) {
                  if ($stage['deadlineLevel'] === 'peringatan') $dlCssClass = 'deadline-peringatan';
                  elseif ($stage['deadlineLevel'] === 'terlambat') $dlCssClass = 'deadline-terlambat';
                }
              @endphp
              <div class="cp-dot {{ $stage['state'] }} {{ $dlCssClass }}">
                @if($stage['state']==='done')
                  <i class="fas fa-check" style="font-size:26px"></i>
                @elseif($stage['state']==='returned')
                  <i class="fas fa-undo-alt"></i>
                @else
                  @php
                    $rawIcon = $stage['icon'];
                    $faIcon = strpos($rawIcon,'fa-')===0 ? 'fas '.$rawIcon : 'fas fa-'.$rawIcon;
                  @endphp
                  <i class="{{ $faIcon }}"></i>
                @endif
                @if($stage['state']==='done')
                  <span class="cp-check"><i class="fas fa-check"></i></span>
                @endif
              </div>

              {{-- Label --}}
              <div class="cp-label">
                <div class="cp-name">{{ $stage['name'] }}</div>
                <span class="cp-badge {{ $stage['state'] }}">{{ $stage['badgeTxt'] }}</span>
                @if($stage['state'] === 'active' && !empty($stage['deadlineLevel']) && $stage['deadlineLevel'] === 'peringatan')
                  <span class="badge-warning-time">⚠️ PERINGATAN</span>
                @elseif($stage['state'] === 'active' && !empty($stage['deadlineLevel']) && $stage['deadlineLevel'] === 'terlambat')
                  <span class="badge-overdue-time">🔴 TERLAMBAT</span>
                @elseif($stage['state'] === 'done' && !empty($stage['deadlineLevel']) && $stage['deadlineLevel'] === 'peringatan' && ($stage['isHistorical'] ?? false))
                  <span class="badge-historis-peringatan">⚠️ PERINGATAN SAAT ITU</span>
                @elseif($stage['state'] === 'done' && !empty($stage['deadlineLevel']) && $stage['deadlineLevel'] === 'terlambat' && ($stage['isHistorical'] ?? false))
                  <span class="badge-historis-terlambat">🔴 TERLAMBAT</span>
                @endif
                @if($stage['timestamp'])
                  <div class="cp-date"><i class="far fa-clock"></i> {{ $stage['timestamp'] }}</div>
                @endif
              </div>
            </div>
          @endforeach


          {{-- Compass decoration --}}
          <div class="map-deco map-compass">
            <svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg" opacity=".7">
              <circle cx="26" cy="26" r="24" stroke="#8a7555" stroke-width="1.5"/>
              <circle cx="26" cy="26" r="3" fill="#8a7555"/>
              <polygon points="26,4 29,24 26,22 23,24" fill="#c0392b"/>
              <polygon points="26,48 29,28 26,30 23,28" fill="#8a7555"/>
              <polygon points="4,26 24,23 22,26 24,29" fill="#8a7555"/>
              <polygon points="48,26 28,23 30,26 28,29" fill="#8a7555"/>
              <text x="26" y="11" text-anchor="middle" font-size="7" fill="#c0392b" font-weight="800" font-family="serif">N</text>
            </svg>
          </div>

          {{-- Start marker --}}
          <div class="map-deco map-start-marker" style="left:1.5%; bottom:10%;">
            ➤ Mulai
          </div>

          {{-- Finish flag --}}
          <div class="map-deco map-finish-flag" style="right:1.5%; bottom:10%;">
            🏁
          </div>

          {{-- Filler icons --}}
          <div class="map-deco map-filler-icon" style="left:38%; top:40%;"><i class="fas fa-mountain"></i></div>
          <div class="map-deco map-filler-icon" style="left:61%; top:43%;"><i class="fas fa-tree"></i></div>
          <div class="map-deco map-filler-icon" style="left:18%; top:48%;"><i class="fas fa-star"></i></div>

        </div>{{-- /map-canvas --}}
      </div>{{-- /map-outer --}}

      {{-- Shared tooltip (outside map-canvas to escape overflow:hidden) --}}
      <div id="mapSharedTooltip" style="
        position: fixed;
        z-index: 9999;
        pointer-events: none;
        display: none;
        background: rgba(255,253,244,.97);
        border: 1px solid #d9c89a;
        border-radius: 14px;
        padding: 13px 15px;
        box-shadow: 0 10px 32px rgba(80,55,15,.22);
        font-size: 12px;
        text-align: left;
        max-width: 240px;
        transition: opacity .15s ease;
      "></div>

      {{-- Detail panel --}}
      <div id="cpDetailPanel">
        <div class="cp-detail-header">
          <div class="cp-detail-title" id="cpDetailTitle">Detail Tahap</div>
          <button class="cp-detail-close" onclick="closeCpDetail()"><i class="fas fa-times"></i></button>
        </div>
        <div class="cp-detail-grid" id="cpDetailGrid"></div>
      </div>

      {{-- MOBILE FALLBACK --}}
      <div class="map-mobile-fallback">
        @foreach($jsStages as $stage)
          <div class="mob-stage">
            <div class="mob-dot {{ $stage['state'] }}">
              @if($stage['state']==='done')<i class="fas fa-check"></i>
              @elseif($stage['state']==='returned')<i class="fas fa-undo-alt"></i>
              @else
                @php $faIcon2 = strpos($stage['icon'],'fa-')===0 ? 'fas '.$stage['icon'] : 'fas fa-'.$stage['icon']; @endphp
                <i class="{{ $faIcon2 }}"></i>
              @endif
            </div>
            <div class="mob-info">
              <div class="mob-name">{{ $stage['name'] }}</div>
              <div class="mob-desc">{{ $stage['description'] }}</div>
              @if($stage['timestamp'])<div class="mob-date"><i class="far fa-clock"></i> {{ $stage['timestamp'] }}</div>@endif
            </div>
          </div>
        @endforeach
      </div>
    </div>{{-- /map-section --}}

    {{-- JS stage data for detail panel --}}
    <script>
    var MAP_STAGES = @json($jsStages);

    /* ── Shared Tooltip helpers ── */
    var _tipEl = null;
    function showMapTip(idx, node) {
      var s = MAP_STAGES[idx];
      if (!s) return;
      if (!_tipEl) _tipEl = document.getElementById('mapSharedTooltip');

      /* Build html */
      var html = '<div style="font-weight:800;color:#1a0f00;margin-bottom:6px;font-size:13px">' + s.name + '</div>';
      html += tipRow('fas fa-tag', s.badgeTxt);
      if (s.description) html += tipRow('fas fa-info-circle', s.description);
      if (s.timestamp)   html += tipRow('far fa-clock',       s.timestamp);
      if (s.duration)    html += tipRow('fas fa-stopwatch',   s.duration);
      /* Deadline status info */
      if (s.deadlineLevel && !s.isHistorical) {
        /* Active/real-time status */
        if (s.deadlineLevel === 'aman') {
          html += '<div style="display:flex;gap:6px;align-items:flex-start;margin-bottom:3px;color:#16a34a"><i class="fas fa-check-circle" style="margin-top:2px;flex-shrink:0"></i><span>⏱️ Status Waktu: AMAN</span></div>';
        } else if (s.deadlineLevel === 'peringatan') {
          html += '<div style="display:flex;gap:6px;align-items:flex-start;margin-bottom:3px;color:#d97706"><i class="fas fa-exclamation-triangle" style="margin-top:2px;flex-shrink:0"></i><span>⏱️ Status Waktu: ⚠️ PERINGATAN</span></div>';
        } else if (s.deadlineLevel === 'terlambat') {
          html += '<div style="display:flex;gap:6px;align-items:flex-start;margin-bottom:3px;color:#dc2626"><i class="fas fa-exclamation-circle" style="margin-top:2px;flex-shrink:0"></i><span>⏱️ Status Waktu: 🔴 TERLAMBAT</span></div>';
        }
      } else if (s.deadlineLevel && s.isHistorical) {
        /* Historical status for completed nodes */
        if (s.deadlineLevel === 'peringatan') {
          html += '<div style="display:flex;gap:6px;align-items:flex-start;margin-bottom:3px;color:#b45309;opacity:.85"><i class="fas fa-exclamation-triangle" style="margin-top:2px;flex-shrink:0"></i><span>⚠️ Dokumen peringatan saat diproses di role ini</span></div>';
        } else if (s.deadlineLevel === 'terlambat') {
          html += '<div style="display:flex;gap:6px;align-items:flex-start;margin-bottom:3px;color:#b91c1c;opacity:.85"><i class="fas fa-exclamation-circle" style="margin-top:2px;flex-shrink:0"></i><span>🔴 Dokumen terlambat saat diproses di role ini</span></div>';
        }
      }
      if (s.deadlineReceivedAt) {
        var agoText = '';
        if (s.deadlineDays > 0) agoText = s.deadlineDays + ' hari';
        if (s.deadlineHours && (s.deadlineHours - (s.deadlineDays||0)*24) > 0) agoText += (agoText ? ' ' : '') + Math.round(s.deadlineHours - (s.deadlineDays||0)*24) + ' jam';
        if (agoText) agoText += ' yang lalu';
        html += tipRow('fas fa-calendar-alt', '📅 Diterima: ' + agoText);
      }
      if (s.isOverdue && !s.deadlineLevel) html += '<div style="display:flex;gap:6px;align-items:flex-start;margin-bottom:3px;color:#dc2626"><i class="fas fa-exclamation-circle" style="margin-top:2px;flex-shrink:0"></i><span>Terlambat dari deadline</span></div>';
      _tipEl.innerHTML = html;
      _tipEl.style.display = 'block';
      _tipEl.style.opacity = '0';

      /* Position: get node bounding rect */
      var r = node.getBoundingClientRect();
      var tw = 240, th = _tipEl.offsetHeight || 130;
      var margin = 10;
      var vpW = window.innerWidth, vpH = window.innerHeight;

      /* Horizontal: prefer centre-aligned; flip if off-screen */
      var left = r.left + r.width / 2 - tw / 2;
      if (left + tw > vpW - margin) left = vpW - tw - margin;
      if (left < margin) left = margin;

      /* Vertical: prefer above node; flip to below if no space */
      var top = r.top - th - 12;
      var arrowDir = 'bottom'; /* arrow points down (tooltip above) */
      if (top < margin) {
        top = r.bottom + 12;
        arrowDir = 'top'; /* arrow points up (tooltip below) */
      }

      _tipEl.style.left = left + 'px';
      _tipEl.style.top  = top  + 'px';
      _tipEl.style.opacity = '1';
    }

    function tipRow(iconClass, text) {
      return '<div style="display:flex;gap:6px;align-items:flex-start;margin-bottom:3px;color:#4b3b22">' +
             '<i class="' + iconClass + '" style="margin-top:2px;flex-shrink:0;color:#7a5c2e"></i>' +
             '<span>' + text + '</span></div>';
    }

    function hideMapTip() {
      if (!_tipEl) return;
      _tipEl.style.opacity = '0';
      setTimeout(function() { if(_tipEl) _tipEl.style.display = 'none'; }, 150);
    }

    /* ── Animate paths on load ── */
    document.addEventListener('DOMContentLoaded', function() {
      var paths = document.querySelectorAll('#mapSvg .map-path.draw');
      paths.forEach(function(path, i) {
        var len = path.getTotalLength ? path.getTotalLength() : 800;
        /* Set up draw animation */
        path.style.strokeDasharray  = len;
        path.style.strokeDashoffset = len;
        setTimeout(function() {
          path.style.strokeDashoffset = '0';
          /* After transition completes, restore correct per-state styles */
          setTimeout(function() {
            path.classList.add('drawn');
            path.style.strokeDasharray  = '';
            path.style.strokeDashoffset = '';
          }, 1400); /* slightly > transition duration 1.3s */
        }, 120 + i * 300);
      });
    });

    /* ── Detail panel ── */
    function openCpDetail(idx) {
      var s = MAP_STAGES[idx];
      if (!s || !s.clickable) return;
      document.getElementById('cpDetailTitle').textContent = s.name + ' — ' + s.badgeTxt;
      var rows = '';
      if (s.description) rows += field('Deskripsi', s.description);
      if (s.timestamp)   rows += field('Waktu Proses', s.timestamp);
      if (s.duration)    rows += field('Durasi', s.duration);
      rows += field('Status', s.badgeTxt);
      /* Deadline status */
      if (s.deadlineLevel && !s.isHistorical) {
        if (s.deadlineLevel === 'aman') rows += field('Status Waktu', '🟢 AMAN');
        else if (s.deadlineLevel === 'peringatan') rows += field('Status Waktu', '🟡 PERINGATAN');
        else if (s.deadlineLevel === 'terlambat') rows += field('Status Waktu', '🔴 TERLAMBAT');
      } else if (s.deadlineLevel && s.isHistorical) {
        if (s.deadlineLevel === 'aman') rows += field('Status Waktu Saat Itu', '🟢 AMAN');
        else if (s.deadlineLevel === 'peringatan') rows += field('Status Waktu Saat Itu', '🟡 PERINGATAN');
        else if (s.deadlineLevel === 'terlambat') rows += field('Status Waktu Saat Itu', '🔴 TERLAMBAT');
      }
      if (s.deadlineReceivedAt) {
        var agoText2 = '';
        if (s.deadlineDays > 0) agoText2 = s.deadlineDays + ' hari';
        if (s.deadlineHours && (s.deadlineHours - (s.deadlineDays||0)*24) > 0) agoText2 += (agoText2 ? ' ' : '') + Math.round(s.deadlineHours - (s.deadlineDays||0)*24) + ' jam';
        if (agoText2) rows += field('Diterima', agoText2 + ' yang lalu');
      }
      if (s.isOverdue && !s.deadlineLevel) rows += field('Keterlambatan', '⚠ Terlambat dari deadline');
      document.getElementById('cpDetailGrid').innerHTML = rows;
      var panel = document.getElementById('cpDetailPanel');
      panel.classList.add('open');
      panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    function closeCpDetail() {
      document.getElementById('cpDetailPanel').classList.remove('open');
    }
    function field(lbl, val) {
      return '<div class="cp-detail-item"><label>' + lbl + '</label><span>' + val + '</span></div>';
    }
    </script>

    {{-- Information Grid --}}
    <div class="info-grid">
      {{-- Hero Financial Card --}}
      <div class="hero-financial-card">
        <div class="hero-financial-content">
          <div class="hero-financial-label">Nilai Nominal</div>
          <div class="hero-financial-value">
            Rp {{ number_format($dokumen->nilai_rupiah ?? 0, 0, ',', '.') }}
          </div>
          <div class="hero-financial-details">
            <div class="hero-detail-item">
              <div class="hero-detail-label">Nomor SPP</div>
              <div class="hero-detail-value">{{ $dokumen->nomor_spp ?? '-' }}</div>
            </div>
            <div class="hero-detail-item">
              <div class="hero-detail-label">Dibayar Kepada</div>
              <div class="hero-detail-value">
                {{ $dokumen->dibayarKepadas->first()?->nama_penerima ?? $dokumen->dibayar_kepada ?? '-' }}
              </div>
            </div>
          </div>
        </div>
      </div>

      {{-- Single Combined Information Card --}}
      <div class="info-card clickable" id="document-info-card" data-modal-type="document">
        <div class="info-card-header">
          <div class="info-card-icon" style="background: linear-gradient(135deg, #083E40 0%, #889717 100%);">
            <i class="fas fa-file-alt"></i>
          </div>
          <div class="info-card-title">Informasi Dokumen</div>
        </div>

        {{-- Informasi Umum --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
          <div>
            <div class="stage-label">Uraian SPP</div>
            <div style="font-weight: 600; color: #0f172a; margin-top: 4px;">{{ $dokumen->uraian_spp ?? '-' }}</div>
          </div>
          <div>
            <div class="stage-label">Nomor Agenda</div>
            <div style="font-weight: 600; color: #0f172a; margin-top: 4px;">{{ $dokumen->nomor_agenda ?? '-' }}</div>
          </div>
          <div>
            <div class="stage-label">Jenis Dokumen</div>
            <div style="font-weight: 600; color: #0f172a; margin-top: 4px;">{{ $dokumen->jenis_dokumen ?? '-' }}</div>
          </div>
          <div>
            <div class="stage-label">Kategori</div>
            <div style="font-weight: 600; color: #0f172a; margin-top: 4px;">{{ $dokumen->kategori ?? '-' }}</div>
          </div>
          <div>
            <div class="stage-label">Bagian Pengirim</div>
            <div style="font-weight: 600; color: #0f172a; margin-top: 4px;">{{ $dokumen->bagian ?? '-' }}</div>
          </div>
        </div>

        {{-- Divider: Data Perpajakan, Akutansi & Pembayaran --}}
        <div style="margin: 28px 0 20px; border-top: 2px solid #e2e8f0; position: relative;">
          <span style="position: absolute; top: -12px; left: 16px; background: white; padding: 0 12px; font-size: 12px; font-weight: 700; text-transform: uppercase; color: #f59e0b; letter-spacing: 0.5px;">
            <i class="fas fa-calculator" style="margin-right: 6px;"></i>Data Perpajakan, Akutansi & Pembayaran
          </span>
        </div>

        @php
          $hasPerpajakanData = $dokumen->npwp || $dokumen->no_faktur || $dokumen->jenis_pph;
          $hasAkutansiData = $dokumen->nomor_miro;
          $hasPembayaranData = $dokumen->tanggal_dibayar || $dokumen->link_bukti_pembayaran;
          $hasAnyData = $hasPerpajakanData || $hasAkutansiData || $hasPembayaranData;
        @endphp

        @if($hasAnyData)
          {{-- Data Perpajakan Section --}}
          @if($hasPerpajakanData)
            <div style="margin-bottom: 20px;">
              <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: #f59e0b; margin-bottom: 12px; letter-spacing: 0.5px;">
                <i class="fas fa-calculator" style="margin-right: 6px;"></i>Data Perpajakan
              </div>
              @if($dokumen->npwp)
                <div style="padding: 12px; background: #f8fafc; border-radius: 8px; margin-bottom: 8px;">
                  <div class="stage-label" style="font-size: 11px;">NPWP</div>
                  <div style="font-family: monospace; font-weight: 600; color: #0f172a; margin-top: 4px; font-size: 13px;">{{ $dokumen->npwp }}</div>
                </div>
              @endif
              @if($dokumen->no_faktur)
                <div style="padding: 12px; background: #f8fafc; border-radius: 8px; margin-bottom: 8px;">
                  <div class="stage-label" style="font-size: 11px;">No. Faktur</div>
                  <div style="font-family: monospace; font-weight: 600; color: #0f172a; margin-top: 4px; font-size: 13px;">{{ $dokumen->no_faktur }}</div>
                </div>
              @endif
              @if($dokumen->jenis_pph)
                <div style="padding: 12px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-radius: 8px; border: 1px solid #86efac;">
                  <div class="stage-label" style="color: #059669; font-size: 11px;">Jenis PPh</div>
                  <div style="font-weight: 600; color: #0f172a; margin-top: 4px; font-size: 13px;">{{ $dokumen->jenis_pph }}</div>
                </div>
              @endif
            </div>
          @endif

          {{-- Data Akutansi Section --}}
          @if($hasAkutansiData)
            <div style="margin-bottom: 20px;">
              <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: #083E40; margin-bottom: 12px; letter-spacing: 0.5px;">
                <i class="fas fa-file-invoice-dollar" style="margin-right: 6px;"></i>Data Akutansi
              </div>
              @if($dokumen->nomor_miro)
                <div style="padding: 12px; background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); border-radius: 8px; border: 1px solid #7dd3fc;">
                  <div class="stage-label" style="color: #0369a1; font-size: 11px;">Nomor MIRO</div>
                  <div style="font-family: monospace; font-weight: 600; color: #0f172a; margin-top: 4px; font-size: 13px;">{{ $dokumen->nomor_miro }}</div>
                </div>
              @endif
            </div>
          @endif

          {{-- Data Pembayaran Section --}}
          @if($hasPembayaranData)
            <div style="margin-bottom: 20px;">
              <div style="font-size: 12px; font-weight: 700; text-transform: uppercase; color: #059669; margin-bottom: 12px; letter-spacing: 0.5px;">
                <i class="fas fa-money-bill-wave" style="margin-right: 6px;"></i>Data Pembayaran
              </div>
              @if($dokumen->tanggal_dibayar)
                <div style="padding: 12px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-radius: 8px; margin-bottom: 8px; border: 1px solid #86efac;">
                  <div class="stage-label" style="color: #059669; font-size: 11px;">Tanggal Pembayaran</div>
                  <div style="font-weight: 600; color: #0f172a; margin-top: 4px; font-size: 13px;">{{ \Carbon\Carbon::parse($dokumen->tanggal_dibayar)->format('d M Y') }}</div>
                </div>
              @endif
              @if($dokumen->link_bukti_pembayaran)
                <div style="padding: 12px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-radius: 8px; border: 1px solid #86efac;">
                  <div class="stage-label" style="color: #059669; font-size: 11px;">Link Google Drive Bukti Pembayaran</div>
                  <div style="margin-top: 4px;">
                    <a href="{{ $dokumen->link_bukti_pembayaran }}" target="_blank"
                      style="color: #059669; font-weight: 600; text-decoration: none; font-size: 13px; word-break: break-all;">
                      <i class="fas fa-external-link-alt" style="margin-right: 4px;"></i>{{ \Illuminate\Support\Str::limit($dokumen->link_bukti_pembayaran, 50) }}
                    </a>
                  </div>
                </div>
              @endif
            </div>
          @endif
        @else
          <div style="text-align: center; padding: 30px; color: #94a3b8;">
            <i class="fas fa-search-dollar" style="font-size: 36px; opacity: 0.3; margin-bottom: 10px;"></i>
            <p style="font-size: 14px; margin: 0;">Belum ada data</p>
          </div>
        @endif
      </div>
    </div>
  </div>

  {{-- Modal Popup --}}
  <div class="modal-overlay" id="detail-modal">
    <div class="modal-container">
      <div class="modal-header">
        <div class="modal-header-content">
          <div class="modal-header-icon" id="modal-icon">
            <i class="fas fa-file-alt"></i>
          </div>
          <div class="modal-header-text">
            <h2 id="modal-title">Detail Dokumen</h2>
            <p id="modal-subtitle">Informasi lengkap dokumen</p>
          </div>
        </div>
        <button class="modal-close" id="modal-close">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div class="modal-body custom-scrollbar" id="modal-body">
        {{-- Content will be populated by JavaScript --}}
      </div>
    </div>
  </div>

  <script>
    // Document data for modal
    @php
      // Calculate status_display before array definition
      $statusMap = [
        'draft' => 'Draft',
        'sedang diproses' => 'Sedang Diproses',
        'menunggu_verifikasi' => 'Menunggu Verifikasi',
        'pending_approval_team_verifikasi' => 'Menunggu Persetujuan Team Verifikasi',
        'sent_to_team_verifikasi' => 'Terkirim ke Team Verifikasi',
        'proses_Team Verifikasi' => 'Diproses Team Verifikasi',
        'sent_to_perpajakan' => 'Terkirim ke Team Perpajakan',
        'proses_perpajakan' => 'Diproses Team Perpajakan',
        'sent_to_akutansi' => 'Terkirim ke Team Akutansi',
        'proses_akutansi' => 'Diproses Team Akutansi',
        'menunggu_approved_pengiriman' => 'Menunggu Persetujuan Pengiriman',
        'proses_pembayaran' => 'Diproses Team Pembayaran',
        'sent_to_pembayaran' => 'Terkirim ke Team Pembayaran',
        'approved_data_sudah_terkirim' => 'Data Sudah Terkirim',
        'rejected_data_tidak_lengkap' => 'Ditolak - Data Tidak Lengkap',
        'selesai' => 'Selesai',
        'returned_to_operator' => 'Dikembalikan ke Ibu Tarapul',
        'returned_to_department' => 'Dikembalikan ke Department',
        'returned_to_bidang' => 'Dikembalikan ke Bidang',
      ];
      $currentStatus = $dokumen->status ?? null;
      $statusDisplay = $currentStatus ? ($statusMap[$currentStatus] ?? ucfirst(str_replace('_', ' ', $currentStatus))) : null;

      $documentDataArray = [
        'nomor_agenda' => $dokumen->nomor_agenda ?? null,
        'nomor_spp' => $dokumen->nomor_spp ?? null,
        'tanggal_spp' => $dokumen->tanggal_spp ? \Carbon\Carbon::parse($dokumen->tanggal_spp)->format('d M Y') : null,
        'uraian_spp' => $dokumen->uraian_spp ?? null,
        'nilai_rupiah' => $dokumen->nilai_rupiah ?? null,
        'kategori' => $dokumen->kategori ?? null,
        'jenis_dokumen' => $dokumen->jenis_dokumen ?? null,
        'jenis_sub_pekerjaan' => $dokumen->jenis_sub_pekerjaan ?? null,
        'jenis_pembayaran' => $dokumen->jenis_pembayaran ?? null,
        'kebun' => $dokumen->kebun ?? null,
        'bagian' => $dokumen->bagian ?? null,
        'nama_pengirim' => $dokumen->nama_pengirim ?? null,
        'dibayar_kepada' => $dokumen->dibayarKepadas->first()?->nama_penerima ?? $dokumen->dibayar_kepada ?? null,
        'no_berita_acara' => $dokumen->no_berita_acara ?? null,
        'tanggal_berita_acara' => $dokumen->tanggal_berita_acara ? \Carbon\Carbon::parse($dokumen->tanggal_berita_acara)->format('d M Y') : null,
        'no_spk' => $dokumen->no_spk ?? null,
        'tanggal_spk' => $dokumen->tanggal_spk ? \Carbon\Carbon::parse($dokumen->tanggal_spk)->format('d M Y') : null,
        'tanggal_berakhir_spk' => $dokumen->tanggal_berakhir_spk ? \Carbon\Carbon::parse($dokumen->tanggal_berakhir_spk)->format('d M Y') : null,
        'nomor_miro' => $dokumen->nomor_miro ?? null,
        'status' => $currentStatus,
        'status_display' => $statusDisplay,
        'keterangan' => $dokumen->keterangan ?? null,
        'tanggal_masuk' => $dokumen->tanggal_masuk ? \Carbon\Carbon::parse($dokumen->tanggal_masuk)->format('d M Y, H:i') : null,
      ];

      $taxDataArray = [
        // Data Perpajakan
        'npwp' => $dokumen->npwp ?? null,
        'status_perpajakan' => $dokumen->status_perpajakan ?? null,
        'no_faktur' => $dokumen->no_faktur ?? null,
        'tanggal_faktur' => $dokumen->tanggal_faktur ? \Carbon\Carbon::parse($dokumen->tanggal_faktur)->format('d M Y') : null,
        'tanggal_selesai_verifikasi_pajak' => $dokumen->tanggal_selesai_verifikasi_pajak ? \Carbon\Carbon::parse($dokumen->tanggal_selesai_verifikasi_pajak)->format('d M Y') : null,
        'jenis_pph' => $dokumen->jenis_pph ?? null,
        'dpp_pph' => $dokumen->dpp_pph ?? null,
        'ppn_terhutang' => $dokumen->ppn_terhutang ?? null,
        'link_dokumen_pajak' => $dokumen->link_dokumen_pajak ?? null,
        'komoditi_perpajakan' => $dokumen->komoditi_perpajakan ?? null,
        'alamat_pembeli' => $dokumen->alamat_pembeli ?? null,
        'no_kontrak' => $dokumen->no_kontrak ?? null,
        'no_invoice' => $dokumen->no_invoice ?? null,
        'tanggal_invoice' => $dokumen->tanggal_invoice ? \Carbon\Carbon::parse($dokumen->tanggal_invoice)->format('d M Y') : null,
        'dpp_invoice' => $dokumen->dpp_invoice ?? null,
        'ppn_invoice' => $dokumen->ppn_invoice ?? null,
        'dpp_ppn_invoice' => $dokumen->dpp_ppn_invoice ?? null,
        'tanggal_pengajuan_pajak' => $dokumen->tanggal_pengajuan_pajak ? \Carbon\Carbon::parse($dokumen->tanggal_pengajuan_pajak)->format('d M Y') : null,
        'dpp_faktur' => $dokumen->dpp_faktur ?? null,
        'ppn_faktur' => $dokumen->ppn_faktur ?? null,
        'selisih_pajak' => $dokumen->selisih_pajak ?? null,
        'keterangan_pajak' => $dokumen->keterangan_pajak ?? null,
        'penggantian_pajak' => $dokumen->penggantian_pajak ?? null,
        'dpp_penggantian' => $dokumen->dpp_penggantian ?? null,
        'ppn_penggantian' => $dokumen->ppn_penggantian ?? null,
        'selisih_ppn' => $dokumen->selisih_ppn ?? null,
        // Data Akutansi
        'nomor_miro' => $dokumen->nomor_miro ?? null,
        // Data Pembayaran
        'tanggal_dibayar' => $dokumen->tanggal_dibayar ? \Carbon\Carbon::parse($dokumen->tanggal_dibayar)->format('d M Y') : null,
        'link_bukti_pembayaran' => $dokumen->link_bukti_pembayaran ?? null,
        'status_pembayaran' => $dokumen->status_pembayaran ?? null,
      ];
    @endphp
    const documentData = @json($documentDataArray);
    const taxData = @json($taxDataArray);

    function formatCurrency(value) {
      if (!value) return '-';
      return 'Rp ' + parseFloat(value).toLocaleString('id-ID');
    }

    function formatField(label, value, options = {}) {
  if (value === null || value === undefined || value === '') return null;

      const { monospace = false, highlight = false, currency = false } = options;
  let displayValue = value;

      if (currency && value) {
        displayValue = formatCurrency(value);
  }

      return {
        label,
        value: displayValue,
        monospace,
        highlight,
        empty: false
      };
    }

    function renderDocumentModal() {
      const modalBody = document.getElementById('modal-body');
      if (!modalBody) {
        console.error('Modal body not found');
        return;
      }
      modalBody.innerHTML = `
        <div class="modal-section">
          <div class="modal-section-title">
            <i class="fas fa-info-circle"></i>
            Informasi Umum
          </div>
          <div class="modal-grid">
            ${formatField('Nomor Agenda', documentData.nomor_agenda) ? `
              <div class="modal-field">
                <div class="modal-field-label">Nomor Agenda</div>
                <div class="modal-field-value highlight">${documentData.nomor_agenda}</div>
              </div>
            ` : ''}
            ${formatField('Nomor SPP', documentData.nomor_spp) ? `
              <div class="modal-field">
                <div class="modal-field-label">Nomor SPP</div>
                <div class="modal-field-value monospace highlight">${documentData.nomor_spp}</div>
              </div>
            ` : ''}
            ${formatField('Tanggal SPP', documentData.tanggal_spp) ? `
              <div class="modal-field">
                <div class="modal-field-label">Tanggal SPP</div>
                <div class="modal-field-value">${documentData.tanggal_spp}</div>
              </div>
            ` : ''}
            ${formatField('Tanggal Masuk', documentData.tanggal_masuk) ? `
              <div class="modal-field">
                <div class="modal-field-label">Tanggal Masuk</div>
                <div class="modal-field-value">${documentData.tanggal_masuk}</div>
              </div>
            ` : ''}
            ${formatField('Status', documentData.status_display || documentData.status) ? `
              <div class="modal-field">
                <div class="modal-field-label">Status</div>
                <div class="modal-field-value highlight">${documentData.status_display || documentData.status || '-'}</div>
              </div>
            ` : ''}
          </div>
        </div>

        <div class="modal-section">
          <div class="modal-section-title">
            <i class="fas fa-file-invoice-dollar"></i>
            Detail Dokumen
          </div>
          <div class="modal-grid">
            ${formatField('Uraian SPP', documentData.uraian_spp) ? `
              <div class="modal-field" style="grid-column: 1 / -1;">
                <div class="modal-field-label">Uraian SPP</div>
                <div class="modal-field-value">${documentData.uraian_spp}</div>
              </div>
            ` : ''}
            ${formatField('Nilai Rupiah', documentData.nilai_rupiah, { currency: true }) ? `
              <div class="modal-field">
                <div class="modal-field-label">Nilai Rupiah</div>
                <div class="modal-field-value highlight">${formatCurrency(documentData.nilai_rupiah)}</div>
              </div>
            ` : ''}
            ${formatField('Kategori', documentData.kategori) ? `
              <div class="modal-field">
                <div class="modal-field-label">Kategori</div>
                <div class="modal-field-value">${documentData.kategori}</div>
              </div>
            ` : ''}
            ${formatField('Jenis Dokumen', documentData.jenis_dokumen) ? `
              <div class="modal-field">
                <div class="modal-field-label">Jenis Dokumen</div>
                <div class="modal-field-value">${documentData.jenis_dokumen}</div>
              </div>
            ` : ''}
            ${formatField('Jenis Sub Pekerjaan', documentData.jenis_sub_pekerjaan) ? `
              <div class="modal-field">
                <div class="modal-field-label">Jenis Sub Pekerjaan</div>
                <div class="modal-field-value">${documentData.jenis_sub_pekerjaan}</div>
              </div>
            ` : ''}
            ${formatField('Jenis Pembayaran', documentData.jenis_pembayaran) ? `
              <div class="modal-field">
                <div class="modal-field-label">Jenis Pembayaran</div>
                <div class="modal-field-value">${documentData.jenis_pembayaran}</div>
              </div>
            ` : ''}
          </div>
        </div>

        <div class="modal-section">
          <div class="modal-section-title">
            <i class="fas fa-building"></i>
            Informasi Pengirim & Penerima
          </div>
          <div class="modal-grid">
            ${formatField('Kebun', documentData.kebun) ? `
              <div class="modal-field">
                <div class="modal-field-label">Kebun</div>
                <div class="modal-field-value">${documentData.kebun}</div>
              </div>
            ` : ''}
            ${formatField('Bagian', documentData.bagian) ? `
              <div class="modal-field">
                <div class="modal-field-label">Bagian Pengirim</div>
                <div class="modal-field-value">${documentData.bagian}</div>
              </div>
            ` : ''}
            ${formatField('Nama Pengirim', documentData.nama_pengirim) ? `
              <div class="modal-field">
                <div class="modal-field-label">Nama Pengirim</div>
                <div class="modal-field-value">${documentData.nama_pengirim}</div>
              </div>
            ` : ''}
            ${formatField('Dibayar Kepada', documentData.dibayar_kepada) ? `
              <div class="modal-field">
                <div class="modal-field-label">Dibayar Kepada</div>
                <div class="modal-field-value highlight">${documentData.dibayar_kepada}</div>
              </div>
            ` : ''}
          </div>
        </div>

        ${(documentData.no_berita_acara || documentData.no_spk || documentData.nomor_miro) ? `
        <div class="modal-section">
          <div class="modal-section-title">
            <i class="fas fa-file-contract"></i>
            Dokumen Pendukung
          </div>
          <div class="modal-grid">
            ${formatField('No. Berita Acara', documentData.no_berita_acara) ? `
              <div class="modal-field">
                <div class="modal-field-label">No. Berita Acara</div>
                <div class="modal-field-value monospace">${documentData.no_berita_acara}</div>
              </div>
            ` : ''}
            ${formatField('Tanggal Berita Acara', documentData.tanggal_berita_acara) ? `
              <div class="modal-field">
                <div class="modal-field-label">Tanggal Berita Acara</div>
                <div class="modal-field-value">${documentData.tanggal_berita_acara}</div>
              </div>
            ` : ''}
            ${formatField('No. SPK', documentData.no_spk) ? `
              <div class="modal-field">
                <div class="modal-field-label">No. SPK</div>
                <div class="modal-field-value monospace">${documentData.no_spk}</div>
              </div>
            ` : ''}
            ${formatField('Tanggal SPK', documentData.tanggal_spk) ? `
              <div class="modal-field">
                <div class="modal-field-label">Tanggal SPK</div>
                <div class="modal-field-value">${documentData.tanggal_spk}</div>
              </div>
            ` : ''}
            ${formatField('Tanggal Berakhir SPK', documentData.tanggal_berakhir_spk) ? `
              <div class="modal-field">
                <div class="modal-field-label">Tanggal Berakhir SPK</div>
                <div class="modal-field-value">${documentData.tanggal_berakhir_spk}</div>
              </div>
            ` : ''}
            ${formatField('Nomor MIRO', documentData.nomor_miro) ? `
              <div class="modal-field">
                <div class="modal-field-label">Nomor MIRO</div>
                <div class="modal-field-value monospace highlight">${documentData.nomor_miro}</div>
              </div>
            ` : ''}
          </div>
        </div>
        ` : ''}

        ${documentData.keterangan ? `
        <div class="modal-section">
          <div class="modal-section-title">
            <i class="fas fa-sticky-note"></i>
            Keterangan
          </div>
          <div class="modal-field">
            <div class="modal-field-value">${documentData.keterangan}</div>
          </div>
        </div>
        ` : ''}
      `;
    }

    function renderTaxModal() {
  const modalBody = document.getElementById('modal-body');

      // Check if there's any data (perpajakan, akutansi, or pembayaran)
  const hasTaxData = taxData.npwp || taxData.no_faktur || taxData.jenis_pph || taxData.nomor_miro || taxData.tanggal_dibayar || taxData.link_bukti_pembayaran;

      if (!hasTaxData) {
        modalBody.innerHTML = `
          <div style="text-align: center; padding: 60px 20px;">
            <i class="fas fa-search-dollar" style="font-size: 64px; color: #cbd5e1; margin-bottom: 20px;"></i>
            <h3 style="color: #64748b; margin-bottom: 8px;">Belum Ada Data</h3>
            <p style="color: #94a3b8;">Data perpajakan, akutansi, atau pembayaran untuk dokumen ini belum diisi.</p>
          </div>
        `;
        return;
      }

      modalBody.innerHTML = `
        ${taxData.npwp || taxData.no_faktur || taxData.jenis_pph ? `
        <div class="modal-section">
          <div class="modal-section-title" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 12px 16px; border-radius: 10px; margin: -24px -24px 20px -24px; border-bottom: none;">
            <div style="width: 36px; height: 36px; background: rgba(255,255,255,0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-id-card" style="font-size: 16px;"></i>
            </div>
            <span>Identitas Perpajakan</span>
          </div>
          <div class="modal-grid">
            ${formatField('NPWP', taxData.npwp) ? `
              <div class="modal-field">
                <div class="modal-field-label">NPWP</div>
                <div class="modal-field-value monospace highlight">${taxData.npwp}</div>
              </div>
            ` : ''}
            ${formatField('No. Faktur', taxData.no_faktur) ? `
              <div class="modal-field">
                <div class="modal-field-label">No. Faktur</div>
                <div class="modal-field-value monospace highlight">${taxData.no_faktur}</div>
              </div>
            ` : ''}
            ${formatField('Tanggal Faktur', taxData.tanggal_faktur) ? `
              <div class="modal-field">
                <div class="modal-field-label">Tanggal Faktur</div>
                <div class="modal-field-value">${taxData.tanggal_faktur}</div>
              </div>
            ` : ''}
            ${formatField('Jenis PPh', taxData.jenis_pph) ? `
              <div class="modal-field">
                <div class="modal-field-label">Jenis PPh</div>
                <div class="modal-field-value highlight">${taxData.jenis_pph}</div>
              </div>
            ` : ''}
            ${formatField('Status Perpajakan', taxData.status_perpajakan) ? `
              <div class="modal-field">
                <div class="modal-field-label">Status Perpajakan</div>
                <div class="modal-field-value highlight">${taxData.status_perpajakan}</div>
              </div>
            ` : ''}
            ${formatField('Tanggal Selesai Verifikasi', taxData.tanggal_selesai_verifikasi_pajak) ? `
              <div class="modal-field">
                <div class="modal-field-label">Tanggal Selesai Verifikasi</div>
                <div class="modal-field-value">${taxData.tanggal_selesai_verifikasi_pajak}</div>
              </div>
            ` : ''}
          </div>
        </div>
        ` : ''}

        ${taxData.komoditi_perpajakan || taxData.alamat_pembeli || taxData.no_kontrak ? `
        <div class="modal-section">
          <div class="modal-section-title">
            <i class="fas fa-building"></i>
            Informasi Pembeli
          </div>
          <div class="modal-grid">
            ${formatField('Komoditi', taxData.komoditi_perpajakan) ? `
              <div class="modal-field">
                <div class="modal-field-label">Komoditi</div>
                <div class="modal-field-value">${taxData.komoditi_perpajakan}</div>
              </div>
            ` : ''}
            ${formatField('Alamat Pembeli', taxData.alamat_pembeli) ? `
              <div class="modal-field" style="grid-column: 1 / -1;">
                <div class="modal-field-label">Alamat Pembeli</div>
                <div class="modal-field-value">${taxData.alamat_pembeli}</div>
              </div>
            ` : ''}
            ${formatField('No. Kontrak', taxData.no_kontrak) ? `
              <div class="modal-field">
                <div class="modal-field-label">No. Kontrak</div>
                <div class="modal-field-value monospace">${taxData.no_kontrak}</div>
              </div>
            ` : ''}
          </div>
        </div>
        ` : ''}

        ${taxData.dpp_pph || taxData.ppn_terhutang ? `
        <div class="tax-summary-card">
          <div class="tax-summary-row">
            <div class="tax-summary-label">DPP PPh</div>
            <div class="tax-summary-value">${formatCurrency(taxData.dpp_pph)}</div>
          </div>
          <div class="tax-summary-row">
            <div class="tax-summary-label">PPN Terhutang</div>
            <div class="tax-summary-value">${formatCurrency(taxData.ppn_terhutang)}</div>
          </div>
        </div>
        ` : ''}

        ${taxData.no_invoice || taxData.dpp_invoice || taxData.ppn_invoice ? `
        <div class="modal-section">
          <div class="modal-section-title">
            <i class="fas fa-file-invoice"></i>
            Data Invoice
          </div>
          <div class="modal-grid">
            ${formatField('No. Invoice', taxData.no_invoice) ? `
              <div class="modal-field">
                <div class="modal-field-label">No. Invoice</div>
                <div class="modal-field-value monospace">${taxData.no_invoice}</div>
              </div>
            ` : ''}
            ${formatField('Tanggal Invoice', taxData.tanggal_invoice) ? `
              <div class="modal-field">
                <div class="modal-field-label">Tanggal Invoice</div>
                <div class="modal-field-value">${taxData.tanggal_invoice}</div>
              </div>
            ` : ''}
            ${formatField('DPP Invoice', taxData.dpp_invoice, { currency: true }) ? `
              <div class="modal-field">
                <div class="modal-field-label">DPP Invoice</div>
                <div class="modal-field-value highlight">${formatCurrency(taxData.dpp_invoice)}</div>
              </div>
            ` : ''}
            ${formatField('PPN Invoice', taxData.ppn_invoice, { currency: true }) ? `
              <div class="modal-field">
                <div class="modal-field-label">PPN Invoice</div>
                <div class="modal-field-value highlight">${formatCurrency(taxData.ppn_invoice)}</div>
              </div>
            ` : ''}
            ${formatField('DPP + PPN Invoice', taxData.dpp_ppn_invoice, { currency: true }) ? `
              <div class="modal-field">
                <div class="modal-field-label">DPP + PPN Invoice</div>
                <div class="modal-field-value highlight">${formatCurrency(taxData.dpp_ppn_invoice)}</div>
              </div>
            ` : ''}
          </div>
        </div>
        ` : ''}

        ${taxData.dpp_faktur || taxData.ppn_faktur || taxData.selisih_pajak ? `
        <div class="modal-section">
          <div class="modal-section-title">
            <i class="fas fa-receipt"></i>
            Data Faktur
          </div>
          <div class="modal-grid">
            ${formatField('DPP Faktur', taxData.dpp_faktur, { currency: true }) ? `
              <div class="modal-field">
                <div class="modal-field-label">DPP Faktur</div>
                <div class="modal-field-value highlight">${formatCurrency(taxData.dpp_faktur)}</div>
              </div>
            ` : ''}
            ${formatField('PPN Faktur', taxData.ppn_faktur, { currency: true }) ? `
              <div class="modal-field">
                <div class="modal-field-label">PPN Faktur</div>
                <div class="modal-field-value highlight">${formatCurrency(taxData.ppn_faktur)}</div>
              </div>
            ` : ''}
            ${formatField('Selisih Pajak', taxData.selisih_pajak, { currency: true }) ? `
              <div class="modal-field">
                <div class="modal-field-label">Selisih Pajak</div>
                <div class="modal-field-value highlight">${formatCurrency(taxData.selisih_pajak)}</div>
              </div>
            ` : ''}
          </div>
        </div>
        ` : ''}

        ${taxData.penggantian_pajak || taxData.dpp_penggantian || taxData.ppn_penggantian || taxData.selisih_ppn ? `
        <div class="modal-section">
          <div class="modal-section-title">
            <i class="fas fa-exchange-alt"></i>
            Penggantian Pajak
          </div>
          <div class="modal-grid">
            ${formatField('Penggantian Pajak', taxData.penggantian_pajak) ? `
              <div class="modal-field">
                <div class="modal-field-label">Penggantian Pajak</div>
                <div class="modal-field-value">${taxData.penggantian_pajak}</div>
              </div>
            ` : ''}
            ${formatField('DPP Penggantian', taxData.dpp_penggantian, { currency: true }) ? `
              <div class="modal-field">
                <div class="modal-field-label">DPP Penggantian</div>
                <div class="modal-field-value highlight">${formatCurrency(taxData.dpp_penggantian)}</div>
              </div>
            ` : ''}
            ${formatField('PPN Penggantian', taxData.ppn_penggantian, { currency: true }) ? `
              <div class="modal-field">
                <div class="modal-field-label">PPN Penggantian</div>
                <div class="modal-field-value highlight">${formatCurrency(taxData.ppn_penggantian)}</div>
              </div>
            ` : ''}
            ${formatField('Selisih PPN', taxData.selisih_ppn, { currency: true }) ? `
              <div class="modal-field">
                <div class="modal-field-label">Selisih PPN</div>
                <div class="modal-field-value highlight">${formatCurrency(taxData.selisih_ppn)}</div>
              </div>
            ` : ''}
          </div>
        </div>
        ` : ''}

        ${taxData.tanggal_pengajuan_pajak ? `
        <div class="modal-section">
          <div class="modal-section-title">
            <i class="fas fa-calendar-check"></i>
            Timeline Perpajakan
          </div>
          <div class="modal-grid">
            ${formatField('Tanggal Pengajuan Pajak', taxData.tanggal_pengajuan_pajak) ? `
              <div class="modal-field">
                <div class="modal-field-label">Tanggal Pengajuan Pajak</div>
                <div class="modal-field-value">${taxData.tanggal_pengajuan_pajak}</div>
              </div>
            ` : ''}
          </div>
        </div>
        ` : ''}

        ${taxData.keterangan_pajak ? `
        <div class="modal-section">
          <div class="modal-section-title">
            <i class="fas fa-sticky-note"></i>
            Keterangan Pajak
          </div>
          <div class="modal-field">
            <div class="modal-field-value">${taxData.keterangan_pajak}</div>
          </div>
        </div>
        ` : ''}

        ${taxData.link_dokumen_pajak ? `
        <div class="modal-section">
          <div class="modal-section-title">
            <i class="fas fa-link"></i>
            Link Dokumen Pajak
          </div>
          <div class="modal-field">
            <a href="${taxData.link_dokumen_pajak}" target="_blank" class="modal-field-value" style="color: #083E40; text-decoration: underline;">
              <i class="fas fa-external-link-alt mr-2"></i>
              ${taxData.link_dokumen_pajak}
            </a>
          </div>
        </div>
        ` : ''}

        ${taxData.nomor_miro ? `
        <div class="modal-section">
          <div class="modal-section-title" style="background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%); color: white; padding: 12px 16px; border-radius: 10px; margin: -24px -24px 20px -24px; border-bottom: none;">
            <div style="width: 36px; height: 36px; background: rgba(255,255,255,0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-file-invoice-dollar" style="font-size: 16px;"></i>
            </div>
            <span>Data Akutansi</span>
          </div>
          <div class="modal-grid">
            ${formatField('Nomor MIRO', taxData.nomor_miro) ? `
              <div class="modal-field">
                <div class="modal-field-label">Nomor MIRO</div>
                <div class="modal-field-value monospace highlight">${taxData.nomor_miro}</div>
              </div>
            ` : ''}
          </div>
        </div>
        ` : ''}

        ${taxData.tanggal_dibayar || taxData.link_bukti_pembayaran || taxData.status_pembayaran ? `
        <div class="modal-section">
          <div class="modal-section-title" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); color: white; padding: 12px 16px; border-radius: 10px; margin: -24px -24px 20px -24px; border-bottom: none;">
            <div style="width: 36px; height: 36px; background: rgba(255,255,255,0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-money-bill-wave" style="font-size: 16px;"></i>
            </div>
            <span>Data Pembayaran</span>
          </div>
          <div class="modal-grid">
            ${formatField('Tanggal Pembayaran', taxData.tanggal_dibayar) ? `
              <div class="modal-field">
                <div class="modal-field-label">Tanggal Pembayaran</div>
                <div class="modal-field-value highlight">${taxData.tanggal_dibayar}</div>
              </div>
            ` : ''}
            ${formatField('Status Pembayaran', taxData.status_pembayaran) ? `
              <div class="modal-field">
                <div class="modal-field-label">Status Pembayaran</div>
                <div class="modal-field-value highlight">${taxData.status_pembayaran}</div>
              </div>
            ` : ''}
            ${formatField('Link Google Drive Bukti Pembayaran', taxData.link_bukti_pembayaran) ? `
              <div class="modal-field" style="grid-column: 1 / -1;">
                <div class="modal-field-label">Link Google Drive Bukti Pembayaran</div>
                <div class="modal-field-value">
                  <a href="${taxData.link_bukti_pembayaran}" target="_blank" style="color: #059669; text-decoration: underline; word-break: break-all;">
                    <i class="fas fa-external-link-alt" style="margin-right: 6px;"></i>
                    ${taxData.link_bukti_pembayaran}
                  </a>
                </div>
              </div>
            ` : ''}
          </div>
        </div>
        ` : ''}
      `;
    }

    // Modal functionality
     document.addEventListener('DOMContentLoaded', function() {
      const modal = document.getElementById('detail-modal');
      const modalClose = document.getElementById('modal-close');
      const documentCard = document.getElementById('document-info-card');
      const taxCard = document.getElementById('tax-data-card');
      const modalTitle = document.getElementById('modal-title');
      const modalSubtitle = document.getElementById('modal-subtitle');
      const modalIcon = document.getElementById('modal-icon');

      // Check if elements exist
      if (!modal || !modalClose || !documentCard || !taxCard || !modalTitle || !modalSubtitle || !modalIcon) {
        console.error('Modal elements not found');
        return;
      }

      function openModal(type) {
        console.log('Opening modal:', type);
        try {
          if (type === 'document') {
            modalTitle.textContent = 'Detail Informasi Dokumen';
            modalSubtitle.textContent = 'Informasi lengkap dokumen';
            modalIcon.innerHTML = '<i class="fas fa-file-alt"></i>';
            modalIcon.style.background = 'linear-gradient(135deg, #083E40 0%, #889717 100%)';
            renderDocumentModal();
          } else if (type === 'tax') {
            modalTitle.textContent = 'Detail Data Perpajakan, Akutansi & Pembayaran';
            modalSubtitle.textContent = 'Informasi lengkap perpajakan, akutansi, dan pembayaran';
            modalIcon.innerHTML = '<i class="fas fa-calculator"></i>';
            modalIcon.style.background = 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)';
            renderTaxModal();
          } else if (type === 'activity') {
            // Content already set by showActivityDetail
            // Just show the modal
  }

          modal.classList.add('show');
          document.body.style.overflow = 'hidden';
          console.log('Modal opened successfully');
        } catch (error) {
          console.error('Error opening modal:', error);
        }
      }

      function closeModal() {
        modal.classList.remove('show');
        document.body.style.overflow = '';
      }

      // Add click event listeners
      if (documentCard) {
         documentCard.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          console.log('Document card clicked');
          openModal('document');
        });
        console.log('Document card event listener attached');
      } else {
        console.error('Document card not found');
      }

      if (taxCard) {
         taxCard.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          console.log('Tax card clicked');
          openModal('tax');
        });
        console.log('Tax card event listener attached');
      } else {
        console.error('Tax card not found');
      }

      if (modalClose) {
        modalClose.addEventListener('click', closeModal);
  }

      if (modal) {
         modal.addEventListener('click', function(e) {
          if (e.target === modal) {
            closeModal();
          }
        });
      }

      // Close on ESC key
       document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('show')) {
          closeModal();
        }
      });

      // Smooth scroll to active stage on load
      const activeStage = document.querySelector('.timeline-stage.active');
      if (activeStage) {
        setTimeout(() => {
          activeStage.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 500);
      }

      // Helper function to escape  HTML (define before use in showActivityDetail)
      window.escapeHtml = function(text) {
        const map = {
          '&': '&amp;',
          '<': '&lt;',
          '>': '&gt;',
          '"': '&quot;',
          "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
      };

      // Helper function to decode HTML entities (reverse of escapeHtml)
      window.decodeHtmlEntities = function(text) {
        const textArea = document.createElement('textarea');
        textArea.innerHTML = text;
        return textArea.value;
      };

      // Function to show activity detail
       window.showActivityDetail = function(activityId) {
        const activityItem = document.querySelector(`[data-activity-id="${activityId}"]`);
        if (!activityItem) return;

        const actionDescription = window.decodeHtmlEntities(activityItem.getAttribute('data-action-description') || 'Activity');
        const performedBy = activityItem.getAttribute('data-performed-by') || 'System';
        const actionAt = activityItem.getAttribute('data-action-at') || '-';
        const actionAtRelative = activityItem.getAttribute('data-action-at-relative') || '-';
        const stage = activityItem.getAttribute('data-stage') || '-';
        const action = activityItem.getAttribute('data-action') || '-';
  const detailsJson = activityItem.getAttribute('data-details') || '{}';

        let details = {};
        try {
          details = JSON.parse(detailsJson);
        } catch (e) {
          details = {};
        }

        // Map stage to display name
        const stageMap = {
          'sender': 'Bagian',
          'reviewer': 'Team Verifikasi',
          'tax': 'Team Perpajakan',
          'accounting': 'Team Akutansi',
          'payment': 'Team Pembayaran',
          'operator': 'Bagian',
          'operator': 'Bagian',
          'team_verifikasi': 'Team Verifikasi',
          'team_verifikasi': 'Team Verifikasi',
          'verifikasi': 'Team Verifikasi',
          'perpajakan': 'Team Perpajakan',
          'akutansi': 'Team Akutansi',
          'pembayaran': 'Team Pembayaran',
        };
        const stageDisplay = stageMap[stage] || stage;

        // Map action to display name
        const actionMap = {
          'sent_to_inbox': 'Dikirim ke Inbox',
          'approved': 'Disetujui',
          'rejected': 'Ditolak',
          'created': 'DOperatort',
          'deleted': 'Dihapus',
          'updated': 'Diperbarui',
          'returned': 'Dikembalikan',
          'forwarded': 'Diteruskan',
          'processed': 'Diproses',
          'completed': 'Selesai',
          'data_edited': 'Data Diubah',
        };
        const actionDisplay = actionMap[action] || action.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

        // Build modal content
        modalTitle.textContent = 'Detail Aktivitas';
        modalSubtitle.textContent = 'Informasi lengkap aktivitas';
        modalIcon.innerHTML = '<i class="fas fa-history"></i>';
        modalIcon.style.background = 'linear-gradient(135deg, #3b82f6 0%, #2563eb 100%)';

        let modalContent = `
          <div style="display: grid; gap: 24px;">
            <div style="background: white; padding: 24px; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
              <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Aktivitas</div>
              <div style="font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 20px;">${window.escapeHtml(actionDescription)}</div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
              <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Dilakukan Oleh</div>
                <div style="font-size: 16px; font-weight: 600; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                  <i class="fas fa-user" style="color: #3b82f6;"></i>
                  ${window.escapeHtml(performedBy)}
                </div>
              </div>

              <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Tahap</div>
                <div style="font-size: 16px; font-weight: 600; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                  <i class="fas fa-layer-group" style="color: #10b981;"></i>
                  ${window.escapeHtml(stageDisplay)}
                </div>
              </div>

              <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Waktu</div>
                <div style="font-size: 16px; font-weight: 600; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                  <i class="far fa-clock" style="color: #f59e0b;"></i>
                  ${window.escapeHtml(actionAt)}
                </div>
                <div style="font-size: 13px; color: #64748b; margin-top: 4px;">${window.escapeHtml(actionAtRelative)}</div>
              </div>

              <div style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                <div style="font-size: 12px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">Tipe Aksi</div>
                <div style="font-size: 16px; font-weight: 600; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                  <i class="fas fa-code" style="color: #8b5cf6;"></i>
                  ${window.escapeHtml(actionDisplay)}
                </div>
              </div>
            </div>
        `;

        // Add details if available
        if (details && Object.keys(details).length > 0) {
          modalContent += `
            <div style="background: white; padding: 24px; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
              <div style="font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-info-circle" style="color: #3b82f6;"></i>
                Detail Tambahan
              </div>
              <div style="display: grid; gap: 12px;">
  `;

          for (const [key, value] of Object.entries(details)) {
            const label = key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            let displayValue = value;
            if (typeof value === 'object') {
              displayValue = JSON.stringify(value, null, 2);
            }
            modalContent += `
              <div style="padding: 12px; background: #f8fafc; border-radius: 8px; border-left: 3px solid #3b82f6;">
                <div style="font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px;">${window.escapeHtml(label)}</div>
                <div style="font-size: 14px; font-weight: 500; color: #0f172a;">${window.escapeHtml(String(displayValue))}</div>
              </div>
            `;
  }

          modalContent += `
              </div>
            </div>
          `;
        }

  modalContent += `</div>`;

        document.getElementById('modal-body').innerHTML = modalContent;
        openModal('activity');
      };
    });
  </script>

@endsection





