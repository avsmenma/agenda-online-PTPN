@extends('layouts.programmer')

@section('title', '2FA Reset Requests')
@section('page_title', '2FA Reset Requests')
@section('page_breadcrumb', 'Programmer → 2FA Reset Requests')

@section('content')
    <style>
        .reset-2fa-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .reset-2fa-header {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border-radius: 12px 12px 0 0;
        }

        .reset-table th {
            background: #f8fafc;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
        }

        .reset-table td {
            vertical-align: middle;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .status-badge.pending {
            background: #fef3c7;
            color: #b45309;
        }

        .status-badge.approved {
            background: #d1fae5;
            color: #047857;
        }

        .status-badge.rejected {
            background: #fee2e2;
            color: #dc2626;
        }

        .btn-approve {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: white;
            font-size: 13px;
            padding: 6px 14px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-approve:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
        }

        .btn-reject {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
            color: white;
            font-size: 13px;
            padding: 6px 14px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-reject:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
        }

        .requester-name {
            font-weight: 600;
            color: #1e293b;
            font-size: 14px;
        }

        .requester-detail {
            font-size: 12px;
            color: #64748b;
        }

        .reason-text {
            max-width: 320px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #475569;
        }

        .handled-info {
            font-size: 12px;
            color: #64748b;
        }

        .empty-state {
            padding: 60px 20px;
            text-align: center;
        }

        .empty-state i {
            font-size: 48px;
            color: #cbd5e1;
            margin-bottom: 12px;
        }

        .empty-state p {
            color: #64748b;
            font-size: 14px;
        }

        /* Reject Modal Styles */
        .reject-modal-overlay {
            position: fixed;
            inset: 0;
            z-index: 1060;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            padding: 16px;
        }

        .reject-modal-overlay.show {
            display: flex;
        }

        .reject-modal-content {
            background: white;
            border-radius: 12px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
            animation: modalSlideIn 0.25s ease;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px) scale(0.96);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .reject-modal-header {
            padding: 20px 24px 0;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .reject-modal-header h5 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #1e293b;
        }

        .reject-modal-header p {
            margin: 4px 0 0;
            font-size: 13px;
            color: #64748b;
        }

        .reject-modal-close {
            background: none;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            cursor: pointer;
            transition: all 0.15s ease;
            flex-shrink: 0;
        }

        .reject-modal-close:hover {
            background: #f1f5f9;
            color: #334155;
        }

        .reject-modal-body {
            padding: 20px 24px 24px;
        }

        .reject-modal-body textarea {
            width: 100%;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 14px;
            color: #1e293b;
            resize: vertical;
            transition: border-color 0.15s ease;
        }

        .reject-modal-body textarea:focus {
            outline: none;
            border-color: #94a3b8;
            box-shadow: 0 0 0 3px rgba(148, 163, 184, 0.15);
        }

        .reject-modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 16px;
        }

        .btn-modal-cancel {
            background: white;
            border: 1px solid #e2e8f0;
            color: #475569;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-modal-cancel:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .btn-modal-reject {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            border: none;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-modal-reject:hover {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
        }

        /* Dark mode support */
        .dark .reset-2fa-card {
            background: #1e293b;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.3);
        }

        .dark .reset-table th {
            background: #0f172a;
            color: #94a3b8;
        }

        .dark .reset-table td {
            color: #cbd5e1;
            border-color: #334155;
        }

        .dark .requester-name {
            color: #e2e8f0;
        }

        .dark .reason-text {
            color: #94a3b8;
        }

        .dark .reject-modal-content {
            background: #1e293b;
        }

        .dark .reject-modal-header h5 {
            color: #e2e8f0;
        }

        .dark .reject-modal-body textarea {
            background: #0f172a;
            border-color: #334155;
            color: #e2e8f0;
        }

        .dark .reject-modal-body textarea:focus {
            border-color: #475569;
        }
    </style>

    <div class="container-fluid">
        <div class="prog-page-header">
            <div class="prog-page-header-left">
                <a href="{{ route('programmer.dashboard') }}" class="btn-back-prog mb-2">
                    <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                </a>
                <h2><i class="fas fa-unlock-alt text-danger me-2"></i>Manajemen Reset 2FA</h2>
                <p>Approve atau reject permintaan reset 2FA dari user.</p>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Main Table Card --}}
        <div class="row">
            <div class="col-12">
                <div class="card reset-2fa-card">
                    <div class="card-header reset-2fa-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Daftar Permintaan Reset 2FA</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover reset-table mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Requester</th>
                                        <th>Alasan</th>
                                        <th>Status</th>
                                        <th>Handled</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($requests as $req)
                                        <tr>
                                            <td><strong>#{{ $req->id }}</strong></td>
                                            <td>
                                                <div class="requester-name">{{ $req->requester->name ?? '-' }}</div>
                                                <div class="requester-detail">{{ $req->requester->username ?? '-' }} · {{ $req->requester->role ?? '-' }}</div>
                                            </td>
                                            <td>
                                                <div class="reason-text" title="{{ $req->reason }}">{{ $req->reason }}</div>
                                            </td>
                                            <td>
                                                <span class="status-badge {{ $req->status }}">{{ $req->status }}</span>
                                            </td>
                                            <td>
                                                @if($req->handled_at)
                                                    <div class="handled-info">{{ $req->handled_at->format('d M Y H:i') }}</div>
                                                    <div class="handled-info">oleh {{ $req->programmer->name ?? '-' }}</div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if($req->status === 'pending')
                                                    <div class="btn-group" role="group">
                                                        <form method="POST" action="{{ url('/programmer/2fa-reset-requests/' . $req->id . '/approve') }}" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-approve me-1">
                                                                <i class="fas fa-check me-1"></i>Approve
                                                            </button>
                                                        </form>
                                                        <button type="button" class="btn btn-sm btn-reject" data-open-reject="{{ $req->id }}">
                                                            <i class="fas fa-times me-1"></i>Reject
                                                        </button>
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6">
                                                <div class="empty-state">
                                                    <i class="fas fa-inbox d-block"></i>
                                                    <p class="mb-0">Belum ada request reset 2FA.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Pagination --}}
                        @if($requests->hasPages())
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <small class="text-muted">
                                    Showing {{ $requests->firstItem() }} - {{ $requests->lastItem() }} of {{ $requests->total() }}
                                </small>
                                {{ $requests->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Reject Modals --}}
    @foreach($requests as $req)
        @if($req->status === 'pending')
            <div id="rejectModal-{{ $req->id }}" class="reject-modal-overlay">
                <div class="reject-modal-content">
                    <div class="reject-modal-header">
                        <div>
                            <h5><i class="fas fa-ban text-danger me-2"></i>Reject Request #{{ $req->id }}</h5>
                            <p>Masukkan alasan penolakan untuk request dari <strong>{{ $req->requester->name ?? '-' }}</strong>.</p>
                        </div>
                        <button type="button" class="reject-modal-close" data-close-reject="{{ $req->id }}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="reject-modal-body">
                        <form method="POST" action="{{ url('/programmer/2fa-reset-requests/' . $req->id . '/reject') }}">
                            @csrf
                            <textarea name="notes" required rows="4" placeholder="Alasan penolakan..."></textarea>
                            <div class="reject-modal-actions">
                                <button type="button" class="btn-modal-cancel" data-close-reject="{{ $req->id }}">Batal</button>
                                <button type="submit" class="btn-modal-reject"><i class="fas fa-ban me-1"></i>Reject</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

    <script>
        document.addEventListener('click', (e) => {
            // Open reject modal
            const openBtn = e.target.closest('[data-open-reject]');
            if (openBtn) {
                const id = openBtn.getAttribute('data-open-reject');
                const modal = document.getElementById(`rejectModal-${id}`);
                if (modal) modal.classList.add('show');
                return;
            }

            // Close reject modal
            const closeBtn = e.target.closest('[data-close-reject]');
            if (closeBtn) {
                const id = closeBtn.getAttribute('data-close-reject');
                const modal = document.getElementById(`rejectModal-${id}`);
                if (modal) modal.classList.remove('show');
                return;
            }

            // Close on backdrop click
            if (e.target.classList.contains('reject-modal-overlay')) {
                e.target.classList.remove('show');
            }
        });
    </script>
@endsection
