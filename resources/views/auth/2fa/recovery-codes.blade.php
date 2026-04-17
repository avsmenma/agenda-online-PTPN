@extends('layouts/app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-key me-2"></i>
                        Recovery Codes
                    </h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Penting!</strong> Simpan recovery codes ini di tempat yang aman. 
                        Recovery codes dapat digunakan untuk login jika Anda kehilangan akses ke aplikasi authenticator.
                    </div>

                    <div class="bg-light p-4 rounded mb-4">
                        <div class="row g-2">
                            @foreach($recoveryCodes as $index => $code)
                                <div class="col-md-6">
                                    <div class="p-2 bg-white rounded border font-monospace text-center fw-bold">
                                        {{ $code }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" onclick="printRecoveryCodes()">
                            <i class="fas fa-print me-2"></i>
                            Cetak Recovery Codes
                        </button>
                        <form method="POST" action="{{ route('2fa.regenerate-recovery-codes') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-warning" onclick="return confirm('Apakah Anda yakin ingin generate ulang recovery codes? Recovery codes lama akan tidak berlaku lagi.')">
                                <i class="fas fa-sync-alt me-2"></i>
                                Generate Ulang Recovery Codes
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

<script>
    function printRecoveryCodes() {
        window.print();
    }

    // Print styles
    const style = document.createElement('style');
    style.textContent = `
        @media print {
            body * {
                visibility: hidden;
            }
            .card, .card * {
                visibility: visible;
            }
            .card {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .btn, .alert {
                display: none !important;
            }
        }
    `;
    document.head.appendChild(style);
</script>
@endsection






