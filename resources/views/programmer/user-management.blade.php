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

        .role-badge.programmer { background: #fef3c7; color: #b45309; }
        .role-badge.admin      { background: #fee2e2; color: #dc2626; }
        .role-badge.owner      { background: #e0e7ff; color: #4338ca; }
        .role-badge.operator   { background: #dbeafe; color: #1d4ed8; }
        .role-badge.team_verifikasi { background: #fef3c7; color: #b45309; }
        .role-badge.perpajakan { background: #e0e7ff; color: #4338ca; }
        .role-badge.akutansi   { background: #d1fae5; color: #047857; }
        .role-badge.pembayaran { background: #fce7f3; color: #be185d; }

        .btn-edit-user {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border: none; color: white;
        }
        .btn-edit-user:hover {
            background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
            color: white;
        }

        .btn-delete-user {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none; color: white;
        }
        .btn-delete-user:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
        }

        /* ── Multi-phone tag input ── */
        .phone-tag-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            padding: 6px 10px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            background: #fff;
            min-height: 42px;
            cursor: text;
            transition: border-color .15s;
        }
        .phone-tag-wrapper:focus-within { border-color: #86b7fe; box-shadow: 0 0 0 .25rem rgba(13,110,253,.25); }
        .phone-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #e0f2fe;
            color: #0369a1;
            border-radius: 20px;
            padding: 2px 10px 2px 10px;
            font-size: 13px;
            font-weight: 500;
        }
        .phone-tag .remove-tag {
            cursor: pointer;
            font-size: 15px;
            line-height: 1;
            color: #0369a1;
            opacity: .7;
            padding: 0 0 0 2px;
        }
        .phone-tag .remove-tag:hover { opacity: 1; }
        .phone-tag-input {
            border: none;
            outline: none;
            flex: 1;
            min-width: 160px;
            font-size: 14px;
            padding: 2px 0;
        }
        .phone-tag-hint {
            font-size: 11px;
            color: #94a3b8;
            margin-top: 4px;
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
                        <div class="d-flex align-items-center gap-2">
                            <select class="form-select form-select-sm d-inline-block w-auto" id="filter-role"
                                onchange="window.location.href='?role=' + this.value">
                                <option value="">Semua Role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role }}" {{ $roleFilter == $role ? 'selected' : '' }}>
                                        {{ ucwords(str_replace('_', ' ', $role)) }}
                                    </option>
                                @endforeach
                            </select>
                            <button class="btn btn-sm btn-light fw-semibold" onclick="openCreateModal()">
                                <i class="fas fa-user-plus me-1 text-success"></i>Tambah User
                            </button>
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
                                            <td>
                                                @if($user->phone_number)
                                                    @foreach(array_filter(array_map('trim', explode('|', $user->phone_number))) as $phone)
                                                        <span class="badge bg-light text-dark border me-1 mb-1" style="font-size:11px;">
                                                            <i class="fas fa-phone fa-xs me-1 text-success"></i>{{ $phone }}
                                                        </span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
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

    {{-- ═══════════════════════════════════════════════════════
         MODAL: CREATE USER (TAMBAH USER BARU)
         ═══════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="createUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg,#10b981 0%,#059669 100%); color:white;">
                    <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Tambah User Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="createUserForm">
                        @csrf
                        <div class="row g-3">
                            {{-- Kolom kiri --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="create-name" name="name" required
                                    placeholder="Nama lengkap user">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="create-username" name="username" required
                                    placeholder="username unik">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="create-email" name="email" required
                                    placeholder="email@domain.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="create-password" name="password" required
                                    placeholder="Minimal 8 karakter">
                                <small class="text-muted">Minimal 8 karakter</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="create-role" name="role" required>
                                    <option value="">-- Pilih Role --</option>
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
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Bagian Code</label>
                                <input type="text" class="form-control" id="create-bagian-code" name="bagian_code"
                                    placeholder="Opsional (misal: AKN, DPM)">
                            </div>

                            {{-- Multi-nomor HP --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">No. HP</label>
                                <input type="hidden" id="create-phone" name="phone_number">
                                <div class="phone-tag-wrapper" id="create-phone-wrapper"
                                     onclick="document.getElementById('create-phone-input').focus()">
                                    <input type="text" id="create-phone-input" class="phone-tag-input"
                                           placeholder="Ketik nomor lalu Enter atau koma...">
                                </div>
                                <p class="phone-tag-hint mb-0">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Tekan <kbd>Enter</kbd> atau <kbd>,</kbd> setelah mengetik nomor untuk menambahkan. Bisa lebih dari 1 nomor.
                                </p>
                            </div>
                        </div>

                        <div class="alert alert-info mt-3 mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            User baru dapat langsung login menggunakan username dan password yang dibuat.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-success" id="btn-create-user" onclick="createUser()">
                        <i class="fas fa-user-plus me-2"></i>Buat User
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         MODAL: EDIT USER
         ═══════════════════════════════════════════════════════ --}}
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i>Edit User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm">
                        @csrf
                        <input type="hidden" id="edit-user-id" name="id">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit-name" name="name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit-username" name="username" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="edit-email" name="email" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
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
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Bagian Code</label>
                                <input type="text" class="form-control" id="edit-bagian-code" name="bagian_code"
                                    placeholder="Opsional">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">New Password</label>
                                <input type="password" class="form-control" id="edit-password" name="password"
                                    placeholder="Kosongkan jika tidak ingin mengubah">
                                <small class="text-muted">Minimal 8 karakter</small>
                            </div>

                            {{-- Multi-nomor HP --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">No. HP</label>
                                <input type="hidden" id="edit-phone" name="phone_number">
                                <div class="phone-tag-wrapper" id="edit-phone-wrapper"
                                     onclick="document.getElementById('edit-phone-input').focus()">
                                    <input type="text" id="edit-phone-input" class="phone-tag-input"
                                           placeholder="Ketik nomor lalu Enter atau koma...">
                                </div>
                                <p class="phone-tag-hint mb-0">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Tekan <kbd>Enter</kbd> atau <kbd>,</kbd> untuk menambahkan nomor. Klik <strong>×</strong> untuk menghapus.
                                </p>
                            </div>
                        </div>

                        <div class="alert alert-warning mt-3 mb-0">
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
        let editModal, createModal;

        $(document).ready(function () {
            editModal   = new bootstrap.Modal(document.getElementById('editUserModal'));
            createModal = new bootstrap.Modal(document.getElementById('createUserModal'));

            // Reset create form when modal closes
            document.getElementById('createUserModal').addEventListener('hidden.bs.modal', function () {
                document.getElementById('createUserForm').reset();
                clearPhoneTags('create');
            });
        });

        // ═══════════════════════════════════════════════════
        // MULTI-PHONE TAG SYSTEM
        // ═══════════════════════════════════════════════════

        /** phoneState[prefix] = [ '08xxx', '08yyy', ... ] */
        const phoneState = { create: [], edit: [] };

        function renderPhoneTags(prefix) {
            const wrapper = document.getElementById(`${prefix}-phone-wrapper`);
            const input   = document.getElementById(`${prefix}-phone-input`);
            const hidden  = document.getElementById(`${prefix}-phone`);

            // Remove existing tags (not the input)
            wrapper.querySelectorAll('.phone-tag').forEach(t => t.remove());

            phoneState[prefix].forEach((num, idx) => {
                const tag = document.createElement('span');
                tag.className = 'phone-tag';
                tag.innerHTML = `${num} <span class="remove-tag" onclick="removePhoneTag('${prefix}',${idx})">×</span>`;
                wrapper.insertBefore(tag, input);
            });

            // Sync hidden input (pipe-separated)
            hidden.value = phoneState[prefix].join('|');
        }

        function addPhoneTag(prefix) {
            const input = document.getElementById(`${prefix}-phone-input`);
            const val   = input.value.trim().replace(/,$/, '');
            if (!val) return;

            // One entry might contain commas (user pasted "08xxx,08yyy")
            const nums = val.split(/[,|]+/).map(s => s.trim()).filter(Boolean);
            nums.forEach(n => {
                if (n && !phoneState[prefix].includes(n)) {
                    phoneState[prefix].push(n);
                }
            });

            input.value = '';
            renderPhoneTags(prefix);
        }

        function removePhoneTag(prefix, idx) {
            phoneState[prefix].splice(idx, 1);
            renderPhoneTags(prefix);
        }

        function clearPhoneTags(prefix) {
            phoneState[prefix] = [];
            renderPhoneTags(prefix);
        }

        function loadPhoneTags(prefix, phoneString) {
            phoneState[prefix] = [];
            if (phoneString) {
                phoneState[prefix] = phoneString.split(/[|,]+/).map(s => s.trim()).filter(Boolean);
            }
            renderPhoneTags(prefix);
        }

        /** Attach keydown listeners for both modals */
        ['create', 'edit'].forEach(prefix => {
            document.addEventListener('DOMContentLoaded', () => {
                const inp = document.getElementById(`${prefix}-phone-input`);
                if (!inp) return;
                inp.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ',') {
                        e.preventDefault();
                        addPhoneTag(prefix);
                    } else if (e.key === 'Backspace' && this.value === '' && phoneState[prefix].length > 0) {
                        phoneState[prefix].pop();
                        renderPhoneTags(prefix);
                    }
                });
                inp.addEventListener('blur', function () {
                    addPhoneTag(prefix);
                });
            });
        });

        // ═══════════════════════════════════════════════════
        // CREATE USER
        // ═══════════════════════════════════════════════════

        function openCreateModal() {
            document.getElementById('createUserForm').reset();
            clearPhoneTags('create');
            createModal.show();
        }

        function createUser() {
            // Flush any pending tag input
            addPhoneTag('create');

            const name     = $('#create-name').val().trim();
            const username = $('#create-username').val().trim();
            const email    = $('#create-email').val().trim();
            const password = $('#create-password').val().trim();
            const role     = $('#create-role').val();

            if (!name || !username || !email || !password || !role) {
                alert('Harap lengkapi semua field yang wajib diisi (Name, Username, Email, Password, Role).');
                return;
            }

            if (!confirm(`Buat user baru "${name}" dengan role "${role}"?`)) return;

            const $btn = $('#btn-create-user');
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Membuat...');

            $.ajax({
                url: '{{ route("programmer.user-management.store") }}',
                method: 'POST',
                data: {
                    name:         name,
                    username:     username,
                    email:        email,
                    password:     password,
                    role:         role,
                    bagian_code:  $('#create-bagian-code').val(),
                    phone_number: $('#create-phone').val(),
                    _token:       '{{ csrf_token() }}'
                },
                success: function (response) {
                    if (response.success) {
                        alert('User berhasil dibuat!');
                        createModal.hide();
                        window.location.reload();
                    } else {
                        alert(response.message || 'Gagal membuat user');
                    }
                    $btn.prop('disabled', false).html('<i class="fas fa-user-plus me-2"></i>Buat User');
                },
                error: function (xhr) {
                    const errors = xhr.responseJSON?.errors;
                    if (errors) {
                        const msg = Object.values(errors).flat().join('\n');
                        alert('Validation Error:\n' + msg);
                    } else {
                        alert('Error: ' + (xhr.responseJSON?.message || 'Gagal membuat user'));
                    }
                    $btn.prop('disabled', false).html('<i class="fas fa-user-plus me-2"></i>Buat User');
                }
            });
        }

        // ═══════════════════════════════════════════════════
        // EDIT USER
        // ═══════════════════════════════════════════════════

        function editUser(userId) {
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
                        $('#edit-password').val('');

                        // Load multi-phone tags
                        loadPhoneTags('edit', user.phone_number || '');

                        editModal.show();
                    } else {
                        alert('Gagal memuat data user');
                    }
                    $('#btn-save-user').prop('disabled', false).html('<i class="fas fa-save me-2"></i>Simpan');
                },
                error: function (xhr) {
                    alert('Error: ' + (xhr.responseJSON?.message || 'Gagal memuat data'));
                    $('#btn-save-user').prop('disabled', false).html('<i class="fas fa-save me-2"></i>Simpan');
                }
            });
        }

        function saveUser() {
            // Flush any pending tag input
            addPhoneTag('edit');

            if (!confirm('Apakah Anda yakin ingin menyimpan perubahan?')) return;

            const $btn = $('#btn-save-user');
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...');

            $.ajax({
                url: '{{ route("programmer.user-management.update") }}',
                method: 'POST',
                data: {
                    id:           $('#edit-user-id').val(),
                    name:         $('#edit-name').val(),
                    username:     $('#edit-username').val(),
                    email:        $('#edit-email').val(),
                    role:         $('#edit-role').val(),
                    bagian_code:  $('#edit-bagian-code').val(),
                    phone_number: $('#edit-phone').val(),
                    password:     $('#edit-password').val(),
                    _token:       '{{ csrf_token() }}'
                },
                success: function (response) {
                    if (response.success) {
                        alert('User berhasil diupdate!');
                        editModal.hide();
                        window.location.reload();
                    } else {
                        alert(response.message || 'Gagal menyimpan');
                    }
                    $btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i>Simpan');
                },
                error: function (xhr) {
                    alert('Error: ' + (xhr.responseJSON?.message || 'Gagal menyimpan'));
                    $btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i>Simpan');
                }
            });
        }

        // ═══════════════════════════════════════════════════
        // DELETE USER
        // ═══════════════════════════════════════════════════

        function deleteUser(userId, userName) {
            if (!confirm(`Apakah Anda yakin ingin menghapus user "${userName}"? Tindakan ini tidak dapat dibatalkan!`)) return;

            $(`button[data-id="${userId}"].btn-delete-user`).prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin"></i> Deleting...');

            $.ajax({
                url: '{{ route("programmer.user-management.destroy", ['id' => 'PLACEHOLDER_ID']) }}'.replace('PLACEHOLDER_ID', userId),
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function (response) {
                    if (response.success) {
                        alert('User berhasil dihapus!');
                        window.location.reload();
                    } else {
                        alert(response.message || 'Gagal menghapus user');
                        $(`button[data-id="${userId}"].btn-delete-user`).prop('disabled', false)
                            .html('<i class="fas fa-trash"></i> Delete');
                    }
                },
                error: function (xhr) {
                    alert('Error: ' + (xhr.responseJSON?.message || 'Gagal menghapus user'));
                    $(`button[data-id="${userId}"].btn-delete-user`).prop('disabled', false)
                        .html('<i class="fas fa-trash"></i> Delete');
                }
            });
        }
    </script>
@endsection