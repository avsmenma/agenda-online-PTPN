@extends('layouts.app')

@section('title', 'Development - Role Switcher')

@section('content')
<div style="padding: 20px; max-width: 800px; margin: 0 auto;">
    <h1 style="color: #083E40; margin-bottom: 30px; text-align: center;">
        🚀 Development Role Switcher
    </h1>

    <div class="alert alert-info" style="background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460;">
        <h5>📝 Cara Penggunaan:</h5>
        <p>Gunakan URL berikut untuk langsung mengakses dashboard dengan role tertentu:</p>
        <ul>
            <li><strong>Operator:</strong> <code>/dashboard?role=Operator</code></li>
            <li><strong>Team Verifikasi:</strong> <code>/dashboardB?role=Team Verifikasi</code></li>
            <li><strong>Perpajakan:</strong> <code>/dashboardPerpajakan?role=Perpajakan</code></li>
            <li><strong>Akutansi:</strong> <code>/dashboardAkutansi?role=Akutansi</code></li>
            <li><strong>Pembayaran:</strong> <code>/dashboardPembayaran?role=Pembayaran</code></li>
        </ul>
        <p>Atau gunakan quick access di bawah:</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <!-- Operator -->
        <a href="/dashboard?role=Operator" style="text-decoration: none;">
            <div style="background: linear-gradient(135deg, #083E40 0%, #0a4f52 100%); color: white; padding: 20px; border-radius: 12px; text-align: center; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(8, 62, 64, 0.2);">
                <div style="font-size: 2em; margin-bottom: 10px;">👩‍💼</div>
                <h4 style="margin: 0; font-weight: 700;">Ibu A</h4>
                <p style="margin: 5px 0 0; opacity: 0.9; font-size: 14px;">Manajer Utama</p>
            </div>
        </a>

        <!-- Team Verifikasi -->
        <a href="/dashboardB?role=Team Verifikasi" style="text-decoration: none;">
            <div style="background: linear-gradient(135deg, #1a4d3e 0%, #0f3d2e 100%); color: white; padding: 20px; border-radius: 12px; text-align: center; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(26, 77, 62, 0.2);">
                <div style="font-size: 2em; margin-bottom: 10px;">👨‍💼</div>
                <h4 style="margin: 0; font-weight: 700;">Ibu B</h4>
                <p style="margin: 5px 0 0; opacity: 0.9; font-size: 14px;">Supervisor</p>
            </div>
        </a>

        <!-- Perpajakan -->
        <a href="/dashboardPerpajakan?role=Perpajakan" style="text-decoration: none;">
            <div style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); color: white; padding: 20px; border-radius: 12px; text-align: center; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(44, 62, 80, 0.2);">
                <div style="font-size: 2em; margin-bottom: 10px;">🧾</div>
                <h4 style="margin: 0; font-weight: 700;">Perpajakan</h4>
                <p style="margin: 5px 0 0; opacity: 0.9; font-size: 14px;">Tax Department</p>
            </div>
        </a>

        <!-- Akutansi -->
        <a href="/dashboardAkutansi?role=Akutansi" style="text-decoration: none;">
            <div style="background: linear-gradient(135deg, #8e44ad 0%, #9b59b6 100%); color: white; padding: 20px; border-radius: 12px; text-align: center; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(142, 68, 173, 0.2);">
                <div style="font-size: 2em; margin-bottom: 10px;">📊</div>
                <h4 style="margin: 0; font-weight: 700;">Akutansi</h4>
                <p style="margin: 5px 0 0; opacity: 0.9; font-size: 14px;">Accounting</p>
            </div>
        </a>

        <!-- Pembayaran -->
        <a href="/dashboardPembayaran?role=Pembayaran" style="text-decoration: none;">
            <div style="background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); color: white; padding: 20px; border-radius: 12px; text-align: center; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(39, 174, 96, 0.2);">
                <div style="font-size: 2em; margin-bottom: 10px;">💳</div>
                <h4 style="margin: 0; font-weight: 700;">Pembayaran</h4>
                <p style="margin: 5px 0 0; opacity: 0.9; font-size: 14px;">Payment</p>
            </div>
        </a>
    </div>

    <div style="background: #f8f9fa; padding: 20px; border-radius: 12px; border-left: 4px solid #28a745;">
        <h5 style="color: #155724; margin-top: 0;">💡 Quick Access:</h5>
        <div style="display: flex; flex-wrap: wrap; gap: 10px;">
            <a href="/dev-dashboard/Operator" class="btn btn-sm" style="background: #083E40; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none;">Operator Dashboard</a>
            <a href="/dev-dashboard/Team Verifikasi" class="btn btn-sm" style="background: #1a4d3e; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none;">Team Verifikasi Dashboard</a>
            <a href="/dev-dashboard/Perpajakan" class="btn btn-sm" style="background: #2c3e50; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none;">Perpajakan Dashboard</a>
            <a href="/dev-dashboard/Akutansi" class="btn btn-sm" style="background: #8e44ad; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none;">Akutansi Dashboard</a>
            <a href="/dev-dashboard/Pembayaran" class="btn btn-sm" style="background: #27ae60; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none;">Pembayaran Dashboard</a>
        </div>
    </div>

    <div style="margin-top: 30px; text-align: center; color: #6c757d;">
        <p><em>🔧 Mode Development - Auto Login Aktif</em></p>
        <p><small>Kembali ke <a href="/dev-all">Role Switcher</a> | <a href="/">Home</a></small></p>
    </div>
</div>

<style>
.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}
</style>
@endsection


