@extends('layouts/app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        2FA Diperlukan
                    </h4>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-shield-alt fa-4x text-warning mb-3"></i>
                        <h5>2FA Harus Diaktifkan</h5>
                        <p class="text-muted">
                            Untuk mengakses pengaturan akun (mengubah email dan password), 
                            Anda harus mengaktifkan Two-Factor Authentication (2FA) terlebih dahulu.
                        </p>
                    </div>

                    <a href="{{ route('2fa.setup') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-shield-alt me-2"></i>
                        Aktifkan 2FA Sekarang
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



