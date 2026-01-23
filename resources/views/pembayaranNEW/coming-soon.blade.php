@extends('layouts/app')
@section('content')

<style>
  .coming-soon-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 60vh;
    padding: 40px 20px;
    text-align: center;
  }

  .coming-soon-icon {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, #083E40 0%, #0a4f52 50%, #889717 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 30px;
    box-shadow: 0 12px 40px rgba(8, 62, 64, 0.2), 0 4px 16px rgba(136, 151, 23, 0.1);
    animation: pulse 2s ease-in-out infinite;
  }

  @keyframes pulse {
    0%, 100% {
      transform: scale(1);
      box-shadow: 0 12px 40px rgba(8, 62, 64, 0.2), 0 4px 16px rgba(136, 151, 23, 0.1);
    }
    50% {
      transform: scale(1.05);
      box-shadow: 0 16px 50px rgba(8, 62, 64, 0.3), 0 6px 20px rgba(136, 151, 23, 0.15);
    }
  }

  .coming-soon-icon i {
    font-size: 60px;
    color: white;
  }

  .coming-soon-title {
    font-size: 36px;
    font-weight: 700;
    background: linear-gradient(135deg, #083E40 0%, #889717 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 16px;
  }

  .coming-soon-message {
    font-size: 18px;
    color: #6c757d;
    margin-bottom: 40px;
    max-width: 600px;
    line-height: 1.6;
  }

  .coming-soon-features {
    background: linear-gradient(135deg, #ffffff 0%, #f8faf8 100%);
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 8px 32px rgba(8, 62, 64, 0.1), 0 2px 8px rgba(136, 151, 23, 0.05);
    border: 1px solid rgba(8, 62, 64, 0.08);
    max-width: 500px;
    margin-top: 30px;
  }

  .coming-soon-features h3 {
    font-size: 20px;
    font-weight: 600;
    color: #083E40;
    margin-bottom: 20px;
  }

  .coming-soon-features ul {
    list-style: none;
    padding: 0;
    margin: 0;
    text-align: left;
  }

  .coming-soon-features li {
    padding: 12px 0;
    color: #6c757d;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .coming-soon-features li i {
    color: #889717;
    font-size: 18px;
  }

  @media (max-width: 768px) {
    .coming-soon-title {
      font-size: 28px;
    }

    .coming-soon-message {
      font-size: 16px;
    }

    .coming-soon-icon {
      width: 100px;
      height: 100px;
    }

    .coming-soon-icon i {
      font-size: 50px;
    }
  }
</style>

<div class="coming-soon-container">
  <div class="coming-soon-icon">
    <i class="fa-solid fa-hourglass-half"></i>
  </div>
  
  <h1 class="coming-soon-title">Fitur Coming Soon</h1>
  
  <p class="coming-soon-message">
    Halaman Rekap Keterlambatan sedang dalam pengembangan. 
    Fitur ini akan segera hadir dengan berbagai kemudahan untuk melacak dan mengelola dokumen yang terlambat.
  </p>

  <div class="coming-soon-features">
    <h3>Fitur yang akan tersedia:</h3>
    <ul>
      <li>
        <i class="fa-solid fa-check-circle"></i>
        <span>Pelacakan dokumen terlambat secara real-time</span>
      </li>
      <li>
        <i class="fa-solid fa-check-circle"></i>
        <span>Filter dan pencarian dokumen terlambat</span>
      </li>
      <li>
        <i class="fa-solid fa-check-circle"></i>
        <span>Statistik dan laporan keterlambatan</span>
      </li>
      <li>
        <i class="fa-solid fa-check-circle"></i>
        <span>Notifikasi otomatis untuk dokumen yang mendekati deadline</span>
      </li>
    </ul>
  </div>
</div>

@endsection





