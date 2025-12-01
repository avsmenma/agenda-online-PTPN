@extends('layouts/app')
@section('content')

<style>
  h2 {
    background: linear-gradient(135deg, #083E40 0%, #889717 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 24px;
    font-weight: 700;
  }

  .chart-container {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
    border: 1px solid rgba(8, 62, 64, 0.08);
    position: relative;
  }

  .chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 2px solid rgba(8, 62, 64, 0.1);
  }

  .chart-title {
    font-size: 16px;
    font-weight: 700;
    color: #083E40;
    letter-spacing: 0.3px;
  }

  .chart-actions {
    display: flex;
    gap: 10px;
    align-items: center;
  }

  .chart-filter {
    padding: 8px 16px;
    border: 2px solid rgba(8, 62, 64, 0.1);
    border-radius: 10px;
    font-size: 13px;
    font-weight: 500;
    background: white;
    color: #083E40;
    cursor: pointer;
    transition: all 0.3s ease;
  }

  .chart-filter:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 4px rgba(136, 151, 23, 0.1);
  }

  .dropTahun {
    padding: 8px 12px;
    color: #083E40;
    border: 2px solid rgba(8, 62, 64, 0.15);
    border-radius: 10px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(8, 62, 64, 0.1);
    background: white;
  }

  .dropTahun:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.2);
    border-color: #889717;
  }

  .dropTahun:focus {
    outline: none;
    border-color: #889717;
    box-shadow: 0 0 0 4px rgba(136, 151, 23, 0.1);
  }

  .chart-wrapper {
    position: relative;
    min-height: 300px;
    padding: 20px 0;
  }

  .legend {
    display: flex;
    gap: 24px;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid rgba(8, 62, 64, 0.1);
  }

  .legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 500;
  }

  .legend-color {
    width: 16px;
    height: 16px;
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .color-teal {
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
  }

  .color-orange {
    background: linear-gradient(135deg, #ff9800 0%, #fb8c00 100%);
  }

  .color-yellow {
    background: linear-gradient(135deg, #889717 0%, #9ab01f 100%);
  }

  .color-blue {
    background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
  }

  .color-red {
    background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);
  }

  .color-pink {
    background: linear-gradient(135deg, #e91e63 0%, #c2185b 100%);
  }

  /* Chart specific styles */
  canvas {
    max-height: 350px;
  }

  .status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
    position: absolute;
    top: 24px;
    right: 24px;
  }

  .status-dot.active {
    background: #28a745;
    box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.2);
    animation: pulse 2s infinite;
  }

  @keyframes pulse {
    0%, 100% {
      opacity: 1;
    }
    50% {
      opacity: 0.5;
    }
  }
</style>

<h2 style="margin-bottom: 20px; font-weight: 700;">{{ $title }}</h2>

<!-- Chart 1: Statistik Jumlah Dokumen (Line Chart) -->
<div class="chart-container">
  <div class="status-dot active"></div>
  <div class="chart-header">
    <h3 class="chart-title">Statistik Jumlah Dokumen</h3>
    <div class="chart-actions">
      <form method="GET" action="{{ route('diagramPembayaran.index') }}" style="display: inline-block;">
        <select name="year" id="yearFilter1" class="dropTahun" onchange="this.form.submit()" style="width: 120px; padding: 8px 12px;">
          @foreach($availableYears as $year)
            <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
          @endforeach
        </select>
      </form>
    </div>
  </div>
  <div class="chart-wrapper">
    <canvas id="lineChart"></canvas>
  </div>
</div>

<!-- Chart 2: Statistik Keterlambatan Dokumen (Dot Chart) -->
<div class="chart-container">
  <div class="status-dot active"></div>
  <div class="chart-header">
    <h3 class="chart-title">Statistik Keterlambatan Dokumen</h3>
    <div class="chart-actions">
      <form method="GET" action="{{ route('diagramPembayaran.index') }}" style="display: inline-block;">
        <select name="year" id="yearFilter2" class="dropTahun" onchange="this.form.submit()" style="width: 120px; padding: 8px 12px;">
          @foreach($availableYears as $year)
            <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
          @endforeach
        </select>
      </form>
    </div>
  </div>
  <div class="chart-wrapper">
    <canvas id="dotChart"></canvas>
  </div>
  <div class="legend">
    <div class="legend-item">
      <span class="legend-color color-orange"></span>
      <span>Keterlambatan (%)</span>
    </div>
    <div class="legend-item">
      <span class="legend-color color-yellow"></span>
      <span>Ketepatan (%)</span>
    </div>
  </div>
</div>

<!-- Chart 3: Statistik Jumlah Dokumen Selesai (Bar Chart) -->
<div class="chart-container">
  <div class="status-dot active"></div>
  <div class="chart-header">
    <h3 class="chart-title">Statistik Jumlah Dokumen Selesai</h3>
    <div class="chart-actions">
      <form method="GET" action="{{ route('diagramPembayaran.index') }}" style="display: inline-block;">
        <select name="year" id="yearFilter3" class="dropTahun" onchange="this.form.submit()" style="width: 120px; padding: 8px 12px;">
          @foreach($availableYears as $year)
            <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
          @endforeach
        </select>
      </form>
    </div>
  </div>
  <div class="chart-wrapper">
    <canvas id="barChart"></canvas>
  </div>
  <div class="legend">
    <div class="legend-item">
      <span class="legend-color color-red"></span>
      <span>Dokumen Tidak Selesai</span>
    </div>
    <div class="legend-item">
      <span class="legend-color color-blue"></span>
      <span>Dokumen Selesai (Sudah Dibayar)</span>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // Data from controller
  const monthlyData = @json($monthlyData);
  const keterlambatanData = @json($keterlambatanData);
  const ketepatanData = @json($ketepatanData);
  const selesaiData = @json($selesaiData);
  const tidakSelesaiData = @json($tidakSelesaiData);
  const months = @json($months);
  const monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

  // Chart 1: Line Chart - Statistik Jumlah Dokumen
  const lineCtx = document.getElementById('lineChart').getContext('2d');
  const lineChart = new Chart(lineCtx, {
    type: 'line',
    data: {
      labels: months,
      datasets: [{
        label: 'Jumlah Dokumen',
        data: monthlyData,
        borderColor: '#083E40',
        backgroundColor: 'rgba(8, 62, 64, 0.1)',
        borderWidth: 3,
        tension: 0.4,
        fill: true,
        pointBackgroundColor: '#083E40',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointRadius: 5,
        pointHoverRadius: 7
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          backgroundColor: 'rgba(8, 62, 64, 0.9)',
          padding: 12,
          cornerRadius: 8,
          titleFont: {
            size: 14,
            weight: 'bold'
          },
          bodyFont: {
            size: 13
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: {
            color: 'rgba(8, 62, 64, 0.05)',
            drawBorder: false
          },
          ticks: {
            color: '#083E40',
            font: {
              size: 12,
              weight: '500'
            }
          }
        },
        x: {
          grid: {
            display: false,
            drawBorder: false
          },
          ticks: {
            color: '#083E40',
            font: {
              size: 11,
              weight: '500'
            }
          }
        }
      }
    }
  });

  // Chart 2: Dot/Bubble Chart - Statistik Keterlambatan Dokumen
  const dotCtx = document.getElementById('dotChart').getContext('2d');
  
  // Prepare bubble chart data
  const keterlambatanBubbleData = keterlambatanData.map((value, index) => ({
    x: index,
    y: value,
    r: 8
  }));
  
  const ketepatanBubbleData = ketepatanData.map((value, index) => ({
    x: index,
    y: value,
    r: 8
  }));
  
  const dotChart = new Chart(dotCtx, {
    type: 'bubble',
    data: {
      labels: monthLabels,
      datasets: [
        {
          label: 'Keterlambatan',
          data: keterlambatanBubbleData,
          backgroundColor: 'rgba(255, 152, 0, 0.7)',
          borderColor: '#ff9800',
          borderWidth: 2
        },
        {
          label: 'Ketepatan',
          data: ketepatanBubbleData,
          backgroundColor: 'rgba(136, 151, 23, 0.7)',
          borderColor: '#889717',
          borderWidth: 2
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          backgroundColor: 'rgba(8, 62, 64, 0.9)',
          padding: 12,
          cornerRadius: 8
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          max: 100,
          grid: {
            color: 'rgba(8, 62, 64, 0.05)',
            drawBorder: false
          },
          ticks: {
            color: '#083E40',
            font: {
              size: 12,
              weight: '500'
            },
            callback: function(value) {
              return value + '%';
            }
          }
        },
        x: {
          type: 'category',
          labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
          grid: {
            display: false,
            drawBorder: false
          },
          ticks: {
            color: '#083E40',
            font: {
              size: 11,
              weight: '500'
            }
          }
        }
      }
    }
  });

  // Chart 3: Bar Chart - Statistik Jumlah Dokumen Selesai
  const barCtx = document.getElementById('barChart').getContext('2d');
  const barChart = new Chart(barCtx, {
    type: 'bar',
    data: {
      labels: monthLabels,
      datasets: [
        {
          label: 'Dokumen Tidak Selesai',
          data: tidakSelesaiData,
          backgroundColor: '#f44336',
          borderRadius: 8,
          borderSkipped: false
        },
        {
          label: 'Dokumen Selesai',
          data: selesaiData,
          backgroundColor: '#2196f3',
          borderRadius: 8,
          borderSkipped: false
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          backgroundColor: 'rgba(8, 62, 64, 0.9)',
          padding: 12,
          cornerRadius: 8
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: {
            color: 'rgba(8, 62, 64, 0.05)',
            drawBorder: false
          },
          ticks: {
            color: '#083E40',
            font: {
              size: 12,
              weight: '500'
            }
          }
        },
        x: {
          grid: {
            display: false,
            drawBorder: false
          },
          ticks: {
            color: '#083E40',
            font: {
              size: 11,
              weight: '500'
            }
          }
        }
      }
    }
  });
</script>

@endsection