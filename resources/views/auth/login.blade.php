<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Agenda Online - PTPN</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Preload Landing Background Image -->
    <link rel="preload" as="image" href="{{ asset('images/landing-bg.png') }}" fetchpriority="high">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ==================== LOADING SCREEN ==================== */
        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: linear-gradient(to bottom, #4fc3f7 0%, #29b6f6 50%, #03a9f4 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 100;
            transition: opacity 0.4s ease, visibility 0.4s ease;
        }

        .loading-screen.loaded {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .loading-spinner-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }

        .loading-spinner-container .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .loading-spinner-container p {
            color: white;
            font-size: 16px;
            font-weight: 500;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* ==================== LANDING PAGE ==================== */
        .landing-page {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: linear-gradient(to bottom, #4fc3f7 0%, #29b6f6 50%, #03a9f4 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 10;
            transition: opacity 0.5s ease, visibility 0.5s ease;
            overflow: hidden;
        }

        .landing-bg-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .landing-bg-image.loaded {
            opacity: 1;
        }

        .landing-page.hidden {
            opacity: 0;
            visibility: hidden;
        }

        .landing-content {
            position: absolute;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            z-index: 5;
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        .landing-content.visible {
            animation: fadeInUp 0.8s ease-out forwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .btn-login-landing {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 16px 48px;
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            border: none;
            border-radius: 50px;
            color: white;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 40px rgba(13, 148, 136, 0.4);
            text-decoration: none;
        }

        .btn-login-landing:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 50px rgba(13, 148, 136, 0.5);
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
            color: white;
        }

        .btn-login-landing:active {
            transform: translateY(-1px);
        }

        .btn-login-landing i {
            font-size: 20px;
        }

        /* ==================== LOGIN FORM OVERLAY ==================== */
        .login-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100vh;
            background: url('{{ asset('images/landing-bg.png') }}') no-repeat center center;
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 20;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.5s ease, visibility 0.5s ease;
        }

        .login-overlay::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(8px);
        }

        .login-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .login-container {
            position: relative;
            width: 100%;
            max-width: 450px;
            z-index: 1;
            animation: scaleIn 0.4s ease-out;
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            backdrop-filter: blur(10px);
        }

        .btn-back {
            position: absolute;
            top: -50px;
            left: 0;
            display: flex;
            align-items: center;
            gap: 8px;
            color: white;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 10px 20px;
            border-radius: 50px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.25);
            color: white;
            transform: translateX(-5px);
        }

        .login-header {
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            padding: 35px 30px;
            text-align: center;
            color: white;
        }

        .login-header .logo {
            width: 72px;
            height: 72px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
        }

        .login-header .logo i {
            font-size: 32px;
            color: white;
        }

        .login-header h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .login-header p {
            font-size: 14px;
            opacity: 0.9;
            margin: 0;
        }

        .login-body {
            padding: 32px 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-size: 14px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 8px;
            display: block;
        }

        .input-group-custom {
            position: relative;
        }

        .input-group-custom>i:not(.password-toggle) {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 16px;
            z-index: 10;
            pointer-events: none;
        }

        .password-lock-icon,
        .password-field-wrapper .fa-lock {
            position: absolute !important;
            left: 15px !important;
            right: auto !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            z-index: 10 !important;
            pointer-events: none !important;
        }

        .form-control {
            height: 48px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            padding-left: 45px;
            padding-right: 45px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #ffffff;
        }

        #password {
            padding-left: 45px !important;
            padding-right: 50px !important;
        }

        .form-control::placeholder {
            color: #9ca3af;
        }

        .form-control:focus {
            border-color: #0d9488;
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15);
        }

        .password-toggle,
        .password-field-wrapper .password-toggle,
        #togglePassword {
            position: absolute !important;
            right: 15px !important;
            left: auto !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            cursor: pointer;
            color: #999;
            z-index: 30 !important;
            font-size: 16px;
            width: 24px;
            height: 24px;
            display: flex !important;
            align-items: center;
            justify-content: center;
            transition: color 0.3s ease;
            background: transparent;
            border: none;
            padding: 0;
            pointer-events: auto !important;
            margin: 0;
        }

        .password-toggle:hover {
            color: #0d9488;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .form-check-label {
            font-size: 14px;
            color: #374151;
            cursor: pointer;
            user-select: none;
        }

        .forgot-password {
            font-size: 14px;
            color: #0d9488;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: #0f766e;
        }

        .btn-login {
            width: 100%;
            height: 50px;
            background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(13, 148, 136, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login.loading {
            opacity: 0.8;
            cursor: not-allowed;
        }

        .alert {
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert i {
            font-size: 16px;
            flex-shrink: 0;
        }

        .alert-danger {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
        }

        .alert-success {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #16a34a;
        }

        .login-footer {
            text-align: center;
            padding: 0 30px 25px;
            font-size: 13px;
            color: #9ca3af;
        }

        .loading-spinner {
            display: none;
            margin-left: 10px;
        }

        .loading .loading-spinner {
            display: inline-block;
        }

        .form-check-input:checked {
            background-color: #0d9488;
            border-color: #0d9488;
        }

        @media (max-width: 576px) {
            .landing-page {
                padding-bottom: 60px;
            }

            .btn-login-landing {
                padding: 14px 36px;
                font-size: 16px;
            }

            .login-card {
                border-radius: 16px;
            }

            .login-header {
                padding: 30px 20px;
            }

            .login-header h1 {
                font-size: 22px;
            }

            .login-body {
                padding: 25px 20px;
            }

            .remember-forgot {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }

            .btn-back {
                top: -45px;
                padding: 8px 16px;
                font-size: 13px;
            }
        }
    </style>
</head>

<body>
    <!-- Loading Screen -->
    <div class="loading-screen" id="loadingScreen">
        <div class="loading-spinner-container">
            <div class="spinner"></div>
            <p>Memuat...</p>
        </div>
    </div>

    <!-- Landing Page -->
    <div class="landing-page" id="landingPage">
        <!-- Image will be set via JavaScript after preloading -->
        <img src="" alt="Agenda Online" class="landing-bg-image" id="landingBgImage">
        <div class="landing-content">
            <button class="btn-login-landing" id="showLoginBtn">
                <i class="fas fa-sign-in-alt"></i>
                <span>Masuk</span>
            </button>
        </div>
    </div>

    <!-- Login Form Overlay -->
    <div class="login-overlay" id="loginOverlay">
        <div class="login-container">
            <a href="#" class="btn-back" id="backBtn">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>

            <div class="login-card">
                <div class="login-header">
                    <div class="logo">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h1>Agenda Online PTPN</h1>
                    <p>Sistem Manajemen Dokumen</p>
                </div>

                <div class="login-body">
                    @if(session('success'))
                        <div class="alert alert-success" role="alert">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-circle"></i>
                            <ul style="margin: 5px 0 0 20px; padding: 0;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.store') }}" id="loginForm">
                        @csrf

                        <div class="form-group">
                            <label class="form-label">Username atau Email</label>
                            <div class="input-group-custom">
                                <i class="fas fa-user"></i>
                                <input type="text" class="form-control" name="username"
                                    placeholder="Masukkan username atau email" value="{{ old('username') }}" required
                                    autofocus>
                            </div>
                            <small class="text-muted" style="font-size: 12px; margin-top: 4px; display: block;">
                                Anda bisa login menggunakan username atau email Anda
                            </small>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <div class="input-group-custom password-field-wrapper">
                                <i class="fas fa-lock password-lock-icon"></i>
                                <input type="password" class="form-control" name="password" id="password"
                                    placeholder="Masukkan password" required>
                                <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                            </div>
                        </div>

                        <div class="remember-forgot">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                <label class="form-check-label" for="remember">
                                    Ingat Saya
                                </label>
                            </div>
                            <a href="#" class="forgot-password">Lupa Password?</a>
                        </div>

                        <button type="submit" class="btn-login" id="loginBtn">
                            <span>Masuk</span>
                            <div class="spinner-border spinner-border-sm loading-spinner" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </button>
                    </form>
                </div>

                <div class="login-footer">
                    <p>&copy; {{ date('Y') }} PTPN. All rights reserved.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Image URL to preload
        const imageUrl = "{{ asset('images/landing-bg.png') }}";
        
        // Elements
        const loadingScreen = document.getElementById('loadingScreen');
        const landingBgImage = document.getElementById('landingBgImage');
        const landingContent = document.querySelector('.landing-content');

        // Function to show content after image is fully loaded
        function showContent() {
            // Set the preloaded image to the visible img element
            landingBgImage.src = imageUrl;
            
            // Force browser to complete rendering
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    landingBgImage.classList.add('loaded');
                    loadingScreen.classList.add('loaded');
                    
                    setTimeout(() => {
                        landingContent.classList.add('visible');
                    }, 100);
                });
            });
        }

        // Preload image in memory (hidden) before displaying
        const preloadImage = new Image();
        preloadImage.onload = function() {
            // Image is fully downloaded in memory, now show it
            showContent();
        };
        preloadImage.onerror = function() {
            // Show content even if image fails to load
            showContent();
        };
        
        // Start preloading
        preloadImage.src = imageUrl;

        // Fallback: show content after 5 seconds maximum
        setTimeout(() => {
            if (!loadingScreen.classList.contains('loaded')) {
                showContent();
            }
        }, 5000);

        // Elements
        const landingPage = document.getElementById('landingPage');
        const loginOverlay = document.getElementById('loginOverlay');
        const showLoginBtn = document.getElementById('showLoginBtn');
        const backBtn = document.getElementById('backBtn');
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');

        // Show login form
        showLoginBtn.addEventListener('click', function () {
            landingPage.classList.add('hidden');
            loginOverlay.classList.add('active');
        });

        // Back to landing page
        backBtn.addEventListener('click', function (e) {
            e.preventDefault();
            loginOverlay.classList.remove('active');
            landingPage.classList.remove('hidden');
        });

        // Toggle password visibility
        togglePassword.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Form submission loading state
        loginForm.addEventListener('submit', function () {
            loginBtn.classList.add('loading');
            loginBtn.disabled = true;
            loginBtn.querySelector('span').textContent = 'Memproses...';
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

        // If there are errors, show login form automatically
        @if($errors->any() || session('error'))
            landingPage.classList.add('hidden');
            loginOverlay.classList.add('active');
        @endif
    </script>
</body>

</html>