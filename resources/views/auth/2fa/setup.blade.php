@extends('layouts/app')

@section('content')
<style>
    .twofa-page {
        max-width: 1220px;
        margin: 0 auto;
        padding: 34px 24px 72px;
    }

    .twofa-stepper {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        align-items: center;
        gap: 10px;
        margin-bottom: 30px;
    }

    .twofa-step {
        position: relative;
        display: flex;
        align-items: center;
        gap: 12px;
        color: #9aa8bd;
        font-weight: 800;
        min-width: 0;
    }

    .twofa-step:not(:last-child)::after {
        content: '';
        height: 3px;
        background: #dfe6ef;
        flex: 1;
        margin-left: 4px;
    }

    .step-number {
        width: 38px;
        height: 38px;
        border-radius: 999px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        background: #eef2f7;
        color: #95a2b7;
        font-weight: 900;
    }

    .twofa-step.active {
        color: #0c493b;
    }

    .twofa-step.active .step-number {
        background: #0c493b;
        color: #fff;
    }

    .twofa-card {
        background: #fff;
        border: 1px solid #dfe7ef;
        border-radius: 16px;
        padding: 36px 42px;
        box-shadow: 0 22px 48px rgba(15, 23, 42, 0.08);
    }

    .twofa-panel {
        display: none;
    }

    .twofa-panel.active {
        display: block;
    }

    .twofa-title {
        color: #073d34;
        font-size: 26px;
        font-weight: 900;
        margin: 0 0 8px;
    }

    .twofa-copy {
        color: #60708f;
        font-size: 18px;
        line-height: 1.6;
        margin: 0 0 26px;
    }

    .app-choice-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 30px;
    }

    .app-choice {
        min-height: 146px;
        border: 1px solid #dfe7ef;
        border-radius: 14px;
        background: #fff;
        display: grid;
        place-items: center;
        gap: 14px;
        padding: 22px;
        text-align: center;
        color: #07152f;
        font-weight: 900;
        font-size: 16px;
    }

    .app-icon {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        display: grid;
        place-items: center;
        font-size: 26px;
    }

    .app-icon.google {
        background: #e8efff;
        color: #4f83ff;
    }

    .app-icon.authy {
        background: #ffe1e5;
        color: #ef233c;
    }

    .app-icon.microsoft {
        background: #e8f5fb;
        color: #087cc8;
    }

    .twofa-action-row {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
    }

    .twofa-btn {
        min-height: 58px;
        border: 0;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 0 30px;
        background: #0c493b;
        color: #fff;
        font-size: 17px;
        font-weight: 900;
        text-decoration: none;
        box-shadow: 0 14px 28px rgba(12, 73, 59, 0.22);
    }

    .twofa-btn:hover {
        background: #0a3d33;
        color: #fff;
    }

    .twofa-btn.secondary {
        border: 1px solid #dbe3ea;
        background: #fff;
        color: #34415c;
        box-shadow: none;
    }

    .scan-grid {
        display: grid;
        grid-template-columns: 270px minmax(0, 1fr);
        gap: 28px;
        align-items: center;
    }

    .qr-box {
        border: 1px solid #dfe7ef;
        border-radius: 16px;
        padding: 22px;
        background: #f8fafc;
        text-align: center;
    }

    .qr-box img {
        width: 220px;
        max-width: 100%;
        border-radius: 10px;
        background: #fff;
    }

    .secret-box {
        display: flex;
        gap: 10px;
        margin: 14px 0 8px;
    }

    .secret-input,
    .code-input {
        width: 100%;
        min-height: 54px;
        border: 1px solid #dbe3ea;
        border-radius: 10px;
        padding: 0 16px;
        color: #07152f;
        font-size: 16px;
        outline: none;
        background: #fff;
    }

    .secret-input {
        font-family: ui-monospace, SFMono-Regular, Consolas, monospace;
    }

    .field-hint {
        display: block;
        color: #93a0b8;
        font-size: 13px;
        margin-top: 7px;
    }

    .code-input {
        max-width: 260px;
        text-align: center;
        letter-spacing: .45em;
        font-size: 28px;
        font-weight: 900;
    }

    .twofa-alert {
        border: 0;
        border-radius: 12px;
        padding: 14px 16px;
        margin-bottom: 20px;
        font-weight: 700;
    }

    .recovery-note {
        border: 1px solid #dfe7ef;
        border-radius: 14px;
        padding: 20px;
        background: #f8fafc;
        color: #60708f;
        font-weight: 600;
    }

    @media (max-width: 900px) {
        .twofa-stepper,
        .app-choice-grid,
        .scan-grid {
            grid-template-columns: 1fr;
        }

        .twofa-step:not(:last-child)::after {
            display: none;
        }
    }

    @media (max-width: 640px) {
        .twofa-page {
            padding: 24px 14px 48px;
        }

        .twofa-card {
            padding: 24px 18px;
        }

        .twofa-btn {
            width: 100%;
        }
    }
</style>

