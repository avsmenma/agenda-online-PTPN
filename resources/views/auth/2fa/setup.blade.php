@extends('layouts/app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-shield-alt me-2"></i>
                        Setup Two-Factor Authentication (2FA)
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

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Langkah-langkah:</strong>
                        <ol class="mb-0 mt-2">
                            <li>Scan QR Code di bawah dengan aplikasi authenticator (Google Authenticator, Authy, dll)</li>
                            <li>Atau masukkan secret key secara manual</li>
                            <li>Masukkan kode 6 digit dari aplikasi untuk mengaktifkan 2FA</li>
                        </ol>
                    </div>

                    <div class="text-center mb-4">
                        <div class="bg-light p-4 rounded d-inline-block">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($qrCodeUrl) }}" 
                                 alt="QR Code" 
                                 class="img-fluid"
                                 style="max-width: 200px;">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Secret Key (untuk input manual):</label>
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control font-monospace" 
                                   id="secretKey" 
                                   value="{{ $secretKey }}" 
                                   readonly>
                            <button class="btn btn-outline-secondary" 
                                    type="button" 
                                    onclick="copySecretKey()">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                        <small class="text-muted">Simpan secret key ini di tempat yang aman sebagai backup</small>
                    </div>

                    <form method="POST" action="{{ route('2fa.enable') }}" id="enableForm">
                        @csrf

                        <div class="mb-4">
                            <label for="code" class="form-label fw-bold">
                                Masukkan Kode 6 Digit dari Aplikasi Authenticator
                            </label>
                            <input type="text"
                                   class="form-control text-center font-monospace"
                                   name="code"
                                   id="code"
                                   placeholder="000000"
                                   maxlength="6"
                                   pattern="[0-9]{6}"
                                   style="font-size: 24px; letter-spacing: 8px;"
                                   required
                                   autofocus>
                            <small class="text-muted">Masukkan kode 6 digit yang sedang aktif di aplikasi authenticator Anda</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="enableBtn">
                                <i class="fas fa-shield-alt me-2"></i>
                                Aktifkan 2FA
                            </button>
                            <a href="{{ url(Auth::user()->getDashboardRoute()) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function copySecretKey() {
        const secretKeyInput = document.getElementById('secretKey');
        secretKeyInput.select();
        secretKeyInput.setSelectionRange(0, 99999); // For mobile devices
        
        navigator.clipboard.writeText(secretKeyInput.value).then(function() {
            alert('Secret key berhasil disalin!');
        }, function(err) {
            // Fallback for older browsers
            document.execCommand('copy');
            alert('Secret key berhasil disalin!');
        });
    }

    // Auto-format code input
    const codeInput = document.getElementById('code');
    
    codeInput.addEventListener('input', function(e) {
        // Only allow numbers
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Form submission loading state
    const enableForm = document.getElementById('enableForm');
    const enableBtn = document.getElementById('enableBtn');

    enableForm.addEventListener('submit', function() {
        enableBtn.disabled = true;
        enableBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
    });
</script>
@endsection





