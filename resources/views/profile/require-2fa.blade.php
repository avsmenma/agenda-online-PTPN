@extends('layouts/app')

@section('content')
<style>
    .require-2fa-page {
        max-width: 1180px;
        margin: 0 auto;
        padding: 58px 24px 72px;
    }

    .require-2fa-card {
        max-width: 760px;
        margin: 0 auto;
        background: #fff;
        border: 1px solid #dfe7ef;
        border-radius: 16px;
        box-shadow: 0 22px 48px rgba(15, 23, 42, 0.08);
        overflow: hidden;
    }

    .require-2fa-header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 18px 24px;
        background: #ffc107;
        color: #07152f;
        font-size: 24px;
        font-weight: 900;
    }

    .require-2fa-body {
        padding: 42px 38px 38px;
        text-align: center;
    }

    .require-2fa-icon {
        width: 86px;
        height: 86px;
        border-radius: 999px;
        display: grid;
        place-items: center;
        margin: 0 auto 22px;
        background: #fff4cc;
        color: #ffc107;
        font-size: 44px;
    }

    .require-2fa-title {
        color: #07152f;
        font-size: 25px;
        font-weight: 900;
        margin-bottom: 12px;
    }

    .require-2fa-copy {
        max-width: 620px;
        margin: 0 auto 28px;
        color: #48536b;
        font-size: 18px;
        line-height: 1.55;
    }

    .require-2fa-button {
        min-height: 58px;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        padding: 0 30px;
        background: #0c493b;
        color: #fff;
        font-size: 18px;
        font-weight: 900;
        text-decoration: none;
        box-shadow: 0 14px 28px rgba(12, 73, 59, 0.22);
    }

    .require-2fa-button:hover {
        background: #0a3d33;
        color: #fff;
    }

    @media (max-width: 640px) {
        .require-2fa-page {
            padding: 32px 14px 48px;
        }

        .require-2fa-body {
            padding: 32px 20px;
        }

        .require-2fa-button {
            width: 100%;
        }
    }
</style>

<div class="require-2fa-page">
    <div class="require-2fa-card">
        <div class="require-2fa-header">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <span>2FA Diperlukan</span>
        </div>
        <div class="require-2fa-body">
            <div class="require-2fa-icon">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <h1 class="require-2fa-title">2FA Harus Diaktifkan</h1>
            <p class="require-2fa-copy">
                Untuk mengakses pengaturan akun, mengubah email, username, atau password, Anda harus mengaktifkan Two-Factor Authentication (2FA) terlebih dahulu.
            </p>
            <a href="{{ route('2fa.setup') }}" class="require-2fa-button">
                <i class="fa-solid fa-shield-halved"></i>
                Aktifkan 2FA Sekarang
            </a>
        </div>
    </div>
</div>
@endsection
