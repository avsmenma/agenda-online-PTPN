@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2>Testing Welcome Messages</h2>

    <div class="card">
        <div class="card-header">
            <h5>Module: {{ ucfirst($module) }}</h5>
        </div>
        <div class="card-body">
            <p><strong>Welcome Message:</strong> {{ $welcomeMessage }}</p>

            <div class="mt-3">
                <h6>Test Other Modules:</h6>
                <div class="btn-group" role="group">
                    <a href="/test-welcome/Operator" class="btn btn-outline-primary">Operator</a>
                    <a href="/test-welcome/Team Verifikasi" class="btn btn-outline-primary">Team Verifikasi</a>
                    <a href="/test-welcome/perpajakan" class="btn btn-outline-primary">Perpajakan</a>
                    <a href="/test-welcome/akutansi" class="btn btn-outline-primary">Akutansi</a>
                    <a href="/test-welcome/pembayaran" class="btn btn-outline-primary">Pembayaran</a>
                </div>
            </div>

            <div class="mt-3">
                <h6>Visit Actual Dashboards:</h6>
                <div class="btn-group" role="group">
                    <a href="/dashboard" class="btn btn-success">Dashboard Operator</a>
                    <a href="/dashboardB" class="btn btn-success">Dashboard Team Verifikasi</a>
                    <a href="/dashboardPerpajakan" class="btn btn-success">Dashboard Perpajakan</a>
                    <a href="/dashboardAkutansi" class="btn btn-success">Dashboard Akutansi</a>
                    <a href="/dashboardPembayaran" class="btn btn-success">Dashboard Pembayaran</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection




