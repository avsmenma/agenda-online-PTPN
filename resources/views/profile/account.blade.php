@extends(strtolower(auth()->user()->role ?? '') === 'programmer' ? 'layouts.programmer' : 'layouts/app')

@section('title', 'Profil Saya')
@section('page_title', 'Profil Saya')
@section('page_breadcrumb', 'Programmer - Profil Saya')

@section('content')
@php
    $displayName = trim((string) ($user->name ?? 'User'));
    $profilePhotoUrl = $user->profile_photo_url;
    $initials = collect(explode(' ', $displayName))
        ->filter()
        ->take(2)
        ->map(fn($part) => strtoupper(substr($part, 0, 1)))
        ->implode('') ?: 'U';
    $resetStatus = $latestResetRequest->status ?? null;
    $resetLabel = match ($resetStatus) {
        'pending' => 'Menunggu persetujuan',
        'approved' => 'Disetujui',
        'rejected' => 'Ditolak',
        default => 'Belum ada pengajuan',
    };
@endphp

<style>
    .profile-page,
    .profile-page * {
        box-sizing: border-box;
    }

    .profile-page {
        width: 100%;
        max-width: 1320px;
        margin: 0 auto;
        padding: 36px clamp(18px, 2vw, 32px) 72px;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: minmax(280px, 340px) minmax(0, 1fr);
        gap: clamp(18px, 2vw, 28px);
        align-items: start;
    }

    .profile-card {
        min-width: 0;
        background: #fff;
        border: 1px solid #dfe7ef;
        border-radius: 14px;
        box-shadow: 0 18px 42px rgba(15, 23, 42, 0.08);
    }

    .profile-sidebar {
        padding: 30px 24px;
        min-height: 560px;
        position: sticky;
        top: 24px;
    }

    .profile-avatar {
        width: 124px;
        height: 124px;
        border-radius: 999px;
        display: grid;
        place-items: center;
        margin: 0 auto 18px;
        background: #0c493b;
        color: #fff;
        font-size: 38px;
        font-weight: 800;
        box-shadow: 0 14px 30px rgba(12, 73, 59, 0.24);
        overflow: hidden;
    }

    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .profile-photo-form {
        margin: 18px 0 0;
        display: grid;
        gap: 10px;
    }

    .profile-photo-input {
        width: 100%;
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        padding: 10px;
        color: #34415c;
        background: #f8fafc;
        font-size: 13px;
    }

    .profile-photo-actions {
        display: grid;
        grid-template-columns: 1fr;
        gap: 8px;
    }

    .btn-photo-profile {
        min-height: 42px;
        border-radius: 10px;
        border: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background: #0c493b;
        color: #fff;
        padding: 0 14px;
        font-size: 13px;
        font-weight: 800;
    }

    .btn-photo-profile:hover {
        background: #0a3d33;
    }

    .btn-photo-remove {
        min-height: 40px;
        border-radius: 10px;
        border: 1px solid #fecaca;
        background: #fff;
        color: #dc2626;
        padding: 0 14px;
        font-size: 13px;
        font-weight: 800;
    }

    .profile-name {
        text-align: center;
        color: #07152f;
        font-size: 22px;
        font-weight: 800;
        margin-bottom: 4px;
    }

    .profile-email {
        text-align: center;
        color: #61708f;
        font-size: 15px;
        margin-bottom: 14px;
        overflow-wrap: anywhere;
    }

    .role-pill {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin: 0 auto;
        padding: 7px 14px;
        border-radius: 999px;
        background: #dff1ff;
        color: #0069a8;
        font-weight: 800;
        font-size: 13px;
    }

    .profile-menu {
        border-top: 1px solid #dfe7ef;
        margin-top: 26px;
        padding-top: 20px;
        display: grid;
        gap: 8px;
    }

    .profile-menu-item,
    .profile-menu-button {
        border: 0;
        width: 100%;
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 16px;
        border-radius: 8px;
        background: transparent;
        color: #34415c;
        font-size: 15px;
        font-weight: 800;
        text-decoration: none;
        text-align: left;
    }

    .profile-menu-item:hover,
    .profile-menu-button:hover {
        background: #eef7fd;
        color: #0069a8;
    }

    .profile-menu-item.active {
        background: #dceffb;
        color: #0069a8;
    }

    .profile-menu-item i,
    .profile-menu-button i {
        width: 18px;
        text-align: center;
    }

    .profile-menu-button.logout {
        color: #ef233c;
    }

    .account-panel {
        padding: clamp(22px, 2.3vw, 34px);
    }

    .panel-header {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        align-items: center;
        margin-bottom: 26px;
    }

    .panel-title {
        color: #07152f;
        font-size: clamp(21px, 1.8vw, 28px);
        font-weight: 800;
        margin: 0;
    }

    .panel-muted {
        color: #93a0b8;
        font-size: 14px;
        font-weight: 600;
    }

    .profile-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px 22px;
    }

    .field-label {
        display: block;
        margin-bottom: 8px;
        color: #66728f;
        font-size: 13px;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .profile-input {
        width: 100%;
        min-width: 0;
        min-height: 54px;
        border: 1px solid #dbe3ea;
        border-radius: 9px;
        padding: 0 16px;
        color: #07152f;
        background: #fff;
        font-size: 17px;
        font-weight: 500;
        outline: none;
    }

    .profile-input:focus {
        border-color: #0c493b;
        box-shadow: 0 0 0 3px rgba(12, 73, 59, 0.1);
    }

    .field-hint {
        display: block;
        color: #93a0b8;
        font-size: 13px;
        margin-top: 7px;
    }

    .profile-section-divider {
        border: 0;
        border-top: 1px solid #dfe7ef;
        margin: 30px 0 24px;
    }

    .security-title {
        color: #07152f;
        font-size: 18px;
        font-weight: 800;
        margin: 0 0 16px;
    }

    .security-list {
        display: grid;
        gap: 14px;
    }

    .security-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        min-width: 0;
        border: 1px solid #dfe7ef;
        border-radius: 12px;
        padding: 18px;
        background: #fff;
    }

    .security-copy {
        display: flex;
        align-items: center;
        gap: 16px;
        min-width: 0;
    }

    .security-copy > div:last-child {
        min-width: 0;
    }

    .security-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        flex: 0 0 auto;
        font-size: 20px;
    }

    .security-icon.ok {
        background: #dcf8ed;
        color: #10b981;
    }

    .security-icon.key {
        background: #efe7ff;
        color: #7c3aed;
    }

    .security-heading {
        color: #07152f;
        font-size: 16px;
        font-weight: 800;
        margin: 0 0 3px;
        overflow-wrap: anywhere;
    }

    .security-sub {
        color: #60708f;
        font-size: 14px;
        margin: 0;
    }

    .security-sub.success {
        color: #00b96b;
        font-weight: 700;
    }

    .security-sub.warning {
        color: #f59e0b;
        font-weight: 700;
    }

    .profile-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 26px;
    }

    .btn-soft {
        min-height: 56px;
        border-radius: 10px;
        border: 1px solid #dbe3ea;
        background: #fff;
        color: #34415c;
        padding: 0 26px;
        font-weight: 800;
    }

    .btn-primary-profile,
    .btn-security {
        min-height: 56px;
        border-radius: 10px;
        border: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        background: #0c493b;
        color: #fff;
        padding: 0 28px;
        font-weight: 800;
        text-decoration: none;
        box-shadow: 0 12px 24px rgba(12, 73, 59, 0.2);
        white-space: nowrap;
    }

    .btn-primary-profile:hover,
    .btn-security:hover {
        color: #fff;
        background: #0a3d33;
    }

    .btn-danger-outline {
        min-height: 50px;
        border: 1px solid #ffc7c7;
        border-radius: 10px;
        background: #fff;
        color: #ef233c;
        padding: 0 22px;
        font-weight: 800;
    }

    .profile-alert {
        border: 0;
        border-radius: 10px;
        padding: 14px 16px;
        margin-bottom: 18px;
        font-weight: 700;
    }

    @media (max-width: 992px) {
        .profile-grid {
            grid-template-columns: 1fr;
        }

        .profile-sidebar {
            min-height: auto;
            position: static;
        }
    }

    @media (max-width: 720px) {
        .profile-page {
            padding: 22px 12px 48px;
        }

        .account-panel {
            padding: 22px 18px;
        }

        .profile-form-grid,
        .profile-grid {
            grid-template-columns: 1fr;
        }

        .panel-header,
        .security-row,
        .profile-actions {
            align-items: stretch;
            flex-direction: column;
        }

        .profile-sidebar {
            padding: 24px 18px;
        }

        .profile-avatar {
            width: 104px;
            height: 104px;
            font-size: 32px;
        }

        .profile-photo-actions {
            grid-template-columns: 1fr;
        }

        .profile-name {
            font-size: 20px;
        }

        .security-copy {
            align-items: flex-start;
        }

        .btn-danger-outline,
        .btn-security,
        .btn-soft,
        .btn-primary-profile {
            width: 100%;
        }
    }
