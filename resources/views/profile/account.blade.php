@extends('layouts/app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-user-circle me-2"></i>
                        Pengaturan Akun
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

                    <!-- Info Alert -->
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Catatan:</strong> Untuk mengubah username, email, atau password, 2FA harus diaktifkan terlebih dahulu.
                    </div>

                    <!-- User Information -->
                    <div class="mb-4">
                        <h5>Informasi Akun</h5>
                        <table class="table table-borderless">
                            <tr>
                                <td width="200"><strong>Username:</strong></td>
                                <td>{{ $user->username }}</td>
                            </tr>
                            <tr>
                                <td><strong>Nama:</strong></td>
                                <td>{{ $user->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <td><strong>Role:</strong></td>
                                <td>{{ $user->getRoleDisplayName() }}</td>
                            </tr>
                            <tr>
                                <td><strong>2FA Status:</strong></td>
                                <td>
                                    @if($user->hasTwoFactorEnabled())
                                        <span class="badge bg-success">
                                            <i class="fas fa-shield-alt me-1"></i>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            Tidak Aktif
                                        </span>
                                        <a href="{{ route('2fa.setup') }}" class="btn btn-sm btn-primary ms-2">
                                            Aktifkan 2FA
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>

                    <hr>

                    <!-- Update Username Form -->
                    <div class="mb-4">
                        <h5>Ubah Username</h5>
                        <form method="POST" action="{{ route('profile.update-username') }}">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">Username Baru</label>
                                <input type="text" 
                                       class="form-control @error('username') is-invalid @enderror" 
                                       id="username" 
                                       name="username" 
                                       value="{{ old('username', $user->username) }}" 
                                       required
                                       pattern="[a-zA-Z0-9_-]+"
                                       title="Username hanya boleh berisi huruf, angka, dash (-) dan underscore (_)">
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Username hanya boleh berisi huruf, angka, dash (-) dan underscore (_)</small>
                            </div>

                            <div class="mb-3">
                                <label for="password_username" class="form-label">Password (Konfirmasi)</label>
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password_username" 
                                       name="password" 
                                       required
                                       placeholder="Masukkan password Anda untuk konfirmasi">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Simpan Perubahan Username
                            </button>
                        </form>
                    </div>

                    <hr>

                    <!-- Update Email Form -->
                    <div class="mb-4">
                        <h5>Ubah Email</h5>
                        <form method="POST" action="{{ route('profile.update-email') }}">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Baru</label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', $user->email) }}" 
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password_email" class="form-label">Password (Konfirmasi)</label>
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password_email" 
                                       name="password" 
                                       required
                                       placeholder="Masukkan password Anda untuk konfirmasi">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Simpan Perubahan Email
                            </button>
                        </form>
                    </div>

                    <hr>

                    <!-- Update Password Form -->
                    <div class="mb-4">
                        <h5>Ubah Password</h5>
                        <form method="POST" action="{{ route('profile.update-password') }}">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Password Lama</label>
                                <input type="password" 
                                       class="form-control @error('current_password') is-invalid @enderror" 
                                       id="current_password" 
                                       name="current_password" 
                                       required
                                       placeholder="Masukkan password lama">
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="new_password" class="form-label">Password Baru</label>
                                <input type="password" 
                                       class="form-control @error('new_password') is-invalid @enderror" 
                                       id="new_password" 
                                       name="new_password" 
                                       required
                                       minlength="6"
                                       placeholder="Masukkan password baru (minimal 6 karakter)">
                                @error('new_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="new_password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="new_password_confirmation" 
                                       name="new_password_confirmation" 
                                       required
                                       minlength="6"
                                       placeholder="Ulangi password baru">
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-key me-2"></i>
                                Simpan Perubahan Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection






