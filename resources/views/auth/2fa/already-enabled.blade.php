@extends('layouts/app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-shield-alt me-2"></i>
                        Two-Factor Authentication (2FA)
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>2FA sudah aktif!</strong> Akun Anda dilindungi dengan two-factor authentication.
                    </div>

                    <div class="mb-4">
                        <h5>Status 2FA</h5>
                        <p class="text-muted">
                            <i class="fas fa-check text-success me-2"></i>
                            Two-Factor Authentication: <strong>Aktif</strong>
                        </p>
                        <p class="text-muted">
                            <i class="fas fa-calendar me-2"></i>
                            Diaktifkan pada: <strong>{{ Auth::user()->two_factor_confirmed_at?->format('d F Y, H:i') ?? '-' }}</strong>
                        </p>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="{{ route('2fa.recovery-codes') }}" class="btn btn-primary">
                            <i class="fas fa-key me-2"></i>
                            Lihat Recovery Codes
                        </a>
                        <form method="POST" action="{{ route('2fa.disable') }}" class="d-inline">
                            @csrf
                            <div class="mb-3">
                                <label for="password" class="form-label">Password (untuk menonaktifkan 2FA)</label>
                                <input type="password" 
                                       class="form-control" 
                                       name="password" 
                                       id="password" 
                                       required
                                       placeholder="Masukkan password Anda">
                            </div>
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menonaktifkan 2FA? Akun Anda akan menjadi kurang aman.')">
                                <i class="fas fa-times me-2"></i>
                                Nonaktifkan 2FA
                            </button>
                        </form>
                        <a href="{{ url(Auth::user()->getDashboardRoute()) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>
                            Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