</style>

<div class="profile-page">
    <div class="profile-grid">
        <aside class="profile-card profile-sidebar">
            <div class="profile-avatar">
                @if($profilePhotoUrl)
                    <img src="{{ $profilePhotoUrl }}" alt="Foto profil {{ $displayName }}">
                @else
                    {{ $initials }}
                @endif
            </div>
            <div class="profile-name">{{ $displayName }}</div>
            <div class="profile-email">{{ $user->email }}</div>
            <div class="text-center">
                <span class="role-pill"><i class="fa-solid fa-id-badge"></i>{{ $user->getRoleDisplayName() }}</span>
            </div>

            <form class="profile-photo-form" method="POST" action="{{ route('profile.update-photo') }}" enctype="multipart/form-data">
                @csrf
                <label for="profile_photo" class="field-label mb-0">Foto Profil</label>
                <input id="profile_photo" name="profile_photo" type="file" accept="image/png,image/jpeg,image/webp" class="profile-photo-input @error('profile_photo') is-invalid @enderror" required>
                <span class="field-hint">Format JPG, PNG, atau WebP. Maksimal 2 MB.</span>
                <div class="profile-photo-actions">
                    <button type="submit" class="btn-photo-profile">
                        <i class="fa-solid fa-camera"></i>
                        {{ $profilePhotoUrl ? 'Ganti Foto' : 'Upload Foto' }}
                    </button>
                </div>
            </form>

            @if($profilePhotoUrl)
                <form class="profile-photo-form" method="POST" action="{{ route('profile.delete-photo') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-photo-remove" onclick="return confirm('Hapus foto profil saat ini?');">
                        <i class="fa-solid fa-trash"></i>
                        Hapus Foto
                    </button>
                </form>
            @endif

            <nav class="profile-menu" aria-label="Menu profil">
                <a href="#account-info" class="profile-menu-item active">
                    <i class="fa-solid fa-user"></i>
                    <span>Informasi Akun</span>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="profile-menu-button logout">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </nav>
        </aside>

        <main class="profile-card account-panel" id="account-info">
            @if(session('success'))
                <div class="alert alert-success profile-alert">
                    <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger profile-alert">
                    <i class="fa-solid fa-circle-exclamation me-2"></i>{{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger profile-alert">
                    <i class="fa-solid fa-circle-exclamation me-2"></i>{{ $errors->first() }}
                </div>
            @endif

            <form id="accountForm" method="POST" action="{{ route('profile.update-account') }}">
                @csrf
                <div class="panel-header">
                    <h1 class="panel-title">Informasi Akun</h1>
                    <span class="panel-muted">Terakhir diubah: {{ $user->updated_at?->diffForHumans() ?? '-' }}</span>
                </div>

                <div class="profile-form-grid">
                    <div>
                        <label for="name" class="field-label">Nama Lengkap</label>
                        <input id="name" name="name" type="text" class="profile-input @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <div>
                        <label for="username" class="field-label">Username</label>
                        <input id="username" name="username" type="text" class="profile-input @error('username') is-invalid @enderror" value="{{ old('username', $user->username) }}" required pattern="[a-zA-Z0-9_-]+">
                    </div>

                    <div>
                        <label for="email" class="field-label">Email</label>
                        <input id="email" name="email" type="email" class="profile-input @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                    </div>

                    <div>
                        <label for="phone_number" class="field-label">Nomor HP</label>
                        <input id="phone_number" name="phone_number" type="text" class="profile-input @error('phone_number') is-invalid @enderror" value="{{ old('phone_number', $user->phone_number) }}" placeholder="+62">
                        <span class="field-hint">Digunakan untuk notifikasi WhatsApp</span>
                    </div>
                </div>
            </form>

                <hr class="profile-section-divider">

                <section id="security-section">
                    <h2 class="security-title">Keamanan</h2>

                    <div class="security-list">
                        <div class="security-row">
                            <div class="security-copy">
                                <div class="security-icon ok"><i class="fa-solid fa-shield-halved"></i></div>
                                <div>
                                    <h3 class="security-heading">Two-Factor Authentication</h3>
                                    @if($user->hasTwoFactorEnabled())
                                        <p class="security-sub success">
                                            <i class="fa-solid fa-circle-check me-1"></i>
                                            Aktif - Google Authenticator
                                        </p>
                                    @else
                                        <p class="security-sub warning">
                                            <i class="fa-solid fa-circle-exclamation me-1"></i>
                                            Belum aktif - aktifkan untuk mengubah data akun
                                        </p>
                                    @endif

                                    @if($user->hasTwoFactorEnabled() && $latestResetRequest)
                                        <p class="security-sub">Pengajuan reset: {{ $resetLabel }}</p>
                                        @if($resetStatus === 'rejected' && !empty($latestResetRequest->notes))
                                            <p class="security-sub">Alasan: {{ $latestResetRequest->notes }}</p>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 justify-content-end">
                                @if($user->hasTwoFactorEnabled())
                                    @if(!($latestResetRequest && $latestResetRequest->status === 'pending'))
                                        <button type="button" class="btn-danger-outline" data-bs-toggle="modal" data-bs-target="#request2faResetModal">
                                            Reset 2FA
                                        </button>
                                    @else
                                        <button type="button" class="btn-danger-outline" disabled>Menunggu</button>
                                    @endif

                                    <form method="POST" action="{{ route('2fa.disable') }}" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan 2FA? Akun Anda akan menjadi kurang aman.');">
                                        @csrf
                                        <input type="hidden" name="password" id="disable2faPassword">
                                        <button type="submit" class="btn-danger-outline">Disable</button>
                                    </form>
                                @else
                                    <a href="{{ route('2fa.setup') }}" class="btn-security">
                                        <i class="fa-solid fa-shield-halved"></i>
                                        Aktifkan 2FA
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div class="security-row">
                            <div class="security-copy">
                                <div class="security-icon key"><i class="fa-solid fa-key"></i></div>
                                <div>
                                    <h3 class="security-heading">Password</h3>
                                    <p class="security-sub">Terakhir diubah {{ $user->updated_at?->diffForHumans() ?? '-' }}</p>
                                </div>
                            </div>
                            <button type="button" class="btn-security" data-bs-toggle="modal" data-bs-target="#passwordModal">Ubah Password</button>
                        </div>
                    </div>
                </section>

                <div class="profile-actions">
                    <a href="{{ url(Auth::user()->getDashboardRoute()) }}" class="btn-soft text-decoration-none d-inline-flex align-items-center justify-content-center">Batal</a>
                    <button type="submit" form="accountForm" class="btn-primary-profile">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Simpan Perubahan
                    </button>
                </div>
        </main>
    </div>
</div>

<div class="modal fade" id="passwordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 14px;">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-key me-2"></i>Ubah Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('profile.update-password') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="current_password" class="form-label fw-bold">Password Lama</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label fw-bold">Password Baru</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" minlength="8" required>
                    </div>
                    <div class="mb-0">
                        <label for="new_password_confirmation" class="form-label fw-bold">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" minlength="8" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-soft" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn-primary-profile">Simpan Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if($user->hasTwoFactorEnabled())
    <div class="modal fade" id="request2faResetModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0" style="border-radius: 14px;">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-shield-halved me-2"></i>Ajukan Reset 2FA</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('profile.2fa-reset-requests.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            Reset 2FA akan menonaktifkan autentikasi dua faktor pada akun Anda setelah disetujui programmer.
                        </div>
                        <label class="form-label fw-bold">Alasan Reset</label>
                        <textarea name="reason" class="form-control @error('reason') is-invalid @enderror" rows="4" required minlength="10">{{ old('reason') }}</textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-soft" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn-danger-outline">Kirim Pengajuan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const disableForm = document.querySelector('form[action="{{ route('2fa.disable') }}"]');
        if (!disableForm) return;

        disableForm.addEventListener('submit', function (event) {
            const target = document.getElementById('disable2faPassword');
            if (!target || target.value) return;

            const password = window.prompt('Masukkan password Anda untuk menonaktifkan 2FA.');
            if (!password) {
                event.preventDefault();
                return;
            }
            target.value = password;
        });
    });
</script>
@endsection
