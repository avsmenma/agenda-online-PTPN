@extends('layouts/app')
@section('content')

    <style>
        /* Color Variables */
        :root {
            --primary-color: #1a4d3e;
            --primary-dark: #0f3d2e;
            --secondary-color: #40916c;
            --primary-rgba: rgba(26, 77, 62, 0.1);
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        h2 {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 30px;
            font-weight: 700;
            font-size: 28px;
        }

        /* Filter Container */
        .filter-container {
            background: #ffffff;
            border-radius: 16px;
            padding: 20px 28px;
            margin-bottom: 28px;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 24px;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .filter-label {
            font-size: 14px;
            font-weight: 600;
            color: var(--primary-color);
            white-space: nowrap;
        }

        .filter-select {
            padding: 10px 18px;
            border: 2px solid rgba(26, 77, 62, 0.15);
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            color: #333;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 160px;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--primary-rgba);
        }

        .filter-select:hover {
            border-color: var(--secondary-color);
        }

        /* Chart Container */
        .chart-container {
            background: #ffffff;
            border-radius: 16px;
            padding: 28px;
            margin-bottom: 28px;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .chart-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chart-title i {
            font-size: 20px;
        }

        .chart-wrapper {
            position: relative;
            height: 400px;
            width: 100%;
        }

        /* Performance Cards Grid */
        .performance-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
            margin-bottom: 28px;
        }

        .performance-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 24px;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .performance-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .performance-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--card-color);
        }

        .performance-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
        }

        .performance-role {
            font-size: 14px;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .performance-grade {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: 800;
            color: white;
        }

        .performance-score {
            font-size: 36px;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 8px;
        }

        .performance-label {
            font-size: 13px;
            color: #888;
            margin-bottom: 16px;
        }

        .performance-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            padding-top: 16px;
            border-top: 1px solid rgba(0, 0, 0, 0.06);
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .stat-value.safe {
            color: #28a745;
        }

        .stat-value.warning {
            color: #ffc107;
        }

        .stat-value.danger {
            color: #dc3545;
        }

        .stat-label {
            font-size: 10px;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* Legend */
        .chart-legend {
            display: flex;
            justify-content: center;
            gap: 24px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #666;
        }

        .legend-dot {
            width: 14px;
            height: 14px;
            border-radius: 4px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .filter-container {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-group {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-select {
                width: 100%;
            }

            .chart-wrapper {
                height: 300px;
            }

            .performance-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Summary Section */
        .summary-section {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border-radius: 16px;
            padding: 24px 28px;
            margin-bottom: 28px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .summary-title {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .summary-subtitle {
            font-size: 14px;
            opacity: 0.85;
        }

        .summary-stats {
            display: flex;
            gap: 32px;
            flex-wrap: wrap;
        }

        .summary-stat {
            text-align: center;
        }

        .summary-stat-value {
            font-size: 28px;
            font-weight: 800;
        }

        .summary-stat-label {
            font-size: 12px;
            opacity: 0.85;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Settings Button */
        .settings-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border: 2px solid rgba(26, 77, 62, 0.15);
            border-radius: 10px;
            font-size: 14px;
            font-weight: 600;
            color: var(--primary-color);
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-left: auto;
        }

        .settings-btn:hover {
            border-color: var(--primary-color);
            background: var(--primary-rgba);
        }

        .settings-btn i {
            font-size: 16px;
        }

        /* Settings Modal */
        .settings-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.2s ease;
        }

        .settings-modal-overlay.active {
            display: flex;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .settings-modal {
            background: white;
            border-radius: 16px;
            width: 100%;
            max-width: 480px;
            margin: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .settings-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 24px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.08);
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border-radius: 16px 16px 0 0;
            color: white;
        }

        .settings-modal-title {
            font-size: 18px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .settings-modal-close {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: none;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .settings-modal-close:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .settings-modal-body {
            padding: 24px;
        }

        .settings-description {
            font-size: 14px;
            color: #666;
            margin-bottom: 24px;
            line-height: 1.6;
            background: #f8f9fa;
            padding: 12px 16px;
            border-radius: 8px;
            border-left: 4px solid var(--primary-color);
        }

        .settings-form-group {
            margin-bottom: 20px;
        }

        .settings-label {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .settings-label .label-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .settings-label .label-dot.safe {
            background: #28a745;
        }

        .settings-label .label-dot.warning {
            background: #ffc107;
        }

        .settings-label .label-dot.danger {
            background: #dc3545;
        }

        .settings-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            color: #333;
            transition: all 0.2s ease;
        }

        .settings-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px var(--primary-rgba);
        }

        .settings-input-hint {
            font-size: 12px;
            color: #888;
            margin-top: 4px;
        }

        .settings-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding: 20px 24px;
            border-top: 1px solid rgba(0, 0, 0, 0.08);
            background: #f8f9fa;
            border-radius: 0 0 16px 16px;
        }

        .settings-btn-cancel {
            padding: 10px 20px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            background: white;
            color: #666;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .settings-btn-cancel:hover {
            border-color: #ccc;
            background: #f8f9fa;
        }

        .settings-btn-save {
            padding: 10px 24px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .settings-btn-save:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .settings-btn-reset {
            padding: 10px 20px;
            border: 2px solid #ffc107;
            border-radius: 8px;
            background: #fff8e1;
            color: #856404;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-right: auto;
        }

        .settings-btn-reset:hover {
            background: #ffc107;
            color: #333;
        }
    </style>

    <div class="container-fluid py-4">
        <h2><i class="fa-solid fa-chart-line me-2"></i>Analisis Kinerja</h2>

        <!-- Summary Section -->
        <div class="summary-section">
            <div>
                <div class="summary-title">Dashboard Performa Tim</div>
                <div class="summary-subtitle">Analisis kinerja pengelolaan dokumen per departemen</div>
            </div>
            <div class="summary-stats">
                @php
                    $totalDocs = collect($analyticsData)->sum(fn($r) => $r['stats']['total']);
                    $totalAman = collect($analyticsData)->sum(fn($r) => $r['stats']['aman']);
                    $totalTerlambat = collect($analyticsData)->sum(fn($r) => $r['stats']['terlambat']);
                  @endphp
                <div class="summary-stat">
                    <div class="summary-stat-value">{{ $totalDocs }}</div>
                    <div class="summary-stat-label">Total Dokumen</div>
                </div>
                <div class="summary-stat">
                    <div class="summary-stat-value">{{ $totalAman }}</div>
                    <div class="summary-stat-label">Tepat Waktu</div>
                </div>
                <div class="summary-stat">
                    <div class="summary-stat-value">{{ $totalTerlambat }}</div>
                    <div class="summary-stat-label">Terlambat</div>
                </div>
            </div>
        </div>

        <!-- Filter Container -->
        <div class="filter-container">
            <div class="filter-group">
                <label class="filter-label"><i class="fa-solid fa-calendar me-2"></i>Bulan</label>
                <select class="filter-select" id="filterBulan">
                    <option value="0" {{ $bulan == 0 ? 'selected' : '' }}>Semua Bulan</option>
                    @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $index => $namaBulan)
                        <option value="{{ $index + 1 }}" {{ $bulan == $index + 1 ? 'selected' : '' }}>{{ $namaBulan }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label"><i class="fa-solid fa-calendar-days me-2"></i>Tahun</label>
                <select class="filter-select" id="filterTahun">
                    @foreach($availableYears as $year)
                        <option value="{{ $year }}" {{ $tahun == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Settings Button -->
            <button class="settings-btn" onclick="openSettingsModal()">
                <i class="fa-solid fa-cog"></i>
                <span>Pengaturan</span>
            </button>
        </div>

        <!-- Settings Modal -->
        <div class="settings-modal-overlay" id="settingsModal">
            <div class="settings-modal">
                <div class="settings-modal-header">
                    <div class="settings-modal-title">
                        <i class="fa-solid fa-sliders"></i>
                        Pengaturan Perhitungan Performa
                    </div>
                    <button class="settings-modal-close" onclick="closeSettingsModal()">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>
                <div class="settings-modal-body">
                    <div class="settings-description">
                        <strong>Rumus Perhitungan:</strong> Score = ((Aman × Nilai) + (Peringatan × Nilai) + (Terlambat × Nilai)) / Total Dokumen
                    </div>

                    <div class="settings-form-group">
                        <label class="settings-label">
                            <span class="label-dot safe"></span>
                            Score Dokumen Aman (< 24 jam)
                        </label>
                        <input type="number" class="settings-input" id="scoreAman" min="0" max="100" value="100">
                        <div class="settings-input-hint">Nilai default: 100 poin</div>
                    </div>

                    <div class="settings-form-group">
                        <label class="settings-label">
                            <span class="label-dot warning"></span>
                            Score Dokumen Peringatan (24-72 jam)
                        </label>
                        <input type="number" class="settings-input" id="scorePeringatan" min="0" max="100" value="50">
                        <div class="settings-input-hint">Nilai default: 50 poin</div>
                    </div>

                    <div class="settings-form-group">
                        <label class="settings-label">
                            <span class="label-dot danger"></span>
                            Score Dokumen Terlambat (≥ 72 jam)
                        </label>
                        <input type="number" class="settings-input" id="scoreTerlambat" min="0" max="100" value="0">
                        <div class="settings-input-hint">Nilai default: 0 poin</div>
                    </div>
                </div>
                <div class="settings-modal-footer">
                    <button class="settings-btn-reset" onclick="resetSettings()">
                        <i class="fa-solid fa-undo me-1"></i> Reset Default
                    </button>
                    <button class="settings-btn-cancel" onclick="closeSettingsModal()">Batal</button>
                    <button class="settings-btn-save" onclick="saveSettings()">
                        <i class="fa-solid fa-check me-1"></i> Simpan
                    </button>
                </div>
            </div>
        </div>

        <!-- Performance Cards -->
        <div class="performance-grid">
            @foreach($analyticsData as $roleCode => $data)
                <div class="performance-card" style="--card-color: {{ $data['color'] }}">
                    <div class="performance-header">
                        <div class="performance-role">{{ $data['name'] }}</div>
                        <div class="performance-grade" style="background: {{ $data['performance']['color'] }}">
                            {{ $data['performance']['grade'] }}
                        </div>
                    </div>
                    <div class="performance-score">{{ $data['performance']['score'] }}%</div>
                    <div class="performance-label">Performance Score</div>
                    <div class="performance-stats">
                        <div class="stat-item">
                            <div class="stat-value safe">{{ $data['stats']['aman'] }}</div>
                            <div class="stat-label">Aman</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value warning">{{ $data['stats']['peringatan'] }}</div>
                            <div class="stat-label">Warning</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value danger">{{ $data['stats']['terlambat'] }}</div>
                            <div class="stat-label">Terlambat</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Chart Container -->
        <div class="chart-container">
            <div class="chart-title">
                <i class="fa-solid fa-chart-bar"></i>
                Perbandingan Kinerja Antar Tim
            </div>
            <div class="chart-wrapper">
                <canvas id="performanceChart"></canvas>
            </div>
            <div class="chart-legend">
                <div class="legend-item">
                    <div class="legend-dot" style="background: #6c757d;"></div>
                    <span>Total Dokumen</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot" style="background: #28a745;"></div>
                    <span>Aman (< 24 jam)</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot" style="background: #ffc107;"></div>
                    <span>Peringatan (24-72 jam)</span>
                </div>
                <div class="legend-item">
                    <div class="legend-dot" style="background: #dc3545;"></div>
                    <span>Terlambat (≥ 72 jam)</span>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Default score settings
        const DEFAULT_SCORES = {
            aman: 100,
            peringatan: 50,
            terlambat: 0
        };

        // Get current settings from localStorage or use defaults
        function getScoreSettings() {
            const saved = localStorage.getItem('performanceScoreSettings');
            if (saved) {
                try {
                    return JSON.parse(saved);
                } catch (e) {
                    return { ...DEFAULT_SCORES };
                }
            }
            return { ...DEFAULT_SCORES };
        }

        // Save settings to localStorage
        function saveScoreSettings(settings) {
            localStorage.setItem('performanceScoreSettings', JSON.stringify(settings));
        }

        // Calculate performance score with custom weights
        function calculateCustomScore(aman, peringatan, terlambat, total, settings) {
            if (total === 0) return { score: 0, grade: '-', color: '#6c757d' };
            
            const totalPoints = (aman * settings.aman) + (peringatan * settings.peringatan) + (terlambat * settings.terlambat);
            const score = totalPoints / total;
            
            let grade, color;
            if (score >= 90) {
                grade = 'A';
                color = '#28a745';
            } else if (score >= 75) {
                grade = 'B';
                color = '#17a2b8';
            } else if (score >= 60) {
                grade = 'C';
                color = '#ffc107';
            } else {
                grade = 'D';
                color = '#dc3545';
            }
            
            return { score: Math.round(score * 10) / 10, grade, color };
        }

        // Update all performance cards with new scores
        function updatePerformanceDisplay() {
            const settings = getScoreSettings();
            const analyticsData = @json($analyticsData);
            
            Object.keys(analyticsData).forEach((roleCode, index) => {
                const data = analyticsData[roleCode];
                const stats = data.stats;
                const newPerformance = calculateCustomScore(
                    stats.aman, 
                    stats.peringatan, 
                    stats.terlambat, 
                    stats.total, 
                    settings
                );
                
                // Update the performance card
                const cards = document.querySelectorAll('.performance-card');
                if (cards[index]) {
                    const scoreEl = cards[index].querySelector('.performance-score');
                    const gradeEl = cards[index].querySelector('.performance-grade');
                    
                    if (scoreEl) scoreEl.textContent = newPerformance.score + '%';
                    if (gradeEl) {
                        gradeEl.textContent = newPerformance.grade;
                        gradeEl.style.background = newPerformance.color;
                    }
                }
            });
        }

        // Modal functions
        function openSettingsModal() {
            const settings = getScoreSettings();
            document.getElementById('scoreAman').value = settings.aman;
            document.getElementById('scorePeringatan').value = settings.peringatan;
            document.getElementById('scoreTerlambat').value = settings.terlambat;
            document.getElementById('settingsModal').classList.add('active');
        }

        function closeSettingsModal() {
            document.getElementById('settingsModal').classList.remove('active');
        }

        function saveSettings() {
            const settings = {
                aman: parseInt(document.getElementById('scoreAman').value) || 0,
                peringatan: parseInt(document.getElementById('scorePeringatan').value) || 0,
                terlambat: parseInt(document.getElementById('scoreTerlambat').value) || 0
            };
            
            saveScoreSettings(settings);
            updatePerformanceDisplay();
            closeSettingsModal();
            
            // Show success feedback
            const btn = document.querySelector('.settings-btn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Tersimpan!';
            btn.style.background = '#d4edda';
            btn.style.borderColor = '#28a745';
            btn.style.color = '#28a745';
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.style.background = '';
                btn.style.borderColor = '';
                btn.style.color = '';
            }, 2000);
        }

        function resetSettings() {
            document.getElementById('scoreAman').value = DEFAULT_SCORES.aman;
            document.getElementById('scorePeringatan').value = DEFAULT_SCORES.peringatan;
            document.getElementById('scoreTerlambat').value = DEFAULT_SCORES.terlambat;
        }

        // Close modal on overlay click
        document.getElementById('settingsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeSettingsModal();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSettingsModal();
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            // Apply saved settings on page load
            updatePerformanceDisplay();

            // Chart data from PHP
            const analyticsData = @json($analyticsData);

            // Prepare chart data
            const labels = [];
            const totalData = [];
            const amanData = [];
            const peringatanData = [];
            const terlambatData = [];

            Object.keys(analyticsData).forEach(roleCode => {
                const role = analyticsData[roleCode];
                labels.push(role.name);
                totalData.push(role.stats.total);
                amanData.push(role.stats.aman);
                peringatanData.push(role.stats.peringatan);
                terlambatData.push(role.stats.terlambat);
            });

            // Create grouped bar chart
            const ctx = document.getElementById('performanceChart').getContext('2d');
            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Total Dokumen',
                            data: totalData,
                            backgroundColor: '#6c757d',
                            borderRadius: 6,
                            barPercentage: 0.7,
                            categoryPercentage: 0.8,
                        },
                        {
                            label: 'Aman',
                            data: amanData,
                            backgroundColor: '#28a745',
                            borderRadius: 6,
                            barPercentage: 0.7,
                            categoryPercentage: 0.8,
                        },
                        {
                            label: 'Peringatan',
                            data: peringatanData,
                            backgroundColor: '#ffc107',
                            borderRadius: 6,
                            barPercentage: 0.7,
                            categoryPercentage: 0.8,
                        },
                        {
                            label: 'Terlambat',
                            data: terlambatData,
                            backgroundColor: '#dc3545',
                            borderRadius: 6,
                            barPercentage: 0.7,
                            categoryPercentage: 0.8,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: { size: 14, weight: 'bold' },
                            bodyFont: { size: 13 },
                            cornerRadius: 8,
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: { size: 13, weight: '600' },
                                color: '#333'
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                font: { size: 12 },
                                color: '#666'
                            }
                        }
                    }
                }
            });

            // Filter change handlers
            document.getElementById('filterBulan').addEventListener('change', updateFilter);
            document.getElementById('filterTahun').addEventListener('change', updateFilter);

            function updateFilter() {
                const bulan = document.getElementById('filterBulan').value;
                const tahun = document.getElementById('filterTahun').value;
                window.location.href = `{{ route('analytics.index') }}?bulan=${bulan}&tahun=${tahun}`;
            }
        });
    </script>

@endsection