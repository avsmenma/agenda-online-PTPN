<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verifikasi 2FA - Agenda Online PTPN</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .verify-container {
            width: 100%;
            max-width: 450px;
        }

        .verify-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .verify-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
        }

        .verify-header .icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
        }

        .verify-header .icon i {
            font-size: 40px;
        }

        .verify-header h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .verify-header p {
            font-size: 14px;
            opacity: 0.9;
            margin: 0;
        }

        .verify-body {
            padding: 40px 30px;
        }

        .code-input-group {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-bottom: 30px;
        }

        .code-input {
            width: 50px;
            height: 60px;
            text-align: center;
            font-size: 24px;
            font-weight: 600;
            border: 2px solid #d1d5db;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .code-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .code-input-group-single {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-bottom: 20px;
        }

        .code-input-single {
            flex: 1;
            max-width: 200px;
            height: 50px;
            text-align: center;
            font-size: 20px;
            font-weight: 600;
            letter-spacing: 8px;
            border: 2px solid #d1d5db;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .code-input-single:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .btn-verify {
            width: 100%;
            height: 48px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-verify:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-verify:active {
            transform: translateY(0);
        }

        .btn-verify.loading {
            opacity: 0.8;
            cursor: not-allowed;
        }

        .recovery-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }

        .recovery-link a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .recovery-link a:hover {
            text-decoration: underline;
        }

        .alert {
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .info-box {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
            font-size: 14px;
            color: #0369a1;
        }

        .info-box i {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <div class="verify-card">
            <div class="verify-header">
                <div class="icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h1>Verifikasi 2FA</h1>
                <p>Masukkan kode 6 digit dari aplikasi authenticator Anda</p>
            </div>

            <div class="verify-body">
                @if(session('info'))
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> {{ session('info') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <ul style="margin: 5px 0 0 20px; padding: 0;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    Buka aplikasi authenticator Anda (Google Authenticator, Authy, dll) dan masukkan kode 6 digit yang sedang aktif.
                </div>

                <form method="POST" action="{{ route('2fa.verify.store') }}" id="verifyForm">
                    @csrf

                    <div class="code-input-group-single">
                        <input type="text"
                               class="form-control code-input-single"
                               name="code"
                               id="code"
                               placeholder="000000"
                               maxlength="6"
                               pattern="[0-9]{6}"
                               required
                               autofocus
                               autocomplete="one-time-code">
                    </div>

                    <button type="submit" class="btn-verify" id="verifyBtn">
                        <i class="fas fa-check"></i>
                        <span>Verifikasi</span>
                    </button>
                </form>

                <div class="recovery-link">
                    <a href="#" onclick="event.preventDefault(); document.getElementById('recoveryForm').style.display = 'block'; this.style.display = 'none';">
                        <i class="fas fa-key"></i> Kehilangan akses ke aplikasi authenticator?
                    </a>
                </div>

                <form method="POST" action="{{ route('2fa.verify.recovery') }}" id="recoveryForm" style="display: none; margin-top: 20px;">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Recovery Code</label>
                        <input type="text"
                               class="form-control"
                               name="recovery_code"
                               placeholder="Masukkan recovery code"
                               maxlength="10"
                               pattern="[A-Z0-9]{10}"
                               required>
                        <small class="text-muted">Masukkan salah satu recovery code yang Anda simpan saat setup 2FA</small>
                    </div>
                    <button type="submit" class="btn-verify">
                        <i class="fas fa-key"></i>
                        <span>Verifikasi dengan Recovery Code</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Auto-focus and format code input
        const codeInput = document.getElementById('code');
        
        codeInput.addEventListener('input', function(e) {
            // Only allow numbers
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Auto-submit when 6 digits entered
            if (this.value.length === 6) {
                document.getElementById('verifyForm').submit();
            }
        });

        // Form submission loading state
        const verifyForm = document.getElementById('verifyForm');
        const verifyBtn = document.getElementById('verifyBtn');

        verifyForm.addEventListener('submit', function() {
            verifyBtn.classList.add('loading');
            verifyBtn.disabled = true;
            verifyBtn.querySelector('span').textContent = 'Memverifikasi...';
        });

        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }, 5000);
        });
    </script>
</body>
</html>

