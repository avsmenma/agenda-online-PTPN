<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Login Agenda Online PTPN untuk pengelolaan agenda dan dokumen perusahaan.">
    <title>Agenda Online - PTPN</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            overflow: hidden;
            background: #000;
        }

        /* ==================== VIDEO BACKGROUND ==================== */
        .video-background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
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

        .video-background .background-poster {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .video-background video {
            opacity: 0;
            transition: opacity 0.35s ease;
        }

        .video-background video.is-ready {
            opacity: 1;
        }

        /* ==================== LOGIN WRAPPER ==================== */
        .login-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 10;
        }

        /* ==================== GLASSMORPHISM CARD ==================== */
        .login-container {
            width: 100%;
            max-width: 420px;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
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

        .login-card {
            background: rgba(26, 35, 50, 0.92);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            padding: 40px 36px;
        }

        /* ==================== LOGIN HEADER ==================== */
        .login-header {
            padding: 0 0 24px 0;
            text-align: center;
            border-bottom: none;
        }

        .login-header .logo-wrap {
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

        .login-header .logo-wrap img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .login-header h1 {
            font-size: 22px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 6px;
            letter-spacing: -0.3px;
        }

        .login-header p {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
            margin: 0;
            font-weight: 400;
        }

        /* ==================== LOGIN BODY ==================== */
        .login-body {
            padding: 24px 0 0 0;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            font-size: 13px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.75);
            margin-bottom: 8px;
            display: block;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.4);
            font-size: 14px;
            z-index: 2;
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            height: 44px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            padding: 10px 14px 10px 40px;
            font-size: 14px;
            color: #ffffff;
            font-family: 'Poppins', sans-serif;
            transition: all 0.25s ease;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        .form-control:focus {
            outline: none;
            border-color: rgba(255, 255, 255, 0.45);
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.06);
            color: #ffffff;
        }

        #password {
            padding-right: 44px;
        }

        .password-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.4);
            font-size: 14px;
            cursor: pointer;
            z-index: 2;
            background: none;
            border: none;
            padding: 0;
            transition: color 0.2s ease;
        }

        .password-toggle:hover {
            color: rgba(255, 255, 255, 0.8);
        }

        .helper-text {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.4);
            margin-top: 5px;
            display: block;
        }

        /* ==================== REMEMBER / FORGOT ==================== */
        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
        }

        .form-check-label {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.65);
            cursor: pointer;
            user-select: none;
        }

        .form-check-input {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.25);
        }

        .form-check-input:checked {
            background-color: #0d9488;
            border-color: #0d9488;
        }

        .forgot-password {
            font-size: 13px;
            color: rgba(100, 220, 200, 0.85);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .forgot-password:hover {
            color: #5eead4;
        }

        /* ==================== SUBMIT BUTTON ==================== */
        .btn-login {
            width: 100%;
            height: 48px;
            background: #187a34;
            border: none;
            border-radius: 8px;
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: 'Poppins', sans-serif;
            box-shadow: 0 4px 15px rgba(24, 122, 52, 0.42);
            letter-spacing: 0.3px;
        }

        .btn-login:hover {
            background: #146c2e;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(20, 108, 46, 0.55);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login.loading {
            opacity: 0.75;
            cursor: not-allowed;
        }

        /* ==================== ALERTS ==================== */
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
            to   { opacity: 1; transform: translateY(0); }
        }

        .alert i {
            font-size: 14px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .alert-danger {
            background: rgba(220, 38, 38, 0.2);
            border: 1px solid rgba(220, 38, 38, 0.35);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(22, 163, 74, 0.2);
            border: 1px solid rgba(22, 163, 74, 0.35);
            color: #86efac;
        }

        /* ==================== FOOTER ==================== */
        .login-footer {
            text-align: center;
            padding: 20px 0 0 0;
            font-size: 12px;
            color: rgba(255, 255, 255, 0.3);
        }

        /* ==================== LOADING SPINNER IN BUTTON ==================== */
        .spinner-border-sm {
            display: none;
        }

        .loading .spinner-border-sm {
            display: inline-block;
        }

        /* ==================== RESPONSIVE ==================== */
        @media (max-width: 480px) {
            .login-card {
                padding: 28px 24px;
            }

            .login-header h1 {
                font-size: 20px;
            }

            .remember-forgot {
                flex-direction: column;
                gap: 12px;
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>
    <!-- Video Background -->
    <div class="video-background" aria-hidden="true">
        <!-- <img
            class="background-poster"
            src="{{ asset('images/landing-bg-poster.jpg') }}"
            width="1600"
            height="679"
            alt=""
            fetchpriority="high"> -->
        <video muted loop playsinline preload="none" poster="{{ asset('images/landing-bg-poster.jpg') }}" aria-hidden="true">
            <source data-src="{{ asset('videos/landing-bg.mp4') }}" type="video/mp4">
        </video>
    </div>

    <!-- Login Wrapper -->
    <div class="login-wrapper">
        <div class="login-container">
            <div class="login-card">

                <!-- Header -->
                <div class="login-header">
                    <div class="logo-wrap">
                        <img src="{{ asset('images/logoPTPNNew-128.png') }}" width="128" height="128" alt="Logo PTPN">
                    </div>
                    <h1>Agenda Online PTPN</h1>
                    <p>Sistem Manajemen Dokumen</p>
                </div>

                <!-- Body -->
                <div class="login-body">

                    @if(session('success'))
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle"></i>
                            <span>{{ session('success') }}</span>
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

                    <form method="POST" action="{{ route('login.store') }}" id="loginForm">
                        @csrf

                        <!-- Username -->
                        <div class="form-group">
                            <label class="form-label">Username atau Email</label>
                            <div class="input-wrap">
                                <i class="fas fa-user input-icon"></i>
                                <input
                                    type="text"
                                    class="form-control"
                                    name="username"
                                    placeholder="Masukkan username atau email"
                                    value="{{ old('username') }}"
                                    required
                                    autofocus>
                            </div>
                            <span class="helper-text">Anda bisa login menggunakan username atau email Anda</span>
                        </div>

                        <!-- Password -->
                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <div class="input-wrap">
                                <i class="fas fa-lock input-icon"></i>
                                <input
                                    type="password"
                                    class="form-control"
                                    name="password"
                                    id="password"
                                    placeholder="Masukkan password"
                                    required>
                                <button type="button" class="password-toggle" id="togglePassword" aria-label="Tampilkan password" aria-controls="password" aria-pressed="false" title="Tampilkan password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Remember / Forgot -->
                        <div class="remember-forgot">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                <label class="form-check-label" for="remember">Ingat Saya</label>
                            </div>
                            <a href="#" class="forgot-password">Lupa Password?</a>
                        </div>

                        <!-- Submit -->
                        <button type="submit" class="btn-login" id="loginBtn">
                            <span id="loginBtnText">Masuk</span>
                            <div class="spinner-border spinner-border-sm text-white" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </button>
                    </form>
                </div>

                <!-- Footer -->
                <div class="login-footer">
                    <p>&copy; {{ date('Y') }} PTPN. All rights reserved.</p>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // ── Toggle password visibility ──────────────────────────────────
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput  = document.getElementById('password');

        togglePassword.addEventListener('click', function () {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
            const label = isPassword ? 'Sembunyikan password' : 'Tampilkan password';
            this.setAttribute('aria-label', label);
            this.setAttribute('title', label);
            this.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
        });

        window.addEventListener('load', function () {
            const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            if (prefersReducedMotion) {
                return;
            }

            const backgroundVideo = document.querySelector('.video-background video');
            const backgroundSource = backgroundVideo ? backgroundVideo.querySelector('source[data-src]') : null;

            if (!backgroundVideo || !backgroundSource) {
                return;
            }

            setTimeout(function () {
                backgroundSource.src = backgroundSource.dataset.src;
                backgroundVideo.load();

                const playPromise = backgroundVideo.play();

                if (playPromise && typeof playPromise.then === 'function') {
                    playPromise
                        .then(function () {
                            backgroundVideo.classList.add('is-ready');
                        })
                        .catch(function () {
                            // Keep the poster image visible when autoplay is blocked.
                        });
                } else {
                    backgroundVideo.classList.add('is-ready');
                }
            }, 5000);
        });

        // ── Form submission loading state ───────────────────────────────
        const loginForm = document.getElementById('loginForm');
        const loginBtn  = document.getElementById('loginBtn');
        const loginBtnText = document.getElementById('loginBtnText');

        loginForm.addEventListener('submit', function () {
            loginBtn.classList.add('loading');
            loginBtn.disabled = true;
            loginBtnText.textContent = 'Memproses...';
        });

        // ── Auto-hide alerts after 5 seconds ───────────────────────────
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
