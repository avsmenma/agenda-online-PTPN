<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - Agenda Online PTPN</title>

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
            background: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 450px;
        }

        .login-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            animation: slideUp 0.5s ease-out;
            border: 1px solid #e5e7eb;
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

        .login-header {
            background: #ffffff;
            padding: 40px 30px;
            text-align: center;
            color: #374151;
            border-bottom: 1px solid #f3f4f6;
        }

        .login-header .logo {
            width: 72px;
            height: 72px;
            background: #f3f4f6;
            border-radius: 16px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-header .logo i {
            font-size: 32px;
            color: #6b7280;
        }

        .login-header h1 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #111827;
        }

        .login-header p {
            font-size: 14px;
            color: #6b7280;
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

        /* Base styling for left icons (user, lock) - exclude password toggle */
        .input-group-custom > i:not(.password-toggle) {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 16px;
            z-index: 10;
            pointer-events: none; /* Prevent icon from blocking clicks */
        }

        /* Specific styling for password field lock icon - ensure it's on the left */
        .password-lock-icon,
        .password-field-wrapper .fa-lock {
            position: absolute !important;
            left: 15px !important;
            right: auto !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            z-index: 10 !important;
            pointer-events: none !important; /* Prevent lock icon from blocking clicks */
        }

        .form-control {
            height: 48px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding-left: 45px;
            padding-right: 45px; /* Add right padding for eye icon */
            font-size: 14px;
            transition: all 0.3s ease;
            background: #ffffff;
        }
        
        /* Ensure password field has proper padding for both icons */
        #password {
            padding-left: 45px !important; /* Space for lock icon on left */
            padding-right: 50px !important; /* Extra space for eye icon on right */
        }

        .form-control::placeholder {
            color: #9ca3af;
        }

        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Eye icon for password toggle - must be on the right side */
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
            z-index: 30 !important; /* Higher z-index than lock icon and input */
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
            pointer-events: auto !important; /* Ensure eye icon is clickable */
            margin: 0;
        }

        .password-toggle:hover {
            color: #3b82f6;
        }

        .password-toggle:active {
            transform: translateY(-50%) scale(0.95);
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
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: #2563eb;
        }

        .btn-login {
            width: 100%;
            height: 48px;
            background: #3b82f6;
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

        .btn-login:hover {
            background: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login.loading {
            opacity: 0.8;
            cursor: not-allowed;
        }

        .alert {
            border-radius: 8px;
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

        .divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
            color: #999;
            font-size: 14px;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e0e0e0;
        }

        .divider span {
            padding: 0 15px;
        }

        .login-footer {
            text-align: center;
            padding: 0 30px 30px;
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

        @media (max-width: 576px) {
            .login-card {
                border-radius: 15px;
            }

            .login-header {
                padding: 30px 20px;
            }

            .login-header h1 {
                font-size: 24px;
            }

            .login-body {
                padding: 30px 20px;
            }

            .remember-forgot {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }
        }

        .form-check-input:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }
    </style>
</head>
<body>
    <div class="login-container">
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
                            <input type="text"
                                   class="form-control"
                                   name="username"
                                   placeholder="Masukkan username atau email"
                                   value="{{ old('username') }}"
                                   required
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
                            <input type="password"
                                   class="form-control"
                                   name="password"
                                   id="password"
                                   placeholder="Masukkan password"
                                   required>
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

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            // Toggle between eye and eye-slash icons
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        // Form submission loading state
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');

        loginForm.addEventListener('submit', function() {
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
    </script>
</body>
</html>


