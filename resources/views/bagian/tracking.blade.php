@extends('layouts.app')

@section('title', 'Tracking Dokumen - Bagian ' . $bagianCode)

@section('content')
    <style>
        .tracking-container {
            padding: 20px;
        }

        .filter-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            padding: 16px 20px;
            margin-bottom: 24px;
        }

        .view-toggle {
            display: flex;
            gap: 8px;
        }

        .view-toggle .btn {
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
        }

        .view-toggle .btn-active {
            background: #083E40;
            color: white;
            border: none;
        }

        .view-toggle .btn-inactive {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #e9ecef;
        }

        /* Card Grid */
        .tracking-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 20px;
        }

        /* Document Card */
        .doc-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .doc-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .doc-card-header {
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
        }

        .doc-agenda {
            font-size: 18px;
            font-weight: 700;
            color: #083E40;
            margin-bottom: 4px;
        }

        .doc-spp {
            font-size: 13px;
            color: #6c757d;
        }

        .doc-nilai {
            font-size: 20px;
            font-weight: 700;
            color: #28a745;
            margin-top: 12px;
        }

        .doc-card-body {
            padding: 20px;
        }

        .doc-position {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 16px;
        }

        .doc-position i {
            color: #083E40;
        }

        /* Progress Steps */
        .progress-section {
            margin-top: 16px;
        }

        .progress-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 600;
            color: #083E40;
            margin-bottom: 12px;
        }

        .progress-steps {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
        }

        .progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 1;
        }

        .step-circle {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .step-circle.completed {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .step-circle.current {
            background: linear-gradient(135deg, #083E40, #0a5f52);
            color: white;
            box-shadow: 0 0 0 4px rgba(8, 62, 64, 0.2);
        }

        .step-circle.pending {
            background: #e9ecef;
            color: #adb5bd;
        }

        .step-label {
            font-size: 10px;
            color: #6c757d;
            text-align: center;
            max-width: 50px;
        }

        .step-label.current {
            color: #083E40;
            font-weight: 600;
        }

        /* Progress Line */
        .progress-line {
            position: absolute;
            top: 14px;
            left: 28px;
            right: 28px;
            height: 3px;
            background: #e9ecef;
            z-index: 0;
        }

        .progress-line-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #083E40);
            transition: width 0.3s;
        }

        /* Paid Stamp - Circular Design */
        .doc-card.paid {
            position: relative;
            border: 2px solid #28a745;
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.03), rgba(32, 201, 151, 0.03));
        }

        .doc-card.paid .doc-card-header {
            background: linear-gradient(135deg, rgba(40, 167, 69, 0.08), rgba(32, 201, 151, 0.08));
        }

        .paid-stamp {
            position: absolute;
            top: 12px;
            right: 12px;
            width: 70px;
            height: 70px;
            border: 3px solid #28a745;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.95);
            transform: rotate(-15deg);
            z-index: 10;
            box-shadow: 0 2px 8px rgba(40, 167, 69, 0.3);
        }

        .paid-stamp::before {
            content: '';
            position: absolute;
            top: 4px;
            left: 4px;
            right: 4px;
            bottom: 4px;
            border: 2px solid #28a745;
            border-radius: 50%;
        }

        .paid-stamp i {
            font-size: 16px;
            color: #28a745;
            margin-bottom: 2px;
        }

        .paid-stamp-text {
            font-size: 8px;
            font-weight: 700;
            color: #28a745;
            text-transform: uppercase;
            text-align: center;
            line-height: 1.1;
            letter-spacing: 0.5px;
        }

        /* Table View */
        .table-container {
            display: none;
        }

        .table-container.active {
            display: block;
        }

        .tracking-grid.active {
            display: grid;
        }

        .tracking-grid.hidden {
            display: none;
        }
    </style>

    <div class="tracking-container">
        <!-- Header with Search & Filters -->
        <div class="filter-card">
            <form method="GET" action="{{ route('bagian.tracking') }}" id="searchForm">
                <div class="row g-3 mb-3">
                    <!-- General Search -->
                    <div class="col-12">
                        <label class="form-label small text-muted mb-1">
                            <i class="fa-solid fa-search me-1"></i>Cari Dokumen
                        </label>
                        <input type="text" name="search" class="form-control"
                            placeholder="Cari nomor agenda, SPP, uraian, kebun..." value="{{ request('search') }}">
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <!-- Status Filter Buttons -->
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('bagian.tracking') }}"
                            class="btn btn-sm {{ !request('status') ? 'btn-dark' : 'btn-outline-secondary' }}">
                            <i class="fa-solid fa-list me-1"></i>Semua
                        </a>
                        <a href="{{ route('bagian.tracking', array_merge(request()->except('status'), ['status' => 'belum_dikirim'])) }}"
                            class="btn btn-sm {{ request('status') == 'belum_dikirim' ? 'btn-warning' : 'btn-outline-warning' }}">
                            <i class="fa-solid fa-clock me-1"></i>Belum Dikirim
                        </a>
                        <a href="{{ route('bagian.tracking', array_merge(request()->except('status'), ['status' => 'terkirim'])) }}"
                            class="btn btn-sm {{ request('status') == 'terkirim' ? 'btn-info' : 'btn-outline-info' }}">
                            <i class="fa-solid fa-paper-plane me-1"></i>Terkirim
                        </a>
                        <a href="{{ route('bagian.tracking', array_merge(request()->except('status'), ['status' => 'selesai'])) }}"
                            class="btn btn-sm {{ request('status') == 'selesai' ? 'btn-success' : 'btn-outline-success' }}">
                            <i class="fa-solid fa-check-circle me-1"></i>Selesai
                        </a>
                    </div>

                    <!-- Search & View Buttons -->
                    <div class="d-flex gap-2 align-items-center">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-search me-1"></i>Cari
                        </button>
                        <a href="{{ route('bagian.tracking') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fa-solid fa-rotate-left me-1"></i>Reset
                        </a>
                        <div class="view-toggle ms-2">
                            <button type="button" class="btn btn-active" id="btn-card-view" onclick="switchView('card')">
                                <i class="fa-solid fa-th-large me-1"></i>Kartu
                            </button>
                            <button type="button" class="btn btn-inactive" id="btn-table-view"
                                onclick="switchView('table')">
                                <i class="fa-solid fa-table me-1"></i>Tabel
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Card View -->
        <div class="tracking-grid active" id="card-view">
            @foreach($dokumens as $doc)
                @php
                    // Determine workflow step
                    $statusLower = strtolower($doc->status ?? '');
                    $currentHandler = strtolower($doc->current_handler ?? '');

                    // Check if document is paid
                    $isPaid = !empty($doc->tanggal_dibayar);

                    // Workflow stages: 1=Bagian, 2=Operator, 3=Verif, 4=Perpajakan/Akutansi, 5=Pembayaran
                    $step = 1;
                    if ($isPaid) {
                        $step = 6; // All completed
                    } elseif ($statusLower == 'belum dikirim') {
                        $step = 1;
                    } elseif (in_array($currentHandler, ['operator']) || $statusLower == 'menunggu_approval_keuangan') {
                        $step = 1; // Still at Bagian level from Bagian's perspective
                    } elseif ($currentHandler == 'team_verifikasi' || str_contains($statusLower, 'team_verifikasi') || str_contains($statusLower, 'waiting_reviewer')) {
                        $step = 2;
                    } elseif ($currentHandler == 'perpajakan' || str_contains($statusLower, 'perpajakan')) {
                        $step = 3;
                    } elseif ($currentHandler == 'akutansi' || str_contains($statusLower, 'akutansi')) {
                        $step = 4;
                    } elseif ($currentHandler == 'pembayaran' || str_contains($statusLower, 'pembayaran') || $statusLower == 'sudah dibayar') {
                        $step = 5;
                    }

                    // Position display
                    $positionMap = [
                        'operator' => 'Operator',
                        'team_verifikasi' => 'Team Verifikasi',
                        'perpajakan' => 'Perpajakan',
                        'akutansi' => 'Akutansi',
                        'pembayaran' => 'Pembayaran',
                    ];
                    $position = $isPaid ? 'Selesai' : ($positionMap[$currentHandler] ?? ucwords(str_replace('_', ' ', $currentHandler)));

                    // Progress percentage (100% if paid)
                    $progressPercent = $isPaid ? 100 : ((($step - 1) / 4) * 100);
                @endphp
                <div class="doc-card {{ $isPaid ? 'paid' : '' }}"
                    onclick="window.location.href='{{ route('owner.workflow', $doc->id) }}'" style="cursor: pointer;">
                    @if($isPaid)
                        <div class="paid-stamp">
                            <i class="fa-solid fa-check-circle"></i>
                            <span class="paid-stamp-text">SUDAH<br>DIBAYAR</span>
                        </div>
                    @endif
                    <div class="doc-card-header">
                        <div class="doc-agenda">{{ $doc->nomor_agenda }}</div>
                        <div class="doc-spp">SPP: {{ $doc->nomor_spp }}</div>
                        <div class="doc-nilai">Rp {{ number_format($doc->nilai_rupiah, 0, ',', '.') }}</div>
                    </div>
                    <div class="doc-card-body">
                        <div class="doc-position">
                            <i class="fa-solid fa-user"></i>
                            <span>Posisi:</span>
                            <strong>{{ $step == 1 && $statusLower == 'belum dikirim' ? 'Bagian ' . $bagianCode : $position }}</strong>
                        </div>

                        <div class="progress-section">
                            <div class="progress-title">
                                <i class="fa-solid fa-bolt"></i>
                                PROGRES ALUR KERJA
                            </div>
                            <div class="progress-steps">
                                <div class="progress-line">
                                    <div class="progress-line-fill" style="width: {{ $progressPercent }}%"></div>
                                </div>

                                <div class="progress-step">
                                    <div
                                        class="step-circle {{ $step >= 1 ? ($step == 1 ? 'current' : 'completed') : 'pending' }}">
                                        1</div>
                                    <span class="step-label {{ $step == 1 ? 'current' : '' }}">BAGIAN</span>
                                </div>

                                <div class="progress-step">
                                    <div
                                        class="step-circle {{ $step >= 2 ? ($step == 2 ? 'current' : 'completed') : 'pending' }}">
                                        2</div>
                                    <span class="step-label {{ $step == 2 ? 'current' : '' }}">VERIF</span>
                                </div>

                                <div class="progress-step">
                                    <div
                                        class="step-circle {{ $step >= 3 ? ($step == 3 ? 'current' : 'completed') : 'pending' }}">
                                        3</div>
                                    <span class="step-label {{ $step == 3 ? 'current' : '' }}">PERPAJAKAN</span>
                                </div>

                                <div class="progress-step">
                                    <div
                                        class="step-circle {{ $step >= 4 ? ($step == 4 ? 'current' : 'completed') : 'pending' }}">
                                        4</div>
                                    <span class="step-label {{ $step == 4 ? 'current' : '' }}">AKUTANSI</span>
                                </div>

                                <div class="progress-step">
                                    <div class="step-circle {{ $step >= 5 ? 'completed' : 'pending' }}">5</div>
                                    <span class="step-label {{ $step == 5 ? 'current' : '' }}">PEMBAYARAN</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Table View -->
        <div class="table-container" id="table-view">
            <div class="card" style="border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
                <div class="card-body p-0">
                    @if($dokumens->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead style="background: linear-gradient(135deg, #083E40 0%, #0a5f52 100%);">
                                    <tr>
                                        <th style="padding: 16px; color: white; font-size: 12px; font-weight: 600;">NO. AGENDA
                                        </th>
                                        <th style="padding: 16px; color: white; font-size: 12px; font-weight: 600;">NO. SPP</th>
                                        <th style="padding: 16px; color: white; font-size: 12px; font-weight: 600;">NILAI</th>
                                        <th style="padding: 16px; color: white; font-size: 12px; font-weight: 600;">STATUS</th>
                                        <th style="padding: 16px; color: white; font-size: 12px; font-weight: 600;">POSISI</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dokumens as $doc)
                                        @php
                                            $statusLower = strtolower($doc->status ?? '');
                                            if ($statusLower == 'belum dikirim') {
                                                $statusText = 'Belum Dikirim';
                                                $statusColor = '#ffc107';
                                            } elseif ($statusLower == 'menunggu_approval_keuangan') {
                                                $statusText = 'Menunggu Approve';
                                                $statusColor = '#e0a800';
                                            } else {
                                                $statusText = 'Terkirim';
                                                $statusColor = '#28a745';
                                            }

                                            $positionMap = [
                                                'operator' => 'Operator',
                                                'team_verifikasi' => 'Team Verifikasi',
                                                'perpajakan' => 'Perpajakan',
                                                'akutansi' => 'Akutansi',
                                                'pembayaran' => 'Pembayaran',
                                            ];
                                            $position = $positionMap[$doc->current_handler] ?? ucwords(str_replace('_', ' ', $doc->current_handler));
                                        @endphp
                                        <tr>
                                            <td style="padding: 16px; vertical-align: middle;">
                                                <strong style="color: #083E40;">{{ $doc->nomor_agenda }}</strong>
                                            </td>
                                            <td style="padding: 16px; vertical-align: middle;">{{ $doc->nomor_spp }}</td>
                                            <td style="padding: 16px; vertical-align: middle;">
                                                <strong style="color: #28a745;">Rp.
                                                    {{ number_format($doc->nilai_rupiah, 0, ',', '.') }}</strong>
                                            </td>
                                            <td style="padding: 16px; vertical-align: middle;">
                                                <span class="badge"
                                                    style="background: {{ $statusColor }}; padding: 6px 12px; border-radius: 20px;">
                                                    {{ $statusText }}
                                                </span>
                                            </td>
                                            <td style="padding: 16px; vertical-align: middle;">
                                                <span style="color: #6c757d;">
                                                    <i class="fa-solid fa-location-dot me-1"></i>{{ $position }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fa-solid fa-route" style="font-size: 64px; color: #dee2e6; margin-bottom: 20px;"></i>
                            <h5 class="text-muted">Tidak ada dokumen untuk dilacak</h5>
                            <p class="text-muted mb-4">Buat dokumen terlebih dahulu</p>
                            <a href="{{ route('bagian.documents.create') }}" class="btn btn-primary"
                                style="background: #083E40; border: none;">
                                <i class="fa-solid fa-plus me-2"></i>Buat Dokumen
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @if($dokumens->count() == 0)
            <div class="text-center py-5">
                <i class="fa-solid fa-folder-open" style="font-size: 64px; color: #dee2e6; margin-bottom: 20px;"></i>
                <h5 class="text-muted">Belum ada dokumen</h5>
                <p class="text-muted">Buat dokumen baru untuk mulai tracking</p>
            </div>
        @endif
    </div>

    <script>
        function switchView(view) {
            const cardView = document.getElementById('card-view');
            const tableView = document.getElementById('table-view');
            const btnCard = document.getElementById('btn-card-view');
            const btnTable = document.getElementById('btn-table-view');

            if (view === 'card') {
                cardView.classList.add('active');
                cardView.classList.remove('hidden');
                tableView.classList.remove('active');
                btnCard.classList.add('btn-active');
                btnCard.classList.remove('btn-inactive');
                btnTable.classList.add('btn-inactive');
                btnTable.classList.remove('btn-active');
            } else {
                cardView.classList.remove('active');
                cardView.classList.add('hidden');
                tableView.classList.add('active');
                btnCard.classList.add('btn-inactive');
                btnCard.classList.remove('btn-active');
                btnTable.classList.add('btn-active');
                btnTable.classList.remove('btn-inactive');
            }
        }
    </script>
@endsection