<div class="twofa-page">
    <div class="twofa-stepper" aria-label="Langkah setup 2FA">
        <div class="twofa-step active" data-step-indicator="1">
            <span class="step-number">1</span>
            <span>Install App</span>
        </div>
        <div class="twofa-step" data-step-indicator="2">
            <span class="step-number">2</span>
            <span>Scan QR</span>
        </div>
        <div class="twofa-step" data-step-indicator="3">
            <span class="step-number">3</span>
            <span>Konfirmasi</span>
        </div>
        <div class="twofa-step" data-step-indicator="4">
            <span class="step-number">4</span>
            <span>Recovery Codes</span>
        </div>
    </div>

    <div class="twofa-card">
        @if(session('success'))
            <div class="alert alert-success twofa-alert">
                <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger twofa-alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger twofa-alert">
                <i class="fa-solid fa-circle-exclamation me-2"></i>{{ $errors->first() }}
            </div>
        @endif

        <section class="twofa-panel active" data-step-panel="1">
            <h1 class="twofa-title">1. Install aplikasi authenticator</h1>
            <p class="twofa-copy">Pasang aplikasi <strong>Google Authenticator</strong>, <strong>Authy</strong>, atau <strong>Microsoft Authenticator</strong> di handphone Anda terlebih dahulu.</p>

            <div class="app-choice-grid">
                <div class="app-choice">
                    <span class="app-icon google"><i class="fa-solid fa-mobile-screen-button"></i></span>
                    <span>Google Authenticator</span>
                </div>
                <div class="app-choice">
                    <span class="app-icon authy"><i class="fa-solid fa-shield-halved"></i></span>
                    <span>Authy</span>
                </div>
                <div class="app-choice">
                    <span class="app-icon microsoft"><i class="fa-solid fa-window-maximize"></i></span>
                    <span>Microsoft Authenticator</span>
                </div>
            </div>

            <div class="twofa-action-row">
                <button type="button" class="twofa-btn" data-next-step="2">Lanjut <i class="fa-solid fa-arrow-right"></i></button>
            </div>
        </section>

        <section class="twofa-panel" data-step-panel="2">
            <h1 class="twofa-title">2. Scan QR Code</h1>
            <p class="twofa-copy">Buka aplikasi authenticator, pilih tambah akun, lalu scan QR Code berikut.</p>

            <div class="scan-grid">
                <div class="qr-box">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=240x240&data={{ urlencode($qrCodeUrl) }}" alt="QR Code 2FA">
                </div>
                <div>
                    <label for="secretKey" class="form-label fw-bold">Secret key untuk input manual</label>
                    <div class="secret-box">
                        <input type="text" class="secret-input" id="secretKey" value="{{ $secretKey }}" readonly>
                        <button class="twofa-btn secondary" type="button" onclick="copySecretKey()">
                            <i class="fa-solid fa-copy"></i>
                            Copy
                        </button>
                    </div>
                    <p class="field-hint">Gunakan secret key ini hanya jika QR Code tidak bisa dipindai.</p>
                </div>
            </div>

            <div class="twofa-action-row mt-4">
                <button type="button" class="twofa-btn secondary" data-prev-step="1">Kembali</button>
                <button type="button" class="twofa-btn" data-next-step="3">Lanjut <i class="fa-solid fa-arrow-right"></i></button>
            </div>
        </section>

        <section class="twofa-panel" data-step-panel="3">
            <h1 class="twofa-title">3. Konfirmasi kode</h1>
            <p class="twofa-copy">Masukkan kode 6 digit yang sedang aktif di aplikasi authenticator Anda.</p>

            <form method="POST" action="{{ route('2fa.enable') }}" id="enableForm">
                @csrf
                <div class="mb-4">
                    <label for="code" class="form-label fw-bold">Kode authenticator</label>
                    <input type="text" class="code-input" name="code" id="code" placeholder="000000" maxlength="6" pattern="[0-9]{6}" required>
                </div>

                <div class="twofa-action-row">
                    <button type="button" class="twofa-btn secondary" data-prev-step="2">Kembali</button>
                    <button type="submit" class="twofa-btn" id="enableBtn">
                        Aktifkan 2FA
                        <i class="fa-solid fa-shield-halved"></i>
                    </button>
                </div>
            </form>
        </section>

        <section class="twofa-panel" data-step-panel="4">
            <h1 class="twofa-title">4. Recovery Codes</h1>
            <p class="twofa-copy">Setelah kode berhasil dikonfirmasi, sistem akan menampilkan recovery codes yang harus Anda simpan.</p>
            <div class="recovery-note">
                Recovery codes dapat digunakan untuk login jika Anda kehilangan akses ke aplikasi authenticator.
            </div>
        </section>
    </div>
</div>

<script>
    function showStep(step) {
        document.querySelectorAll('[data-step-panel]').forEach(function (panel) {
            panel.classList.toggle('active', panel.dataset.stepPanel === String(step));
        });

        document.querySelectorAll('[data-step-indicator]').forEach(function (indicator) {
            indicator.classList.toggle('active', indicator.dataset.stepIndicator === String(step));
        });

        if (String(step) === '3') {
            setTimeout(function () {
                document.getElementById('code')?.focus();
            }, 80);
        }
    }

    function copySecretKey() {
        const secretKeyInput = document.getElementById('secretKey');
        secretKeyInput.select();
        secretKeyInput.setSelectionRange(0, 99999);

        navigator.clipboard.writeText(secretKeyInput.value).then(function () {
            alert('Secret key berhasil disalin.');
        }).catch(function () {
            document.execCommand('copy');
            alert('Secret key berhasil disalin.');
        });
    }

    document.addEventListener('click', function (event) {
        const next = event.target.closest('[data-next-step]');
        const prev = event.target.closest('[data-prev-step]');

        if (next) showStep(next.dataset.nextStep);
        if (prev) showStep(prev.dataset.prevStep);
    });

    const codeInput = document.getElementById('code');
    if (codeInput) {
        codeInput.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }

    const enableForm = document.getElementById('enableForm');
    const enableBtn = document.getElementById('enableBtn');
    if (enableForm && enableBtn) {
        enableForm.addEventListener('submit', function () {
            enableBtn.disabled = true;
            enableBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
        });
    }

    @if($errors->any())
        showStep(3);
    @endif
</script>
@endsection
