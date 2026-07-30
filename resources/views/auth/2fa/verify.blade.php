<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verifikasi 2FA - Agenda Online PTPN</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            background: #000;
            overflow: hidden;
        }

        .video-background {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
        }

        .video-background video {
            position: absolute;
            top: 50%;
            left: 50%;
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            transform: translate(-50%, -50%);
            object-fit: cover;
        }

        .verify-wrapper {
            position: fixed;
            inset: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-y: auto;
        }

        .verify-container {
            width: 100%;
            max-width: 420px;
            opacity: 0;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px) scale(0.96);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .verify-card {
            background: rgba(26, 35, 50, 0.92);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            padding: 40px 36px;
        }

        .verify-header {
            padding: 0 0 24px;
            text-align: center;
        }

        .logo-wrap {
            width: 88px;
            height: 88px;
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            margin: 0 auto 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .logo-wrap img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .verify-header h1 {
            color: #fff;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .verify-header p {
            color: rgba(255, 255, 255, 0.62);
            font-size: 13px;
            margin: 0;
        }

        .verify-body {
            padding-top: 24px;
        }

        .alert {
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 18px;
            font-size: 13px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert i {
            flex: 0 0 auto;
            margin-top: 1px;
        }

        .alert-info {
            background: rgba(14, 165, 233, 0.18);
            border: 1px solid rgba(125, 211, 252, 0.28);
            color: #bae6fd;
        }

        .alert-danger {
            background: rgba(220, 38, 38, 0.2);
            border: 1px solid rgba(220, 38, 38, 0.35);
            color: #fca5a5;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            color: rgba(255, 255, 255, 0.75);
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .input-wrap {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.4);
            z-index: 2;
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            height: 48px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            padding: 10px 14px 10px 40px;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
            transition: all 0.25s ease;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.32);
        }

        .form-control:focus {
            outline: none;
            border-color: rgba(255, 255, 255, 0.45);
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.06);
            color: #fff;
        }

        .code-input {
            height: 56px;
            padding-left: 16px;
            text-align: center;
            letter-spacing: 0.45em;
            font-size: 22px;
            font-weight: 700;
        }

        .helper-text {
            display: block;
            color: rgba(255, 255, 255, 0.42);
            font-size: 11px;
            margin-top: 6px;
        }

        .btn-verify {
            width: 100%;
            height: 48px;
            border: 0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #28a745;
            color: #fff;
            font-family: 'Poppins', sans-serif;
            font-size: 15px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
            transition: all 0.3s ease;
        }

        .btn-verify:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.55);
        }

        .btn-verify.loading {
            opacity: 0.75;
            cursor: not-allowed;
        }

        .recovery-link {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.12);
            text-align: center;
        }

        .recovery-link a {
            color: rgba(100, 220, 200, 0.9);
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
        }

        .recovery-link a:hover {
            color: #5eead4;
        }

        .login-footer {
            padding-top: 20px;
            color: rgba(255, 255, 255, 0.3);
            font-size: 12px;
            text-align: center;
        }

        .reason-input {
            height: auto;
            min-height: 88px;
            padding: 12px 14px;
            resize: vertical;
            line-height: 1.5;
            font-size: 14px;
        }

        /* Sengaja BUKAN .alert: skrip di bawah menghapus semua .alert setelah
           5 detik, sedangkan catatan ini harus tetap terbaca saat form dibuka. */
        .request-note {
            border-radius: 12px;
            padding: 12px 16px;
            margin-bottom: 14px;
            font-size: 12.5px;
            line-height: 1.55;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            background: rgba(14, 165, 233, 0.15);
            border: 1px solid rgba(125, 211, 252, 0.25);
            color: #bae6fd;
        }

        .request-note i {
            flex: 0 0 auto;
            margin-top: 2px;
        }

        .btn-request {
            background: #d97706;
            box-shadow: 0 4px 15px rgba(217, 119, 6, 0.4);
        }

        .btn-request:hover {
            background: #b45309;
            box-shadow: 0 8px 25px rgba(217, 119, 6, 0.55);
        }

        @media (max-width: 480px) {
            .verify-card {
                padding: 28px 24px;
            }

            .verify-header h1 {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="video-background">
        <video autoplay muted loop playsinline preload="auto">
            <source src="{{ asset('videos/landing-bg.mp4') }}" type="video/mp4">
        </video>
    </div>

    <div class="verify-wrapper">
        <div class="verify-container">
            <div class="verify-card">
                <div class="verify-header">
                    <div class="logo-wrap">
                        <img src="{{ asset('images/logoPTPNNew.png') }}" alt="Logo PTPN">
                    </div>
                    <h1>Verifikasi 2FA</h1>
                    <p>Masukkan kode dari aplikasi authenticator Anda</p>
                </div>

                <div class="verify-body">
                    @if(session('info'))
                        <div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle"></i>
                            <span>{{ session('info') }}</span>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-circle"></i>
                            <ul style="margin: 0; padding-left: 16px;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('2fa.verify.store') }}" id="verifyForm">
                        @csrf
                        <div class="form-group">
                            <label class="form-label" for="code">Kode Authenticator</label>
                            <input
                                type="text"
                                class="form-control code-input"
                                name="code"
                                id="code"
                                placeholder="000000"
                                maxlength="6"
                                pattern="[0-9]{6}"
                                required
                                autofocus
                                autocomplete="one-time-code">
                            <span class="helper-text">Gunakan kode 6 digit yang sedang aktif.</span>
                        </div>

                        <button type="submit" class="btn-verify" id="verifyBtn">
                            <i class="fas fa-check"></i>
                            <span>Verifikasi</span>
                        </button>
                    </form>

                    <div class="recovery-link">
                        <a href="#" id="showRecoveryForm">
                            <i class="fas fa-key"></i>
                            Kehilangan akses ke aplikasi authenticator?
                        </a>
                    </div>

                    <form method="POST" action="{{ route('2fa.verify.recovery') }}" id="recoveryForm" style="display: none; margin-top: 20px;">
                        @csrf
                        <div class="form-group">
                            <label class="form-label" for="recovery_code">Recovery Code</label>
                            <div class="input-wrap">
                                <i class="fas fa-key input-icon"></i>
                                <input
                                    type="text"
                                    class="form-control"
                                    name="recovery_code"
                                    id="recovery_code"
                                    placeholder="Masukkan recovery code"
                                    maxlength="10"
                                    pattern="[A-Z0-9]{10}"
                                    autocomplete="off"
                                    required>
                            </div>
                            <span class="helper-text">Masukkan salah satu recovery code yang Anda simpan saat setup 2FA.</span>
                        </div>
                        <button type="submit" class="btn-verify">
                            <i class="fas fa-key"></i>
                            <span>Verifikasi Recovery Code</span>
                        </button>
                    </form>

                    @php
                        // Kalau pengajuan tadi ditolak validasi, form dibuka kembali
                        // supaya user tidak perlu mencari tautannya lagi.
                        $showResetRequestForm = $errors->has('reason');
                    @endphp

                    <div class="recovery-link" id="resetRequestLink" style="display: {{ $showResetRequestForm ? 'none' : 'block' }};">
                        <a href="#" id="showResetRequestForm">
                            <i class="fas fa-shield-halved"></i>
                            Recovery code juga hilang? Ajukan reset 2FA
                        </a>
                    </div>

                    <form method="POST" action="{{ route('2fa.reset-request') }}" id="resetRequestForm" style="display: {{ $showResetRequestForm ? 'block' : 'none' }}; margin-top: 20px;">
                        @csrf
                        <div class="request-note">
                            <i class="fas fa-circle-info"></i>
                            <span>Pengajuan dikirim ke programmer. Setelah disetujui, 2FA akun Anda dinonaktifkan sehingga Anda bisa masuk dengan kata sandi saja — lalu segera atur ulang 2FA dan simpan recovery code-nya.</span>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="reason">Alasan Pengajuan</label>
                            <textarea
                                class="form-control reason-input"
                                name="reason"
                                id="reason"
                                rows="3"
                                minlength="10"
                                required>{{ old('reason', 'Kehilangan akses aplikasi authenticator dan recovery code tidak tersimpan.') }}</textarea>
                            <span class="helper-text">Minimal 10 karakter. Semakin jelas, semakin cepat ditinjau.</span>
                        </div>
                        <button type="submit" class="btn-verify btn-request">
                            <i class="fas fa-shield-halved"></i>
                            <span>Kirim Pengajuan Reset 2FA</span>
                        </button>
                    </form>
                </div>

                <div class="login-footer">
                    <p>&copy; {{ date('Y') }} PTPN. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const codeInput = document.getElementById('code');
        const verifyForm = document.getElementById('verifyForm');
        const verifyBtn = document.getElementById('verifyBtn');
        const showRecoveryForm = document.getElementById('showRecoveryForm');
        const recoveryForm = document.getElementById('recoveryForm');
        const recoveryCode = document.getElementById('recovery_code');

        codeInput?.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '');
            if (this.value.length === 6) {
                verifyForm.submit();
            }
        });

        verifyForm?.addEventListener('submit', function () {
            verifyBtn.classList.add('loading');
            verifyBtn.disabled = true;
            verifyBtn.querySelector('span').textContent = 'Memverifikasi...';
        });

        showRecoveryForm?.addEventListener('click', function (event) {
            event.preventDefault();
            recoveryForm.style.display = 'block';
            this.style.display = 'none';
            setTimeout(function () {
                recoveryCode?.focus();
            }, 80);
        });

        recoveryCode?.addEventListener('input', function () {
            this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        });

        const showResetRequestForm = document.getElementById('showResetRequestForm');
        const resetRequestForm = document.getElementById('resetRequestForm');
        const resetRequestLink = document.getElementById('resetRequestLink');
        const reasonInput = document.getElementById('reason');

        showResetRequestForm?.addEventListener('click', function (event) {
            event.preventDefault();
            resetRequestForm.style.display = 'block';
            resetRequestLink.style.display = 'none';
            setTimeout(function () {
                reasonInput?.focus();
            }, 80);
        });

        document.querySelectorAll('.alert').forEach(function (alert) {
            setTimeout(function () {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(function () { alert.remove(); }, 500);
            }, 5000);
        });
    </script>
</body>

</html>
