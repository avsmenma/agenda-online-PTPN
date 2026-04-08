@extends('layouts.app')

@section('title', 'User Management')

@section('content')
    <style>
        .user-mgmt-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .user-mgmt-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            border-radius: 12px 12px 0 0;
        }

        .user-table th {
            background: #f8fafc;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
        }

        .user-table td {
            vertical-align: middle;
        }

        .role-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .role-badge.programmer {
            background: #fef3c7;
            color: #b45309;
        }

        .role-badge.admin {
            background: #fee2e2;
            color: #dc2626;
        }

        .role-badge.owner {
            background: #e0e7ff;
            color: #4338ca;
        }

        .role-badge.operator {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .role-badge.team_verifikasi {
            background: #fef3c7;
            color: #b45309;
        }

        .role-badge.perpajakan {
            background: #e0e7ff;
            color: #4338ca;
        }

        .role-badge.akutansi {
            background: #d1fae5;
            color: #047857;
        }

        .role-badge.pembayaran {
            background: #fce7f3;
            color: #be185d;
        }

        .btn-edit-user {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border: none;
            color: white;
        }

        .btn-edit-user:hover {
            background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
            color: white;
        }

        .btn-delete-user {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
            color: white;
        }

        .btn-delete-user:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
        }
    </style>

    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('programmer.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">User Management</li>
                    </ol>
                </nav>
                <h2><i class="fas fa-users-cog text-success me-2"></i>User Management</h2>
                <p class="text-muted">Kelola data user dan credentials untuk semua role</p>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card user-mgmt-card">
                    <div class="card-header user-mgmt-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Daftar Users</h5>
                        <div>
                            <select class="form-select form-select-sm d-inline-block w-auto me-2" id="filter-role"
                                onchange="window.location.href='?role=' + this.value">
                                <option value="">Semua Role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role }}" {{ $roleFilter == $role ? 'selected' : '' }}>
                                        {{ ucwords(str_replace('_', ' ', $role)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover user-table mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>No. HP</th>
                                        <th>Role</th>
                                        <th>Bagian</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $user)
                                        <tr>
                                            <td><strong>{{ $user->id }}</strong></td>
                                            <td>{{ $user->name }}</td>
                                            <td><code>{{ $user->username }}</code></td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ $user->phone_number ?? '-' }}</td>
                                            <td>
                                                <span class="role-badge {{ strtolower($user->role ?? '') }}">
                                                    {{ ucwords(str_replace('_', ' ', $user->role ?? '-')) }}
                                                </span>
                                            </td>
                                            <td>{{ $user->bagian_code ?? '-' }}</td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-edit-user" data-id="{{ $user->id }}"
                                                        onclick="editUser({{ $user->id }})">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <button class="btn btn-sm btn-delete-user" data-id="{{ $user->id }}"
                                                        onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                <i class="fas fa-users fa-2x mb-2"></i>
                                                <p class="mb-0">Tidak ada user ditemukan</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if ($users->hasPages())
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <small class="text-muted">
                                    Showing {{ $users->firstItem() }} - {{ $users->lastItem() }} of {{ $users->total() }}
                                </small>
                                {{ $users->appends(['role' => $roleFilter])->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i>Edit User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm">
                        @csrf
                        <input type="hidden" id="edit-user-id" name="id">

                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" id="edit-name" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" id="edit-username" name="username" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit-email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-select" id="edit-role" name="role" required>
                                <option value="Admin">Admin</option>
                                <option value="owner">Owner</option>
                                <option value="programmer">Programmer</option>
                                <option value="operator">Operator</option>
                                <option value="team_verifikasi">Team Verifikasi</option>
                                <option value="perpajakan">Perpajakan</option>
                                <option value="akutansi">Akuntansi</option>
                                <option value="pembayaran">Pembayaran</option>
                                <option value="bagian_akn">Bagian AKN</option>
                                <option value="bagian_dpm">Bagian DPM</option>
                                <option value="bagian_kpl">Bagian KPL</option>
                                <option value="bagian_pmo">Bagian PMO</option>
                                <option value="bagian_sdm">Bagian SDM</option>
                                <option value="bagian_skh">Bagian SKH</option>
                                <option value="bagian_tan">Bagian TAN</option>
                                <option value="bagian_tep">Bagian TEP</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Bagian Code</label>
                            <input type="text" class="form-control" id="edit-bagian-code" name="bagian_code"
                                placeholder="Optional">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="edit-phone" name="phone_number"
                                placeholder="Optional">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" id="edit-password" name="password"
                                placeholder="Kosongkan jika tidak ingin mengubah password">
                            <small class="text-muted">Minimal 6 karakter</small>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Perhatian:</strong> Perubahan akan langsung tersimpan!
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" id="btn-save-user" onclick="saveUser()">
                        <i class="fas fa-save me-2"></i>Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let editModal;

        $(document).ready(function () {
            editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
        });

        function editUser(userId) {
            // Show loading
            $('#btn-save-user').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Loading...');

            $.ajax({
                url: '{{ url("programmer/user-management") }}/' + userId,
                method: 'GET',
                success: function (response) {
                    if (response.success) {
                        const user = response.user;
                        $('#edit-user-id').val(user.id);
                        $('#edit-name').val(user.name);
                        $('#edit-username').val(user.username);
                        $('#edit-email').val(user.email);
                        $('#edit-role').val(user.role);
                        $('#edit-bagian-code').val(user.bagian_code || '');
                        $('#edit-phone').val(user.phone_number || '');
                        $('#edit-password').val('');

                        editModal.show();
                    } else {
                        alert('Gagal memuat data user');
                    }
                    $('#btn-save-user').prop('disabled', false).html(
                        '<i class="fas fa-save me-2"></i>Simpan');
                },
                error: function (xhr) {
                    alert('Error: ' + (xhr.responseJSON?.message || 'Gagal memuat data'));
                    $('#btn-save-user').prop('disabled', false).html(
                        '<i class="fas fa-save me-2"></i>Simpan');
                }
            });
        }

        function saveUser() {
            const formData = {
                id: $('#edit-user-id').val(),
                name: $('#edit-name').val(),
                username: $('#edit-username').val(),
                email: $('#edit-email').val(),
                role: $('#edit-role').val(),
                bagian_code: $('#edit-bagian-code').val(),
                phone_number: $('#edit-phone').val(),
                password: $('#edit-password').val(),
                _token: '{{ csrf_token() }}'
            };

            if (!confirm('Apakah Anda yakin ingin menyimpan perubahan?')) {
                return;
            }

            $('#btn-save-user').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...');

            $.ajax({
                url: '{{ route("programmer.user-management.update") }}',
                method: 'POST',
                data: formData,
                success: function (response) {
                    if (response.success) {
                        alert('User berhasil diupdate!');
                        editModal.hide();
                        window.location.reload();
                    } else {
                        alert(response.message || 'Gagal menyimpan');
                    }
                    $('#btn-save-user').prop('disabled', false).html(
                        '<i class="fas fa-save me-2"></i>Simpan');
                },
                error: function (xhr) {
                    alert('Error: ' + (xhr.responseJSON?.message || 'Gagal menyimpan'));
                    $('#btn-save-user').prop('disabled', false).html(
                        '<i class="fas fa-save me-2"></i>Simpan');
                }
            });
        }

        function deleteUser(userId, userName) {
            if (!confirm(`Apakah Anda yakin ingin menghapus user "${userName}"? Tindakan ini tidak dapat dibatalkan!`)) {
                return;
            }

            // Disable delete button and show loading
            $(`button[data-id="${userId}"].btn-delete-user`).prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin"></i> Deleting...');

            $.ajax({
                url: '{{ route("programmer.user-management.destroy", ['id' => 'PLACEHOLDER_ID']) }}'.replace('PLACEHOLDER_ID', userId),
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    if (response.success) {
                        alert('User berhasil dihapus!');
                        window.location.reload();
                    } else {
                        alert(response.message || 'Gagal menghapus user');
                        // Restore button
                        $(`button[data-id="${userId}"].btn-delete-user`).prop('disabled', false)
                            .html('<i class="fas fa-trash"></i> Delete');
                    }
                },
                error: function (xhr) {
                    alert('Error: ' + (xhr.responseJSON?.message || 'Gagal menghapus user'));
                    // Restore button
                    $(`button[data-id="${userId}"].btn-delete-user`).prop('disabled', false)
                        .html('<i class="fas fa-trash"></i> Delete');
                }
            });
        }
    </script>
@endsection