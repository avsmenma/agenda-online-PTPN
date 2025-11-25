@extends('layouts/app');
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

  .btn-export {
    padding: 8px 20px;
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%);
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(8, 62, 64, 0.2);
  }

  .btn-export:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(8, 62, 64, 0.3);
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

<h2>{{ $title }}</h2>

<!-- Chart 1: Statistik Jumlah Dokumen (Line Chart) -->
<div class="chart-container">
  <div class="status-dot active"></div>
  <div class="chart-header">
    <h3 class="chart-title">Statistik Jumlah Dokumen</h3>
    <div class="chart-actions">
      <button class="btn-export">Export</button>
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
      <button class="btn-export">Export</button>
    </div>
  </div>
  <div class="chart-wrapper">
    <canvas id="dotChart"></canvas>
  </div>
  <div class="legend">
    <div class="legend-item">
      <span class="legend-color color-teal"></span>
      <span>Kategori 1</span>
    </div>
    <div class="legend-item">
      <span class="legend-color color-orange"></span>
      <span>Kategori 2</span>
    </div>
    <div class="legend-item">
      <span class="legend-color color-yellow"></span>
      <span>Kategori 3</span>
    </div>
  </div>
</div>

<!-- Chart 3: Statistik Jumlah Dokumen Selesai (Bar Chart) -->
<div class="chart-container">
  <div class="status-dot active"></div>
  <div class="chart-header">
    <h3 class="chart-title">Statistik Jumlah Dokumen Selesai</h3>
    <div class="chart-actions">
      <button class="btn-export">Export</button>
    </div>
  </div>
  <div class="chart-wrapper">
    <canvas id="barChart"></canvas>
  </div>
  <div class="legend">
    <div class="legend-item">
      <span class="legend-color color-red"></span>
      <span>Kategori A</span>
    </div>
    <div class="legend-item">
      <span class="legend-color color-blue"></span>
      <span>Kategori B</span>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  // Chart 1: Line Chart - Statistik Jumlah Dokumen
  const lineCtx = document.getElementById('lineChart').getContext('2d');
  const lineChart = new Chart(lineCtx, {
    type: 'line',
    data: {
      labels: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
      datasets: [{
        label: 'Jumlah Dokumen',
        data: [65, 59, 80, 81, 56, 95, 70, 85, 60, 90, 75, 88],
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
  const dotChart = new Chart(dotCtx, {
    type: 'bubble',
    data: {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
      datasets: [
        {
          label: 'Kategori 1',
          data: [
            {x: 0, y: 50, r: 8},
            {x: 1, y: 30, r: 8},
            {x: 2, y: 70, r: 8},
            {x: 3, y: 40, r: 8},
            {x: 4, y: 60, r: 8},
            {x: 5, y: 35, r: 8},
            {x: 6, y: 55, r: 8},
            {x: 7, y: 45, r: 8},
            {x: 8, y: 65, r: 8},
            {x: 9, y: 50, r: 8},
            {x: 10, y: 40, r: 8},
            {x: 11, y: 60, r: 8}
          ],
          backgroundColor: '#083E40'
        },
        {
          label: 'Kategori 2',
          data: [
            {x: 0, y: 65, r: 8},
            {x: 1, y: 45, r: 8},
            {x: 2, y: 55, r: 8},
            {x: 3, y: 70, r: 8},
            {x: 4, y: 50, r: 8},
            {x: 5, y: 60, r: 8},
            {x: 6, y: 40, r: 8},
            {x: 7, y: 75, r: 8},
            {x: 8, y: 55, r: 8},
            {x: 9, y: 65, r: 8},
            {x: 10, y: 70, r: 8},
            {x: 11, y: 50, r: 8}
          ],
          backgroundColor: '#ff9800'
        },
        {
          label: 'Kategori 3',
          data: [
            {x: 0, y: 80, r: 8},
            {x: 1, y: 60, r: 8},
            {x: 2, y: 40, r: 8},
            {x: 3, y: 85, r: 8},
            {x: 4, y: 35, r: 8},
            {x: 5, y: 75, r: 8},
            {x: 6, y: 70, r: 8},
            {x: 7, y: 60, r: 8},
            {x: 8, y: 80, r: 8},
            {x: 9, y: 45, r: 8},
            {x: 10, y: 85, r: 8},
            {x: 11, y: 75, r: 8}
          ],
          backgroundColor: '#889717'
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
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
      datasets: [
        {
          label: 'Kategori A',
          data: [45, 52, 38, 60, 42, 55, 48, 65, 50, 58, 44, 62],
          backgroundColor: '#f44336',
          borderRadius: 8,
          borderSkipped: false
        },
        {
          label: 'Kategori B',
          data: [35, 42, 48, 40, 52, 45, 58, 50, 55, 48, 60, 52],
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